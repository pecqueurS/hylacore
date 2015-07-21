<?php
use Hyla\Config\Conf;
use Hyla\FrontController\FrontController;

require_once ("../src/core/Autoloader/autoloader.php");

Conf::init();
FrontController::init();

var_dump($_SERVER);



var_dump(Conf::getAll());
echo 'hello world !';

?>
