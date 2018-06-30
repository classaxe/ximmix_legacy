<?php
define('VERSION_POLL','1.0.10');
/*
Version History:
  1.0.10 (2014-02-17)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.poll.txt)
*/
class Poll extends Displayable_Item {
  const fields = 'ID, archive, archiveID, deleted, systemID, active, category, choices_in_random_order, date, date_end, max_votes_per_ballot, question, responses, show_descriptions, show_scores, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID=""){
    parent::__construct("poll",$ID);
    $this->_set_name_field('question');
    $this->_set_has_actions(false);
    $this->_set_has_groups(false);
    $this->_set_assign_type('poll');
    $this->_set_object_name('Poll');
    $this->_set_message_associated('and defined Choices, Questions and Responses have');
  }

  function copy($new_name=false,$new_systemID=false,$new_date=true) {
    $newID =        parent::copy($new_name,$new_systemID,$new_date);
    $answers =      $this->get_choices();
    $Obj =          new Poll_Choice;
    foreach ($answers as $data) {
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
      $data['pollID'] =                 $newID;
      $data['votes'] =                  0;
      $Obj->insert($data);
    }
    $this->_set_ID($newID);
    $this->set_field('responses','');
    $this->set_field('active',0);
    return $newID;
  }

  function delete() {
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `poll_choice`\n"
      ."WHERE\n"
      ."  `pollID` IN(".$this->_get_ID().")";
    if ($records = $this->get_records_for_sql($sql)) {
      foreach ($records as $record) {
        $Obj = new Poll_Choice($record['ID']);
        $Obj->delete();
      }
    }
    return parent::delete();
  }

  function do_vote($choiceID_csv=''){
    if (!$record = $this->get_question(true)) {
      return "";
    }
    if (!$record['active']) {
      return "";
    }
    $this->_set_ID($record['ID']);
    $expires = time()+60*60*24*90; // 90 days
    setcookie("poll_".$this->_get_ID(),1,$expires,'/','',0);
    $_SESSION["poll_".$this->_get_ID()] = true;
    $ip_arr = unserialize($record['responses']);
    $ip = $_SERVER["REMOTE_ADDR"];
    $voted = false;
    if (!is_array($ip_arr) || !in_array($ip,$ip_arr)){
      $ip_arr[] = $ip;
      $this->set_field('responses',addslashes(serialize($ip_arr)));
      $choiceID_arr = explode(',',$choiceID_csv);
      foreach ($choiceID_arr as $ID){
        $Obj = new Poll_Choice($ID);
        $Obj->increment('votes');
      }
      $voted = true;
    }
    print $this->draw_result($voted);
    die;
  }

  function draw_question() {
    if (!$record = $this->get_question(true)) {
      return "";
    }
    if (!$record['active']) {
      return "";
    }
    $ID = $this->_get_ID();
    if ($record['choices_in_random_order']) {
      usort($record['choices'],array("Poll", "poll_randomize"));
    }
    $out =
       "<div class='question' id='poll_question_".$ID."'>".$record['question']."</div>\n"
      .draw_form_field('poll_choice_for_'.$ID,'','hidden')
      .draw_form_field('poll_max_votes_for_'.$ID,$record['max_votes_per_ballot'],'hidden')
      ."<b>Choices</b>\n"
      ."<div class='answers'>\n"
      ."<table class='answers' summary='List of choices for poll question'>\n";
    foreach ($record['choices'] as $choice) {
      $out.=
         "  <tr id='poll_choice_row_".$choice['ID']."'>\n"
        ."    <td>"
        .($record['max_votes_per_ballot']==1 ?
           "<input type='radio' id='poll_choice_".$choice['ID']."' name='poll_choices_for_".$ID."' value='".$choice['ID']."'"
          ." onclick=\"geid_set('".'poll_choice_for_'.$ID."','".$choice['ID']."')\""
          ." />"
         :
           "<input type='checkbox' name='poll_choice_".$choice['ID']."' id='poll_choice_".$choice['ID']."' value='".$choice['ID']."'"
          ." onclick=\""
          ."geid_set('poll_choice_for_".$ID."',csv_item_set(geid_val('poll_choice_for_".$ID."'),this.value,this.checked));"
          ."poll('limit','".$ID."')\" />"
         )
        ."</td>"
        ."    <td class='option'><label for='poll_choice_".$choice['ID']."'>".$choice['title']."</label></td>\n"
        .($record['show_descriptions']=='yes' ?
           "    <td class='option'><label for='poll_choice_".$choice['ID']."'>".$choice['content']."</label></td>\n"
         :
           ""
         )
        ."  </tr>\n";
    }
    $out.=
       "</table>\n"
      .($record['max_votes_per_ballot']==1 ?
          ""
       :
          "<p class='txt_c' style='margin-top:0;'><i>(Choose up to <span id='poll_choices_remaining_for_".$ID."'>"
         .$record['max_votes_per_ballot']
         ."</span>)</i></p>"
       )
      ."</div>\n"
      ."<div class='buttons'>\n"
      ."  <input type='button' onclick=\"poll('vote','".$ID."')\" value=\"Vote\" />\n"
      .($record['show_scores']=='always' ? "<br />\n<a href='#' onclick=\"return poll('result','".$ID."');\">View Results</a>\n" : "")
      ."</div>";
    return $out;
//    y($question);die;
  }

  function draw_result($voted=false) {
    if (!$record = $this->get_question()) {
      return "";
    }
    $total = 0;
    foreach ($record['choices'] as $choice) {
      $total+= $choice['votes'];
    }
    if ($total>0) {
      usort($record['choices'],array("Poll", "poll_sort"));
    }
    $show_scores = ($record['show_scores']!='never' || ($voted && $record['show_scores']=='after-voting'));
    $out =
       "<div class='question'>".$record['question']."</div>\n"
      .($show_scores ? "<b>Votes (Total ".$total.")</b>\n" : "<b>Choices</b>\n")
      ."<table class='answers'>\n";
    foreach($record['choices'] as $choice) {
      $percent =    ($total>0 ? (int)(100*$choice['votes']/$total)."%" : "&nbsp;");
      $out.=
         "  <tr>\n"
        ."    <td>".$choice['title']."</td>\n"
        .($show_scores ? "    <td class='percent'>".$percent."</td>\n" : "")
        .($show_scores ? "    <td class='score'>(".$choice['votes'].")</td>\n" : "")
        ."  </tr>\n";
    }
    $out.=
       "</table>\n"
      .(!$voted && $record['active']=='1' ?
          "<div class='buttons'>\n"
         ."  <a href='#' onclick=\"return poll('show','".$this->_get_ID()."');\">Add your vote...</a>\n"
         ."</div>\n"
       : ""
       )
      .(!$show_scores || $voted || $record['active']==0 ?
          "<div class='status txt_c'>"
         .($voted ? "<b>Thanks for voting!</b><br />" : "")
         .(!$show_scores ? "(Scores are not yet public)" : "")
         .($record['active']==0 && !$voted ? "<b>This poll is closed.</b>" : "")
         ."</div>\n"
       : ""
       )
       ;
    return $out;
//    y($question);die;
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with Answers";
    $extra_delete =
       "DELETE FROM `poll_choice`            WHERE `pollID` IN (".$targetID.");\n"
      ;
    $Obj = new Backup;
    $extra_select =
       $Obj->db_export_sql_query("`poll_choice`           ","SELECT * FROM `poll_choice` WHERE `pollID` IN (".$targetID.");",$show_fields)
      ;
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_all_polls($include_active=false,$include_unanswered=false){
    $out = array();
    $sql =
       "SELECT\n"
      ."  `poll`.`ID`,\n"
      ."  `poll`.`active`,\n"
      ."  `poll`.`systemID`,\n"
      ."  `poll`.`max_votes_per_ballot`,\n"
      ."  `poll`.`question`,\n"
      ."  `poll`.`history_created_date`\n"
      ."FROM\n"
      ."  `poll`\n"
      ."WHERE\n"
      ."  `poll`.`systemID` IN (1,".SYS_ID.") AND\n"
      .($include_active ? "" : "  `poll`.`active`=0 AND\n"       )
      ."  `poll`.`show_scores` IN ('always','after-voting')\n"
      ."ORDER BY\n"
      ."  `poll`.`date` DESC";
    if (!$polls = Poll::get_records_for_sql($sql)){
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `poll_choice`.`parentID`,\n"
      ."  `poll_choice`.`title`,\n"
      ."  `poll_choice`.`content`,\n"
      ."  `poll_choice`.`votes`\n"
      ."FROM\n"
      ."  `poll_choice`\n"
      ."INNER JOIN `poll` ON\n"
      ."  `poll`.`ID` = `poll_choice`.`parentID`\n"
      ."WHERE\n"
      ."  `poll`.`systemID` IN (1,".SYS_ID.") AND\n"
      .($include_active ? "" : "  `poll`.`active`=0 AND\n"       )
      ."  `poll`.`show_scores` IN ('always','after-voting')\n"
      ."ORDER BY\n"
      ."  `poll_choice`.`votes` DESC,\n"
      ."  `poll_choice`.`title`\n";
    $choices = Poll::get_records_for_sql($sql);
    foreach ($polls as $poll) {
      $poll['choices'] = array();
      $answers = 0;
      foreach($choices as $choice){
        if ($poll['ID'] == $choice['parentID']){
          $answers += $choice['votes'];
          $poll['choices'][] = $choice;
        }
      }
      if ($answers>0 || $include_unanswered){
        $out[] = $poll;
      }
    }
    return $out;
  }

  function get_current_polls($category="*",$limit=0,$offset=0) {
    sscanf(get_timestamp(), "%4s-%2s-%2s", $now_YYYY, $now_MM, $now_DD);
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `poll`\n"
      ."WHERE\n"
      ."  `poll`.`systemID` IN (1,".SYS_ID.") AND\n"
      .($category!="*" ? " `poll`.`category` REGEXP \"".implode("|",explode(',',$category))."\" AND\n": "")
      ."  `poll`.`active` = 1 AND\n"
      ."  `poll`.`archive` = 0 AND\n"
      ."  `poll`.`date` <= '".$now_YYYY."-".$now_MM."-".$now_DD."' AND\n"
      ."  (`poll`.`date_end`='0000-00-00 00:00:00' OR `poll`.`date_end` >= '".$now_YYYY."-".$now_MM."-".$now_DD."')\n"
      ."ORDER BY\n"
      ."  `poll`.`date` DESC"
      .($limit ? "\nLIMIT\n  ".$offset.",".$limit : "")
      ;
//    z($sql);
    return $this->get_records_for_sql($sql);
  }

  function get_question(){
    if (!$record = $this->get_record()) {
      return false;
    }
    $record['choices'] = $this->get_choices();
    return $record;
  }

  function get_choices() {
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `poll_choice`\n"
      ."WHERE\n"
      ."  `archive` = 0 AND\n"
      ."  `parentID` = ".$this->_get_ID()."\n"
      ."ORDER BY\n"
      ."  `seq`,\n"
      ."  `title`";
    return $this->get_records_for_sql($sql);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function manage_choices(){
    if (get_var('command')=='report'){
      return draw_auto_report('poll_choice',1);
    }
    $out = "<h3 style='margin:0.25em'>Choices for this ".$this->_get_object_name()."</h3>";
    if (!get_var('selectID')) {
      $out.="<p style='margin:0.25em'>No choices - this ".$this->_get_object_name()." has not been saved yet.</p>";
    }
    else {
      $out.= draw_auto_report('poll_choice',1);
    }
    return $out;
  }

  static function poll_randomize($a,$b){
    return round(rand(0,1))==0 ? -1 : 1;
  }

  static function poll_sort($a,$b){
    return (int)$a['votes'] == (int)$b['votes'] ? 0 : ((int)$a['votes'] > (int)$b['votes']) ? -1 : +1;
  }

  public function get_version(){
    return VERSION_POLL;
  }
}
?>