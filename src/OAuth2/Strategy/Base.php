<?php

namespace OAuth2\Strategy;

class Base
{
  protected $client = null;

 /**
  * A new instance of Base
  *
  * @param \OAuth2\Client $client The OAuth2::Client instance
  */
  public function __construct($client = null)
  {
    // Throw exception if the client arg isn't an instance of OAuth2\Client
    if (!$client instanceof \OAuth2\Client) {
      throw new \InvalidArgumentException('The provided client arguments isn\'t an instance of an OAuth2\Client object');
    }
    $this->client = $client;
  }
}
