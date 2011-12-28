<?php

if (false === class_exists('Symfony\Component\ClassLoader\UniversalClassLoader', false)) {
  require_once __DIR__.'/vendor/Symfony/Component/ClassLoader/UniversalClassLoader.php';
}

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Symfony'  => __DIR__.'/vendor'
  , 'OAuth2'   => __DIR__.'/src',
));
$loader->register();


// Test run against the Facebook API
$client = new \OAuth2\Client(
    '139898982694516'
  , 'afde14a12e1447c086132f795b859cf5'
  , array('site' => 'https://graph.facebook.com', 'token_url' => '/oauth/access_token')
);
// echo $client->auth_code()->authorize_url(array('redirect_uri' => 'http://advertspray.local/auth/facebook/callback'));
// code=AQA_flIXf3RSZkGP2aU1WHCDZiVz8K7TQJMi7QRLkezWNXuNT9uhN83VkgnCzFRqUuHIpcLNP_AgJs_smjXfi3Je-Eane5l-7fZvX63VrN3oN9TLjJIUT05tgsUB4Y47VefT1cMl2IjwHkw3f7qu-SoLQ9ugb2mbJVLyjm-T4Zvsqt93KLV9YtJSs0rAErUw0PE
$token = $client->auth_code()->get_token(
    'AQA_flIXf3RSZkGP2aU1WHCDZiVz8K7TQJMi7QRLkezWNXuNT9uhN83VkgnCzFRqUuHIpcLNP_AgJs_smjXfi3Je-Eane5l-7fZvX63VrN3oN9TLjJIUT05tgsUB4Y47VefT1cMl2IjwHkw3f7qu-SoLQ9ugb2mbJVLyjm-T4Zvsqt93KLV9YtJSs0rAErUw0PE'
  , array('parse' => 'query', 'redirect_uri' => 'http://advertspray.local/auth/facebook/callback')
  , array('header_format' => 'OAuth %s', 'param_name' => 'access_token')
);
print_r($token->get($client->site.'/me'));
