<?php
  define ("VERSION_REPORT_FORM_FIELD_LOOKUP","1.0.2");
/*
Version History:
  1.0.2 (2014-03-17)
    1) Now if multiple options are provided as XML for report_field, mode can also
       be specified for each option
       Report_Form_Field_Lookup::_setup_get_filter_criteria() now looks for mode
       items as well as field

  (Older version history in class.report_form_field_lookup.txt)
*/
class Report_Form_Field_Lookup extends Report_Column {
  protected $_field;
  protected $_filter_criteria;
  protected $_filter_mode;
  protected $_value;
  protected $_html;
  protected $_isMASTERADMIN;

  public function draw(){
    $this->_setup();
    $this->_draw_js();
    $this->_draw_search_field();
    $this->_draw_query_selector();
    $this->_draw_results_container_open();
    $this->_draw_wait();
    $this->_draw_result_controls();
    $this->_draw_status();
    $this->_draw_result();
    $this->_draw_results_container_close();
    return $this->_html;
  }

  protected function _draw_js(){
    $this->_html.=
       Ajax::get_report(
         // Either pushes JS, or returns an error
         $this->_args['field'],
         $this->_args['control_num'],
         $this->_args['report_name'],
         $this->_args['report_field'],
         $this->_args['report_matchmode'],
         $this->_args['linked_field'],
         $this->_args['displayed_field'],
         $this->_args['autocomplete'],
         $this->_args['row_js'],
         $this->_args['onematch_js'],
         $this->_args['nomatch_js']
       );
    if ($this->_value!=''){
      Page::push_content(
        'javascript_onload',
         "  sajax.request_".$this->_args['control_num']."();\n"
      );
    }
  }

  protected function _draw_query_selector(){
    if (!$this->_filter_criteria){
      return;
    }
    $options = array();
    for($i=0; $i<count($this->_filter_criteria); $i++){
      $options[] = $i.'|'.$this->_filter_criteria[$i].'|'.$this->_filter_mode[$i];
    }
    $this->_html.=
       "<div class='fl'>"
      .draw_form_field('q_'.$this->_args['field'],0,'selector_csvlist','140','',0," onchange='sajax.request_".$this->_args['control_num']."()'",0,0,'',implode(',',$options))."\n"
      ."</div>\n";
//$field,$value,$type,$width="",$selectorSQL="",$reportID=0,$jsCode="",$readOnly=0,$bulk_update=0,$label="",$formFieldSpecial='',$height='') {

  }

  protected function _draw_result(){
    $this->_html.=
       "<div style='overflow:auto;max-height:".$this->_args['results_height']."px;'>\n"
      ."  <div id=\"ajax_result_".$this->_args['control_num']."\" style='display:block;'>\n"
      .($this->_args['lookup_result_initial'] ? "      ".$this->_args['lookup_result_initial']."\n" : "")
      ."  </div>\n"
      ."</div>\n";
  }

  protected function _draw_results_container_close(){
    $this->_html.= "</div>\n";
  }

  protected function _draw_results_container_open(){
    $this->_html.= "<div onclick=\"sajax.lookup_toggle(".$this->_args['control_num'].");\">\n";
  }

  protected function _draw_result_controls(){
    $this->_html.=
       "<div class='fl' style='display:none;' id=\"ajax_result_show_".$this->_args['control_num']."\">\n"
      ."  <img class='expand_contract' src='".BASE_PATH."img/sysimg/icon_show.gif' alt='Show results'/>\n"
      ."</div>\n"
      ."<div class='fl' style='display:none;' id=\"ajax_result_hide_".$this->_args['control_num']."\">\n"
      ."  <img class='expand_contract' src='".BASE_PATH."img/sysimg/icon_hide.gif' alt='Hide results' />\n"
      ."</div>\n";
  }

  protected function _draw_search_field(){
    $this->_html.=
       "<input type='text' id=\"".$this->_field."\" name=\"".$this->_field."\" class='formField fl'"
      ." style='height:16px; width:".(((int)$this->_args['width'] - ($this->_filter_criteria ? 124 : 0)))."px;'"
      ." onfocus=\"this.setAttribute('autocomplete','off');\""
      ." onkeyup=\"if (ajax_keytest(event)){ sajax.request_".$this->_args['control_num']."();}\""
      ." value=\"".$this->_value."\"/>\n"
      .($this->_args['displayed_field']!='' ?
         draw_form_field($this->_args['field'],$this->_args['value'],'hidden')."\n"
       :
         ""
       );
  }

