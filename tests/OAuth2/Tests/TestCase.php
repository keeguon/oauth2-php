<?php

namespace OAuth2\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
 /**
  * Creates a stub client where the OAuth\Client::request() method gets mocked
  *
  * @param  string        $client_id     The client ID
  * @param  string        $client_secret The client secret
  * @param  array         $opts          An array of additionnal options
  * @return OAuth2\Client $client        The stubbed client
  */
  public function getClientStub($client_id, $client_secret, $opts = array())
  {
    // create a client stub
    $client = $this->getMock('\OAuth2\Client', ['getResponse'], [$client_id, $client_secret, $opts]);

    // configure client stub
    $client->expects($this->any())
           ->method('getResponse')
           ->will($this->returnCallback([$this, 'mockGetResponse']));

    return $client;
  }
}
