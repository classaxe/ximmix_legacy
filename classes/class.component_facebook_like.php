<?php
define ("VERSION_COMPONENT_FACEBOOK_LIKE","1.0.4");
/*
Version History:
  1.0.4 (2012-11-02)
    1) Bug fix for jquery call - was unescaped
  1.0.3 (2012-10-28)
    1) Component_Facebook_Like::_draw_content() now uses jquery to select target
       element, not prototypejs
  1.0.2 (2012-08-16)
    1) Uses new API method and supports new options, such as 'show faces'
  1.0.1 (2012-02-29)
    1) Component_Facebook_Like::_draw_content() changed frameborder to 0
  1.0.0 (2012-01-25)
    1) Initial release
*/

class Component_Facebook_Like extends Component_Base {
  public function __construct(){
    global $system_vars;
    $this->_ident =             "facebook_like";
    $this->_parameter_spec =    array(
      'layout' =>       array('match' => 'enum|standard,button_count,box_count',    'default'=>'standard',  'hint'=>'Style of layout to use'),
      'send_button' =>  array('match' => 'enum|0,1',                                'default'=>1,           'hint'=>'Whether or not to show send button'),
      'show_faces' =>   array('match' => 'enum|0,1',                                'default'=>0,           'hint'=>'Whether or not to show faces of friends'),
      'verb' =>         array('match' => 'enum|like,recommend',                     'default'=>'like',      'hint'=>'Whether to use term like or recommend'),
      'width' =>        array('match' => 'range|1,n',                               'default'=>400,         'hint'=>'1..n or blank - width in px to resize')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel();
    $this->_draw_content();
    return $this->_html;
  }

  protected function _draw_content(){
    global $page_vars;
    Page::push_content('javascript',
       "function fb_like_button(key){\n"
      ."  var div, fjs, js;\n"
      ."  fjs = document.getElementsByTagName('script')[0];\n"
      ."  div = \$J('#fb_like')[0];\n"
      ."  div.setAttribute('data-action',\"".$this->_cp['verb']."\");\n"
      ."  div.setAttribute('data-href',\"".htmlentities($page_vars['absolute_URL'])."\");\n"
      ."  div.setAttribute('data-send',\"".($this->_cp['send_button'] ? "true" : "false")."\");\n"
      ."  div.setAttribute('data-layout',\"".$this->_cp['layout']."\");\n"
      ."  div.setAttribute('data-width',".$this->_cp['width'].");\n"
      ."  div.setAttribute('data-show-faces',\"".($this->_cp['show_faces'] ? "true" : "false")."\");\n"
      ."  var js, fjs = document.getElementsByTagName('script')[0];\n"
      ."  if (document.getElementById('facebook-jssdk')){return;}\n"
      ."  js = document.createElement('script');\n"
      ."  js.id = 'facebook-jssdk';\n"
      ."  js.src = \"//connect.facebook.net/en_US/all.js#xfbml=1&appId=\"+key;\n"
      ."  fjs.parentNode.insertBefore(js, fjs);\n"
      ."};\n"
    );
    Page::push_content('javascript_onload',
      "  fb_like_button('2393249470');\n"
    );
    Page::push_content('body_top',
      "<div id=\"fb-root\"></div>"
    );
    $this->_html.= "<div id=\"fb_like\" class=\"fb-like\"></div>";
  }

  public function get_version(){
    return VERSION_COMPONENT_FACEBOOK_LIKE;
  }
}
?>