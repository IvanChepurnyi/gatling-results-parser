<?php

namespace spec\Gatling\ParserBundle\PressureConverter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GroupSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([
            'load-test-magento1oro-bootstrap' => 'magento1',
            'load-test-magento1-bootstrap' => 'magento1',
            'load-test-magento2-bootstrap' => 'magento2'
        ]);
    }

    function it_is_not_ready_by_default()
    {
        $this->isReady()->shouldReturn(false);
    }

    function it_is_not_ready_at_the_beggining_of_sequence()
    {
        $this->parseLine('BEGIN:1471469251:load-test-magento1oro-bootstrap')->shouldReturn($this);
        $this->parseLine('Some line text')->shouldReturn($this);
        $this->parseLine('Another line text')->shouldReturn($this);
        $this->isReady()->shouldReturn(false);
    }

    function it_is_ready_only_on_final_stage()
    {
        $this->parseLine('BEGIN:1471469251:load-test-magento1oro-bootstrap')->shouldReturn($this);
        $this->parseLine('Some line text')->shouldReturn($this);
        $this->parseLine('Another line text')->shouldReturn($this);
        $this->parseLine('Line in between')->shouldReturn($this);
        $this->parseLine('END:1471469251:load-test-magento1oro-bootstrap:1471470009')->shouldReturn($this);
        $this->isReady()->shouldReturn(true);
    }

    function it_returns_returns_proper_values_on_final_stage()
    {
        $this->parseLine('BEGIN:1471469251:load-test-magento1oro-bootstrap')->shouldReturn($this);
        $this->parseLine('Some line text')->shouldReturn($this);
        $this->parseLine('Another line text')->shouldReturn($this);
        $this->parseLine('Line in between')->shouldReturn($this);
        $this->parseLine('END:1471469251:load-test-magento1oro-bootstrap:1471470009')->shouldReturn($this);
        $this->parseLine('Line we shouldn not see')->shouldReturn($this);
        $this->sequence()->shouldReturn([
            'code' => 'magento1',
            'start' => 1471469251,
            'lines' => [
                'Some line text',
                'Another line text',
                'Line in between'
            ],
            'end' => 1471470009
        ]);
    }

    function it_throws_error_if_sequance_is_not_ready_yet()
    {
        $this->shouldThrow(new \RuntimeException('Sequence is not ready'))->duringSequence();
    }




    function it_is_not_ready_on_wrong_sequence_stage()
    {
        $this->parseLine('Some line text')->shouldReturn($this);
        $this->parseLine('Another line text')->shouldReturn($this);
        $this->parseLine('Line in between')->shouldReturn($this);
        $this->parseLine('END:1471469251:load-test-magento1oro-bootstrap:1471470009')->shouldReturn($this);
        $this->isReady()->shouldReturn(false);
    }

    function it_is_not_ready_on_incomplete_sequence_stage()
    {
        $this->parseLine('BEGIN:1471469251:load-test-magento1oro-bootstrap')->shouldReturn($this);
        $this->parseLine('Some line text')->shouldReturn($this);
        $this->parseLine('Another line text')->shouldReturn($this);
        $this->parseLine('Line in between')->shouldReturn($this);
        $this->parseLine('END:1471469251:load-test-magento1oro-bootstrap')->shouldReturn($this);
        $this->isReady()->shouldReturn(false);
    }

}
