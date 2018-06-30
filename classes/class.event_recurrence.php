<?php
define('VERSION_EVENT_RECURRENCE','1.0.12');
/*
Version History:
  1.0.12 (2013-12-10)
    1) Now uses Displayable_Item::_draw_render_JSON() instead of its own variant

  (Older version history in class.event_recurrence.txt)
*/
class Event_Recurrence extends Event {
  protected $_msg_no_change =   "No changes were made.";
  protected $_update_status =   "dates_unchanged";
  protected $_count_inserted =  0;
  protected $_count_deleted =   0;

  public function children_clone_for_dates(){
    if (!$this->_new_dates_arr){
      return;
    }
    if (!isset($this->record)){
      $this->load();
    }
    sscanf($this->record['effective_date_start'],"%4s-%2s-%2s",$YYYY, $MM, $DD);
    $start =    mktime(0, 0, 0, $MM, $DD, $YYYY);
    sscanf($this->record['effective_date_end'],"%4s-%2s-%2s",$YYYY, $MM, $DD);
    $end =      mktime(0, 0, 0, $MM, $DD, $YYYY);
    $dur =      ($end-$start)/(24*60*60);
    $parentID = $this->_get_ID();
    foreach($this->_new_dates_arr as $date){
      if (!in_array($date,$this->_old_dates_arr)){
        sscanf($date,"%4s-%2s-%2s",$YYYY, $MM, $DD);
        $start =  date('Y-m-d',mktime(0, 0, 0, $MM, $DD, $YYYY));
        $end =    date('Y-m-d',mktime(0, 0, 0, $MM, $DD+$dur, $YYYY));
        $newID =  $this->copy();
        $Obj_Event =    new Event($newID);
        $data =   array(
          'parentID' =>                 $parentID,
          'effective_date_start' =>     $start,
          'effective_date_end' =>       $end,
          'recur_description' =>        '',
          'recur_mode' =>               '',
          'recur_daily_mode' =>         '',
          'recur_daily_interval' =>     '',
          'recur_weekly_interval' =>    '',
          'recur_weekly_days_csv' =>    '',
          'recur_monthly_mode' =>       '',
          'recur_monthly_dd' =>         '',
          'recur_monthly_interval' =>   '',
          'recur_monthly_nth' =>        '',
          'recur_monthly_day' =>        '',
          'recur_yearly_mode' =>        '',
          'recur_yearly_mm' =>          '',
          'recur_yearly_dd' =>          '',
          'recur_yearly_interval' =>    '',
          'recur_yearly_nth' =>         '',
          'recur_yearly_day' =>         '',
          'recur_range_mode' =>         '',
          'recur_range_count' =>        '',
          'recur_range_end_by' =>       ''
        );
        $Obj_Event->update($data);
        $Obj_Event->set_path();
      }
    }
  }

  public function children_delete(){
    $children =     $this->get_children();
    $Obj_Event =    new Event;
    foreach ($children as $child){
      $Obj_Event->_set_ID($child['ID']);
      $date =   $Obj_Event->get_field('effective_date_start');
      if (!in_array($date, $this->_new_dates_arr)){
        $Obj_Event->delete();
      }
    }
  }

  public function children_get_dates(){
    $children =     $this->get_children();
    $Obj_Event =    new Event;
    $dates_arr =    array();
    foreach ($children as $child){
      $Obj_Event->_set_ID($child['ID']);
      $date =   $Obj_Event->get_field('effective_date_start');
      if (!in_array($date, $dates_arr)){
        $dates_arr[] = $date;
      }
    }
    sort($dates_arr);
    return $dates_arr;
  }

  public function children_set_recurrence_description(){
    $children =     $this->get_children();
    $description =  $this->get_field('recur_description');
    $Obj_Event =    new Event;
    $instance =     1;
    foreach ($children as $child){
      $Obj_Event->_set_ID($child['ID']);
      $Obj_Event->set_field('recur_description',$description.' (#'.($instance++).' of '.count($children).')',true,false);
    }
  }

  public static function sort_events_by_date($a, $b){
    if ($a['effective_date_start'] == $b['effective_date_start']) {
      return 0;
    }
    return ($a['effective_date_start'] > $b['effective_date_start']) ? +1 : -1;
  }

  public function draw(){
    $this->_height =    200;
    $this->_width =     650;
    if (!$this->_draw_setup()){
      $this->_draw_render();

      return;
    }
    $this->_draw_title();
    $this->_draw_status_message();
    if ($this->_update_status!='dates_unchanged'){
      $this->_js.=
        "window.setTimeout(function(){if(window.opener && window.opener.geid('form')){window.opener.geid('form').submit()};geid('form').submit()},1000);";
    }
    else{
      $this->_draw_js();
      $this->_draw_settings_mode();
      $this->_draw_settings_none();
      $this->_draw_settings_daily();
      $this->_draw_settings_weekly();
      $this->_draw_settings_monthly();
      $this->_draw_settings_yearly();
      $this->_draw_settings_range();
      $this->_draw_form_controls();
    }
    $this->_draw_render_JSON();
  }

