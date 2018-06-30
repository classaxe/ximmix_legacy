<?php
define('VERSION_MAIL_IDENTITY','1.0.6');
/*
Version History:
  1.0.7 (2011-06-29)
    1) Renamed class from Email_Identity to Mail_Identity
  1.0.6 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.5 (2010-06-09)
    1) Change to Email_Identity::get_selector_SQL() now `PEmail` field is `email`
  1.0.4 (2010-02-17)
    1) Added edit parameters to allow report copy with rename
  1.0.3 (2009-12-09)
    1) Tweak to Email_Identity::get_selector_SQL() to correctly use SYS_ID
  1.0.2 (2009-12-06)
    1) Email_Identity::get_selector_SQL() now extended to include '(None)'
  1.0.1 (2009-12-01)
    1) Added Email_Identity::get_selector_SQL()
  1.0.0 (2009-07-02)
    Initial release
*/
class Mail_Identity extends Record {

  function __construct($ID="") {
    parent::__construct("mailidentity",$ID);
    $this->_set_name_field('name');
    $this->_set_object_name('Mail Identit');
    $this->set_plural_append('y','ies');
    $this->set_edit_params(
      array(
        'report' =>                 'mailidentity',
        'report_rename' =>          true,
        'report_rename_label' =>    'name'
      )
    );
  }

  function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  static function get_selector_SQL(){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN) {
      return
         "SELECT\n"
        ."  1 as `seq`,\n"
        ."  '' AS `value`,\n"
        ."  '(None)' as `text`,\n"
        ."  'd0d0d0' as `color_background`\n"
        ."UNION SELECT\n"
        ."  2,\n"
        ."  `mailidentity`.`ID`,\n"
        ."  CONCAT(\n"
        ."    UPPER(`system`.`textEnglish`),' | ',`mailidentity`.`name`,\n"
        ."    IF (`mailidentity`.`email`!='',CONCAT(' [',`mailidentity`.`email`,']'),'')\n"
        ."  ),\n"
        ."  IF(`systemID`=1,'e0e0e0',IF(`systemID`=".SYS_ID.",'c0ffc0','ffe0e0'))\n"
        ."FROM\n"
        ."  `mailidentity`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `mailidentity`.`systemID`\n"
        ."ORDER BY\n"
        ."  `seq`, `text`";
    }
    return
       "SELECT\n"
      ."  1 as `seq`,\n"
      ."  '' AS `value`,\n"
      ."  '(None)' as `text`,\n"
      ."  'd0d0d0' as `color_background`\n"
      ."UNION SELECT\n"
      ."  2,\n"
      ."  `mailidentity`.`ID`,\n"
      ."  CONCAT(\n"
      ."    IF(`mailidentity`.`systemID`=1,'* ',''),\n"
      ."    `mailidentity`.`name`,\n"
      ."    IF(`mailidentity`.`email`!='',CONCAT(' [',`mailidentity`.`email`,']'),'')\n"
      ."  ),\n"
      ."  'c0ffc0'\n"
      ."FROM\n"
      ."  `mailidentity`\n"
      ."WHERE\n"
      ."  `mailidentity`.`systemID`=".SYS_ID."\n"
      ."ORDER BY\n"
      ."  `seq`,`text`";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_MAIL_IDENTITY;
  }
}
?>