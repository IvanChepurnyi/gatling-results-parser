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

        $uniquePages = [];

        $allPages = $this->config->getPages();

        foreach ($reports as $report) {
            $pages = array_combine(
                array_map([$this->config, 'mapPageCode'], array_keys($report->getPages())),
                array_values($report->getPages())
            );

            $allPages = array_intersect_key($allPages, $pages);
            $pageNames = array_intersect_key($pages, $allPages);
            $uniquePages += array_flip($pageNames);
        }

        $lineScales = [
            'xAxes' => [
                [
                    'type' => 'linear',
                    'position' => 'bottom',
                    'min' => 0,
                    'scaleLabel' => ['labelString' => 'Test duration (s)']
                ]
            ],
            'yAxes' => [
                ['id' => 'first', 'position' => 'left', 'type' => 'linear'],
                ['id' => 'second', 'position' => 'right', 'type' => 'linear']
            ]
        ];

        $result = [
            'legends' => $this->config->getLegends(),
            'filters' => $this->config->getFilters(),
            'pages' => $allPages,
            'aggregateReport' => [
                [
                    'dataCode' => 'requests_ok',
                    'label' => 'Successful Requests per Page',
                    'axis' => array_values($allPages)
                ],
                [
                    'dataCode' => 'requests_ko',
                    'label' => 'Failed Requests per Page',
                    'axis' => array_values($allPages)
                ],
                [
                    'dataCode' => 'response_ok',
                    'label' => 'Mean Successful Response Time per Page',
                    'axis' => array_values($allPages)
                ],
                [
                    'dataCode' => 'response_ko',
                    'label' => 'Mean Failed Response Time per Page',
                    'axis' => array_values($allPages)
                ]
            ],
            'pageReport' => [
                [
                    'dataCode' => 'response_ok',
                    'label' => '#Page Successful Response Times',
                    'axis' => [
                        'Min',
                        '50pct',
                        '75pct',
                        '95pct',
                        '99pct',
                        'Max',
                        'Mean'
                    ]
                ],
                [
                    'dataCode' => 'response_ko',
                    'label' => '#Page Failed Response Times',
                    'axis' => [
                        'Min',
                        '50pct',
                        '75pct',
                        '95pct',
                        '99pct',
                        'Max',
                        'Mean'
                    ]
                ],
                [
                    'dataCode' => 'indicatorPercent',
                    'label' => '#Page Indicator Percent',
                    'axis' => ['<800ms', '>800ms <1200ms', '>1200ms', 'Failed']
                ],
                [
                    'dataCode' => 'indicatorCount',
                    'label' => '#Page Indicator Count',
                    'axis' => ['<800ms', '>800ms <1200ms', '>1200ms', 'Failed']
                ]
            ],
            'systemReport' => [
                [
                    'dataCode' => 'mysqlLocks',
                    'label' => 'MySQL Locks',
                    'scales' => $lineScales
                ],
                [
                    'dataCode' => 'mysqlEfficiency',
                    'label' => 'MySQL Efficiency',
                    'scales' => $lineScales
                ],
                [
                    'dataCode' => 'memory',
                    'label' => 'System Memory',
                    'scales' => $lineScales
                ],
                [
                    'dataCode' => 'dynamic',
                    'label' => 'CPU Intensity',
                    'scales' => $lineScales
                ]
            ],
            'overtimeReport' => [
                [
                    'dataCode' => 'request',
                    'label' => 'Requests Over Time',
                    'scales' => $lineScales
                ],
                [
                    'dataCode' => 'response',
                    'label' => 'Responses Over Time',
                    'scales' => $lineScales
                ],
                [
                    'dataCode' => 'responseTime',
                    'label' => 'TTFB Over Time',
                    'scales' => $lineScales
                ]
            ]
        ];

        /** @var ReportReader $report */
        foreach ($reports as $report) {
            $legend = $this->config->findLegend($report->getReportCode());
            $info = [
                'code' => $report->getReportCode(),
                'path' => $report->getReportPath(),
                'legend' => $legend,
                'description' => $this->config->getLegends()[$legend],
                'position' => array_search($legend, array_keys($this->config->getLegends()))
            ];
            
            foreach (array_keys($this->config->getFilters()) as $filterCode) {
                $info['filter'][$filterCode] = $this->config->findFilterValue(
                    $filterCode,
                    $report->getReportCode()
                );
            }

            $reportResult = [];
            $info['urlPath'] = $report->getInfoUrl();

            $result['reports'][] = $info;

            $mappedCodes = [];

            foreach (array_keys($report->getPages()) as $pageCode) {
                $mappedCodes[$this->config->mapPageCode($pageCode)] = $pageCode;
            }

            $reportResult['aggregateReport']['requests_ok'] = [];
            $reportResult['aggregateReport']['requests_ko'] = [];
            $reportResult['aggregateReport']['response_ok'] = [];
            $reportResult['aggregateReport']['response_ko'] = [];

            $opinionStat = $report->fetchGlobalOpinionStat();
            $reportResult['aggregateReport']['indicatorPercent'] = $opinionStat['percent'];
            $reportResult['aggregateReport']['indicatorCount'] = $opinionStat['count'];

            $reportResult['pageReport']['response'] = [];
            $reportResult['pageReport']['indicatorPercent'] = [];
            $reportResult['pageReport']['indicatorCount'] = [];

            foreach (array_keys($allPages) as $pageCode) {
                $requests =  $report
                    ->fetchNumberOfPageRequestsStat($mappedCodes[$pageCode]);
                $response =  $report
                    ->fetchMeanResponseStat($mappedCodes[$pageCode]);

                $reportResult['aggregateReport']['requests_ok'][] = $requests[0];
                $reportResult['aggregateReport']['requests_ko'][] = $requests[1];

                $reportResult['aggregateReport']['response_ok'][] = $response[0];
                $reportResult['aggregateReport']['response_ko'][] = $response[1];

                $opinionStat = $report->fetchOpinionStat($mappedCodes[$pageCode]);

                $responseStat = $report->fetchResponseStat($mappedCodes[$pageCode]);
                $reportResult['pageReport']['response_ok'][$pageCode] = $this->extractOkValues($responseStat);
                $reportResult['pageReport']['response_ko'][$pageCode] = $this->extractKoValues($responseStat);

                $reportResult['pageReport']['indicatorPercent'][$pageCode] = $opinionStat['percent'];
                $reportResult['pageReport']['indicatorCount'][$pageCode] = $opinionStat['count'];
            }

            $reportResult += $this->fetchTimelineReports($report, $uniquePages);

            file_put_contents($report->getInfoPath(), json_encode($reportResult));
        }

        return $result;
    }

    private function extractOkValues($list)
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $item[0];
        }
        return $result;
    }

    private function extractKoValues($list)
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $item[1];
        }
        return $result;
    }

    private function fetchTimelineReports(ReportReader $report, $uniquePages)
    {
        if (!is_file($report->getSimulationPath())) {
            return [];
        }

        $result = [];

        $simulation = new SimulationParser($report->getSimulationPath());

        $timelineDistributor = new TimelineDistributor($simulation->getSimulationStartTime(), 1);

        $data = $simulation->aggregate($timelineDistributor, array_keys($uniquePages));

        $result['overtimeReport']['request'] = $this->generateDataSet('# of Requests', $data['request']);

        $result['overtimeReport']['response'] = $this->generateDataSet('# of Responses', $data['response']);

        $result['overtimeReport']['responseTime'] = $this->generateDataSet('Mean Response Time', $data['mean'])
            + $this->generateDataSet('Max Response Time', $data['max'], '#1B5E20', '#b71c1c');
        
        if (is_file($report->getPressurePath())) {
            $indexDataSets = $this->generateIndexDataSets($report, $timelineDistributor);

            foreach ($result['overtimeReport'] as $code => $value) {
                $result['overtimeReport'][$code] = $value + $indexDataSets;
            }
        }

        if (is_file($report->getSystemUsagePath())) {
            $systemUsageReader = new SysUsageReader($report->getSystemUsagePath());
            $result['systemReport']['mysqlLocks'] = $this->generateStatReport(
                [
                    'Deadlocks' => [
                        'code' => 'mysql_deadlocks',
                        'dynamic' => true
                    ],
                    'Peak Lock Waits' => 'mysql_wait_locks',
                    'Total Lock Waits' => [
                        'code' => 'mysql_total_locks',
                        'dynamic' => true
                    ],
                    'Average Lock Time' => [
                        'code' => 'mysql_lock_time_avg',
                        'axis' => 'second'
                    ]
                ],
                $timelineDistributor,
                $systemUsageReader
            );

            $result['systemReport']['mysqlEfficiency'] = $this->generateStatReport(
                [
                    'Table Scans (%)' => [
                        'code' => 'mysql_table_scans_pct',
                        'axis' => 'second'
                    ],
                    'Query Select Scans' => 'mysql_select_scans',
                    'Query Sort Scans' => 'mysql_sort_scans',
                    'Query Join Scans' => 'mysql_join_scans'
                ],
                $timelineDistributor,
                $systemUsageReader
            );

            $result['systemReport']['memory'] = $this->generateStatReport(
                [
                    'Used Memory' => 'sys_memory_used',
                    'Used Memory (Buffers)' => 'sys_memory_used_buffers',
                    'Used Memory (Cache)' => 'sys_memory_used_cache',
                    'Used Swap' => 'sys_memory_swap_used',
                    'Used Swap (Buffers)' => 'sys_memory_swap_used_cache',
                ],
                $timelineDistributor,
                $systemUsageReader
            );

            $result['systemReport']['dynamic'] = $this->generateStatReport(
                [
                    'Listen Queue' => 'fpm_listen_queue',
                    'Total Workers' => 'fpm_total_processes',
                    'Active Workers' => 'fpm_active_processes',
                    'Load Average' => [
                        'code' => 'sys_load_avg',
                        'axis' => 'second'
                    ]
                ],
                $timelineDistributor,
                $systemUsageReader
            );
        }

        return $result;
    }

    private function generateStatReport($properties, TimelineDistributor $distributor, SysUsageReader $reader)
    {
        $report = [];
        $index = 1;
        $total = count($properties);

        foreach ($properties as $propertyLabel => $property) {
            if (!is_array($property)) {
                $property = ['code' => $property];
            }


            if (!empty($property['dynamic'])) {
                $value = $reader->dynamicValue($property['code'], $distributor);
            } else {
                $value = $reader->staticValue($property['code'], $distributor);
            }

            $report[$propertyLabel] = [
                'color' => sprintf('hsl(%d, 50%%, 50%%)', ceil($index / $total * 360)),
                'data' => $this->extractXy($value),
                'max' => max($value),
                'yAxisID' => isset($property['axis']) ? $property['axis'] : 'first'
            ];

            $index++;
        }

        return $report;
    }

    private function generateDataSet($prefix, $data, $okColor = '#8BC34A', $koColor = '#E53935')
    {
        $result = [];

        if (isset($data['ok'])) {
            $result[sprintf('%s OK', $prefix)] = [
                'color' => $okColor,
                'data' => $this->extractXy($data['ok']),
                'max' => max($data['ok']),
                'yAxisID' => 'first',
            ];
        }
        if (isset($data['ko'])) {
            $result[sprintf('%s KO', $prefix)] = [
                'color' => $koColor,
                'data' => $this->extractXy($this->scatterData($data['ko'])),
                'max' => max($data['ko']),
                'yAxisID' => 'first',
            ];
        }

        return $result;
    }

    private function scatterData($entries)
    {
        foreach (array_keys($entries) as $key) {
            if (!isset($entries[$key - 1]) && $key > 0) {
                $entries[$key - 1] = 0;
            }
        }

        return $entries;
    }

    private function extractXy($values)
    {
        $result = [];

        foreach ($values as $x => $y) {
            $result[] = ['x' => (int)$x, 'y' => (float)$y];
        }

        return $result;
    }

    /**
     * @param ReportReader $report
     * @param TimelineDistributor $timelineDistributor
     *
     * @return array
     */
    private function generateIndexDataSets(ReportReader $report, TimelineDistributor $timelineDistributor)
    {
        $pressureReader = new PressureReader($report->getPressurePath());
        $pressureReport = $pressureReader->aggregate($timelineDistributor);

        $indexDataSets = [];

        $index = 1;
        $total = count($pressureReport);

        foreach ($pressureReport as $indexName => $times) {
            $indexDataSets[sprintf('Running %s', $indexName)] = [
                'color' => sprintf('hsl(%d, 50%%, 50%%)', ceil($index / $total * 360)),
                'data' => $this->extractXy($times),
                'max' => 4,
                'yAxisID' => 'second'
            ];

            $index ++;
        }
        return $indexDataSets;
    }
}
