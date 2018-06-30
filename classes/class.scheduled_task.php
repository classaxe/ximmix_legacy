<?php
define('VERSION_SCHEDULED_TASK','1.0.4');
/*
Version History:
  1.0.4 (2014-01-28)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.scheduled_task.txt)
*/
class Scheduled_Task extends Record{
  const fields = 'ID, archive, archiveID, deleted, enabled, systemID, description, componentID, at_YYYY, at_MM, at_DD, at_hour, at_min, at_DOW, at_WOM, date_last_run, last_result, repeats_remaining, running, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  private $_tasks;
  private $_current_task;

  public function __construct($ID="") {
    parent::__construct("scheduled_task",$ID);
    $this->_set_object_name('Scheduled Task');
    $this->_set_name_field('tag');
    $this->set_edit_params(
      array(
        'report_rename' =>          false
      )
    );
  }

  public function _get_current_task()               { return $this->_current_task; }
  public function _get_tasks()                      { return $this->_tasks; }
  public function _set_current_task($value)         { $this->_current_task = $value; }
  public function _set_tasks($value)                { $this->_tasks = $value; }

  function all_tasks_load(){
    sscanf(
      get_timestamp_extended(),
      '%4s-%2s-%2s %2s:%2s:%2s %s %s',
      $now_YYYY, $now_MM, $now_DD, $now_hour, $now_min, $now_sec, $now_DOW, $now_WOM
    );
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` IN(1,".SYS_ID.") AND\n"
      ."  `repeats_remaining` !='0' AND\n"
      ."  (`at_YYYY`='*' OR `at_YYYY` LIKE '%".$now_YYYY."%') AND\n"
      ."  (`at_MM`='*'   OR `at_MM`   LIKE '%".$now_MM."%') AND\n"
      ."  (`at_DD`='*'   OR `at_DD`   LIKE '%".$now_DD."%') AND\n"
      ."  (`at_hour`='*' OR `at_hour` LIKE '%".$now_hour."%') AND\n"
      ."  (`at_min`='*'  OR `at_min`  LIKE '%".$now_min."%') AND\n"
      ."  (`at_DOW`='*'  OR `at_DOW`  LIKE '%".$now_DOW."%') AND\n"
      ."  (`at_WOM`='*'  OR `at_WOM`  LIKE '%".$now_WOM."%')";
    $this->_tasks = $this->get_records_for_sql($sql);
  }

  function all_tasks_run(){
    foreach ($this->_tasks as $task){
      if ($task['enabled']=='1' && $task['running']=='0' && $task['repeats_remaining']!='0'){
        $this->_set_current_task($task);
        $this->_current_task_lock();
        $this->_current_task_run();
        $this->_current_task_unlock();
      }
    }
  }

  private function _current_task_lock(){
    $now =  get_timestamp();
    $task = $this->_get_current_task();
    if ($task['repeats_remaining']=='*'){
      $data = array(
        'date_last_run' =>  $now,
        'running' =>        1
      );
    }
    else{
      $data = array(
        'date_last_run' =>      $now,
        'repeats_remaining' =>  (int)$task['repeats_remaining'] -1,
        'running' =>            1
      );
    }
    $this->_set_ID($task['ID']);
    $this->update($data,true,false);
  }

  private function _current_task_run(){
    $task = $this->_get_current_task();
    $Obj_Component = new Component($task['componentID']);
    if (!$Obj_Component->exists()) {
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','component_execute','No such Component as '.$task['componentID']);
      return false;
    }
    $result = $Obj_Component->execute();
    $task = $this->_get_current_task();
    $this->_set_ID($task['ID']);
    $this->set_field('last_result',$result,true,false);
  }

  private function _current_task_unlock(){
    $task = $this->_get_current_task();
    $this->_set_ID($task['ID']);
    $this->set_field('running',0,true,false);
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip);
  }

  public function get_version(){
    return VERSION_SCHEDULED_TASK;
  }
}
?>