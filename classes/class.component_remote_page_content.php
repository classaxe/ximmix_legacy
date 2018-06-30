<?php
  define ("VERSION_COMPONENT_REMOTE_PAGE_CONTENT","1.0.0");
/*
Version History:
  1.0.0 (2011-12-31)
    1) Initial release - moved from Component class
*/
class Component_Remote_Page_Content extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false) {
    $ident =            "get_remote_page_content";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'url' =>   array('default'=>'http://www.example.com', 'hint'=>'Page at another '.SYSTEM_FAMILY.' site from which to obtain content')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if ($cp['url']=='') {
      return  __CLASS__."::".__FUNCTION__."() requires parameter remote_page";
    }
    $cp['url'].=(strpos($cp['url'],'?') ? "&" : "?")."command=page_content";
    $Obj_Socket = new gwSocket;
    if ($Obj_Socket->getUrl($cp['url'])) {
      $out.= $Obj_Socket->page;
    }
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_REMOTE_PAGE_CONTENT;
  }
}
?>