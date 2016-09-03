<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database\Adapter;

/**
 * Description of BaseAdapter
 *
 * @author Simone Esposito
 */
class BaseAdapter extends AbstractAdapter implements AdapterInterface {

    protected $name = 'basic';

    public function getColumns($table, $database = null) {
        return null;
    }

    public function getConstraint($table, $database = null) {
        return null;
    }

    public function getDatabaseName() {
        return null;
    }

    public function getForeignKey($table, $database = null) {
        return null;
    }

    public function getPrimaryKeys($table, $database = null) {
        return null;
    }

    public function getTables($table, $database = null) {
        return null;
    }

}
