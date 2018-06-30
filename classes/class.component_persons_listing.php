<?php
  define ("VERSION_COMPONENT_PERSONS_LISTING","1.0.1");
/*
Version History:
  1.0.1 (2014-01-17)
    1) Change to Component_Persons_Listing::_draw_entry() map info-window code
       to use new ecc_map.point.i() helper function

  (Older version history in class.component_persons_listing.txt)
*/
class Component_Persons_Listing extends Component_Base {
  protected $_records = array();

  public function __construct(){
    $this->_ident =             "persons_listing";
    $this->_parameter_spec =    array(
      'filter_category' =>          array('match' => 'html',            'default' => '',                                'hint' => 'Category to filter by'),
      'filter_sp' =>                array('match' => 'html',            'default' => '',                                'hint' => 'CSV list of States and Provinces to filter on'),
      'group_by' =>                 array('match' => 'enum|none,city',  'default' => 'city',                            'hint' => 'none|city - Optionally group by field'),
      'jumplinks_header' =>         array('match' => 'html',            'default' => '<b><i>Towns and Cities</i></b>',  'hint' => 'Heading to place above jumplinks'),
      'persons_map_instance' =>     array('match' => 'html',            'default' => '',                                'hint' => 'To use this control in conjunction with a person_map, specify ident'),
      'show_home' =>                array('match' => 'enum|0,1',        'default' => '1',                               'hint' => '0|1 - Whether or not to include home addresses'),
      'show_home_email' =>          array('match' => 'enum|0,1',        'default' => '0',                               'hint' => '0|1 - Whether or not to include home addresses'),
      'show_home_phone' =>          array('match' => 'enum|0,1',        'default' => '0',                               'hint' => '0|1 - Whether or not to include home phone number'),
      'show_work' =>                array('match' => 'enum|0,1',        'default' => '1',                               'hint' => '0|1 - Whether or not to include work addresses'),
      'show_work_email' =>          array('match' => 'enum|0,1',        'default' => '0',                               'hint' => '0|1 - Whether or not to include home addresses'),
      'show_work_phone' =>          array('match' => 'enum|0,1',        'default' => '0',                               'hint' => '0|1 - Whether or not to include work phone number'),
      'show_jumplinks' =>           array('match' => 'enum|0,1',        'default' => '1',                               'hint' => '0|1 - Whether or not to show jumplinks when grouping'),
      'show_jumplinks_header' =>    array('match' => 'enum|0,1',        'default' => '1',                               'hint' => '0|1 - Whether or not to show jumplinks header'),
    );
  }

  function draw($instance='',$args=array(),$disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_draw_css();
    $this->_draw_jumplinks();
    $this->_draw_groupings();
    return $this->_render();
  }

  protected function _draw_groupings(){
    switch ($this->_cp['group_by']){
      case 'city':
        $this->_draw_grouped_by_city();
      break;
      case 'none':
        $this->_draw_grouped_by_none();
      break;
    }
  }

  protected function _draw_grouped_by_city(){
    $old_city = '';
    foreach ($this->_records as $r){
      if ($r['c']!=$old_city){
        $this->_html.=
           ($old_city!='' ? "</ul>" : "")
          ."<h3>"
          ."<a name='".str_replace(' ','-',$r['c'])."'></a>"
          .$r['c']
          .($this->_cp['show_jumplinks'] ? " <sup><a href='#".$this->_safe_ID."_listing'>^Top</a></sup>\n" : "")
          ."</h3>\n"
          ."<ul>";
        $old_city = $r['c'];
      }
      $this->_draw_entry_addresses($r);
    }
    $this->_html.= "</ul>";
  }

  protected function _draw_grouped_by_none(){
    $this->_html.= "<ul>";
    foreach ($this->_records as $r){
      $this->_draw_entry_addresses($r);
    }
    $this->_html.= "</ul>";
  }

  protected function _draw_css(){
    Page::push_content(
      'style',
       "#".$this->_safe_ID." p{\n"
      ."  margin:1em auto; text-align: center; width: 60%;\n"
      ."}"
      ."#".$this->_safe_ID." a{\n"
      ."  color: #00f; text-decoration: none;\n"
      ."}"
      ."#".$this->_safe_ID." a:hover{\n"
      ."  text-decoration: underline;\n"
      ."}"
      ."#".$this->_safe_ID." h3{\n"
      ."  margin:1em 0 0 0;\n"
      ."}"
      ."#".$this->_safe_ID." ul{\n"
      ."  margin:0;\n"
      ."}"
      ."#".$this->_safe_ID." ul li{\n"
      ."  margin:0 0 1em 0;\n"
      ."}"
      ."#".$this->_safe_ID." ul li label{\n"
      ."  margin:0 0.5em 0 0;\n"
      ."}"
    );
  }

