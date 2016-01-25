<?php

namespace Hyla\Templates;

use Hyla\Config\Conf;
use Hyla\Templates\ExtentionsTwig\FormExtTwig;
use Hyla\Templates\ExtentionsTwig\TranslateExtTwig;

/**
 * Class Tpl
 * @package Hyla\Templates
 */
class Tpl {
	
	protected $environnement = array(
			"debug" => true, //"debug" => false,
			"charset" => "utf-8",
			"base_template_class" => "Twig_Template",
			"cache" => false,
			"auto_reload" => true, // "auto_reload" => false,
			"strict_variables" => false,
			"autoescape" => true, // "autoescape" => true, 
			"optimizations" => -1

		);

	public $dirTwigTpl = 'app/example/src/Views/Twig_Tpl';
	 
	protected $vars = array();

	protected $twig;

	public function __construct($dirTwigTpl = null) {
		if($dirTwigTpl !== null) {
			$this->dirTwigTpl = $dirTwigTpl;
		}
		require_once(Conf::get('app.root') . "src/lib/Twig-1.15.1/lib/Twig/Autoloader.php");
		\Twig_Autoloader::register();
		$loader = new \Twig_Loader_Filesystem(Conf::get('app.root') . $dirTwigTpl);
		$this->twig = new \Twig_Environment($loader, $this->environnement);
		$this->twig->addExtension(new \Twig_Extension_Debug());
		// Bundle Formulaire affichage par form(nomForm, nomInput)
		$this->twig->addExtension(new FormExtTwig());
		// Bundle Translate affichage par dico(cle)
		//$this->twig->addExtension(new TranslateExtTwig());
	}

	public static function display(array $vars = array(), $dirTpl = null) {
		$tplObj = new Tpl($dirTpl);
		return $tplObj->addVars($vars)->getTpl();
	}

	public function getTpl($tpl = null) {
		if(!$tpl) {
			$tpl = $this->selectTpl();
		}
		return $this->twig->render($tpl, $this->vars);
	}

	public function addVars($vars=array()) {
		$this->vars = array_merge((array)$this->vars, (array)$vars);
		return $this;
	}

	protected function selectTpl() {
		if (!empty(Conf::get('routeInfo.tpl'))) {
			return Conf::get('routeInfo.tpl');
		} elseif (file_exists(Conf::get('app.root') . "{$this->dirTwigTpl}/{$this->getDefaultTemplate()}")) {
			return $this->getDefaultTemplate();
		} else {
			return Conf::get('view.path');
        }
	}

	protected function getDefaultTemplate() {
		$routeInfos = Conf::get('routeInfo');
		$class = explode('\\', $routeInfos['class']);
		$directory = end($class);
		$file = $routeInfos['method'];

		return "$directory/$file.twig";
	}
}
