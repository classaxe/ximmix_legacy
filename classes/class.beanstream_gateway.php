<?php
define('VERSION_BEANSTREAM_GATEWAY','1.0.4');

/*
Version History:
  1.0.4 (2014-01-06)
    1) Beanstream_Gateway::_setup_get_customer_name() now uses User class to
       load name of customer - contacts cannot place orders

  (Older version history in class.beanstream_gateway.txt)
*/

class Beanstream_Gateway {
  private $_num;
  private $_card_number;
  private $_card_cvd;
  private $_gateway_record;
  private $_order_record;
  private $_order_Items;
  private $_response;
  private $_result;
  private $_URL;

  public function payment($order){
    $this->_setup($order, false);
    $this->_build_request();
//    print $this->_URL."?".$this->_request;die;
    $this->_send_request();
    if ($this->_response===false) {
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','(none)','Cannot connect to Beanstream at '.$this->_URL);
      $msg =
         "<b>ERROR: Cannot connect to payment gateway</b>.<br />\n"
        ."Your credit card has not been billed.<br />\n"
        ."Please contact us to report this error.";
      $this->_Obj_Order->set_field('gateway_result',$msg,true,false);
      $this->_Obj_Order->actions_process_product_pay_failure();
      return false;
    }
    $this->_Obj_Order->set_field('gateway_result',urldecode($this->_response['messageText']),true,false);
    $authCode = $this->_response['authCode'];
    if ($this->_response['trnApproved']=="1") {
      switch ($authCode) {
        case "TEST":
          $this->_Obj_Order->mark_paid('TEST: ',false);
          do_log(1,__CLASS__.'::'.__FUNCTION__.'()','(none)','Beanstream approved - TEST');
          return $authCode;
        break;
        default:
          $this->_Obj_Order->mark_paid('',false);
          do_log(1,__CLASS__.'::'.__FUNCTION__.'()','(none)','Beanstream approved');
          return $authCode;
        break;
      }
    }
    $this->_Obj_Order->actions_process_product_pay_failure();
    return $authCode;
  }

  protected function _build_request(){
    $this->_build_request_header();
    $this->_build_request_add_card_details();
    $this->_build_request_add_address_billing();
    $this->_build_request_add_address_shipping();
    $this->_build_request_add_items();
    $this->_build_request_add_shipping();
    $this->_build_request_add_taxes();
    $this->_build_request_add_method_surcharge();
    $this->_build_request_add_totals();
  }

  protected function _build_request_header(){
    $this->_request.=
       "requestType=BACKEND"
      ."&trnOrderNumber=".  urlencode($this->_Obj_Order->_get_ID())
      ."&errorPage=https://www.beanstream.com/samples/order_form.asp"
      ."&merchant_id=".     urlencode($this->_gateway_record['settings']['merchantID']);
  }

  protected function _build_request_add_address_billing(){
    $this->_request.=
       "&ordEmailAddress=". urlencode($this->_order_record['BEmail'])
      ."&ordName=".         urlencode($this->_customer_name)
      ."&ordPhoneNumber=".  urlencode($this->_order_record['BTelephone'])
      ."&ordAddress1=".     urlencode($this->_order_record['BAddress1'])
      ."&ordAddress2=".     urlencode($this->_order_record['BAddress2'])
      ."&ordCity=".         urlencode($this->_order_record['BCity'])
      ."&ordProvince=".     urlencode($this->_get_beanstream_state($this->_order_record['BCountryID'], $this->_order_record['BSpID']))
      ."&ordPostalCode=".   urlencode($this->_order_record['BPostal'])
      ."&ordCountry=".      urlencode($this->_get_beanstream_country($this->_order_record['BCountryID']));
  }

  protected function _build_request_add_address_shipping(){
    if ($this->_order_record['SCountryID']){
      $this->_request.=
         "&shipName=".        urlencode($this->_customer_name)
        ."&shipAddress1=".    urlencode($this->_order_record['SAddress1'])
        ."&shipAddress2=".    urlencode($this->_order_record['SAddress2'])
        ."&shipCity=".        urlencode($this->_order_record['SCity'])
        ."&shipProvince=".    urlencode($this->_get_beanstream_state($this->_order_record['SCountryID'], $this->_order_record['SSpID']))
        ."&shipPostalCode=".  urlencode($this->_order_record['SPostal'])
        ."&shipCountry=".     urlencode($this->_get_beanstream_country($this->_order_record['SCountryID']));
    }
  }

  protected function _build_request_add_card_details(){
    $this->_request.=
       "&trnCardOwner=".    urlencode($this->_order_record['payment_card_name'])
      ."&trnCardNumber=".   urlencode($this->_card_number)
      ."&trnCardCvd=".      urlencode($this->_card_cvd)
      ."&trnExpMonth=".     urlencode(substr($this->_order_record['payment_card_expiry'],0,2))
      ."&trnExpYear=".      urlencode(substr($this->_order_record['payment_card_expiry'],3,2));
  }