  protected function _draw_save(){
    if (!get_var('submode')=='repeat_settings_submit'){
      return;
    }
    $this->_old_dates_arr = $this->children_get_dates();
    $this->set_recurrence_settings();
    $this->set_recurrence_description();
    $this->_new_dates_arr = $this->get_recurring_date_array();
    if (implode(',',$this->_old_dates_arr) == implode(',',$this->_new_dates_arr)){
      $this->_msg = $this->_msg_no_change;
      $this->_update_status = 'dates_unchanged';
      $this->_new_dates_arr = array();
      return;
    }
    $this->_update_status = 'dates_changed';
    $this->children_delete();
    $this->children_clone_for_dates();
    $this->children_set_recurrence_description();
    switch($this->_recur_mode){
      case '':
        $this->_msg = "<b>Success:</b> All repeats have been removed.";
      break;
      case 'daily':
        $this->_msg = "<b>Success:</b> Daily Repeat Settings have been applied.";
      break;
      case 'weekly':
        $this->_msg = "<b>Success:</b> Weekly Repeat Settings have been applied.";
      break;
      case 'monthly':
        $this->_msg = "<b>Success:</b> Monthly Repeat Settings have been applied.";
      break;
      case 'yearly':
        $this->_msg = "<b>Success:</b> Yearly Repeat Settings have been applied.";
      break;
    }
    $this->_msg.="<br /><br />Please wait while the form is reloaded...";
  }

  protected function _draw_setup(){
    $this->_set_ID(get_var('targetID'));
    $this->_css =       "";
    $this->_js =        "";
    $this->_html =      "";
    $this->_msg =       "";
    if (!$this->exists()){
      $this->_draw_error_invalid();
      return false;
    }
    $this->_draw_save();
    $this->load();
    return true;
  }

  protected function _draw_error_invalid(){
    $this->_html.=
       "<h1 style='margin:0.25em 0;'>Error</h1>\n"
      ."<p>Sorry - the specified event is unavailable.<br />\nPerhaps it was deleted?</p>"
      ."<div class='txt_c'>\n"
      ."<input type='button' id='recurrence_form_cancel' class='formButton' style='width:100px' value='Close' onclick=\"hidePopWin(null);response=false;\" />\n"
      ."</div>\n";
  }

  protected function _draw_form_controls(){
    if ($this->_update_status!='dates_unchanged'){
      return;
    }
    $this->_html.=
       "<div class='clear'>&nbsp;</div>\n"
      ."<div class='txt_c'>\n"
      ."<input type='button' id='recurrence_form_cancel' class='formButton' style='width:100px' value='Close' onclick=\"hidePopWin(null);response=false;\" />\n"
      ."<input type='button' id='recurrence_form_submit' class='formButton' style='width:100px' value='Save' onclick=\"repeat_settings_onsubmit(".$this->_get_ID().")\" />\n"
      ."</div>\n";
  }

  protected function _draw_js(){
    $this->_js.=
       "dropdown_range_field('recur_daily_interval',1,30,\"radio_group_set('recur_daily_mode','day')\");\n"
      ."dropdown_range_field('recur_weekly_interval',1,30);\n"
      ."dropdown_range_field('recur_monthly_dd',1,31,\"radio_group_set('recur_monthly_mode','day')\");\n"
      ."dropdown_range_field('recur_monthly_interval',1,30,\"geid_set('recur_monthly_interval2',geid_val('recur_monthly_interval'));radio_group_set('recur_monthly_mode','day')\");\n"
      ."dropdown_range_field('recur_monthly_interval2',1,30,\"geid_set('recur_monthly_interval',geid_val('recur_monthly_interval2'));radio_group_set('recur_monthly_mode','the')\");\n"
      ."dropdown_range_field('recur_yearly_dd',1,31,\"radio_group_set('recur_yearly_mode','on')\");\n"
      ."dropdown_range_field('recur_yearly_interval',1,30,\"geid_set('recur_yearly_interval2',geid_val('recur_yearly_interval'));radio_group_set('recur_yearly_mode','on')\");\n"
      ."dropdown_range_field('recur_yearly_interval2',1,30,\"geid_set('recur_yearly_interval',geid_val('recur_yearly_interval2'));radio_group_set('recur_yearly_mode','the')\");\n"
      ."dropdown_range_field('recur_range_count',1,30,\"radio_group_set('recur_range_mode','endafter')\");\n"
      ;
  }

  protected function _draw_settings_mode(){
    $recur_mode_arr = array(
      array('text'=>'(None)', 'value'=>'',       'color_background'=>'e8e8e8'),
      array('text'=>'Daily',  'value'=>'daily',  'color_background'=>'e0ffe0'),
      array('text'=>'Weekly', 'value'=>'weekly', 'color_background'=>'ffff80'),
      array('text'=>'Monthly','value'=>'monthly','color_background'=>'ffc0a0'),
      array('text'=>'Yearly', 'value'=>'yearly', 'color_background'=>'ffd0d0')
    );
    $this->_html.=
       "<div style='float:left;'>\n"
      ."<fieldset style='width:100px;height:".$this->_height."px;padding:2px;'>\n"
      ."<legend>Pattern</legend><p>\n"
      .Report_Column::draw_radio_selector(
         'recur_mode',
         $this->record['recur_mode'],
         $recur_mode_arr,
         100,
         "onclick=\"repeat_settings_mode('".$this->record['effective_date_start']."')\"",
         1,
         1
       )
      ."</p></fieldset>\n"
      ."</div>";
  }

  protected function _draw_settings_none(){
    $this->_html.=
       "<div id='repeat_settings_'  style='float:left;"
      .($this->record['recur_mode']=='' ? '' : "display:none")
      ."'>\n"
      ."</div>";
  }


  protected function _draw_settings_daily(){
    $daily_mode_arr = array(
      array('text'=>"Every <label>"
      .draw_form_field('recur_daily_interval',$this->record['recur_daily_interval'],'text',30)
      ." day(s)</label>", 'value'=>'day',    'color_background'=>'ffffff'),
      array('text'=>'Every Week Day',  'value'=>'weekday',  'color_background'=>'ffffff'),
      array('text'=>'Every Weekend Day',  'value'=>'weekendday',  'color_background'=>'ffffff')
    );
    $this->_html.=
       "<div id='repeat_settings_daily' style='float:left;"
      .($this->record['recur_mode']=='daily' ? '' : "display:none")
      ."'>\n"
      ."<fieldset style='width:".($this->_width-140)."px;height:".(($this->_height/2)-4)."px;padding:2px;'>\n"
      ."<legend>Daily Repeat Settings</legend>\n"
      ."<p>\n"
      .Report_Column::draw_radio_selector(
         'recur_daily_mode',
         $this->record['recur_daily_mode'],
         $daily_mode_arr,
         ($this->_width-150),
         "",
         1,
         1
       )
      ."</p>"
      ."</fieldset>"
      ."</div>";
  }

