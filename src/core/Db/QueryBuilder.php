<?php

namespace Hyla\Db;

/**
 * Class QueryBuilder
 * @package Hyla\Db
 */
class QueryBuilder {

    /**
     * Build type
     */
    const SELECT = 'SELECT';
    const INSERT = 'INSERT';
    const UPDATE = 'UPDATE';
    const DELETE = 'DELETE';

    /**
     * Escape type
     */
    const ESCAPE_FIELD = '`';
    const ESCAPE_VALUE = '\'';

    /**
     * query elements
     */
    protected $fields = array();
    protected $table = '';
    protected $joins = array();
    protected $conditions = array();
    protected $orderBy = array();
    protected $groupBy = array();
    protected $limit = array(
        'limit' => null,
        'offset' => null
    );


    /**
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
    }


    /**
     * @param array $fields
     */
    public function addFields(array $fields)
    {
        $this->fields = array_merge($this->fields, $fields);
    }


    /**
     * @param array $joins
     */
    public function addJoins(array $joins)
    {
        $this->joins = array_merge($this->joins, $joins);
    }


    /**
     * @param array $conditions
     */
    public function addConditions(array $conditions)
    {
        $this->conditions = array_merge($this->conditions, $conditions);
    }


    /**
     * @param array $orderBy
     */
    public function addOrderBy(array $orderBy)
    {
        $this->orderBy = array_merge($this->orderBy, $orderBy);
    }


    /**
     * @param string $groupBy
     */
    public function addGroupBy($groupBy)
    {
        $this->groupBy[] = $groupBy;
    }


    /**
     * @param int|null $limit
     * @param int|null $offset
     */
    public function addLimit($limit, $offset = null)
    {
        $this->limit['limit'] = $limit;
        $this->limit['offset'] = $offset;
    }


    /**
     * @param string $type
     * @param array $rows
     * @return string
     * @throws \Exception
     */
    public function build($type = self::SELECT, array $rows = array())
    {
        switch ($type) {
            case self::SELECT:
                $query = $this->select();
                break;
            case self::INSERT:
                $query = $this->insert($rows);
                break;
            case self::UPDATE:
                $query = $this->update($rows);
                break;
            case self::DELETE:
                $query = $this->delete();
                break;
            default:
                throw new \Exception('Type does not exist');
        }

        return trim($query);
    }


    /**
     * @return string
     */
    protected function select()
    {
        $query = "SELECT {$this->formatFields()}
                FROM `{$this->table}`
                {$this->formatJoins()}
                {$this->formatCondition()}
                {$this->formatGroupBy()}
                {$this->formatOrderBy()}
                {$this->formatLimit()};";

        return $query;
    }


    /**
     * @param array $rows
     * @return string
     */
    protected function insert(array $rows = array())
    {
        if (empty($rows)) {
            return '';
        }
        $this->addFields($this->retrieveFieldnames($rows));

        $query = "INSERT
                  INTO `{$this->table}`
                  ({$this->formatFields()})
                  VALUES
                  {$this->formatInsertValues($rows)};";

        return $query;
    }


    /**
     * @param array $rows
     * @return string
     */
    protected function update(array $rows = array())
    {
        if (empty($rows)) {
            return '';
        } elseif (count($rows) === 1 && !empty($this->conditions)) {
            $query = "UPDATE `{$this->table}`
                      SET {$this->formatSetValues($rows)}
                      {$this->formatCondition()};";
        } else {
            $fields = $this->retrieveFieldnames($rows);
            $query = "INSERT
                      INTO `{$this->table}`
                      ({$this->formatFields()})
                      VALUES
                      {$this->formatInsertValues($rows)}
                      ON DUPLICATE KEY UPDATE
                          {$this->formatDuplicateValues($fields)};";
        }

        return $query;
    }


    /**
     * @return string
     */
    protected function delete()
    {
        $query = "DELETE
                  FROM `{$this->table}`
                  {$this->formatCondition()}
                  {$this->formatLimit()};";

        return $query;
    }


    /**
     * @param array $rows
     * @return array
     */
    protected function retrieveFieldnames(array $rows)
    {
        $firstRow = array_shift($rows);
        if ($firstRow !== null) {
            return array_keys($firstRow);
        }
        return array();
    }


    /**
     * @return string
     */
    protected function formatFields()
    {
        return implode(',', static::escapeFields($this->fields));
    }


