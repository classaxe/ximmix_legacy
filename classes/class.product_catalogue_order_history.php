<?php
define ("VERSION_PRODUCT_CATALOGUE_ORDER_HISTORY","1.0.4");
/*
Version History:
  1.0.4 (2012-10-17)
    1) Product_Catalogue_Order_History::_draw_item_quantity() now ONLY spans two rows
       IF item's 'has_second_row' flag is true
  1.0.3 (2012-09-19)
    1) Product_Catalogue_Order_History::_draw_item_quantity() now spans two rows
  1.0.2 (2012-05-23)
    1) Product_Catalogue_Order_History::_draw_item_quantity() now looks at both
       product AND related object (if any) to see if an item is flagged or not
  1.0.1 (2011-12-15)
    1) Product_Catalogue_Order_History::_draw_credit_memo_controls() changed JS
       calls from attach_field_behaviour() to shorter alias afb()
  1.0.0 (2011-08-25)
    1) Initial release - extends Product_Catalogue for 'order_history' operations
*/

class Product_Catalogue_Order_History extends Product_Catalogue {

  public function draw($args) {
    $this->_draw_setup($args);
    $this->_draw_calculate_order_totals();
    $this->_draw_product_groupings_with_items();
    $this->_draw_refund_items_and_credit_memo_controls();
    $this->_draw_totals();
    $this->_html.= "<div class='clr_b'></div>";
    return $this->_html;
  }

  protected function _draw_calc_cost_info($item) {
    return $item;
  }

  protected function _draw_calculate_order_totals(){
    $this->_totals['sub_total'] =       0;
    $this->_totals['item_total'] =      0;
    $this->_totals['shipping'] =        0;
    $this->_totals['total_quantity'] =  0;
    $this->_totals['total_tax'] =       array();
    $this->_totals['total_tax_all'] =   0;
    $this->_totals['paymentMethodSurcharge'] = 0;
    $this->_totals['method_surcharge_cost'] = 0;
    $this->_totals['grand_total'] = 0;
    for($i=1; $i<=20; $i++){
      $this->_totals['total_tax'][$i] = array(
        'name' => '',
        'rate' => '',
        'cost' => 0
      );
    }
    foreach ($this->_items as $item){
      $hasCreditMemo =      (isset($item['creditMemo']) ? $item['creditMemo'] : 0);
      if (!$hasCreditMemo) {
        $_calc =                  $this->_draw_calc_cost_info($item);
        $quantity =               $_calc['quantity'];
        $this->_totals['total_quantity'] +=        $quantity;
        $this->_totals['item_total'] +=            $_calc['net'];
        $this->_totals['sub_total'] +=             $_calc['cost'];
        for($i=1; $i<=20; $i++){
          $this->_totals['total_tax'][$i]['cost'] += $_calc['tax'.$i.'_cost'];
        }
      }
    }
    $Obj_Order = new Order($this->_orderID);
    $Obj_Order->load();
    for($i=1; $i<=20; $i++){
      $this->_totals['total_tax'][$i]['name'] = $_calc['tax'.$i.'_name'];
      $this->_totals['total_tax'][$i]['rate'] = $_calc['tax'.$i.'_rate'];
      $this->_totals['total_tax_all'] +=        $this->_totals['total_tax'][$i]['cost'];
    }
    $this->_shipping =
      array(
        'method' => $Obj_Order->record['SMethod'],
        'cost' =>   $Obj_Order->record['cost_shipping'],
        'taxes' =>  $Obj_Order->record['taxes_shipping']
      );
    $this->_totals['sub_total'] +=                $this->_shipping['cost'];
    $this->_totals['paymentMethodSurcharge'] =    $Obj_Order->record['paymentMethodSurcharge'];
    $this->_totals['method_surcharge_cost'] =     ($this->_totals['sub_total']*$this->_totals['paymentMethodSurcharge']/100);
    $this->_totals['paymentMethod'] =             $Obj_Order->record['paymentMethod'];
    $this->_totals['shipping'] =                  $Obj_Order->record['cost_shipping'];
    if ($this->_shipping['taxes']!='') {
      $shipping_taxes_arr = explode(",",$this->_shipping['taxes']);
      foreach ($shipping_taxes_arr as $shipping_tax) {
        $shipping_tax_bits = explode      (":",$shipping_tax);
        foreach ($this->_totals['total_tax'] as &$total_tax_item){
          if (strToLower($shipping_tax_bits[0])==strToLower($total_tax_item['name'])){
            $total_tax_item['cost']+=$shipping_tax_bits[1];
            $this->_totals['sub_total']+=$shipping_tax_bits[1];
          }
        }
      }
    }
    $this->_totals['grand_total'] = $this->_totals['sub_total']+$this->_totals['method_surcharge_cost'];
  }

