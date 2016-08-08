<?php

namespace spec\Gatling\ParserBundle;

use Gatling\ParserBundle\InvalidFilterException;
use Gatling\ParserBundle\InvalidLegendException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ConfigurationSpec extends ObjectBehavior
{
    function it_implements_configuration_interface()
    {
        $this->shouldImplement('Gatling\ParserBundle\ConfigurationInterface');
    }

    function it_when_no_config_is_specified_returns_no_legends()
    {
        $this->getLegends()->shouldReturn([]);
    }

    function it_when_configuration_is_provided_returns_no_filters()
    {
        $this->getFilters()->shouldReturn([]);
    }

    function it_when_configuration_is_provided_throws_InvalidLegendException()
    {
        $this->shouldThrow(new InvalidLegendException())->duringFindLegend('some-dummy-directory-name');
    }

    function it_when_configuration_is_provided_throws_InvalidFilterException()
    {
        $this->shouldThrow(new InvalidFilterException())->duringFindFilterValue('users', 'some-dummy-directory-name');
    }

    private function provideConfiguration()
    {
        $this->beConstructedWith([
            'legend-match' => [
                'Legend 1' => 'legend1-',
                'Legend 1 Any' => '*-legend1-',
                'Legend 2' => 'legend2-'
            ],
            'legend-color' => [
                'Legend 1' => 'red',
                'Legend 1 Any' => 'blue',
                'Legend 2' => 'yellow'
            ],
            'filters' => [
                'filter1' => [
                    'value1' => '*-f1v1-',
                    'value2' => '*-f1v2-'
                ],
                'filter2' => [
                    'value1' => '*-f2v1-',
                    'value2' => '*-f2v2-'
                ],
                'filter3' => [
                    'value1' => '*-f3v1-',
                    'value2' => '*-f3v2-',
                    'value_default' => ''
                ]
            ],
            'pages' => [
                'page_1' => 'Page One Title',
                'page_2' => 'Page Two Title'
            ],
            'page_map' => [
                'page_3' => 'page_2'
            ]
        ]);
    }
    
    
    function it_when_configuration_is_provided_returns_proper_legends()
    {
        $this->provideConfiguration();
        $this->getLegends()->shouldReturn([
            'Legend 1' => 'red',
            'Legend 1 Any' => 'blue',
            'Legend 2' => 'yellow'
        ]);
    }


    function it_when_configuration_is_provided_returns_proper_filters()
    {
        $this->provideConfiguration();

        $this->getFilters()->shouldReturn([
            'filter1' => ['value1', 'value2'],
            'filter2' => ['value1', 'value2'],
            'filter3' => ['value1', 'value2', 'value_default']
        ]);
    }

    function it_when_configuration_is_provided_returns_legend_by_match()
    {
        $this->provideConfiguration();

        $this->findLegend('legend1-test-run')->shouldReturn('Legend 1');
        $this->findLegend('another-legend1-test-run')->shouldReturn('Legend 1 Any');
        $this->findLegend('legend2-test-run')->shouldReturn('Legend 2');
    }

    function it_when_configuration_is_provided_throws_InvalidLegendException_if_legend_is_not_found()
    {
        $this->provideConfiguration();

        $this->shouldThrow(new InvalidLegendException())->duringFindLegend('legend3-test-run');
    }


    function it_when_configuration_is_provided_returns_filter_by_match()
    {
        $this->provideConfiguration();

        $testString = 'legend1-f1v2-f2v1-run';

        $this->findFilterValue('filter1', $testString)->shouldReturn('value2');
        $this->findFilterValue('filter2', $testString)->shouldReturn('value1');
        $this->findFilterValue('filter3', $testString)->shouldReturn('value_default');
    }

    function it_when_configuration_is_provided_throws_InvalidFilterException_if_filter_value_is_not_found()
    {
        $this->provideConfiguration();
        $this->shouldThrow(new InvalidFilterException())->duringFindFilterValue('filter1', 'legend3-f2v1');
    }

    function it_when_configuration_is_provided_returns_pages()
    {
        $this->provideConfiguration();
        $this->getPages()->shouldReturn([
            'page_1' => 'Page One Title',
            'page_2' => 'Page Two Title'
        ]);
    }

    function it_remaps_page_code()
    {
        $this->provideConfiguration();
        $this->mapPageCode('page_3')->shouldReturn('page_2');
        $this->mapPageCode('page_1')->shouldReturn('page_1');
    }
}
