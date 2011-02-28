<?php

namespace OAuth2;

class AccessToken
{
  protected
    $client        = null,
    $token         = '',
    $refresh_token = '',
    $expires_in    = null,
    $expires_at    = null,
    $params        = array(),
    $token_param   = 'access_token'
  ;
  
  public function __construct($client, $token, $refresh_token = null, $expires_in = null, $params = array())
  {
    $this->setClient($client);
    $this->setToken($token);
    $this->setRefreshToken($refresh_token);
    if ($expires_in) {
      $this->expires_in = $expires_in;
      $this->expires_at = time() + $expires_in;
    }
    $this->setParams($params);
  }
}
