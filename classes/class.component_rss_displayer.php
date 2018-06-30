<?php
  define ("VERSION_COMPONENT_RSS_DISPLAYER","1.0.1");
/*
Version History:
  1.0.1 (2014-01-28)
    1) Added newline and semicolon after JS onload code
  1.0.0 (2013-05-15
    1) Initial release - uses zrssfeed and Google Feeds API
*/
class Component_RSS_Displayer extends Component_Base {

  public function __construct(){
    $this->_ident =            "rss_displayer";
    $this->_parameter_spec =   array(
        'limit' =>          array('match' => 'range|0,n',		'default'=>'3',         'hint'=>'0..n'),
        'linktarget' =>     array('match' => '',		        'default'=>'_blank',    'hint'=>'Target window for links to open in'),
        'offset' =>         array('match' => 'range|0,n',		'default'=>'1',         'hint'=>'0..n'),
        'sort' =>           array('match' => 'enum|,title,date','default'=>'date',      'hint'=>'|title|date'),
        'sortasc' =>        array('match' => 'range|0,1',       'default'=>'0',         'hint'=>'0|1'),
        'show_content' =>   array('match' => 'range|0,1',		'default'=>'1',         'hint'=>'0|1'),
        'show_date' =>      array('match' => 'range|0,1',		'default'=>'1',         'hint'=>'0|1'),
        'show_title' =>     array('match' => 'range|0,1',		'default'=>'0',         'hint'=>'0|1'),
        'show_snippet' =>   array('match' => 'range|0,1',		'default'=>'1',         'hint'=>'0|1'),
        'url' =>            array('match' => '',				'default'=>'',          'hint'=>'URL for site')
    );
  }

  public function draw($instance='',$args=array(),$disable_params=false){
    static $shown_js =  false;
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    if (!$shown_js){
      Page::push_content(
        "javascript_top",
        "<script type=\"text/javascript\" src=\"".BASE_PATH."sysjs/zrssfeed\"></script>"
      );
      $shown_js = true;
    }
    Page::push_content(
      "javascript",
        "function ".$this->_safe_ID."_init(){\n"
       ."  \$('#".$this->_safe_ID."').rssfeed(\n"
       ."    '".$this->_cp['url']."',\n"
       ."    {\n"
       ."       content: ".($this->_cp['show_content']=='1' ? 'true' : 'false').",\n"
       ."       date: ".($this->_cp['show_date']=='1' ? 'true' : 'false').",\n"
       ."       header: ".($this->_cp['show_title']=='1' ? 'true' : 'false').",\n"
       ."       limit: ".$this->_cp['limit'].",\n"
       ."       linktarget: '".$this->_cp['linktarget']."',\n"
       ."       offset: ".$this->_cp['offset'].",\n"
       ."       snippet: ".($this->_cp['show_snippet']=='1' ? 'true' : 'false').",\n"
       ."       sort: '".$this->_cp['sort']."',\n"
       ."       sortasc: ".($this->_cp['sortasc']=='1' ? 'true' : 'false').",\n"
       ."       titletag: 'span'\n"
       ."    }\n"
       ."  );\n"
       ."}\n"
    );
    Page::push_content(
        "javascript_onload",
        "  ".$this->_safe_ID."_init();\n"
    );
    Page::push_content(
        "style",
        "  #".$this->_safe_ID." ul { list-style: none; margin: 1em; padding: 0; }\n"
       ."  #".$this->_safe_ID." ul li { padding: 0.5em 0; }\n"
       ."  #".$this->_safe_ID." ul li div { font-size: 80%; color: #444; }\n"
       ."  #".$this->_safe_ID." ul li p { margin: 0; }\n"
    );
    $this->_html.=  "<div id=\"".$this->_safe_ID."\">".$this->_cp['url']."</div>";
    return $this->_html;
  }

  public function get_version(){
    return VERSION_COMPONENT_RSS_DISPLAYER;
  }
}
?>