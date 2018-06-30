<?php
define ("VERSION_PRODUCT_CATALOGUE_CHECKOUT","1.0.5");
/*
Version History:
  1.0.5 (2012-10-17)
    1) Product_Catalogue_Checkout::_draw_item_quantity() now ONLY spans two rows
       IF item's 'has_second_row' flag is true
  1.0.4 (2012-09-19)
    1) Product_Catalogue_Checkout::_draw_item_quantity() now spans two rows
  1.0.3 (2011-12-16)
    1) Change to Product_Catalogue_Checkout::_draw_calculate_order_totals
  1.0.2 (2011-09-01)
    1) Product_Catalogue_Checkout::_draw_checkout_action_buttons() no longer
       includes 'Recalculate' button - not needed
  1.0.1 (2011-09-01)
    1) Product_Catalogue_Checkout::_draw_item_quantity() now always sets a compound
       key like this:
         'productID|related_object|related_objectID'
       If no context is given, key is still valid compound key like this:
         'productID||'
  1.0.0 (2011-08-25)
    1) Initial release - extends Product_Catalogue for 'checkout' operations
*/

class Product_Catalogue_Checkout extends Product_Catalogue {

  protected function _draw_calc_cost_info($item) {
    return OrderItem::get_costs($item,$this->_BCountryID,$this->_BSpID);
  }

  public function draw($args) {
    $this->_draw_setup($args);
    $this->_draw_calculate_order_totals();
    $this->_draw_product_groupings_with_items();
    $this->_draw_totals();
    $this->_draw_checkout_action_buttons();
    $this->_html.= "<div class='clr_b'></div>";
    return $this->_html;
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
      if (!$hasCreditMemo || $this->_personality=="credit_memo") {
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
    for($i=1; $i<=20; $i++){
      $this->_totals['total_tax'][$i]['name'] = $_calc['tax'.$i.'_name'];
      $this->_totals['total_tax'][$i]['rate'] = $_calc['tax'.$i.'_rate'];
      $this->_totals['total_tax_all'] +=        $this->_totals['total_tax'][$i]['cost'];
    }
    if ($TMethod=get_var('TMethod')){
      $Obj_PM = new Payment_Method;
      $this->_totals['paymentMethod'] = $TMethod;
      $this->_totals['paymentMethodSurcharge'] = $Obj_PM->get_method_surcharge($TMethod);
    }
    else {
      $Obj_PM = new Payment_Method;
      $qty =    $Obj_PM->count_methods_available();
      if ($qty==1){
        $method_label = "";
        $Obj_PM->get_first_method($method_label, $this->_totals['paymentMethod']);
        $this->_totals['paymentMethodSurcharge'] = $Obj_PM->get_method_surcharge($this->_totals['paymentMethod']);
      }
    }
    $this->_totals['method_surcharge_cost'] = ($this->_totals['sub_total']*$this->_totals['paymentMethodSurcharge']/100);
    $this->_totals['grand_total'] = $this->_totals['sub_total']+$this->_totals['method_surcharge_cost'];
  }

  protected function _draw_checkout_action_buttons(){
    $this->_html.=
       "  <img src='".BASE_PATH."img/spacer' height='5' width='1' class='b' alt=''/>\n"
      ."<div class='txt_c'>"
      ."<input type='button' value='Empty Cart' onclick=\"if (confirm('Empty your cart?')) { geid('command').value='empty_cart';geid('form').submit();}\"/>"
      ."</div>\n";
  }

  protected function _draw_item_quantity($item){
    global $page;
    $ID =               $item['ID'];
    $quantity =         $item['quantity'];
    $related_object =   $item['related_object'];
    $related_objectID = $item['related_objectID'];
    $anchor =           "product_".$ID."_".$item['related_object']."_".$item['related_objectID'];
    $field =            "product_qty_".$ID."_".$item['related_object']."_".$item['related_objectID'];
    $js =
       "onchange=\""
      ."show_popup_please_wait('<b>Please wait...</b><br />Updating cart contents',200,60);"
      ."geid_set('anchor',(window.location.hash ? window.location.hash : '#".$anchor."'));"
      ."geid_set('command','cart');"
      ."geid_set('source','".$related_object."|".$related_objectID."');"
      ."geid_set('targetID','".$ID."');"
      ."geid_set('targetValue',this.value);"
      ."geid('form').submit();\"";
    return
       "    <td".($item['has_second_row'] ? " rowspan='2'" : "").">\n"
      ."      <div style='width:40px;'>\n"
      ."<a name=\"".$anchor."\" id=\"".$anchor."\"></a>"
      .draw_form_field($field,$quantity,"qty",20,'','',$js)
      ."</div>\n"
      ."    </td>\n";
  }

  protected function _draw_totals(){
    $this->_html.= "<div style='margin:0 0 5px 0' class='fr'>\n";
    $this->_draw_totals_for_order();
    $this->_html.= "</div>";
  }

  public function get_version(){
    return VERSION_PRODUCT_CATALOGUE_CHECKOUT;
  }
}
?>