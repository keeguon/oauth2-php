<?php

namespace OAuth2\Strategy;

class AuthCode extends \OAuth2\Strategy\Base
{
 /**
  * The required query parameters for the authorize URL
  *
  * @param  array $params Additional query parameters
  * @return array
  */
  public function authorizeParams($params = array())
  {
    return array_merge(array(
        'response_type' => 'code'
      , 'client_id'     => $this->client->getId()
    ), $params);
  }

 /**
  * The authorization URL endpoint of the provider
  *
  * @param  array $params Additional query parameters for the URL
  * @return string
  */
  public function authorizeUrl($params = array())
  {
    $params = array_merge($this->authorizeParams(), $params);
    return $this->client->authorizeUrl($params);
  }

 /**
  * Retrieve an access token given the specified validation code.
  *
  * @param string $code   The Authorization Code value
  * @param array  $params Additional params
  * @param array  $opts   Options
  */
  public function getToken($code, $params = array(), $opts = array())
  {
    $params = array_merge(array(
        'grant_type' => 'authorization_code'
      , 'code'       => $code
    ), $params);
    return $this->client->getToken($params, $opts);
  }
}
