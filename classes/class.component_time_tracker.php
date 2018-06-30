<?php
  define ("VERSION_COMPONENT_TIME_TRACKER","1.0.3");
/*
Version History:
  1.0.3 (2012-10-28)
    1) Changes to use jquery for elemet selection, not prototypejs
  1.0.2 (2012-10-09)
    1) Now has link to edit categories used
  1.0.1 (2012-10-05)
    1) Better error handling for missing listtype
    2) Improved formatting of form data
  1.0.0 (2012-10-05)
    1) Initial release
*/
class Component_Time_Tracker extends Component_Base {
  protected $_categories;
  protected $_listTypeID;
  protected $_postings;
  protected $_personID;

  function __construct(){
    $this->_ident =            "time_tracker";
    $this->_parameter_spec =   array(
      'category_listtype' => array('match' => '',           'default' =>'lst_time_tracker_category',    'hint'=>'Listtype to use for categories'),
      'date_show' =>         array('match' => 'enum|0,1',   'default' =>'1',                            'hint'=>'0|1'),
      'heading_show' =>      array('match' => 'enum|0,1',   'default' =>'1',                            'hint'=>'0|1'),
      'heading_text' =>      array('match' => '',           'default' =>'Time Tracker',                 'hint'=>'Heading to use for component')
    );
  }

  function draw($instance='', $args=array(), $disable_params=false) {
    global $system_vars;
    try{
      $this->_setup($instance,$args,$disable_params);
    }
    catch (Exception $e){
      $this->_html.= "<p class='error'><b>Error:</b> ".$e->getMessage()."</p>";
      return $this->_render();
    }
    $this->_do_submode();
    $this->_draw_css();
    $this->_draw_js();
    $this->_draw_control_panel(true);
    $this->_draw_heading();
    $this->_draw_category_edit_link();
    $this->_draw_date();
    $this->_draw_form();
    return $this->_render();
  }

  protected function _draw_category_edit_link(){
    $_report_name =   "listtype";
    $_popup_size =    get_popup_size($_report_name);
    $this->_html.=
      "<div><a "
     ."onmouseover=\"window.status='Edit Categories';return true;\" "
     ."onmouseout=\"window.status='';return true;\" "
     ."href=\"#\" onclick=\"details('".$_report_name."','".$this->_listTypeID."','".$_popup_size['h']."','".$_popup_size['w']."','','');return false;\">"
     ."Edit categories"
     ."</a></div>";
  }

  protected function _draw_css(){
    Page::push_content(
       'style',
       "#".$this->_safe_ID." h2                          { margin: 0.25em 0; }"
      ."#".$this->_safe_ID." table                       { border-collapse: collapse; width: 100%; }"
      ."#".$this->_safe_ID." table thead tr th           { border: 1px solid #000; background: #A0A0FF; color: #ffffff; }"
      ."#".$this->_safe_ID." table tbody tr td           { border: 1px solid #000; }"
      ."#".$this->_safe_ID." table tbody tr td.go        { width: 18px; cursor: pointer; background:#ffd0d0; }"
      ."#".$this->_safe_ID." table tbody tr td.go img    { background-position: -7773px 0px; }"
      ."#".$this->_safe_ID." table tbody tr td.category  { width: 170px; color: #800000; font-weight: bold; }"
      ."#".$this->_safe_ID." table tbody tr td.click     { cursor: pointer; }"
      ."#".$this->_safe_ID." table tbody tr td.clock     { width: 56px; font-weight: normal; background:#ffd0d0; }"
      ."#".$this->_safe_ID." table tbody tr td           { }"
      ."#".$this->_safe_ID." table tr td input           { text-align: right; }"
      ."#".$this->_safe_ID." table tr.active td.go       { background: #d0ffd0; }"
      ."#".$this->_safe_ID." table tr.active td.go img   { background-position: -7789px 0px; }"
      ."#".$this->_safe_ID." table tr.active td.clock    { background: #d0ffd0; }"
      ."#".$this->_safe_ID." table tr.active td.category { color: #004000; }"
    );
  }

  protected function _draw_date(){
    global $system_vars;
    if ($this->_cp['date_show']==0){
      return;
    }
    $this->_html.=
      "<p>".date($system_vars['defaultDateFormat'],time($this->_date))."</p>\n";
  }

