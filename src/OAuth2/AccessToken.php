<?php

namespace OAuth2;

class AccessToken
{
  protected
      $client        = null
    , $expires_in    = null
    , $refresh_token = null
  ;

  public
      $expires_at    = null
    , $token         = null
    , $options = array()
  ;

 /**
  * Initializes an AccessToken from a Hash
  *
  * @param  \OAuth2\Client $client The OAuth2::Client instance
  * @param  array          $hash   Array of AccessToken property values
  * @return \OAuth2\AccessToken
  */
  public static function from_hash($client, $hash)
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
  public static function from_kvform($client, $kvform)
  {
    // Parse key/value application/x-www-form-urlencoded string into a hash
    parse_str($kvform, $hash);

    return \OAuth\AccessToken::from_hash($client, $hash);
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
      , 'expires_at'    => null           // integer The epoch time in seconds in which AccessToken will expire
      , 'mode'          => 'header'       // string  The transmission mode of the Access Token parameter value one of 'header', 'body' or 'query'
      , 'header_format' => 'Bearer %s'    // string  The string format to use for the Authorization header
      , 'param_name'    => 'bearer_token' // string  he parameter name to use for transmission of the Access Token value in 'body' or 'query' transmission mode
    ), $opts);

    // Setting class attributes
    $this->client = $client;
    $this->token  = $token;
    foreach (array('refresh_token', 'expires_in', 'expires_at') as $arg) {
      $this->$arg = (string) $opts[$arg];
    }
    $this->expires_in = isset($opts['expires']) ? (int) $opts['expires'] : (int) $this->expires_in;
    if ($this->expires_in) {
      $this->expires_at = $this->expires_at ? $this->expires_at : time() + $this->expires_in;
    }
    $this->options = array(
        'mode'          => $opts['mode']
      , 'header_format' => $opts['header_format']
      , 'param_name'    => $opts['param_name']
    );
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
  * Whether or not the token is expired
  *
  * @return boolean
  */
  public function is_expired()
  {
    return $this->expires() && ($this->expires_at < time());
  }

 /**
  * Whether or not the token expires
  *
  * @return boolean
  */
  public function expires()
  {
    return is_null($this->expires_at);
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
  * Refreshes the current Access Token
  *
  * @param  array               $params
  * @return \OAuth2\AccessToken $new_token
  */
  public function refresh($params = array())
  {
    if (!$this->refresh_token) {
      throw new Exception("A refresh_token is not available");
    }

    array_merge($params, array(
        'client_id'     => $this->client->id
      , 'client_secret' => $this->client->secret
      , 'grant_type'    => 'refresh_token'
      , 'refresh_token' => $this->refresh_token
    ));

    $new_token = $this->client->get_token($params);
    $new_token->options = $this->options;
    return $new_token;
  }

 /**
  * Make a request with the Access Token
  *
  * @param string $verb The HTTP request method
  * @param string $path The HTTP URL path of the request
  * @param array  $opts The options to make the request with
  * @see Client::request
  */
  public function request($verb, $path, $opts = array())
  {
    $opts = $this->set_token($opts);
    return $this->client->request($verb, $path, $opts);
  }


  private function set_token($opts)
  {
    switch ($this->options['mode']) {
      case 'header':
        $opts['headers'] = isset($opts['headers']) ? $opts['headers'] : array();
        $opts['headers']['Authorization'] = sprintf($this->options['header_format'], $this->token);
        break;

      case 'query':
        $opts['params'] = isset($opts['params']) ? $opts['params'] : array();
        $opts['params'][$this->options['param_name']] = $this->token;
        break;

      case 'body':
        $opts['body'] = isset($opts['body']) ? $opts['body'] : '';
        $opts['body'] += "{$this->options['param_name']}={$this->token}";
        break;

      default:
        throw new \Exception("invalid 'mode' option of {$this->options['mode']}");
        break;
    }

    return $opts;
  }
}
