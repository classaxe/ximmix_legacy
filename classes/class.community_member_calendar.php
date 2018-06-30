<?php
define('COMMUNITY_MEMBER_CALENDAR_VERSION','1.0.1');
/*
Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)
*/
/*
Version History:
  1.0.1 (2013-09-20)
    1) Community_Member_Calendar::_shared_source_link() now sets anchor to linked
       calendar for member who shared event on calendar
  1.0.0 (2013-07-24)
    1) Moved this class out of Community_Member class
*/

class Community_Member_Calendar extends Component_Calendar_Large{
  public function __construct(){
    parent::__construct();
    $this->_event_category_name =       'Community Posting Category';
    $this->_event_report_name =         'community_member.events';
    $this->_event_context_menu_name =   'module_cm_event';
  }

  protected function _setup_load_events(){
    $Obj_CME =                      new Community_Member_Event;
    $Obj_CME->memberID =            $this->memberID;
    $Obj_CME->partner_csv =         $this->partner_csv;
    $Obj_CME->communityID =         $this->communityID;
    $Obj_CME->community_record =    $this->community_record;
    $this->_arr_cal =               $Obj_CME->get_calendar_dates($this->_MM,$this->_YYYY,$this->memberID);
  }

  protected function _shared_source_link(){
    return Community_Member_Posting::BL_mini_shared_source_link($this,'#calendar');
  }

  public function get_version(){
    return COMMUNITY_MEMBER_CALENDAR_VERSION;
  }
}
?>