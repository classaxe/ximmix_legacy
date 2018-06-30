<?php
define('VERSION_GROUP_WIZARD','1.0.13');
/*
Version History:
  1.0.13 (2013-10-27)
    1) Group_Wizard::_setup_get_targetIDs() now uses Record::set_group_concat_max_len()
       to change MYSQL session variable group_concat_max_len

  (Older version history in class.group_wizard.txt)
*/
class Group_Wizard extends Group{

  function draw() {
    try{
      $this->_setup();
    }
    catch(Exception $e){
      return $e->getMessage();
    }
    $isMASTERADMIN =	    get_person_permission("MASTERADMIN");
    switch ($this->_submode) {
      case "confirm":
        $this->_do_submode();
      break;
    }
    if ($this->_submode){
      $out =
         "<div class='txt_c'>"
        ."<input type='submit' value='Done' onclick=\"window.close();return false;\" />\n"
        ."</div>\n";
      return $this->_draw_frame($out);
    }
    Page::push_content(
      'javascript',
      "var global_groups_current_vals  = [];"
    );
    $sqlGroups =            $this->get_selector_groups_SQL(true,false);
    $out =
      HTML::draw_section_tabs(
        array(
          array('ID'=>'basic','label'=>'Basic Settings','width'=>160),
          array('ID'=>'advanced', 'label'=>'Advanced Settings','width'=>160)
        ),'group_add',$this->_selected_section
       )
      ."        <table width='100%' class='admin_containerpanel'>\n"
      ."          <tr>\n"
      ."            <td class='va_t' style='padding:2px;height:371px;'>"
      ."This screen allows you to add "
      .($this->_Obj_Report->record['primaryObject']=='Contact' ? 'Contacts' : 'People')
      ." to a group,<br />and specify their rights within it.<br /><br />\n"
      ."<b>1) "
      .($this->_targetID && $this->_total_count!=$this->_selected_count && $this->_selected_count>0?
          "Which persons do you wish to add?</b>"
         ."<div style='padding: 2px 2px 10px 10px;'>"
         ."  <div style='width:40%' class='fl'>"
         ."    <input type='radio' name='source' id='source_selected' value='selected' checked='checked' /> "
         ."    <label for='source_selected'>Just the ".$this->_selected_count." selected"
         ."</label></div>\n"
         ."  <div class='fl' style='width:60%'>\n"
         ."    <input type='radio' name='source' id='source_all' value='all'/> "
         ."    <label for='source_all'>All ".$this->_total_count." matching the report criteria.</label>\n"
         ."  </div>\n"
         ."</div>"
        :
          "Preparing to add ".$this->_total_count." "
         .($this->_Obj_Report->record['primaryObject']=='Contact' ?
             'contact'.($this->_total_count==1 ? '' : 's')
          :
            ($this->_total_count==1 ? 'person' : 'people')
          )
         ." to a group.</b><br />"
       )
      ."<br />"
      ."<b>2) Which group do you wish to add members to?</b><br />\n"
      ."<div style='padding: 2px 2px 2px 10px;'>"
      .draw_form_field('groupID','','combo_selector',500,$sqlGroups,$this->_targetReportID,"onchange=\"group_add_people_group_select('groupID',500);return true;\" onfocus=\"group_add_people_group_select('groupID',500);return true;\" ")
      ."</div>"
      ."<div id='step_3' style='display:none;'>"
      ."<b>3) What permissions do you wish to assign for these members?</b><br />\n"
      .draw_section_tab_div('basic',$this->_selected_section)
      ."  <div style='height:185px;'>"
      ."    <div style='padding: 2px 2px 2px 10px;'>"
      ."      <div class='txt_c'>"
      .draw_form_field('basic_perms','','radio_listdata',500,'',$this->_targetReportID,"onclick='return group_selector_basic_click(global_groups_current_vals,spans_group_add)'",false,false,'','lst_group_basic_permission_options')
      ."      </div>\n"
      ."<b>Please note:</b><br />"
      ." &nbsp; Basic Settings only allows you to ADD a permission.<br />"
      ." &nbsp; To REMOVE a permission you should use the Advanced settings tab."
      ."    </div>\n"
      ."  </div>\n"
      ."</div>\n"
      .draw_section_tab_div('advanced',$this->_selected_section)
      ."  <div style='height:185px;padding:10px 10px;'>"
      ."    <table cellpadding='2' cellspacing='0' border='0' width='100%'>\n"
      ."      <tr>\n"
      ."        <th class='txt_l' colspan='2'>Email List Permissions:</th>\n"
      ."      </tr>\n"
      ."      <tr>\n"
      ."        <td>&nbsp; Email Recipient</td>\n"
      ."        <td style='padding:2px;'>".draw_form_field('permEMAILRECIPIENT','','radio_listdata',500,'',$this->_targetReportID,"onclick='return group_selector_advanced_click(global_groups_current_vals,spans_group_add)'",false,false,'','lst_group_permission_options')."</td>\n"
      ."      </tr>\n"
      ."      <tr>\n"
      ."        <th class='txt_l' colspan='2'>Site Access Permissions:</th>\n"
      ."      </tr>\n"
      ."      <tr>\n"
      ."        <td>&nbsp; Viewer</td>\n"
      ."        <td>".draw_form_field('permVIEWER','','radio_listdata',500,'',$this->_targetReportID,"onclick='return group_selector_advanced_click(global_groups_current_vals,spans_group_add)'",false,false,'','lst_group_permission_options')."</td>\n"
      ."      </tr>\n"
      ."      <tr>\n"
      ."        <td>&nbsp; Editor</td>\n"
      ."        <td>".draw_form_field('permEDITOR','','radio_listdata',500,'',$this->_targetReportID,"onclick='return group_selector_advanced_click(global_groups_current_vals,spans_group_add)'",false,false,'','lst_group_permission_options')."</td>\n"
      ."      </tr>\n"
      ."    </table>\n"
      ."    </div>"
      ."</div>"
      ."<div class='txt_c'>\n"
      ."  <input type='submit' value='Cancel' onclick=\"window.close();return false;\"/>\n"
      ."  <input type='submit' value='Save'"
      ." onclick=\"geid('targetID').value='".$this->_targetID."';"
      ."geid('targetReportID').value=".$this->_targetReportID.";"
      ."geid('submode').value='confirm';"
      ."this.disabled=true;this.value='Please Wait...';"
      ."geid('form').submit();\"/>\n"
      ."</div>"
      ."</div>"
      ."</td>\n"
      ."  </tr>\n"
      ."</table>";
      return $this->_draw_frame($out);
  }

