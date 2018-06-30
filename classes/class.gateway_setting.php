<?php
define('VERSION_GATEWAY_SETTING','1.0.4');
/*
Version History:
  1.0.4 (2012-09-05)
    1) Gateway_Setting::do_donation() wasn't recognising gateway types of
       'Paypal (Live)' and 'Paypal (Test)' - it does now.
  1.0.3 (2012-05-08)
    1) Added handle_report_copy() method for cloning entries
  1.0.2 (2011-10-04)
    1) Added Gateway_Setting::get_selector_sql()
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Gateway_Setting extends Record{

  public function __construct($ID="") {
    parent::__construct("gateway_settings",$ID);
    $this->_set_object_name('Gateway Setting');
  }

  public function do_donation() {
    $Obj_gateway = new System(SYS_ID);
    $gateway_record = $Obj_gateway->get_gateway();
    if ($gateway_record===false) {
      do_log(3,'Gateway_Setting::do_donation()','(none)','There is no gateway defined for this system.');
      return 'There is no gateway defined for this system.';
    }
    switch($gateway_record['type']['name']) {
      case "Bean Stream":
        return "The Donation button is not currently supported with Beanstream gateways";
      break;
      case "Paypal (Live)":
      case "Paypal (Test)":
        return
           "<html><body onload=\"document.getElementById('form').submit();\">\n"
          ."<form id=\"form\" action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">\n"
          .draw_form_field('cmd','_s-xclick','hidden')
          .draw_form_field('encrypted',$_REQUEST['targetValue'],'hidden')
          ."</form></body></html>";
      break;
      default:
        return "The Donation button is not currently supported with the ".$gateway_record['type']['name']." gateway type for this system.";
      break;
    }
  }

  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public static function get_selector_sql() {
    return
       "SELECT\n"
      ."  `gateway_settings`.`name` AS `text`,\n"
      ."  `gateway_settings`.`ID` AS `value`\n"
      ."FROM\n"
      ."  `gateway_settings`\n"
      ."WHERE\n"
      ."  `gateway_settings`.`systemID` IN(1,".SYS_ID.")\n"
      ."ORDER BY\n"
      ."  `gateway_settings`.`name`";

  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  public function test_requiresSSL() {
    $sql =
       "SELECT\n"
      ."  `gateway_settings`.`forceSSL` AS `forceSSL`\n"
      ."FROM\n"
      ."  `gateway_settings`\n"
      ."WHERE\n"
      ."  `gateway_settings`.`ID` = ".$this->_get_ID();
    return $this->get_field_for_sql($sql);
  }

  public function get_version(){
    return VERSION_GATEWAY_SETTING;
  }
}
?>