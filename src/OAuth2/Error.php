<?php

namespace OAuth2;

class Error extends \Exception
{
  
 /**
  * Construct the OAuth 2 error using a response object
  *
  * @param \HttpResponse $response The response object
  */
  public function __construct($response)
  {
    
  }
  
}