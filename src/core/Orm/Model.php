<?php
namespace Hyla\Orm;
use Hyla\Db\QueryBuilder;

/**
 * Class Model
 * @package Hyla\Orm
 */
class Model {

    protected $table;

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function select($fields = '*', $conditions = null, $joins = null, $orderBy = null, $groupBy = null, $limit = null)
    {
        $query = $this->prepareQuery($fields, $conditions, $joins, $orderBy, $groupBy, $limit);

    }

    public function save($rows = null)
    {

    }

    public function remove()
    {

    }

    private function prepareQuery($fields = '*', $conditions = null, $joins = null, $orderBy = null, $groupBy = null, $limit = null)
    {
        $query = new QueryBuilder($this->table);
        if ($fields === '*') {
            $fields = array($fields);
        }
        $query->addFields($fields);

        if ($conditions !== null) {
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