<?php

require_once __DIR__.'/ClassLoader/Psr4ClassLoader.php';

use Symfony\Component\ClassLoader\Psr4ClassLoader as ClassLoader;
use Hyla\Config\Conf;

$loader = new ClassLoader();
$loader->addPrefix('Hyla',dirname(__DIR__));

$loader->register();

Conf::initApp();
$app = Conf::get('app');
if (!empty($app)) {
    $loader->addPrefix($app['name'], Conf::$rootDir . $app['path'] . '/etc');
}

// Load ErrorHandler Class
//require_once dirname(__DIR__).'/ErrorHandling/ErrorHandling.class.php';
