<?php

namespace Hyla\Forms;

use Hyla\Config\Conf;

use Hyla\Forms\Utils\Inputs;
use Hyla\Forms\Utils\Inspector;

use Hyla\Forms\Tpl\FormTpl;

//use Hyla\Translate\Dico;


/**
 * Class Forms
 * @package Hyla\Forms
 */
class Forms
{
	const TYPE_POST = 'POST';
    const TYPE_GET = 'GET';
    const TYPE_FILE = 'FILE';

    const FORMS_PATH = '/etc/forms/';

	public $dirForm;

	public static $renderHTML = array();
	public static $isValid = false;

	public $nameForm = 'form1';
	public $type;
	public $inputs;
	public $errors;

    /**
     * Forms constructor.
     * @param string $nameForm
     * @param string $type
     * @param string|null $dirForm
     */
    public function __construct($nameForm, $type = self::TYPE_POST, $dirForm = null)
    {
        $this->nameForm = $nameForm;
        $this->type = $type;

        $app = Conf::get('app');
        $this->dirForm = $app['root'] . $app['path'] . ($dirForm === null ? self::FORMS_PATH : $dirForm);

        $this->loadFormConf();
 	}

    /**
     * Load form conf from file
     */
    public function loadFormConf()
    {
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
	 * @param string $name
	 * @param array $options
	 */
	public function add($name,$options=array())
    {
		$this->inputs[$name] = array("name" => (string)$name, "options" =>(array)$options);
	}

    /**
     * @param string $name
     * @param string $option
     * @param mixed $value
     */
    public function changeOption($name, $option, $value)
    {
		$this->inputs[$name]['options'][$option] = $value;
	}

    /**
     * @param string $name
     * @param string $constraint
     * @param mixed $value
     */
    public function changeConstraint($name, $constraint, $value)
    {
		$this->inputs[$name]['options']['constraints'][$constraint] = $value;
	}

    /**
     * @return bool
     */
    public function isValid()
    {
		$result = true;
		foreach ($this->inputs as $input) {
			if(!$this->checkBlock($input['name'], $input['options'])) {
				$result = false;
			}
		}
		self::$isValid = $result;

		return $result;
	}

    /**
     * @param string $name
     * @param array $options
     * @return bool
     */
    private function checkBlock($name, array $options)
    {
        $vars = $this->getVars($name, $options);
        if(isset($vars[$name]) || (strtoupper($options['type']) === self::TYPE_FILE)) {
            $response = true;
            if (isset($options['constraints'])) {
                $value = ((strtoupper($options['type']) === self::TYPE_FILE) ? $vars : $vars[$name]);
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
            }

            return $response;
        }

        return false;
    }

    /**
     * @param string $name
     * @param array $options
     * @return mixed
     */
    private function getVars($name, array $options)
    {
        if (isset($options['type']) && strtoupper($options['type']) === self::TYPE_FILE) {
            return $_FILES[$name];
        } elseif ($this->type === self::TYPE_GET) {
            return $_GET;
        }

        return $_POST;
    }


    /**
     * @return array
     */
    public function render()
    {
        $inputsHTML = array();
        foreach ($this->input as $input) {
            $inputsHTML[$input['name']] = $this->constructBlock($input['name'], $input['options']);
            self::$renderHTML[$this->nameForm][$input['name']] = $inputsHTML[$input['name']];
        }

        return $inputsHTML;
    }

    /**
     * @param string $name
     * @param array $options
     * @return string
     */
    private function constructBlock($name, array $options)
    {
		$response['name'] = $name;
		$response['required'] = (isset($options['required']) && $options['required'] === true) ? "<span class='required-form'> *</span>" : '' ;
		if(isset($options['label'])) {
			if($options['label'] !== false) {
                // TODO add translate
				$response['label'] = /*(isset($options['translate']) && $options['translate'] === true) ? Dico::trad($options['label']) : */$options['label'] ;
			} 
		} else {
			$response['label'] = $name;
		}
    	$response['input'] = $this->constructInput($name, $options);
		if(isset($this->errors[$name])) {
			$response['errors'] = (isset($options['errorMsg']))? $options['errorMsg'] : $this->errors[$name] ;
		}
		if(isset($options['desc'])) {
			$response['description'] = $options['desc'];
		}

        return FormTpl::display($response);
	}

    /**
     * @param string $name
     * @param array $options
     * @return mixed
     */
	private function constructInput($name, array $options)
    {
		if(empty($options)) {
            $options['type'] = 'text';
        }

		return Inputs::render($name, $options);
	}

    /**
     * @param string $name
     * @return array|mixed
     */
	public function getValue($name)
    {
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
