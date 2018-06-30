<?php
define('VERSION_GATEWAY_TYPE','1.0.2');
/*
Version History:
  1.0.2 (2012-05-08)
    1) Added handle_report_copy() method for cloning entries
    2) Removed stub for Gateway_Type::get_beanstream_country() -
       was never implemented nor used
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Gateway_Type extends Record{

  public function __construct($ID="") {
    parent::__construct("gateway_type",$ID);
    $this->_set_object_name("Payment Gateway Type");
  }

  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  public function get_version(){
    return VERSION_GATEWAY_TYPE;
  }
}
?>