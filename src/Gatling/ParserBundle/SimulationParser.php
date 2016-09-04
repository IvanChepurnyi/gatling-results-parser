<?php

namespace Gatling\ParserBundle;

class SimulationParser
{
    /**
     * File with simulation
     *
     * @var \SplFileObject
     */
    private $file;

    /**
     * Start time of simulation
     *
     * @var int
     */
    private $simulationStartTime;

    /**
     * End time of simulation
     *
     * @var int
     */
    private $simulationEndTime;

    public function __construct($path)
    {
        $this->file = new \SplFileObject($path, 'r+');
        $this->file->setFlags(\SplFileObject::READ_CSV);
        $this->file->setCsvControl("\t");
        
        $this->simulationStartTime = $this->readSimulationStartTime();
    }

    public function getSimulationStartTime()
    {
        return $this->simulationStartTime;
    }
    
    private function readSimulationStartTime()
    {
        foreach ($this->file as $line) {
            if (!$line) {
                continue;
            }
            
            if ($line[0] === 'RUN') {
                return (int)$line[4];
            }
        }
    }

    public function aggregate(TimelineDistributor $distributor, $allowedPages)
    {
        // Make pages possible to check by index
        $allowedPages = array_flip($allowedPages);

        $userCount = 0;
        $result = [
            'user' => [],
            'request' => [],
            'response' => [],
            'mean' => [],
            'max' => []
        ];

        $responseTimes = [];

        foreach ($this->file as $line) {
            if (!$line) {
                continue;
            }

            if ($line[0] === 'USER') {
                if ($line[3] === 'START') {
                    $userCount ++;
                    $result['user'][$distributor->offset($distributor->distribute($line[4]))] = $userCount;
                } elseif ($line[3] === 'END') {
                    $userCount --;
                    $result['user'][$distributor->offset($distributor->distribute($line[5]))] = $userCount;
                }
            }

            if ($line[0] === 'REQUEST') {
                if (!isset($allowedPages[$line[4]])) {
                    continue;
                }

                $startTime = $distributor->offset($distributor->distribute($line[5]));
                $endTime = $distributor->offset($distributor->distribute($line[6]));

                $responseTime = $line[6] - $line[5];

                if (!isset($result['request'][strtolower($line[7])][$startTime])) {
                    $result['request'][strtolower($line[7])][$startTime] = 0;
                }

                if (!isset($result['response'][strtolower($line[7])][$endTime])) {
                    $result['response'][strtolower($line[7])][$endTime] = 0;
                }

                $result['request'][strtolower($line[7])][$startTime]++;
                $result['response'][strtolower($line[7])][$endTime]++;

                $responseTimes[strtolower($line[7])][$endTime][] = $responseTime;
            }
        }

        foreach ($responseTimes as $type => $times) {
            foreach ($times as $timeFrame => $values) {
                $result['mean'][$type][$timeFrame] = floor(array_sum($values) / count($values));
                $result['max'][$type][$timeFrame] = max($values);
            }
        }

        return $result;
    }

    public function getSimulationStartTimeSeconds()
    {
        return (int)floor($this->getSimulationStartTime()/1000);
    }

    public function getSimulationEndTimeSeconds()
    {
        if ($this->simulationEndTime === null) {
            $this->simulationEndTime = $this->readSimulationEndTime();
        }

        return (int)floor($this->simulationEndTime / 1000);
    }

    private function readSimulationEndTime()
    {
        $endTime = $this->simulationStartTime;

        foreach ($this->file as $line) {
            if (!$line) {
                continue;
            }

            if ($line[0] === 'USER' && $line[3] === 'END') {
                $endTime = (int)$line[5];
            }
        }

        return $endTime;
    }
}
