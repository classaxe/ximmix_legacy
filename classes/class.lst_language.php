<?php
define('VERSION_LST_LANGUAGE','1.0.0');
/*
Version History:
  1.0.0 (2013-10-08)
    Initial release
*/
class lst_language extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_language','Language Code (ISO639-3)');
  }
  public function get_version(){
    return VERSION_LST_LANGUAGE;
  }
}
?>