<?php
define('COMMUNITY_EVENT','1.0.2');
/*
Version History:
  1.0.2 (2013-12-14)
    1) Added override for report form to use for 'Add New...' icon

  (Older version history in class.community_event.txt)
*/


class Community_Event extends Event{
  public function __construct(){
    parent::__construct();
    $this->_set_context_menu_ID('module_cm_event');
    $this->_show_latest_for_each_member = false;
    $this->_set_edit_param('report','community_member.events');
  }

  protected function _get_records_get_sql(){
    return Community_Posting::_get_records_get_sql($this);
  }

  protected function BL_shared_source_link(){
    return Community_Posting::BL_shared_source_link($this,'#calendar');
  }

  protected function BL_category(){
    return Community_Posting::BL_category();
  }

  public function get_version(){
    return COMMUNITY_EVENT;
  }
}

?>