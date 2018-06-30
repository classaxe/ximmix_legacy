<?php
define('VERSION_LST_NAMED_TYPE','1.0.4');
/*
Version History:
  1.0.4 (2012-04-20)
    1) Added new method lst_named_type::get_listdata() to get all items
  1.0.3 (2011-09-06)
    1) Added lst_named_type::get_value_for_text() - used in Country class
  1.0.2 (2011-02-01)
    1) Moved setter and getter for listTypeID into record and fixed a bug there
    2) Added get_text_for_value() and get_value_for_text()
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class lst_named_type extends Record {

  function __construct($ID="",$listtype="",$object_name="") {
    parent::__construct('listdata',$ID);
    $this->_set_name_field('value'); // Default for exists_named() etc - may be overridden
    if ($listtype!="") {
      $this->_set_listtype($listtype);
      $Obj = new Record('listtype');
      $this->_set_listTypeID($Obj->get_ID_by_name($listtype));
    }
    if ($object_name) { $this->_set_object_name($object_name); }
  }

  function get_listdata($sortBy=''){
    $out = array();
    $sql =
       "SELECT\n"
      ."  `listdata`.*\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listdata`.`listtypeID` = ".$this->_get_listtypeID()." AND\n"
      ."  `listdata`.`systemID` IN (1,".SYS_ID.")\n"
      ."ORDER BY\n  "
      .($sortBy!='' ?
          $sortBy
       :
          "  `seq`,"
         ."  `textEnglish`"
       );
    return $this->get_records_for_sql($sql);
  }

  function get_text_for_value($value){
    $sql =
       "SELECT\n"
      ."  `textEnglish`\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listtypeID` = ".$this->_get_listtypeID()." AND\n"
      ."  `value` = \"".$value."\"\n"
      ."ORDER BY\n"
      ."  `systemID` = ".SYS_ID." DESC\n"
      ."LIMIT 0,1";
    return $this->get_field_for_sql($sql);
  }

  function get_value_for_text($text){
    $sql =
       "SELECT\n"
      ."  `value`\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listtypeID` = ".$this->_get_listtypeID()." AND\n"
      ."  `textEnglish` = \"".$text."\"\n"
      ."ORDER BY\n"
      ."  `systemID` = ".SYS_ID." DESC\n"
      ."LIMIT 0,1";
    return $this->get_field_for_sql($sql);
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  public function get_version(){
    return VERSION_LST_NAMED_TYPE;
  }
}
?>