<?php

namespace Hyla\Config;

/**
 * Class BaseConfig
 * @package Hyla\Config
 */
abstract class BaseConfig {

    const CLI = 'cli';
    const GUI = 'gui';

    public static $isCli;

    /**
     * @var string root dir project
     */
    public static $rootDir;

    /**
     * @var array config vars
     */
    protected static $conf = [];

    /**
     * Add conf file
     * @param string $pathFile
     * @param string|null $fieldName
     */
    public static function add($pathFile, $fieldName = null, $merge = false)
    {
        if (self::$rootDir === null) {
            self::$rootDir = __DIR__ . '/../../../';
        }

        static::loadFile($pathFile, $fieldName, $merge);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        $keys = explode('.', $key);
        $value = static::$conf;
        foreach ($keys as $levelKey) {
            if (isset($value[$levelKey])) {
                $value = $value[$levelKey];
            } else {
                return null;
            }
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        static::$conf[$key] = $value;
        return static::$conf[$key];
    }

    /**
     * @return array
     */
    public static function getAll()
    {
        return static::$conf;
    }

    /**
     * @param array $conf
     */
    protected static function addConf(array $conf)
    {
        static::$conf = array_merge(static::$conf, $conf);
    }

    /**
     * @param array $conf
     */
    protected static function mergeConf(array $conf)
    {
        foreach ($conf as $key => $value) {
            if (!empty(static::$conf[$key])) {
                static::$conf[$key] = array_merge(static::$conf[$key], $value);
            } else {
                static::$conf[$key] = $value;
            }
        }
    }

    /**
     * @param string $path
     * @param string|null $fieldName
     */
    protected static function loadFile($path, $fieldName = null, $merge = false)
    {
        if (file_exists(self::$rootDir . $path)) {
            $data = parse_ini_file(self::$rootDir . $path, true);
            if ($merge) {
                static::mergeConf($fieldName !== null ? array($fieldName => $data) : $data);
            } else {
                static::addConf($fieldName !== null ? array($fieldName => $data) : $data);
            }

        }
    }
}
