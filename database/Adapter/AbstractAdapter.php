<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database\Adapter;


use \smn\pheeca\Database\Statement;
use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\BindableClauseInterface;
use \smn\pheeca\Database\Result;
/**
 * Description of AbstractAdapter
 *
 * @author Simone Esposito
 */
abstract class AbstractAdapter extends \PDO implements AdapterInterface {

    /**
     * Nome dell'adapter
     * @var String 
     */
    protected $name = '';

    /**
     * Default namespace per le clausole
     * @var type 
     */
    protected $clause_namespace = null;

    /**
     * Configura il namespace di base per le clausole
     * @param type $clause_ns
     */
    public function setClauseNamespace($clause_ns = self::DEFAULT_CLAUSE_NAMESPACE) {
        $this->clause_namespace = $clause_ns;
    }

    /**
     * Restituisce il namespace di default per le clausole
     * @return String
     */
    public function getClauseNamespace() {
        return (is_null($this->clause_namespace)) ? self::DEFAULT_CLAUSE_NAMESPACE : $this->clause_namespace;
    }

    /**
     * Restituisce il nome dell'adapter
     * @return String
     */
    public function getAdapterName() {
        return $this->name;
    }

    public function execute($statement, $params = array(), $result = null) {
        if ($statement instanceof Statement) {
            $data = $statement->getQueryAndParams();
            $query = $data->query;
            $params = $data->params;
        } else if ($statement instanceof Clause) {
            $query = $statement->toString();
            $params = ($statement instanceof BindableClauseInterface) ? $statement->getBindParams() : $params;
        } else if (is_string($statement)) {
            $query = $statement;
        } else {
            throw new \Exception('Invalid argument');
        }
        try {
            $stmn = $this->prepare($query);
            $result = $stmn->execute($params);
            if ($result === false) {
                throw new \Exception(implode('|', $stmn->errorInfo()));
            }
            else if ($stmn->columnCount() > 0) {
                // è un result set , restituiscilo
                return new Result($stmn->fetchAll(\PDO::FETCH_ASSOC));
            }
            // altrimenti è uno statement che non restituisce risultati, quindi mettici un bel return
            return $result;
            
        } catch (\Exception $ex) {
            echo 'Errore ' . $ex->getCode() . '<br>';
            echo 'File ' .$ex->getFile() .'<br>';
            echo 'Line ' .$ex->getLine() .'<br>';
            echo '<pre>';
            print_r($ex->getMessage());
            print_r($ex->getTrace());
            echo '</pre>';
        }
    }
    
    abstract public function getDatabaseName();
    
    abstract public function getTables($database = null);
    
    abstract public function getColumns($table, $database = null);
    
    abstract public function getPrimaryKeys($table, $database = null);
        
    abstract public function getForeignKey($table, $database = null);
    
    abstract public function getConstraint($table, $database = null);

}
