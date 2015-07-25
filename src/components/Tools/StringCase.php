<?php

namespace Components\Tools;


abstract class StringCase {


    /**
     * @param string $snake
     * @return string
     */
    public static function snakeToCamel($snake)
    {
        $snakeArray = explode('_', strtolower($snake));
        $camel = '';
        for ($i = 0; $i < count($snakeArray); $i++) {
            $camel .= $i === 0 ? $snakeArray[$i] : ucfirst($snakeArray[$i]);
        }
        return $camel;
    }


    /**
     * @param string $snake
     * @return string
     */
    public static function snakeToScreamingSnake($snake)
    {
        return strtoupper($snake);
    }


    /**
     * @param string $camel
     * @return string
     */
    public static function camelToSnake($camel)
    {
        if (preg_match_all('/[A-Z][a-z]*/', $camel, $matches) && !empty($matches[0])) {
           return strtolower(implode('_', $matches[0]));
        }
        return $camel;
    }


    /**
     * @param string $camel
     * @return string
     */
    public static function camelToScreamingSnake($camel)
    {
        return self::snakeToScreamingSnake(self::camelToSnake($camel));
    }


    /**
     * @param string $screamingSnake
     * @return string
     */
    public static function screamingSnakeToSnake($screamingSnake)
    {
        return strtolower($screamingSnake);
    }


    /**
     * @param string $screamingSnake
     * @return string
     */
    public static function screamingSnakeToCamel($screamingSnake)
    {
        return self::snakeToCamel(self::screamingSnakeToSnake($screamingSnake));
    }
}