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

        $results = [];

        $migrator = $this->migratorManager->createMigrator();

        $migrator->setDataRowManipulator(function($dataRow) use ($term, &$results) {

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

                if (strlen($value) < strlen($term)) {
                    continue;
                }

                $percent = 0;
                similar_text($value, $term, $percent);
                $relevance += $percent;
                
                $terms = [$term, Str::singular($term)];

                foreach(explode(' ', $term) as $word) {
                    if (!$word) {
                        continue;
                    }
                    $word = trim($word);
                    $terms[] = $word;
                    $terms[] = Str::singular($word);
                }

                foreach($terms as $term) {

                    if (Str::contains($value, $term) !== false) {
                        $relevance += 100;
                    }

                    if (Str::contains(' '.$value.' ', ' '.$term.' ') !== false) {
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