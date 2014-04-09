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
      $this->site = ['base_url' => $opts['site']];
      unset($opts['site']);
    }

    // Default options
    $this->options = array_merge([
        'authorize_url' => '/oauth/authorize'
      , 'token_url'     => '/oauth/token'
      , 'token_method'  => 'POST'
      , 'client_auth'   => 'header'
      , 'request_opts'  => [ 'exceptions' => true ]
    ], $opts);

    // Connection object using Guzzle
    $this->connection = new \GuzzleHttp\Client($this->site);
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
    $authorizeUrl = (strpos($this->options['authorize_url'], 'http') === 0) ? $this->options['authorize_url'] : $this->site['base_url'].$this->options['authorize_url'];
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
    $tokenUrl = (strpos($this->options['token_url'], 'http') === 0) ? $this->options['token_url'] : $this->site['base_url'].$this->options['token_url'];
    return (count($params)) ? $tokenUrl.'?'.http_build_query($params) : $tokenUrl;
  }

 /**
  * Makes a request relative to the specified site root.
  *
  * @param string $verb One of the following http method: GET, POST, PUT, DELETE
  * @param string $url  URL path of the request
  * @param array  $opts The options to make the request with (possible options: params (array), body (string), headers (array), exceptions (boolean), parse ('automatic', 'query' or 'json')
  * @return \GuzzleHttp\Message\Request
  */
  public function createRequest($verb, $url, $opts = array())
  {
    // Set some default options
    $opts = array_merge(array(
        'body'       => ''
      , 'query'     => array()
      , 'headers'    => array()
    ), $this->options['request_opts'], $opts);

    // Create the request
    $verb = (in_array($verb, ['GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'OPTIONS', 'PATCH']) ? $verb : 'GET');
    $request = $this->connection->createRequest($verb, $url, $opts);

    return $request;
  }

 /**
  * Initializes an AccessToken by making a request to the token endpoint
  *
  * @param  \GuzzleHttp\Message\Request $request   The request object
  * @param  string                      $parseMode The mode of parsing for the response
  * @return \OAuth2\Response
  */
  public function getResponse($request, $parseMode = 'automatic')
  {
    return new \OAuth2\Response($this->connection->send($request), $parseMode);
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
    // Get parse mode for the response
    $parseMode = isset($params['parse']) ? $params['parse'] : 'automatic';
    unset($params['parse']);

    if ($this->options['token_method'] === 'POST') {
      $opts['headers'] = array('Content-Type' => 'x-www-form-urlencoded');
      $opts['body']    = $params;
    } else {
      $opts['query'] = $params;
    }

    // Create request
    $request = $this->createRequest($this->options['token_method'], $this->tokenUrl(), $opts);

    // Set auth
    if (isset($this->options['client_auth'])) {
      if ($this->options['client_auth'] === 'header') {
        $request->setHeader('Authorization', 'Basic ' . base64_encode("$this->id:$this->secret"));
      } else if ($this->options['client_auth'] === 'query') {
        $request->getQuery()->merge(['client_id' => $this->id, 'client_secret' => $this->secret]);
      } else if ($this->options['client_auth'] === 'body') {
        // Retrieve current body as a \Guzzle\Query object since we'll have to add client auth
        $body = \GuzzleHttp\Query::fromString((string) $request->getBody());

        // Add client auth
        $body->merge(['client_id' => $this->id, 'client_secret' => $this->secret]);

        // Replace body
        $request->setBody(\GuzzleHttp\Stream\Stream::factory((string) $body));
      } else {
        throw new \Exception("Unknown client authentication method.");
      }
    } else {
      throw new \Exception("Missing client authentication method.");
    }

    // Get response
    $response = $this->getResponse($request, $parseMode);

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

