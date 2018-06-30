<?php
define('VERSION_REPORT_DEFAULTS','1.0.3');
/*
Version History:
  1.0.3 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.report_defaults.txt)
*/
class Report_Defaults extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, personID, reportID, sortColumnID, sortColumnReverse, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("report_defaults",$ID);
    $this->_set_has_groups(false);
    $this->_set_object_name('Report Defaults');
  }

  function get_defaults($reportID,$reportSortBy){
    $out =
      array(
        'ID' =>false,
        'sortColumnID' => $reportSortBy,
        'sortColumnReverse' => 0
      );

    if (!$personID = get_userID()){
      return $out;
    }
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `sortColumnID`,\n"
      ."  `sortColumnReverse`\n"
      ."FROM\n"
      ."  `report_defaults`\n"
      ."WHERE\n"
      ."  `systemID` = ".SYS_ID." AND\n"
      ."  `reportID` = ".$reportID." AND\n"
      ."  `personID` = ".$personID;
    if (!$result = $this->get_record_for_sql($sql)) {
      return $out;
    }
    return $result;
  }

  function set_defaults($reportID,$save_default_columnID,$save_default_column_reverse){
    $data =
      array(
        'systemID' =>             SYS_ID,
        'personID' =>             get_userID(),
        'reportID' =>             $reportID,
        'sortColumnID' =>         $save_default_columnID,
        'sortColumnReverse' =>    $save_default_column_reverse
      );
    $this->update($data);  // Will insert if $this->ID is false
  }

  public function get_version(){
    return VERSION_REPORT_DEFAULTS;
  }
}
?>