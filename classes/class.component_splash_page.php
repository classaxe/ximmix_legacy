<?php
  define ("VERSION_COMPONENT_SPLASH_PAGE","1.0.0");
/*
Version History:
  1.0.0 (2012-01-01)
    1) Initial release - moved from Component class
*/
class Component_Splash_Page extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false) {
    $ident =            "splash_page";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'page' =>         array('match' => '',		'default'=>'splash',  'hint'=>'name of page to show initially')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if (!isset($_SESSION['splash'])){
      $_SESSION['splash']=1;
      header("Location: ".BASE_PATH.$cp['page'],302);
      die;
    }
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_SPLASH_PAGE;
  }
}
?>