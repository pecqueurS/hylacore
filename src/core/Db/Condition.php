<?php

namespace Hyla\Db;

/**
* Class Condition
 * @package Hyla\Db
*/
class Condition {

    const LOGIC_AND = 'AND';

    const LOGIC_OR = 'OR';

    const EQUAL = '=';
    const NOT_EQUAL = '!=';
    const GREATER_THAN = '>';
    const GREATER_EQUAL = '>=';
    const LESS_THAN = '<';
    const LESS_EQUAL = '<=';
    const LIKE = "LIKE";
    const NOT_LIKE = "NOT LIKE";
    const IS_NULL = "IS NULL";
    const IS_NOT_NULL = "IS NOT NULL";
    const IN = "IN";
    const NOT_IN = "NOT IN";

    private $field;

    private $value;

    private $operator;

    private $logicOperator;

    private $escapeValueLikeField;

    public function __construct($field, $value, $operator = self::EQUAL, $logicOperator = self::LOGIC_AND, $escapeValueLikeField = false)
    {
        $this->field = $field;
        $this->value = $value;
        $this->operator = $operator;
        $this->logicOperator = $logicOperator;
        $this->escapeValueLikeField = $escapeValueLikeField;
    }


    public function getSql($first = false)
    {
        $prepend = ' ';
        if (!$first) {
            $prepend .= "{$this->logicOperator} ";
        }

        return $prepend . "{$this->getField()} {$this->operator} {$this->getValue()}";
    }


    private function getField()
    {
        $fields = QueryBuilder::escapeFields(array($this->field));
        return array_shift($fields);
    }


    private function getValue()
    {
        $method = $this->escapeValueLikeField ? 'escapeFields' : 'escapeValues';
        switch ($this->operator) {
            case self::EQUAL:
            case self::NOT_EQUAL:
            case self::GREATER_EQUAL:
            case self::GREATER_THAN:
            case self::LESS_EQUAL:
            case self::LESS_THAN:
            case self::LIKE:
            case self::NOT_LIKE:
                if(is_array($this->value)) {
                    $value = array_shift($this->value);
                } else {
                    $value = $this->value;
                }
                $values = QueryBuilder::$method(array((string) $value));

                return array_shift($values);
            case self::IS_NULL:
            case self::IS_NOT_NULL:
                return '';
            case self::IN:
            case self::NOT_IN:

                return '(' . implode(',', QueryBuilder::$method((array) $this->value)) . ')';
        }

        return '';
    }
}

