<?php
define('VERSION_CRM_CASE_TASK','1.0.2');
/*
Version History:
  1.0.2 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.1 (2009-11-25)
    1) Added CRM_Case_task::handle_report_copy() to allow tasks to be cloned
  1.0.0 (2009-07-02)
    Initial release
*/
class CRM_Case_task extends Record {

  function __construct($ID="") {
    parent::__construct("case_tasks",$ID);
    $this->_set_object_name('Case Task');
    $this->_set_name_field('subject');
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  function on_update() {
    global $action_parameters;
//    print_r($action_parameters);die;
    $ID_arr = explode(",",$action_parameters['triggerID']);
    $bulk_update = (isset($action_parameters['data']['bulk_update']) && $action_parameters['data']['bulk_update']==1 ? true : false);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $done = false;
      $change_arr =             array();
      $old =                    $this->get_record(false);
      $old_notes =              $old['notes'];
      $old_closed =             $old['closed'];
      $old_assigned_personID =  $old['assigned_personID'];
      $old_history_due_date =   $old['history_due_date'];
      $old_category =           $old['category'];
      $old_subject =            $old['subject'];
      $timestamp =              get_timestamp();
      $PUsername =              get_userPUsername();

      // Get info to link to parent case
      $old_seq =                $old['seq'];
      $old_destinationID =      $old['destinationID'];
      $old_destinationType =    $old['destinationType'];

      switch ($bulk_update) {
        case true:
          $new_closed =
            ( isset($action_parameters['data']['closed']) &&
              isset($action_parameters['data']['closed_apply'])
            ?
              $action_parameters['data']['closed']
            : 0);
          $changed_assigned_personID =
            ( isset($action_parameters['data']['assigned_personID']) &&
              isset($action_parameters['data']['assigned_personID_apply'])) &&
              (int)$action_parameters['data']['assigned_personID'] != $old_assigned_personID;
          $changed_history_due_date =
            ( isset($action_parameters['data']['history_due_date']) &&
              isset($action_parameters['data']['history_due_date_apply'])) &&
              $action_parameters['data']['history_due_date'] != $old_history_due_date;
          $changed_category =
            ( isset($action_parameters['data']['category']) &&
              isset($action_parameters['data']['category_apply'])) &&
              $action_parameters['data']['category'] != $old_category;
          $changed_subject =
            ( isset($action_parameters['data']['subject']) &&
              isset($action_parameters['data']['subject_apply'])) &&
              $action_parameters['data']['subject'] != $old_subject;
        break;
        default:
          $new_closed =
            (isset($action_parameters['data']['closed']) ?
              $action_parameters['data']['closed']
            : 0);
          $changed_assigned_personID =  isset($action_parameters['data']['assigned_personID']) && (int)$action_parameters['data']['assigned_personID'] != $old_assigned_personID;
          $changed_history_due_date =   isset($action_parameters['data']['history_due_date'])  && $action_parameters['data']['history_due_date'] !=  $old_history_due_date;
          $changed_category =           isset($action_parameters['data']['category'])  && $action_parameters['data']['category'] !=  $old_category;
          $changed_subject =            isset($action_parameters['data']['subject'])  && $action_parameters['data']['subject'] !=  $old_subject;
        break;
      }
      $changed_closed =               $new_closed != $old_closed;

      if ($changed_closed && !$new_closed){
        $change_arr[] = "  Change: Opened task";
        $history_closed_date = '0000-00-00 00:00:00';
      }
      if ($changed_category) {
        $change_arr[] = "  Change: Changed Category to ".$action_parameters['data']['category'];
      }
      if ($changed_subject) {
        $change_arr[] = "  Change: Changed Subject to ".$action_parameters['data']['subject'];
      }
      if ($changed_history_due_date) {
        $change_arr[] = "  Change: Changed Due date to ".$action_parameters['data']['history_due_date'];
      }
      if ($changed_assigned_personID) {
        $Obj = new Person($action_parameters['data']['assigned_personID']);
        $PUsername = $Obj->get_name();
        $change_arr[] = "  Change: Assigned task to ".($PUsername ? $PUsername : '(none)');
      }
      if ($changed_closed && $new_closed){
        $change_arr[] = "  Change: Closed task";
        $history_closed_date = $timestamp;
      }
      if (!count($change_arr)) {
        $change_arr[] = "  Status: Saved task";
      }
      $ObjRC = new Report_Column();
      $title = "[CASE TASK ".$old_seq."] ";
      $changes = implode("\n",$change_arr);
      $notes = $title.$ObjRC->note_prepend($changes).$old_notes;

      $data = array();
      $_POST['notes'] = $notes;
      $data['notes'] = $notes;
      if ($changed_closed){
        $data['history_closed_date'] = $history_closed_date;
        $_POST['history_closed_date'] = $history_closed_date;
      }
      $this->update($data,false);
      switch ($old_destinationType) {
        case "cases":
          $Obj = new CRM_Case($old_destinationID);
          $old_case_notes_combined = $Obj->get_field('notes_combined');
          $case_notes_combined = $title.$ObjRC->note_prepend($changes).$old_case_notes_combined;
          $Obj->set_field('notes_combined',$case_notes_combined,false);
        break;
      }
    }
  }
  public function get_version(){
    return VERSION_CRM_CASE_TASK;
  }
}
?>