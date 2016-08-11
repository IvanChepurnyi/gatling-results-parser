<?php

namespace Gatling\ParserBundle;

class StyleUpdater
{
    /**
     * Reports finder
     *
     * @var ReportFinder
     */
    private $finder;

    /**
     * Styles path
     *
     * @var string
     */
    private $stylePath;

    /**
     * Results path
     *
     * @var string
     */
    private $resultPath;

    /**
     * Style updater
     *
     * @param ReportFinder $finder
     * @param $stylePath
     * @param $resultPath
     */
    public function __construct(ReportFinder $finder, $stylePath, $resultPath)
    {
        $this->finder = $finder;
        $this->stylePath = $stylePath;
        $this->resultPath = $resultPath;
    }

    private function getStylePathFiles()
    {
        $results = [];
        foreach (new \DirectoryIterator($this->stylePath) as $item) {
            $results += $this->resolveFiles($item);
        }

        return $results;
    }

    public function removeSharedFiles()
    {
        $replacements = $this->getStylePathFiles();

        foreach ($this->finder->find() as $report) {
            $path = $this->finder->reportPath($report);
            foreach (new \DirectoryIterator($path) as $file) {
                if ($file->isFile() && $file->getExtension() === 'html') {
                    $this->replaceFileContent($file->getRealPath(), $replacements);
                }
            }

            foreach ($replacements as $source => $target) {
                if (file_exists($path . '/' . $source)) {
                    unlink($path . '/' . $source);
                }
            }
        }
        return $this;
    }

    private function replaceFileContent($file, $replacementList)
    {
        $content = file_get_contents($file);
        $content = str_replace(array_keys($replacementList), array_values($replacementList), $content);
        file_put_contents($file, $content);
        return $this;
    }

    private function extractPath($itemPath)
    {
        $originalPath = substr($itemPath, strlen($this->stylePath));
        $commonPath = substr($itemPath, 0, strlen($this->resultPath));
        $targetPath = substr($itemPath, strlen($commonPath));

        $countOriginal = count(explode('/', $originalPath));
        $countTarget = count(explode('/', $targetPath));

        return [$originalPath => str_repeat('../', $countTarget - $countOriginal) . $targetPath];
    }

    private function resolveFiles(\DirectoryIterator $item)
    {
        if ($item->isDot()) {
            return [];
        }

        if (!$item->isDir()) {
            return $this->extractPath($item->getRealPath());
        }

        $results = [];

        foreach (new \DirectoryIterator($item->getRealPath()) as $dirItem) {
            $results += $this->resolveFiles($dirItem);
        }

        return $results;
    }
}
