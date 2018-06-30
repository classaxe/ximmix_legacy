<?php
define('VERSION_LST_PERSONTITLE','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class lst_persontitle extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_persontitle','Person Title');
  }
  public function get_version(){
    return VERSION_LST_PERSONTITLE;
  }
}
?>