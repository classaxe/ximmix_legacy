<?php
  define ("VERSION_COMPONENT_CONTENT_SIGNIN_MIRROR","1.0.2");
/*
Version History:
  1.0.2 (2012-10-17)
    1) Now uses Page::get_ID_by_path()
  1.0.1 (2012-10-02)
    1) Change to Component_Content_Signin_Mirror::draw() to make call to
       Page::get_field() in the same way as other object's get_field() methods
       operate
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Content_Signin_Mirror extends Component_Base {

 function draw($instance='',$args=array(),$disable_params=false) {
    $ident =            "content_signin_mirror";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'public_page' =>    array('default'=>'',  'hint'=>'Page with content for public'),
      'signed_in_page' => array('default'=>'',  'hint'=>'Page with content for people who have signed in')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $page =         (isset($_SESSION['person']) ? $cp['signed_in_page'] : $cp['public_page']);
    $path =       '//'.trim($page,'/').'/';
    $Obj_Page =     new Page;
    $Obj_Page->_set_ID($Obj_Page->get_ID_by_path($path));
    $content = $Obj_Page->get_field('content');
    if ($content!==false) {
      return $out.$content;
    }
    return $out.$ident.": Page not found - ".$path;
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_CONTENT_SIGNIN_MIRROR;
  }
}
?>