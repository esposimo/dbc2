<?php
namespace smn\pheeca\Database;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 *
 * @author Simone Esposito
 */
interface BindableClauseInterface {
    public function getBindParams();
    
}
