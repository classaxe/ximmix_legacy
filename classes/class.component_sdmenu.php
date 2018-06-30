<?php
define ("VERSION_COMPONENT_SDMENU","1.0.5");
/*
Version History:
  1.0.5 (2014-01-28)
    1) Newline after JS code in Component_SDMenu::_draw_js_onload()
  1.0.4 (2012-01-23)
    1) Changes to _setup() to harness protected method in Component_Base class
    2) Removed local implementation of _setup_load_parameters()
  1.0.3 (2012-01-23)
    1) Removed _draw_control_panel() - inherits instead
  1.0.2 (2011-10-02)
    1) Now includes _setup_fix_ie7_spaces_bug() to remove spaces between elements
       prevening extra gaps showing
  1.0.1 (2011-09-24)
    1) Big changes to work with bare minimum of modification to input tree
    2) Removed unused support for 'memorize' in JS
    3) Added cps to govern speed and 'one-only' selection of submenu items
  1.0.0 (2011-09-24)
    1) Initial release from code in Component class, with major cleanup
*/
class Component_SDMenu extends Component_Base {
  private $_Obj_NS;
  private $_suiteID;

  function __construct(){
    $this->_ident =             "sd_menu";
    $this->_parameter_spec =   array(
      'border_top' =>           array('match' => 'enum|0,1',        'default'=>1,                             'hint'=>'0|1'),
      'border_bottom' =>        array('match' => 'enum|0,1',        'default'=>1,                             'hint'=>'0|1'),
      'buttonsuite_name' =>     array('match' => '',                'default'=>'Home',                        'hint'=>'name of buttonsuite to render'),
      'main_bgcolor' =>         array('match' => 'hex3|#abbbbd',    'default'=>'#abbbbd',                     'hint'=>'#RGB'),
      'main_border' =>          array('match' => 'hex3|#ffffff',    'default'=>'#ffffff',                     'hint'=>'#RGB'),
      'main_color' =>           array('match' => 'hex3|#ffffff',    'default'=>'#ffffff',                     'hint'=>'#RGB'),
      'main_font_family' =>     array('match' => '',                'default'=>'Verdana, Arial, sans-serif',  'hint'=>'CSS Font list'),
      'main_font_size' =>       array('match' => '',                'default'=>11,                            'hint'=>'font size in px - no units'),
      'one_only' =>             array('match' => 'enum|0,1',        'default'=>0,                             'hint'=>'0|1'),
      'speed' =>                array('match' => 'range|0,10',      'default'=>3,                             'hint'=>'0-10 speed for opening and closing'),
      'sub_bgcolor_active' =>   array('match' => 'hex3|#dae1e2',    'default'=>'#dae1e2',                     'hint'=>'#RGB'),
      'sub_bgcolor_normal' =>   array('match' => 'hex3|#ffffff',    'default'=>'#ffffff',                     'hint'=>'#RGB'),
      'sub_bgcolor_over' =>     array('match' => 'hex3|#e0f0f0',    'default'=>'#e0f0f0',                     'hint'=>'#RGB'),
      'sub_border' =>           array('match' => 'hex3|#abbbbd',    'default'=>'#abbbbd',                     'hint'=>'#RGB'),
      'sub_color_active' =>     array('match' => 'hex3|#3c4845',    'default'=>'#3c4845',                     'hint'=>'#RGB'),
      'sub_color_normal' =>     array('match' => 'hex3|#3c4845',    'default'=>'#3c4845',                     'hint'=>'#RGB'),
      'sub_color_over' =>       array('match' => 'hex3|#3c4845',    'default'=>'#3c4845',                     'hint'=>'#RGB'),
      'sub_font_family' =>      array('match' => '',                'default'=>'Verdana, Arial, sans-serif',  'hint'=>'CSS Font list'),
      'sub_font_size' =>        array('match' => '',                'default'=>10,                            'hint'=>'font size in px - no units')
    );
  }

  function draw($instance='',$args=array(),$disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel();
    $this->_draw_css();
    $this->_draw_js_onload();
    if (!$this->_suiteID) {
      $this->_html.=
         "<div style='border:1px solid red; background: #ffe0e0;padding: 0.25em;'>"
        ."<strong>Error for ".__CLASS__."::".__FUNCTION__."()</strong><br />\n"
        ."Specify a valid suite name for the control to render -<br />\n"
        ."'<b>".$this->_cp['buttonsuite_name']."</b>' is not a valid suite name."
        ."</div>";
      return $this->_html;
    }
    $this->_html.= $this->_tree;
    return $this->_html;
  }

