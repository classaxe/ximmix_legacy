<?php
define('VERSION_Report_Filter_Criteria','1.0.2');
/*
Version History:
  1.0.2 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.1 (2009-12-27)
    1) Changed reference to parent constructor
  1.0.0 (2009-07-02)
    Initial release
*/
class Report_Filter_Criteria extends Record {

  function __construct($ID="") {
    parent::__construct("report_filter_criteria",$ID);
    $this->_set_assign_type('Report Filter Criteria');
    $this->_set_has_groups(true);
  }

  public function get_version(){
    return VERSION_Report_Filter_Criteria;
  }
}
?>