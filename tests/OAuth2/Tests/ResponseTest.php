<?php

namespace OAuth2\Tests;

class ResponseTest extends \OAuth2\Tests\TestCase
{
 /**
  * @var OAuth2\Response
  */
  protected $response;

 /**
  * @covers OAuth2\Response::__construct()
  * @covers OAuth2\Response::headers()
  * @covers OAuth2\Response::status()
  * @covers OAuth2\Response::body()
  */
  public function testConstructorBuildsResponse()
  {
    $status  = 200;
    $headers = array('foo' => array('bar'));
    $body    = 'foo';

    // returns the status, headers and body
    $this->response = new \OAuth2\Response(new \Guzzle\Http\Message\Response($status, $headers, $body));
    $this->assertEquals($headers, $this->response->headers());
    $this->assertEquals($status, $this->response->status());
    $this->assertEquals($body, $this->response->body());
  }
  
 /**
  * @covers OAuth2\Response::content_type()
  * @covers OAuth2\Response::parse()
  */
  public function testParseResponse()
  {
    // parses application/x-www-form-urlencoded body
    $headers        = array('Content-Type' => 'application/x-www-form-urlencoded');
    $body           = 'foo=bar&answer=42';
    $this->response = new \OAuth2\Response(new \Guzzle\Http\Message\Response(200, $headers, $body));
    $parsedResponse = $this->response->parse();
    $this->assertEquals(2, count(array_keys($parsedResponse)));
    $this->assertEquals('bar', $parsedResponse['foo']);
    $this->assertEquals(42, $parsedResponse['answer']);

    // parses application/json body
    $headers        = array('Content-Type' => 'application/json');
    $body           = json_encode(array('foo' => 'bar', 'answer' => 42));
    $this->response = new \OAuth2\Response(new \Guzzle\Http\Message\Response(200, $headers, $body));
    $parsedResponse = $this->response->parse();
    $this->assertEquals(2, count(array_keys($parsedResponse)));
    $this->assertEquals('bar', $parsedResponse['foo']);
    $this->assertEquals(42, $parsedResponse['answer']);

    // doesn't try to parse other content-types
    $headers        = array('Content-Type' => 'text/html');
    $body           = '<!DOCTYPE html><html><head></head><body></body></html>';
    $this->response = new \OAuth2\Response(new \Guzzle\Http\Message\Response(200, $headers, $body));
    $this->assertNull($this->response->parse());
  }
}
