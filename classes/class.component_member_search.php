<?php
  define ("VERSION_COMPONENT_MEMBER_SEARCH","1.0.12");
/*
Version History:
  1.0.12 (2014-03-28)
    1) Now has additional CPs:
         'filter_type'
         'show_range_ring'
         'show_start_ring'
    2) Component_Member_Search::_get_query_limit_for_filter() now filters on type
    2) Component_Member_Search::_setup_lookup_search_location() now remembers
       search_area to provide for range ring indicating certainty of search location

  (Older version history in class.component_member_search.txt)
*/
class Component_Member_Search extends Component_Base {
  protected $_Obj_Map;
  protected $_Obj_Person;
  protected $_records =     false;
  protected $_search_lat =  false;
  protected $_search_lon =  false;
  protected $_search_area = false;
  protected $_selector_city_sql;
  protected $_selector_sp_sql;

  const lst_country_ID = 8;
  const lst_sp_ID = 9;

  public function __construct(){
    $this->_ident =            "member_search";
    $this->_parameter_spec =   array(
      'block_layout' =>             array('match' => '',                            'default'=>'Person',      'hint' => 'Name of Block Layout to use'),
      'filter_category' =>          array('match' => 'html',                        'default'=>'',            'hint' => 'Category to filter by'),
      'filter_group' =>             array('match' => 'html',                        'default'=>'',            'hint' => 'Group to search for members in'),
      'filter_type' =>              array('match' => 'enum_csv|contact,user',       'default'=>'',            'hint' => 'Type to filter by'),
      'format_name' =>              array('match' => 'enum_csv|NTitle,NFirst,NMiddle,NLast', 'default' => 'NTitle,NFirst,NMiddle,NLast',               'hint' => 'CSV list with any combination of NTitle,NFirst,NMiddle,NLast'),
      'label_company' =>            array('match' => '',						    'default'=>'Company',     'hint'=>'Label to use for Company'),
      'label_name' =>               array('match' => '',						    'default'=>'Name',        'hint'=>'Label to use for Name'),
      'label_radius_distance' =>    array('match' => '',						    'default'=>'Maximum Distance',              'hint'=>'Label to use for Radius Distance'),
      'label_radius_location' =>    array('match' => '',						    'default'=>'Your Town or Postcode',         'hint'=>'Label to use for Radius Location'),
      'map_default_zoom' =>         array('match' => 'range|1,15',				    'default'=>14,            'hint'=>'Mapp zoom to apply if there is only one point to show'),
      'map_height' =>               array('match' => 'range|1,n',				    'default'=>540,           'hint'=>'Height of map in pixels (if shown)'),
      'map_width' =>                array('match' => 'range|1,n',				    'default'=>540,           'hint'=>'Width of map in pixels (if shown)'),
      'marker_home' =>              array('match' => '',				            'default'=>'',            'hint'=>'Icon to use for map points'),
      'marker_work' =>              array('match' => '',				            'default'=>'',            'hint'=>'Icon to use for map points'),
      'radius_default' =>           array('match' => '',				            'default'=>'2',           'hint'=>'If given use this as the default range when loading the page'),
      'radius_distances' =>         array('match' => '',				            'default'=>'2,5,10,20,30,50,100,200',       'hint'=>'CSV list of distance units'),
      'results_order' =>            array('match' => 'enum|distance,NFirst,NLast,WCompany',	'default'=>'NLast',                 'hint'=>'distance|NFirst,NLast,WCompany'),
      'search_city' =>              array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'search_company' =>           array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'search_name' =>              array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'search_radius' =>            array('match' => 'enum|0,1',				    'default'=>0,             'hint'=>'0|1'),
      'search_sp' =>                array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_address' =>             array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_address_map_link' =>    array('match' => 'enum|0,1',                    'default'=>'1',           'hint' => '0|1'),
      'show_company' =>             array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_controls' =>            array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_email' =>               array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_fax' =>                 array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_phone' =>               array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_letter_anchors' =>      array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_map' =>                 array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_name' =>                array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'show_range_ring' =>          array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1 - shows a ringed area signifying search range coverage'),
      'show_start_ring' =>          array('match' => 'enum|0,1',				    'default'=>0,             'hint'=>'0|1 - shows a ringed area signifying confidence in start location'),
      'show_web' =>                 array('match' => 'enum|0,1',				    'default'=>1,             'hint'=>'0|1'),
      'text_map_link' =>            array('match' => '',                            'default'=>'Map',         'hint' => 'Text to use in map link'),
      'text_no_results' =>          array('match' => '',                            'default'=>'No results matched criteria.',  'hint'=>'Message to show when there are no results to show.'),
      'width' =>                    array('match' => 'range|1,n',				    'default'=>540,           'hint'=>'Width in pixels')
    );
  }



  public function draw($instance='', $args=array(), $disable_params=false){
    try{
      $this->_setup($instance, $args, $disable_params);
    }
    catch (Exception $e){
      $this->_draw_control_panel(true);
      $this->_msg.= $e->getMessage();
      $this->_draw_status();
      return $this->_render();
    }
    $this->_draw_js();
    $this->_draw_control_panel(true);
    if ($this->_records===false){
      $this->_draw_status();
      $this->_draw_dialog();
      return $this->_render();
    }
    if (!count($this->_records)) {
      $this->_draw_status();
      $this->_draw_dialog();
      $this->_draw_no_results();
      return $this->_render();
    }
    $this->_draw_status();
    $this->_draw_dialog();
    $this->_draw_results_header();
    $this->_draw_results();
    return $this->_render();
  }

  protected function _check_view_permissions($r, $type){
    switch ($r[$type]){
      case '':
        return false;
      break;
      case 'A':
        return true;
      break;
    }
    if ($this->_current_user_rights['isPUBLIC']){
      // Site or group - both invalid for public
      return false;
    }
    if ($r[$type]=='S' && $this->_current_user_rights['isSYSMEMBER']){
      // Recognised approved Site Users
      return true;
    }
    if ($this->_current_user_groups_access_csv=='' || $r['groups']==''){
      return false;
    }
    $visitor_groups = explode(',',$this->_current_user_groups_access_csv);
    $member_groups =  explode(',',$r['groups']);
    foreach ($visitor_groups as $v){
      if (in_array($v, $member_groups)){
        return true;
      }
    }
    return false;
  }

  protected function _draw_dialog(){
    if (!$this->_cp['show_controls']){
      return;
    }
    $this->_html.=
      "<table cellpadding='0' cellspacing='2' class='member_search_dialog' summary='Member Search Controls'>\n";
    $this->_draw_dialog_member_selector();
    $this->_draw_dialog_company_selector();
    $this->_draw_dialog_city_selector();
    $this->_draw_dialog_sp_selector();
    $this->_draw_dialog_radius_selector();
    $this->_draw_dialog_buttons();
    $this->_html.=
      "</table>\n";
  }

  protected function _draw_dialog_buttons(){
    $this->_html.=
      "  <tr>\n"
     ."    <td colspan='2' class='txt_c'>\n"
     ."      <input type='button' value=\"Search\" class='formButton' style=\"width:5em\" onclick=\"".$this->_safe_ID."_search(this)\" />\n"
     ."      <input type='button' value=\"Clear\"  class='formButton' style=\"width:5em\" onclick=\"".$this->_safe_ID."_clear()\" />\n"
     ."    </td>\n"
     ."  </tr>\n";
  }

  protected function _draw_dialog_city_selector(){
    if (!$this->_cp['search_city']){
      return;
    }
    $this->_html.=
        "  <tr>\n"
       ."    <th>City</th>\n"
       ."    <td>".draw_form_field('search_city',$this->_filter_city,'selector',$this->_field_width,$this->_selector_city_sql,0, "onchange=\"if(geid('sp')){geid('sp').selectedIndex=0;}\"")."</td>\n"
       ."  </tr>\n";
  }

  protected function _draw_dialog_company_selector(){
    if (!$this->_cp['search_company']){
      return;
    }
    $this->_html.=
        "  <tr>\n"
       ."    <th>".$this->_cp['label_company']."</th>\n"
       ."    <td>".draw_form_field('search_company',$this->_filter_company,'text',$this->_field_width)."</td>\n"
       ."  </tr>\n";
  }

  protected function _draw_dialog_member_selector(){
    if (!$this->_cp['search_name']){
      return;
    }
    $this->_html.=
        "  <tr>\n"
       ."    <th>".$this->_cp['label_name']."</th>\n"
       ."    <td>".draw_form_field('search_member',$this->_filter_member,'text',$this->_field_width)."</td>\n"
       ."  </tr>\n";
  }

  protected function _draw_dialog_radius_selector(){
    if (!$this->_cp['search_radius']){
      return;
    }
    $this->_html.=
        "  <tr>\n"
       ."    <th>".$this->_cp['label_radius_location']."</th>\n"
       ."    <td>"
       .draw_form_field('search_location',$this->_filter_radius_location,'text',$this->_field_width)
       ."</td>\n"
       ."  </tr>\n"
       ."  <tr>\n"
       ."    <th>".$this->_cp['label_radius_distance']."</th>\n"
       ."    <td>"
       .draw_form_field('search_distance',$this->_filter_radius_distance,'selector_csvlist',$this->_field_width-120,'',0,'',0,0,'',$this->_cp['radius_distances'])
       ."<div class='fr txt_r'>".draw_form_field('search_units',$this->_filter_radius_units,'radio_csvlist',120,'',0,'',0,0,'','km|KM,mi|Miles')."</div>"
       ."</td>\n"
       ."  </tr>\n"
       ;
  }

  protected function _draw_dialog_sp_selector(){
    if (!$this->_cp['search_sp']){
      return;
    }
    $this->_html.=
        "  <tr>\n"
       ."    <th>State / Province</th>\n"
       ."    <td>".draw_form_field('search_sp',$this->_filter_sp,'selector',$this->_field_width,$this->_selector_sp_sql,0, "onchange=\"if(geid('city')){geid('city').selectedIndex=0;}\"")."</td>\n"
       ."  </tr>\n";
  }

  protected function _draw_js(){
    $dist_arr = explode(',',$this->_cp['radius_distances']);
    Page::push_content(
      'javascript',
       "function ".$this->_safe_ID."_search(btn){\n"
      ."  if(\n"
      .($this->_cp['search_name'] ?    "    geid_val('search_member')=='' &&\n" : "")
      .($this->_cp['search_company'] ? "    geid_val('search_company')=='' &&\n" : "")
      .($this->_cp['search_city'] ?    "    geid('search_city').selectedIndex==0 &&\n" : "")
      .($this->_cp['search_sp'] ?      "    geid('search_sp').selectedIndex==0 &&\n" : "")
      .($this->_cp['search_radius'] ?  "    geid_val('search_location')=='' &&\n" : "")
      ."    1){\n"
      ."    alert('Please enter search criteria to limit search results');\n"
      ."    return;\n"
      ."  }\n"
      ."  btn.disabled=true;\n"
      ."  geid('submode').value='search';\n"
      ."  geid('form').submit();\n"
      ."}\n"
      ."function ".$this->_safe_ID."_clear(){\n"
      .($this->_cp['search_name'] ?    "  geid_set('search_member','');\n"  : "")
      .($this->_cp['search_company'] ? "  geid_set('search_company','');\n" : "")
      .($this->_cp['search_city'] ?    "  geid_set('search_city','-1');\n" : "")
      .($this->_cp['search_sp'] ?      "  geid_set('search_sp','-1');\n" : "")
      .($this->_cp['search_radius'] ?  "  geid_set('search_location','');\n" : "")
      .($this->_cp['search_radius'] ?  "  geid_set('search_distance','".$this->_filter_range_default."');\n" : "")
      .($this->_cp['search_radius'] ?  "  geid_set('search_units','km');\n" : "")
      ."  geid_set('submode','');\n"
      ."  geid('form').submit();\n"
      ."}\n"
    );
  }

  protected function _draw_no_results(){
    $this->_html.= "<h3 class='txt_c'>".$this->_cp['text_no_results']."</h3>";
  }

  protected function _draw_results(){
    $this->_html.= $this->_Obj_Person->draw_Block_Layout();
  }

  protected function _draw_results_header(){
    $this->_html.=
       "<h3 class='txt_c'>"
      .(count($this->_records)==1 ? "One " : count($this->_records))." Match".(count($this->_records)==1 ? '' : 'es')
      .(count($this->_records)>1 ?
         " (Ordered by "
        .($this->_cp['results_order']=='distance' ?   "Distance" : "")
        .($this->_cp['results_order']=='NFirst' ?     "First Name" : "")
        .($this->_cp['results_order']=='NLast' ?      "Last Name" : "")
        .($this->_cp['results_order']=='WCompany' ?   "Company Name" : "")
        .")"
       :
         ""
      )
     ."</h3>";
  }

  protected function _get_query_for_results($category=false, $type=false, $group=false){
    $sql =
      "SELECT\n"
     .($this->_filter_radius_location && System::has_feature('show-home-address') ? "  CAST(if(`AMap_lat`=0 AND `AMap_lon`=0,100000,COALESCE(ROUND(DEGREES(ACOS(SIN(RADIANS(".$this->_search_lat.")) * SIN(RADIANS(`AMap_lat`)) + COS(RADIANS(".$this->_search_lat.")) * COS(RADIANS(`AMap_lat`)) * COS(RADIANS(".$this->_search_lon." - `AMap_lon`)))) * ".($this->_filter_radius_units=="km" ? "111.05" : "69").", 2),'')) AS DECIMAL(7,1)) AS `AMap_range`,\n" : "")
     .($this->_filter_radius_location && System::has_feature('show-work-address') ? "  CAST(if(`WMap_lat`=0 AND `WMap_lon`=0,100000,COALESCE(ROUND(DEGREES(ACOS(SIN(RADIANS(".$this->_search_lat.")) * SIN(RADIANS(`WMap_lat`)) + COS(RADIANS(".$this->_search_lat.")) * COS(RADIANS(`WMap_lat`)) * COS(RADIANS(".$this->_search_lon." - `WMap_lon`)))) * ".($this->_filter_radius_units=="km" ? "111.05" : "69").", 2),'')) AS DECIMAL(7,1)) AS `WMap_range`,\n" : "")
     .($this->_current_user_groups_access_csv!=='' ? "  (SELECT GROUP_CONCAT(`gm`.`groupID`) FROM `group_members` `gm` WHERE `gm`.`personID`=`person`.`ID`) `groups`,\n" : "")
     .(System::has_feature('show-home-address') ? "  (SELECT `textEnglish` FROM `listdata` where `listtypeID`='".Component_Member_Search::lst_country_ID."' AND `listdata`.`value`!='' AND `listdata`.`value` = `ACountryID`) AS `ACountry`,\n" : "")
     .(System::has_feature('show-work-address') ? "  (SELECT `textEnglish` FROM `listdata` where `listtypeID`='".Component_Member_Search::lst_country_ID."' AND `listdata`.`value`!='' AND `listdata`.`value` = `WCountryID`) AS `WCountry`,\n" : "")
     .($this->_cp['results_order']=='NFirst' ?   "  UCASE(LEFT(`person`.`NFirst`,1)) AS `anchor`,\n"    : '')
     .($this->_cp['results_order']=='NLast' ?    "  UCASE(LEFT(`person`.`NLast`,1)) AS `anchor`,\n"    : '')
     .($this->_cp['results_order']=='WCompany' ? "  UCASE(LEFT(`person`.`WCompany`,1)) AS `anchor`,\n" : '')
     ."  CONCAT(`NFirst`,IF(`NMiddle`!='',CONCAT(' ',`NMiddle`),''),IF(`NLast`!='',CONCAT(' ',`NLast`),'')) AS `name`,\n"
     ."  `person`.*\n"
     ."FROM\n"
     ."  `person`\n"
     ."WHERE\n"
     .$this->_get_query_limit_for_filter($category, $type, $group)." AND\n"
     .$this->_get_query_limit_for_privacy('home,work')." AND\n"
     .$this->_get_query_limit_for_search()." AND\n"
     ."  1\n"
     ."ORDER BY\n"
     .($this->_cp['results_order']=='WCompany' ? "  `WCompany`,`NLast`,`NFirst`" : "")
     .($this->_cp['results_order']=='NFirst' ?   "  `NFirst`,`NLast`" : "")
     .($this->_cp['results_order']=='NLast' ?    "  `NLast`,`NFirst`" : "")
     .($this->_cp['results_order']=='distance' ?
         (System::has_feature('show-home-address')  &&  System::has_feature('show-work-address') ? "  (IF(`AMap_range`<`WMap_range`,`AMap_range`,`WMap_range`)) ASC" : "")
        .(System::has_feature('show-home-address')  && !System::has_feature('show-work-address') ? "  `AMap_range` ASC" : "")
        .(!System::has_feature('show-home-address') &&  System::has_feature('show-work-address') ? "  `WMap_range` ASC" : "")
      :
        ""
      );
    return get_sql_constants($sql);
  }
  protected function _get_query_limit_for_filter($category='', $type='', $group=''){
    return
      ($category && $category!='*' ?
          "  `category` LIKE \"%".$category."%\" AND\n"
       :
          ""
       )
      .($type && $type!='*' ?
          "  `type` IN(\"".implode("\",\"",explode(',',$type))."\") AND\n"
       :
          ""
       )
      .($group!='' ?
          "  `person`.`ID` IN(\n"
         ."    SELECT\n"
         ."      `gm`.`personID`\n"
         ."    FROM\n"
         ."      `group_members` `gm`\n"
         ."    INNER JOIN `groups` `g` ON\n"
         ."      `gm`.`groupID` = `g`.`ID`\n"
         ."    WHERE\n"
         ."      `g`.`name` = '".$group."' AND\n"
         ."      `gm`.`personID`=`person`.`ID`\n"
         ."  ) AND\n"
       :
          ""
       )
      ."  `person`.`systemID` = ".SYS_ID;
  }

  protected function _get_query_limit_for_privacy($address){
    $clauses =      array();
    $address_arr =  explode(',',$address);
    $a_settings =   array();
    foreach($address_arr as $a){
      if (System::has_feature('show-home-address') && $a=='home' || System::has_feature('show-work-address') && $a=='work'){
        $a_settings[] = "`privacy_address_".$a."`='A'";
      }
    }
    $clauses[] =    '    ('.implode(' OR ',$a_settings).')';
    if ($this->_current_user_rights['isSYSMEMBER']){
      $a_settings =  array();
      foreach($address_arr as $a){
        if (System::has_feature('show-home-address') && $a=='home' || System::has_feature('show-work-address') && $a=='work'){
          $a_settings[] = "`privacy_address_".$a."`='S'";
        }
      }
      $clauses[] =    '    ('.implode(' OR ',$a_settings).')';
    }
    if ($this->_current_user_groups_access_csv){
      $a_settings =  array();
      foreach($address_arr as $a){
        if (System::has_feature('show-home-address') && $a=='home' || System::has_feature('show-work-address') && $a=='work'){
          $a_settings[] = "`privacy_address_".$a."`='G'";
        }
      }
      $clauses[] =
         "    (\n"
        ."      (".implode(' OR ',$a_settings).") AND\n"
        ."      (SELECT EXISTS(SELECT `ID` FROM `group_members` `gm` WHERE `gm`.`groupID` IN(".$this->_current_user_groups_access_csv.") AND `gm`.`personID`=`person`.`ID`))\n"
        ."    )";
    }
    return
       "  (\n"
      .implode(" OR\n",$clauses)."\n"
      ."  )";
  }

  protected function _get_query_limit_for_search(){
    if (!$this->_cp['show_controls']){
      return;
    }
    $clauses = array();
    if ($this->_filter_member){
      $clauses[] =  "  (CONCAT(`NFirst`,IF(`NMiddle`!='',' ',''),`NMiddle`,IF(`NLast`!='',' ',''),`NLast`) LIKE \"%".str_replace(' ','% %',$this->_filter_member)."%\")";
    }
    if ($this->_filter_company){
      $clauses[] =  "  (`WCompany` LIKE \"%".$this->_filter_company."%\")";
    }
    if ($this->_filter_city && $this->_filter_city!=="-1"){
      $clauses[] =
         "  ("
        .(System::has_feature('show-home-address') ? "`ACity`=\"".$this->_filter_city."\"" : "")
        .(System::has_feature('show-home-address') && System::has_feature('show-work-address') ? " OR " : "")
        .(System::has_feature('show-work-address') ? "`WCity`=\"".$this->_filter_city."\"" : "")
        .")";
    }
    if ($this->_filter_sp && $this->_filter_sp!=="-1"){
      $clauses[] =
         "  ("
        .(System::has_feature('show-home-address') ? "`ASpID`=\"".$this->_filter_sp."\"" : "")
        .(System::has_feature('show-home-address') && System::has_feature('show-work-address') ? " OR " : "")
        .(System::has_feature('show-work-address') ? "`WSpID`=\"".$this->_filter_sp."\"" : "")
        .")";
    }
    $this->_filter_radius_distance =    get_var('search_distance',20);
    $this->_filter_radius_location =    get_var('search_location');
    $this->_filter_radius_units =       get_var('search_units','km');
    if ($this->_filter_radius_location){
      $clauses[] =
         "  ("
        .(System::has_feature('show-home-address') ? "ROUND(DEGREES(ACOS(SIN(RADIANS(".$this->_search_lat.")) * SIN(RADIANS(AMap_lat)) + COS(RADIANS(".$this->_search_lat.")) * COS(RADIANS(AMap_lat)) * COS(RADIANS(".$this->_search_lon." - AMap_lon))))*".($this->_filter_radius_units=="km" ? "111.05" : "69").", 2) < ".$this->_filter_radius_distance : "")
        .(System::has_feature('show-home-address') && System::has_feature('show-work-address') ? " OR " : "")
        .(System::has_feature('show-work-address') ? "ROUND(DEGREES(ACOS(SIN(RADIANS(".$this->_search_lat.")) * SIN(RADIANS(WMap_lat)) + COS(RADIANS(".$this->_search_lat.")) * COS(RADIANS(WMap_lat)) * COS(RADIANS(".$this->_search_lon." - WMap_lon))))*".($this->_filter_radius_units=="km" ? "111.05" : "69").", 2) < ".$this->_filter_radius_distance : "")
        .")";
    }
    return implode(" AND\n",$clauses);
  }

  protected function _render(){
    return
      "<div class='".$this->_ident."'".($this->_cp['width'] ? " style='width:".$this->_cp['width']."px;'" : "").">\n"
     .$this->_html
     ."</div>";

  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    if ($this->_cp['results_order']=='distance' && !$this->_cp['search_radius']){
      $this->_cp['results_order'] = 'WCompany';
    }
    $this->_setup_check_privacy_controls();
    $this->_setup_check_addresses_available();
    $this->_setup_set_field_width();
    $this->_setup_load_user_rights();
    $this->_setup_load_user_groups();
    $this->_setup_set_filter_criteria();
    $this->_setup_set_selector_city_sql();
    $this->_setup_set_selector_sp_sql();
    $this->_setup_lookup_search_location();
    $this->_setup_load_results();
    $this->_setup_highlight_search_terms();
    $this->_setup_set_person_object();
  }

  protected function _setup_check_addresses_available(){
    if (!System::has_feature('show-home-address') && !System::has_feature('show-work-address')){
      throw new Exception("<b>Error:</b> Site settings permit display of neither home nor work address.");
    }
  }

  protected function _setup_check_privacy_controls(){
    if (!System::has_feature('person-privacy-controls')){
      throw new Exception("<b>Error:</b> Privacy controls must be enabled before this component may be used.");
    }
  }

  protected function _setup_highlight_search_terms(){
    if (!$this->_records){
      return;
    }
    foreach($this->_records as &$r){
      if (trim($this->_filter_member)!="") {
        $r['name'] = highlight($r['name'],$this->_filter_member);
      }
      if ($r['name']==''){
        $r['name']='(none)';
      }
      if (trim($this->_filter_company)!="") {
        $r['WCompany'] = highlight($r['WCompany'],$this->_filter_company);
      }
      if (trim($this->_filter_city)!="") {
        $r['ACity'] =    highlight($r['ACity'],$this->_filter_city);
        $r['WCity'] =    highlight($r['WCity'],$this->_filter_city);
      }
      if (trim($this->_filter_sp)!="") {
        $r['ASpID'] =    highlight($r['ASpID'],$this->_filter_sp);
        $r['WSpID'] =    highlight($r['WSpID'],$this->_filter_sp);
      }
    }
  }

  protected function _setup_load_results(){
    if ($this->_cp['show_controls'] && $this->_submode!= "search"){
      return;
    }
    if ($this->_msg){
      return;
    }
    $sql = $this->_get_query_for_results($this->_cp['filter_category'], $this->_cp['filter_type'], $this->_cp['filter_group']);
    $this->_records = $this->get_records_for_sql($sql);
  }

  protected function _setup_lookup_search_location(){
    if (!$this->_filter_radius_location){
      return;
    }
    $result = Google_Map::find_geocode($this->_filter_radius_location);
    if ($result['error']!=''){
      $this->_msg = "<b>Error:</b> ".$result['error'];
      return;
    }
    $this->_search_lat =    $result['lat'];
    $this->_search_lon =    $result['lon'];
    if ($this->_cp['show_start_ring']){
      $this->_search_area =   $result['match_area'];
    }
  }

  protected function _setup_set_field_width(){
    $this->_field_width = $this->_cp['width']-200;
  }

  protected function _setup_set_filter_criteria(){
    $dist_arr =                         explode(',',$this->_cp['radius_distances']);
    $this->_filter_range_default =      (!$this->_cp['radius_default'] || !in_array($this->_cp['radius_default'],$dist_arr) ? trim($dist_arr[0]) : $this->_cp['radius_default']);
    $this->_submode =                   get_var('submode');
    $this->_filter_city =               get_var('search_city');
    $this->_filter_company =            get_var('search_company');
    $this->_filter_member =             get_var('search_member');
    $this->_filter_radius_distance =    get_var('search_distance',$this->_filter_range_default);
    $this->_filter_radius_location =    get_var('search_location');
    $this->_filter_radius_units =       get_var('search_units','km');
    $this->_filter_sp =                 get_var('search_sp');
  }

  protected function _setup_set_person_object(){
    $this->_Obj_Person = new Person;
    $this->_Obj_Person->_set('_block_layout_name',$this->_cp['block_layout']);
    $this->_Obj_Person->_common_load_block_layout();
    $this->_Obj_Person->_set('_records',$this->_records);
    $args = array(
      '_cp' =>                          $this->_cp,
      '_current_user_rights' =>         $this->_current_user_rights,
      '_block_layout' =>                $this->_Obj_Person->_get('_block_layout'),
      '_context_menu_ID' =>             'contact',
      '_mode' =>                        'list',
      '_safe_ID' =>                     $this->_safe_ID,
      '_search_area' =>                 $this->_search_area,
      '_search_lat' =>                  $this->_search_lat,
      '_search_lon' =>                  $this->_search_lon,
      '_filter_radius_distance' =>      $this->_filter_radius_distance,
      '_filter_radius_units' =>         $this->_filter_radius_units
    );
    $this->_Obj_Person->_set_multiple($args);
  }

  protected function _setup_set_selector_city_sql(){
    $this->_selector_city_sql =
       "SELECT\n"
      ."  0 AS `seq`,\n"
      ."  'D0D0D0' AS `color_background`,\n"
      ."  -1 AS `value`,\n"
      ."  '--- Select a value to filter by City ---' AS `text`\n"
      ."UNION SELECT\n"
      ."  '1',\n"
      ."  'E0FFE0',\n"
      ."  `city`,\n"
      ."  CONCAT(\n"
      ."    `city`,\n"
      ."    IF(`sp`!='', CONCAT(', ',`sp`),''),\n"
      ."    ' [',\n"
      ."    COUNT(DISTINCT `ID`),\n"
      ."    ']'\n"
      ."  ) `city` FROM\n"
      ."(\n"
      .(System::has_feature('show-home-address') ?
           "(SELECT\n"
          ."  `ID`,\n"
          ."  `ACity` `city`,\n"
          ."  `ASpID` `sp`\n"
          ."FROM\n"
          ."  person\n"
          ."WHERE\n"
          ."  `ACity`!='' AND\n"
          .$this->_get_query_limit_for_privacy('home')." AND\n"
          .$this->_get_query_limit_for_filter()
          .")\n"
        :
          ""
       )
      .(System::has_feature('show-home-address') && System::has_feature('show-work-address') ? "UNION ALL\n" : "")
      .(System::has_feature('show-work-address') ?
           "(SELECT\n"
          ."  `ID`,\n"
          ."  `WCity` `city`,\n"
          ."  `WSpID` `sp`\n"
          ."FROM\n"
          ."  person\n"
          ."WHERE\n"
          ."  `WCity`!='' AND\n"
          .$this->_get_query_limit_for_privacy('work')." AND\n"
          .$this->_get_query_limit_for_filter()
          .")\n"
       :
         ""
       )
      .") AS `city`\n"
      ."GROUP BY `city`\n"
      ."ORDER BY `seq`,`value`";
  }

  protected function _setup_set_selector_sp_sql(){
    $this->_selector_sp_sql =
       "SELECT\n"
      ."  0 AS `seq`,\n"
      ."  'D0D0D0' AS `color_background`,\n"
      ."  -1 AS `value`,\n"
      ."  '--- Select a value to filter by State / Province ---' AS `text`\n"
      ."UNION SELECT '1', IF(`sp`='','FFE0E0','E0FFE0'), `sp`,\n"
      ."  CONCAT(\n"
      ."    COALESCE(\n"
      ."      IF(\n"
      ."        `sp`='',\n"
      ."        '(None)',\n"
      ."        (SELECT `textEnglish` FROM `listdata` `ld` WHERE `ld`.`listTypeID`=".Component_Member_Search::lst_sp_ID." AND `ld`.`value`=`sp`)\n"
      ."      ),\n"
      ."      `sp`\n"
      ."    ),\n"
      ."    ' [', COUNT(DISTINCT `ID`),']'\n"
      ."  ) `sp` FROM\n"
      ."(\n"
      .(System::has_feature('show-home-address') ?
           "(SELECT\n"
          ."  `ID`,\n"
          ."  `ASpID` `sp`\n"
          ."FROM\n"
          ."  person\n"
          ."WHERE\n"
          ."  `ACity`!='' AND\n"
          .$this->_get_query_limit_for_privacy('home')." AND\n"
          .$this->_get_query_limit_for_filter()
          .")\n"
       :
          ""
       )
      .(System::has_feature('show-home-address') && System::has_feature('show-work-address') ? "UNION ALL\n" : "")
      .(System::has_feature('show-work-address') ?
           "(SELECT\n"
          ."  `ID`,\n"
          ."  `WSpID` `sp`\n"
          ."FROM\n"
          ."  person\n"
          ."WHERE\n"
          ."  `WCity`!='' AND\n"
          .$this->_get_query_limit_for_privacy('work')." AND\n"
          .$this->_get_query_limit_for_filter()
          .")\n"
       :
         ""
       )
      .") AS `sp`\n"
      ."GROUP BY `sp`\n"
      ."ORDER BY `seq`,`value`";
  }


  public function get_version(){
    return VERSION_COMPONENT_MEMBER_SEARCH;
  }
}
?>