<?php
define('VERSION_KEYWORD','1.0.10');
/*
Version History:
  1.0.10 (2011-11-18)
    1) Keyword::delete() now removes associated csv entries for all types
       including products (not previously handled) and contacts
       (was looking in contacts table for these - that doesn't event exist now!)
  1.0.9 (2011-10-26)
    1) Keyword::get_related() now includes related products
  1.0.8 (2011-10-19)
    1) Change to Keyword::get_related() to reference `effective_date_start`

  (Older version history in class.keyword.txt)
*/
class Keyword extends Record {
  function __construct($ID=""){
    parent::__construct("keywords",$ID);
    $this->_set_object_name('Keyword');
    $this->_set_name_field('keyword');
  }

  function delete(){
    $ID_csv = explode(",",$this->_get_ID());
    foreach ($ID_csv as $ID){
      $this->_set_ID($ID);
      // Find all assignments for keyword(s)
      $keyword = $this->get_name();
      $sql =
         "SELECT\n"
        ."  `assignID`,\n"
        ."  `assign_type`\n"
        ."FROM\n"
        ."  `keyword_assign`\n"
        ."WHERE\n"
        ."  `keywordID`=".$ID."\n"
        ."ORDER BY\n"
        ."  `assign_type`";
      $records = $this->get_records_for_sql($sql);
      $assign_type_arr = array();
      foreach ($records as $record) {
        $assignID =       $record['assignID'];
        $assign_type =    $record['assign_type'];
        if (!isset($assign_type_arr[$assign_type])) {
          $assign_type_arr[$assign_type] = array();
        }
        $assign_type_arr[$assign_type][] = $assignID;
      }
      foreach ($assign_type_arr as $assign_type=>$assignID_arr) {
        $assignID_csv = implode(",",$assignID_arr);
        switch ($assign_type) {
          case "contact":
          case "person":
            $table = "person";
          break;
          case "pages":
            $table = "pages";
          break;
          case "product":
            $table = "product";
          break;
          default:
            $table = "postings";
          break;
        }
        $sql =
           "UPDATE\n"
          ."  `".$table."`\n"
          ."SET\n"
          ."  `keywords` = SUBSTRING(REPLACE(CONCAT(', ',`keywords`),', ".$keyword."',''),3)\n"
          ."WHERE\n"
          ."  `ID` IN (".$assignID_csv.")";
        $this->do_sql_query($sql);
      }

      $sql =
         "DELETE FROM\n"
        ."  `keyword_assign`\n"
        ."WHERE\n"
        ."  `keywordID` = ".$ID;
      $this->do_sql_query($sql);
      parent::delete();
    }
  }

