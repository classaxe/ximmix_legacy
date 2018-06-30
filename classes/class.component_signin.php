<?php
  define ("VERSION_COMPONENT_SIGNIN","1.0.2");
/*
Version History:
  1.0.2 (2014-01-28)
    1) Newline and semicolun after JS code in Component_Signin::_draw_signin()
  1.0.1 (2013-05-24)
    1) Updated code to make it conformant to modern component specification
  1.0.0 (2012-01-01)
    1) Initial release - moved from Component class
*/
class Component_Signin extends Component_Base {
  protected $_msg;
  protected $_password;
  protected $_username;

  public function __construct(){
    $this->_ident =         'signin';
    $this->_parameter_spec = array(
      'help_page' =>        array('match' => '',            'default'=>'_help_user_signin_existing',    'hint'=>'Page to shw if help is activated'),
      'label_width' =>      array('match' => 'range|0,n',   'default'=>'6',                             'hint'=>'Width of text labels in em units'),
      'shadow' =>           array('match' => 'enum|0,1',    'default'=>'0',                             'hint'=>'0|1'),
      'show_help' =>        array('match' => 'enum|0,1',    'default'=>'1',                             'hint'=>'0|1'),
      'signed_in_page' =>   array('match' => '',            'default'=>'/signed_in',                    'hint'=>'Page to go to when signed in'),
      'text_button' =>      array('match' => '',            'default'=>'Sign In',                       'hint'=>'Your Text Here'),
      'text_password' =>    array('match' => '',            'default'=>'Password',                      'hint'=>'Your Text Here'),
      'text_title' =>       array('match' => '',            'default'=>'Sign In',                       'hint'=>'Your Text Here'),
      'text_username' =>    array('match' => '',            'default'=>'Username',                      'hint'=>'Your Text Here')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    if (get_userID()){
      if ($this->_html) {
        $this->_draw_no_redirect_message();
      }
      else {
        $this->_do_redirect_if_signed_in();
        $this->_draw_redirect_message();
        die;
      }
    }
    $this->_draw_status();
    $this->_draw_signin();
    return $this->_html;
  }

  protected function _draw_no_redirect_message(){
    $this->_msg.= 'noredirect';
  }

  protected function _draw_redirect_message(){
    print "Redirecting...";
  }

  protected function _draw_signin(){
    $content =
       "  <label for='username' style='width:".$this->_cp['label_width']."em'>".$this->_cp['text_username']."</label>\n"
      ."  <input type='text' id='username' name='username' value=\"".$this->_username."\" size='20' style='width: 120px;'/>\n"
      ."  <div class='clr'>&nbsp;</div>\n"
      ."  <label for='password' style='width:".$this->_cp['label_width']."em'>".$this->_cp['text_password']."</label>\n"
      ."  <input type='password' id='password' name='password' size='20' style='width: 120px;' onkeypress=\"return keytest_enter_transfer(event,'signin_cmd');\"/>\n"
      ."  <div class='clr'>&nbsp;</div>\n"
      ."  <div class='controls'>\n"
      ."    <input type='button' name='signin_cmd' id='signin_cmd' onclick=\"geid_set('anchor',window.location.hash);geid_set('command','signin');geid('form').submit();\" value=\"".$this->_cp['text_button']."\" style='formButton'/>"
      ."  </div>\n";
    $this->_html.=
        HTML::draw_form_box($this->_cp['text_title'],$content,($this->_cp['show_help'] ? $this->_cp['help_page'] : ""),$this->_cp['shadow']);
    Page::push_content(
      'javascript_onload',
      "  geid('username').focus();\n"
    );
  }

  protected function _draw_status(){
    switch ($this->_msg) {
      case "invalid":
        $this->_html.= "<p style='color:#ff0000'><b>Error:</b> Invalid ".$this->_cp['text_username']." or ".$this->_cp['text_password'].".</p>";
      break;
      case "inactive":
        $this->_html.= "<p style='color:#ff0000'><b>Status:</b> Your account is presently inactive.</p>";
      break;
      case "missing":
        $this->_html.= "<p style='color:#ff0000'><b>Error:</b> You must provide ".$this->_cp['text_username']." and ".$this->_cp['text_password'].".</p>";
      break;
      case "noredirect":
        $this->_html.= "<p style='color:#008000'><b>Notice:</b> This component would normally result a redirect to <a href=\"".$this->_cp['signed_in_page']."\">".$this->_cp['signed_in_page']."</a> since you have already signed in.</p>";
      break;
    }
  }

  protected function _do_redirect_if_signed_in(){
    $url =
       BASE_PATH
      .trim($this->_cp['signed_in_page'],'/')
      ."?rnd=".dechex(mt_rand(0,mt_getrandmax()));
    header("Location: ".$url);
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_msg =       sanitize('html',get_var('msg'));
    $this->_password =  sanitize('html',get_var('password'));
    $this->_username =  sanitize('html',get_var('username'));
  }

  public function get_version(){
    return VERSION_COMPONENT_SIGNIN;
  }
}
?>