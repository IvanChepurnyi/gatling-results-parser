<?php

namespace Gatling\ParserBundle;

class Configuration implements ConfigurationInterface
{
    private $legends = [];

    private $filters = [];

    private $legendRegexp = [];

    private $filterMatches = [];

    private $filterDefault = [];

    private $config;

    public function __construct($configuration = [])
    {
        if (isset($configuration['legend-match'])) {
            foreach ($configuration['legend-match'] as $legend => $match) {
                $this->legends[$legend] = isset($configuration['legend-color'][$legend])
                                            ? $configuration['legend-color'][$legend]
                                            : "";

                $this->legendRegexp[$this->prepareRegexp($match)] = $legend;
            }
        }

        if (isset($configuration['filters'])) {
            foreach ($configuration['filters'] as $filter => $matches) {
                $this->filters[$filter] = [];

                foreach ($matches as $value => $match) {
                    $this->filters[$filter][] = $value;
                    if (!$match) {
                        $this->filterDefault[$filter] = $value;
                        continue;
                    }

                    $this->filterMatches[$filter][$this->prepareRegexp($match)] = $value;
                }
            }
        }

        $this->config = $configuration;
    }

    private function prepareRegexp($match)
    {
        $placeholder = uniqid();
        $match = str_replace(
            $placeholder,
            '.*',
            preg_quote(
                str_replace('*', $placeholder, $match),
                '/'
            )
        );

        return '/^' . $match . '/i';
    }

    public function getLegends()
    {
        return $this->legends;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function findLegend($code)
    {
        foreach ($this->legendRegexp as $regExp => $legend) {
            if (preg_match($regExp, $code)) {
                return $legend;
            }
        }

        throw new InvalidLegendException();
    }

    public function findFilterValue($filter, $code)
    {
        if (!isset($this->filterMatches[$filter])) {
            throw new InvalidFilterException();
        }

        foreach ($this->filterMatches[$filter] as $regExp => $value) {
            if (preg_match($regExp, $code)) {
                return $value;
            }
        }

        if (isset($this->filterDefault[$filter])) {
            return $this->filterDefault[$filter];
        }

        throw new InvalidFilterException();
    }

    public function getPages()
    {
        return isset($this->config['pages']) ? $this->config['pages'] : [];
    }

    public function mapPageCode($pageCode)
    {
        if (isset($this->config['page_map'][$pageCode])) {
            return $this->config['page_map'][$pageCode];
        }

        return $pageCode;
    }
}
