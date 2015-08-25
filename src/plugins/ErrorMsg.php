<?php

namespace HylaPlugins;

/**
 * Class ErrorMsg
 * @package HylaPlugins
 */
class ErrorMsg extends AbstractPlugins {

    protected static function execute()
    {
        // Formattage du message d'erreur
        if(isset($_SESSION['message'])) {
            $response = $_SESSION['message'];
            unset($_SESSION['message']);

            return $response;
        }

        return null;
    }
}
