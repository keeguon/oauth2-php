<?php

namespace OAuth2\Tests;

class ClientTest extends \PHPUnit_Framework_TestCase
{
  protected $client;

  protected function setUp()
  {
    $this->client = new \OAuth2\Client('abc', 'def', array(
        'redirect_uri' => 'http://multipass.local/callback'
    ));
  }

  public function testConstructor()
  {
    $this->assertEquals('abc', $this->client->id);
    $this->assertEquals('def', $this->client->secret);
  }
}
