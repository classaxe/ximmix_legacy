<?php
define('VERSION_PAYMENT_METHOD','1.0.10');
/*
Version History:
  1.0.10 (2014-01-29)
    1) Payment_Method::draw_selector() changes to JS for loadTotalCost() to add extra newline

  (Older version history in class.payment_method.txt)
*/
class Payment_Method extends Record{

  function __construct($ID="") {
    parent::__construct("payment_method",$ID);
    $this->_set_has_groups(true);
    $this->_set_assign_type('payment_method');
    $this->_set_object_name('Payment Method');
    $this->_set_name_field('value');
  }

  public function count_methods_available(){
    $methods_available =    $this->get_methods_available();
    $qty = 0;
    foreach ($methods_available as $method){
      if ($method['value']){
        $qty++;
      }
    }
    return $qty;
  }

  public function draw_selector($id_method='TMethod',$width=150,$type='checkout') {
    $id_card_name =     'TCardName';
    $id_card_number =   'TCardNumber';
    $id_card_exp_mm =   'TCardExpiry_mm';
    $id_card_exp_yy =   'TCardExpiry_yy';
    $id_card_cvv =      'TCardCvv';
    $id_surcharge =     'div_surcharge';
    $showcc_arr =       array();
    $method_costs =     array();
    $methods = $this->get_methods_available();
    foreach ($methods as $method) {
      if ($method['value']!='') {
        $method_costs[] =
           pad("  payment_method_arr[\"".$method['value']."\"] = ",40)
          ."{ "
          ."'surcharge' : ".(float)$method['method_surcharge'].", "
          ."'show_cc' : ".($method['get_card_details'] ? 'true' : 'false')." };\n";
        if ($method['get_card_details']!='1') {
          $showcc_arr[] = "    case '".$method['value']."':\n";
        }
      }
    }
    $out = array();
    $value = get_var($id_method);
    $Obj_ReportColumn = new Report_Column;
    if ($this->get_method_count()==1){
      $this->get_first_method($method_text,$method_value);
      $out['html']=
         "<div id='payment_block'>"
        .$method_text
        .draw_form_field('TMethod',$method_value,'hidden')
        ."</div>";
    }
    else {
      $out['html']=
       "<select id=\"".$id_method."\" name=\"".$id_method."\""
      ." style=\"width: ".(((int)$width)+4)."px;\" class=\"formField\" onchange=\"TMethod_change();\">\n"
      .$Obj_ReportColumn->draw_select_options_from_records($value,$methods)
      ."</select>";
    }
    $out['js'] =
       "\n"
      ."// ********************************\n"
      ."// * Payment Method Selector code *\n"
      ."// ********************************\n"
      ."var payment_method_arr = [];\n"
      .implode('',$method_costs)."\n"
      ."function checkout_method_hide_card_details(method) {\n"
      .(count($showcc_arr) ?
         "  switch (method) {\n"
        .implode('',$showcc_arr)
        ."      return true;\n"
        ."    break;\n"
        ."  }\n"
       : ""
       )
      ."  return false;\n"
      ."}\n"
      ."function checkout_validate_payment_details(err_arr){\n"
      ."  var hascost = parseFloat(geid_val('total_cost'));\n"
      ."  var method =  geid_val('".$id_method."');\n"
      ."  if (hascost && !checkout_method_hide_card_details(method)) {\n"
      ."    err_arr = validate_payment_details(err_arr,'".$id_method='TMethod'."','".$id_card_name."','".$id_card_number."','".$id_card_exp_mm."','".$id_card_exp_yy."');\n"
      ."  }\n"
      ."  return err_arr;\n"
      ."}\n"
      ."function TMethod_reveal() {\n"
      ."  var method = geid_val('".$id_method."');\n"
      ."  var hide_cc = (checkout_method_hide_card_details(method));\n"
      ."  payment_method_change(hide_cc,'".$id_method."','".$id_card_name."','".$id_card_number."','".$id_card_exp_mm."','".$id_card_exp_yy."','".$id_card_cvv."');\n"
      ."  if (payment_method_arr[method] && geid(\"".$id_surcharge."\")){ // (for custom forms)\n"
      ."    setDisplay(\"".$id_surcharge."\",parseFloat(payment_method_arr[method]['surcharge']));\n"
      ."    geid(\"".$id_surcharge."_name\").innerHTML = method;\n"
      ."    geid(\"".$id_surcharge."_percent\").innerHTML = parseFloat(payment_method_arr[method]['surcharge']);\n"
      ."  }\n"
      ."}\n"
      ."function TMethod_change() {\n"
      ."  TMethod_reveal();\n"
      ."  loadTotalCost();\n"
      ."}\n"
      .($type=='checkout' ?
         "function loadTotalCost() {\n"
        ."  show_popup_please_wait('<b>Please wait...<\/b><br \/>Recalculating costs based<br \/>on your selection.',200,60);\n"
        ."  geid_set('submode','recalculate');\n"
        ."  geid('form').submit();\n"
        ."}\n"
       : ""
       )
      ."// **********************************\n"
      ."// * End of Payment Method Selector *\n"
      ."// **********************************\n"
      ;
    return
      $out;
  }

  public function get_need_card_details(){
    $methods_available =    $this->get_methods_available();
    foreach ($methods_available as $method){
      if ($method['get_card_details']){
        return true;
      }
    }
    return false;
  }

