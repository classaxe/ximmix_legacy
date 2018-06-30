<?php
define('VERSION_ORDERITEM','1.0.14');
/*
Version History:
  1.0.14 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.orderitem.txt)
*/
class OrderItem extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, orderID, productID, cost, creditMemo, creditMemoID, custom_1, custom_2, custom_3, custom_4, custom_5, net, price, price_non_refundable, quantity, related_object, related_objectID, tax_regimeID, tax1_apply, tax1_cost, tax1_name, tax1_rate, tax2_apply, tax2_cost, tax2_name, tax2_rate, tax3_apply, tax3_cost, tax3_name, tax3_rate, tax4_apply, tax4_cost, tax4_name, tax4_rate, tax5_apply, tax5_cost, tax5_name, tax5_rate, tax6_apply, tax6_cost, tax6_name, tax6_rate, tax7_apply, tax7_cost, tax7_name, tax7_rate, tax8_apply, tax8_cost, tax8_name, tax8_rate, tax9_apply, tax9_cost, tax9_name, tax9_rate, tax10_apply, tax10_cost, tax10_name, tax10_rate, tax11_apply, tax11_cost, tax11_name, tax11_rate, tax12_apply, tax12_cost, tax12_name, tax12_rate, tax13_apply, tax13_cost, tax13_name, tax13_rate, tax14_apply, tax14_cost, tax14_name, tax14_rate, tax15_apply, tax15_cost, tax15_name, tax15_rate, tax16_apply, tax16_cost, tax16_name, tax16_rate, tax17_apply, tax17_cost, tax17_name, tax17_rate, tax18_apply, tax18_cost, tax18_name, tax18_rate, tax19_apply, tax19_cost, tax19_name, tax19_rate, tax20_apply, tax20_cost, tax20_name, tax20_rate, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID=""){
    parent::__construct("order_items",$ID);
    $this->_set_object_name("Ordered Item");
  }

  function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  static function get_costs($data,$BCountryID,$BSpID){
    $Obj_Tax_Regime = new Tax_Regime($data['tax_regimeID']);
    return $Obj_Tax_Regime->get_costs($data,$BCountryID,$BSpID);
  }

  function get_quantity_refundable($data) {
    $sql =
       "SELECT\n"
      ."  SUM(`quantity`) AS `qty`,\n"
      ."  SUM(IF(`creditMemo`=1 AND `creditMemoID`=0,`quantity`,0)) AS `pending`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `orderID` = ".$data['orderID']." AND\n"
      ."  `productID` = ".$data['productID']." AND\n"
      ."  `related_object` = \"".$data['related_object']."\" AND\n"
      ."  `related_objectID` = \"".$data['related_objectID']."\"";
    $record = $this->get_record_for_sql($sql);
    if ((int)$record['pending']!=0) {
      return 0;
    }
    return (int)$record['qty'];
  }

  function get_payment_details(){
    if (!$this->_get_ID()) {
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `orders`.`paymentMethod`,\n"
      ."  `orders`.`paymentStatus`,\n"
      ."  `order_items`.`ID`,\n"
      ."  `order_items`.`quantity`,\n"
      ."  `order_items`.`orderID`,\n"
      ."  `order_items`.`price`,\n"
      ."  `order_items`.`cost`\n"
      ."FROM\n"
      ."  `order_items`\n"
      ."INNER JOIN `orders` ON\n"
      ."  `order_items`.`orderID` = `orders`.`ID`\n"
      ."WHERE\n"
      ."  `order_items`.`ID` = ".$this->_get_ID();
    return $this->get_record_for_sql($sql);
  }

  function refund_flag_clear() {
    $record = $this->get_record();
    if ($record['creditMemo'] && !$record['creditMemoID']){
      $this->delete();
    }
  }

  function refund_flag_set($quantity){
    if ((int)$quantity<1) {
      return;
    }
    $data = $this->get_record();
    $available = $this->get_quantity_refundable($data);
    if ($available>0) {
      if ((int)$quantity > (int)$available){
        $quantity = (int)$available;
      }
      unset($data['ID']);
      unset($data['history_created_by']);
      unset($data['history_created_date']);
      unset($data['history_created_IP']);
      unset($data['history_modified_by']);
      unset($data['history_modified_date']);
      unset($data['history_modified_IP']);
      $data['creditMemo'] =  1;
      $data['quantity'] =    $quantity;
      $this->insert($data);
    }
  }

  public function get_version(){
    return VERSION_ORDERITEM;
  }
}
?>