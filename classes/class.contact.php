<?php
define('VERSION_CONTACT','1.0.5');
/*
Version History:
  1.0.5 (2013-11-07)
    1) Added edit_parameters to allow this type to be iewed in a listings panel

  (Older version history in class.contact.txt)
*/
class Contact extends Person {
  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_object_name('Contact');
    $this->_set_assign_type('contact');
    $this->_set_type('contact');
    $this->set_edit_params(
      array(
        'report' =>                 'contact',
        'report_rename' =>          false,
        'report_rename_label' =>    ''
      )
    );
    $this->_cp_vars_listings['block_layout']['default'] = 'User';
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  public function get_version(){
    return VERSION_CONTACT;
  }
}
?>