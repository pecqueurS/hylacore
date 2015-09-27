<?php
use Hyla\Config\Conf;
use Hyla\FrontController\FrontController;
use \Hyla\ErrorHandler\ErrorHandler;
require_once (__DIR__ . '/../src/core/Autoloader/autoloader.php');

try{
    Conf::init();
    FrontController::launch();
} catch(Exception $e){
    ErrorHandler::handleException($e);
}



//var_dump(Conf::getAll());




//echo 'hello world !';

?>
