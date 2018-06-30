<?php
define ("VERSION_CUSTOM_FORM","1.0.41");
/*
Version History:
  1.0.41 (2014-02-07)
    1) JS callback functions for ajax fedex rate lookups now contained within sajax
       object to reduce namespace clutter

  (Older version history in class.custom_form.txt)
*/

class Custom_Form extends Record {
  var $_ObjXML;
  var $_current_user_rights =           array();
  static $valid_prefix = "vp_";
  static $js_arr = array();
  static $html_arr = array();
  static $dtd =
"<!DOCTYPE form [
<!ELEMENT form (js*,field*,section*)>
<!ELEMENT section (js*,label?,extra?,row*)>
<!ELEMENT row (label?,spacer?,field*)>
<!ELEMENT js (#PCDATA)>
<!ELEMENT label (#PCDATA|php)*>
<!ELEMENT extra (#PCDATA|php)*>
<!ELEMENT field EMPTY>
<!ELEMENT php (#PCDATA)>

<!ATTLIST form number_style (a|A|i|I|1) #IMPLIED>
<!ATTLIST form default_field_width CDATA #IMPLIED>
<!ATTLIST form default_section_width CDATA #IMPLIED>
<!ATTLIST form id ID #IMPLIED>
<!ATTLIST section id ID #IMPLIED>
<!ATTLIST section line (yes|no) \"no\">
<!ATTLIST section locked (yes|no) \"no\">
<!ATTLIST section hidden (yes|no) \"no\">
<!ATTLIST section width CDATA #IMPLIED>
<!ATTLIST row id ID #IMPLIED>
<!ATTLIST row bgcolor CDATA #IMPLIED>
<!ATTLIST row height CDATA #IMPLIED>
<!ATTLIST row hidden (yes|no) \"no\">
<!ATTLIST field name ID #REQUIRED>
<!ATTLIST field type (
    combo_listdata|combo_sp_selector|date|fees_overview|fees_shipping|
    field_processor|file_upload|int|radio_csvlist|radio_listdata|hidden|
    selector_billing_address|selector_csvlist|
    selector_listdata|selector_payment_method|
    selector_product_child|text|textarea) #REQUIRED>
<!ATTLIST field default CDATA #IMPLIED>
<!ATTLIST field destination CDATA #IMPLIED>
<!ATTLIST field errormsg CDATA #IMPLIED>
<!ATTLIST field height CDATA #IMPLIED>
<!ATTLIST field js CDATA #IMPLIED>
<!ATTLIST field maxlength CDATA #IMPLIED>
<!ATTLIST field nobreak CDATA #IMPLIED>
<!ATTLIST field params CDATA #IMPLIED>
<!ATTLIST field prefix CDATA #IMPLIED>
<!ATTLIST field readonly (yes|no|isset) \"no\">
<!ATTLIST field required (yes|no|depends) \"no\">
<!ATTLIST field suffix CDATA #IMPLIED>
<!ATTLIST field width CDATA #IMPLIED>
]>
";
  // Functions prefixed frm produce parts for forms

  function __construct($ID="") {
    parent::__construct("custom_form",$ID);
    $this->_set_has_actions(true);
    $this->_set_assign_type('custom_form');
    $this->_set_object_name('Custom Form');
    $this->_set_name_field('name');
    $this->_set_message_associated('and associated actions have');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  function actions_execute($trigger,$primaryTable,$primaryObject,$targetID,$data=array()){
    $sourceID =         $this->_get_ID();
    $sourceType =       $this->_get_assign_type();
    $sourceTrigger =    $trigger;
    $triggerID =        $targetID;
    $triggerType =      $primaryTable;
    $triggerObject =    $primaryObject;
    $personID =         $data['ID'];
    $ObjAction =        new Action();
    switch ($trigger) {
      case "custom_form_submit":
        return
          $ObjAction->execute(
            $sourceType,
            $sourceID,
            $sourceTrigger,
            $personID,
            $triggerType,
            $triggerObject,
            $triggerID,
            $data
          );
      break;
    }
    return false;
  }

  function draw($xml_doc,$cp) {
    $this->_xml_doc = $xml_doc;
    $this->_cp = $cp;
    global $submode;
    switch ($submode) {
      case "pay":
        $out = $this->xml_form_process();
      break;
      default:
        $out = $this->form();
      break;
    }
    return $out;
  }

  function draw_fees_overview($width=150) {
    global          $system_vars;
    $lbl_width =    (int)$width-70;
    $out =
       "<div style='width:".(int)$width."px'>\n"
      ."  <div id='div_net_cost' style='line-height:1.25em;float:right'>\n"
      ."    <div class='fl txt_r' style='width:".$lbl_width."px;'>Net Cost</div>\n"
      ."    <div class='fl txt_r' style='width:15px;'>".$system_vars['defaultCurrencySymbol']."</div>"
      ."    <div class='fr txt_r' style='width:55px;'>"
      ."<input id=\"total_net\" class='fr formField txt_r' "
      ."style='width: 50px;background-color: #f0f0f0;color: #404040;' type='text' onfocus='blur()' />"
      ."    </div>"
      ."  </div>"
      ."  <div class='clear'>&nbsp;</div>\n"
      ."  <div id='div_tax_totals' style='width:".(int)$width."px'>&nbsp;</div>\n"
      ."  <div id='div_tax_total' style='line-height:1.25em;float:right'>\n"
      ."    <div class='fl txt_r' style='width:".$lbl_width."px;'>Tax Total</div>\n"
      ."    <div class='fl txt_r' style='width:15px;'>".$system_vars['defaultCurrencySymbol']."</div>"
      ."    <div class='fr txt_r' style='width:55px;'>"
      ."<input id=\"total_tax\" class='fr formField txt_r' "
      ."style='width: 50px;background-color: #f0f0f0;color: #404040;' type='text' onfocus='blur()' />"
      ."    </div>"
      ."  </div>"
      ."  <div class='clear'>&nbsp;</div>\n"
      ."  <div id=\"div_surcharge\" style=\"line-height:1.25em;float:right;display:none\">\n"
      ."    <div class='fl txt_r' style='width:".$lbl_width."px;'><span id='div_surcharge_name'>Payment Method</span> Surcharge at <span id='div_surcharge_percent'>0</span>%</div>\n"
      ."    <div class='fl txt_r' style='width:15px;'>".$system_vars['defaultCurrencySymbol']."</div>"
      ."    <div class='fr txt_r' style='width:55px;'>"
      ."<input id=\"total_surcharge\" class='fr formField txt_r' "
      ."style='width: 50px;background-color: #f0f0f0;color: #404040;' type='text' onfocus='blur()' />"
      ."    </div>"
      ."  </div>"
      ."  <div class='clear'>&nbsp;</div>\n"
      ."  <div style='line-height:1.25em;float:right'>\n"
      ."    <div class='fl txt_r' style='width:".$lbl_width."px;'><b>Total Cost (".$system_vars['defaultCurrencySuffix'].")</b></div>\n"
      ."    <div class='fl txt_r' style='width:15px'>".$system_vars['defaultCurrencySymbol']."</div>"
      ."    <div class='fr txt_r' style='width:55px;'>"
      ."<input id=\"total_cost\" class='fr formField txt_r' "
      ."style='width: 50px;background-color: #f0f0f0;color: #404040;' type='text' onfocus='blur()' />"
      ."    </div>"
      ."  </div>"
      ."</div>"
      ."<div class='clear'>&nbsp;</div>\n"
      ;
    return $out;
  }

  function draw_js_loadTotalCost() {
    $out =
       "// ************************************\n"
      ."// * Custom Form Support Code         *\n"
      ."// ************************************\n"
      ."var purchase_options = {\n"
      ."  'BPostal':'',\n"
      ."  'BCountryID':'',\n"
      ."  'BSpID':'',\n"
      ."  'products':''\n"
      ."};\n"
      ."function payment_method_show(show) {\n"
      ."  for (var i=0; i<10; i++){\n"
      ."    if (geid('payment_block_'+i)) {\n"
      ."      geid('payment_block_'+i).style.display = (show ? '' : 'none');\n"
      ."    }\n"
      ."  }\n"
      ."}\n"
      ."function loadTotalCost(){\n"
      ."  var details_changed = false;\n"
      ."  var BCountryID, xml, opt, BSpID, sub_cost, sub_tax;\n"
      ."  var total_arr, prefix, chosen_products = '', chosen_products_arr = [];\n"
      ."  var ship_tax_line='';\n"
      ."  var page =        geid_val('goto');\n"
      ."  var preferred = (arguments[0] ? arguments[0] : (geid('billing_address_selector') ? geid_val('billing_address_selector').toUpperCase() : geid_val('TBillingAddress').toUpperCase()));\n"
      ."  switch (preferred){\n"
      ."    case 'H':\n"
      ."      prefix = 'A';\n"
      ."	  if (typeof copy_to_billing_address == 'function') {copy_to_billing_address('H');}\n"
      ."	break;\n"
      ."    case 'C':\n"
      ."      prefix = 'W';\n"
      ."      if (typeof copy_to_billing_address == 'function') {copy_to_billing_address('C');}\n"
      ."    break;\n"
      ."    default:\n"
      ."      prefix = 'B';\n"
      ."        if (typeof copy_to_billing_address == 'function') {copy_to_billing_address('X');}\n"
      ."    break;\n"
      ."  }\n"
      ."  BAddress1 =   geid_val(prefix+'Address1');\n"
      ."  BAddress2 =   geid_val(prefix+'Address2');\n"
      ."  BCity =       geid_val(prefix+'City');\n"
      ."  BSpID =       geid_val(prefix+'SpID')!='--' ? geid_val(prefix+'SpID') : geid_val(prefix+'SpAlt');\n"
      ."  BCountryID =  geid_val(prefix+'CountryID');\n"
      ."  BPostal =     geid_val(prefix+'Postal');\n"
      ."  for (var idx in cost_arr) {\n"
      ."    if (geid_val('product_'+idx)){\n"
      ."      chosen_products_arr.push(geid_val('product_'+idx));\n"
      ."    }\n"
      ."  }\n"
      ."  chosen_products = chosen_products_arr.join(',');\n"
      ."  if(\n"
      ."    BPostal!=purchase_options.BPostal ||\n"
      ."    BSpID!=purchase_options.BSpID ||\n"
      ."    BCountryID!=purchase_options.BCountryID ||\n"
      ."    chosen_products!=purchase_options.products\n"
      ."  ){\n"
      ."    purchase_options.BPostal=BPostal;\n"
      ."    purchase_options.BCountryID=BCountryID;\n"
      ."    purchase_options.BSpID=BSpID;\n"
      ."    purchase_options.products=chosen_products;\n"
      ."    details_changed=true;\n"
      ."  }\n"
      ."  total_arr = {'net':0,'tax':0,'total':0};\n"
      ."  for(var i=0; i<tax_regime_taxes_used.length; i++){\n"
      ."    total_arr[tax_regime_taxes_used[i]]=0;\n"
      ."  }\n"
      ."  for (idx in cost_arr) {\n"
      ."    if (geid('cost_'+idx)){\n"
      ."      opt = geid_val('product_'+idx);\n"
      ."      product = cost_arr[idx][opt];\n"
      ."      if (opt) {\n"
      ."        sub_cost =  product.c;\n"
      ."        geid_set('cost_'+idx, two_dp(sub_cost)); // show cost beside product\n"
      ."        sub_tax = customform_get_tax_costs(product,tax_regime_arr,tax_regime_tax_columns_used,BCountryID,BSpID);\n"
      ."        for(i=0; i<tax_regime_taxes_used.length; i++){\n"
      ."          total_arr[tax_regime_taxes_used[i]]+=sub_tax[tax_regime_taxes_used[i]];\n"
      ."        }\n"
      ."        total_arr.net +=   sub_cost;\n"
      ."        total_arr.tax +=   sub_tax.total;\n"
      ."        total_arr.total += sub_cost+sub_tax.total;\n"
      ."      }\n"
      ."      else {\n"
      ."        sub_cost = 0;\n"
      ."        geid('cost_'+idx).value = '';\n"
      ."      }\n"
      ."    }\n"
      ."  }\n"
      ."  if (geid('ajax_result_fees_shipping_method')) {\n"
      ."    if (details_changed) {\n"
      ."      xml = \n"
      ."        '<?xml version=\"1.0\" encoding=\"utf-8\" ?>'+\n"
      ."        '<ship>'+\n"
      ."        '<page>'+encodeURIComponent(page)+'<\/page>'+\n"
      ."        '<SAddress1>'+encodeURIComponent(BAddress1)+'<\/SAddress1>'+\n"
      ."        '<SAddress2>'+encodeURIComponent(BAddress2)+'<\/SAddress2>'+\n"
      ."        '<SCity>'+encodeURIComponent(BCity)+'<\/SCity>'+\n"
      ."        '<SPostal>'+encodeURIComponent(BPostal)+'<\/SPostal>'+\n"
      ."        '<SSpID>'+encodeURIComponent(BSpID)+'<\/SSpID>'+\n"
      ."        '<SCountryID>'+encodeURIComponent(BCountryID)+'<\/SCountryID>'+\n"
      ."        '<items>'+encodeURIComponent(chosen_products)+'<\/items>'+\n"
      ."        '<\/ship>';\n"
      ."      sajax.callback_fees_shipping(xml,'ajax_result_fees_shipping');\n"
      ."    }\n"
      ."  }\n"
      ."  if (geid('total_ship_taxes') && geid_val('total_ship_taxes')) {\n"
      ."    var total_ship_taxes_arr = geid_val('total_ship_taxes').split(',');\n"
      ."    for (i=0; i < total_ship_taxes_arr.length; i++){\n"
      ."      ship_tax_line = total_ship_taxes_arr[i].split('=');\n"
      ."      total_arr[ship_tax_line[0].toLowerCase()] += parseFloat(ship_tax_line[1]);\n"
      ."      total_arr.tax +=   parseFloat(ship_tax_line[1]);\n"
      ."      total_arr.total += parseFloat(ship_tax_line[1]);\n"
      ."    }\n"
      ."  }\n"
      ."  if (geid('total_ship_cost')) {\n"
      ."    total_arr.total += parseFloat(geid_val('total_ship_cost'));\n"
      ."  }\n"
      ."  if (geid('fees_coupons')) {\n"
      ."    alert('call ajax coupons');\n"
      ."  }\n"
      ."  if (geid('total_tax'))  { geid('total_tax').value = two_dp(total_arr.tax);}\n"
      ."  if (geid('total_net'))  { geid('total_net').value = two_dp(total_arr.net);}\n"
      ."  if (geid('total_cost')) { geid('total_cost').value = two_dp(total_arr.total);}\n"
      ."  if (geid('total_ship_cost')) { geid('total_cost').value = two_dp(total_arr.total);}\n"
      ."  if (geid('total_surcharge')) {\n"
      ."    var surcharge_percent = (payment_method_arr[geid_val('TMethod')] ? payment_method_arr[geid_val('TMethod')].surcharge : 0);\n"
      ."    var surcharge_cost = surcharge_percent/100*geid_val('total_cost');\n"
      ."    geid('total_surcharge').value = two_dp(surcharge_cost);\n"
      ."    geid('total_cost').value =\n"
      ."      two_dp(total_arr.total+parseFloat(surcharge_cost));\n"
      ."  }\n"
      ."  for(i=0; i<tax_regime_taxes_used.length; i++){\n"
      ."    if(geid('total_'+tax_regime_taxes_used[i])){\n"
      ."      geid_set(\n"
      ."        'total_'+tax_regime_taxes_used[i],\n"
      ."        two_dp(total_arr[tax_regime_taxes_used[i]])\n"
      ."      );\n"
      ."    }\n"
      ."  }\n"
      ."  var hascost = parseFloat(geid_val('total_cost'));\n"
      ."  payment_method_show(hascost);\n"
      ."}";
    return $out;
  }

  function draw_js_tax_regimes($taxRegimeID_csv){
    if (!$taxRegimeID_csv){
      return
         "// Tax Regimes:\n"
        ."var tax_regime_tax_columns_used = [];\n"
        ."var tax_regime_taxes_used = [];\n"
        ."var tax_regime_arr = {};\n";
    }
    $Obj_Tax_Regime = new Tax_Regime;
    $regimes =          $Obj_Tax_Regime->get_tax_regimes_and_rules_for_CSV($taxRegimeID_csv);
    $tax_columns_used = $Obj_Tax_Regime->get_tax_columns_in_use($regimes);
    $taxes_used = array();
    $tax_columns_used_arr = ($tax_columns_used ? explode(',',$tax_columns_used) : array());
    // get first regime to extract tax names
    foreach($regimes as $regime){
      $tax_regime = $regime;
      break;
    }
    foreach($tax_columns_used_arr as $i){
      $taxes_used[] = "\"".$tax_regime['tax'.($i+1).'_name']."\"";
    }
    $out =
       "// Tax Regimes:\n"
      ."var tax_regime_tax_columns_used = [".$tax_columns_used."];\n"
      ."var tax_regime_taxes_used = [".implode(",",$taxes_used)."];\n"
      ."var tax_regime_arr = {};\n";
    foreach($regimes as $regime){
      $tax_names_arr = array();
      $tax_rates_arr = array();
      $tax_rules_arr = array();
      for($i=1; $i<=20; $i++){
        $tax_names_arr[] = $regime['tax'.$i.'_name'];
        $tax_rates_arr[] = (float)$regime['tax'.$i.'_rate']/100;
      }
      foreach($regime['rules'] as $rule){
        $tax_rule_entries = array();
        for($i=1; $i<=20; $i++){
          $tax_rule_entries[] = str_replace(' ','', $rule['tax'.$i.'_apply']);
        }
        $tax_rules_arr[] = "[\"".implode("\",\"",$tax_rule_entries)."\"]";
      }
      $out.=
         "  tax_regime_arr[".$regime['ID']."] = {\n"
        ."    'tax_names' : [\"".implode("\",\"",$tax_names_arr)."\"],\n"
        ."    'tax_rates' : [".implode(",",$tax_rates_arr)."],\n"
        ."    'tax_rules' : [\n"
        ."      ".implode(",\n      ",$tax_rules_arr)."\n"
        ."    ]\n"
        ."  }\n";
    }
    return $out;
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with associated actions";
    return parent::sql_export($targetID,$show_fields,$header);
  }

  function form() {
    global $mode, $submode, $page_vars, $print;
    global $ID, $PUsername_lookup;
    $out = array();
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $canEdit =  ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    $canProxy = ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);

    $lookup_status = "";
    $ID = "";

    switch ($submode) {
      case 'lookup':
        if ($canProxy) {
          $this->xml_form_reset($this->_xml_doc);
          $Obj = new Person();
          if ($PUsername_lookup=="") {
            // Assume still operating in current user context
            $ID =   get_userID();
            break;
          }
          $_ID = $Obj->get_ID_by_name($PUsername_lookup,SYS_ID);
          if ($_ID) {
            $ID = $_ID;
            $lookup_status = "exists";
          }
          else {
            $lookup_status = "new";
          }
        }
      break;
      case "reset":
        $this->xml_form_reset($this->_xml_doc);
      break;
      default:
        $ID =   get_userID();
      break;
    }
    if ($ID!=false) {
      $fields = $this->xml_form_fields($this->_xml_doc);
      $Obj =  new Person($ID);
      $data = $Obj->get_record();
      foreach ($fields as $field) {
        if (array_key_exists($field,$data)) {
          $_REQUEST[$field] = $data[$field];
        }
      }
    }
    $args =
      array(
        'lookup_status'=>$lookup_status,
        'PUsername_lookup'=>$PUsername_lookup,
        'canProxy'=> $canProxy
      );
    $this->xml_form_prepare($args);
    Page::push_content(
      'javascript',
      $this->get_js()
    );
    return $this->get_html();
  }

  function frm_row_content($content,$bgcolor='',$id='',$hidden=false) {
    return
       "  <tr valign='top'"
      .($id!="" ? " id=\"".$id."\"" : "")
      .($bgcolor!="" || $hidden ?
          " style=\""
          .($bgcolor!='' ? "background-color:".$bgcolor.";" : "")
          .($hidden!='' ? "display:none;" : "")
          ."\""
       : "")
      .">\n"
      ."    <td colspan='3' class='formFieldMargin formFieldContent'>".$content."</td>\n"
      ."  </tr>";
  }

  function frm_row_head($text){
    return
       "  <tr valign='top' class='formHead'>\n"
      ."    <td colspan='3'>".$text."</td>\n"
      ."  </tr>\n"
      .$this->frm_row_spacer(5);
  }

  function frm_row_head_sub($text){
    return
       "  <tr valign='top' class='formHead'>\n"
      ."    <td colspan='3' class='formFieldMargin formSubHead'>".$text."</td>\n"
      ."  </tr>\n"
      .$this->frm_row_spacer(5);
  }

  function frm_row_label($label,$bgcolor="") {
    return
       "  <tr valign='top'".($bgcolor!="" ? " style=\"background-color:".$bgcolor."\"" : "").">\n"
      ."    <td colspan='3' class='formFieldLabel'>".$label."</td>\n"
      ."  </tr>\n";
  }

  function frm_row_label_content($label,$content,$bgcolor="",$content_width='',$id='',$hidden=false) {
    // label width now unused - caused conflicts
    return
       "  <tr valign='top'"
      .($id!="" ? " id=\"".$id."\"" : "")
      .($bgcolor!="" || $hidden ?
          " style=\""
          .($bgcolor!='' ? "background-color:".$bgcolor.";" : "")
          .($hidden!='' ? "display:none;" : "")
          ."\""
       : "")
      .">\n"
      ."    <td class='formFieldMargin formFieldLabel'>".$label."</td>\n"
      ."    <td colspan='2' class='formFieldContent'".($content_width ? " style='width:".(50+$content_width)."px'" : "").">".$content."</td>\n"
      ."  </tr>\n";
  }

  function frm_row_label_cost_content($label,$cost,$content,$bgcolor="") {
    return
       "  <tr valign='top'".($bgcolor!="" ? " style=\"background-color:".$bgcolor."\"" : "").">\n"
      ."    <td width='45%' valign='top' class='formFieldLabel'>".$label."</td>\n"
      ."    <td width='25%' valign='top' class='formFieldContent'>\$".$cost['cost']." CAD"
      .((isset($cost['gst']) && $cost['gst']) ||
        (isset($cost['qst']) && $cost['qst']) ||
        (isset($cost['pst']) && $cost['pst']) ||
        (isset($cost['hst']) && $cost['hst']) ?
         " ("
         .(isset($cost['gst']) && $cost['gst'] ? "+GST" : "")
         .(isset($cost['qst']) && $cost['qst'] ? "+QST" : "")
         .(isset($cost['hst']) && $cost['hst'] ? "+HST" : "")
         .(isset($cost['pst']) && $cost['pst'] ? "+PST" : "")
        .")" : "")
      ."</td>\n"
      ."    <td width='30%' valign='top' class='formFieldContent'>".$content."</td>\n"
      ."  </tr>\n";
  }

  function frm_row_label_req_content($label,$req,$content,$bgcolor="",$label_width='',$content_width='') {
    return
       "  <tr valign='top'".($bgcolor!="" ? " style=\"background-color:".$bgcolor."\"" : "").">\n"
      ."    <td class='formFieldMargin formFieldLabel'".($label_width ? " style='width:".$label_width.";'" : "").">".$label."</td>\n"
      ."    <td class='formFieldReqIndicator'>".$req."</td>\n"
      ."    <td class='formFieldContent'".($content_width ? " style='width:".$content_width.";'" : "").">".$content."</td>\n"
      ."  </tr>\n";
  }

  function frm_row_spacer($height=5) {
    return
       "  <tr valign='top'>\n"
      ."    <td colspan='3'><img src='".BASE_PATH."img/spacer' height='".$height."' width='1' alt='' class='b' /></td>\n"
      ."  </tr>\n";
  }

  function frm_table_close() {
    return "</table>\n";
  }

  function frm_table_open($ID='',$width='',$hidden=false) {
    return
      "<table class='form' border='0' cellpadding='0' cellspacing='0'"
      .($width ?  " width='".$width."'" : "")
      .($hidden ? " style=\"display:none\"" : "" )
      .($ID ? " id=\"".$ID."\"" : "")
      .">\n";
  }

  function get_html() {
    return implode("",Custom_Form::$html_arr);
  }

  function get_ID_by_name($name,$systemID="",$no_cache=false,$just_form=true) {
    $key = $this->_get_object_name()."_".$systemID."_".$name;
    if (isset(Record::$cache_ID_by_name_array[$key]) && !$no_cache) {
      return Record::$cache_ID_by_name_array[$key];
    }
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."WHERE\n"
      .($systemID!="" ? "  `systemID` IN($systemID) AND\n" : "")
      .($just_form ? "  `type` = 'form' AND\n" : "")
      ."  `".$this->_get_name_field()."` = \"$name\"\n"
      ."ORDER BY\n"
      ."  `systemID` = ".SYS_ID." DESC\n"
      ."LIMIT 0,1";
//    z($sql);
    $value = $this->get_field_for_sql($sql);
    Record::$cache_ID_by_name_array[$key] = $value;
    return $value;
  }

  function get_js() {
    return
       "//Custom Form JS code:\n"
      .implode("",Custom_Form::$js_arr)."\n"
      ;
  }

  function get_valid_name($name) {
    return (is_numeric(substr($name,0,1)) ? $this->get_valid_prefix().$name : $name);
  }

  function get_valid_prefix() {
    return Custom_Form::$valid_prefix;
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function manage_actions() {
    return parent::manage_actions('actions_for_custom_form');
  }

  function push_html($code) {
    Custom_Form::$html_arr[] = $code;
  }

  function push_js($code) {
    Custom_Form::$js_arr[] = $code;
  }

  function set_billing_address(
    $use,&$BAddress1,&$BAddress2,&$BCity,
    &$BSpID,&$BPostal,&$BCountryID,&$BTelephone) {
    switch (strToLower($use)) {
      case "h":
        $BAddress1 =    $_REQUEST['AAddress1'];
        $BAddress2 =    $_REQUEST['AAddress2'];
        $BCity =        $_REQUEST['ACity'];
        $BSpID =        $_REQUEST['ASpID'];
        $BPostal =      $_REQUEST['APostal'];
        $BCountryID =   $_REQUEST['ACountryID'];
        $BTelephone =   $_REQUEST['ATelephone'];
      break;
      case "c":
        $BAddress1 =    $_REQUEST['WAddress1'];
        $BAddress2 =    $_REQUEST['WAddress2'];
        $BCity =        $_REQUEST['WCity'];
        $BSpID =        $_REQUEST['WSpID'];
        $BPostal =      $_REQUEST['WPostal'];
        $BCountryID =   $_REQUEST['WCountryID'];
        $BTelephone =   $_REQUEST['WTelephone'];
      break;
      default:
        $BAddress1 =    $_REQUEST['BAddress1'];
        $BAddress2 =    $_REQUEST['BAddress2'];
        $BCity =        $_REQUEST['BCity'];
        $BSpID =        $_REQUEST['BSpID'];
        $BPostal =      $_REQUEST['BPostal'];
        $BCountryID =   $_REQUEST['BCountryID'];
        $BTelephone =   $_REQUEST['BTelephone'];
      break;
    }
  }

  function set_tax_apply(
      $apply_GST,$apply_HST,$apply_QST,$system_vars,
      &$tax1_apply,&$tax2_apply,&$tax3_apply,&$tax4_apply) {
    switch ($system_vars['tax1_name']) {
      case "GST" :  $tax1_apply = $apply_GST;   break;
      case "HST" :  $tax1_apply = $apply_HST;   break;
      case "QST" :  $tax1_apply = $apply_QST;   break;
      default:      $tax1_apply = false;        break;
    }
    switch ($system_vars['tax2_name']) {
      case "GST" :  $tax2_apply = $apply_GST;   break;
      case "HST" :  $tax2_apply = $apply_HST;   break;
      case "QST" :  $tax2_apply = $apply_QST;   break;
      default:      $tax2_apply = false;        break;
    }
    switch ($system_vars['tax3_name']) {
      case "GST" :  $tax3_apply = $apply_GST;   break;
      case "HST" :  $tax3_apply = $apply_HST;   break;
      case "QST" :  $tax3_apply = $apply_QST;   break;
      default:      $tax3_apply = false;        break;
    }
    switch ($system_vars['tax4_name']) {
      case "GST" :  $tax4_apply = $apply_GST;   break;
      case "HST" :  $tax4_apply = $apply_HST;   break;
      case "QST" :  $tax4_apply = $apply_QST;   break;
      default:      $tax4_apply = false;        break;
    }
  }

  function xml_download(){
    header('Content-Type: application/xml');
    print
       "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n"
      ."<!-- Generator: ".System::get_item_version('system_family')." ".System::get_item_version('build')." -->\n"
      .Custom_Form::$dtd
      .$this->get_field('content');
    die;
  }

  function xml_form_prepare($args=array()) {
    global $system_vars, $WSpAlt;
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $canProxy = ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    $this->_current_user_rights['canProxy'] = $canProxy;

    if ($this->_xml_doc=='') {
      $this->push_html('No XML document to parse');
      return false;
    }
    try {
      $this->_ObjXML = new SimpleXMLElement($this->_xml_doc);
    }
    catch (Exception $e) {
      $this->push_html($this->_xml_doc.' is not a valid xml string');
      return false;
    }
    $this->_xml_form_prepare_get_products();
    $this->_xml_form_prepare_js();
    $out = "<div class=\"custom_form\" id=\"cf\" style=\"display:none\">\n";
    $number_style =             isset($this->_ObjXML['number_style']) ?             $this->_ObjXML['number_style'] : false;
    $default_field_width =      isset($this->_ObjXML['default_field_width']) ?      $this->_ObjXML['default_field_width'] : 300;
    $default_section_width =    isset($this->_ObjXML['default_section_width']) ?    $this->_ObjXML['default_section_width'] : 600;
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $canProxy = ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    $products_arr = array();
    // JS for field support functions
    foreach ($this->_ObjXML->section as $section) {
      foreach ($section->row as $row) {
        foreach ($row->field as $field) {
          $_name =      (string)$field['name'];
          $_params =    (string)$field['params'];
          $_width =     (isset($field['width']) ? (string)$field['width'] : $default_field_width);
          if (isset($field['type'])) {
            switch($field['type']) {
              case "selector_billing_address":
                $this->push_js(
                   "function TBillingAddress_change() {\n"
                  ."  var disp = (geid_val('TBillingAddress').toLowerCase()=='x' ? '' : 'none');\n"
                  ."  geid('row_BAddress1').style.display = disp;\n"
                  ."  geid('row_BAddress2').style.display = disp;\n"
                  ."  geid('row_BCity').style.display = disp;\n"
                  ."  geid('row_BSpID').style.display = disp;\n"
                  ."  geid('row_BPostal').style.display = disp;\n"
                  ."  geid('row_BCountryID').style.display = disp;\n"
                  ."  geid('row_BTelephone').style.display = disp;\n"
                  ."  loadTotalCost();\n"
                  ."}\n"
                );
                $this->push_js("addEvent(window, 'load', TBillingAddress_change);\n");
              break;
              case "selector_payment_method":
                $Obj_PM = new Payment_Method;
                $pm =  $Obj_PM->draw_selector($_name,$_width,'custom_form');
                $this->push_js($pm['js']);
              break;
              case "selector_product_child":
                $Obj =  new Product;
                $pr =   $Obj->draw_product_child_selector($_name,$_params,$_width);
                $this->push_js($pr['js']);
                $products_arr[$_params] = $pr;
              break;
            }
          }
        }
      }
    }
    foreach ($this->_ObjXML->js as $js) {
      $this->push_js("// Custom Form JS:\n".(string)$js."\n");
    }
    foreach ($this->_ObjXML->section as $section) {
      foreach ($section->js as $js) {
//        die ($js);
        $this->push_js("// Custom Form Section JS:\n".(string)$js."\n");
      }
    }
    $this->push_js(
       "// **************************\n"
      ."// * Form Submit Validation *\n"
      ."// **************************\n"
      ."function validate(err_arr) {\n"
      ."  var n = err_arr.length + 1;\n"
    );
    // Hidden fields (if any)
    foreach ($this->_ObjXML->field as $field) {
      $_name =      (string)$field['name'];
      $_type =      (isset($field['type']) ? $field['type'] : "text");
      $_value =
        (isset($_REQUEST[$_name]) ?
           $_REQUEST[$_name]
         :
           (isset($field['default']) ? $field['default'] : "")
        );
      $out.= draw_form_field($_name,$_value,$_type);
    }
// section
    $section_num = 1;
    foreach ($this->_ObjXML->section as $section) {
//      y($section);die;
      $section_ID =     $section['id'];
      $section_width =  (isset($section['width']) ? $section['width'] : $default_section_width) ;
      $section_hidden =
        (isset($section['hidden']) ?
           ($section['hidden']=='yes' ? true : false)
         : false
        );
      $out.=
         $this->frm_table_open($section_ID,$section_width,$section_hidden)
        .(isset($section['line']) && $section['line']=='yes' ?
           "  <tr valign='top'>\n"
          ."    <td colspan='3'><hr /></td>\n"
          ."  </tr>\n"
         : ""
        );
// head:
        if (isset($section->label) || isset($section->extra)) {
          $text_arr = array();
          if (isset($section->label)) {
            $php_arr = array();
            foreach ($section->label->php as $php) {
              $php_arr[] = eval($php);
            }
            $text_arr[] =
              "<b>"
              .($number_style ?
                  ($number_style=='1' ? $section_num++ : "")
                 .($number_style=='a' ? chr(ord('a')-1+($section_num++)) : "")
                 .($number_style=='A' ? chr(ord('A')-1+($section_num++)) : "")
                 .($number_style=='i' ? convert_a2r($section_num++,true) : "")
                 .($number_style=='I' ? convert_a2r($section_num++,false) : "")
                 .". "
               : "")
              .$section->label
              .implode('',$php_arr)
              ."</b>";
          }
          if (isset($section->extra)) {
            $php_arr = array();
            foreach ($section->extra->php as $php) {
              $php_arr[] = eval($php);
            }
            $text_arr[] =
               $section->extra
              .implode('',$php_arr);
          }
          $out.= $this->frm_row_head(implode('',$text_arr));
        }
// row(s)
      foreach ($section->row as $row) {
        $php_arr = array();
        if (isset($row->label)) {
          foreach ($row->label->php as $php) {
            $php_arr[] = eval($php);
          }
        }

        $out.=
          (isset($row['height']) ?
            $this->frm_row_spacer($row['height'])
           : "");
        $fields_arr = array();
        $bgcolor =      (isset($row['bgcolor']) ? $row['bgcolor'] : "");
        $id =           (isset($row['id']) ?    $row['id'] : "");
        $row_hidden =
          (isset($row['hidden']) ?
             ($row['hidden']=='yes' ? true : false)
           : false
          );

        foreach ($row->field as $field) {
          if (isset($field['name']) && (string)$field['name']) {
            $_name =      (string)$field['name'];
            $_nobreak =   (isset($field['nobreak']) ? $field['nobreak'] : 0);
            $_type =      (isset($field['type']) ? $field['type'] : "text");
            $_width =     (isset($field['width']) ? $field['width'] : $default_field_width);
            $_height =    (isset($field['height']) ? $field['height'] : '');
            $_maxlength = (isset($field['maxlength']) ? " maxlength='".$field['maxlength']."'" : "");
            $_params =    (isset($field['params']) ? (string)$field['params'] : "");
            $_prefix =    (isset($field['prefix']) ? $field['prefix'] : "");
            $_suffix =    (isset($field['suffix']) ? $field['suffix'] : "");
            $_js =        (isset($field['js']) ? " ".$field['js'] : "");
            $_readonly =  (isset($field['readonly']) ? $field['readonly'] : "");
            $_req_val =   (isset($field['required']) ? $field['required'] : "");
            $_value =
              (isset($_REQUEST[$_name]) ?
                 $_REQUEST[$_name]
               :
                 (isset($field['default']) ? $field['default'] : "")
              );
            $_itemcode =  (isset($field['itemcode']) ? $field['itemcode'] : '');
            switch ($_req_val) {
              case "":
              case "no":
                $_req = "<span class='no'>&nbsp;</span>";
              break;
              case "yes":
                $_req = "<span class='yes'>*</span>";
              break;
              case "depends":
                $_req = "<span class='depends'>**</span>";
              break;
            }
            switch ($_readonly) {
              case "":
              case "no":
                $_readonly = 0;
              break;
              case "yes":
                $_readonly = 1;
              break;
              case "isset":
                $_readonly = (isset($_REQUEST['ID']) && $_REQUEST['ID']!="" ? 1 : 0);
              break;
            }
            switch ($_type) {
              case 'combo_sp_selector':
                $_field_obj = draw_form_field($_name,$_value,'combo_listdata',$_width,'',0,"onchange=\"combo_selector_set('".$_name."','".((int)$_width)."px');".$_js."\"",0,0,'','lst_sp');
              break;
              case 'fees_overview':
                $_field_obj = $this->draw_fees_overview($_width);
              break;
              case 'fees_shipping':
                $ObjAjax = new Ajax;
                $_field_obj = $ObjAjax->get_shipping($_params,$_width,'fees_shipping',$this->_cp);
                $this->push_js(
                   "\n  // For Shipping Selector - extra checks if selected:\n"
                  ."  if (geid(\"total_ship_error\") && geid_val(\"total_ship_error\")==1) { err_arr[err_arr.length] = (n++)+\") Problem with shipping address\" }\n"
                );
              break;
              case 'selector_payment_method':
                $_field_obj = $pm['html'];
              break;
              case 'selector_product_child':
                $pr = $products_arr[$_params];
                $_field_obj = $pr['html'];
              break;
              default:
                $_field_obj =
                  draw_form_field($_name,$_value,$_type,$_width,'','',$_js.$_maxlength,$_readonly,'','',$_params,$_height);
//                print "draw_form_field($_name,$_value,$_type,$_width,'','',$_js.$_maxlength,$_readonly,'','',$_params,$_height);<br />";
              break;
            }
            switch ($_type) {
              case "hidden":
                $fields_arr[] = $_field_obj;
              break;
              default:
                $fields_arr[] =
                   "<div class='fl txt_r' style='width:20px'>".$_req."</div>\n"
                  ."<div class='fl'>\n"
                  .$_prefix."\n"
                  .$_field_obj."\n"
                  .$_suffix."\n"
                  ."</div>\n"
                  .($_nobreak ? "" : "<div class='clear'>&nbsp;</div>")
                  ;
              break;
            }
            switch($field['required']) {
              case 'yes':
                $err_label =
                  str_replace(
                    array('<br />','<br />','<br />',':'),
                    array(' ',' ',' ',''),
                    (isset($field['errormsg']) ? $field['errormsg'] : $row->label)
                  );
                switch ($_type) {
                  case "selector_payment_method":
                    $this->push_js(
                       "\n  // For Payment Method Selector - extra checks if selected:\n"
                      ."  var hascost = parseFloat(geid_val('total_cost'));\n"
                      ."  if (hascost && geid_val(\"".$_name."\")=='') { err_arr[err_arr.length] = (n++)+\") ".$err_label."\" }\n"
                      ."  else { err_arr = checkout_validate_payment_details(err_arr);}\n"
                    );
                  break;
                  case "selector_billing_address":
                    $this->push_js(
                       "\n  // For Billing Address Selector -  - validates selected billing address\n"
                      ."  switch (geid_val(\"".$_name."\")) {\n"
                      ."    case 'H':\n"
                      ."    case 'C':\n"
                      ."      // validated these fields already\n"
                      ."    break;\n"
                      ."    default:\n"
                      ."      var prefix = 'B'; var name = 'Billing';\n"
                      ."      if (geid_val(prefix+'Address1')=='') { err_arr[err_arr.length] = (n++)+') '+name+' Address' };\n"
                      ."      if (geid_val(prefix+'City')=='')     { err_arr[err_arr.length] = (n++)+') '+name+' City' };\n"
                      ."      if (geid_val(prefix+'SpID')=='')     { err_arr[err_arr.length] = (n++)+') '+name+' State / Province' };\n"
                      ."      if (geid_val(prefix+'Postal')=='')   { err_arr[err_arr.length] = (n++)+') '+name+' Postal Code' };\n"
                      ."      if (geid_val(prefix+'CountryID')==''){ err_arr[err_arr.length] = (n++)+') '+name+' Country' };\n"
                      ."    break;\n"
                      ."  }\n"
                    );
                  break;
                  default:
                    $this->push_js(
                       "  if (geid_val(\"".$_name."\")=='') { err_arr[err_arr.length] = (n++)+\") ".$err_label."\" }\n"
                    );
                  break;
                }
              break;
            }
          }
        }
        if (isset($row->label)) {
          if (count($fields_arr)) {
            $out.=
              $this->frm_row_label_content(
                $row->label.implode('',$php_arr),
                implode('',$fields_arr),
                $bgcolor,
                $default_field_width,
                $id,
                $row_hidden
              );
          }
          else {
            $out.=
              $this->frm_row_content(
                $row->label
                .implode('',$php_arr),
                $bgcolor,
                $id,
                $row_hidden
              );
          }
        }
        else {
          if (count($fields_arr)) {
            $out.=
              $this->frm_row_content(
                implode('',$fields_arr),
                $bgcolor,
                $id,
                $row_hidden
              );
          }
        }
      }
      $out.= $this->frm_table_close();
    }
    $this->push_js(
       "\n  // Done checks - return final result:\n"
      ."  return err_arr;\n"
      ."}\n"
    );
    $out.= "</div>\n";
    $this->push_html($out);
    return true;
  }

  protected function _xml_form_prepare_js(){
    $this->push_js(
      "addEvent(window,'load',function(e){ doStartupFunctions();});\n"
     ."\n"
     ."var err_arr = [];\n\n"
     ."var valFn;\n"
     ."\n"
     ."var validationFunctions = [];\n"
     ."var startupFunctions = [];\n"
     ."\n"
     ."function doStartupFunctions(){\n"
     ."  for (var i=0; i<startupFunctions.length; i++) {\n"
     ."    startupFunctions[i]();\n"
     ."  }\n"
     ."  geid('cf').style.display = '';\n"
     ."}\n"
     ."function showAllSections(state){\n"
     ."  var container = geid('cf');\n"
     ."  var node;\n"
     ."  for (var i=0; i<container.childNodes.length; i++) {\n"
     ."    node = container.childNodes[i];\n"
     ."    if (node.nodeType==1  && node.nodeName.toLowerCase()=='table'){\n"
     ."      node.style.display = (state ? '' : 'none');\n"
     ."    }\n"
     ."  }\n"
     ."}\n"
     ."function doVerify(lang){\n"
     ."  var _err_arr = validate(err_arr);\n"
     ."  var err = _err_arr.join('\\n');\n"
     ."  if (err==''){\n"
     ."    return true;\n"
     ."  }\n"
     ."  if (typeof lang==='undefined'){\n"
     ."    lang='en';\n"
     ."  }\n"
     ."  switch(lang){\n"
     ."    case 'fr':\n"
     ."      alert(\n"
     ."        '-----------------------\\n'+\n"
     ."        'L\'attention\\n'+\n"
     ."        '-----------------------\\n' +\n"
     ."        ('Les champs obligatoires suivants n\'ont pas été fournis:\\n')+\n"
     ."        err + '\\n\\nPresse [OK] continuer.'\n"
     ."      );\n"
     ."      return false;\n"
     ."    break;\n"
     ."    default:\n"
     ."      alert(\n"
     ."        '-----------------------\\n'+\n"
     ."        'Attention Required\\n'+\n"
     ."        '-----------------------\\n' +\n"
     ."        'The following required fields were not provided:\\n'+\n"
     ."        err + '\\n\\nPress [OK] to continue.'\n"
     ."      );\n"
     ."      return false;\n"
     ."    break;\n"
     ."  }\n"
     ."}\n"
     ."\n"
    );
    if (count($this->_products)>0){
      // JS for tax regime
      $this->push_js("startupFunctions.push(function(){customform_draw_fees_overview(tax_regime_taxes_used,currency_symbol);});\n");
      $this->push_js($this->_xml_form_prepare_get_product_tax_regimes_js()."\n");
      $this->push_js($this->draw_js_loadTotalCost()."\n");
      $this->push_js("// Cost array: costs for products\n");
      $this->push_js("  var cost_arr = {};\n");
    }
  }

  protected function _xml_form_prepare_get_product_tax_regimes_js(){
    $taxRegimes_csv = $this->_xml_form_prepare_get_product_tax_regimes();
    return $this->draw_js_tax_regimes($taxRegimes_csv);
  }

  protected function _xml_form_prepare_get_products(){
    $out = array();
    foreach ($this->_ObjXML->section as $section) {
      foreach ($section->row as $row) {
        foreach ($row->field as $field) {
          if (isset($field['type'])) {
            switch($field['type']) {
              case "selector_product_child":
                $out[(string)$field['name']] = (string)$field['params'];
              break;
            }
          }
        }
      }
    }
    $this->_products = $out;
  }

  protected function _xml_form_prepare_get_product_tax_regimes(){
    $tax_regimes_arr = array();
    $Obj_Product = new Product;
    foreach ($this->_products as $name=>$itemCode){
      $ID = $Obj_Product->get_ID_by_name($itemCode);
      if ($this->_current_user_rights['canProxy']){
        $options = $Obj_Product->get_children_by_parentID($ID,false,true);
      }
      else {
        $options = $Obj_Product->get_children_by_parentID($ID,true,true);
      }
      if ($options){
        foreach($options as $option){
          $tax_regimeID = $option['tax_regimeID'];
          $tax_regimes_arr[$tax_regimeID] = true;
        }
      }
    }
    return implode(',',array_keys($tax_regimes_arr));
  }

  function xml_form_fields() {
    $out = array();
    // returns array of all fields
    if ($this->_xml_doc=='') {
      return $out;
    }
    try {
      $xml = new SimpleXMLElement($this->_xml_doc);
    }
    catch (Exception $e) {
      return $out;
    }
    // Top form hidden elements
    foreach ($xml->field as $field) {
      $out[] =      (string)$field['name'];
    }
    // Now fields within section rows
    foreach ($xml->section as $section) {
      foreach ($section->row as $row) {
        foreach ($row->field as $field) {
          $out[] =      (string)$field['name'];
        }
      }
    }
//    y($out);
    return $out;
  }

  function xml_form_get_field_list($field,&$out){
//    y($field);
    $dest_arr =   explode(',',(isset($field['destination']) ? $field['destination'] : ''));
    foreach ($dest_arr as $dest) {
      $dest_bits =    explode('.',$dest);
      $table_name =   $dest_bits[0];
      $field_name =   (isset($dest_bits[1]) ? $dest_bits[1] : (string)$field['name']);
      $type =         (isset($field['type']) ? $field['type'] : "text");
      $readonly =    (isset($field['readonly']) ? $field['readonly'] : 'no');
      switch ($readonly) {
        case "yes":
          $readonly = 1;
        break;
        case "isset":
          $readonly = (isset($_REQUEST['ID']) && $_REQUEST['ID']!="" ? 1 : 0);
        break;
        default:
          $readonly = 0;
        break;
      }
      $field['readonly'] =$readonly;
      if (!$readonly){
        switch($table_name) {
          case '':
            switch ($type){
              case "selector_product_child":
                $table_name = 'order_items';
                if (!isset($out[$table_name])) {
                  $out[$table_name] = array();
                }
                $ID =
                  (isset($_REQUEST[(string)$field['name']]) ?
                    $_REQUEST[(string)$field['name']]
                  : ''
                  );
                if ($ID) {
                  $out[$table_name][$ID] = 1;
                }
              break;
              case "field_processor":
                $table_name = 'order_items';
                if (!isset($out[$table_name])) {
                  $out[$table_name] = array();
                }
                $ID =
                  (isset($_REQUEST[(string)$field['name']]) ?
                    $_REQUEST[(string)$field['name']]
                  : ''
                  );
                if ($ID) {
                  $out[$table_name][$ID] = 1;
                }
              break;
            }
          break;
          default:
            if (!isset($out[$table_name])) {
              $out[$table_name] = array();
            }
            switch ($type){
              case 'combo_sp_selector':
                $out[$table_name][$field_name] =    (isset($_REQUEST[(string)$field['name']]) ? $_REQUEST[(string)$field['name']] : '');
              break;
              case "file_upload":
                if (is_uploaded_file($_FILES[(string)$field['name']]['tmp_name'])){
                  $out[$table_name][(string)$field['name']] =
                     'name:'.$_FILES[(string)$field['name']]['name']
                    .',size:'.$_FILES[(string)$field['name']]['size']
                    .',type:'.$_FILES[(string)$field['name']]['type']
                    .',data:'.file_get_contents($_FILES[(string)$field['name']]['tmp_name']);
                }
              break;
              default:
                $out[$table_name][$field_name] =    (isset($_REQUEST[(string)$field['name']]) ? $_REQUEST[(string)$field['name']] : '');
              break;
            }
          break;
        }
      }
    }
    return true;
  }

  function xml_form_process() {
    global $page_vars;
    $redirect =         $this->_cp['redirect_to_page'];
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $canProxy = ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    $xml = new SimpleXMLElement($this->_xml_doc);
    $tables = array();
    foreach ($xml->field as $field) {
      $this->xml_form_get_field_list($field,$tables);
    }
    $shipping = false;
    // Now fields within section rows
    foreach ($xml->section as $section) {
      foreach ($section->row as $row) {
        foreach ($row->field as $field) {
          $this->xml_form_get_field_list($field,$tables);
          if ($field['type']=='fees_shipping'){
            $shipping =  (string)$field['params'];
          }
        }
      }
    }
    $records_inserted = array();
    // This is in case the hidden field for order category was not provided
    if (isset($tables['order_items']) && !isset($tables['order'])){
       $tables['order'] = array();
    }
    // Get shipping cost if shipping module applied:
    if ($shipping) {
      $tables['order']['SMethod'] = $shipping;
    }
    $data = (isset($tables['person']) ? $tables['person'] : false);
    $data['ID'] = $this->xml_form_process_person($data,$canProxy);
    $personID = $data['ID'];
    if (isset($tables['order'])) {
      $tables['order']['personID'] =            $personID;
      $tables['order']['originating_page'] =    BASE_PATH.trim($page_vars['path'],'/');
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Process',"Preparing to generate and process order");
      $orderID =
        $this->xml_form_process_order(
          $tables['order'],$tables['order_items'],$canProxy
        );
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Process',"Generated and processed order ".$orderID);
      $_POST['orderID'] = $orderID;
    }
    foreach ($tables as $table=>$data) {
      switch ($table) {
        case 'person':
        case 'order':
        case 'order_items':
          // did these already
        break;
        default:
          do_log(0,__CLASS__."::".__FUNCTION__."()",'Process',"saving data for ".$table);
          if (!isset($data['personID'])) {
            $data['personID'] = $personID;
          }
          // ONLY DO THIS IF THERE IS AN ORDER INVOLVED
          if (array_key_exists('order',$tables)) {
	        if (!isset($data['orderID'])) {
  	          $data['orderID'] = $orderID;
    	    }
    	  }
          if (!isset($data['systemID'])) {
            $data['systemID'] = SYS_ID;
          }
          $ID = (isset($data['ID']) ? $data['ID'] : false);
          $Obj_Record = new Record($table,$ID);
          foreach($data as $key=>$val) {
            $data[$key] = Record::escape_string($val);
          }
          $result = $Obj_Record->update($data);
          if (!$result){
            die();
          }
          $_POST[$table."_ID"] = $result;
          $records_inserted[] = $table."_ID=".$result;
        break;
      }
    }
    $this->actions_execute('custom_form_submit',$this->_get_table_name(),'Custom_Form',$this->_get_ID(),$_POST);
    if (!$canProxy){
      $Obj_User =       new User($personID);
      $PUsername =      $Obj_User->get_field("PUsername");
      $PPasswordEnc =   $Obj_User->get_field("PPassword");
      $Obj_User->get_person_to_session($PUsername,$PPasswordEnc);
    }
    $vars =   implode('&',$records_inserted);
    if ($redirect){
      if (strpos($redirect, "|") !== false) {
        $redirBits = explode("|", $redirect);
        if ($redirBits[0] == 'checkout') {
          // Only for TSM
          if (Cart::has_items()) {
            $redirect = $redirBits[0];
          }
          else {
            $redirect = $redirBits[1];
          }
        }
      }
      header("Location: ".BASE_PATH.$redirect."?".$vars);
    }
    else if (array_key_exists('order',$tables)) { // only view order if there is an order involved
      header("Location: ".BASE_PATH."view_order?ID=".$orderID."&".$vars);
      print BASE_PATH."view_order?ID=".$orderID;
    }
    else {
      echo "If you see this message no order was generated and no re-direct page action was set.";
    }
  }

  function xml_form_process_order($order_data,$item_data,$canProxy) {
    global $system_vars,$page;
    // Set billing address
    $this->set_billing_address(
      (isset($_REQUEST['TBillingAddress']) ? $_REQUEST['TBillingAddress'] : 'x'),
      $_REQUEST['BAddress1'],$_REQUEST['BAddress2'],$_REQUEST['BCity'],
      $_REQUEST['BSpID'],$_REQUEST['BPostal'],$_REQUEST['BCountryID'],
      $_REQUEST['BTelephone']
    );
    $_REQUEST['BEmail'] = $_REQUEST['PEmail'];
    //    print "\$order_data:<br />";y($order_data);
    //    print "\$item_data:<br />";y($item_data);
    //    print "Globals:<br />";y($_POST);
    //    die;
// create order
    $Obj_Order = new Order;
    $orderID =
      $Obj_Order->create(
        $order_data['personID'],
        (isset($_REQUEST['TMethod']) ? $_REQUEST['TMethod'] : ''),
        (isset($_REQUEST['TCardName']) ? $_REQUEST['TCardName'] : ''),
        (isset($_REQUEST['TCardNumber']) ? $_REQUEST['TCardNumber'] : ''),
        (isset($_REQUEST['TCardExpiry_mm']) ? $_REQUEST['TCardExpiry_mm'] : ''),
        (isset($_REQUEST['TCardExpiry_yy']) ? $_REQUEST['TCardExpiry_yy'] : '')
      );
    $Obj_Order->_set_ID($orderID);
    do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-1',"Created order ".$orderID);
    // Now write in any other data we have for the order - categories or instructions etc
    $Obj_Order->update($order_data,true, false);
    // Independently verify shipping costs:
    if (isset($order_data['SMethod']) && $order_data['SMethod']) {
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-2a',"Getting shipping costs for order ".$orderID);
//      y($item_data);die;
      $items = array();
      foreach ($item_data as $key=>$val){
        $items[] = $key;
      }
      $dest_xml =
         "<ship>\n"
        ."  <page>".htmlentities($page)."</page>\n"
        ."  <SAddress1>".htmlentities($_REQUEST['BAddress1'])."</SAddress1>\n"
        ."  <SAddress2>".htmlentities($_REQUEST['BAddress2'])."</SAddress2>\n"
        ."  <SCity>".htmlentities($_REQUEST['BCity'])."</SCity>\n"
        ."  <SPostal>".htmlentities($_REQUEST['BPostal'])."</SPostal>\n"
        ."  <SSpID>".htmlentities($_REQUEST['BSpID'])."</SSpID>\n"
        ."  <SCountryID>".htmlentities($_REQUEST['BCountryID'])."</SCountryID>\n"
        ."  <items>\n".implode(",",$items)."</items>\n"
        ."</ship>"
        ;
      $ObjShipping =        new Shipping;
      $shipping =           $ObjShipping->get_shipping($order_data['SMethod'],$dest_xml,$this->_cp);
      if ($shipping['error']==0) {
        $order_data['SMethod'] =        $shipping['method'];
        $order_data['cost_shipping'] =  $shipping['cost'];
        $tax_arr = array();
        if ($shipping['taxes']) {
          foreach ($shipping['taxes'] as $tax=>$cost) {
            $tax_arr[] = $tax.":".$cost;
          }
          $order_data['taxes_shipping'] = implode(",",$tax_arr);
        }
      }
      $Obj_Order->update($order_data,true, false);
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-2b',"Updating shipping costs for order #".$orderID);
    }
    //    $record = $Obj_Order->get_record(); y($record);die;
    //    y($system_vars);die;
    //    y($order_data);y($item_data);die;
    $ObjOrderItem = new OrderItem;
    $ObjProduct =   new Product;
    $ObjAction =    new Action;
    if ($item_data) {
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-3',"Preparing to add ordered items to order #".$orderID);
      foreach ($item_data as $productID=>$qty){
        $ObjProduct->_set_ID($productID);
        $record = $ObjProduct->get_record();
        do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-3a',"  Getting tax rates for ".$qty." x ".$productID." (".$record['itemCode'].") in order #".$orderID);
        $data =
          array(
            'systemID' =>       $record['systemID'],
            'orderID' =>        $orderID,
            'productID' =>      $productID,
            'quantity' =>       $qty,
            'price' =>          $record['price'],
            'tax_regimeID' =>   $record['tax_regimeID']
          );
        $orderItemID =    $ObjOrderItem->insert($data);
        do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-3d',"    Added order item ".$orderItemID." to order #".$orderID);
        $sourceID =       $productID;
        $sourceType =     'product';
        $sourceTrigger =  'product_order';
        $triggerType =    'order_items';
        $triggerObject =  'OrderItem';
        $triggerID =      $orderItemID;
        $personID =       $order_data['personID'];
        do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-3e',"    About to execute product_order actions for ordered item ".$orderItemID." in order #".$orderID);
        $ObjAction->execute(
          $sourceType,
          $sourceID,
          $sourceTrigger,
          $personID,
          $triggerType,
          $triggerObject,
          $triggerID,
          $_POST
        );
        do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-3f',"    Executed product_order actions for ordered item ".$orderItemID." in order #".$orderID);
      }
    }
    do_log(0,__CLASS__."::".__FUNCTION__."()",'Setup-4',"Setting costs for order ".$orderID);
    $Obj_Order->set_costs();
    do_log(0,__CLASS__."::".__FUNCTION__."()",'Payment-1',"Order #".$orderID." payment method: ".$_REQUEST['TMethod']);
    $Obj_PM = new Payment_Method;
    if ($Obj_PM->method_is_offline(get_var('TMethod'))){
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Payment-2',"  Order #".$orderID." to be processed by offline-payment method ".$_REQUEST['TMethod']);
      $Obj_Order->mark_paid('',false);
    }
    else {
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Payment-2',"  Order #".$orderID." to be processed by online-payment method ".$_REQUEST['TMethod']);
      $Obj_Order->payment(
        isset($_REQUEST['TCardNumber']) ? $_REQUEST['TCardNumber'] : "",
        $this->_cp['payment_gateway_setting']
      );
    }
    $processed =      $Obj_Order->get_field('processed')>0;
    $gateway_result = $Obj_Order->get_field('gateway_result');
    if ($processed) {
      do_log(0,__CLASS__."::".__FUNCTION__."()",'Payment-3',"  Order #".$orderID." processed successfully");
    }
    else {
      do_log(2,__CLASS__."::".__FUNCTION__."()",'Payment-3',"  Order #".$orderID." failed to process - $gateway_result");
    }
    return $orderID;
  }

  function xml_form_process_person($data=false,$canProxy){
    if (!$data) {
      return false;
    }
    if (!array_key_exists('ID', $data)) {
    	$data['ID'] = '';
    }
    $personID =     $data['ID'];
    $Obj_User =   new User($personID);
    if ($personID!='') {
      $PUsername =    $Obj_User->get_field("PUsername");
      $Obj_User->update($data);
      do_log(0,__CLASS__."::".__FUNCTION__."()",'','Updated person record for '.$PUsername);
      unset($_SESSION['user_created']);
      return $personID;
    }
    $new_PUsername =      $Obj_User->uniq_PUsername("new_");
    $data['systemID'] =   SYS_ID;
    $data['PUsername'] =  $new_PUsername;
    $data['permACTIVE'] = 1;
    foreach ($data as &$d){
      $d = str_replace("\"","'",$d);
    }
    $personID =         $Obj_User->insert($data);
    $Obj_User->_set_ID($personID);
    $_POST['ID'] =      $personID;
    $_POST['new_personID'] = $personID;
    $Obj_User->set_random_password();
    $_SESSION['user_created'] = $personID;
    do_log(0,__CLASS__."::".__FUNCTION__."()",'','Creating person '.$new_PUsername);
    if ($this->_cp['new_user_email'] && $this->_cp['new_user_email_template']){
      $Obj_User->do_email_signup($this->_cp['new_user_email_template']);
    }
    if (!$canProxy){
      $Obj_User->load();
      $new_PUsername =  $Obj_User->record['PUsername'];
      $PPasswordEnc =   $Obj_User->record['PPassword'];
      $Obj_User->get_person_to_session($new_PUsername,$PPasswordEnc);
      do_log(0,__CLASS__."::".__FUNCTION__."()",'','Logged in as '.$new_PUsername);
    }
    $ObjGroup = new Group;
    $group_name = "New Applicants";
    if ($groupID = $ObjGroup->get_ID_by_name($group_name)) {
      $ObjGroup->_set_ID($groupID);
  	  $ObjGroup->member_assign($personID,array("permVIEWER"=>1));
      do_log(0,__CLASS__."::".__FUNCTION__."()",'','Added '.$new_PUsername.' to '.$group_name);
  	}
    return $personID;
  }

  function xml_form_reset($xmlstr) {
    $xml = new SimpleXMLElement($xmlstr);
    // Top form hidden elements
    foreach ($xml->field as $field) {
      $_name =      (string)$field['name'];
      $_REQUEST[$_name] = (isset($field['default']) ? $field['default'] : "");
    }
    // Now forms within section rows
    foreach ($xml->section as $section) {
      foreach ($section->row as $row) {
        foreach ($row->field as $field) {
          $_name =      (string)$field['name'];
          $_REQUEST[$_name] = (isset($field['default']) ? $field['default'] : "");
        }
      }
    }
  }
  public function get_version(){
    return VERSION_CUSTOM_FORM;
  }
}
?>