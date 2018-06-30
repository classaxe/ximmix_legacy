<?php
  define ("VERSION_COMPONENT_CUSTOM_FORM","1.0.3");
/*
Version History:
  1.0.3 (2012-11-15)
    1) Control Panel now has setting for gateway settings to use when processing
       any orders that may result
  1.0.2 (2012-03-30)
    1) Now defaults name to instance name of inserted custom form
  1.0.1 (2012-01-26)
    1) Added two new component parameters -
       new_user_email (0|1) and new_user_email_template (default=user_signup)
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Custom_Form extends Component_Base {

  function draw($instance='', $args=array(), $disable_params=false) {
    global $print;
    $ident =            "custom_form";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    global $system_vars;
    $Obj_GS = new Gateway_Setting($system_vars['gatewayID']);
    $gateway_settings_name = $Obj_GS->get_field('name');
    if (System::has_feature('Fedex')){
      $parameter_spec =   array(
        'name' =>                     array('match' => '',          'default'=>$instance,   'hint'=>'Name for custom form to use'),
        'new_user_email' =>           array('match' => 'enum|0,1',	'default'=>'0',         'hint'=>'Whether or not to send an email iof a new user is created'),
        'new_user_email_template' =>  array('match' => '',	        'default'=>'user_signup','hint'=>'If an email is to be sent to new users, use this email'),
        'payment_gateway_setting' =>  array('match' => '',          'default'=>$gateway_settings_name,      'hint'=>'Payment Gateway Settings to use for any payments that might occur'),
        'redirect_to_page' =>         array('match' => '',          'default'=>'',          'hint'=>'Optional - takes 1 or two | delimited parameters If options>1 and 1st is checkout, second for when cart is empty'),
        'ship_FEDEX_AccountNumber' => array('match' => '',          'default'=>'',          'hint'=>'Account Number for shipping gateway'),
        'ship_FEDEX_MeterNumber' =>   array('match' => '',	        'default'=>'',          'hint'=>'Meter Number for shipping gateway'),
        'ship_FEDEX_DropoffType' =>   array('match' => 'enum|BUSINESS_SERVICE_CENTER,DROP_BOX,REGULAR_PICKUP,REQUEST_COURIER,STATION',  				        'default'=>'REGULAR_PICKUP',    'hint'=>'BUSINESS_SERVICE_CENTER|DROP_BOX|REGULAR_PICKUP|REQUEST_COURIER|STATION'),
        'ship_FEDEX_LiveGateway' =>   array('match' => 'enum|0,1',	'default'=>'0',         'hint'=>'0|1'),
        'ship_FEDEX_PackagingType' => array('match' => 'enum|FEDEX_10KG_BOX,FEDEX_25KG_BOX,FEDEX_BOX,FEDEX_ENVELOPE,FEDEX_PAK,FEDEX_TUBE,YOUR_PACKAGING',		'default'=>'FEDEX_PAK',         'hint'=>'FEDEX_10KG_BOX|FEDEX_25KG_BOX|FEDEX_BOX|FEDEX_ENVELOPE|FEDEX_PAK|FEDEX_TUBE|YOUR_PACKAGING'),
        'ship_FEDEX_username' =>      array('match' => '',  		'default'=>'',          'hint'=>'Key for shipping gateway'),
        'ship_FEDEX_password' =>      array('match' => '',  		'default'=>'',          'hint'=>'Password for shipping gateway'),
        'ship_from_AAddress1' =>      array('match' => '',  		'default'=>'',          'hint'=>'Ship from Address Line 1'),
        'ship_from_AAddress2' =>      array('match' => '',  		'default'=>'',          'hint'=>'Ship from Address Line 2'),
        'ship_from_ACity' =>          array('match' => '',  		'default'=>'',          'hint'=>'Ship from City'),
        'ship_from_ASpID' =>          array('match' => '',  		'default'=>'',          'hint'=>'Ship from State / Province'),
        'ship_from_APostal' =>        array('match' => '',  		'default'=>'',          'hint'=>'Ship from Postal Code'),
        'ship_from_ACountryID' =>     array('match' => '',  		'default'=>'',          'hint'=>'Ship from Country')
      );
    }
    else {
      $parameter_spec =   array(
        'name' =>                     array('match' => '',			'default'=>$instance,   'hint'=>'Name for custom form to use'),
        'new_user_email' =>           array('match' => 'enum|0,1',	'default'=>'0',         'hint'=>'Whether or not to send an email iof a new user is created'),
        'new_user_email_template' =>  array('match' => '',	        'default'=>'user_signup','hint'=>'If an email is to be sent to new users, use this email'),
        'payment_gateway_setting' =>  array('match' => '',          'default'=>$gateway_settings_name,      'hint'=>'Payment Gateway Settings to use for any payments that might occur'),
        'redirect_to_page' =>         array('match' => '',  		'default'=>'',          'hint'=>'Optional - takes 1 or two | delimited parameters If options>1 and 1st is checkout, second for when cart is empty')
      );
    }
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $Obj_CF =       new Custom_Form;
    $ID =           $Obj_CF->get_ID_by_name( $cp['name']);
    $Obj_CF->_set_ID($ID); // Custom form now has its own implementation
    $xml =          $Obj_CF->get_field('content');
    $Obj_HTML =     new HTML;
    $out.=          ($print!=1 ? $Obj_HTML->draw_toolbar('custom_form',array('ID'=>$ID,'name'=> $cp['name'])) : "");
    $out.=          $Obj_CF->draw($xml,$cp);
    return          $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_CUSTOM_FORM;
  }
}
?>