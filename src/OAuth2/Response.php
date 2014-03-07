<?php

namespace OAuth2;

class Response
{
  public $error   = null;
  public $options = array();
  
  protected $response = null;

  public function __construct($response, $opts = array())
  {
    $this->response = $response;
    $this->options  = array_merge(array('parse' => 'automatic'), $opts);
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
    return $this->response->getHeaders()->getAll();
  }

  public function status()
  {
    return $this->response->getStatusCode();
  }
  
  public function body()
  {
    return $this->response->getBody(true);
  }

  public function parse()
  {
    $parsed = null;

    switch ($this->options['parse']) {
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
    return $this->response->getContentType();
  }
}
