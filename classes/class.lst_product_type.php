<?php
define('VERSION_LST_PRODUCT_TYPE','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class lst_product_type extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_product_type','Product Type');
  }
  public function get_version(){
    return VERSION_LST_PRODUCT_TYPE;
  }
}
?>