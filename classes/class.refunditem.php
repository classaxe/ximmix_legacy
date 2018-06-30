<?php
define('VERSION_REFUNDITEM','1.0.1');
/*
Version History:
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class RefundItem extends OrderItem {

  function __construct($ID="") {
    parent::__construct($ID);
    $this->_set_object_name('Refunded Item');
  }

  public function get_version(){
    return VERSION_REFUNDITEM;
  }
}
?>