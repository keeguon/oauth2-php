<?php

namespace OAuth2;

class AccessToken
{
  public $options = array();

  protected $client       = null;
  protected $expiresIn    = null;
  protected $params       = array();
  protected $refreshToken = null;
  protected $token        = null;

 /**
  * Initializes an AccessToken from a Hash
  *
  * @param  \OAuth2\Client $client The OAuth2::Client instance
  * @param  array          $hash   Array of AccessToken property values
  * @return \OAuth2\AccessToken
  */
  public static function fromHash($client, $hash)
  {
    // Clumsy PHP token handling
    $token = $hash['access_token'];
    unset($hash['access_token']);

    return new \OAuth2\AccessToken($client, $token, $hash);
  }

 /**
  * Initializes an AccessToken from a key/value application/x-www-form-urlencoded string
  *
  * @param  \OAuth2\Client $client The OAuth2::Client instance
  * @param  string         $kvform The application/x-www-form-urlencoded string
  * @return \OAuth2\AccessToken
  */
  public static function fromKvform($client, $kvform)
  {
    // Parse key/value application/x-www-form-urlencoded string into a hash
    parse_str($kvform, $hash);

    return \OAuth2\AccessToken::fromHash($client, $hash);
  }
  
 /**
  * Creates an AccessToken
  *
  * @param \OAuth2\Client $client The OAuth2::Client instance
  * @param string         $token  The Access Token value
  * @param array          $opts   The options to create the Access Token with
  */
  public function __construct($client, $token, $opts = array())
  {
    // Set default options
    $opts = array_merge(array(
        'refresh_token' => null           // string  The refresh_token value
      , 'expires_in'    => null           // integer The number of seconds in which the AccessToken will expire
      , 'mode'          => 'header'       // string  The transmission mode of the Access Token parameter value one of 'header', 'body' or 'query'
      , 'header_format' => 'Bearer %s'    // string  The string format to use for the Authorization header
      , 'param_name'    => 'bearer_token' // string  he parameter name to use for transmission of the Access Token value in 'body' or 'query' transmission mode
    ), $opts);

    // Setting class attributes
    $this->client = $client;
    $this->token  = $token;
    foreach (array('refresh_token', 'expires_in') as $arg) {
      // camelize arg
      $camelizedArg = lcfirst(str_replace(" ", "", ucwords(strtr($arg, "_-", "  "))));

      // set property
      $this->$camelizedArg = $opts[$arg];
      unset($opts[$arg]);
    }

    $this->options = array(
        'mode'          => $opts['mode']
      , 'header_format' => $opts['header_format']
      , 'param_name'    => $opts['param_name']
    );
    unset($opts['mode'], $opts['header_format'], $opts['param_name']);

    $this->params = $opts;
  }

 /**
  * client getter
  *
  * @return OAuth2\Client
  */
  public function getClient()
  {
    return $this->client;
  }

 /**
  * expiresIn getter
  *
  * @return mixed
  */
  public function getExpiresIn()
  {
    return $this->expiresIn;
  }

 /**
  * params getter
  *
  * @return array
  */
  public function getParams()
  {
    return $this->params;
  }

 /**
  * param getter
  *
  * @return mixed
  */
  public function getParam($key)
  {
    return isset($this->params[$key]) ? $this->params[$key] : null;
  }

 /**
  * refreshToken getter
  *
  * @return mixed
  */
  public function getRefreshToken()
  {
    return $this->refreshToken;
  }

 /**
  * token getter
  *
  * @return mixed
  */
  public function getToken()
  {
    return $this->token;
  }

 /**
  * Whether or not the token expires
  *
  * @return boolean
  */
  public function expires()
  {
    return !is_null($this->expiresIn);
  }

 /**
  * Whether or not the token is expired
  *
  * @return boolean
  */
  public function isExpired()
  {
    return $this->expires() && ($this->expiresIn === 0);
  }

 /**
  * Make a request with the Access Token
  *
  * @param string $verb The HTTP request method
  * @param string $path The HTTP URL path of the request
  * @param array  $opts The options to make the request with
  * @see Client::sendRequest
  */
  public function request($verb, $path, $opts = array())
  {
    // Set parse mode
    $parseMode = 'automatic';
    if (isset($opts['parse'])) {
      $parseMode = $opts['parse'];
      unset($opts['parse']);
    }

    // Set token
    $opts = $this->setToken($opts);

    // Make request and return response
    $request = $this->client->createRequest($verb, $path, $opts);
    return $this->client->getResponse($request, $parseMode);
  }

 /**
  * Make a GET request with the Access Token
  *
  * @see request
  */
  public function get($path, $opts = array())
  {
    return $this->request('GET', $path, $opts);
  }

 /**
  * Make a POST request with the Access Token
  *
  * @see request
  */
  public function post($path, $opts = array())
  {
    return $this->request('POST', $path, $opts);
  }

 /**
  * Make a PUT request with the Access Token
  *
  * @see request
  */
  public function put($path, $opts = array())
  {
    return $this->request('PUT', $path, $opts);
  }

 /**
  * Make a DELETE request with the Access Token
  *
  * @see request
  */
  public function delete($path, $opts = array())
  {
    return $this->request('DELETE', $path, $opts);
  }

 /**
  * Refreshes the current Access Token
  *
  * @param  array               $params
  * @return \OAuth2\AccessToken $new_token
  */
  public function refresh($params = array())
  {
    if (!$this->refreshToken) {
      throw new \ErrorException("A refresh_token is not available");
    }

    $params = array_merge($params, array(
        'grant_type'    => 'refresh_token'
      , 'refresh_token' => $this->refreshToken
    ));

    $newToken = $this->client->getToken($params);
    $newToken->options = $this->options;
    return $newToken;
  }


  private function setToken($opts)
  {
    switch ($this->options['mode']) {
      case 'header':
        $opts['headers'] = isset($opts['headers']) ? $opts['headers'] : array();
        $opts['headers']['Authorization'] = sprintf($this->options['header_format'], $this->token);
        break;

      case 'query':
        $opts['query'] = isset($opts['query']) ? $opts['query'] : array();
        $opts['query'][$this->options['param_name']] = $this->token;
        break;

      case 'body':
        $opts['body'] = isset($opts['body']) ? $opts['body'] : '';
        $opts['body'] .= "{$this->options['param_name']}={$this->token}";
        break;

      default:
        throw new \ErrorException("invalid 'mode' option of {$this->options['mode']}");
        break;
    }

    return $opts;
  }
}

