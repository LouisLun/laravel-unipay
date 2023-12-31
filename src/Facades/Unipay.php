<?php
namespace LouisLun\LaravelUnipay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LouisLun\LaravelUnipay\Response request(\LouisLun\LaravelUnipay\Enumerations\RequestType $type, array $params) 向Unipay請求付款
 * @method static \LouisLun\LaravelUnipay\Response refund($params) 退款
 * @method static \LouisLun\LaravelUnipay\Response details($params) 查詢交易紀錄
 * @method static array decrypt(string $encryptStr = "") 解碼
 *
 * @see \LouisLun\LaravelUnipay\Unipay
 */
class Unipay extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \LouisLun\LaravelUnipay\Unipay::class;
    }
}
