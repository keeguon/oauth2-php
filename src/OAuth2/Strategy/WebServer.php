<?php

namespace OAuth2\Strategy;

class WebServer extends Base
{
  public function authorize_params($options = array())
  {
    return parent::authorize_params(array_merge($options, array('type' => 'web_server')));
  }
  
 /**
  * Retrieve an access token given the specified validation code.
  * Note that you must also provide a <tt>:redirect_uri</tt> option
  * in order to successfully verify your request for most OAuth 2.0
  * endpoints.
  */
  public function get_access_token($code, $options = array())
  {
    $response = $this->getClient()->request('POST', $this->getClient()->getAccessTokenUrl(), $this->access_token_params($code, $options));
    
    $params = json_decode($response);
    
    $access = $params['access_token'];
    $refresh = (isset($params['refresh_token'])) ? $params['refresh_token'] : null ;
    $expires_in = (isset($params['expires_in'])) ? $params['expires_in'] : null ;
    return new OAuth2\AccessToken($this->getClient(), $access, $refresh, $expires_in, $params);
  }
  
  public function access_token_params($code, $options = array())
  {
    return parent::access_token_params(array_merge($options, array(
      'type' => 'web_server',
      'code' => $code
    )));
  }
}

