<?php

namespace Gatling\ParserBundle\PressureConverter;

class MagentoIndex
{
    /**
     * Index code
     *
     * @var string
     */
    private $code;

    /**
     * Index sequence and map
     *
     * @var string[]
     */
    private $indexSequence;

    /**
     * Regular expression
     *
     * @var string
     */
    private $regExp;

    /**
     * @param string $code
     * @param string[] $indexSequence
     * @param string $regExp
     */
    public function __construct($code, $indexSequence, $regExp)
    {
        $this->code = $code;
        $this->indexSequence = $indexSequence;
        $this->regExp = $regExp;
    }

    public function getCode()
    {
        return $this->code;
    }

    /**
     * Returns parsed index sequences
     *
     * @param string[] $indexLines
     * @param int $startTime
     * @param int $endTime
     * @return string[][]
     */
    public function parse($indexLines, $startTime, $endTime)
    {
        $sequence = array_keys($this->indexSequence);

        $result = [];

        foreach ($indexLines as $line) {
            if (!preg_match($this->regExp, $line, $matches)) {
                continue;
            }
            
            $duration = \DateTime::createFromFormat('Y-m-d H:i:s', '1970-01-01 ' . $matches[2]);
            $time = $duration->getTimestamp();

            $expectedSequence = array_shift($sequence);

            if ($expectedSequence !== $matches[1]) {
                return [];
            }

            $result[] = [$this->indexSequence[$matches[1]], $startTime, $startTime + $time];
            $startTime += $time + 1;
        }

        if ($sequence) {
            $result[] = [$this->indexSequence[array_shift($sequence)], $startTime, $endTime];
        }

        return $result;
    }
}
