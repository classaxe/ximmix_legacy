<?php
  define ("VERSION_COMPONENT_PASSWORD_PROTECT","1.0.3");
/*
Version History:
  1.0.3 (2014-01-28)
    1) Newline and semicolon after js in Component_Password_Protect::_draw_js()

  (Older version history in class.component_collection_viewer.txt)
*/
class Component_Password_Protect extends Component_Base {

  public function __construct(){
    $this->_ident =     "password_protect";
    $this->_parameter_spec = array(
      'head_text' =>            array('match' => '',            'default'=>'Sign In',        'hint'=>'Text to display at top of Password challenge box'),
      'page' =>                 array('match' => '',            'default'=>'',               'hint'=>'Name of page containing real content'),
      'pre_text' =>             array('match' => '',            'default'=>'',               'hint'=>'Text to display above dialog if not authenticated'),
      'shadow' =>               array('match' => 'enum|0,1',    'default'=>'0',              'hint'=>'0|1'),
      'signin_label' =>         array('match' => '',            'default'=>'Sign In',        'hint'=>'Text to place on signin button'),
      'password' =>             array('match' => '',            'default'=>'Sign In',        'hint'=>'Correct password'),
      'password_label' =>       array('match' => '',            'default'=>'Password',       'hint'=>'Label for Password field'),
    );
  }

  function draw($instance='', $args=array(), $disable_params=false) {
    // Used with roof password page -
    // sets a cookie to allow access for the remainder of the session
    global $page_vars, $msg, $submode;
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_draw_js();
    $key =              'pwd_session_'.$page_vars['ID'];
    switch(get_var('submode')) {
      case $this->_safe_ID.'_submit':
        if ($_REQUEST['password']==$this->_cp['password']){
//          $expires = time()+60*60; // 1 hour
          setcookie($key,encrypt($this->_cp['password']),0,'/','',0);
          $_COOKIE[$key]=encrypt($this->_cp['password']);
        }
        else {
          $this->_msg = "<b>Error:</b> ".($_REQUEST['password']=='' ? 'You must provide a password.' : 'Invalid Password.');
        }
      break;
    }
    if (isset($_COOKIE[$key]) && $_COOKIE[$key]==encrypt($this->_cp['password'])){
      $Obj_Page =     new Page;
      $Obj_Page->_set_ID($Obj_Page->get_ID_by_name($this->_cp['page']));
      $content = $Obj_Page->get_field('content');
      $this->_html.= ($content===false ? "Error: Page not found - ".$this->_cp['page'] : $content);
      return $this->_html;
    }
    $content =
       "<label for='password'>".$this->_cp['password_label']."</label>\n"
      ."<input type='password' id='password' name='password' size='20' style='width: 120px;'/>"
      ."<div class='controls'>\n"
      ."<input type='submit' onclick=\"geid_set('submode','".$this->_safe_ID.'_submit'."')\" value='".$this->_cp['signin_label']."' style='formButton'/>"
      ."</div>\n";
    $this->_html.=
       $this->_cp['pre_text']
      .$this->_draw_status()
      .HTML::draw_form_box($this->_cp['head_text'], $content, '',$this->_cp['shadow']);
    return $this->_html;
  }

  protected function _draw_js(){
    Page::push_content('javascript_onload',"  geid(\"password\").focus();\n");
  }

  public function get_version(){
    return VERSION_COMPONENT_PASSWORD_PROTECT;
  }
}
?>