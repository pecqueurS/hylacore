<?php

namespace Hyla\Logger;
use Hyla\Config\Conf;

/**
 * Class Logger
 * @package Hyla\Logger
 */
abstract class Logger {

    const DEBUG = 'DEBUG';
    const INFO = 'INFO';
    const CRITICAL = 'CRITICAL';

    private static $level = array(
        self::DEBUG => 0,
        self::INFO => 1,
        self::CRITICAL => 2
    );

    private static $configLevel;
    private static $file = '/../../../var/log/core.log';

    /**
     * @param string $message
     * @param string $level
     * @param string|null $file
     * @param bool $formatMessage
     */
    public static function log($message, $level, $file = null, $formatMessage = true)
    {
        if ($file === null) {
            $file = __DIR__ . self::$file;
        }

        if (self::validLevel($level) && self::fileExists($file)) {
            self::saveMessage($message, $level, $formatMessage, $file);
        }
    }


    /**
     * @param string $level
     * @return bool
     */
    private static function validLevel($level)
    {
        if (self::$configLevel === null) {
            self::$configLevel = Conf::get('logger.level');
        }

        return self::$level[$level] >= self::$level[self::$configLevel];
    }


    /**
     * @param string|null $file
     * @return bool
     */
    private static function fileExists($file = null)
    {
        if ($file === null) {
            $file = Conf::get('app.root') . Conf::get('logger.file');
        }
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        if (!file_exists($file)) {
            touch($file);
            chmod($file, 0777);
        }

        return file_exists($file) && is_writable($file);
    }


    /**
     * @param string $message
     * @param string $level
     * @param bool $formatMessage
     */
    private static function saveMessage($message, $level, $formatMessage = true, $file = null)
    {
        if ($formatMessage) {
            $message = self::formatMessage($message, $level);
        }
        $message .= PHP_EOL;

        file_put_contents($file, $message, FILE_APPEND);
    }


    /**
     * @param string $message
     * @param string $level
     * @return string
     */
    private static function formatMessage($message, $level)
    {
        $now = strftime('%F %T');
        return $now . "  [$level]  $message";
    }
}
