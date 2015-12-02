<?php
namespace Hyla\Orm;

use Hyla\Orm\Drivers\ModelApi;
use Hyla\Orm\Drivers\ModelDb;

class Cluster {
    const API = 'API';
    const DB = 'DB';

    protected $model;
    protected $type;
    protected $table;
    protected $url;

    public function __construct()
    {
        switch ($this->type) {
            case self::API:
                $this->model = new ModelApi($this->url);
                break;
            case self::DB:
                $this->model = new ModelDb($this->table);
                break;
        }
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->model,$name)) {
            return call_user_func_array(array($this->model,$name), $arguments);
        } else {
            throw new \Exception('unknown method : ' . $name);
        }
    }
}
