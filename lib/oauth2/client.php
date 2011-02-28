<?php

namespace OAuth2;

class Client
{
  protected
    $id         = '',
    $secret     = '',
    $site       = '',
    $options    = array()
  ;

  public function __construct($client_id, $client_secret, $opts = array())
  {
    $this->setId($client_id);
    $this->setSecret($client_secret);
    if ($opts['site']) {
      $this->setSite($opts['site']);
      unset($opts['site']);
    }
    $this->setOptions($opts);
  }
  
 /**
  * Get the client id
  *
  * @return string The client id
  */
  public function getId()
  {
    return $this->id;
  }
  
 /**
  * Set the client id
  *
  * @param string $id The client id
  */
  public function setId($id)
  {
    $this->id = $id;
  }

 /**
  * Get the client secret
  *
  * @return string The client secret
  */
  public function getSecret()
  {
    return $this->secret;
  }
  
 /**
  * Set the client secret
  *
  * @param string $secret The client secret
  */
  public function setSecret($secret)
  {
    $this->secret = $secret;
  }
  
 /**
  * Get the provide site
  *
  * @return string The provider site
  */
  public function getSite()
  {
    return $this->site;
  }
  
 /**
  * Set the provider site
  *
  * @param string $site The provide site
  */
  public function setSite($site)
  {
    $this->site = $site;
  }
  
 /**
  * Get options
  *
  * @return array The options
  */  
  public function getOptions()
  {
    return $this->options;
  }
  
 /**
  * Set options
  *
  * @param array $options The options
  */
  public function setOptions($options)
  {
    $this->options = $options;
  }
  
  public function authorize_url($params = null)
  {
    $path = $this->options['authorize_url'] || $this->options['authorize_path'] || "/oauth/authorize";
    return $path.'?'.http_build_query($params);
  }
  
  public function access_token_url($params = null)
  {
    $path = $this->options['access_token_url'] || $this->options['access_token_path'] || "/oauth/access_token";
    return $path.'?'.http_build_query($params);
  }
  
  public function request($verb, $url, $params = array(), $headers = array())
  {
    if ($verb === 'GET') {
    
    } else {
    
    }
  }
  
  public function web_server()
  {
    return new OAuth2\Strategy\WebServer($this);
  }
}

