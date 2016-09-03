<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database;

use \smn\pheeca\Database;

/**
 * Description of Statement
 *
 * @author Simone Esposito
 */
class Statement implements StatementInterface {

    /**
     * Lista delle clausole
     * @var ClauseInterface[] 
     */
    protected $clauselist = array();

    /**
     * Lista dei parametri da bindare
     * @var Array 
     */
    protected $bind_params = array();

    /**
     *
     * @var Adapter\AdapterInterface 
     */
    protected $adapter;

    /**
     *
     * @var \PDOStatement 
     */
    protected $statementInstance;
    protected $connection_name;

    /**
     * 
     * @param array $clause_list
     * @param type $namespace
     */
    public function __construct(Array $clause_list = array(), $connection_name = Database::DEFAULT_RESOURCE) {
        $this->connection_name = $connection_name;
        $adapter = Database::getAdapterInstanceByConnectionName($connection_name);
        $this->setAdapter($adapter);
        foreach ($clause_list as $clause_name => $clause_data) {
            if ($clause_data instanceof Clause) {
                $this->addClause($clause_data);
            } else {
                $clause_name = (is_numeric($clause_name)) ? ucfirst($clause_data) : ucfirst($clause_name);
                $clause_class_adapter = sprintf('%s%s', $adapter->getClauseNamespace(), ucfirst($clause_name));
                $clause_class_standard = sprintf('%s%s', Database::DEFAULT_CLAUSE_NS, ucfirst($clause_name));
                if (!class_exists($clause_class_adapter)) {
                    if (!class_exists($clause_class_standard)) {
                        throw new \Exception(sprintf('Clausola %s o %s non trovata', $clause_class_adapter, $clause_class_standard));
                    }
                    $clause_class_adapter = $clause_class_standard;
                }
                $reflect = new \ReflectionClass($clause_class_adapter);
                $instance = (is_array($clause_data)) ? $reflect->newInstanceArgs($clause_data) : $reflect->newInstanceArgs();
                $this->addClause($instance);
            }
        }
    }

    /**
     * Aggiunge una clausola in coda alla lista
     * @param \smn\pheeca\Database\ClauseInterface $instance
     */
    public function addClause(ClauseInterface $instance) {
        $name = $instance->getName();
        $this->clauselist[$name] = $instance;
    }

    /**
     * Restituisce tutti i parametri da bindare
     * @return type
     */
    public function getBindParams() {
        $bind_params = array();
        foreach ($this->clauselist as $clause) {
            if (($clause instanceof BindableClauseInterface) || ($clause instanceof RunnableClauseInterface)) {
                $bind_params = array_merge($bind_params, $clause->getBindParams());
            }
        }
        $this->bind_params = $bind_params;
        return $this->bind_params;
    }

    /**
     * Restituisce la query in formato stringa compresa di variabili da bindare
     * @return String
     */
    public function getQueryString() {

        $string = '';
        foreach ($this->clauselist as $clause) {
            $string .= $clause->toString();
        }
        return $string;
    }

    /**
     * Restituisce tutte le clausole
     * @return ClauseInterface[]
     */
    public function getClauseList() {
        return $this->clauselist;
    }

    /**
     * 
     * @param type $name
     * @return Clause|Clause\Where
     */
    public function getClause($name) {
        $clauselist = $this->getClauseList();
        return (array_key_exists($name, $clauselist)) ? $clauselist[$name] : null;
    }

    /**
     * Restituisce un array con la query nella stringa 'query' e i parametri bindati nell'indice 'params'
     * @return \stdClass
     */
    public function getQueryAndParams() {
        return (object) [
                    'query' => $this->getQueryString(),
                    'params' => $this->getBindParams()
        ];
    }

    /**
     * 
     * @param type $name
     * @return ClauseInterface
     */
    public function __get($name) {
        if (array_key_exists($name, $this->clauselist)) {
            return $this->clauselist[$name];
        }
        return false;
    }

    public function run() {
        $statement = $this->getQueryAndParams();
        return Database::execute($statement->query, $statement->params, $this->connection_name);
    }

    /**
     * 
     * @return \PDOStatement
     */
    public function getStatement() {
        return $this->statementInstance;
    }

    /**
     * 
     * @return Adapter\AbstractAdapter
     */
    public function getAdapter() {
        return $this->adapter;
    }

    public function setAdapter(Adapter\AbstractAdapter $interface) {
        $this->adapter = $interface;
        return $this;
    }

    public function getLastInsertId() {
        return $this->getAdapter()->lastInsertId();
    }
    
    
    public function __clone() {
        $clonelist = [];
        foreach($this->getClauseList() as $name => $clause) {
            $clonelist[$name] = clone $clause;
        }
        $this->clauselist = $clonelist;
        
    }

}
