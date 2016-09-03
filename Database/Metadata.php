<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database;

/**
 * Description of Metadata
 *
 * @author Simone Esposito
 */
class Metadata {
    
    /**
     *
     * @var AbstractAdapter 
     */
    protected $adapter;
    
    public function __construct(Adapter\AbstractAdapter $adapter) {
        $this->setAdapter($adapter);
    }
    
    
    public function setAdapter(Adapter\AbstractAdapter $adapter) {
        $this->adapter = $adapter;
    }
    
    public function getAdapter() {
        return $this->adapter;
    }
    
    
    public function getSchemaName() {
        
    }
    
    
}
