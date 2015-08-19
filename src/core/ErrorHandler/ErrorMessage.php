<?php
namespace Hyla\ErrorHandler;

use Hyla\Config\Conf;

abstract class ErrorMessage {

    const ERR_VARS = 'ERR_VARS';
    const ERR_TRACES = 'ERR_TRACES';
    const ERR_ALL = 'ERR_ALL';
    const ERR_MIN = 'ERR_MIN';

    /**
     * @param $date
     * @param $errno
     * @param $errType
     * @param $errMsg
     * @param $file
     * @param $line
     * @param $vars
     * @param null $stackTrace
     * @return string
     */
    public static function log($date, $errno, $errType, $errMsg, $file, $line, $vars, $stackTrace = null)
    {
        $conf = Conf::get('errors');
        $err = '********************************************************************************' . PHP_EOL;
        $err .= "[$date] $errno.$errType :" . PHP_EOL;
        $err .= $errMsg . PHP_EOL;
        $err .= "in $file on line $line" . PHP_EOL;

        if ($conf['level'] == self::ERR_VARS) {
            $complementaryInformations = self::linearizeVar($vars, true);
            $err .= 'Informations Vars : ' . PHP_EOL . $complementaryInformations;
        } elseif ($conf['level'] == self::ERR_TRACES) {
            $stackTrace = $stackTrace === null ? implode("\n", self::getStackTrace(true)) : self::linearizeVar($stackTrace, true);
            $err .= 'StackTrace : ' . $stackTrace;
        } elseif ($conf['level'] == self::ERR_ALL) {
            $complementaryInformations = self::linearizeVar($vars, true);
            $err .= 'Informations Vars : ' . PHP_EOL . $complementaryInformations;
            $stackTrace = $stackTrace === null ? implode("\n", self::getStackTrace(true)) : self::linearizeVar($stackTrace, true);
            $err .= 'StackTrace : ' . $stackTrace;
        } else {
            $err .= PHP_EOL;
        }

        return $err;
    }


