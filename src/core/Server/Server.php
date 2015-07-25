<?php

namespace Hyla\Server;
use Components\Tools\StringCase;

/**
 * Class Session
 * @package Hyla\Session
 */
abstract class Server {

    public static function get($name)
    {
        $key = StringCase::camelToScreamingSnake($name);
        if (!empty($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        return null;
    }


    /**
     * @return array
     */
    public static function getAll()
    {
        return $_SERVER;
    }
}
