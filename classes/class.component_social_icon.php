<?php
define ("VERSION_COMPONENT_SOCIAL_ICON","1.0.1");
/*
Version History:
  1.0.1 (2013-07-30)
    1) SEO improvments with inclusion of inline height and width attributes
  1.0.0 (2012-12-08)
    1) Initial release

*/
class Component_Social_Icon extends Component_Base {
  protected $_html =    '';

  public function __construct(){
    $this->_ident =             "draw_social_icon";
    $this->_types_csv =         "delicious,digg,email,facebook,flickr,google,linkedin,rss,skype,stumbleupon,twitter,vimeo,youtube";
    $this->_parameter_spec =    array(
      'disable' =>      array('match' => 'enum|0,1',                'default'=>'0',         'hint'=>'0|1'),
      'icon' =>         array('match' => 'enum|'.$this->_types_csv, 'default'=>'facebook',  'hint'=>'Icon to use - delicious digg email facebook flickr google linkedin rss skype stumbleupon twitter vimeo youtube'),
      'size' =>         array('match' => 'enum|16,32',              'default'=>'16',        'hint'=>'Size of icon - 16 or 32'),
      'tooltip' =>      array('match' => '',                        'default'=>'Facebook',  'hint'=>'Text to place on Tooltip'),
      'url' =>          array('match' => '',                        'default'=>'http://',   'hint'=>''),
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_draw();
    return $this->_html;
  }

  protected function _draw(){
    $this->_draw_control_panel(true);
    if (!$this->_cp['disable']) {
      $this->_draw_css();
      $this->_html.=
         "<a href=\"".$this->_cp['url']."\" rel=\"external\" class='noprint social_icon_".$this->_cp['size']."' title=\"".$this->_cp['tooltip']."\">"
        ."<img id=\"".$this->_js_safe_ID."\" src='".BASE_PATH."img/spacer' class=''"
        ." alt=\"".$this->_cp['tooltip']."\""
        ." height=\"".$this->_cp['size']."\""
        ." width=\"".$this->_cp['size']."\""
        ."/></a>"
         ;
    }
  }

  protected function _draw_css(){
    static $sections = array();
    if (!isset($sections['base_'.$this->_cp['size']])){
      $this->_css.=
         ".social_icon_".$this->_cp['size']." img {\n"
        ."  width:".$this->_cp['size']."px; height:".$this->_cp['size']."px; border: 0;\n"
        ."  background-image: url(".BASE_PATH."img/sysimg/social_icons_".$this->_cp['size']."x".$this->_cp['size'].".png);\n"
        ."}\n";
      $sections['base_'.$this->_cp['size']] = true;
    }
    if (!isset($sections['base_'.$this->_cp['size'].'_'.$this->_cp['icon']])){
      $idx =    array_search($this->_cp['icon'],explode(',',$this->_types_csv));
      $offset = -1*$idx*$this->_cp['size'];
      $this->_css.=
         ".social_icon_".$this->_cp['size']." #".$this->_js_safe_ID."       { background-position:".$offset."px 0px;}\n"
        .".social_icon_".$this->_cp['size']." #".$this->_js_safe_ID.":hover { background-position:".$offset."px ".(-1*$this->_cp['size'])."px;}\n";
      $sections['base_'.$this->_cp['size'].'_'.$this->_cp['icon']] = true;
    }
    Page::push_content('style',$this->_css);
  }

  public function get_version(){
    return VERSION_COMPONENT_SOCIAL_ICON;
  }

}
?>