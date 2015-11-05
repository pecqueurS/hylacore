<?php

namespace Hyla\Db;

/**
* Class Join
 * @package Hyla\Db
*/
class OrderBy {

    const ASC = 'ASC';
    const DESC = 'DESC';

    private $field;
    private $order;

    public function __construct($field, $order = self::ASC)
    {
        $field = QueryBuilder::escapeFields(array($field));
        $this->field = array_shift($field);
        $this->order = $order;
    }


    public function getSql($first = false)
    {
        $prepend = $first ? ' ORDER BY ' : ', ';

        return "$prepend {$this->field} {$this->order}";
    }
}

