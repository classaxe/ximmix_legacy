<?php
define ("VERSION_PRODUCT_GROUPING_COLUMN","1.0.1");
/*
Version History:
  1.0.1 (2011-08-26)
    1) Changed references to Product_Category to Product_Grouping
  1.0.0 (2011-08-25)
    1) Initial release
*/

class Product_Grouping_Column extends Lst_Named_Type {
  static $types =       array();
  private $_columns =    array();

  function __construct($ID="") {
    parent::__construct($ID,'lst_product_grouping_columns','Product Grouping Column');
  }

  public function get_all(){
    if (count(Product_Grouping_Column::$types)) {
      return Product_Grouping_Column::$types;
    }
    $sql =
       "SELECT\n"
      ."  `value`    `field`,\n"
      ."  `custom_1` `heading`,\n"
      ."  `custom_2` `width`,\n"
      ."  `custom_3` `isNum`,\n"
      ."  `custom_4` `row`,\n"
      ."  `custom_5` `for_all`\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `listTypeID` = ".$this->_get_listTypeID()." AND\n"
      ."  `systemID` IN(1,".SYS_ID.")";
//    z($sql);
    Product_Grouping_Column::$types = $this->get_records_for_sql($sql);
    return Product_Grouping_Column::$types;
  }

  public function get_all_for_grouping($groupingID, $filtered=false){
    $Obj_PC =       new Product_Grouping($groupingID);
    $column_arr =   explode(",",$Obj_PC->get_field('columnList'));
    $options =      $this->get_all();
    foreach($column_arr as $column){
      foreach($options as $option){
        if ($option['field']==$column){
          if ($option['for_all'] || !$filtered){
            $this->_columns[] = $option;
          }
        }
      }
    }
    $this->_get_column_widths();
    return $this->_columns;
  }

  private function _get_column_widths(){
    $count_item_code_or_title = 0;
    foreach ($this->_columns as $column){
      switch($column['field']){
        case 'itemCode':
        case 'title':
          $count_item_code_or_title++;
        break;
      }
    }
    foreach ($this->_columns as &$column){
      switch($column['field']){
        case 'itemCode':
        case 'title':
          $column['width'] = ($count_item_code_or_title==2 ? '50%' : '100%');
        break;
        default:
          $column['width'].= "px";
        break;
      }
    }
  }

  public function get_version(){
    return VERSION_PRODUCT_GROUPING_COLUMN;
  }
}
?>