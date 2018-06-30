<?php
define('VERSION_COUNTRY','1.0.1');
/*
Version History:
  1.0.1 (2011-09-06)
    1) Changes to Country::get_iso3166() to use lst_named_type and avoid
       hard-coded listtypeID and made static
  1.0.0 (2009-07-02)
    Initial release
*/
class Country extends lst_named_type {
  var $countries;
  public function __construct($ID="") {
    parent::__construct($ID,'lst_country','Country Definition');
  }

  public static function get_iso3166($country){
    switch ($country) {
      case 'CAN':
        return 'CA';
      break;
      case 'USA':
        return 'US';
      break;
      default:
        $Obj = new lst_named_type('','lst_iso3166_country','ISO 3166 Country Code');
        return $Obj->get_value_for_text($country);
      break;
    }
  }

  public function get_version(){
    return VERSION_COUNTRY;
  }
}
?>