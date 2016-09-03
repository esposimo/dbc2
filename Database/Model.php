<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database;

use \smn\pheeca\Database;
use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\Statement;

/**
 * Description of Model
 *
 * @author Simone Esposito
 */
abstract class Model {

    /**
     *
     * @var Statement[] 
     */
    protected $statements = array();
    protected $connection_name;

    public function __construct($connection_name = Database::DEFAULT_RESOURCE) {
        $this->setConnectionName($connection_name);
    }

    /**
     * Aggiunge un nuovo Statement
     * @param String $name
     * @param Statement $statement
     */
    public function addStatement($name, Statement $statement) {
        $this->statements[$name] = $statement;
    }

    /**
     * Restituisce uno Statement
     * @param type $name
     * @return Statement
     */
    public function getStatement($name) {
        return (array_key_exists($name, $this->statements)) ? $this->statements[$name] : null;
    }
    
    /**
     * Rimuove uno Statement
     * @param String $name
     */
    public function removeStatement($name) {
        if ($this->getStatement($name)) {
            unset($this->statements[$name]);
        }
    }
    
    /**
     * Esegue uno statement
     * @param type $name
     * @return Result|Boolean
     */
    public function runStatement($name) {
        if ($this->getStatement($name)) {
            return Database::execute($this->getStatement($name), array(), $this->getConnectionName());
        }
        throw new \Exception('Statement non presente');
    }
    
    /**
     * 
     * @return String
     */
    public function getConnectionName() {
        return $this->connection_name;
    }
    
    /**
     * 
     * @param String $connection_name
     */
    public function setConnectionName($connection_name = Database::DEFAULT_RESOURCE) {
        $this->connection_name = $connection_name;
    }
    
    
    /**
     * 
     * @return Adapter\AbstractAdapter
     */
    public function getAdapter() {
        return Database::getAdapterInstanceByConnectionName($this->getConnectionName());
    }

}
