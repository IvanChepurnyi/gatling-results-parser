<?php

namespace Gatling\ParserBundle\Command;

use Gatling\ParserBundle\ReportFinder;
use Gatling\ParserBundle\StyleUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ShareStylesCommand extends Command
{
    protected function configure()
{
    $this->setName('share:styles');
    $this->setDescription('Shares styles between multiple reports');

    $this->addArgument('path', InputArgument::REQUIRED, 'Path to gatling results folder');
    $this->addArgument('stylePath', InputArgument::REQUIRED, 'Path to gatling results folder');

}

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reportFinder = new ReportFinder($input->getArgument('path'));
        $styleUpdater = new StyleUpdater($reportFinder, $input->getArgument('stylePath'), $input->getArgument('path'));

        $styleUpdater->removeSharedFiles();
        return 0;
    }
}
