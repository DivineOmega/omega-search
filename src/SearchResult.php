<?php

namespace RapidWeb\Search;


class SearchResult {

    public $id;
    public $relevance;

    public function __construct($id, $relevance) {
        $this->id = $id;
        $this->relevance = $relevance;
    }


}