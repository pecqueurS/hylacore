<?php

namespace HylaPlugins;
use Hyla\Config\Conf;

/**
 * Class Tpl
 * @package HylaPlugins
 */
class Tpl extends AbstractPlugins {

    protected static $response;

    protected static function init(array $data = null)
    {
        static::$response = $data;
    }


    protected static function display()
    {
        $responseType = Conf::get('routeInfo.response');
        switch ($responseType) {
            case Conf::CLI:
                $response = '';
                break;
            case Conf::GUI:
                $response = Tpl::display(self::$response, "/app/" . Conf::getAppName() . "/Views/Twig_Tpl");
                break;
            case 'JSON':
                $response = json_encode(self::$response);
                break;
            default:
                throw new \Exception("'$responseType' is not a correct type");
        }

        echo $response;
    }

}
