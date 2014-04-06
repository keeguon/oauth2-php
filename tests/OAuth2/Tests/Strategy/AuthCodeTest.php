<?php

namespace OAuth2\Tests\Strategy;

class AuthCodeTest extends \OAuth2\Tests\TestCase
{
  protected $access        = null;
  protected $authCode      = null;
  protected $client        = null;
  protected $code          = 'sushi';
  protected $jsonToken     = '';
  protected $kvformToken   = '';
  protected $mode          = '';

 /**
  * Set up fixtures
  */
  protected function setUp()
  {
    // set tokens
    $this->kvformToken   = 'expires_in=600&access_token=salmon&refresh_token=trout&extra_param=steve';
    $this->jsonToken     = json_encode(array('expires_in' => 600, 'access_token' => 'salmon', 'refresh_token' => 'trout', 'extra_param' => 'steve'));

    // get client stub
    $this->client = $this->getClientStub('abc', 'def', array('site' => 'https://api.example.com'));

    // create authCode
    $this->authCode = new \OAuth2\Strategy\AuthCode($this->client);
  }

  protected function tearDown()
  {
    unset($this->authCode);
    unset($this->access);
    unset($this->client);
    unset($this->code);
    unset($this->jsonToken);
    unset($this->kvformToken);
    unset($this->mode);
  }

 /**
  * @covers OAuth2\Strategy\AuthCode::authorize_url()
  */
  public function testAuthorizeUrl()
  {
    // should include the client_id
    $this->assertContains('client_id=abc', $this->authCode->authorizeUrl());

    // should include the type
    $this->assertContains('response_type=code', $this->authCode->authorizeUrl());

    // should include passed in options
    $cb = 'http://myserver.local/oauth/callback';
    $this->assertContains('redirect_uri='.urlencode($cb), $this->authCode->authorizeUrl(array('redirect_uri' => $cb)));
  }

 /**
  * @covers OAuth2\Strategy\AuthCode::get_token()
  */
  public function testGetToken()
  {
    foreach(array('json', 'formencoded') as $mode) {
      // set mode
      $this->mode = $mode;

      foreach (array('GET', 'POST') as $verb) {
        // set token_method and get token
        $this->client->options['token_method'] = $verb;
        $this->access = $this->authCode->getToken($this->code);
      }

      // returns AccessToken with same Client
      $this->assertEquals($this->client, $this->access->getClient());

      // returns AccessToken with $token
      $this->assertEquals('salmon', $this->access->getToken());

      // returns AccessToken with $refresh_token
      $this->assertEquals('trout', $this->access->getRefreshToken());

      // returns AccessToken with $expires_in
      $this->assertEquals(600, $this->access->getExpiresIn());

      // returns AccessToken with params accessible via the params array
      $this->assertEquals('steve', $this->access->getParam('extra_param'));
    }
  }

 /**
  * Intercept all OAuth2\Client::getResponse() calls and mock their responses
  */
  public function mockGetResponse()
  {
    // retrieve args
    $args = func_get_args();

    // map responses
    $map = array(
        'formencoded'   => new \GuzzleHttp\Message\Response(200, array('Content-Type' => 'application/x-www-form-urlencoded'), \GuzzleHttp\Stream\Stream::factory($this->kvformToken))
      , 'json'          => new \GuzzleHttp\Message\Response(200, array('Content-Type' => 'application/json'), \GuzzleHttp\Stream\Stream::factory($this->jsonToken))
    );

    return new \OAuth2\Response($map[$this->mode]);
  }
}
