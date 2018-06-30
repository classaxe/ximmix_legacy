<?php
define('VERSION_POLL_CHOICE','1.0.4');
/*
Version History:
  1.0.4 (2014-02-17)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.poll_choice.txt)
*/
class Poll_Choice extends Record {
  const fields = 'ID, archive, archiveID, deleted, content, parentID, seq, systemID, title, votes, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID=""){
    parent::__construct("poll_choice",$ID);
    $this->_set_name_field('choice');
    $this->_set_has_actions(false);
    $this->_set_has_groups(false);
    $this->_set_assign_type('poll_choice');
    $this->_set_object_name('Poll Choice');
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_POLL_CHOICE;
  }
}
?>