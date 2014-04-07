<?php

namespace OAuth2\Tests;

class ClientTest extends \OAuth2\Tests\TestCase
{
 /**
  * @var OAuth2\Client
  * @var string
  * @var string
  */
  protected $client                = null;
  protected $errorValue            = 'invalid_token';
  protected $errorDescriptionValue = 'bad bad token';

 /**
  * Sets up the fixture, here, creating a new client.
  * This method is called before a test is executed.
  */
  protected function setUp()
  {
    // get client stub
    $this->client = $this->getClientStub('abc', 'def', array('site' => 'https://api.example.com'));
  }

  protected function tearDown()
  {
    unset($this->client);
  }

 /**
  * @covers OAuth2\Client::__construct()
  */
  public function testConstructorBuildsClient()
  {
    // client id and secret should be assigned
    $this->assertEquals('abc', $this->client->getId());
    $this->assertEquals('def', $this->client->getSecret());

    // client site should be assigned
    $this->assertEquals('https://api.example.com', $this->client->site);

    // connection baseUrl should be assigned
    $this->assertEquals('https://api.example.com', $this->client->connection->getBaseUrl());

    // raise_error option should be true
    $this->assertTrue($this->client->options['raise_errors']);

    // allows true/false for raise_error option
    $client = new \OAuth2\Client('abc', 'def', array('site' => 'https://api.example.com', 'raise_errors' => false));
    $this->assertFalse($client->options['raise_errors']);
    $client = new \OAuth2\Client('abc', 'def', array('site' => 'https://api.example.com', 'raise_errors' => true));
    $this->assertTrue($client->options['raise_errors']);

    // allow GET/POST for token_method option
    $client = new \OAuth2\Client('abc', 'def', array('site' => 'https://api.example.com', 'token_method' => 'GET'));
    $this->assertEquals('GET', $client->options['token_method']);
    $client = new \OAuth2\Client('abc', 'def', array('site' => 'https://api.example.com', 'token_method' => 'POST'));
    $this->assertEquals('POST', $client->options['token_method']);
  }

 /**
  * @covers OAuth2\Client::authorize_url()
  * @covers OAuth2\Client::token_url()
  */
  public function testUrlsEnpoints()
  {
    foreach (array('authorize', 'token') as $urlType) {
      // {$url_type}_url should default to /oauth/{$url_type}
      $this->assertEquals("https://api.example.com/oauth/{$urlType}", call_user_func(array($this->client, "{$urlType}Url")));

      // {$url_type}_url should be settable via the {$url_type}_url option
      $this->client->options["{$urlType}_url"] = '/oauth/custom';
      $this->assertEquals("https://api.example.com/oauth/custom", call_user_func(array($this->client, "{$urlType}Url")));

      // allows a different host than the site
      $this->client->options["{$urlType}_url"] = 'https://api.foo.com/oauth/custom';
      $this->assertEquals("https://api.foo.com/oauth/custom", call_user_func(array($this->client, "{$urlType}Url")));
    }
  }

 /**
  * @covers OAuth2\Client::request()
  */
  public function testRequest()
  {
    // works with a null response body
    $request = $this->client->createRequest('GET', '/empty_get');
    $this->assertEmpty($this->client->getResponse($request)->body());

    // returns on a successful response body
    $request  = $this->client->createRequest('GET', '/success');
    $response = $this->client->getResponse($request);
    $this->assertEquals('yay', $response->body());
    $this->assertEquals(200, $response->status());
    $headers = $response->headers();
    $this->assertCount(1, $headers);
    $this->assertArrayHasKey('content-type', $headers);
    $this->assertEquals(array('text/awesome'), $headers['content-type']->toArray());

    // posts a body
    $request  = $this->client->createRequest('POST', '/reflect', array('body' => 'foo=bar'));
    $response = $this->client->getResponse($request);
    $this->assertEquals('foo=bar', $response->body());

    // follows redirect properly
    $request  = $this->client->createRequest('GET', '/redirect');
    $response = $this->client->getResponse($request);
    $this->assertEquals('yay', $response->body());
    $this->assertEquals(200, $response->status());
    $headers = $response->headers();
    $this->assertCount(1, $headers);
    $this->assertArrayHasKey('content-type', $headers);
    $this->assertEquals(array('text/awesome'), $headers['content-type']->toArray());

    // redirects using GET on a 303
    $request  = $this->client->createRequest('POST', '/redirect', array('body' => 'foo=bar'));
    $response = $this->client->getResponse($request);
    $this->assertEmpty($response->body());
    $this->assertEquals(200, $response->status());

    // obeys the max_redirects option
    $max_redirects = $this->client->options['max_redirects'];
    $this->client->options['max_redirects'] = 0;
    $request  = $this->client->createRequest('GET', '/redirect');
    $response = $this->client->getResponse($request);
    $this->assertEquals(302, $response->status());
    $this->client->options['max_redirects'] = $max_redirects;

    // returns if raise_errors is false
    $this->client->options['raise_errors'] = false;
    $request  = $this->client->createRequest('GET', '/unauthorized');
    $response = $this->client->getResponse($request);
    $this->assertEquals(401, $response->status());
    $headers = $response->headers();
    $this->assertCount(1, $headers);
    $this->assertArrayHasKey('content-type', $headers);
    $this->assertEquals(array('application/json'), $headers['content-type']->toArray());
    $this->assertNotNull($response->error);

    // test if exception are thrown when raise_errors is true
    $this->client->options['raise_errors'] = true;
    foreach (array('/unauthorized', '/conflict', '/error') as $errorPath) {
      $request = $this->client->createRequest('GET', $errorPath);

      // throw OAuth\Error on error response to path {$errorPath}
      $this->setExpectedException('\OAuth2\Error');
      $this->client->getResponse($request);
    }

    // parses OAuth2 standard error response
    try {
      $this->client->createRequest('GET', '/error');
    } catch (\OAuth2\Error $e) {
      $this->assertEquals($this->errorValue, $e->getCode());
      $this->assertEquals($this->errorDescriptionValue, $e->getDescription());
    }

    // provides the response in the Exception
    try {
      $this->client->createRequest('GET', '/error');
    } catch (\OAuth2\Error $e) {
      $this->assertNotNull($e->getResponse());
    }
  }

