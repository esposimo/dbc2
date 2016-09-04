<?php

namespace smn\pheeca\Database;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Operator
 *
 * @author Simone Esposito
 */
class Operator implements BindableClauseInterface {

    protected $operator = self::OPERATOR_EQUAL;
    protected $formattedString = '%s %s %s %s'; // <negate> <column> <operator> <value>
    protected $column;
    protected $values;
    protected $uniqid;
    protected $counter_bind_params = 0;
    protected $bind_params = array();
    protected $bindable = true;
    protected $negate = false;
    protected $processed = [];

    const OPERATOR_EQUAL = '=';
    const OPERATOR_NOT_EQUAL = '!=';
    const OPERATOR_LESS_EQUAL_THAN = '<=';
    const OPERATOR_LESS_THAN = '<';
    const OPERATOR_EQUAL_GREATER_THAN = '=>';
    const OPERATOR_GREATER = '>';

    public function __construct($column = null, $operator = self::OPERATOR_EQUAL, $value = null, $bindable = true, $negate = false) {
        $this->uniqid = uniqid();
        $this->setBindable($bindable);
        if (!is_null($column)) {
            $this->setColumn($column);
        }
        if (!is_null($value)) {
            $this->setValue($value);
        }
        if ($operator != self::OPERATOR_EQUAL) {
            $this->setOperator($operator);
        }
    }

    public function setNegate($negate = false) {
        $this->negate = $negate;
    }

    public function getNegate() {
        return $this->negate;
    }

    public function getNegateString() {
        return ($this->getNegate()) ? 'NOT' : '';
    }

    public function setColumn($column) {
        $this->column = $column;
        return $this;
    }

    /**
     * Restituisce il nome colonna
     * @return type
     */
    public function getColumn() {
        return $this->column;
    }

    public function setValue($values) {
        $this->values = $values;
        return $this;
    }

    /**
     * Deve restituire i valori (sotto forma di bind se serve)
     * @return type
     */
    public function getValue() {
        return $this->values;
    }

    public function setOperator($operator) {
        $this->operator = $operator;
        return $this;
    }

    public function getOperator() {
        return $this->operator;
    }

    public function setBindable($bindable = true) {
        $this->bindable = $bindable;
    }

    public function getBindable() {
        return $this->bindable;
    }

    public function process() {
        $this->bind_params = [];
        $this->counter_bind_params = 0;
        $values = $this->getValue();

        $return = (object) [
                    'negate' => $this->getNegateString(),
                    'column' => $this->getColumn(),
                    'operator' => $this->getOperator(),
                    'values' => $this->getValue()
        ];

        if (is_array($this->getValue())) {
            $newvalues = [];
            foreach ($this->getValue() as $value) {
                $newvalues[] = $this->addBindParam($this->getColumn(), $value);
            }
            $return->values = implode(',', $newvalues);
        } else if (is_scalar($this->getValue())) {
            $return->values = $this->addBindParam($this->getColumn(), $values);
        } else if ($values instanceof Statement) {
            $statement = $values->getQueryAndParams();
            $return->values = sprintf('(%s)', trim($statement->query));
            $this->bind_params = array_merge($this->bind_params, $statement->params);
        }
        else {
            throw new \Exception('Valore non interepretabile');
        }
        return $return;
    }

    public function getExpression() {
        if ($this->bindable === false) {
           return sprintf($this->formattedString, $this->getNegateString(), $this->getColumn(), $this->getOperator(), $this->getValue());
        }
        $strings = array_values((array) $this->process());
        return vsprintf($this->formattedString, $strings);
    }

    public function getBindParams() {
        return $this->bind_params;
    }

    public function addBindParam($name, $value) {
        $bindname = str_replace('.','__',sprintf(':%s_%s_%s', $this->uniqid, $this->counter_bind_params++, $name));
        $this->bind_params[$bindname] = $value;
        return $bindname;
    }

    public function getPrefixParam() {
        
    }

    public function setPrefixParam($prefix = '') {
        
    }

}
