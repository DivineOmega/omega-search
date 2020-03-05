<?php

namespace DivineOmega\OmegaSearch;

use PDO;
use InvalidArgumentException;
use Illuminate\Support\Str;
use Psr\Cache\CacheItemPoolInterface;

class OmegaSearch {

    private $pdo;
    private $table;
    private $primaryKey;
    private $fields = [];
    private $conditions;
    private $cacheItemPool;
    private $cacheExpiresAfter;
    private $sqlOverride;

    public function setDatabaseConnection(PDO $pdo) {
        $this->pdo = $pdo;
        return $this;
    }

    public function setTable($table) {
        $this->table = $table;
        return $this;
    }

    public function setSqlOverride($sqlOverride) {
        $this->sqlOverride = $sqlOverride;
        return $this;
    }

    public function setPrimaryKey($primaryKey) {
        $this->primaryKey = $primaryKey;
        return $this;
    }

    public function setFieldsToSearch(array $fields = []) {
        $this->fields = $fields;
        return $this;
    }

    public function setConditions(array $conditions = []) {
        $this->conditions = $conditions;
        return $this;
    }

    public function setCache(CacheItemPoolInterface $cacheItemPool, $cacheExpiresAfter = 60*60*24) {
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheExpiresAfter = $cacheExpiresAfter;
        return $this;
    }

    private function sanityCheck() {
        if (!$this->table) {
            throw new InvalidArgumentException('No table specified. You must specify a table to search.');
        }

        if (!$this->primaryKey) {
            throw new InvalidArgumentException('No primary key specified. You must specify the table\'s primary key.');
        }

        if (!$this->sqlOverride && !$this->fields) {
            throw new InvalidArgumentException('No fields specified. You must specify the fields you wish to search.');
        }

        if (!in_array($this->primaryKey, $this->fields)) {
            $this->fields[] = $this->primaryKey;
        }

        foreach($this->conditions as $fieldName => $value) {
            if (!in_array($fieldName, $this->fields)) {
                $this->fields[] = $fieldName;
            }
        }
    }

    private function buildSearchTerms($term) {
        $term = strtolower(trim($term));

        $terms = [$term];

        $singularTerm = Str::singular($term);
        if ($singularTerm != $term) {
            $terms[] = $singularTerm;
        }

        foreach(explode(' ', $term) as $word) {
            if (!$word || in_array($word, $terms)) {
                continue;
            }
            $word = trim($word);
            $terms[] = $word;
            $singularWord = Str::singular($word);
            if ($singularWord != $word) {
                $terms[] = $singularWord;
            }
        }

        return $terms;
    }

    public function query($term, $limit = PHP_INT_MAX) {

        $startMicrotime = microtime(true);

        $this->sanityCheck();

        $terms = $this->buildSearchTerms($term);

        $results = [];

        $migratorManager = new MigratorManager($this->pdo, $this->table, $this->fields);
        $migrator = $migratorManager->createMigrator($this->sqlOverride);

        if ($this->cacheItemPool && $this->cacheExpiresAfter) {
            $cacheKey = sha1(serialize([$this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS), $this->table, $this->fields]));
            $migrator->setSourceCache($this->cacheItemPool, $cacheKey, $this->cacheExpiresAfter);
        }

        $migrator->setDataRowManipulator(function($dataRow) use ($terms, &$results) {

            foreach($this->conditions as $fieldName => $value) {
                $dataItem = $dataRow->getDataItemByFieldName($fieldName);
                if ($dataItem->value != $value) {
                    return;
                }
            }

            $dataItems = $dataRow->getDataItems();

            $relevance = 0;

            foreach($dataItems as $dataItem) {

                if ($dataItem->fieldName == $this->primaryKey || array_key_exists($dataItem->fieldName, $this->conditions)) {
                    continue;
                }

                $value = strtolower($dataItem->value);

                if (strlen($value) < strlen($terms[0])) {
                    continue;
                }

                $percent = 0;
                similar_text($value, $terms[0], $percent);
                $relevance += $percent;

                foreach($terms as $term) {

                    if (Str::contains($value, $term)) {
                        $relevance += 100;
                    }

                    if (Str::contains(' '.$value.' ', ' '.$term.' ')) {
                        $relevance += 100;
                    }

                }

            }

            $primaryKeyValue = $dataRow->getDataItemByFieldName($this->primaryKey)->value;
            
            $results[$primaryKeyValue] = $relevance;

        });

        $migrator->migrate();

        arsort($results);

        $results = array_slice($results, 0, $limit, true);

        $searchResults = new SearchResults;

        foreach ($results as $id => $relevance) {
            $searchResults->addSearchResult(new SearchResult($id, $relevance));
        }

        $searchResults->calculateRelevances();
        $searchResults->time = microtime(true) - $startMicrotime;

        return $searchResults;

    }

}