  function export_sql($targetID,$show_fields) {
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with assignment records";
    $extra_delete = "DELETE FROM `keyword_assign`         WHERE `keywordID` IN (".$targetID.");\n";
    $Obj = new Backup;
    $extra_select = $Obj->db_export_sql_query("`keyword_assign`        ","SELECT * FROM `keyword_assign` WHERE `keywordID` IN(".$targetID.") ORDER BY `keywordID`;",$show_fields)."\n";
    return  $this->sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_keywordIDs_list_by_keywords_list($keywords) {
    $keywords_arr = explode(",",str_replace(" ","",$keywords));
    $keywords = "\"".implode("\",\"",$keywords_arr)."\"";
    $sql =
       "SELECT\n"
      ."  GROUP_CONCAT(`keywords`.`ID` ORDER BY `keyword` SEPARATOR ', ') `csv`\n"
      ."FROM\n"
      ."  `keywords`\n"
      ."WHERE\n"
      ."  `keywords`.`keyword` IN(".$keywords.")";
    return Keyword::get_field_for_sql($sql);
  }

  function get_keywords_list_by_IDs_list($keywordIDs) {
    $sql =
       "SELECT\n"
      ."  GROUP_CONCAT(`keywords`.`keyword` ORDER BY `keyword` SEPARATOR ', ') `csv`\n"
      ."FROM\n"
      ."  `keywords`\n"
      ."WHERE\n"
      ."  `keywords`.`ID` IN(".$keywordIDs.")";
    return Keyword::get_field_for_sql($sql);
  }

  function get_keyword_list_with_weight($systemIDs_csv=false,$all_sites=false,$min=1){
    $systemIDs_csv = ($systemIDs_csv===false ? SYS_ID : $systemIDs_csv);
    $sql =
       "SELECT\n"
      ."  `keywords`.`ID`,\n"
      ."  `keywords`.`keyword`,\n"
      ."  COUNT(`keyword_assign`.`ID`) `count`\n"
      ."FROM\n"
      ."  `keywords`\n"
      ."INNER JOIN `keyword_assign` ON\n"
      ."  `keyword_assign`.`keywordID` = `keywords`.`ID`\n"
      ."WHERE\n"
      ."  `keywords`.`systemID` IN(1,".$systemIDs_csv.") AND\n"
      ."  `keyword_assign`.`systemID` IN(".$systemIDs_csv.")\n"
      ."GROUP BY\n"
      ."  `keywords`.`ID`\n"
      .($min>1 ? "HAVING COUNT(`keyword_assign`.`ID`)>=".$min."\n" : "")
      ."ORDER BY\n"
      ."  `keyword`";
    $records = Keyword::get_records_for_sql($sql);
    $total =    0;
    $max =      0;
    foreach ($records as $record) {
      $total += $record['count'];
      if ($record['count']>$max) {
        $max = $record['count'];
      }
    }
    foreach ($records as &$record) {
      $record['weight'] = round(100*$record['count']/$max);
    }
    return $records;
  }

  function get_related($ID_csv,$systemIDs_csv='',$this_type,$this_ID,$limit=false,&$related_matches_total) {
    $systemIDs_csv =  ($systemIDs_csv=='' ? SYS_ID : $systemIDs_csv);
    $related_matches_total = 0;
    $out = array();
    $sql =
       "SELECT\n"
      ."  `ka`.`assign_type` `type`,\n"
      ."  `ka`.`assignID` `ID`,\n"
      ."  `s`.`URL` `systemURL`,\n"
      ."  `s`.`textEnglish` `systemTitle`,\n"
      ."  COALESCE(`pa`.`systemID`,`po`.`systemID`,`pr`.`systemID`) `systemID`,\n"
      ."  COALESCE(`pa`.`page`,`po`.`name`,`pr`.`itemCode`) `name`,\n"
      ."  COALESCE(`pa`.`path`,`po`.`path`,CONCAT('//',`pr`.`itemCode`)) `path`,\n"
      ."  COALESCE(`pa`.`group_assign_csv`,`po`.`group_assign_csv`,`pr`.`group_assign_csv`) `group_assign_csv`,\n"
      ."  COALESCE(`pa`.`permPUBLIC`,`po`.`permPUBLIC`,`pr`.`permPUBLIC`) `permPUBLIC`,\n"
      ."  COALESCE(`pa`.`permSYSLOGON`,`po`.`permSYSLOGON`,`pr`.`permSYSLOGON`) `permSYSLOGON`,\n"
      ."  COALESCE(`pa`.`permSYSMEMBER`,`po`.`permSYSMEMBER`,`pr`.`permSYSMEMBER`) `permSYSMEMBER`,\n"
      ."  COALESCE(`pa`.`title`,`po`.`title`,`pr`.`title`) `title`,\n"
      ."  COALESCE(`po`.`effective_date_start`,`pr`.`active_date_from`,'') `date`,\n"
      ."  COALESCE(`pa`.`history_created_date`,`po`.`history_created_date`,`pr`.`history_created_date`) `history_created_date`,\n"
      ."  COALESCE(`pa`.`page`,'','') `page`,\n"
      ."  GROUP_CONCAT(`k`.`keyword` ORDER BY `k`.`keyword` SEPARATOR ', ') `matched`,\n"
      ."  COUNT(`ka`.`ID`) `hits`\n"
      ."FROM\n"
      ."  `keyword_assign` `ka`\n"
      ."INNER JOIN `keywords` `k` ON\n"
      ."  `ka`.`keywordID` = `k`.`ID`\n"
      ."LEFT JOIN `postings` `po` ON\n"
      ."  `ka`.`assign_type` IN('article','event','gallery-album','gallery-image','news item','job posting','podcast') AND\n"
      ."  `ka`.`assignID` = `po`.`ID`\n"
      ."LEFT JOIN `pages` `pa` ON\n"
      ."  `ka`.`assign_type` IN('page') AND\n"
      ."  `ka`.`assignID` = `pa`.`ID`\n"
      ."LEFT JOIN `product` `pr` ON\n"
      ."  `ka`.`assign_type` IN('product') AND\n"
      ."  `ka`.`assignID` = `pr`.`ID`\n"
      ."INNER JOIN `system` `s` ON\n"
      ."  `s`.`ID` = COALESCE(`po`.`systemID`,`pa`.`systemID`,`pr`.`systemID`)\n"
      ."WHERE\n"
      ."  `s`.`ID` IN(".$systemIDs_csv.") AND\n"
      ."  (\n"
      ."    COALESCE(`pa`.`systemID`,`po`.`systemID`,`pr`.`systemID`)=".SYS_ID." OR\n"
      ."    COALESCE(`pa`.`permPUBLIC`,`po`.`permPUBLIC`,`pr`.`permPUBLIC`) = 1\n"
      ."  ) AND\n"
      ."  `ka`.`keywordID` IN(".$ID_csv.") AND\n"
      ."  NOT (`ka`.`assign_type`='".$this_type."' AND `ka`.`assignID`=".$this_ID.")\n"
      ."GROUP BY\n"
      ."  COALESCE(`pa`.`ID`,`po`.`ID`,`pr`.`ID`)\n"
      ."ORDER BY\n"
      ."  `hits` DESC,\n"
      ."  `history_created_date` DESC\n";
//    z($sql);
    $records = Record::get_records_for_sql($sql);
    if ($records) {
      foreach ($records as $row) {
        if ($row['systemID']==SYS_ID) {
          $visible = $this->is_visible($row);
        }
        else {
          $visible = $row['permPUBLIC'];
        }
        if ($visible) {
          // Visible record, increment count
          $related_matches_total++;
          if ($limit===false || $limit > count($out)) {
            $out[] = $row;
          }
        }
      }
    }
//      .($limit ? "LIMIT ".$limit : "");
//    z($sql);
    return $out;
  }

  function get_selector_sql($getID=false) {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    return
        "SELECT\n"
       ."  'd0d0d0' AS `color_background`,\n"
       ."  '000000' AS `color_text`,\n"
       ."  '(None)' AS `text`,\n"
       ."  '' AS `value`\n"
       ."UNION SELECT\n"
       ."  IF(`keywords`.`systemID`=1,\n"
       ."    'e0e0ff',\n"
       ."    IF(`keywords`.`systemID`=".SYS_ID.",\n"
       ."      'c0ffc0',\n"
       ."      'ffe0e0'\n"
       ."    )\n"
       ."  ) AS `color_background`,\n"
       ."  '000000' AS `color_text`,\n"
       ."  CONCAT(\n"
       ."    IF(`keywords`.`systemID` = 1,\n"
       ."      '* ',\n"
       .($isMASTERADMIN ?
          "      CONCAT(UPPER(`system`.`textEnglish`),' | ')\n"
        : "      ''\n"
        )
       ."    ),\n"
       ."    `keyword`,\n"
       ."    ' (',\n"
       ."    (SELECT COUNT(`keyword_assign`.`ID`) FROM `keyword_assign` WHERE `keywordID` = `keywords`.`ID`),\n"
       ."    ')'\n"
       ."  ) `text`,\n"
       .($getID ?
           "  `keywords`.`ID` `value`\n"
         : "  `keywords`.`keyword` `value`\n"
        )
       ."FROM\n"
       ."  `keywords`\n"
       .($isMASTERADMIN ?
           "INNER JOIN `system` ON\n"
          ."   `system`.`ID` = `keywords`.`systemID`\n"
        :  ""
        )
       ."WHERE\n"
       .($isMASTERADMIN ?
           "  1\n"
        :  "  `keywords`.`systemID` IN(1,".SYS_ID.")\n"
        )
       ."ORDER BY\n"
       ."  `text`";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  function handle_report_delete(&$msg) {
    $targetID = $this->_get_ID();
    $keywords = $this->get_name();
    $usage =    $this->usage_count();
    $this->delete();
    $msg =    status_message(0,true,$this->_get_object_name()," <b>".$keywords."</b> and ".$usage." linked reference".($usage==1 ? " has" : "s have"),'been deleted.',$targetID);
  }

  function usage_count(){
    $sql =
       "SELECT\n"
      ."  COUNT(*) AS `count`\n"
      ."FROM\n"
      ."  `keyword_assign`\n"
      ."WHERE\n"
      ."  `keywordID` IN(".$this->_get_ID().")";
//    z($sql);
    return $this->get_field_for_sql($sql);
  }

  public function get_version(){
    return VERSION_KEYWORD;
  }
}
?>