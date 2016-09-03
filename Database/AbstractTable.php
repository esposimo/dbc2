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
abstract class AbstractTable extends Model {

    protected $table;

    const ALL_TABLE = 'ALL_TABLE';

    protected $pkeys = null;
    protected $columns = null;
    protected $fkeys = null;

    public function __construct($table, $connection_name = \smn\pheeca\Database::DEFAULT_RESOURCE) {
        parent::__construct($connection_name);
        $this->setTableName($table);
        $this->addStatement(self::ALL_TABLE, new Statement(['select', 'from' => [$this->getTableName()]], $this->getConnectionName()));
    }

    /**
     * Configura la tabella
     * @param String $name
     */
    public function setTableName($name) {
        $this->table = $name;
    }

    /**
     * Restituisce il nome della tabella
     * @return type
     */
    public function getTableName() {
        return $this->table;
    }

    public function getTable() {
        return $this->runStatement(self::ALL_TABLE);
    }

    public function fetchPrimaryKeys() {
        $this->pkeys = $this->getAdapter()->getPrimaryKeys($this->getTableName());
    }

    public function fetchColumns() {
        $this->columns = $this->getAdapter()->getColumns($this->getTableName());
    }

    public function fetchForeignKey() {
        $this->fkeys = $this->getAdapter()->getForeignKey($this->getTableName());
    }

    public function getPrimaryKeys() {
        if (is_null($this->pkeys)) {
            $this->fetchPrimaryKeys();
        }
        return $this->pkeys;
    }

    public function getForeignKeys() {
        if (is_null($this->fkeys)) {
            $this->fetchForeignKey();
        }
        return $this->fkeys;
    }

    public function getColumns() {
        if (is_null($this->columns)) {
            $this->fetchColumns();
        }
        return $this->columns;
    }

    /**
     * 
     * @param type $values
     * @return Boolean|Result
     */
    public function getByPrimaryKey($values = array()) {
        if (is_scalar($values)) {
            $values = array($values);
        }
        $column_value = array_combine($this->getPrimaryKeys(), array_values($values));
        $condition = [];
        $i = 0;
        $x = count($column_value);
        foreach ($column_value as $column => $value) {
            $i++;
            $condition[] = ($i < $x) ? ['column' => $column, 'value' => $value, 'conjunction' => 'AND'] : ['column' => $column, 'value' => $value];
        }
        $statement = \smn\pheeca\Database::getStatement(array('select', 'from', 'where'));
        $statement->getClause('from')->setData(array($this->getTableName()));
        $statement->getClause('where')->setData($condition);
        return $statement->run();
    }

    public function hasPrimaryKey() {
        return (count($this->getPrimaryKeys()) == 0) ? false : true;
    }

    /**
     * Esegue la truncate di una tabella
     * @return Boolean
     */
    public function truncate() {
        $query = sprintf('TRUNCATE %s', $this->getTableName());
        return \smn\pheeca\Database::execute($query);
    }

    /**
     * Esegue una delete della riga avente come primary key il valore o i valori indicati in $pkeys
     * @param type $pkeys
     * @return Boolean
     * @throws \Exception
     */
    public function deleteByPrimaryKey($pkeys = array()) {
        // in teoria dovresti cancellare una sola riga..
        // per delete multiple su più primary key, conviene fare una transiction
        if (!$this->hasPrimaryKey()) {
            throw new \Exception(sprintf('La tabella %s non contiene chiavi primarie', $this->getTableName()));
        }
        $table_pkeys = $this->getPrimaryKeys();
        if (is_scalar($pkeys)) {
            $pkeys = [$pkeys];
        }
        if (count($table_pkeys) !== count($pkeys)) {
            throw new \Exception(sprintf('Il numero di valori non corrisponde con il numero di chiavi primarie (%s)', implode(' , ', $table_pkeys)));
        }
        $deleteKeys = array_combine(array_values($table_pkeys), array_values($pkeys)); // qui c'è la condizione basata sulle PK per eseguire la delete
        $statement = \smn\pheeca\Database::getStatement(['delete' => [$this->getTableName(), $deleteKeys]], $this->getConnectionName());
        return $statement->run();
    }

