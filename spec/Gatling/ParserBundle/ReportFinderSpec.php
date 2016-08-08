<?php

namespace spec\Gatling\ParserBundle;

use Gatling\ParserBundle\ReportReader;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReportFinderSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(__DIR__  . '/fixture');
    }

    function it_returns_list_of_valid_reports_in_directory()
    {
        $this->find()->shouldBeLike([new ReportReader(__DIR__  . '/fixture/sample-report')]);
    }
}
