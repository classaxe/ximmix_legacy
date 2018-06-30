<?php
define('VERSION_NAVSUITE','1.0.33');
/*
Version History:
  1.0.33 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.navsuite.txt)
*/
class Navsuite extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, buttonStyleID, childID_csv, name, parentButtonID, width, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  static $cache_buttons_array = array();
  static $cache_childID_array = array();

  function __construct($ID="") {
    parent::__construct("navsuite",$ID);
    $this->_set_assign_type('navsuite');
    $this->_set_object_name('Button Suite');
    $this->_set_message_associated('and contained Buttons have');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new suite name'
      )
    );
  }

  function ajax_set_seq(){
    $this->_set_ID(get_var('targetID'));
    $this->set_field('childID_csv',get_var('targetValue'));
    $this->clear_cache();
    $records = $this->get_buttons(true,true,false);
    $out = array();
    foreach($records as $r){
      $out[]=array($r['ID'],$r['img_checksum']);
    }
    print json_encode($out);
    die;
  }

  function clear_cache() {
    $navsuite_width =   $this->get_field('width');
    if ($navsuite_width==0){
      $max_width = $this->get_max_width();
      $this->set_field('width',$max_width,true);
    }
    $sql =
       "SELECT\n"
      ."  GROUP_CONCAT(`ID`)\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."WHERE\n"
      ."  `suiteID` = ".$this->_get_ID();
    $ID_arr =   explode(',',$this->get_field_for_sql($sql));
    $Obj_Navbutton = new Navbutton;
    foreach ($ID_arr as $ID){
      $Obj_Navbutton->_set_ID($ID);
      $Obj_Navbutton->clear_cache();
      $Obj_Navbutton->make_image();
    }
    if ($navsuite_width==0){
      $this->set_field('width',0,true);
    }
  }

  function copy($new_name=false,$new_systemID=false,$new_date=true) {
    $newID =    parent::copy($new_name,$new_systemID,$new_date);
    $buttons =  $this->get_buttons(true);
    $Obj =      new Navbutton;
    foreach ($buttons as $data) {
      $oldButtonID = $data['ID'];
      unset($data['ID']);
      unset($data['archive']);
      unset($data['archiveID']);
      if ($new_date){
        unset($data['history_created_by']);
        unset($data['history_created_date']);
        unset($data['history_created_IP']);
        unset($data['history_modified_by']);
        unset($data['history_modified_date']);
        unset($data['history_modified_IP']);
      }
      unset($data['childID']);
      if ($new_systemID) {
        $data['systemID'] = $new_systemID;
      }
      $newButtonID = $Obj->insert($data);
      $Obj->_set_ID($oldButtonID);
      $Obj->copy_group_assign($newButtonID);
    }
    return $newID;
  }

  function delete() {
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."WHERE\n"
      ." `suiteID` IN (".$this->_get_ID().")";
    if ($records = $this->get_records_for_sql($sql)) {
      foreach ($records as $record) {
        $Obj = new Navbutton($record['ID']);
        $Obj->delete();
      }
    }
    return parent::delete();
  }

  function draw_nav($nav="",$navsuiteID='root',$rootOrientation='notInitialisedYet',$depth=0) {
    global $page_vars, $page;
    $depth++;
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
    $isAdmin =          ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN);
    $path = trim(urldecode($_SERVER["REQUEST_URI"]),'/');
    if ($nav=="") {
      return "";
    }
    $suiteID = ($navsuiteID=='root' ? $page_vars['navsuite'.$nav.'ID'] : $navsuiteID);
    if ($suiteID=="1") {
      return "";
    }
    $this->_set_ID($suiteID);
    $buttons =      $this->get_buttons();
    if ($buttons===false){
      return "";     // There are no buttons so stop drawing
    }
    // Establish range for max and min button positions in bar:
    $visible =  false;
    $pos_min =  false;
    $pos_max =  false;
    $site_URL = ($_SERVER["SERVER_PORT"]==443 ? "https://" : "http://").$_SERVER["HTTP_HOST"];
    foreach ($buttons as $button) {
      if ($button['visible'] || $isAdmin) {
        $visible=true;
        if ($pos_min===false || $pos_min > $button['position']) {  $pos_min=$button['position']; }
        if ($pos_max===false || $pos_max < $button['position']) {  $pos_max=$button['position']; }
      }
    }
    if (!$visible) {
      return "";  // All buttons are invisible so stop drawing
    }
    $sql =
       "SELECT\n"
      ."  `ns`.`ID`,\n"
      ."  `ns`.`buttonStyleID`,\n"
      ."  `ns`.`name`,\n"
      ."  `ns`.`parentButtonID`,\n"
      ."  `ns`.`width`,\n"
      ."  `nst`.`name` AS `navstyle_name`,\n"
      ."  `nst`.`orientation`,\n"
      ."  `nst`.`subnavStyleID`,\n"
      ."  `nst`.`button_spacing`,\n"
      ."  `p_nst`.`subnavOffsetX`,\n"
      ."  `p_nst`.`subnavOffsetY`,\n"
      ."  `p_nst`.`orientation` AS `p_orientation`,\n"
      ."  `p_nb`.`img_width` AS `p_width`,\n"
      ."  `p_nb`.`img_height` AS `p_height`\n"
      ."FROM\n"
      ."  `navsuite` as `ns`\n"
      ."INNER JOIN `navstyle` AS `nst` ON\n"
      ."  `nst`.`ID` = `ns`.`buttonStyleID`\n"
      ."LEFT JOIN `navbuttons` AS `p_nb` ON\n"
      ."  `p_nb`.`ID` = `ns`.`parentButtonID`\n"
      ."LEFT JOIN `navsuite` AS `p_ns` ON\n"
      ."  `p_ns`.`ID` = `p_nb`.`suiteID`\n"
      ."LEFT JOIN `navstyle` AS `p_nst` ON\n"
      ."  `p_nst`.`ID` = `p_ns`.`buttonStyleID`\n"
      ."WHERE\n"
      ."  `ns`.`ID` = ".$this->_get_ID();
