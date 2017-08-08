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

    public function __construct(PDO $pdo, $table, array $fields = []) {

        if (!$table) {
            throw new InvalidArgumentException('No table specified. You must specify a table to search.');
        }

        $this->pdo = $pdo;
        $this->table = $table;
        $this->fields = $fields;
    }

    public function createMigrator() {

        $pdoSource = new PDOSource($this->pdo, $this->table);
        $pdoSource->setPerPage(100);

        $migrator = new Migrator();
        $migrator->setSource($pdoSource);
        $migrator->setDestination(new NullDestination());
        $migrator->setFieldsToMigrate($this->fields);

        return $migrator;
    }

}