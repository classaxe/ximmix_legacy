<?php
define('VERSION_FONT_FACE','1.0.1');
/*
Version History:
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Font_Face extends lst_named_type {
  var $file_prefix;

  function __construct($ID="") {
    parent::__construct($ID,'lst_font_face','Font Face');
    $this->file_prefix = "font_face_";
  }

  function clear_cache() {
    $filename = SYS_FONTS.$this->file_prefix.$this->_get_ID().".gif";
    if (file_exists($filename)) {
      unlink($filename);
    }
  }

  function sample($size=14,$text='info_101@me.com The quick brown fox') {
    $font = $this->get_field('value').".ttf";
    return
       "<img src=\"".BASE_PATH."img/text/000000/FFFFFF/".$size."/".$font."/0/".$text."\""
      ." alt=\"Sample of ".$font." at ".$size."pt.\" />";
  }

  public function get_version(){
    return VERSION_FONT_FACE;
  }
}
?>