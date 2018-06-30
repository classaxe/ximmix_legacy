<?php
define('COMMUNITY_MEMBERSHIP_VERSION','1.0.4');
/* Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)


/*
Version History:
  1.0.4 (2012-11-10)
    1) Now implemented as a standard class library, not a module
  1.0.3 (2012-11-09)
    1) Now references 'community.membership' report, not 'module.community_membership'
       as before
  1.0.2 (2011-09-03)
    1) Changed object name to 'Community Membership Record' for clearer status
  1.0.1 (2010-11-06)
    1) Changes to eliminate deprecated function calls
  1.0.0 (2010-02-15)
*/

class Community_Membership extends Record {
  static $member_record = false;
  var $url;

  function __construct($ID="") {
    parent::__construct('community_membership',$ID);
    $this->_set_assign_type('Community Membership');
    $this->_set_object_name('Community Membership Record');
    $this->set_edit_params(
      array(
        'report' =>                 'community.membership',
        'report_rename' =>          false,
        'report_rename_label' =>    ''
      )
    );
  }

  function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip);
  }

  public function get_version(){
    return COMMUNITY_MEMBERSHIP_VERSION;
  }
}
?>