<?php

namespace Hyla\Config;
use Hyla\Server\Server;

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
        static::$isCli = php_sapi_name() === static::CLI;

        foreach (static::get('app') as $appName => $app) {
            if (static::$isCli) {
                $argv = Server::get('argv');
                if ($argv !== null && !empty($argv[1])) {
                    if (strtolower($argv[1]) === strtolower($appName)) {
                        $app['serverName'] = $app['serverName'][0];
                        $app['name'] = $appName;
                        $app['href'] = $argv[0];
                        $app['root'] = static::$rootDir;
                        static::set('app', $app);
                        $foundApp = true;
                        break;
                    }
                } else {
                   break;
                }
            } else {
                $serverNameIndex = array_search(Server::get('serverName'), $app['serverName']);
                $serverNameIndex = $serverNameIndex === false ? array_search(Server::get('serverName') . ':' . Server::get('serverPort'), $app['serverName']) : $serverNameIndex;

                if (false !== $serverNameIndex) {
                    $app['serverName'] = $app['serverName'][$serverNameIndex];
                    $app['name'] = $appName;
                    $app['href'] = (!empty($app["protocole"]) ? $app["protocole"] : 'http') . '://' . $app['serverName'] . '/';
                    $app['root'] = static::$rootDir;
                    static::set('app', $app);
                    $foundApp = true;
                    break;
                }
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
        } else {
            throw new \Exception('App not found !');
        }
    }
}
