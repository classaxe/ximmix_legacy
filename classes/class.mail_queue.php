<?php
define('VERSION_MAIL_QUEUE','1.0.37');
/*
Version History:
  1.0.37 (2014-03-11)
    1) Mail_Queue::_draw_wizard_preview() now URL decodes content supplied to it
       because the javascript that packages the content now first URL encodes it.

  (Older version history in class.mail_queue.txt)
*/
class Mail_Queue extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, groupID, mailidentityID, mailtemplateID, body_html, body_text, date_aborted, date_completed, date_started, date_queued, sender_email, sender_name, status, style, subject, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("mailqueue",$ID);
    $this->_set_object_name('Mail Job');
    $this->_set_message_associated('and associated recipients have');
    $this->_set_name_field('');
  }

  public function create_queue($mailidentityID,$mailtemplateID,$groupID){
    $data =
      array(
        'systemID' =>       SYS_ID,
        'groupID' =>        $groupID,
        'mailidentityID' => $mailidentityID,
        'mailtemplateID' => $mailtemplateID,
        'date_queued' =>    get_timestamp()
      );
    $mailQueueID = $this->insert($data);
    $this->_set_ID($mailQueueID);
    $this->save_sender_and_template_snapshot();
    $Obj_Group =    new Group($groupID);
    $recipients =   $Obj_Group->get_email_recipients();
    $Obj_MQI =      new Mail_Queue_Item;
    foreach ($recipients as $recipient){
      $data =
        array(
          'systemID' =>         SYS_ID,
          'mailQueueID' =>      $mailQueueID,
          'PEmail' =>           $recipient['PEmail'],
          'NGreetingName' =>    $recipient['NGreetingName'],
          'NName' =>            $recipient['NName'],
          'NTitle' =>           $recipient['NTitle'],
          'personID' =>         $recipient['ID'],
          'PUsername' =>        $recipient['PUsername']
        );
      $Obj_MQI->insert($data);
    }
    return $mailQueueID;
  }

  public function delete() {
    $sql =
       "DELETE FROM\n"
      ."  `mailqueue_item`\n"
      ."WHERE\n"
      ."  `mailQueueID` IN (".$this->_get_ID().")";
    $this->do_sql_query($sql);
    parent::delete();
  }

  public function deliver($smtp_data_fail_limit=0) {
    global $component_result, $system_vars;
    @set_time_limit(600);	// Extend maximum execution time to 10 mins
    $record =               $this->get_record();
    get_mailsender_to_component_results($record['mailidentityID']);
    $Obj_System =           new System($record['systemID']);
    $Obj_System->load();
    component_result_set('systemID',$Obj_System->record['ID']);
    component_result_set('system_title',$Obj_System->record['textEnglish']);
    component_result_set('system_URL',$Obj_System->record['URL']);
    $Obj_Mail_Template =    new Mail_Template($record['mailtemplateID']);
    $Obj_Mail_Template->load();
    $recipients = $this->get_recipients();
    $ObjMailQueueItem =    new Mail_Queue_Item;
    $ObjPerson  = new Person;
    $data_not_accepted_count = 0;
    foreach ($recipients as $row) {
      $ObjMailQueueItem->_set_ID($row['ID']);
//      print "*";
      $ObjPerson->_set_ID($row['personID']);
      $ObjPerson->load_profile_fields();
      component_result_set('ID',$row['ID']);
      if ($Obj_Mail_Template->record['set_random_password']){
        $Obj = new Person(component_result('personID'));
        $Obj->set_random_password();
      }
      $data =               array();
      $data['ID'] =         $row['ID'];
      $data['PEmail'] =     $row['PEmail'];
      $data['NName'] =      $row['NName'];
      $data['subject'] =    $Obj_Mail_Template->record['subject'];
      $data['html'] =
         $Obj_Mail_Template->record['body_html']
        ."<p><small>This email was sent to [ECL]field_person_primary_email[/ECL] by [ECL]field_mail_sender_name[/ECL] &lt;[ECL]field_mail_sender_email[/ECL]&gt;.<br />\n"
        ."<a href=\"[ECL]field_person_link_unsubscribe[/ECL]\">Click here</a> to unsubscribe from mail messages.<br />\n"
        ."<a href=\"[ECL]field_person_link_view_message[/ECL]\">Click here</a> to view this message in your web browser.\n"
        ."[ECL]email_webbeacon[/ECL]</small></p>\n";
      $data['text'] =       $Obj_Mail_Template->record['body_text'];
      $data['style']   =    $Obj_Mail_Template->record['style'];
      $mail_result =        mailto($data);
      if (substr($mail_result,0,12)=="Message-ID: ") {
        $data =
          array(
            'mail_error'=>'',
            'mail_messageID'=>substr($mail_result,12),
            'mail_sent'=>1,
            'mail_vars'=>addslashes(serialize($component_result))
          );
      }
      else if ($mail_result=='SMTP Error: Data not accepted.'){
        $data =
          array(
            'mail_error'=>$mail_result
          );
      }
      else {
        $data =
          array(
            'mail_error'=>$mail_result,
            'mail_failed'=>1,
            'mail_sent'=>1,
            'mail_vars'=>addslashes(serialize($component_result))
          );
      }
      $ObjMailQueueItem->update($data);
      if ($mail_result=='SMTP Error: Data not accepted.'){
        $data_not_accepted_count++;
      }
      if ($smtp_data_fail_limit && $data_not_accepted_count>=$smtp_data_fail_limit){
        return false;
      }
    }
    $sql =
       "SELECT\n"
      ."  COUNT(*) AS `count`\n"
      ."FROM\n"
      ."  `mailqueue_item`\n"
      ."WHERE\n"
      ."  `mailQueueID` = ".$this->_get_ID()." AND\n"
      ."  `mail_failed` = 0 AND\n"
      ."  `mail_sent` = 0 AND\n"
      ."  `PEmail` LIKE '%@%'";
    $row = $this->get_field_for_sql($sql);
    if ($row['count']==0) {
      $data =
        array(
          'date_completed'=>get_timestamp()
        );
      $this->update($data);
      $this->save_sender_and_template_snapshot();
      return true;
    }
    return false;
  }

  public function deliver_all($smtp_data_fail_limit=0) {
    $records = $this->get_pending();
    if (!$records) {
      return 'Mail Queue: Nothing to do';
    }
    @set_time_limit(600);	// Extend maximum execution time to 10 mins
    $items = 0;
    foreach ($records as $record) {
      $this->_set_ID($record['ID']);
      $this->deliver($smtp_data_fail_limit);
      $items++;
    }
    return 'Delivered '.$items.' item'.($items==1 ? '' : 's');
  }

  public function draw_broadcast_form(){
    global $submode, $groupID, $mailtemplateID, $mailidentityID;
    global $selectID, $targetID, $targetReportID;
    global $offset, $limit;
    global $filterField,$filterValue,$filterExact;
    $msg = "";
    $out = "";
    switch ($submode) {
      case "delete":
        $Obj_Report =   new Report($targetReportID);
        $report_name =  $Obj_Report->get_name();
        switch ($report_name){
          case 'mail_queue':
            $count = count(explode(',',$targetID));
            $this->_set_ID($targetID);
            $this->delete();
            $msg =
               "<b>Success:</b> The "
              .($count==1 ? "Mail Job" : $count." Mail Jobs")
              ." and associated recipients have been deleted.";
            $targetID = "";
            $submode = "";
          break;
        }
      break;
      case "queue":
        if ($groupID==0) {
          $msg = "<b>Error:</b> Please create a Group containing members who are set to receive email then try this operation again.";
          break;
        }
        $selectID = $this->create_queue($mailidentityID,$mailtemplateID,$groupID);
        $msg = "<b>Success:</b> the Mail Job has been created but has not yet been started.";
      break;
      case "send":
        $this->_set_ID($selectID);
        $msg = $this->send();
      break;
      case "send_now":
        if ($groupID==0) {
          $msg = "<b>Error:</b> Please create a Group containing members who are set to receive email then try this operation again.";
          break;
        }
        $selectID = $this->create_queue($mailidentityID,$mailtemplateID,$groupID);
        $this->_set_ID($selectID);
        $msg = $this->send();
      break;
      case "status":
        $msg = "<b>Status:</b> Message status for all Mail Jobs was updated at ".get_timestamp();
        $this->handle_bounced_messages();
      break;
    }
    $out.=
       "<h3 class='admin_heading' style='display: inline;'>Create Mail Job</h3><br />\n"
      .HTML::draw_status('mail_broadcast',$msg);
    if (!$this->get_emailable_group_count()) {
      $out.=
         "<p>There are no Groups with Email Recipients.<br />\n"
        ."You cannot create a new Mail Broadcast Job until you have one.</p>";
    }
    else {
      $out.=
         "<table class='minimal'>\n"
        ."  <tr>\n"
        ."    <td>".draw_form_header("Mail Broadcast","",0)."</td>\n"
        ."  </tr>\n"
        ."  <tr>\n"
        ."    <td>\n"
        ."    <table cellpadding='0' cellspacing='0' class='table_border' width='100%'>\n"
        ."      <tr class='table_header'>\n"
        ."        <td>&nbsp;From:&nbsp;</td>\n"
        ."        <td><select name=\"mailidentityID\" class='admin_formFixed' style='width: 500px;'>"
        .draw_select_options(Mail_Identity::get_selector_SQL(),$mailidentityID)
        ."</select></td>\n"
        ."      </tr>\n"
        ."      <tr class='table_header'>\n"
        ."        <td>&nbsp;To:&nbsp;</td>\n"
        ."        <td><select name=\"groupID\" class='admin_formFixed' style='width: 500px;'>"
        .draw_select_options(Group::get_selector_email_groups_sql(),$groupID)
        ."</select></td>\n"
        ."      </tr>\n"
        ."      <tr class='table_header'>\n"
        ."        <td>&nbsp;Template:&nbsp;</td>\n"
        ."        <td><select name=\"mailtemplateID\" class='admin_formFixed' style='width: 500px;'>"
        .draw_select_options(Mail_Template::get_selector_SQL(),$mailtemplateID)
        ."</select></td>\n"
        ."      </tr>\n"
        ."      <tr class='table_header'>\n"
        ."        <td colspan='2' align='center'>"
        ."<input type='reset' value='Clear Form' style='formButton'/>"
        ."<input type='button' onclick=\"if(geid_val('mailidentityID') && geid_val('mailtemplateID') && geid_val('groupID')){geid_set('submode','send_now');geid('form').submit();}else{alert('All three fields are required');}\" value='Send Now' style='formButton'/>"
        ."<input type='button' onclick=\"if(geid_val('mailidentityID') && geid_val('mailtemplateID') && geid_val('groupID')){geid_set('submode','queue');geid('form').submit();}else{alert('All three fields are required');}\" value='Send Later' style='formButton'/>"
        ."</td>\n"
        ."      </tr>\n"
        ."    </table></td>"
        ."  </tr>\n"
        ."</table>\n"
        ."<br /><br />\n";
    }
    $mailqueue_count = $this->get_mailqueue_count();
    if (!$mailqueue_count){
      return $out;
    }
    $this->_set_ID($selectID);
    $record = $this->get_mailqueue_status();
    if ($record===false){
      $selectID = '';
    }
    $out.=
       "<h3 class='admin_heading' style='margin:0'>Mail Jobs</h3>\n"
      .($selectID == '' ?
          "<p>Click a Mail <span style='color:#808000'><b>Job Number</b></span> in the report below to <span style='background-color: #ffffa0;border:1px solid #888'>&nbsp;select&nbsp;</span> it.<br />\n"
         ."This lets you view details of the Recipients, and allows you to start the Job if it hasn't already been started."
         ."</p>\n"
       :
          "<p>Mail Job <a href=\"#mail_queue_items\" style='color:#000;text-decoration:none;' title=\"Click to View Recipients\"><b>#".$selectID."</b></a> has been selected"
         .($record['date_started'] != "0000-00-00 00:00:00" ?
              ".<br />"
            :
              " but hasn't yet been started.<br />"
             ."Click <a href=\"".BASE_PATH."report/mail_broadcast?submode=send&amp;selectID=".$selectID."\"><b>here</b></a> to start it now."
          )
         ."</p>\n"
       )
      .draw_auto_report('mail_queue',1)
      ."<p>Click <a href=\"".BASE_PATH."report/mail_broadcast?submode=status&amp;selectID=".$selectID."\" onclick=\"show_popup_please_wait('Please wait...<br />Checking for bounced messages.',240,200);return true;\"><b>here</b></a>"
      ." to check all previous Mail Jobs for bounced messages to update final message counts.</p>";
    if ($selectID){
      if ($record['date_started'] == "0000-00-00 00:00:00") {
        $out.= "<input type='button' onclick=\"this.value='Please Wait...';this.disabled=1;geid_set('selectID','".$selectID."');geid_set('submode','send');geid('form').submit()\" value='Begin Delivery' style='formButton'/>";
      }
      $out.=
         "<br />\n"
        ."<a name='mail_queue_items'></a>\n"
        ."<h3 class='admin_heading' style='display: inline;'>"
        ."Recipients for Mail Job #".$selectID."</h3>\n"
        .draw_auto_report('mail_queue_items',1);
    }
    return $out;
  }

  public function draw_wizard(){
    $submode =          get_var('submode');
    $mailidentityID =   $this->_draw_wizard_get_mail_identity();
    $mailtemplateID =   $this->_draw_wizard_get_mail_template();
    $groupID =          $this->_draw_wizard_get_mail_group();
    $zones =            array();
    $msg =              "";
    if ($mailtemplateID){
      $Obj_Mail_Template = new Mail_Template($mailtemplateID);
      $Obj_Mail_Template->load();
      $zones_count = $Obj_Mail_Template->record['content_zones'];
      $subject = (isset($_REQUEST['subject']) ? $_REQUEST['subject'] : $Obj_Mail_Template->record['subject']);
      $zones = array();
      for($i=1; $i<=$zones_count; $i++){
        $zones[$i] = get_var('content_zone_'.$i);
      }
      if ($mailidentityID){
        $Obj_Mail_Identity = new Mail_Identity($mailidentityID);
        $Obj_Mail_Identity->load();
      }
      if ($groupID){
        $Obj_Mail_Group = new Group($groupID);
        $Obj_Mail_Group->load();
      }
    }
    switch ($submode){
      case "preview":
        print $this->_draw_wizard_preview($Obj_Mail_Group,$Obj_Mail_Identity,$Obj_Mail_Template,$subject,$zones);
        die;
      break;
      case "preview_content":
        $Obj_Mail_Template = new Mail_Template(get_var('mailtemplateID'));
        $Obj_Mail_Template->load();
        print $this->_draw_wizard_preview_content($Obj_Mail_Template,$zones);
        die;
      break;
      case "queue":
        $mailQueueID = $this->_draw_wizard_queue($Obj_Mail_Group,$Obj_Mail_Identity,$Obj_Mail_Template,$subject,$zones);
        $msg =          "<b>Success</b> Job was queued. To work with it in the Mail Queue, <a href='".BASE_PATH."report/mail_broadcast?selectID=".$mailQueueID."'>click here</a>";
      break;
      case "send":
        $mailQueueID = $this->_draw_wizard_queue($Obj_Mail_Group,$Obj_Mail_Identity,$Obj_Mail_Template,$subject,$zones);
        $this->_set_ID($mailQueueID);
        $msg = $this->send();
      break;
    }
    if (!$this->get_emailable_group_count()) {
      $html =
         "<p>There are no Groups with Email Recipients.<br />\n"
        ."You cannot use the Email Wizard until you have one.</p>";
      return $html;
    }
    Page::push_content(
      'javascript',
       "function email_wizard_reload(){\n"
      ."  geid('form').submit();\n"
      ."}"
      ."function email_wizard_group_check(){\n"
      ."  if((geid_val('groupID')=='' && '".$groupID."'!='') || (geid_val('groupID')!='' && '".$groupID."'=='')){\n"
      ."    geid('form').submit();\n"
      ."  };\n"
      ."}"
    );
    $html =
       HTML::draw_status('emailwizard',$msg)
      ."<div id='emailwizard'>\n"
      .draw_form_header("Email Wizard","",0)
      ."<table class='admin_containerpanel' summary='Email wizard container'>\n"
      ."  <tr>\n"
      ."    <th>1. Choose Sender:</th>\n"
      ."    <td>".draw_form_field("mailidentityID",$mailidentityID,"selector","600px",Mail_Identity::get_selector_SQL(),0,"onchange=\"email_wizard_reload()\"")."</td>\n"
      ."  </tr>\n";
    if ($mailidentityID){
      $html.=
         "  <tr>\n"
        ."    <th>2. Choose Group:</th>\n"
        ."    <td>".draw_form_field("groupID",$groupID,"selector","600px",Group::get_selector_email_groups_sql(),0,"onchange=\"email_wizard_group_check()\"")."</td>\n"
        ."  </tr>\n";
   }
   if ($mailidentityID && $groupID) {
     $html.=
       "  <tr>\n"
      ."    <th>3. Choose Stationery:</th>\n"
      ."    <td>".draw_form_field("mailtemplateID",$mailtemplateID,"selector","600px",Mail_Template::get_selector_SQL(1),0,"onchange=\"email_wizard_reload()\"")."</td>\n"
      ."  </tr>\n";
   }
   if ($mailidentityID && $groupID && count($zones)){
     $row = array();
     for($i=1; $i<=count($zones); $i++){
       $row['content_zone_'.$i] = get_var('content_zone_'.$i);
     }
     $html .=
         "  <tr>\n"
        ."    <th>4. Provide Subject:</th>\n"
        ."    <td>".draw_form_field("subject",$subject,"text","600px")."</td>\n"
        ."  </tr>\n"
        ."  <tr>\n"
        ."    <th>5. Provide Content:</th>\n"
        ."    <td>"
        .draw_form_field('content_zone_count',count($zones),'hidden')
        .Report_Column::draw_form_field($row,"content_zone",'',"html_multi_block","600px",'','','','','','',count($zones).'|Email_Wizard|Zone')."</td>\n"
        ."  </tr>\n"
        ."  <tr>\n"
        ."    <td colspan='2' class='txt_c'>\n"
        ."<input type='button' value=\"Cancel\" onclick=\"emailwizard_cancel()\" />\n"
        ."<input type='button' value=\"Preview\" onclick=\"emailwizard_preview()\" />\n"
        ."<input type='button' value=\"Send Now\" onclick=\"emailwizard_send()\" />\n"
        ."<input type='button' value=\"Send Later\" onclick=\"emailwizard_queue()\" />\n"
        ."</td>\n"
        ."  </tr>\n";
    }
    $html .=
       "</table>"
      ."</div>\n";
    return $html;
  }

  private function _draw_wizard_get_mail_group(){
    $out =  get_var('groupID');
    $sql =  Group::get_selector_email_groups_sql();
    $records = $this->get_records_for_sql($sql);
    if (count($records)==2){
      $out = $records[1]['value'];
    }
    return $out;
  }

  private function _draw_wizard_get_mail_identity(){
    $out =  get_var('mailidentityID');
    $sql =  Mail_Identity::get_selector_SQL();
    $records = $this->get_records_for_sql($sql);
    if (count($records)==2){
      $out = $records[1]['value'];
    }
    return $out;
  }

  private function _draw_wizard_get_mail_template(){
    $out =  get_var('mailtemplateID');
    $sql =  Mail_Template::get_selector_SQL(1);
    $records = $this->get_records_for_sql($sql);
    if (count($records)!=2){
      return $out;
    }
    return $records[1]['value'];
  }

  private function _draw_wizard_preview($Obj_Mail_Group,$Obj_Mail_Identity,$Obj_Mail_Template,$subject,$zones){
    $sender =           $Obj_Mail_Identity->record['name'].' ['.$Obj_Mail_Identity->record['email'].']';
    $recipient =        $Obj_Mail_Group->record['name'];
    $recipient_count =  $Obj_Mail_Group->get_email_recipients_count();
    $html =             $Obj_Mail_Template->record['body_html'];
    for($i=1; $i<=count($zones); $i++){
      $content = urldecode(($zones[$i]!='' ? $zones[$i] : "<span title='This zone has no content' style='background-color:#ffe0e0;color:#ff0000'>%%".$i."%%</span>"));
      $html = str_replace('%%'.$i.'%%',$content,$html);
    }
    $html = convert_safe_to_php($html);
    return
       "<div style='padding:5px;height:420px'>"
      ."<table cellpadding='2' cellspacing='0' border='1'>\n"
      ."  <tr>\n"
      ."    <td width='60'><b>From:</b></td>\n"
      ."    <td>".$sender."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><b>To:</b></td>\n"
      ."    <td>".$recipient." [Recipients: ".$recipient_count."]</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><b>Subject:</b></td>\n"
      ."    <td>".$subject."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2'class='txt_l'><b>Message:</b><br />\n"
      ."<input type='hidden' id='emailwizard_html' value=\"".rawurlencode($html)."\" />"
      ."<iframe frameborder='0' marginwidth='0' marginheight='0' style='width:780px;height:340px;padding:0;border:0;margin:0;overflow:auto'"
      ." src=\"".BASE_PATH."report/email_wizard?submode=preview_content&mailtemplateID=".$Obj_Mail_Template->record['ID']."\"></iframe>\n"
      ."</td>\n"
      ."  </tr>\n"
      ."</table>\n"
      ."</div>";
  }

  private function _draw_wizard_preview_content($Obj_Mail_Template,$zones){
    global $system_vars;
    return
       "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
      ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".$system_vars['defaultLanguage']."\">\n"
      ."<head>\n"
      ."<title>Manage Email Templates and Stationery</title>\n"
      ."<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".(ini_get('default_charset') ? ini_get('default_charset') : "UTF-8")."\"/>\n"
      ."<meta http-equiv=\"Generator\" content=\"".System::get_item_version('system_family')." ".System::get_item_version('codebase').".".$system_vars['db_version']."\"/>\r\n"
      ."<style type='text/css'>\n"
      .$Obj_Mail_Template->record['style']."\n"
      ."</style>\n"
      ."<script type='text/javascript'>\n"
      ."function getContent(){\n"
      ."  var content = decodeURIComponent(parent.document.getElementById('emailwizard_html').value);\n"
      ."  document.getElementById('emailwizard_body').innerHTML=content;\n"
      ."}\n"
      ."</script>"
      ."</head>\n"
      ."<body id='emailwizard_body' onload='getContent()'>"
      ."</body>\n"
      ."</html>";
  }

  private function _draw_wizard_queue($Obj_Mail_Group,$Obj_Mail_Identity,$Obj_Mail_Template,$subject,$zones){
    $html =  $Obj_Mail_Template->record['body_html'];
    for($i=1;$i<=count($zones); $i++){
      $html = str_replace('%%'.$i.'%%',"<!-- start_".$i." -->".$zones[$i]."<!-- end_".$i." -->",$html);
    }
    $text = $Obj_Mail_Template->record['body_text'];
    for($i=1;$i<=count($zones); $i++){
      $text = str_replace('%%'.$i.'%%',$zones[$i],$text);
    }
    $style =    $Obj_Mail_Template->record['style'];
    $name =
       $Obj_Mail_Template->record['name']
      ." (Prepared on "
      .date('Y-m-d \a\t H:i:s',time())
      .")";
    $set_random_password = $Obj_Mail_Template->record['set_random_password'];
    $Obj_Mail_Template2 = new Mail_Template;
    $data =
       array(
         'archive' =>               0,
         'archiveID' =>             0,
         'systemID' =>              SYS_ID,
         'name' =>                  addslashes($name),
         'body_html' =>             addslashes($html),
         'style' =>                 addslashes($style),
         'content_zones' =>         0,
         'stationery' =>            0,
         'set_random_password' =>   $set_random_password,
         'subject' =>               addslashes($subject)
       );
    $mailtemplateID = $Obj_Mail_Template2->insert($data);
    $Obj_Mail_Template2->fix_subject_bodytext_and_path();
    return $this->create_queue($Obj_Mail_Identity->_get_ID(),$mailtemplateID,$Obj_Mail_Group->_get_ID());
  }

  public function export_sql($targetID,$show_fields) {
    $header =
      "Selected ".$this->_get_object_name().$this->plural($targetID)." (with mailqueue items)";
    $Obj_Backup =   new Backup;
    $extra_delete = "DELETE FROM `mailqueue_item`         WHERE `mailqueueID` IN (".$targetID.");\n";
    $extra_select = $Obj_Backup->db_export_sql_query("`mailqueue_item`        ","SELECT * FROM `mailqueue_item` WHERE `mailqueueID` IN (".$targetID.")",$show_fields);
    return  $this->sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  public function get_emailable_group_count(){
    $isMASTERADMIN = get_person_permission("MASTERADMIN");
    $sql =
       "SELECT\n"
      ."  COUNT(*) AS `count`\n"
      ."FROM\n"
      ."  `groups`\n"
      ."INNER JOIN `group_members` ON\n"
      ."  `groups`.`ID` = `group_members`.`groupID`\n"
      ."WHERE\n"
      .($isMASTERADMIN ? "" : "  `groups`.`systemID` = ".SYS_ID." AND\n")
      ."  `group_members`.`permEMAILRECIPIENT` = 1\n";
    return $this->get_field_for_sql($sql);
  }

  public function get_mail_identites_in_use(){
    $isMASTERADMIN = get_person_permission("MASTERADMIN");
    $sql =
       "SELECT DISTINCT\n"
      ."  `mailidentity`.`bounce_pop3_host`,\n"
      ."  `mailidentity`.`bounce_pop3_password`,\n"
      ."  `mailidentity`.`bounce_pop3_port`,\n"
      ."  `mailidentity`.`bounce_pop3_username`\n"
      ."FROM\n"
      ."  `mailqueue`\n"
      ."LEFT JOIN `mailidentity` ON\n"
      ."  `mailidentity`.`ID` = `mailqueue`.`mailidentityID`\n"
      ."WHERE\n"
      .($isMASTERADMIN ? "  1" : "  `mailqueue`.`systemID` = ".SYS_ID);
    return $this->get_records_for_sql($sql);
  }

  public function get_mailqueueID_for_messageID($messageID){
    $sql =
       "SELECT\n"
      ."  `mailqueueID`\n"
      ."FROM\n"
      ."  `mailqueue_item`\n"
      ."WHERE\n"
      ."  `mail_messageID` = \"".$messageID."\"";
    return $this->get_record_for_sql($sql);
  }

  public function get_mailqueue_count(){
    $isMASTERADMIN = get_person_permission("MASTERADMIN");
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `mailqueue`\n"
      ."WHERE\n"
      .($isMASTERADMIN ? "  1" : "  `mailqueue`.`systemID` = ".SYS_ID);
    return $this->get_field_for_sql($sql);
  }

  public function get_mailqueue_status(){
    if (!$this->_get_ID()){
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `date_started`,\n"
      ."  `date_completed`\n"
      ."FROM\n"
      ."  `mailqueue`\n"
      ."WHERE\n"
      ."  `ID` = \"".$this->_get_ID()."\"";
    return $this->get_record_for_sql($sql);
  }

  public function get_pending() {
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `".$this->table."`\n"
      ."WHERE\n"
      ."  `date_started`!='0000-00-00 00:00:00' AND\n"
      ."  `date_completed`='0000-00-00 00:00:00' AND\n"
      ."  `date_aborted`='0000-00-00 00:00:00'";
    return $this->get_records_for_sql($sql);
  }

  public function get_recipients() {
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `PEmail`,\n"
      ."  `NGreetingName`,\n"
      ."  `NName`,\n"
      ."  `NTitle`,\n"
      ."  `personID`,\n"
      ."  `PUsername`\n"
      ."FROM\n"
      ."  `mailqueue_item`\n"
      ."WHERE\n"
      ."  `mail_sent` = 0 AND\n"
      ."  `mailQueueID` = ".$this->_get_ID();
    return $this->get_records_for_sql($sql);
  }

  public function handle_bounced_messages(){
    @set_time_limit(600);	// Extend maximum execution time to 10 mins
    // Get login credentials for mailqueues
    $mailidentities = $this->get_mail_identites_in_use();
    foreach ($mailidentities as $mailidentity){
      $Obj_POP3 = new phPOP3(
        $mailidentity['bounce_pop3_host'],
        $mailidentity['bounce_pop3_port'],
        $mailidentity['bounce_pop3_username'],
        $mailidentity['bounce_pop3_password']
      );
      $mailbox = $Obj_POP3->pop3_list();
      if( $mailbox["messages"] > 0 ) {
        for($i=1; $i<$mailbox["messages"]+1; $i++ ) {
          $message = $Obj_POP3->pop3_retrieve($i);
          $body = implode('',$message->body);
          if ($start = strpos($body,"Content-Type: message/delivery-status")) {
            $text = substr($body,$start);
            preg_match("/Status: ([0-9.]+)/", $text, $status);
            preg_match("/Message-ID: <([^>]+)>/",$text, $messageID);
            preg_match("/Diagnostic-Code:(.*?)\r\n\r\n/s",$text, $diagnostic);
            $status =       (isset($status[1]) ? $status[1] : false);
            $messageID =    (isset($messageID[1]) ? $messageID[1] : "");
            $diagnostic =   (isset($diagnostic[1]) ? str_replace(array('\r\n','  '),' ',$diagnostic[1]) : "");
            if ($messageID) {
              $soft = substr($status,0,1)!='5';  // Not permanent error
              $mailqueueID = $this->get_mailqueueID_for_messageID($messageID);
              if ($mailqueueID){
                $error =    $status." ".$diagnostic;
                if ($soft) {
                  $this->_handle_bounced_messages_set_soft_bounce($messageID,$error);
                  $this->set_field('date_completed','0000-00-00 00:00:00');
                }
                else {
                  $this->_handle_bounced_messages_set_hard_bounce($messageID,$error);
                }
                $Obj_POP3->pop3_delete($i);
              }
            }
          }
        }
      }
    }
    $Obj_POP3->pop3_quit();
  }

  private function _handle_bounced_messages_set_hard_bounce($messageID,$error){
    $sql =
        "UPDATE\n"
       ."  `mailqueue_item`\n"
       ."SET\n"
       ."  `mail_error` = \"".$error."\",\n"
       ."  `mail_failed` = 1\n"
       ."WHERE\n"
       ."  `mail_messageID` = \"".$messageID."\"";
    $this->do_sql_query($sql);
  }

  private function _handle_bounced_messages_set_soft_bounce($messageID,$error){
    $sql =
        "UPDATE\n"
       ."  `mailqueue_item`\n"
       ."SET\n"
       ."  `mail_error` = \"".$error."\",\n"
       ."  `mail_bounce_count` = `mail_bounce_count`+1,\n"
       ."  `mail_sent` = 0\n"
       ."WHERE\n"
       ."  `mail_messageID` = \"".$messageID."\"";
    $this->do_sql_query($sql);
  }

  public function send(){
    $record = $this->get_record();
    if ($record===false) {
      return "<b>Error:</b> That job no longer exists";
    }
    if ($record['date_completed'] != "0000-00-00 00:00:00") {
      return "The job has now been completed.";
    }
    if ($record['date_started'] == "0000-00-00 00:00:00") {
      $this->set_field('date_started',get_timestamp());
    }
    return "<b>Status:</b> Job has been accepted for delivery. Delivery will commence shortly.";
  }

  public function save_sender_and_template_snapshot(){
    $sql =
       "UPDATE\n"
      ."  `mailqueue`,\n"
      ."  `mailidentity`,\n"
      ."  `mailtemplate`\n"
      ."SET\n"
      ."  `mailqueue`.`sender_email` = `mailidentity`.`email`,\n"
      ."  `mailqueue`.`sender_name` =  `mailidentity`.`name`,\n"
      ."  `mailqueue`.`body_html` = `mailtemplate`.`body_html`,\n"
      ."  `mailqueue`.`body_text` = `mailtemplate`.`body_text`,\n"
      ."  `mailqueue`.`style` =     `mailtemplate`.`style`,\n"
      ."  `mailqueue`.`subject` =   `mailtemplate`.`subject`\n"
      ."WHERE\n"
      ."  `mailqueue`.`mailidentityID` = `mailidentity`.`ID` AND\n"
      ."  `mailqueue`.`mailtemplateID` = `mailtemplate`.`ID` AND\n"
      ."  `mailqueue`.`ID` = ".$this->_get_ID();
    $this->do_sql_query($sql);
  }

  public function get_version(){
    return VERSION_MAIL_QUEUE;
  }
}
?>