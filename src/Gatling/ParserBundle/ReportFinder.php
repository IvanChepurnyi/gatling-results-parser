<?php

namespace Gatling\ParserBundle;

class ReportFinder
{
    /**
     * Path to report directories
     *
     * @var
     */
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return ReportReader[]
     */
    public function find()
    {
        $readers = [];
        $directoryIterator = new \DirectoryIterator($this->path);
        foreach ($directoryIterator as $item) {
            if (!$item->isDir()) {
                continue;
            }

            try {
                $readers[] = new ReportReader($item->getRealPath());
            } catch (InvalidReportDirectoryException $e) {
                continue;
            }
        }

        return $readers;
    }

    public function reportPath(ReportReader $reportReader)
    {
        return $this->path . '/' . $reportReader->getReportCode();
    }
}
