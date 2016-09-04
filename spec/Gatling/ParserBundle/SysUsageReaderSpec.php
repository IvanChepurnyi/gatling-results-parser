<?php

namespace spec\Gatling\ParserBundle;

use Gatling\ParserBundle\TimelineDistributor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SysUsageReaderSpec extends ObjectBehavior
{
    /**
     * @var TimelineDistributor
     */
    private $distribution;

    function let()
    {
        $this->beConstructedWith(
            __DIR__  . '/fixture/usage.csv'
        );

        $this->distribution = new TimelineDistributor(1471473386452, 50);
    }

    function it_returns_maximum_value_within_distribution_for_static_value()
    {
        $this->staticValue('mysql_current_locks', $this->distribution)->shouldBeLike([
            0 => 4789,
            50 => 13471,
            100 => 7558,
            150 => 9768,
            200 => 10934,
            250 => 5,
            300 => 943
        ]);
    }

    function it_returns_maximum_value_within_distribution_for_dynamic_value()
    {
        $this->dynamicValue('mysql_total_locks', $this->distribution)->shouldBeLike([
            0 => 4,
            50 => 3,
            100 => 1,
            150 => 2,
            200 => 2,
            250 => 0,
            300 => 1
        ]);
    }

}
