<?php
define ("VERSION_COMPONENT_CALENDAR_LARGE","1.0.28");
/*
Version History:
  1.0.28 (2014-01-28)
    1) Newline after JS onload code in Component_Calendar_Large::_draw_js()

  (Older version history in class.component_calendar_large.txt)
*/
class Component_Calendar_Large extends Component_Base {
  protected $_arr_cal;
  protected $_categories;
  protected $_controls_visible;
  protected $_isAdmin;
  protected $_memberID;
  protected $_MM;
  protected $_popup_size;
  protected $_show_icons;
  protected $_YYYY;
  protected $_event_category_name;
  protected $_event_report_name;
  protected $_event_context_menu_name;

  public function __construct(){
    global $system_vars;
    $this->_ident =             "calendar_large";
    $this->_parameter_spec = array(
      'control_categories' =>   array('match' => 'enum|0,1',        'default'=>1,   'hint'=>'Whether or not to allow categories to be chosen'),
      'control_icons' =>        array('match' => 'enum|0,1',        'default'=>1,   'hint'=>'Whether or not to allow icons to be turned on or '),
      'filter_shared' =>        array('match' => 'enum|0,1,x',      'default'=>'x', 'hint'=>'Limit only to shared items'),
      'heading' =>              array('match' => '',                'default'=>"Monthly Calendar for ".$system_vars['textEnglish'], 'hint'=>'Displayed heading'),
      'show_controls' =>        array('match' => 'enum|0,1',        'default'=>1,   'hint'=>'Whether or not to show controls'),
      'show_heading' =>         array('match' => 'enum|0,1',        'default'=>1,   'hint'=>'Whether or not to show heading'),
    );
    $this->_event_category_name =     'Event Category';
    $this->_event_report_name =       'events';
    $this->_event_context_menu_name = 'event';
  }

