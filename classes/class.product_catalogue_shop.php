<?php
define ("VERSION_PRODUCT_CATALOGUE_SHOP","1.0.6");
/*
Version History:
  1.0.6 (2012-10-17)
    1) Product_Catalogue_Shop::_draw_item_quantity() now ONLY spans two rows
       IF item's 'has_second_row' flag is true
  1.0.5 (2012-09-19)
    1) Product_Catalogue_Shop::_draw_item_quantity() now spans two rows
  1.0.4 (2011-09-15)
    1) Product_Catalogue_Shop::_draw_totals() now labelled 'Selection Summary'
  1.0.3 (2011-09-01)
    1) Product_Catalogue_Shop::_draw_item_quantity() - JS tweak to set vars in
       alphabetical order (clearer)
  1.0.2 (2011-08-31)
    1) Product_Catalogue_Shop::_draw_item_quantity() now always sets a compound
       key like this:
         'productID|related_object|related_objectID'
       If no context is given, key is still valid compound key like this:
         'productID||'
  1.0.1 (2011-08-25)
    1) Now includes draw() method customised for this child class
  1.0.0 (2011-08-25)
    1) Initial release - extends Product_Catalogue for 'shop' operations
*/

class Product_Catalogue_Shop extends Product_Catalogue {

  public function draw($args) {
    $this->_draw_setup($args);
    $this->_draw_calculate_order_totals();
    $this->_draw_product_groupings_with_items();
    $this->_draw_totals();
    $this->_html.= "<div class='clr_b'></div>";
    return $this->_html;
  }


  protected function _draw_calc_cost_info($item) {
    $data['orderID'] =       false;
    $data['quantity'] =      0;
    $data['net'] =           0;
    $data['cost'] =          0;
    for($i=1; $i<=20; $i++){
      $data['tax'.$i.'_cost'] = 0;
    }
    return $data;
  }

  protected function _draw_calculate_order_totals(){
    $this->_totals['total_quantity'] =  0;
    $this->_totals['item_total'] =      0;
    foreach ($this->_items as $item) {
      $this->_totals['total_quantity']+=$item['quantity'];
      $this->_totals['item_total']+=$item['quantity']*$item['price'];
    }
  }

  protected function _draw_item_quantity($item){
    $ID =               $item['ID'];
    $quantity =         $item['quantity'];
    $js =
       "onchange=\""
      ."if(parseInt(this.value,10)>=0){"
      ."show_popup_please_wait('<b>Please wait...</b><br />Updating cart contents',200,60);"
      ."geid_set('anchor',(window.location.hash ? window.location.hash : '#product_".$ID."'));"
      ."geid_set('command','cart');"
      ."geid_set('source','".($this->_related_type ? $this->_related_type."|".$this->_related_ID : "|")."');"
      ."geid_set('targetID','".$ID."');"
      ."geid_set('targetValue',this.value);"
      ."geid('form').submit();}\"";
    if ($quantity!=0) {
      return
         "    <td".($item['has_second_row'] ? " rowspan='2'" : "").">"
        ."<div style='width:55px;'>"
        .draw_form_field("product_qty_".$ID,$quantity,"qty",20,'','',$js)
        ."<a name=\"product_".$ID."\" id=\"product_".$ID."\" class='fl' href='#'"
        ." onclick=\"geid_set('product_qty_".$ID."',0);geid('product_qty_".$ID."').onchange();return false;\">"
        ."<img class='icons fl' alt='' title='Cart has ".$quantity." of this item \nUse +/- or enter a new value to change \nor click cart to remove the item(s)' src='".BASE_PATH."img/spacer'"
        ." style='height:16px;width:14px;margin-left:2px;background-position: -1027px 0px;' />"
        ."</a></div></td>";
    }
    else {
      return
         "    <td".($item['has_second_row'] ? " rowspan='2'" : "").">\n"
        ."<div style='width:60px;'>\n"
        .draw_form_field("product_qty_".$ID,$quantity,"qty",20,'','',$js)
        ."<a name=\"product_".$ID."\" id=\"product_".$ID."\" href='#'"
        ." onclick=\"geid_set('product_qty_".$ID."',1);geid('product_qty_".$ID."').onchange();return false;\">"
        ."<img class='icons fl' alt='' title='Cart has none of this item \nClick cart to add it' src='".BASE_PATH."img/spacer'"
        ." style='height:16px;width:14px;margin-left:2px;background-position: -1013px 0px;' />"
        ."</a></div>\n"
        ."</td>\n";
    }
  }

  protected function _draw_totals(){
    $this->_html.=
       "<div style='margin:0 0 5px 0' class='fr'>\n"
      ."  <table class='order_cost_summary' width='200'>\n"
      ."    <tr>\n"
      ."      <th colspan='2' class='txt_l'>Selection Summary</th>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td style='width:120px'><b>Items:</b></td>\n"
      ."      <td class='txt_r'>".$this->_totals['total_quantity']."</td>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td><b>Item Total:</b></td>\n"
      ."      <td class='txt_r'>".$this->_currency_symbol.two_dp($this->_totals['item_total'])."</td>\n"
      ."    </tr>\n"
      ."  </table>\n"
      ."</div>";
  }

  protected function _get_columns_for_grouping($groupingID=""){
    $Obj_PGC =  new Product_Grouping_Column;
    $filtered = true;
    return $Obj_PGC->get_all_for_grouping($groupingID, $filtered);
  }

  public function get_version(){
    return VERSION_PRODUCT_CATALOGUE_SHOP;
  }
}
?>