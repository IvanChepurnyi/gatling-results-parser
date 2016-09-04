<?php

namespace Gatling\ParserBundle\PressureConverter;

class Group
{
    /**
     * Type map for group
     *
     * @var string[]
     */
    private $typeMap;

    /**
     * Is sequence started
     *
     * @var boolean
     */
    private $isStarted = false;

    /**
     * Sequence lines
     *
     * @var \stdClass
     */
    private $sequence;


    public function __construct($typeMap)
    {
        $this->typeMap = $typeMap;
    }

    public function isReady()
    {
        return $this->sequence && isset($this->sequence->ready);
    }

    public function parseLine($line)
    {
        $commandLine = false;

        if (strpos($line, 'BEGIN:') === 0
            || strpos($line, 'END:') === 0 ) {
            $commandLine = explode(':', $line);
        }

        if (!$commandLine && $this->isStarted) {
            $this->sequence->lines[] = $line;
            return $this;
        }

        if ($commandLine[0] === 'BEGIN' && isset($this->typeMap[$commandLine[2]])) {
            $this->isStarted = true;
            $this->sequence = (object)[
                'code' => $this->typeMap[$commandLine[2]],
                'start' => (int)$commandLine[1],
                'lines' => []
            ];
        } elseif ($this->isStarted && $commandLine[0] === 'END' && count($commandLine) === 4) {
            $this->isStarted = false;
            $this->sequence->end = (int)$commandLine[3];
            $this->sequence->ready = true;
        }
        return $this;
    }

    public function sequence()
    {
        if (!$this->isReady()) {
            throw new \RuntimeException('Sequence is not ready');
        }

        $data = [
            'code' => $this->sequence->code,
            'start' => $this->sequence->start,
            'lines' => $this->sequence->lines,
            'end' => $this->sequence->end
        ];

        $this->sequence = null;
        return $data;
    }
}
