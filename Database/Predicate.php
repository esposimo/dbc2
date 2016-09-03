<?php

namespace smn\pheeca\Database;

/**
 * Description of Predicate
 *
 * @author Simone Esposito
 */
class Predicate extends Clause implements BindableClauseInterface, PrintableStatementInterface {

    protected $bind_params = array();
    protected $uniqid = '';
    protected $bind_counter = 0;

    const OPERATOR_EQUAL_TO = '=';
    const OPERATOR_LESS_THAN = '<';
    const OPERATOR_LESS_EQ_THAN = '<=';
    const OPERATOR_GREATER_THAN = '>';
    const OPERATOR_GREATER_EQ_THAN = '=>';
    const OPERATOR_NOT_EQUAL = '!=';
    const OPERATOR_IN = 'IN';
    const OPERATOR_BETWEEN = 'BETWEEN';
    const OPERATOR_LIKE = 'LIKE';

    protected $operators = array('');

    public function __construct($conditions) {
        parent::__construct([
            'prefix' => '',
            'suffix' => '',
            'data' => $conditions
        ]);
        $this->setPrefixParam(uniqid());
    }

    public function addBindParam($name, $value) {
        $bindname = str_replace('.','__',sprintf(':%s_%s_%s', $this->getPrefixParam(), $this->bind_counter++, $name));
        $this->bind_params[$bindname] = $value;
        return $bindname;
    }

    public function setData($data) {
        $this->bind_params = array();
        $this->bind_counter = 0;
        parent::setData($data);
    }

    private function analyzeCondition($condition) {

        $default = [
            'logic' => self::OPERATOR_EQUAL_TO,
            'conjunction' => '',
            'negate' => false,
            'bindable' => true,
            'negate' => false
        ];


        if ((!array_key_exists('column', $condition)) || (!array_key_exists('value', $condition))) {
            throw new \Exception('index column and index value are mandatory');
        }

        $condition = (object) array_merge($default, $condition); // ma che cast fai !!
        $column = $condition->column;
        $value = $condition->value;
        $logic = $condition->logic;
        $conjunction = $condition->conjunction;
        $negate = $condition->negate;
        $bindable = $condition->bindable;

        // se è una stringa, uso la Operator di base
        if (is_string($logic)) {
            $logic = new Operator($column, $logic, $value, $bindable);
        }
        // se è una istanza di Operator, gli passo colonna, value, e bindable
        else if ($logic instanceof Operator) {
            $logic->setColumn($column);
            $logic->setValue($value);
            $logic->setBindable($bindable);
        }

        $string = sprintf('%s %s', ($negate) ? 'NOT' : '', $logic->getExpression());
        $this->bind_params = array_merge($this->bind_params, $logic->getBindParams());
        return sprintf('%s %s ', $string, $conjunction);
    }

    public function processFields() {
        $cond = $this->getData();
        $string = '';
        $x = 0;
        $count = count($cond);
        foreach ($cond as $key => $condition) {
            $x++;
            if ($condition instanceof Predicate) {
                $string .= $condition->getSqlString();
                $this->bind_params = array_merge($this->bind_params, $condition->getBindParams());
            } else if ((is_array($condition)) && (array_keys($condition) == range(0, (count($condition) - 1)))) {
                $last_condition = end($condition);
                $last_conjunction = '';
                if (array_key_exists('conjunction', $last_condition)) {
                    $last_conjunction = $last_condition['conjunction'];
                    unset($condition[key($condition)]['conjunction']);
                }
                // queste righe sopra sono veramente brutte :D
                $predicate = new Predicate($condition);
                $string .= sprintf('(%s) %s', trim($predicate->getSqlString()), $last_conjunction);
                $this->bind_params = array_merge($this->bind_params, $predicate->getBindParams());
            } else {
//                $last_conjunction = (($x == $count) && ($condition['conjunction'])) ? $condition['conjunction']
                $string .= $this->analyzeCondition($condition);
            }
        }
        $this->_fields = $string;
    }

    public function getBindParams() {
        return $this->bind_params;
    }

    public function getPrefixParam() {
        return $this->uniqid;
    }

    public function setPrefixParam($prefix = '') {
        $this->uniqid = $prefix;
    }

    public function getSqlString() {
        return $this->toString();
    }

}
