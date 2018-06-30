<?php
define('VERSION_TAX_CODE','1.0.1');
/*
Version History:
  1.0.1 (2014-01-28)
    1) Refreshed fields list - now declared as a class constant
  1.0.0 (2012-10-29)
    1) Initial release
*/

class Tax_Code extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, name, qb_ident, qb_name, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("tax_code",$ID);
    $this->_set_object_name("Tax Code");
  }

  public function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  public static function get_selector_sql(){
    return
       "SELECT\n"
      ."  `ID` `value`,\n"
      ."  CONCAT((SELECT `TextEnglish` FROM `system` WHERE `system`.`ID` = `tax_code`.`systemID`),' | ',`name`) `text`,\n"
      ."  IF(`tax_code`.`systemID`=1,'e0e0e0',IF(`tax_code`.`systemID`=".SYS_ID.",'c0ffc0','ffe0e0')) AS `color_background`\n"
      ."FROM\n"
      ."  `tax_code`\n"
      ."ORDER BY\n"
      ."  `text`";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip);
  }

  public function get_version(){
    return VERSION_TAX_CODE;
  }
}
?>