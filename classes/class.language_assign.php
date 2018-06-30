<?php
define('VERSION_LANGUAGE_ASSIGN','1.0.1');
/*
Version History:
  1.0.1 (2013-10-10)
    1) New method Language_Assign::get_listdata_for_assignment()
    2) New method Language_Assign::get_text_csv_for_assignment()
  1.0.0 (2013-10-08)
    Initial release
*/
class Language_Assign extends Record {
  function __construct($ID=""){
    parent::__construct("language_assign",$ID);
    $this->_set_object_name('Language Assign');
  }

  function delete_for_assignment($assign_type,$assignID) {
    $sql =
       "DELETE FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `assign_type` = \"".$assign_type."\" AND\n"
      ."  `assignID` IN(".$assignID.")";
//    z($sql);
    $this->do_sql_query($sql);
  }

  function get_listdata_for_assignment($assign_type,$assignID) {
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listtypeID`=(SELECT `ID` FROM `listtype` WHERE `listtype`.`name`='lst_language') AND\n"
      ."  `ID` IN(SELECT `languageID` FROM `".$this->_get_table_name()."` WHERE `assign_type` = \"".$assign_type."\" AND `assignID` IN(".$assignID."))";
//    z($sql);
    return $this->get_records_for_sql($sql);
  }

  function get_text_csv_for_assignment($assign_type,$assignID) {
    $sql =
       "SELECT\n"
      ."  GROUP_CONCAT(`textEnglish` ORDER BY `textEnglish` separator ', ') `text`\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."WHERE\n"
      ."  `listtypeID`=(SELECT `ID` FROM `listtype` WHERE `listtype`.`name`='lst_language') AND\n"
      ."  `ID` IN(SELECT `languageID` FROM `".$this->_get_table_name()."` WHERE `assign_type` = \"".$assign_type."\" AND `assignID` IN(".$assignID."))";
//    z($sql);
    return $this->get_field_for_sql($sql);
  }

  function set_for_assignment($assign_type,$assignID,$csv,$systemID) {
    $this->delete_for_assignment($assign_type,$assignID);
    if (!$csv) {
      return;
    }
    $arr = explode(",",$csv);
    $Obj = new Lst_Language;
    foreach ($arr as $language) {
      $languageID = $Obj->get_ID_by_name(trim($language),"1,".$systemID);
      if ($languageID) {
        $data =
          array(
            'assign_type' =>$assign_type,
            'assignID' =>   $assignID,
            'languageID' => $languageID,
            'systemID' =>   $systemID
          );
//        y($data);
        $this->insert($data);
      }
    }
  }

  public function get_version(){
    return VERSION_LANGUAGE_ASSIGN;
  }
}
?>