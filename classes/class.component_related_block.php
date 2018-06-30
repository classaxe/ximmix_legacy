<?php
define ("VERSION_COMPONENT_RELATED_BLOCK","1.0.4");
/*
Version History:
  1.0.4 (2012-01-23)
    1) Changes to _setup() to harness protected method in Component_Base class
    2) Removed local implementation of _setup_load_parameters()
  1.0.3 (2012-01-23)
    1) Removed _draw_control_panel() - inherits instead
  1.0.2 (2011-12-24)
    1) Component_Related_Block::_setup_load_source_object() now uses actual
       viewed object type -
       was HARD-CODED as 'New_Item' - Ugh!
  1.0.1 (2011-11-10)
    1) More work on preparing this component to operate using Block Layouts
    2) Bug fix - was using wrong component to establish class release version 
  1.0.0 (2011-10-26)
    1) Initial release - moved in from Displayable_Item::draw_keywords_block()
*/

class Component_Related_Block extends Component_Base {
  private $_keywords_arr;
  private $_related_siteIDs;
  private $_Obj_Source;

  public function __construct(){
    global $system_vars;
    $this->_ident =             "draw_related";
    $this->_parameter_spec =    array(
      'related_limit' =>   array('default'=>5, 'hint'=>'0..n'),
      'show_keywords' =>   array('default'=>1, 'hint'=>'0|1'),
      'show_related' =>    array('default'=>1, 'hint'=>'0|1'),
      'sites_list' =>      array('default'=>$system_vars['URL'], 'hint'=>'csv list of local site URLs')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel();
    if (!$this->_Obj_Source->_get_has_keywords()){
      return $this->_html;
    }
//    y($this->_Obj_Source);die;
    $this->_keywords_arr = $this->_Obj_Source->get_keywords();
    if (!count($this->_keywords_arr)) {
      return $this->_html;
    }
    $keyword_links =    array();
    $keywordIDs_arr =   array();
    $keyword_arr =      array();
    foreach ($this->_keywords_arr as $keyword) {
      $keywordIDs_arr[] = $keyword['ID'];
      $keyword_arr[] = $keyword['keyword'];
    }
    foreach ($this->_keywords_arr as $keyword) {
      $keyword_links[] =
         "<a class='nowrap'"
        ." href=\"".BASE_PATH."tags/".$keyword['keyword']."\""
        ."><b>".$keyword['keyword']."</b></a>"
        ."<span class='num' style='padding-left:0.25em;'>(".$keyword['count'].")</span>";
    }
    $out =
       "<div class='keyword_block clr_b' style='margin-top: 1em;'>\n"
      .$this->_html
      ."  <div class='fl' style='width:3.5em;'><b>Tags:</b></div>\n"
      ."  <div class='fl'>".implode(", ",$keyword_links)."</div>\n"
      ."  <div class='clr_b'></div>";
    if ($this->_cp['show_related']) {
      $related_matches_total = 0;
      $related_arr =
        Keyword::get_related(
          implode(",",$keywordIDs_arr),
          $this->_related_siteIDs,
          $this->_Obj_Source->_get_assign_type(),
          $this->_Obj_Source->_get_ID(),
          $this->_cp['related_limit'],
          $related_matches_total
        );
      if ($related_arr && count($related_arr)) {
        $related_links = array();
        foreach ($related_arr as $related) {
          $systemID =   $related['systemID'];
          $local =      $systemID==SYS_ID;
          $title =      ($related['title'] ? $related['title'] : "(edit)");
          $text =       context($title,'&nbsp;',100);
          $type =       $related['type'];
          $hits =       $related['hits'];
          $matched =    $related['matched'];
          $relevance =  (int)(100*$hits/count($keywordIDs_arr));
          $URL =        $this->_Obj_Source->get_URL($related);
          $local =      $systemID==SYS_ID;
          $related_links[] =
             ($local ?
               ""
             :
                " <a href=\"".$related['systemURL']."\" title=\"Content from ".$related['systemTitle']." - click to visit\" rel='external'>"
               ."[*]"
               ."</a> "
             )
            ."<a href=\"".$URL."\""
            .($local ? "" : " rel='external'")
            .($title!=strip_tags($text) ? " title=\"".$related['title']."\"" : "")
            .">".$text."</a>"
            ." <i>(<span title=\"Matched:".$matched."\">".$relevance."%</span>)</i>"
            ;
        }
        $out.=
           "  <div style='padding-top:0.25em;'><b>Related:</b>"
          .($related_matches_total>count($related_links) ?
              " <a href=\"".BASE_PATH."tags/".implode(",",$keyword_arr)."\">"
             ."<b>See all</b></a> <i>(".(1+$related_matches_total)." - includes this ".strtolower($this->_Obj_Source->_get_object_name()).")</i>"
            : ""
           )
          ."<ul style='margin:0.5em 2em'>\n"
          ."  <li>"
          .implode("</li>\n  <li>",$related_links)."</li>\n"
          ."</ul>"
          ."</div>\n";
      }
    }
    $out.= "</div><div class='keyword_block_spacer'></div>";
    return $out;
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_load_source_object();
    $this->_setup_get_related_site_IDs();
  }

  private function _setup_load_source_object(){
    global $page_vars;
//    $this->_Obj_Source = new News_Item;//$page_vars['object_type'];
    $this->_Obj_Source = new $page_vars['object_type'];
    $this->_Obj_Source->_set_ID($page_vars['ID']);
  }

  private function _setup_get_related_site_IDs(){
    global $system_vars;
    if ($this->_cp['sites_list']==$system_vars['URL']) {
      $this->_related_siteIDs = SYS_ID;
    }
    else {
      $this->_related_siteIDs = System::get_IDs_for_URLs($this->_cp['sites_list']);
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_RELATED_BLOCK;
  }
}
?>