  protected function _draw_item_quantity($item,$hasCreditMemo){
    global $page;
    $ID =               $item['ID'];
    $quantity =         $item['quantity'];
    $orderItemID =      $item['orderItemID'];
    $creditMemoID =     $item['creditMemoID'];
    $hasCreditMemo =    $item['creditMemo'];
    if ($this->_current_user_rights['canIssueRefund'] && !$hasCreditMemo){
      $qty_refundable = $item['quantity'];
      $price_non_refundable = $item['price_non_refundable'];
      foreach ($this->_items as $order_item_temp){
        // Are there refunds already on this product?
        if (
          $order_item_temp['creditMemo'] &&
          $item['productID']==$order_item_temp['productID'] &&
          $item['related_object']==$order_item_temp['related_object'] &&
          $item['related_objectID']==$order_item_temp['related_objectID']
        ) {
          if (!$order_item_temp['creditMemoID']) {
            $qty_refundable=-1;
            break;
          }
          $qty_refundable+=($order_item_temp['quantity']*($order_item_temp['creditMemo'] ? -1 : 1));
        }
      }
      switch ($qty_refundable) {
        case -1:
          $creditMemo_flag = 'pending';
        break;
        case 0:
          $creditMemo_flag = 'completed';
        break;
        default:
          $creditMemo_flag = 'none';
        break;
      }
      return
         "    <td class='txt_r'".($item['has_second_row'] ? " rowspan='2'" : "").">"
        ."<a name='row_".$orderItemID."'></a>"
        .(!$hasCreditMemo ?
           "      "
          .($creditMemo_flag=='none' ?
               "<a href='#row_".$orderItemID."' "
              ."onclick=\"order_item_refund_flag_set('".$page."','".$this->_orderID."','".$orderItemID."','".$qty_refundable."')\">"
            : ""
           )
          ."<img src='".BASE_PATH."img/spacer' class='icons' "
          ."style='height:10px;width:10px;background-position:"
          .($creditMemo_flag=='none' ? '-2022' : ($creditMemo_flag=='pending' ? '-2032' : '-2042'))
          ."px 0px;'"
          ." alt=\""
          .($creditMemo_flag=='none' ? 'Click to flag this item for refund' : ($creditMemo_flag=='pending' ? 'Refund Pending' : 'Refund Issued'))
          ."\"/>"
          .($creditMemo_flag=='none' ? "</a>" : "")
          ." "
          : ""
         )
        .$quantity
        ."</td>\n";
     }
     else {
       return
         "    <td class='txt_r'".($item['has_second_row'] ? " rowspan='2'" : "").">".$quantity."</td>\n";
      }
  }

  protected function _draw_refund_items_and_credit_memo_controls(){
    $this->_html.= "<div style='margin:0 0 5px 0' class='fl'>\n";
    $this->_draw_refund_items();
    $this->_draw_credit_memo_controls();
    $this->_html.= "</div>";
  }

  protected function _draw_credit_memo_controls(){
    if (!$this->_current_user_rights['canIssueRefund']) {
      return;
    }
    $this->_html.=
       "<table class='order_cost_summary'>\n"
      ."  <tr>\n"
      ."    <th colspan='2' class='txt_l'>Credit Memo Details:</th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>Total Suggested Refund:</td>\n"
      ."    <td>\n"
      ."<div class='formField_ro txt_r' id=\"ref_suggested_total\" style='width:40px'>0.00</div>\n"
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>Actual awarded refund:</td>\n"
      ."    <td><input type='text' tabindex=\"".$this->_tabIndex++."\" id='ref_actual_total' class='formField txt_r fl' style='width:40px;' name='ref_actual_total' value='0.00' /></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>Notes to customer:</td>\n"
      ."    <td><textarea id='ref_notes_customer' tabindex=\"".$this->_tabIndex++."\" class='formField' rows='2' cols='80' style='width:300px;height:40px' name='ref_notes_customer'></textarea></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2' class='txt_c'><input type='button' class='formButton' onclick=\"order_issue_credit_memo('".$this->_orderID."',ref_items_arr);\" value='Issue Credit Memo' /></td>\n"
      ."  </tr>\n"
      ."</table>\n"
      ."<script type='text/javascript'>\n"
      ."//<![CDATA[\n"
      ;
    foreach ($this->_items as $item) {
      if ($item['creditMemo']==1 && $item['creditMemoID']==0){
        $this->_html.=
           "afb(\"ref_".$item['orderItemID']."_nra\",\"currency_s\",{'min':0,'max':".$item['price']."});\n"
          ."geid(\"ref_".$item['orderItemID']."_nra\").onchange();\n";
      }
    }
    $this->_html.=
       "//order_items_refund_total(ref_items_arr);\n"
      ."afb(\"ref_actual_total\",\"currency_s\");\n"
      ."//]]>\n"
      ."</script>\n";
  }

  public function get_version(){
    return VERSION_PRODUCT_CATALOGUE_ORDER_HISTORY;
  }
}
?>