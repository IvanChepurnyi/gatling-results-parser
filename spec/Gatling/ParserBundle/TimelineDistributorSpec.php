<?php

namespace spec\Gatling\ParserBundle;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TimelineDistributorSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(1471662930127, 5);
    }

    function it_distributes_start_value_into_a_start_group()
    {
        $this->distribute(1471662930127)->shouldReturn(1471662930127);
    }
    
    function it_distributes_value_till_the_next_group_limit()
    {
        $this->distribute(1471662930127)->shouldReturn(1471662930127);
        $this->distribute(1471662932127)->shouldReturn(1471662930127);
        $this->distribute(1471662935127)->shouldReturn(1471662935127);
        $this->distribute(1471662940127)->shouldReturn(1471662940127);
    }

    function it_should_return_number_of_milliseconds_since_test_start()
    {
        $this->offset(1471662930127)->shouldReturn(0);
        $this->offset(1471662932127)->shouldReturn(2);
        $this->offset(1471662935127)->shouldReturn(5);
    }

    function it_should_return_number_of_milliseconds_from_seconds_since_test_start()
    {
        $this->offsetBySeconds(1471662930)->shouldReturn(0);
        $this->offsetBySeconds(1471662932)->shouldReturn(2);
        $this->offsetBySeconds(1471662935)->shouldReturn(5);
    }


}
