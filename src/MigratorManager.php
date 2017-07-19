<?php

namespace RapidWeb\Search;

use PDO;
use InvalidArgumentException;
use RapidWeb\uxdm\Objects\Migrator;
use RapidWeb\uxdm\Objects\Sources\PDOSource;
use RapidWeb\uxdm\Objects\Destinations\NullDestination;

class MigratorManager {

    private $pdo;
    private $table;
    private $fields;
    private $conditions;

    public function __construct(PDO $pdo, $table, array $fields = [], array $conditions = []) {

        if (!$table) {
            throw new InvalidArgumentException('No table specified. You must specify a table to search.');
        }

        $this->pdo = $pdo;
        $this->table = $table;
        $this->fields = $fields;
        $this->conditions = $conditions;
    }

    public function createMigrator() {

        $migrator = new Migrator();
        $migrator->setSource(new PDOSource($this->pdo, $this->table));
        $migrator->setDestination(new NullDestination());
        $migrator->setFieldsToMigrate($this->fields);
        $migrator->setSkipIfTrueCheck(function($dataRow) {
            foreach($this->conditions as $fieldName => $value) {
                $dataItem = $dataRow->getDataItemByFieldName($fieldName);
                if ($dataItem->value != $value) {
                    return true;
                }
            }
            return false;
        });

        return $migrator;
    }

}