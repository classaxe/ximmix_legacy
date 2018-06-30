<?php
define('VERSION_TABLE','1.0.5');
/*
Version History:
  1.0.5 (2012-09-08)
    1) Tweak to table::get_checksum() to no longer ignore fields prefixed with word
       'module' - new modules should from now on use XML fields instead of hacking
       database structure
  1.0.4 (2012-09-04)
    1) Removed Table::copy() - unused
    2) Removed Table::uniqID() - unused
    3) Removed Table::get_tables_and_fields() - unused
  1.0.3 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.2 (2009-11-13)
    1) Tweak to Table::get_table_create_sql() to be conformant with mysql 4.1.20(!)
  1.0.1 (2009-11-10)
    1) Table::get_table_create_sql() now makes three subtle changes to deal with
       differences in TABLE CREATE output between 5.0.68 and 5.1.36
  1.0.0 (2009-07-02)
    Initial release
*/
class Table extends Record{
  static $cache_create_table_array =  array();

  function __construct($table="") {
    if ($table!="") {
      $this->_set_table_name($table);
      $this->_set_object_name('Table');
    }
  }

  function get_checksum($ignore_custom_fields = true) {
    $sql = $this->get_table_create_sql($this->table);
    if ($ignore_custom_fields) {
      $sql = preg_replace('/  `cus_([^\n])*\n/','',$sql);
    }
    return crc32($sql);
  }

  function get_table_create_sql($name,$remove_autonum=true) {
    if (isset(Table::$cache_create_table_array[$name])) {
      return Table::$cache_create_table_array[$name];
    }
    $sql =      "SHOW CREATE TABLE ".$name."";
    $record =   $this->get_record_for_sql($sql);
    $out =      $record['Create Table'];
    $out =  str_replace('default','DEFAULT',$out);
    $out =  str_replace('PRIMARY KEY  ','PRIMARY KEY ',$out);
    $out =  str_replace('auto_increment','AUTO_INCREMENT',$out);
    if ($remove_autonum) {
      $out =      preg_replace('/ AUTO_INCREMENT=([0-9])*/','',$out);
    }
    Table::$cache_create_table_array[$name] = $out;
//    z($out);die;
    return $out;
  }

  function get_tables_names() {
    $sql =      "SHOW TABLE STATUS";
    $records =  $this->get_records_for_sql($sql);
    $out =      array();
    foreach ($records as $record) {
      $out[] =  array('Name'=>$record['Name']);
    }
    return $out;
  }

  function hasSystemID($table){
    $sql =
      "SHOW COLUMNS FROM `".$table."`";
    $rows = $this->get_records_for_sql($sql);
    foreach ($rows as $row) {
      if ($row['Field'] == 'systemID') {
        return true;
      }
    }
    return false;
  }

  public function get_version(){
    return VERSION_TABLE;
  }
}
?>