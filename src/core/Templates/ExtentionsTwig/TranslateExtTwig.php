<?php

namespace Hyla\Templates\ExtentionsTwig;

use Hyla\Translate\Dico;

/**
 * Class TranslateExtTwig
 * @package Hyla\Templates\ExtentionsTwig
 */
class TranslateExtTwig extends \Twig_Extension
{
    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        return array(
            //new \Twig_SimpleFunction('form', 'twig_get_form', array('is_safe' => array('html'))),
            'dico' => new \Twig_Function_Method($this, 'twig_get_dico'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'dico';
    }

    public function twig_get_dico($name)
    {
        return Dico::trad($name);
    }

}

