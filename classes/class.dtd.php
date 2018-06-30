<?php
define ("VERSION_DTD","1.0.0");
/*
Version History:
  1.0.0 (2012-02-03)
    1) Initial release
*/
class DTD extends Record {

  public function draw(){
    header("Content-Type: application/xml-dtd; charset=utf-8");
    print file_get_contents(SYS_SHARED.'xhtml1-strict-with-iframe.dtd');
    die();
  }

  public function get_version(){
    return VERSION_DTD;
  }

}
?>