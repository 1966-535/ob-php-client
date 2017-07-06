<?php

// $ch = curl_init('http://localhost:8080/stocks/add');
// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
// curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
// curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($ch, CURLOPT_HTTPHEADER, array(
//     'Content-Type: application/json',
//     'Content-Length: ' . strlen($data_string))
// );
// curl_setopt($ch, CURLOPT_TIMEOUT, 5);
// curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
//
// //execute post
// $result = curl_exec($ch);
//
// //close connection
// curl_close($ch);
//
// echo $result;


// GET http://swiq3.com:8080/locations?apikey=YOUR_API_KEY&client_id=YOUR_CLIENT_ID

class sweetClient {
  public $handle;
  public $http_options;
  public $response_object;
  public $response_info;

  function __construct() {
    $this->http_options = [];
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
  function get($url, $http_options = array()) {
    $http_options = $http_options + $this->http_options;
    $this->handle = curl_init($url);
    if(! curl_setopt_array($this->handle, $http_options)){
      throw new RestClientException("Error setting cURL request options");
    }
    $this->response_object = curl_exec($this->handle);
    $this->http_parse_message($this->response_object);
    curl_close($this->handle);
    return $this->response_object;
  }
  /**
   * Perform a POST call to the server
   *
   * Additionaly in $response_object and $response_info are the
   * response from server and the response info as it is returned
   * by curl_exec() and curl_getinfo() respectively.
   *
   * @param string $url The url to make the call to.
   * @param string|array The data to post. Pass an array to make a http form post.
   * @param array $http_options Extra option to pass to curl handle.
   * @return string The response from curl if any
   */
  function post($url, $fields = array(), $http_options = array()) {
    $http_options = $http_options + $this->http_options;
    $http_options[CURLOPT_POST] = true;
    $http_options[CURLOPT_POSTFIELDS] = $fields;
    if(is_array($fields)){
      $http_options[CURLOPT_HTTPHEADER] =
        array('Content-Type: multipart/form-data');
    }
    $this->handle = curl_init($url);
    if(! curl_setopt_array($this->handle, $http_options)){
      throw new RestClientException("Error setting cURL request options.");
    }
    $this->response_object = curl_exec($this->handle);
    $this->http_parse_message($this->response_object);
    curl_close($this->handle);
    return $this->response_object;
  }
  /**
   * Perform a PUT call to the server
   *
   * Additionaly in $response_object and $response_info are the
   * response from server and the response info as it is returned
   * by curl_exec() and curl_getinfo() respectively.
   *
   * @param string $url The url to make the call to.
   * @param string|array The data to post.
   * @param array $http_options Extra option to pass to curl handle.
   * @return string The response from curl if any
   */
  function put($url, $data = '', $http_options = array()) {
    $http_options = $http_options + $this->http_options;
    $http_options[CURLOPT_CUSTOMREQUEST] = 'PUT';
    $http_options[CURLOPT_POSTFIELDS] = $data;
    $this->handle = curl_init($url);
    if(! curl_setopt_array($this->handle, $http_options)){
      throw new RestClientException("Error setting cURL request options.");
    }
    $this->response_object = curl_exec($this->handle);
    $this->http_parse_message($this->response_object);
    curl_close($this->handle);
    return $this->response_object;
  }
  /**
   * Perform a DELETE call to server
   *
   * Additionaly in $response_object and $response_info are the
   * response from server and the response info as it is returned
   * by curl_exec() and curl_getinfo() respectively.
   *
   * @param string $url The url to make the call to.
   * @param array $http_options Extra option to pass to curl handle.
   * @return string The response from curl if any
   */
  function delete($url, $http_options = array()) {
    $http_options = $http_options + $this->http_options;
    $http_options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
    $this->handle = curl_init($url);
    if(! curl_setopt_array($this->handle, $http_options)){
      throw new RestClientException("Error setting cURL request options.");
    }
    $this->response_object = curl_exec($this->handle);
    $this->http_parse_message($this->response_object);
    curl_close($this->handle);
    return $this->response_object;
  }
  private function http_parse_message($res) {
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
