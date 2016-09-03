<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database;

/**
 * Description of Table
 *
 * @author Simone Esposito
 */
class Table extends AbstractTable {

    public function __construct($table, $connection_name = \smn\pheeca\Database::DEFAULT_RESOURCE) {
        parent::__construct($table, $connection_name);
    }

    /**
     * Metodo statico per avere tutte le righe della tabella
     * @param type $table
     * @param type $connection_name
     * @return type
     */
    public static function getFullTable($table, $connection_name = \smn\pheeca\Database::DEFAULT_RESOURCE) {
        $instance = new self($table, $connection_name);
        return $instance->runStatement(self::ALL_TABLE);
    }

}
