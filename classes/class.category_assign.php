<?php
define('VERSION_CATEGORY_ASSIGN','1.0.4');
/*
Version History:
  1.0.4 (2011-11-18)
    1) Category_Assign::delete_for_assignment() now uses method to get table name
  1.0.3 (2011-05-18)
    1) Slight tidy up of Category_Assign::set_for_assignment()
  1.0.2 (2010-10-19)
    1) Cagetory_Assign::set_for_assignment() now calls insert() methods
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Category_Assign extends Record {
  function __construct($ID=""){
    parent::__construct("category_assign",$ID);
    $this->_set_object_name('Category Assign');
  }

  function delete_for_assignment($assign_type,$assignID) {
    $sql =
       "DELETE FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `assign_type` = \"".$assign_type."\" AND\n"
      ."  `assignID` IN(".$assignID.")";
//    z($sql);
    $this->do_sql_query($sql);
  }

  function set_for_assignment($assign_type,$assignID,$csv,$systemID) {
    $this->delete_for_assignment($assign_type,$assignID);
    if (!$csv) {
      return;
    }
    $arr = explode(",",$csv);
    $Obj = new Category;
    foreach ($arr as $category) {
      $categoryID = $Obj->get_ID_by_name(trim($category),"1,".$systemID);
      if ($categoryID) {
        $data =
          array(
            'assign_type' =>$assign_type,
            'assignID' =>   $assignID,
            'categoryID' => $categoryID,
            'systemID' =>   $systemID
          );
//        y($data);
        $this->insert($data);
      }
    }
  }

  public function get_version(){
    return VERSION_CATEGORY_ASSIGN;
  }
}
?>