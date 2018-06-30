<?php
define('VERSION_REPORT_FILTER','1.0.16');
/*
Version History:
  1.0.16 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.report_filter.txt)
*/
class Report_Filter extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, destinationID, destinationType, label, reportID, seq, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  var $_html =      "";
  var $_selected =  array();
  var $_types =     array('global','system','person');
  var $_filters =   array();
  var $_filters_arr = array();

  public function __construct($ID="") {
    parent::__construct("report_filter",$ID);
    $this->_set_assign_type('Report Filter');
    $this->_set_object_name('Report Filter');
    $this->_set_name_field('label');
  }

  public function filter_add($reportID,$targetValue,$filterField,$filterExact,$filterValue){
    $personID =  get_userID();
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $data =
      array(
        'systemID' =>             ($isMASTERADMIN ? 1 : SYS_ID),
        'destinationType' =>      'person',
        'destinationID' =>        $personID,
        'label' =>                $targetValue,
        'reportID' =>             $reportID
      );
    $filterID = parent::insert($data);
    $Obj_Criteria = new Report_Filter_Criteria;
    $data =
      array(
        'systemID' =>             ($isMASTERADMIN ? 1 : SYS_ID),
        'filterID' =>             $filterID,
        'filter_criterion' =>     $filterField,
        'filter_matchmodeID' =>   $filterExact,
        'filter_value' =>         $filterValue,
        'filter_seq' =>           1
      );
    $Obj_Criteria->insert($data);
    $Obj_Report_Settings = new Report_Settings;
    $report_settings = $Obj_Report_Settings->get_settings($reportID, 'person', $personID);
    if ($report_settings){
      $report_settings_ID = $report_settings['ID'];
      $report_filters_csv = $report_settings['report_filters_csv'];
      $report_filters_arr = explode(',',$report_filters_csv);
      $report_filters_arr[] = $filterID;
      $report_filters_csv = implode(',',$report_filters_arr);
      $Obj_Report_Settings->_set_ID($report_settings_ID);
      $Obj_Report_Settings->set_field('report_filters_csv',$report_filters_csv);
    }
    else {
      $data = array(
        'systemID' =>           SYS_ID,
        'reportID' =>           $reportID,
        'destinationType' =>    'person',
        'destinationID' =>      $personID,
        'report_filters_csv' => $filterID
      );
      $Obj_Report_Settings->insert($data);
    }
  }

  public function assign($targetReportID,$destinationType,$_destinationID) {
//    print "$targetReportID,$destinationType,$destinationID";die;
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =	    get_person_permission("SYSADMIN");
    if (!($isMASTERADMIN || $isSYSADMIN)){
      return false;
    }
    switch ($isMASTERADMIN) {
      case true:
        switch ($destinationType) {
          case 'global':
            $destinationID =    0;
            $systemID =         1;
          break;
          case 'system':
            $destinationID =    SYS_ID;
            $systemID =         SYS_ID;
          break;
          default:
            // Make available for me on any system
            $destinationID =    get_userID();
            $systemID =         1;
          break;
        }
      break;
      default:
        switch ($destinationType) {
          case 'global':
            // Force assignment to this system
            $destinationID = 0;
            $systemID = 1;
          break;
          case 'system':
            // Force assignment to this system
            $destinationID = SYS_ID;
            $systemID = SYS_ID;
          break;
          default:
            // Force assignment to me on this system
            $destinationID = get_userID();
            $systemID = SYS_ID;
          break;
        }
      break;
    }
    // Set appropriate system for related criteria:
    $sql =
       "UPDATE\n"
      ."  `report_filter_criteria`\n"
      ."SET\n"
      ."  `systemID` = ".$systemID."\n"
      ."WHERE\n"
      ."  `filterID` IN(".$this->_get_ID().")";
    $this->do_sql_query($sql);
    $old_record =   $this->load();
    $Obj_Report_Settings = new Report_Settings;
    $Obj_Report_Settings->delete_settings_for_filter($old_record);
    $data =
      array(
        'systemID' =>             $systemID,
        'destinationType' =>      $destinationType,
        'destinationID' =>        $destinationID
      );
    parent::update($data);
    $new_record =   $this->load();
    $Obj_Report_Settings->assign_settings_for_filter($new_record);
  }

  public function delete(){
    $record = $this->load();
    $Obj_Report_Settings = new Report_Settings;
    $Obj_Report_Settings->delete_settings_for_filter($record);
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `report_filter_criteria`\n"
      ."WHERE\n"
      ."  `filterID` IN (".$this->_get_ID().")\n";
    $records = $this->get_records_for_sql($sql);
    $Obj_Report_Filter_Criteria =   new Report_Filter_Criteria;
    foreach ($records as $record) {
      $Obj_Report_Filter_Criteria->_set_ID($record['ID']);
      $Obj_Report_Filter_Criteria->delete();
    }
    parent::delete();
  }

  public function _draw_filters_get_filters() {
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `destinationID`,\n"
      ."  `destinationType`,\n"
      ."  `label`\n"
      ."FROM\n"
      ."  `report_filter`\n"
      ."WHERE\n"
      ."  `reportID` = ".$this->_reportID." AND\n"
      ."  `systemID` IN(1,".SYS_ID.") AND\n"
      ."  (\n"
      ."    (`destinationType`='global') OR\n"
      ."    (`destinationType`='system' AND `destinationID`=" .SYS_ID.") OR\n"
      ."    (`destinationType`='person' AND `destinationID`=".($this->_personID ? $this->_personID : 0).")\n"
      ."  )\n"
      ."ORDER BY\n"
      ."  `destinationType`='system' DESC,\n"
      ."  `destinationid`=1 DESC\n";
    $records = $this->get_records_for_sql($sql);
    if (!$records) {
      return false;
    }
    $filters = array();
    foreach ($records as &$record){
      $record['active'] = 0;
      $record['can_edit'] = (
        ($this->_isMASTERADMIN) ||
        ($this->_isSYSADMIN && $record['destinationType']=='system') ||
        ($record['destinationType']=='person') ? 1 : 0
      );
      $ID_arr[] = $record['ID'];
      $filters[$record['ID']] = $record;
    }
    $sql =
        "SELECT\n"
       ."  `ID`,\n"
       ."  `filterID`,\n"
       ."  `filter_criterion`,\n"
       ."  `filter_matchmodeID`,\n"
       ."  `filter_seq`,\n"
       ."  `filter_value`\n"
       ."FROM\n"
       ."  `report_filter_criteria`\n"
       ."WHERE\n"
       ."  `filterID` IN (".implode(',',$ID_arr).")\n"
       ."ORDER BY\n"
       ."  `filter_seq`";
//    z($sql);
    $criteria_arr = $this->get_records_for_sql($sql);
    foreach($filters as &$filter) {
      $filter['criteria'] = array();
      foreach ($criteria_arr as $criterion) {
        if ($criterion['filterID']==$filter['ID']) {
          $filter['criteria'][] = $criterion;
          if(
            $criterion['filter_criterion'] == $this->_filterField &&
            $criterion['filter_matchmodeID'] == $this->_filterExact &&
            $criterion['filter_value'] == $this->_filterValue){
            $filter['active'] = true;
          }
        }
      }
    }
    $this->_filters_arr = array(
      'global' => array(),
      'system' => array(),
      'person' => array()
    );
    $Obj_Report_Settings = new Report_Settings;
    $settings = $Obj_Report_Settings->get_settings_for_report($this->_reportID);
    foreach ($settings as $key=>$values){
      if (isset($values['report_filters'])){
        foreach ($values['report_filters'] as $ID){
          if (isset($filters[$ID])){
            $this->_filters_arr[$key][] = $filters[$ID];
          }
        }
      }
    }
  }

  private function _draw_filters_get_tooltip($label){
    $value =
      str_replace(
        array(
          'All',
          '+Today',
          '~Today',
          '+Yesterday',
          '~Yesterday',
          '+This ',
          '~This ',
          '+Last ',
          '~Last ',
          '!Y',
          '!N'
        ),
        array(
          'All Records',
          'Created Today',
          'Modified Today',
          'Created Yesterday',
          'Modified Yesterday',
          'Created This ',
          'Modified This ',
          'Created Last ',
          'Modified Last ',
          'High Importance',
          'Normal Importance'
        ),
        $label
      );
    if (substr($value,strlen($value)-2)==' N'){
      $value = "Without ".substr($value,0,strlen($value)-2);
    }
    if (substr($value,strlen($value)-2)==' Y'){
      $value = "With ".substr($value,0,strlen($value)-2);
    }
    if (substr($value,0,6)=='[ICON]'){
      $value_arr = preg_split("/\[ICON\]|\[\/ICON\]/",$value);
      $value_arr = explode(' ',$value_arr[1]);
      $value = implode(' ',array_splice($value_arr,3));
    }
    return $value;
  }

  private function _draw_filters_html(){
    $this->_html.=  "<div class=\"section_tabs\">\n";
    $this->_draw_filters_html_types();
    $this->_html.=  "</div>\n";
  }

  private function _draw_filters_html_types(){
    foreach($this->_types as $type){
      if(count($this->_filters_arr[$type])){
        $this->_html.="<div id=\"filters_for_report_".$this->_reportID."_".$type."\" class='".$type."'>\n";
        foreach($this->_filters_arr[$type] as $filter) {
          $this->_html.=
             "  <div id=\"filters_for_report_".$this->_reportID."_".$filter['ID']."\""
            ." class=\"tab"
            .($filter['active'] ? " active" : "")
            .(!$filter['criteria'] ? " error" : "")
            ."\">".$filter['label']."</div>\n";
        }
        $this->_html.= "</div>\n";
      }
    }

  }

  private function _draw_filters_js(){
    global $page_vars;
    static $js_started =    false;
    $resource_url =         BASE_PATH.trim($page_vars['path'],'/');
    $js = "";
    if (!$js_started){
      Page::push_content('javascript_onload',"  report_filter_setup();\n");
      $js.=
         "// ***********************\n"
        ."// * Report Filters data *\n"
        ."// ***********************\n"
        ."var report_filters = [];\n"
        ."var report_filters_sort = [];\n";
      $js_started=true;
    }
    $js.= "report_filters[".$this->_reportID."] = { g:[], s:[], p:[]};\n";
    foreach ($this->_types as $type){
      $i=0;
      foreach ($this->_filters_arr[$type] as $filter){
        $js.=
           "report_filters[".$this->_reportID."].".substr($type,0,1)."[".$i++."] = {\n"
          ."  ID: ".$filter['ID'].",\n"
          ."  can_edit: ".$filter['can_edit'].",\n"
          ."  report: \"".$this->_report_name."\",\n"
          ."  resource_url: \"".$resource_url."\",\n"
          ."  settings: {\n";
        foreach ($filter['criteria'] as $criterion) {
          $filterID = "filters_for_report_".$this->_reportID."_".$filter['ID'];
          $js.=
             "    ID: \"".$criterion['ID']."\",\n"
            ."    criterion: \"".$criterion['filter_criterion']."\",\n"
            ."    matchmode: \"".$criterion['filter_matchmodeID']."\",\n"
            ."    value: \"".$criterion['filter_value']."\"\n"
            ;
        }
        $js.=
           "  },\n"
          ."  systemID: \"".$filter['destinationID']."\",\n"
          ."  title: \"".$this->_draw_filters_get_tooltip($filter['label'])."\",\n"
          ."  type: \"".$filter['destinationType']."\"\n"
          ."};\n";
      }
    }
    $js.=
       "report_filters_sort[".$this->_reportID."] = { "
      ."g:".($this->_isMASTERADMIN && count($this->_filters_arr['global'])>1 ? 1: 0).", "
      ."s:".($this->_isSYSADMIN    && count($this->_filters_arr['system'])>1 ? 1: 0).", "
      ."p:".(                         count($this->_filters_arr['person'])>1 ? 1: 0)
      ."}\n";
    Page::push_content('javascript',$js);
  }

  private function _draw_filters_setup($reportID,$report_name){
    $this->_reportID =      $reportID;
    $this->_report_name =   $report_name;
    $this->_filterField =   get_var('filterField');
    $this->_filterExact =   get_var('filterExact');
    $this->_filterValue =   get_var('filterValue');
    $this->_isMASTERADMIN = get_person_permission("MASTERADMIN");
    $this->_isSYSADMIN =    get_person_permission("SYSADMIN");
    $this->_personID =      get_userID();

    $this->_draw_filters_get_filters();
  }

  public function get_filter_buttons_for_report($reportID,$report_name=false) {
    $this->_draw_filters_setup($reportID,$report_name);
    if (!$this->_filters_arr){
      return "";
    }
    $this->_draw_filters_js();
    $this->_draw_filters_html();
    return $this->_html;
  }

  public function get_next_seq($reportID,$destinationType,$destinationID){
    $sql =
       "SELECT\n"
      ."  MAX(`seq`)\n"
      ."FROM\n"
      ."  `report_filter`\n"
      ."WHERE\n"
      ."  `systemID` IN(1,".SYS_ID.") AND\n"
      ."  `reportID` = ".$reportID." AND\n"
      ."  `destinationType`='".$destinationType."' AND\n"
      ."  `destinationID`=".$destinationID;
    $result = $this->get_field_for_sql($sql);
    if ($result==""){
      $result=0;
    }
    return 1+$result;
  }

  public function ajax_set_seq(){
    $reportID =             get_var('targetReportID');
    $destinationType =      get_var('mode');
    $report_filters_csv =   get_var('targetValue');
    switch ($destinationType){
      case 'global':
        $destinationID = 0;
      break;
      case 'system':
        $destinationID = SYS_ID;
      break;
      case 'person':
        $destinationID = get_userID();
      break;
    }
    $Obj_Report_Settings = new Report_Settings;
    $report_settings = $Obj_Report_Settings->get_settings($reportID,$destinationType,$destinationID);
    $Obj_Report_Settings->_set_ID($report_settings['ID']);
    $Obj_Report_Settings->set_field('report_filters_csv',$report_filters_csv);
    print 'Updated report filters for '.$reportID.' to '.$report_filters_csv;
    die;
  }

  public function sql_export($targetID,$show_fields,$header="",$orderBy="",$extra_delete="",$extra_select="") {
    $Obj = new Backup;
    $extra_delete.=
       "DELETE FROM `report_filter_criteria` WHERE `filterID` IN (".$targetID.");\n";
    $extra_select.=
       $Obj->db_export_sql_query("`report_filter_criteria`","SELECT * FROM `report_filter_criteria` WHERE `filterID` IN (".$targetID.") ORDER BY `filterID`,`filter_seq`",$show_fields);
    return parent::sql_export($targetID,$show_fields,$header,$orderBy,$extra_delete,$extra_select);
  }

  public function get_version(){
    return VERSION_REPORT_FILTER;
  }
}
?>