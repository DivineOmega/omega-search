<?php

namespace DivineOmega\OmegaSearch;

use PDO;
use InvalidArgumentException;
use DivineOmega\uxdm\Objects\Migrator;
use DivineOmega\uxdm\Objects\Sources\PDOSource;
use DivineOmega\uxdm\Objects\Destinations\NullDestination;

class MigratorManager {

    private $pdo;
    private $table;
    private $fields;

    public function __construct(PDO $pdo, $table, array $fields = []) {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->fields = $fields;
    }

    public function createMigrator($sqlOverride = null) {

        $pdoSource = new PDOSource($this->pdo, $this->table);
        $pdoSource->setPerPage(100);

        if($sqlOverride) {
            $pdoSource->setOverrideSQL($sqlOverride);
        }

        $migrator = new Migrator();
        $migrator->setSource($pdoSource);
        $migrator->setDestination(new NullDestination());
        $migrator->setFieldsToMigrate($this->fields);

        return $migrator;
    }

}
