<?php
namespace Hyla\ErrorHandler;

use Hyla\Config\Conf;

abstract class ErrorHandler {

	const DEV = 'DEV';
    const PROD = 'PROD';

    const CLI = 'cli';

    private static $errortype = array (
        E_ERROR => "Erreur",
        E_WARNING => "Alerte",
        E_PARSE => "Erreur d'analyse",
        E_NOTICE => "Note",
        E_CORE_ERROR => "Core Error",
        E_CORE_WARNING => "CoreWarning",
        E_COMPILE_ERROR => "Compile Error",
        E_COMPILE_WARNING => "Compile Warning",
        E_USER_ERROR => "Erreur specifique",
        E_USER_WARNING => "Alerte specifique",
        E_USER_NOTICE => "Note specifique",
        E_STRICT => "Runtime Notice"
    );


    private static $isCli;
    private static $env = 'DEV';

    private static $now;

    private static $errno;
    private static $errmsg;
    private static $filename;
    private static $linenum;
    private static $vars;
    private static $stackTrace;

    public static function init()
    {
        self::$env = Conf::get('environment.env');
        self::$isCli = php_sapi_name() === self::CLI;
        switch (self::$env) {
            case self::DEV :
                error_reporting(-1);
                ini_set('display_errors', 'On');
                ini_set('html_errors', 'On');

                break;
            default:
                error_reporting(0);
                ini_set('display_errors', 'Off');
                ini_set('html_errors', 'Off');
        }

        set_error_handler(array(get_called_class(), 'handleError'));
        set_exception_handler(array(get_called_class(), 'handleException'));
        if (!self::$isCli) {
            register_shutdown_function(array(get_called_class(), 'handleShutdown'));
        }
    }


    public static function handleError($errno, $errmsg, $filename, $linenum, $vars, $stackTrace = null)
    {
        self::setDate();
        self::setError($errno, $errmsg, $filename, $linenum, $vars, $stackTrace);
        self::saveToLOG();
        switch(self::$env) {
            case self::DEV :
                var_dump('tesssssssssssssssssssssssssssssssssssst');
                self::displayErrors();
                break;
            default:
                self::saveToXML();
                self::redirectURL();
        }
    }


    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error !== null && $error['type'] === E_ERROR) {
            self::handleError($error["type"], $error["message"], $error["file"], $error["line"], null);
        }
    }


    public static function handleException(\Exception $Exception)
    {
        self::handleError('EXCEPTION ' . get_class($Exception), $Exception->getMessage(), $Exception->getFile(), $Exception->getLine(), null, $Exception->getTrace());
    }


    private static function setDate() {
        self::$now = date("Y-m-d H:i:s T");
    }


    private static function setError($errno, $errmsg, $filename, $linenum, $vars, $stackTrace = null) {
        self::$errno = $errno;
        self::$errmsg = $errmsg;
        self::$filename = $filename;
        self::$linenum = $linenum;
        self::$vars = $vars;
        self::$stackTrace = $stackTrace;
    }


    private static function saveToLOG() {
        $err = ErrorMessage::log(
            self::$now, self::$errno, self::$errortype[self::$errno], self::$errmsg, self::$filename, self::$linenum, self::$vars, self::$stackTrace
        );

        ErrorSave::save($err, ErrorSave::LOG);
    }


    private static function saveToXML() {
        $err = ErrorMessage::xml(
            self::$now, self::$errno, self::$errortype[self::$errno], self::$errmsg, self::$filename, self::$linenum, self::$vars, self::$stackTrace
        );

        ErrorSave::save($err, ErrorSave::XML);
    }


    private static function redirectURL() {
        $user_errors = array(E_ERROR, E_WARNING, E_PARSE, E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

        if (in_array(self::$errno, $user_errors)) {
            header ('HTTP/1.0 500 Internal Server Error', true, 500);
        }
    }


    private static function displayErrors() {
        echo self::$isCli
            ? ErrorMessage::log(self::$now, self::$errno, self::$errortype[self::$errno], self::$errmsg, self::$filename, self::$linenum, self::$vars, self::$stackTrace,  true)
            : ErrorMessage::display(self::$now, self::$errno, self::$errortype[self::$errno], self::$errmsg, self::$filename, self::$linenum, self::$vars, self::$stackTrace, true);
    }


}