  protected function _draw_settings_weekly(){
    $this->_html.=
       "<div id='repeat_settings_weekly' style='float:left;"
      .($this->record['recur_mode']=='weekly' ? '' : "display:none")
      ."'>\n"
      ."<fieldset style='width:".($this->_width-140)."px;height:".(($this->_height/2)-4)."px;padding:2px;'>\n"
      ."<legend>Weekly Repeat Settings</legend>\n"
      ."<p>\n"
      ."<label>Every\n"
      .draw_form_field('recur_weekly_interval',$this->record['recur_weekly_interval'],'text',30)
      ." week(s)</label> &nbsp; &nbsp; on "
      .draw_form_field(
         'recur_weekly_days_csv',$this->record['recur_weekly_days_csv'],'checkbox_csvlist',
         $this->_width-140,'','','','',false,'','Sun,Mon,Tue,Wed,Thu,Fri,Sat',60
       )
      ."</p>"
      ."</fieldset>"
      ."</div>";
  }

  protected function _draw_settings_monthly(){
    $monthly_mode_arr = array(
      array(
        'text' =>
           "<span style='display:inline-block;width:60px'>Day</span>"
          ."<span style='display:inline-block;width:180px'><label>"
          .draw_form_field('recur_monthly_dd',$this->record['recur_monthly_dd'],'text',30)
          ."</label></span><label>of every "
          .draw_form_field('recur_monthly_interval',$this->record['recur_monthly_interval'],'text',30)
          ." month(s)</label><br />",
        'value' =>              'day',
        'color_background' =>   'ffffff'
      ),
      array(
        'text' =>
           "<span style='display:inline-block;width:60px'>The</span>"
          ."<span style='display:inline-block;width:180px'><label>"
          .draw_form_field('recur_monthly_nth',$this->record['recur_monthly_nth'],'selector_csvlist',80,'','',"onchange=\"radio_group_set('recur_monthly_mode','the')\"",'','','','1|first|ffffff,2|second|ffffff,3|third|ffffff,4|fourth|ffffff,5|last|ffffff')
          .draw_form_field('recur_monthly_day',$this->record['recur_monthly_day'],'selector_csvlist',80,'','',"onchange=\"radio_group_set('recur_monthly_mode','the')\"",'','','','0|Sunday|ffffff,1|Monday|ffffff,2|Tuesday|ffffff,3|Wednesday|ffffff,4|Thursday|ffffff,5|Friday|ffffff,6|Saturday|ffffff')
          ."</label></span><label>of every "
          .draw_form_field('recur_monthly_interval2',$this->record['recur_monthly_interval'],'text',30)
          ." month(s)</label><br />",
        'value' =>              'the',
        'color_background' =>   'ffffff'
      )
    );
    $this->_html.=
       "<div id='repeat_settings_monthly' style='float:left;"
      .($this->record['recur_mode']=='monthly' ? '' : "display:none")
      ."'>\n"
      ."<fieldset style='width:".($this->_width-140)."px;height:".(($this->_height/2)-4)."px;padding:2px;'>\n"
      ."<legend>Monthly Repeat Settings</legend>\n"
      ."<p>\n"
      .Report_Column::draw_radio_selector(
         'recur_monthly_mode',
         $this->record['recur_monthly_mode'],
         $monthly_mode_arr,
         ($this->_width-150),
         "",
         1,
         1
       )
      ."</p>"
      ."</fieldset>"
      ."</div>";
  }

  protected function _draw_settings_yearly(){
    $months_csv =       '1|January,2|February,3|March,4|April,5|May,6|June,7|July,8|August,9|September,10|October,11|November,12|December';
    $yearly_mode_arr =  array(
      array(
        'text' =>
           "<span style='display:inline-block;width:60px'>On</span>"
          ."<span style='display:inline-block;width:280px'>\n"
          ."<label>"
          .draw_form_field('recur_yearly_mm',$this->record['recur_yearly_mm'],'selector_csvlist',80,'','',"onchange=\"geid_set('recur_yearly_mm2',geid_val('recur_yearly_mm'));radio_group_set('recur_yearly_mode','on')\"",'','','',$months_csv)
          ."</label>\n"
          ."<label>"
          .draw_form_field('recur_yearly_dd',$this->record['recur_yearly_dd'],'text',30)
          ."</label>\n"
          ."</span>"
          ."<label>every "
          .draw_form_field('recur_yearly_interval',$this->record['recur_yearly_interval'],'text',30)
          ." year(s)</label><br />",
        'value' =>              'on',
        'color_background' =>   'ffffff'
      ),
      array(
        'text' =>
           "<span style='display:inline-block;width:60px'>The</span>"
          ."<span style='display:inline-block;width:280px'><label>"
          .draw_form_field('recur_yearly_nth',$this->record['recur_yearly_nth'],'selector_csvlist',80,'','',"onchange=\"radio_group_set('recur_yearly_mode','the')\"",'','','','1|first|ffffff,2|second|ffffff,3|third|ffffff,4|fourth|ffffff,5|last|ffffff')
          .draw_form_field('recur_yearly_day',$this->record['recur_yearly_day'],'selector_csvlist',80,'','',"onchange=\"radio_group_set('recur_yearly_mode','the')\"",'','','','0|Sunday|ffffff,1|Monday|ffffff,2|Tuesday|ffffff,3|Wednesday|ffffff,4|Thursday|ffffff,5|Friday|ffffff,6|Saturday|ffffff')
          ."</label><label>in "
          .draw_form_field('recur_yearly_mm2',$this->record['recur_yearly_mm'],'selector_csvlist',80,'','',"onchange=\"geid_set('recur_yearly_mm',geid_val('recur_yearly_mm2'));radio_group_set('recur_yearly_mode','the')\"",'','','',$months_csv)
          ."</label></span><label>every "
          .draw_form_field('recur_yearly_interval2',$this->record['recur_yearly_interval'],'text',30)
          ." year(s)</label>",
        'value' =>              'the',
        'color_background' =>   'ffffff'
      )
    );
    $this->_html.=
       "<div id='repeat_settings_yearly' style='float:left;"
      .($this->record['recur_mode']=='yearly' ? '' : "display:none")
      ."'>\n"
      ."<fieldset style='width:".($this->_width-140)."px;height:".(($this->_height/2)-4)."px;padding:2px;'>\n"
      ."<legend>Yearly Repeat Settings</legend>\n"
      ."<p>\n"
      .Report_Column::draw_radio_selector(
         'recur_yearly_mode',
         $this->record['recur_yearly_mode'],
         $yearly_mode_arr,
         ($this->_width-150),
         "",
         1,
         1
       )
      ."</p>"
      ."</fieldset>"
      ."</div>";
  }

