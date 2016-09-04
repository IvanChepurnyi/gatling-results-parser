<?php

namespace Gatling\ParserBundle;

use Gatling\ParserBundle\PressureConverter\Group;
use Gatling\ParserBundle\PressureConverter\MagentoIndex;

class PressureFinder
{
    /**
     * @var string
     */
    private $file;

    /**
     * @var Group
     */
    private $groupConverter;

    /**
     * @var MagentoIndex[]
     */
    private $magentoIndexConverter = [];

    /**
     * Records
     *
     * @var string[][]
     */
    private $records;

    /**
     * Index of record positions
     *
     * @var int[][]
     */
    private $recordIndex;

    /**
     * PressureFinder constructor.
     * @param string $file
     * @param Group $groupConverter
     * @param MagentoIndex[] $magentoIndexConverters
     */
    public function __construct($file, Group $groupConverter, $magentoIndexConverters)
    {
        $this->file = new \SplFileObject($file, 'r+');
        $this->groupConverter = $groupConverter;

        foreach ($magentoIndexConverters as $converter) {
            $this->magentoIndexConverter[$converter->getCode()] = $converter;
        }
    }

    private function load()
    {
        if ($this->recordIndex !== null && $this->records !== null) {
            return $this;
        }

        $this->recordIndex = [];
        $this->records = [];
        $recordIndex = 0;

        foreach ($this->file as $line) {
            $this->groupConverter->parseLine(trim($line));


            if ($this->groupConverter->isReady()) {
                $sequence = $this->groupConverter->sequence();
                $records = $this->magentoIndexConverter[$sequence['code']]->parse(
                    $sequence['lines'],
                    $sequence['start'],
                    $sequence['end']
                );

                $this->records[$recordIndex] = [$sequence['start'], $records];
                $this->recordIndex[$this->timestampIndex($sequence['start'])][$recordIndex] = $recordIndex;
                $this->recordIndex[$this->timestampIndex($sequence['start'] - 1000)][$recordIndex] = $recordIndex;
                $recordIndex++;
            }
        }

        return $this;
    }

    /**
     * Returns timestamp index
     *
     * @param int $time
     *
     * @return int
     */
    private function timestampIndex($time)
    {
        return (int)floor($time / 1000);
    }

    /**
     * Finds entries for indexes that were running at particular time-frame
     *
     * @param $start
     * @param $end
     * @return string[]
     */
    public function find($start, $end)
    {
        $start -= 40;
        $this->load();

        if (!isset($this->recordIndex[$this->timestampIndex($start)])) {
            return [];
        }

        $indexes = $this->recordIndex[$this->timestampIndex($start)];

        asort($indexes);

        $result = [];
        foreach ($indexes as $index) {
            list($startTime, $records) = $this->records[$index];
            if ($startTime > $start && $startTime < $end) {
                $result = array_merge($result, $records);
            }
        }

        return $result;
    }
}
