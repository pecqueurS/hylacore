<?php

namespace Hyla\Db;

/**
* Class OrderBy
 * @package Hyla\Db
*/
class OrderBy {

    const ASC = 'ASC';
    const DESC = 'DESC';

    private $field;
    private $order;

    /**
     * @param string $field
     * @param string $order
     */
    public function __construct($field, $order = self::ASC)
    {
        $field = QueryBuilder::escapeFields(array($field));
        $this->field = array_shift($field);
        $this->order = $order;
    }


    /**
     * @param bool|false $first
     * @return string
     */
    public function getSql($first = false)
    {
        $prepend = $first ? ' ORDER BY ' : ', ';

        return "$prepend {$this->field} {$this->order}";
    }
}