  protected function _draw_settings_range(){
    $months_csv =       '1|January,2|February,3|March,4|April,5|May,6|June,7|July,8|August,9|September,10|October,11|November,12|December';
    $range_mode_arr =  array(
      array(
        'text' =>
           "<span style='display:inline-block;width:80px;'>\n"
          ."Repeat</span>"
          .draw_form_field('recur_range_count',$this->record['recur_range_count'],'text',30)
          ." time(s)\n"
          ."<br />",
        'value' =>              'endafter',
        'color_background' =>   'ffffff'
      ),
      array(
        'text' =>
           "<span style='display:inline-block;width:80px'>\n"
          ."Repeat until</span>"
          .draw_form_field('recur_range_end_by',$this->record['recur_range_end_by'],'date',80,'','',"onchange=\"radio_group_set('repeat_settings_range','endby')\"")
          ."\n",
        'value' =>              'endby',
        'color_background' =>   'ffffff'
      )
    );
    $this->_html.=
       "<div id='repeat_settings_range' style='float:left;"
      .($this->record['recur_mode']!='' ? '' : "display:none;")
      ."'>\n"
      ."<fieldset style='width:".($this->_width-140)."px;height:".(($this->_height/2)-4)."px;padding:2px;'>\n"
      ."<legend>Repetition Limits</legend>\n"
      ."<p>\n"
      .Report_Column::draw_radio_selector(
         'recur_range_mode',
         $this->record['recur_range_mode'],
         $range_mode_arr,
         ($this->_width-150),
         "",
         1,
         1
       )
      ."</p>"
      ."</fieldset>"
      ."</div>";
  }

  protected function _draw_status_message(){
    $no_close = ($this->_update_status=='dates_unchanged' ? 0 : 1);
    $this->_html.=
       "<div style='width:".($this->_width-12)."px'>"
      .HTML::draw_status('recurrence_settings_msg',$this->_msg,false,false,$no_close)
      ."</div>\n";
  }

  protected function _draw_title(){
    $this->_html.=
       "<h1 style='margin:0.25em 0;'>"
      ."Repeat \"".$this->record['title']."\""
      ." (".format_date($this->record['effective_date_start']).")"
      ."</h1>\n";
  }

  function get_recurring_date_array(){
    $this->record = $this->load(false,false);
    if ($this->record['recur_mode']==''){
      return array();
    }
    switch ($this->record['recur_mode']){
      case 'daily':
        $this->_get_recurring_date_array_daily();
      break;
      case 'weekly':
        $this->_get_recurring_date_array_weekly();
      break;
      case 'monthly':
        $this->_get_recurring_date_array_monthly();
      break;
      case 'yearly':
        $this->_get_recurring_date_array_yearly();
      break;
    }
    return $this->_result;
  }

  private function _get_recurring_date_array_daily(){
    $this->_result =    array();
    sscanf($this->record['effective_date_start'],"%4s-%2s-%2s",$YYYY, $MM, $DD);
    switch ($this->record['recur_daily_mode']){
      case 'day':
        $step_d =   $this->record['recur_daily_interval'];
        $valid =    array(0,1,2,3,4,5,6);
      break;
      case 'weekday':
        $step_d =   1;
        $valid =    array(1,2,3,4,5);
      break;
      case 'weekendday':
        $step_d =   1;
        $valid =    array(0,6);
      break;
    }
    switch($this->record['recur_range_mode']){
      case 'endafter':
        $count = 0;
        while($count<$this->record['recur_range_count']){
          $valid_day = false;
          while(!$valid_day){
            $current =      mktime(0, 0, 0, $MM, $DD, $YYYY);
            $day =          date('w',$current);
            $valid_day =    in_array($day,$valid);
            $DD +=          $step_d;
          }
          $date =   date('Y-m-d',$current);
          if ($date!=$this->record['effective_date_start']){
            $this->_result[] = $date;
            $count++;
          }
        }
      break;
      case 'endby':
        $date =  $this->record['effective_date_start'];
        while($date<=$this->record['recur_range_end_by']){
          $valid_day = false;
          while(!$valid_day){
            $current =      mktime(0, 0, 0, $MM, $DD, $YYYY);
            $day =          date('w',$current);
            $valid_day =    in_array($day,$valid);
            $DD +=          $step_d;
          }
          $date =   date('Y-m-d',$current);
          if ($date<=$this->record['recur_range_end_by'] && $date!=$this->record['effective_date_start']){
            $this->_result[] = $date;
          }
        }
      break;
    }
  }

