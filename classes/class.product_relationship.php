<?php
define('VERSION_PRODUCT_RELATIONSHIP','1.0.4');
/*
Version History:
  1.0.4 (2011-10-19)
    1) Change to Product_Relationship::draw_combo_product_relationship() to
       reference `effective_date_start`
  1.0.3 (2011-09-08)
    1) Bug fix for Product_Relationship::draw_combo_product_relationship() on
       Gallery Image choices
  1.0.2 (2011-09-08)
    1) Added Product_Relationship::draw_combo_product_relationship()
  1.0.1 (2011-09-07)
    1) Bug fix for cloning Product Relationship without rename prompt
  1.0.0 (2011-09-07)
    1) Initial release
*/
class Product_Relationship extends Record {

  function __construct($ID=""){
    parent::__construct("product_relationship",$ID);
    $this->_set_object_name("Product Relationship");
    $this->set_edit_params(
      array(
        'report_rename' =>          false,
        'report_rename_label' =>    ''
      )
    );
  }

  function draw_combo_product_relationship($related_object,$related_objectID,$formFieldSpecial,$formContentWidth,$report_name) {
    if (!$formFieldSpecial){
      $type_arr = array();
      $filter = '';
    }
    else{
      $type_arr =   explode(',',str_replace(' ','',$formFieldSpecial));
      $filter_arr = array();
      foreach($type_arr as $type){
        $filter_arr[] = "'".trim($type)."'";
      }
      $filter = "  `value` IN(".implode(',',$filter_arr).")";
    }
    $Obj_Listtype =         new Listtype;
    $sql_related_object =   $Obj_Listtype->get_sql_options('lst_product_relationship_object','`text`', $filter);
    $isMASTERADMIN =	    get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN) {
      $sql_events =
         "SELECT\n"
        ."  CONCAT(IF(`systemID`=1,'*',`system`.`textEnglish`),' | ',`postings`.`effective_date_start`,': ',`postings`.`title`) AS `text`,\n"
        ."  `postings`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `postings`.`systemID`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'event'\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_ga =
         "SELECT\n"
        ."  CONCAT(IF(`systemID`=1,'*',`system`.`textEnglish`),' | ',REPLACE(REPLACE(`postings`.`path`,'//',''),'/',' / ')) AS `text`,\n"
        ."  `postings`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `postings`.`systemID`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'gallery-album'\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_gi =
         "SELECT\n"
        ."  CONCAT(IF(`systemID`=1,'*',`system`.`textEnglish`),' | ',REPLACE(REPLACE(`postings`.`path`,'//',''),'/',' / ')) AS `text`,\n"
        ."  `postings`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `postings`.`systemID`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'gallery-image'\n"
        ."ORDER BY\n"
        ."  `text`";
    }
    else {
      $sql_events =
         "SELECT\n"
        ."  CONCAT(IF(`systemID`=1,'* ',''),`effective_date_start`,': ',`title`) AS `text`,\n"
        ."  `ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'event' AND\n"
        ."  `systemID` = ".SYS_ID."\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_ga =
         "SELECT\n"
        ."  CONCAT(IF(`systemID`=1,'* ',''),REPLACE(REPLACE(`postings`.`path`,'//',''),'/',' / ')) AS `text`,\n"
        ."  `postings`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `postings`.`systemID`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'gallery-album'\n"
        ."ORDER BY\n"
        ."  `text`";
      $sql_gi =
         "SELECT\n"
        ."  CONCAT(IF(`systemID`=1,'* ',''),REPLACE(REPLACE(`postings`.`path`,'//',''),'/',' / ')) AS `text`,\n"
        ."  `postings`.`ID` AS `value`,\n"
        ."  IF(`systemID`=1,'e0e0ff',IF(`systemID`=".SYS_ID.",'e0ffe0','ffe0e0')) AS `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."INNER JOIN `system` ON\n"
        ."  `system`.`ID` = `postings`.`systemID`\n"
        ."WHERE\n"
        ."  `postings`.`type` = 'gallery-image'\n"
        ."ORDER BY\n"
        ."  `text`";
    }
    $html =
       "<table class='minimal'>\n"
      ."  <tr>\n"
      ."    <td style='width:164px;'>".Report_Column::draw_label('Object Type','Determines the kind of item involved in this Product Relationship.')."</td>\n"
      ."    <td>"
      .draw_form_field('related_object',$related_object,"selector",$formContentWidth,$sql_related_object,0, "onchange=\"set_product_relationship_options(geid('related_object'),geid('related_objectID'));\"")
      .draw_form_field('temp_id',$related_objectID,'hidden')
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>".Report_Column::draw_label('Object','Determines the specific item involved in this Product Relationship.')."</td>\n"
      ."    <td>"
      ."<select id='related_objectID' name='related_objectID' class='formField' style='width:".($formContentWidth+4)."px;'>\n"
      ."  <option value='".$related_objectID."'>Loading available options...</option>\n"
      ."</select></td>\n"
      ."  </tr>\n"
      ."</table>";
    $js =
       "\n"
      ."// Product Relationship Object entries:\n"
      ."  var pr_destID = {};\n";
    if (count($type_arr)>1){
      $js.=
         "  pr_destID.none = [];\n"
        ."  pr_destID.none[0] = ['Please choose an object type','none','c0c0c0'];\n";
    }
    if (count($type_arr)==0 || in_array('event',$type_arr)){
      $result = $this->get_records_for_sql($sql_events);
      $js.=
         "  pr_destID.event = [];\n"
        ."  pr_destID.event[0] = ['EVENTS',''];\n";
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $js.=
           "  pr_destID.event[".($i+1)."] = "
          ."[\"".str_replace("\"","'",$row['text'])."\",".$row['value'].",\"#".$row['color_background']."\"];\n";
      }
    }
    if (count($type_arr)==0 || in_array('gallery-album',$type_arr)){
      $result = $this->get_records_for_sql($sql_ga);
      $js.=
         "  pr_destID.gallery_album = [];\n"
        ."  pr_destID.gallery_album[0] = ['Gallery Albums',''];\n";
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $js.=
           "  pr_destID.gallery_album[".($i+1)."] = "
          ."[\"".str_replace("\"","'",$row['text'])."\",".$row['value'].",\"#".$row['color_background']."\"];\n";
      }
    }
    if (count($type_arr)==0 || in_array('gallery-image',$type_arr)){
      $result = $this->get_records_for_sql($sql_gi);
      $js.=
         "  pr_destID.gallery_image = [];\n"
        ."  pr_destID.gallery_image[0] = ['Gallery Images',''];\n";
      for ($i=0; $i<count($result); $i++) {
        $row = $result[$i];
        $js.=
           "  pr_destID.gallery_image[".($i+1)."] = "
          ."[\"".str_replace("\"","'",$row['text'])."\",".$row['value'].",\"#".$row['color_background']."\"];\n";
      }
    }
    Page::push_content('javascript',$js);
    Page::push_content('javascript_onload','  set_product_relationship_options();');
    return $html;
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip);
  }

  public function get_version(){
    return VERSION_PRODUCT_RELATIONSHIP;
  }
}
?>