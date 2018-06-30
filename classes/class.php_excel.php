<?php
define ("VERSION_PHP_EXCEL","1.0.2");
/*
Version History:
  1.0.2 (2012-05-01)
    1) Changes to constructor:
       Greatly improved error handling if Pear date library isn't present -
       Now provides instructions to correct the issue
  1.0.1 (2011-03-02)
    1) Now includes PHPExcel.php from path supplied when package is installed
       via PEAR channel installer
  1.0.0 (2011-03-01)
    1) Initial release -
       Implements PEAR version of libraries at http://www.phpexcel.net
*/

@include_once("PHPExcel/PHPExcel.php");
if (class_exists('PHPExcel')){
  class PHP_Excel extends PHPExcel{
    function __construct(){
      parent::__construct();
    }
    public function get_version(){
      return VERSION_PHP_EXCEL;
    }
  }
}
else {
  class PHP_Excel{
    function __construct(){
      Page::push_content(
        'javascript_onload',
         "showPopWin("
        ."\"Missing Library\","
        ."\"<div style='padding:5px;'>\\n"
        ."<p><b>Cannot find PEAR Library PHPExcel.php</b><br />\\n"
        ."To install it, open a shell console and enter the following commands:</p>\\n"
        ."<p><code><b>pear channel-discover pear.pearplex.net</b></code><br />\\n"
        ."<code><b>pear install pearplex/PHPExcel</b></code></p>\\n"
        ."<div style='text-align:center'>\\n"
        ."<input type='button' value='&nbsp;OK&nbsp;' class='formButton' onclick='hidePopWin()' />\\n"
        ."</div>\","
        ."400,200)");
      return;
    }
    public function get_version(){
      return VERSION_PHP_EXCEL;
    }
  }
}
?>