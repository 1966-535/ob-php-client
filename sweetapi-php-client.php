<?php

// GET http://swiq3.com:8080/locations?apikey=YOUR_API_KEY&client_id=YOUR_CLIENT_ID

class sweetClient {
  public $handle;
  public $http_options;
  public $response_object;
  public $response_info;

   /**
   * Constructor.
   *
   */
  function __construct() {
    $this->http_options = [];
    
// @todo 
// curl_setopt($ch, CURLOPT_TIMEOUT, 5);
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $this->http_options[CURLOPT_RETURNTRANSFER] = true;
    $this->http_options[CURLOPT_FOLLOWLOCATION] = false;
  }
  
  /**
   * Perform a GET call to server
   *
   * Additionaly in $response_object and $response_info are the
   * response from server and the response info as it is returned
   * by curl_exec() and curl_getinfo() respectively.
   *
   * @param string $url The url to make the call to.
   * @param array $http_options Extra option to pass to curl handle.
   * @return string The response from curl if any
   */
  public function get($url, $http_options = array()) {
    $http_options = $http_options + $this->http_options;
    $this->handle = curl_init($url);
    if(! curl_setopt_array($this->handle, $http_options)){
      throw new RestClientException("Error setting cURL request options");
    }
    $this->response_object = curl_exec($this->handle);
    $this->_http_parse_message($this->response_object);
    curl_close($this->handle);
    return $this->response_object;
  }
 
  /**
   * Response handler.
   *
   *
   * @param string $res the response object from the API.
   * @return string exception
   */
  private function _http_parse_message($res) {
    if(! $res){
      throw new HttpServerException(curl_error($this->handle), -1);
    }
    $this->response_info = curl_getinfo($this->handle);
    $code = $this->response_info['http_code'];
    if($code == 404) {
      throw new HttpServerException404(curl_error($this->handle));
    }
    if($code >= 400 && $code <=600) {
      throw new HttpServerException('Server response status was: ' . $code .
        ' with response: [' . $res . ']', $code);
    }
    if(!in_array($code, range(200,207))) {
      throw new HttpServerException('Server response status was: ' . $code .
        ' with response: [' . $res . ']', $code);
    }
  }
}