    /**
     * @param $date
     * @param $errno
     * @param $errType
     * @param $errMsg
     * @param $file
     * @param $line
     * @param $vars
     * @param null $stackTrace
     * @return string
     */
    public static function xml($date, $errno, $errType, $errMsg, $file, $line, $vars, $stackTrace = null)
    {
        $user_errors = array(E_ERROR, E_WARNING, E_PARSE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

        $err = "<errorentry>\n";
        $err .= "\t<datetime>" . $date . "</datetime>\n";
        $err .= "\t<errornum>" . $errno . "</errornum>\n";
        $err .= "\t<errortype>" . $errType . "</errortype>\n";
        $err .= "\t<errormsg>" . $errMsg . "</errormsg>\n";
        $err .= "\t<scriptname>" . $file . "</scriptname>\n";
        $err .= "\t<scriptlinenum>" . $line . "</scriptlinenum>\n";
        if (in_array($errno, $user_errors)) {
            $err .= "\t<vartrace>".wddx_serialize_value($vars,"Variables")."</vartrace>\n";
        }
        $err .= "</errorentry>\n\n";

        return $err;
    }


    /**
     * @param $date
     * @param $errno
     * @param $errType
     * @param $errMsg
     * @param $file
     * @param $line
     * @param $vars
     * @param null $stackTrace
     * @return string
     */
    public static function display($date, $errno, $errType, $errMsg, $file, $line, $vars, $stackTrace = null)
    {
        $conf = Conf::get('errors');
        $err = '<pre style="border:1px solid #aaa; border-radius:5px; background:#eee; padding: 15px;">';
        $err .= "<b><span style='color:blue;'>[</span>" . $date . "<span style='color:blue;'>]</span> <span style='color:red;'>" . $errno . '.' . $errType . "</span> :</b>\n";
        $err .= '<b style="color:green">' . $errMsg . "</b>\n";
        $err .= 'in <b style="color:blue">' . $file . '</b> on line <b style="color:blue">' . $line . "</b>\n";

        if ($conf['level'] == self::ERR_VARS) {
            $complementaryInformations = self::linearizeVar($vars, false);
            $err .= "<b>Informations Vars</b> : \n$complementaryInformations\n";
        } elseif ($conf['level'] == self::ERR_TRACES) {
            $stackTrace = $stackTrace === null ? implode("\n", self::getStackTrace(true)) : self::linearizeVar($stackTrace, true);
            $err .= "<b>StackTrace</b> : \n$stackTrace\n";
        } elseif ($conf['level'] == self::ERR_ALL) {
            $complementaryInformations = self::linearizeVar($vars, false);
            $err .= "<b>Informations Vars</b> : \n$complementaryInformations\n";

            $stackTrace = $stackTrace === null ? implode("\n", self::getStackTrace(true)) : self::linearizeVar($stackTrace, true);
            $err .= "<b>StackTrace</b> : \n$stackTrace\n";
        } else {
            $err .= "\n";
        }
        $err .= '</pre>';

        return $err;
    }


    /**
     * @param $isCli
     * @return array
     */
    private static function getStackTrace($isCli) {
        $stackTrace = [];
        $backtraces = array_reverse(debug_backtrace());

        if ($isCli) {
            foreach ($backtraces as $key => $backtrace) {
                if (!empty($backtrace['file']) /*&& strstr($backtrace['class'], 'Hyla\ErrorHandler') === false*/) {
                    $trace = $key . '. ';
                    $trace .= !empty($backtrace['file']) ? $backtrace['file'] : '';
                    $trace .= !empty($backtrace['line']) ? "\nLine : " . $backtrace['line'] : '';
                    $trace .= !empty($backtrace['class']) && isset($backtrace['function']) && isset($backtrace['type']) ? "\nMethod : " . $backtrace['class'] . $backtrace['type'] . $backtrace['function'] . '()' : '';
                    $trace .= !empty($backtrace['args']) ? "\nArguments : \n" . self::linearizeVar($backtrace['args'], $isCli) : "\n";

                    $stackTrace[] = $trace;
                }
            }
        } else {
            foreach ($backtraces as $key => $backtrace) {
                if (!empty($backtrace['file'])/* && strstr($backtrace['class'], 'Hyla\ErrorHandler') === false*/) {
                    $trace = '<b>#' . $key . '. </b>';
                    $trace .= !empty($backtrace['file']) ? '<b style="color:blue">' . $backtrace['file'] . '</b>' : '';
                    $trace .= !empty($backtrace['line']) ? "\n<b> -> Line : <span style='color:blue'>" . $backtrace['line'] . '</span></b>' : '';
                    $trace .= !empty($backtrace['class']) && isset($backtrace['function']) && isset($backtrace['type']) ? "\n<b> -> Method : <span style='color:purple'>" . $backtrace['class'] . $backtrace['type'] . $backtrace['function'] . '()</span></b>' : '';
                    $trace .= !empty($backtrace['args']) ? "\n<b> -> Arguments : </b>\n" . self::linearizeVar($backtrace['args'], $isCli) : "\n";

                    $stackTrace[] = $trace;
                }
            }
        }

        return $stackTrace;
    }


    /**
     * @param $var
     * @param $isCli
     * @param int $nbOfLoop
     * @return string
     */
    private static function linearizeVar($var, $isCli, $nbOfLoop = 0) {
        $result = '';
        if (is_object($var)) {
            $var =  (array) $var;
        }

        if (is_array($var)) {
            $j = 0;
            foreach ($var as $key => $value) {
                if ($j != 0) {
                    for ($i=0; $i < $nbOfLoop ; $i++) {
                        $result .= "    ";
                    }
                }
                $result .= ($isCli ? "[$key] => " : "<b>[<span style='color:blue'>$key</span>]</b> => ") . self::linearizeVar($value, $isCli, $nbOfLoop + 1);
                $j++;
            }
        } else {
            $result .= $isCli
                ? trim((string) $var) . "\n"
                : '<b style="color:purple">' . trim((string) $var) . "</b>\n";
        }

        return $result;
    }
}
