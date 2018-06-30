<?php
define('VERSION_GEOCODE_CACHE','1.0.2');
/*
Version History:
  1.0.2 (2014-02-13)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.geocode_cache.txt)
*/
class Geocode_Cache extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, input_address, match_area, match_quality, match_type, output_address, output_json, output_lat, output_lon, partial_match, query_date, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  const queries_per_day = 2400;    // Now 100 less than Google's daily maximum
  const max_cache_age =   90;     // Maximum number of days to cache previous results

  public function __construct($ID=""){
    parent::__construct("geocode_cache",$ID);
    $this->_set_object_name('Geocode Cache Entry');
    $this->_set_has_groups(false);
  }

  public function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  public function get_daily_count($systemID=SYS_ID){
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".$systemID." AND\n"
      ."  `query_date`='".date('Y-m-d',time())."'";
    return (int)$this->get_field_for_sql($sql);
  }

  public function get_cached_location($address,$systemID=SYS_ID){
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".$systemID." AND\n"
      ."  `input_address` = \"".$address."\"";
//    z($sql);
    return $this->get_record_for_sql($sql);
  }

  public function get_version(){
    return VERSION_GEOCODE_CACHE;
  }
}
?>