  public function draw($instance='', $args=array(), $disable_params=false) {
    global $page_vars,$system_vars;
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_draw_status_message();  // not working - system::do_commands seems to intercept this
    $this->_draw_js();
    $this->_html.=
       "<div class='monthly_calendar' id='".$this->_safe_ID."'>"
      .draw_form_field($this->_safe_ID.'_YYYYMM',$this->_YYYY.'-'.$this->_MM,'hidden')
      .($this->_cp['show_heading'] ? "<h1>".$this->_cp['heading']."</h1>" : "");
    $this->_draw_user_controls();
    $this->_html.=
       "<div class='clr_b'></div>"
      .HTML::draw_status('calendar',$this->_msg)
      ."<table cellpadding='0' cellspacing='0' class='calendar_big cal_table' summary='Calendar with day headings'>\n"
      ."  <thead>\n"
      ."  <tr>\n"
      ."    <th class='cal_head'>&nbsp;</th>\n"
      ."    <th class='cal_head' colspan='5'>".MM_to_MMMM($this->_MM)." ".$this->_YYYY."</th>\n"
      ."    <th class='cal_head'><div class='fr'>".HTML::draw_icon('print_calendar')."&nbsp;</div></th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th class='cal_control' id='cal_control_last_year' title='Go back one year' onclick=\"cal_goto(-12)\">&lt;&lt; Prev Year</th>\n"
      ."    <th class='cal_control' id='cal_control_last_month' title='Go back one month' onclick=\"cal_goto(-1)\">&lt; Prev Month</th>\n"
      ."    <th class='cal_control' id='cal_control_today' title='See today' colspan='3' onclick=\"cal_goto(0)\">Today</th>\n"
      ."    <th class='cal_control' id='cal_control_next_month' title='Go forward one month' onclick=\"cal_goto(1)\">&gt; Next Month</th>\n"
      ."    <th class='cal_control' id='cal_control_next_year' title='Go forward one year' onclick=\"cal_goto(12)\">&gt;&gt; Next Year</th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td class='cal_days cal_days_s'>Sunday</td>\n"
      ."    <td class='cal_days'>Monday</td>\n"
      ."    <td class='cal_days'>Tuesday</td>\n"
      ."    <td class='cal_days'>Wednesday</td>\n"
      ."    <td class='cal_days'>Thursday</td>\n"
      ."    <td class='cal_days'>Friday</td>\n"
      ."    <td class='cal_days cal_days_s'>Saturday</td>\n"
      ."  </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    $date = substr(get_timestamp(),0,10);
    for ($i=0; $i<42; $i+=7) {
      $this->_html.=		 "  <tr>\n";
      for ($j=0; $j<7; $j++) {
        $k = $i+$j;
        $icon = "";
        foreach ($this->_arr_cal[$k]['evt'] as $evt) {
          if ($evt['icon'] && $this->_show_icons){
            $icon =
               "<img src=\""
              .trim($evt['systemURL'],"/")
              ."/img/max/80"
              .$evt['icon']
              ."\" alt=\"".$evt['title']."\" />";
            break;
          }
        }
//        y($this->_arr_cal[$k]);
        $_DD = 	    $this->_arr_cal[$k]['DD'];
        $_MM =	    $this->_arr_cal[$k]['MM'];
        $_YYYY =	$this->_arr_cal[$k]['YYYY'];
        $_date =    $_YYYY.'-'.$_MM.'-'.$_DD;
        $today =    $date == $_date;
        $this->_html.=
           "    <td"
          .($_MM==$this->_MM ?
              " class='cal cal_current".($today ? " cal_today" : "")."'"
            :
              " class='cal cal_then".($today ? " cal_today" : "")."'"
           )
          .($today ?
              " title='(Today)' "
           :
              ($_MM!=$this->_MM ? " title='Click for ".MM_to_MMMM($_MM)." $_YYYY'" : "")
           )
          .($_MM!=$this->_MM ? " onclick=\"cal_goto(".($i<7 ? '-1' : '1').")\"" : "")
          ;
        $uri_args =
           "effective_date_start=".$this->_YYYY."-".$this->_MM."-".$_DD
          ."&amp;effective_date_end=".$this->_YYYY."-".$this->_MM."-".$_DD
          .(isset($this->memberID) && $this->memberID ? "&amp;memberID=".$this->memberID : "");
        $this->_html.=
          ">"
          ."<div class='fl' style=\"font-size:120%;"
          .(count($this->_arr_cal[$k]["evt"]) ?
              "color:#".$system_vars['cal_event']
              .($_MM==$this->_MM ? ";cursor:pointer;text-decoration:underline;" : "") : "")
          ."\""
          .(count($this->_arr_cal[$k]["evt"]) && $_MM==$this->_MM ? " title='Click to list all events for this day' onclick=\"cal_list('".$_YYYY.'-'.$_MM.'-'.$_DD."',".($this->_isAdmin ? 1 : 0).")\"" : "")
          .">"
          .($_MM==$this->_MM ? "<b>".$_DD."</b>" : $_DD)
          ."</div>"
          .($_MM==$this->_MM && $this->_isAdmin ?
              "<a class=\"fl icon_add_new\" href=\"".BASE_PATH."details/".$this->_event_report_name."/?ID=".$uri_args."\""
             ." onclick=\"details('".$this->_event_report_name."','','".$this->_popup_size[$this->_event_report_name]['h']."','".$this->_popup_size[$this->_event_report_name]['w']."','','','','".$uri_args."');return false;\""
             ." title=\"Add new event for this day\">"
             ."<img src=\"".BASE_PATH."img/spacer\" class=\"icons\" style=\"margin-left:3px;width:12px;height:13px;background-position: -3512px 0px;\" alt=\"\" />"
             ."</a>"
           : ""
           )
          ."<div class='clr_b'></div>"
          .($_MM==$this->_MM ? $icon : "")
          ;
        $this->_old_times = "";
        for ($i_evt=0; $i_evt<count($this->_arr_cal[$k]['evt']); $i_evt++) {
          $this->record =    $this->_arr_cal[$k]['evt'][$i_evt];
          $this->_draw_event();
        }
        $this->_html.=		"</td>\n";
      }
      $this->_html.=		 "  </tr>\n";
    }
    $this->_html.=
       "</tbody>\n"
      ."</table>"
      ."</div>\n";
    return $this->_html;
  }

