<?php

namespace OAuth2;

class Client
{
  protected
      $options    = array()
  ;

  public
      $connection = null
    , $id         = ''
    , $secret     = ''
    , $site       = ''
  ;

  public function __construct($client_id, $client_secret, $opts = array())
  {
    $this->id     = $client_id;
    $this->secret = $client_secret;
    if (isset($opts['site'])) {
      $this->site = $opts['site'];
      unset($opts['site']);
    }

    // Default options
    $this->options = array_merge(array(
        'authorize_url'   => '/oauth/authorize'
      , 'token_url'       => '/oauth/token'
      , 'token_method'    => 'POST'
      , 'connection_opts' => array()
      , 'max_redirects'   => 5
      , 'raise_errors'    => true
    ), $opts);

    // Connection object using Guzzle
    $this->connection = new \Guzzle\Service\Client($this->site);
  }

 /**
  * The Authorization Code strategy
  *
  * @return \OAuth2\Strategy\AuthCode
  */
  public function auth_code()
  {
    $this->auth_code = isset($this->auth_code) ? $this->auth_code : new \OAuth2\Strategy\AuthCode($this);
    return $this->auth_code;
  }

 /**
  * The authorize endpoint URL of the OAuth2 provider
  *
  * @param  array $params Additional query parameters
  * @return string
  */
  public function authorize_url($params = array())
  {
    return (strpos($uri, 'http') === 0) ? $this->options['authorize_url'].'?'.http_build_query($params) : $this->site.$this->options['authorize_url'].'?'.http_build_query($params);
  }

 /**
  * Initializes an AccessToken by making a request to the token endpoint
  *
  * @param  array $params An array of params for the token endpoint
  * @param  array $access Token options, to pass to the AccessToken object
  * @return \OAuth2\AccessToken
  */
  public function get_token($params = array(), $access_token_opts = array())
  {
    $opts = array(
        'raise_errors' => true
      , 'parse' => isset($params['parse']) ? $params['parse'] : 'automatic'
    );
    unset($params['parse']);
    
    if ($this->options['token_method'] === 'POST') {
      $opts['body']    = http_build_query($params);
      $opts['headers'] = array('Content-Type' => 'x-www-form-urlencoded');
    } else {
      $opts['params'] = http_build_query($params);
    }

    // Make request
    $response = $this->request($this->options['token_method'], $this->token_url(), $opts);
    
    // Handle response
    $parsedResponse = $response->parse();
    if (!is_array($parsedResponse) && !isset($parsedResponse['access_token'])) {
      throw new \OAuth2\Error($response);
    }

    // Return access token
    return \OAuth2\AccessToken::from_hash($this, array_merge($parsedResponse, $access_token_opts));
  }

 /**
  * The Resource Owner Password Credentials strategy
  *
  * @return \OAuth2\Strategy\Password
  */
  public function password()
  {
    $this->password = $this->password ? $this->password : new \OAuth2\Strategy\Password($this);
    return $this->password;
  }
  
 /**
  * Makes a request relative to the specified site root.
  *
  * @param string $verb One of the following http method: GET, POST, PUT, DELETE
  * @param string $url  URL path of the request
  * @param array  $opts The options to make the request with (possible options: params (array), body (string), headers (array), raise_errors (boolean), parse ('automatic', 'query' or 'json')
  */
  public function request($verb, $url, $opts = array())
  {
    // Set some default options
    $opts = array_merge(array(
        'params'       => array()
      , 'body'         => ''
      , 'headers'      => array()
      , 'raise_errors' => $this->options['raise_errors']
      , 'parse'        => 'automatic'
    ), $opts);

    // Create the request
    switch ($verb) {
      case 'DELETE':
        $request = $this->connection->delete($url, $opts['headers']);
        $request->getQuery()->merge($opts['params']);
        break;
      case 'POST':
        $request = $this->connection->post($url, $opts['headers'], $opts['params']);
        $request->setBody($opts['body']);
        break;
      case 'PUT':
        $request = $this->connection->put($url, $opts['headers'], $opts['body']);
        break;
      case 'GET':
      default:
        $request = $this->connection->get($url, $opts['headers'], $opts['body']);
        $request->getQuery()->merge($opts['params']);
        break;
    }

    // Custom cURL options
    $request->getCurlOptions->set(CURLOPT_MAXREDIRS, isset($this->options['max_redirects']) ? $this->options['max_redirects'] : 0);
    
    // Send request and use the returned HttpMessage to create an \OAuth2\Response object
    $response = new \OAuth2\Response($request->send(), array('parse' => $opts['parse']));

    // Response handling
    if (in_array($response->status(), range(200, 299))) {
      return $response;
    } else if (in_array($response->status(), range(400, 599))) {
      $e = new \OAuth2\Error($response);
      if ($opts['raise_errors'] || $this->options['raise_errors']) {
        throw $e;
      }
      $response->error = $e;
      return $response;
    } else {
      throw new \OAuth2\Error($response);
    }
  }

 /**
  * The token endpoint URL of the OAuth2 provider
  *
  * @param  array $params Additional query parameters
  * @return string
  */
  public function token_url($params = array())
  {
    return $this->options['token_url'].'?'.http_build_query($params);
  }
}
