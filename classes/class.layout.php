<?php
define('VERSION_LAYOUT','1.0.28');
/*
Version History:
  1.0.28 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.layout.txt)
*/

class Layout extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, name, colour1, colour2, colour3, colour4, component_parameters, content, head_include, language, languageOptionParentID, navsuite1ID, navsuite2ID, navsuite3ID, style, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  function __construct($ID="") {
    parent::__construct("layout",$ID);
    $this->_set_object_name('Layout');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function get_css_checksum($ID){
    if (!$ID){
      return "";
    }
    $sql =
       "SELECT\n"
      ."  CONCAT(`colour1`,`colour2`,`colour3`,`colour4`,`style`) AS `code`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `ID`=".$ID;
    $code = $this->get_field_for_sql($sql);
    return dechex(crc32($code));
  }

  function get_language_options(){
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `language`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `languageOptionParentID` IN(".$this->_get_ID().")\n"
      ."ORDER BY\n"
      ."  `language`";
    return $this->get_records_for_sql($sql);
  }

  public static function get_selector_sql($include_system_default=true){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN) {
      return
         "SELECT\n"
        ."  `layout`.`ID` AS `value`,\n"
        ."  CONCAT(IF(`layout`.`ID`=1,' ',CONCAT(IF(`systemID` = 1,'* ',CONCAT(UPPER(`system`.`textEnglish`),' | ')))),`layout`.`name`) AS `text`,\n"
        ."  IF(`layout`.`ID`=1,'f0f0f0',IF(`layout`.`systemID`=1,'e0e0ff',IF(`layout`.`systemID`=".SYS_ID.",'c0ffc0','ffe0e0'))) AS `color_background`\n"
        ."FROM\n"
        ."  `layout`\n"
        ."INNER JOIN `system` ON\n"
        ."  `layout`.`systemID` = `system`.`ID`\n"
        ."WHERE\n"
        .($include_system_default ? "" : "  `layout`.`ID`!=1 AND\n")
        ."  `layout`.`languageOptionParentID`=1 AND\n"
        ."  1\n"
        ."ORDER BY\n"
        ."  `layout`.`systemID`!=1,`text`";
    }
    return
       "SELECT\n"
      ."  CONCAT(IF(`systemID`=1,IF(`layout`.`ID`=1,' ','* '),' '),`layout`.`name`) AS `text`,\n"
      ."  `layout`.`ID` AS `value`,\n"
      ."  IF(`ID`=1,'f0f0f0',IF(`systemID`=1,'e0e0ff','c0ffc0')) AS `color_background`\n"
      ."FROM\n"
      ."  `layout`\n"
      ."WHERE\n"
      .($include_system_default ? "" : "  `layout`.`ID`!=1 AND\n")
      ."  `layout`.`languageOptionParentID`=1 AND\n"
      ."  `systemID` IN(1,SYS_ID)\n"
      ."ORDER BY\n"
      ."  `text`";

  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function handle_report_delete(&$msg) {
    $targetID = $this->_get_ID();
    $is_are =   (count(explode(",",$targetID))>1 ? 'are' : 'is');
    $usage =    $this->usage();
    if ($usage['internal']==0 && $usage['system']==0 && $usage['page']==0){
      return parent::try_delete($msg);
    }
    $errors = array();
    if ($usage['internal']>0){
      $errors[] =   ($usage['internal']==1 ? " specified as an internal layout" : " specified as internal layouts");
    }
    if ($usage['system']>0){
      $errors[] =   " specified by ".$usage['system']." system".($usage['system']==1 ? "" : "s")." as default layout";
    }
    if ($usage['page']>0){
      $errors[] =   " required for ".$usage['page']." page".($usage['page']==1 ? "" : "s");
    }
    $msg =
      status_message(
        2,true,$this->_get_object_name(),'',$is_are." ".implode(', ',$errors)." - deletion has therefore been cancelled.",$targetID
      );
    return false;
  }

