<?php
define('VERSION_MAIL_QUEUE_ITEM','1.0.15');
/*
Version History:
  1.0.15 (2014-08-17)
    1) Message list now hidden unless viewer clicks 'Click here' link to display all messages

  (Older version history in class.mail_queue_item.txt)
*/
class Mail_Queue_Item extends Displayable_Item {
  const fields = 'ID, archive, archiveID, deleted, systemID, mailqueueID, mail_bounce_count, mail_error, mail_failed, mail_messageID, mail_sent, mail_vars, mail_webbeacon_date, mail_webbeacon_view, PEmail, NGreetingName, NName, NTitle, personID, PUsername, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  protected $_messages;
  protected $_with_list;

  public function __construct($ID="") {
    parent::__construct("mailqueue_item",$ID);
    $this->_set_object_name('Email Queue Item');
    $this->_set_name_field('');
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function get_message_details(){
    if (!sanitize('ID',$this->_get_ID())){
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `mailqueue`.`sender_name`    AS `sender_name`,\n"
      ."  `mailqueue`.`sender_email`   AS `sender_email`,\n"
      ."  `mailqueue`.`groupID`        AS `groupID`,\n"
      ."  `mailqueue_item`.`NName`     AS `recipient_name`,\n"
      ."  `mailqueue_item`.`personID`  AS `personID`,\n"
      ."  `mailqueue_item`.`PEmail`    AS `recipient_email`,\n"
      ."  `mailqueue_item`.`mail_vars` AS `mail_vars`,\n"
      ."  `mailqueue`.`body_html`      AS `body`,\n"
      ."  `mailqueue`.`style`          AS `style`,\n"
      ."  `mailqueue`.`subject`        AS `subject`\n"
      ."FROM\n"
      ."  `mailqueue_item`\n"
      ."INNER JOIN `mailqueue` ON\n"
      ."  `mailqueue_item`.`mailqueueID` = `mailqueue`.`ID`\n"
      ."WHERE\n"
      ."  `mailqueue_item`.`ID` = ".$this->_get_ID();
    return $this->get_record_for_sql($sql);
  }

  public function track_beacon(){
    $this->increment('mail_webbeacon_view');
    $this->set_field('mail_webbeacon_date',get_timestamp());
  }

  public function unsubscribe(){
    $this->_html =  "<h2 style='margin:0.25em 0'>Unsubscribe from email messages</h2>";
    if (!$this->_record = $this->get_message_details()){
      $this->_html.= "<p>Sorry - that message is no longer on our server.</p>";
      return $this->_html;
    }
    switch (get_var('submode')){
      case 'all':
        $Obj_person = new Person($this->_record['personID']);
        $records = $Obj_person->get_group_membership();
        $this->_html.= "<pre>".print_r($records,true)."</pre>";
      break;
      case 'group':
        $Obj_Group = new Group($this->_record['groupID']);
        $current = $Obj_Group->member_perms($this->_record['personID']);
        if($current['permEMAILRECIPIENT']==0 && $current['permEMAILOPTOUT']==1){
          $this->_html.= "You have already been unsubscribed from this group.";
        }
        else {
          $permArr = array('permEMAILOPTOUT'=>1,'permEMAILRECIPIENT'=>0);
          $Obj_Group->member_assign($this->_record['personID'],$permArr);
          $this->_html.= "You have now been unsubscribed from this group.";
        }
      break;
      default:
        $this->_unsubscribe_overview();
      break;
    }
    return $this->_html;
  }

  public function view(){
    global $component_result;
    if (!$record = $this->get_message_details()){
      $this->do_tracking("404");
      header("Status: 404 Not Found",true,404);
      print "<h1>404</h1><p>Sorry - that email message is no longer stored on our server.";
      die;
    }
    $personID = get_userID();
    if (!$personID || $personID==$record['personID']){
      $this->track_beacon();
    }
    $component_result = unserialize($record['mail_vars']);
    $subject = 	        convert_safe_to_php(str_replace("<br />","\n",$record['subject']));
    $this->_with_list = get_var('with_list')=='yes';
    $Obj =              new Person($record['personID']);
    $this->_messages =  $Obj->get_email_message_list();
    $count =            count($this->_messages);
    $html =
       "<html>\n"
      ."<head>\n"
      ."  <title>".$record['subject']."</title>\n"
      ."  <style type=\"text/css\">".$record['style']."</style>\n"
      ."</head>\n"
      ."<body>\n"
      ."<table cellpadding='2' cellspacing='0' border='1' style='border-collapse:collapse'>\n"
      ."  <tr>\n"
      ."    <td width='60' style='vertical-align:top'><b>From:</b></td>\n"
      ."    <td>".$record['sender_name']." &lt;".$record['sender_email']."&gt;</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td style='vertical-align:top'><b>To:</b></td>\n"
      ."    <td>".$record['recipient_name']." &lt;".$record['recipient_email']."&gt;</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td style='vertical-align:top'><b>Subject:</b></td>\n"
      ."    <td>".$record['subject']."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td style='vertical-align:top'><b>Message:</b></td>\n"
      ."    <td>".$record['body']."</td>\n"
      ."  </tr>\n"
      ."</table>\n";
    if ($count>1){
      $html.=
        ($this->_with_list ?
            "<p><b>All ".$count." messages sent to your account (<a href=\"".BASE_PATH."email-view/".$this->_get_ID()."\">hide this list</a>)</b></p>\n"
            .$this->view_message_list()
         :
             "<p><b><a href=\"".BASE_PATH."email-view/".$this->_get_ID()."?with_list=yes\">Click here</a> to see all ".$count." messages sent to your account</b></p>\n"
         );
    }
    $html.=
       "</body>\n"
      ."</html>\n";
    print convert_safe_to_php($html);
    die;
  }

  public function view_message_list(){
    $out =
       "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse'>\n"
      ."  <thead style='background:#d8d8d8'>\n"
      ."    <tr>\n"
      ."      <th>Date</th>\n"
      ."      <th>Subject</th>\n"
      ."      <th>View</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach($this->_messages as $m){
      $url =    BASE_PATH."email-view/".$m['ID'].($this->_with_list ? "?with_list=yes" : "");
      $out.=
         "  <tr".($m['ID']===$this->_get_ID() ? " style='background:#e0ffe0' title='(Currently displayed message)'" : "").">\n"
        ."    <td class='va_t'>".format_datetime($m['date_started'])."</td>\n"
        ."    <td>".$m['subject']."</td>\n"
        ."    <td><a href=\"".$url."\""
        ." onclick=\"popWin(this.href,'message_".$m['ID']."','location=1,status=1,scrollbars=1,resizable=1',900,600,1);return false;\">View</a></td>\n"
        ."  </tr>\n";
    }
    $out.=
       "  </tbody>\n"
      ."</table><br />\n";
    return $out;
  }

  public function get_version(){
    return VERSION_MAIL_QUEUE_ITEM;
  }
}
?>