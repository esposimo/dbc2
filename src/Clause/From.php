<?php

namespace smn\pheeca\Database\Clause;

use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\Query;
use \smn\pheeca\Database\BindableClauseInterface;

/**
 * Description of Select
 *
 * @author Simone Esposito
 */

class From extends Clause {

    protected $_name = 'from';
    protected $_clause = 'FROM';
    protected $aliases = [];
    protected $_derivedTableCounter = 0;

    public function __construct($fields = '', $prefix = '', $suffix = '') {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        parent::__construct([
            'prefix' => $prefix,
            'data' => $fields,
            'suffix' => $suffix]
        );
    }

    public function processFields() {
        $fields = array();
        $tables = $this->getData();
        $this->aliases = [];
        foreach ($tables as $tableAlias => $tableName) {
            if ($tableName instanceof Query) { // aggiustare qui !!
                $fields[] = sprintf('(%s) %s%s', trim($tableName->toString()), 't', ++$this->_derivedTableCounter);
            } else if (is_numeric($tableAlias)) {
                $fields[] = $tableName;
            } else {
                $fields[] = $tableName . ' ' . $tableAlias;
                $this->aliases[$tableAlias] = $tableName;
            }
        }
        $this->_fields = implode(', ', $fields);
    }

    public function formatString() {
        $this->_formedString = sprintf('%s %s %s %s', $this->_clause, $this->_prefix, $this->_fields, $this->_suffix);
    }
    
    public function getAlias($table) {
        return array_search($this->aliases, $table);
    }

}

class From2 extends Clause {

    protected $_name = 'from';
    protected $_clause = 'FROM';
    protected $aliases = [];
    protected $_derivedTableCounter = 0;

    public function __construct($fields = '', $prefix = '', $suffix = '') {
        if (is_string($fields)) {
            $fields = array($fields);
        }
        parent::__construct([
            'prefix' => $prefix,
            'data' => $fields,
            'suffix' => $suffix]
        );
    }

    public function processFields() {
        $fields = array();
        $tables = $this->getData();
        $this->aliases = [];
        foreach ($tables as $tableAlias => $tableName) {
            if ($tableName instanceof \smn\pheeca\Database\Statement) { // aggiustare qui !!
                $fields[] = sprintf('(%s) %s%s', trim($tableName->getQueryString()), 't', ++$this->_derivedTableCounter);
            } else if (is_numeric($tableAlias)) {
                $alias = $this->createAlias($tableName);
                $this->setAlias($tableName, $alias);
                $fields[] = sprintf('%s %s', $tableName, $alias);
            } else {
                $alias = $tableAlias;
                if ($this->isAlias($alias)) { // se esiste l'alias indicato
                    $table_rename = $this->getTableFromAlias($alias); // mi prendo la tabella al quale è associato l'alias
                    $this->aliases[$alias] = $tableName;
                    $this->setAlias($table_rename);
                } else {
                    $this->setAlias($tableName);
                }
                $fields[] = sprintf('%s %s', $tableName, $alias);
            }
        }
        $this->_fields = implode(', ', $fields);
    }

    public function formatString() {
        $this->_formedString = sprintf('%s %s %s %s', $this->_clause, $this->_prefix, $this->_fields, $this->_suffix);
    }

    public function createAlias($table) {
        $x = 1;
        $shortname = substr($table, 0, $x);
        $aliases = $this->getAliases();
        while (array_key_exists($shortname, $aliases) !== false) {
            $shortname = substr($table, 0, ++$x);
        }
//        $this->aliases[$shortname] = $table;
        return $shortname;
    }

    public function isAlias($alias) {
        return array_key_exists($alias, $this->aliases);
    }

    public function getAliases() {
        return $this->aliases;
    }

    public function getAlias($table) {
        return array_search($this->getAliases(), $table);
    }

    public function setAlias($table, $alias = null) {
        if (is_null($alias)) {
            // se non esiste l'alias, lo creo
            $alias = $this->createAlias($table);
            $this->setAlias($table, $alias);
        } else if ((!is_null($alias) && ($this->isAlias($alias)))) {
            // invece se esiste l'alias  prendo la tabella associata all'alias esistente e le cambio l'alias
            // sostituisco la tabella che tiene l'alias
            $tablename = $this->getTableFromAlias($alias);
            $shortname = $this->createAlias($tablename);
            $this->aliases[$alias] = $table;
            $this->aliases[$shortname] = $tablename;
        } else {
            $this->aliases[$alias] = $table;
        }
    }

    public function getTableFromAlias($alias) {
        $tables = $this->getAliases();
        return (array_key_exists($alias, $tables)) ? $tables[$alias] : false;
    }

}
