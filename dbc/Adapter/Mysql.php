<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database\Adapter;

/**
 * Description of Mysql
 *
 * @author Simone Esposito
 */
class Mysql extends AbstractAdapter {

    protected $name = 'mysql';

    /**
     * Default namespace per le clausole
     * @var type 
     */
    protected $clause_namespace = '\smn\pheeca\Database\Clause\Mysql\\';

    public function getDatabaseName() {
        $query = 'select database() as DATABASE_NAME';
        $result = \smn\pheeca\Database::execute($query);
        if (count($result) > 0) {
            return $result[0]->DATABASE_NAME;
        }
        throw new \Exception('Errore nel trovare il nome del database');
    }

    public function getTables($database = null) {
        if (is_null($database)) {
            $database = $this->getDatabaseName();
        }
        $statement = \smn\pheeca\Database::getStatement(['select', 'from', 'where']);
        $statement->getClause('select')->setData(array('TABLE_NAME'));
        $statement->getClause('from')->setData(array('information_schema.tables'));
        $statement->getClause('where')->setData(array(['column' => 'TABLE_SCHEMA', 'value' => $database]));
        $result = $statement->run();
        $tables = [];
        foreach ($result as $table) {
            $tables[] = $table->table_name;
        }
        return $tables;
    }

    public function getColumns($table, $database = null) {
        if (is_null($database)) {
            $database = $this->getDatabaseName();
        }
        $statement = \smn\pheeca\Database::getStatement(['select', 'from', 'where']);
        $statement->getClause('select')->setData(array('COLUMN_NAME'));
        $statement->getClause('from')->setData(array('information_schema.columns'));
        $statement->getClause('where')->setData([
            ['column' => 'TABLE_SCHEMA', 'value' => $database, 'conjunction' => 'AND'],
            ['column' => 'TABLE_NAME', 'value' => $table]
        ]);
        $result = $statement->run();
        $columns = [];
        foreach ($result as $column) {
            $columns[] = $column->COLUMN_NAME;
        }
        return $columns;
    }

    public function getPrimaryKeys($table, $database = null) {
        if (is_null($database)) {
            $database = $this->getDatabaseName();
        }
        $statement = \smn\pheeca\Database::getStatement(['select', 'from', 'where']);
        $statement->getClause('select')->setData(array('COLUMN_NAME'));
        $statement->getClause('from')->setData(array('information_schema.columns'));
        $statement->getClause('where')->setData([
            ['column' => 'TABLE_SCHEMA', 'value' => $database, 'conjunction' => 'AND'],
            ['column' => 'TABLE_NAME', 'value' => $table, 'conjunction' => 'AND'],
            ['column' => 'COLUMN_KEY', 'value' => 'PRI']
        ]);
        $result = $statement->run();
        $columns = [];
        foreach ($result as $column) {
            $columns[] = $column->COLUMN_NAME;
        }
        return $columns;
    }

    public function getForeignKey($table, $database = null) {
        if (is_null($database)) {
            $database = $this->getDatabaseName();
        }
        $statement = \smn\pheeca\Database::getStatement(['select', 'from', 'where']);
        $statement->getClause('select')->setData(array('CONSTRAINT_SCHEMA, CONSTRAINT_NAME, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME'));
        $statement->getClause('from')->setData(array('information_schema.KEY_COLUMN_USAGE'));
        $statement->getClause('where')->setData([
            ['column' => 'TABLE_SCHEMA', 'value' => $database, 'conjunction' => 'AND'],
            ['column' => 'TABLE_NAME', 'value' => $table, 'conjunction' => 'AND'],
            ['column' => 'CONSTRAINT_NAME', 'value' => 'PRIMARY', 'logic' => '!=']
        ]);
        $result = $statement->run();
        $fkeys = [];
        foreach ($result as $fkey) {

            $refer = ['SCHEMA' => $fkey->REFERENCED_TABLE_SCHEMA, 'TABLE' => $fkey->REFERENCED_TABLE_NAME, 'COLUMN' => $fkey->REFERENCED_COLUMN_NAME];
            $column = ['SCHEMA' => $fkey->TABLE_SCHEMA, 'TABLE' => $fkey->TABLE_NAME, 'COLUMN' => $fkey->COLUMN_NAME];
            $fkeys[] = array('col' => $column, 'ref' => $refer);
        }
        return $fkeys;
    }

    public function getConstraint($table, $database = null) {
        if (is_null($database)) {
            $database = $this->getDatabaseName();
        }
        $statement = \smn\pheeca\Database::getStatement(['select', 'from', 'where']);
        $statement->getClause('select')->setData(array('CONSTRAINT_NAME'));
        $statement->getClause('from')->setData(array('information_schema.TABLE_CONSTRAINTS'));
        $statement->getClause('where')->setData([
            ['column' => 'TABLE_SCHEMA', 'value' => $database, 'conjunction' => 'AND'],
            ['column' => 'TABLE_NAME', 'value' => $table, 'conjunction' => 'AND'],
            [
                ['column' => 'CONSTRAINT_TYPE', 'value' => 'PRIMARY KEY', 'logic' => '!=', 'conjunction' => 'AND'],
                ['column' => 'CONSTRAINT_TYPE', 'value' => 'FOREIGN KEY', 'logic' => '!=']
            ]
        ]);
        $result = $statement->run();
        $constraints = [];
        foreach ($result as $constraint) {
            $constraints[] = $constraint->CONSTRAINT_NAME;
        }
        return $constraints;
    }

}
