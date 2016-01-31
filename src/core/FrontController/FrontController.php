<?php

namespace Hyla\FrontController;


use Hyla\Config\Conf;
use Hyla\ErrorHandler\ErrorHandler;
use Hyla\Router\Router;
use Hyla\Server\Server;
use Hyla\Session\Session;

/**
 * Class FrontController
 * @package Hyla\FrontController
 */
abstract class FrontController {

    const PRECALL = 'PRECALL';
    const POSTCALL = 'POSTCALL';

    const PLUGINS_NAMESPACE = 'HylaPlugins\\';

    protected static $response;

    public static function launch()
    {
        self::init();
        self::run();
    }


    protected static function init()
    {
        Session::init();
        ErrorHandler::init();
        Router::init();
    }


    protected static function run()
    {
        self::launchPlugins(self::PRECALL);
        self::launchController();
        self::addConfToResponse();
        self::launchPlugins(self::POSTCALL);
    }


    protected static function launchPlugins($type)
    {
        switch ($type) {
            case self::PRECALL:
                $plugins = Conf::get('plugins.precall');
                break;
            case self::POSTCALL:
                $plugins = Conf::get('plugins.postcall');
                break;
            default:
                throw new \Exception('Invalid plugin type');
        }

        foreach ($plugins as $plugin) {
            $class = self::PLUGINS_NAMESPACE . $plugin;
            self::$response[$plugin] = $class::launch(self::$response);
        }
    }


    protected static function launchController()
    {
        $classname = Conf::get('routeInfo.class');
        $handler = array( new $classname(), Conf::get('routeInfo.method'));
        if (is_callable($handler)) {
            $args = (Conf::get('routeInfo.argv'));
            $response = call_user_func_array($handler, $args === null ? array() : $args);
            if ($response) {
                self::$response = array_merge(self::$response, $response);
            }
        } else {
            throw new \Exception('Controller does not exist');
        }
    }


    protected static function addConfToResponse()
    {
        self::$response['app'] = Conf::get('app');
        self::$response['routeInfo'] = Conf::get('routeInfo');
        self::$response['session'] = Session::getAll();
        self::$response['server'] = Server::getAll();
    }
}
