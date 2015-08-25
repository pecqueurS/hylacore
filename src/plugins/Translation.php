<?php

namespace HylaPlugins;
use Hyla\Config\Conf;

/**
 * Class Translation
 * @package HylaPlugins
 */
class Translation extends Tpl {

    const DB = 'DB';
    const XML = 'XML';
    const INI = 'INI';
    const JSON = 'JSON';
    const NO_TRANSLATE = 'NO_TRANSLATE';

    private static $traduction = array();

    public static function init(array $data = null) {
        $type = Conf::get('translate.type');
        switch ($type) {
            case self::DB:
                //self::$traduction = DictionnaireModel::init()->getValues();
                break;

            case self::XML:

                break;

            case self::INI:

                break;
            case self::JSON:

                break;
            case self::NO_TRANSLATE:
var_dump('test');
                break;
        }

    }

    public static function trad($name) {
        return self::$traduction[$name];
    }
}
