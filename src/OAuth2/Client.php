<?php

namespace OAuth2;

class Client
{
  public $connection = null;
  public $options    = '';
  public $site       = '';

  protected $id     = '';
  protected $secret = '';

  public function __construct($clientId, $clientSecret, $opts = array())
  {
    $this->id     = $clientId;
    $this->secret = $clientSecret;
    if (isset($opts['site'])) {
      $this->site = $opts['site'];
      unset($opts['site']);
    }

    // Default options
    $this->options = array_merge(array(
        'authorize_url' => '/oauth/authorize'
      , 'token_url'     => '/oauth/token'
      , 'token_method'  => 'POST'
      , 'client_auth'   => 'header'
      , 'request_opts'  => array()
      , 'max_redirects' => 5
      , 'raise_errors'  => true
    ), $opts);

    // Connection object using Guzzle
    $this->connection = new \Guzzle\Service\Client($this->site);
  }
 
 /**
  * id getter
  *
  * @return string
  */
  public function getId()
  {
    return $this->id;
  }

 /**
  * secret getter
  *
  * @return string
  */
  public function getSecret()
  {
    return $this->secret;
  }

 /**
  * The authorize endpoint URL of the OAuth2 provider
  *
  * @param  array $params Additional query parameters
  * @return string
  */
  public function authorizeUrl($params = array())
  {
    $authorizeUrl = (strpos($this->options['authorize_url'], 'http') === 0) ? $this->options['authorize_url'] : $this->site.$this->options['authorize_url'];
    return (count($params)) ? $authorizeUrl.'?'.http_build_query($params) : $authorizeUrl;
  }

 /**
  * The token endpoint URL of the OAuth2 provider
  *
  * @param  array $params Additional query parameters
  * @return string
  */
  public function tokenUrl($params = array())
  {
    $tokenUrl = (strpos($this->options['token_url'], 'http') === 0) ? $this->options['token_url'] : $this->site.$this->options['token_url'];
    return (count($params)) ? $tokenUrl.'?'.http_build_query($params) : $tokenUrl;
  }

 /**
  * Makes a request relative to the specified site root.
  *
  * @param string $verb One of the following http method: GET, POST, PUT, DELETE
  * @param string $uri  URI path of the request
  * @param array  $opts The options to make the request with (possible options: params (array), body (string), headers (array), raise_errors (boolean), parse ('automatic', 'query' or 'json')
  * @return \Guzzle\Http\Message\Request
  */
  public function createRequest($verb, $uri, $opts = array())
  {
    // Set some default options
    $opts = array_merge(array(
        'query'   => array()
      , 'headers' => array()
      , 'body'    => ''
    ), $opts);
    $opts['request_opts'] = isset($opts['request_opts']) ? array_merge($this->options['request_opts'], $opts['request_opts']) : $this->options['request_opts'];

    // Create the request
    $verb    = (in_array($verb, array('GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'OPTIONS', 'PATCH')) ? $verb : 'GET');
    $request = $this->connection->createRequest($verb, $uri, $opts['headers'], $opts['body'], $opts['request_opts']);
    $request->getQuery()->merge($opts['query']);

    return $request;
  }

 /**
  * Initializes an AccessToken by making a request to the token endpoint
  *
  * @param  \Guzzle\Http\Message\Request $request The request object
  * @param  array                        $options An array of options to handle the response
  * @return \OAuth2\Response
  */
  public function getResponse($request, array $opts = array())
  {
    // Send request and use the returned HttpMessage to create an \OAuth2\Response object
    $response = new \OAuth2\Response($request->send(), (isset($params['parse']) ? $params['parse'] : 'automatic'));

    // Response handling
    if (in_array($response->status(), range(200, 299))) {
      // Reset redirect count for future requests since we reached something
      $this->options['redirect_count'] = 0;

      return $response;
    } else if (in_array($response->status(), range(300, 399))) {
      // Count redirects
      $this->options['redirect_count'] = $this->options['redirect_count'] || 0;
      $this->options['redirect_count'] += 1;
      if ($this->options['redirect_count'] > $this->options['max_redirects']) {
        return $response;
      }

      // Get vars to make the redirect
      if ($response->status() === 303) {
        $verb = 'GET';
        $body = '';
      }
      $headers = $response->headers();

      // Create redirected request and get response
      $request = $this->createRequest($verb ? $verb : $request->getMethod, $headers['location'], array(
          'params'  => $request->getQuery()
        , 'headers' => $request->getHeaders()
        , 'body'    => $body ? $body : $request->getResponseBody()
      ));
      return $this->getResponse($request, $opts);
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
  * Initializes an AccessToken by making a request to the token endpoint
  *
  * @param  array $params An array of params for the token endpoint
  * @param  array $access Token options, to pass to the AccessToken object
  * @return \OAuth2\AccessToken
  */
  public function getToken($params = array(), $tokenOpts = array())
  {
    $opts = array(
        'raise_errors' => true
      , 'parse'        => isset($params['parse']) ? $params['parse'] : 'automatic'
    );
    unset($params['parse']);

    $requestOpts = array();
    if ($this->options['token_method'] === 'POST') {
      $requestOpts['headers'] = array('Content-Type' => 'x-www-form-urlencoded');
      $requestOpts['body']    = $params;
    } else {
      $requestOpts['query'] = $params;
    }

    // Make request
    $request = $this->createRequest($this->options['token_method'], $this->tokenUrl(), array(
        'params'  => $params
      , 'headers' => isset($headers) ? $headers : array()
    ));

    // Set auth
    if (isset($this->options['client_auth'])) {
      if ($this->options['client_auth'] === 'header') {
        $request->setAuth($this->id, $this->secret);
      } else if ($this->options['client_auth'] === 'query') {
        $request->getQuery()->merge(array('client_id' => $this->id, 'client_secret' => $this->secret));
      } else if ($this->options['client_auth'] === 'body') {
        if (!in_array($request->getMethod(), array('GET', 'HEAD', 'TRACE', 'OPTIONS'))) {
          $request->getBody()->addPostFields(array('client_id' => $this->id, 'client_secret' => $this->secret));
        }
      } else {
        throw new \Exception("Unknown client authentication method.");
      }
    } else {
      throw new \Exception("Missing client authentication method.");
    }

    // Get response
    $response = $this->getResponse($request, $opts);

    // Handle response
    $parsedResponse = $response->parse();
    if (!is_array($parsedResponse) && !isset($parsedResponse['access_token'])) {
      throw new \OAuth2\Error($response);
    }

    // Return access token
    return \OAuth2\AccessToken::fromHash($this, array_merge($parsedResponse, $tokenOpts));
  }

 /**
  * The Authorization Code strategy
  *
  * @return \OAuth2\Strategy\AuthCode
  */
  public function authCode()
  {
    $this->authCode = isset($this->authCode) ? $this->authCode : new \OAuth2\Strategy\AuthCode($this);
    return $this->authCode;
  }

 /**
  * The Resource Owner Password Credentials strategy
  *
  * @return \OAuth2\Strategy\Password
  */
  public function password()
  {
    $this->password = isset($this->password) ? $this->password : new \OAuth2\Strategy\Password($this);
    return $this->password;
  }
}
