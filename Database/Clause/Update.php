<?php

namespace smn\pheeca\Database\Clause;

use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\RunnableClauseInterface;
use \smn\pheeca\Database\BindableClauseInterface;

/**
 * Description of Insert
 *
 * @author Simone Esposito
 */

/**
 * 
 * 'table' => <table>
 * 'rows' => array(
 *  'column' => <value>
 * )
 * 
 */
class Update extends Clause implements RunnableClauseInterface {

    protected $_name = 'update';
    protected $_clause = 'UPDATE';
    protected $bind_params = array();
    protected $tablename = '';

    /**
     * 
     * @param type $table Nome della tabella su cui fare l'update
     * @param type $values Valori da settare (per la clausola SET)
     * @param Array|Where|\smn\pheeca\Database\Predicate $rows Valori da aggiornare (per la clausola Where)
     * @param type $prefix
     * @param type $suffix
     */
    public function __construct($table, $values, $rows = [], $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => ['table' => $table, 'values' => $values, 'rows' => $rows],
            'suffix' => $suffix
        ]);
    }

    public function processFields() {
        // table => tabella
        // columns => array(column => value)
        // rows => WhereCondition | array()
        $data = $this->getData();
        $this->tablename = $data['table'];
        $values = $data['values'];
        $rows = $data['rows'];

        $set = new Set($values);
        $string = sprintf('%s %s', $this->tablename, $set->toString());
        $this->bind_params = $set->getBindParams();
        
        if ($rows instanceof \smn\pheeca\Database\Predicate) {
            $string .= ' WHERE ' .$rows->getSqlString();
            $this->bind_params = array_merge($this->bind_params, $rows->getBindParams());
        }
        else if ($rows instanceof Where) {
            $string .= ' ' .$rows->toString();
            $this->bind_params = array_merge($this->bind_params, $rows->getBindParams());
        }
        else if ((is_array($rows)) && (count($rows) > 0)) {
            $conditions = [];
            $i = 0;
            $x = count($rows);
            foreach ($rows as $column => $value) {
                $i++;
                $conditions[] = ['column' => $column, 'value' => $value, 'conjunction' => 'AND'];
            }
            end($conditions);
            unset($conditions[key($conditions)]['conjunction']);
            reset($conditions);
            $predicate = new \smn\pheeca\Database\Predicate([$conditions]);
            $string .= ' WHERE ' . $predicate->getSqlString();
            $this->bind_params = array_merge($this->bind_params, $predicate->getBindParams());
        }
        $this->_fields = $string;



        return;
//        $values = $this->getData();
//        $this->tablename = $values['table'];
//        $columns = $values['columns'];
//        $counter = 0;
//        $newvalues = array();
//        // creo i SET
//        foreach ($columns as $column => $value) {
//            $bindableName = sprintf('%s_%s', $this->_uniqid, $column);
//            $bindparamname = sprintf(':%s_%s', $bindableName, $counter++);
//            $newvalues[] = sprintf('%s = %s', $column, $bindparamname);
//            $this->bind_params[$bindparamname] = $value;
//        }
//
//        $sets = implode(', ', $newvalues);
//        $conditions = '';
//        if (array_key_exists('rows', $values)) {
//            $params = $values['rows'];
//            if (($params instanceof BindableClauseInterface) && ($params instanceof Clause)) {
//                $conditions = $params->toString();
//                $this->bind_params = array_merge($this->bind_params, $params->getBindParams());
//            } else {
//                $fields = array();
//                $bindparams = array();
//                foreach ($params as $column => $value) {
//                    // $column è il nome della colonna
//                    // $value è il valore che assumerà
//                    $bindableName = sprintf('%s_%s', $this->_uniqid, $column);
//                    $bindparamname = sprintf(':%s_%s', $bindableName, $counter++);
//                    $fields[] = sprintf('%s = %s', $column, $bindparamname);
//                    $bindparams[$bindparamname] = $value;
//                }
//                $this->bind_params = array_merge($this->bind_params, $bindparams);
//                $conditions = 'WHERE ' . implode(', ', $fields);
//            }
//        }
//        $string = ($conditions == '') ? sprintf('%s SET %s', $this->tablename, $sets) : sprintf('%s SET %s %s', $this->tablename, $sets, $conditions);
//        $this->_fields = $string;
    }

    public function getBindParams() {
        return $this->bind_params;
    }

    public function getQueryString() {
        return $this->toString();
    }

}
