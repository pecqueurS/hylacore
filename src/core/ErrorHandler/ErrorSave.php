<?php
namespace Hyla\ErrorHandler;


use Hyla\Config\Conf;

abstract class ErrorSave {

    const LOG = 'log';
    const XML = 'xml';

    public static function save($msg, $type = self::LOG)
    {
        $confErrors = Conf::get('errors');
        $confApp = Conf::get('app');
        $file = $confApp['root'] . $confApp['path'] . '/' . $confErrors['filename'] . '.' . $type;
        /**
         * TODO SAVE MESSAGE TO LOG FILE
         */
var_dump($msg, $file);

    }

}
