<?php
define('VERSION_SURVEY_RESPONSE','1.0.1');
/*
Version History:
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-021)
    Initial release
*/
class Survey_response extends Posting {
  public function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_has_actions(false);
    $this->_set_has_groups(false);
    $this->_set_type('survey response');
    $this->_set_assign_type('survey response');
    $this->_set_object_name('Survey Response');
    $this->_set_message_associated('');
  }

  public function get_version(){
    return VERSION_SURVEY_RESPONSE;
  }
}
?>