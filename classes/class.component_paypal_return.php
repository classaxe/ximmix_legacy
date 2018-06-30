<?php
define ("VERSION_COMPONENT_PAYPAL_RETURN","1.0.1");
/*
Version History:
  1.0.1 (2013-10-11)
    1) Now reports error if there are no gateway settings defined for component
       or for the site
  1.0.0 (2012-07-31)
    1) Initial release
*/
class Component_Paypal_Return extends Component_Base {
  protected $_Obj_Gateway;
  protected $_Obj_Gateway_Settings;
  protected $_Obj_Gateway_Type;

  public function __construct(){
    $this->_ident =             "paypal_return";
    $this->_parameter_spec =    array(
      'gateway' =>      array('match' => '',    'default'=>'',    'hint'=>'Gateway Settings to use when checking transaction status - leave blank to use default gateway settings for site')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    try{
      $this->_load_gateway_settings();
    }
    catch (Exception $e){
      $this->_msg = $e->getMessage();
      $this->_draw_status();
      return $this->_html;
    }
    $settings = array(
      'submitButtonText' => 'Click to continue to PayPal'
    );
    $this->_Obj_Gateway = new PayPal_Gateway($settings,$this->_gateway_record);
    $this->_html.= $this->_Obj_Gateway->simplePaymentVerify();
    return $this->_html;
  }

  protected function _draw_status(){
    $this->_html.=      HTML::draw_status($this->_safe_ID,$this->_msg);
  }

  protected function _load_gateway_settings(){
    if ($this->_cp['gateway']){
      $this->_load_gateway_settings_specified();
      return;
    }
    $this->_load_gateway_settings_default();
  }

  protected function _load_gateway_settings_default(){
    $Obj_System = new System(SYS_ID);
    if (!$this->_gateway_record = $Obj_System->get_gateway()){
      throw new exception('<b>Error:</b> No Gateway Settings specified either in the system or for this page');
    }
  }

  protected function _load_gateway_settings_specified(){
    $this->_Obj_Gateway_Settings = new Gateway_Setting;
    $this->_gateway_record = array(
      'settings',
      'type'
    );
    if (!$GS_ID = $this->_Obj_Gateway_Settings->get_ID_by_name($this->_cp['gateway'],SYS_ID)){
      throw new exception('<b>Error:</b> Invalid Gateway settings - the Gateway Settings named &quot;'.$this->_cp['gateway'].'&quot; cannot be found');
    }
    $this->_Obj_Gateway_Settings->_set_ID($GS_ID);
    $this->_gateway_record['settings'] = $this->_Obj_Gateway_Settings->get_record();
    $this->_Obj_Gateway_Type = new Gateway_Type($this->_gateway_record['settings']['gateway_typeID']);
    $this->_gateway_record['type'] =     $this->_Obj_Gateway_Type->get_record();
  }

  public function get_version(){
    return VERSION_COMPONENT_PAYPAL_RETURN;
  }

}
?>