  public function draw_json($instance='', $args=array(), $disable_params=false) {
    global $system_vars;
    $base = trim($system_vars['URL'],'/').'/';
    $out = array(
      'css' =>  '',
      'js' =>   'ecc.cal_setup()',
      'html' => ''
    );
    $html = $this->draw($instance,$args,$disable_params);
    if (isset(Page::$content['style'])){
      $html.= "<style type='text/css'>".implode("\n",Page::$content['style'])."</style>";
    }
    if (isset(Page::$content['style_include'])){
      $html.= implode("\n",Page::$content['style_include']);
    }
    $html = absolute_path($html,$base);
    $html = str_replace(
      array(
        "onclick=\"cal_goto(",
        "onclick=\"cal_list("
      ),
      array(
        "onclick=\"ecc.cal_goto('".$this->_safe_ID."_YYYYMM',",
        "onclick=\"ecc.cal_list("
      ),
      $html
    );
    $html = preg_replace("/ onmouseover=[^\>]+\>/",">",$html);
    $html = preg_replace("/<a class=\"icon_add_new\"(.+)><\/a\>/","",$html);

//    $html = "<textarea style='width:100%;height:600px;'>".print_r(Page::$content,true)."</textarea>";
    $out['html'] = $html;
    return $out;
  }

  protected function _do_submode(){
    if (!$this->_isAdmin) {
      return;
    }
    switch (get_var('submode')) {
      case "event_delete":
        $Obj_Event = new Event(get_var('targetID'));
        if ($Obj_Event && $Obj_Event->get_field('systemID')==SYS_ID) {
          $Obj_Event->delete();
          $this->_msg = "Success: Event was deleted.";
        }
      break;
    }
  }

