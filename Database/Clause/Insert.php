<?php

namespace smn\pheeca\Database\Clause;

use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\RunnableClauseInterface;

/**
 * Description of Insert
 *
 * @author Simone Esposito
 */

/**
 * 
 * 'table' => <table>
 * 'values' => array(
 *  'column' => <value>
 * )
 * 
 */
class Insert extends Clause implements RunnableClauseInterface {

    protected $_name = 'insert';
    protected $_clause = 'INSERT';
    protected $bind_params = array();
    protected $tablename = '';

    public function __construct($table, $values, $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => ['table' => $table, 'values' => $values],
            'suffix' => $suffix
        ]);
        
    }

    public function processFields() {

        // se i valori sono con indici numerici e ordinati, allora non specifico i campi della tabella
        // se i valori hanno indici nominali (caso opposto a questo sopra) allora devo indicare i campi nella tabella
        //((is_array($condition)) && (array_keys($condition) == range(0, (count($condition) - 1)))) 
        $values = $this->getData()['values'];
        $table = $this->getData()['table'];
        if ((is_array($values)) && (array_keys($values) !== range(0, (count($values) - 1)))) {
            $this->bind_params = array_combine(
                    array_map(function($e) {
                        return sprintf(':%s', $e);
                    }, array_keys($values)), array_values($values));
            $string = sprintf('INTO %s(%s) VALUES(%s)', $table, implode(' , ', array_keys($values)), implode(' , ', array_keys($this->bind_params)));
        } else {
            $string = sprintf('INTO %s VALUES(%s)', $table, implode(' , ', array_fill(0, count($values), '?')));
            $this->bind_params = $values;
        }
        $this->_fields = $string;
//
//        return;
//        $values = $this->getData();
//        $this->tablename = $values['table'];
//        $params = (array_key_exists('values', $values)) ? $values['values'] : array();
//
//        $fields = array();
//        $bindparams = array();
//        foreach ($params as $column => $value) {
//            // $column è il nome della colonna
//            // $value è il valore che assumerà
//            $fields[] = $column;
//            $bindparamname = sprintf(':%s', $column);
//            $bindparams[$bindparamname] = $value;
//        }
//        $this->bind_params = $bindparams;
//        $this->_fields = sprintf(
//                'INTO %s(%s) VALUES(%s)', $this->tablename, implode(', ', $fields), implode(', ', array_keys($this->bind_params)
//        ));
    }

    public function getBindParams() {
        return $this->bind_params;
    }

    public function getQueryString() {
        return $this->toString();
    }

}
