<?php

namespace Hyla\FrontController;

use Hyla\ErrorHandler\ErrorHandler;
use Hyla\Router\Router;
use Hyla\Session\Session;

/**
 * Class FrontController
 * @package Hyla\FrontController
 */
abstract class FrontController {

    public static function init()
    {
        Session::init();
        ErrorHandler::init();

        Router::init();

    }
}
