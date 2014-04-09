<?php

namespace OAuth2;

class Error extends \Exception
{
  protected $code     = 0;
  protected $message  = '';
  protected $response = null;

 /**
  * Construct the OAuth 2 error using a response object
  *
  * @param \HttpResponse $response The response object
  */
  public function __construct($response)
  {
    $response->error = $this;
    $this->response  = $response;

    $parsedResponse = $response->parse();
    if (is_array($parsedResponse)) {
      $this->code    = isset($parsedResponse['error']) ? $parsedResponse['error'] : 0;
      $this->message = isset($parsedResponse['error_description']) ? $parsedResponse['error_description'] : null;
    }
  }

 /**
  * response getter
  *
  * @return OAuth2\Response
  */
  public function getResponse()
  {
    return $this->response;
  }
}
