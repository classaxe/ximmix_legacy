<?php
define('VERSION_REPORT_COLUMN_TYPE','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class Report_Column_Type extends lst_named_type {
  function __construct($ID="") {
    parent::__construct($ID,'lst_column_types','Report Column Type');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new value'
      )
    );
  }
  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    // Supports rename
    return parent::try_copy($newID,$msg,$msg_tooltip,$name,true);
  }
  public function get_version(){
    return VERSION_REPORT_COLUMN_TYPE;
  }
}
?>