<?php
namespace smn\pheeca\Database;

/**
 *
 * @author Simone Esposito
 */
interface ClauseInterface {
    
    public function toString();
    
    public function formatString();    
}