    /**
     * 
     * @param String|Array $pkeys chiave primaria o array di chiavi primarie
     * @param Array $values Array associativo column => value 
     * @return Boolean
     * @throws \Exception
     */
    public function updateByPrimaryKey($pkeys = array(), $values = array()) {
        if (!$this->hasPrimaryKey()) {
            throw new \Exception(sprintf('La tabella %s non contiene chiavi primarie', $this->getTableName()));
        }
        $table_pkeys = $this->getPrimaryKeys();
        if (is_scalar($pkeys)) {
            $pkeys = [$pkeys];
        }
        if (count($table_pkeys) !== count($pkeys)) {
            throw new \Exception(sprintf('Il numero di valori non corrisponde con il numero di chiavi primarie (%s)', implode(' , ', $table_pkeys)));
        }
        $updateKeys = array_combine(array_values($table_pkeys), array_values($pkeys)); // qui c'è la condizione basata sulle PK per eseguire l'update
        $statement = \smn\pheeca\Database::getStatement(['update' => [$this->getTableName(), $values, $updateKeys]], $this->getConnectionName());
        return $statement->run();
    }

    public function insert($values) {
        $statement = \smn\pheeca\Database::getStatement(['insert' => [$this->getTableName(), $values]], $this->getConnectionName());
        try {
            $statement->run();
            return $this->getAdapter()->lastInsertId();
        } catch (Exception $ex) {
            
        }
    }

    /**
     * 
     * @param type $table
     * @param type $filter
     * @return type
     */
    public function relationByFk($table = null, $filter = [], $columns = '*') {
        $fkeys = (is_null($table)) ? $this->getForeignKeys() : array_filter($this->getForeignKeys(), function($i) use ($table) {
                    return ($i['ref']['TABLE'] == $table) ? true : false;
                }, ARRAY_FILTER_USE_BOTH);
        $join_list = [];

        foreach ($fkeys as $fkey) {
            $ref_table = $fkey['ref']['TABLE'];
            $ref_column = $fkey['ref']['COLUMN'];
            $t_column = $fkey['col']['COLUMN'];
            $joinData = [
                $ref_table,
                [
                    'column' => sprintf('%s.%s', $ref_table, $ref_column),
                    'value' => sprintf('%s.%s', $this->getTableName(), $t_column),
                    'bindable' => false
                ]
            ];
            $join_list[] = \smn\pheeca\Database::getClauseInstanceByConnectionName('Join', $joinData, $this->getConnectionName());
        }
        $clause_list = (count($filter) > 0) ? array_merge(['select' => $columns, 'from' => [$this->getTableName()]], $join_list, ['where' => [$filter]]) : array_merge(['select' => $columns, 'from' => [$this->getTableName()]], $join_list);
        $statement = new Statement($clause_list);
        return $statement->run();
    }

    /**
     * Esegue una delete in base alle condizioni indicate
     * @param Array|Predicate|Clause\Where $condition Se è un array deve essere nella forma column => value, ed accetta una sola colonna
     * Può essere passata anche una Predicate o Where già configurata. Se passato un array multidimensionale nella forma column => value,
     * le condizioni saranno messe in AND. Per congiunzioni diverse usare una Where class o Predicate
     * Se $condition è un array vuoto viene effettuato il delete dell'intera tabella
     */
    public function delete($condition) {
        $statement = \smn\pheeca\Database::getStatement(['delete' => [$this->getTableName(), $condition]], $this->getConnectionName());
        return $statement->run();
    }

    /**
     * Effettua update sulla tabella. 
     * @param Array $values Coppia di valori nella forma column => value
     * @param Array|Predicate|Clause\Where $condition Condizioni da applicare alla Update. 
     */
    public function update($values, $condition = []) {
        $statement = \smn\pheeca\Database::getStatement(['update' => [$this->getTableName(), $values, $condition]], $this->getConnectionName());
        return $statement->run();
    }

}
