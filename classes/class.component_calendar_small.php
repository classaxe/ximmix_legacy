<?php
define ("VERSION_COMPONENT_CALENDAR_SMALL","1.0.3");
/*
Version History:
  1.0.3 (2014-01-28)
    1) Extra newline after JS code in Component_Calendar_Small::draw()

  (Older version history in class.component_calendar_small.txt)
*/
class Component_Calendar_Small extends Component_Base {
  function draw($args=array(),$disable_params=false){
    global $YYYY,$MM,$DD,$system_vars,$page_vars;
    $instance =         '';
    $ident =            "calendar";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'filter_category_list' =>     array('match' => '',						'default' =>'*',          'hint'=>'*|CSV value list'),
      'filter_memberID' =>          array('match' => 'range|0,n',	  		    'default' =>'',           'hint'=>'ID of Community Member to restrict by that criteria - or zero to exclude all member content'),
      'link_enlarge' =>             array('match' => '',						'default'=>'',            'hint'=>'URL for enlarged view'),
      'link_enlarge_popup' =>       array('match' => 'enum|0,1',  				'default'=>'1',           'hint'=>'0|1'),
      'link_help' =>                array('match' => 'enum|0,1',  				'default'=>'1',           'hint'=>'0|1'),
      'shadow' =>                   array('match' => 'enum|0,1',  				'default'=>'0',           'hint'=>'0|1'),
      'show' =>                     array('match' => 'enum|days,events,sample',	'default'=>'events',      'hint'=>'days|events|sample'),
      'weeks' =>                    array('match' => 'enum|0,1',  				'default'=>'0',           'hint'=>'0|1'),
      'width' =>                    array('match' => '',						'default'=>'0',           'hint'=>'0..x'),
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $isMASTERADMIN =    get_person_permission("MASTERADMIN");
    $isSYSADMIN =	    get_person_permission("SYSADMIN");
    $isSYSEDITOR =      get_person_permission("SYSEDITOR");
    $canPublish =       ($isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR);
    $_DD = $DD;
    $sd_arr = array();
    if ($cp['show']=='events') {
      $Obj_Event = new Event;
      $arr_cal = $Obj_Event->get_calendar_dates($MM,$YYYY,$cp['filter_memberID'],$cp['filter_category_list']);
      $special_days = array();
      foreach ($arr_cal as $item) {
        if ($item['evt']) {
          $date = $item['YYYY']."-".$item['MM']."-".$item['DD'];
          $special_days[$date] = array();
          foreach ($item['evt'] as $event){
            $special_days[$date][] = ($event['systemID']==SYS_ID ? "  " : "* ").$event['title'];
          }
        }
      }
//      y($special_days);die;
      foreach ($special_days as $key=>$array){
        $sd_arr[] = "'".$key."' : [\"".implode("\",\"",$array)."\"]";
      }
    }
//    y($sd_arr);die;
    if ($cp['show']=='sample'){
      $sd_arr[] = "'".$YYYY."-".$MM."-".($_DD=="01" ? "03" : "01")."' : ['   Test event','* Shared event']";
      $sd_arr[] = "'".$YYYY."-".$MM."-".($_DD=="15" ? "16" : "15")."' : ['   Test event']";
      $_DD = ($_DD=="01" ? "02" : "01");
    }
    $js =
       "var calendar_special_days = {\n"
      ."  ".implode(",\n  ",$sd_arr)."\n"
      ."};\n";
    Page::push_content('javascript',$js);
    $flatCallback = false;
    switch($cp['show']){
      case "days":
      case "events":
        $flatCallback = ($canPublish ? "calendar_changed_admin_fn" : "calendar_changed_fn");
      break;
    }
    $js_onload =
       "  Calendar.setup({\n"
      ."    date:               new Date(".(int)$YYYY.",".((int)$MM-1).",".(int)$_DD."),\n"
      ."    dateStatusFunc:     calendar_status_fn,\n"
      ."    dateToolTipFunc:    calendar_tooltip_fn,\n"
      ."    flat:               '".$safe_ID."',\n"
      .($flatCallback ? "    flatCallback:       ".$flatCallback.",\n" : "")
      ."    link_enlarge:       '".$cp['link_enlarge']."',\n"
      ."    link_enlarge_popup: '".$cp['link_enlarge_popup']."',\n"
      ."    link_help:          '".$cp['link_help']."',\n"
      ."    showOthers:         true,\n"
      ."    weekNumbers:        ".($cp['weeks'] ? 'true' : 'false')."\n"
      ."  });\n";
    Page::push_content('javascript_onload',$js_onload);
    return
       $out
      ."<div id=\"".$safe_ID."\""
      .($cp['shadow'] ? " class='shadow'" : "")
      ." style='".($cp['width'] ? "width:".$cp['width']."px" : "xheight:1%")."'></div>";
  }

  public function get_version(){
    return VERSION_COMPONENT_CALENDAR_SMALL;
  }
}
?>