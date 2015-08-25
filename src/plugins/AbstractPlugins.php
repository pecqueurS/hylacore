<?php

namespace HylaPlugins;

/**
 * Class AbstractPlugins
 * @package HylaPlugins
 */
abstract class AbstractPlugins {

    public static function launch(array $data = null)
    {
        static::init($data);
        static::display();
        return static::execute();

    }


    protected static function init(array $data = null)
    {

    }


   protected static function display()
    {

    }


    protected static function execute()
    {
        return null;
    }
}
