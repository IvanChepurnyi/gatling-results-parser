<?php

namespace spec\Gatling\ParserBundle;

use Gatling\ParserBundle\TimelineDistributor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PressureReaderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            __DIR__  . '/fixture/pressure.csv'
        );
    }

    function it_returns_report_distribution()
    {
        $this->aggregate(new TimelineDistributor(1471473386452, 5))->shouldReturn([
            'Category Products Index' => [
                -5 => 0,
                -4 => 1,
                -2 => 1,
                -1 => 0,
                307 => 0,
                308 => 1,
                310 => 1,
                311 => 0,
            ],
            'Product Categories Index' => [
                -2 => 0,
                -1 => 1,
                0 => 1,
                1 => 0,
                310 => 0,
                311 => 1,
                313 => 1,
                314 => 0,
            ],
            'Stock Index' => [
                0 => 0,
                1 => 1,
                6 => 1,
                7 => 0,
                313 => 0,
                314 => 1,
                321 => 1,
                322 => 0
            ],
            'Product Prices Index' => [
                6 => 0,
                7 => 1,
                70 => 1,
                71 => 0,
                321 => 0,
                322 => 1,
                332 => 1,
                333 => 0
            ],
            'Layered Navigation Index' => [
               70 => 0,
               71 => 1,
               224 => 1,
               225 => 0,
            ],
            'Product Flat Index' => [
               224 => 0,
               225 => 1,
               244 => 1,
               245 => 0,
            ]
        ]);
    }
}