  protected function _draw_status(){
    $this->_html.=
       "<span class='ajax_info fl' id=\"ajax_extra_".$this->_args['control_num']."\">\n"
      .($this->_args['lookup_info_initial'] ? "    ".$this->_args['lookup_info_initial']."\n" : "")
      ."</span><br class='clear' />\n";
  }

  protected function _draw_wait(){
    $this->_html.=
       "<div class='fl'>\n"
      ."  <img id='ajax_wait_show_".$this->_args['control_num']."' class='ajax_wait va_t' style='display:none' src='".BASE_PATH."img/sysimg/icon_ajax_wait.gif' alt='Please wait...' />\n"
      ."  <img id='ajax_wait_hide_".$this->_args['control_num']."' class='ajax_wait va_t' style='display:none' src='".BASE_PATH."img/spacer' alt='' />\n"
      ."</div>\n";
  }

  public function init(){
    $args = func_get_args();
//    $args[0]['value']='1982664045';
//    $args[0]['displayed_field'] = '';
    $vars = array(
      'field' =>                    '',
      'value' =>                    '',
      'control_num' =>              '',
      'report_name' =>              '',
      'report_field' =>             '',
      'report_matchmode' =>         '',
      'linked_field' =>             '',
      'displayed_field' =>          '',
      'autocomplete' =>             '',
      'row_js' =>                   '',
      'onematch_js' =>              '',
      'nomatch_js' =>               '',
      'lookup_info_initial' =>      '',
      'lookup_result_initial' =>    '',
      'results_height' =>           100,
      'width' =>                    240
    );
    if (!$this->_get_args($args,$vars,true)){
      die(__CLASS__.'::'.__FUNCTION__.'() - Error - no parameters passed - '.x());
    }
    $_args =
      array(
        'field' =>                  $vars['field'],
        'value' =>                  $vars['value'],
        'control_num' =>            $vars['control_num'],
        'report_name' =>            $vars['report_name'],
        'report_field' =>           $vars['report_field'],
        'report_matchmode' =>       $vars['report_matchmode'],
        'linked_field' =>           $vars['linked_field'],
        'displayed_field' =>        $vars['displayed_field'],
        'autocomplete' =>           $vars['autocomplete'],
        'row_js' =>                 $vars['row_js'],
        'onematch_js' =>            $vars['onematch_js'],
        'nomatch_js' =>             $vars['nomatch_js'],
        'lookup_info_initial' =>    $vars['lookup_info_initial'],
        'lookup_result_initial' =>  $vars['lookup_result_initial'],
        'results_height' =>         $vars['results_height'],
        'width' =>                  $vars['width']
      );
    $this->_args =  $_args;
//    y($this->_args);die;
  }

  protected function _setup(){
    $this->_isMASTERADMIN = get_person_permission("MASTERADMIN");
    $this->_setup_get_display_field();
    $this->_setup_get_initial_displayed_value();
    $this->_setup_get_filter_criteria();
  }

  protected function _setup_get_filter_criteria(){
    $options = array();
    if (trim(substr($this->_args['report_field'],0,9)!='<options>')){
      return;
    }
    $doc = simplexml_load_string($this->_args['report_field']);
    foreach($doc->option as $o){
      $this->_filter_criteria[] = (string)$o->label;
    }
  }

  protected function _setup_get_display_field(){
    if ($this->_args['displayed_field']=='') {
      $this->_field = $this->_args['field'];
      return;
    }
    $this->_field = "x_".$this->_args['field'];
  }

  protected function _setup_get_initial_displayed_value(){
    if ($this->_args['displayed_field']=='' ) {
      $this->_value = $this->_args['value'];
      return;
    }
    $Obj_R = new Report;
    $Obj_R->_set_ID($Obj_R->get_ID_by_name($this->_args['report_name']));
    $Obj_R->load();
    $sql =
      ($this->_isMASTERADMIN ?
         $Obj_R->record['reportSQL_MASTERADMIN']
       :
         $Obj_R->record['reportSQL_SYSADMIN']
       )
      .Report_Report::get_filter($this->_args['linked_field'],1,$this->_args['value'])
      .($Obj_R->record['reportGroupBy']!="" ?
         "\nGROUP BY\n  ".$Obj_R->record['reportGroupBy']
       : "");
    $sql = get_sql_constants($sql);
//    z($sql);
    $record = $this->get_record_for_sql($sql);
    if ($record!==false) {
      $this->_value = $record[$this->_args['displayed_field']];
    }
  }

  public function get_version(){
    return VERSION_REPORT_FORM_FIELD_LOOKUP;
  }
}
?>