  public function draw_payment_options($TCardName="",$type=false) {
    $label_width =          200;
    $field_width =          300;
    $pm =                   $this->draw_selector('TMethod',$field_width,$type);
    Page::push_content('javascript',$pm['js']);
    if ($this->get_method_count()==1){
      $this->get_first_method($method_text,$method_value);
    }
    if ($this->get_method_count()==1 && !$this->get_need_card_details()){
      return
         "<div id='payment_block'>"
        ."Click 'Place Order' to proceed with payment for this order using <b>".$method_text."</b>:"
        .draw_form_field('TMethod',$method_value,'hidden')
        ."</div><br />"
        .$this->draw_payment_options_button($type);
    }
    $out =
       "<table id=\"payment_block\" cellpadding='2' cellspacing='0' border='0' style='background:#ffffff;border:1px solid #c0c0c0'>\n"
      ."  <tr class='grid_head_nosort'>\n"
      ."    <th colspan='2'>Payment Details</th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td style='width:".$label_width."px'>I am paying by: <span style='color: #ff0000'>*</span></td>\n"
      ."    <td>\n"
      .($this->get_method_count()==1 ?
          draw_form_field('TMethod_text',$method_text,'text','','','','',1)
          .draw_form_field('TMethod',$method_value,'hidden') : $pm['html'])
      ."</td>\n"
      ."  </tr>\n";
    if ($this->get_need_card_details()){
      $out.=
         "  <tr>\n"
        ."    <td id='row_card_name'>Cardholder Name: <span style='color: #ff0000'>*</span>&nbsp;</td>\n"
        ."    <td>".draw_form_field('TCardName',$TCardName,'text',$field_width)."</td>\n"
        ."  </tr>\n"
        ."  <tr>\n"
        ."    <td id='row_card_number'>Card Number: <span style='color: #ff0000'>*</span></td>\n"
        ."    <td>".draw_form_field('TCardNumber','','text',$field_width)."</td>\n"
        ."  </tr>\n"
        ."  <tr id='row_card_expiry'>\n"
        ."    <td>Expiry Month / Year: <span style='color: #ff0000'>*</span>&nbsp;</td>\n"
        ."    <td>"
        ."<input id='TCardExpiry_mm' name='TCardExpiry_mm' maxlength='2' size='2' style='width:20px;' class='formField' /> / "
        ."<input id='TCardExpiry_yy' name='TCardExpiry_yy' maxlength='2' size='2' style='width:20px;' class='formField' />\n"
        ."<div style='float:right'>CVV: <span style='color: #ff0000'>*</span>&nbsp;"
        ."<input id='TCardCvv' name='TCardCvv' maxlength='3' size='3' style='width:30px;' class='formField' />"
        ."</div></td>\n"
        ."  </tr>\n";
    }
    $out.=
       "</table>"
       .$this->draw_payment_options_button($type);
    return $out;
  }

  public function draw_payment_options_button($type){
    return
       "<div id='payment_block_not_required' style='display:none'>Payment not required for this order.</div>\n"
      ."<div class='txt_c'><input type='submit' value='Place Order' name='btn_payment' "
      ."onclick=\"if(!verify_checkout()){return false;};geid('form').submit();\" class='admin_formButton' style='width: 100px;' /></div>\n"
      ."<script type='text/javascript'>\n"
      ."//<![CDATA[\n"
      ."TMethod_reveal();\n"
      .($type=='custom_form' ?
          "TMethod_change();\n"
       :
          "if(parseFloat(geid_val('total_cost'))){\n"
         ."  setDisplay('payment_block',1);\n"
         ."  setDisplay('payment_block_not_required',0);\n"
         ."}\n"
         ."else {\n"
         ."  geid_set('TMethod','');\n"
         ."  setDisplay('payment_block',0);\n"
         ."  setDisplay('payment_block_not_required',1);\n"
         ."}\n"
      )
      ."//]]>\n"
      ."</script><br />";

  }

  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function get_first_method(&$method_text, &$method_value){
    $methods_available =    $this->get_methods_available();
    foreach ($methods_available as $method){
      if ($method['value']){
        $method_text =     $method['text'];
        $method_value =    $method['value'];
        break;
      }
    }
  }

  public function get_methods_available(){
    static $_methods_available;
    if ($_methods_available){
//      return $_methods_available;
    }
    $sql =
       "SELECT\n"
      ."  `payment_method`.*,\n"
      ."  CONCAT(\n"
      ."    `text`,\n"
      ."    IF(\n"
      ."      `method_surcharge`>0,\n"
      ."      CONCAT(\n"
      ."        ' (+',\n"
      ."        `method_surcharge`,\n"
      ."        '%)'\n"
      ."      ),\n"
      ."      ''\n"
      ."    )\n"
      ."  ) AS `text`\n"
      ."FROM\n"
      ."  `payment_method`\n"
      ."WHERE\n"
      ."  `systemID` IN (1,".SYS_ID.")\n"
      ."ORDER BY\n"
      ."  `text`";
    $records = $this->get_records_for_sql($sql);
    $_methods_available = array();
    foreach($records as $record){
      if ($this->is_visible($record)){
        $_methods_available[] = $record;
      }
    }
    return $_methods_available;
  }

  public function get_method_count(){
    $methods_available =    $this->get_methods_available();
    $method_count = 0;
    foreach ($methods_available as $method){
      if ($method['value']){
        $method_count++;
      }
    }
    return $method_count;
  }

  public function get_method_surcharge($TMethod){
    if ($record = $this->get_record_by_name($TMethod)){
      return $record['method_surcharge'];
    }
    return 0;
  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  public function method_is_offline($method){
    $payment_method_records = $this->get_methods_available();
    $offline_arr = array();
    foreach ($payment_method_records as $payment_method_record) {
      if ($payment_method_record['method_offline']) {
        $offline_arr[] = strtolower($payment_method_record['value']);
      }
    }
    if (in_array(strtolower($method),$offline_arr)) {
      return true;
    }
    return false;

  }

  public function get_version(){
    return VERSION_PAYMENT_METHOD;
  }
}
?>