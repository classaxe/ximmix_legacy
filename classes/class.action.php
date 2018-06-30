<?php
define('VERSION_ACTION','1.0.21');
/*
Version History:
  1.0.21 (2014-01-06)
    1) Action::_execute_event_register() now uses User class to get inviter

  (Older version history in class.action.txt)
*/
class Action extends Record {

  function __construct($ID="") {
    parent::__construct("action",$ID);
    $this->_set_object_name("Action");
  }

  function count_for_source($destinationOperation,$destinationID,$sourceID,$sourceTrigger,$sourceType) {
    $sql =
       "SELECT\n"
      ."  COUNT(*) AS `count`\n"
      ."FROM\n"
      ."  `action`\n"
      ."WHERE\n"
      ."  `destinationOperation` = \"$destinationOperation\" AND\n"
      ."  `destinationID` = \"$destinationID\" AND\n"
      ."  `sourceID` = \"$sourceID\" AND\n"
      ."  `sourceTrigger` = \"$sourceTrigger\" AND\n"
      ."  `sourceType` = \"$sourceType\"";
    return $this->get_field_for_sql($sql);
  }

  function copy($newSourceID=false,$newSourceType=false) {
    if ($this->_get_ID()=="") {
      return false;
    }
    $newID = parent::copy();
    $data = array();
    if ($newSourceID)   { $data['sourceID'] = $newSourceID; }
    if ($newSourceType) { $data['sourceType'] = $newSourceType; }
    if (count($data)){
      $this->_set_ID($newID);
      $this->update($data,false,false);
    }
    return $newID;
  }