  private function _do_submode(){
    @set_time_limit(600);	// Extend maximum execution time to 10 mins
    $this->_do_submode_get_permissions();
    $this->_do_submode_get_persons_csv();
    $this->_do_submode_process_group();
    $this->_do_submode_process_persons();
    $this->_do_submode_set_message();
  }

  private function _do_submode_get_permissions(){
    $this->_permissions_arr = array();
    if (get_var('permEMAILRECIPIENT')!==false){
      $this->_permissions_arr['permEMAILRECIPIENT'] =   (get_var('permEMAILRECIPIENT') ? 1 : 0);
    }
    if (get_var('permVIEWER')!==false){
      $this->_permissions_arr['permVIEWER'] =           (get_var('permVIEWER') ? 1 : 0);
    }
    if (get_var('permEDITOR')!==false){
      $this->_permissions_arr['permEDITOR'] =           (get_var('permEDITOR') ? 1 : 0);
    }
  }

  private function _do_submode_get_persons_csv(){
    if ($this->_targetID && $this->_source=='selected') {
      $this->_personID_csv = $this->_targetID;
    }
    else {
      $isMASTERADMIN =	    get_person_permission("MASTERADMIN");
      $sql =
         "SELECT\n"
        ."  `".($isMASTERADMIN ? "formSelectorSQLMaster" : "formSelectorSQLMember")."`\n"
        ."FROM\n"
        ."  `report_columns`\n"
        ."WHERE\n"
        ."  `reportID` = ".$this->_targetReportID." AND\n"
        ."  `fieldType` = 'selected_add_to_group'";
      $sql = $this->get_field_for_sql($sql);
      $sql =
         get_sql_constants($sql)
        .Report_Report::get_filter(
           $this->_filterField_sql,
           $this->_filterExact,
           $this->_filterValue
         );
      $this->_personID_csv = $this->get_csv_for_sql($sql);
    }
  }

