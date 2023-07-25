<?php
namespace LouisLun\LaravelUnipay;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LouisLun\LaravelUnipay\Contracts\PaymentType;
use LouisLun\LaravelUnipay\Enumerations\RequestType;
use LouisLun\LaravelUnipay\Exceptions\UnipayConnectException;
use LouisLun\LaravelUnipay\Exceptions\UnipayException;

class Unipay
{
    /**
     * production host
     */
    const API_HOST = 'https://api.payuni.com.tw';

    /**
     * staging host
     */
    const SANDBOX_API_HOST = 'https://sandbox-api.payuni.com.tw';

    /**
     * jkospay api uri list
     *
     * @var array
     */
    protected static $apiUris = [
        'UNIPaypage' => '/api/upp',
        'requestCredit' => '/api/credit',
        'requestATM' => '/api/atm',
        'requestCVS' => '/api/cvs',
        'details' => '/api/trade/query',
        'refundCredit' => '/api/trade/close',
        'cancelCreditAuth' => '/api/trade/cancel',
    ];

    /**
     * config
     *
     * @var array
     */
    protected $config;

    /**
     * HTTP Client
     *
     * @var GuzzleHttp\Client
     */
    protected $httpClient;

    /**
     * Merchant ID
     *
     * @var string
     */
    protected $merchantID;

    /**
     * Merchant Key
     *
     * @var string
     */
    protected $merchantKey;

    /**
     * Merchant IV
     *
     * @var string
     */
    protected $merchantIV;

    /**
     * constructor
     * @param array $config config
     * @return self
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
        $this->merchantID = $config['merchantID'];
        $this->merchantKey = $config['merchantKey'];
        $this->merchantIV = $config['merchantIV'];
        $isSanbox = $config['isSandbox'] ?? false;
        $debug = $config['debug'] ?? false;

        // Base URI
        $baseUri = ($isSanbox) ? self::SANDBOX_API_HOST : self::API_HOST;

        // Headers
        $headers = [
            'Content-Type' => 'application/JSON',
            'charset' => 'utf8',
        ];

        $this->httpClient = new Client([
            'base_uri' => $baseUri,
            'headers' => $headers,
            'http_errors' => false,
            'debug' => $debug,
        ]);

        return $this;
    }

    /**
     * sender
     *
     * @return \GuzzleHttp\Client
     */
    public function client()
    {
        return $this->httpClient;
    }

    /**
     * get api uri
     *
     * @param string $key
     * @return string
     */
    public function getAPIUri($key)
    {
        return self::$apiUris[$key];
    }

    public function request(RequestType $type, array $params)
    {
        if ($type == RequestType::CREDIT_CARD) {
            return $this->requestCredit($params);
        } else if ($type == RequestType::ATM) {
            return $this->requestATM($params);
        } else if ($type == RequestType::CVS) {
            return $this->requestCVS($params);
        } else {
            throw new UnipayException('do not support this payment method(' . $type . ')');
        }
    }

    /**
     * request payment by credit card
     *
     * @param array $params parameters(Please refer to https://www.payuni.com.tw/docs/web/#/7/35)
     * @return \LouisLun\LaravelUnipay\Response
     */
    public function requestCredit(array $params)
    {
        return $this->requestHandler('1.0', 'POST', $this->getAPIUri('requestCredit'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * request payment by atm
     *
     * @param array $params parameters(Please refer to https://www.payuni.com.tw/docs/web/#/7/36)
     * @return \LouisLun\LaravelUnipay\Response
     */
    public function requestATM(array $params)
    {
        return $this->requestHandler('1.0', 'POST', $this->getAPIUri('requestATM'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * request payment by cvs
     *
     * @param array $params parameters(Please refer to https://www.payuni.com.tw/docs/web/#/7/37)
     * @return \LouisLun\LaravelUnipay\Response
     */
    public function requestCVS(array $params)
    {
        return $this->requestHandler('1.0', 'POST', $this->getAPIUri('requestCVS'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * refund credit order
     *
     * @param array $params parameters(Please refer to https://www.payuni.com.tw/docs/web/#/7/38)
     * @return \LouisLun\LaravelUnipay\Response
     */
    public function refundCredit(array $params)
    {
        return $this->requestHandler('1.0', 'POST', $this->getAPIUri('refundCredit'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * refund credit order
     *
     * @param array $params parameters(Please refer to https://www.payuni.com.tw/docs/web/#/7/38)
     * @return \LouisLun\LaravelUnipay\Response
     */
    public function refund(array $params)
    {
        return $this->refundCredit($params);
    }

    /**
     * cancel credit's request
     *
     * @param array $params parameters(Please refer to https://www.payuni.com.tw/docs/web/#/7/39)
     * @return \LouisLun\LaravelUnipay\Response
     */
    public function cancelCreditAuth(array $params)
    {
        return $this->requestHandler('1.0', 'POST', $this->getAPIUri('cancelCreditAuth'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * get the detail of order
     *
     * @param array $params parameters(Please refer to https://www.payuni.com.tw/docs/web/#/7/39)
     * @return \LouisLun\LaravelUnipay\Response
     */
    public function details(array $params)
    {
        return $this->requestHandler('2.0', 'POST', $this->getAPIUri('details'), $params, [
            'connect_timeout' => 5,
            'timeout' => 20,
        ]);
    }

    /**
     * request handler
     *
     * @param string $version version(api version)
     * @param string $method method
     * @param string $uri
     * @param array $params
     * @param array $options
     * @return \LouisLun\LaravelJkopay\Response
     */
    public function requestHandler($version, $method, $uri, array $params = [], $options = [])
    {
        $headers = [
            'content-type' => 'application/json'
        ];

        if (!isset($params['MerID'])) {
            $params['MerID'] = $this->merchantID;
        }

        $url = $uri;
        $stats = null;
        $options['on_stats'] = function (\GuzzleHttp\TransferStats $transferStats) use (&$stats) {
            $stats = $transferStats;
        };

        $encryptStr = $this->encrypt($params);
        $request = new Request($method, $url, $headers, json_encode([
            'Version' => $version,
            'MerID' => $this->merchantID,
            'EncryptInfo' => $encryptStr,
            'HashInfo' => $this->hash($encryptStr),
        ]));
        try {
            $response = $this->client()->send($request, $options);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            dd($e->getMessage());
            throw new UnipayConnectException($e->getMessage(), $e->getCode(), $e->getPrevious(), $e->getHandlerContext());
        }

        return new Response($response, $this->merchantKey, $this->merchantIV, $stats);
    }

    private function encrypt(array $data = [], string $merKey = "", string $merIV = "")
    {
        if (!$merKey) {
            $merKey = $this->merchantKey;
        }

        if (!$merIV) {
            $merIV = $this->merchantIV;
        }
        return \unipay_encrypt($data, $merKey, $merIV);
    }

    public function decrypt(string $encryptStr = "") {
        return \unipay_decrypt($encryptStr, $this->merchantKey, $this->merchantIV);
    }

    private function hash(string $encryptStr = "", string $merKey = "", string $merIV = "")
    {
        if (!$merKey) {
            $merKey = $this->merchantKey;
        }

        if (!$merIV) {
            $merIV = $this->merchantIV;
        }
        return \unipay_hash($encryptStr, $merKey, $merIV);
    }
}
