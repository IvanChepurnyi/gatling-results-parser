<?php

namespace spec\Gatling\ParserBundle;

use Gatling\ParserBundle\TimelineDistributor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SimulationParserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            __DIR__  . '/fixture/simulation.log'
        );
    }

    function it_returns_simulation_start_time()
    {
        $this->getSimulationStartTime()->shouldReturn(1471662930127);
    }

    function it_returns_simulation_start_time_in_seconds()
    {
        $this->getSimulationStartTimeSeconds()->shouldReturn(1471662930);
    }

    function it_returns_simulation_end_time_in_seconds()
    {
        $this->getSimulationEndTimeSeconds()->shouldReturn(1471663260);
    }

    function it_returns_only_users_array_if_non_existing_page_is_specified()
    {
        $distributor = new TimelineDistributor(1471662930127, 30);
        $this->aggregate($distributor, ['Non existing page'])->shouldReturn([
            'user' => [
                0 => 9,
                30 => 10,
                300 => 1,
                330 => 0
            ],
            'request' => [],
            'response' => [],
            'mean' => [],
            'max' => []
        ]);
    }

    function it_returns_full_aggregated_data_when_correct_pages_specified_specified()
    {
        $distributor = new TimelineDistributor(1471662930127, 30);
        $this->aggregate($distributor, ['Homepage', 'Category View: Default'])->shouldBeLike([
            'user' => [
                0 => 9,
                30 => 10,
                300 => 1,
                330 => 0
            ],
            'request' => [
                'ok' => [
                    0 => 44,
                    30 => 97,
                    60 => 105,
                    90 => 101,
                    120 => 103,
                    150 => 112,
                    180 => 107,
                    210 => 103,
                    240 => 114,
                    270 => 105,
                    300 => 71,
                ]
            ],
            'response' => [
                'ok' => [
                    0 => 44,
                    30 => 97,
                    60 => 105,
                    90 => 101,
                    120 => 103,
                    150 => 112,
                    180 => 107,
                    210 => 103,
                    240 => 114,
                    270 => 105,
                    300 => 71,
                ]
            ],
            'mean' => [
                'ok' => [
                    0 => 25,
                    30 => 3,
                    60 => 2,
                    90 => 3,
                    120 => 2,
                    150 => 2,
                    180 => 2,
                    210 => 2,
                    240 => 2,
                    270 => 3,
                    300 => 2,
                ]
            ],
            'max' => [
                'ok' => [
                    0 => 922,
                    30 => 11,
                    60 => 9,
                    90 => 32,
                    120 => 9,
                    150 => 8,
                    180 => 7,
                    210 => 12,
                    240 => 13,
                    270 => 49,
                    300 => 8,
                ]
            ]
        ]);
    }


}
