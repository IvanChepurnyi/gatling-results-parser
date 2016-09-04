<?php

namespace Gatling\ParserBundle;

class SysUsageReader
{
    private $file;

    private $data = [];

    public function __construct($path)
    {
        $this->file = new \SplFileObject($path, 'r');
        $this->file->setFlags(\SplFileObject::READ_CSV);
        $this->file->setCsvControl();
        foreach ($this->file as $row) {
            if (count($row) < 2) {
                continue;
            }

            $this->data[$row[0]] = json_decode($row[1], true);
        }
    }

    public function staticValue($statName, TimelineDistributor $distributor)
    {
        $result = [];
        foreach ($this->data as $time => $stats) {
            $result[$distributor->distribute($time*1000)][] = (float)$stats[$statName];
        }

        return $this->normalizeStats($result, $distributor);
    }

    public function dynamicValue($statName, TimelineDistributor $distributor)
    {
        $result = [];
        $previousValue = 0;
        foreach ($this->data as $time => $stats) {
            $value = (float)$stats[$statName];
            $result[$distributor->distribute($time*1000)][] = $value - $previousValue;
            $previousValue = $value;
        }

        return $this->normalizeStats($result, $distributor);
    }

    private function normalizeStats($stats, TimelineDistributor $distributor)
    {
        $result = [];
        foreach ($stats as $time => $values) {
            $result[$distributor->offset($time)] = max($values);
        }

        return $result;
    }
}
