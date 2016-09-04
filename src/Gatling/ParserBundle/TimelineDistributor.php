<?php

namespace Gatling\ParserBundle;

class TimelineDistributor
{
    /**
     * Timeline starting point
     *
     * @var int
     */
    private $startTime;

    /**
     * Timeline milliseconds offset
     *
     * @var int
     */
    private $startTimeMilliseconds;

    /**
     * Timeline step
     *
     * @var int
     */
    private $step;

    public function __construct($startTime, $step)
    {
        $this->startTime = $startTime;
        $this->startTimeMilliseconds = ($startTime - floor($startTime / 1000)*1000)/1000;

        $this->step = $step * 1000;
    }

    public function distribute($time)
    {
        $offset = $time - $this->startTime;

        return (int)($this->startTime + (floor($offset / $this->step) * $this->step));
    }

    /**
     * @param int $time
     *
     * @return int
     */
    public function offset($time)
    {
        return (int)floor(($time - $this->startTime)/1000);
    }

    /**
     * Returns an offset by seconds
     *
     * @param int $time
     *
     * @return int
     */
    public function offsetBySeconds($time)
    {
        return (int)$this->offset(($time + $this->startTimeMilliseconds)*1000);
    }
    
}
