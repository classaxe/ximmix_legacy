<?php
define('VERSION_ACTIVITY','1.0.18');
/*
Version History:
  1.0.18 (2014-02-17)
    1) Refreshed fields list - now statically declared in class definition

  (Older version history in class.activity.txt)
*/
class Activity extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, count_total_comments, count_total_emails, count_total_ratings, count_total_visits, count_weighted_comments, count_weighted_emails, count_weighted_ratings, count_weighted_visits, rating_percent, rating_submissions, sourceID, sourceType, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  protected $_num_updated_records = 0;
  protected $_sites_with_activity_tracking = '';

  function __construct($ID="") {
    parent::__construct("activity",$ID);
    $this->_set_object_name("Activity");
  }

  public function decay_all(){
    $decay_rate = 0.95;
    $this->_decay_all_get_systemIDs();
    $this->_decay_all_update_records($decay_rate);
    return $this->_decay_all_get_status_message();
  }

  protected function _decay_all_get_systemIDs(){
    $sql =
       "SELECT\n"
      ."  GROUP_CONCAT(`ID`)\n"
      ."FROM\n"
      ."  `system`\n"
      ."WHERE\n"
      ."  `features` LIKE '%activity_tracking%'";
    $this->_sites_with_activity_tracking = $this->get_field_for_sql($sql);
  }

  protected function _decay_all_get_status_message(){
    $num_sites = count(explode(',',$this->_sites_with_activity_tracking));
    return
       ($this->_num_updated_records==1 ? "one activity record" :  $this->_num_updated_records." activity records")
      ." updated, "
      .($num_sites==1 ? "One site" : $num_sites." sites")
      ." affected.";
  }

  protected function _decay_all_update_records($decay_rate) {
    $sql =
       "UPDATE\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."SET\n"
      ."  `count_weighted_comments` = `count_weighted_comments` * ".$decay_rate.",\n"
      ."  `count_weighted_emails` =   `count_weighted_emails`   * ".$decay_rate.",\n"
      ."  `count_weighted_ratings` =  `count_weighted_ratings`  * ".$decay_rate.",\n"
      ."  `count_weighted_visits` =   `count_weighted_visits`   * ".$decay_rate."\n"
      ."WHERE\n"
      ."  `systemID` IN(".$this->_sites_with_activity_tracking.")";
    $this->do_sql_query($sql);
    $this->_num_updated_records = Record::get_affected_rows();
  }

  function do_tracking($activity,$sourceType,$sourceID,$amount=1) {
    switch ($sourceType){
      case 'Report':
        return;
      break;
    }
    if (!$sourceID){
      return;
    }
    $data = $this->get_record_for_item($sourceType,$sourceID);
    if ($data) {
      $this->_set_ID($data['ID']);
      $data['count_total_'.$activity] =    ($data['count_total_'.$activity]+$amount >0 ?    $data['count_total_'.$activity]+$amount : 0);
      $data['count_weighted_'.$activity] = ($data['count_weighted_'.$activity]+$amount >0 ? $data['count_weighted_'.$activity]+$amount : 0);
      $data['rating_submissions'] =         addslashes($data['rating_submissions']);
      unset($data['history_modified_date']);
      unset($data['history_modified_by']);
      unset($data['history_modified_IP']);
    }
    else {
      $data =
        array(
          'systemID' =>                     SYS_ID,
          'count_total_'.$activity =>       ($amount > 0 ? $amount : 0),
          'count_weighted_'.$activity =>    ($amount > 0 ? $amount : 0),
          'sourceID' =>                     $sourceID,
          'sourceType' =>                   $sourceType,

        );
    }
    $ID = $this->update($data);
    return $ID;
  }

  function get_record_for_item($sourceType,$sourceID){
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".SYS_ID." AND\n"
      ."  `sourceType` = '".$sourceType."' AND\n"
      ."  `sourceID` = ".$sourceID;
    return $this->get_record_for_sql($sql);
  }

  function get_rating($sourceType,$sourceID) {
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `count_total_ratings`,\n"
      ."  `count_weighted_ratings`,\n"
      ."  `rating_percent`,\n"
      ."  `rating_submissions`\n"
      ."FROM"
      ."  `activity`\n"
      ."WHERE\n"
      ."  `sourceType` = '".$sourceType."' AND\n"
      ."  `sourceID` = ".$sourceID;
    $result = $this->get_record_for_sql($sql);
    return
      array(
        'ID' =>                     ($result ? $result['ID'] : false),
        'count_total_ratings' =>    ($result ? $result['count_total_ratings'] : 0),
        'count_weighted_ratings' => ($result ? $result['count_weighted_ratings'] : 0),
        'percent' =>                ($result ? $result['rating_percent'] : 0),
        'votes' =>                  ($result && $result['rating_submissions']!='' ? unserialize($result['rating_submissions']) : array())
      );
  }

  function get_n_per_activity($args){
    $activity_arr = explode(",",str_replace(" ","",$args['activity_list']));
    $exclude_arr =  explode(",",str_replace(" ","",$args['exclude_list']));
    $sql_arr = array();
    foreach ($activity_arr as $activity){
      $sql_arr[] =
         "(SELECT\n"
        ."  `activity`.`systemID`,\n"
        ."  '".$activity."' `activity`,\n"
        ."  `activity`.`count_weighted_".$activity."` `count`,\n"
        ."  `activity`.`sourceID` `ID`,\n"
        ."  `activity`.`sourceType` `object_type`,\n"
        ."  COALESCE(`postings`.`content`, `pages`.`content`) `content`,\n"
        ."  COALESCE(`postings`.`content_summary`,'') `content_summary`,\n"
        ."  COALESCE(`postings`.`date`, `pages`.`history_created_date`) `date`,\n"
        ."  COALESCE(`postings`.`history_created_date`, `pages`.`history_created_date`) `history_created_date`,\n"
        ."  COALESCE(`postings`.`parentID`, `pages`.`parentID`) `parentID`,\n"
        ."  COALESCE(`postings`.`category`,'') `path`,\n"
        ."  COALESCE(`postings`.`path`, `pages`.`path`) `path`,\n"
        ."  COALESCE(`postings`.`comments_allow`,`pages`.`comments_allow`) `comments_allow`,\n"
        ."  COALESCE(`postings`.`comments_count`,`pages`.`comments_count`) `comments_count`,\n"
        ."  COALESCE(`postings`.`name`,`pages`.`page`) `name`,\n"
        ."  COALESCE(`postings`.`title`,`pages`.`title`) `title`,\n"
        ."  COALESCE(`postings`.`type`,'page') `type`,\n"
        ."  `system`.`TextEnglish` `systemTitle`,\n"
        ."  `system`.`URL` `systemURL`\n"
        ."FROM\n"
        ."  `activity`\n"
        ."LEFT JOIN `pages` ON\n"
        ."  `activity`.`sourceType`='Page' AND\n"
        ."  `activity`.`sourceID` = `pages`.`ID`\n"
        ."LEFT JOIN `postings` ON\n"
        ."  `activity`.`sourceID` = `postings`.`ID`\n"
        ."LEFT JOIN `system` ON\n"
        ."  COALESCE(`postings`.`systemID`,`pages`.`systemID`)=`system`.`ID`\n"
        ."WHERE\n"
        ."  COALESCE(`pages`.`systemID`,`postings`.`systemID`) = ".SYS_ID." AND\n"
        ."  COALESCE(`postings`.`date` < NOW() AND (`postings`.`date_end` ='0000-00-00' OR `postings`.`date_end` > NOW()),1) AND\n"
        ."  COALESCE(`postings`.`enabled`,1) AND\n"
        ."  COALESCE(`pages`.`permPUBLIC`,`postings`.`permPUBLIC`)=1\n"
        ."HAVING\n"
        .($args['activity_list'] ? "  `name` NOT IN('".implode("','",$exclude_arr)."') AND\n" : "")
        ."  `count`>0\n"
        ."ORDER BY\n"
        ."  ".($activity=='comments' ? "`comments_count` DESC" : "`count` DESC")."\n"
        ."LIMIT\n"
        ."  0,".$args['limit_per_activity'].")\n";
    }
    $sql = implode("UNION ",$sql_arr);
//    z($sql);die;
    return $this->get_records_for_sql($sql);
  }

  public function get_version(){
    return VERSION_ACTIVITY;
  }
}
?>