  function prepare() {
    global $page_vars;
    $content = $page_vars['layout']['content'];
    $Obj_Page = new Page;
    if ($include = $page_vars['layout']['head_include']){
      $Obj_Page->push_content('head_include',$include);
    }
    if ($personID = get_userID()){
      $Obj_Person = new Person($personID);
      $Obj_Person->load_profile_fields();
    }
    $Obj_Page->prepare_html_head();
    $Obj_Page->push_content('body',convert_safe_to_php($content));
    $Obj_Page->prepare_html_foot();
  }

  public static function render() {
    mem('Pre Render');
    $content =
       Page::pop_content('html_top')
      .Page::pop_content('head_top')
      .Page::pop_content('head_include')
      .Page::pop_content('style_include')
      ."<style type=\"text/css\">\n"
      ."/*<![CDATA[*/\r\n"
      .Page::pop_content('style')
      ."/*]]>*/\n"
      ."</style>\n\n"
      .Page::pop_content('javascript_top')
      ."<script type=\"text/javascript\">\n"
      ."//<![CDATA[\n"
      .Page::pop_content('javascript')
      .(Page::$content['javascript_top'] ?
          "\nfunction _onload() {\n"
         .Page::pop_content('javascript_onload')
         .Page::pop_content('javascript_onload_bottom')
         ."}\n"
        : ""
       )
      .(Page::$content['javascript_top'] ?
          "\nfunction _onunload() {\n"
         .Page::pop_content('javascript_onunload')
         ."}\n"
        : ""
       )
      ."\n"
      .Page::pop_content('javascript_bottom')
      ."//]]>\n"
      ."</script>\n"
      .Page::pop_content('head_bottom')
      .Page::pop_content('body_top')
      .Page::pop_content('body')
      .Page::pop_content('body_bottom')
      .Page::pop_content('html_bottom')
      ;
//    print $content;return;
    $content =
      str_replace(
        array(
          '<p><div',
          '</div></p>',
          'target="_blank"',
          "target='_blank'"
        ),
        array(
          '<div',
          '</div>',
          'rel="external"',
          'rel="external"'
        ),
        $content
      );
    if (substr($_SERVER["HTTP_HOST"],0,8)!='desktop.' && substr($_SERVER["HTTP_HOST"],0,7)!='laptop.') {
//      @header("Cache-Control: private, no-cache, no-cache=\"Set-Cookie\", proxy-revalidate");
    }
    @header("Pragma: no-cache");
    print
      preg_replace(
        "#(action='|action=\"|href='|href=\"|src='|src=\"|background='|background=\"|url\()(\./)#",
        "$1".BASE_PATH,
        $content
      );
    mem('Post Render');
    if (DEBUG_MEMORY || get_var('mem')==1) {
      print
        mem()
        ."<script type='text/javascript'>\n"
        ."\$J('#memory_monitor').draggable({ handle:'#memory_monitor_handle',opacity:0.9});\n"
        ."</script>";
    }
  }

  function usage() {
    $sql =
       "SELECT\n"
      ."  SUM(IF(`systemID`=1 AND `name`\n"
      ."    IN(\n"
      ."      '--- Use Default ---',\n"
      ."      '_content',\n"
      ."      '_help',\n"
      ."      '_popup',\n"
      ."      '_print',\n"
      ."      '_report'\n"
      ."    ),1,0)) AS `internal`,\n"
      ."  SUM((SELECT COUNT(*) FROM `system` WHERE `defaultLayoutID`=`layout`.`ID`)) AS `system`,\n"
      ."  SUM((SELECT COUNT(*) FROM `pages` WHERE `layoutID` =`layout`.`ID`)) AS `page`\n"
      ."FROM\n"
      ."  `layout`\n"
      ."WHERE\n"
      ."  `ID` IN(".$this->_get_ID().")";
    return $this->get_record_for_sql($sql);
  }

  public function get_version(){
    return VERSION_LAYOUT;
  }
}
?>