<?php
define ("VERSION_COMPONENT_IMAGE_TEXT","1.0.0");
/*
Version History:
  1.0.0 (2010-07-07)
    1) Moved Component::image_text() into here
*/
class Component_Image_Text extends Component_Base {
  function draw($instance='', $args=array(), $disable_params=false) {
    global $page_vars;
    $ident =            "image_text";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'bgcolor' =>      array('match' => '',				'default'=>'ffffff',      'hint'=>'#RGB'),
      'bold' =>         array('match' => 'enum|0,1',		'default'=>'0',           'hint'=>'0|1'),
      'color' =>        array('match' => '',				'default'=>'000000',      'hint'=>'#RGB'),
      'field' =>        array('match' => '',				'default'=>'',            'hint'=>'(optional) - field in record to use for text, overrides text value when given'),
      'font' =>         array('match' => '',				'default'=>'cour.ttf',    'hint'=>'fontfilename.ttf'),
      'size' =>         array('match' => '',				'default'=>'12',          'hint'=>'size in pt'),
      'text' =>         array('match' => '',				'default'=>'Hello World!','hint'=>'text to display (unless field is set)')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if ($cp['field']) {
      $cp['text'] = $page_vars[$cp['field']];
    }
    if ($cp['text']) {
      $out.=
        "<img src=\"".BASE_PATH."img/text/"
        .trim($cp['color'],'#')."/"
        .trim($cp['bgcolor'],'#')."/"
        .$cp['size']."/"
        .$cp['font']."/"
        .$cp['bold']."/"
        .str_replace(' ','+',$cp['text'])."\""
        ." alt=\"".$cp['text']."\" />";
      }
     return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_IMAGE_TEXT;
  }
}
?>