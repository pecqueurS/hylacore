<?php
namespace Hyla\Controller;


class Controller {

    protected $response = array();

    protected function addResponse($key, $value)
    {
        $this->response[$key] = $value;
    }

    protected function getReponse($key = null)
    {
        if ($key !== null) {
            if (!empty($this->response[$key])) {
                return $this->response;
            }

            return null;
        } else {
            return $this->response;
        }
    }

    protected function delResponse($key = null)
    {
        if ($key !== null) {
            if (!empty($this->response[$key])) {
                unset($this->response[$key]);
            }
        } else {
            $this->response = array();
        }
    }
}
