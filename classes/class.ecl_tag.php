<?php
define('VERSION_ECL_TAG','1.0.3');
/*
Version History:
  1.0.3 (2012-05-27)
    1) Tweak to ECL_Tag::get_all() to make sure that tag names are all lowercase
       for consistent use, even in email broardcast where plain text version of
       message is in uppercase, including ECL tags
  1.0.2 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.1 (2009-07-16)
    1) Added ECL_Tag::get_js_options()
  1.0.0 (2009-07-02)
    Initial release
*/
class ECL_Tag extends Record{
  static $cache_tags_array = array();

  public function __construct($ID="") {
    parent::__construct("ecl_tags",$ID);
    $this->_set_object_name('ECL Tag');
    $this->_set_name_field('tag');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new Tag value'
      )
    );
  }

  public function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function get_all() {
    if (isset(ECL_Tag::$cache_tags_array['tag'])) {
      return ECL_Tag::$cache_tags_array;
    }
    $out =
      array(
        'text' =>       array(),
        'php' =>        array(),
        'script' =>     array(),
        'tag' =>        array(),
        'nameable' =>   array()
      );
    $sql =
       "SELECT\n"
      ."  CONCAT(\n"
      ."    '[',\n"
      ."    IF(`for_email`,'E','&nbsp;'),\n"
      ."    IF(`for_page`,'P','&nbsp;'),\n"
      ."    IF(`for_layout`,'L','&nbsp;'),\n"
      ."    '] ',\n"
      ."    IF(`systemID`=1,'* ','&nbsp; '),\n"
      ."    `description`\n"
      ."  ) `text`,\n"
      ."  `nameable`,\n"
      ."  `php`,\n"
      ."  LOWER(`tag`) `tag`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` IN (1,".SYS_ID.")\n"
      ."ORDER BY\n"
      ."  `systemID`=1,`description`";
    $records = $this->get_records_for_sql($sql);
    if (!$records) {
      return $out;
    }
    foreach ($records as $record) {
      ECL_Tag::$cache_tags_array['nameable'][] = $record['nameable'];
      ECL_Tag::$cache_tags_array['php'][] =      $record['php'];
      ECL_Tag::$cache_tags_array['tag'][] =      $record['tag'];
      ECL_Tag::$cache_tags_array['text'][] =     $record['text'];
    }
    return ECL_Tag::$cache_tags_array;
  }

  public function get_js_options(){
    $tags = $this->get_all();
    $out =
      "ecltag_options = [];\n";
    for ($i=0; $i<count($tags['tag']); $i++) {
      $label =      str_replace('&nbsp;','-',$tags['text'][$i]);
      $nameable =   $tags['nameable'][$i];
      $label =      substr($label,0,4).($nameable ? "N" : "-").substr($label,4);
      $value =      "[ECL]".$tags['tag'][$i]."[/ECL]";
      $out.=
         "  ecltag_options.push({\n"
        ."    label: \"".$label."\",\n"
        ."    nameable: \"".$nameable."\",\n"
        ."    value: \"".$value."\"\n"
        ."  });\n";
    }
    return $out;
  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_ECL_TAG;
  }
}
?>