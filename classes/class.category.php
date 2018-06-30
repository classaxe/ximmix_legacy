<?php
define('VERSION_CATEGORY','1.0.1');
/*
Version History:
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Category extends Record {
  // Not really lst_named_type as may draw from multiple list types
  function __construct($ID="") {
    parent::__construct('listdata',$ID);
    $this->_set_name_field('value');
  }
  function get_labels_for_values($search_categories,$types){
    if ($types==""){
      return $search_categories;
    }
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $sql =
       "SELECT\n"
      ."  `listdata`.`textEnglish` `text`,\n"
      ."  `value`,\n"
      ."  IF (`listdata`.`systemID`=1,1,2) `seq`\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."INNER JOIN `listtype` ON\n"
      ."  `listdata`.`listTypeID` = `listtype`.`ID`\n"
      ."WHERE\n"
      .($isMASTERADMIN ?
          ""
       :  "  `listdata`.`systemID` IN(1,".SYS_ID.") AND\n"
       )
      ."  `listtype`.`name` IN (".$types.") AND\n"
      ."  `listdata`.`value` IN (".str_replace(' ','',$search_categories).")\n"
      ."ORDER BY\n"
      ."  `seq`,`text`\n";
//    z($sql);die;
    $records = Category::get_records_for_sql($sql);
    $out = array();
    foreach ($records as $record){
      $out[$record['value']] = $record['text'];
    }
    return $out;
  }

  function get_selector_sql($types){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    return
       "SELECT\n"
      ."  '(None)' AS `text`,\n"
      ."  '' AS `value`,\n"
      ."  'd0d0d0' AS `color_background`,\n"
      ."  1 `seq`\n"
      ."UNION SELECT\n"
      ."  CONCAT(\n"
      ."    IF(`listdata`.`systemID` = 1,\n"
      ."      '* ',\n"
      .($isMASTERADMIN ?
         "      CONCAT(\n"
        ."        UPPER(`system`.`textEnglish`),\n"
        ."        ' | ',\n"
        ."        REPLACE(`listtype`.`name`,' Category',': ')\n"
        ."      )\n"
       : "      ''\n"
       )
      ."    ),\n"
      ."    `listdata`.`textEnglish`\n"
      ."  ) `text`,\n"
      ."  `listdata`.`value`,\n"
      ."  `listdata`.`color_background`,\n"
      ."  `listdata`.`seq`\n"
      ."FROM\n"
      ."  `listdata`\n"
      ."INNER JOIN `listtype` ON\n"
      ."  `listdata`.`listTypeID` = `listtype`.`ID`\n"
      .($isMASTERADMIN ?
          "INNER JOIN `system` ON\n"
         ."   `system`.`ID` = `listdata`.`systemID`\n"
       :  ""
       )
      ."WHERE\n"
      .($isMASTERADMIN ?
          ""
       :  "  `listdata`.`systemID` IN(1,".SYS_ID.") AND\n"
       )
      ."  `listtype`.`name` IN (".$types.") AND\n"
      ."  `listdata`.`value`!=''\n"
      ."ORDER BY\n"
      ."  `seq`,`text`\n";
  }
  public function get_version(){
    return VERSION_CATEGORY;
  }
}
?>