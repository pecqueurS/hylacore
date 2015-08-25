<?php
require_once __DIR__ . '/../../lib/ClassLoader/Psr4ClassLoader.php';

use Symfony\Component\ClassLoader\Psr4ClassLoader as ClassLoader;
use Hyla\Config\Conf;

$loader = new ClassLoader();
$loader->addPrefix('Hyla',dirname(__DIR__));
$loader->addPrefix('HylaComponents',dirname(dirname(__DIR__)) . '/components');
$loader->addPrefix('HylaPlugins',dirname(dirname(__DIR__)) . '/plugins');

$loader->register();

Conf::initApp();
$app = Conf::get('app');
if (!empty($app)) {
    $loader->addPrefix($app['name'], Conf::$rootDir . $app['path'] . '/etc');
}