  protected function _draw_event(){
    $times =
      ($this->record['effective_time_start']||$this->record['effective_time_end']||1 ?
          "<div style='font-weight:bold;margin:1em 0 0 0;'>"
         .Event::format_times($this->record['effective_time_start'],$this->record['effective_time_end'])
         ."</div>"
        : "");
    $category_arr = explode(",",$this->record['category']);
    $category = trim($category_arr[0]);
    $this->_ObjEvent->_set_ID($this->record['ID']);
    $registrations = $this->_ObjEvent->count_registrations();
    $this->_html.=
       "<div style='margin-bottom:0.5em'"
      .($this->_isAdmin && $this->record['ID'] && ($this->record['systemID']==SYS_ID || $isMASTERADMIN)?
          " onmouseover=\""
         ."if(!CM_visible('CM_event')) {"
         ."this.style.backgroundColor='"
         .($this->record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
         ."';"
         ."_CM.type='".$this->_event_context_menu_name."';"
         ."_CM.ID=".$this->record['ID'].";_CM_text[0]='&quot;".str_replace("'",'',str_replace('& ','&amp; ',$this->record['title']))."&quot;';"
         .(isset($this->record['enabled']) ? "_CM.enabled=".$this->record['enabled'].";" : "")
         .(isset($this->record['important']) ? "_CM.important=".$this->record['important'].";" : "")
         .(isset($this->record['permSHARED']) ? "_CM.shared=".$this->record['permSHARED'].";" : "")
         ."_CM_text[1]=_CM_text[0];"
         ."_CM.event_registrants=".$registrations.";"
         ."_CM_ID[3]='"
         .($this->_isAdmin && $this->record['systemID']==SYS_ID || $isMASTERADMIN ? $this->_blockLayoutID : '')
         ."';"
         ."_CM_text[3]='&quot;Event&quot;';"
         .";}\""
         ." onmouseout=\"this.style.backgroundColor='';_CM.type='';\""
       :
         ""
      )
      .">"
      .($times!=$this->_old_times ? $times : "")
      ."<div class='fl lce css3 category_".$category."' title='Category: ".($this->record['category'] ? $this->record['category'] : "(None)")."'>"
      .$this->_shared_source_link($this->record)
      ."<div class='fl' style='padding:2px;'>"
      ."<a class='category_".$category."' href=\"#\" onclick=\"popWin('".BASE_PATH."event/".$this->record['ID']."?print=1','event_".$this->record['ID']."','location=1,status=1,scrollbars=1,resizable=1',600,600,1);return false\">"
      .strip_tags(str_replace('& ','&amp; ',$this->record['title']))
      ."</a>"
      ."</div>"
      ."</div>"
      ."<div class='clr_b'></div>"
      ."</div>";
    $this->_old_times = $times;
  }

  protected function _draw_js(){
    $cats = array();
    foreach ($this->_categories as $key=>$value) {
      $cats[] = '"'.$value['value'].'"';
    }
    $js =
       "function category_select(state){\n"
      ."  cal_large_categories = [".implode(',',$cats)."];"
      ."  for(var i=0; i<cal_large_categories.length; i++){\n"
      ."    geid_set('show_'+cal_large_categories[i],state);\n"
      ."  }\n"
      ."}";
    Page::push_content('javascript',$js);
    Page::push_content('javascript_onload',"  cal_setup();\n");
  }

  protected function _draw_status_message(){
    $this->_html.=      HTML::draw_status($this->_safe_ID,$this->_msg);
  }

  protected function _draw_user_controls(){
    if (!$this->_cp['show_controls']){
      return;
    }
    if (!$this->_cp['control_categories'] && !$this->_cp['control_icons']){
      return;
    }
    $this->_html.=
       "<div class='noprint fl' style='padding:10px;'>"
      ."<h2 style='cursor:hand;font-style:italic;' title='Click to view / hide customisation options'"
      ." onclick=\"widget_toggle('id_calendar_options');\">"
      ."<img id='id_calendar_options_show' class='icon' src='/img/spacer' style='width:9px;height:9px;margin:2px;background-position:-3190px 0px;".($this->_controls_visible ? "display:none" : "")."' alt='+' />"
      ."<img id='id_calendar_options_hide' class='icon' src='/img/spacer' style='width:9px;height:9px;margin:2px;background-position:-3199px 0px;".($this->_controls_visible ? "" : "display:none")."' alt='-' />"
      ."Click to choose displayed details"
      ."</h2>"
      ."<div id='id_calendar_options' style='padding: 10px 0 0 20px;".($this->_controls_visible ? "" : "display:none")."'>";
    if ($this->_cp['control_icons']){
      $this->_html.=
         "<div style='line-height:1.25em;margin-bottom:2px;' onclick=\"geid('show_icons').checked=!geid('show_icons').checked\">\n"
        ."<div class='fl' style='width:150px'>Show Icons</div>"
        ."<div style='height:1.25em;' class='fl'>"
        .draw_form_field(
          'show_icons',
          (!isset($_REQUEST['submode']) ? '1' : (isset($_REQUEST['show_icons']) ? $_REQUEST['show_icons'] : 1)),
          'radio_listdata','','','','','','','','lst_bool_no_yes_0_1')
        ."</div>"
        ."</div>"
        ."<div class='clr_b' style='overflow:hidden;height:0;width:0'>&nbsp;</div>\n";
    }
    if ($this->_cp['control_categories'] && count($this->_categories)){
      $this->_html.=
         "<div class='fl' style='width:150px'>Categories</div>"
        ."<div style='width:250px;padding:2px;' class='txt_c'>\n"
        ."<a href='#' onclick='category_select(true);return false;'>All</a> | "
        ."<a href='#' onclick='category_select(false);return false;'>None</a>"
        ."</div>\n";
      foreach ($this->_categories as $key=>$value) {
        $this->_html.=
           "<div style='padding-left:5px;line-height:1.25em;margin-bottom:2px;border-left:1px solid #ccc;border-top:1px solid #ccc;border-bottom:1px solid #ccc' class='fl category_".$value['value']."' onclick=\"geid('show_".$value['value']."').checked=!geid('show_".$value['value']."').checked\">\n"
          ."<div class='fl' style='width:200px'>".($value['text'] ? $value['text'] : "(No Category)")."</div>"
          ."<div class='fl' style='width:30px;'>[".$value['count']."]</div>\n"
          ."</div>"
          ."<div style='height:1.25em;border-right:1px solid #ccc;border-top:1px solid #ccc;border-bottom:1px solid #ccc' class='fl category_".$value['value']."'>"
          .draw_form_field(
            'show_'.$value['value'],
            (!isset($_REQUEST['submode']) || isset($_REQUEST['show_'.$value['value']]) ? 1 : 0),
            'bool')
          ."</div>"
          ."<div class='clr_b' style='overflow:hidden;height:0;width:0'>&nbsp;</div>\n";
      }
    }
    $this->_html.=
       "<div class='txt_c' style='width:230px;'>\n"
      ." <input type='button' value='Apply' onclick=\"geid_set('submode','apply');geid('form').submit()\" />\n"
      ."</div>\n"
      ."</div>\n"
      ."</div>\n";
  }

  protected function _setup($instance, $args, $disable_params){
    global $MM, $YYYY, $page_vars;
    parent::_setup($instance, $args, $disable_params);
    $this->_categories =        array();
    $this->_css =               "";
    $this->_html =              "";
    $this->_MM =                $MM;
    $this->_YYYY =              $YYYY;
    $this->_memberID =          (isset($page_vars['memberID']) ? $page_vars['memberID'] : 0);
    $this->_msg =               "";
    $this->_safe_ID =           Component_Base::get_safe_ID($this->_ident,$this->_instance);
    $this->_ObjEvent =          new Event;
    $this->_setup_load_permissions();
    $this->_do_submode();
    $this->_setup_load_blockLayoutID();
    $this->_setup_load_popup_sizes();
    $this->_setup_load_events();
    $this->_setup_load_categories();
    $this->_setup_apply_filters();
    $this->_setup_apply_icon_visibility();

  }

  protected function _setup_apply_filters(){
    $filtered = array();
    foreach ($this->_arr_cal as $item) {
      $date =
        array(
          'YYYY' => $item['YYYY'],
          'MM'   => $item['MM'],
          'DD'   => $item['DD'],
          'evt'  => array()
        );
      for($i=0; $i<count($item['evt']); $i++) {
        $csv_arr = explode(",",$item['evt'][$i]['category']);
        $present = false;
        foreach ($csv_arr as $csv) {
          if ($this->_cp['control_categories']==0 || get_var('show_'.$csv)){
            $present = true;
            break;
          }
        }
        if (!get_var('submode') || $present) {
          $date['evt'][] = $item['evt'][$i];
        }
      }
      $filtered[] = $date;
    }
    $this->_arr_cal = $filtered;
  }

  protected function _setup_apply_icon_visibility(){
    $this->_controls_visible = false;
    $this->_show_icons = true;
    if (isset($_REQUEST['show_icons']) && $_REQUEST['show_icons']=='0'){
      $this->_controls_visible = true;
      $this->_show_icons = false;
    }
  }

  protected function _setup_load_blockLayoutID(){
    if ($this->_isAdmin){
      $this->_Obj_BlockLayout =   new Block_Layout;
      $this->_blockLayoutID =     $this->_Obj_BlockLayout->get_ID_by_name('Event');
    }
  }

  protected function _setup_load_categories(){
    $Obj = new ListType;
    $Obj->_set_ID($Obj->get_ID_by_name($this->_event_category_name));
    $listdata = $Obj->get_listdata();
    if (!$listdata){
      return;
    }
    foreach ($listdata as $item){
      $this->_categories[$item['value']]=
        array(
          'value' => $item['value'],
          'text' => ($item['value'] ? $item['textEnglish'] : "(No category)"),
          'count'=> 0
        );
      if ($item['value']) {
        $this->_css.=
           ".category_".$item['value']." { "
          ."color:#".$item['color_text']."; "
          ."background-color:#".$item['color_background'].";} /* ".$item['textEnglish']." */";
      }
    }
    Page::push_content('style',"/* Style for category highlighting */\n".$this->_css);
    foreach ($this->_arr_cal as &$item) {
      foreach ($item['evt'] as &$event) {
        $csv_arr = explode(",",$event['category']);
        foreach ($csv_arr as $csv) {
          if (!isset($this->_categories[trim($csv)])) {
            $this->_categories[trim($csv)] =
              array(
                'value' =>    trim($csv),
                'text' =>     str_replace(array('-','_'),' ',title_case_string(trim($csv))),
                'count' =>    0
              );
          }
          $this->_categories[trim($csv)]['count']++;
        }
      }
    }
    sort($this->_categories);
  }

  protected function _setup_load_events(){
    $Obj_Event =        new Event;
    $this->_arr_cal =   $Obj_Event->get_calendar_dates($this->_MM,$this->_YYYY,$this->_memberID);
  }

  protected function _setup_load_permissions(){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $this->_isAdmin =   ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
  }

  protected function _setup_load_popup_sizes(){
    if ($this->_isAdmin){
      $this->_popup_size[$this->_event_report_name] =    get_popup_size($this->_event_report_name);
    }
  }

  protected function _shared_source_link($evt){
    return
      ($evt['systemID']!=SYS_ID ?
         "<div class='fl shared' title=\"External content from ".str_replace('& ','&amp; ',$evt['systemTitle'])."\">"
        ."<img src='".BASE_PATH."img/sysimg/icon_shared.gif' style='padding:2px;height:13px;width:15px' alt=\"External content from ".str_replace('& ','&amp; ',$evt['systemTitle'])."\" />"
        ."</div>"
       : ""
       );
  }

  public function get_version(){
    return VERSION_COMPONENT_CALENDAR_LARGE;
  }

}
?>