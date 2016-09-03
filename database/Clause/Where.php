<?php

namespace smn\pheeca\Database\Clause;

use \smn\pheeca\Database\Clause;
use \smn\pheeca\Database\BindableClauseInterface;
use \smn\pheeca\Database\StatementException;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Where
 *
 * @author Simone Esposito
 */
class Where extends Clause implements BindableClauseInterface {

    protected $_name = 'where';
    protected $_clause = 'WHERE';
    protected $bind_params = array();
    protected $replacement_counter = 0;
    protected $uniqid = '';

    public function __construct($condition = array(), $prefix = '', $suffix = '') {
        parent::__construct([
            'prefix' => $prefix,
            'data' => $condition,
            'suffix' => $suffix]
        );

        $this->uniqid = uniqid();
    }

    private function createPredicate($condition) {
        $default = [
            'column' => '',
            'logic' => \smn\pheeca\Database\Predicate::OPERATOR_EQUAL_TO,
            'conjunction' => '',
            'negate' => false,
            'bindable' => true,
            'negate' => false
        ];

        $condition = (object) array_merge($default, $condition); // ma che cast fai !!

        return new \smn\pheeca\Database\Predicate($condition->column, $condition->value, $condition->logic);
    }

    public function processFields() {
        $string = '';
        
        $predicate = new \smn\pheeca\Database\Predicate($this->getData());
        $string .= $predicate->getSqlString();
        $this->bind_params = array_merge($this->bind_params, $predicate->getBindParams());
        
        
//        foreach ($this->getData() as $where) {
//            if ((is_array($where)) && (array_keys($where) == range(0, (count($where) - 1)))) {
//                $predicate = new \smn\pheeca\Database\Predicate($where);
//                $string .= $predicate->getSqlString();
//                $this->bind_params = array_merge($this->bind_params, $predicate->getBindParams());
//            } else if (is_array($where)) {
//                $predicate = new \smn\pheeca\Database\Predicate([$where]);
//                $string .= $predicate->getSqlString();
//                $this->bind_params = array_merge($this->bind_params, $predicate->getBindParams());
//            }
//            else if ($where instanceof \smn\pheeca\Database\Predicate) {
//                $string .= $where->getSqlString();
//                $this->bind_params = array_merge($this->bind_params, $where->getBindParams());
//            }
//        }
            $this->_fields = trim($string);
    }

    public function setData($data) {
        $this->bind_params = array();
        $this->replacement_counter = 0;
        parent::setData($data);
    }

    /**
     * Aggiunge una condizione (Predicate) alla clausola
     * @param String $column Nome della colonna
     * @param Mixed $value Valore (Expression)
     * @param String|\smn\pheeca\Database\Operator $logic Logica da applicare (default = '=')
     * @param String $conjunction Congiunzione da aggiungere (default = '')
     * @param Boolean $bindable Se il valore è da bindare o meno
     * @param Boolean $negate Se il Predicato va negato
     * @param Boolean $nested Se il Predicato va tra parentesi o meno
     */
    public function addCondition($column, $value, $logic = \smn\pheeca\Database\Operator::OPERATOR_EQUAL, $conjunction = '', $bindable = true, $negate = false, $nested = false) {
        $condition = [
            [
                'column' => $column,
                'value' => $value,
                'logic' => $logic,
                'bindable' => $bindable,
                'negate' => $negate
            ]
        ];
        $data = $this->getData();
        if (count($data) > 0) {
            end($data);
            $data[key($data)]['conjunction'] = $conjunction;
            reset($data);
            $data = ($nested === true) ? array_merge($data, [$condition]) : array_merge($data, $condition);
        } else {
            $data = $condition;
        }
        $this->setData($data);
        // così ho messo la congiunzione nell'ultimo Predicato
        // poi aggiungo alla fine la nuova condizione
    }

    public function addAnd($column, $value, $logic = \smn\pheeca\Database\Operator::OPERATOR_EQUAL, $bindable = true, $negate = false) {
        $this->addCondition($column, $value, $logic, 'AND', $bindable, $negate);
    }

    public function addOr($column, $value, $logic = \smn\pheeca\Database\Operator::OPERATOR_EQUAL, $bindable = true, $negate = false) {
        $this->addCondition($column, $value, $logic, 'OR', $bindable, $negate);
    }

    public function addAndNested($column, $value, $logic = \smn\pheeca\Database\Operator::OPERATOR_EQUAL, $bindable = true, $negate = false) {
        $this->addCondition($column, $value, $logic, 'AND', $bindable, $negate, true);
    }

    public function addOrNested($column, $value, $logic = \smn\pheeca\Database\Operator::OPERATOR_EQUAL, $bindable = true, $negate = false) {
        $this->addCondition($column, $value, $logic, 'OR', $bindable, $negate, true);
    }

    /**
     * 
     * @param type $whereCondition
     * @param type $conjunction
     * @param type $negate
     * @deprecated since version number
     */
    public function addConjunction($whereCondition, $conjunction = 'AND', $negate = false) {
        if (!empty($whereCondition)) {
            $data = $this->getData();
            end($data);
            $data[key($data)]['conjunction'] = $conjunction;
            $data[key($data)]['negate'] = $negate;
            reset($data);
//            foreach ($whereCondition as $where) {
//                array_push($data, $where);
//            }
            array_push($data, $whereCondition);
            $this->setData($data);
        }
    }

    public function getBindParams() {
        return $this->bind_params;
    }

    public function addBindParam($name, $value) {
        return false;
    }

    public function getPrefixParam() {
        return false;
    }

    public function setPrefixParam($param = '') {
        return false;
    }

}
