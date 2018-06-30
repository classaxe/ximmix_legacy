<?php
define ("VERSION_COMPONENT_CALENDAR_YEARLY","1.0.0");
/*
Version History:
  1.0.0 (2012-02-27)
    1) Initial release
*/
class Component_Calendar_Yearly extends Component_Base {

   public function __construct(){
    $this->_ident =             "calendar_yearly";
    $this->_parameter_spec = array(
      'spacing' =>              array('match' => '',		'default'=>10,           'hint'=>'0..x'),
      'width' =>                array('match' => '',		'default'=>800,          'hint'=>'1..x')
    );
  }



  public function draw($instance='', $args=array(), $disable_params=false) {
    // http://www.gaijin.at/en/scrphpcalj.php
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $isMASTERADMIN =    get_person_permission("MASTERADMIN");
    $isSYSADMIN =	    get_person_permission("SYSADMIN");
    $isSYSEDITOR =      get_person_permission("SYSEDITOR");
    $canPublish =       ($isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR);
    $month_arr =    array('January','February','March','April','May','June','July','August','September','October','November','December');
    $day_arr =      array('Su','Mo','Tu','We','Th','Fr','Sa');
    $this->_html.=
       "<table border=\"0\" cellspacing=\"".$this->_cp['spacing']."\">\n"
      ."  <tr>\n"
      ."    <th colspan=\"4\"><h1 style='margin:0'><a href=\"?YYYY=".($this->_YYYY-1)."\">&lt;</a> ".$this->_YYYY." <a href=\"?YYYY=".($this->_YYYY+1)."\">&gt;</a></h1></th>\n"
      ."  </tr>\n";
    for ($row=0; $row<3; $row++) {
      $this->_html.= '<tr>';
      for ($column=1; $column<=4; $column++) {
        $_MM =  $row*4+$column;
        $_DD =  date('w',mktime(0,0,0,$_MM,1,$this->_YYYY))-1;
        $_DIM = date('t',mktime(0,0,0,$_MM,1,$this->_YYYY));
        $width = floor($this->_cp['width']/(4*7));
        $this->_html.=
           "<td width=\"25%\" valign=\"top\">\n"
          ."  <table border=1 cellspacing='0' cellpadding='2' class='cal_yearly cal_table' style=\"border-collapse:collapse;font-size:8pt; font-family:Verdana;\"\">\n"
          ."    <thead>\n"
          ."      <tr>\n"
          ."        <th colspan=7 class='cal_head'>"
          .$month_arr[$_MM-1]
          ."</th>\n"
          ."      </tr>\n"
          ."      <tr>\n"
          ."        <th class='cal_days weekend' style=\"width:".$width."px\">".$day_arr[0]."</th>\n"
          ."        <th class='cal_days' style=\"width:".$width."px\">".$day_arr[1]."</th>\n"
          ."        <th class='cal_days' style=\"width:".$width."px\">".$day_arr[2]."</th>\n"
          ."        <th class='cal_days' style=\"width:".$width."px\">".$day_arr[3]."</th>\n"
          ."        <th class='cal_days' style=\"width:".$width."px\">".$day_arr[4]."</th>\n"
          ."        <th class='cal_days' style=\"width:".$width."px\">".$day_arr[5]."</th>\n"
          ."        <th class='cal_days weekend' style=\"width:".$width."px\">".$day_arr[6]."</th>\n"
          ."      </tr>\n"
          ."    </thead>\n"
          ."    <tr>\n";
        for($i=0; $i<=$_DD; $i++){
          $this->_html.="<td class='cal cal_then'>&nbsp;</td>\n";
        }
        $i=1;
        for($i=1; $i<=$_DIM; $i++){
          $_DOW=($i+$_DD)%7;
          $YYYY_MM_DD = $this->_YYYY.'-'.lead_zero($_MM,2).'-'.lead_zero($i,2);
          $events = count($this->_arr_cal[$YYYY_MM_DD]);
          $items = array();
          $category =    false;
          if ($events>0){
            foreach ($this->_arr_cal[$YYYY_MM_DD] as $event){
              $items[] = $event['title'];
            }
//            y($event);die;
            $category = "category_".$this->_arr_cal[$YYYY_MM_DD][0]['category'];
          }
          $tooltip =
            ($events>0 ?
              ($events>1 ? "EVENTS: (".$events.")\n" : "EVENT:\n")
              .implode("\n",$items)
             :
              ''
          );
          $this->_html.=
             "<td class='cal cal_current"
            .($events==1 ? " cal_has_event" : "")
            .($events>1 ? " cal_has_events" : "")
            .($events>0 ? " ".$category : "")
            .(($i==$this->_DD) && (lead_zero($_MM,2)==$this->_MM) ? " cal_today" : "")
            .($_DOW==0 || $_DOW==6 ? " cal_current_we" : "")
            ."'"
            .($tooltip ? " title=\"".$tooltip."\"" : "")
            .">"
            .$i
            ."</td>\n";
          if ($_DOW==6) $this->_html.= "</tr>\n<tr>\n";
        }
        if($_DIM+$_DD<35){
          for($i=$i; $i<35-$_DD; $i++){
            $this->_html.="<td class='cal cal_then'>&nbsp;</td>\n";
          }
          $this->_html.= "</tr><tr>";
          for($i=0; $i<7; $i++){
            $this->_html.="<td class='cal cal_then'>&nbsp;</td>\n";
          }
        }
        else {
          for($i=$i; $i<42-$_DD; $i++){
            $this->_html.="<td class='cal cal_then'>&nbsp;</td>\n";
          }
        }
        $this->_html.= '</tr>';
        $this->_html.= '</table>';
        $this->_html.= '</td>';
      }
      $this->_html.= '</tr>';
    }

    $this->_html.= '</table>';
    return $this->_html;
  }

  protected function _setup($instance, $args, $disable_params){
    global $MM, $YYYY, $DD, $page_vars;
    parent::_setup($instance, $args, $disable_params);
    $this->_categories =    array();
    $this->_css =           "";
    $this->_html =          "";
    $this->_DD =            $DD;
    $this->_MM =            $MM;
    $this->_YYYY =          $YYYY;
    $this->_memberID =     (isset($page_vars['memberID']) ?  $page_vars['memberID'] : 0);
    $this->_setup_load_events();
    $this->_setup_load_categories();
    $this->_safe_ID =       Component_Base::get_safe_ID($this->_ident,$this->_instance);
  }

  private function _setup_load_categories(){
    $Obj = new ListType;
    $Obj->_set_ID($Obj->get_ID_by_name('Event Category'));
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
          ."background-color:#".$item['color_background'].";} /* ".$item['textEnglish']." */\n";
      }
    }
    Page::push_content('style',"/* Style for category highlighting */\n".$this->_css);
    foreach ($this->_arr_cal as &$item) {
      foreach ($item as &$event) {
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
    $Obj_Event =            new Event;
    $this->_arr_cal =         $Obj_Event->get_yearly_dates($this->_YYYY,$this->_memberID);
  }

  public function get_version(){
    return VERSION_COMPONENT_CALENDAR_YEARLY;
  }
}
?>