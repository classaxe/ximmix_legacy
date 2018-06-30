<?php
define('COMMUNITY_COMPONENT_CALENDAR_LARGE','1.0.1');
/*
Version History:
  1.0.1 (2013-07-23)
    1) Anchor for mini link changed to '#calendar'
  1.0.0 (2013-07-23)
    1) Moved Community_Component_Calendar_Large out of Community class file
*/


class Community_Component_Calendar_Large extends Component_Calendar_Large{
  public function __construct(){
    parent::__construct();
    $this->_event_category_name =       'Community Posting Category';
    $this->_event_report_name =         'community_member.events';
    $this->_event_context_menu_name =   'module_cm_event';
  }

  protected function _setup_load_events(){
    $Obj_Event =        new Community_Event;
    $Obj_Event->community_record = $this->community_record;
    $this->_arr_cal =   $Obj_Event->get_calendar_dates($this->_MM,$this->_YYYY,$this->_memberID);
  }

  protected function _shared_source_link(){
    return Community_Posting::BL_mini_shared_source_link($this,'#calendar');
  }

  public function get_version(){
    return COMMUNITY_COMPONENT_CALENDAR_LARGE;
  }
}

?>