 /**
  * @covers OAuth2\Client::auth_code()
  */
  public function testAuthCodeInstatiation()
  {
    // auth_code() should instantiate a AuthCode strategy with this client
    $this->assertInstanceOf("\OAuth2\Strategy\AuthCode", $this->client->authCode());
  }

 /**
  * Intercept all OAuth2\Client::getResponse() calls and mock their responses
  */
  public function mockGetResponse()
  {
    // retrieve arguments
    $args = func_get_args();

    // default options
    $opts = array_merge(array(
      'raise_errors' => $this->client->options['raise_errors']
    ), $args[1]);

    // map routes
    $map = array();
    $map['GET']['/success']      = array('status' => 200, 'headers' => array('Content-Type' => 'text/awesome'), 'body' => 'yay');
    $map['GET']['/reflect']      = array('status' => 200, 'headers' => array(), 'body' => $args[0]->getResponseBody());
    if ($args[0]->getMethod() === 'POST' && $args[0]->getPath() === '/reflect') {
      $map['POST']['/reflect']     = array('status' => 200, 'headers' => array(), 'body' => (string) $args[0]->getBody());
    }
    $map['GET']['/unauthorized'] = array('status' => 401, 'headers' => array('Content-Type' => 'application/json'), 'body' => json_encode(array('error' => $this->errorValue, 'error_description' => $this->errorDescriptionValue)));
    $map['GET']['/conflict']     = array('status' => 409, 'headers' => array('Content-Type' => 'text/plain'), 'body' => 'not authorized');
    $map['GET']['/redirect']     = array('status' => 302, 'headers' => array('Content-Type' => 'text/plain', 'location' => '/success'), 'body' => '');
    $map['POST']['/redirect']    = array('status' => 303, 'headers' => array('Content-Type' => 'text/plain', 'location' => '/reflect'), 'body' => '');
    $map['GET']['/error']        = array('status' => 500, 'headers' => array(), 'body' => '');
    $map['GET']['/empty_get']    = array('status' => 200, 'headers' => array(), 'body' => '');

    // match response
    $response = $map[$args[0]->getMethod()][$args[0]->getPath()];

    // wrap response in an OAuth2\Response object
    $response = new \OAuth2\Response(new \Guzzle\Http\Message\Response($response['status'], $response['headers'], $response['body']));

    // Response handling
    if (in_array($response->status(), range(200, 299))) {
      // Reset redirect count for future requests since we reached something
      $this->client->options['redirect_count'] = 0;

      return $response;
    } else if (in_array($response->status(), range(300, 399))) {
      // Count redirects
      $this->client->options['redirect_count'] = $this->client->options['redirect_count'] || 0;
      $this->client->options['redirect_count'] += 1;
      if ($this->client->options['redirect_count'] > $this->client->options['max_redirects']) {
        return $response;
      }

      // Get vars to make the redirect
      if ($response->status() === 303) {
        $verb = 'GET';
        $body = '';
      }
      $headers = $response->headers();

      // Create redirected request and get response
      $request = $this->client->createRequest(isset($verb) ? $verb : $args[0]->getMethod(), $headers['location'], array(
          'params'  => $args[0]->getQuery()
        , 'headers' => $args[0]->getHeaders()
        , 'body'    => isset($body) ? $body : $args[0]->getResponseBody()
      ));
      return $this->client->getResponse($request, $opts);
    } else if (in_array($response->status(), range(400, 599))) {
      $e = new \OAuth2\Error($response);
      if ($opts['raise_errors'] || $this->client->options['raise_errors']) {
        throw $e;
      }
      $response->error = $e;
      return $response;
    } else {
      throw new \OAuth2\Error($response);
    }
  }
}
