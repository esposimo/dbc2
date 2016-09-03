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
class Delete extends Clause implements RunnableClauseInterface {

    protected $_name = 'delete';
    protected $_clause = 'DELETE';
    protected $bind_params = array();
    protected $tablename;

    public function __construct($table, $rows = [], $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => ['table' => $table, 'rows' => $rows],
            'suffix' => $suffix
        ]);
    }

    public function processFields() {
        $data = $this->getData();
        $this->tablename = $data['table'];
        $string = sprintf('FROM %s', $this->tablename);
        $rows = $this->getData()['rows'];

        if ($rows instanceof \smn\pheeca\Database\Predicate) {
            $string .= ' WHERE ' . $rows->getSqlString();
            $this->bind_params = $rows->getBindParams();
        } else if ($rows instanceof Where) {
            $string .= ' ' .$rows->toString();
            $this->bind_params = $rows->getBindParams();
        }
        else if ((is_array($rows)) && (count($rows) > 0)) {
            $conditions = [];
            $i = 0;
            $x = count($rows);
            foreach ($rows as $column => $value) {
                $i++;
                $conditions[] = ($i == $x) ? ['column' => $column, 'value' => $value] : ['column' => $column, 'value' => $value, 'conjunction' => 'AND'];
            }
            $predicate = new \smn\pheeca\Database\Predicate([$conditions]);
            $string .= ' WHERE ' . $predicate->getSqlString();
            $this->bind_params = $predicate->getBindParams();
        }

        $this->_fields = $string;
    }

    public function getBindParams() {
        return $this->bind_params;
    }

    public function getQueryString() {
        return $this->toString();
    }

}
