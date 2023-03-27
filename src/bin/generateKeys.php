<?php
$config = array(
    "digest_alg" => "sha512",
    "private_key_bits" => 4096,
    "private_key_type" => OPENSSL_KEYTYPE_RSA,
);

$privateKey = '';
$publicKey = '';

$res = openssl_pkey_new($config);
openssl_pkey_export($res, $privateKey);

$public_data = openssl_pkey_get_details($res);
$publicKey = $public_data["key"];

echo $privateKey . PHP_EOL . PHP_EOL . $publicKey;

echo str_replace(PHP_EOL, '\\n', $privateKey) . PHP_EOL . PHP_EOL;
echo str_replace(PHP_EOL, '\\n', $publicKey) . PHP_EOL . PHP_EOL;