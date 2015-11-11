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

    /**
     * @param array $fields
     * @param bool|false $isNew
     */
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

    /**
     * @return array|null
     */
    public function toArray()
    {
        return $this->row;
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    public function __set($field, $value)
    {
        $this->row[$field] = $value;
    }

    /**
     * @param string $field
     * @return mixed
     */
    public function __get($field)
    {
        if (isset($this->row[$field])) {
            return $this->row[$field];
        }

        return null;
    }

    /**
     * @param array $row
     */
    public function setAll(array $row)
    {
        $this->row = $row;
    }

    /**
     * @return mixed
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * @param mixed $field
     */
    public function setPrimaryKey($field)
    {
        if (isset($this->row[$field])) {
            $this->primaryKey = $field;
        }
    }

    /**
     * @return mixed
     */
    public function getPrimaryKeyValue()
    {
        if (isset($this->row[$this->primaryKey])) {
            return $this->row[$this->primaryKey];
        } else {
            return array_values($this->row)[0];
        }
    }

    /**
     * @return bool|false
     */
    public function isNew()
    {
        return $this->isNew;
    }
}
