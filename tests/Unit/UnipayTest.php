<?php
namespace LouisLun\LaravelUnipay\Tests;

use LouisLun\LaravelUnipay\Enumerations\RequestType;
use LouisLun\LaravelUnipay\Unipay;
use PHPUnit\Framework\TestCase;

class UnipayTest extends TestCase
{
    public function test_Unipay_encrypt()
    {
        $data = [
            "MerID" => "AAA",
            "MerTradeNo" => "BBB",
        ];
        $merKey="12345678901234567890123456789012";
        $merIV="1234567890123456";

        $this->assertEquals(
            '47396636346f66735853533167396942344f587a3775696b34752b596e70452b3a3a3a4373354a5a5143306b7153467531354c6e6f554a69773d3d',
            unipay_encrypt(
                $data,
                $merKey,
                $merIV
            )
        );
    }

    public function test_Unipay_decrypt()
    {
        $encryptStr ="47396636346f66735853533167396942344f587a3775696b34752b596e70452b3a3a3a4373354a5a5143306b7153467531354c6e6f554a69773d3d";
        $merKey="12345678901234567890123456789012";
        $merIV="1234567890123456";

        $this->assertEquals(
            [
                "MerID" => "AAA",
                "MerTradeNo" => "BBB"
            ],
            unipay_decrypt($encryptStr, $merKey, $merIV),
        );
    }

    public function test_request()
    {
        $unipay = new Unipay([
            // unipay merchant id
            'merchantID' => env('UNIPAY_MERCHANT_ID', 'S06382279'),

            // unipay merchant key
            'merchantKey' => env('UNIPAY_MERCHANT_KEY', 'TCDWPKobnEfB5zXpv92wpq6R582CmDoa'),

            // unipay merchant iv
            'merchantIV' => env('UNIPAY_MERCHANT_IV', 'qDKYknz053nsB3cT'),

            // is sandbox
            'isSandbox' => env('UNIPAY_IS_SANDBOX', true),

            // guzzleHttp debug mode
            'debug' => env('UNIPAY_GUZZLEHTTP_DEBUG', false),

            'returnURL' => env('UNIPAY_RETURN_URL', ''),

            'notifyURL' => env('UNIPAY_NOTIFY_URL', ''),
        ]);

        // 信用卡測試
        $ret = $unipay->request(RequestType::CREDIT_CARD, [
            'MerTradeNo' => 'test' . time(),
            'TradeAmt' => 100,
            'Timestamp' => time(),
            'CardNo' => '4147631000000001',
            'CardCVC' => '222',
            'ProdDesc' => 'test',
            'CardExpired' => '0228',
        ]);

        // $ret = $unipay->request(RequestType::ATM, [
        //     'MerTradeNo' => 'test' . time(),
        //     'TradeAmt' => 100,
        //     'Timestamp' => time(),
        //     'BankType' => '822',
        //     'ProdDesc' => 'test',
        //     'ExpireDate' => date('Y-m-d'),
        // ]);

        dd($ret->toArray());
    }
}