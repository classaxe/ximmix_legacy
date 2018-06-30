<?php
define('VERSION_CRM_CASE','1.0.12');
/*
Version History:
  1.0.12 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.crm_case.txt)
*/
class CRM_Case extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, assigned_personID, category, closed, custom_1, custom_2, custom_3, custom_4, custom_5, custom_6, custom_7, custom_8, custom_9, custom_10, date_1, date_2, date_3, date_4, notes, notes_combined, related_orderID, related_personID, priority, status, subject, XML_data, history_closed_date, history_created_by, history_created_date, history_created_IP, history_due_date, history_modified_by, history_modified_date, history_modified_IP';

  // Note: cannot call these Case - that's a reserved word in PHP
  function __construct($ID="") {
    parent::__construct("cases",$ID);
    $this->_set_object_name('Case');
    $this->_set_name_field('subject');
    $this->_set_message_associated('and associated tasks have');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new subject'
      )
    );
  }

  function cases_for_person(){
    return draw_auto_report('cases_assigned_to_person',1);
  }

  function copy($new_name=false,$new_systemID=false,$new_date=true) {
    $newID = parent::copy($new_name,$new_systemID,$new_date);
    $Obj_CRM_Case_task =  new CRM_Case_task;
    $tasks = $this->get_tasks();
    foreach ($tasks as $data) {
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
      $data['destinationID'] = $newID;
      if ($new_systemID) {
        $data['systemID'] = $new_systemID;
      }
      $Obj_CRM_Case_task->insert($data);
    }
    return $newID;
  }

  // ************************************
  // * METHOD: delete()                 *
  // ************************************
  function delete() {
    $sql =
       "DELETE FROM\n"
      ."  `case_tasks`\n"
      ."WHERE\n"
      ."  `destinationType` = 'cases' AND\n"
      ."  `destinationID` IN(".$this->_get_ID().")";
    $this->do_sql_query($sql);
    parent::delete();
  }
  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with related Tasks";
    $extra_delete = "DELETE FROM `case_tasks`             WHERE `destinationType`='cases' AND `destinationID` IN (".$targetID.");\n";
    $Obj = new Backup;
    $extra_select = $Obj->db_export_sql_query("`case_tasks`            ","SELECT * FROM `case_tasks` WHERE `destinationType`='cases' AND `destinationID` IN (".$targetID.") ORDER BY `destinationID`,`seq`",$show_fields)."\n";
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }
  function get_tasks() {
    $sql =
       "SELECT * FROM\n"
      ."  `case_tasks`\n"
      ."WHERE\n"
      ."  `destinationType` = 'cases' AND\n"
      ."  `destinationID` IN(".$this->_get_ID().")";
    return $this->get_records_for_sql($sql);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function on_update() {
    global $action_parameters;
    $ID_arr = explode(",",$action_parameters['triggerID']);
    $bulk_update = (isset($action_parameters['data']['bulk_update']) && $action_parameters['data']['bulk_update']==1 ? true : false);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $change_arr =             array();
      $old =                    $this->get_record(false);
      $old_notes =              $old['notes'];
      $old_notes_combined =     $old['notes_combined'];
      $old_closed =             $old['closed'];
      $old_assigned_personID =  $old['assigned_personID'];
      $old_related_personID =   $old['related_personID'];
      $old_history_due_date =   $old['history_due_date'];
      $old_category =           $old['category'];
      $old_subject =            $old['subject'];
      $timestamp =              get_timestamp();
      $PUsername =              get_userPUsername();
      switch ($bulk_update) {
        case true:
          $changed_closed =
            ( isset($action_parameters['data']['closed_apply'])) &&
              (isset($action_parameters['data']['closed']) ? 1 : 0)!= $old_closed;
          $changed_assigned_personID =
            ( isset($action_parameters['data']['assigned_personID']) &&
              isset($action_parameters['data']['assigned_personID_apply'])) &&
              (int)$action_parameters['data']['assigned_personID'] != $old_assigned_personID;
          $changed_related_personID =
            ( isset($action_parameters['data']['related_personID']) &&
              isset($action_parameters['data']['related_personID_apply'])) &&
              (int)$action_parameters['data']['related_personID'] != $old_related_personID;
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
          if (isset($action_parameters['data']['ID'])){
            // This is a single-record form post:
            $changed_closed =           (isset($action_parameters['data']['closed']) ? 1 :0)!=$old_closed;
          }
          else {
            // This is a single-record report-click operation:
            $changed_closed =           isset($action_parameters['data']['closed']) ? $action_parameters['data']['closed']!=$old_closed : false;
          }
          $changed_assigned_personID =  isset($action_parameters['data']['assigned_personID']) && (int)$action_parameters['data']['assigned_personID'] != $old_assigned_personID;
          $changed_related_personID =   isset($action_parameters['data']['related_personID'])  && (int)$action_parameters['data']['related_personID'] !=  $old_related_personID;
          $changed_history_due_date =   isset($action_parameters['data']['history_due_date'])  && $action_parameters['data']['history_due_date'] !=  $old_history_due_date;
          $changed_category =           isset($action_parameters['data']['category'])  && $action_parameters['data']['category'] !=  $old_category;
          $changed_subject =            isset($action_parameters['data']['subject'])  && $action_parameters['data']['subject'] !=  $old_subject;
        break;
      }
      if ($changed_closed && (!isset($action_parameters['data']['closed']) || $action_parameters['data']['closed']==0)){
        $change_arr[] = "  Change: Opened case";
        $history_closed_date = '0000-00-00 00:00:00';
      }
      if ($changed_related_personID) {
        $Obj_Person =   new Person($action_parameters['data']['related_personID']);
        $PUsername =    $Obj_Person->get_name();
        $change_arr[] = "  Change: Case now relates to ".($PUsername ? $PUsername : '(none)');
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
        $Obj_Person =   new Person($action_parameters['data']['assigned_personID']);
        $PUsername =    $Obj_Person->get_name();
        $change_arr[] = "  Change: Assigned case to ".($PUsername ? $PUsername : '(none)');
      }
      if ($changed_closed && isset($action_parameters['data']['closed']) && $action_parameters['data']['closed']==1){
        $change_arr[] = "  Change: Closed case";
        $history_closed_date = $timestamp;
      }
      if (!count($change_arr)) {
        $change_arr[] = "  Status: Saved case";
      }
      $Obj_RC = new Report_Column;
      $title = "[CASE] ";
      $notes = $title.$Obj_RC->note_prepend(implode("\n",$change_arr)).$old_notes;
      $notes_combined = $title.$Obj_RC->note_prepend(implode("\n",$change_arr)).$old_notes_combined;
      $data = array();
      $_POST['notes'] = $notes;
      $data['notes'] = $notes;
      $data['notes_combined'] = $notes_combined;
      if ($changed_closed){
        $data['history_closed_date'] = $history_closed_date;
        $_POST['history_closed_date'] = $history_closed_date;
      }
      $this->update($data,false);
    }
  }

  public function manage_tasks(){
    if (get_var('command')=='report'){
      return draw_auto_report('tasks_for_case',2);
    }
    $out = "<h3 style='margin:0.25em'>Tasks for this ".$this->_get_object_name()."</h3>";
    if (!get_var('selectID')) {
      $out.="<p style='margin:0.25em'>There are no tasks - this ".$this->_get_object_name()." has not been saved yet.</p>";
    }
    else {
      $out.= draw_auto_report('tasks_for_case',2);
    }
    return $out;
  }

  function update($data,$validate=true) {
//    print"<pre>";print_r($data);print "</pre>";
    parent::update($data,$validate);
  }
  public function get_version(){
    return VERSION_CRM_CASE;
  }
}
?>