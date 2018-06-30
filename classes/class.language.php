<?php
define('VERSION_LANGUAGE','1.0.3');
/*
Version History:
  1.0.3 (2012-12-09)
    1) Changes to Language::prepare_field() to handle resaving of single language
       content after conversion to a multi-language system.
    2) Bug fix for changing language to ensure that viewer remains on original page
  1.0.2 (2012-04-30)
    1) Corrections to comments in header
  1.0.1 (2011-01-09)
    1) Added Language::get_supported() and Language::convert_tags()
  1.0.0 (2011-01-06)
    1) Initial release
*/
class Language extends Record {

  public function set($code='en'){
    global $page_vars;
    $url = BASE_PATH.trim($page_vars['path'],'/');
    $_SESSION['lang']=$code;
    header('Location: '.$url,302);
    print "redirecting...";
    die;
  }

  static public function convert_tags($string){
    global $system_vars;
    if (!System::has_feature('multi-language')){
      return $string;
    }
    $supported = explode(', ',$system_vars['languages']);
    $language = (isset($_SESSION['lang']) ? $_SESSION['lang'] : $system_vars['defaultLanguage']);
    $pagebits = preg_split("/\[LANG\]|\[\/LANG\]/",$string);
    if (count($pagebits)<=1) {
      return $string;
    }
    $out = "";
    $renderedbit =    array();
    $plaintext =      true;
    foreach($pagebits as $bit){
      if ($plaintext){
        $out.= $bit;
      }
      else {
        if (in_array($bit,$renderedbit)){
          $out.= $renderedbit[$bit];
        }
        else {
          $bit_arr =  explode('|',$bit);
          $lang =     array_shift($bit_arr);
          $text =     implode('|',$bit_arr);
          if ($language==$lang){
            if ($text!==""){
              $out.= $text;
            }
            else {
              $out.= '';    // There's no content in the chosen language
            }
          }
        }
      }
      $plaintext = !$plaintext;
    }
//    y($out);die;
    return $out;
  }

  public function get_browser_preferences(){
//    setcookie('lang','fr-ca');
    $out = array();
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
      // break up string into pieces (languages and q factors)
      preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);
      if (count($lang_parse[1])) {
        // create a list like "en" => 0.8
        $out = array_combine($lang_parse[1], $lang_parse[4]);
        // set default to 1 for any without q factor
        foreach ($out as $lang => $val) {
          if ($val === '') $out[$lang] = 1;
        }
      }
    }
    if (isset($_COOKIE['lang']) && $_COOKIE['lang']){
      $out[$_COOKIE['lang']]=2;
    }
    arsort($out, SORT_NUMERIC);
    return $out;
  }

  public function get_supported(){
    global $system_vars;
    $out =      array();
    $codes =    array();
    if (!System::has_feature('multi-language')){
      $codes[] =  $system_vars['defaultLanguage'];
    }
    else {
      $supported = explode(', ',$system_vars['languages']);
      foreach ($supported as $s){
        $codes[] = $s;
      }
    }
    $sql =
        "SELECT\n"
       ."  `textEnglish` `text`,\n"
       ."  `value`\n"
       ."FROM\n"
       ."  `listdata`\n"
       ."WHERE\n"
       ."  `listTypeID` = (SELECT `ID` FROM `listtype` WHERE `name`='lst_iso-639-1') AND\n"
       ."  `value` IN('".implode("','",$codes)."')\n"
       ."ORDER BY\n"
       ."  `value`!='".$system_vars['defaultLanguage']."',\n"
       ."  `textEnglish`";
    return $this->get_records_for_sql($sql);
  }

  public function prepare_field($field){
    $field_form_safe =  (substr($field,0,4)=='xml:' ? str_replace('/',':',$field) : $field);
    if (!System::has_feature('multi-language')){
      return (isset($_POST[$field_form_safe]) ? $_POST[$field_form_safe] : "");
    }
    $languages =    $this->get_supported();
    $value =        "";
    foreach ($languages as $language){
      $value.=
         "[LANG]"
        .$language['value']."|".$_POST[$field."_".$language['value']]
        ."[/LANG]";
    }
    return $value;
  }

  public function get_version(){
    return VERSION_LANGUAGE;
  }
}
?>