  private function _do_submode_process_group(){
    $systemID =             SYS_ID;
    $this->_group_name =    $this->_groupID;
    if (is_numeric($this->_groupID)) {
      $this->_set_ID($this->_groupID);
      if ($this->exists()) {
        $this->touch(); // Show that the group has been modified
        $this->load();
        $this->_group_name =    $this->record['name'];
        $systemID =             $this->record['systemID'];
      }
      else {
        $data = array(
          'description' =>  'Group created '.get_timestamp(),
          'systemID' =>     SYS_ID,
          'name' =>         $this->_group_name
        );
        $this->_groupID =       $this->insert($data);
        $this->_group_created = true;
      }
    }
    else {
      if ($this->_groupID = $this->get_ID_by_name($this->_group_name,SYS_ID)) {
        $this->touch(); // Show that the group has been modified
        $this->load();
        $this->_group_name =    $this->record['name'];
        $systemID =             $this->record['systemID'];
      }
      else {
        $data = array(
          'description' =>  'Group created '.get_timestamp(),
          'systemID' =>     SYS_ID,
          'name' =>         $this->_group_name
        );
        $this->_groupID =       $this->insert($data);
        $this->_group_created = true;
      }
    }
    $Obj =  new System($systemID);
    $this->_group_site = $Obj->get_field('textEnglish');
    $this->_set_ID($this->_groupID);
  }

  private function _do_submode_process_persons(){
    $personID_arr = explode(",",$this->_personID_csv);
    $Obj_Person = new Person;
    foreach ($personID_arr as $personID) {
      if ($this->member_assign($personID,$this->_permissions_arr)) {
        $this->_updated_count++;
      }
      $Obj_Person->_set_ID($personID);
      $Obj_Person->set_groups_list_description();
    }
  }

  private function _do_submode_set_message(){
    $this->_msg =
       "<div style='background-color: #e8ffe8'><b>Success:</b><br />\n"
      ."The group <b>'".$this->_group_site.": ".$this->_group_name."'</b>"
      .($this->_group_created ?
         " was created and ".$this->_updated_count." members have been added to it."
       :
         ($this->_updated_count ?
           " has had ".$this->_updated_count." members either added to it, "
           ."or their permisions modified for it."
         :
           " was not affected by this particular operation."
         )
       )
       ."</div>";
  }

  private function _draw_frame($content){
    return
       draw_form_header("Add People to Group","_help_admin_groups",0)
      ."<table width='100%' border='0' class='minimal admin_containertable'>\n"
      ."  <tr>\n"
      ."    <td class='va_t'>\n"
      .($this->_msg!="" ? $this->_msg."<br />" : "")
      .$content
      ."</td>\n"
      ."  </tr>\n"
      ."</table>";
  }

  private function _setup(){
    try{
      $this->_setup_get_vars();
      $this->_setup_get_report();
      $this->_setup_filter_sql();
      $this->_setup_get_targetIDs();
      $this->_setup_get_total_count();
      $this->_setup_get_selected_count();
      $this->_msg =             '';
      $this->_group_created =   false;
      $this->_group_name =      '';
      $this->_group_site =      '';
      $this->_updated_count =   0;
    }
    catch(Exception $e){
      throw $e;
    }
  }

  private function _setup_get_report(){
    $this->_Obj_Report =    new Report($this->_targetReportID);
    if (!$this->_Obj_Report->load()){
      throw new Exception(
         __CLASS__."::".__FUNCTION__."()<br />\n"
        ."Error: No report with ID of ".$this->_targetReportID
      );
    }
  }

