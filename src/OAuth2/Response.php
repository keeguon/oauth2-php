<?php

namespace OAuth2;

class Response
{
  protected
      $options  = array()
    , $response = null
  ;

  public $error = null;

  public function __construct($response, $opts = array())
  {
    $this->response = $response;
    $this->options  = array_merge(array('parse' => 'automatic'), $opts);
  }

  public function body()
  {
    return $this->response->getBody();
  }

  public function content_type()
  {
    return $this->response->getHeader('Content-Type');
  }

  public function headers()
  {
    return $this->response->getHeaders();
  }

  public function parse()
  {
    $parsed = $this->body();

    switch ($this->options['parse']) {
      case 'json':
        $parsed = json_decode($parsed, true);
        break;

      case 'query':
        parse_str($parsed, $parsed);
        break;

      case 'automatic':
      default:
        if (in_array($this->content_type(), array('application/json', 'text/javascript'))) {
          $parsed = json_decode($parsed, true);
        }

        if ($this->content_type() === "application/x-www-form-urlencoded") {
          parse_str($parsed, $parsed);
        }
        break;
    }

    return $parsed;
  }

  public function status()
  {
    return $this->response->getResponseCode();
  }
}
