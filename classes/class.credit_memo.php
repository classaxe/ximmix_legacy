<?php
define('VERSION_CREDIT_MEMO','1.0.11');
/*
Version History:
  1.0.11 (2011-08-26)
    1) Changes to Credit_Memo::draw_items() for renaming of product_category to
       product_grouping table

  (Older version history in class.credit_memo.txt)
*/
class Credit_Memo extends Order {

  function __construct($ID="") {
    parent::__construct($ID);
    $this->_set_object_name('Credit Memo');
    $this->_set_message_associated('and associated items have');
  }

  function delete(){
    $sql =
       "DELETE FROM\n"
      ."  `order_items`\n"
      ."WHERE\n"
      ." `creditMemoID` IN(".$this->_get_ID().")";
    $this->do_sql_query($sql);
    parent::delete();
  }

  function detail($instance='',$args=array(), $disable_params=false) {
    global $selectID, $filterField, $filterExact, $filterValue;
    global $sortBy, $print;
    $ident =            "credit_memo_detail";
    $parameter_spec =   array(
      'billing' =>                  array('default'=>'1',    'hint'=>'0|1'),
      'billing_expanded' =>         array('default'=>'1',    'hint'=>'0|1'),
      'changes' =>                  array('default'=>'1',    'hint'=>'0|1'),
      'changes_expanded' =>         array('default'=>'1',    'hint'=>'0|1'),
      'customer_notes' =>           array('default'=>'1',    'hint'=>'0|1'),
      'customer_notes_expanded' =>  array('default'=>'1',    'hint'=>'0|1'),
      'headers' =>                  array('default'=>'1',    'hint'=>'0|1'),
      'items' =>                    array('default'=>'1',    'hint'=>'0|1'),
      'items_expanded' =>           array('default'=>'1',    'hint'=>'0|1'),
      'person' =>                   array('default'=>'1',    'hint'=>'0|1'),
      'person_expanded' =>          array('default'=>'1',    'hint'=>'0|1'),
      'status' =>                   array('default'=>'1',    'hint'=>'0|1'),
      'status_expanded' =>          array('default'=>'1',    'hint'=>'0|1'),
      'title' =>                    array('default'=>'Details for Credit Memo Number',    'hint'=>'Custom title')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if (!$this->exists()) {
      $out.= "<h1>No such order as ".$this->_get_ID()."</h1>";
      return $out;
    }
    $Obj_Report =   new Report;
    $record = $this->get_record();
    $out.=
        draw_form_field('ID',$this->_get_ID(),'hidden')
       ."<h3 class='fl margin_none padding_none'>"
       .$cp['title']." ".$this->_get_ID()
       ." (".$record['credit_memo_status'].")</h3>"
      .($print==1 || $print==2 ? "<div class='fr'>".HTML::draw_icon('print',true)."</div>\n" : "")
      ."<div class='clr_b'></div>";
// Status:
    if ($cp['status']) {
      $old_selectID =   $selectID;
      $selectID =       $this->_get_ID();
      $div = "div_status_".$this->_get_ID();
      $out.=
         draw_hide_show($div,"Credit Memo Status",$cp['status_expanded'])
        .draw_auto_report('credit_memo_status',0)
        ."</div>";
      $selectID =       $old_selectID;
    }
// Notes for Customer:
    if ($cp['customer_notes']) {
      $notes = $this->get_field('credit_memo_notes_customer');
      $div = "div_customer_notes_".$this->_get_ID();
      $out.=
         draw_hide_show($div,"Notes for Customer",$cp['customer_notes_expanded'])
        ."<div>".$notes."</div><br />"
        ."</div>";
      $selectID =       $old_selectID;
    }
// Change History:
    if ($cp['changes']) {
      $changes = $this->count_changes();
      if ($changes>0){
        $old_selectID = $selectID;
        $selectID =     $this->_get_ID();
        $div =          "div_change_history_".$this->_get_ID();
        $old_sortBy =   $sortBy;
        $sortBy =       "history_modified_date_d";
        $out.=
           draw_hide_show($div,"Change History (".$changes." previous - latest is highlighted)",$cp['changes_expanded'])
          .draw_auto_report('credit_memo_change_history',0)
          ."</div>";
        $sortBy =       $old_sortBy;
        $selectID =     $old_selectID;
      }
    }
// Person Details:
    if ($cp['person']) {
      $div =        "p_person_details_".$this->_get_ID();
      $personID =   $record['personID'];

      $out.=
         draw_hide_show($div,"Person's Details",$cp['person_expanded'])
        ."<div class='fl' style='width: 50%;'>"
        ."<b>Profile / Home Address</b>"
        .$Obj_Report->draw_form_view('person_view_for_order_1',$personID,true,$cp['headers'])
        ."</div>"
        ."<div class='fl' style='width: 50%'>"
        ."<b>Work Address</b>"
        .$Obj_Report->draw_form_view('person_view_for_order_2',$personID,true,$cp['headers'])
        ."</div><div class='clr_b'>&nbsp;</div></div>";
    }
// Billing / Shipping Addresses:
    if ($cp['billing']) {
      $div =        "order_billing_".$this->_get_ID();
      $out.=
         draw_hide_show($div,"Billing and Shipping Information",$cp['billing_expanded'])
        ."<div class='fl' style='width: 50%;'>"
        ."<b>Billing Information</b>"
        .$Obj_Report->draw_form_view('order_billing',$this->_get_ID(),true,$cp['headers'])
        ."</div>"
        ."<div class='fl' style='width: 50%'>"
        ."<b>Shipping Information</b>"
        .$Obj_Report->draw_form_view('order_shipping',$this->_get_ID(),true,$cp['headers'])
        ."</div><div class='clr_b'>&nbsp;</div></div>";
    }
// Items
    if ($cp['items']){
      $div =        "order_items_".$this->_get_ID();
      $out.=
         draw_hide_show($div,"Items refunded in Credit Memo ".$this->_get_ID(),$cp['items_expanded'])
        .$this->draw_items()
        ."</div>";
    }
    return $out;
  }

  function draw_items() {
    $sql =
      "SELECT\n"
     ."  `oi`.`ID` AS `orderItemID`,\n"
     ."  `pr`.`ID`,\n"
     ."  `pr`.`groupingID`,\n"
     ."  `pr`.`content`,\n"
     ."  `pr`.`thumbnail_small`,\n"
     ."  `pr`.`thumbnail_medium`,\n"
     ."  `pr`.`thumbnail_large`,\n"
     ."  `pr`.`itemCode`,\n"
     ."  `pr`.`tax_regimeID`,\n"
     ."  `pr`.`title`,\n"
     ."  `pg`.`name` AS `product_grouping_name`,\n"
     ."  `system`.`textEnglish` AS `systemTitle`,\n"
     ."  `oi`.*\n"
     ."FROM\n"
     ."  `order_items` as `oi`\n"
     ."INNER JOIN `product` AS `pr` ON\n"
     ."  `oi`.`productID` = `pr`.`ID`\n"
     ."INNER JOIN `product_grouping` AS `pg` ON\n"
     ."  `pr`.`groupingID` = `pg`.`ID`\n"
     ."INNER JOIN `system` ON\n"
     ."  `pr`.`systemID` = `system`.`ID` AND\n"
     ."  `pg`.`systemID` = `system`.`ID`\n"
     ."INNER JOIN `orders` as `o` ON\n"
     ."  `oi`.`creditMemoID` = `o`.`ID`\n"
     ."WHERE\n"
     ."  `o`.`ID` = ".$this->_get_ID()."\n"
     ."ORDER BY\n"
     ."  `product_grouping_name`,\n"
     ."  `seq`,\n"
     ."  `title`,\n"
     ."  `creditMemo`,\n"
     ."  `creditMemoID`\n"
     ;
    $items = $this->get_records_for_sql($sql);
    $Obj_Product_Catalogue = new Product_Catalogue_Credit_Memo;
    $args =
      array(
        'items' =>            $items,
        'paymentStatus' =>    '',
        'BCountryID' =>       '',
        'BSpID' =>            '',
        '_orderID' =>         $this->_get_ID()
      );
    return
       draw_form_field('ID',$this->_get_ID(),'hidden')
      .$Obj_Product_Catalogue->draw($args);
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with refunded items";
    $extra_delete = "DELETE FROM `order_items`            WHERE `creditMemoID` IN (".$targetID.");\n";
    $Obj = new Backup;
    $extra_select = $Obj->db_export_sql_query("`order_items`           ","SELECT * FROM `order_items` WHERE `creditMemoID` IN (".$targetID.") ORDER BY `creditMemoID`",$show_fields)."\n";
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  public function get_version(){
    return VERSION_CREDIT_MEMO;
  }
}
?>