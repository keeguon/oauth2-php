<?php

namespace OAuth2\Strategy;

class Password extends \OAuth2\Strategy\Base
{
 /**
  * Not used for this strategy
  */
  public function authorizeUrl()
  {
    throw new \ErrorException('The authorization endpoint is not used in this strategy.');
  }

 /**
  * Retrieve an access token given the specified End User username and password.
  *
  * @param string $username The End User username
  * @param string $password The End User password
  * @param array  $params   Additional params
  * @param array  $opts     Options 
  */
  public function getToken($username, $password, $params = array(), $opts = array())
  {
    $params = array_merge(array(
        'grant_type' => 'password'
      , 'username'   => $username
      , 'password'   => $password
    ), $params);
    return $this->client->getToken($params, $opts);
  }
}
