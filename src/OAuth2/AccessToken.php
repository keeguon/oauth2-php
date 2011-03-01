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
  
 /**
  * Get the OAuth2 client
  *
  * @return OAuth2\Client The OAuth2 client
  */
  public function getClient()
  {
    return $this->client;
  }
  
 /**
  * Set the OAuth2 client
  *
  * @param OAuth2\Client $client The OAuth2 client
  */
  public function setClient($client)
  {
    $this->client = $client;
  }
  
 /**
  * Get the access token
  *
  * @return string The access token
  */
  public function getToken()
  {
    return $this->token;
  }
  
 /**
  * Set the access token
  *
  * @param string $token The access token
  */
  public function setToken($token)
  {
    $this->token = $token;
  }
  
 /**
  * Get the refresh token
  *
  * @return string The refresh token
  */
  public function getRefreshToken()
  {
    return $this->refresh_token;
  }
  
 /**
  * Set the refresh token
  *
  * @param string $refresh_token The refresh token
  */
  public function setRefreshToken($refresh_token)
  {
    $this->refresh_token = $refresh_token;
  }
  
 /**
  * Get the token expiration date (in second since the Unix Epoch)
  *
  * @return integer The token expiration date
  */
  public function getExpiresAt()
  {
    return $this->expires_at;
  }
  
 /**
  * Add a param
  *
  * @param  mixed $key   The parameter key
  * @param  mixed $value The parameter value
  * @return boolean
  */
  public function addParam($key = null, $value = null)
  {
    if (!$value) return false;
    
    if ($key) $this->params[$key] = $value;
    else $this->params[] = $value;
    
    return true;
  }
  
 /**
  * Get the params
  *
  * @return array The params
  */
  public function getParams()
  {
    return $this->params;
  }
  
 /**
  * Set the params
  *
  * @param array $params The params
  */
  public function setParams($params)
  {
    $this->params = $params;
  }
  
  public function request($verb, $path, $params = array(), $headers = array())
  {
    $params  = array_merge($params, $this->getParams());
    $headers = array_merge($headers, array('Authorization' => 'OAuth ${$this->getToken()}'));
    return $this->getClient()->request($verb, $path, $params, $headers);
  }
  
  public function get($path, $params = array(), $headers = array())
  {
    return $this->request('GET', $path, $params, $headers);
  }
  
  public function post($path, $params = array(), $headers = array())
  {
    return $this->request('POST', $path, $params, $headers);
  }
  
  public function put($path, $params = array(), $headers = array())
  {
    return $this->request('PUT', $path, $params, $headers);
  }
  
  public function delete($path, $params = array(), $headers = array())
  {
    return $this->request('DELETE', $path, $params, $headers);
  }
}
