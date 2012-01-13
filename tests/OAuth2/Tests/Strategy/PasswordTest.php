<?php

namespace OAuth2\Tests\Strategy;

class PasswordTest extends \PHPUnit_Framework_TestCase
{
  protected
      $client   = null
    , $password = null
    , $mode     = ''
    , $access   = null
  ;

 /**
  * Set up fixtures
  */
  protected function setUp()
  {
    // mock client
    $this->client = $this->getMock('\OAuth2\Client', array('request'), array('abc', 'def', array('site' => 'https://api.example.com')));

    // configure mock
    $this->client->expects($this->any())
                 ->method('request')
                 ->will($this->returnCallback(array($this, 'mockRequest')));

    // create password
    $this->password = new \OAuth2\Strategy\Password($this->client);
  }

  protected function tearDown()
  {
    unset($this->client);
    unset($this->password);
    unset($this->mode);
    unset($this->access);
  }

 /**
  * @covers OAuth2\Strategy\Password::authorize_url()
  */
  public function testAuthorizeUrl()
  {
    $this->setExpectedException('\ErrorException', 'The authorization endpoint is not used in this strategy.');
    $this->password->authorize_url();
  }

 /**
  * @covers OAuth2\Strategy\Password::get_token()
  */
  public function testGetToken()
  {
    foreach(array('json', 'formencoded') as $mode) {
      // get_token (mode)
      $this->mode   = $mode;
      $this->access = $this->password->get_token('username', 'password');

      // returns AccessToken with same Client
      $this->assertEquals($this->client, $this->access->client);

      // returns AccessToken with $token
      $this->assertEquals('salmon', $this->access->token);

      // returns AccessToken with $refresh_token
      $this->assertEquals('trout', $this->access->refresh_token);

      // returns AccessToken with $expires_in
      $this->assertEquals(600, $this->access->expires_in);

      // eturns AccessToken with $expires_at
      $this->assertNotNull($this->access->expires_at);
    }
  }

 /**
  * Intercept all OAuth2\Client::request() calls and mock their responses
  */
  public function mockRequest()
  {
    // retrieve args
    $args = func_get_args();

    // map responses
    $map = array(
        'formencoded'   => new \Guzzle\Http\Message\Response(200, array('Content-Type' => 'application/x-www-form-urlencoded'), 'expires_in=600&access_token=salmon&refresh_token=trout')
      , 'json'          => new \Guzzle\Http\Message\Response(200, array('Content-Type' => 'application/json'), '{"expires_in":600,"access_token":"salmon","refresh_token":"trout"}')
    );
    
    return new \OAuth2\Response($map[$this->mode]);
  }
}
