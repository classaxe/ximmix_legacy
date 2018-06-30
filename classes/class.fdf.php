<?php
define('VERSION_FDF','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class FDF {
  // Based on LGPL licenced code:
  // KOIVI HTML Form to FDF Parser for PHP (C) 2004 Justin Koivisto
  // http://koivi.com/fill-pdf-form-fields/tutorial.php
  function get_FDF($pdf_file,$data){
    $out = array();
    $out[] = "%FDF-1.2\n1 0 obj\n<< \n/FDF << /Fields [ ";
    foreach($data as $field => $val){
      if(is_array($val)){
        $out[] = '<</T('.$field.')/V[';
        foreach($val as $opt) {
          $out[] = '('.trim($opt).')';
        }
        $out[] = ']>>';
      }
      else{
        $out[] = '<</T('.$field.')/V('.trim($val).')>>';
      }
    }
    $out[] =
       "] \n/F (".$pdf_file.") /ID [ <".md5(time()).">\n] >>"
      ." \n>> \nendobj\ntrailer\n"
      ."<<\n/Root 1 0 R \n\n>>\n%%EOF\n";
    return implode('',$out);
  }
  function get_XFDF($file,$data,$enc='UTF-8'){
    $out = array();
    $out[] =
       '<?xml version="1.0" encoding="'.$enc.'"?>'."\n"
      .'<xfdf xmlns="http://ns.adobe.com/xfdf/" xml:space="preserve">'."\n"
      .'<fields>'."\n";
    foreach($data as $field => $val){
      $out[] = '<field name="'.$field.'">'."\n";
      if(is_array($val)){
        $out[] = '<</T('.$field.')/V[';
        foreach($val as $opt)
          $out[] = '<value><![CDATA['.$opt.']]></value>'."\n";
      }
      else{
        $out[] = '<value><![CDATA['.$val.']]></value>'."\n";
      }
      $out[] = '</field>'."\n";
    }
    $out[] =
       '</fields>'."\n"
      .'<ids original="'.md5($file).'" modified="'.time().'" />'."\n"
      .'<f href="'.$file.'" />'."\n"
      .'</xfdf>'."\n";
    return implode('',$out);
  }
  public function get_version(){
    return VERSION_FDF;
  }
}
?>