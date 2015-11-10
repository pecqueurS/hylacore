<?php
namespace Hyla\Orm;

/**
 * Class Unit
 * @package Hyla\Orm
 */
class Unit {

    private $row;
    private $primaryKey;
    private $isNew = false;

    public function __construct(array $fields = array(), $isNew = false)
    {
        $this->isNew = $isNew;
        foreach ($fields as $field) {
            if ($field === 'id') {
                $this->primaryKey = 'id';
            }
            $this->row[$field] = null;
        }

        if ($this->primaryKey === null) {
            foreach ($fields as $field) {
                $this->primaryKey = $field;
                break;
            }
        }
    }

    public function toArray()
    {
        return $this->row;
    }

    public function __set($field, $value)
    {
        $this->row[$field] = $value;
    }

    public function __get($field)
    {
        if (isset($this->row[$field])) {
            return $this->row[$field];
        }

        return null;
    }

    public function setAll(array $row)
    {
        $this->row = $row;
    }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    public function setPrimaryKey($field)
    {
        if (isset($this->row[$field])) {
            $this->primaryKey = $field;
        }
    }
}
