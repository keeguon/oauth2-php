<?php

namespace OAuth2\Tests;

require(__DIR__.'/../src/OAuth2.php');

// Your code here
$foursquare = new \OAuth2\Client('', '', array(
  'site'             => 'https://api.foursquare.com/v2',
  'authorize_url'    => 'https://foursquare.com/oauth2/authorize',
  'access_token_url' => 'https://foursquare.com/oauth2/access_token'
));
$foursquare_token = new \OAuth2\AccessToken($foursquare, '');
$response = $foursquare_token->get($foursquare->getSite().'/users/self', array('oauth_token' => $foursquare_token->getToken()));
print_r($response);