    /**
     * @return string
     */
    protected function formatJoins()
    {
        $joins = '';
        foreach ($this->joins as $join) {
            if (is_array($join)) {
                $join = new Join(
                    $join['table'],
                    $join['on'],
                    isset($join['type'])
                );
            }
            if (!($join instanceof Join)) {
                continue;
            }

            $joins .= $join->getSql($joins === '');
        }

        return $joins;
    }


    /**
     * @return string
     */
    protected function formatCondition()
    {
        $conditions = '';
        foreach ($this->conditions as $condition) {
            if (is_array($condition)) {
                $condition = GroupCondition::createConditionClass($condition);
            }
            if (!($condition instanceof Condition) && !($condition instanceof GroupCondition)) {
                continue;
            }

            $conditions .= $condition->getSql($conditions === '');
        }

        return ($conditions !== '' ? ' WHERE ': '') . $conditions;
    }


    /**
     * @return string
     */
    protected function formatGroupBy()
    {
        if (!empty($this->groupBy)) {
            return ' GROUP BY' . implode(',', self::escapeFields($this->groupBy));
        }

        return '';
    }


    /**
     * @return string
     */
    protected function formatOrderBy()
    {
        $orderBys = '';
        foreach ($this->orderBy as $orderBy) {
            if (is_array($orderBy)) {
                $orderBy = new OrderBy(
                    $orderBy['field'],
                    $orderBy['order']
                );
            }
            if (!($orderBy instanceof OrderBy)) {
                continue;
            }

            $orderBys .= $orderBy->getSql($orderBys === '');
        }

        return $orderBys;
    }


    /**
     * @return string
     */
    protected function formatLimit()
    {
        if ($this->limit['limit'] !== null) {
            return ' LIMIT ' . ($this->limit['offset'] !== null ? $this->limit['offset'] . ', ' : '') . $this->limit['limit'];
        }

        return '';
    }


    /**
     * @param array $rows
     * @return string
     */
    protected function formatSetValues(array $rows)
    {
        $row = array_shift($rows);
        $escapeValues = $this->escapeValues($row);
        $escapeFields = $this->escapeFields(array_keys($row));
        $setValuesArray = array_combine($escapeFields, $escapeValues);

        $setValues = array();
        foreach ($setValuesArray as $key => $value) {
            $setValues[] = ' ' . $key . ' = ' . $value;
        }

        return ' SET ' . implode(', ', $setValues);
    }


    /**
     * @param array $rows
     * @return string
     */
    protected function formatInsertValues(array $rows)
    {
        $insertValues = array();
        foreach($rows as $row) {
            $escapeValues = array_map(function($value) {
                return "'$value'";
            }, $row);
            $insertValues[] = implode(',', $escapeValues);
        }

        return '(' . implode('),(', $insertValues) . ')';
    }


    /**
     * @param array $fields
     * @return string
     */
    protected function formatDuplicateValues(array $fields)
    {
        $result = array();
        foreach ($fields as $field) {
            $result[] = "$field=VALUES($field)";
        }
        return implode(',', $result);
    }


    /**
     * @param array $values
     * @param string $escapeType
     * @return array
     */
    protected static function escape(array $values, $escapeType = self::ESCAPE_FIELD)
    {
        return array_map(function($element) use($escapeType) {
            if ($escapeType === QueryBuilder::ESCAPE_FIELD && $element === '*') {
                return $element;
            }

            if ($escapeType === QueryBuilder::ESCAPE_FIELD && strstr($element, ' ') !== false) {
                return $element;
            }

            if ($escapeType === QueryBuilder::ESCAPE_FIELD && strstr($element, '.') !== false) {
                $elements = explode('.', $element);
                foreach ($elements as &$elem) {
                    $elem = $escapeType . $elem . $escapeType;
                }

                return implode('.', $elements);
            }

            if ($escapeType === QueryBuilder::ESCAPE_VALUE && ($element[0] === ':' || $element[0] === '\'')) {
                return $element;
            }

            return $escapeType . $element . $escapeType;
        }, $values);
    }


    /**
     * @param array $fields
     * @return array
     */
    public static function escapeFields(array $fields)
    {
        return static::escape($fields);
    }


    /**
     * @param array $values
     * @return array
     */
    public static function escapeValues(array $values)
    {
        return static::escape($values, self::ESCAPE_VALUE);
    }
}
