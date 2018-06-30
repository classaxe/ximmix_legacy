<?php

define ("VERSION_COMPONENT_SHOP","1.0.8");
/*
Version History:
  1.0.8 (2012-01-23)
    1) Changes to _setup() to harness protected method in Component_Base class
    2) Removed local implementation of _setup_load_parameters()
  1.0.7 (2012-01-23)
    1) Removed _draw_control_panel() - inherits instead
  1.0.6 (2012-01-19)
    1) Added new cps for checkout_label, checkout_page and empty_cart_label
    2) Now writes the given checkout page URL to the history cache so that as
       soon as first item is placed in cart, this history item is available to
       the checkout icon on the personal toolbar with the correct URL.

  (Older version history in class.component_shop.txt)
*/

class Component_Shop extends Component_Base {

  public function __construct(){
    $this->_ident =            "shop";
    $this->_parameter_spec =   array(
      'checkout_label' =>           array('match' => '',			'default'=>'Checkout',      'hint'=>'Label to place on Checkout button'),
      'checkout_page' =>            array('match' => '',			'default'=>'/checkout',     'hint'=>'Page to use for checkout'),
      'content_plaintext' =>        array('match' => 'enum|0,1',  	'default'=>'0',             'hint'=>'0|1'),
      'content_char_limit' =>       array('match' => 'range|0,n',	'default'=>'0',             'hint'=>'0..n'),
      'empty_cart_label' =>         array('match' => '',			'default'=>'Empty Cart',    'hint'=>'Label to place on Empty Cart button'),
      'filter_category_list' =>     array('match' => '',			'default'=>'',              'hint'=>'category1,category2,category3...'),
      'filter_grouping_list' =>     array('match' => '',			'default'=>'',              'hint'=>'productGrouping1,productGrouping2,productGrouping3...'),
      'product_get_children' =>     array('match' => 'enum|0,1',  	'default'=>'0',             'hint'=>'0|1')
    );
  }

  function draw($instance='',$args=array(), $disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel();
    $this->_draw_instructions();
    $this->_draw_products();
    $this->_draw_checkout_button();
    return $this->_html;
  }

  protected function _draw_checkout_button(){
    if (Cart::has_items()){
      $this->_html.=
          "<div class='clear'>&nbsp;</div>"
         ."<h3 style='display:inline;'>"
         ."<span style='font-size:170%;font-weight:bold;font-style:italic;'>2.</span>"
         ." Next Step:</h3>\n"
         ." &nbsp; <input type=\"button\" value=\"".$this->_cp['checkout_label']."\" onclick=\"document.location='"
         .BASE_PATH.trim($this->_cp['checkout_page'],'/')."'\"/> or "
         ."<input type='button' value='Empty Cart' onclick=\"if (confirm('Empty your cart?')) { geid('command').value='empty_cart';geid('form').submit();}\"/>";
    }
  }

  protected function _draw_instructions(){
    $this->_html.=
       "<h3 style='margin-bottom:0;'>"
      ."<span style='font-size:170%;font-weight:bold;font-style:italic;'>1.</span>"
      ." Select items to purchase:</h3>\n"
      ."<p>Click on the shopping cart icons to place any item in your cart.<br />\n"
      ."You can adjust quantities for each item by entering a new value in the quantity box "
      ."and then clicking the shopping cart icon a second time.</p>\n";
  }

  protected function _draw_products(){
    if (!count($this->_products)) {
      $this->_html.= "<p>There are no items available at this time.</p>";
      return $this->_html;
    }
    $Obj_Product_Catalogue = new Product_Catalogue_Shop;
    $args =
      array(
        'items' =>            $this->_products,
        'paymentStatus' =>    '',
        'BCountryID' =>       '',
        'BSpID' =>            '',
        '_orderID' =>         ''
      );
    $this->_html.= $Obj_Product_Catalogue->draw($args);
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_remember_checkout_page();
    $this->_setup_remember_shop_page();
    $this->_setup_load_products();
    $this->_setup_load_product_quantities();
  }

  protected function _setup_load_products(){
    $Obj_Product = new Product;
    $this->_products = $Obj_Product->get_products_filtered(
      $this->_cp['filter_category_list'],
      $this->_cp['filter_grouping_list'],
      $this->_cp['product_get_children']
    );
  }

  protected function _setup_load_product_quantities(){
    foreach($this->_products as &$item){
      $quantity =   Cart::item_get_quantity($item['ID'],'','');
      $item['quantity'] = $quantity;
    }
  }

  private function _setup_remember_checkout_page(){
    History::set('checkout',BASE_PATH.trim($this->_cp['checkout_page'],'/'));
  }

  private function _setup_remember_shop_page(){
    global $page_vars;
    History::set('shop',BASE_PATH.trim($page_vars['path'],'/'));
  }



  public function get_version(){
    return VERSION_COMPONENT_SHOP;
  }
}

?>