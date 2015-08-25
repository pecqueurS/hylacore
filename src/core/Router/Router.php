<?php

namespace Hyla\Router;
use Hyla\Config\Conf;
use Hyla\Server\Server;

/**
 * Class Router
 * @package Hyla\Router
 */
abstract class Router {

    /**
     * Path to routing for app
     */
    const ROUTING_PATH = 'etc/routing/';

    /**
     * @var array application conf
     */
    private static $appConf;

    /**
     * @var array all routes for an app
     */
    private static $allRoutes = array();

    /**
     * @var array route informations
     */
    private static $routeInfos = array();


    /**
     * Init routing for app
     *
     * @throws \Exception
     */
    public static function init()
    {
        self::loadRoutes();
        self::createRouteInfos(self::matchRoute());

        self::addGetParameters();
        self::addTypeResponse();

        Conf::set('routeInfo', self::$routeInfos);
    }


    /**
     * Load routes from app folder
     */
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


    /**
     * Load files routing from app folder
     *
     * @return array
     */
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


    /**
     * Retrieve route used
     *
     * @return array
     */
    private static function matchRoute()
    {
        $routing = Conf::get('route');
        $routeUri = self::getRouteUri();

        $routes = array();
        foreach ($routing as $routeName => $routeTest) {
            $routeNameFormatted = substr($routeName, 0, 32);
            $routes[] = '^(?<' . $routeNameFormatted . '>' . $routeTest['pattern'] . ')$';
            self::$allRoutes[$routeNameFormatted] = $routeTest;
        }
        $testPattern = '#' . implode('|', $routes) . '#';

        preg_match($testPattern, $routeUri, $argv);
        array_shift($argv);
        $isFound = false;
        $results = array_filter($argv, function ($value, $key) use (&$isFound) {
            $resultFilter = is_string($key) && $value != '';
            if (!$isFound && $resultFilter) {
                $isFound = true;
            } elseif ($isFound && $resultFilter) {
                $isFound = false;
            }

            return $isFound ? true : $resultFilter;
        }, ARRAY_FILTER_USE_BOTH);

        return $results;
    }


    /**
     * Create route informations
     *
     * @param $routeParameters
     * @throws \Exception
     */
    private static function createRouteInfos($routeParameters)
    {
        $setName = true;
        $pattern = '';
        foreach ($routeParameters as $routeNameFormatted => $result) {
            if ($setName === true) {
                $pattern = $result;
                if (!empty(self::$allRoutes[$routeNameFormatted])) {
                    self::$routeInfos = self::$allRoutes[$routeNameFormatted];
                    //Logger::log('['.__CLASS__.'] route matches -> '.$routeNameFormatted, Logger::LOG_DEBUG);
                }
                $setName = false;
            } elseif ($result !== $pattern) {
                self::$routeInfos['argv'][] = $result;
            }
        }

        if (empty(self::$routeInfos)) {
            throw new \Exception('route no match !');
        }
    }


    /**
     * Add $_GET parameters to route informations
     *
     * @throws \Exception
     */
    private static function addGetParameters()
    {
        if (!empty(self::$routeInfos['parameters'])) {
            $getParameters = self::getUriParameters();
            foreach(self::$routeInfos['parameters'] as $key => $pattern) {
                if (preg_match('#^'. $pattern .'$#', (!empty($getParameters[$key]) ? $getParameters[$key] : ''), $argv)) {
                    $parameter = array_shift($argv);
                    self::$routeInfos['argv'][] = $parameter !== '' ? $parameter : null;
                } else {
                    throw new \Exception('invalid parameters !');
                }
            }
        }
    }


    /**
     * Add response type default
     */
    private static function addTypeResponse()
    {
        if (empty(self::$routeInfos['response'])) {
            self::$routeInfos['response'] = Conf::$isCli ? Conf::CLI : Conf::GUI;
        }
    }


    /**
     * Return URI
     *
     * @return null|string
     */
    private static function getRouteUri()
    {
        if (Conf::$isCli) {
            $argvServer = Server::get('argv');
            array_shift($argvServer);
            return implode($argvServer, ' ');
        } else {
            $requestUriServer = Server::get('requestUri');
            $explodedRoute = explode('?', $requestUriServer);
            return (!empty($explodedRoute[0])) ? $explodedRoute[0] : $requestUriServer;
        }
    }


    /**
     * Return $_GET parameters from URI informations Server
     *
     * @return array
     */
    private static function getUriParameters()
    {
        $queryStringServer = Server::get('queryString');
        $queryStringExploded = explode('&', $queryStringServer);
        $getParameters = array();
        foreach ($queryStringExploded as $parameter) {
            list($key, $value) = explode('=', $parameter);
            $getParameters[$key] = $value;
        }

        return $getParameters;
    }
}
