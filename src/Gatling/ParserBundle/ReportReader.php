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
    }

    private function load()
    {
        if ($this->stats !== null) {
            return $this;
        }

        $stats = json_decode(file_get_contents($this->path . '/js/stats.json'), true);
        $this->stats = $stats['stats'];

        foreach ($stats['contents'] as $content) {
            $this->pageStats[$this->replacePageName($content['name'])] = $content['stats'];
            $this->pages[$this->replacePageName($content['name'])] = $content['name'];
        }

        return $this;
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
        $this->load();
        return $this->pages;
    }

    private function fetchPageStat($pageCode, $statField)
    {
        $this->load();
        $stat = $this->pageStats[$pageCode][$statField];

        return isset($stat['ok']) ? [$stat['ok'], $stat['ko']] : $stat;
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
                $this->fetchPageStat($pageCode, 'group3')['percentage'],
                $this->fetchPageStat($pageCode, 'group4')['percentage'],
            ],
            'count' => [
                $this->fetchPageStat($pageCode, 'group1')['count'],
                $this->fetchPageStat($pageCode, 'group2')['count'],
                $this->fetchPageStat($pageCode, 'group3')['count'],
                $this->fetchPageStat($pageCode, 'group4')['count'],
            ]
        ];
    }

    public function fetchGlobalOpinionStat()
    {
        $this->load();
        return [
            'percent' => [
                $this->stats['group1']['percentage'],
                $this->stats['group2']['percentage'],
                $this->stats['group3']['percentage'],
                $this->stats['group4']['percentage'],
            ],
            'count' => [
                $this->stats['group1']['count'],
                $this->stats['group2']['count'],
                $this->stats['group3']['count'],
                $this->stats['group4']['count']
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

    public function getSimulationPath()
    {
        return $this->path . DIRECTORY_SEPARATOR . 'simulation.log';
    }

    public function getSystemUsagePath()
    {
        return $this->path . DIRECTORY_SEPARATOR . 'usage.csv';
    }

    public function getPressurePath()
    {
        return $this->path . DIRECTORY_SEPARATOR . 'pressure.csv';
    }

    public function getInfoPath()
    {
        return $this->path . DIRECTORY_SEPARATOR . 'info.json';
    }

    public function getInfoUrl()
    {
        return basename($this->path) . '/info.json';
    }
}
