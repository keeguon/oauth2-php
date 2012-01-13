<?php

namespace OAuth2\Tests;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
 /**
  * @var OAuth2\AccessToken
  * @var OAuth2\Client
  * @var string
  * @var string
  * @var string
  */
  protected
      $access_token = null
    , $client       = null
    , $token        = 'monkey'
    , $token_body   = ''
    , $refresh_body = ''
  ;

 /**
  * Sets up the fixture.
  * This method is called before a test is executed.
  */
  protected function setUp()
  {
    // default properties
    $this->token_body   = json_encode(array('access_token' => 'foo', 'expires_in' => 600, 'refresh_token' => 'bar'));
    $this->refresh_body = json_encode(array('access_token' => 'refreshed_foo', 'expires_in' => 600, 'refresh_token' => 'refresh_bar'));

    // mock client object
    $this->client = $this->getMock('\OAuth2\Client', array('request'), array('abc', 'def', array('site' => 'https://api.example.com')));

    // configure mocked client
    $this->client->expects($this->any())
                 ->method('request')
                 ->will($this->returnCallback(array($this, 'mockRequest')));

    // instantiate access_token
    $this->access_token = new \OAuth2\AccessToken($this->client, $this->token);
  }

  protected function tearDown()
  {
    unset($this->access_token);
    unset($this->client);
    unset($this->refresh_body);
    unset($this->token_body);
  }

 /**
  * @covers OAuth2\AccessToken::__construct()
  */
  public function testConstructorBuildsAccessToken()
  {
    // assigns client and token
    $this->assertEquals($this->client, $this->access_token->client);
    $this->assertEquals($this->token, $this->access_token->token);

    // assigns extra params
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('foo' => 'bar'));
    $this->assertArrayHasKey('foo', $target->params);
    $this->assertEquals('bar', $target->params['foo']);

    // initialize with a Hash
    $hash   = array('access_token' => $this->token, 'expires_in' => time() + 200, 'foo' => 'bar');
    $target = \OAuth2\AccessToken::from_hash($this->client, $hash);
    $this->assertInitializeToken($target);

    // initalizes with a form-urlencoded key/value string
    $kvform = "access_token={$this->token}&expires_at={time() + 200}&foo=bar";
    $target = \OAuth2\AccessToken::from_kvform($this->client, $kvform);
    $this->assertInitializeToken($target);

    // sets options
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('param_name' => 'foo', 'header_format' => 'Bearer %', 'mode' => 'body'));
    $this->assertEquals('foo', $target->options['param_name']);
    $this->assertEquals('Bearer %', $target->options['header_format']);
    $this->assertEquals('body', $target->options['mode']);
  }

 /**
  * @covers OAuth2\AccessToken::get()
  * @covers OAuth2\AccessToken::post()
  * @covers OAuth2\AccessToken::put()
  * @covers OAuth2\AccessToken::delete()
  * @covers OAuth2\AccessToken::request()
  */
  public function testRequest()
  {
    // header mode
    $this->access_token->options['mode'] = 'header';
    foreach (array('GET', 'POST', 'PUT', 'DELETE') as $verb) {
      // sends the token in the Authorization header for a {$verb} request
      $this->assertContains($this->token, $this->access_token->request($verb, '/token/header')->body());
    }

    // query mode
    $this->access_token->options['mode'] = 'query';
    foreach (array('GET', 'POST', 'PUT', 'DELETE') as $verb) {
      // sends the token in the query params for a {$verb} request
      $this->assertEquals($this->token, $this->access_token->request($verb, '/token/query')->body());
    }
    
    // body mode
    $this->access_token->options['mode'] = 'body';
    foreach (array('GET', 'POST', 'PUT', 'DELETE') as $verb) {
      // sends the token in the body for a {$verb} request
      $data = array_reverse(explode('=', $this->access_token->request($verb, '/token/body')->body()));
      $this->assertEquals($this->token, $data[0]);
    }
  }

 /**
  * @covers OAuth2\AccessToken::expires()
  */
  public function testExpires()
  {
    // should be false if there is no expires_at
    $target = new \OAuth2\AccessToken($this->client, $this->token);
    $this->assertFalse($target->expires());

    // should be true if there is an expires_in
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_in' => 600));
    $this->assertTrue($target->expires());

    // should be true if there is an expires_at
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_at' => time() + 600));
    $this->assertTrue($target->expires());
  }

 /**
  * @covers OAuth2\AccessToken::is_expired()
  */
  public function testIsExpired()
  {
    // should be false if there is no expires_in or expires_at
    $target = new \OAuth2\AccessToken($this->client, $this->token);
    $this->assertFalse($target->is_expired());

    // should be false if expires_in is in the future
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_in' => 10800));
    $this->assertFalse($target->is_expired());

    // should be true if expires_at is in the past
    $target = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_at' => time() - 600));
    $this->assertTrue($target->is_expired());
  }

 /**
  * @covers OAuth2\AccessToken::refresh()
  */
  public function testRefresh()
  {
    // returns a refresh token with appropriate values carried over
    $target    = new \OAuth2\AccessToken($this->client, $this->token, array('refresh_token' => 'abaca', 'expires_in' => 600, 'param_name' => 'o_param'));
    $refreshed = $target->refresh();
    $this->assertEquals($refreshed->client, $target->client);
    $this->assertEquals($refreshed->options['param_name'], $target->options['param_name']);
  }

 /**
  * Intercept all OAuth2\Client::request() calls and mock their responses
  */
  public function mockRequest()
  {
    // retrieve args
    $args = func_get_args();

    // create response based on mode
    switch ($args[1]) {
      case '/token/header':
        $body = sprintf($this->access_token->options['header_format'], $this->access_token->token);
        return new \OAuth2\Response(new \Guzzle\Http\Message\Response(200, array(), $body));
        break;

      case '/token/query':
        return new \OAuth2\Response(new \Guzzle\Http\Message\Response(200, array(), $this->access_token->token));
        break;

      case '/token/body':
        $body = "{$this->access_token->options['param_name']}={$this->access_token->token}";
        return new \OAuth2\Response(new \Guzzle\Http\Message\Response(200, array(), $body));
        break;

      case 'https://api.example.com/oauth/token':
        return new \OAuth2\Response(new \Guzzle\Http\Message\Response(200, array('Content-Type' => 'application/json'), $this->refresh_body));
        break;
    }
  }

 /**
  * Assert for token initialization
  */
  private function assertInitializeToken($target) {
    $this->assertEquals($this->token, $target->token);
    $this->assertTrue($target->expires());
    $this->assertArrayHasKey('foo', $target->params);
    $this->assertEquals('bar', $target->params['foo']);
  }
}