  function draw_combo_action_operation($destinationOperation,$destinationID,$destinationValue,$formFieldSpecial,$formContentWidth,$report_name) {
    if (!$formFieldSpecial){
      $type_arr = array();
      $filter = '';
    }
    else{
      $type_arr =   explode(',',str_replace(' ','',$formFieldSpecial));
      $filter_arr = array();
      foreach($type_arr as $type){
        $filter_arr[] = "'".trim($type)."'";
      }
      $filter = "  `value` IN(".implode(',',$filter_arr).")";
    }
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $Obj_Listtype =     new Listtype;
    $filter =           ($report_name!='actions_for_product' ? "  `value`!='event_register'" : "");
    $form_op_sql =      $Obj_Listtype->get_sql_options('lst_action_operation','`text`', $filter);
    if ($isMASTERADMIN) {
      $sql_components =
         "SELECT\n"
        ."  CONCAT('[C] [',IF(`systemID`=1,'*',`system`.`textEnglish`),'] ',`component`.`name`) AS `text`,\n"
        ."  `component`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe8e8')) AS `color_background`\n"
        ."FROM\n"
        ."  `component`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `component`.`systemID`\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_events =
         "SELECT\n"
        ."  CONCAT('[E] [',IF(`systemID`=1,'*',`system`.`textEnglish`),'] ',`postings`.`effective_date_start`,': ',`postings`.`title`) AS `text`,\n"
        ."  `postings`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `postings`.`systemID`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'event'\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_groups =
         "SELECT\n"
        ."  CONCAT('[G] [',IF(`systemID`=1,'*',`system`.`textEnglish`),'] ',`groups`.`name`) AS `text`,\n"
        ."  `groups`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `groups`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `groups`.`systemID`\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_mailtemplates =
         "SELECT\n"
        ."  CONCAT('[M] [',IF(`systemID`=1,'*',`system`.`textEnglish`),'] ',`name`) AS `text`,\n"
        ."  `mailtemplate`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `mailtemplate`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `mailtemplate`.`systemID`\n"
        ."ORDER BY\n"
        ."  `text`";
    }
    else {
      $sql_components =
         "SELECT\n"
        ."  CONCAT('[C] ',IF(`systemID`=1,'* ',''),`name`) AS `text`,\n"
        ."  `ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `component`\n"
        ."WHERE\n"
        ."  `systemID` = ".SYS_ID." OR `systemID` = 1\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_events =
         "SELECT\n"
        ."  CONCAT('[E] ',IF(`systemID`=1,'* ',''),`effective_date_start`,': ',`title`) AS `text`,\n"
        ."  `ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'event' AND\n"
        ."  `systemID` = ".SYS_ID."\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_groups =
         "SELECT\n"
        ."  CONCAT('[G] ',IF(`systemID`=1,'* ',''),`name`) AS `text`,\n"
        ."  `ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `groups`\n"
        ."WHERE\n"
        ."  `systemID` = ".SYS_ID."\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_mailtemplates =
         "SELECT\n"
        ."  CONCAT('[M] ',IF(`systemID`=1,'* ',''),`name`) AS `text`,\n"
        ."  `ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `mailtemplate`\n"
        ."WHERE\n"
        ."  `systemID` = ".SYS_ID." OR `systemID` = 1\n"
        ."ORDER BY\n"
        ."  `text`";
    }
    $html =
       "<table class='minimal'>\n"
      ."  <tr>\n"
      ."    <td style='width:164px;'>".Report_Column::draw_label('Operation','Determines the kind of activity this action performs.')."</td>\n"
      ."    <td>"
      .draw_form_field('destinationOperation',$destinationOperation,"selector",$formContentWidth,$form_op_sql,0, "onchange=\"set_action_operation_options(geid('destinationOperation'),geid('destinationValue'));\"")
      .draw_form_field('temp_id',$destinationID,'hidden')
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>".Report_Column::draw_label('Target','Determines which item relating to the operation will be used.')."</td>\n"
      ."    <td>"
      ."<select id='destinationID' name='destinationID' class='formField' style='width:".($formContentWidth+4)."px;'>\n"
      ."  <option value='".$destinationID."'>Loading available options...</option>\n"
      ."</select></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>".Report_Column::draw_label('Value','Provides additional parameters to a component for execution or permissions for group membership.')."</td>\n"
      ."    <td>".draw_form_field('destinationValue',$destinationValue,'text',$formContentWidth)."</td>\n"
      ."  </tr>\n"
      ."</table>";
    $js =
       "\n"
      ."// Action Operation selector entries:\n"
      ."  var destID = {};\n";
    if (count($type_arr)>1){
      $js.=
         "  destID.none = [];\n"
        ."  destID.none[0] = ['Please choose an operation type','none','c0c0c0'];\n";
    }
    if (count($type_arr)==0 || in_array('component_execute',$type_arr)){
      $result = $this->get_records_for_sql($sql_components);
        $js.=
           "  destID.component_execute = [];\n"
          ."  destID.component_execute[0] = ['COMPONENTS (Please Select - standard entries are prefixed with *) ',''];\n";
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $js.=
           "  destID.component_execute[".($i+1)."] = "
          ."[\"".str_replace("\"","'",$row['text'])."\",".$row['value'].",\"#".$row['color_background']."\"];\n";
      }
    }
    if (count($type_arr)==0 || in_array('event_register',$type_arr)){
      $js.=
         "  destID.event_register = [];\n"
        ."  destID.event_register[0] = ['EVENTS',''];\n";
      $result = $this->get_records_for_sql($sql_events);
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $js.=
           "  destID.event_register[".($i+1)."] = "
          ."[\"".str_replace("\"","'",$row['text'])."\",".$row['value'].",\"#".$row['color_background']."\"];\n";
      }
    }
    if (count($type_arr)==0 || in_array('group_membership_set',$type_arr)){
      $js.=
         "  destID.group_membership_set = [];\n"
        ."  destID.group_membership_set[0] = ['GROUPS',''];\n";
      $result = $this->get_records_for_sql($sql_groups);
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $js.=
         "  destID.group_membership_set[".($i+1)."] = "
        ."[\"".str_replace("\"","'",$row['text'])."\",".$row['value'].",\"#".$row['color_background']."\"];\n";
      }
    }
    if (count($type_arr)==0 || in_array('mailtemplate_send_email',$type_arr)){
      $js.=
         "  destID.mailtemplate_send_email = [];\n"
        ."  destID.mailtemplate_send_email[0] = ['EMAIL TEMPLATES: (Please Select - standard entries are prefixed with *)',''];\n";
      $result = $this->get_records_for_sql($sql_mailtemplates);
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $js.=
           "  destID.mailtemplate_send_email[".($i+1)."] = "
          ."[\"".str_replace("\"","'",$row['text'])."\",".$row['value'].",\"#".$row['color_background']."\"];\n";
      }
    }
    Page::push_content('javascript',$js);
    Page::push_content('javascript_onload','  set_action_operation_options();');
    return $html;
  }

  function execute($sourceType,$sourceID,$sourceTrigger,$_personID,$triggerType,$triggerObject,$triggerID,$data=array()) {
//    print "executing - $sourceType | $sourceID | $sourceTrigger | $_personID | $triggerType | $triggerID<br /><pre>".print_r($data,1)."</pre>";
    global $personID;
    $records = $this->get_records_matching($sourceType,$sourceID,$sourceTrigger);
    if (!count($records)) {
      return true;
    }
//    y($records);die;
    foreach ($records as $row) {
      $operation =  $row['destinationOperation'];
      $ID =         $row['destinationID'];
      $value =      $row['destinationValue'];
      switch ($operation) {
        case "component_execute":
          $Obj = new Component($ID);
          if (!$Obj->exists()) {
            do_log(3,__CLASS__.'::'.__FUNCTION__.'()','component_execute','No such Component as '.$ID);
            break;
          }
          global $action_parameters;
          $action_parameters = array(
            'destinationValue' => $value,
            'sourceType' =>     $sourceType,
            'sourceID' =>       $sourceID,
            'sourceTrigger' =>  $sourceTrigger,
            '_personID' =>      $_personID,
            'triggerType' =>    $triggerType,
            'triggerObject' =>  $triggerObject,
            'triggerID' =>      $triggerID,
            'data' =>           $data
          );
          $old_personID = $personID;
          $personID = $_personID;
//          print_r($action_parameters); die;
          $Obj->execute();
          $personID = $old_personID;
        break;
        case "event_register":
          $eventID =    $row['destinationID'];
          $multiplier = ($row['destinationValue'] ? $row['destinationValue'] : 1);
          $inviterID =  $_personID;
          $this->_execute_event_register($eventID, $multiplier, $inviterID, $triggerType, $triggerID);
        break;
        case "group_membership_set":
          $Obj = new Group($ID);
          if (!$Obj->exists()) {
            do_log(3,__CLASS__.'::'.__FUNCTION__.'()','group_membership_set','No such Group as '.$ID);
            break;
          }
          $perms_arr = $Obj->get_perms_array_from_csv($value);
          if (strlen($_personID)>0) {
          	$Obj->member_assign($_personID,$perms_arr);
          } else if (array_key_exists('new_personID', $data)) {
          	$Obj->member_assign($data['new_personID'],$perms_arr);
          }
        break;
        case "mailtemplate_send_email":
          $Obj = new Mail_Template($ID);
          if (!$Obj->exists()) {
            do_log(3,__CLASS__.'::'.__FUNCTION__.'()','mailtemplate_send_email','No such Email Template as '.$ID);
            break;
          }
          $Obj->send_email($_personID);
          do_log(1,__CLASS__.'::'.__FUNCTION__.'()','mailtemplate_send_email','Sending Email for '.component_result('PUsername').' using template '.$ID);
        break;
        default:
          die ("Action::execute() Unknown operation $operation");
        break;
      }
    }
    return true;
  }

  private function _execute_event_register($eventID, $multiplier, $inviterID, $triggerType, $triggerID){
    $Obj_Event =        new Event($eventID);
    if (!$Obj_Event->exists()) {
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','event_register','No such Event as '.$eventID);
      return;
    }
    $rowEvent =     $Obj_Event->get_record();
    $Obj_User =     new User($inviterID);
    $row_user =    $Obj_User->get_record();
    $sendEmail =    ($row_user['PEmail']!='' && $rowEvent['no_email']==0);
    $qty =              1;
    $paymentMethod =    "";
    $orderID =          "";
    $order_itemID =     "";
    if ($triggerType=='order_items') {
      $Obj = new OrderItem($triggerID);
      $paymentDetails =     $Obj->get_payment_details();
      $qty =                $paymentDetails['quantity'];
      $paymentCost =        $paymentDetails['cost'];
      $paymentMethod =      $paymentDetails['paymentMethod'];
      $order_itemID =       $paymentDetails['ID'];
      $orderID =            $paymentDetails['orderID'];
    }
    $Obj_RegisterEvent =        new Register_Event;
    $total = $qty * $multiplier;
    $inviterName =
      trim(
        $row_user['NFirst']
        .($row_user['NMiddle'] ? ' '.$row_user['NMiddle'] : '')
        .($row_user['NLast'] ? ' '.$row_user['NLast'] : '')
      );
    for ($i=0 ; $i<$total; $i++){
      if ($total==1){
        $attender_personID =    $row_user['ID'];
        $attender_NFirst =      $row_user['NFirst'];
        $attender_NMiddle =     $row_user['NMiddle'];
        $attender_NLast =       $row_user['NLast'];
        $attender_PUsername =   $row_user['PUsername'];
        $attender_WCompany =    $row_user['WCompany'];
        $attender_PEmail =      $row_user['PEmail'];
        $attender_ATelephone =  $row_user['ATelephone'];
      }
      else {
        $guest_number =         lead_zero($i+1,3);
        $attender_personID =    0;
        $attender_NFirst =      "";
        $attender_NMiddle =     "";
        $attender_NLast =       "#".$guest_number." of ".$total;
        $attender_PUsername =   "";
        $attender_WCompany =    "";
        $attender_PEmail =      "";
        $attender_ATelephone =  "";
      }
      $data =
        array(
          'systemID'=>                  SYS_ID,
          'eventID'=>                   $eventID,
          'attender_personID'=>         $attender_personID,
          'attender_PEmail'=>           $attender_PEmail,
          'attender_ATelephone'=>       $attender_ATelephone,
          'attender_NFirst'=>           $attender_NFirst,
          'attender_NMiddle'=>          $attender_NMiddle,
          'attender_NLast'=>            $attender_NLast,
          'attender_PUsername'=>        $attender_PUsername,
          'attender_WCompany'=>         $attender_WCompany,
          'order_item_cost'=>           $paymentCost,
          'order_payment_method'=>      $paymentMethod,
          'orderID'=>                   $orderID,
          'inviter_personID'=>          $inviterID
        );
      if ($Obj_RegisterEvent->matched($data)) {
        break; // Person is already registered, don't register them a second time
      }
      $regID = $Obj_RegisterEvent->insert($data);
      $Obj_RegisterEvent->assign_booking_number();
      if ($i==1 && $sendEmail) {
        $Obj_RegisterEvent->_set_ID($regID); // Only want to send the mail once
        $Obj_RegisterEvent->send_email();
      }
    }
  }

  function exists_for_source($destinationOperation,$destinationID,$sourceID,$sourceTrigger,$sourceType) {
    return ($this->count_for_source($destinationOperation,$destinationID,$sourceID,$sourceTrigger,$sourceType)>0);
  }

  function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  function get_records_matching($sourceType,$sourceID,$sourceTrigger) {
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `action`\n"
      ."WHERE\n"
      ."  `systemID` IN(1,".SYS_ID.") AND\n"
      ."  `sourceType` = \"$sourceType\" AND\n"
      ."  `sourceID` = ".$sourceID." AND \n"
      ."  `sourceTrigger` = \"$sourceTrigger\"\n"
      ."ORDER BY\n"
      ."  `seq`";
//    z($sql);
    return $this->get_records_for_sql($sql);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip);
  }

  public function on_pre_update(){
    global $_POST, $submode, $msg;
    $destinationOperation =   $_POST['destinationOperation'];
    $destinationID =          $_POST['destinationID'];
    $destinationValue =       $_POST['destinationValue'];
    $sourceID =               $_POST['sourceID'];
    $sourceTrigger =          $_POST['sourceTrigger'];
    $sourceType =             $_POST['sourceType'];
    if ($destinationID=="") {
      $submode =  "";
      switch ($destinationOperation) {
        case "component_execute":
          $msg =    "You should choose a Component for this Action to execute.";
        break;
        case "event_register":
          $msg =    "You should choose an Event for this Action to register.";
        break;
        case "group_membership_set":
          $msg =    "You should choose a Group for this Action to set membership for.";
        break;
        case "mailtemplate_send_email":
          $msg =    "You should choose an Email Template for this Action to use..";
        break;
        case "":
          $msg =    "You must select an operation type to create an Action.";
        break;
      }
    }
  }


  public function get_version(){
    return VERSION_ACTION;
  }
}
?>