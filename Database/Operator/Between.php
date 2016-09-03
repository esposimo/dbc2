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
class Between extends Operator {

    protected $formattedString = '%s %s BETWEEN %s AND %s '; // <negate> <column> <operator> <value>
    protected $operator = 'BETWEEN';
    protected $left;
    protected $right;

    public function setValue($values) {
        parent::setValue($values);
        $this->left = $values[0];
        $this->right = $values[1];
    }

    public function process() {
        $this->bind_params = [];
        $this->counter_bind_params = 0;
        $values = $this->getValue();

        if (is_array($this->getValue()) && (count($this->getValue() == 2))) {
            $this->left = $this->addBindParam($this->getColumn(), $this->getValue()[0]);
            $this->right = $this->addBindParam($this->getColumn(), $this->getValue()[1]);
        } else {
            throw new \Exception('Argomento non valido');
        }
        $return = (object) [
                    'negate' => $this->getNegateString(),
                    'column' => $this->getColumn(),
                    'value_left' => $this->left,
                    'value_right' => $this->right
        ];

        return $return;
    }

    public function getExpression() {
        if ($this->bindable === false) {
            return sprintf($this->formattedString, $this->getNegateString(), $this->getColumn(), $this->left, $this->right);
        }
        $strings = array_values((array) $this->process());
        return vsprintf($this->formattedString, $strings);
    }

}
