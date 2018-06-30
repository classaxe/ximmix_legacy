<?php
  define ("VERSION_COMPONENT_FORGOTTEN_PASSWORD","1.0.1");
/*
Version History:
  1.0.1 (2014-01-06)
    1) Component_Forgotten_Password now uses User class to draw actual control
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Forgotten_Password extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false){
    $ident =            "forgotten_password";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'shadow' =>         array('match' => 'enum|0,1',		'default'=>'0', 'hint' => '0|1'),
      'text_failure' =>   array('match' => '',				'default'=>'The email address you entered is not on file.', 'hint'=>'Message shown when email address was not recognised'),
      'text_initial' =>   array('match' => '',				'default'=>'<p>If you forget your username or password, use this form to have a new password emailed to your primary email address.</p><p>When the email arrives, you should signin at once and set a new password.</p>', 'hint'=>'Message to show above control initially'),
      'text_success' =>   array('match' => '',				'default'=>'<strong>Success!</strong><br />Your new password and existing username have been emailed to the address you gave.', 'hint'=>'Message for success')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $Obj_User =     new User;
    $out.=          $Obj_User->draw_forgotten_password($cp['text_initial'],$cp['text_failure'],$cp['text_success'],$cp['shadow']);
    return  $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_FORGOTTEN_PASSWORD;
  }
}
?>