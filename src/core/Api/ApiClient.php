<?php
namespace Hyla\Api;

/**
 * Class ApiClient
 * @package Hyla\Api
 */
class ApiClient
{
    /**
     * METHODS
     */
    const GET = 'GET';
    const POST = 'POST';
    const PUT = 'PUT';
    const DELETE = 'DELETE';

    /**
     * @var string
     */
    private $serverName;

    /**
     * @var string complete url to use
     */
    private $url;

    /**
     * @var string body of request
     */
    private $params;

    /**
     * @var string http method to use
     */
    private $method;

    /**
     * @var bool check if result status === 'ok'
     */
    private $isOkResult;

    /**
     * @var int response status code
     */
    private $responseStatus;

    /**
     * ApiClient constructor.
     * @param string $serverName
     * @param string $method
     */
    public function __construct($serverName, $method = self::GET)
    {
        $this->serverName = $serverName;
        $this->method = $method;
    }


    /**
     * @param array $paramsRoute
     */
    public function setParamsRoute(array $paramsRoute = [])
    {
        $this->url = $this->buildUrl($paramsRoute);
    }


    /**
     * @param array $paramsBody
     */
    public function setParamsBody(array $paramsBody = [])
    {
        $this->params = $this->buildBody($paramsBody);
    }


    /**
     * Execute request with good url and formatted parameters
     *
     * @return mixed
     */
    public function execute($brutData = false)
    {
        if ($this->url === null) {
            $this->setParamsRoute();
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        switch($this->method) {
            case self::GET:
                curl_setopt($ch, CURLOPT_URL, $this->url . $this->params);
                break;
            case self::POST:
                curl_setopt($ch, CURLOPT_URL, $this->url);
                curl_setopt($ch, CURLOPT_POST, substr_count($this->params, '='));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
                break;
            case self::PUT:
                curl_setopt($ch, CURLOPT_URL, $this->url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::PUT);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
                break;
            case self::DELETE:
                curl_setopt($ch, CURLOPT_URL, $this->url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, self::DELETE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->params));
                break;
        }

        $result = curl_exec($ch);
        $this->responseStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($brutData) {
            return $result;
        }

        $jsonDecode = json_decode($result, true);
        $this->setIsOkResult($jsonDecode);

        return $jsonDecode;
    }


    /**
     * @param array $paramsRoute
     * @return string
     */
    private function buildUrl(array $paramsRoute)
    {
        return $this->serverName . implode('/', $paramsRoute);
    }


    /**
     * @param array $paramsBody
     * @return string
     */
    private function buildBody(array $paramsBody, $parentKey = '')
    {
        $bodyStr = '';
        $isNewParams = $parentKey == '';
        foreach ($paramsBody as $key => $value) {
            $oldParentKey = $parentKey;
            $parentKey .= $parentKey !== '' ? "[$key]" : $key;
            if (! empty($bodyStr)) {
                $bodyStr .= '&';
            }
            if (is_array($value)) {
                $bodyStr .= $this->buildBody($value, $parentKey);
            } else {
                $bodyStr .= $parentKey . '=' . $value;
            }

            $parentKey = $isNewParams ? '' : $oldParentKey;
        }
        if ($this->method === self::GET && $bodyStr !== '') {
            $bodyStr = "?$bodyStr";
        }

        return $bodyStr;
    }


    /**
     * @param array $result
     */
    private function setIsOkResult(array $result = null)
    {
        $this->isOkResult = $result !== null && isset($result['message']) && strtolower($result['message']) === 'ok';
    }


    /**
     * @return bool
     */
    public function getIsOkResult()
    {
        return $this->isOkResult;
    }


    /**
     * Return response status code
     *
     * @return int
     */
    public function getResponseStatusCode()
    {
        return $this->responseStatus;
    }
}
