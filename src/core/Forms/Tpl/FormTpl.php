<?php

namespace Hyla\Forms\Tpl;


use Hyla\Templates\Tpl;

/**
 * Class FormTpl
 * @package Hyla\Forms\Tpl
 */
class FormTpl extends Tpl
{
	
	const DEFAULT_FILE_TPL = 'form.twig';

	const PATH_TO_FORM_TPL = '/src/core/Forms/Tpl';

	/**
	 * @param array $vars
	 * @param string $tpl
	 * @return string
	 */
	public static function display(array $vars = array(), $dirTpl = null)
	{
		$dirTpl = $dirTpl === null ? self::DEFAULT_FILE_TPL : $dirTpl;
		$tplObj = new Tpl(self::PATH_TO_FORM_TPL);

		return $tplObj->addVars($vars)->getTpl($dirTpl);
	}
}
