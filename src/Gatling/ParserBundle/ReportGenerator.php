<?php

namespace Gatling\ParserBundle;

class ReportGenerator
{
    private $config;

    private $reportFinder;

    public function __construct(ConfigurationInterface $config, ReportFinder $reportFinder)
    {
        $this->config = $config;
        $this->reportFinder = $reportFinder;
    }

    public function generate()
    {
        $reports = $this->reportFinder->find();

        $reports = array_filter($reports, function ($report) {
            try {
                $this->config->findLegend($report->getReportCode());
            } catch (InvalidLegendException $e) {
                return false;
            }

            return true;
        });

        $allPages = array_map(
            function ($report) {
                return array_combine(
                    array_map([$this->config, 'mapPageCode'], array_keys($report->getPages())),
                    array_values($report->getPages())
                );
            },
            $reports
        );

        array_unshift($allPages, $this->config->getPages());

        $commonPages = call_user_func_array(
            'array_intersect_key',
            $allPages
        );

        $result = [
            'legends' => $this->config->getLegends(),
            'filters' => $this->config->getFilters(),
            'pages' => $commonPages,
            'aggregateReport' => [
                [
                    'dataCode' => 'requests',
                    'label' => 'Requests served per Page',
                    'axis' => array_values($commonPages)
                ],
                [
                    'dataCode' => 'response',
                    'label' => 'Mean Response Time per Page',
                    'axis' => array_values($commonPages)
                ]
            ],
            'pageReport' => [
                [
                    'dataCode' => 'response',
                    'label' => '#Page Response Times',
                    'axis' => [
                        'Minimum Response Time',
                        '50 percentile',
                        '75 percentile',
                        '95 percentile',
                        '99 percentile',
                        'Maximum Response Time',
                        'Mean Response Time'
                    ]
                ],
                [
                    'dataCode' => 'indicatorPercent',
                    'label' => '#Page Indicator Percent',
                    'axis' => ['below 800ms', 'between 800ms and 1200ms', 'above 1200ms']
                ],
                [
                    'dataCode' => 'indicatorCount',
                    'label' => '#Page Indicator Count',
                    'axis' => ['below 800ms', 'between 800ms and 1200ms', 'above 1200ms']
                ]
            ]
        ];

        /** @var ReportReader $report */
        foreach ($reports as $report) {
            $legend = $this->config->findLegend($report->getReportCode());
            $reportResult = [
                'code' => $report->getReportCode(),
                'path' => $report->getReportPath(),
                'legend' => $legend,
                'color' => $this->config->getLegends()[$legend],
                'position' => array_search($legend, array_keys($this->config->getLegends()))
            ];

            foreach (array_keys($this->config->getFilters()) as $filterCode) {
                $reportResult['filter'][$filterCode] = $this->config->findFilterValue(
                    $filterCode,
                    $report->getReportCode()
                );
            }

            $mappedCodes = [];

            foreach (array_keys($report->getPages()) as $pageCode) {
                $mappedCodes[$this->config->mapPageCode($pageCode)] = $pageCode;
            }

            $reportResult['aggregateReport']['requests'] = [];
            $reportResult['aggregateReport']['response'] = [];
            $reportResult['pageReport']['response'] = [];
            $reportResult['pageReport']['indicatorPercent'] = [];
            $reportResult['pageReport']['indicatorCount'] = [];

            foreach (array_keys($commonPages) as $pageCode) {
                $reportResult['aggregateReport']['requests'][] = $report
                    ->fetchNumberOfPageRequestsStat($mappedCodes[$pageCode]);
                $reportResult['aggregateReport']['response'][] = $report
                    ->fetchMeanResponseStat($mappedCodes[$pageCode]);

                $opinionStat = $report->fetchOpinionStat($mappedCodes[$pageCode]);
                $reportResult['pageReport']['response'][$pageCode] = array_values($report->fetchResponseStat($mappedCodes[$pageCode]));
                $reportResult['pageReport']['indicatorPercent'][$pageCode] = $opinionStat['percent'];
                $reportResult['pageReport']['indicatorCount'][$pageCode] = $opinionStat['count'];
            }


            $result['reports'][] = $reportResult;
        }

        return $result;
    }
}
