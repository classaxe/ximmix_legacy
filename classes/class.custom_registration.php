<?php
define('VERSION_CUSTOM_REGISTRATION','1.0.3');
/*
Version History:
  1.0.3 (2012-08-28)
    1) Further tweak to correct newline conversion - wasn't quite right last time 
  1.0.2 (2012-08-24)
    1) Now converts embedded newlines to break tags
  1.0.1 (2012-08-24)
    1) New method Custom_Registration::get_notification_summary()
  1.0.0 (2012-06-11)
    1) Initial Release

*/
class Custom_Registration extends Posting {
  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_type('custom-registration');
    $this->_set_assign_type('custom-registration');
    $this->_set_object_name('Custom Registration');
    $this->_set_has_publish_date(false);      // Do now allow item to be seen prior to publish date
    $this->_set_has_activity(false);
    $this->_set_has_categories(false);
    $this->_set_has_groups(false);
    $this->_set_has_keywords(false);
    $this->_set_message_associated('');
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_notification_summary($datetime,$systemID,$base_url){
    $records = $this->get_records_since($datetime,$systemID);
    if (!count($records)){
      return;
    }
    $out = "<h2>New ".$this->_get_object_name().$this->plural('1,2')."</h2>\n";
    foreach ($records as $record){
      $this->xmlfields_decode($record);
      $data = array(
        'Category' =>   $record['category'],
        'Date' =>       $record['history_created_date']
      );
      foreach($record as $key=>$value){
        if (substr($key,0,4)=='xml:'){
          $data[substr($key,4)] = $value;
        }
      }
      $out.= "<table cellpadding='2' cellspacing='0' border='1'>\n";
      foreach($data as $label=>$value){
        $value = str_replace(array("\\r","\\n"),array("\r","\n"),$value);
        $out.=
           "  <tr>\n"
          ."    <th style='vertical-align:top; text-align: left'>".$label."</th>\n"
          ."    <td style='vertical-align:top; text-align: left'>".nl2br($value)."</td>\n"
          ."  </tr>\n";
      }
      $out.=
         "</table>\n"
        ."<hr />\n";
    }
    return $out;
  }

  public function get_version(){
    return VERSION_CUSTOM_REGISTRATION;
  }
}
?>