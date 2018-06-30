<?php
  define ("VERSION_COMPONENT_IMAGE_GALLERY","1.0.0");
/*
Version History:
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Image_Gallery extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false){
    $ident =            "image_gallery";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'border' =>               array('match' => '',				'default'=>'#808080',       'hint'=>'RGB Colour for borders'),
      'border_hover' =>         array('match' => '',				'default'=>'#ffff80',       'hint'=>'RGB Colour for border when hovering over a thumbnail'),
      'folder' =>               array('match' => '',				'default'=>BASE_PATH.'UserFiles/Image/gallery', 'hint'=>'Path to folder containing images'),
      'main_image_position' =>  array('match' => 'enum|top,bottom', 'default'=>'top',           'hint'=>'Main image position - top or bottom'),
      'width' =>                array('match' => '',				'default'=>'200',           'hint'=>'Width of image thumnails')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $cp['folder'] = './'.trim($cp['folder'],'./').'/';
    if (!file_exists($cp['folder'])) {
      $out.="<b>Error:</b> No such directory as ".$cp['folder'];
      return $out;
    }
    if (!is_dir($cp['folder'])) {
      $out.="<b>Error:</b> we expected a folder - ".$cp['folder']." looks a lot like a file.";
      return $out;
    }
    Page::push_content(
       "javascript",
       "function ".$safe_ID."(src) {\n"
      ."  geid('".$safe_ID."_main_img').src = src;\n"
      ."  return false;\n"
      ."}"
    );
    Page::push_content(
      "style",
       "#".$safe_ID." {\n"
      ."}\n"
      ."#".$safe_ID."_thumbnails img {\n"
      ."  margin: 10px 6px 0px 0px;\n"
      ."  border: 1px solid ".$cp['border'].";\n"
      ."}\n"
      ."#".$safe_ID."_thumbnails img:hover {\n"
      ."  border: 1px solid ".$cp['border_hover'].";\n"
      ."}\n"
      ."#".$safe_ID."_main {\n"
      ."  margin-top: 20px;\n"
      ."  padding: 10px;\n"
      ."  background-color: ".$cp['border'].";\n"
      ."}\n"
      ."#".$safe_ID."_main img {\n"
      ."  display: block;\n"
      ."  margin: 0 auto;\n"
      ."}\n"
    );
    $fileList =     scandir($cp['folder']);
    $first_image =  false;
    $out.= "<div id=\"".$safe_ID."\">\r\n";
    foreach ($fileList as $filePath) {
      if ($filePath != '.' && $filePath != '..' && !is_dir($cp['folder'].$filePath)) {
        if (!$first_image){
          $first_image = "/img/sysimg/?img=".$cp['folder'].$filePath;
          break;
        }
      }
    }
    if ($cp['main_image_position']=='top') {
      $out.=
        "<div id=\"".$safe_ID."_main\">\r\n"
       ."<img id=\"".$safe_ID."_main_img\" src=\"".$first_image."\" />\r\n"
       ."</div>\r\n";
    }
    $out.= "<div id='".$safe_ID."_thumbnails'>\r\n";
    foreach ($fileList as $filePath) {
      if ($filePath != '.' && $filePath != '..' && !is_dir($cp['folder'].$filePath)) {
        $out.=
           "<img src=\"/img/width/".$cp['width']
          .$cp['folder'].$filePath."\""
          ." onclick=\"".$safe_ID."('/img/sysimg/?img=".$cp['folder'].$filePath."');\" />";
      }
    }
    if ($cp['main_image_position']=='bottom') {
      $out.=
        "<div id=\"".$safe_ID."_main\">\r\n"
       ."<img id=\"".$safe_ID."_main_img\" src=\"".$first_image."\" />\r\n"
       ."</div>\r\n";
    }
    $out.=
       "</div>\r\n"
      ."</div>\r\n";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_IMAGE_GALLERY;
  }
}
?>