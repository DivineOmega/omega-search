<?php

namespace RapidWeb\Search;

use PDO;
use InvalidArgumentException;
use RapidWeb\Search\MigratorManager;
use Illuminate\Support\Str;

class Search {

    private $migratorManager;
    private $primaryKey;
    private $conditions;

    public function __construct(PDO $pdo, $table, $primaryKey, array $fields = [], array $conditions = []) {

        if (!$table) {
            throw new InvalidArgumentException('No table specified. You must specify a table to search.');
        }

        if (!$primaryKey) {
            throw new InvalidArgumentException('No primary key specified. You must specify the table\' primary key.');
        }

        if (!in_array($primaryKey, $fields)) {
            $fields[] = $primaryKey;
        }

        foreach($conditions as $fieldName => $value) {
            if (!in_array($fieldName, $fields)) {
                $fields[] = $fieldName;
            }
        }

        $this->migratorManager = new MigratorManager($pdo, $table, $fields);
        $this->primaryKey = $primaryKey;
        $this->conditions = $conditions;

    }

    public function query($term, $limit = PHP_INT_MAX) {

        $term = strtolower(trim($term));

        $terms = [$term];

        $singularTerm = Str::singular($term);
        if ($singularTerm != $term) {
            $terms[] = $singularTerm;
        }

        foreach(explode(' ', $term) as $word) {
            if (!$word) {
                continue;
            }
            $word = trim($word);
            $terms[] = $word;
            $singularWord = Str::singular($word);
            if ($singularWord != $word) {
                $terms[] = $singularWord;
            }
        }

        $results = [];

        $migrator = $this->migratorManager->createMigrator();

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

                if (strlen($value) < strlen($term[0])) {
                    continue;
                }

                $percent = 0;
                similar_text($value, $term[0], $percent);
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

        return $results;

    }

}