  private function _setup_get_total_count(){
    switch($this->_Obj_Report->record['primaryObject']){
      case "Person":
      case "Contact":
      case "User":
        $this->_total_count = $this->_Obj_Report->get_records_count(
          $this->_Obj_Report->record,
          $this->_filterField_sql,
          $this->_filterExact,
          $this->_filterValue
        );
        return;
      break;
    }
    // Otherwise take the longer route:
    $isMASTERADMIN =	    get_person_permission("MASTERADMIN");
    $sql =
       "SELECT\n"
      ."  `".($isMASTERADMIN ? "formSelectorSQLMaster" : "formSelectorSQLMember")."`\n"
      ."FROM\n"
      ."  `report_columns`\n"
      ."WHERE\n"
      ."  `reportID` = ".$this->_targetReportID." AND\n"
      ."  `fieldType` = 'selected_add_to_group'";
    $sql = $this->get_field_for_sql($sql);
    if (!$sql){
      throw new Exception(
         __CLASS__."::".__FUNCTION__."()<br />\n"
        ."Error: the related report deals with items of type "
        ."'".$this->_Obj_Report->record['primaryObject']."'"
        .", but there are no SQL queries for the add_to_group report column."
      );
    }
    $sql =
       get_sql_constants($sql)
      .Report_Report::get_filter(
         $this->_filterField_sql,
         $this->_filterExact,
         $this->_filterValue
      );
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `person`\n"
      ."WHERE\n"
      ."  `ID` IN(".$sql.")";
    $this->_total_count = $this->get_field_for_sql($sql);
  }

  private function _setup_get_selected_count(){
    $targetID_arr =             ($this->_targetID ? explode(',',$this->_targetID) : array());
    $this->_selected_count =     count($targetID_arr);
  }

  private function _setup_get_targetIDs(){
    switch($this->_Obj_Report->record['primaryObject']){
      case "Person":
      case "Contact":
      case "User":
        // We were counting people, so there CANNOT be multiples or gaps selected
        return;
      break;
    }
    // Otherwise take the longer route:
    if ($this->_submode=='confirm'){
      // IDs have already been converted once
      return;
    }
    $this->set_group_concat_max_len(1000000);
    $isMASTERADMIN =	    get_person_permission("MASTERADMIN");
    $sql =
       "SELECT\n"
      ."  `".($isMASTERADMIN ? "formSelectorSQLMaster" : "formSelectorSQLMember")."`\n"
      ."FROM\n"
      ."  `report_columns`\n"
      ."WHERE\n"
      ."  `reportID` = ".$this->_targetReportID." AND\n"
      ."  `fieldType` = 'selected_add_to_group'";
    $sql = $this->get_field_for_sql($sql);
    $sql =
       get_sql_constants($sql)
      .Report_Report::get_filter(
         $this->_filterField_sql,
         $this->_filterExact,
         $this->_filterValue
      );
    $Obj_primary = new $this->_Obj_Report->record['primaryObject'];
    $table_name = $Obj_primary->_get_table_name();
    $sql =
       "SELECT\n"
      ."  GROUP_CONCAT(`person`.`ID`)\n"
      ."FROM\n"
      ."  `person`\n"
      ."WHERE\n"
      ."  `person`.`ID` IN("
      .$sql." AND\n"
      .($this->_targetID ? "  `".$table_name."`.`ID` IN (".$this->_targetID.")\n" : "  1\n")
      .")";
//    z($sql);
    $this->_targetID = $this->get_field_for_sql($sql);
  }

  private function _setup_filter_sql(){
    $this->_filterField_sql = "";
    if ($this->_filterField!='') {
      $Obj =  new Report_Column;
      $Obj->_set_ID($this->_filterField);
      if (!$Obj->load()){
        throw new Exception(
           __CLASS__."::".__FUNCTION__."()<br />\n"
          ."Error: No report column with ID of ".$this->_filterField
        );
      }
      $filter_column_record = $Obj->get_record();
      if ($Obj->record['reportID'] == $this->_targetReportID){
        $this->_filterField_sql = $Obj->record['reportFilter'];
        Report::convert_xml_field_for_filter($this->_filterField_sql,$this->_Obj_Report->record['primaryTable']);
      }
    }
  }

  private function _setup_get_vars(){
    $this->_selected_section =  get_var('selected_section','basic');
    $this->_source =            get_var('source');
    $this->_submode =           get_var('submode');
    $this->_targetID =          get_var('targetID');
    $this->_groupID =           get_var('groupID');
    $this->_targetReportID =    get_var('targetReportID');
    $this->_filterField =       get_var('filterField');
    $this->_filterExact =       get_var('filterExact');
    $this->_filterValue =       get_var('filterValue')=='(Search for ...)' ? '' : get_var('filterValue');
  }

  public function get_version(){
    return VERSION_GROUP_WIZARD;
  }
}
?>