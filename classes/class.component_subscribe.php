<?php
define ("VERSION_COMPONENT_SUBSCRIBE","1.0.1");
/*
Version History:
  1.0.1 (2010-10-25)
    1) Bit more work on subscription component - not complete yet
  1.0.0 (2010-10-12)
    1) Initial release
*/
class Component_Subscribe extends Component_Base {
  function draw_standalone($instance='', $args=array(), $disable_params=false) {
    $ident =        "subscribe_standalone";
    $parameter_spec = array(
      'shadow' =>           array('match' => 'enum|0,1',    'default'=>'0',             'hint'=>'0|1'),
      'text_button' =>      array('match' => '',            'default'=>'Subscribe',     'hint'=>'Your Text Here'),
      'text_description' => array('match' => '',            'default'=>'Get new articles emailed directly to you', 'hint'=>'Your Text Here'),
      'text_email' =>       array('match' => '',            'default'=>'Email',         'hint'=>'Your Text Here'),
      'text_title' =>       array('match' => '',            'default'=>'Subscribe',     'hint'=>'Your Text Here'),
      'width' =>            array('match' => '',            'default'=>'260px',         'hint'=>'Width as % or in px')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $ID =           Component_Base::get_safe_ID($ident,$instance);
    $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
    $msg = "";
    switch(get_var('submode')){
      case 'submit':
        $email = sanitize('html',get_var('email'));
        $msg = "<b>Success</b>:<br />We sent you a confirmation email.";
        $out.= HTML::draw_status('subscribe_standalone',$msg);
      break;
      default:
        $content =
           "<div id=\"".$ID."_content\">\n"
          ."  <div style='padding:0 0 5px 0;'>".$cp['text_description']."</div>"
          ."  <div class='clr'>&nbsp;</div>\n"
          ."  <div class='label' style='width:50px;'>".$cp['text_email']."</div>\n"
          ."  <input type='text' id='email' name='email' size='20' style='width: 150px;' onkeypress=\"return keytest_enter_transfer(event,'subscribe_cmd');\"/>\n"
          ."  <div class='clr'>&nbsp;</div>\n"
          ."  <div class='controls'>\n"
          ."    <input type='button' name='subscribe_cmd' id='subscribe_cmd' onclick=\"if (geid_val('email').indexOf('@')!=-1){geid_set('submode','submit');geid('form').submit();}else{alert('Please provide a valid email address')}\" value=\"".$cp['text_button']."\" style='formButton'/>"
          ."  </div>\n"
          ."</div>\n";
        $out.= HTML::draw_form_box($cp['text_title'],$content,'',$cp['shadow'],$cp['width']);
      break;
    }
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_SUBSCRIBE;
  }

}
?>