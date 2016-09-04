<?php

namespace spec\Gatling\ParserBundle;

use Gatling\ParserBundle\InvalidReportDirectoryException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReportReaderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(__DIR__  . '/fixture/sample-report');
    }

    function it_when_path_does_not_exists_throws_InvalidReportDirectoryException()
    {
        $this->beConstructedWith('some_non_existing_path');
        $this->shouldThrow(new InvalidReportDirectoryException('Report directory does not exist'))->duringInstantiation();
    }

    function it_when_path_is_invalid_throws_InvalidReportDirectoryException()
    {
        $this->beConstructedWith(__DIR__  . '/fixture/bad-path');
        $this->shouldThrow(new InvalidReportDirectoryException('Missing report file'))->duringInstantiation();
    }

    function it_when_report_contains_errors_it_should_wrok_fine()
    {
        $this->beConstructedWith(__DIR__  . '/fixture/error-path');
        $this->getReportPath()->shouldReturn('error-path/index.html');
    }

    function it_returns_report_path()
    {
        $this->getReportPath()->shouldReturn('sample-report/index.html');
    }

    function it_returns_simulation_log_path()
    {
        $this->getSimulationPath()->shouldReturn(__DIR__ . '/fixture/sample-report/simulation.log');
    }

    function it_returns_system_usage_path()
    {
        $this->getSystemUsagePath()->shouldReturn(__DIR__ . '/fixture/sample-report/usage.csv');
    }

    function it_returns_pressure_path()
    {
        $this->getPressurePath()->shouldReturn(__DIR__ . '/fixture/sample-report/pressure.csv');
    }

    function it_returns_info_path()
    {
        $this->getInfoPath()->shouldReturn(__DIR__ . '/fixture/sample-report/info.json');
    }

    function it_returns_info_url()
    {
        $this->getInfoUrl()->shouldReturn('sample-report/info.json');
    }

    function it_returns_list_of_tested_pages()
    {
        $this->getPages()->shouldReturn([
            'homepage' => 'Homepage',
            'category_page_default' => 'Category Page: Default',
            'category_page_back' => 'Category Page: Back'
        ]);
    }

    function it_returns_number_of_page_requests_by_page_code()
    {
        $this->fetchNumberOfPageRequestsStat('homepage')->shouldReturn([974, 0]);
    }

    function it_returns_mean_response_time_by_page_code()
    {
        $this->fetchMeanResponseStat('homepage')->shouldReturn([3, 0]);
    }

    function it_returns_opnion_stats_by_page_code()
    {
        $this->fetchOpinionStat('homepage')->shouldReturn([
            'percent' => [
                100, 0, 0, 0
            ],
            'count' => [
                973, 1, 0, 0
            ]
        ]);
    }

    function it_returns_global_opinion_stats()
    {
        $this->fetchGlobalOpinionStat()->shouldReturn([
            'percent' => [
                99, 1, 0, 0
            ],
            'count' => [
                10438, 79, 15, 0
            ]
        ]);
    }

    function it_returns_response_time_stats_by_page_code()
    {
        $this->fetchResponseStat('homepage')->shouldReturn([
            'min' => [1, 0],
            'p50' => [2, 0],
            'p75' => [3, 0],
            'p95' => [6, 0],
            'p99' => [12, 0],
            'max' => [946, 0],
            'mean' => [3, 0]
        ]);
    }

    function it_returns_report_code()
    {
        $this->getReportCode()->shouldReturn('sample-report');
    }

    function it_returns_page_request_count()
    {
        $this->getReportCode()->shouldReturn('sample-report');
    }

}
