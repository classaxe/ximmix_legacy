<?php
define('VERSION_REPORT_SETTINGS','1.0.5');
/*
Version History:
  1.0.5 (2014-02-21)
    1) Report_Settings::delete_settings_for_filter() now removes filter settings
       record if there are no filters to show

  (Older version history in class.report_settings.txt)
*/
class Report_Settings extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, destinationID, destinationType, reportID, report_columns_csv, report_filters_csv, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("report_settings",$ID);
    $this->_set_has_groups(false);
    $this->_set_object_name('Report Settings');
  }

  function delete_settings_for_filter($record){
    $report_settings = $this->get_settings($record['reportID'], $record['destinationType'], $record['destinationID']);
    if ($report_settings){
      $report_settings_ID = $report_settings['ID'];
      $report_filters_csv = $report_settings['report_filters_csv'];
      $report_filters_arr = ($report_filters_csv ? explode(',',$report_filters_csv) : array());
      $report_filters_arr = array_diff($report_filters_arr,array($record['ID']));
      $report_filters_csv = implode(',',$report_filters_arr);
      $this->_set_ID($report_settings_ID);
      if ($report_filters_csv==''){
        return $this->delete();
      }
      return $this->set_field('report_filters_csv',$report_filters_csv);
    }
  }

  function assign_settings_for_filter($record){
    $report_settings = $this->get_settings($record['reportID'], $record['destinationType'], $record['destinationID']);
    $systemID = ($record['destinationType']=='global' ? 1 : SYS_ID);
    if ($report_settings){
      $report_settings_ID = $report_settings['ID'];
      $report_filters_csv = $report_settings['report_filters_csv'];
      $report_filters_arr = ($report_filters_csv ? explode(',',$report_filters_csv) : array());
      $report_filters_arr[] = $record['ID'];
      $report_filters_csv = implode(',',$report_filters_arr);
      $this->_set_ID($report_settings_ID);
      $this->set_field('systemID',$systemID);
      $this->set_field('report_filters_csv',$report_filters_csv);
      return $report_settings_ID;
    }
    $data = array(
      'systemID' =>           $systemID,
      'reportID' =>           $record['reportID'],
      'destinationType' =>    $record['destinationType'],
      'destinationID' =>      $record['destinationID'],
      'report_filters_csv' => $record['ID']
    );
    return $this->insert($data);
  }

  function get_settings_for_report($reportID){
    $personID = get_userID();
    $out = array(
      'global' => array(),
      'system' => array(),
      'person' => array()
    );
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `reportID`=".$reportID." AND\n"
      ."  ((`destinationType`='global') OR (`destinationType`='system' AND `destinationID`=".SYS_ID.")"
      .($personID ? " OR (`destinationType`='person' AND `destinationID`=".$personID.")" : "")
      .")\n";
    $records = $this->get_records_for_sql($sql);
    foreach ($records as $record){
      $out[$record['destinationType']] = array(
        'report_columns' => ($record['report_columns_csv'] ? explode(',',$record['report_columns_csv']) : array()),
        'report_filters' => ($record['report_filters_csv'] ? explode(',',$record['report_filters_csv']) : array())
      );
    }
    return $out;
  }

  function get_settings($reportID, $destinationType, $destinationID){
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `reportID`=".$reportID." AND\n"
      ."  `destinationType`='".$destinationType."' AND\n"
      ."  `destinationID`=".$destinationID;
    return $this->get_record_for_sql($sql);
  }


  public function get_version(){
    return VERSION_REPORT_SETTINGS;
  }
}
?>