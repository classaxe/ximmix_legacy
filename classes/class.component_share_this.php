<?php
  define ("VERSION_COMPONENT_SHARE_THIS","1.0.2");
/*
Version History:
  1.0.2 (2012-11-06)
    1) Removed hidden paragraph for facebook context -
       We now have open graph (og) metatags provided by
       Displayable_Item::_draw_detail_include_og_support()
  1.0.1 (2012-11-05)
    1) Now places hidden paragraph containing referenced content immediately after
       body tag to provide proper context for facebook sharer
    2) Updated component to use common machinery for component controls and setup
  1.0.0 (2011-12-29)
    1) Initial release - moved from Component class
*/
class Component_Share_This extends Component_Base {

  public function __construct(){
    $this->_ident =             "share_this";
    $this->_parameter_spec =    array(
      'delicious' =>    array('match' => 'enum|0,1',		'default'=>1,  'hint'=>'0|1'),
      'digg' =>         array('match' => 'enum|0,1',		'default'=>1,  'hint'=>'0|1'),
      'email' =>        array('match' => 'enum|0,1',		'default'=>1,  'hint'=>'0|1'),
      'explain' =>      array('match' => 'enum|0,1',		'default'=>1,  'hint'=>'0|1'),
      'facebook' =>     array('match' => 'enum|0,1',		'default'=>1,  'hint'=>'0|1'),
      'reddit' =>       array('match' => 'enum|0,1',		'default'=>1,  'hint'=>'0|1'),
      'stumbleupon' =>  array('match' => 'enum|0,1',		'default'=>1,  'hint'=>'0|1')
    );
  }

  public function draw($instance='',$args=array(),$disable_params=false) {
    global $page_vars;
    $this->_setup($instance,$args,$disable_params);
    $this->_html.=      "<div class=\"share_with\" id=\"".$this->_safe_ID."\">\n";
    $this->_draw_control_panel(true);
    $title =    $page_vars['title'];
    $url =      trim($page_vars['absolute_URL'],'/').(isset($page_vars['path_extension']) ? "/".$page_vars['path_extension'] : "");
    $this->_html.=
       "<h3 class='fl'>Share this ".$page_vars['object_name'].":</h3>\n"
      .($this->_cp['explain'] ? "<a class='explain' href=\"javascript:popup_help('_help_user_share_with')\">What's This?</a>" : "")
      ."<div class='clr_b'></div>"
      ."<ul class='share_with'>\n"
      .($this->_cp['email'] ?
         "  <li class='email'>\n"
        ."    <a rel=\"external\" title=\"Email this ".$page_vars['object_name']." to a friend - see 'What's This?' for details\" href=\"".$url."?command=email_to_friend\" onclick=\"return popup_email_to_friend('".$url."?command=email_to_friend');\">\n"
        ."      <img class='icon' alt=\"Email to Friend Icon\" src=\"".BASE_PATH."img/spacer\" />Email</a>\n"
        ."  </li>\n" : "")
      .($this->_cp['delicious'] ?
         "  <li class='delicious'>\n"
        ."    <a rel=\"external\" title=\"[EXTERNAL SITE]\nPost this ".$page_vars['object_name']." to Delicious - see 'What's This?' for details\" href=\"http://del.icio.us/post?url=".urlencode($url)."&amp;title=".urlencode($title)."\">\n"
        ."      <img class='icon' alt=\"Delicious Icon\" src=\"".BASE_PATH."img/spacer\" />Delicious</a>\n"
        ."  </li>\n" : "")
      .($this->_cp['digg'] ?
         "  <li class='digg'>\n"
        ."    <a rel=\"external\" title=\"[EXTERNAL SITE]\nPost this ".$page_vars['object_name']." to Digg - see 'What's This?' for details\" href=\"http://digg.com/submit?url=".urlencode($url)."&amp;title=".urlencode($title)."\">\n"
        ."      <img class='icon' alt=\"Digg Icon\" src=\"".BASE_PATH."img/spacer\" />Digg</a>\n"
        ."  </li>\n" : "")
      .($this->_cp['facebook'] ?
         "  <li class='facebook'>\n"
        ."    <a rel=\"external\" title=\"[EXTERNAL SITE]\nPost this ".$page_vars['object_name']." to Facebook - see 'What's This?' for details\" href=\"http://www.facebook.com/sharer.php?u=".urlencode($url)."\">\n"
        ."      <img class='icon' alt=\"Facebook Icon\" src=\"".BASE_PATH."img/spacer\" />Facebook</a>\n"
        ."  </li>\n" : "")
      .($this->_cp['reddit'] ?
         "  <li class='reddit'>\n"
        ."    <a rel=\"external\" title=\"[EXTERNAL SITE]\nPost this ".$page_vars['object_name']." to reddit - see 'What's This?' for details\" href=\"http://reddit.com/submit?url=".urlencode($url)."&amp;title=".urlencode($title)."\">\n"
        ."      <img class='icon' alt=\"reddit Icon\" src=\"".BASE_PATH."img/spacer\" />reddit</a>\n"
        ."  </li>\n" : "")
      .($this->_cp['stumbleupon'] ?
         "  <li class='stumbleupon'>\n"
        ."    <a rel=\"external\" title=\"[EXTERNAL SITE]\nPost this ".$page_vars['object_name']." to StumbleUpon - see 'What's This?' for details\" href=\"http://www.stumbleupon.com/submit?url=".urlencode($url)."&amp;title=".urlencode($title)."\">\n"
        ."      <img class='icon' alt=\"StmbleUpon Icon\" src=\"".BASE_PATH."img/spacer\" />StumbleUpon</a>\n"
        ."  </li>\n" : "")
      ."</ul>\n"
      ."</div>";
    return $this->_html;
  }

  public function get_version(){
    return VERSION_COMPONENT_SHARE_THIS;
  }
}
?>