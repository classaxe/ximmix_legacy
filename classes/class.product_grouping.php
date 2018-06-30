<?php
define('VERSION_PRODUCT_GROUPING','1.0.8');
/*
Version History:
  1.0.8 (2011-08-26)
    1) Renamed class to Product_Grouping and changed table name to improve consistency
    2) Changed references to `product`.`categoryID` to `product`.`groupingID`

  (Older version history in class.product_grouping.txt)
*/
class Product_Grouping extends Record {

  function __construct($ID=""){
    parent::__construct("product_grouping",$ID);
    $this->_set_name_field('name');
    $this->_set_object_name("Product Grouping");
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  function export_sql($targetID,$show_fields) {
    $header =
       "Selected ".$this->_get_object_name().$this->plural($targetID)."\n"
      ."(with related Products, Actions and Group Assigments)";
    $extra_delete =
       "DELETE FROM `product`                WHERE `groupingID` IN (".$targetID.");\n"
      ."DELETE FROM `group_assign`           WHERE `assign_type` = 'product' AND `assignID` IN(SELECT `ID` FROM `product` WHERE `groupingID` IN(".$targetID."));\n"
      ."DELETE FROM `action`                 WHERE `sourceType` = 'product' AND `sourceID` IN (SELECT `ID` FROM `product` WHERE `groupingID` IN(".$targetID."));";
    $Obj = new Backup;
    $extra_select =
       $Obj->db_export_sql_query("`product`               ","SELECT * FROM `product` WHERE `groupingID` IN(".$targetID.");",$show_fields)
      .$Obj->db_export_sql_query("`group_assign`          ","SELECT * FROM `group_assign`  WHERE `assign_type` = 'product' AND `assignID` IN(SELECT `ID` FROM `product` WHERE `groupingID` IN(".$targetID."));",$show_fields)
      .$Obj->db_export_sql_query("`action`                ","SELECT * FROM `action`  WHERE `sourceType` = 'product' AND `sourceID` IN (SELECT `ID` FROM `product` WHERE `groupingID` IN(".$targetID.")) ORDER BY `seq`",$show_fields)
      ;
    return  $this->sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function manage_products(){
    if (get_var('command')=='report'){
      return draw_auto_report('products_for_product_grouping',1);
    }
    $out = "<h3 style='margin:0.25em'>Poducts belonging to this ".$this->_get_object_name()."</h3>";
    if (!get_var('selectID')) {
      $out.="<p style='margin:0.25em'>No associated Products - this ".$this->_get_object_name()." has not been saved yet.</p>";
    }
    else {
      $out.= draw_auto_report('products_for_product_grouping',1);
    }
    return $out;
  }

  public function get_version(){
    return VERSION_PRODUCT_GROUPING;
  }
}
?>