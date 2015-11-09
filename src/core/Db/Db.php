<?php

namespace Hyla\Db;
use Hyla\Config\Conf;
use Hyla\Logger\Logger;

/**
 * Class Db
 * @package Hyla\Db
 */
class Db {

    const STEP = 1000;

    protected $pdo;
    protected $pdoStmt;
    protected $query;

    protected $isPreparedQuery = false;
    protected $type;

    protected static $instance;

    /**
     * @param array|null $config
     * @return Db
     */
    public static function getInstance(array $config = null)
    {
        if ($config !== null || !(self::instance instanceof Db)) {
            self::$instance = new Db($config);
        }

        return self::$instance;
    }


    /**
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        $dbConf = $this->getConfig($config);

        $dns = "{$dbConf['type']}:dbname={$dbConf['dbname']};host={$dbConf['server']};port={$dbConf['port']};charset={$dbConf['charset']}";
        $this->pdo = new \PDO($dns, $dbConf['username'], $dbConf['password'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $dbConf['charset'] . '\''));
    }


    /**
     * @param $query
     * @param string $type
     * @param array|null $rows
     * @param bool|false $isPreparedQuery
     * @throws \Exception
     */
    public function prepare($query, $type = QueryBuilder::SELECT, array $rows = null, $isPreparedQuery = false)
    {
        if ($query instanceof QueryBuilder) {
            $this->query = $query->build($type, $rows);
        } else {
            $this->query = $query;
        }
        Logger::log($this->query, Logger::DEBUG);
        $this->type = $type;
        $this->isPreparedQuery = $isPreparedQuery;
        if ($this->isPreparedQuery) {
            $this->pdoStmt = $this->pdo->prepare($this->query);
        }
    }


    /**
     * @param array $params
     * @return array|int
     */
    public function execute(array $params = array())
    {
        if ($this->isPreparedQuery) {
            foreach ($params as $key => $parameter) {
                $this->pdoStmt->bindParam(
                    $key,
                    $parameter['variable'],
                    isset($parameter['data_type']) ? $parameter['data_type'] : null,
                    isset($parameter['length']) ? $parameter['length'] : null
                );
            }
            $this->pdoStmt->execute();
        } elseif ($this->type === QueryBuilder::SELECT) {
            $this->pdoStmt = $this->pdo->query($this->query);
        } else {
            return $this->pdo->exec($this->query);
        }

        return $this->fetch();
    }


    /**
     * @param array|null $config
     * @return array|mixed
     * @throws \Exception
     */
    protected function getConfig(array $config = null)
    {
        if ($config === null) {
            $config = Conf::get('db');
        }

        if (empty($config['dbname'])
            || empty($config['server'])
            || empty($config['username'])
            || empty($config['password'])
        ) {
            throw new \Exception('db config dismiss');
        }

        if (empty($config['charset'])) {
            $config['charset'] = 'UTF8';
        }

        if (empty($config['port'])) {
            $config['port'] = '3306';
        }

        return $config;
    }


    /**
     * @return array
     */
    protected function fetch()
    {
        $result = array();
        while($row = $this->pdoStmt->fetch()) {
            if (isset($row['id'])) {
                $result[$row['id']] = $row;
            } else {
                $result[] = $row;
            }
        }
        $this->pdoStmt->closeCursor();

        return $result;
    }


    /**
     * @param array $rows
     * @return array
     */
    static public function chunk(array $rows = array())
    {
        return array_chunk($rows, self::STEP);
    }


    /**
     * @param array $fields
     * @param array $rows
     * @return array
     */
    static public function createRows(array $fields, array $rows)
    {
        $assotiativeRows = array();
        foreach ($rows as $row) {
            $assotiativeRows[] = array_combine($fields, $row);
        }

        return $assotiativeRows;
    }
}
