<?php
namespace Hyla\Orm\Drivers;
use Hyla\Db\Condition;
use Hyla\Db\Db;
use Hyla\Db\QueryBuilder;
use Hyla\Orm\Unit;

/**
 * Class ModelApi
 * @package Hyla\Orm\Drivers
 */
class ModelApi {

    protected $url;

    protected $method;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function select($fields = '*', $conditions = null, $joins = null, $orderBy = null, $groupBy = null, $limit = null)
    {

    }

    public function update(array $values, $conditions = null)
    {

    }

    public function saveRows($rows = null)
    {

    }

    public function remove($conditions = null)
    {

    }

    public function removeRows($rows = null)
    {

    }


}
