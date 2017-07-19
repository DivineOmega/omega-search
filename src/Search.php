<?php

namespace RapidWeb\Search;

use PDO;
use InvalidArgumentException;
use RapidWeb\Search\MigratorManager;

class Search {

    private $migratorManager;
    private $primaryKey;

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

        $this->migratorManager = new MigratorManager($pdo, $table, $fields);
        $this->primaryKey = $primaryKey;

    }

    public function query($term, $limit = PHP_INT_MAX, $conditions) {

        $term = strtolower($term);

        $results = [];

        $migrator = $this->migratorManager->createMigrator();

        $migrator->setDataRowManipulator(function($dataRow) use ($term, $conditions, &$results) {

            foreach($conditions as $fieldName => $value) {
                $dataItem = $dataRow->getDataItemByFieldName($fieldName);
                if ($dataItem->value != $value) {
                    return;
                }
            }

            $dataItems = $dataRow->getDataItems();

            $relevance = 0;

            foreach($dataItems as $dataItem) {

                if ($dataItem->fieldName == $this->primaryKey) {
                    continue;
                }

                $value = strtolower($dataItem->value);

                if (strlen($value) < strlen($term)) {
                    continue;
                }

                $percent = 0;
                similar_text($value, $term, $percent);
                $relevance += $percent;

                if (strpos($dataItem->value, $term) !== false) {
                    $relevance += 100;
                }

                if (strpos(' '.$dataItem->value.' ', ' '.$term.' ') !== false) {
                    $relevance += 100;
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