  protected function _draw_form(){
//    y($this->_categories);
    $this->_html.=
       "<table cellpadding='2' cellspacing='0' border='1'>"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th>[ICON]16 16 7773 Timer Control[/ICON]</th>\n"
      ."      <th>Category</th>\n"
      ."      <th>Task</th>\n"
      ."      <th>Output</th>\n"
      ."      <th>Benefit</th>\n"
      ."      <th>Time</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n"
      ;
    foreach($this->_categories as $k=>$c){
      if ($c['isHeader']==0){
        $v = seconds_format($c['elapsed_seconds']);
        $this->_html.=
           "  <tr id=\"tt_row_".$k."\""
          ." style=\"background:#".$c['color_background'].";color:#".$c['color_text'].";\""
          .">\n"
          ."    <td class='go click' onclick=\"tt.click('".$k."')\"><img src=\"".BASE_PATH."img/spacer\" class=\"icons\" width=\"16\" height=\"16\" alt=\"Timer Control\"/></td>\n"
          ."    <td class='category click' onclick=\"tt.click('".$k."')\">".$c['textEnglish']."</td>\n"
          ."    <td>Task details</td>\n"
          ."    <td>Output</td>\n"
          ."    <td>Benefit</td>\n"
          ."    <td class='clock'>".draw_form_field('tt_time_'.$k,$v,'text',50,'','',"onfocus='this.blur()' onselect='this.blur();'")."</td>\n"
          ."  </tr>\n";
      }
    }
    $this->_html.=
       "  </tbody>\n"
      ."</table>";
  }
  protected function _draw_heading(){
    if ($this->_cp['heading_show']==0){
      return;
    }
    $this->_html.=
      "<h2>".$this->_cp['heading_text']."</h2>\n";
  }

  protected function _draw_js(){
    global $page_vars;
    $id_arr = array();
    foreach ($this->_categories as $c){
      if ($c['isHeader']==0){
        $id_arr[] = "[".$c['ID'].",'".$c['value']."']";
      }
    }
    $js =
       "function tt(){\n"
      ."  this.categories = [".implode(',',$id_arr)."]\n"
      ."  this.active = false;\n"
      ."  self.timer_handle = false;\n"
      ."}\n"
      ."tt.prototype.click = function(id){\n"
      ."  var self = this;\n"
      ."  if (self.active){\n"
      ."    self.stop();\n"
      ."  }\n"
      ."  if(\$J('#tt_row_'+id)[0].className=='active'){\n"
      ."    \$J('#tt_row_'+id)[0].className='';\n"
      ."  }\n"
      ."  else {\n"
      ."    for(var i=0; i<self.categories.length; i++){\n"
      ."      if (id==self.categories[i][0]){\n"
      ."        \$J('#tt_row_'+self.categories[i][0])[0].className='active';\n"
      ."        self.active=id;\n"
      ."      }\n"
      ."      else{\n"
      ."        \$J('#tt_row_'+self.categories[i][0])[0].className='';\n"
      ."      }\n"
      ."    }\n"
      ."  }\n"
      ."  if (self.active){\n"
      ."    self.start();\n"
      ."  }\n"
      ."}\n"
      ."tt.prototype.save = function(ID){\n"
      ."  var post_vars, xFn;\n"
      ."  var self = this;\n"
      ."  post_vars = 'submode=save_timer&ID='+ID+'&target_value='+self.get_elapsed();\n"
      ."  xFn = function() { };\n"
      ."  ajax_post('".BASE_PATH.trim($page_vars['path'],'/')."','tt_status',post_vars,xFn);\n"
      ."}\n"
      ."tt.prototype.start = function(){\n"
      ."  var self = this;\n"
      ."  self.timer_handle = setTimeout(function(){self.timer();},1000);\n"
      ."}\n"
      ."tt.prototype.stop = function(){\n"
      ."  var self = this;\n"
      ."  self.save(self.active);\n"
      ."  self.active = false;\n"
      ."  if (self.timer_handle){\n"
      ."    clearInterval(self.timer_handle);\n"
      ."  }\n"
      ."}\n"
      ."tt.prototype.get_elapsed = function(){\n"
      ."  var self = this;\n"
      ."  return hhmmss_to_s(\$J('#tt_time_'+self.active)[0].value);\n"
      ."}\n"
      ."tt.prototype.timer = function(){\n"
      ."  var self = this;\n"
      ."  var secs = self.get_elapsed()+0;\n"
      ."  if (secs%60==0){\n"
      ."    self.save(self.active);\n"
      ."  }\n"
      ."  secs++;\n"
      ."  if (self.active){\n"
      ."    \$J('#tt_time_'+self.active)[0].value=s_to_hhmmss(secs);\n"
      ."    self.start();\n"
      ."  }\n"
      ."}\n"
      ."function hhmmss_to_s(str){\n"
      ."  var p=str.split(':'), s=0, m=1;\n"
      ."  while (p.length > 0) {\n"
      ."    s += m * parseFloat(p.pop());\n"
      ."    m *= 60;\n"
      ."  }\n"
      ."  return s;\n"
      ."}\n"
      ."function s_to_hhmmss(secs){\n"
      ."  var h,m,s;\n"
      ."  h = Math.floor(secs/3600);\n"
      ."  m = Math.floor((secs-(h*3600))/60);\n"
      ."  s = secs -(h*3600)-(m*60);\n"
      ."  if (h==0){\n"
      ."    return m+':'+lead_zero(s+'',2);\n"
      ."  }\n"
      ."  return h+':'+lead_zero(m+'',2)+':'+lead_zero(s+'',2);\n"
      ."}\n"

      ."var tt = new tt();\n";
    Page::push_content('javascript', $js);
  }

