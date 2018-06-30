<?php
  define ("VERSION_COMPONENT_DONATE","1.0.2");
/*
Version History:
  1.0.2 (2014-01-28)
    1) Newline after JS onload code
  1.0.1 (2014-01-06)
    1) Component_Donate:draw() now has User class to lookup person making donation -
       contacts cannot donate
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Donate extends Component_Base {

  function draw($instance='', $args=array(), $disable_params=false) {
    global $system_vars, $mode, $submode, $command, $ID;
    $ident =            "donate";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'currency' =>                         array('match' => 'enum|CAD,GBP,USD',			'default'=>'',    'hint'=>'CAD|GBP|USD'),
      'extra_instructions_field_list' =>    array('match' => '',							'default'=>'',    'hint'=>'CSV list format: fieldID|type|label|params,fieldID|type|label|params...'),
      'gatewayType' =>                      array('match' => '',							'default'=>'',    'hint'=>'paypal|worldpay'),
      'merchantID' =>                       array('match' => '',							'default'=>'',    'hint'=>'Merchant ID'),
      'minimumAmount' =>                    array('match' => '',							'default'=>0,     'hint'=>'Minimum Amount (number only)'),
      'product_itemcode' =>                 array('match' => '',							'default'=>'',    'hint'=>'Product Itemcode for donations'),
      'field_width' =>                      array('match' => '',							'default'=>300,   'hint'=>'Width in pixels')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $productID = false;
    if ($cp['product_itemcode']!=''){
      $Obj_Product = new Product;
      $productID =   $Obj_Product->get_ID_by_name($cp['product_itemcode']);
    }
    if ($cp['currency']=='' || $cp['gatewayType']=='' || $cp['merchantID']=='' || $cp['minimumAmount']=='' || $cp['product_itemcode']=='' || !$productID) {
      $out.=
         "<div style='border:1px solid red;padding:5px;background-color:#ffe0e0'><b>Error:</b>"
        ."<div>The Donation Component needs to know <ul>"
        .($cp['currency']=='' ?           "<li>What currency to use (CAD, GBP or USD)</li>" : "")
        .($cp['gatewayType']=='' ?        "<li>What kind of payment gateway to use</li>" : "")
        .($cp['merchantID']=='' ?         "<li>What MerchantID should be credited with the payment</li>" : "")
        .($cp['minimumAmount']=='' ?      "<li>What the minimum amount of donation that can be accepted is</li>" : "")
        .($cp['product_itemcode']=='' ?   "<li>What product to use for donations</li>" : "")
        .($cp['product_itemcode']!='' && !$productID ?   "<li>A VALID product to use for donations - ".$cp['product_itemcode']." does not correspond to any recognised product for this site</li>" : "")
        ."</ul>"
        ."An administrator should correct this by specifying Component Parameters for this page.</div></div>";
      return $out;
    }
    $page_gateway =     "https://select.worldpay.com/wcc/purchase";
    $page_gateway =     "https://select-test.worldpay.com/wcc/purchase";
    $extra_instructions_field_arr =     ($cp['extra_instructions_field_list'] ? explode(",",$cp['extra_instructions_field_list']) : array());
    $record_user = array();
    if ($personID = get_userID()) {
      $Obj = new User($personID);
      $record_user = $Obj->get_record();
    }
    $fields =
      explode(
         ',',
         'NTitle,NFirst,NMiddle,NLast,AAddress1,AAddress2,ACity,APostal,ASpID,ACountryID,ATelephone,'
        .'PEmail,Donation_Amount'
      );
    foreach($fields as $field){
      $$field = (isset($_REQUEST[$field]) ? sanitize('html',$_REQUEST[$field]) : (isset($record_user[$field]) ? $record_user[$field] : ''));
    }
    $instructions_arr = array();
    foreach($extra_instructions_field_arr as $field){
      $field_arr = explode('|',$field);
      $$field_arr[0] = (isset($_REQUEST[$field_arr[0]]) ? sanitize('html',$_REQUEST[$field_arr[0]]) :  '');
      $instructions_arr[] = $field_arr[2].": ".$$field_arr[0];
    }
    $instructions = implode("<br />\n",$instructions_arr);
    switch ($submode){
      case "donation_process":
        // Create User
        if (!$personID){
	    do_log(0,__CLASS__."::".__FUNCTION__."()",$submode,"Preparing add new user");
          $Obj =    new User;
          $PUsername =  $Obj->uniq_PUsername("new_");
          $data =
            array(
              'systemID' =>         SYS_ID,
              'PUsername' =>        $PUsername,
              'NFirst' =>           $NFirst,
              'NMiddle' =>          $NMiddle,
              'NLast' =>            $NLast,
              'permActive' =>       1,
              'PEmail' =>           $PEmail,
              'AAddress1' =>        $AAddress1,
              'AAddress2' =>        $AAddress2,
              'ACity' =>            $ACity,
              'APostal' =>          $APostal,
              'ASpID' =>            $ASpID,
              'AEmail' =>           $PEmail,
              'ATelephone' =>       $ATelephone
            );
          $personID =               $Obj->insert($data);
          $Obj->_set_ID($personID);
          get_person_to_session($PUsername,''); // Lets logs start tracking this user immediately
          do_log(0,__CLASS__."::".__FUNCTION__."()",$submode,"Add new user -     \"".$PUsername."\" (ID:".$personID.")");
          $result =                 $Obj->do_email_signup();
          do_log(0,__CLASS__."::".__FUNCTION__."()",$submode,"Sent signup email to \"".$PUsername."\" (ID:".$personID.")");
          $PUsername =              $Obj->get_field("PUsername");
          $PPasswordEnc =           $Obj->get_field("PPassword");
          get_person_to_session($PUsername,$PPasswordEnc);
          do_log(0,__CLASS__."::".__FUNCTION__."()",$submode,"Signed in new user \"".$PUsername."\" (ID:".$personID.")");
       }
        // Create Order

        $Obj_Order = new Order;
        $data =
          array(
            'systemID' =>           SYS_ID,
            'personID' =>           $personID,
            'BAddress1' =>          $AAddress1,
            'BAddress2' =>          $AAddress2,
            'BCity' =>              $ACity,
            'BPostal' =>            $APostal,
            'BSpID' =>              $ASpID,
            'BEmail' =>             $PEmail,
            'BTelephone' =>         $ATelephone,
            'cost_items_pre_tax' => $Donation_Amount,
            'cost_grand_total' =>   $Donation_Amount,
            'instructions' =>       $instructions,
            'paymentStatus' =>      'Pending'
          );
        $orderID = $Obj_Order->insert($data);
	    do_log(0,__CLASS__."::".__FUNCTION__."()",$submode,"Created order ".$orderID);
        // Add OrderItem for the donation
        $Obj_OrderItem = new OrderItem;
        $data =
          array(
            'systemID' =>           SYS_ID,
            'orderID' =>            $orderID,
            'productID' =>          $productID,
            'cost' =>               $Donation_Amount,
            'net' =>                $Donation_Amount,
            'price' =>              $Donation_Amount,
            'quantity' =>           1
          );
        $orderItemID = $Obj_OrderItem->insert($data);
	    do_log(0,__CLASS__."::".__FUNCTION__."()",$submode,"Added item ".$cp['product_itemcode']." to order ".$orderID);
        $http_url = 'https://select-test.worldpay.com/wcc/purchase';
        $request =
           "?"
          ."instId=".$cp['merchantID']."&"
          ."cartId=".$orderItemID."&"
          ."amount=".$Donation_Amount."&"
          ."currency=".$cp['currency']."&"
          ."desc=".$cp['product_itemcode']."&"
          ."name=AUTHORISED&"
          ."address="
          .urlencode(
             $AAddress1
            .($AAddress2 ? "\r\n".$AAddress2 : "")
            ."\r\n".$ACity
          )
          ."&"
          ."postcode=".$APostal."&"
          ."tel=".$ATelephone."&"
          ."email=".$PEmail."&"
          ."testMode=100"
          ;
        header("Location: ".$http_url.$request);
      break;
    }
    Page::push_content(
      'javascript_onload',
      "    afb('Donation_Amount_alt','currency_s','');\n"
    );
    Page::push_content(
      'javascript',
       "var donation_req ='NFirst,NLast,AAddress1,ACity,APostal,ASpID,ACountryID,ATelephone,PEmail,Donation_Amount';\n"
      ."function validate_req(field_csv){\n"
      ."  var errors, field, field_arr, field_ok, form_ok, i;\n"
      ."  field_arr = field_csv.split(',');\n"
      ."  form_ok = true;\n"
      ."  for(i=0; i<field_arr.length; i++){\n"
      ."    field = field_arr[i];\n"
      ."    if (\n"
      ."      (field!='ASpID' && field!='WSpID' && geid_val(field)=='') ||\n"
      ."      (field=='ASpID' && (geid_val('ACountry')=='CAN' || geid_val('ACountry')=='USA' || geid_val('ACountry')=='MEX') && geid_val(field)=='') ||\n"
      ."      (field=='WSpID' && (geid_val('WCountry')=='CAN' || geid_val('WCountry')=='USA' || geid_val('WCountry')=='MEX') && geid_val(field)=='')\n"
      ."    ){\n"
      ."      field_ok = false;\n"
      ."      form_ok = false;\n"
      ."    }\n"
      ."    else {\n"
      ."      field_ok = true;\n"
      ."    }\n"
      ."    if (geid(field+'_label')) {\n"
      ."      geid(field+'_label').style.color=(field_ok ? '' : 'red');\n"
      ."    }\n"
      ."    else{alert(field+'_label');}\n"
      ."  }\n"
      ."  if (!form_ok) {\n"
      ."    alert('Please complete the fields highlighted on the form and try again.');\n"
      ."  }\n"
      ."  return form_ok;\n"
      ."}\n"
    );
    Page::push_content(
      'style',
      'input#Donation_Amount_alt{ text-align: right; }'
    );
    $out.=
       "<table cellpadding='0' cellspacing='0' class='donate' id='donate_".$instance."' summary='Online Donation Form'>\n"
      ."  <tr>\n"
      ."    <th colspan='2' class='donation_header txt_c'>About You</th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2' class='donation_header_spacer'></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label for='NTitle_selector'>Title</label></th>\n"
      ."    <td>"
      .draw_form_field('NTitle',$NTitle,'combo_listdata',$cp['field_width'],'','','',0,0,'','lst_persontitle')
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label for='NFirst'><span id='NFirst_label'>Name</span>, MI, <span id='NLast_label'>Surname</span></label></th>\n"
      ."    <td>"
      .draw_form_field('NFirst',$NFirst,'text',(($cp['field_width']-50)/2))
      ."<span style='padding:0 10px;'>".draw_form_field('NMiddle',$NMiddle,'text',22)."</span>"
      .draw_form_field('NLast',$NLast,'text',(($cp['field_width']-50)/2))
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th class='va_t'><label id='AAddress1_label' for='AAddress1'>Address</label></th>\n"
      ."    <td>"
      .draw_form_field('AAddress1',$AAddress1,'text',$cp['field_width'])."<br />"
      .draw_form_field('AAddress2',$AAddress2,'text',$cp['field_width'])
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label id='ACity_label' for='ACity'>Town / City</label></th>\n"
      ."    <td>"
      .draw_form_field('ACity',$ACity,'text',$cp['field_width'])
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label id='ASpID_label' for='ASpID_selector'>State / Province</label></th>\n"
      ."    <td>"
      .draw_form_field('ASpID',$ASpID,'combo_listdata',$cp['field_width'],'','','',0,0,'','lst_sp')
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label id='APostal_label' for='APostal'>Postal Code</label></th>\n"
      ."    <td>"
      .draw_form_field('APostal',$APostal,'text',(($cp['field_width']-50)/2))
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label id='ACountryID_label' for='ACountryID'>Country</label></th>\n"
      ."    <td>"
      .draw_form_field('ACountryID',$ACountryID,'selector_listdata',$cp['field_width'],'','','',0,0,'','lst_country')
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label id='ATelephone_label' for='ATelephone'>Phone Number</label></th>\n"
      ."    <td>"
      .draw_form_field('ATelephone',$ATelephone,'text',(($cp['field_width']-50)/2))
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label id='PEmail_label' for='PEmail'>Email Address</label></th>\n"
      ."    <td>"
      .draw_form_field('PEmail',$PEmail,'text',$cp['field_width'])
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2' class='donation_header_spacer'></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th colspan='2' class='donation_header txt_c'>Your Donation</th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2' class='donation_header_spacer'></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th><label id='Donation_Amount_label' for='Donation_Amount_selector'>Donation (".$system_vars['defaultCurrencySymbol'].")</label></th>\n"
      ."    <td>"
      .draw_form_field('Donation_Amount',$Donation_Amount,'combo_listdata',$cp['field_width'],'','','','','','','lst_donation_amount')
      ."</td>\n"
      ."  </tr>\n"
      .(count($extra_instructions_field_arr) ?
          "  <tr>\n"
         ."    <td colspan='2' class='donation_header_spacer'></td>\n"
         ."  </tr>\n"
         ."  <tr>\n"
         ."    <th colspan='2' class='donation_header txt_c'>Other Information</th>\n"
         ."  </tr>\n"
         ."  <tr>\n"
         ."    <td colspan='2' class='donation_header_spacer'></td>\n"
         ."  </tr>\n"
        : ""
       );
    foreach($extra_instructions_field_arr as $field_item){
      $field_arr =  explode('|',$field_item);
      $fieldID =    $field_arr[0];
      $type =       $field_arr[1];
      $label =      $field_arr[2];
      $params =    (isset($field_arr[3]) ? $field_arr[3] : '');
      switch ($type){
        case "combo_listdata":
          $label_for_ID =   $fieldID."_selector";
        break;
        case "radio_listdata":
          // Find value used to deletrmine ID for first item in list
          $sql =
            "SELECT\n"
           ."  `value`\n"
           ."FROM\n"
           ."  `listdata`\n"
           ."INNER JOIN `listtype` ON\n"
           ."  `listdata`.`listTypeID` = `listtype`.`ID`\n"
           ."WHERE\n"
           ."  `listtype`.`name` = \"".$params."\" AND\n"
           ."  `listdata`.`systemID` IN(1,".SYS_ID.")\n"
           ."ORDER BY\n"
           ."  `seq`,`textEnglish`\n"
           ."LIMIT 0,1";
          $label_for_ID =   $fieldID."_".$this->get_field_for_sql($sql);
        break;
        default:
          $label_for_ID =   $fieldID;
        break;
      }
      $out.=
         "  <tr>\n"
        ."    <th>"
        .($label_for_ID ? "<label for='".$label_for_ID."'>".$label."</label>" : $label)
        ."</th>\n"
        ."    <td>"
        .draw_form_field($fieldID,'',$type,$cp['field_width'],'','','','','','',$params)
        ."</td>\n"
        ."  </tr>\n";
    }
    $out.=
       "  <tr>\n"
      ."    <td colspan='2' class='txt_c'>"
      ."<input type='submit' class='formButton' style='width:60px' onclick=\"if (validate_req(donation_req)){geid_set('submode','donation_process')}else{return false}\" value='Submit' />\n"
      ."<input type='reset' class='formButton' style='width:60px' onclick=\"if (!confirm('Reset this form?')){ return false }\" value='Reset' />\n"
      ."</td>\n"
      ."  </tr>\n"
      ."</table>"
      ."<p class='txt_c'>"
      ."<script type='text/javascript' src='https://select.worldpay.com/wcc/logo?instId=".$cp['merchantID']."'></script>"
      ."</p>"
      ;
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_DONATE;
  }
}
?>