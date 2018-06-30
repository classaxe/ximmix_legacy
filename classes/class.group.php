<?php
define('VERSION_GROUP','1.0.29');
/*
Version History:
  1.0.29 (2014-06-22)
    1) Group::member_assign() now includes support for permEMAILOPTIN and optional parameter
       $email_subscription_log

  (Older version history in class.group.txt)
*/
class Group extends Record{

  public function __construct($ID="") {
    parent::__construct("groups",$ID);
    $this->_set_object_name('Group');
    $this->_set_message_associated('and Group Membership records have');
    $this->set_edit_params(
      array(
        'report' =>                 'groups',
        'report_rename' =>          true,
        'report_rename_label' =>    'name'
      )
    );
  }

  function copy($new_name=false,$new_systemID=false,$new_date=true) {
    $newID =    parent::copy($new_name,$new_systemID,$new_date);
    $members =  $this->get_members();
    $Obj =      new Group_Member;
    foreach ($members as $data) {
      unset($data['ID']);
      unset($data['archive']);
      unset($data['archiveID']);
      if ($new_date){
        unset($data['history_created_by']);
        unset($data['history_created_date']);
        unset($data['history_created_IP']);
        unset($data['history_modified_by']);
        unset($data['history_modified_date']);
        unset($data['history_modified_IP']);
      }
      if ($new_systemID) {
        $data['systemID'] = $new_systemID;
      }
      $data['groupID'] = $newID;
      $Obj->insert($data);
    }
    return $newID;
  }

  function delete() {
    $Obj = new Group_Assign;
    $Obj->delete_for_group($this->_get_ID());
    $this->delete_members();
    parent::delete();
    return true;
  }

