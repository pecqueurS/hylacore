<?php

namespace Hyla\Db;

/**
* Class Join
 * @package Hyla\Db
*/
class Join {

    const INNER = 'INNER JOIN';

    const OUTER = 'OUTER JOIN';

    const LEFT = 'LEFT JOIN';

    const LEFT_OUTER = 'LEFT OUTER JOIN';

    const RIGHT = 'RIGHT JOIN';

    const RIGHT_OUTER = 'RIGHT OUTER JOIN';

    private $table;

    private $on;

    private $type;

    public function __construct($table, array $on, $type = self::INNER)
    {
        $this->table = $table;
        $this->on = $on;
        $this->type = $type;
    }


    public function getSql()
    {
        return " {$this->type} . {$this->getTable()} ON ({$this->getConditions()}) ";
    }

    private function getTable()
    {
        $tables = QueryBuilder::escapeFields(array($this->table));
        return array_shift($tables);
    }

    private function getConditions()
    {
        $on = '';
        foreach ($this->on as $condition) {
            if (is_array($condition)) {
                $condition = GroupCondition::createConditionClass($condition);
            }
            if (!($condition instanceof Condition) && !($condition instanceof GroupCondition)) {
                continue;
            }

            $on .= $condition->getSql($on === '');
        }

        return $on;
    }
}

