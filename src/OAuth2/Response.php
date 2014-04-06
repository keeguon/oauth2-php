<?php

namespace OAuth2;

class Response
{
  public $error     = null;
  public $parseMode = '';

  protected $response = null;

  public function __construct($response, $parseMode = 'automatic')
  {
    $this->response   = $response;
    $this->parseMode = $parseMode;
  }

 /**
  * response getter
  *
  * @return Guzzle\Http\Message\Response
  */
  public function getResponse()
  {
    return $this->response;
  }

  public function headers()
  {
    return $this->response->getHeaders();
  }

  public function status()
  {
    return $this->response->getStatusCode();
  }

  public function body()
  {
    return (string) $this->response->getBody();
  }

  public function parse()
  {
    $parsed = null;

    switch ($this->parseMode) {
      case 'json':
        $parsed = json_decode($this->body(), true);
        break;

      case 'query':
        parse_str($this->body(), $parsed);
        break;

      case 'automatic':
      default:
        $types = array('application/json', 'text/javascript');
        $content_type = $this->content_type();

        foreach ($types as $type) {
          if (stripos($content_type, $type) !== false) {
            $parsed = json_decode($this->body(), true);
            break;
          }
        }

        if (stripos($content_type, "application/x-www-form-urlencoded") !== false) {
          parse_str($this->body(), $parsed);
        }
        break;
    }

    return $parsed;
  }

  public function content_type()
  {
    return $this->response->getHeader('Content-Type');
  }
}
