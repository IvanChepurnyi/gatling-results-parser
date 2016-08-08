<?php

namespace Gatling\ParserBundle;

class ReportReader
{
    /**
     * Report path
     *
     * @var string
     */
    private $path;

    /**
     * @var array[]
     */
    private $stats;

    /**
     * Pages stats
     *
     * @var string[]
     */
    private $pageStats;

    /**
     * Pages list
     *
     * @var string[]
     */
    private $pages;

    /**
     * New report reader instance
     *
     * @param string $path
     * @throws InvalidReportDirectoryException
     */
    public function __construct($path)
    {
        $this->path = $path;

        if (!is_dir($path)) {
            throw new InvalidReportDirectoryException('Report directory does not exist');
        }

        if (!file_exists($path . '/js/stats.json')) {
            throw new InvalidReportDirectoryException('Missing report file');
        }

        $this->stats = json_decode(file_get_contents($this->path . '/js/stats.json'), true);

        if (!empty($this->stats['stats']['numberOfRequests']['ko'])) {
            throw new InvalidReportDirectoryException('Report contains at least one failed request');
        }

        foreach ($this->stats['contents'] as $content) {
            $this->pageStats[$this->replacePageName($content['name'])] = $content['stats'];
            $this->pages[$this->replacePageName($content['name'])] = $content['name'];
        }
    }

    private function replacePageName($pageName)
    {
        return strtr(strtolower(preg_replace('/[^a-zA-Z0-9 ]/', '', $pageName)), ' ', '_');
    }

    public function getReportPath()
    {
        return basename($this->path) . '/index.html';
    }

    public function getPages()
    {
        return $this->pages;
    }

    private function fetchPageStat($pageCode, $statField)
    {
        $stat = $this->pageStats[$pageCode][$statField];

        return isset($stat['total']) ? $stat['total'] : $stat;
    }

    public function fetchNumberOfPageRequestsStat($pageCode)
    {
        return $this->fetchPageStat($pageCode, 'numberOfRequests');
    }

    public function fetchOpinionStat($pageCode)
    {
        return [
            'percent' => [
                $this->fetchPageStat($pageCode, 'group1')['percentage'],
                $this->fetchPageStat($pageCode, 'group2')['percentage'],
                $this->fetchPageStat($pageCode, 'group3')['percentage']
            ],
            'count' => [
                $this->fetchPageStat($pageCode, 'group1')['count'],
                $this->fetchPageStat($pageCode, 'group2')['count'],
                $this->fetchPageStat($pageCode, 'group3')['count']
            ]
        ];
    }

    public function fetchResponseStat($pageCode)
    {
        return [
            'min' => $this->fetchPageStat($pageCode, 'minResponseTime'),
            'p50' => $this->fetchPageStat($pageCode, 'percentiles1'),
            'p75' => $this->fetchPageStat($pageCode, 'percentiles2'),
            'p95' => $this->fetchPageStat($pageCode, 'percentiles3'),
            'p99' => $this->fetchPageStat($pageCode, 'percentiles4'),
            'max' => $this->fetchPageStat($pageCode, 'maxResponseTime'),
            'mean' => $this->fetchPageStat($pageCode, 'meanResponseTime'),
        ];
    }

    public function fetchMeanResponseStat($pageCode)
    {
        return $this->fetchPageStat($pageCode, 'meanResponseTime');
    }

    public function getReportCode()
    {
        return basename($this->path);
    }
}
