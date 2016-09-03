<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database\Adapter;

/**
 *
 * @author Simone Esposito
 */
interface AdapterInterface {

    /**
     * Namespace base per le clausole
     */
    const DEFAULT_CLAUSE_NAMESPACE = '\smn\pheeca\Database\Clause\\';

    /**
     * Configura il namespace di base per le clausole
     * @param type $clause_ns
     */
    public function setClauseNamespace($clause_ns = self::DEFAULT_CLAUSE_NAMESPACE);

    /**
     * Restituisce il namespace di default per le clausole
     * @return String
     */
    public function getClauseNamespace();

//    /**
//     * Restituisce una classe Statement contenente le clausole associate al driver corretto 
//     * @return \smn\pheeca\Database\StatementInterface
//     */
//    public function getStatement($clauselist = array());

    /**
     * Restituisce il nome dell'adapter
     * @return String
     */
    public function getAdapterName();
    
    
    
    
    
    
}
