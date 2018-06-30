<?php
  define ("VERSION_COMPONENT_INLINE_SIGNIN","1.0.2");
/*
Version History:
  1.0.2 (2014-01-28)
    1) Newline after js onload code
  1.0.1 (2012-06-14)
    1) Changed maxlength of username to 50 chars (was 20), and password to 25 (was 20)
  1.0.0 (2011-12-29)
    1) Initial release - moved from Component class
*/
class Component_Inline_Signin extends Component_Base {

  function draw($instance='', $args=array(), $disable_params=false){
    $ident =            "inline_signin";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'autocomplete_disabled' =>    array('match' => 'enum|0,1',    'default'=>'1',                                             'hint'=>'Prevents browsers from \'remembering\' username and password'),
      'css_background_disabled' =>  array('match' => 'enum|0,1',    'default'=>'0',                                             'hint'=>'Prevents browsers from \'remembering\' username and password'),
      'footer_public' =>            array('match' => '',    'default'=>'',                                                      'hint'=>'Text to show after if person has not signed in'),
      'footer_signedin' =>          array('match' => '',    'default'=>'',                                                      'hint'=>'Text to show before if person has signed in'),
      'header_public' =>            array('match' => '',    'default'=>'',                                                      'hint'=>'Text to show after if person has not signed in'),
      'header_signedin' =>          array('match' => '',    'default'=>'',                                                      'hint'=>'Text to show before if person has signed in'),
      'label_password' =>           array('match' => '',    'default'=>'',                                                      'hint'=>'Label to show before password field (if used)'),
      'label_username' =>           array('match' => '',    'default'=>'',                                                      'hint'=>'Label to show before username field (if used)'),
      'message_inactive' =>         array('match' => '', 	'default'=>'<b>Sorry:</b> Your account is presently inactive',      'hint'=>'Message top show if login is inactive'),
      'message_invalid' =>          array('match' => '', 	'default'=>'<b>Sorry:</b> Your credentials are not recognised',     'hint'=>'Message top show if login is not recognised'),
      'message_missing' =>          array('match' => '', 	'default'=>'Please provide <b>username</b> and <b>password</b>',    'hint'=>'Message top show if login details are incomplete'),
      'signin_button_alt' =>        array('match' => '',    'default'=>'Sign In',                                               'hint'=>'Text to use for signin button image title  / alt text')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if (get_userID()){
      $out.= $cp['header_signedin'].$cp['footer_signedin'];
      return $out;
    }
    switch (get_var('msg')){
      case "invalid":
        $msg_html = "<div id=\"topbar_signin_msg\">".$cp['message_invalid']."</div>";
        $username = "";
        $password = "";
      break;
      case "inactive":
        $msg_html = "<div id=\"topbar_signin_msg\">".$cp['message_inactive']."</div>";
        $username = "";
        $password = "";
      break;
      case "missing":
        $msg_html = "<div id=\"topbar_signin_msg\">".$cp['message_missing']."</div>";
        $username = "";
        $password = "";
      break;
      default:
        $msg_html = "";
        $username = sanitize('html',get_var('topbar_username'));
        $password = sanitize('html',get_var('topbar_password'));
      break;
    }
    if ($cp['autocomplete_disabled']==1){
      Page::push_content('javascript_onload',"  autocomplete_off();\n");
    }
    $out.=
       "<div id=\"topbar_signin\">\n"
      .$msg_html
      .$cp['header_public']
      .($cp['label_username'] ? "<label for=\"topbar_username\">".$cp['label_username']."</label>" : "")
      ."<input type=\"text\" id=\"topbar_username\""
      .($cp['autocomplete_disabled']==1 ? " class=\"autocomplete_off\"" : "")
      ." name=\"topbar_username\""
      ." value=\"".$username."\" size=\"10\" maxlength=\"50\""
      ." onkeypress=\"return keytest_enter_execute(event,function(){geid('topbar_password').focus();})\""
      .($cp['css_background_disabled'] ? "" : " onblur=\"this.style.backgroundPosition=(this.value ? '100% 0%' : '0% 0%')\"")
      ." onfocus=\"inline_signin_hide_msg();"
      .($cp['css_background_disabled'] ? "" : "this.style.backgroundPosition='100% 0%'")
      ."\" />\n"
      .($cp['label_password'] ? "<label for=\"topbar_password\">".$cp['label_password']."</label>" : "")
      ."<div id=\"topbar_password_container\">\n"
      ."<input type=\"password\" id=\"topbar_password\""
      .($cp['autocomplete_disabled']==1 ? " class=\"autocomplete_off\"" : "")
      ." name=\"topbar_password\""
      ." value=\"".$password."\" size=\"10\" maxlength=\"25\""
      ." onkeypress=\"keytest_enter_execute(event,function(){geid_set('command','topbar_signin');geid('form').submit()})\""
      .($cp['css_background_disabled'] ? "" : " onblur=\"this.style.backgroundPosition=(this.value ? '100% 100%' : '0% 100%')\"")
      ." onfocus=\"inline_signin_hide_msg();"
      .($cp['css_background_disabled'] ? "" : "this.style.backgroundPosition='100% 100%'")
      ."\" />\n"
      ."</div>\n"
      ."<input type=\"image\" id=\"topbar_signin_btn\" alt=\"".$cp['signin_button_alt']."\" title=\"".$cp['signin_button_alt']."\""
      ." onclick=\"geid_set('command','topbar_signin');geid('form').submit();return false;\" src=\"".BASE_PATH."img/spacer\" />\n"
      .$cp['footer_public']
      ."</div>";
    return $out;
  }



  public function get_version(){
    return VERSION_COMPONENT_INLINE_SIGNIN;
  }
}
?>