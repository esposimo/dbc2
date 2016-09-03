<?php

namespace smn\pheeca\Database\Clause;

use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\BindableClauseInterface;
use \smn\pheeca\Database\StatementException;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Where
 *
 * @author Simone Esposito
 */
class Join extends Clause implements BindableClauseInterface {

    protected $_name = 'join';
    protected $_clause = 'JOIN';
    protected $bind_params = array();
    
    const LEFT_JOIN = 'LEFT';
    const RIGHT_JOIN = 'RIGHT';
    

    public function __construct($table, $keys = array(), $type_join = self::LEFT_JOIN, $prefix = '', $suffix = '')
    {
        parent::__construct([
            'prefix' => $prefix,
            'data' => ['table' => $table, 'keys' => $keys, 'type_join' => $type_join],
            'suffix' => $suffix]
        );
        $this->_name = sprintf('join_%s', uniqid());
    }

    public function processFields()
    {
        
        $data = $this->getData();
        $string = $data['table'];
        $keys = $data['keys'];
        if ($keys instanceof \smn\pheeca\Database\Predicate) {
            $string .= sprintf(' ON (%s)', $keys->getSqlString());
            $this->bind_params = array_merge($this->getBindParams(), $keys->getBindParams());
        }
        else if (is_array($keys)) {
            $keys = (\smn\pheeca\ArrayUtils::staticIsMultiDimensional($keys)) ? $keys : [$keys];
            $predicate = new \smn\pheeca\Database\Predicate($keys);
            $string .= sprintf(' ON (%s)', $predicate->getSqlString());
            $this->bind_params = array_merge($this->getBindParams(), $predicate->getBindParams());
        }
        $this->_clause = sprintf('%s JOIN', $data['type_join']);
        $this->_fields = $string;
        
        return;
    }

    
    

    public function getBindParams()
    {
        return $this->bind_params;
    }

}
