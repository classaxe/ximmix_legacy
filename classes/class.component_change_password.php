<?php
  define ("VERSION_COMPONENT_CHANGE_PASSWORD","1.0.1");
/*
Version History:
  1.0.1 (2014-01-06)
    1) Component_Change_Password::_setup_do_command() now uses User class -
       contacts don't have passwords
  1.0.0 (2013-06-03)
    1) Initial release - moved from Person class
*/
class Component_Change_Password extends Component_Base {
  protected $_command;
  protected $_done = false;
  protected $_msg = '';
  protected $_Obj_User;
  protected $_pwd;
  protected $_pwd2;


  public function __construct(){
    $this->_ident =         'password';
    $this->_parameter_spec = array(
      'help_page' =>            array('match' => '',            'default'=>'_help_user_toolbar_password',   'hint'=>'Help page to display if user click on help icon'),
      'page_public' =>          array('match' => '',            'default'=>'/signin',                       'hint'=>'Page to redirect to if member is notr signed in'),
      'shadow'=>                array('match' => 'enum|0,1',    'default'=>'0',                             'hint'=>'0|1'),
      'text_confirm_password'=> array('match' => '',            'default'=>'Confirm Password',              'hint'=>'Text to use for Confirm Password label'),
      'text_heading' =>         array('match' => '',            'default'=>'Change Your Password',          'hint'=>'Your Text Here'),
      'text_initial' =>         array('match' => '',            'default'=>'',                              'hint'=>'Your Text Here'),
      'text_new_password'=>     array('match' => '',            'default'=>'New Password',                  'hint'=>'Text to use for New Password label')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_draw_status();
    $this->_draw_initial();
    $this->_draw_form();
    $this->_draw_done();
    return $this->_html;
  }

  protected function _draw_done(){
    if (!$this->_done){
      return;
    }
    $this->_html.= "<p class='txt_c'><input type='submit' value='Done' onclick=\"geid_set('goto','')\" /></p>";
  }

  public function _draw_form() {
    if ($this->_done){
      return;
    }
    $content =
       "  <label for='change_password' style='width: 10em'>".$this->_cp['text_new_password']."</label>\n"
      ."  <input type='password' id='change_password' name='change_password' size='20' style='width: 180px;' value=\"\"/>\n"
      ."  <div class='clr'>&nbsp;</div>\n"
      ."  <label for='change_password2' style='width: 10em'>".$this->_cp['text_confirm_password']."</label>\n"
      ."  <input type='password' id='change_password2' name='change_password2' size='20' style='width: 180px;' value=\"\"/>\n";
    $controls ="<input type='submit' value='Change Password' style='formButton' onclick=\"geid('command').value='Change Password';\"/>";
    $this->_html.=
       HTML::draw_form_box(
        $this->_cp['text_heading'],
        $content,
        "_help_user_toolbar_password",
        $this->_cp['shadow'],
        false,
        $controls
      );
  }

  protected function _draw_initial(){
    if ($this->_command){
      return;
    }
    $this->_html.= $this->_cp['text_initial'];
  }


  protected function _draw_status(){
    if ($this->_msg==''){
      return;
    }
    $this->_html.=
       "<p style='color:"
      .(substr(strip_tags($this->_msg),0,5)=='Error' ? '#ff0000' : '#008000')
      ."'>".$this->_msg."</p>";
  }

  protected function _setup($instance='', $args=array(), $disable_params=false){
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_load_person();
    $this->_setup_do_command();
  }

  protected function _setup_do_command(){
    $this->_command =   get_var('command');
    $this->_pwd =       get_var('change_password');
    $this->_pwd2 =      get_var('change_password2');
    switch ($this->_command) {
      case "Change Password":
        if ($this->_pwd != $this->_pwd2) {
          $this->_msg =	"<b>Error</b>: Both passwords should match";
        }
        else if (strlen($this->_pwd)<5) {
          $this->_msg =	"<b>Error</b>: You must enter a password of at least 5 characters.";
        }
        else {
          $Obj = new User(get_userID());
          $Obj->set_field('PPassword',encrypt(strToLower($this->_pwd)));
          $_SESSION['person']['PPassword'] = $this->_pwd;
          $this->_done = true;
          $this->_msg =	"<b>Success</b>: Your password has been changed.";
        }
      break;
    }

  }

  protected function _setup_load_person(){
    if (!get_userID()){
      $this->_setup_redirect_public();
    }
    $this->_Obj_User = new User(get_userID());
    if (!$this->_Obj_User->exists()){
      $this->_setup_redirect_public();
    }
  }

  protected function _setup_redirect_public(){
    if (!get_userID()){
      header("Location: ".BASE_PATH.trim($this->_cp['page_public'],'/'));
      print "&nbsp;";
      die();
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_CHANGE_PASSWORD;
  }
}
?>