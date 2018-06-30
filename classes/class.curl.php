<?php
define('VERSION_CURL','1.0.1');
/*
Version History:
  1.0.1 (2009-10-21)
    1) Changes to Curl::_construct() to allow for cookies file for persistent sessions
  1.0.0 (2009-07-02)
    Initial release
*/
class Curl {
  var $ch;
  function __construct($URL='',$vars='',$cookies=false) {
    $this->ch =     curl_init();
    curl_setopt($this->ch,CURLOPT_POST,1);
    curl_setopt($this->ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($this->ch,CURLOPT_URL,$URL);
    curl_setopt($this->ch,CURLOPT_POSTFIELDS,$vars);
    curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
    if ($cookies){
      curl_setopt($this->ch, CURLOPT_COOKIEJAR, $cookies);
      curl_setopt($this->ch, CURLOPT_COOKIEFILE, $cookies);
    }
    //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  }
  // ************************************
  // * METHOD: exec()                   *
  // ************************************
  function exec(){
    $result = curl_exec($this->ch);
    $errors = curl_errno($this->ch);
    if ($errors!="0") {
      return false;
    }
    else {
      $result_arr = explode("&",$result);
      $out = array();
      for ($i=0; $i<count($result_arr); $i++) {
        $row = explode("=",$result_arr[$i]);
        $out[$row[0]] = $row[1];
      }
      return $out;
    }
    curl_close($this->ch);
  }
  // ************************************
  // * METHOD: get()                    *
  // ************************************
  function get(){
    $out = curl_exec($this->ch);
    $errors = curl_errno($this->ch);
    if ($errors!="0") {
      $out = "Curl errors: $errors";
    }
    curl_close($this->ch);
    return $out;
  }
  public function get_version(){
    return VERSION_CURL;
  }
}
?>