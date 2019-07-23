<?php

namespace DivineOmega\OmegaSearch;

use DivineOmega\OmegaSearch\SearchResult;

class SearchResults {

    public $results = [];
    public $highestRelevance = null;
    public $lowestRelevance = null;
    public $averageRelevance = null;
    public $time = null;

    public function addSearchResult(SearchResult $searchResult) {
        $this->results[] = $searchResult;
    }
    
    public function calculateRelevances() {

        if (!$this->results) {
            return;
        }

        $this->lowestRelevance = PHP_INT_MAX;
        $this->highestRelevance = 0;

        $relevances = [];

        foreach($this->results as $result) {
            if ($result->relevance < $this->lowestRelevance) {
                $this->lowestRelevance = $result->relevance;
            }
            if ($result->relevance > $this->highestRelevance) {
                $this->highestRelevance = $result->relevance;
            }
            $relevances[] = $result->relevance;
        }

        $this->averageRelevance = array_sum($relevances) / count($this->results);
    }

}