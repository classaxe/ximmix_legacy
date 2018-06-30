<?php
  define ("VERSION_COMPONENT_VIDEO_PLAYER","1.0.0");
/*
Version History:
  1.0.0 (2011-12-29)
    1) Initial release - moved from Component class
*/
class Component_Video_Player extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false){
    $ident =            "video_player";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'allow_full_screen' =>    array('match' => 'enum|0,1',        'default'=>'1',             'hint'=>'Allow player to zoom to full screen'),
      'height' =>               array('match' => '',				'default'=>'240',           'hint'=>'Height of video player'),
      'path_flv' =>             array('match' => '',				'default'=>'',              'hint'=>'Path to FLV file'),
      'path_jpg' =>             array('match' => '',				'default'=>'',              'hint'=>'Path to JPG preview image file'),
      'width' =>                array('match' => '',				'default'=>'320',           'hint'=>'Width of video player')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $out.=
       "<object id=\"".$safe_ID."\" classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" name=\"".$safe_ID."\" width=\"".$cp['width']."\" height=\"".$cp['height']."\">\n"
      ."  <param name=\"movie\" value=\"".BASE_PATH."resource/jwplayer/".Component_Video_Player::get_version()."\" />\n"
      ."  <param name=\"wmode\" value=\"opaque\" />\n"
      ."  <param name=\"allowfullscreen\" value=\"".($cp['allow_full_screen'] ? "true" : "false")."\" />\n"
      ."  <param name=\"allowscriptaccess\" value=\"always\" />\n"
      ."  <param name=\"flashvars\" value=\"file=".$cp['path_flv'].($cp['path_jpg'] ? "&amp;image=".BASE_PATH."img/sysimg/?img=".$cp['path_jpg'] : "")."\" />\n"
      ."  <object type=\"application/x-shockwave-flash\" data=\"".BASE_PATH."resource/jwplayer/".Component_Video_Player::get_version()."\" width=\"".$cp['width']."\" height=\"".$cp['height']."\">\n"
      ."    <param name=\"movie\" value=\"".BASE_PATH."resource/jwplayer/".Component_Video_Player::get_version()."\" />\n"
      ."    <param name=\"wmode\" value=\"opaque\" />\n"
      ."    <param name=\"allowfullscreen\" value=\"".($cp['allow_full_screen'] ? "true" : "false")."\" />\n"
      ."    <param name=\"allowscriptaccess\" value=\"always\" />\n"
      ."    <param name=\"flashvars\" value=\"file=".$cp['path_flv'].($cp['path_jpg'] ? "&amp;image=".BASE_PATH."img/sysimg/?img=".$cp['path_jpg'] : "")."\" />\n"
      ."    <p><a href=\"http://get.adobe.com/flashplayer\">Get Flash</a> to see this player.</p>\n"
      ."  </object>\n"
      ."</object>\n";
    return $out;
  }


  public function get_version(){
    return VERSION_COMPONENT_VIDEO_PLAYER;
  }
}
?>