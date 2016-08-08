<?php

namespace Gatling\ParserBundle\Command;

use Gatling\ParserBundle\Configuration;
use Gatling\ParserBundle\ReportFinder;
use Gatling\ParserBundle\ReportGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReportGenerateCommand extends Command
{
    protected function configure()
    {
        $this->setName('report:generate');
        $this->setDescription('Generates report from directory of all gatling files');

        $this->addArgument('path', InputArgument::REQUIRED, 'Path to gatling results folder');
        $this->addArgument('config', InputArgument::REQUIRED, 'Path to config.json with report requirements');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reportFinder = new ReportFinder($input->getArgument('path'));
        $configArray = json_decode(file_get_contents($input->getArgument('config')), true);
        $configuration = new Configuration($configArray);
        $reportGenerator = new ReportGenerator($configuration, $reportFinder);
        
        $output->write(json_encode($reportGenerator->generate(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
