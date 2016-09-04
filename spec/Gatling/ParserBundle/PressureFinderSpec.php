<?php

namespace spec\Gatling\ParserBundle;

use Gatling\ParserBundle\PressureConverter\Group;
use Gatling\ParserBundle\PressureConverter\MagentoIndex;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class PressureFinderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            __DIR__  . '/fixture/pressure.log',
            new Group([
                'load-test-magento1oro-bootstrap' => 'magento1',
                'load-test-magento1-bootstrap' => 'magento1',
                'load-test-magento2-bootstrap' => 'magento2'
            ]),
            [
                new MagentoIndex(
                    'magento1',
                    [
                        'Category Products' => 'Category Products Index',
                        'Stock Status' => 'Stock Index',
                        'Product Prices' => 'Product Prices Index',
                        'Product Attributes' => 'Layered Navigation Index',
                        'Product Flat Data' => 'Product Flat Index'
                    ],
                    '/^(.*?)\s+index was rebuilt successfully in\s+(.*?)$/'
                ),
                new MagentoIndex(
                    'magento2',
                    [
                        'Category Products' => 'Category Products Index',
                        'Product Categories' => 'Product Categories Index',
                        'Stock' => 'Stock Index',
                        'Product Price' => 'Product Prices Index',
                        'Product EAV' => 'Layered Navigation Index',
                        'Product Flat Data' => 'Product Flat Index'
                    ],
                    '/^(.*?)\s+index has been rebuilt successfully in\s+(.*?)$/'
                )
            ]
        );
    }

    function it_can_find_one_record_by_timestamp()
    {
        $this->find(1471473386, 1471473716)->shouldReturn([
            ['Category Products Index', 1471473694, 1471473696],
            ['Product Categories Index', 1471473697, 1471473699],
            ['Stock Index', 1471473700, 1471473707],
            ['Product Prices Index', 1471473708, 1471473718]
        ]);
    }

    function it_can_find_multi_record_set_by_timestamp()
    {
        $this->find(1471473386, 1471474165)->shouldReturn([
            ['Category Products Index', 1471473694, 1471473696],
            ['Product Categories Index', 1471473697, 1471473699],
            ['Stock Index', 1471473700, 1471473707],
            ['Product Prices Index', 1471473708, 1471473718],
            ['Category Products Index', 1471473828, 1471473839],
            ['Stock Index', 1471473840, 1471473850],
            ['Product Prices Index', 1471473851, 1471473948],
            ['Layered Navigation Index', 1471473949, 1471474162],
            ['Product Flat Index', 1471474163, 1471474165]
        ]);
    }
}
