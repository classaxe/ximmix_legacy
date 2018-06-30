<?php
define('VERSION_FIELD_TEMPLATE','1.0.1');
/*
Version History:
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Field_Template extends Record{
  // ************************************
  // * Constructor:                     *
  // ************************************
  function __construct($ID="") {
    parent::__construct("field_templates",$ID);
    $this->_set_object_name('Field Template');
  }
  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }
  public function get_version(){
    return VERSION_FIELD_TEMPLATE;
  }
}
?>