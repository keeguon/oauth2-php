<?php

namespace OAuth2\Tests;

class AccessTokenTest extends \OAuth2\Tests\TestCase
{
 /**
  * @var OAuth2\AccessToken
  * @var OAuth2\Client
  * @var string
  * @var string
  * @var string
  */
  protected $accessToken = null;
  protected $client      = null;
  protected $refreshNody = '';
  protected $token       = 'monkey';
  protected $tokenBody   = '';

 /**
  * Sets up the fixture.
  * This method is called before a test is executed.
  */
  protected function setUp()
  {
    // default properties
    $this->tokenBody   = json_encode(array('access_token' => 'foo', 'expires_in' => 600, 'refresh_token' => 'bar'));
    $this->refreshBody = json_encode(array('access_token' => 'refreshed_foo', 'expires_in' => 600, 'refresh_token' => 'refresh_bar'));

    // get client stub
    $this->client = $this->getClientStub('abc', 'def', array('site' => 'https://api.example.com'));

    // instantiate access_token
    $this->accessToken = new \OAuth2\AccessToken($this->client, $this->token);
  }

  protected function tearDown()
  {
    unset($this->accessToken);
    unset($this->client);
    unset($this->refreshBody);
    unset($this->token);
    unset($this->tokenBody);
  }

 /**
  * @covers OAuth2\AccessToken::__construct()
  */
  public function testConstructorBuildsAccessToken()
  {
    // assigns client and token
    $this->assertEquals($this->client, $this->accessToken->getClient());
    $this->assertEquals($this->token, $this->accessToken->getToken());

    // assigns extra params
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('foo' => 'bar'));
    $this->assertArrayHasKey('foo', $target->getParams());
    $this->assertEquals('bar', $target->getParam('foo'));

    // initialize with a Hash
    $hash   = array('access_token' => $this->token, 'expires_in' => time() + 200, 'foo' => 'bar');
    $target = \OAuth2\AccessToken::fromHash($this->client, $hash);
    $this->assertInitializeToken($target);

    // initalizes with a form-urlencoded key/value string
    $kvform = "access_token={$this->token}&expires_in={time() + 200}&foo=bar";
    $target = \OAuth2\AccessToken::fromKvform($this->client, $kvform);
    $this->assertInitializeToken($target);

    // sets options
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('param_name' => 'foo', 'header_format' => 'Bearer %', 'mode' => 'body'));
    $this->assertEquals('foo', $target->options['param_name']);
    $this->assertEquals('Bearer %', $target->options['header_format']);
    $this->assertEquals('body', $target->options['mode']);
  }

 /**
  * @covers OAuth2\AccessToken::request()
  * @covers OAuth2\AccessToken::get()
  * @covers OAuth2\AccessToken::post()
  * @covers OAuth2\AccessToken::put()
  * @covers OAuth2\AccessToken::delete()
  */
  public function testRequest()
  {
    // header mode
    $this->accessToken->options['mode'] = 'header';
    foreach (array('GET', 'POST', 'PUT', 'DELETE') as $verb) {
      // sends the token in the Authorization header for a {$verb} request
      $this->assertContains($this->token, $this->accessToken->request($verb, '/token/header')->body());
    }

    // query mode
    $this->accessToken->options['mode'] = 'query';
    foreach (array('GET', 'POST', 'PUT', 'DELETE') as $verb) {
      // sends the token in the query params for a {$verb} request
      $this->assertEquals($this->token, $this->accessToken->request($verb, '/token/query')->body());
    }

    // body mode
    $this->accessToken->options['mode'] = 'body';
    foreach (array('GET', 'POST', 'PUT', 'DELETE') as $verb) {
      // sends the token in the body for a {$verb} request
      $data = array_reverse(explode('=', $this->accessToken->request($verb, '/token/body')->body()));
      $this->assertEquals($this->token, $data[0]);
    }
  }

 /**
  * @covers OAuth2\AccessToken::expires()
  */
  public function testExpires()
  {
    // should be false if expires_in is null
    $target = new \OAuth2\AccessToken($this->client, $this->token);
    $this->assertFalse($target->expires());

    // should be true if there is an expires_in
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_in' => 600));
    $this->assertTrue($target->expires());
  }

 /**
  * @covers OAuth2\AccessToken::isExpired()
  */
  public function testIsExpired()
  {
    // should be false if there is no expires_in or expires_at
    $target = new \OAuth2\AccessToken($this->client, $this->token);
    $this->assertFalse($target->isExpired());

    // should be false if expires_in is in the future
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_in' => 10800));
    $this->assertFalse($target->isExpired());
  }

 /**
  * @covers OAuth2\AccessToken::refresh()
  */
  public function testRefresh()
  {
    // returns a refresh token with appropriate values carried over
    $target    = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_in' => 600, 'param_name' => 'o_param'));
    $refreshed = $target->refresh();
    $this->assertEquals($refreshed->getClient(), $target->getClient());
    $this->assertEquals($refreshed->options['param_name'], $target->options['param_name']);
  }

 /**
  * Intercept all OAuth2\Client::getResponse() calls and mock their responses
  */
  public function mockGetResponse()
  {
    // retrieve args
    $args = func_get_args();

    // create response based on mode
    switch ($args[0]->getPath()) {
      case '/token/header':
        $body = sprintf($this->accessToken->options['header_format'], $this->accessToken->getToken());
        return new \OAuth2\Response(new \GuzzleHttp\Message\Response(200, array(), \GuzzleHttp\Stream\Stream::factory($body)));
        break;

      case '/token/query':
        return new \OAuth2\Response(new \GuzzleHttp\Message\Response(200, array(), \GuzzleHttp\Stream\Stream::factory($this->accessToken->getToken())));
        break;

      case '/token/body':
        $body = $this->accessToken->options['param_name'] . '=' . $this->accessToken->getToken();
        return new \OAuth2\Response(new \GuzzleHttp\Message\Response(200, array(), \GuzzleHttp\Stream\Stream::factory($body)));
        break;

      case '/oauth/token':
        return new \OAuth2\Response(new \GuzzleHttp\Message\Response(200, ['Content-Type' => 'application/json'], \GuzzleHttp\Stream\Stream::factory($this->refreshBody)));
        break;
    }
  }

 /**
  * Assert for token initialization
  */
  private function assertInitializeToken($target) {
    $this->assertEquals($this->token, $target->getToken());
    $this->assertTrue($target->expires());
    $this->assertArrayHasKey('foo', $target->getParams());
    $this->assertEquals('bar', $target->getParam('foo'));
  }
}
