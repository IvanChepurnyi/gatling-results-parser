<?php

namespace Gatling\ParserBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ApplySysUsageCommand extends Command
{
    protected function configure()
    {
        $this->setName('report:apply:sys-usage');
        $this->setDescription('Applies system usage information into reports directory');

        $this->addArgument('usagePath', InputArgument::REQUIRED, 'Path to sys usage directory');
        $this->addArgument('resultPath', InputArgument::REQUIRED, 'Path to gatling results folder');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $usageIterator = new \GlobIterator(
            $input->getArgument('usagePath') . '/*.csv',
            \GlobIterator::CURRENT_AS_PATHNAME
        );
        
        $reportPath = $input->getArgument('resultPath');

        $exitCode = 0;


        foreach ($usageIterator as $usageFile) {
            $reportName = basename($usageFile, '.csv');
            if (is_dir($reportPath . '/' . $reportName)) {
                
                if (rename($usageFile, $reportPath . '/' . $reportName . '/usage.csv')) {
                    $output->writeln(
                        sprintf('<info>Moved %s to report directory</info>', $usageFile),
                        OutputInterface::VERBOSITY_NORMAL
                    );
                } else {
                    $output->writeln(
                        sprintf('<error>Failed to move report %s</error>', $usageFile),
                        OutputInterface::VERBOSITY_NORMAL
                    );
                    $exitCode = 1;
                }
            } else {
                $output->writeln(
                    sprintf('<error>Target directory does not exists %s</error>', $reportName . '/' . $reportName),
                    OutputInterface::VERBOSITY_VERBOSE
                );
            }
        }

        return $exitCode;
    }
}
