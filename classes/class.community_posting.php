<?php
define('COMMUNITY_POSTING_VERSION','1.0.4');
/* Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)

/*
Version History:
  1.0.4 (2014-01-21)
    1) Changes to Community_Posting::_get_records_get_sql() to include items
       assigned to this community and not to any specific community member
       to allow generic community postings (e.g. Prayer Breakfasts) even  if there
       isn't a ministerial to represent them
    2) Changes to
         Community_Posting::BL_shared_source_link()
         Community_Posting::BL_mini_shared_source_link
       to display Community Name if posting isn't assigned to a specific member

  (Older version history in class.community_posting.txt)
*/


class Community_Posting extends Posting{
  public function _get_records_get_sql($Obj){
    return
       "SELECT\n"
      ."  `system`.`textEnglish` `systemTitle`,\n"
      ."  `system`.`URL` `systemURL`,\n"
      ."  COALESCE((SELECT `cm`.`title`          FROM `community_member` `cm` WHERE `cm`.`ID` = `postings`.`memberID`),'') `member_title`,\n"
      ."  COALESCE((SELECT `cm`.`name`           FROM `community_member` `cm` WHERE `cm`.`ID` = `postings`.`memberID`),'') `member_url`,\n"
      ."  COALESCE((SELECT `cm`.`shortform_name` FROM `community_member` `cm` WHERE `cm`.`ID` = `postings`.`memberID`),'') `member_shortform`,\n"
      ."  `postings`.*\n"
      ."FROM\n"
      ."  `postings`\n"
      ."INNER JOIN `system` ON\n"
      ."  `postings`.`systemID` = `system`.`ID`\n"
      .($Obj->_show_latest_for_each_member ?
           "INNER JOIN (SELECT\n"
          ."    `memberID` `m_ID`,\n"
          ."     MAX(`date`) `m_date`\n"
          ."  FROM\n"
          ."    `postings`\n"
          ."  WHERE\n"
          .$Obj->_get_records_get_sql_filter_date()
          .($Obj->_get_records_args['category']!="*" ?    "  `postings`.`category` REGEXP \"".implode("|",explode(',',$Obj->_get_records_args['category']))."\" AND\n": "")
          .($Obj->_get_records_args['category_master'] ?  "  `postings`.`category` REGEXP \"".implode("|",explode(',',$Obj->_get_records_args['category_master']))."\" AND\n": "")
          ."    `type`='".$Obj->_get_type()."' AND\n"
          ."    `permSHARED`=1 AND\n"
          ."    (\n"
          ."      `memberID` IN(SELECT `memberID` FROM `community_membership` WHERE `communityID` = ".$Obj->community_record['ID'].") OR\n"
          ."      `communityID` = ".$Obj->community_record['ID']."\n"
          ."    )\n"
          ."  GROUP BY\n"
          ."    `m_ID`\n"
          .") `m` ON\n"
          ."  `m_ID` = `memberID` AND\n"
          ."  `m_date` = `date`\n"
        : ""
       )
      ."WHERE\n"
      ."  `postings`.`type` = '".$Obj->_get_type()."' AND\n"
      ."  `postings`.`permSHARED` = 1 AND\n"
      .$Obj->_get_records_get_sql_filter_date()
      .($Obj->_get_records_args['category']!="*" ?    "  `postings`.`category` REGEXP \"".implode("|",explode(',',$Obj->_get_records_args['category']))."\" AND\n": "")
      .($Obj->_get_records_args['category_master'] ?  "  `postings`.`category` REGEXP \"".implode("|",explode(',',$Obj->_get_records_args['category_master']))."\" AND\n": "")
      ."  `postings`.`systemID` = '".$Obj->_get_systemID()."' AND\n"
      ."  (\n"
      ."    `postings`.`memberID` IN(SELECT `memberID` FROM `community_membership` WHERE `communityID` = ".$Obj->community_record['ID'].") OR\n"
      ."    `postings`.`memberID`=0 AND `postings`.`communityID`= ".$Obj->community_record['ID']."\n"
      ."   )";
  }

  protected function BL_category(){
    if (!isset($this->_cp['category_show']) || $this->_cp['category_show']!='1'){
      return '';
    }
    if ($this->record['category']==''){
      return '';
    }
    $Obj_Category = new Category;
    $categories = array();
    $category_csv = explode(",",$this->record['category']);
    foreach ($category_csv as $cat){
      $categories[$cat] = $cat;
    }
    $categories =
      $Obj_Category->get_labels_for_values(
        "'".implode("','",array_keys($categories))."'",
        "'Community Posting Category'");
    $category_text = implode(", ",$categories);
    return $category_text;
  }

  public function BL_shared_source_link($Obj,$anchor){
    $URL =          $Obj->community_record['URL'].'/'.$Obj->record['member_url'].$anchor;
    $title =        $Obj->record['member_title'] ?     $Obj->record['member_title'] :     "The Community of ".$Obj->community_record['title'];
    $shortform =    $Obj->record['member_shortform'] ? $Obj->record['member_shortform'] : "Community of ".$Obj->community_record['title'];
    $href =
      "<a class='shared_source' rel=\"external\""
     ." href=\"".$URL."\""
     ." title=\"Shared by ".str_replace('& ','&amp; ',$title)." - click to visit\""
     .">";
    return
      $href
     ."<img src='".BASE_PATH."img/spacer' class='icons'"
     ." style='padding:0;margin:0 2px 0 0;height:13px;width:15px;background-position:-1173px 0px;'"
     ." alt=\"External content from ".str_replace('& ','&amp; ',$Obj->record['member_title'])."\" />\n"
     ."</a> "
     .$href
     ."<b>".str_replace('& ','&amp; ',$shortform)."</b></a>";
  }

  public function BL_mini_shared_source_link($Obj,$anchor){
    $URL =          $Obj->community_record['URL'].'/'.$Obj->record['member_url'].$anchor;
    $title =        $Obj->record['member_title'] ?     $Obj->record['member_title'] :     "The Community of ".$Obj->community_record['title'];
    $shortform =    $Obj->record['member_shortform'] ? $Obj->record['member_shortform'] : "Community of ".$Obj->community_record['title'];
    $href =
      "<a class='shared_source' href=\"".$URL."\""
     ." title=\"Shared by ".str_replace('& ','&amp; ',$title)." - click to visit\""
     .">";
    return
      "<div style='padding:2px'>"
     .$href
     ."<img src='".BASE_PATH."img/spacer' class='icons'"
     ." style='padding:0;margin:0 2px 0 0;height:8px;width:10px;background-position:-6144px 0px;'"
     ." alt=\"External content from ".str_replace('& ','&amp; ',$Obj->record['member_title'])."\" />\n"
     ."</a> "
     .$href
     .str_replace('& ','&amp; ',$shortform)."</a></div>";
  }
  public function get_version(){
    return COMMUNITY_POSTING_VERSION;
  }
}

?>