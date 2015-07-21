<?php

namespace Hyla\Config;

/**
 * Class Conf
 * @package Hyla\Config
 */
abstract class Conf extends BaseConfig {

    /**
     * add app conf
     */
    public static function initApp()
    {
        static::add('etc/app.ini', 'app');
        $foundApp = false;
        foreach (static::get('app') as $appName => $app) {
            $serverNameIndex = array_search($_SERVER['SERVER_NAME'], $app['serverName']);
            $serverNameIndex = $serverNameIndex === false ? array_search($_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'], $app['serverName']) : $serverNameIndex;
            if (false !== $serverNameIndex) {
                $app['serverName'] = $app['serverName'][$serverNameIndex];
                $app['name'] = $appName;
                $app['href'] = (!empty($app["protocole"]) ? $app["protocole"] : 'http') . '://' . $app['serverName'] . '/';
                $app['root'] = static::$rootDir;
                static::set('app', $app);
                $foundApp = true;
            }
        }
        if (!$foundApp) {
            static::set('app', []);
        }
    }


    public static function init()
    {
        $appConf = static::get('app');
        if (!empty($appConf)) {
            static::add($appConf['path'] . '/etc/config.ini');
            static::add($appConf['path'] . '/etc/private.ini');
        }
    }
}
