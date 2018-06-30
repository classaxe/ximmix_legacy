<?php
define('VERSION_PUSH_PRODUCT_ASSIGN','1.0.1');
/*
Version History:
  1.0.1 (2011-11-21)
    1) Push_Product_Assign::set_for_assignment() - bug fix
  1.0.0 (2011-11-18)
    Initial release
*/
class Push_Product_Assign extends Record {

  public function __construct($ID=""){
    parent::__construct("push_product_assign",$ID);
    $this->_set_object_name('Push Product Assign');
  }

  public function delete_for_assignment($assign_type,$assignID) {
    $sql =
       "DELETE FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `assign_type` = \"".$assign_type."\" AND\n"
      ."  `assignID` IN(".$assignID.")";
//    z($sql);
    $this->do_sql_query($sql);
  }

  public function set_for_assignment($assign_type,$assignID,$csv,$systemID) {
    $this->delete_for_assignment($assign_type,$assignID);
    if (!$csv) {
      return;
    }
    $arr = explode(",",str_replace(' ','',$csv));
    $Obj = new Product;
    foreach ($arr as $productID) {
      $data =
        array(
          'assign_type' =>$assign_type,
          'assignID' =>   $assignID,
          'productID' =>  $productID,
          'systemID' =>   $systemID
        );
      $this->insert($data);
    }
  }

  public function get_version(){
    return VERSION_PUSH_PRODUCT_ASSIGN;
  }
}
?>