  private function _get_recurring_date_array_weekly(){
    $this->_result =    array();
    sscanf($this->record['effective_date_start'],"%4s-%2s-%2s",$YYYY, $MM, $DD);
    $days_arr = explode(',','Sun,Mon,Tue,Wed,Thu,Fri,Sat');
    $valid =    array();
    $recur_days_csv =   str_replace(' ','',$this->record['recur_weekly_days_csv']);
    $days =     explode(',',$recur_days_csv);
    foreach ($days as $day){
      $valid[] = array_search($day, $days_arr);
    }
    $step_d = 1;
    $step_w = $this->record['recur_weekly_interval'];
    switch($this->record['recur_range_mode']){
      case 'endafter':
        $count =    0;
        while($count<$this->record['recur_range_count']){
          $valid_day =  false;
          $week =       0;
          while(!$valid_day){
            $current =      mktime(0, 0, 0, $MM, $DD, $YYYY);
            $day =          date('w',$current);
            if ($day==0){
              $week++;
            }
            $valid_day =    in_array($day,$valid) && ($week%$step_w==0);
            $DD +=          $step_d;
          }
          $date =   date('Y-m-d',$current);
          if ($date!=$this->record['effective_date_start']){
            $this->_result[] = $date;
            $count++;
          }
        }
      break;
      case 'endby':
        $date =  $this->record['effective_date_start'];
        while($date<=$this->record['recur_range_end_by']){
          $valid_day = false;
          $week =       0;
          while(!$valid_day){
            $current =      mktime(0, 0, 0, $MM, $DD, $YYYY);
            $day =          date('w',$current);
            if ($day==0){
              $week++;
            }
            $valid_day =    in_array($day,$valid) && ($week%$step_w==0);
            $DD +=          $step_d;
          }
          $date =   date('Y-m-d',$current);
          if ($date<=$this->record['recur_range_end_by'] && $date!=$this->record['effective_date_start']){
            $this->_result[] = $date;
          }
        }
      break;
    }
  }

  private function _get_recurring_date_array_monthly(){
    $this->_result =    array();
    sscanf($this->record['effective_date_start'],"%4s-%2s-%2s",$YYYY, $MM, $DD);
    switch ($this->record['recur_monthly_mode']){
      case 'day':
        $DD =           $this->record['recur_monthly_dd'];
        switch($this->record['recur_range_mode']){
          case 'endafter':
            $count = 0;
            while($count<$this->record['recur_range_count']){
              $MM+=         $this->record['recur_monthly_interval'];
              $current =    mktime(0, 0, 0, $MM, $DD, $YYYY);
              $last_dd =    date('t',mktime(0,0,0,$MM,1,$YYYY));
              if ($DD>$last_dd){
                $current =    mktime(0, 0, 0, $MM, $last_dd, $YYYY);
              }
              $date =   date('Y-m-d',$current);
              if ($date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
                $count++;
              }
            }
          break;
          case 'endby':
            $date =  $this->record['effective_date_start'];
            while($date<=$this->record['recur_range_end_by']){
              $MM+=         $this->record['recur_monthly_interval'];
              $current =    mktime(0, 0, 0, $MM, $DD, $YYYY);
              $last_dd =    date('t',mktime(0,0,0,$MM,1,$YYYY));
              if ($DD>$last_dd){
                $current =    mktime(0, 0, 0, $MM, $last_dd, $YYYY);
              }
              $date =   date('Y-m-d',$current);
              if ($date<=$this->record['recur_range_end_by'] && $date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
              }
            }
          break;
        }
      break;
      case 'the':
        $DOW =  $this->record['recur_monthly_day'];
        $nth =  $this->record['recur_monthly_nth'];
        switch($this->record['recur_range_mode']){
          case 'endafter':
            $count = 0;
            while($count<$this->record['recur_range_count']){
              $MM+=         $this->record['recur_monthly_interval'];
              $date =       $this->_get_Xth_DOW($DOW,$nth,$MM,$YYYY);
              if ($date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
                $count++;
              }
            }
          break;
          case 'endby':
            $date =  $this->record['effective_date_start'];
            while($date<=$this->record['recur_range_end_by']){
              $MM+=         $this->record['recur_monthly_interval'];
              $date =       $this->_get_Xth_DOW($DOW,$nth,$MM,$YYYY);
              if ($date<=$this->record['recur_range_end_by'] && $date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
              }
            }
          break;
        }
      break;
    }
  }

