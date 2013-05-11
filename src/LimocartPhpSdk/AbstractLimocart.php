<?php

namespace LimocartPhpSdk;

if (!function_exists('curl_init')) {
    throw new RuntimeException('Limocart needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
    throw new RuntimeException('Limocart needs the JSON PHP extension.');
}

require_once 'Result/StandardResult.php';

use InvalidArgumentException;
use RuntimeException;
use LimocartPhpSdk\Result\StandardResult;

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

    /**
     * @var \Zend\Cache\Storage\StorageInterface
     */
    protected $_cache;

    /**
     * @var int
     */
    protected $_currencyId = 147;

    /**
     * @var string
     */
    protected $_locale = 'en-US';

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

        if (isset($config['cache'])) {
            $this->setCache($config['cache']);
        }

        $this->setClientId($config['clientId']);
        $this->setClientSecret($config['clientSecret']);
    }

    public function api(
        $path,
        array $args = array(),
        $method = self::METHOD_GET,
        $cache = false,
        $cacheKey = null
    )
    {
        $result = new StandardResult();
        $apiUrl = $this->buildApiUrl($path, $args, $method);
        $opts = $this->_curlOpts;

        if (null === $cacheKey) {
            $cacheKey = sha1(strtolower($apiUrl));
        }

        if (self::METHOD_POST === $method) {
            $opts[CURLOPT_POST] = true;

            /**
             * @todo gecici olarak bu sekilde ayirdim. Diger turlu dosya upload olmuyor.
             */
            if (isset($_FILES) && is_array($_FILES) && count($_FILES)) {
                $opts[CURLOPT_POSTFIELDS] = $args;
            } else {
                $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
            }

        } elseif (self::METHOD_PUT === $method) {
            $opts[CURLOPT_CUSTOMREQUEST] = 'PUT';
            $opts[CURLOPT_POSTFIELDS] = http_build_query($args);
        } elseif(self::METHOD_GET === $method) {
            $opts[CURLOPT_HTTPGET] = true;
            if ($cache && $this->getCache()->hasItem($cacheKey)) {
                return $this->getCache()->getItem($cacheKey);
            }
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

        if ($cache) {
            $this->getCache()->setItem($cacheKey, $result);
        }

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


        $params['currencyId'] = $this->getCurrencyId();
        $params['locale'] = $this->getLocale();

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

    /**
     * @param \Zend\Cache\Storage\StorageInterface $cache
     */
    public function setCache($cache)
    {
        $this->_cache = $cache;
    }

    /**
     * @return \Zend\Cache\Storage\StorageInterface
     */
    public function getCache()
    {
        return $this->_cache;
    }

    /**
     * @param int $currencyId
     */
    public function setCurrencyId($currencyId)
    {
        $this->_currencyId = $currencyId;
    }

    /**
     * @return int
     */
    public function getCurrencyId()
    {
        return $this->_currencyId;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->_locale = $locale;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->_locale;
    }


}