<?php

namespace OAuth2\Strategy;

class Base
{
  protected
    $client = null
  ;

  public function __construct($client)
  {
    $this->setClient($client);
  }
  
 /**
  * Get the client object
  *
  * @return OAuth2\Client The client object
  */
  public function getClient()
  {
    return $this->client;
  }
  
 /**
  * Set the client object
  *
  * @param OAuth2\Client $client The client object
  */
  public function setClient($client)
  {
    $this->client = $client;
  }
  
  public function authorize_url($options = array())
  {
    return $this->getClient()->authorize_url($this->authorize_params($options));
  }
  
  public function authorize_params($options = array())
  {
    return array_merge(array('client_id' => $this->getClient()->getId()), $options);
  }
  
  public function access_token_url($options = array())
  {
    return $this->getClient()->access_token_url($this->access_token_params($options));
  }
  
  public function access_token_params($options = array())
  {
    return array_merge(array(
      'client_id'     => $this->getClient()->getId(),
      'client_secret' => $this->getClient()->getSecret()
    ), $options);
  }
}

