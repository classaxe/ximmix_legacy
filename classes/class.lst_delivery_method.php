<?php
define('VERSION_LST_DELIVERY_METHOD','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class lst_delivery_method extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_delivery_method','Delivery Method');
  }
  public function get_version(){
    return VERSION_LST_DELIVERY_METHOD;
  }
}
?>