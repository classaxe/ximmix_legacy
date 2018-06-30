<?php
define('VERSION_HTTP_RAW_SOCKET','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
// ####################################
// # Class HTTP_Raw_Socket            #
// ####################################
class HTTP_Raw_Socket {
  var $http_port, $http_site, $http_cookies, $http_debug;
  function http_raw_socket($http_port=false, $http_site=false, $http_cookies=false, $http_debug=false) {
    $this->http_port = $http_port;
    $this->http_site = $http_site;
    $this->http_cookies = $http_cookies;
    $this->http_debug = $http_debug;
  }
  function get_http_port() {
    return $this->http_port;
  }
  function get_http_site() {
    return $this->http_site;
  }
  function get_http_cookies() {
    return $this->http_cookies;
  }
  function get_http_debug() {
    return $this->http_debug;
  }
  function set_http_port($value) {
    $this->http_port = $value;
  }
  function set_http_site($value) {
    $this->http_site = $value;
  }
  function set_http_cookies($index,$value) {
    $this->http_cookies[$index] = $value;
  }
  function set_http_debug($value) {
    $this->http_debug = $value;
  }
  function show_codes($string) {
    $out = array();
    for ($i=0; $i<strlen($string); $i++) {
      $out[] =bin2hex(substr($string,$i,1));
    }
    return implode("",$out);
  }
  function setcookies($response) {
    global $http_cookies;
    $response_arr = explode("\n",$response);
    for($i=0; $i<count($response_arr); $i++) {
      if(preg_match("/^set-cookie:[\s]+([^=]+)=([^;]+)/i", $response_arr[$i],$match)) {
        $this->set_http_cookies($match[1],$match[2]);
      }
    }
  }

  function http_send($request){
    $out =    array();

    if (!$socket = fsockopen($this->get_http_site(),$this->get_http_port(), $errno, $errstr, 60)) {
    	echo $this->get_http_site." $errstr ($errno)<br />";
  	  return;
    }
    $cookies = array();
    foreach ($this->get_http_cookies() as $key=>$value) {
      $cookies[] = $key."=".$value;
    }
    if (count($cookies)) {
      $request = str_replace("Cookie: ####","Cookie: ".implode("; ",$cookies),$request);
    }
    fwrite($socket,$request,strlen($request));
     while (!feof($socket)) {
       $out[] = fgets($socket, 128);
    }
    $out =    implode("",$out);
    $this->setcookies($out);
    if ($this->get_http_debug()) {
      print "<table><tr><td><textarea style='width: 600px; height: 100px;'>Request\n$request</textarea></td>";
      print "<td><textarea style='width: 300px; height: 100px;'>Response\n$out</textarea></td></tr></table>";
    }
    fclose($socket);
    return $out;
  }
  public function get_version(){
    return VERSION_HTTP_RAW_SOCKET;
  }
}
?>