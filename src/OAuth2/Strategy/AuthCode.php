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
  public function authorize_params($params = array())
  {
    return array_merge($params, array(
        'response_type' => 'code'
      , 'client_id'     => $this->client->id
    ));
  }

 /**
  * The authorization URL endpoint of the provider
  *
  * @param  array $params Additional query parameters for the URL
  * @return string
  */
  public function authorize_url($params = array())
  {
    return $this->client->authorize_url(array_merge($this->authorize_params(), $params));
  }

 /**
  * Retrieve an access token given the specified validation code.
  *
  * @param string $code   The Authorization Code value
  * @param array  $params Additional params
  * @param array  $opts   Options 
  */
  public function get_token($code, $params = array(), $opts = array())
  {
    $params = array_merge(array(
        'grant_type' => 'authorization_code'
      , 'code'       => $code
    ), $this->client_params, $params);
    return $this->client->get_token($params, $opts);
  }
}
