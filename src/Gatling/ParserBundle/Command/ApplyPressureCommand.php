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

class ApplyPressureCommand extends Command
{
    protected function configure()
    {
        $this->setName('report:apply:pressure');
        $this->setDescription('Applies system pressure information into reports directory');

        $this->addOption(
            'magento1',
            '',
            InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED,
            'Codename in pressure file for Magetno 1.x',
            ['load-test-magento1-bootstrap', 'load-test-magento1oro-bootstrap']
        );

        $this->addOption(
            'magento2',
            '',
            InputOption::VALUE_IS_ARRAY|InputOption::VALUE_REQUIRED,
            'Codename in pressure file for Magento 1.x',
            ['load-test-magento2-bootstrap']
        );

        $this->addArgument('pressureFile', InputArgument::REQUIRED, 'Path to sys usage directory');
        $this->addArgument('resultPath', InputArgument::REQUIRED, 'Path to gatling results folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $magentoOneOptions = $input->getOption('magento1');
        $magentoTwoOptions = $input->getOption('magento2');

        $group = new Group(
            array_combine($magentoOneOptions, array_fill(0, count($magentoOneOptions), 'magento1'))
            + array_combine($magentoTwoOptions, array_fill(0, count($magentoTwoOptions), 'magento2'))
        );

        $magentoIndexes = [
            new MagentoIndex(
                'magento1',
                [
                    'Category Products' => 'Category Products Index',
                    'Stock Status' => 'Stock Index',
                    'Product Prices' => 'Product Prices Index',
                    'Product Attributes' => 'Layered Navigation Index',
                    'Product Flat Data' => 'Product Flat Index'
                ],
                '/^(.*?)\s+index was rebuilt successfully in\s+(.*?)$/'
            ),
            new MagentoIndex(
                'magento2',
                [
                    'Category Products' => 'Category Products Index',
                    'Product Categories' => 'Product Categories Index',
                    'Stock' => 'Stock Index',
                    'Product Price' => 'Product Prices Index',
                    'Product EAV' => 'Layered Navigation Index',
                    'Product Flat Data' => 'Product Flat Index'
                ],
                '/^(.*?)\s+index has been rebuilt successfully in\s+(.*?)$/'
            )
        ];

        $reportFinder = new ReportFinder($input->getArgument('resultPath'));
        $pressureFinder = new PressureFinder($input->getArgument('pressureFile'), $group, $magentoIndexes);

        foreach ($reportFinder->find() as $report) {
            $simulation = new SimulationParser($report->getSimulationPath());
            $records = $pressureFinder->find(
                $simulation->getSimulationStartTimeSeconds(),
                $simulation->getSimulationEndTimeSeconds()
            );

            if ($records) {
                $output->writeln(
                    sprintf('<info>Creating pressure.csv file in report directory:%s</info>', $report->getReportCode()),
                    OutputInterface::VERBOSITY_NORMAL
                );

                if (file_exists($report->getPressurePath())) {
                    unlink($report->getPressurePath());
                }

                $file = new \SplFileObject($report->getPressurePath(), 'w');
                foreach ($records as $record) {
                    $file->fputcsv($record);
                }
                unset($file);
            } else {
                $output->writeln(
                    sprintf('No pressure.csv file in report directory: %s', $report->getReportCode()),
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }

        return 0;
    }
    
}
