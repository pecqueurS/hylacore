<?php

namespace Hyla\Forms\Tpl;


use Hyla\Templates\Tpl;

/**
 * Class FormTpl
 * @package Hyla\Forms\Tpl
 */
class FormTpl extends Tpl {
	
	public static function display($vars = array(), $tpl = null) {
		$tplObj = new Tpl('/Bundles/Formulaires/Tpl');
		//$this->dirTwigTpl = '/Bundles/Formulaires/Tpl';
		return $tplObj->addVars($vars)->getTpl($tpl);
	}

	

}
