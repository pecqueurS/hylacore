<?php
namespace HylaComponents\Tools;

/**
 * Class DebugTimer
 * @package Mobile\Backend\Services\Timer
 */
class DebugTimer
{
    protected static $timerLevelValue = 'DEBUG';
    protected static $maxMemoryUsed = 0;
    /**
     * @var array list level
     */
    protected static $listLevel = array(
        0 => 'DEBUG',
        1 => 'INFO',
        2 => 'ERROR',
        3 => 'CRIT'
    );
    protected static $timeClocks = array();
    public static function init()
    {
        self::createTimePoint(microtime(true), 'init', true);
    }
    public static function log($entity = null, $message = null, $begin = null, $var = null)
    {
        $conf = Conf::getConfig('logger_timer');
        if (array_search(self::$timerLevelValue, self::$listLevel)
            >= array_search($conf['log_level'], self::$listLevel)) {
            self::add($message);
            $message = self::createErrorMessage($entity, self::$timerLevelValue, $message, $begin, $var);
            $file = new File($conf);
            $file->log($message, self::$timerLevelValue);
        }
    }
    public static function add($message = 'default')
    {
        if (empty(static::$timeClocks)) {
            self::createTimePoint($_SERVER["REQUEST_TIME_FLOAT"], 'init');
        }
        self::createTimePoint(microtime(true), $message);
    }
    private static function createTimePoint($time, $message, $init = false)
    {
        $timePoint = array(
            'time' => $time,
            'message' => $message,
            'memory' => $init ? 0 : (memory_get_usage(false)/1024/1024)
        );
        if (static::$maxMemoryUsed < $timePoint['memory']) {
            static::$maxMemoryUsed = $timePoint['memory'];
        }
        if ($init) {
            static::$timeClocks = array($timePoint);
            static::$maxMemoryUsed = 0;
        } else {
            static::$timeClocks[] = $timePoint;
        }
    }
    /**
     * @param $entity
     * @param $level
     * @param $message
     * @param $begin
     * @return string
     */
    private static function createErrorMessage($entity, $level, $message, $begin, $var = null)
    {
        $count = count(static::$timeClocks);
        $lastInterval = static::$timeClocks[$count - 1]['time'] - static::$timeClocks[$count - 2]['time'];
        $totalInterval = static::$timeClocks[$count - 1]['time'] - static::$timeClocks[$begin === null
                ? 0
                : $begin]['time'];
        $lastMemoryInterval = static::$timeClocks[$count - 1]['memory'] - static::$timeClocks[$count - 2]['memory'];
        $totalMemoryInterval = number_format((static::$timeClocks[$count - 1]['memory']), 2, '.', '');
        $messageToLog = "\n[Level] : $count";
        if ($message !== null) {
            $messageToLog .= "\n[Message] : $message";
        }
        $messageToLog .= "\n[LastInterval] : $lastInterval sec.";
        $messageToLog .= "\n[totalInterval] : $totalInterval sec.";
        $messageToLog .= "\n[Memory used last] : $lastMemoryInterval MiB.";
        $messageToLog .= "\n[Memory used total] : $totalMemoryInterval MiB.";
        $messageToLog .= "\n[Memory limit] : " . ini_get('memory_limit');
        $messageToLog .= "\n[Maximum memory used] : " . number_format((static::$maxMemoryUsed), 2, '.', '') . ' MiB.';
        $messageToLog .= "\n[Variable dump] : " . var_export($var, true);
        return (new \DateTime())->format('Y-m-d H:i:s')
        . ' [' . $level . '] '
        . $className = get_class($entity)
            . $messageToLog
            . "\n";
    }
}