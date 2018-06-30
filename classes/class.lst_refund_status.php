<?php
define('VERSION_LST_REFUND_STATUS','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class lst_refund_status extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_refund_status','Refund Status');
    $this->set_plural_append('','es');
  }
  public function get_version(){
    return VERSION_LST_REFUND_STATUS;
  }
}
?>