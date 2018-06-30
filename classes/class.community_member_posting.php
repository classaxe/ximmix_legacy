<?php
define('COMMUNITY_MEMBER_POSTING_VERSION','1.0.3');
/*
Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)
*/
/*
Version History:
  1.0.3 (2014-03-27)
    1) Community_Member_Posting::BL_shared_source_link() and
       Community_Member_Posting::BL_mini_shared_source_link() now use new
       community_url preperty when absolutising shared link hrefs

  (Older version history in class.community_member_posting.txt)
*/

class Community_Member_Posting extends Posting{
  protected function _get_records_get_sql($Obj){
    $sql =
       "SELECT\n"
      ."  (SELECT `system`.`textEnglish` FROM `system` WHERE `postings`.`systemID` = `system`.`ID`) `systemTitle`,\n"
      ."  (SELECT `system`.`URL` FROM `system` WHERE `postings`.`systemID` = `system`.`ID`) `systemURL`,\n"
      ."  (SELECT `community_member`.`title` FROM `community_member` WHERE `community_member`.`ID` = `postings`.`memberID`) `member_title`,\n"
      ."  (SELECT `community_member`.`name` FROM `community_member` WHERE `community_member`.`ID` = `postings`.`memberID`) `member_url`,\n"
      ."  `postings`.*\n"
      ."FROM\n"
      ."  `postings`\n"
      ."WHERE\n"
      ."  `postings`.`type` = '".$Obj->_get_type()."' AND\n"
      ."  (\n"
      ."    (`postings`.`memberID` = ".$Obj->memberID.") OR\n"
      ."    (\n"
      ."      `postings`.`permSHARED`=1 AND\n"
      ."      `postings`.`important`=1 AND\n"
      ."      `postings`.`memberID` IN(\n"
      ."        SELECT\n"
      ."          `cm1`.`ID`\n"
      ."        FROM\n"
      ."          `community_member` `cm1`\n"
      ."        WHERE\n"
      ."          `cm1`.`primary_communityID` IN(\n"
      ."            SELECT `cm2`.`primary_communityID` FROM `community_member` `cm2` WHERE `cm2`.`ID` = ".$Obj->memberID."\n"
      ."          )\n"
      ."      )\n"
      ."    )\n"
      .($Obj->partner_csv ? " OR (`memberID` IN(".$Obj->partner_csv.") AND `postings`.`permSHARED`=1)" : "")
      .") AND\n"
      .$Obj->_get_records_get_sql_filter_date()
      .($Obj->_get_records_args['category']!="*" ?    "  `postings`.`category` REGEXP \"".implode("|",explode(',',$Obj->_get_records_args['category']))."\" AND\n": "")
      .($Obj->_get_records_args['category_master'] ?  "  `postings`.`category` REGEXP \"".implode("|",explode(',',$Obj->_get_records_args['category_master']))."\" AND\n": "")
      ."  `postings`.`systemID` = '".$Obj->_get_systemID()."'\n";
//    z($sql);
    return $sql;
  }

  public function BL_mini_shared_source_link($Obj,$anchor=''){
    if ($Obj->record['memberID']==$Obj->memberID){
      return;
    }
    $href =
      "<a class='shared_source' style='color:#fff;text-decoration:none;' rel=\"external\""
     ." href=\"".$Obj->community_URL.'/'.$Obj->record['member_url'].$anchor."\""
     ." title=\"Shared by ".$Obj->record['member_title']." - click to visit\""
     .">";
    return
      "<div class='lce_shared css3'>"
     .$href
     ."<img src='".BASE_PATH."img/spacer' class='icons'"
     ." style='padding:0;margin:0 2px 0 0;height:8px;width:10px;background-position:-6144px 0px;'"
     ." alt=\"External content from ".$Obj->record['member_title']."\" />\n"
     ."</a> "
     .$href."<b>".$Obj->record['member_title']."</b></a></div>";
  }

  public function BL_shared_source_link($Obj,$anchor=''){
    if ($Obj->record['memberID']==$Obj->memberID){
      return;
    }
    $href =
      "<a class='shared_source' href=\"".$Obj->community_URL.'/'.$Obj->record['member_url'].$anchor."\""
     ." title=\"Shared by ".$Obj->record['member_title']." - click to visit\""
     ." rel=\"external\">";
    return
      $href
     ."<img src='".BASE_PATH."img/spacer' class='icons'"
     ." style='padding:0;margin:0 2px 0 0;height:13px;width:15px;background-position:-1173px 0px;'"
     ." alt=\"External content from ".$Obj->record['systemTitle']."\" />\n"
     ."</a> "
     .$href.$Obj->record['member_title']."</a>";
  }

  public function get_version(){
    return COMMUNITY_MEMBER_POSTING_VERSION;
  }
}
?>