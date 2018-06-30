<?php
define('VERSION_COLOUR_SCHEME','1.0.1');
/*
Version History:
  1.0.1 (2011-10-04)
    1) Made Colour_Scheme::get_selector_sql() static
  1.0.0 (2009-07-02)
    Initial release
*/
class Colour_Scheme extends Record {
  function __construct($ID="") {
    parent::__construct("colour_scheme",$ID);
  }

  function get_match($systemID) {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $sql =
       "SELECT\n"
      ."  `c`.`ID` AS `color_schemeID`\n"
      ."FROM\n"
      ."  `colour_scheme` AS `c`,\n"
      ."  `system` AS `s`\n"
      ."WHERE\n"
      ."  `s`.`ID` = ".$systemID." AND\n"
      .($isMASTERADMIN ?
         ""
       : "  `c`.`systemID` IN(1,".SYS_ID.") AND\n")
      ."  `c`.`cal_border`=        `s`.`cal_border` AND\n"
      ."  `c`.`cal_current`=       `s`.`cal_current` AND\n"
      ."  `c`.`cal_current_we`=    `s`.`cal_current_we` AND\n"
      ."  `c`.`cal_days`=          `s`.`cal_days` AND\n"
      ."  `c`.`cal_event`=         `s`.`cal_event` AND\n"
      ."  `c`.`cal_head`=          `s`.`cal_head` AND\n"
      ."  `c`.`cal_then`=          `s`.`cal_then` AND\n"
      ."  `c`.`cal_then_we`=       `s`.`cal_then_we` AND\n"
      ."  `c`.`cal_today`=         `s`.`cal_today` AND\n"
      ."  `c`.`colour1`=           `s`.`colour1` AND\n"
      ."  `c`.`colour2`=           `s`.`colour2` AND\n"
      ."  `c`.`colour3`=           `s`.`colour3` AND\n"
      ."  `c`.`colour4`=           `s`.`colour4` AND\n"
      ."  `c`.`defaultBgColor`=    `s`.`defaultBgColor` AND\n"
      ."  `c`.`table_border`=      `s`.`table_border` AND\n"
      ."  `c`.`table_data`=        `s`.`table_data` AND\n"
      ."  `c`.`table_header`=      `s`.`table_header` AND\n"
      ."  `c`.`text_heading` =     `s`.`text_heading`\n"
      ."ORDER BY\n"
      ."  `c`.`systemID`=1\n"   //; z($sql);die;
      ."LIMIT\n"
      ."  0,1";
//    z($sql);
    return $this->get_field_for_sql($sql);
  }

  public static function get_selector_sql() {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN) {
      return
         "SELECT\n"
        ."  0 AS `seq`,\n"
        ."  1 AS `value`,\n"
        ."  'Custom Scheme...' AS `text`,\n"
        ."  'd0d0d0' AS `color_background`\n"
        ."UNION SELECT\n"
        ."  1,\n"
        ."  `colour_scheme`.`ID`,\n"
        ."  CONCAT(\n"
        ."    IF(`systemID` = 1,\n"
        ."      '* ',\n"
        ."      CONCAT(UPPER(`system`.`textEnglish`),' | ')\n"
        ."    ),\n"
        ."    `colour_scheme`.`name`\n"
        ."  ),\n"
        ."  IF(`systemID` = 1,\n"
        ."    'e0e0ff',\n"
        ."    IF(`systemID` = SYS_ID,\n"
        ."      'c0ffc0',\n"
        ."      'ffe0e0'\n"
        ."    )\n"
        ."  )\n"
        ."FROM\n"
        ."  `colour_scheme`\n"
        ."INNER JOIN `system` ON\n"
        ."  `colour_scheme`.`systemID` = `system`.`ID`\n"
        ."ORDER BY\n"
        ."  `seq`,`text`";
    }
    return
       "SELECT\n"
      ."  0 AS `seq`,\n"
      ."  1 AS `value`,\n"
      ."  'Custom Scheme...' AS `text`,\n"
      ."  'd0d0d0' AS `color_background`\n"
      ."UNION SELECT\n"
      ."  1,\n"
      ."  `colour_scheme`.`ID` AS `value`,\n"
      ."  CONCAT(IF(`systemID`=1,IF(`colour_scheme`.`ID`=1,'','* '),''),`colour_scheme`.`name`) AS `text`,\n"
      ."  IF(`systemID` = 1,\n"
      ."    'e0e0ff',\n"
      ."    'c0ffc0'\n"
      ."  ) AS `color_background`\n"
      ."FROM\n"
      ."  `colour_scheme`\n"
      ."WHERE\n"
      ."  `systemID` IN(1,SYS_ID)\n"
      ."ORDER BY\n"
      ."  `seq`,`text`";
  }

  function Lookup($data) {
    ksort($data);
    foreach ($data as $key=>$value) {
      $sql_field[] = "  `$key` = \"$value\"";
    }
    $sql =
       "SELECT\n"
      ."  `name`,\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `colour_scheme`\n"
      ."WHERE\n"
      ."  `systemID` =         \"".SYS_ID."\" AND\n"
      .implode(" AND\n",$sql_field)."\n";
//    z($sql);
    return $this->get_record_for_sql($sql);
  }

  public function get_version(){
    return VERSION_COLOUR_SCHEME;
  }
}
?>