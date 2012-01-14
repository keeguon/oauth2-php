<?php

namespace OAuth2\Tests\Strategy;

class PasswordTest extends \OAuth2\Tests\TestCase
{
  protected $access   = null;
  protected $client   = null;
  protected $mode     = '';
  protected $password = null;

 /**
  * Set up fixtures
  */
  protected function setUp()
  {
    // get client stub
    $this->client = $this->getClientStub('abc', 'def', array('site' => 'https://api.example.com'));

    // create password
    $this->password = new \OAuth2\Strategy\Password($this->client);
  }

  protected function tearDown()
  {
    unset($this->access);
    unset($this->client);
    unset($this->mode);
    unset($this->password);
  }

 /**
  * @covers OAuth2\Strategy\Password::authorize_url()
  */
  public function testAuthorizeUrl()
  {
    $this->setExpectedException('\ErrorException', 'The authorization endpoint is not used in this strategy.');
    $this->password->authorizeUrl();
  }

 /**
  * @covers OAuth2\Strategy\Password::get_token()
  */
  public function testGetToken()
  {
    foreach(array('json', 'formencoded') as $mode) {
      // get_token (mode)
      $this->mode   = $mode;
      $this->access = $this->password->getToken('username', 'password');

      // returns AccessToken with same Client
      $this->assertEquals($this->client, $this->access->getClient());

      // returns AccessToken with $token
      $this->assertEquals('salmon', $this->access->getToken());

      // returns AccessToken with $refresh_token
      $this->assertEquals('trout', $this->access->getRefreshToken());

      // returns AccessToken with $expires_in
      $this->assertEquals(600, $this->access->getExpiresIn());

      // eturns AccessToken with $expires_at
      $this->assertNotNull($this->access->getExpiresAt());
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
