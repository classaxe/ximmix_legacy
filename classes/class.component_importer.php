<?php
define ("VERSION_COMPONENT_IMPORTER","1.0.2");
/*
Version History:
  1.0.2 (2012-01-23)
    1) Now uses _setup() and _setup_load_parameters() in base class
  1.0.1 (2012-01-23)
    1) Removed _draw_control_panel() - inherits instead
  1.0.0 (2011-03-01)
    1) Initial release
*/
class Component_Importer extends Component_Base {

  public function __construct(){
    $this->_ident =             "importer";
    $this->_parameter_spec = array(
      'destination' =>  array('match' => 'enum|contact,person',     'default' =>'person',   'hint'=>'What type of object to import - contact|person')
    );
  }

  function draw($instance='', $args=array(), $disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    return $this->_render();
  }

  private function _render(){
    return
       "<div class=\"importer\" id=\"".$this->_safe_ID."\">\n"
      .$this->_html
      ."</div>\n"
      ."<div class='clear'>&nbsp;</div>\n";
  }

  public function get_version(){
    return VERSION_COMPONENT_IMPORTER;
  }

}
?>