  protected function _draw_entry($record,$address_type,$suffix){
    if (!$record[$address_type]){
      return;
    }
    $popup_size = get_popup_size($record['type']);
    $lines = explode("\r\n",trim(str_replace("\r\n\r\n","\r\n",$record[$address_type]),"\r\n"));
    $person_map_instance = get_js_safe_ID('google_map_persons_map_'.$this->_cp['persons_map_instance']);
    $this->_html.=
       "<li><b>"
      .($this->_cp['persons_map_instance'] ?
          "<a href='#".$person_map_instance."'"
         ." onclick=\"ecc_map.point.i(_".$person_map_instance."_marker_".$record['ID']."_".$address_type.")\""
         .">"
       :
         ""
       )
      .array_shift($lines).($suffix ? " ".$suffix : "")
      .($this->_isAdmin || $this->_cp['persons_map_instance'] ? "</a>" : "")
      .($this->_isAdmin ?
          " <a href='#'"
         ." onclick=\"details('".$record['type']."',".$record['ID'].",".$popup_size['h'].",".$popup_size['w'].",'','');"
         ."return false;\"><i>[Edit...]</i></a>"
        :
          ""
       )
      ."</b>"
      ."<br />"
      .implode("<br />\n",$lines)."<br />\n"
      .($address_type=='a' && $this->_cp['show_home_email'] && strpos($record['AEmail'],'@')!==false ? '<label>E:</label>'.$record['AEmail']."<br />\n" : '')
      .($address_type=='a' && $this->_cp['show_home_phone'] && $record['ATelephone'] ? '<label>H:</label>'.$record['ATelephone']."<br />\n" : '')
      .($address_type=='w' && $this->_cp['show_work_email'] && strpos($record['WEmail'],'@')!==false ? '<label>E:</label>'.$record['WEmail']."<br />\n" : '')
      .($address_type=='w' && $this->_cp['show_work_phone'] && $record['WTelephone'] ? '<label>W:</label>'.$record['WTelephone']."<br />\n" : '')
      ."</li>";
  }

  protected function _draw_entry_addresses($r){
    if ($this->_cp['show_home'] && $this->_cp['show_work']){
      $this->_draw_entry($r,'a','(Home)');
      $this->_draw_entry($r,'w','(Work)');
      return;
    }
    if ($this->_cp['show_home']){
      $this->_draw_entry($r,'a','');
      return;
    }
    if ($this->_cp['show_work']){
      $this->_draw_entry($r,'w','');
      return;
    }
  }

  protected function _draw_jumplinks(){
    if (!$this->_cp['show_jumplinks']){
      return;
    }
    if ($this->_cp['group_by']!='city'){
      return;
    }
    $this->_html.=
       "<p id='".$this->_safe_ID."_listing'>".($this->_cp['show_jumplinks_header'] ? $this->_cp['jumplinks_header']."<br />" : "");
    $old_city = '';
    $city_links = array();
    foreach ($this->_records as $r){
      if ($r['c']!=$old_city){
        $city_links[] = "<a href=\"#".str_replace(' ','-',$r['c'])."\">".$r['c']."</a> ";
        $old_city = $r['c'];
      }
    }
    $this->_html.= implode(' | ',$city_links);
    $this->_html.=
       "</p>";

  }

  protected function _render(){
    return
       "<div id=\"".$this->_safe_ID."\">\n"
      .$this->_html
      ."</div>\n";
  }

  protected function _setup($instance,$args,$disable_params){
    parent::_setup($instance,$args,$disable_params);
    $this->_setup_load_user_rights();
    $this->_setup_load_records();
  }

  protected function _setup_load_records(){
    $sp_csv = implode("','",explode(',',str_replace(' ','',$this->_cp['filter_sp'])));
    $Obj_Person = new Person;
    $sql =
       "SELECT\n"
      ."  *,\n"
      ."  IF(`ACity`!='', `ACity`, `WCity`) `c`,\n"
      ."  `AMap_description` `a`,\n"
      ."  `WMap_description` `w`\n"
      ."FROM\n"
      ."  `person`\n"
      ."WHERE\n"
      .($this->_cp['filter_category'] && $this->_cp['filter_category']!='*' ?
          "  `category` LIKE \"%".$this->_cp['filter_category']."%\" AND\n"
       :
          ""
       )
      .($this->_cp['filter_sp'] && $this->_cp['filter_sp']!='*' ?
          ($this->_cp['show_home']  && !$this->_cp['show_work'] ? "  `ASpID` IN('".$sp_csv."') AND\n" : "")
         .(!$this->_cp['show_home'] &&  $this->_cp['show_work'] ? "  `WSpID` IN('".$sp_csv."') AND\n" : "")
         .($this->_cp['show_home']  &&  $this->_cp['show_work'] ? " (`ASpID` IN('".$sp_csv."') OR `WSpID` IN('".$sp_csv."')) AND\n" : "")
       :
          ""
       )
      ."  `systemID` = ".SYS_ID."\n"
      ."ORDER BY\n"
      ."  `c`";
    $this->_records = $Obj_Person->get_records_for_sql($sql);
  }

  public function get_version(){
    return VERSION_COMPONENT_PERSONS_LISTING;
  }
}
?>