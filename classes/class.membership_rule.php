<?php
define('VERSION_MEMBERSHIP_RULE','1.0.2');
/*
Version History:
  1.0.2 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.1 (2009-12-27)
    1) Changed reference to parent constructor
  1.0.0 (2009-07-02)
    Initial release
*/
class Membership_Rule extends Record {
  function __construct($ID=""){
    parent::__construct("membership_rule",$ID);
    $this->_set_assign_type('membership_rule');
    $this->_set_object_name('Membership Rule');
    $this->_set_has_groups(true);
  }

  function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  function get_rules($systemID=SYS_ID){
  // Fetches all rules for the system, filters to include only rules pertaining to
  // signed in user.
  // If 'stop_when_applied' is set, doesn't include any rules after current one.
    $out = array();
    if (!$personID = get_userID()) {
      return $out;
    }
    $sql =
       "SELECT\n"
      ."  IF (\n"
      ."    `person`.`active_date_to`!='0000:00:00' AND\n"
      ."     NOW() <`person`.`active_date_to` AND\n"
      ."     NOW() >= date_sub(`person`.`active_date_to`,INTERVAL `renewal_pre_expiry_notice_days` DAY)\n"
      ."    ,DATEDIFF(`person`.`active_date_to`,NOW()),0\n"
      ."  ) AS `renewal_pre_expiry_notice_days_remaining`,\n"
      ."  IF (\n"
      ."    `person`.`active_date_to`!='0000:00:00' AND\n"
      ."     NOW() >=`person`.`active_date_to` AND\n"
      ."     NOW() < date_add(`person`.`active_date_to`,INTERVAL `renewal_post_expiry_notice_days` DAY)\n"
      ."    ,DATEDIFF(date_add(`person`.`active_date_to`,INTERVAL `renewal_post_expiry_notice_days` DAY),NOW()),0\n"
      ."  ) AS `renewal_post_expiry_notice_days_remaining`,\n"
      ."  `".$this->table."`.*,\n"
      ."  `person`.`active_date_to` AS `renewal_date`\n"
      ."FROM\n"
      ."  `".$this->table."`\n"
      ."INNER JOIN `person` ON\n"
      ."  `person`.`ID` = ".get_userID()." AND\n"
      ."  `person`.`systemID` IN (1,`".$this->table."`.`systemID`)\n"
      ."WHERE\n"
      ."  `".$this->table."`.`systemID` = $systemID\n"
      ."ORDER BY\n"
      ."  `seq`";
//    z($sql);
    $records = $this->get_records_for_sql($sql);
    foreach ($records as $record) {
      if ($this->is_visible($record)) {
        $out[] = $record;
        if ($record['stop_when_applied']) {
          break;
        }
      }
    }
    return $out;
  }

  function get_renewal_notice($systemID=SYS_ID) {
    $records = $this->get_rules($systemID);
    foreach ($records as $record) {
      if ($record['renewal_pre_expiry_notice_days_remaining']) {
        return $record['renewal_pre_expiry_notice_text'];
      }
      if ($record['renewal_post_expiry_notice_days_remaining']) {
        return $record['renewal_post_expiry_notice_text'];
      }
    }
    return "";
  }

  function get_renewal_date($systemID=SYS_ID) {
    $records = $this->get_rules($systemID);
    foreach ($records as $record) {
      if ($record['renewal_pre_expiry_notice_days_remaining']) {
        return $record['renewal_date'];
      }
      if ($record['renewal_post_expiry_notice_days_remaining']) {
        return $record['renewal_date'];
      }
    }
    return 0;
  }

  function get_renewal_days($systemID=SYS_ID) {
    $records = $this->get_rules($systemID);
    foreach ($records as $record) {
      if ($record['renewal_pre_expiry_notice_days_remaining']) {
        return $record['renewal_pre_expiry_notice_days_remaining'];
      }
      if ($record['renewal_post_expiry_notice_days_remaining']) {
        return $record['renewal_post_expiry_notice_days_remaining'];
      }
    }
    return 0;
  }

  public function get_version(){
    return VERSION_MEMBERSHIP_RULE;
  }
}
?>