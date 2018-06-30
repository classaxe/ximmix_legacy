<?php
define('VERSION_BASE_ERROR','1.0.1');
/*
Version History:
  1.0.1 (2011-04-07)
    1) Now extends Exception class to allow these to be thrown properly
  1.0.0 (2009-07-02)
    Initial release
*/
class Base_Error extends Exception{
  private $errorMessage;
  public function __construct($errorMessage='') {
    $this->errorMessage = $errorMessage;
  }
  public function __call($methodName, $parameters) {
    return $this->errorMessage;
  }
  public function __get($propertyName) {
    return $this->errorMessage;
  }
  public function __set($propertyName, $propertyValue) {
    return $this->errorMessage;
  }
  public function get_version(){
    return VERSION_BASE_ERROR;
  }
}

?>