<?php
namespace LouisLun\LaravelUnipay\Tests;

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
            Unipay::encrypt(
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
            'MerID=AAA&MerTradeNo=BBB',
            Unipay::decrypt($encryptStr, $merKey, $merIV),
        );
    }
}