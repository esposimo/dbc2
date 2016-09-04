<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace smn\pheeca\Database\Operator;

use \smn\pheeca\Database\Operator;

/**
 * Description of IN
 *
 * @author Simone Esposito
 */
class In extends Operator {

    protected $formattedString = '%s %s %s (%s)'; // <negate> <column> <operator> <value>
    protected $operator = 'IN';

    public function setValue($values) {
        if (!is_array($values)) {
            throw new \Exception('Per la clausola IN serve un array con almeno un valore');
        }
        parent::setValue($values);
    }

}
