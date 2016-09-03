<?php
namespace smn\pheeca\Database;

/**
 *
 * @author Simone Esposito
 */
interface RunnableClauseInterface {
    public function getQueryString();
    
    public function getBindParams();
    
    
}