//    z($sql);
    $navsuite_record = $this->get_record_for_sql($sql);
    if ($navsuite_record===false) {
      return "";
    }
    if ($navsuiteID=='root') {
      $total_height =   0;
      $total_width =    0;
      foreach ($buttons as $button) {
        if ($button['visible'] || $isAdmin) {
          $bURL =     $button['URL'];
          if ($navsuite_record['orientation']=="|"){
            $total_height+=$button['img_height'];
            $total_height+=$navsuite_record['button_spacing'];
          }
          else {
            $total_width+=$button['img_width'];
            $total_width+=$navsuite_record['button_spacing'];
          }
        }
      }
      if ($navsuite_record['orientation']=="|"){
        $total_width+=$buttons[0]['img_width'];
      }
      else {
        $total_height+=$buttons[0]['img_height'];
      }
    }
    $isIE =	            strpos(getenv("HTTP_USER_AGENT"),"MSIE");
    $isIE5 =            stristr(strtolower(@$_SERVER['HTTP_USER_AGENT']),"msie 5.0");  // For offset change for vertical menu
    $ObjNB =            new Navbutton;
    $orientation =      $navsuite_record['orientation'];
    $p_orientation =    $navsuite_record['p_orientation'];
    $bStyleID =         $navsuite_record['buttonStyleID'];
    $bStyleText =       $navsuite_record['navstyle_name'];
    $bSubnavStyleID =   $navsuite_record['subnavStyleID'];
    switch($p_orientation) {
      case "":
        $bOffsetX = 0;
        $bOffsetY = 0;
      break;
      case "|":
        $bOffsetX = $navsuite_record['p_width']+$navsuite_record['subnavOffsetX'];
        $bOffsetY = $navsuite_record['subnavOffsetY'];
      break;
      default:
        $bOffsetX = $navsuite_record['subnavOffsetX'];
        $bOffsetY = $navsuite_record['p_height']+$navsuite_record['subnavOffsetY'];
      break;
    }
    if ($rootOrientation=='notInitialisedYet'){
      $rootOrientation = $orientation;
    }
    $out = "";
    $buttons_count = 0;
    $current_button = 0;
    foreach ($buttons as $button) {
      if ($button['visible'] || $isAdmin){
        $buttons_count++;
      }
    }
    switch ($orientation) {
      case '|':
        $out.=
           str_repeat('  ',$depth)
          ."<ul id='nav_".$navsuite_record['ID']."'"
          .($navsuiteID=='root' ?
              " class='vnavmenu'>\n"
            :
              " style=\"left:".($isIE5 && $bOffsetX==0 ? -16 : $bOffsetX)."px; top:".$bOffsetY."px;\""
             .">\n"
           );
        foreach ($buttons as $b) {
          if ($b['visible'] || $isAdmin) {
            $bID =      $b['ID'];
            $bSystemID =      $b['systemID'];
            $bCS =      $b['img_checksum'];
            $bPos =     $b['position'];
            $bURL =     $b['URL'];
            $bPopup =   $b['popup'];
            $bSuiteID = $b['suiteID'];
            $bText =    $b['text1'];
            $bTextSafe = str_replace(array("'","\r\n","\n"),array("&rsquo;"," "," "),sanitize('html',$bText));
            $sNameSafe = str_replace("'","&rsquo;",sanitize('html',$navsuite_record['name']));
            $active =   Navbutton::is_active($bURL,$site_URL);
            $childID =  $b['childID'];
            $canAddSubmenu = ($childID ? -1 : ($bSubnavStyleID==1 ? 0 : 1));
            $dropdown = $ObjNB->has_visible_children($bID);
            $bHeight =  $b['img_height'];
            $bWidth =   $b['img_width'];
            $bSrc =     "url(./img/button/".$bID."/".$b['img_checksum'].")";
            $bOffset =  ($dropdown ? '100%' : '0')." ".($active ? '0' : -2*$bHeight).'px';
            if (substr($bURL,0,8)=='./?page='){
              $bURL = BASE_PATH.substr($bURL,8);
            }
            $bURL = htmlentities(html_entity_decode($bURL));
            $out.=
               str_repeat('  ',$depth)
              ."  <li"
              ." id=\"btn_".$bID."\""
              .($b['visible'] ?
                 ""
               :
                 " class=\"invisible\" title=\"This button would normally be hidden,\nbut administrators can still see it.\""
               )
              .">"
              ."<a"
              .($active ? " class='nav_active'" : "")
              .($bPopup ? " rel='external'" : "")
              ." href=\"".$bURL."\""
              .(($isAdmin && $bSystemID==SYS_ID) || $isMASTERADMIN ?
                  " onmouseover=\""
                 ."CM_Navbutton_Over("
                 .$bID.","
                 .$bStyleID.","
                 .$canAddSubmenu.","
                 ."'".$sNameSafe."',"
                 ."'".sanitize('html',$bStyleText)."'"
                 .");"
                 ."\""
                :
                  ""
               )
              .">"
              ."<img"
              ." src=\"".BASE_PATH."img/spacer\""
              ." height=\"".$bHeight."\""
              ." width=\"".$bWidth."\""
              ." style='"
              .($navsuiteID=='root' && $navsuite_record['button_spacing'] && $current_button<$buttons_count ?
                 "margin-bottom:".$navsuite_record['button_spacing']."px;"
               :
                 ""
               )
              ."background:".$bSrc." no-repeat ".$bOffset."'"
              ." alt=\"".$bTextSafe."\"/>"
              ."</a>";
            $current_button++;
            if ($childID) {
              $out.=
                 "\n"
                .$this->draw_nav('submenu',$childID,$rootOrientation,$depth)
                .str_repeat('  ',$depth+1);
            }
            $out.=
              "</li>\n";
          }
        }
        $out.=
           str_repeat('  ',$depth)
          ."</ul>\n";
      break;
      case '---':
        // Can ONLY be root menu - only root is permitted to be horizontal
        $out.=
           str_repeat('  ',$depth)
          ."<ul id='nav_".$navsuite_record['ID']."'"
          .($navsuiteID=='root' ?
              " class='hnavmenu'>\n"
           :
              " style=\"left:".$bOffsetX."px; top:".$bOffsetY."px;\">\n"
           );
        foreach ($buttons as $b) {
          if ($b['visible'] || $isAdmin) {
            $bID =      $b['ID'];
            $bSystemID =      $b['systemID'];
            $bPos =     $b['position'];
            $bURL =     $b['URL'];
            $bPopup =   $b['popup'];
            $bSuiteID = $b['suiteID'];
            $bText =    $b['text1'];
            $bTextSafe = str_replace(array("'","\r\n","\n"),array("&rsquo;"," "," "),sanitize('html',$bText));
            $sNameSafe = str_replace("'","&rsquo;",sanitize('html',$navsuite_record['name']));
            $active =   Navbutton::is_active($bURL,$site_URL);
            $childID =  $b['childID'];
            $canAddSubmenu = ($childID ? -1 : ($bSubnavStyleID==1 ? 0 : 1));
            $dropdown = $ObjNB->has_visible_children($bID);
            $bHeight =  $b['img_height'];
            $bWidth =   $b['img_width'];
            $bSrc =     "url(./img/button/".$bID."/".$b['img_checksum'].")";
            $bOffset =  ($dropdown ? '100%' : '0')." ".($active ? '0' : -2*$bHeight).'px';
            if (substr($bURL,0,8)=='./?page='){
              $bURL = BASE_PATH.substr($bURL,8);
            }
            $bURL = htmlentities(html_entity_decode($bURL));
            $out.=
               str_repeat('  ',$depth)
              ."  <li"
              ." id=\"btn_".$bID."\""
              .($b['visible'] ?
                 ""
               :
                 " class=\"invisible\" title=\"This button would normally be hidden,\nbut administrators can still see it.\""
               )
              .">"
              ."<a"
              ." href=\"".$bURL."\""
              .($active ? " class='nav_active'" : "")
              .($bPopup ? " rel='external'" : "")
              .(($isAdmin && $bSystemID==SYS_ID) || $isMASTERADMIN ?
                  " onmouseover=\""
                 ."CM_Navbutton_Over("
                 .$bID.","
                 .$bStyleID.","
                 .$canAddSubmenu.","
                 ."'".$sNameSafe."',"
                 ."'".sanitize('html',$bStyleText)."'"
                 .");"
                 ."\""
                :
                  ""
               )
              .">"
              ."<img"
              ." src=\"".BASE_PATH."img/spacer\""
              ." height=\"".$bHeight."\""
              ." width=\"".$bWidth."\""
              ." style='"
              .($navsuiteID=='root' && $navsuite_record['button_spacing'] && $current_button<$buttons_count ?
                 "margin-right:".$navsuite_record['button_spacing']."px;"
               :
                 ""
               )
              ."background:".$bSrc." no-repeat ".$bOffset."'"
              ." alt=\"".$bTextSafe."\"/>"
              ."</a>";
            if ($childID) {
              $out.=
                 "\n"
                .$this->draw_nav('submenu',$childID,$rootOrientation,$depth)
                .str_repeat('  ',$depth+1);
            }
            $out.= "</li>\n";
          }
        }
        $out.=
           str_repeat('  ',$depth)
          ."</ul>\n";
        break;
      }
    return
      ($navsuiteID=='root' ?
         "<div id='nav_root_".$nav."' style='width:".$total_width."px;height:".$total_height."px;'>\n"
        .$out
        ."</div>\n"
      :
        $out
      );
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with Buttons";
    $extra_delete =
       "DELETE FROM `navbuttons`             WHERE `suiteID` IN (".$targetID.");\n"
      ."DELETE FROM `group_assign`           WHERE `assign_type` = 'navbuttons' AND `assignID` IN (SELECT `ID` FROM `navbuttons` WHERE `suiteID` IN(".$targetID."));\n";
    $Obj = new Backup;
    $extra_select =
       $Obj->db_export_sql_query("`navbuttons`            ","SELECT * FROM `navbuttons` WHERE `suiteID` IN(".$targetID.") ORDER BY `position`",$show_fields)
      .$Obj->db_export_sql_query("`group_assign`          ","SELECT * FROM `group_assign` WHERE `assign_type` = 'navbuttons' AND `assignID` IN(SELECT `ID` FROM `navbuttons` WHERE `suiteID` IN(".$targetID."));",$show_fields)."\n";
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_buttons($all=false,$no_cache=false,$SDMenuMode=false){
    $key =              $this->_get_ID()."_".($all?1:0);
    if (isset(Navsuite::$cache_buttons_array[$key]) && !$no_cache) {
      return Navsuite::$cache_buttons_array[$key];
    }
    // Need to get all fields as this is used for Navsuite::copy()
    $sql =
       "SELECT\n"
      ."  `nb`.*,\n"
      ."  (SELECT COALESCE(GROUP_CONCAT(`ns`.`ID`),0) FROM `navsuite` `ns` WHERE `nb`.`ID` = `ns`.`parentButtonID`) `childID`\n"
      ."FROM\n"
      ."  `navbuttons` `nb`\n"
      ."WHERE\n"
      ."  `suiteID` = ".$this->_get_ID()
      .($SDMenuMode ? "\nORDER BY `position`" : "");
    $records = $this->get_records_for_sql($sql);
    $buttons = array();
    $childID_csv =  $this->get_field('childID_csv');
    $csv_arr =  explode(',',$childID_csv);
    if ($SDMenuMode){
      $buttons = $records;
    }
    else {
      foreach ($csv_arr as $ID){
        foreach ($records as &$item){
          if ($item['ID']==$ID){
            $buttons[] = $item;
          }
        }
      }
      foreach ($records as &$item){
        if (!in_array($item['ID'],$csv_arr)){
          $buttons[] = $item;
        }
      }
    }
    Navsuite::$cache_buttons_array[$key] = $buttons;
    if (!$buttons){
      return false;
    }
    if ($all) {
      return $buttons;
    }
    foreach($buttons as &$button) {
      $button['visible'] = $this->is_visible($button);
    }
    Navsuite::$cache_buttons_array[$key] = $buttons;
    return $buttons;
  }

  function get_childID($parentButtonID,$no_cache=false){
    $key = $parentButtonID;
    if (isset(Navsuite::$cache_childID_array[$key]) && !$no_cache) {
      return Navsuite::$cache_childID_array[$key];
    }
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `navsuite`\n"
      ."WHERE\n"
      ."  `parentButtonID` = ".$parentButtonID;
    $result =   $this->get_field_for_sql($sql);
    Navsuite::$cache_childID_array[$key] = $result;
    return $result;
  }

  function get_js_preload() {
    global $page_vars, $print;
    if ($print=='1' || $print=='2') {
      return "";
    }
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
    $isAdmin =          ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN ? 1 : 0);
    $out = "";
    for ($i=1; $i<=3; $i++) {
      $suiteID =  $page_vars['navsuite'.$i.'ID'];
      if ($suiteID!=1 && $suiteID!="") {
        $Obj = new Navsuite($suiteID);
        if ($Obj->exists()) {
          $out.= "  nav_setup(".$i.",".($isAdmin ? 1 : 0).",'".BASE_PATH.trim($page_vars['path'],'/')."');\n";
        }
      }
    }
    return $out;
  }

  function get_max_width(){
    $sql =
       "SELECT\n"
      ."  `navsuite`.`systemID`,\n"
      ."  `navstyle`.`orientation`,\n"
      ."  `navstyle`.`templateFile`,\n"
      ."  `navstyle`.`text1_font_face`,\n"
      ."  `navstyle`.`text1_font_size`,\n"
      ."  `navstyle`.`text2_font_face`,\n"
      ."  `navstyle`.`text2_font_size`,\n"
      ."  `navsuite`.`width` `suite_width`,\n"
      ."  (SELECT MAX(`width`) FROM `navbuttons` WHERE `navbuttons`.`suiteID`=`navsuite`.`ID`) `max_fixed_width`,\n"
      ."  (SELECT GROUP_CONCAT(IF(`navstyle`.`text1_uppercase`,UCASE(`navbuttons`.`text1`),`navbuttons`.`text1`) SEPARATOR '\\n') FROM `navbuttons` WHERE `navbuttons`.`suiteID`=`navsuite`.`ID` AND `navbuttons`.`width`=0) `text1`,\n"
      ."  (SELECT GROUP_CONCAT(IF(`navstyle`.`text2_uppercase`,UCASE(`navbuttons`.`text2`),`navbuttons`.`text2`) SEPARATOR '\\n') FROM `navbuttons` WHERE `navbuttons`.`suiteID`=`navsuite`.`ID` AND `navbuttons`.`width`=0) `text2`\n"
      ."FROM\n"
      ."  `navsuite`\n"
      ."INNER JOIN `navstyle` ON\n"
      ."  `navstyle`.`ID` = `navsuite`.`buttonStyleID`\n"
      ."WHERE\n"
      ."  `navsuite`.`ID`=".$this->_get_ID();
//    z($sql);
    $record = $this->get_record_for_sql($sql);
    if ($record['orientation']=='---'){
      return $record['suite_width'];
    }
    $Obj_NBI =    new Navbutton_Image;
    $Obj_NBI->get_text_size(
      $record['text1_font_face'],
      $record['text1_font_size'],
      $record['text1'],
      $text1_width,
      $text1_height
    );
    $Obj_NBI->get_text_size(
      $record['text2_font_face'],
      $record['text2_font_size'],
      $record['text2'],
      $text2_width,
      $text2_height
    );
    $text_width = max($text1_width, $text2_width);
    $Obj_NBI->get_button_base_size($record, $template_width, $template_height);
    $auto_width =       $text_width+$template_width;
    return ($auto_width > $record['max_fixed_width'] ? $auto_width : $record['max_fixed_width']);
  }

  function get_next_seq() {
    $sql =
       "SELECT\n"
      ."  MAX(`position`)+1\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."WHERE\n"
      ."  `suiteID` = ".$this->_get_ID();
//    z($sql);
    $seq = $this->get_field_for_sql($sql);
    return (!$seq ? 1 : $seq);
  }

  function get_selector_sql($include_default=false) {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN){
      return
         "SELECT\n"
        ."  1 `value`,\n"
        ."  '(None)' `text`,\n"
        ."  'd0d0d0' `color_background`,\n"
        ."  1 `seq`\n"
        .($include_default ?
             "UNION SELECT\n"
            ."  0,\n"
            ."  '--- Use Default ---',\n"
            ."  'f0f0f0',\n"
            ."  0\n"
         : "")
        ."UNION SELECT\n"
        ."  `navsuite`.`ID`,\n"
        ."  CONCAT(\n"
        ."    IF(`navsuite`.`ID`=1,\n"
        ."      '  ',\n"
        ."      IF(`navsuite`.`systemID` = 1,\n"
        ."        '* ',\n"
        ."        CONCAT(UPPER(`system`.`textEnglish`),' | ')\n"
        ."      )\n"
        ."    ),\n"
        ."    `navsuite`.`name`\n"
        ."  ),\n"
        ."  IF(`navsuite`.`ID`=1,\n"
        ."    'd0d0d0',\n"
        ."    IF(`navsuite`.`systemID`=1,\n"
        ."      'e0e0ff',\n"
        ."      IF(`navsuite`.`systemID`=SYS_ID,\n"
        ."        'c0ffc0',\n"
        ."        'ffe0e0'\n"
        ."      )\n"
        ."    )\n"
        ."  ),\n"
        ."  IF(`navsuite`.`systemID`=1,2,3)\n"
        ."FROM\n"
        ."  `navsuite`\n"
        ."INNER JOIN `system` ON\n"
        ."  `navsuite`.`systemID` = `system`.`ID`\n"
        ."WHERE\n"
        ."  `navsuite`.`parentButtonID` = 1\n"
        ."ORDER BY\n"
        ."  `seq`,`text`";
    }
    return
       "SELECT\n"
      ."  1 `value`,\n"
      ."  '(None)' `text`,\n"
      ."  'd0d0d0' `color_background`,\n"
      ."  1 `seq`\n"
      .($include_default ?
           "UNION SELECT\n"
          ."  0,\n"
          ."  '--- Use Default ---',\n"
          ."  'f0f0f0',\n"
          ."  0\n"
       : "")
      ."UNION SELECT\n"
      ."  `navsuite`.`ID`,\n"
      ."  CONCAT(\n"
      ."    IF(`navsuite`.`ID`=1,\n"
      ."      '',\n"
      ."      IF(`navsuite`.`systemID` = 1,\n"
      ."        '* ',\n"
      ."        ''\n"
      ."      )\n"
      ."    ),\n"
      ."    `navsuite`.`name`\n"
      ."  ),\n"
      ."  IF(`navsuite`.`ID`=1,\n"
      ."    'd0d0d0',\n"
      ."    IF(`navsuite`.`systemID`=1,\n"
      ."      'e0e0ff',\n"
      ."      'c0ffc0'\n"
      ."    )\n"
      ."  ),\n"
      ."  IF(`navsuite`.`systemID`=1,2,3)\n"
      ."FROM\n"
      ."  `navsuite`\n"
      ."INNER JOIN `system` ON\n"
      ."  `navsuite`.`systemID` = `system`.`ID`\n"
      ."WHERE\n"
      ."  `systemID` IN(1,SYS_ID) AND\n"
      ."  `navsuite`.`parentButtonID` = 1\n"
      ."ORDER BY\n"
      ."  `seq`,`text`";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function has_loop() {
    return in_array('loop',$this->parents());
  }

  function on_delete() {
    global $action_parameters;
    $ID_arr = explode(",",$action_parameters['triggerID']);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $this->clear_cache();
    }
  }

  function on_pre_update() {
    global $action_parameters, $_POST, $submode, $msg;
    if($action_parameters['data']['bulk_update']){
      if (!isset($action_parameters['data']['parentButtonID_apply'])){
        return;
      }
    }
    $ID_arr = explode(",",$action_parameters['triggerID']);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $OldParentButtonID = $this->get_field('parentButtonID');
      $this->set_field('parentButtonID',$_POST['parentButtonID'],true,false);
      if ($this->has_loop()) {
        $this->set_field('parentButtonID',$OldParentButtonID,true,false);
        $submode = "";
        $msg = "The Parent Button you tried to choose would result in an infinate loop.";
      }
    }
  }

  function on_update() {
    global $action_parameters;
    $ID_arr = explode(",",$action_parameters['triggerID']);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $this->clear_cache();
    }
  }

  function parent() {
    $sql =
       "SELECT\n"
      ."  `parentButtonID`\n"
      ."FROM\n"
      ."  `navsuite`\n"
      ."WHERE\n"
      ."  `ID` = \"".$this->_get_ID()."\"";
    return $this->get_field_for_sql($sql);
  }

  function parents() {
    $out = array();
    $Obj = new Navsuite($this->_get_ID());
    $parent = $Obj->parent();
    while ($parent!=1) {
      $out[] = $parent;
      $Obj = new Navbutton($parent);
      $parent = $Obj->parent();
      if (in_array($parent,$out)) {
        $out[] = 'loop';
        break;
      }
    }
    return $out;
  }

  function get_tree($full=false,$navsuiteID='',$depth=0,$SDMenu_mode=false) {
    global $page,$page_vars;
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
    $isAdmin = ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN);
    $depth++;
    if ($navsuiteID=='') {
      $navsuiteID = $this->_get_ID();
    }
    $out =	            array();
    $links =	        array();
    $Obj_Navsuite =     new Navsuite($navsuiteID);
    $buttons =          $Obj_Navsuite->get_buttons(false,false,true);
    $visible =  false;
    $pos_min =  false;
    $pos_max =  false;
    foreach ($buttons as $button) {
      if ($button['visible']) {
        $visible=true;
        if ($pos_min===false || $pos_min > $button['position']) {  $pos_min=$button['position']; }
        if ($pos_max===false || $pos_max < $button['position']) {  $pos_max=$button['position']; }
      }
    }
    if (!$visible) {
      return "";  // All buttons are invisible so stop drawing
    }
    $sql =
       "SELECT\n"
      ."  `ns`.`ID`,\n"
      ."  `ns`.`buttonStyleID`,\n"
      ."  `ns`.`name`,\n"
      ."  `ns`.`parentButtonID`,\n"
      ."  `ns`.`width`,\n"
      ."  `nst`.`name` AS `navstyle_name`,\n"
      ."  `nst`.`orientation`,\n"
      ."  `nst`.`subnavStyleID`,\n"
      ."  `p_nst`.`subnavOffsetX`,\n"
      ."  `p_nst`.`subnavOffsetY`,\n"
      ."  `p_nst`.`orientation` AS `p_orientation`,\n"
      ."  `p_nb`.`img_width` AS `p_width`,\n"
      ."  `p_nb`.`img_height` AS `p_height`\n"
      ."FROM\n"
      ."  `navsuite` as `ns`\n"
      ."INNER JOIN `navstyle` AS `nst` ON\n"
      ."  `nst`.`ID` = `ns`.`buttonStyleID`\n"
      ."LEFT JOIN `navbuttons` AS `p_nb` ON\n"
      ."  `p_nb`.`ID` = `ns`.`parentButtonID`\n"
      ."LEFT JOIN `navsuite` AS `p_ns` ON\n"
      ."  `p_ns`.`ID` = `p_nb`.`suiteID`\n"
      ."LEFT JOIN `navstyle` AS `p_nst` ON\n"
      ."  `p_nst`.`ID` = `p_ns`.`buttonStyleID`\n"
      ."WHERE\n"
      ."  `ns`.`ID` = ".$navsuiteID;
