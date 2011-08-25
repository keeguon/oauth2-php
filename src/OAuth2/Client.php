<?php

namespace OAuth2;

class Client
{
 /**
  * Default options for cURL.
  */
  public static
    $httpOptions = array(
      'connecttimeout' => 10,
      'timeout'        => 60,
    ),
    $httpHeaders = array(
      'User-Agent' => 'ShopWiz/1.0; Facebook PHP',
      'Accept'     => 'application/json'
    )
  ;

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
    // Create the HttpRequest
    $httpRequest = new \HttpRequest($url);
    switch ($verb) {
      //case 'DELETE':
      //  $httpRequest->setMethod(HTTP_METH_DELETE);
      //  break;
      case 'POST':
        $httpRequest->setMethod(HTTP_METH_POST);
        $httpRequest->setPostFields($params);
        break;
      //case 'PUT':
      //  $httpRequest->setMethod(HTTP_METH_PUT);
      //  break;
      case 'GET':
      default:
        $httpRequest->setQueryData($params);
        break;
    }
    $httpRequest->setOptions(self::$httpOptions);
    $httpRequest->setHeaders(array_merge(self::$httpHeaders, $headers, array('Expect' => '')));
    
    // Send the HttpRequest
    $httpRequest->send();

    /*
    if (!$httpRequest->getResonseBody()) {
      $e = new Exception(array(
        'code' => curl_errno($ch),
        'message' => curl_error($ch),
      ));
      throw $e;
    }
    */
    
    // We catch HTTP/1.1 4xx or HTTP/1.1 5xx error response.
    if (in_array($httpRequest->getResponseCode(), range(400, 599))) {
      $result = array(
        'code' => $httpRequest->getResponseCode(),
        'message' => $httpRequest->getResponseStatus(),
      );

      return json_encode($result);
    }

    return $httpRequest->getResponseBody();
  }
  
  public function web_server()
  {
    return new Strategy\WebServer($this);
  }
}

