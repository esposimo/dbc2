<?php
namespace smn\pheeca\Database\Clause;

use \smn\pheeca\Database\Clause;


/**
 * Description of Select
 *
 * @author Simone Esposito
 */
class OrderBy extends Clause {

    protected $_name = 'OrderBy';
    protected $_clause = 'ORDER BY';

    public function __construct($fields = array('*'), $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $fields,
            'suffix' => $suffix]
        );
    }

    private function processSelect($columns) {
        $fields = array();
        foreach ($columns as $columnName => $columnAlias) {
            if (is_array($columnAlias)) {
                $fields[] = $this->processSub($columnName, $columnAlias);
            } else {
                $fields[] = $this->processAlias($columnName, $columnAlias);
            }
        }
        return implode(', ', $fields);
    }

    private function processAlias($columnName, $columnAlias, $prefix = null) {
        if (is_numeric($columnName)) {
            return (is_null($prefix)) ? $columnAlias : $prefix . '.' . $columnAlias;
        }
        return (is_null($prefix)) ? $columnName . ' AS ' . $columnAlias : $prefix . '.' . $columnName . ' AS ' . $columnAlias;
    }

    private function processSub($prefix, $columns) {
        $fields = array();
        foreach ($columns as $columnName => $columnAlias) {
            $fields[] = $this->processAlias($columnName, $columnAlias, $prefix);
        }
        return implode(', ', $fields);
    }

    public function processFields() {
        if (empty($this->getData())) {
            $this->_fields = '*';
        } else {
            $this->_fields = $this->processSelect($this->getData());
        }
    }
    
    public function formatString() {
        $this->_formedString = sprintf('%s %s %s %s', $this->_clause, $this->_prefix, $this->_fields, $this->_suffix);
    }

}
