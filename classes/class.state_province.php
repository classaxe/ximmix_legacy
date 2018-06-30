<?php
define('VERSION_STATE_PROVINCE','1.0.4');
/*
Version History:
  1.0.4 (2011-06-01)
    1) Removed State_Province::draw_selector() - now obsolete
  1.0.3 (2011-03-31)
    1) Added State_Province::get_records_for_country()
  1.0.2 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.1 (2010-05-28)
    1) Change to State_Province::draw_selector() to correct alpha sort of states
       within each country
  1.0.0 (2009-07-02)
    Initial release
*/
class State_Province extends lst_named_type {

  function __construct($ID="") {
    parent::__construct($ID,'lst_sp','State / Province Name');
  }

  function get_records_by_country($country='',$systemID='',$sortBy='') {
    $countryID = false;
    if ($country){
      $Obj = new Country;
      $countryID = $Obj->get_ID_by_name($country,$systemID='');
    }
    return $this->get_records_by_parentID($countryID, $systemID, $sortBy);
  }


  public function get_version(){
    return VERSION_STATE_PROVINCE;
  }
}
?>