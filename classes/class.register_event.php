<?php
define('VERSION_REGISTER_EVENT','1.0.23');
/*
Version History:
  1.0.23 (2014-01-28)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.register_event.txt)
*/
class Register_Event extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, eventID, sequence, attender_attended, attender_PEmail, attender_ATelephone, attender_NFirst, attender_NLast, attender_NMiddle, attender_personID, attender_PUsername, attender_WCompany, orderID, order_itemID, order_item_cost, order_payment_method, inviter_personID, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("registerevent",$ID);
    $this->_set_object_name('Event Registration');
  }

  public function assign_booking_number(){
    if (!$this->_get_ID()){
      return;
    }
    $sql =
       "SELECT\n"
      ."  `postings`.`max_sequence`\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."INNER JOIN `postings` ON\n"
      ."  `postings`.`ID` = `".$this->_get_table_name()."`.`eventID`\n"
      ."WHERE\n"
      ."  `".$this->_get_table_name()."`.`ID` = ".$this->_get_ID();
    $seq = (int)$this->get_field_for_sql($sql);
    $seq++;
    $this->set_field('sequence',$seq);
    $sql =
       "UPDATE\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."INNER JOIN `postings` ON\n"
      ."  `postings`.`ID` = `".$this->_get_table_name()."`.`eventID`\n"
      ."SET\n"
      ."  `postings`.`max_sequence` = ".$seq."\n"
      ."WHERE\n"
      ."  `".$this->_get_table_name()."`.`ID` = ".$this->_get_ID();
    $this->do_sql_query($sql);
  }

  public function draw_ticket(){
    $out = "";
    $ID_arr = explode(',',$this->_get_ID());
    foreach ($ID_arr as $ID) {
      $out.= "<img src=\"".BASE_PATH."img/ticket/".$ID."\" alt=\"Ticket #".$ID."\" />";
    }
    return $out;
  }

  public function draw_ticket_image(){
    $data = $this->get_ticket_data();
    if ($data==false){
      die("Invalid ticket #".$this->_get_ID());
    }
    $xml_doc = $data['layout_xml'];
    $result = Image_Factory::xml_to_image($xml_doc,$data);
    if (isset($result['error'])){
      die($result['error']." - Ticket #".$data['ID']." - layout ID is ".$data['layout_ID']);
    }
  }

  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function get_notification_summary($datetime,$systemID,$base_url){
    $records = $this->get_records_since($datetime,$systemID);
    if (!count($records)){
      return;
    }
    $out =
       "<h2>New ".$this->_get_object_name().$this->plural('1,2')."</h2>"
      ."<table cellpadding='2' cellspacing='0' border='1'>\n"
      ."  <thead>\n"
      ."    <th>Title</th>\n"
      ."    <th>Date</th>\n"
      ."    <th>Inviter</th>\n"
      ."    <th>Attender</th>\n"
      ."    <th class='datetime'>Created</th>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach ($records as $record){
      $Obj_inviter =    new Person($record['inviter_personID']);
      $Obj_inviter->load();
      $inviter =
         $Obj_inviter->record['NFirst']." "
        .$Obj_inviter->record['NMiddle']." "
        .$Obj_inviter->record['NLast']
        .($Obj_inviter->record['PUsername'] ? " (".$Obj_inviter->record['PUsername'].")" : "");
      $inviter_URL =     $base_url.'details/user/'.$Obj_inviter->record['ID'];
      $attender =
         $record['attender_NFirst']." "
        .$record['attender_NMiddle']." "
        .$record['attender_NLast']
        .($record['attender_PUsername'] ? " (".$record['attender_PUsername'].")" : "");
      $Obj_Event = new Event($record['eventID']);
      $Obj_Event->load();
      $event =      $Obj_Event->record['title'];
      $event_URL =  $base_url.$Obj_Event->get_URL($Obj_Event->record);
      $out.=
         "  <tr>\n"
        ."    <td><a target=\"_blank\" href=\"".$event_URL."\">".$Obj_Event->record['title']."</a></td>\n"
        ."    <td>".format_date($Obj_Event->record['effective_date_start'])." ".Event::format_times($Obj_Event->record['effective_time_start'],$Obj_Event->record['effective_time_end'])."</td>\n"
        ."    <td>".($inviter_URL ? "<a target=\"_blank\" href=\"".$inviter_URL."\">".$inviter."</a>" : $inviter)."</td>\n"
        ."    <td>".$attender."</td>\n"
        ."    <td class='datetime'>".$record['history_created_date']."</td>\n"
        ."  </tr>\n";
    }
    $out.=
       "  </tbody>\n"
      ."</table>\n";
    return $out;
  }

  public function get_ticket_data(){
    if (!$this->_get_ID()){
      return false;
    }
    global $system_vars;
    $sql =
       "SELECT\n"
      ."  IF(`r`.`orderID`=0,'(None)',`r`.`orderID`) `orderID`,\n"
      ."  CONCAT('".$system_vars['defaultCurrencySymbol']."',`r`.`order_item_cost`) `item_cost`,\n"
      ."  COALESCE(\n"
      ."    CONCAT(\n"
      ."      `r`.`attender_NFirst`,\n"
      ."      IF(`r`.`attender_NFirst`!='',' ',''),\n"
      ."      `r`.`attender_NMiddle`,\n"
      ."      IF(`r`.`attender_NMiddle`!='',' ',''),\n"
      ."      `r`.`attender_NLast`\n"
      ."    ),\n"
      ."    '') AS `attender`,\n"
      ."  COALESCE(\n"
      ."    CONCAT(\n"
      ."      `i`.`NFirst`,\n"
      ."      IF(\n"
      ."        `i`.`NMiddle`!='',\n"
      ."        CONCAT(' ',`i`.`NMiddle`),\n"
      ."        ''),\n"
      ."      IF(\n"
      ."        `i`.`NLast`!='',\n"
      ."        CONCAT(' ',`i`.`NLast`),\n"
      ."        '')\n"
      ."      ),\n"
      ."    '') AS `inviter_name`,\n"
      ."  COALESCE(`i`.`PUsername`,'') AS `inviter_username`,\n"
      ."  COALESCE(`i`.`WCompany`,'') AS `inviter_company`,\n"
      ."  `e`.`title`,\n"
      ."  `e`.`content`,\n"
      ."  `e`.`effective_date_start` `date`,\n"
      ."  `e`.`effective_date_end`,\n"
      ."  `e`.`effective_time_start`,\n"
      ."  `e`.`effective_time_end`,\n"
      ."  `e`.`location`,\n"
      ."  `r`.*,\n"
      ."  `t`.`ID` AS `layout_ID`,\n"
      ."  `t`.`name` AS `layout_name`,\n"
      ."  `t`.`content` AS `layout_xml`\n"
      ."FROM\n"
      ."  `registerevent` `r`\n"
      ."LEFT JOIN `postings` `e` ON\n"
      ."  `r`.`eventID` = `e`.`ID`\n"
      ."LEFT JOIN `postings` `t` ON\n"
      ."  `t`.`ID` = `e`.`image_templateID`\n"
      ."LEFT JOIN `person` `i`ON\n"
      ."  `r`.`inviter_personID` = `i`.`ID`\n"
      ."WHERE\n"
      ."  `t`.`type` = 'image template' AND\n"
      ."  `r`.`ID` = ".$this->_get_ID();
//    z($sql);
    $record = $this->get_record_for_sql($sql);
    if ($record===false) {
      z($sql);
    }
    sscanf($record['date'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
    $_YYYY =    ($_YYYY == "0000" ? $YYYY : $_YYYY);
    $date =     date($system_vars['defaultDateFormat'],	mktime(0, 0, 0, $_MM, $_DD, $_YYYY));
    $time =     str_replace('&#8201;',' ',Event::format_times($record['effective_time_start'], $record['effective_time_end']));
    $record['format_date'] = $date;
    $record['format_time'] = $time;
    $record['format_datetime'] = $date." ".$time;
//    y($record);die;
    return $record;
  }

  public function send_email() {
    @set_time_limit(60);	// Extend maximum execution time to 1 mins
    global $system_vars, $YYYY;
    if ($this->_get_ID()=="") {
      return "Register_Event::send_email() EventID not given.";
    }
    $sql =
       "SELECT\n"
      ."  `e`.`ID`,\n"
      ."  `e`.`custom_1`,\n"
      ."  `e`.`custom_2`,\n"
      ."  `e`.`custom_3`,\n"
      ."  `e`.`custom_4`,\n"
      ."  `e`.`effective_date_end`,\n"
      ."  `e`.`effective_date_start`,\n"
      ."  `e`.`effective_time_end`,\n"
      ."  `e`.`effective_time_start`,\n"
      ."  `e`.`location`,\n"
      ."  `e`.`no_email`,\n"
      ."  `e`.`content`,\n"
      ."  `e`.`title`,\n"
      ."  `e`.`URL`,\n"
      ."  `s`.`system_cancellation_days`,\n"
      ."  `re`.`attender_PEmail` AS `PEmail`,\n"
      ."  IF(`e`.`image_templateID`!=0,CONCAT(`s`.`URL`,'/_ticket?ID=',`re`.`ID`),'') `ticketURL`,\n"
      ."  CONCAT(`re`.`attender_NFirst`,' ',`re`.`attender_NMiddle`,' ',`re`.`attender_NLast`) AS `NName`\n"
      ."FROM\n"
      ."  `registerevent` `re`\n"
      ."INNER JOIN `system` `s` ON\n"
      ."  `s`.`ID` = `re`.`systemID`\n"
      ."INNER JOIN `postings` `e` ON\n"
      ."  `e`.`ID` = `re`.`eventID`\n"
      ."WHERE\n"
      ."  `re`.`ID` = ".$this->_get_ID();
    $record = $this->get_record_for_sql($sql);
    if (!$record){
      return "No such event";
    }
    if ($record['no_email'] =="1") {
      return "<b>Success:</b> You have successfully registered the event.<br />No email confirmation message was sent since this event is set not to require one.<br /><br />";
    }
    if (trim($record['PEmail']) =="") {
      return "<b>Success:</b> You have successfully registered the event.<br />No email confirmation message was sent since no email address was provided.<br /><br />";
    }
    sscanf($record['effective_date_start'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
    $_YYYY =    ($_YYYY == "0000" ? $YYYY : $_YYYY);
    $date =     date($system_vars['defaultDateFormat'],	mktime(0, 0, 0, $_MM, $_DD, $_YYYY));
    $time =     Event::format_times($record['effective_time_start'], $record['effective_time_end']);
    get_mailsender_to_component_results(); // Use system default mail details
    component_result_set('NName',$record['NName']);
    component_result_set('PEmail',$record['PEmail']);
    component_result_set('system_title',$system_vars['textEnglish']);
    component_result_set('system_URL',$system_vars['URL']);
    component_result_set('title',$record['title']);
    $Obj_Report = new Report;
    $Obj_Report->_set_ID($Obj_Report->get_ID_by_name('events'));
    $columns = $Obj_Report->get_columns();
    $fields = array();
    foreach ($columns as $column) {
      $field = $column['formField'];
      $label = $column['formLabel'];
      switch ($field) {
        case "":
        case "ID":
        case "canRegister":
        case "category":
        case "effective_date_start":
        case "effective_date_end":
        case "effective_time_start":
        case "effective_time_end":
        case "no_email":
        case "systemID":
        case "title":
        case "URL":
           // do nothing
        break;
        default:
          if (isset($record[$field])) {
            $fields[] = array('label'=>$label,'value'=>$record[$field]);
          }
        break;
      }
    }
    $html =
       "<table cellpadding='1' cellspacing='0' border='0'>\n"
      ."  <tr>\n"
      ."    <th align='left' width='170' valign='top'>Title:</th>\n"
      ."    <td valign='top'>".$record['title']."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th align='left' valign='top'>Date:</th>\n"
      ."    <td valign='top'>".$date."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th align='left' valign='top'>Time:</th>\n"
      ."    <td valign='top'>".$time."</td>\n"
      ."  </tr>\n"
      .($record['system_cancellation_days']!=0 ?
          "  <tr>\n"
         ."    <th align='left' valign='top'>Cancellations:</th>\n"
         ."    <td valign='top'>".$record['system_cancellation_days']." days notice required.</td>\n"
         ."  </tr>\n"
       : ""
      );
    foreach ($fields as $field){
      $html.=
         "  <tr>\n"
        ."    <th align='left' valign='top'>".$field['label'].":</th>\n"
        ."    <td valign='top'>".$field['value']."</td>\n"
        ."  </tr>\n";
    }
    $info = (substr($record['URL'],0,8)=='./?page=' ? trim($system_vars['URL'],"/")."/".substr($record['URL'],8) : "");
    $html.=
       ($record['URL']!="" ?
          "  <tr>\n"
         ."    <th align='left' valign='top'>More Information:</th>\n"
         ."    <td valign='top'><a href=\"".$info."\" rel='external'>$info</a></td>\n"
         ."  </tr>\n"
       : "")
       ."  <tr>\n"
       ."    <th align='left' valign='top'>Actions available:</th>\n"
       ."    <td valign='top'>"
       ."<a href=\"".trim($system_vars['URL'],"/")."/export/icalendar/".$record['ID']."\""
       ." title=\"Export this Event to Outlook 2000 or later\" rel='external'>"
       ."Add this event to your Outlook Calendar"
       ."</a></td>\n"
       ."  </tr>\n"
       .($record['ticketURL'] ?
            "  <tr>\n"
           ."    <th align='left' valign='top'>Your ticket:</th>\n"
           ."    <td valign='top'>"
           ."<a href=\"".$record['ticketURL']."\" title=\"Click for ticket by itself\" rel='external'>"
           ."<img src=\"".$record['ticketURL']."&submode=image\" />"
           ."</td>\n"
           ."  </tr>\n"
         : ""
        )
       ."</table>";
    $text =
       pad("Title:",20).$record['title']."\n"
      .pad("Date:",20).$date."\n"
      .pad("Time:",20).$time."\n";
      ($text['system_cancellation_days']!=0 ?
         pad("Cancellations:",20).$record['system_cancellation_days']." days notice required.\n"
       : ""
      );
    foreach ($fields as $field){
       $text.= "\n".$field['label'].":\n".strip_tags($field['value'])."\n";
    }
    $text.=
       "\n"
      .(trim($record['URL'])!="" ?     pad("More Information:",20).$info."\n" : "")
      .pad("Export to Outlook:",20).trim($system_vars['URL'],"/")."/export/icalendar/".$record['ID']."\n"
      .(trim($record['ticketURL']) ?   pad("Printable ticket:",20).$record['ticketURL']."\n" : "");
    component_result_set('content_text',$text);
    component_result_set('content_html',$html);
    $Obj_Mail_Template =    new Mail_Template;
    $template =             $Obj_Mail_Template->get_record_by_name('user_registered_for_event');
    $data =                 array();
    $data['PEmail'] =       component_result('PEmail');
    $data['NName'] =        component_result('NName');
    $data['subject'] =      $template['subject'];
    $data['html'] =         $template['body_html'];
    $data['text'] =         $template['body_text'];
    return mailto($data);
  }

  public function get_version(){
    return VERSION_REGISTER_EVENT;
  }
}
?>