  private function _draw_css(){
    Page::push_content(
      'style',
       "/* Style for ".$this->_safe_ID." */\n"
      ."#".$this->_safe_ID." {\n"
      .($this->_cp['border_bottom'] ?
         "  background: url(".BASE_PATH."img/sysimg/_sdmenu_bottom.gif,".trim($this->_cp['main_bgcolor'],'#').") no-repeat right bottom;\n"
        ."  padding-bottom: 10px;\n"
       : ""
       )
      ."  width: 180px;\n"
      ."}\n"
      ."#".$this->_safe_ID." .border_top {\n"
      .($this->_cp['border_top'] ?
         "  background: url(".BASE_PATH."img/sysimg/_sdmenu_top.gif,".trim($this->_cp['main_bgcolor'],'#').") no-repeat right top;\n"
        ."  height: 7px;\n"
       : "  height: 0px;\n"
       )
      ."}\n"
      ."#".$this->_safe_ID." li {\n"
      ."  background: ".$this->_cp['main_bgcolor'].";\n"
      ."}\n"
      ."#".$this->_safe_ID." li span {\n"
      ."  border-bottom: 1px solid ".$this->_cp['main_border'].";\n"
      ."  color: ".$this->_cp['main_color'].";\n"
      ."  font: bold ".$this->_cp['main_font_size']."px ".$this->_cp['main_font_family'].";\n"
      ."}\n"
      ."#".$this->_safe_ID." li.collapsed {\n"
      ."  height: ".($this->_cp['main_font_size']+12)."px;\n"
      ."}\n"
      ."#".$this->_safe_ID." li a {\n"
      ."  background: ".$this->_cp['sub_bgcolor_normal'].";\n"
      ."  color:  ".$this->_cp['sub_color_normal'].";\n"
      ."  border: 1px solid ".$this->_cp['sub_border'].";\n"
      ."  border-top: none;\n"
      ."  font: normal ".$this->_cp['sub_font_size']."px ".$this->_cp['sub_font_family'].";\n"
      ."}\n"
      ."#".$this->_safe_ID." li a.current {\n"
      ."  background-color: ".$this->_cp['sub_bgcolor_active'].";\n"
      ."  color: ".$this->_cp['sub_color_active'].";\n"
      ."}\n"
      ."#".$this->_safe_ID." li a:hover {\n"
      ."  background:  ".$this->_cp['sub_bgcolor_over']." url(".BASE_PATH."img/sysimg/_sdmenu_link_arrow.gif) no-repeat right center;\n"
      ."  color: ".$this->_cp['sub_color_over'].";\n"
      ."}\n"
    );
  }

  private function _draw_js_onload(){
    Page::push_content(
      'javascript_onload',
      "  new SDMenu('".$this->_safe_ID."',".$this->_cp['speed'].",".$this->_cp['one_only'].").init();\n"
    );
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_Obj_NS =            new Navsuite;
    $this->_setup_load_navsuiteID();
    $this->_setup_load_tree();
  }

  private function _setup_fix_ie7_spaces_bug(){
    // see http://stackoverflow.com/questions/2923735/css-ul-li-gap-in-ie7
    $this->_tree = str_replace(
      array(
        "</li>\n  <li>",
        "</li>\n    <li>"
      ),
      array(
        "</li><li>",
        "</li><li>"
      ),
      $this->_tree
    );
//    print $this->_tree;
  }

  private function _setup_load_navsuiteID(){
    $this->_suiteID =           $this->_Obj_NS->get_ID_by_name($this->_cp['buttonsuite_name']);
  }

  private function _setup_load_tree(){
    if (!$this->_suiteID){
      return;
    }
    $this->_tree = $this->_Obj_NS->get_tree(false,$this->_suiteID,0,true);
    $this->_tree = substr($this->_tree, 5); // strip leading <ul>\n
    $this->_tree =
       "<ul id=\"".$this->_safe_ID."\" class=\"sdmenu\">"
      ."<li class=\"border_top\">&nbsp;</li>\n"
      .$this->_tree;
    $this->_setup_fix_ie7_spaces_bug();
  }


  public function get_version(){
    return VERSION_COMPONENT_SDMENU;
  }
}
?>