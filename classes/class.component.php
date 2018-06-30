<?php
define ("VERSION_COMPONENT","1.0.107");
/*
Version History:
  1.0.107 (2013-11-13)
    1) Archived old comments

  (Older version history in class.component.txt)
*/
class Component extends Component_Base {

  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function execute($data=false) {
    $php = $this->get_field('php');
    return eval($php);
  }

  public function execute_code_isolated($php,$args){
    return eval($php);
  }

  public static function get_selector_sql(){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN){
      return
         "SELECT\n"
        ."  `component`.`ID` AS `value`,\n"
        ."  CONCAT(IF(`component`.`ID`=1,' ',CONCAT(IF(`component`.`systemID` = 1,'* ',CONCAT(UPPER(`system`.`textEnglish`),' | ')))),`component`.`name`) AS `text`,\n"
        ."  IF(`component`.`ID`=1,'d0d0d0',IF(`systemID`=1,'e0e0ff',IF(`component`.`systemID`=".SYS_ID.",'c0ffc0','ffe0e0'))) AS `color_background`\n"
        ."FROM\n"
        ."  `component`\n"
        ."INNER JOIN `system` ON\n"
        ."  `component`.`systemID` = `system`.`ID`\n"
        ."ORDER BY\n"
        ."  `text`";
    }
    return
       "SELECT\n"
      ."  CONCAT(IF(`systemID`=1,IF(`component`.`ID`=1,' ','* '),' '),`component`.`name`) AS `text`,\n"
      ."  `component`.`ID` AS `value`,\n"
      ."  IF(`ID`=1,'d0d0d0',IF(`systemID`=1,'e0e0ff','c0ffc0')) AS `color_background`\n"
      ."FROM\n"
      ."  `component`\n"
      ."WHERE\n"
      ."  `systemID` IN(1,".SYS_ID.")\n"
      ."ORDER BY\n"
      ."  `text`";

  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_COMPONENT;
  }
}
?>