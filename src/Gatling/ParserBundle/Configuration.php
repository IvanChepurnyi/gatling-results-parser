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
            foreach ($configuration['filters'] as $filter => $info) {

                if (!isset($info['multiple'])) {
                    $info['multiple'] = false;
                }

                if (!isset($info['selection'])) {
                    if ($info['multiple']) {
                        $info['selection'] = array_keys($info['options']);
                    } else {
                        $info['selection'] = key($info['options']);
                    }
                }

                $this->filters[$filter] = [
                    'label' => $info['label'],
                    'multiple' => $info['multiple'],
                    'selection' => $info['selection']
                ];


                foreach ($info['options'] as $value => $match) {
                    $this->filters[$filter]['options'][] = $value;
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
        $placeholderMap = [
            '*' => uniqid(),
            '[d]' => uniqid(),
            '[a]' => uniqid()
        ];

        $expressionMap = [
            $placeholderMap['*'] => '.*',
            $placeholderMap['[d]'] => '[0-9]+',
            $placeholderMap['[a]'] => '[a-zA-Z]+g',
        ];

        $match = str_replace(
            array_keys($expressionMap),
            array_values($expressionMap),
            preg_quote(
                str_replace(
                    array_keys($placeholderMap),
                    array_values($placeholderMap),
                    $match
                ),
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
        $matchedLegend = null;
        foreach ($this->legendRegexp as $regExp => $legend) {
            if (preg_match($regExp, $code)) {
                 $matchedLegend = $legend;
            }
        }

        if ($matchedLegend) {
            return $matchedLegend;
        }

        throw new InvalidLegendException();
    }

    public function findFilterValue($filter, $code)
    {
        if (!isset($this->filterMatches[$filter])) {
            throw new InvalidFilterException();
        }

        $matchedFilter = null;
        foreach ($this->filterMatches[$filter] as $regExp => $value) {
            if (preg_match($regExp, $code)) {
                $matchedFilter = $value;
            }
        }

        if ($matchedFilter !== null) {
            return $matchedFilter;
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
