<?php

namespace Hyla\Db;

/**
* Class Condition
 * @package Hyla\Db
*/
class GroupCondition {

    const LOGIC_AND = 'AND';

    const LOGIC_OR = 'OR';

    private $logicOperator;

    private $conditions;

    public function __construct(array $conditions, $logicOperator = self::LOGIC_AND)
    {
        $this->conditions = $conditions;
        $this->logicOperator = $logicOperator;
    }


    public function getSql($first = false)
    {
        $prepend = ' ';
        if (!$first) {
            $prepend .= "{$this->logicOperator} ";
        }

        return $prepend . "({$this->getConditions()})";
    }


    private function getConditions()
    {
        $conditions = '';
        foreach ($this->conditions as $condition) {
            if (is_array($condition)) {
                $condition = self::createConditionClass($condition);
            }
            if (!($condition instanceof Condition) && !($condition instanceof GroupCondition)) {
                continue;
            }

            $conditions .= $condition->getSql($conditions === '');
        }

        return $conditions;
    }


    public static function createConditionClass(array $condition)
    {
        if (isset($condition['conditions'])) {
            $condition = new GroupCondition(
                $condition['conditions'],
                isset($condition['logic_operator']) ? $condition['logic_operator'] : Condition::LOGIC_AND
            );
        } else {
            $condition = new Condition(
                $condition['field'],
                $condition['value'],
                isset($condition['operator']) ? $condition['operator'] : Condition::EQUAL,
                isset($condition['logic_operator']) ? $condition['logic_operator'] : Condition::LOGIC_AND,
                isset($condition['escape_value_like_field']) ? $condition['escape_value_like_field'] : false
            );
        }

        return $condition;
    }
}

