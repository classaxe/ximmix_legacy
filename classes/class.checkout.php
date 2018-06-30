<?php
define('VERSION_CHECKOUT','1.0.43');
/*
Version History:
  1.0.43 (2014-02-06)
    1) Now invokes Report_Form_Field_Lookup class to handle Ajax lookup

  (Older version history in class.checkout.txt)
*/
class Checkout extends Component_Base{
  private $_lookup_status =             '';
  private $_personID =                  '';
  private $_PUsername_lookup =          '';
  private $_step_number;

  public function __construct(){
    global $system_vars;
    $Obj_GS = new Gateway_Setting($system_vars['gatewayID']);
    $gateway_settings_name = $Obj_GS->get_field('name');
    $this->_ident =            "checkout";
    $this->_parameter_spec =   array(
      'category' =>                         array('match' => '',                'default'=>'',      'hint'=>'Category (or list of categories) to assign to resulting orders'),
      'default_shop_page' =>                array('match' => '',                'default'=>'/shop', 'hint'=>'Location of shop page if history has not provided a previous shop page'),
      'extra_details' =>                    array('match' => '',                'default'=>'',      'hint'=>'Label to use when prompting for extra details - blank skips this'),
      'image' =>                            array('match' => 'enum|,s,m,l',     'default'=>'',      'hint'=>'|s|m|l'),
      'image_height' =>                     array('match' => 'range|1,n',       'default'=>'',      'hint'=>'Max height in pixels'),
      'image_width' =>                      array('match' => 'range|1,n',       'default'=>'',      'hint'=>'Max width in pixels'),
      'manditory_product_csv' =>            array('match' => '',                'default'=>'',      'hint'=>'CSV list of products that MUST be included if there are any items in cart'),
      'payment_gateway_setting' =>          array('match' => '',                'default'=>$gateway_settings_name,      'hint'=>'Payment Gateway Settings to use for any payments that might occur'),
      'person_details_component' =>         array('match' => '',                'default'=>'',      'hint'=>'Name of component to use when displaying person\'s details'),
      'person_details_default_address' =>   array('match' => 'enum|home,work',  'default'=>'home',  'hint'=>'home|work'),
      'person_details_default_CountryID' => array('match' => '',                'default'=>'',      'hint'=>'Default Country code - e.g. CAN'),
      'person_details_default_SpID' =>      array('match' => '',                'default'=>'',      'hint'=>'Default State / Province code - e.g. ON'),
      'receipt_page' =>                     array('match' => '',                'default'=>BASE_PATH.'view_order',      'hint'=>'Page to display when order has been processed'),
      'terms_and_conditions' =>             array('match' => '',                'default'=>'',      'hint'=>'If given these terms must be agreed to before an order can be placed.')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    global $page_vars, $system_vars;
    global $BCountryID, $BSpID, $TCardName;
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel();
    if (!Cart::has_items()){
      $this->_draw_empty_message();
      return $this->_html;
    }
    $this->_draw_js_verify_checkout($this->_cp['person_details_component']);
    // This is so we have the details of the person who will be placing the order
    // and can set location-based costs
    $Obj_Order =    new Order;
    $Obj_PM =       new Payment_Method;
    $person_details =
      ($this->_cp['person_details_component']!='' ?
         draw_component_by_name($this->_cp['person_details_component'])
        :
         $Obj_Order->draw_person_details(
           $this->_personID,
           $this->_cp['person_details_default_CountryID'],
           $this->_cp['person_details_default_SpID'],
           $this->_cp['person_details_default_address']
         )
      );
    $items =    $this->_get_products_in_cart();
//    y($items);
    $chosen =   count($items);
    if ($chosen==0){
      Cart::empty_cart();
      $this->_draw_empty_message();
      return $this->_html;
    }
    if ($chosen && $this->_cp['manditory_product_csv']){
      $itemCode_arr =   explode(',',$this->_cp['manditory_product_csv']);
      $Obj_Product =    new Product;
      foreach($itemCode_arr as $itemCode){
        $productID =    $Obj_Product->get_ID_by_name(trim($itemCode));
        Cart::item_set_quantity($productID,1);
      }
      $items =    $this->_get_products_in_cart();
    }
    $Obj_Product_catalogue = new Product_Catalogue_Checkout;
    $args =
      array(
        'items' =>              $items,
        'paymentStatus' =>      '',
        'BCountryID' =>         $BCountryID,
        'BSpID' =>              $BSpID,
        '_orderID' =>           '',
        'image' =>              $this->_cp['image'],
        'image_height' =>       $this->_cp['image_height'],
        'image_width' =>        $this->_cp['image_width']
      );
    $this->_html.=
       $this->_draw_person_lookup()
      .draw_form_field('category',$this->_cp['category'],'hidden') 
      .$this->_draw_confirm_contents_message()
      ."<div class='product_catalogue'>\n"
      .$Obj_Product_catalogue->draw($args)
      ."</div>\n"
      .($this->_cp['extra_details'] ?
         $this->_draw_step_number($this->_cp['extra_details'])
        .draw_form_field("instructions",strip_tags(get_var('instructions')),"textarea",500,"","","onkeypress=\"return this.value.length<250;\" onkeyup=\"char_counter(this,250,'your_message_chars');\"","","","","",200)
        ."<br />(<span id='your_message_chars'>250 characters left</span>)\n"
       : ""
       )
      .($this->_cp['terms_and_conditions'] ?
         $this->_draw_step_number("Confirm Your Acceptance of Our Terms and Conditions")
        ."<div class='product_catalogue_terms'>".$this->_cp['terms_and_conditions']."<br /><br />"
        .draw_form_field("agree_to_terms",0,"bool")
        ." <label for='agree_to_terms'><b>I agree to these terms.</b></label>"
        ."</div>"
       : ""
       )
      .$this->_draw_step_number(($this->_personID ? 'Confirm' : 'Enter').' your details:')
      .$person_details
      .$this->_draw_step_number('Payment method:')
      .$Obj_PM->draw_payment_options(
         $TCardName,
         'checkout'
       );
    return $this->_html;
  }

  private function _do_submode(){
    global $submode;
    switch ($submode) {
      case "payment":
        $this->_do_submode_payment();
      break;
      case "recalculate":
        // Do nothing - and don't reload customer profile!
        // Happens when person's state / province or country change
      break;
    }
  }

  private function _do_submode_payment(){
    global $page_vars;
    if (!$this->_personID){
      $this->_do_submode_payment_create_user();
    }
    $Obj_Order =    new Order;
    $orderID =      $Obj_Order->save($this->_personID);
    $Obj_Order->_set_ID($orderID);
    $Obj_Order->set_field('originating_page',BASE_PATH.trim($page_vars['path'],'/'),false);
    $Obj_Order->actions_process_product_ordered();
    $Obj_Order->set_costs();

    $Obj_PM = new Payment_Method;
    if ($Obj_PM->method_is_offline(get_var('TMethod'))){
      $Obj_Order->mark_paid('',false);
    }
    else {
      $Obj_Order->payment(get_var('TCardNumber'),$this->_cp['payment_gateway_setting']);
    }
    header('Location: '.BASE_PATH.trim($this->_cp['receipt_page'],'/').'?ID='.$orderID);
    print BASE_PATH.trim($this->_cp['receipt_page'],'/').'?ID='.$orderID;
  }

  private function _do_submode_payment_create_user(){
    $Obj_User =   new User;
    $PUsername =    ($this->_PUsername_lookup ? $this->_PUsername_lookup :  $Obj_User->uniq_PUsername("new_"));
    $prefix =       (strtolower($this->_cp['person_details_default_address'])=='home' ? 'A' : 'W');
    $data =
      array(
        'systemID' =>             SYS_ID,
        'PUsername' =>            $PUsername,
        'PEmail' =>               addslashes(get_var('BEmail')),
        'WCompany' =>             addslashes(get_var('WCompany')),
        $prefix.'Email' =>        addslashes(get_var('BEmail')),
        $prefix.'Address1' =>     addslashes(get_var('BAddress1')),
        $prefix.'Address2' =>     addslashes(get_var('BAddress2')),
        $prefix.'City' =>         addslashes(get_var('BCity')),
        $prefix.'SpID' =>         addslashes(get_var('BSpID')),
        $prefix.'Postal' =>       addslashes(get_var('BPostal')),
        $prefix.'CountryID' =>    addslashes(get_var('BCountryID')),
        $prefix.'Telephone' =>    addslashes(get_var('BTelephone')),
        'NFirst' =>               addslashes(get_var('checkout_NFirst')),
        'NMiddle' =>              addslashes(get_var('checkout_NMiddle')),
        'NLast' =>                addslashes(get_var('checkout_NLast')),
        'permACTIVE' =>           1
      );
    $new_personID = $Obj_User->insert($data);
    $Obj_User->_set_ID($new_personID);
    do_log(1,__CLASS__.'::'.__FUNCTION__.'():','(none)','Person created - '.$new_personID);
    $result = $Obj_User->do_email_signup();
    if (!get_userID()){
      $PUsername =       $Obj_User->get_field("PUsername");
      $PPasswordEnc =    $Obj_User->get_field("PPassword");
      get_person_to_session($PUsername,$PPasswordEnc);
      $this->_personID = $new_personID;
    }
  }

  private function _draw_confirm_contents_message(){
    $shop = History::get('shop');
    return
       $this->_draw_step_number('Confirm contents of this Order:')
      ."<p>Please review the items in your cart and adjust quantities if required.</p>"
      .($shop ?
           "<p>Alternatively, click "
          ."<a href=\"".$shop."\""
          ." onmouseover=\"window.status='Continue Shopping';return true;\""
          ." onmouseout=\"window.status='';return true;\"><b>here</b></a> to continue shopping.</p>"
       :
         ""
       );
  }

  protected function _draw_empty_message(){
    $shop = (History::get('shop') ? History::get('shop') : $this->_cp['default_shop_page']);
    $this->_html.=
       "<p>Your cart is currently empty."
      .($shop ?
         " Click <a href=\"".$shop."\" "
        ."onmouseover=\"window.status='Continue Shopping';return true;\" "
        ."onmouseout=\"window.status='';return true;\"><b>here</b></a> to continue shopping."
      : ""
      )
      ."</p>";
  }

  private function _draw_js_verify_checkout($checkout_person_details_component){
    Page::push_content(
      'javascript',
       "function verify_checkout(){\n"
      ."  var err_msg = [];\n"
      .($checkout_person_details_component=='' ?
          "  err_msg = checkout_validate_billing_details(err_msg);\n"
       :
          "  err_msg = custom_validate_billing_details(err_msg);\n"
       )
      ."  err_msg = checkout_validate_payment_details(err_msg);\n"
      .($this->_cp['terms_and_conditions'] ? "  if(geid_val('agree_to_terms')!=1){  err_msg[err_msg.length] = (err_msg.length+1)+') You must accept our terms and conditions.'; }\n" : "")
      ."  if (err_msg.length>0) {\n"
      ."    alert('There are problems with your details:\\n\\n'+err_msg.join('\\n')+'\\n\\nPlease make corrections and try again.');\n"
      ."    return false;\n"
      ."  }\n"
      ."  geid('btn_payment').value='Please wait...';\n"
      ."  geid('btn_payment').disabled=1;\n"
      ."  geid('submode').value='payment';\n"
      ."  return true;\n"
      ."}\n"
    );
  }

  private function _draw_person_lookup() {
    if (!$this->_current_user_rights['allowProxyOrdering']){
      return "";
    }
    $Obj_Ajax = new Ajax;
    $_control_num = $Obj_Ajax->generate_control_num();
    switch ($this->_lookup_status) {
      case "exists":
        $_lookup_result_initial = "<p><b>Lookup Result:</b><br />Person ".$this->_PUsername_lookup." exists and their details have been loaded.</p>\n";
      break;
      case "new":
        $_lookup_result_initial = "<p><b>Lookup Result:</b><br />Person ".$this->_PUsername_lookup." does <b>not</b> exist and will be added upon completion of this form.</p>\n";
      break;
      default:
        $_lookup_result_initial = "<br />";
      break;
    }
    $_lookup_info_initial = "";
    $_row_js =              "geid('submode').value='lookup';geid('form').submit();";
    $_report_filter =       "CONCAT(`PUsername`,' ',`NFirst`,' ',`NLast`,' ',`ATelephone`,' ',`WTelephone`,' ',`AAddress1`,' ',`ACity`,' ',`APostal`,' ',`ASpID`,`WAddress1`,' ',`WCity`,' ',`WPostal`,' ',`WSpID`)";
    $Obj_RFFL = new Report_Form_Field_Lookup;
    $Obj_RFFL->init(
      'PUsername_lookup',
      $this->_PUsername_lookup,
      $_control_num,
      'User Lookup',
      $_report_filter,
      "Contains",
      'PUsername',
      '',
      1,
      $_row_js,
      '',
      '',
      $_lookup_info_initial,
      $_lookup_result_initial
    );
    return
       $this->_draw_step_number('Administrators:')
      ."<p>You may enter the Username of a person to act on their behalf, then press 'Load Details' "
      ."to load their details into this form, leave the field blank to use your own profile, or press 'New Person' to create an order for someone not already in the system:</p>"
      ."<label for='PUsername_lookup' style='float:left;margin:0 5px 0 0'><b>Username:</b></label>"
      .$Obj_RFFL->draw()
      ."<input type='button' value='Load Details' class='formButton' "
      ."onclick=\"geid_set('submode','lookup');geid('form').submit();\" /> "
      ."<input class='formButton' type=\"button\" value=\"New Person\" "
      ."onclick=\"if (confirm('Clear form to add a new person?')) {geid_set('submode','reset');geid_set('PUsername_lookup','');geid('form').submit();}\" />"
      ."<br /><br />";
  }

  private function _draw_step_number($label){
    return
       "<h3 style='margin-bottom:0;'>"
      ."<span style='font-size:170%'><i>".$this->_step_number++.".</i></span>"
      ." ".$label
      ."</h3>\n";
  }

  private function _get_products_in_cart(){
    $items =            Cart::get_items();
    $productID_arr =    array();
    foreach ($items as $item){
      $productID_arr[] = $item['ID'];
    }
    if (!count($productID_arr)){
      return array();
    }
    $Obj_Product =    new Product;
    $ID_csv =         implode(",",$productID_arr);
    $products =       $Obj_Product->get_products_for_productID_list($ID_csv);
    $out = array();
    foreach($items as $item){
      foreach($products as $product){
        if ($item['ID']==$product['ID']){
          $cart_item =                      $product;
          $cart_item['quantity'] =          $item['qty'];
          $cart_item['related_object'] =    $item['related_object'];
          $cart_item['related_objectID'] =  $item['related_objectID'];
          $out[] =                          $cart_item;
        }
      }
    }
    usort($out, array($this,'_get_products_in_cart_sort'));
    return $out;
  }

  private function _get_products_in_cart_sort($a, $b){
    if ($a['product_grouping_name'] == $b['product_grouping_name']) {
      if ($a['seq'] == $b['seq']) {
        if($a['title'] == $b['title']){
          return 0;
        }
        return strcmp($a['title'], $b['title']);;
      }
      if ($a['seq'] == $b['seq']) {
        return 0;
      }
      return ($a['seq'] < $b['seq']) ? -1 : 1;
    }
    return ($a['product_grouping_name'] < $b['product_grouping_name']) ? -1 : 1;
  }

  private function _reset_vars(){
    $vars =
      explode(
        ',',
         'BAddress1,BAddress2,BCity,BSpID,BPostal,BCountryID,BEmail,BTelephone,'
        .'checkout_NName,checkout_NFirst,checkout_NMiddle,checkout_NLast,'
        .'TCardName,WCompany'
      );
    foreach($vars as $var){
      global $$var;
      $$var = '';
    }
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_step_number =       1;
    $this->_personID =          get_var('personID');
    $this->_PUsername_lookup =  get_var('PUsername_lookup');
    $this->_setup_ssl_redirect_if_required();
    $this->_setup_load_parameters();
    $this->_setup_load_admin_rights();
    $this->_setup_load_personID();
    $this->_setup_load_TCardName();
    $this->_do_submode();
  }

  private function _setup_load_admin_rights(){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isUSERADMIN =		get_person_permission("USERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isAdmin =          ($isMASTERADMIN || $isUSERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    $this->_current_user_rights['allowOfflinePaymentMethods'] = $isAdmin;
    $this->_current_user_rights['allowProxyOrdering'] =         $isAdmin;
  }

  private function _setup_load_personID(){
    global $submode;
    switch ($submode){
      case '':
        if (!$this->_personID) {
          $this->_personID = get_userID();
        }
      break;
      case 'lookup':
        if ($this->_current_user_rights['allowProxyOrdering']){
          $submode = '';
          if (!$this->_PUsername_lookup = get_var('PUsername_lookup')){
            break;
          }
          $Obj_User = new User;
          $this->_personID = $Obj_User->get_ID_by_name(
            $this->_PUsername_lookup,
            SYS_ID
          );
          if ($this->_personID){
            $this->_lookup_status = 'exists';
          }
          else {
            $this->_lookup_status = 'new';
            $this->_reset_vars();
          }
        }
      break;
      case 'reset':
        if ($this->_current_user_rights['allowProxyOrdering']){
          $submode = '';
          $this->_personID = '';
          $this->_reset_vars();
        }
      break;
    }
  }

  private function _setup_load_TCardName(){
    global $TCardName;
    if ($this->_lookup_status || $TCardName=='') {
      $Obj_User = new User($this->_personID);
      $row =        $Obj_User->get_record();
      $TCardName =  trim(
          $row['NFirst']
        .($row['NMiddle'] ? " ".$row['NMiddle'] : "")
        .($row['NLast'] ? " ".$row['NLast'] : "")
      );
    }
  }

  private function _setup_ssl_redirect_if_required(){
    global $page_vars, $system_vars;
    $Obj_Gateway_Setting = new Gateway_Setting($system_vars['gatewayID']);
    if ($Obj_Gateway_Setting->test_requiresSSL()) {
      if ($_SERVER["SERVER_PORT"]!=443 &&
          substr($_SERVER["SERVER_NAME"],0,8)!='desktop.' &&
          substr($_SERVER["SERVER_NAME"],0,4)!='dev.' &&
          substr($_SERVER["SERVER_NAME"],0,7)!='laptop.'
      ) {
        $host =   $_SERVER["HTTP_HOST"];
        header(
           "Location: https://".$host.BASE_PATH.urlencode(trim($page_vars['path'],'/')));
        die;
      }
    }
  }

  public function get_version(){
    return VERSION_CHECKOUT;
  }
}
?>