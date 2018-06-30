<?php
define('VERSION_TAX_RULE','1.0.1');
/*
Version History:
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2010-03-15)
    Initial release
*/
class Tax_Rule extends Record {
  function __construct($ID="") {
    parent::__construct("tax_rule",$ID);
    $this->_set_object_name("Tax Rule");
    $this->_set_message_associated('');
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID);
    return parent::sql_export($targetID,$show_fields,$header);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip);
  }

  public function get_version(){
    return VERSION_TAX_RULE;
  }
}
?>