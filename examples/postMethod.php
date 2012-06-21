<?php

require_once '../src/Limocart.php';

$limocart = new \LimocartPhpSdk\Limocart(array(
    'clientId' => 1,
    'clientSecret' => 'secret'
));

var_dump($limocart->api('oauth2/token', array(
    'username' => 'customer1',
    'password' => '12345',
    'grant_type' => 'password',
    'client_id' => 1
), $limocart::METHOD_POST));