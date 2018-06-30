<?php
define('VERSION_GROUP_MEMBER','1.0.5');
/*
Version History:
  1.0.5 (2014-06-22)
    1) Added permEMAILOPTIN to permArr

  (Older version history in class.group_member.txt)
*/
class Group_Member extends Record {
  public $permArr;
  // ************************************
  // * Constructor:                     *
  // ************************************
  public function __construct($ID="") {
    parent::__construct("group_members",$ID);
    $this->_set_object_name("Group Membership Record");
    $this->permArr =
      array(
        "permEDITOR",
        "permEMAILOPTIN",
        "permEMAILOPTOUT",
        "permEMAILRECIPIENT",
        "permVIEWER"
      );
  }
  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function on_action_set_group_membership_description($reveal_modification=false){
    $ID_arr = explode(',',str_replace(' ','',$this->_get_ID()));
    $Obj_Person = new Person;
    foreach ($ID_arr as $ID){
      $this->_set_ID($ID);
      $Obj_Person->_set_ID($this->get_field('personID'));
      $Obj_Person->set_groups_list_description($reveal_modification);
    }
  }


  public function get_version(){
    return VERSION_GROUP_MEMBER;
  }
}
?>