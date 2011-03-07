<?php

namespace OAuth2;

require __DIR__.'/../vendor/php-multi-curl/EpiCurl.php';

class Client
{
 /**
  * Multi-curl handler
  */
  protected $mc = EpiCurl::getInstance();

 /**
  * Default options for cURL.
  */
  public static $CURL_OPTS = array(
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_RETURNTRANSFER => TRUE,
    CURLOPT_HEADER         => TRUE,
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_USERAGENT      => 'oauth2-draft-v10',
    CURLOPT_HTTPHEADER     => array("Accept: application/json"),
  );

  protected
    $id         = '',
    $secret     = '',
    $site       = '',
    $options    = array()
  ;

  public function __construct($client_id, $client_secret, $opts = array())
  {
    $this->setId($client_id);
    $this->setSecret($client_secret);
    if (isset($opts['site'])) {
      $this->setSite($opts['site']);
      unset($opts['site']);
    }
    $this->setOptions($opts);
  }
  
 /**
  * Get the client id
  *
  * @return string The client id
  */
  public function getId()
  {
    return $this->id;
  }
  
 /**
  * Set the client id
  *
  * @param string $id The client id
  */
  public function setId($id)
  {
    $this->id = $id;
  }

 /**
  * Get the client secret
  *
  * @return string The client secret
  */
  public function getSecret()
  {
    return $this->secret;
  }
  
 /**
  * Set the client secret
  *
  * @param string $secret The client secret
  */
  public function setSecret($secret)
  {
    $this->secret = $secret;
  }
  
 /**
  * Get the provide site
  *
  * @return string The provider site
  */
  public function getSite()
  {
    return $this->site;
  }
  
 /**
  * Set the provider site
  *
  * @param string $site The provide site
  */
  public function setSite($site)
  {
    $this->site = $site;
  }
  
 /**
  * Get options
  *
  * @return array The options
  */  
  public function getOptions()
  {
    return $this->options;
  }
  
 /**
  * Set options
  *
  * @param array $options The options
  */
  public function setOptions($options)
  {
    $this->options = $options;
  }
  
  public function authorize_url($params = null)
  {
    $path =  ($this->options['authorize_url']) ? $this->options['authorize_url'] :
            (($this->options['authorize_path']) ? $this->options['authorize_path'] :
            "/oauth/authorize");
    return $path.'?'.http_build_query($params, null, '&');
  }
  
  public function access_token_url($params = null)
  {
    $path =  ($this->options['access_token_url']) ? $this->options['access_token_url'] :
            (($this->options['access_token_path']) ? $this->options['access_token_path'] :
            "/oauth/access_token");
    return $path.'?'.http_build_query($params, null, '&');
  }
  
  public function request($verb, $url, $params = array(), $headers = array())
  {
    $ch = curl_init();
    $opts = self::$CURL_OPTS;
  
    if ($params) {
      switch ($verb) {
        case 'GET':
          $url .= '?'.http_build_query($params, null, '&');
          break;
        default:
          $opts[CURLOPT_POSTFIELDS] = http_build_query($params, NULL, '&');
          break;
      }
    }
    $opts[CURLOPT_URL] = $url;
    
    if ($headers && isset($opts[CURLOPT_HTTPHEADER])) {
      $existing_headers = $opts[CURLOPT_HTTPHEADER];
      array_merge($existing_headers, $headers);
      $opts[CURLOPT_HTTPHEADER] = $existing_headers;
    }
    
    // Disable the 'Expect: 100-continue' behaviour. This causes cURL to wait
    // for 2 seconds if the server does not support this header.
    if (isset($opts[CURLOPT_HTTPHEADER])) {
      $existing_headers = $opts[CURLOPT_HTTPHEADER];
      $existing_headers[] = 'Expect:';
      $opts[CURLOPT_HTTPHEADER] = $existing_headers;
    }
    else {
      $opts[CURLOPT_HTTPHEADER] = array('Expect:');
    }
    
    curl_setopt_array($ch, $opts);
    $result = $this->mc->addCurl($ch);
    
    if ($result->data === FALSE) {
      $e = new Exception(array(
        'code' => curl_errno($ch),
        'message' => curl_error($ch),
      ));
      throw $e;
    }

    return $result->data;
  }
  
  public function web_server()
  {
    return new Strategy\WebServer($this);
  }
}

