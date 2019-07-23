<?php

namespace DivineOmega\OmegaSearch;


class SearchResult {

    public $id;
    public $relevance;

    public function __construct($id, $relevance) {
        $this->id = $id;
        $this->relevance = $relevance;
    }


}