//    z($sql);
    $navsuite_record = $this->get_record_for_sql($sql);
    if ($navsuite_record===false) {
      return "";
    }
    if ($SDMenu_mode && $depth>2) {
      if ($isAdmin) {
        return "  <li>\n<a title=\"ERROR - the previous entry has a submenu attached. This type of menu doesn't support that.\">[Error above ^]</a>\n</li>";
      }
      return "";
    }
    $bStyleID =         $navsuite_record['buttonStyleID'];
    $bStyleText =       $navsuite_record['navstyle_name'];
    if (!$buttons===false){
//      y($buttons);die;
      foreach ($buttons as $button) {
        $Obj = new Navbutton($button['ID']);
        if ($button['visible']) {
          $bID =        $button['ID'];
          $bSystemID =  $button['systemID'];
          $bText =      $button['text1'];
          $bTextSafe = str_replace(array("'","\\n"),array("&rsquo;","\n"),sanitize('html',$bText));
          $bPos =       $button['position'];
          $bSuiteID =   $button['suiteID'];
          $sNameSafe = str_replace("'","&rsquo;",sanitize('html',$navsuite_record['name']));
          $bSubnavStyleID =   $navsuite_record['subnavStyleID'];
          $bPopup =     $button['popup'];
          $childID =    $button['childID'];
          $bURL =       $button['URL'];
          $bURL =       htmlentities(html_entity_decode($bURL));
          if (substr($bURL,0,8)=='./?page='){
            $bURL = BASE_PATH.substr($bURL,8);
          }
          if ($SDMenu_mode && $bURL && $depth==1){
            $bURL = "";
            if (($isAdmin && $bSystemID==SYS_ID) || $isMASTERADMIN) {
              $bText = "[Error] ".$bText;
              $bTextSafe="ERROR - This item is not linkable.\nPlease right click to edit this button and remove the URL.";
            }
          }
          if ($SDMenu_mode && !$bURL && $depth>1){
            $bURL = "./";
            if (($isAdmin && $bSystemID==SYS_ID) || $isMASTERADMIN) {
              $bText = "[Error] ".$bText;
              $bTextSafe="Error - This item MUST have a URL.\\nPlease right click to edit this button and provide a URL.";
            }
          }
          $CM =
             (($isAdmin && $bSystemID==SYS_ID) || $isMASTERADMIN ?
               " onmouseout=\"_CM.type=''\" onmouseover=\""
              ."CM_SDMenu_Over("
              .$bID.","
              ."'".str_replace("\n"," ",$bTextSafe)."',"
              .($childID ? 1 : 0).","
              .($childID ? '-1' : ($bSubnavStyleID==1 ? 0 : $bSubnavStyleID)).","
              .$bSuiteID.","
              ."'".$sNameSafe."',"
              .$bStyleID.","
              ."'".sanitize('html',$bStyleText)."'"
              .");\""
             : "");
          $links[] =
             str_repeat("  ",$depth)
            ."<li>"
            .($bURL ?
                 "<a id=\"btn_".$bID."\""
                ." href=\"".$bURL."\""
                .($bPopup ? " rel='external'" : "")
               : "<span id=\"btn_".$bID."\""
             )
            .$CM
            .($SDMenu_mode ? "" : " title=\"".$bTextSafe."\"")
            .">"
            .$bText
            .($bPopup ? HTML::draw_icon('external') : "")
            .($bURL ?  "</a>" : "</span>")
            .($childID ?
                "\n".$this->get_tree(false,$childID,$depth,$SDMenu_mode).str_repeat("  ",$depth)."</li>\n"
              :
                "</li>\n"
            );
        }
      }
    }
    return
       str_repeat("  ",$depth-1)."<ul>\n"
      .implode("",$links)
      .str_repeat("  ",$depth-1)."</ul>\n";
  }


 function test_loop($parentButtonID) {
    if ($this->_get_ID()=="") {
      return false;
    }
    $OldParentButtonID = $this->get_field('parentButtonID');
    $this->set_field('parentButtonID',$_POST['parentButtonID'],true,false);
    if ($this->has_loop()) {
      $this->set_field('parentButtonID',$OldParentButtonID,true,false);
      return true;
    }
    $this->set_field('parentButtonID',$OldParentButtonID,true,false);
    return false;  // no loop
  }

  public function get_version(){
    return VERSION_NAVSUITE;
  }
}
?>