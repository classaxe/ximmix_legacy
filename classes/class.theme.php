<?php
define('VERSION_THEME','1.0.8');
/*
Version History:
  1.0.8 (2014-02-17)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.theme.txt)
*/
class Theme extends Record{
  const fields = 'ID, archive, archiveID, deleted, systemID, name, accent_1, accent_2, accent_3, accent_4, accent_5, banner, layoutID, navsuite1ID, navsuite2ID, navsuite3ID, style, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("theme",$ID);
    $this->_set_object_name('Theme');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  function draw_accent($num){
    global $page_vars;
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $canEdit =          ($isMASTERADMIN || ($isSYSADMIN && $page_vars['theme']['systemID']==SYS_ID));
    if ($canEdit) {
      $popup =          get_popup_size('theme');
      $tip =            "Right-click to edit";
    }
    return
       "<div id=\"theme_".$num."\""
       .($canEdit ?
           " title='".$tip."' "
          ."onmouseover=\""
          ."if(!CM_visible('CM_theme_accent')) {"
          ."window.status='".$tip."';"
          ."this.style.backgroundColor='"
          .($page_vars['theme']['systemID']==SYS_ID ? '#80ff80' : '#ffe0e0')
          ."';"
          ."_CM.type='theme_accent';"
          ."_CM.ID='".$page_vars['theme']['ID']."';"
          ."_CM_text[0]='&quot;".str_replace(array("'","\""),'',$page_vars['theme']['name'])."&quot;';"
          ."_CM_ID[1]='".$num."';"
          ."}\" "
          ."onmouseout=\""
          ."if(!CM_visible('CM_event')){"
          ."this.style.backgroundColor=''"
          ."};"
          ."window.status='';"
          ."_CM.type='';\">"
        : ">"
       )
       .($canEdit && $page_vars['theme']['accent_'.$num]=='' ?
           "<span style='background-color:#ffffd0;'>Theme Accent ".$num." for <b>\"".$page_vars['theme']['name']."\"</b> is empty... <i>right-click if you want to edit it</i></span>"
         : $page_vars['theme']['accent_'.$num]
        )
       ."</div>";
  }

  function draw_banner(){
    global $page_vars;
    $out = $page_vars['theme']['banner'];
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $canEdit =          ($isMASTERADMIN || ($isSYSADMIN && $page_vars['theme']['systemID']==SYS_ID));
    if ($canEdit) {
      $popup =          get_popup_size('theme');
      $tip =            "Right-click to edit";
    }
    return
       "<div id=\"theme_banner\""
       .($canEdit ?
           " title='".$tip."' "
          ."onmouseover=\""
          ."if(!CM_visible('CM_theme_banner')) {"
          ."window.status='".$tip."';"
          ."this.style.backgroundColor='"
          .($page_vars['theme']['systemID']==SYS_ID ? '#80ff80' : '#ffe0e0')
          ."';"
          ."_CM.type='theme_banner';"
          ."_CM.ID='".$page_vars['theme']['ID']."';"
          ."_CM_text[0]='&quot;".str_replace(array("'","\""),'',$page_vars['theme']['name'])."&quot;';"
          ."}\" "
          ."onmouseout=\""
          ."if(!CM_visible('CM_event')){"
          ."this.style.backgroundColor=''"
          ."};"
          ."window.status='';"
          ."_CM.type='';\">"
        : ">"
       )
       .($canEdit && $out=='' ?
           "<span style='background-color:#ffffd0;'>Theme Banner for <b>\"".$page_vars['theme']['name']."\"</b> is empty... <i>right-click if you want to to edit it.</i></span>"
         : $out
        )
       ."</div>";
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function get_selector_sql($include_system_default=true) {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN) {
      return
         "SELECT\n"
        .($include_system_default ?
           "  1 AS `value`,\n"
          ."  '--- Use Default ---' AS `text`,\n"
          ."  'f0f0f0' AS `color_background`,\n"
          ."  1 AS `seq`\n"
          ."UNION SELECT\n"
         : "")
        ."  `theme`.`ID` AS `value`,\n"
        ."  CONCAT(\n"
        ."    IF(`theme`.`systemID`=1,\n"
        ."      '* ',\n"
        ."      CONCAT(\n"
        ."        UPPER(`system`.`textEnglish`),\n"
        ."        ' | '\n"
        ."      )\n"
        ."    ),\n"
        ."    `theme`.`name`\n"
        ."  ) AS `text`,\n"
        ."  IF(`theme`.`systemID`=1,\n"
        ."    'e0e0ff',\n"
        ."    IF(`theme`.`systemID`=SYS_ID,\n"
        ."      'c0ffc0',\n"
        ."    'ffe0e0')\n"
        ."  ) AS `color_background`,\n"
        ."  IF(`theme`.`systemID`=1, 2, 3) AS `seq`\n"
        ."FROM\n"
        ."  `theme`\n"
        ."INNER JOIN `system` ON\n"
        ."  `theme`.`systemID` = `system`.`ID`\n"
        ."ORDER BY\n"
        ."  `seq`,`text`";
    }
    return
       "SELECT\n"
      .($include_system_default ?
         "  1 AS `value`,\n"
        ."  '--- Use Default ---' AS `text`,\n"
        ."  'f0f0f0' AS `color_background`,\n"
        ."  1 AS `seq`\n"
        ."UNION SELECT\n"
       : "")
      ."  `theme`.`ID` AS `value`,\n"
      ."  IF(`theme`.`systemID`=1,\n"
      ."    CONCAT('* ',`theme`.`name`),\n"
      ."    `theme`.`name`\n"
      ."  ) AS `text`,\n"
      ."  IF(`theme`.`systemID`=1,\n"
      ."    'e0e0ff',\n"
      ."    IF(`theme`.`systemID`=SYS_ID,\n"
      ."      'c0ffc0',\n"
      ."    'ffe0e0')\n"
      ."  ) AS `color_background`,\n"
      ."  IF(`theme`.`systemID`=1, 2, 3) AS `seq`\n"
      ."FROM\n"
      ."  `theme`\n"
      ."WHERE\n"
      ."  `theme`.`systemID` IN(1,SYS_ID)\n"
      ."ORDER BY\n"
      ."  `seq`,`text`";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_THEME;
  }
}
?>