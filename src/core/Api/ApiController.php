<?php
namespace Hyla\Api;

abstract class ApiController
{
    /**
     * status code
     */
    const STATUS_503 = 503;
    const STATUS_409 = 409;
    const STATUS_400 = 400;
    const STATUS_401 = 401;
    const STATUS_200 = 200;
    const STATUS_201 = 201;

    const OUTPUT_JSON = 'json';
    const OUTPUT_XML = 'xml';
    const OUTPUT_PLIST = 'plist';
    const OUTPUT_SERIALIZE = 'serialize';
    const OUTPUT_CSV = 'csv';

    /**
     * @var array based response
     */
    protected $okStatus = array('message' => 'Ok');
    protected $koStatus = array('message' => 'Ko');

    /**
     * @var int actual Http status response
     */
    protected $statusCode;

    /**
     * @var string output format (json, xml, plist)
     */
    protected $outputFormat = self::OUTPUT_JSON;

    /**
     * @var ApiLogger logger for api
     */
    protected $loggerApi;

    /**
     * Parameters accepted
     * key => required status (bool, Filter::method)
     * ex : array(
     *          'test' => true,
     *          'test2' => 'isNumeric,isRequired',
     *          'test3' => false
     *      );
     *
     * @var array
     */
    protected $parameters = array();

    /**
     * @param mixed $config
     */
    protected function init($config)
    {
        parent::init($config);
        $this->loggerApi = new ApiLogger();
        $this->statusCode = self::STATUS_200;
    }


    /**
     * @return mixed
     */
    protected function getParameters()
    {
        $getParams = Server::getInstance()->getParameters();
        if (!empty($getParams['output'])) {
            $this->outputFormat = $getParams['output'];
        }

        if (Server::getInstance()->getRequestMethod() === 'GET') {
            $parameters = Server::getInstance()->getParameters();
            $input = '';
            foreach ($parameters as $key => $param) {
                $input .= ($input !== '' ? '&' : '') . $key . '=' . $param;
            }
        } else {
            $input = file_get_contents('php://input');
            $headers = getallheaders();
            if (!empty($headers['Content-Type']) && strpos($headers['Content-Type'], 'application/x-www-form-urlencoded') !== FALSE) {
                parse_str($input, $parameters);
            } else {
                $parameters = array('data' => $input);
            }
        }
        $this->loggerApi->log(get_class($this), ApiLogger::IN, $parameters, $input);

        return $parameters;
    }


    /**
     * @param $statusCode
     * @param array $content
     * @return string
     */
    protected function apiResponse(array $content = array('message' => 'Ok'), $outputFormat = null)
    {
        if ($outputFormat !== null) {
            $this->outputFormat = $outputFormat;
        }
        $this->setOutputFormat();

        if ($this->statusCode === null) {
            $this->statusCode = self::STATUS_200;
        }

        http_response_code($this->statusCode);
        $this->loggerApi->log(get_class($this), ApiLogger::OUT, $content, $this->statusCode);

        return $this->utf8ize($content);
    }

    /**
     * Check parameters
     *
     * @param $params
     * @return bool
     */
    protected function verifyParams(array $params)
    {
        $result = true;
        foreach ($this->parameters as $param => $requiredAction) {
            if ($requiredAction !== false && empty($params[$param])) {
                $result = false;
                break;
            }
            if (is_string($requiredAction)) {
                $arrayAction = explode(',', $requiredAction);
                foreach ($arrayAction as $action) {
                    if (!call_user_func_array("Telelab\\Filter\\Filter::$action", array($params[$param]))) {
                        $result = false;
                        break;
                    }
                }
            } elseif ($requiredAction === true && empty($params[$param])) {
                $result = false;
                break;
            }
        }


        return $result;
    }


    /**
     * Force to utf8 resize data
     *
     * @param array|string $d
     * @return array|string
     */
    protected function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = $this->utf8ize($v);
            }
        } else if (is_string ($d) && mb_detect_encoding($d) === 'UTF-8') {
            $iconv = iconv('UTF-8', 'ISO-8859-1', $d);
            if (json_encode($iconv) !== false) {
                $d = $iconv;
            }
        }
        return $d;
    }


    /**
     * set output format (json, xml, csv, serialize) => Telelab\Format\Format
     */
    protected function setOutputFormat()
    {
        if ($this->outputFormat === null) {
            $this->outputFormat = self::OUTPUT_JSON;
        }

        Globals::set('format', $this->outputFormat);
    }
}
