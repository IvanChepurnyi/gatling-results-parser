<?php

namespace Gatling\ParserBundle;

class PressureReader
{
    private $file;
    
    public function __construct($path)
    {
        $this->file = new \SplFileObject($path, 'r');
        $this->file->setFlags(\SplFileObject::READ_CSV);
        $this->file->setCsvControl();
    }

    public function aggregate(TimelineDistributor $timelineDistributor)
    {
        $result = [];
        
        foreach ($this->file as $line) {
            if (count($line) < 3) {
                continue;
            }
            $result[$line[0]][$timelineDistributor->offsetBySeconds($line[1]) - 1] = 0;
            $result[$line[0]][$timelineDistributor->offsetBySeconds($line[1])] = 1;
            $result[$line[0]][$timelineDistributor->offsetBySeconds($line[2])] = 1;
            $result[$line[0]][$timelineDistributor->offsetBySeconds($line[2]) + 1] = 0;
        }

        return $result;
    }
}
