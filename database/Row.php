<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database;

/**
 * Description of Row
 *
 * @author Simone Esposito
 */
class Row implements \Iterator {
    /*
     * @return Row[]
     */

    protected $data = array();

    /**
     * Indice dell'array
     * @var Integer 
     */
    protected $position = 0;

    /**
     * Mappa degli indici
     * @var Array 
     */
    protected $map_index = array();

    public function __construct($data) {
        $this->data = $data;
        $this->map_index = array_combine(array_keys(array_change_key_case($this->data, \CASE_LOWER)), array_keys($this->data));
    }

    private function getRealIndex() {
        $indexes = array_keys($this->data);
        return (isset($indexes[$this->position])) ? $indexes[$this->position] : false;
    }

    public function current() {
        $index = $this->getRealIndex();
        return (isset($this->data[$index])) ? $this->data[$index] : null;
    }

    public function key() {
        return $this->getRealIndex();
    }

    public function next() {
        $this->position++;
    }

    public function rewind() {
        $this->position = 0;
    }

    public function valid() {
        $index = $this->getRealIndex();
        return isset($this->data[$index]);
    }

    public function __get($name) {
        $index = $this->map_index[strtolower($name)];
        return (array_key_exists($index, $this->data)) ? $this->data[$index] : false;
    }

    public function __set($name, $value) {
        if (array_key_exists(strtolower($name), $this->map_index)) {
            $index = $this->map_index[strtolower($name)];
            $this->data[$index] = $value;
        }
        else {
            $this->map_index[strtolower($name)] = $name;
            $this->data[$name] = $value;
        }
    }

}
