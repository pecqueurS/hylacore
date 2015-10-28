<?php

namespace Hyla\Db;
use Hyla\Config\Conf;

/**
 * Class Db
 * @package Hyla\Db
 */
class Db {

    const STEP = 1000;

    protected $pdo = array();

    protected $rows = array();

    protected static $instance;

    /**
     * @param null $config
     * @return Db
     */
    public static function getInstance($config = null)
    {
        if ($config !== null || !(self::instance instanceof Db)) {
            self::$instance = new Db($config);
        }

        return self::$instance;
    }


    /**
     * @param array|null $config
     */
    public function __construct($config = null)
    {
        $dbConf = $this->getConfig($config);


        $dns = "{$dbConf['type']}:dbname={$dbConf['dbname']};host={$dbConf['server']};port={$dbConf['port']};charset={$dbConf['charset']}";
        $this->pdo = new \PDO($dns, $dbConf['username'], $dbConf['password'], array(\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'' . $dbConf['charset'] . '\''));

        $test = $this->pdo->query("SELECT * FROM `test`");

        var_dump($test->fetchAll());
    }


    /**
     * @param null $config
     * @return mixed|null
     * @throws \Exception
     */
    protected function getConfig($config = null)
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


    public function addRow($row)
    {
        $this->rows[] = $row;
    }


    public function stepping(array $rows = array())
    {
        $steppingRows = array();
        for ($i = 0; $i < count($rows); $i++) {
            $steppingRows[(int) ($i/self::STEP)][] = $rows[$i];
        }

        return $steppingRows;
    }


    public function createRows(array $fields, array $rows)
    {
        $assotiativeRows = array();
        foreach ($rows as $row) {
            $assotiativeRows[] = array_combine($fields, $row);
        }

        return $assotiativeRows;
    }
}
