<?php

require_once '../src/Limocart.php';

$limocart = new \LimocartPhpSdk\Limocart(array(
    'clientId' => 1,
    'clientSecret' => 'secret'
));

var_dump($limocart->api('maps/areas/1', array(
    'shortName' => 'US'
), $limocart::METHOD_PUT));