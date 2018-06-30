<?php
define('VERSION_CASEPAYMENTECH_GATEWAY','1.0.6');

/*
Version History:
  1.0.6 (2014-01-06)
    1) Chasepaymentech_Gateway::payment() now uses User class to get details of
       person placing order - Contacts cannot place orders

  (Older version history in class.chasepaymentech_gateway.txt)
*/

class Chasepaymentech_Gateway {
	// holds all the properties of this object
	// unset properties (ie properties that do not exist) return a value of false if accessed
  private $data = array();

  public function __get($key) {
    if (array_key_exists($key, $this->data)) {
    	return $this->data[$key];
    }
    return false;
  }

  public function __set($key, $value) {
    // allows arbitrary creation of properties
    $this->data[$key] = $value;
  }

  public function __construct($data = '') {
    $Obj_System = new System(SYS_ID);
    if (!$gateway_info = $Obj_System->get_gateway()) {
      return;
    }
    $Obj_System->xmlfields_decode($gateway_info['settings']);
    $gateway_url = parse_url($gateway_info['type']['URL']);
    $defaultData = array(
      'afterCancelURL' =>                           (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/checkout',
      'afterPaymentURL' =>                          (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/view_order',
      'cancelURL' =>                                (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/chasepaymentech_cancel',
      'currencyCode' =>                             'CAD',
      'gateway_settings' =>                         $gateway_info,
      'gateway_url' =>                              $gateway_url,
      'ipnURL' =>                                   (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/?command=chasepaymentech_ipn',
      'merchantID' =>                               $gateway_info['settings']['merchantID'],
      'returnURL' =>                                (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['SERVER_NAME'] . '/chasepaymentech_return'
    );
    if (is_array($data)) {
      $this->data = array_merge($defaultData, $data);
    }
    else {
      $this->data = $defaultData;
    }
  }

  public function cancel() {
    Cart::repopulate_all_from_pending();
    if ($orderID = Cart::pending_order_get_ID()) {
      $Obj_Order = new Order($orderID);
      $Obj_Order->delete();
      Cart::pending_order_unset_ID();
    }
    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Chase Paymentech cancelled',$orderID);
    header('Location: '.$this->afterCancelURL);
  }

  public function notify() {
    unset($_SESSION['pending_cart_items']);
    $req =  array();
    foreach ($_REQUEST as $key=>$value) {
      $req[] = $key."=".$value;
    }
    $req = implode('&',$req);
//    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Chase Paymentech debug info',$req);
    $Obj_System = new System(SYS_ID);
    if (!$gateway_info = $Obj_System->get_gateway()) {
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','No gateway set for this site','Request:'.$req);
      return;
    }
    $response_key =             $this->gateway_settings['settings']['xml:chase_response_key'];
    $x_amount =                 false;
    $x_auth_code =              false;
    $x_invoice_num =            false;
    $x_login =                  false;
    $x_MD5_Hash =               false;
    $x_response_reason_code =   false;
    $x_response_reason_text =   false;
    $x_trans_id =               false;
    foreach ($_REQUEST as $key=>$value) {
      switch ($key){
        case 'x_amount':
          $x_amount =               $value;
        break;
        case 'x_auth_code':
          $x_auth_code =            $value;
        break;
        case 'x_invoice_num':
          $x_invoice_num =          $value;
        break;
        case 'x_login':
          $x_login =                $value;
        break;
        case 'x_MD5_Hash':
          $x_MD5_Hash =             $value;
        break;
        case 'x_response_reason_code':
          $x_response_reason_code = $value;
        break;
        case 'x_response_reason_text':
          $x_response_reason_text = $value;
        break;
        case 'x_trans_id':
          $x_trans_id =             $value;
        break;
      }
    }
    $fingerprint =  md5($response_key.$x_login.$x_trans_id.$x_amount);
    if ($x_MD5_Hash!==$fingerprint){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Bad IPN verify response','Request - expected: '.$x_MD5_Hash.', actual: '.$fingerprint,'Request: '.$req);
      return;
    }
    $Obj_Order = new Order($x_invoice_num);
    if (!$Obj_Order->exists()){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Invoice number invalid - '.$x_invoice_num,'Request: '.$req);
      return;
    }
    $cost_grand_total = $Obj_Order->get_field('cost_grand_total');
    if ($cost_grand_total!==$x_amount){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Payment amount incorrect - expected '.$cost_grand_total.', received '.$vnp['mc_gross'],'Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    $msg =  $x_response_reason_text.' - Code:'.$x_auth_code;
    $Obj_Order->set_field('gateway_result', $msg);
    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','','Gateway result - '.$msg);
    switch($x_response_reason_code){
      case 1:
        if (!$Obj_Order->get_field('paymentApproved')){
          $Obj_Order->mark_paid('',false);
          do_log(1,__CLASS__.'::'.__FUNCTION__.'()','','Marked as paid - '.$x_invoice_num);
        }
      break;
      case 2:
        do_log(2,__CLASS__.'::'.__FUNCTION__.'()','','Payment unsuccessful - '.$x_invoice_num,'Request: '.$req);
      break;
      case 3:
        do_log(3,__CLASS__.'::'.__FUNCTION__.'()','','Payment error - '.$x_invoice_num,'Request: '.$req);
      break;
    }
    die();
  }

  public function payment($Obj_Order){ // pass in object of class=Order
    $order_record =     $Obj_Order->get_record();
    $order_items =      $Obj_Order->get_order_items();
    $Obj_User =       new User($order_record['personID']);
    $Obj_User->load();
    switch ($this->gateway_settings['type']['name']){
      case 'Chase Paymentech (Live)':
        $test_mode = 0;
      break;
      case 'Chase Paymentech (Test)':
        $test_mode = 1;
      break;
      default:
        $msg =  "Gateway type ".$this->gateway_settings['type']['name']." not handled by this interface.";
        do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Constructor',$msg);
        die($msg);
      break;
    }
    $host_url =         (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'];
    $transaction_mode = 'true';
    srand(time()*(get_userID() ? get_userID() : 1));
    $sequence =     rand(1, 1000000);
    $tstamp =       time();
    $login =        $this->gateway_settings['settings']['xml:chase_login'];
    $txn_key =      $this->gateway_settings['settings']['xml:chase_transaction_key'];
    $ccode =        'CAD';
    $amount =       $order_record['cost_grand_total'];
    $fingerprint =  hash_hmac('md5', $login.'^'.$sequence.'^'.$tstamp.'^'.$amount.'^'.$ccode, $txn_key);
    $submit_data = array(
      // essentials & good practice params
      'x_login' =>                $this->gateway_settings['settings']['xml:chase_login'],
      'x_fp_sequence' =>          $sequence,
      'x_fp_timestamp' =>         $tstamp,
      'x_amount' =>               $amount,
      'x_fp_hash' =>              $fingerprint,
      'x_show_form' =>            'PAYMENT_FORM',
      'x_test_request' =>         ($test_mode ? 'TRUE' : 'FALSE'),
      'x_type' =>                 'AUTH_CAPTURE', // 'AUTH_ONLY',
      // return/redirect back values
      'x_receipt_link_method' =>  'POST',
      'x_receipt_link_text' =>    'View your Order',
      'x_receipt_link_url' =>     $host_url.BASE_PATH.'?command=cpt_receipt',
      // customer ip if using FDS
      'x_customer_ip' =>          '', //ip_address(),
      'x_version' =>              '3.1',
      // aesthetics
      'x_logo_url' =>             'test.gif',
      'x_color_background' =>     '#e0ffe0',
      // non-required but good to have
      'x_currency_code' =>        'CAD',
      'x_email_customer' =>       'TRUE', // ''
      'x_cust_id' =>              $order_record['personID'],
      'x_invoice_num' =>          $order_record['ID'],
      'x_po_num' =>               $order_record['ID'],
      'x_first_name' =>           substr($Obj_User->record['NFirst'], 0, 50),
      'x_last_name' =>            substr($Obj_User->record['NLast'], 0, 50),
//      'x_company' =>              substr($order_record['BCompany'], 0, 50),
      'x_address' =>              substr($order_record['BAddress1'], 0, 60),
      'x_city' =>                 substr($order_record['BCity'], 0, 40),
      'x_state' =>                substr($order_record['BSpID'], 0, 40),
      'x_zip' =>                  substr($order_record['BPostal'], 0, 20),
      'x_country' =>              $order_record['BCountryID'],
      'x_phone' =>                substr($order_record['BTelephone'], 0, 25),
      'x_email' =>                $order_record['BEmail'],
      'x_ship_to_first_name' =>   substr($Obj_User->record['NFirst'], 0, 50),
      'x_ship_to_last_name' =>    substr($Obj_User->record['NLast'], 0, 50),
      'x_ship_to_address' =>      substr($order_record['SAddress1'], 0, 60),
      'x_ship_to_city' =>         substr($order_record['SCity'], 0, 40),
      'x_ship_to_state' =>        substr($order_record['SSpID'], 0, 40),
      'x_ship_to_zip' =>          substr($order_record['SPostal'], 0, 20),
      'x_ship_to_country' =>      $order_record['SCountryID'],
      'x_description' =>          "Order Created: ".$order_record['history_created_date']
    );
    $taxAmount = 0;
    $taxCount = 1;
    while (isset($order_record['tax' . $taxCount . '_cost'])) {
      $taxAmount += $order_record['tax' . $taxCount . '_cost'];
      $taxCount++;
    }
    if ($taxAmount) $submit_data['x_tax'] = $taxAmount;
    $i = 0;
    $item_data = array();
    foreach ($order_items as $item) {
      $item_title =         $item['title'];
      $item_sku =           $item['productID'];
      $item_quantity =      $item['quantity'];
      $item_price =         $item['price'];
      if ($item['related_object']){
        $Obj_type =         $item['related_object'];
        $Obj_ID =           $item['related_objectID'];
        $Obj =              new $Obj_type($Obj_ID);
        $Obj->load();
        $item_sku.=    "|".$Obj->_get_object_name().'|'.$Obj->record['ID'];
        $item_title.=       ": ".$Obj->_get_object_name().': ';
        if (strtolower($item['related_object'])=='event'){
          $item_title.=
             ' '
            .$Obj->record['effective_date_start'].' '
            .$Obj->record['effective_time_start'].'-'
            .$Obj->record['effective_time_end'].' ';
        }
        $item_title.= $Obj->record['title'];
      }
      $item_data[] = $item['itemCode'].'<|>'.$item['title'].'<|>'.$item_title.'<|>'.$item['quantity'].'<|>'.$item['price'].'<|>YES<|>';
      $i++;
    }
    $out =
       "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
      ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n"
      ."<head>\n"
      ."<title>Proceed to Chase Paymentech</title>\n"
      ."</head>\n"
      ."<body>\n"
      ."<form method=\"post\" action=\"".$this->gateway_settings['type']['URL']."\">\n"
      ."<div>\n";
    foreach ($submit_data as $key=>$value){
      $out.= "  <input type='hidden' name=\"".$key."\" value=\"".$value."\" />\n";
    }
    foreach ($item_data as $value){
      $out.= "  <input type='hidden' name=\"x_line_item\" value=\"".$value."\" />\n";
    }
    $out.=
       "  <input id='subbtn' type='submit' value='Continue to Chase Paymentech' />\n"
      ."</div>\n"
      ."</form>\n"
      ."<script type='text/javascript'>\n"
      ."//<![CDATA[\n"
      ."document.getElementById('subbtn').style.display='none';\n"
      ."document.forms[0].submit();\n"
      ."//]]>\n"
      ."</script>\n"
      ."</body>\n"
      ."</html>\n"
      ;
    echo $out;
    die();
  }

  public function receipt() {
    unset($_SESSION['pending_cart_items']);
    header('Location: '.$this->afterPaymentURL.'?ID='.$_SESSION['pending_order']);
    Cart::pending_order_unset_ID();
    die();
  }

  public function get_version(){
    return VERSION_CASEPAYMENTECH_GATEWAY;
  }
}
?>