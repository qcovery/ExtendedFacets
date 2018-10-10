<?php
/**
 * SideFacets Recommendations Module: Parser for YearFacets
 *
 * PHP version 7
 *
 * Copyright (C) Staats- und Universitätsbibliothek Hamburg 2018.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License version 2,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Hajo Seng <hajo.seng@sub.uni-hamburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://github.com/beluga-core
 */
namespace ExtendedFacets\Recommend;

/**
 * SideFacets Recommendations Module: Parser for YearFacets
 *
 * This class provides a special parser for publishYear facet
 *
 * @category VuFind
 * @package  RecordDrivers
 * @author   Hajo Seng <hajo.seng@sub.uni-hamburg.de>
 * @license  http://opensource.org/licenses/gpl-2.0.php GNU General Public License
 * @link     https://vufind.org/wiki/development:plugins:record_drivers Wiki
 */
class SideFacets extends \VuFind\Recommend\SideFacets
{
    /**
     * getYearFacets
     *
     * Return year facet information in a format processed for use in the view.
     *
     * @param array $oldFacetList list of facets
     * @param string $label filterlabel.
     *
     * @return array list of facets
     */
    public function getYearFacets($oldFacetList, $label)
    {
        array_multisort($oldFacetList, SORT_DESC);
        $minYear = $oldFacetList[count($oldFacetList)-1]['value'];
        $maxYear = $oldFacetList[0]['value'];
        $facetListAssoc = array();
        foreach ($oldFacetList as $oldFacetListItem) {
            $facetListAssoc[$oldFacetListItem['value']] = $oldFacetListItem['count'];
        }
        $newFacetList = array();

        $filters = $this->results->getParams()->getFilterList();
        if (isset($filters[$label])) {
            $lastYearFilter = array_pop($filters[$label]);
            list($filteredMinYear,$filteredMaxYear) = explode(' TO ',str_replace(array('[', ']'), '', $lastYearFilter['value']));
            $displayText = ($filteredMaxYear <= date('Y')) ? $filteredMinYear.'-'.$filteredMaxYear : $filteredMinYear.'-';
            $filteredYearFacet = array('value' => '['.$filteredMinYear.' TO '.$filteredMaxYear.']', 'displayText' => $displayText, 'count' => 1, 'operator' => 'AND', 'isApplied' => true);
            if ($minYear < $filteredMinYear) {
                $minYear = $filteredMinYear;
            }
            if ($maxYear > $filteredMaxYear) {
                $maxYear = $filteredMaxYear;
            }
        }

        foreach (array(100, 10, 1) as $scale) {
            if (floor($minYear/$scale) != floor($maxYear/$scale)) {
                for ($year = $scale*floor($minYear/$scale); $year <= $scale*floor($maxYear/$scale); $year += $scale) {
                    $newCount = 0;
                    for ($y=$year; $y < $year + $scale; $y++) {
                        if (isset($facetListAssoc[$y])) {
                            $newCount += $facetListAssoc[$y];
                        }
                    }
                    if ($newCount > 0) {
                        if ($scale == 1) {
                            $displayText = $year;
                        } else {
                            $displayText = ($year + $scale - 1 <= date('Y')) ? $year.'-'.($year + $scale - 1) : $year.'-';
                        }
                        $newFacetList[] = array('value' => '['.$year.' TO '.($year + $scale - 1).']', 'displayText' => $displayText, 'count' => $newCount, 'operator' => 'AND', 'isApplied' => false);
                    }
                }
                krsort($newFacetList);
                if (isset($filteredYearFacet)) {
                    array_unshift($newFacetList, $filteredYearFacet);
                }
                return $newFacetList;
            }
        }
        if (isset($filteredYearFacet)) {
            array_unshift($newFacetList, $filteredYearFacet);
        }
        return $newFacetList;
    }

    /**
     * Get facet information from the search results.
     *
     * @return array
     */
    public function getFacetSet()
    {
        $facetSet = \VuFind\Recommend\SideFacets::getFacetSet();
        if (isset($facetSet['publishDate'])) {
            $facetSet['publishDate']['list'] = $this->getYearFacets($facetSet['publishDate']['list'], $facetSet['publishDate']['label']);
        }
        return $facetSet;
    }
}
