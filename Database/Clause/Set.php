<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database\Clause;

use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\BindableClauseInterface;
use \smn\pheeca\ArrayUtils;

/**
 * Description of Set
 *
 * @author Simone Esposito
 */
class Set extends Clause implements BindableClauseInterface {

    protected $_name = 'set';
    protected $_clause = 'SET';
    protected $bind_params = array();

    public function __construct($values = [], $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $values,
            'suffix' => $suffix
        ]);
    }

    public function processFields() {
//        $this->bind_params = array_combine(
//                array_map(function($e) {
//                    return sprintf(':%s', $e);
//                }, array_keys($this->getData())), array_values($this->getData()));

        $i = 0;
        $this->bind_params = [];
        $sets = [];
        foreach($this->getData() as $column => $value) {
            $bindparam = sprintf(':%s_%s', $column,++$i);
            $this->bind_params[$bindparam] = $value;
            $sets[] = sprintf('%s = %s', $column, $bindparam);
        }
        $this->_fields = implode(' , ', $sets);
    }

    public function getBindParams() {
        return $this->bind_params;
    }

}
