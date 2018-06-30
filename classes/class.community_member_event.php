<?php
define('COMMUNITY_MEMBER_EVENT_VERSION','1.0.2');
/*
Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)
*/
/*
Version History:
  1.0.2 (2013-12-14)
    1) Added override for report form to use for 'Add New...' icon

  (Older version history in class.community_member_event.txt)
*/

class Community_Member_Event extends Event{
  public function __construct(){
    parent::__construct();
    $this->_set_object_name('Community Member Event');
    $this->_set_context_menu_ID('module_cm_event');
    $this->_set_edit_param('report','community_member.events');
  }

  protected function _get_records_get_sql(){
    return Community_Member_Posting::_get_records_get_sql($this);
  }

  protected function BL_category(){
    return Community_Posting::BL_category();
  }

  protected function BL_shared_source_link(){
    return Community_Member_Posting::BL_shared_source_link($this);
  }

  public function manage_recurrences(){
    if (get_var('command')=='report'){
      return draw_auto_report('community_member.event_recurrences',1);
    }
    if (!$selectID = get_var('selectID')) {
      return
         "<h3 style='margin:0.25em'>Recurrences for ".$this->_get_object_name()."</h3>"
        ."<p style='margin:0.25em'>No Recurrences - this ".$this->_get_object_name()." has not been saved yet.</p>";
    }
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `postings`\n"
      ."WHERE\n"
      ."  `postings`.`parentID` = ".$selectID;
    //print "<pre>$sql</pre>";
    if (!$row = $this->get_record_for_sql($sql)){
      return
         "<h3 style='margin:0.25em'>Recurrences for ".$this->_get_object_name()."</h3>"
        ."<p style='margin:0.25em'>Sorry - the related ".$this->_get_object_name()." cannot be found - perhaps it was deleted?</p>";
    }
    $isMASTERADMIN = get_person_permission("MASTERADMIN");
    return draw_auto_report('community_member.event_recurrences',1);
  }

  public function get_version(){
    return COMMUNITY_MEMBER_EVENT_VERSION;
  }
}
?>