  protected function _build_request_add_items(){
    foreach ($this->_order_items as $item) {
      $item_title =         $item['title'];
      $item_sku =           $item['productID'];
      $item_quantity =      $item['quantity'];
      $item_price =         $item['price'];
      if ($item['related_object']){
        $Obj_type =         $item['related_object'];
        $Obj_ID =           $item['related_objectID'];
        $Obj =              new $Obj_type($Obj_ID);
        $Obj->load();
        $item_sku.=         "-".$Obj->_get_object_name().'-'.$Obj->record['ID'];
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
      $this->_request.=
         "&prod_name_".$this->_num."=".urlencode($item_title)
        ."&prod_id_".$this->_num."=".urlencode($item_sku)
        ."&prod_quantity_".$this->_num."=".urlencode($item_quantity)
        ."&prod_shipping_".$this->_num."=0.00"
        ."&prod_cost_".$this->_num."=".urlencode($item_price);
      $this->_num++;
    }
  }

  protected function _build_request_add_method_surcharge(){
    if ($this->_order_record['paymentMethodSurcharge']!=0) {
      $this->_request.=
         "&prod_name_".$this->_num."=".urlencode($this->_order_record['paymentMethod'].' Surcharge at '.$this->_order_record['paymentMethodSurcharge'].'% of total')
        ."&prod_id_".$this->_num."=".urlencode($this->_order_record['paymentMethod'].' '.$this->_order_record['paymentMethodSurcharge'].'%')
        ."&prod_quantity_".$this->_num."=1"
        ."&prod_shipping_".$this->_num."=0.00"
        ."&prod_cost_".$this->_num."=".urlencode((float)$this->_order_record['cost_grand_total']-$this->_order_record['cost_sub_total']);
      $this->_num++;
    }
  }

  protected function _build_request_add_shipping(){
    if ($this->_order_record['SMethod']!='') {
      $this->_request.=
         "&prod_name_".$this->_num."=".urlencode('Shipping '.$this->_order_record['SMethod'])
        ."&prod_id_".$this->_num."=".urlencode($this->_order_record['SMethod'])
        ."&prod_quantity_".$this->_num."=1"
        ."&prod_shipping_".$this->_num."=0.00"
        ."&prod_cost_".$this->_num."=".urlencode($this->_order_record['cost_shipping']);
      $this->_num++;
    }
  }

  protected function _build_request_add_taxes(){
    for ($i=1; $i<=20; $i++) {
      $tax_cost =           $this->_order_record['tax'.$i.'_cost'];
      $tax_name =           $this->_order_record['tax'.$i.'_name'];
      $tax_rate =           $this->_order_record['tax'.$i.'_rate'];
      if ($tax_cost!=0) {
        $this->_request.=
           "&prod_name_".$this->_num."=".urlencode($tax_name.' at '.$tax_rate.'%')
          ."&prod_id_".$this->_num."=".urlencode($tax_name.' '.$tax_rate.'%')
          ."&prod_quantity_".$this->_num."=1"
          ."&prod_shipping_".$this->_num."=0.00"
          ."&prod_cost_".$this->_num."=".urlencode($tax_cost);
        $this->_num++;
      }
    }
  }

  protected function _build_request_add_totals(){
    $tax = array();
    for($i=1; $i<=20; $i++){
      if((float)$this->_order_record['tax'.$i.'_cost']>0){
        $tax[] = $this->_order_record['tax'.$i.'_cost'];
      }
    }
    $this->_request.=
      "&trnAmount=".urlencode($this->_order_record['cost_grand_total'])
      .(isset($tax[0]) ? "&ordTax1Price=".$tax[0] : "")
      .(isset($tax[1]) ? "&ordTax2Price=".$tax[1] : "");
  }

  protected function _get_beanstream_country($country){
    return Country::get_iso3166($country);
  }

  protected function _get_beanstream_state($country, $state){
    switch($country){
      case 'CAN':
      case 'USA':
        return $state;
      break;
      default:
        return '--';
      break;
    }
  }

  protected function _send_request(){
    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','(none)','Connect to Beanstream at '.$this->_URL.' - sending request.');
    $Obj_CURL =         new Curl($this->_URL,$this->_request);
    $this->_response =  $Obj_CURL->exec();
  }

  protected function _setup($order){
    $this->_Obj_Order =             $order;
    $this->_gateway_record =        $order->_gateway_record;
    $this->_order_record =          $order->get_record();
    $this->_order_items =           $order->get_order_items();
    $this->_URL =                   $this->_gateway_record['type']['URL'];
    $this->_card_number =           get_var('TCardNumber');
    $this->_card_cvd =              get_var('TCardCvv');
    $this->_num =                   1;
    $this->_setup_get_customer_name();
  }

  protected function _setup_get_customer_name(){
    $Obj_User =           new User($this->_order_record['personID']);
    $Obj_User->load();
    $this->_customer_name =
       ($Obj_User->record['NFirst']!='' ? $Obj_User->record['NFirst'].' ' : '')
      .($Obj_User->record['NMiddle']!='' ? $Obj_User->record['NMiddle'].' ' : '')
      .$Obj_User->record['NLast'];
  }

  public function get_version(){
    return VERSION_BEANSTREAM_GATEWAY;
  }
}

?>