<?php
define('VERSION_MAIL_TEMPLATE','1.0.13');
/*
Version History:
  1.0.13 (2012-05-25)
    1) Mail_Template::send_email() now sets NGreetingName
  1.0.12 (2011-06-29)
    1) Renamed class from Email_Template to Mail_Template
  1.0.11 (2011-04-04)
    1) Email_Template::fix_subject_bodytext_and_path() no longer touches subject -
       was mangling french characters in subject line

  (Older version history in class.mail_template.txt)
*/
class Mail_Template extends Record {

  function __construct($ID="") {
    parent::__construct("mailtemplate",$ID);
    $this->_set_object_name("Email Template");
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new Template Name'
      )
    );
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function fix_subject_bodytext_and_path(){
    $this->load();
    $Obj_System =     new System($this->record['systemID']);
    $site_URL =       trim($Obj_System->get_field('URL'),'/').'/';
    $body_html =      absolute_path($this->record['body_html'],$site_URL);
    $Obj_HTML2Text =  new html2text($body_html);
    $body_text =
       "Email from ".$Obj_System->get_field('TextEnglish')
      ."\n".trim($Obj_HTML2Text->get_text(),"\n\t ");
    $data =
      array(
        'body_html' =>    addslashes($body_html),
        'body_text' =>    addslashes($body_text)
      );
    $this->update($data);
  }

  public static function get_selector_SQL($stationary=0){
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
        ."  `mailtemplate`.`ID` AS `value`,\n"
        ."  CONCAT(\n"
        ."    UPPER(`system`.`textEnglish`),' | ',\n"
        ."    `mailtemplate`.`name`\n"
        ."  ) AS `text`,\n"
        ."  IF(`systemID`=1,'e0e0e0',IF(`systemID`=".SYS_ID.",'c0ffc0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `mailtemplate`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `mailtemplate`.`systemID`\n"
        ."WHERE\n"
        ."  `mailtemplate`.`stationery`=".$stationary."\n"
        ."ORDER BY\n"
        ."  `seq`,`text`";
    }
    return
       "SELECT\n"
      ."  1 as `seq`,\n"
      ."  '' AS `value`,\n"
      ."  '(None)' as `text`,\n"
      ."  'd0d0d0' as `color_background`\n"
      ."UNION SELECT\n"
      ."  2,\n"
      ."  `mailtemplate`.`ID` AS `value`,\n"
      ."  CONCAT(IF(`mailtemplate`.`systemID`=1,'* ',''),`mailtemplate`.`name`) AS `text`,\n"
      ."  IF(`systemID`=1,'e0e0e0','c0ffc0') AS `color_background`\n"
      ."FROM\n"
      ."  `mailtemplate`\n"
      ."WHERE\n"
      ."  `mailtemplate`.`systemID` IN(1,".SYS_ID.") AND\n"
      ."  `mailtemplate`.`stationery`=".$stationary."\n"
      ."ORDER BY\n"
      ."  `seq`,`text`";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public static function on_action_fix_subject_bodytext_and_paths(){
    global $action_parameters;
    $type =     $action_parameters['triggerObject'];
    $ID =       $action_parameters['triggerID'];
    $ID_arr =   explode(',',$ID);
    foreach($ID_arr as $ID){
      $Obj_Mail_Template =  new Mail_Template($ID);
      $Obj_Mail_Template->fix_subject_bodytext_and_path();
    }
  }

  function send_email($personID){
    global $system_vars;
    $this->load();
    get_mailsender_to_component_results();      // Use system default mail sender details
    component_result_set('system_title',$system_vars['textEnglish']);
    component_result_set('system_URL',trim($system_vars['URL'],'/').'/');
    $Obj_Person = new Person($personID);
    $person =     $Obj_Person->get_record();
    component_result_set('personID',$personID);
    component_result_set('WCompany',$person['WCompany']);
    component_result_set('NName',$person['NFirst']." ".$person['NLast']);
    component_result_set('NGreetingName',$person['NGreetingName']);
    component_result_set('PEmail',$person['PEmail']);
    component_result_set('PUsername',$person['PUsername']);
    if ($this->record['set_random_password']){
      $Obj_Person->set_random_password();
    }
    $data =                     array();
    $data['PEmail'] =           component_result('PEmail');
    $data['NGreetingName'] =    component_result('NGreetingName');
    $data['NName'] =            component_result('NName');
    $data['subject'] =          $this->record['subject'];
    $data['html'] =             $this->record['body_html'];
    $data['text'] =             $this->record['body_text'];
    $data['style'] =            $this->record['style'];
//    y($data);die;
    $result = mailto($data);
    return $result;
  }

  public function get_version(){
    return VERSION_MAIL_TEMPLATE;
  }
}
?>