<?php

namespace OAuth2\Tests\Strategy;

class AuthCodeTest extends \PHPUnit_Framework_TestCase
{
  protected
      $code           = 'sushi'
    , $mode           = ''
    , $kvform_token   = ''
    , $facebook_token = ''
    , $json_token     = ''
    , $client         = null
    , $auth_code      = null
    , $access         = null
  ;

 /**
  * Set up fixtures
  */
  protected function setUp()
  {
    // set tokens
    $this->kvform_token   = 'expires_in=600&access_token=salmon&refresh_token=trout&extra_param=steve';
    $this->facebook_token = preg_replace('/_in/i', '', $this->kvform_token);
    $this->json_token     = json_encode(array('expires_in' => 600, 'access_token' => 'salmon', 'refresh_token' => 'trout', 'extra_param' => 'steve'));

    // mock client
    $this->client = $this->getMock('\OAuth2\Client', array('request'), array('abc', 'def', array('site' => 'https://api.example.com')));

    // configure mock
    $this->client->expects($this->any())
                 ->method('request')
                 ->will($this->returnCallback(array($this, 'mockRequest')));

    // create auth_code
    $this->auth_code = new \OAuth2\Strategy\AuthCode($this->client);
  }

  protected function tearDown()
  {
    unset($this->code);
    unset($this->mode);
    unset($this->kvform_token);
    unset($this->facebook_token);
    unset($this->json_token);
    unset($this->client);
    unset($this->auth_code);
    unset($this->access);
  }

 /**
  * @covers OAuth2\Strategy\AuthCode::authorize_url()
  */
  public function testAuthorizeUrl()
  {
    // should include the client_id
    $this->assertContains('client_id=abc', $this->auth_code->authorize_url());

    // should include the type
    $this->assertContains('response_type=code', $this->auth_code->authorize_url());

    // should include passed in options
    $cb = 'http://myserver.local/oauth/callback';
    $this->assertContains('redirect_uri='.urlencode($cb), $this->auth_code->authorize_url(array('redirect_uri' => $cb)));
  }

 /**
  * @covers OAuth2\Strategy\AuthCode::get_token()
  */
  public function testGetToken()
  {
    foreach(array('json', 'formencoded', 'from_facebook') as $mode) {
      // set mode
      $this->mode = $mode;
      
      foreach (array('GET', 'POST') as $verb) {
        // set token_method and get token
        $this->client->options['token_method'] = $verb;
        $this->access = $this->auth_code->get_token($this->code);
      }

      // returns AccessToken with same Client
      $this->assertEquals($this->client, $this->access->client);

      // returns AccessToken with $token
      $this->assertEquals('salmon', $this->access->token);

      // returns AccessToken with $refresh_token
      $this->assertEquals('trout', $this->access->refresh_token);

      // returns AccessToken with $expires_in
      $this->assertEquals(600, $this->access->expires_in);

      // returns AccessToken with $expires_at
      $this->assertInternalType('integer', $this->access->expires_at);

      // returns AccessToken with params accessible via the params array
      $this->assertEquals('steve', $this->access->params['extra_param']);
    }
  }

 /**
  * Intercept all OAuth2\Client::request() calls and mock their responses
  */
  public function mockRequest()
  {
    $args = func_get_args();

    // map responses
    $map = array(
        'formencoded'   => new \Guzzle\Http\Message\Response(200, array('Content-Type' => 'application/x-www-form-urlencoded'), $this->kvform_token)
      , 'json'          => new \Guzzle\Http\Message\Response(200, array('Content-Type' => 'application/json'), $this->json_token)
      , 'from_facebook' => new \Guzzle\Http\Message\Response(200, array('Content-Type' => 'application/x-www-form-urlencoded'), $this->facebook_token)
    );

    return new \OAuth2\Response($map[$this->mode]);
  }
}
