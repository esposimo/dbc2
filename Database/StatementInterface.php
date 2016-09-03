<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace smn\pheeca\Database;
/**
 *
 * @author Simone Esposito
 */
interface StatementInterface {
    //put your code here
    
     public function addClause(ClauseInterface $instance);
     
     /**
      * @return String
      */
     public function getQueryString();
     
     /**
      * @return Array
      */
     public function getBindParams();
     
     /**
      * return ClauseInterface[]
      */
     public function getClauseList();
     
     
     /**
      * Restituisce un array con la query e i bindparams
      * 'query' index contiene la query
      * 'params' index contiene i parametri
      */
     public function getQueryAndParams();
     
     /**
      * 
      * @param \smn\pheeca\Database\Adapter\AdapterInterface $interface
      * @return self
      */
     public function setAdapter(Adapter\AbstractAdapter $interface);
     
     /**
      * @return Statement
      */
     public function getAdapter();
     
     /**
      * Esegue lo statement
      */
     public function run();
     
     
}
