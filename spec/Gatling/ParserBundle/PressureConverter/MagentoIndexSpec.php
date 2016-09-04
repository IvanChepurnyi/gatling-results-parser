<?php

namespace spec\Gatling\ParserBundle\PressureConverter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MagentoIndexSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'magento1',
            [
                'Category Products' => 'Category Product Index',
                'Stock Status' => 'Stock Index',
                'Product Prices' => 'Product Prices Index',
                'Product Attributes' => 'Layered Navigation Index',
                'Product Flat Data' => 'Product Flat Index'
            ],
            '/^(.*?) index was rebuilt successfully in (.*?)$/'
        );
    }

    function it_returns_expected_code_for_index_parser()
    {
        $this->getCode()->shouldReturn('magento1');
    }

    function it_parses_full_sequence_of_index_calls()
    {
        $this->parse(
            [
                'Category Products index was rebuilt successfully in 00:00:07',
                'Stock Status index was rebuilt successfully in 00:00:08',
                'Product Prices index was rebuilt successfully in 00:01:28',
                'Product Attributes index was rebuilt successfully in 00:02:54',
                'Product Flat Data index was rebuilt successfully in 00:00:45'
            ],
            1471469251,
            1471469577
        )->shouldReturn([
            ['Category Product Index', 1471469251, 1471469258],
            ['Stock Index', 1471469259, 1471469267],
            ['Product Prices Index', 1471469268, 1471469356],
            ['Layered Navigation Index', 1471469357, 1471469531],
            ['Product Flat Index', 1471469532, 1471469577]
        ]);
    }

    function it_ignores_wrong_line_sequence_of_index_calls()
    {
        $this->parse(
            [
                'Category Products index was rebuilt successfully in 00:00:07',
                'Some wrong line',
                'Stock Status index was rebuilt successfully in 00:00:08',
                'Product Prices index was rebuilt successfully in 00:01:28',
                'Product Attributes index was rebuilt successfully in 00:02:54',
                'Product Flat Data index was rebuilt successfully in 00:00:45'
            ],
            1471469251,
            1471469577
        )->shouldReturn([
            ['Category Product Index', 1471469251, 1471469258],
            ['Stock Index', 1471469259, 1471469267],
            ['Product Prices Index', 1471469268, 1471469356],
            ['Layered Navigation Index', 1471469357, 1471469531],
            ['Product Flat Index', 1471469532, 1471469577]
        ]);
    }

    function it_breaks_wrong_sequence_of_index_calls()
    {
        $this->parse(
            [
                'Category Products index was rebuilt successfully in 00:00:07',
                'Product Prices index was rebuilt successfully in 00:01:28',
                'Stock Status index was rebuilt successfully in 00:00:08',
                'Product Attributes index was rebuilt successfully in 00:02:54',
                'Product Flat Data index was rebuilt successfully in 00:00:45'
            ],
            1471469251,
            1471469577
        )->shouldReturn([]);
    }


    function it_parses_incomplete_sequence_of_index_calls()
    {
        $this->parse(
            [
                'Category Products index was rebuilt successfully in 00:00:07',
                'Stock Status index was rebuilt successfully in 00:00:08',
                'Product Prices index was rebuilt successfully in 00:01:28',
                'Product Attributes index was rebuilt successfully in 00:02:54'
            ],
            1471469251,
            1471469577
        )->shouldReturn([
            ['Category Product Index', 1471469251, 1471469258],
            ['Stock Index', 1471469259, 1471469267],
            ['Product Prices Index', 1471469268, 1471469356],
            ['Layered Navigation Index', 1471469357, 1471469531],
            ['Product Flat Index', 1471469532, 1471469577]
        ]);
    }

}
