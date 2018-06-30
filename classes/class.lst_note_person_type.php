<?php
define('VERSION_LST_NOTE_PERSON_TYPE','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class lst_note_person_type extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_note_person_type','Note Type');
  }
  public function get_version(){
    return VERSION_LST_NOTE_PERSON_TYPE;
  }
}
?>