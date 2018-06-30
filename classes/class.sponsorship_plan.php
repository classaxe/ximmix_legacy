<?php
define('SPONSORSHIP_PLAN_VERSION','1.0.4');
/*
Version History:
  1.0.4 (2012-11-22)
    1) Sponsorship_Plan constructor now sets subtype to 'sponsorship-plan' but
       leaves type as determined by the parent, in this case 'gallery-album'

  (Older version history in class.sponsorship_plan.txt)
*/

class Sponsorship_Plan extends Gallery_Album {

  public function __construct($ID='',$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_subtype('sponsorship-plan');
    $this->_set_assign_type('sponsorship-plan');
    $this->_set_object_name('Sponsorship Plan');
    $this->_set_has_activity(false);
    $this->_set_has_categories(false);
    $this->_set_has_groups(false);
    $this->_set_has_keywords(false);
    $this->_set_message_associated('');
    $this->set_edit_params(
      array(
        'report' =>                 'sponsorship_plan',
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  protected function _get_records_available(){
    $records = $this->_get_records_records;
    $this->_get_records_records = array();
    foreach ($records as $record) {
      if ($this->is_enabled($record)) {
        $this->_get_records_records[] = $record;
      }
    }
  }

  protected function _get_records_sort_records(){
    $order_arr = array(array('xml:cost','d'));
    $this->_get_records_records = $this->sort_records($this->_get_records_records, $order_arr);
  }


  function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return SPONSORSHIP_PLAN_VERSION;
  }
}
?>