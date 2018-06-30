<?php
define('VERSION_LST_EFFECTIVE_PERIOD_UNIT','1.0.0');
/*
Version History:
  1.0.0 (2010-09-20)
    Initial release
  0.
*/
class lst_effective_period_unit extends lst_named_type{
  function __construct($ID="") {
    parent::__construct($ID, 'lst_effective_period_unit','Effective Period Unit');
  }
  public function get_version(){
    return VERSION_LST_EFFECTIVE_PERIOD_UNIT;
  }
}
?>