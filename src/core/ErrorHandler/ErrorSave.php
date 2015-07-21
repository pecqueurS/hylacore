<?php
namespace Hyla\ErrorHandler;


use Hyla\Config\Conf;
use Hyla\Logger\Logger;

abstract class ErrorSave {

    const LOG = 'log';
    const XML = 'xml';

    /**
     * @param $msg
     * @param string $type
     */
    public static function save($msg, $type = self::LOG)
    {
        $confErrors = Conf::get('errors');
        $confApp = Conf::get('app');
        $file = $confApp['root'] . $confApp['path'] . '/' . $confErrors['path'] . '/' . $confErrors['filename'] . '.' . $type;

        Logger::log($msg, Logger::CRITICAL, $file, false);
    }
}
