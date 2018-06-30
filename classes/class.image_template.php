<?php
define('VERSION_IMAGE_TEMPLATE','1.0.2');
/*
Version History:
  1.0.2 (2011-08-24)
    1) Added handle_report_copy() to implement renaming of cloned item
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Image_Template extends Posting {

  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_type('image template');
    $this->_set_assign_type('image template');
    $this->_set_object_name('Image Template');
    $this->_set_has_activity(false);
    $this->_set_has_categories(false);
    $this->_set_has_groups(false);
    $this->_set_has_keywords(false);
    $this->_set_message_associated('');
  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_IMAGE_TEMPLATE;
  }
}
?>