<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database;

/**
 * Description of Result
 *
 * @author Simone Esposito
 */
class Result implements \Countable, \ArrayAccess, \Iterator {

    protected $data = array();
    protected $position = 0;

    public function __construct($data) {
        $rows = [];
        foreach($data as $row) {
            $rows[] = new Row($row);
        }
        $this->data = $rows;
    }

    public function count() {
        return count($this->data);
    }

    public function offsetExists($offset) {
        return isset($this->data[$offset]);
    }

    public function offsetGet($offset) {
        return (isset($this->data[$offset])) ? $this->data[$offset] : null;
    }

    public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->data[] = $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    public function offsetUnset($offset) {
        unset($this->data[$offset]);
    }

    public function current() {
        return $this->data[$this->position];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function valid() {
        return isset($this->data[$this->position]);
    }

}
