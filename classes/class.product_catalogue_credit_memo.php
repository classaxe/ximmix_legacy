<?php
define ("VERSION_PRODUCT_CATALOGUE_CREDIT_MEMO","1.0.2");
/*
Version History:
  1.0.2 (2012-10-17)
    1) Product_Catalogue_Credit_Memo::_draw_item_quantity() now ONLY spans two rows
       IF item's 'has_second_row' flag is true
  1.0.1 (2012-09-19)
    1) Product_Catalogue_Credit_Memo::_draw_item_quantity() now spans two rows
  1.0.0 (2011-08-25)
    1) Initial release - extends Product_Catalogue for 'credit_memo' operations
*/

class Product_Catalogue_Credit_Memo extends Product_Catalogue {

  protected function _draw_calc_cost_info($item) {
    return $item;
  }

  public function draw($args) {
    $this->_draw_setup($args);
    $this->_draw_calculate_order_totals();
    $this->_draw_product_groupings_with_items();
    $this->_draw_totals();
    $this->_html.= "<div class='clr_b'></div>";
    return $this->_html;
  }

  protected function _draw_calculate_order_totals(){
    global $TMethod;
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
      $_calc =                  $this->_draw_calc_cost_info($item);
      $quantity =               $_calc['quantity'];
      $this->_totals['total_quantity'] +=        $quantity;
      $this->_totals['item_total'] +=            $_calc['net'];
      $this->_totals['sub_total'] +=             $_calc['cost'];
      for($i=1; $i<=20; $i++){
        $this->_totals['total_tax'][$i]['cost'] += $_calc['tax'.$i.'_cost'];
      }
    }
    $Obj_Credit_Memo = new Credit_Memo($this->_orderID);
    $Obj_Credit_Memo->load();
    $this->_totals['credit_memo_refund_awarded'] =    $Obj_Credit_Memo->record['credit_memo_refund_awarded'];
    $this->_totals['credit_memo_transaction_code'] =  $Obj_Credit_Memo->record['credit_memo_transaction_code'];
    $this->_totals['credit_memo_notes_customer'] =    $Obj_Credit_Memo->record['credit_memo_notes_customer'];
  }

  protected function _draw_item_quantity($item,$hasCreditMemo){
    return "    <td".($item['has_second_row'] ? " rowspan='2'" : "")." class='txt_r'>".$item['quantity']."</td>";
  }

  protected function _draw_totals(){
    $this->_html.=
      "<div style='margin:0 0 5px 0' class='fr'>\n"
      ."  <table class='order_cost_summary'>\n"
      ."    <tr>\n"
      ."      <th colspan='2' class='txt_l'>Refund Details</th>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td style='width:120px'><b>Items:</b></td>\n"
      ."      <td class='txt_r'>".$this->_totals['total_quantity']."</td>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td><b>Refund Awarded:</b></td>\n"
      ."      <td class='txt_r'>".$this->_currency_symbol.$this->_totals['credit_memo_refund_awarded']."</td>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td style='width:120px'><b>Transaction Code:</b></td>\n"
      ."      <td class='txt_r'>".$this->_totals['credit_memo_transaction_code']."</td>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td style='width:120px'><b>Notes for Customer:</b></td>\n"
      ."      <td>".$this->_totals['credit_memo_notes_customer']."</td>\n"
      ."    </tr>\n"
      ."  </table>\n"
      ."</div>";
  }

  public function get_version(){
    return VERSION_PRODUCT_CATALOGUE_CREDIT_MEMO;
  }
}
?>