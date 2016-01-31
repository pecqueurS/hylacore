<?php

namespace HylaComponents\Mails\Tpl;

use Hyla\Config\Conf;
use Hyla\Templates\Tpl;

/**
* 
*/
class MailsTpl extends Tpl {

    const DEFAULT_FILE_TPL = 'main';
	
	public static function display(array $vars = array(), $dirTpl = null)
    {
        $path = Conf::get('app.path') . '/src/Views/Twig_Tpl/Mails/Matrices';
		$tplObj = new Tpl($path);
        $dirTpl = $dirTpl === null ? self::DEFAULT_FILE_TPL : $dirTpl;

		return $tplObj->addVars($vars)->getTpl($dirTpl);
	}
}
