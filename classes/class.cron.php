<?php
define('VERSION_CRON','1.0.10');
/*
Version History:
  1.0.10 (2014-02-21)
    1) CRON::heartbeat_actions() now includes queued map updates
    2) Bug fix for CRON::heartbeat() to correctly prevent a second thread from being
       activated within 55 seconds of a previous activation

  (Older version history in class.cron.txt)
*/
class CRON extends Record {
  public static function heartbeat(){
    global $system_vars;
    $system_vars =      get_system_vars();
    $last_heartbeat =   $system_vars['cron_job_heartbeat_last_run'];
    sscanf(
      $last_heartbeat,
      "%4s-%2s-%2s %2s:%2s:%2s",
      $now_YYYY, $now_MM, $now_DD, $now_hh, $now_mm, $now_ss
    );
    $heartbeat = mktime($now_hh, $now_mm, $now_ss, $now_MM, $now_DD, $now_YYYY);
    $diff = (time()-$heartbeat);
    if ($diff>50){
      CRON::heartbeat_actions();
    }
    if (get_var('mem')){
      mem('in heartbeat()');
      y(mem());
    }
    die('I am alive!');
  }

  public static function heartbeat_actions(){
    CRON::_heartbeat_scheduled_tasks();
    CRON::_heartbeat_notifications();
    CRON::_heartbeat_maps_update_pending();
    CRON::_heartbeat_update_timestamps();
  }

  protected static function _heartbeat_maps_update_pending(){
    Google_Map::on_schedule_update_pending();
  }

  protected static function _heartbeat_notifications(){
    $Obj_N = new Notification;
    $Obj_N->notify_all();
  }

  protected static function _heartbeat_scheduled_tasks(){
    $Obj_ST = new Scheduled_Task;
    $Obj_ST->all_tasks_load();
    $Obj_ST->all_tasks_run();
  }

  protected static function _heartbeat_update_timestamps(){
    $Obj_S = new System;
    $Obj_S->set_field_for_all('cron_job_heartbeat_last_run',get_timestamp());
  }

  public function get_version(){
    return VERSION_CRON;
  }
}
?>