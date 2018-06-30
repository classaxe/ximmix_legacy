<?php
define('VERSION_LISTTYPE','1.0.7');
/*
Version History:
  1.0.7 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.listtype.txt)
*/
class Listtype extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, name, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("listtype",$ID);
    $this->_set_name_field('name');
    $this->_set_object_name('List Type');
    $this->_set_message_associated('and associated List Data records have');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new ListType name'
      )
    );
  }

  function copy($new_name="",$new_systemID="") {
    $list_data_arr = $this->get_listdata();
    $ID =           parent::copy($new_name,$new_systemID);
    $Obj_Listtype = new Listtype($ID);
    $Obj_ListData = new ListData;
    foreach ($list_data_arr as $list_data) {
      $data = $list_data;
      $data['listTypeID'] = $ID;
      if ($new_systemID) {
        $data['systemID'] = $new_systemID;
      }
      unset($data['ID']);
      unset($data['history_created_by']);
      unset($data['history_created_date']);
      unset($data['history_created_IP']);
      unset($data['history_modified_by']);
      unset($data['history_modified_date']);
      unset($data['history_modified_IP']);
      $Obj_ListData->insert($data);
    }
    return $ID;
  }

  function delete(){
    $list_data_arr = $this->get_listdata();
    $Obj_ListData = new ListData;
    foreach ($list_data_arr as $list_data) {
      $Obj_ListData->_set_ID($list_data['ID']);
      $Obj_ListData->delete();
    }
    parent::delete();
  }

  function export_sql($targetID,$show_fields) {
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with List Data";
    $extra_delete = "DELETE FROM `listdata`               WHERE `systemID` IN (1,".SYS_ID.") AND `listtypeID` IN (".$targetID.");\n";
    $Obj = new Backup;
    $extra_select = $Obj->db_export_sql_query("`listdata`              ","SELECT * FROM `listdata` WHERE `systemID` IN (1,".SYS_ID.") AND `listtypeID` IN (".$targetID.") ORDER BY `listtypeID`;",$show_fields)."\n";
    return  $this->sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_listdata($sortBy=''){
    $out = array();
    if ($this->_get_ID()=='') {
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `listdata`.*\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listdata`.`listTypeID` = ".$this->_get_ID()." AND\n"
      ."  `listdata`.`systemID` IN (1,".SYS_ID.")\n"
      .($sortBy!="" ? "ORDER BY\n  $sortBy" : "");
//    z($sql);
    return $this->get_records_for_sql($sql);
  }

  function get_next_seq() {
    $sql =
       "SELECT\n"
      ."  MAX(`seq`)+1\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listTypeID` = ".$this->_get_ID();
//    z($sql);
    $seq = $this->get_field_for_sql($sql);
    return (!$seq ? 1 : $seq);
  }

  function get_record_by_name($value){
    if ($this->_get_ID()=='') {
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `listdata`.*\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listdata`.`listTypeID` = ".$this->_get_ID()." AND\n"
      ."  `listdata`.`textEnglish` = \"$value\" AND\n"
      ."  `listdata`.`systemID` IN (1,".SYS_ID.")\n";
//    z($sql);
    return $this->get_record_for_sql($sql);
  }

  function get_sql_options($name,$sortBy='',$filter=''){
    return
       "SELECT\n"
      ."  `listdata`.`color_background`,\n"
      ."  `listdata`.`color_text`,\n"
      ."  `listdata`.`isHeader`,\n"
      ."  `listdata`.`textEnglish` AS `text`,\n"
      ."  `listdata`.`value` AS `value`\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."INNER JOIN `listtype` ON\n"
      ."  `listdata`.`listTypeID` = `listtype`.`ID`\n"
      ."WHERE\n"
      .($filter ? $filter." AND\n" : "")
      ."  `listtype`.`name` = '$name' AND\n"
      ."  `listdata`.`systemID` IN (1,".SYS_ID.")\n"
      ."ORDER BY\n"
      .($sortBy!='' ? "  $sortBy" : "  `seq`,`value`");
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function manage_data() {
    if (get_var('command')=='report'){
      return draw_auto_report('listdata_for_listtype',1);
    }
    if (!$selectID = get_var('selectID')) {
      return
         "<h3 style='margin:0.25em'>List Data for ".$this->_get_object_name()."</h3>"
        ."The ".$this->_get_object_name()." must be saved before any entries can be added";
    }
    $this->_set_ID($selectID);
    if ($this->exists()){
      return
         "<h3 style='margin:0.25em'>List Data for ".$this->_get_object_name()." \"".$this->get_name()."\"</h3>"
        .draw_auto_report('listdata_for_listtype',1);
    }
    return
       "<h3 style='margin:0.25em'>List Data for ".$this->_get_object_name()." #".$selectID."</h3>"
      ."Sorry - the ".$this->_get_object_name()." appears to have been deleted";
  }

  static function getListData($listTypeName, $filter = false, $sortBy = false) {
    // gets an assoc array of keys=>values for the list type requested, and perhaps the filter provided
    $sql =
       "SELECT\n"
      ."  `listdata`.`textEnglish` AS `text`,\n"
      ."  `listdata`.`value` AS `value`\n"
      ."FROM\n"
      ."  `listtype`\n"
      ."INNER JOIN `listdata` ON\n"
      ."  `listdata`.`listTypeID` = `listtype`.`ID`\n"
      ."WHERE\n"
      .($filter ? "  ".$filter." AND\n" : "")
      ."  `listdata`.`systemID` IN(1,".SYS_ID.") AND\n"
      ."  `listtype`.`name` = '".$listTypeName."'\n"
      .($sortBy ? "ORDER BY\n  ".$sortBy : "");
//    z($sql);
    $results = Listtype::get_records_for_sql($sql);
    $out = array();
    foreach ($results as $result){
      $out[$result['value']] = $result['text'];
    }
    return $out;
  }

  public function get_version(){
    return VERSION_LISTTYPE;
  }
}
?>