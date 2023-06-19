<?php
namespace LouisLun\LaravelUnipay\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \LouisLun\LaravelUnipay\Response request($params) 向街口請求付款
 * @method static \LouisLun\LaravelUnipay\Response refund($params) 退款
 * @method static \LouisLun\LaravelUnipay\Response details($params) 查詢交易紀錄
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
