<?php
namespace Hyla\Orm\Drivers;
use Hyla\Db\Condition;
use Hyla\Db\Db;
use Hyla\Db\QueryBuilder;
use Hyla\Orm\Unit;

/**
 * Class Model
 * @package Hyla\Orm\Drivers
 */
class ModelDb {

    protected $table;
    protected $db;

    protected $data;

    /**
     * @param string $table
     */
    public function __construct($table)
    {
        $this->table = $table;
        $this->db = Db::getInstance();
    }

    /**
     * @param mixed $fields
     * @param mixed $conditions
     * @param mixed $joins
     * @param mixed $orderBy
     * @param mixed $groupBy
     * @param mixed $limit
     * @return array
     */
    public function select($fields = '*', $conditions = null, $joins = null, $orderBy = null, $groupBy = null, $limit = null)
    {
        $query = $this->prepareSelectQuery($fields, $conditions, $joins, $orderBy, $groupBy, $limit);
        $this->db->prepare($query, QueryBuilder::SELECT);

        $data = $this->db->execute();
        $this->data = $this->formatRows($data);

        return $this->data;
    }

    /**
     * @param array $values
     * @param mixed $conditions
     * @return array|int
     */
    public function update(array $values, $conditions = null)
    {
        $query = new QueryBuilder($this->table);
        if ($conditions !== null) {
            $query->addConditions($conditions);
        }
        $this->db->prepare($query, QueryBuilder::UPDATE, array($values));

        return $this->db->execute();
    }

    /**
     * @param array|null $rows
     * @return array|int
     */
    public function saveRows($rows = null)
    {
        $rows = $this->formatDefaultRows($rows);
        $isInsert = true;
        $rowsArray = array();
        foreach ($rows as $row) {
            if ($row instanceof Unit) {
                $rowsArray[] = $row->toArray();
                if ($isInsert) {
                    $isInsert = $row->isNew();
                }
            } elseif (is_array($row)) {
                $rowsArray[] = $row;
                if ($isInsert) {
                    foreach ($row as $field => $value) {
                        if (!($field === 'id' && $value === null)) {
                            $isInsert = false;
                        }
                        break;
                    }
                }
            }
        }
        $query = new QueryBuilder($this->table);
        $this->db->prepare($query, $isInsert ? QueryBuilder::INSERT : QueryBuilder::UPDATE, $rowsArray);

        return $this->db->execute();
    }

    /**
     * @param mixed $conditions
     * @return array|int
     */
    public function remove($conditions = null)
    {
        $query = $this->prepareRemoveQuery($conditions);
        $this->db->prepare($query, QueryBuilder::DELETE);

        return $this->db->execute();
    }

    /**
     * @param mixed $rows
     * @return array|int
     */
    public function removeRows($rows = null)
    {
        $rows = $this->formatDefaultRows($rows);

        return $this->remove(array($this->createConditionInPrimaryKeys($rows)));
    }

    /**
     * @param mixed $rows
     * @return array
     */
    protected function formatDefaultRows($rows)
    {
        if ($rows === null && $this->data !== null) {
            $rows = $this->data;
        }
        if ($rows instanceof Unit) {
            $rows = array($rows);
        }

        return $rows;
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function createConditionInPrimaryKeys(array $rows)
    {
        $primaryKey = 'id';
        $primaryKeyValues = array();
        foreach ($rows as $row) {
            if ($row instanceof Unit) {
                $primaryKey = $row->getPrimaryKey();
                $primaryKeyValues[] = $row->getPrimaryKeyValue();
            } elseif (is_array($row)) {
                foreach ($row as $field => $value) {
                    $primaryKey = $field;
                    $primaryKeyValues[] = $value;
                    break;
                }
            } else {
                $primaryKeyValues[] = $row;
            }
        }

        return array('field' => $primaryKey,
            'value' => $primaryKeyValues,
            'operator' => Condition::IN
        );
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function formatRows($rows)
    {
        if (!(is_array($rows))) {
            return array();
        }

        $result = array();
        $fieldsName = $this->retrieveFieldsname($rows);
        foreach ($rows as $row) {
            $unit = new Unit($fieldsName);
            $unit->setAll($row);
            $result[$unit->getPrimaryKeyValue()] = $unit;
        }

        return $result;
    }

    /**
     * @param array $rows
     * @return array
     */
    protected function retrieveFieldsname(array $rows)
    {
        $firstRow = array_shift($rows);
        if ($firstRow !== null) {
            return array_keys($firstRow);
        }
        return array();
    }

    /**
     * @param mixed $conditions
     * @param mixed $limit
     * @return QueryBuilder
     */
    protected function prepareRemoveQuery($conditions = null, $limit = null)
    {
        $query = new QueryBuilder($this->table);
        if ($conditions !== null) {
            $query->addConditions($conditions);
        }

        if ($limit !== null) {
            $query->addLimit($limit);
        }

        return $query;
    }

    /**
     * @param string $fields
     * @param array|null $conditions
     * @param array|null $joins
     * @param array|null $orderBy
     * @param array|null $groupBy
     * @param array|null $limit
     * @return QueryBuilder
     */
    protected function prepareSelectQuery($fields = '*', $conditions = null, $joins = null, $orderBy = null, $groupBy = null, $limit = null)
    {
        $query = new QueryBuilder($this->table);
        if ($fields === '*') {
            $fields = array($fields);
        }
        $query->addFields($fields);

        if ($conditions !== null) {
            if (!is_array($conditions)) {
                $conditions = array($conditions);
            }
            $query->addConditions($conditions);
        }

        if ($joins !== null) {
            $query->addJoins($joins);
        }

        if ($orderBy !== null) {
            $query->addOrderBy($orderBy);
        }

        if ($groupBy !== null) {
            $query->addGroupBy($groupBy);
        }

        if ($limit !== null) {
            $limitToUse = isset($limit['limit']) ? $limit['limit'] : 1;
            $offset = isset($limit['offset']) ? $limit['offset'] : null;
            $query->addLimit($limitToUse, $offset);
        }

        return $query;
    }
}
