<?php
/**
 * gatling-result-parser
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2016 EcomDev BV (http://www.ecomdev.org)
 * @license    https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Ivan Chepurnyi <ivan@ecomdev.org>
 */

namespace Gatling\ParserBundle;


interface ConfigurationInterface
{
    /**
     * Returns list of legends
     *
     * Key is legend name, value is a legend color
     *
     * @return string[]
     */
    public function getLegends();

    /**
     * Returns list of available filters with options
     *
     * @return mixed
     */
    public function getFilters();

    /**
     * Pages for report generation
     *
     * @return string[]
     */
    public function getPages();

    /**
     * Finds a match of directory name to legend
     *
     * @param string $code
     *
     * @return string
     */
    public function findLegend($code);

    /**
     * Map existing page code
     *
     * @param string $pageCode
     *
     * @return string
     */
    public function mapPageCode($pageCode);

    /**
     * Finds a match of directory name to a filter value
     *
     * @param string $filter
     * @param string $code
     * 
     * @throws InvalidFilterException when filter is unknown
     * @return string
     */
    public function findFilterValue($filter, $code);
}