  protected function _do_save(){
    $ID =           get_var('ID');
    $seconds =      get_var('target_value');
    if (!isset($this->_categories[$ID])){
      return;
    }
    $postingID =    $this->_categories[$ID]['postingID'];
    $category =     $this->_categories[$ID]['value'];
    $Obj =          new Time_Tracker_Posting($postingID);
    $data = array(
      'systemID' =>             SYS_ID,
      'category' =>             $category,
      'effective_date_start' => $this->_date,
      'xml:elapsed_seconds' =>  $seconds,
      'personID' =>             $this->_personID
    );
    $this->_categories[$ID]['postingID'] = $Obj->update($data);
//    y($data);
//    $Obj
  }

  protected function _do_submode(){
    switch (get_var('submode')){
      case 'save_timer':
        $this->_do_save();
        die('Done');
      break;
    }
  }

  protected function _render(){
    return
      "<div id=\"".$this->_safe_ID."\">"
      .$this->_html
      ."</div>";
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_get_user();
    if (!$this->_personID){
      throw new Exception('You must log in to use this application');
    }
    $this->_setup_get_date();
    $this->_setup_load_categories();
    $this->_setup_load_postings();
    $this->_setup_map_categories_to_postings();
//    $this->_setup_load_user();
  }

  protected function _setup_get_date(){
    $this->_date = substr(get_timestamp(),0,10);
  }

  protected function _setup_get_user(){
    $this->_personID = get_userID();
  }

  protected function _setup_load_categories(){
    $Obj = new Time_Tracker_Category($this->_cp['category_listtype']);
    if (!$this->_listTypeID = $Obj->_get_listtypeID()){
      throw new Exception('No such listtype as '.$this->_cp['category_listtype']);
    }
    $records = $Obj->get_listdata();
    foreach ($records as $r){
      $this->_categories[$r['ID']] = $r;
    }
  }

  protected function _setup_load_postings(){
    $Obj = new Time_Tracker_Posting;
    $this->_postings = $Obj->get_postings_for_day($this->_personID, $this->_date);
  }

  protected function _setup_map_categories_to_postings(){
    foreach($this->_categories as &$c){
      $c['postingID'] =         0;
      $c['elapsed_seconds'] =   0;
      foreach ($this->_postings as $p){
        if ($c['value'] == $p['category']){
          $c['postingID'] =         $p['ID'];
          $c['elapsed_seconds'] =   $p['xml:elapsed_seconds'];
          break;
        }
      }
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_TIME_TRACKER;
  }
}

class Time_Tracker_Category extends lst_named_type{
  public function __construct($category) {
    parent::__construct(0, $category,'Time Tracker Category');
  }
}

class Time_Tracker_Posting extends Posting{
  public function __construct($ID="") {
    parent::__construct($ID);
    $this->_set_type('time_tracker_posting');
    $this->_set_assign_type('time_tracker_posting');
    $this->_set_object_name('Time Tracker Postingg');
  }
  public function get_postings_for_day($personID, $date){
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".SYS_ID." AND\n"
      ."  `type` = '".$this->_get_type()."' AND\n"
      ."  `personID` = ".$personID." AND\n"
      ."  `effective_date_start`='".$date."'";;
//    z($sql);
    $records = $this->get_records_for_sql($sql);
    foreach ($records as &$r){
      $this->xmlfields_decode($r);
    }
    return $records;
  }
}

?>