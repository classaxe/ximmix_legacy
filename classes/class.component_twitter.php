<?php
define ("VERSION_COMPONENT_TWITTER","1.0.2");
/*
Version History:
  1.0.2 (2012-05-04)
    1) Added Component_Twitter::draw_tweets()
  1.0.1 (2012-04-05)
    1) Changes to Component_Twitter::draw() to exit cleanly if running in
       debug_no_internet mode
  1.0.0 (2011-03-11)
    1) Initial release
*/
class Component_Twitter extends Component_Base {
  public function draw_profile($instance='', $args=array(), $disable_params=false){
    global $system_vars;
    $ident =            "twitter_profile";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'panel_background' => array('match' => 'hex3|#333333',    'default'=>'#333333',   'hint'=>'Background colour for panel'),
      'panel_text' =>       array('match' => 'hex3|#333333',    'default'=>'#ffffff',   'hint'=>'Text colour for panel'),
      'height' =>           array('match' => 'range|0,n',       'default'=>'300',       'hint'=>'Height for panel'),
      'items_background' => array('match' => 'hex3|#000000',    'default'=>'#000000',   'hint'=>'Background colour for items'),
      'items_links' =>      array('match' => 'hex3|#4aed05',    'default'=>'#4aed05',   'hint'=>'Link colour for items'),
      'items_text' =>       array('match' => 'hex3|#333333',    'default'=>'#ffffff',   'hint'=>'Text colour for items'),
      'user' =>             array('match' => '',                'default'=>'twitter',   'hint'=>'Twitter account to link to'),
      'width' =>            array('match' => 'range|0,n',       'default'=>'250',       'hint'=>'Width for panel'),
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if ($system_vars['debug_no_internet']==1){
      $out.="<b>Twitter Panel Offline</b> -<br />No Internet Connection";
      return $out;
    }
    $js_top =       "<script type=\"text/javascript\" src=\"http://widgets.twimg.com/j/2/widget.js\"></script>";
    Page::push_content('javascript_top',$js_top);

    $out.=
       "<script type='text/javascript'>\n"
      ."twitter_profile({\n"
      ."  height: ".$cp['height'].",\n"
      ."  items_background: '".$cp['items_background']."',\n"
      ."  items_links: '".$cp['items_links']."',\n"
      ."  items_text: '".$cp['items_text']."',\n"
      ."  panel_background: '".$cp['panel_background']."',\n"
      ."  panel_text: '".$cp['panel_text']."',\n"
      ."  width:  ".$cp['width'].",\n"
      ."  user: \"".$cp['user']."\"\n"
      ."});\n"
      ."</script>";
    return $out;
  }

  public function draw_tweets($instance='', $args=array(), $disable_params=false){
    global $system_vars;
    $ident =            "tweets";
    $this->_safe_ID =   Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'id' =>               array('match' => '',                'default'=>'twitter',   'hint'=>'Twitter screen name, list (format user/list-name) or hash tag'),
      'callback' =>         array('match' => '',                'default'=>'',          'hint'=>'Function to call when completed (unless timed out)'),
      'count' =>            array('match' => 'range|1,n',       'default'=>'3',         'hint'=>'Number to show'),
      'enableLinks' =>      array('match' => 'enum|0,1',        'default'=>'1',         'hint'=>'Makes links clickable'),
      'ignoreReplies' =>    array('match' => 'enum|0,1',        'default'=>'1',         'hint'=>'Skips tweets starting with @'),
      'newWindow' =>        array('match' => 'enum|0,1',        'default'=>'1',         'hint'=>'Opens links in a new window'),
      'onTimeout' =>        array('match' => '',                'default'=>'',          'hint'=>'Function to call when timeout is reached. Context is set to HTML element tweets were going to be inserted into'),
      'onTimeoutCancel' =>  array('match' => 'enum|0,1',        'default'=>'0',         'hint'=>'Allow script to continue loading even after timeout has fired'),
      'prefix' =>           array('match' => '',                'default'=>'',          'hint'=>'May include template variables'),
      'template' =>         array('match' => '',                'default'=>'<span class="prefix"><img height="16" width="16" src="%user_profile_image_url%" /> <a href="http://twitter.com/%user_screen_name%">%user_name%</a> said: </span> <span class="status">"%text%"</span> <span class="time"><a href="http://twitter.com/%user_screen_name%/statuses/%id_str%">%time%</a></span>', 'hint'=>'Template variables: text, id_str, source, time, created_at, user_name, user_screen_name, user_id_str, user_profile_image_url, user_url, user_location,user_description'),
      'timeout' =>          array('match' => 'range|1000,n',    'default'=>'10000',     'hint'=>'Milliseconds before triggering onTimeout'),
      'withFriends' =>      array('match' => 'enum|0,1',        'default'=>'0',         'hint'=>'Whether to include friends tweets')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $this->_cp =    $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if ($system_vars['debug_no_internet']==1){
      $out.="<b>Twitter Panel Offline</b> -<br />No Internet Connection";
      return $out;
    }
    $js_top =       "<script type=\"text/javascript\" src=\"http://twitterjs.googlecode.com/svn/trunk/src/twitter.min.js\"></script>";
    Page::push_content('javascript_top',$js_top);
    $js_onload =
       "getTwitters(\n"
      ."  '".$this->_safe_ID."_tweets',{\n"
      ."    id: '".$this->_cp['id']."',\n"
      .($this->_cp['callback'] ? "    callback: ".$this->_cp['callback']."\n" : "")
      ."    clearContents: true,\n"
      ."    count: ".$this->_cp['count'].",\n"
      ."    enableLinks: ".($this->_cp['enableLinks'] ? 'true' : 'false').",\n"
      ."    ignoreReplies: ".($this->_cp['ignoreReplies'] ? 'true' : 'false').",\n"
      ."    newWindow: ".($this->_cp['newWindow'] ? 'true' : 'false').",\n"
      .($this->_cp['onTimeout'] ? "    onTimeout: '".$this->_cp['onTimeout']."'\n" : "")
      ."    onTimeoutCancel: ".($this->_cp['onTimeoutCancel'] ? 'true' : 'false').",\n"
      ."    prefix: '".$this->_cp['prefix']."',\n"
      ."    template: '".$this->_cp['template']."',\n"
      ."    timeout: ".$this->_cp['timeout']."\n"
      ."  }\n"
      .");";
    Page::push_content('javascript_onload',$js_onload);
    $out.=
       "<div id=\"".$this->_safe_ID."_tweets\" class=\"css3 twitter_tweets\">\n"
      ."Loading tweets...\n"
      ."</div>";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_TWITTER;
  }
}
?>