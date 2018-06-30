<?php
  define ("VERSION_COMPONENT_IMAGE_FADER","1.0.1");
/*
Version History:
  1.0.1 (2014-01-28)
    1) Newline after js onload code
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Image_Fader extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false){
    $ident =        "image_fader";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'folder' =>       array('match' => '',				'default'=>'./UserFiles/Image/fader', 'hint'=>'Path to folder containing images'),
      'random_start' => array('match' => 'enum|0,1',        'default' =>'1',                      'hint'=>'0|1 - Whether to start with first image or play from a random position'),
      'secFade' =>      array('match' => '',				'default'=>'0.5',                     'hint'=>'Decimal time in seconds for fade'),
      'secShow' =>      array('match' => '',				'default'=>'2',                       'hint'=>'Decimal time in seconds for show'),
      'title' =>        array('match' => '',				'default'=>'',                        'hint'=>'Title to to give images'),
      'URL' =>          array('match' => '',                'default' =>'',                       'hint'=>'If given, clicking on any image launches this URL'),
      'URL_popup' =>    array('match' => 'enum|0,1',        'default' =>'0',                      'hint'=>'0|1 - Used when URL is fixed')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $_out =         Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $out =          "";
    $cp['folder'] = './'.trim($cp['folder'],'/');
    if (!file_exists($cp['folder'])) {
      $out.="<b>Error:</b> No such directory as ".BASE_PATH.$cp['folder'];
      return $out;
    }
    if (!is_dir($cp['folder'])) {
      $out.="<b>Error:</b> we expected a folder - ".BASE_PATH.$cp['folder']." looks a lot like a file.";
      return $out;
    }
    $h = opendir($cp['folder']);
    $image_js =    array();
    $x_max = 0;
    $y_max = 0;
    $img_urls = array();
    while (false !== ($fileName = readdir($h))) {
      if ($fileName !== "." && $fileName !== "..") {
        $ext_arr = explode('.',$fileName);
        $ext = $ext_arr[count($ext_arr)-1];
        switch ($ext){
          case "gif":
            $img = imagecreatefromgif($cp['folder'].'/'.$fileName);
          break;
          case "png":
            $img = imagecreatefrompng($cp['folder'].'/'.$fileName);
          break;
          case "jpg":
          case "jpeg":
            $img = imagecreatefromjpeg($cp['folder'].'/'.$fileName);
          break;
          default:
            $img = false;
          break;
        }
        if ($img) {
          if (imagesx($img)>$x_max) { $x_max = imagesx($img); }
          if (imagesy($img)>$y_max) { $y_max = imagesy($img); }
          $img_url = BASE_PATH."img/sysimg?img=".$cp['folder'].'/'.$fileName;
          $img_urls[] = $img_url;
          $image_js[] =
             "      {\n"
            ."        image:     \"".$img_url."\",\n"
            ."        url:       \"".$cp['URL']."\",\n"
            ."        url_popup: ".$cp['URL_popup']."\n"
            ."      }";
        }
      }
    }
    asort($image_js);
    if (!count($image_js)){
      $out.= "<b>Error:</b> ".BASE_PATH.$cp['folder']." contains no images.";
      return $out;
    }
    $firstImageIndex =  ($cp['random_start']==1 ? rand(0, count($image_js)-1) : 0);
    $id =               $ident."_".$instance;
    Page::push_content(
      "javascript",
        "var obj_".$id." =\n"
       ."  new image_rotator(\n"
       ."    '".$id."',".$firstImageIndex.",false,false,".$cp['secShow'].",".$cp['secFade'].",\n"
       ."    [\n"
       .implode(",\n",$image_js)."\n"
       ."    ]\n"
       ."  );\n"
    );
    Page::push_content('javascript_onload',"  obj_".$id.".do_setup();\n");
    $out=
       "<div id=\"".$ident."_".$instance."\">"
      .$_out
      ."  <div id=\"".$ident."_".$instance."_mask\" style=\"height:".$y_max."px;width:".$x_max."px;"
      .($cp['URL'] ? "cursor:pointer;" : "")
      ."\""
      .($cp['URL'] ?
         " onclick=\""
        .($cp['URL_popup'] ?
           "popWin('".$cp['URL']."','url_".$instance."','location=1,status=1,scrollbars=1,resizable=1',720,400,1)"
         :
           "window.location='".$cp['URL']."'"
         )
         ."\""
       :
         ""
       )
      ."\">\n"
      ."    <img id=\"".$ident."_".$instance."_1\" src=\"".$img_urls[$firstImageIndex]."\" alt=\"\" style=\"position:absolute\" title=\"".$cp['title']."\" />\n"
      ."    <img id=\"".$ident."_".$instance."_2\" src=\"".BASE_PATH."img/spacer\" alt=\"\" style=\"position:absolute;opacity:0;filter: alpha(opacity = 0);\" title=\"".$cp['title']."\" />\n"
      ."  </div>\n"
      ."</div>";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_IMAGE_FADER;
  }
}
?>