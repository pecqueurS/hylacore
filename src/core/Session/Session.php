<?php

namespace Hyla\Session;

/**
 * Class Session
 * @package Hyla\Session
 */
abstract class Session {

    public static function init()
    {
        session_start();
    }

    public static function erase()
    {
        session_unset();
    }

    public static function getId()
    {
        return session_id();
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
        return $_SESSION[$key];
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        $keys = explode('.', $key);
        $value = $_SESSION;
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
     * @return array
     */
    public static function getAll()
    {
        return $_SESSION;
    }


}
