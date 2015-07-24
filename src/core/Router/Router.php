<?php

namespace Hyla\Router;
use Hyla\Config\Conf;

/**
 * Class Router
 * @package Hyla\Router
 */
abstract class Router {

    const ROUTING_PATH = 'etc/routing/';

    private static $appConf;

    public static function init()
    {
        self::loadRoutes();

    }


    private static function loadRoutes()
    {
        self::$appConf = Conf::get('app');
        if (!empty(self::$appConf)) {
            $files = self::loadFiles();
            if (empty($files)) {
                Conf::set('route', []);
            } else {
                foreach ($files as $file) {
                    Conf::add($file, 'route', true);
                }
            }

        } else {
            Conf::set('route', []);
        }
    }


    private static function loadFiles()
    {
        $path = self::$appConf['path'] . '/' . self::ROUTING_PATH;
        $allFiles = scandir(self::$appConf['root'] . $path);

        $files = array();
        foreach ($allFiles as $file) {
            if (strpos($file, 'ini') !== false && is_readable(self::$appConf['root'] . $path . $file)) {
                $files[] = $path . $file;
            }
        }

        return $files;
    }
}
