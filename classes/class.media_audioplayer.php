<?php
define('VERSION_MEDIA_AUDIOPLAYER','1.0.9');
/*
Version History:
  1.0.9 (2014-01-28)
    1) Newline after code in JS onload code in Media_Audioplayer::draw_clip()

  (Older version history in class.media_audioplayer.txt)
*/
class Media_Audioplayer {
  // Implements swf mp3 player from http://www.1pixelout.net/code/audio-player-wordpress-plugin
  static $controls = 0;
  var $control_id;
  var $params;

  public function __construct($params="") {
    $this->params = $params;
  }

  public function draw_clip(){
    static $libraries_shown = false;
    if (!$libraries_shown){
      $version = System::get_item_version('js_jdplayer');
      $path =   BASE_PATH."sysjs/jdplayer/".$version."/";
      page::push_content(
        'javascript_top',
        "<script type=\"text/javascript\" src=\"".$path."mediaelement-and-player.min.js\"></script>"
      );
      page::push_content(
        'style_include',
        "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$path."mediaelementplayer.css\" />"
      );
      page::push_content(
        'javascript_onload',
        "  \$('audio').mediaelementplayer();\n"
      );
      $libraries_shown = true;
    }
    $params_arr =               explode("|",$this->params);
    $media_url =                array_shift($params_arr);
    $_args = array();
    foreach($params_arr as $param) {
      $param_bits =             explode("=",$param);
      $_args[$param_bits[0]] =   $param_bits[1];
    }
    $width =    (isset($_args['width'])  ? $_args['width']  : '180');
    return
       "\n"
      ."<audio controls=\"controls\" style=\"width:".$width."px\">\n"
      ."  <source src=\"".$media_url."\" type=\"audio/mp3\" />\n"
      ."</audio>\n";
  }

  public function get_version(){
    return VERSION_MEDIA_AUDIOPLAYER;
  }

}
?>