  private function _get_recurring_date_array_yearly(){
    $this->_result =    array();
    sscanf($this->record['effective_date_start'],"%4s-%2s-%2s",$YYYY, $MM, $DD);
    switch ($this->record['recur_yearly_mode']){
      case 'on':
        $MM =           $this->record['recur_yearly_mm'];
        $DD =           $this->record['recur_yearly_dd'];
        switch($this->record['recur_range_mode']){
          case 'endafter':
            $count = 0;
            while($count<$this->record['recur_range_count']){
              $YYYY+=       $this->record['recur_yearly_interval'];
              $current =    mktime(0, 0, 0, $MM, $DD, $YYYY);
              $last_dd =    date('t',mktime(0,0,0,$MM,1,$YYYY));
              if ($DD>$last_dd){
                $current =  mktime(0, 0, 0, $MM, $last_dd, $YYYY);
              }
              $date =       date('Y-m-d',$current);
              if ($date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
                $count++;
              }
            }
          break;
          case 'endby':
            $date =  $this->record['effective_date_start'];
            while($date<=$this->record['recur_range_end_by']){
              $YYYY+=       $this->record['recur_yearly_interval'];
              $current =    mktime(0, 0, 0, $MM, $DD, $YYYY);
              $last_dd =    date('t',mktime(0,0,0,$MM,1,$YYYY));
              if ($DD>$last_dd){
                $current =  mktime(0, 0, 0, $MM, $last_dd, $YYYY);
              }
              $date =       date('Y-m-d',$current);
              if ($date<=$this->record['recur_range_end_by'] && $date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
              }
            }
          break;
        }
      break;
      case 'the':
        $DOW =  $this->record['recur_yearly_day'];
        $nth =  $this->record['recur_yearly_nth'];
        $MM =   $this->record['recur_yearly_mm'];
        switch($this->record['recur_range_mode']){
          case 'endafter':
            $count = 0;
            while($count<$this->record['recur_range_count']){
              $YYYY+=       $this->record['recur_yearly_interval'];
              $date =       $this->_get_Xth_DOW($DOW,$nth,$MM,$YYYY);
              if ($date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
                $count++;
              }
            }
          break;
          case 'endby':
            $date =  $this->record['effective_date_start'];
            while($date<=$this->record['recur_range_end_by']){
              $YYYY+=       $this->record['recur_yearly_interval'];
              $date =       $this->_get_Xth_DOW($DOW,$nth,$MM,$YYYY);
              if ($date<=$this->record['recur_range_end_by'] && $date!=$this->record['effective_date_start']){
                $this->_result[] = $date;
              }
            }
          break;
        }
      break;
    }
  }

  private function _get_Xth_DOW($DOW,$NTH,$MM,$YYYY){
    // Thanks Joe Dundas of Chicago, IL:
    // http://filchiprogrammer.wordpress.com/2008/02/27/getting-the-first-monday-of-the-month/
    $numDays =  date('t',mktime(0,0,0,$MM,1,$YYYY));
    $add =      7*($NTH-1);
    $firstDOW = date('w',mktime(0,0,0,$MM,1,$YYYY));
    $diff =     $firstDOW-$DOW;
    $DD =       1;
    if($diff > 0) {
      $DD += (7-$diff);
    }
    else if ($diff < 0) {
      $DD += -1*$diff;
    }
    $DD = $DD + $add;
    while($DD > $numDays) {
      $DD -= 7;
    }
    return  date('Y-m-d', mktime(0, 0, 0, $MM, $DD, $YYYY));
  }

  static function form_field_recurrence_settings($width, $bulk_update, $row){
    $_div_open =      "<div style='float:left;height:21px; font-size:80%'>";
    if (!isset($row['ID'])){
      return
         $_div_open
        ."None - you must save this Event first before trying to make it repeat."
        ."</div>"
        ."<div class='clear'>&nbsp;</div>";
    }
    $ID =             ($row['parentID'] ? $row['parentID'] : $row['ID']);
    $field_rdesc =    'recur_description';
    $value_rdesc =    (isset($row[$field_rdesc]) ? $row[$field_rdesc] : '');
    $link =           "<a href='#' onclick=\"return repeat_settings_dialog('".$ID."')\">";
    $text =           "No repeats ".$link."<b>(Change...)</b></a>";
    if ($row['parentID']){
      $text = "<b>Occurrence:</b> ".$value_rdesc." ".$link."<b>(Change...)</b></a>";
    }
    else if ($value_rdesc) {
      $text = "<b>Series:</b> ".$value_rdesc." ".$link."<b>(Change...)</b></a>";
    }
    return
       $_div_open
      .$text
      ."</div>"
      ."<div class='clear'>&nbsp;</div>";
  }

  protected function promote_first_child(){
    $master =       $this->load();
    $children =     $this->get_children();
    $first_child =  array_shift($children);
    $Obj_first_child = new Event_Recurrence($first_child['ID']);
    if (!count($children)){
      // No more children, promote this one to stand-alone and quit
      $data = array(
        'parentID' =>               0,
        'recur_description' =>      ''
      );
      $Obj_first_child->update($data);
      $Obj_first_child->set_recurrence_description();
      return $first_child['ID'];
    }
    $data = array(
      'parentID' =>               0,
      'recur_mode' =>             $master['recur_mode'],
      'recur_daily_mode' =>       $master['recur_daily_mode'],
      'recur_daily_interval' =>   $master['recur_daily_interval'],
      'recur_weekly_interval' =>  $master['recur_weekly_interval'],
      'recur_weekly_days_csv' =>  $master['recur_weekly_days_csv'],
      'recur_monthly_mode' =>     $master['recur_monthly_mode'],
      'recur_monthly_dd' =>       $master['recur_monthly_dd'],
      'recur_monthly_interval' => $master['recur_monthly_interval'],
      'recur_monthly_nth' =>      $master['recur_monthly_nth'],
      'recur_monthly_day' =>      $master['recur_monthly_day'],
      'recur_yearly_interval' =>  $master['recur_yearly_interval'],
      'recur_yearly_mode' =>      $master['recur_yearly_mode'],
      'recur_yearly_mm' =>        $master['recur_yearly_mm'],
      'recur_yearly_dd' =>        $master['recur_yearly_dd'],
      'recur_yearly_nth' =>       $master['recur_yearly_nth'],
      'recur_yearly_day' =>       $master['recur_yearly_day'],
      'recur_range_mode' =>       $master['recur_range_mode'],
      'recur_range_count' =>      ($master['recur_range_count']>0 ? $master['recur_range_count']-1 : 0),
      'recur_range_end_by' =>     $master['recur_range_end_by']
    );
    $Obj_first_child->update($data);
    $Obj_first_child->set_recurrence_description();
    foreach($children as $child){
      $Obj = new Event_Recurrence($child['ID']);
      $Obj->set_field('parentID',$first_child['ID'],true,false);
    }
    $Obj_first_child->children_set_recurrence_description();
    return $first_child['ID'];
  }

