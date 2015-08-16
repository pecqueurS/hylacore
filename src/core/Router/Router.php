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
        self::matchRoute();
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


    private static function matchRoute()
    {
        $routing = Conf::get('route');

        var_dump($routing);
        $test = microtime();
        $routes = array();
        $routingFormatted = array();
        foreach ($routing as $routeName => $routeTest) {
            $routeNameFormatted = substr($routeName, 0,32);
            $routes[] = '^(?<' . $routeNameFormatted . '>' . $routeTest['pattern'] . ')$';
            $routingFormatted[$routeNameFormatted] = $routeTest;
        }
        $testPattern = '#'.implode('|', $routes).'#';
        var_dump($testPattern);
        preg_match($testPattern, $routeUri, $argv);
        unset($argv[0]);
        $isFound = false;
        $results = array_filter($argv, function($value, $key) use (&$isFound) {
            $resultFilter = is_string($key) && $value != '';
            if (!$isFound && $resultFilter) {
                $isFound = true;
            } elseif ($isFound && $resultFilter) {
                $isFound = false;
            }

            return $isFound ? true : $resultFilter;
        }, ARRAY_FILTER_USE_BOTH);
        $setName = true;
        $pattern = '';
        foreach ($results as $routeNameFormatted => $result) {
            if ($setName === true) {
                $pattern = $result;
                if (!empty($routingFormatted[$routeNameFormatted])) {
                    $routeInfos = $routingFormatted[$routeNameFormatted];
                    Logger::log('['.__CLASS__.'] route matches -> '.$routeNameFormatted, Logger::LOG_DEBUG);
                    $routeInfos['argv'] = array();//$this->getParams;
                    $routeInfos['name'] = $this->getRouteAppLoaded().':'.$routeNameFormatted;
                    Logger::log('['.__CLASS__.'] route loaded -> '.$this->getRouteAppLoaded().':'.$routeNameFormatted, Logger::LOG_DEBUG);
                }

                $setName = false;
            }elseif ($result !== $pattern) {
                $routeInfos['argv'][] = $result;
            }
        }
        var_dump($routeInfos);
        $test2 = microtime();
        var_dump($test2 - $test);
    }
}
