<?php
namespace LouisLun\LaravelUnipay;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use LouisLun\LaravelUnipay\Exceptions\UnipayConnectException;

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
     * request handler
     *
     * @param string $method method
     * @param string $uri
     * @param array $params
     * @param array $options
     * @return \LouisLun\LaravelJkopay\Response
     */
    public function requestHandler($method, $uri, array $params = [], $options = [])
    {
        $headers = [];
        $authParams = '';
        $url = $uri;
        $body = '';
        if ($method == 'GET') {
            $rows = [];
            foreach ($params as $key => $val) {
                if (is_array($val)) {
                    $rows[] = $key . '=' . implode(',', $val);
                } else {
                    $rows[] = $key . '=' . $val;
                }
            }
            $authParams = implode('&', $rows);
            $url = "$uri?$authParams";
        } else {
            $authParams = json_encode($params);
            $body = $authParams;
        }

        // set Digest
        // $headers['Digest'] = $this->getAuthSignature($this->secretKey, $authParams);
        // $headers['Api-Key'] = $this->apiKey;

        $stats = null;
        $options['on_stats'] = function (\GuzzleHttp\TransferStats $transferStats) use (&$stats) {
            $stats = $transferStats;
        };

        $request = new Request($method, $url, $headers, $body);
        try {
            $response = $this->client()->send($request, $options);
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            throw new UnipayConnectException($e->getMessage(), $e->getCode(), $e->getPrevious(), $e->getHandlerContext());
        }

        return new Response($response, $stats);
    }

    public static function encrypt(array $data = [], string $merKey = "", string $merIV = "")
    {
        $tag = ""; //預設為空
        $encrypted = openssl_encrypt(http_build_query($data), "aes-256-gcm", trim($merKey), 0, trim($merIV), $tag);
        return trim(bin2hex($encrypted . ":::" . base64_encode($tag)));
    }

    public static function decrypt(string $encryptStr = "", string $merKey = "", string $merIV = "")
    {
        list($encryptData, $tag) = explode(":::", hex2bin($encryptStr), 2);
        return openssl_decrypt($encryptData, "aes-256-gcm", trim($merKey), 0, trim($merIV), base64_decode($tag));
    }

    public static function hash(string $encryptStr = "", string $merKey = "", string $merIV = "")
    {
        return strtoupper(hash("sha256", "$merKey$encryptStr$merIV"));
    }
}
