<?php

namespace LimocartPhpSdk;

if (!function_exists('curl_init')) {
    throw new RuntimeException('Limocart needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new RuntimeException('Limocart needs the JSON PHP extension.');
}

require_once 'Result/StandardResult.php';

use RuntimeException,
    InvalidArgumentException,
    LimocartPhpSdk\Result\StandardResult;



abstract class AbstractLimocart
{

    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_DELETE = 'DELETE';

    /**
     * @var int
     */
    protected $_clientId;

    /**
     * @var string
     */
    protected $_clientSecret;

    /**
     * @var string
     */
    protected $_accessToken;

    /**
     * @var string
     */
    protected $_apiUrl = 'http://api.limocart.com/';

    protected $_curlOpts = array(
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_USERAGENT      => 'limocart-php-sdk',
    );

    public function __construct(array $config)
    {
        if (!isset($config['clientId']) || !$config['clientId']) {
            throw new InvalidArgumentException('Invalid client id');
        }
        if (!isset($config['clientSecret']) || !$config['clientSecret']) {
            throw new InvalidArgumentException('Invalid client secret');
        }

        if (isset($config['accessToken'])) {
            $this->setAccessToken($config['accessToken']);
        }

        $this->setClientId($config['clientId']);
        $this->setClientSecret($config['clientSecret']);
    }

    public function api($path, array $args = array(), $method = self::METHOD_GET)
    {
        $result = new StandardResult();
        $apiUrl = $this->buildApiUrl($path, $args, $method);
        $opts = $this->_curlOpts;

        if (self::METHOD_POST === $method) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
        } elseif (self::METHOD_PUT === $method) {
            $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
        } elseif(self::METHOD_GET === $method) {
            $opts[CURLOPT_HTTPGET] = true;
        } else {
            $opts[CURLOPT_CUSTOMREQUEST ] = $method;
        }

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, $opts);

        $result->setVariables(json_decode(curl_exec($ch)));
        $result->setResponseInfo(curl_getinfo($ch));

        $responseInfo = $result->getResponseInfo();
        if (isset($responseInfo['http_code'])
            && 200 === $responseInfo['http_code']) {
            $result->setSuccess(true);
        }

        curl_close($ch);

        return $result;
    }

    public function buildApiUrl($path, array $args, $method)
    {
        $apiUrl = $this->getApiUrl() . $path;
        $params = array();

        if (self::METHOD_GET === $method) {
            $params =  $args;
        }

        if (null !== $this->getAccessToken()) {
            $params['access_token'] = $this->getAccessToken();
        }

        $params['client_id'] = $this->getClientId();
        $params['client_secret'] = $this->getClientSecret();

        if (false === strpos($apiUrl, '?')) {
            $apiUrl .= '?';
        } else {
            $apiUrl .= '&';
        }

        $apiUrl .= http_build_query($params);

        return $apiUrl;
    }

    /**
     * @param int $clientId
     */
    public function setClientId($clientId)
    {
        $this->_clientId = $clientId;
    }

    /**
     * @return int
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @param string $clientSecret
     */
    public function setClientSecret($clientSecret)
    {
        $this->_clientSecret = $clientSecret;
    }

    /**
     * @return string
     */
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->_accessToken = $accessToken;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    /**
     * @param string $apiUrl
     */
    public function setApiUrl($apiUrl)
    {
        $this->_apiUrl = $apiUrl;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->_apiUrl;
    }

}