<?php

namespace Gatling\ParserBundle\Command;

use Gatling\ParserBundle\PressureConverter\Group;
use Gatling\ParserBundle\PressureConverter\MagentoIndex;
use Gatling\ParserBundle\PressureFinder;
use Gatling\ParserBundle\ReportFinder;
use Gatling\ParserBundle\SimulationParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixSimulationLogCommand extends Command
{
    protected function configure()
    {
        $this->setName('report:fix:simulation');
        $this->setDescription('Fixes simulation logs, if they do not follow right format');

        $this->addArgument('resultPath', InputArgument::REQUIRED, 'Path to gatling results folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reportFinder = new ReportFinder($input->getArgument('resultPath'));


        foreach ($reportFinder->find() as $report) {
            $simulation = new \SplFileObject($report->getSimulationPath());
            $simulation->setFlags(\SplFileObject::READ_CSV);
            $simulation->setCsvControl("\t");

            $simulation->rewind();
            if ($simulation->current()[0] === 'RUN') {
                continue;
            }

            $newSimulation = new \SplFileObject($report->getSimulationPath() . '.new', 'w');

            $typeRemap = [
                'RUN' => [
                    2 => 0,
                    3 => 4
                ],
                'USER' => [
                    2 => 0
                ],
                'REQUEST' => [
                    2 => 0,
                    8 => 6,
                    9 => 7
                ]
            ];
            
            foreach ($simulation as $line) {
                $row = $line;
                if (isset($line[2]) && isset($typeRemap[$line[2]])) {
                    foreach ($typeRemap[$line[2]] as $from => $to) {
                        $row[$from] = '';
                        $row[$to] = $line[$from];
                    }
                }

                ksort($row);
                $newSimulation->fwrite(implode("\t", $row) . "\n");
            }
        }

        return 0;
    }
    
}
