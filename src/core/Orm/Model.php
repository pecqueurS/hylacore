<?php
namespace Hyla\Orm;

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
}