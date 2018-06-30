<?php
define('VERSION_LISTDATA','1.0.5');
/*
Version History:
  1.0.5 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.listdata.txt)
*/
class Listdata extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, color_background, color_text, content, custom_1, custom_2, custom_3, custom_4, custom_5, isHeader, listTypeID, parentID, seq, textEnglish, thumbnail, value, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("listdata",$ID);
    $this->_set_object_name('List Data Item');
  }

  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  public function get_version(){
    return VERSION_LISTDATA;
  }
}
?>