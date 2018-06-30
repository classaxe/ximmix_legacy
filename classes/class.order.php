<?php
define('VERSION_ORDER','1.0.68');
/*
Version History:
  1.0.68 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.order.txt)
*/
class Order extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, personID, BAddress1, BAddress2, BCity, BCountryID, BEmail, BPostal, BSpID, BTelephone, category, cost_grand_total, cost_items_pre_tax, cost_shipping, cost_sub_total, credit_memo_for_orderID, credit_memo_notes_admin, credit_memo_notes_customer, credit_memo_refund_awarded, credit_memo_status, credit_memo_transaction_code, custom_1, custom_2, custom_3, custom_4, custom_5, custom_6, custom_7, custom_8, custom_9, custom_10, deliveryMethod, deliveryStatus, gateway_result, gateway_settingsID, instructions, notes, originating_page, paymentAmount, paymentApproved, paymentMethod, paymentMethodSurcharge, paymentStatus, payment_card_expiry, payment_card_name, payment_card_partial, processed, qb_ident, SAddress1, SAddress2, SCity, SCountryID, SMethod, SPostal, SSpID, tax1_cost, tax1_name, tax1_rate, tax2_cost, tax2_name, tax2_rate, tax3_cost, tax3_name, tax3_rate, tax4_cost, tax4_name, tax4_rate, tax5_cost, tax5_name, tax5_rate, tax6_cost, tax6_name, tax6_rate, tax7_cost, tax7_name, tax7_rate, tax8_cost, tax8_name, tax8_rate, tax9_cost, tax9_name, tax9_rate, tax10_cost, tax10_name, tax10_rate, tax11_cost, tax11_name, tax11_rate, tax12_cost, tax12_name, tax12_rate, tax13_cost, tax13_name, tax13_rate, tax14_cost, tax14_name, tax14_rate, tax15_cost, tax15_name, tax15_rate, tax16_cost, tax16_name, tax16_rate, tax17_cost, tax17_name, tax17_rate, tax18_cost, tax18_name, tax18_rate, tax19_cost, tax19_name, tax19_rate, tax20_cost, tax20_name, tax20_rate, taxes_shipping, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID=""){
    parent::__construct("orders",$ID);
    $this->_set_assign_type('order');
    $this->_set_has_archive(true);
    $this->_set_has_categories(true);
    $this->_set_object_name('Order');
    $this->_set_message_associated('and associated items have');
  }
  // Deprecated - do not use!
  // Added back for short-term support of hrpyr's custom.php code - remove once done with
  function process_product_ordered_actions() {
    $this->actions_process_product_ordered();
  }

  function actions_process_product_ordered() {
    $ID_arr =   explode(",",$this->_get_ID());
    for ($i=0; $i<count($ID_arr); $i++){
      $ObjOrder =         new Order($ID_arr[$i]);
      $items =            $ObjOrder->get_order_items();
      $sourceType =       'product';
      $sourceTrigger =    'product_order';
      $personID =         $ObjOrder->get_field('personID');
      $ObjAction =        new Action();
      for ($j=0; $j<count($items); $j++) {
        $sourceID =       $items[$j]['productID'];
        $triggerType =    'order_items';
        $triggerObject =  'OrderItem';
        $triggerID =      $items[$j]['ID'];
        $ObjAction->execute($sourceType,$sourceID,$sourceTrigger,$personID,$triggerType,$triggerObject,$triggerID);
      }
    }
    do_log(0,__CLASS__.'::'.__FUNCTION__.'():','','Order '.$this->_get_ID().' executed product_order actions.');
    return true;
  }

  function actions_process_product_pay() {
    $ID_arr =   explode(",",$this->_get_ID());
    $orders_processed = array();
    for ($i=0; $i<count($ID_arr); $i++){
      $ObjOrder =         new Order($ID_arr[$i]);
      $processed =          $ObjOrder->get_field('processed');
      if ($processed==0) {
        $items =            $ObjOrder->get_order_items();
        $sourceType =       'product';
        $sourceTrigger =    'product_pay';
        $personID =         $ObjOrder->get_field('personID');
        $ObjAction =        new Action();
        for ($j=0; $j<count($items); $j++) {
          $sourceID =       $items[$j]['productID'];
          $triggerType =    'order_items';
          $triggerObject =  'OrderItem';
          $triggerID =      $items[$j]['ID'];
          $ObjAction->execute($sourceType,$sourceID,$sourceTrigger,$personID,$triggerType,$triggerObject,$triggerID);
        }
        $ObjOrder->set_field('processed',"1",true,false);
        $orders_processed[] = $ID_arr[$i];
      }
    }
    do_log(0,__CLASS__.'::'.__FUNCTION__.'():','','Order '.$this->_get_ID().' processed.');
    return implode(",",$orders_processed);
  }

  function actions_process_product_pay_failure() {
    $ID_arr =   explode(",",$this->_get_ID());
    $orders_processed = array();
    for ($i=0; $i<count($ID_arr); $i++){
      $ObjOrder =         new Order($ID_arr[$i]);
      $items =            $ObjOrder->get_order_items();
      $sourceType =       'product';
      $sourceTrigger =    'product_pay_failure';
      $personID =         $ObjOrder->get_field('personID');
      $ObjAction =        new Action();
      for ($j=0; $j<count($items); $j++) {
        $sourceID =       $items[$j]['productID'];
        $triggerType =    'order_items';
        $triggerObject =  'OrderItem';
        $triggerID =      $items[$j]['ID'];
        $ObjAction->execute($sourceType,$sourceID,$sourceTrigger,$personID,$triggerType,$triggerObject,$triggerID);
      }
//      $ObjOrder->set_field('processed',"1");
      $orders_processed[] = $ID_arr[$i];
    }
    do_log(0,__CLASS__.'::'.__FUNCTION__.'():','','Order '.$this->_get_ID().' NOT processed.');
    return implode(",",$orders_processed);
  }

  function batch_process($orders_arr) {
    foreach ($orders_arr as $order) {
      $this->_set_ID($order);
      $this->mark_paid();
    }
  }

  function command($mode,$orderID=false) {
    global $personID;
    $this->_set_ID($orderID);
    switch ($mode) {
      case "save":
        return $this->save($personID);
      break;
      case "payment":
        return $this->payment();
      break;
      case "mark_paid":
        return $this->mark_paid();
      break;
      default:
        print "Invalid mode $mode";
        return 0;
      break;
    }
  }

  function count_credit_memos() {
    if ($this->_get_ID()=="") {
      return false;
    }
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `credit_memo_for_orderID` IN(".$this->_get_ID().") AND"
      ."  `archive` = 0";
//    z($sql);die;
    return $this->get_field_for_sql($sql);
  }

  function count_registered_events() {
    if ($this->_get_ID()=="") {
      return false;
    }
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `registerevent`\n"
      ."WHERE\n"
      ."  `orderID` IN(".$this->_get_ID().") AND"
      ."  `archive` = 0";
//    z($sql);die;
    return $this->get_field_for_sql($sql);
  }

  function create($personID,$TMethod,$TCardName,$TCardNumber,$TCardExpiry_mm,$TCardExpiry_yy) {
    $Obj_PM = new Payment_Method;
    $paymentMethodSurcharge = $Obj_PM->get_method_surcharge($TMethod);
    $data =
      array(
        'personID' =>               $personID,
        'systemID' =>               SYS_ID,
        'BAddress1' =>              addslashes($_REQUEST['BAddress1']),
        'BAddress2' =>              addslashes($_REQUEST['BAddress2']),
        'BCity' =>                  addslashes($_REQUEST['BCity']),
        'BSpID' =>                  addslashes($_REQUEST['BSpID']),
        'BPostal' =>                addslashes($_REQUEST['BPostal']),
        'BCountryID' =>             addslashes($_REQUEST['BCountryID']),
        'BTelephone' =>             addslashes($_REQUEST['BTelephone']),
        'BEmail' =>                 addslashes($_REQUEST['BEmail']),
        'category' =>               addslashes(get_var('category')),
        'paymentMethod' =>          addslashes($TMethod),
        'paymentMethodSurcharge' => addslashes($paymentMethodSurcharge),
        'payment_card_name' =>      addslashes($TCardName),
        'payment_card_partial' =>   addslashes($this->get_obfuscated_card_number($TCardNumber)),
        'payment_card_expiry' =>    addslashes(($TCardExpiry_mm=='' ? '' : $TCardExpiry_mm."/".$TCardExpiry_yy)),
        'paymentStatus' =>          'Pending',
        'history_created_by' =>     $personID
      );
    $orderID = $this->insert($data);
    $this->_set_ID($orderID);
    $this->category_assign(get_var('category'),SYS_ID);
    Cart::pending_order_set_ID($orderID);
    return $orderID;
  }

  function delete() {
    $sql =
       "DELETE FROM\n"
      ."  `order_items`\n"
      ."WHERE\n"
      ." `orderID` IN(".$this->_get_ID().")";
    $this->do_sql_query($sql);
    parent::delete();
  }

  function download_pdf($pdf_template_path){
    global $system_vars;
    if (!file_exists($pdf_template_path)){
      die('PDF template file not found - '.$pdf_template_path);
    }
    $_order =       $this->get_record();
    $_order_items = $this->get_order_items(true);
    $Obj_Person =   new Person($_order['personID']);
    $_person =      $Obj_Person->get_record();
    $items_arr =
      array(
        'itemcode' =>   array(),
        'title' =>      array(),
        'quantity' =>   array(),
        'price' =>      array()
      );
    $items = 0;
    foreach ($_order_items as $order_item){
      $items_arr['itemcode'][] =    htmlentities($order_item['itemCode']);
      $items+=                      $order_item['quantity'];
      $items_arr['title'][] =       $order_item['quantity']." x ".htmlentities($order_item['title']);
      $items_arr['price'][] =       $system_vars['defaultCurrencySymbol'].$order_item['price'];
    }
    $_tax_columns = array();
    foreach($_order_items as $_order_item){
      for ($i=1; $i<=20; $i++){
        if ($_order_item['tax'.$i.'_cost']!='0.00'){
          $_tax_columns[$i] = array(
            'name' =>   $_order_item['tax'.$i.'_name'],
            'rate' =>   $_order_item['tax'.$i.'_rate'],
            'column' => $i,
            'cost' =>   0,
            'costs' =>  array()
          );
        }
      }
    }
    $tax_columns = array();
    foreach($_tax_columns as &$_tax_column){
      foreach($_order_items as $_order_item){
        if ($_order_item['tax'.$_tax_column['column'].'_cost']!='0.00'){
          $_tax_column['cost']+= (float)$_order_item['tax'.$_tax_column['column'].'_cost'];
          $_tax_column['costs'][] = $_order_item['tax'.$_tax_column['column'].'_rate']."%";
        }
        else {
          $_tax_column['costs'][] = "";
        }
      }
      $tax_columns[] = $_tax_column;
    }
    $data_arr =
      array(
        'orderid' =>    $_order['ID'],
        'date' =>
           substr($_order['history_created_date'],8,2)." "
          .MM_to_MMM(substr($_order['history_created_date'],5,2))." "
          .substr($_order['history_created_date'],0,4)."\n",
        'Home' =>
           ($_person['NTitle'] ?    $_person['NTitle']." " : "")
          .($_person['NFirst'] ?    $_person['NFirst']." " : "")
          .($_person['NMiddle'] ?   $_person['NMiddle']." " : "")
          .($_person['NLast'] ?     $_person['NLast']." " : "")
          .($_person['NProfDes'] ?  $_person['NProfDes']." " : "")
          ."\n"
          .($_person['AAddress1'] ? $_person['AAddress1']."\n" : "")
          .($_person['AAddress2'] ? $_person['AAddress2']."\n" : "")
          .($_person['ACity'] ?     $_person['ACity']."\n" : "")
          .($_person['ASpID'] ?     $_person['ASpID']."\n" : "")
          .($_person['APostal'] ?   $_person['APostal']."\n" : "")
          .($_person['ACountryID'] ?$_person['ACountryID']."\n" : ""),
        'Work' =>
           ($_person['WCompany'] ?  $_person['WCompany']."\n" : "")
          .($_person['WAddress1'] ? $_person['WAddress1']."\n" : "")
          .($_person['WAddress2'] ? $_person['WAddress2']."\n" : "")
          .($_person['WCity'] ?     $_person['WCity']."\n" : "")
          .($_person['WSpID'] ?     $_person['WSpID']."\n" : "")
          .($_person['WPostal'] ?   $_person['WPostal']."\n" : "")
          .($_person['WCountryID'] ?$_person['WCountryID']."\n" : ""),
        'Billing' =>
           ($_order['payment_card_name'] ?  $_order['payment_card_name']."\n" : "")
          .$_order['paymentMethod']." ".($_order['payment_card_partial']!='************0000' ?  $_order['payment_card_partial']."\n" : "\n")
          .($_order['gateway_result'] ? "Status: ".$_order['gateway_result']."\n" : "")
          .($_order['payment_card_name']||$_order['payment_card_partial']||$_order['gateway_result'] ? "\n" : "")
          .($_order['BAddress1'] ?  $_order['BAddress1']."\n" : "")
          .($_order['BAddress2'] ?  $_order['BAddress2']."\n" : "")
          .($_order['BCity'] ?      $_order['BCity']."\n" : "")
          .($_order['BSpID'] ?      $_order['BSpID']."\n" : "")
          .($_order['BPostal'] ?    $_order['BPostal']."\n" : "")
          .($_order['BCountryID'] ? $_order['BCountryID']."\n" : ""),
        'tax1_name' =>      (isset($tax_columns[0]) ? $tax_columns[0]['name'] : ""),
        'tax2_name' =>      (isset($tax_columns[1]) ? $tax_columns[1]['name'] : ""),
        'tax3_name' =>      (isset($tax_columns[2]) ? $tax_columns[2]['name'] : ""),
        'tax4_name' =>      (isset($tax_columns[3]) ? $tax_columns[3]['name'] : ""),
        'itemcode' =>       implode("\n",$items_arr['itemcode']),
        'title' =>          implode("\n",$items_arr['title']),
        'tax1' =>           (isset($tax_columns[0]) ? implode("\n",$tax_columns[0]['costs']) : ""),
        'tax2' =>           (isset($tax_columns[1]) ? implode("\n",$tax_columns[1]['costs']) : ""),
        'tax3' =>           (isset($tax_columns[2]) ? implode("\n",$tax_columns[2]['costs']) : ""),
        'tax4' =>           (isset($tax_columns[3]) ? implode("\n",$tax_columns[3]['costs']) : ""),
        'price' =>          implode("\n",$items_arr['price']),
        'items' =>                  $items,
        'cost_items_pre_tax' =>     $system_vars['defaultCurrencySymbol'].two_dp($_order['cost_items_pre_tax']),
        'shipping_method' =>        $_order['SMethod'],
        'cost_shipping' =>          ($_order['SMethod'] !='' ? $system_vars['defaultCurrencySymbol'].two_dp($_order['cost_shipping']) : ""),
        'tax1_name_rate' =>         (isset($tax_columns[0]) ? $tax_columns[0]['name'].' at '.$tax_columns[0]['rate'].'%' : ""),
        'tax2_name_rate' =>         (isset($tax_columns[1]) ? $tax_columns[1]['name'].' at '.$tax_columns[1]['rate'].'%' : ""),
        'tax3_name_rate' =>         (isset($tax_columns[2]) ? $tax_columns[2]['name'].' at '.$tax_columns[2]['rate'].'%' : ""),
        'tax4_name_rate' =>         (isset($tax_columns[3]) ? $tax_columns[3]['name'].' at '.$tax_columns[3]['rate'].'%' : ""),
        'tax1_total' =>             (isset($tax_columns[0]) ? $system_vars['defaultCurrencySymbol'].two_dp($tax_columns[0]['cost']) : ""),
        'tax2_total' =>             (isset($tax_columns[1]) ? $system_vars['defaultCurrencySymbol'].two_dp($tax_columns[1]['cost']) : ""),
        'tax3_total' =>             (isset($tax_columns[2]) ? $system_vars['defaultCurrencySymbol'].two_dp($tax_columns[2]['cost']) : ""),
        'tax4_total' =>             (isset($tax_columns[3]) ? $system_vars['defaultCurrencySymbol'].two_dp($tax_columns[3]['cost']) : ""),
        'paymentMethod' =>          ((float)$_order['paymentMethodSurcharge']? $_order['paymentMethod']." charge" : ""),
        'paymentMethodSurcharge' => ((float)$_order['paymentMethodSurcharge']? $_order['paymentMethodSurcharge']."%" : ""),
        'cost_grand_total' =>       $system_vars['defaultCurrencySymbol'].two_dp($_order['cost_grand_total']),
        'paymentStatus' =>          $_order['paymentStatus']
// This line for backward compatability for incorrectly named CICBV PDF template field
// Remove once fixed
       ,'cost' =>      implode("\n",$items_arr['price'])
      );
//    y($data_arr);die;
//    $rand = mt_rand(0,mt_getrandmax());
    $path = "./UserFiles/";
    $xfdf_filename =    "order_".$this->_get_ID().".xfdf";
    $pdf_filename =     "order_".$this->_get_ID().".pdf";
    if (file_exists($path.$xfdf_filename)) {
      unlink($path.$xfdf_filename);
    }
    if (file_exists($path.$pdf_filename)) {
      unlink($path.$pdf_filename);
    }
    $Obj_FDF =  new FDF;
    $fdf_data = $Obj_FDF->get_XFDF($pdf_template_path,$data_arr);
    $Obj_FS =   new FileSystem;
    $Obj_FS->write_file($path.$xfdf_filename,$fdf_data);
    $cmd =      "pdftk ".$pdf_template_path." fill_form ".$path.$xfdf_filename." output ".$path.$pdf_filename." flatten";
//    die($cmd);
    exec($cmd);
    $pdf_data = file_get_contents($path.$pdf_filename);
    header_mimetype_for_extension('pdf');
    header("Content-Disposition: attachment;filename=\"".$pdf_filename."\"");
    header('Content-Length: '.strlen($pdf_data));
    print $pdf_data;
    if (file_exists($path.$xfdf_filename)) {
      unlink($path.$xfdf_filename);
    }
    if (file_exists($path.$pdf_filename)) {
      unlink($path.$pdf_filename);
    }
    flush();
    die;
  }

  function draw_order_summary($payment_status) {
    $sql =
      "SELECT\n"
     ."  `oi`.*,\n"
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
     ."  `system`.`textEnglish` AS `systemTitle`\n"
     ."FROM\n"
     ."  `order_items` AS `oi`\n"
     ."LEFT JOIN `product` AS `pr` ON\n"
     ."  `oi`.`productID` = `pr`.`ID`\n"
     ."LEFT JOIN `product_grouping` AS `pg` ON\n"
     ."  `pr`.`groupingID` = `pg`.`ID`\n"
     ."LEFT JOIN `system` ON\n"
     ."  `oi`.`systemID` = `system`.`ID`\n"
     ."WHERE\n"
     ."  `oi`.`orderID` = ".$this->_get_ID()."\n"
     ."ORDER BY\n"
     ."  `product_grouping_name`,\n"
     ."  `seq`,\n"
     ."  `title`,\n"
     ."  `creditMemo`,\n"
     ."  `creditMemoID`\n";
    $items = $this->get_records_for_sql($sql);
    $Obj_Product_Catalogue = new Product_Catalogue_Order_History;
    $args =
      array(
        'items' =>            $items,
        'paymentStatus' =>    $payment_status,
        'BCountryID' =>       '',
        'BSpID' =>            '',
        '_orderID' =>         $this->_get_ID()
      );

    return
       draw_form_field('ID',$this->_get_ID(),'hidden')
      .$Obj_Product_Catalogue->draw($args);
      ;
  }

  function draw_person_details($personID='',$default_CountryID='',$default_SpID='',$default_address='home') {
    global $submode;
    global $checkout_NFirst, $checkout_NMiddle, $checkout_NLast;
    global $BAddress1, $BAddress2, $BCity, $BSpID;
    global $BPostal, $BCountryID, $BEmail, $BTelephone, $WCompany;
    $label_width = 200;
    $field_width = 300;
    if ($submode=="") {
      if ($personID) {
        $prefix = (strToLower($default_address)=='home' ? 'A' : 'W');
        $Obj =              new Person($personID);
        $row =              $Obj->get_record();
        $checkout_NFirst =  $row['NFirst'];
        $checkout_NMiddle = $row['NMiddle'];
        $checkout_NLast =   $row['NLast'];
        $WCompany =         $row['WCompany'];
        $BAddress1 =        $row[$prefix.'Address1'];
        $BAddress2 =        $row[$prefix.'Address2'];
        $BCity =            $row[$prefix.'City'];
        $BSpID =            $row[$prefix.'SpID'];
        $BPostal =          $row[$prefix.'Postal'];
        $BCountryID =       $row[$prefix.'CountryID'];
        $BEmail =           $row[$prefix.'Email'];
        $BTelephone =       $row[$prefix.'Telephone'];
      }
      else {
        $BSpID =            $default_SpID;
        $BCountryID =       $default_CountryID;
      }
    }
    $Obj = new Listtype();
    $BCountryID_SQL = $Obj->get_sql_options('lst_country',"`value` != '',`text`");
    Page::push_content(
      'javascript',
       "function checkout_validate_billing_details(err_msg,paymentType){\n"
      ."  var n = err_msg.length;\n"
      ."  if (geid_val('checkout_NFirst').length<2 || geid_val('checkout_NLast').length<2) {\n"
      ."    err_msg[n++] = (n)+\") Please enter valid Name in Invoice / Ship to panel\";\n"
      ."  }\n"
      ."  if (geid_val('BAddress1').length<2) {\n"
      ."    err_msg[n++] = (n)+\") Please enter Address in Invoice / Ship to panel\";\n"
      ."  }\n"
      ."  if (geid_val('BCity').length<2) {\n"
      ."    err_msg[n++] = (n)+\") Please enter Town / City in Invoice / Ship to panel\";\n"
      ."  }\n"
      ."  if (geid_val('BCountryID').length<3) {\n"
      ."    err_msg[n++] = (n)+\") Please enter Country in Invoice / Ship to panel\";\n"
      ."  }\n"
      ."  if (geid_val('BCountryID')=='USA' || geid_val('BCountryID')=='CAN' || geid_val('BCountryID')=='AUS' || geid_val('BCountryID')=='MEX') {\n"
      ."    if (geid_val('BSpID')=='' || geid_val('BSpID')=='--') {\n"
      ."      err_msg[n++] = (n)+\") Please enter valid State / Province for selected country.\";\n"
      ."    }\n"
      ."    if (geid_val('BPostal').length<5) {\n"
      ."      err_msg[n++] = (n)+\") Please enter your Postal Code in Invoice / Ship to panel\";\n"
      ."    }\n"
      ."  }\n"
      ."  else {\n"
      ."    if (geid_val('BSpID')!='--') {\n"
      ."      err_msg[n++] = (n)+\") Please enter '--- Outside AUS / CAN / MEX / USA ---' as State / Province.\";\n"
      ."    }\n"
      ."  }\n"
      ."  if (geid_val('BEmail').length<5) {\n"
      ."    err_msg[n++] = (n)+\") Please enter a contact Email Address in Invoice / Ship to panel\";\n"
      ."  }\n"
      ."  if (geid_val('BTelephone').length<5) {\n"
      ."    err_msg[n++] = (n)+\") Please enter a contact Telephone number in Invoice / Ship to panel\";\n"
      ."  }\n"
      ."  if (err_msg.length>0) {\n"
      ."    err_msg[n-1]=err_msg[n-1]+'\\n';\n"
      ."  }\n"
      ."  return err_msg;\n"
      ."}\n"
      ."function BSpID_selector_onchange(){\n"
      ."  combo_selector_set('BSpID','".$field_width."px');\n"
      ."  if(!(geid_val('BSpID_selector')=='--' && geid_val('BSpID_alt')=='')){\n"
      ."    loadTotalCost();\n"
      ."  }\n"
      ."}\n"
    );
    return
       "<div class='checkout_person_details'>"
      .($personID!='' ?
         "<p>If required, you may provide a different address for use with this order only.<br />\n"
        ."To update your normal invoice / shipping address, visit "
        ."<a href=\"".BASE_PATH."manage_profile\">Manage Your Profile</a> and then reload this page.</p>\n"
       : "<p>Please provide valid billing details. A password will be emailled to you at the email address you give to allow you to sign in later and view orders.</p>"
       )
      .draw_form_field('personID',$personID,'hidden')
      ."<table cellpadding='2' cellspacing='0' border='0' style='background:#ffffff;border:1px solid #c0c0c0'>\n"
      ."  <tr class='grid_head_nosort'>\n"
      ."    <th colspan='2'>Invoice / Ship to</th>\n"
      ."  </tr>\n"
      ."  <tr class='admin_containerpanel'>\n"
      ."    <td style='width:".$label_width."px'><label for='checkout_NFirst'>Name (First</label> / <label for='checkout_NMiddle'>Middle</label> / <label for='checkout_NLast'>Last)</label></td>\n"
      ."    <td>"
      ."<div style='width:135px;float:left'>"
      .draw_form_field("checkout_NFirst",$checkout_NFirst,"text","125")
      ."</div>"
      ."<div style='width:40px;float:left'>"
      .draw_form_field("checkout_NMiddle",$checkout_NMiddle,"text","30")
      ."</div>"
      ."<div style='width:125px;float:left'>"
      .draw_form_field("checkout_NLast",$checkout_NLast,"text","125")
      ."</div>"
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><label for='WCompany'>Company</label></td>\n"
      ."    <td>".draw_form_field("WCompany",$WCompany,"text",$field_width)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><label for='BAddress1'>Address</label></td>\n"
      ."    <td>".draw_form_field("BAddress1",$BAddress1,"text",$field_width)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>&nbsp;</td>\n"
      ."    <td>".draw_form_field("BAddress2",$BAddress2,"text",$field_width)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><label for='BCity'>Town / City</label></td>\n"
      ."    <td>".draw_form_field("BCity",$BCity,"text",$field_width)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><label for='BSpID_selector'>State / Province</label></td>\n"
      ."    <td>"
      .draw_form_field(
        'BSpID', $BSpID, 'combo_listdata', $field_width, '', 0,
        " onchange=\"BSpID_selector_onchange()\"",
        0, 0, '', 'lst_sp'
      )
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><label for='BPostal'>Postal / Zip Code</label>, <label for='BCountryID'>Country</label></td>\n"
      ."    <td>".draw_form_field("BPostal",$BPostal,"text",(($field_width-10)/2))
      ." "
      .draw_form_field("BCountryID",$BCountryID,"selector",(($field_width-10)/2),$BCountryID_SQL,'',"onchange=\"loadTotalCost()\"")."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><label for='BEmail'>Email</label></td>\n"
      ."    <td>".draw_form_field("BEmail",$BEmail,"text",$field_width)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td><label for='BTelephone'>Telephone</label></td>\n"
      ."    <td>".draw_form_field("BTelephone",$BTelephone,"text",$field_width)."</td>\n"
      ."  </tr>\n"
      ."</table>\n"
      ."</div>\n";
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with change history and ordered items";
    $extra_delete =
       "DELETE FROM `orders`                 WHERE `archive`=1 AND `archiveID` IN(".$targetID.");\n"
      ."DELETE FROM `order_items`            WHERE `orderID` IN(".$targetID.");\n";
    $Obj = new Backup;
    $extra_select =
       $Obj->db_export_sql_query("`orders`                ","SELECT * FROM `orders` WHERE `archive`=1 AND `archiveID` IN(".$targetID.") ORDER BY `archiveID`,`history_created_date`",$show_fields)
      .$Obj->db_export_sql_query("`order_items`           ","SELECT * FROM `order_items` WHERE `orderID` IN(".$targetID.") ORDER BY `orderID`",$show_fields)."\n";
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_costs() {
    if ($this->_get_ID()=="") {
      do_log(3,__CLASS__.'::'.__FUNCTION__.'():','',"ID missing");
      return false;
    }
    $sql =
       "SELECT\n"
      ."  SUM(`oi`.`net`)        AS `cost_items_pre_tax`,\n"
      ."  SUM(`oi`.`cost`)       AS `cost_items`,\n"
      ."  SUM(`oi`.`tax1_cost`)  AS `tax1_cost`,\n"
      ."  SUM(`oi`.`tax2_cost`)  AS `tax2_cost`,\n"
      ."  SUM(`oi`.`tax3_cost`)  AS `tax3_cost`,\n"
      ."  SUM(`oi`.`tax4_cost`)  AS `tax4_cost`,\n"
      ."  SUM(`oi`.`tax5_cost`)  AS `tax5_cost`,\n"
      ."  SUM(`oi`.`tax6_cost`)  AS `tax6_cost`,\n"
      ."  SUM(`oi`.`tax7_cost`)  AS `tax7_cost`,\n"
      ."  SUM(`oi`.`tax8_cost`)  AS `tax8_cost`,\n"
      ."  SUM(`oi`.`tax9_cost`)  AS `tax9_cost`,\n"
      ."  SUM(`oi`.`tax10_cost`) AS `tax10_cost`,\n"
      ."  SUM(`oi`.`tax11_cost`) AS `tax11_cost`,\n"
      ."  SUM(`oi`.`tax12_cost`) AS `tax12_cost`,\n"
      ."  SUM(`oi`.`tax13_cost`) AS `tax13_cost`,\n"
      ."  SUM(`oi`.`tax14_cost`) AS `tax14_cost`,\n"
      ."  SUM(`oi`.`tax15_cost`) AS `tax15_cost`,\n"
      ."  SUM(`oi`.`tax16_cost`) AS `tax16_cost`,\n"
      ."  SUM(`oi`.`tax17_cost`) AS `tax17_cost`,\n"
      ."  SUM(`oi`.`tax18_cost`) AS `tax18_cost`,\n"
      ."  SUM(`oi`.`tax19_cost`) AS `tax19_cost`,\n"
      ."  SUM(`oi`.`tax20_cost`) AS `tax20_cost`,\n"
      ."  `oi`.`tax1_name`       AS `tax1_name`,\n"
      ."  `oi`.`tax2_name`       AS `tax2_name`,\n"
      ."  `oi`.`tax3_name`       AS `tax3_name`,\n"
      ."  `oi`.`tax4_name`       AS `tax4_name`,\n"
      ."  `oi`.`tax5_name`       AS `tax5_name`,\n"
      ."  `oi`.`tax6_name`       AS `tax6_name`,\n"
      ."  `oi`.`tax7_name`       AS `tax7_name`,\n"
      ."  `oi`.`tax8_name`       AS `tax8_name`,\n"
      ."  `oi`.`tax9_name`       AS `tax9_name`,\n"
      ."  `oi`.`tax10_name`      AS `tax10_name`,\n"
      ."  `oi`.`tax11_name`      AS `tax11_name`,\n"
      ."  `oi`.`tax12_name`      AS `tax12_name`,\n"
      ."  `oi`.`tax13_name`      AS `tax13_name`,\n"
      ."  `oi`.`tax14_name`      AS `tax14_name`,\n"
      ."  `oi`.`tax15_name`      AS `tax15_name`,\n"
      ."  `oi`.`tax16_name`      AS `tax16_name`,\n"
      ."  `oi`.`tax17_name`      AS `tax17_name`,\n"
      ."  `oi`.`tax18_name`      AS `tax18_name`,\n"
      ."  `oi`.`tax19_name`      AS `tax19_name`,\n"
      ."  `oi`.`tax20_name`      AS `tax20_name`,\n"
      ."  `oi`.`tax1_rate`       AS `tax1_rate`,\n"
      ."  `oi`.`tax2_rate`       AS `tax2_rate`,\n"
      ."  `oi`.`tax3_rate`       AS `tax3_rate`,\n"
      ."  `oi`.`tax4_rate`       AS `tax4_rate`,\n"
      ."  `oi`.`tax5_rate`       AS `tax5_rate`,\n"
      ."  `oi`.`tax6_rate`       AS `tax6_rate`,\n"
      ."  `oi`.`tax7_rate`       AS `tax7_rate`,\n"
      ."  `oi`.`tax8_rate`       AS `tax8_rate`,\n"
      ."  `oi`.`tax9_rate`       AS `tax9_rate`,\n"
      ."  `oi`.`tax10_rate`      AS `tax10_rate`,\n"
      ."  `oi`.`tax11_rate`      AS `tax11_rate`,\n"
      ."  `oi`.`tax12_rate`      AS `tax12_rate`,\n"
      ."  `oi`.`tax13_rate`      AS `tax13_rate`,\n"
      ."  `oi`.`tax14_rate`      AS `tax14_rate`,\n"
      ."  `oi`.`tax15_rate`      AS `tax15_rate`,\n"
      ."  `oi`.`tax16_rate`      AS `tax16_rate`,\n"
      ."  `oi`.`tax17_rate`      AS `tax17_rate`,\n"
      ."  `oi`.`tax18_rate`      AS `tax18_rate`,\n"
      ."  `oi`.`tax19_rate`      AS `tax19_rate`,\n"
      ."  `oi`.`tax20_rate`      AS `tax20_rate`,\n"
      ."  `o`.`paymentMethodSurcharge` AS `PMethodSurcharge`,\n"
      ."  `o`.`cost_shipping` AS `cost_shipping`,\n"
      ."  `o`.`taxes_shipping` AS `taxes_shipping`\n"
      ."FROM\n"
      ."  `order_items` AS `oi`\n"
      ."INNER JOIN `orders` AS `o` ON\n"
      ."  `oi`.`orderID` = `o`.`ID`\n"
      ."WHERE\n"
      ."  `o`.`ID` = ".$this->_get_ID()."\n"
      ."GROUP BY\n"
      ."  `o`.`ID`";
    $out = $this->get_record_for_sql($sql);
    if (!$out){
      return false;
    }
    $out['cost_sub_total'] = $out['cost_items'] + $out['cost_shipping'];

    if (strlen($out['taxes_shipping']) > 0) {
      $taxes_shipping_arr = explode(",",$out['taxes_shipping']);
      foreach ($taxes_shipping_arr as &$tax_shipping) {
        $tax_shipping_arr = explode(":",$tax_shipping);
        for ($i=1; $i<=20; $i++){
          if (strToLower($tax_shipping_arr[0])==strToLower($out['tax'.$i.'_name'])){
            $out['tax'.$i.'_cost'] += (float)$tax_shipping_arr[1];
            $out['cost_sub_total'] += (float)$tax_shipping_arr[1];
          }
        }
      }
    }
    $out['cost_grand_total'] =
      $out['cost_sub_total']+
      ($out['cost_sub_total']*$out['PMethodSurcharge']/100);
    return $out;
  }

  public function get_notification_summary($datetime,$systemID,$base_url){
    $records = $this->get_records_since($datetime,$systemID);
    if (!count($records)){
      return '';
    }
    $out =
       "<h2>New ".$this->_get_object_name().$this->plural('1,2')."</h2>"
      ."<table cellpadding='2' cellspacing='0' border='1'>\n"
      ."  <thead>\n"
      ."    <th>Order #</th>\n"
      ."    <th>Name</th>\n"
      ."    <th>Method</th>\n"
      ."    <th>Payment</th>\n"
      ."    <th>Cost</th>\n"
      ."    <th class='datetime'>Created</th>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach ($records as $record){
      $Obj_Person = new Person($record['personID']);
      $Obj_Person->load();
      $NName =
         $Obj_Person->record['NFirst']." "
        .$Obj_Person->record['NMiddle']." "
        .$Obj_Person->record['NLast']
        .($Obj_Person->record['PUsername'] ? " (".$Obj_Person->record['PUsername'].")" : "");
      $User_URL = $base_url.'details/user/'.$Obj_Person->record['ID'];
      $out.=
         "  <tr>\n"
        ."    <td><a target=\"_blank\" href=\"".$base_url.'view_order?ID='.$record['ID']."\">".$record['ID']."</a></td>\n"
        ."    <td>"
        .($User_URL ? "<a target=\"_blank\" href=\"".$User_URL."\">".$NName."</a>" : $NName)
        ."</td>\n"
        ."    <td>".($record['paymentMethod'] ? $record['paymentMethod'] : "&nbsp;")."</td>\n"
        ."    <td>".$record['paymentStatus']."</td>\n"
        ."    <td>".$record['cost_grand_total']."</td>\n"
        ."    <td class='datetime'>".$record['history_created_date']."</td>\n"
        ."  </tr>\n";
    }
    $out.=
       "  </tbody>\n"
      ."</table>\n";
    return $out;
  }

  public function get_obfuscated_card_number($TCardNumber){
    $TCardNumber = preg_replace('/[^0-9]+/','',$TCardNumber);
    return
      ($TCardNumber ?
         substr($TCardNumber,0,4)
           .str_repeat('*',strlen($TCardNumber)-8)
           .substr($TCardNumber,-4)
        :
          ''
      );
  }

  function get_order_items($exclude_credit_memo_items=false) {
    if ($this->_get_ID()=="") {
      do_log(3,__CLASS__.'::'.__FUNCTION__.'():','',"ID missing");
      return false;
    }
    $sql =
       "SELECT\n"
      ."  `order_items`.*,\n"
      ."  `product`.`tax_regimeID`,\n"
      ."  `product`.`itemCode`,\n"
      ."  `product`.`title`\n"
      ."FROM\n"
      ."  `order_items`\n"
      ."LEFT JOIN `product` ON\n"
      ."  `order_items`.`productID` = `product`.`ID`\n"
      ."WHERE\n"
      ."  `orderID` IN(".$this->_get_ID().")"
      .($exclude_credit_memo_items ? " AND\n  `order_items`.`creditMemo`=0" : "");
//    z($sql);
    return $this->get_records_for_sql($sql);
  }

  function get_registered_event_tickets(){
    if ($this->_get_ID()=="") {
      return false;
    }
    $sql =
      "SET SESSION group_concat_max_len = 1000000";
    $this->do_sql_query($sql);
    $sql =
       "SELECT\n"
      ."  COALESCE(\n"
      ."    GROUP_CONCAT(\n"
      ."      `registerevent`.`ID`\n"
      ."      ORDER BY `sequence`\n"
      ."    ),\n"
      ."    ''\n"
      ."  ) `ID_csv`\n"
      ."FROM\n"
      ."  `registerevent`\n"
      ."INNER JOIN `postings` ON\n"
      ."  `postings`.`ID`=`registerevent`.`eventID`\n"
      ."LEFT JOIN `postings` `ticket_layout` ON\n"
      ."  `postings`.`image_templateID` = `ticket_layout`.`ID`\n"
      ."WHERE\n"
      ."  `registerevent`.`orderID` IN(".$this->_get_ID().") AND\n"
      ."  `ticket_layout`.`ID`!=0 AND\n"
      ."  `registerevent`.`archive` = 0";
//    z($sql);die;
    $result = $this->get_field_for_sql($sql);
    $sql =
      "SET SESSION group_concat_max_len = 1024";
    $this->do_sql_query($sql);
    return $result;
  }

  function is_approved(){
    $paymentStatus =    $this->get_field('paymentStatus');
    $Obj_PS =           new lst_payment_status;
    return $Obj_PS->is_approved($paymentStatus);
  }

  function issue_credit_memo($targetValue) {
    $targetValue_arr =  explode("|",$targetValue);
    $orderID =          $targetValue_arr[0];
    $items_arr = array();
    if (trim($targetValue_arr[1])){
      $items_arr =        explode(",",$targetValue_arr[1]);
    }
    $refundTotal =      $targetValue_arr[2];
    $notes_customer =   $targetValue_arr[3];
    $this->_set_ID($orderID);
    $data =             $this->get_record();
    $data['credit_memo_for_orderID'] =      $orderID;
    $data['credit_memo_status'] =           'Pending';
    $data['credit_memo_notes_customer'] =   addslashes($notes_customer);
    $data['credit_memo_refund_awarded'] =     $refundTotal;
    unset($data['ID']);
    unset($data['archive']);
    unset($data['archiveID']);
    unset($data['cost_items_pre_tax']);
    unset($data['cost_shipping']);
    unset($data['cost_sub_total']);
    unset($data['deliveryMethod']);
    unset($data['deliveryStatus']);
    unset($data['gateway_result']);
    unset($data['notes']);
    unset($data['paymentMethod']);
    unset($data['paymentMethodSurcharge']);
    unset($data['paymentStatus']);
    unset($data['payment_card_expiry']);
    unset($data['payment_card_name']);
    unset($data['payment_card_partial']);
    unset($data['processed']);
    unset($data['tax1_cost']);
    unset($data['tax1_name']);
    unset($data['tax1_rate']);
    unset($data['tax2_cost']);
    unset($data['tax2_name']);
    unset($data['tax2_rate']);
    unset($data['tax3_cost']);
    unset($data['tax3_name']);
    unset($data['tax3_rate']);
    unset($data['tax4_cost']);
    unset($data['tax4_name']);
    unset($data['tax4_rate']);
    unset($data['taxes_shipping']);
    unset($data['history_created_by']);
    unset($data['history_created_date']);
    unset($data['history_created_IP']);
    unset($data['history_modified_by']);
    unset($data['history_modified_date']);
    unset($data['history_modified_IP']);
    $creditMemoID = $this->insert($data);
    $Obj = new OrderItem;
    foreach ($items_arr as $item) {
      $item_arr =   explode("=",$item);
      $ID =         $item_arr[0];
      $nra =        $item_arr[1];
      $Obj->_set_ID($ID);
      $data = array();
      $data['price_non_refundable'] = $nra;
      $data['creditMemoID'] = $creditMemoID;
      $data['creditMemo'] = 1;
      $Obj->update($data);
    }
    return $creditMemoID;
  }

  function manage($admin=0) {
    $ident =        "order_manage";
    $parameter_spec = array(
      'header' => array('default'=>'', 'hint'=>'Optional header to proceed orders')
    );
    $cp_settings =  Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
    $out.=          $cp['header'];
    switch($admin) {
      case true:
        $out.=
           "Click on an order number to view details and history"
          .draw_auto_report('manage_orders',1);
      break;
      default:
        $out.=
           (get_userID()!="" ?
            draw_auto_report('your_order_history',0)
           :
            "<p>You must log on to view orders.</p>"
           );
      break;
    }
    return $out;
  }

  static function manage_ecommerce_options() {
    global $sortBy;
    if (!isset($sortBy)) {
      $sortBy = 'value';
    }
    // No toolbars as we have mixed reports here - filters for one may crash the other
    return
       "<p style='text-align:center'>"
      ."<a href='#delivery_methods'><b>Delivery Methods</b></a> | "
      ."<a href='#delivery_status'><b>Delivery Status</b></a> | "
      ."<a href='#effective_period_units'><b>Effective Period Units</b></a> | "
      ."<a href='#gateway_types'><b>Gateway Types</b></a><br />\n"
      ."<a href='#gateway_settings_beanstream'><b>Gateway Settings - Beanstream</b></a> | "
      ."<a href='#gateway_settings_chasepaymentech'><b>Gateway Settings - Chase Paymentech</b></a> | "
      ."<a href='#gateway_settings_paypal'><b>Gateway Settings - Paypal</b></a><br />"
      ."<a href='#payment_methods'><b>Payment Methods</b></a> | "
      ."<a href='#payment_status'><b>Payment Status</b></a> | "
      ."<a href='#product_grouping_column_types'><b>Product Grouping Column Types</b></a> | "
      ."<a href='#product_types'><b>Product Types</b></a> | "
      ."<a href='#refund_status'><b>Refund Status</b></a> "
      ."</p>"
      ."<h3 style='margin:1em 0 0.15em 0'><a name='delivery_methods'></a>Delivery Methods <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('lst_delivery_method',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='delivery_status'></a>Delivery Status <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('lst_delivery_status',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='effective_period_units'></a>Effective Period Units <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('lst_effective_period_units',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='gateway_types'></a>Gateway Types <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('gateway_type',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='gateway_settings_beanstream'></a>Gateway Settings - Beanstream<a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('gateway_settings_beanstream',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='gateway_settings_chasepaymentech'></a>Gateway Settings - Chase Paymentech<a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('gateway_settings_chasepaymentech',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='gateway_settings_paypal'></a>Gateway Settings - Paypal<a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('gateway_settings_paypal',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='payment_methods'></a>Payment Methods <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('payment_method',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='payment_status'></a>Payment Status <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('lst_payment_status',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='product_grouping_column_types'></a>Product Grouping Column Types <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('lst_product_grouping_columns',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='product_types'></a>Product Types <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('lst_product_type',1)
      ."<h3 style='margin:1em 0 0.15em 0'><a name='refund_status'></a>Refund Status <a style='font-size:75%' href='#top'>Top</a></h3>"
      .draw_auto_report('lst_refund_status',1)
      ;
  }

  function manage_refunds($admin=0) {
    switch($admin) {
      case true:
        return
           "<h1 style='margin:0'>Manage Refunds</h1>\n"
          ."<p>Click on an order item to edit it, or click the order link for complete details of the originating order.</p>"
          .draw_auto_report('manage_refunds',1);
      break;
    }
    return $out;
  }

  function mark_paid($prefix='',$reveal_modification=true) {
    $this->actions_process_product_pay();
    $this->load();
    $paymentMethod = $this->get_field('paymentMethod');
    $Obj_PM = new Payment_Method;
    $method = $Obj_PM->get_record_by_name($paymentMethod);
    $paymentStatus = $method['paymentStatus'];
    $this->set_field('paymentApproved',1,true,$reveal_modification);
    do_log(0,__CLASS__.'::'.__FUNCTION__.'():','','Order '.$this->_get_ID().' marked Approved');
    $this->set_field('paymentStatus',$prefix.$paymentStatus,true,$reveal_modification);
    do_log(0,__CLASS__.'::'.__FUNCTION__.'():','','Order '.$this->_get_ID().' marked as '.$paymentStatus);
    if ($method['method_pays_in_full']){
      $amount = $this->get_field('cost_grand_total');
      $this->set_field('paymentAmount',$amount,true,$reveal_modification);
      do_log(0,__CLASS__.'::'.__FUNCTION__.'():','','Order '.$this->_get_ID().' set Payment Amount to '.$amount);
    }
  }

  function on_update_process_payment_actions(){
    global $action_parameters;
    $ID_arr =   explode(",",$action_parameters['triggerID']);
    $orders_processed = array();
    $ObjOrder =           new Order;
    foreach($ID_arr as $ID){
      $ObjOrder->_set_ID($ID);
      if ($ObjOrder->get_field('processed')==0 && $ObjOrder->is_approved()) {
        $items =            $ObjOrder->get_order_items();
        $sourceType =       'product';
        $sourceTrigger =    'product_pay';
        $personID =         $ObjOrder->get_field('personID');
        $ObjAction =        new Action;
        $ObjOrder->set_field('paymentApproved',1,true,false);
        foreach($items as $item){
          $sourceID =       $item['productID'];
          $triggerType =    'order_items';
          $triggerObject =  'OrderItem';
          $triggerID =      $item['ID'];
          $ObjAction->execute(
            $sourceType,$sourceID,$sourceTrigger,
            $personID,$triggerType,$triggerObject,$triggerID
          );
        }
        $ObjOrder->set_field('processed',1,true,false);
        $orders_processed[] = $ID;
        do_log(0,__CLASS__.'::'.__FUNCTION__.'():','','Order '.$ID.' processed.');
      }
    }
    return implode(",",$orders_processed);
  }

  function payment($payment_card_number,$gateway=false,$originating_page='') {
//    die("DEBUG: \$payment_card_number=".$payment_card_number.", \$gateway=".$gateway.x());
    $order_record =     $this->get_record();
    if ($gateway){
      $this->_gateway_record = array(
        'settings',
        'type'
      );
      $Obj_GS = new Gateway_Setting;
      if (!$GS_ID = $Obj_GS->get_ID_by_name($gateway,SYS_ID)){
        $msg =
           "<b>ERROR: The gateway settings '".$gateway."' specified were not found on this site</b>.<br />\n"
          ."Your credit card has not been billed.<br />Please contact us to report this error.";
        $this->set_field('gateway_result',$msg,true,false);
        do_log(3,__CLASS__.'::'.__FUNCTION__.'():','(none)',"Gateway settings '".$gateway."' not found.");
        return false;
      }
      $Obj_GS->_set_ID($GS_ID);
      $this->_gateway_record['settings'] = $Obj_GS->get_record();
      $Obj_GT = new Gateway_Type($this->_gateway_record['settings']['gateway_typeID']);
      $this->_gateway_record['type'] =     $Obj_GT->get_record();
    }
    else {
      $Obj_System = new System(SYS_ID);
      if (!$this->_gateway_record = $Obj_System->get_gateway()){
        $msg =
           "<b>ERROR: There is no payment gateway defined for this system</b>.<br />\n"
          ."Your credit card has not been billed.<br />Please contact us to report this error.";
        $this->set_field('gateway_result',$msg,true,false);
        do_log(3,__CLASS__.'::'.__FUNCTION__.'():','(none)','There is no gateway defined for this system.');
        return false;
      }
    }
    $this->set_field('gateway_settingsID',$this->_gateway_record['settings']['ID'],true,false);
    $grand_total =      $order_record['cost_grand_total'];
    if ((float)$grand_total==0) {
      $this->set_field('gateway_result','Zero-cost order',true,false);
      $this->mark_paid('Zero Cost: ',false);
      do_log(1,__CLASS__.'::'.__FUNCTION__.'():','(none)','Gateway bypassed - zero cost order');
      return true;
    }
    switch($this->_gateway_record['type']['name']) {
      case "Bean Stream":
        $Obj_Beanstream_Gateway = new Beanstream_Gateway;
        $Obj_Beanstream_Gateway->payment($this);
      break;
      case "Chase Paymentech (Live)":
      case "Chase Paymentech (Test)":
        $Obj_ChasePaymentech_Gateway = new ChasePaymentech_Gateway;
        $Obj_ChasePaymentech_Gateway->payment($this); // pass the current order to this function, draw the HTML redirector
        die();
      break;
      case "Paypal (Live)":
      case "Paypal (Test)":
        $settings = array(
          'submitButtonText' => 'Click to continue to PayPal'
        );
        $Obj_PayPal_Gateway = new PayPal_Gateway($settings,$this->_gateway_record);
        $Obj_PayPal_Gateway->payment($this); // pass the current order to this function, draw the HTML redirector
        die();
      break;
      default:
        $msg =  "<b>Error: Cannot process transaction - no valid gateway</b>";
        do_log(3,__CLASS__.'::'.__FUNCTION__.'():','(none)',$msg);
        $this->set_field('gateway_result',$msg,true,false);
        $this->actions_process_product_pay_failure();
        return false;
      break;
    }
  }

  function save($personID){
    global $TCardName, $TCardNumber, $TCardExpiry_mm, $TCardExpiry_yy, $TMethod;
    global $BAddress1, $BAddress2, $BCity, $BSpID, $BPostal, $BCountryID, $BEmail, $BTelephone;
    $Obj_PM = new Payment_Method;
    $paymentMethodSurcharge = $Obj_PM->get_method_surcharge($TMethod);
    $orderID = $this->insert(
      array(
        'personID' =>             $personID,
        'systemID' =>             SYS_ID,
        'BAddress1' =>            addslashes($BAddress1),
        'BAddress2' =>            addslashes($BAddress2),
        'BCity' =>                addslashes($BCity),
        'BCountryID' =>           addslashes($BCountryID),
        'BEmail' =>               addslashes($BEmail),
        'BPostal' =>              addslashes($BPostal),
        'BSpID' =>                addslashes($BSpID),
        'BTelephone' =>           addslashes($BTelephone),
        'category' =>             addslashes($_REQUEST['category']),
        'deliveryMethod' =>       'None',
        'deliveryStatus' =>       'Pending',
        'payment_card_expiry' =>  addslashes($TCardExpiry_mm)."/".addslashes($TCardExpiry_yy),
        'payment_card_name' =>    addslashes($TCardName),
        'payment_card_partial' => addslashes($this->get_obfuscated_card_number($TCardNumber)),
        'paymentMethod' =>        addslashes($TMethod),
        'paymentMethodSurcharge' => $paymentMethodSurcharge,
        'paymentStatus' =>        'Pending',
        'instructions' =>         addslashes(get_var('instructions'))
      )
    );
    $this->_set_ID($orderID);
    $this->category_assign(get_var('category'),SYS_ID);
    $ObjProduct =   new Product;
    $ObjOrderItem = new OrderItem;
    $items = Cart::get_items();
    foreach ($items as $item) {
      $ID =                 $item['ID'];
      $qty =                $item['qty'];
      $related_object =     $item['related_object'];
      $related_objectID =   $item['related_objectID'];
      $ObjProduct->_set_ID($ID);
      $record = $ObjProduct->get_record();
      $data =
        array(
          'systemID' =>             $record['systemID'],
          'orderID' =>              $orderID,
          'productID' =>            $ID,
          'quantity' =>             $qty,
          'related_object' =>       $related_object,
          'related_objectID' =>     $related_objectID,
          'price' =>                $record['price'],
          'price_non_refundable' => $record['price_non_refundable'],
          'tax_regimeID' =>         $record['tax_regimeID']
        );
      $ObjOrderItem->insert($data);
      Cart::item_convert_to_pending($ID, $related_object, $related_objectID);
    }
    Cart::pending_order_set_ID($orderID);
    return $orderID;
  }

  function set_costs() {
    $order = $this->get_record();
    $items = $this->get_order_items();
    foreach ($items as $item) {
      $Obj_OrderItem =  new OrderItem($item['ID']);
      $data =           $Obj_OrderItem->get_costs($item,$order['BCountryID'],$order['BSpID']);
      $Obj_OrderItem->update($data,true,false);
    }
    $data =             $this->get_costs();
    $this->update($data,true,false);
  }

  public function get_version(){
    return VERSION_ORDER;
  }

}
?>