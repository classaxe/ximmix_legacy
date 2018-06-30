<?php
define('VERSION_PAYPAL_GATEWAY','1.0.24');

/*
Version History:
  1.0.24 (2013-03-08)
    1) PayPal_Gateway::simplePaymentVerify() -
       Extensive changes to have system show prominent 'print tickets' link
       and also to prevent errors if page is accessed with TX token but person
       isn't signed in anymore.
  1.0.23 (2012-11-21)
    1) PayPal_Gateway::payment() formerly sent productID as sku -
       this is now corrected to send itemCode instead
  1.0.22 (2012-11-16)
    1) Paypal::IPNPaymentVerify() now uses gateway_settingsID saved in order to
       authenticate to paypal and verify payment was made correctly

  (Older version history in class.paypal_gateway.txt)
*/

class PayPal_Gateway {
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

  public function __construct($data='', $gateway_info=false) {
    if (!$gateway_info){
      $Obj_System = new System(SYS_ID);
      if (!$gateway_info = $Obj_System->get_gateway()) {
        return;
      }
    }
    $gateway_url =      parse_url($gateway_info['type']['URL']);
    $host_url =         (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'];
    $checkout_page =    (History::get('checkout') ? History::get('checkout') : BASE_PATH."checkout");
    $defaultData = array(
      'afterCancelURL' =>                           $host_url.$checkout_page,
      'cancelURL' =>                                $host_url.BASE_PATH.'paypal_cancel',
      'ipnURL' =>                                   $host_url.BASE_PATH.'?command=paypal_ipn',
      'returnURL' =>                                $host_url.BASE_PATH.'paypal_return',
      'authToken' =>                                $gateway_info['settings']['authorizationToken'],
      'currencyCode' =>                             'CAD',
      'error_VerifyPayment_HTTPError' =>            "Error: Unable to communicate with PayPal server to verify payment status - web server needs to support ssl <a href='".$_SERVER["REQUEST_URI"]."'>try again</a>",
      'error_VerifyPayment_NoTXToken' =>            'Error: Transaction Token not found',
      'error_VerifyPayment_InvalidPaymentStatus' => 'Error: Invalid Payment Status',
      'error_VerifyPayment_MissingTXID'	=>          'Error: Missing Transaction Token', // this should NEVER happen unless the postback gets totally mucked
      'error_VerifyPayment_DuplicateTXID' =>        'Error: Duplicate Transaction ID',
      'error_VerifyPayment_MissingGrandTotal' =>    'Missing grand total',
      'error_VerifyPayment_BadTotal' =>             'Total paid and total ordered do not match',
      'error_VerifyPayment_InvalidCurrency' =>      'Currency incorrect or missing',
      'merchantID' =>                               $gateway_info['settings']['merchantID'],
      'simplePaymentURL' =>                         $gateway_info['type']['URL'],
      'submitButtonText' =>                         'Continue to PayPal', // this only gets displayed if the user doesn't have javascript turned on
      'success_VerifyPayment' =>                    "<b>Thank you for your payment</b>.<br />Your transaction has been completed, and a receipt for your purchase has been emailed to you.\n",
      'gateway_type' =>                             $gateway_info['type']['name']
    );
    if (is_array($data)) {
      $this->data = array_merge($defaultData, $data);
    }
    else {
      $this->data = $defaultData;
    }
  }

  public function drawPaymentRedirect($order) {
    $order_items = $order->get_order_items();
    $grouping_name = '';
    if ($order_items && count($order_items) == 1) {
      $p =              new Product($order_items[0]['productID']);
      $data =           $p->get_record();
      $pg =             new Product_Grouping($data['groupingID']);
      $grouping =       $pg->get_record();
      $grouping_name =  $grouping['name'];
    }

    if ($grouping_name == 'Subscriptions_Recurring') {
      $this->drawRecurringPaymentRedirect($order);
    }
    else {
      $this->drawSimplePaymentRedirect($order);
    }
  }

  public function drawRecurringPaymentRedirect($order,$live=true) {
    // Incomplete
    if ($live){
      return
         "<html><body>\r\n"
        ."<form action='https://www.paypal.com/cgi-bin/webscr' method='post'>\r\n"
        ."\t<input type='hidden' name='return' value='".$this->returnURL."' />\r\n"
        ."<input type='hidden' name='cmd' value='_s-xclick'>\r\n"
        ."<input type='hidden' name='hosted_button_id' value='2046317'>\r\n"
        ."<input id='subscribeBtn' type='image' src='https://www.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif' border='0' name='submit' alt=''>\r\n"
        ."<img alt='' border='0' src='https://www.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'>\r\n"
        ."</form>\r\n"
        ."<script type='text/javascript'>document.getElementById('subscribeBtn').style.display='none';document.forms[0].submit();</script>\r\n"
        ."</body></html>";
  }
  return
     "<html><body>\r\n"
    ."<form action='https://www.sandbox.paypal.com/cgi-bin/webscr' method='post'>\r\n"
    ."\t<input type='hidden' name='return' value='".$this->returnURL."' />\r\n"
    ."<input type='hidden' name='cmd' value='_s-xclick'>\r\n"
    ."<input type='hidden' name='hosted_button_id' value='13389'>\r\n"
    ."<input id='subscribeBtn' type='image' src='https://www.sandbox.paypal.com/en_US/i/btn/btn_subscribeCC_LG.gif' border='0' name='submit' alt=''>\r\n"
    ."<img alt='' border='0' src='https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif' width='1' height='1'>\r\n"
    ."</form>\r\n"
    ."<script type='text/javascript'>document.getElementById('subscribeBtn').style.display='none';document.forms[0].submit();</script>\r\n"
    ."</body></html>";
  }

  public function drawSimplePaymentRedirect($Obj_Order){ // pass in object of class=Order
    $this->payment($Obj_Order);
  }

  public function payment($Obj_Order){ // pass in object of class=Order
    $order_record =   $Obj_Order->get_record();
    $order_items =    $Obj_Order->get_order_items();
    switch ($this->gateway_type){
      case 'Paypal (Live)':
        $live = 1;
      break;
      case 'Paypal (Test)':
        $live = 0;
      break;
      default:
        $msg =  "Gateway type ".$this->gateway_type." not handled by this interface.";
        do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Constructor',$msg);
        die($msg);
      break;
    }
    $taxCount = 1;
    $taxAmount = 0;
    while (isset($order_record['tax' . $taxCount . '_cost'])) {
      $taxAmount += $order_record['tax' . $taxCount . '_cost'];
      $taxCount++;
    }
    $Obj_country =      new Country;
    $BCountry =         $Obj_country->get_iso3166($order_record['BCountryID']);
    $out =
       "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
      ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n"
      ."<head>\n"
      ."<title>Proceed to Paypal</title>\n"
      ."</head>\n"
      ."<body>\n"
      ."<form method=\"post\" action=\"".$this->simplePaymentURL."\">\n"
      ."<div>\n"
      ."  <input type='hidden' name=\"cmd\" value=\"_cart\" />\n"
      ."  <input type='hidden' name=\"upload\" value=\"1\" />\n"
      .($live ? "" : "  <input type='hidden' name=\"return\" value=\"".$this->returnURL."\" />\n")
      ."  <input type='hidden' name=\"test_ipn\" value=\"".$this->ipnURL."\" />\n"
      ."  <input type='hidden' name=\"cancel_return\" value=\"".$this->cancelURL."\" />\n"
      ."  <input type='hidden' name=\"business\" value=\"".$this->merchantID."\" />\n"
      ."  <input type='hidden' name=\"currency_code\" value=\"".$this->currencyCode."\" />\n"
      ."  <input type='hidden' name=\"tax_cart\" value=\"".$taxAmount."\" />\n"
      ."  <input type='hidden' name=\"invoice\" value=\"".$order_record['ID']."\" />\n"
      ;
    $itemCount = 1;
    foreach ($order_items as $item) {
      $item_title =         $item['title'];
      $item_sku =           $item['itemCode'];
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
      $out.=
         "    <input type='hidden' name='item_name_".$itemCount."' value='".$item_title."' />\n"
        ."    <input type='hidden' name='item_number_".$itemCount."' value='".$item_sku."' />\n"
        ."    <input type='hidden' name='amount_".$itemCount."' value='".$item_price."' />\n"
        ."    <input type='hidden' name='quantity_".$itemCount."' value='".$item_quantity."' />\n"
        ;
      $itemCount++;
    }
    $out.=
       "  <input id='subbtn' type='submit' value='Continue to PayPal' />\n"
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
  }

  public function simplePaymentCancel() {
    Cart::repopulate_all_from_pending();
    if ($orderID = Cart::pending_order_get_ID()) {
      $Obj_Order = new Order($orderID);
      $Obj_Order->delete();
      Cart::pending_order_unset_ID();
    }
    header('Location: '.$this->afterCancelURL);
  }

  public function simplePaymentVerify() {
    // callback to paypal to see if the payment made matches the payment expected
    $url_parts = parse_url($this->simplePaymentURL);
    if (!isset($_GET['tx']) || strlen($_GET['tx']) == 0) {
      return $this->error_VerifyPayment_NoTXToken;
    }
    // read the post from PayPal system and add 'cmd'
    $req = 'cmd=_notify-synch&tx=' . $_GET['tx'] . '&at=' . $this->authToken;
    $header =
       "POST ".$url_parts['path']." HTTP/1.0\r\n"
      ."Content-Type: application/x-www-form-urlencoded\r\n"
      ."Content-Length: ".strlen($req)."\r\n\r\n";
    //$fp = fsockopen ('www.paypal.com', 80, $errno, $errstr, 30);
    // If possible, securely post back to paypal using HTTPS
    // Your PHP server will need to be SSL enabled
    $fp = @fsockopen ('ssl://'.$url_parts['host'], 443, $errno, $errstr, 30);
    if (!$fp) {
      return $this->error_VerifyPayment_HTTPError;
    }
    fputs ($fp, $header . $req);
    // read the body data
    $res = '';
    $headerdone = false;
    while (!feof($fp)) {
      $line = fgets ($fp, 1024);
      if (strcmp($line, "\r\n") == 0) {
        // read the header
        $headerdone = true;
      }
      else if ($headerdone) {
        // header has been read. now read the contents
        $res .= $line;
      }
    }
    // parse the data
    $lines = explode("\n", $res);
    $keyarray = array();
    if (strcmp($lines[0], "SUCCESS") == 0) {
      foreach ($lines as $line) {
        $bits = explode("=", $line);
        if (count($bits) > 0) {
          if (count($bits) > 1) {
            $keyarray[urldecode($bits[0])] = urldecode($bits[1]);
          }
          else {
            $keyarray[urldecode($bits[0])] = '';
          }
        }
      }
    }
    // check that txn_id has not been previously processed
    // check that receiver_email is your Primary PayPal email
    // check that payment_amount/payment_currency are correct
    // process payment
    // check that the payment_status = Completed
    if (!isset($keyarray['payment_status']) || $keyarray['payment_status'] != 'Completed') {
      return $this->error_VerifyPayment_InvalidPaymentStatus;
    }
    if (!isset($keyarray['txn_id'])) {
      return $this->error_VerifyPayment_MissingTXID;
    }
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `orders`\n"
      ."WHERE\n"
      ."  `gateway_result`='SUCCESS: ".$keyarray['txn_id']."'";
    $rec = new Record;
    $stuff = $rec->get_records_for_sql($sql);
    if (count($stuff) > 1) {
      return $this->error_VerifyPayment_DuplicateTXID;
    }
    // check that the price is the same as what we have recorded
    if (!isset($keyarray['mc_gross'])) {
      return $this->error_VerifyPayment_MissingGrandTotal;
    }
    if ($orderID=Cart::pending_order_get_ID()){
      $Obj_Order = new Order($orderID);
      $record = $Obj_Order->get_record();
      if ($record['cost_grand_total'] != $keyarray['mc_gross']) {
        return $this->error_VerifyPayment_BadTotal;
      }
      if (!isset($keyarray['mc_currency']) || $keyarray['mc_currency'] != $this->currencyCode) {
        return $this->error_VerifyPayment_InvalidCurrency;
      }
      $Obj_Order->set_field('gateway_result', 'Simple Payment Verify: '.$keyarray['payment_status'].' txn_id='.$keyarray['txn_id']);
      switch($keyarray['payment_status']){
        case "Canceled_Reversal":
        case "Completed":
        case "Created":
        case "Processed":
          if (!$Obj_Order->get_field('paymentApproved')){
            do_log(1,__CLASS__.'::'.__FUNCTION__.'()','','Marked as paid - '.$keyarray['invoice']);
            $Obj_Order->mark_paid('',false);
          }
        break;
      }
      Cart::pending_order_unset_ID();
    }
    $out =
       "<p><b>Thank you for your payment.</b><br />\n"
      ."Order number <a href=\"/view_order/?print=2&ID=".$keyarray['invoice']."\" rel=\"external\">".$keyarray['invoice']."</a> has been successfully processed, and a receipt for your purchase has been emailed to you.</p>\n";
    if (System::has_feature('Event-Ticketing')){
      $Obj_Order = new Order($keyarray['invoice']);
      $Obj_COD = new Component_Order_Detail;
      $Obj_COD->_record = $Obj_Order->load();
      $out.= $Obj_COD->draw_print_tickets_link();
    }
    if (!get_userID()){
      return $out;
    }
    $out.=
       "<p>Your order history is shown below. Click on any order number to view transaction details or print out tickets.</p>"
      .draw_auto_report('your_order_history',1);
    return $out;
  }

  public static function IPNPaymentVerify() {
    $req =      'cmd=_notify-validate';
    $tx =       false;
    $orderID =  false;
    foreach ($_REQUEST as $key=>$value) {
      switch ($key){
        case 'invoice':
          $orderID = $value;
        break;
        case 'txn_id':
          $tx = $value;
        break;
      }
      $req.= "&".$key."=".$value;
    }
    $stringData =   get_timestamp()." ".$req."\r\n";
    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Paypal debug info',$stringData);
    $myFile =       "logs/paypal_ipn.txt";
    $fh =           fopen($myFile, 'a') or die("can't open file");
    fwrite($fh, $stringData);
    fclose($fh);
    if (!$tx){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Missing txn_id value',$stringData);
      return;
    }
    if (!$orderID){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Missing invoice value',$stringData);
      return;
    }
    $Obj_Order =            new Order($orderID);
    $gateway_settingsID =   $Obj_Order->get_field('gateway_settingsID');
    $Obj_System =           new System(SYS_ID);
    $gateway_info =         $Obj_System->get_gateway($gateway_settingsID);
    $authToken =            $gateway_info['settings']['authorizationToken'];
    $url_parts =            parse_url($gateway_info['type']['URL']);
    $req =                  'cmd=_notify-synch&tx='.$tx.'&at='. $authToken;
    $header =
       "POST ".$url_parts['path']." HTTP/1.0\r\n"
      ."Content-Type: application/x-www-form-urlencoded\r\n"
      ."Content-Length: ".strlen($req)."\r\n\r\n";
    $fp = @fsockopen ('ssl://'.$url_parts['host'], 443, $errno, $errstr, 30);
    if (!$fp) {
      $msg = "Error: Unable to communicate with PayPal server to verify payment status - web server needs to support ssl <a href='".$_SERVER["REQUEST_URI"]."'>try again</a>";
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()',$msg);
      return $msg;
    }
    fputs ($fp, $header . $req);
    // read the body data
    $res = '';
    $headerdone = false;
    while (!feof($fp)) {
      $line = fgets ($fp, 1024);
      if (strcmp($line, "\r\n") == 0) {
        // read the header
        $headerdone = true;
      }
      else if ($headerdone) {
        // header has been read. now read the contents
        $res .= $line;
      }
    }
    // parse the data
    $lines = explode("\n", $res);
    if ($lines[0]!="SUCCESS"){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Bad IPN verify response','Gateway_settingsID='.$gateway_settingsID.' Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    array_shift($lines);
    sort($lines);
    $vnp = array();
    foreach($lines as &$line){
      $line_arr =   explode('=',$line);
      if (count($line_arr)==2){
        $key =          $line_arr[0];
        $value =        $line_arr[1];
        $value =        urldecode(stripslashes($value));
        $vnp[$key] =    $value;
      }
    }
    if (!isset($vnp['receiver_email']) || $vnp['receiver_email']==''){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','MerchantID missing','Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    if (!isset($vnp['invoice']) || $vnp['invoice']==''){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Invoice number missing','Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    if (!isset($vnp['mc_gross'])){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Payment amount missing','Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    if ($vnp['receiver_email']!=$gateway_info['settings']['merchantID']){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Invalid merchantID - '.$vnp['receiver_email'].' Gateway details: '.$gateway_info['settings']['merchantID'],'Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    $Obj_Order = new Order($vnp['invoice']);
    if (!$Obj_Order->exists()){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Invoice number invalid - '.$vnp['invoice'],'Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    $cost_grand_total = $Obj_Order->get_field('cost_grand_total');
    if ($cost_grand_total!==$vnp['mc_gross']){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Payment amount incorrect - expected '.$cost_grand_total.', received '.$vnp['mc_gross'],'Request:'.$header.$req."\r\nReponse:".$res);
      return;
    }
    $msg =  'IPN Result: '.$vnp['payment_status'].' txn_id='.$vnp['txn_id'];
    $Obj_Order->set_field('gateway_result', $msg);
    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','',$msg);
    switch($vnp['payment_status']){
      case "Canceled_Reversal":
      case "Completed":
      case "Created":
      case "Processed":
        if (!$Obj_Order->get_field('paymentApproved')){
          $Obj_Order->mark_paid('',false);
          do_log(1,__CLASS__.'::'.__FUNCTION__.'()','','Marked as paid - '.$vnp['invoice']);
        }
      break;
    }
    return;
  }

  public function get_version(){
    return VERSION_PAYPAL_GATEWAY;
  }
}
?>