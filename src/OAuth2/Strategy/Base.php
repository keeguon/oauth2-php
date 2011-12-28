<?php

namespace OAuth2\Strategy;

class Base
{
  protected
    $client = null
  ;

 /**
  * A new instance of Base
  *
  * @param \OAuth2\Client $client The OAuth2::Client instance
  */
  public function __construct($client)
  {
    $this->client = $client;
  }

 /**
  * The OAuth client_id and client_secret
  *
  * @return array
  */
  public function client_params()
  {
    return array('client_id' => $this->client->id, 'client_secret' => $this->client->secret);
  }
}
