<?php

namespace Hyla\Forms;

use Bundles\Parametres\Conf;

use Hyla\Forms\Utils\Inputs;
use Hyla\Forms\Utils\Inspector;

use Hyla\Forms\Tpl\FormTpl;

use Bundles\Translate\Dico;


/**
 * Class Forms
 * @package Hyla\Forms
 */
class Forms {
	public $dirForm;

	public static $renderHTML = array();
	public static $isValid = false;

	public $nameForm = 'form1';
	public $type;
	public $inputs;
	public $errors;

	public function __construct($nameForm=null, $type="POST", $dirForm=null) {
		$this->dirForm = Conf::getConstants()->getConf()['FORMS'];
		if($nameForm) $this->nameForm = $nameForm;
		if($dirForm) $this->dirForm = Conf::getConstants()->getConf()['ROOT'].$dirForm;
		$this->type = $type;
		$this->loadForm();
	}

	public static function make($nameForm=null, $type="POST", $dirForm=null) {
		$form = new Forms($nameForm, $type, $dirForm);
		return $form;
	}

	public function loadForm(){
		$form = json_decode(file_get_contents($this->dirForm.$this->nameForm.'.json'), true);
		foreach ($form as $input) {
			$this->add($input['name'], $input['options']);
		}
	}


	/**
	 * ->add("nom_du_champs", array(
	 *     "label" => "nom_du_champs_different",
	 *     "type" => "text",
	 *     "id" => "id_du_champs",
	 *     "class" => "class_du_champs",
	 *     "attr" => array(
	 *         ["attr1","value1"], 
	 *         ["attr2","value2"]
	 *     ),
	 *     "errorMsg" => "Ce_message_peut_etre_personnalisé",
	 *     "constraints" => array(
	 *         "NotBlank" => true,
	 *         "Type" => "integer",
	 *         "LessThan" => 12
	 *         
	 *     )
	 * ));
	 * @param [str] $name    [description]
	 * @param [arr] $options [description]
	 */
	public function add($name,$options=array()) {
		$this->inputs[$name] = array("name" => (string)$name, "options" =>(array)$options);
	}

	public function changeOption($name, $option, $value) {
		$this->inputs[$name]['options'][$option] = $value;
	}

	public function changeConstraint($name, $constraint, $value) {
		$this->inputs[$name]['options']['constraints'][$constraint] = $value;
	}



	public function render(){
		$inputsHTML = array();
		foreach ($this->input as $input) {
			$inputsHTML[$input['name']] = $this->constructBlock($input['name'], $input['options']);
			self::$renderHTML[$this->nameForm][$input['name']] = $inputsHTML[$input['name']];
		}
		
		return $inputsHTML;
	}


	public function isValid(){
		$result = true;
		foreach ($this->inputs as $input) {
			if(!$this->verifBlock($input['name'], $input['options'])) {
				$result = false;
			}
		}
		self::$isValid = $result;
		return $result;
	}


	private function constructBlock($name, $options) {

		$response['name'] = $name;
		
		// required
		$response['required'] = (isset($options['required']) && $options['required'] === TRUE) ? "<span class='required-form'> *</span>" : '' ;

		// Label option
		if(isset($options['label'])) {
			if($options['label']!==FALSE) {
				$response['label'] = (isset($options['translate']) && $options['translate'] === TRUE) ? Dico::trad($options['label']) : $options['label'] ;
			} 
		} else {
			$response['label'] = $name;
		}

		// Input
		$response['input'] = $this->constructInput($name, $options);

		// Error
		if(isset($this->errors[$name])) {
			$response['errors'] = (isset($options['errorMsg']))? $options['errorMsg'] : $this->errors[$name] ;
		}

		// Description
		if(isset($options['desc'])) {
			$response['description'] = $options['desc'];
		}


		//Tpl::$dirTwigTpl = '/Bundles/Formulaires/Tpl';
		return FormTpl::display($response, 'form.twig');




	}

	private function constructInput($name, $options) {
		if(empty($options)) $options['type'] = 'text'; 
		return Inputs::render($name, $options);
	}

	

	private function verifBlock($name, $options) {
		switch ($this->type) {
			case 'GET':
				$vars = $_GET;
				break;
			
			default:
				$vars = $_POST;
				break;
		}

		$vars = (($options['type'] == 'file')? $_FILES[$name] : $vars);
		if(isset($vars[$name]) || ($options['type'] == 'file')) { 
			if (isset($options['constraints'])) {
				$response = true;
				
				$value = (($options['type'] == 'file') ? $vars : $vars[$name]);
				foreach ($options['constraints'] as $type => $constraint) {
					if(!(isset($options['disabled']) && $options['disabled'] === true)) {
						if(!Inspector::checkData($value, $type, $constraint)) {
							if(isset($this->errors[$name])) {
								$this->errors[$name] .= Inspector::getMsg();
							} else {
								$this->errors[$name] = Inspector::getMsg();
							}
							$response = false;
						}
					}
					
					if(isset($options['value'])){
						$this->inputs[$name]['options']['value'] = (is_array($vars[$name])) ? $vars[$name] : array($vars[$name]);
					}
				}
				return $response;
			} else {
				return true;
			}
		} else {
			return false;
		}
		
	}


	public function getValue($name) {
		$input = $this->inputs[$name];
		if (empty($input['options']['value'])) {
			return array();
		}

		$result = array();
		foreach ($input['options']['value'] as $key => $value) {
			$result[$key] = htmlentities($value, ENT_QUOTES | ENT_IGNORE, "UTF-8");
		}

		if (count($result) === 1) {
			$result = array_shift($result);
		}

		return $result;
	}

	






}
