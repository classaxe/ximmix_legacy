<?php
define('VERSION_LST_PAYMENT_STATUS','1.0.2');
/*
Version History:
  1.0.2 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.1 (2009-11-10)
    1) Added lst_payment_status::is_approved()
  1.0.0 (2009-07-02)
    Initial release
*/
class lst_payment_status extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_payment_status','Payment Status');
    $this->set_plural_append('','es');
  }

  function is_approved($status){
    if (!$ID = $this->get_ID_by_name($status)){
      return false;
    }
    $this->_set_ID($ID);
    return ($this->get_field('custom_1')=='1' ? true : false);
  }
  
  public function get_version(){
    return VERSION_LST_PAYMENT_STATUS;
  }
}
?>