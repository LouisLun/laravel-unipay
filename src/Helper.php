<?php

if (! function_exists('unipay_encrypt')) {
    function unipay_encrypt(array $data = [], string $merKey = "", string $merIV = "")
    {
        $tag = ""; //預設為空
        $encrypted = openssl_encrypt(http_build_query($data), "aes-256-gcm", trim($merKey), 0, trim($merIV), $tag);
        return trim(bin2hex($encrypted . ":::" . base64_encode($tag)));
    }
}

if (! function_exists('unipay_decrypt')) {
    function unipay_decrypt(string $encryptStr = "", string $merKey = "", string $merIV = "")
    {
        list($encryptData, $tag) = explode(":::", hex2bin($encryptStr), 2);
        $input = openssl_decrypt($encryptData, "aes-256-gcm", trim($merKey), 0, trim($merIV), base64_decode($tag));
        parse_str($input, $output);
        return $output;
    }
}

if (! function_exists('unipay_hash')) {
    function unipay_hash(string $encryptStr = "", string $merKey = "", string $merIV = "")
    {
        return strtoupper(hash("sha256", "$merKey$encryptStr$merIV"));
    }
}