  function delete_members() {
    $members = $this->get_members();
    $sql =
       "DELETE FROM\n"
      ."  `group_members`\n"
      ."WHERE\n"
      ."  `groupID` IN (".$this->_get_ID().")";
    $this->do_sql_query($sql);
    $Obj_Person = new Person;
    foreach ($members as $member){
      $Obj_Person->_set_ID($member['personID']);
      $Obj_Person->set_groups_list_description(true);
    }
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with membership records";
    $extra_delete = "DELETE FROM `group_members`          WHERE `groupID` IN (".$targetID.");\n";
    $Obj = new Backup;
    $extra_select = $Obj->db_export_sql_query("`group_members`         ","SELECT * FROM `group_members` WHERE `groupID` IN (".$targetID.") ORDER BY `groupID`",$show_fields)."\n";
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_names_for_IDs(){
    if (!$this->_get_ID()){
      return array();
    }
    $sql =
       "SELECT\n"
      ."  `name`\n"
      ."FROM\n"
      ."  `groups`\n"
      ."WHERE\n"
      ."  `ID` IN(".$this->_get_ID().")\n"
      ."ORDER BY\n"
      ."  `name`";
    $results = $this->get_records_for_sql($sql);
    $out = array();
    foreach ($results as $row){
      $out[] = $row['name'];
    }
    return $out;
  }

  function get_email_recipients(){
    $sql =
       "SELECT\n"
      ."  `person`.`ID`,\n"
      ."  `person`.`NGreetingName`,\n"
      ."  CONCAT(`NFirst`,IF(`NMiddle`!='',CONCAT(' ',`NMiddle`),''),IF(`NLast`!='',CONCAT(' ',`NLast`),'')) AS `NName`,\n"
      ."  `person`.`NTitle`,\n"
      ."  `person`.`PEmail`,\n"
      ."  `person`.`PUsername`,\n"
      ."  `person`.`systemID`\n"
      ."FROM\n"
      ."  `group_members`\n"
      ."INNER JOIN `person` ON\n"
      ."  `group_members`.`personID` = `person`.`ID`\n"
      ."WHERE\n"
      ."  `person`.`PEmail` != '' AND\n"
      ."  `group_members`.`groupID` IN(".$this->_get_ID().") AND\n"
      ."  `group_members`.`permEMAILRECIPIENT` = 1";
    return $this->get_records_for_sql($sql);
  }

  function get_email_recipients_count(){
    $sql =
       "SELECT\n"
      ."  count(*)\n"
      ."FROM\n"
      ."  `group_members`\n"
      ."INNER JOIN `person` ON\n"
      ."  `group_members`.`personID` = `person`.`ID`\n"
      ."WHERE\n"
      ."  `person`.`PEmail` != '' AND\n"
      ."  `group_members`.`groupID` IN(".$this->_get_ID().") AND\n"
      ."  `group_members`.`permEMAILRECIPIENT` = 1";
    return $this->get_field_for_sql($sql);
  }

  function get_members() {
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `group_members`\n"
      ."WHERE\n"
      ."  `groupID` IN (".$this->_get_ID().")";
//    z($sql);
    return $this->get_records_for_sql($sql);
  }

  function get_perms_array_from_csv($csv) {
    $out = array();
    $value_arr = explode(",",$csv);
    foreach ($value_arr as $value) {
      $value_arr =  explode("=",$value);
      $perm =     trim($value_arr[0]);
      $setting =  trim($value_arr[1]);
      switch($perm) {
        case "permEMAILOPTOUT":
        case "permEMAILRECIPIENT":
        case "permVIEWER":
        case "permEDITOR":
          $out[$perm] = (int)$setting;
        break;
      }
    }
    return $out;
  }

  function get_selector_groups_SQL($showAddNew=false,$personID=false) {
    global $system_vars;
    $out = '';
    $UnionOrder = 0;
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $out.=
       "SELECT\n"
      ."  ".$UnionOrder++." AS `UnionOrder`,\n"
      ."  '' AS `value`,\n"
      ."  '' AS `text_val`,\n"
      ."  'Please select a group below: &nbsp; [n=members]' AS `text`,\n"
      ."  'fff0f0' AS `color_background`,\n"
      ."  '000000' AS `color_text`,\n"
      ."  0 AS `isHeader`\n";
    if ($isMASTERADMIN) {
      $sql =
         "SELECT\n"
        ."  `system`.`ID`,\n"
        ."  `system`.`textEnglish`\n"
        ."FROM\n"
        ."  `system`\n"
        ."INNER JOIN `groups` ON\n"
        ."  `system`.`ID` = `groups`.`systemID`\n"
        ."GROUP BY\n"
        ."  `system`.`ID`\n"
        ."ORDER BY\n"
        ."  `system`.`textEnglish`";
      $records = $this->get_records_for_sql($sql);
      $bgcolor = '';
      foreach ($records as $record) {
        $bgcolor = ($bgcolor =='f8fff8' ? 'e8ffe8' : 'f8fff8');
        $out.=
           "UNION\n"
          ."SELECT\n"
          ."  ".$UnionOrder++.",\n"
          ."  '',\n"
          ."  '',\n"
          ."  \"".$record['textEnglish']."\",\n"
          ."  '$bgcolor',\n"
          ."  '000000',\n"
          ."  1\n"
          ."UNION\n"
          ."SELECT\n"
          ."  ".$UnionOrder++.",\n"
          ."  `groups`.`ID`,\n"
          ."    `name`,\n"
          ."  CONCAT(\n"
          .($personID!==false ?
             "IF($personID IN (SELECT `group_members`.`personID` FROM `group_members` WHERE `group_members`.`groupID`=`groups`.`ID`),'* ','&nbsp; &nbsp;'),\n"
           : "")
          ."    `name`,\n"
          ."    '&nbsp; [',(SELECT COUNT(`group_members`.`ID`) FROM `group_members` WHERE `groupID` = `groups`.`ID`),']'\n"
          ."  ),\n"
          ."  '$bgcolor',\n"
          ."  '000000',\n"
          ."  0\n"
          ."FROM\n"
          ."  `groups`\n"
          ."WHERE\n  `groups`.`systemID`=".$record['ID']."\n";
        }
    }
    else {
      $out.=
        ($personID===false ?
           "UNION\n"
          ."SELECT\n"
          ."  ".$UnionOrder++.",\n"
          ."  '',\n"
          ."  '',\n"
          ."  \"Existing Groups\",\n"
          ."  'e8ffe8',\n"
          ."  '000000',\n"
          ."  1\n"
         : "")
        ."UNION\n"
        ."SELECT\n"
        ."  ".$UnionOrder++.",\n"
        ."  `ID`,\n"
        ."  `name`,\n"
        ."  CONCAT(\n"
        .($personID!==false ?
           "IF($personID IN (SELECT `group_members`.`personID` FROM `group_members` WHERE `group_members`.`groupID`=`groups`.`ID`),'* ','&nbsp; &nbsp;'),\n"
         : "")
        ."    `name`,\n"
        ."    '&nbsp; [',(SELECT COUNT(`group_members`.`ID`) FROM `group_members` WHERE `groupID` = `groups`.`ID`),']'\n"
        ."  ),\n"
        ."  'e8ffe8',\n"
        ."  '000000',\n"
        ."  0\n"
        ."FROM\n"
        ."  `groups`\n"
        .($isMASTERADMIN ? "" : "WHERE\n  `groups`.`systemID` IN(1,".SYS_ID.")\n");
    }
    if ($showAddNew) {
      $out.=
         "UNION\n"
        ."SELECT\n"
        ."  ".$UnionOrder++.",\n"
        ."  '',\n"
        ."  '',\n"
        ."  \"New Group for ".$system_vars['textEnglish'].":\",\n"
        ."  'f0f0ff',\n"
        ."  '000080',\n"
        ."  1\n"
        ."UNION\n"
        ."SELECT\n"
        ."  ".$UnionOrder++." AS `UnionOrder`,\n"
        ."  '--' AS `value`,\n"
        ."  '',\n"
        ."  'Enter name then press [TAB] --&gt;' AS `text`,\n"
        ."  'f0f0ff',\n"
        ."  '000080',\n"
        ."  0\n";
    }
    $out.=
       "ORDER BY\n"
      ."  `UnionOrder`,`text_val`";
    return $out;
  }

  static function get_selector_email_groups_sql(){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN){
      return
         "SELECT\n"
        ."  1 as `seq`,\n"
        ."  '' AS `value`,\n"
        ."  '(None)' as `text`,\n"
        ."  'd0d0d0' as `color_background`\n"
        ."UNION SELECT\n"
        ."  2,\n"
        ."  `groups`.`ID` AS `value`,\n"
        ."  CONCAT(\n"
        ."    UPPER(`system`.`textEnglish`),' | ',\n"
        ."    `groups`.`name`,' [Recipients: ',\n"
        ."    (SELECT COUNT(*) FROM `group_members` INNER JOIN `person` ON `group_members`.`personID` = `person`.`ID` WHERE `groupID` = `groups`.`ID` AND `permEMAILRECIPIENT` = 1 AND `person`.`PEmail` != ''),\n"
        ."    ']'\n"
        ."  ) AS `text`,\n"
        ."  IF(`groups`.`systemID`=1,'e0e0e0',IF(`groups`.`systemID`=".SYS_ID.",'c0ffc0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `groups`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `groups`.`systemID`\n"
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
      ."  `groups`.`ID` AS `value`,\n"
      ."  CONCAT(\n"
      ."    `groups`.`name`,' [Recipients: ',\n"
      ."    (SELECT COUNT(*) FROM `group_members` INNER JOIN `person` ON `group_members`.`personID` = `person`.`ID` WHERE `groupID` = `groups`.`ID` AND `permEMAILRECIPIENT` = 1 AND `person`.`PEmail` != ''),\n"
      ."    ']'\n"
      ."  ) AS `text`,\n"
      ."  'c0ffc0' AS `color_background`\n"
      ."FROM\n"
      ."  `groups`\n"
      ."WHERE\n"
      ."  `groups`.`systemID` = ".SYS_ID."\n"
      ."ORDER BY\n"
      ."  `seq`,`text`";

  }

  function get_selector_sql() {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN){
      return
         "SELECT\n"
        ."  CONCAT(\n"
        ."    IF(`groups`.`systemID` = 1,\n"
        ."    '* ',\n"
        ."    CONCAT(\n"
        ."      UPPER(`system`.`textEnglish`),\n"
        ."      ' | ')\n"
        ."    ),\n"
        ."    `groups`.`name`) AS `text`,\n"
        ."  `groups`.`ID` AS `value`\n"
        ."FROM\n"
        ."  `groups`\n"
        ."INNER JOIN `system` ON\n"
        ."  `groups`.`systemID` = `system`.`ID`\n"
        ."ORDER BY\n"
        ."  `text`";
    }
    return
       "SELECT\n"
      ."  `groups`.`name` AS `text`,\n"
      ."  `groups`.`ID` AS `value`\n"
      ."FROM\n"
      ."  `groups`\n"
      ."WHERE\n"
      ."  `groups`.`systemID` = ".SYS_ID."\n"
      ."ORDER BY\n"
      ."  `text`";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function handle_report_delete(&$msg) {
    $members = $this->member_count();
    $this->delete();
    $msg = status_message(0,true,$this->_get_object_name(),($members ? 'and '.($members==1 ? 'one associated member have' : $members.' associated members have'): ""),'been deleted.',$this->_get_ID());
  }

  public function on_action_set_group_membership_description($reveal_modification=false){
    $ID_arr = explode(',',str_replace(' ','',$this->_get_ID()));
    $Obj_Person = new Person;
    foreach ($ID_arr as $ID){
      $this->_set_ID($ID);
      $members = $this->get_members();
      foreach ($members as $member){
        $Obj_Person->_set_ID($member['personID']);
        $Obj_Person->set_groups_list_description($reveal_modification);
      }
    }
  }

  function manage($admin=0) {
    global $filterField,$filterValue,$filterExact;
    global $selectID;
    $group_name = "";
    if ($selectID!="") {
      $Obj = new Group($selectID);
      if ($Obj->exists()){
        $group_name = $Obj->get_name();
      }
    }
    $out =
       ($selectID == '' ?
          "You may click a Group Members '<span style='color:#808000'><b>View...</b></span>' link in the report below to <span style='background-color: #ffffa0;border:1px solid #888'>&nbsp;select&nbsp;</span> it.<br />\n"
         ."Selecting a Group will allow you to add, modify or remove its members."
       :
          "Group <b><a href=\"#group_members\" style='color:#000;text-decoration:none;' title=\"Click to View Members\">".$group_name."</a></b> has been selected."
       )
      .draw_auto_report('groups',1);
    if ($selectID!="") {
      $Obj = new Group($selectID);
      if ($Obj->exists()){
        $out.=
           "<a name='group_members' id='group_members'></a>"
          ."<h3 class='admin_heading' style='margin:0'>Members for Group \"".$Obj->get_name()."\"</h3>\n"
          .draw_auto_report('group_members',1);
      }
    }
    return $out;
  }

  function member_assign($personID, $permArr, $email_subscription_log='') {
    if ($personID=="") {
      do_log(3,__CLASS__."::".__FUNCTION__."():",'',"personID missing");
      return false;
    }
    if (!is_array($permArr)) {
      do_log(3,__CLASS__."::".__FUNCTION__."():",'',"permArr missing");
      return false;
    }
    $ObjGroupMember = new Group_Member;
    foreach ($permArr as $key=>$value) {
      if (!in_array($key,$ObjGroupMember->permArr)) {
        do_log(3,__CLASS__."::".__FUNCTION__."():",'',"permArr invalid - ".$key);
      }
    }
    $fieldsArr =
      array_merge(
        array(
          "systemID"=>SYS_ID,
          "groupID"=>$this->_get_ID(),
          "personID"=>$personID
        ),
        $permArr
      );
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `permEMAILOPTIN`,\n"
      ."  `permEMAILOPTOUT`,\n"
      ."  `permEMAILRECIPIENT`,\n"
      ."  `permVIEWER`,\n"
      ."  `permEDITOR`\n"
      ."FROM\n"
      ."  `group_members`\n"
      ."WHERE\n"
      ."  `groupID` = ".$this->_get_ID()." AND\n"
      ."  `personID` = ".$personID;
    $record = $this->get_record_for_sql($sql);
    if ($record===false) {
      if ($email_subscription_log!==''){
        $new_log =
            get_timestamp().' | '
           .pad((isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "No remote IP"),16).' | '
           .pad(get_userPUsername(),25).' | '
           .$email_subscription_log;
        $fieldsArr['email_subscription_log'] = addslashes($new_log);
      }
      $result = $ObjGroupMember->insert($fieldsArr);
      return 1;
    }
    if (
      ($email_subscription_log==='') &&
      (isset($permArr['permEMAILOPTIN'])     ? $permArr['permEMAILOPTIN']==$record['permEMAILOPTIN'] : true) &&
      (isset($permArr['permEMAILOPTOUT'])    ? $permArr['permEMAILOPTOUT']==$record['permEMAILOPTOUT'] : true) &&
      (isset($permArr['permEMAILRECIPIENT']) ? $permArr['permEMAILRECIPIENT']==$record['permEMAILRECIPIENT'] : true) &&
      (isset($permArr['permVIEWER'])         ? $permArr['permVIEWER']==$record['permVIEWER'] : true) &&
      (isset($permArr['permEDITOR'])         ? $permArr['permEDITOR']==$record['permEDITOR'] : true)
    ){
      return 0;
    }
    $ObjGroupMember->ID = $record['ID'];
    if ($email_subscription_log!==''){
      $old_log =    $ObjGroupMember->get_field('email_subscription_log');
      $new_log =
          get_timestamp().' | '
         .pad((isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "No remote IP"),16).' | '
         .pad(get_userPUsername(),25).' | '
         .$email_subscription_log
         .($old_log ? "\r\n".$old_log : "");
      $fieldsArr['email_subscription_log'] = addslashes($new_log);
    }
    $result = $ObjGroupMember->update($fieldsArr);
    return $result;
  }

  function member_count() {
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `group_members`\n"
      ."WHERE\n"
      ."  `groupID` IN(".$this->_get_ID().")";
    return $this->get_field_for_sql($sql);
  }

  function member_perms($personID){
    if ($personID=="") {
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `systemID`,\n"
      ."  `permEMAILOPTOUT`,\n"
      ."  `permEMAILRECIPIENT`,\n"
      ."  `permEDITOR`,\n"
      ."  `permVIEWER`\n"
      ."FROM\n"
      ."  `group_members`\n"
      ."WHERE\n"
      ."  `groupID` IN (".$this->_get_ID().") AND\n"
      ."  `personID` = ".$personID;
//      z($sql);
    return $this->get_record_for_sql($sql);
  }

  function member_unassign($personID) {
    if ($personID=="") {
      do_log(3,__CLASS__."::".__FUNCTION__."():",'',"personID missing");
      return false;
    }
    $sql =
       "DELETE\n"
      ."FROM\n"
      ."  `group_members`\n"
      ."WHERE\n"
      ."  `groupID` = \"".$this->_get_ID()."\" AND\n"
      ."  `personID` = \"$personID\"";
    $this->do_sql_query($sql);
    return Record::get_affected_rows();
  }

  function test_person_access_for_named_group($group_name,$personID) {
    $ObjGroup =     new Group();
    $ObjGroup->_set_ID($ObjGroup->get_ID_by_name($group_name));
    $perms_arr =      $ObjGroup->member_perms($personID);
    return
      ($perms_arr!==false) &&
      ($perms_arr['permVIEWER']==1 ||
       $perms_arr['permEDITOR']==1);
  }

  public function get_version(){
    return VERSION_GROUP;
  }
}
?>