<?php
namespace LouisLun\LaravelUnipay;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class Response implements Arrayable, ArrayAccess, Jsonable
{
    /**
     * response
     *
     * @var \GuzzleHttp\Psr7\Response
     */
    public $response;

    /**
     * transfer stats
     *
     * @var \GuzzleHttp\TransferStats
     */
    public $stats;

    /**
     * body array cache
     *
     * @var array
     */
    protected $bodyArrayCache;

    /**
     * raw body cache
     *
     * @var array
     */
    protected $bodyCache;

    /**
     * merchant key
     *
     * @var string
     */
    private $merchantKey;

    /**
     * merchant iv
     *
     * @var string
     */
    private $merchantIV;

    /**
     * constructor
     *
     * @param \GuzzleHttp\Psr7\Response $response
     * @param string merchant key
     * @param string merchant iv
     * @param \GuzzleHttp\TransferStats|null $stats
     * @return self
     */
    public function __construct(\GuzzleHttp\Psr7\Response $response, string $merchantKey = "", string $merchantIV = "", \GuzzleHttp\TransferStats|null $stats = null)
    {
        $this->response = $response;
        $this->stats = $stats;

        if (!$this->bodyArrayCache) {
            $this->bodyArrayCache = json_decode($this->getRawBody(), true, 512, JSON_BIGINT_AS_STRING);
            if (isset($this->bodyArrayCache['EncryptInfo'])) {
                $this->bodyArrayCache['DecryptInfo'] = unipay_decrypt(
                    $this->bodyArrayCache['EncryptInfo'],
                    $merchantKey,
                    $merchantIV
                );
            }
        }
    }

    /**
     * check the resposne is success
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->response->getStatusCode() == 200;
    }

    /**
     * @return \GuzzleHttp\TransferStats|null
     */
    public function getStats()
    {
        return $this->stats;
    }

    /**
     * raw body
     *
     * @return string
     */
    public function getRawBody()
    {
        if (!$this->bodyCache) {
            $this->bodyCache = $this->response->getBody()->getContents();
        }

        return $this->bodyCache;
    }

    /**
     * return response data array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->bodyArrayCache;
    }

    /**
     * return response data array
     *
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->toArray()[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->toArray()[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        return;
    }

    public function offsetUnset(mixed $offset): void
    {
        return;
    }

    public function __get($key)
    {
        return $this->offsetGet($key);
    }
}