  public function set_recurrence_description(){
    $this->_recur_description = '';
    $this->load();
    switch($this->record['recur_mode']){
      case '':
        // do nothing
      break;
      case 'daily':
        $this->_set_recurrence_description_setup_daily();
      break;
      case 'weekly':
        $this->_set_recurrence_description_setup_weekly();
      break;
      case 'monthly':
        $this->_set_recurrence_description_setup_monthly();
      break;
      case 'yearly':
        $this->_set_recurrence_description_setup_yearly();
      break;
    }
    $this->_set_recurrence_description_setup_range();
    $this->set_field('recur_description',$this->_recur_description,true,false);
  }


  protected function _set_recurrence_description_setup_daily(){
    switch($this->record['recur_daily_mode']){
      case 'day':
        $this->_recur_description.=
           'Repeat every '
          .($this->record['recur_daily_interval']==1 ? ' day' : '')
          .($this->record['recur_daily_interval']==2 ? ' two days' : '')
          .($this->record['recur_daily_interval']>2 ? $this->record['recur_daily_interval']. ' days' : '');
      break;
      case 'weekday':
        $this->_recur_description.= 'Repeat Weekdays (Mon-Fri)';
      break;
      case 'weekendday':
        $this->_recur_description.= 'Repeat Weekend Days (Sat-Sun)';
      break;
    }
    $this->_recur_description.= ' ';
  }

  protected function _set_recurrence_description_setup_weekly(){
    $this->_recur_description.=
       'Repeat every '
      .($this->record['recur_weekly_interval']==1 ? ' week' : '')
      .($this->record['recur_weekly_interval']>1 ? $this->record['recur_weekly_interval'].' weeks' : '')
      .' on '.$this->record['recur_weekly_days_csv']
      .' ';
  }

  protected function _set_recurrence_description_setup_monthly(){
    switch($this->record['recur_monthly_mode']){
      case 'day':
        $this->_recur_description.=
           'Repeat on the '
          .get_number_with_ordinal($this->record['recur_monthly_dd'])
          .' day of '
          .($this->record['recur_monthly_interval']==1 ? 'each month' : '')
          .($this->record['recur_monthly_interval']==2 ? 'every other month' : '')
          .($this->record['recur_monthly_interval']>2 ? 'every '.$this->record['recur_monthly_interval'].' months' : '')
          .', ';
      break;
      case 'the':
        $days_arr = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
        $nth_arr =  array('first','second','third','fourth','last');
        $this->_recur_description.=
           'Repeat on the '
          .$nth_arr[$this->record['recur_monthly_nth']-1]
          .' '
          .$days_arr[$this->record['recur_monthly_day']]
          .' of '
          .($this->record['recur_monthly_interval']==1 ? 'each month' : '')
          .($this->record['recur_monthly_interval']==2 ? 'every other month' : '')
          .($this->record['recur_monthly_interval']>2 ? 'every '.$this->record['recur_monthly_interval'].' months' : '')
          .', ';
      break;
    }
  }

  protected function _set_recurrence_description_setup_yearly(){
    switch($this->record['recur_yearly_mode']){
      case 'on':
        $months_arr = array('January','February','March','April','May','June','July','August','September','October','November','December');
        $this->_recur_description.=
           'Repeat on '
          .$months_arr[$this->record['recur_yearly_mm']]
          .' '
          .get_number_with_ordinal($this->record['recur_yearly_dd']-1)
          .' '
          .($this->record['recur_yearly_interval']==1 ? 'each year' : '')
          .($this->record['recur_yearly_interval']==2 ? 'every other year' : '')
          .($this->record['recur_yearly_interval']>2 ? 'every '.$this->record['recur_yearly_interval'].' years' : '')
          .', ';
      break;
      case 'the':
        $months_arr = array('January','February','March','April','May','June','July','August','September','October','November','December');
        $days_arr = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
        $nth_arr =  array('first','second','third','fourth','last');
        $this->_recur_description.=
           'Repeat on the '
          .$nth_arr[$this->record['recur_yearly_nth']-1]
          .' '
          .$days_arr[$this->record['recur_yearly_day']]
          .' in '
          .$months_arr[$this->record['recur_yearly_mm']]
          .' '
          .($this->record['recur_yearly_interval']==1 ? 'each year' : '')
          .($this->record['recur_yearly_interval']==2 ? 'every other year' : '')
          .($this->record['recur_monthly_interval']>2 ? 'every '.$this->record['recur_yearly_interval'].' years' : '')
          .', ';
      break;
    }
  }

  protected function _set_recurrence_description_setup_range(){
    switch ($this->record['recur_range_mode']){
      case 'endafter':
        $this->_recur_description.=
           ($this->record['recur_range_count']==1 ? ' once' : '')
          .($this->record['recur_range_count']==2 ? ' twice' : '')
          .($this->record['recur_range_count']>2 ? ' '.$this->record['recur_range_count'].' times' : '')
          .'.';
      break;
      case 'endby':
        $this->_recur_description.=         ' until '.$this->record['recur_range_end_by'];
      break;
    }
  }


  public function set_recurrence_settings(){
    $this->_set_recurrence_settings_initialise();
    switch($this->_recur_mode){
      case '':
        // do nothing
      break;
      case 'daily':
        $this->_set_recurrence_settings_setup_daily();
      break;
      case 'weekly':
        $this->_set_recurrence_settings_setup_weekly();
      break;
      case 'monthly':
        $this->_set_recurrence_settings_setup_monthly();
      break;
      case 'yearly':
        $this->_set_recurrence_settings_setup_yearly();
      break;
    }
    $this->_set_recurrence_settings_setup_range();
    $this->update($this->_data,true,false);
  }

