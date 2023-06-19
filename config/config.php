<?php

return [
    // unipay merchant id
    'merchantID' => env('UNIPAY_MERCHANT_ID', ''),

    // unipay merchant key
    'merchantKey' => env('UNIPAY_MERCHANT_KEY', ''),

    // unipay merchant iv
    'merchantIV' => env('UNIPAY_MERCHANT_IV', ''),

    // is sandbox
    'isSandbox' => env('UNIPAY_IS_SANDBOX', true),

    // guzzleHttp debug mode
    'debug' => env('UNIPAY_GUZZLEHTTP_DEBUG', false),

    'returnURL' => env('UNIPAY_RETURN_URL', ''),

    'notifyURL' => env('UNIPAY_NOTIFY_URL', ''),

    'UUPReturnURL' => env('UNIPAY_UUP_RETURN_URL', ''),

    'UUPNotifyURL' => env('UNIPAY_UUP_NOTIFY_URL', ''),

    'UUPBackURL' => env('UNIPAY_UUP_BACK_URL', ''),
];