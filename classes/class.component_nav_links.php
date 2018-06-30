<?php
define ("VERSION_COMPONENT_NAV_LINKS","1.0.1");
/*
Version History:
  1.0.1 (2012-11-16)
    1) Now cleanly handles issue where there are no buttons to draw links for
  1.0.0 (2012-09-25)
    1) Initial release
*/
class Component_Nav_Links extends Component_Base {
  protected $_buttons;
  public function __construct(){
    $this->_ident =             'nav_links';
    $this->_parameter_spec = array(
      'navsuite_number' =>            array('match' => 'range|1,3',  	'default'=>'1',         'hint'=>'Number of navsuite to use 1-3')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_draw_links();
    return $this->_html;
  }

  protected function _draw_links(){
    if (!$this->_buttons){
      return;
    }
    $this->_html.= "<ul>";
    foreach ($this->_buttons as $button){
      if ($button['visible']){
        $URL = $button['URL'];
        if (substr($URL,0,8)=='./?page='){
          $URL = substr($URL,8);
        }
        $this->_html.=
           "<li><a href=\"".$URL."\""
          .($button['popup'] ? " rel=\"external\"" : "")
          .($button['text2'] ? " title=\"".$button['text2']."\"" : "")
          .">"
          .$button['text1']
          ."</a></li>\n";
      }
    }
    $this->_html.= "</ul>";
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_load_nav();
  }

  protected function _setup_load_nav(){
    global $page_vars;
    $index = 'navsuite'.$this->_cp['navsuite_number'].'ID';
    $Obj_NS = new Navsuite($page_vars[$index]);
    $this->_buttons = $Obj_NS->get_buttons();
  }

  public function get_version(){
    return VERSION_COMPONENT_NAV_LINKS;
  }
}
?>