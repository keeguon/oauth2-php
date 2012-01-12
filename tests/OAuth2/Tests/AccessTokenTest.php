<?php

namespace OAuth2\Tests;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
{
  const GET    = 'GET';
  const POST   = 'POST';
  const PUT    = 'PUT';
  const DELETE = 'DELETE';

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
    $this->client = $this->getMock('\OAuth2\Client', array('request'), array('client_id' => 'abc', 'client_secret' => 'def', 'opts' => array('site' => 'https://api.example.com')));

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

  }

  private function assertInitializeToken($target) {
    $this->assertEquals($this->token, $target->token);
    $this->assertTrue($target->expires());
    $this->assertArrayHasKey('foo', $target->params);
    $this->assertEquals('bar', $target->params['foo']);
  }
}
