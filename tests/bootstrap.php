<?php

namespace OAuth2\Tests;

require __DIR__.'/../src/OAuth2.php';

// Your code here
$client = new \OAuth2\Client('148293211852192', '3f5cdb5a50f68f792a6772b13990e635', array(
  'site'             => 'https://graph.facebook.com',
  'authorize_url'    => 'https://graph.facebook.com/oauth/authorize',
  'access_token_url' => 'https://graph.facebook.com/oauth/access_token'
));
$access_token = new \OAuth2\AccessToken($client, '148293211852192|dc812661a396525c687885f2-688704171|X1U7HYfYgYB5x3NfVOAQJf2XdOE');
$response = $access_token->get($client->getSite().'/me', array('access_token' => $access_token->getToken()));
print_r($response);

