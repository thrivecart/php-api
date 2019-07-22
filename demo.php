<?php
include 'vendor/autoload.php';

\ThriveCart\Api::setBaseUri('http://dev-thrivecart.com');

$provider = new \ThriveCart\Oauth([
    'clientId'                => 'example-client-1',
    'clientSecret'            => 'examplepass',
    'redirectUri'             => 'http://localhost/thrivecart-api-demo/oauth_example.php', // URL to be redirected to after OAuth acceptance
]);

$tc = new \ThriveCart\Api('52ebd2e01c4cb5ca825175093feaf57fdcb9072b');

print_r($tc->getProducts());