  protected function _set_recurrence_settings_initialise(){
    $this->_recur_mode = sanitize('enum',get_var('recur_mode'),array('','daily','weekly','monthly','yearly'));
    $this->_data = array(
      'recur_mode' =>               $this->_recur_mode,
      'recur_daily_mode' =>         '',
      'recur_daily_interval' =>     '',
      'recur_weekly_interval' =>    '',
      'recur_weekly_days_csv' =>    '',
      'recur_monthly_mode' =>       '',
      'recur_monthly_dd' =>         '',
      'recur_monthly_interval' =>   '',
      'recur_monthly_nth' =>        '',
      'recur_monthly_day' =>        '',
      'recur_yearly_interval' =>    '',
      'recur_yearly_mode' =>        '',
      'recur_yearly_dd' =>          '',
      'recur_yearly_mm' =>          '',
      'recur_yearly_nth' =>         '',
      'recur_yearly_day' =>         '',
      'recur_range_mode' =>         '',
      'recur_range_count' =>        '',
      'recur_range_end_by' =>       ''
    );
  }

  protected function _set_recurrence_settings_setup_daily(){
    $recur_daily_mode =                         sanitize('enum',get_var('recur_daily_mode'),array('day','weekday','weekendday'));
    $this->_data['recur_daily_mode'] =          $recur_daily_mode;
    if($recur_daily_mode=='day'){
      $this->_data['recur_daily_interval'] =    sanitize('range',get_var('recur_daily_interval'),1,30,1);
    }
  }

  protected function _set_recurrence_settings_setup_weekly(){
    $this->_data['recur_weekly_interval'] = sanitize('range',get_var('recur_weekly_interval'),1,30,1);
    $all_days_arr =  array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
    $days_csv = sanitize('enum_csv',get_var('recur_weekly_days_csv'),$all_days_arr);
    $days_arr = explode(',',str_replace(' ','',$days_csv));
    $new_arr =  array();
    foreach ($all_days_arr as $day){
      foreach($days_arr as $d){
        if ($day==$d){
          $new_arr[] = $d;
        }
      }
    }
    $this->_data['recur_weekly_days_csv'] = implode(', ',$new_arr);
    if ($this->_data['recur_weekly_days_csv']==''){
      sscanf($this->get_field('effective_date_start'),"%4s-%2s-%2s",$YYYY, $MM, $DD);
      $day =                                    date('w',mktime(0, 0, 0, $MM, $DD, $YYYY));
      $days_arr =   array('Sun','Mon','Tue','Wed','Thu','Fri','Sat');
      $this->_data['recur_weekly_days_csv'] =   $days_arr[$day];
    }
  }

  protected function _set_recurrence_settings_setup_monthly(){
    $recur_monthly_mode =                       sanitize('enum',get_var('recur_monthly_mode'),array('day','the'));
    $this->_data['recur_monthly_mode'] =        $recur_monthly_mode;
    $this->_data['recur_monthly_dd'] =          sanitize('range',get_var('recur_monthly_dd'),1,31,1);
    $this->_data['recur_monthly_nth'] =         sanitize('range',get_var('recur_monthly_nth'),1,5,1);
    $this->_data['recur_monthly_day'] =         sanitize('range',get_var('recur_monthly_day'),0,6,0);
    if($recur_monthly_mode=='day'){
      $this->_data['recur_monthly_interval'] =  sanitize('range',get_var('recur_monthly_interval'),1,30,1);
    }
    if($recur_monthly_mode=='the'){
      $this->_data['recur_monthly_interval'] =  sanitize('range',get_var('recur_monthly_interval2'),1,30,1);
    }
  }

  protected function _set_recurrence_settings_setup_yearly(){
    $recur_yearly_mode =                        sanitize('enum',get_var('recur_yearly_mode'),array('on','the'));
    $this->_data['recur_yearly_dd'] =           sanitize('range',get_var('recur_yearly_dd'),1,31,1);
    $this->_data['recur_yearly_mode'] =         $recur_yearly_mode;
    $this->_data['recur_yearly_nth'] =          sanitize('range',get_var('recur_yearly_nth'),1,5,1);
    $this->_data['recur_yearly_day'] =          sanitize('range',get_var('recur_yearly_day'),0,6,0);
    if($recur_yearly_mode=='on'){
      $this->_data['recur_yearly_mm'] =         sanitize('range',get_var('recur_yearly_mm'),1,12,1);
      $this->_data['recur_yearly_interval'] =   sanitize('range',get_var('recur_yearly_interval'),1,30,1);
    }
    if($recur_yearly_mode=='the'){
      $this->_data['recur_yearly_mm'] =         sanitize('range',get_var('recur_yearly_mm2'),1,12,1);
      $this->_data['recur_yearly_interval'] =   sanitize('range',get_var('recur_yearly_interval2'),1,30,1);
    }
  }

  protected function _set_recurrence_settings_setup_range(){
    $recur_range_mode =                           sanitize('enum',get_var('recur_range_mode'),array('endafter','endby'));
    $this->_data['recur_range_mode'] =            $recur_range_mode;
    if ($recur_range_mode=='endafter'){
      $this->_data['recur_range_count'] =         sanitize('range',get_var('recur_range_count'),1,30,1);
    }
    if ($recur_range_mode=='endby'){
      $this->_data['recur_range_end_by'] =        sanitize('date-stamp',get_var('recur_range_end_by'));
    }
  }

  public function get_version(){
    return VERSION_EVENT_RECURRENCE;
  }
}

?>