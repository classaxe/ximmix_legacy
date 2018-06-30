<?php
define('VERSION_SYSTEM_COPY','1.0.7');

/*
Version History:
  1.0.7 (2012-08-22)
    1) Now remaps posting parents to their new copied parents
    2) Now remaps page parents to their new copied parents
  1.0.6 (2012-07-11)
    1) Removed reference to 'treenodes' in non-cloned tables comment

  (Older version history in class.system_copy.txt)

*/

class System_Copy extends System {
  private $_map = array();
  private $_Old_System_Record;
  private $_Old_SystemID;
  private $_Obj_New_System;
  private $_New_SystemID;

  private function initialise(){
    $this->_map['Category_Assign'] =    array();
    $this->_map['Component'] =          array();
    $this->_map['Group'] =              array();
    $this->_map['Group_Assign'] =       array();
    $this->_map['Layout'] =             array();
    $this->_map['Listdata'] =           array();
    $this->_map['Listtype'] =           array();
    $this->_map['Mail_Identity'] =      array();
    $this->_map['Mail_Template'] =      array();
    $this->_map['Mail_Queue'] =         array();
    $this->_map['Navbutton'] =          array();
    $this->_map['Navbutton_Style'] =    array();
    $this->_map['Navsuite'] =           array();
    $this->_map['Page'] =               array();
    $this->_map['Person'] =             array();
    $this->_map['Posting'] =            array();
    $this->_map['Product'] =            array();
    $this->_map['Report'] =             array();
    $this->_map['Report_Column'] =      array();
    $this->_map['Theme'] =              array();
  }

  function copy($new_name=false) {
    /*
    This does NOT copy any data from the following tables:
      action, activity, case_tasks, cases, colour_scheme,
      comment, community, community_member, community_membership,
      custom_form, gateway_settings, gateway_type, keyword_assign, keywords,
      membership_rule, order_items, orders, poll, poll_choice
      product, product_categories, register_events, report, report_columns,
      report_defaults, report_filter, report_filter_criteria, report_settings,
      scheduled_task, tax_regime, tax_rule, tax_zone, widget
   */
    $this->initialise();
    $this->_copy_system($new_name);
    $this->_copy_persons();
    $this->_copy_groups();
    $this->_copy_group_assign();
    $this->_copy_group_members();
    $this->_copy_block_layouts();
    $this->_copy_components();
    $this->_copy_content_blocks();
    $this->_copy_ecl_tags();
    $this->_copy_field_templates();
    $this->_copy_payment_methods();
    $this->_copy_listtypes();
    $this->_copy_listdata();
    $this->_copy_category_assign();
    $this->_copy_mail();
    $this->_copy_navbutton_styles();
    $this->_copy_navsuites();
    $this->_copy_navbuttons();
    $this->_copy_layouts();
    $this->_copy_themes();
    $this->_copy_pages();
    $this->_copy_postings();
    $this->_remap_category_assigns();
    $this->_remap_group_assigns();
    $this->_remap_page_parents();
//    $this->copy_reports();
    return $this->_New_SystemID;
  }

  private function _copied_item_remap_categoryIDs($Obj, $record){
    $oldVal =                 $record['categoryID'];
    if (isset($this->_map['Listdata'][$oldVal]['newID'])) {
      $newVal =   $this->_map['Listdata'][$oldVal]['newID'];
      $Obj->set_field('categoryID',$newVal,false);
    }
  }

  private function _copied_item_remap_category_assign_assignID($Obj, $record){
    $oldVal =                 $record['assignID'];
    if ($oldVal){
      switch($record['assign_type']){
        case 'navbuttons':
          $type = 'Navbutton';
        break;
        case 'pages':
          $type = 'Page';
        break;
        case 'product':
          $type = 'Product';
        break;
        case 'Report Column':
          $type = 'Report_Column';
        break;
        default:
          $type = 'Posting';
        break;
      }
      if (isset( $this->_map[$type][$oldVal]['newID'])){
        $newVal = $this->_map[$type][$oldVal]['newID'];
        $Obj->set_field('assignID',$newVal,false);
      }
    }
  }

  private function _copied_item_remap_components($Obj, $record){
    $oldVal =                 $record['componentID_pre'];
    if (isset($this->_map['Component'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Component'][$oldVal]['newID'];
      $Obj->set_field('componentID_pre',$newVal,false);
    }
    $oldVal =                 $record['componentID_post'];
    if (isset($this->_map['Component'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Component'][$oldVal]['newID'];
      $Obj->set_field('componentID_post',$newVal,false);
    }
  }

  private function _copied_item_remap_groupIDs($Obj, $record){
    $oldVal =                 $record['groupID'];
    if (isset($this->_map['Group'][$oldVal]['newID'])) {
      $newVal =   $this->_map['Group'][$oldVal]['newID'];
      $Obj->set_field('groupID',$newVal,false);
    }
  }

  private function _copied_item_remap_group_assign_assignID($Obj, $record){
    $oldVal =                 $record['assignID'];
    if ($oldVal){
      switch($record['assign_type']){
        case 'navbuttons':
          $type = 'Navbutton';
        break;
        case 'pages':
          $type = 'Page';
        break;
        case 'product':
          $type = 'Product';
        break;
        case 'Report Column':
          $type = 'Report_Column';
        break;
        default:
          $type = 'Posting';
        break;
      }
      if (isset( $this->_map[$type][$oldVal]['newID'])){
        $newVal = $this->_map[$type][$oldVal]['newID'];
        $Obj->set_field('assignID',$newVal,false);
      }
    }
  }

  private function _copied_item_remap_group_assign_csv($Obj, $record){
    $oldVal =                 $record['group_assign_csv'];
    if ($oldVal){
      $old_val_arr = explode(',',$oldVal);
      $new_val_arr = array();
      foreach ($old_val_arr as $old){
        if (isset( $this->_map['Group'][$old]['newID'])){
          $new_val_arr[] = $this->_map['Group'][$old]['newID'];
        }
      }
      $newVal = implode(',',$new_val_arr);
      $Obj->set_field('group_assign_csv',$newVal,false);
    }
  }

  private function _copied_item_remap_layouts($Obj, $record){
    $oldVal =                 $record['layoutID'];
    if (isset($this->_map['Layout'][$oldVal]['newID'])) {
      $newVal =   $this->_map['Layout'][$oldVal]['newID'];
      $Obj->set_field('layoutID',$newVal,false);
    }
  }

  private function _copied_item_remap_listtypes($Obj, $record){
    $oldVal =                 $record['listTypeID'];
    if (isset($this->_map['Listtype'][$oldVal]['newID'])) {
      $newVal =   $this->_map['Listtype'][$oldVal]['newID'];
      $Obj->set_field('listTypeID',$newVal,false);
    }
  }

  private function _copied_item_remap_mail_identities($Obj, $record){
    $oldVal =                 $record['mailidentityID'];
    if (isset($this->_map['Mail_Identity'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Mail_Identity'][$oldVal]['newID'];
      $Obj->set_field('mailidentityID',$newVal,false);
    }
  }

  private function _copied_item_remap_mail_queues($Obj, $record){
    $oldVal =                 $record['mailqueueID'];
    if (isset($this->_map['Mail_Queue'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Mail_Queue'][$oldVal]['newID'];
      $Obj->set_field('mailqueueID',$newVal,false);
    }
  }

  private function _copied_item_remap_mail_templates($Obj, $record){
    $oldVal =                 $record['mailtemplateID'];
    if (isset($this->_map['Mail_Template'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Mail_Template'][$oldVal]['newID'];
      $Obj->set_field('mailtemplateID',$newVal,false);
    }
  }

  private function _copied_item_remap_navstyles($Obj, $record){
    $oldVal =                 $record['buttonStyleID'];
    if (isset($this->_map['Navbutton_Style'][$oldVal]['newID'])) {
      $newVal =   $this->_map['Navbutton_Style'][$oldVal]['newID'];
      $Obj->set_field('buttonStyleID',$newVal,false);
    }
  }

  private function _copied_item_remap_navsuites($Obj, $record){
    for ($i=1; $i<=3; $i++) {
      $oldVal =                 $record['navsuite'.$i.'ID'];
      if (isset($this->_map['Navsuite'][$oldVal]['newID'])) {
        $newVal =  $this->_map['Navsuite'][$oldVal]['newID'];
        $Obj->set_field('navsuite'.$i.'ID',$newVal,false);
      }
    }
  }

  private function _copied_item_remap_navsuite_childID_csv($Obj, $record){
    $oldVal =                 $record['childID_csv'];
    if ($oldVal){
      $old_val_arr = explode(',',$oldVal);
      $new_val_arr = array();
      foreach ($old_val_arr as $old){
        if (isset( $this->_map['Navbutton'][$old]['newID'])){
          $new_val_arr[] = $this->_map['Navbutton'][$old]['newID'];
        }
      }
      $newVal = implode(',',$new_val_arr);
      $Obj->set_field('childID_csv',$newVal,false);
    }
  }

  private function _copied_item_remap_navsuite_parentButtonID($Obj, $record){
    $oldVal =                 $record['parentButtonID'];
    if (isset($this->_map['Navbutton'][$oldVal]['newID'])) {
      $newVal = $this->_map['Navbutton'][$oldVal]['newID'];
      $Obj->set_field('parentButtonID',$newVal,false);
    }
  }

  private function _copied_item_remap_page_parent($Obj, $record){
    $oldVal =                 $record['parentID'];
    if (isset($this->_map['Page'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Page'][$oldVal]['newID'];
      $Obj->set_field('parentID',$newVal,false);
    }
  }

  private function _copied_item_remap_personIDs($Obj, $record){
    $oldVal =                 $record['personID'];
    if (isset($this->_map['Person'][$oldVal]['newID'])) {
      $newVal =   $this->_map['Person'][$oldVal]['newID'];
      $Obj->set_field('personID',$newVal,false);
    }
  }

  private function _copied_item_remap_posting_parent($Obj, $record){
    $oldVal =                 $record['parentID'];
    if (isset($this->_map['Posting'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Posting'][$oldVal]['newID'];
      $Obj->set_field('parentID',$newVal,false);
    }
  }

  private function _copied_item_remap_themes($Obj, $record){
    $oldVal =                 $record['themeID'];
    if (isset($this->_map['Theme'][$oldVal]['newID'])) {
      $newVal =    $this->_map['Theme'][$oldVal]['newID'];
      $Obj->set_field('themeID',$newVal,false);
    }
  }

  private function _copy_block_layouts(){
    $Obj =         new Record('block_layout');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
    }
  }

  private function _copy_category_assign(){
    $Obj =         new Record('category_assign');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Category_Assign'][$ID] =  array('newID'=>$newID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_categoryIDs($Obj,$record);
    }
  }

  private function _copy_components(){
    $Obj =         new Record('component');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Component'][$ID] =  array('newID'=>$newID);
    }
  }

  private function _copy_content_blocks(){
    $Obj =      new Record('content_block');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
    }
  }

  private function _copy_ecl_tags(){
    $Obj =         new Record('ecl_tags');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
    }
  }

  private function _copy_field_templates(){
    $Obj =         new Record('field_templates');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
    }
  }

  private function _copy_groups(){
    $Obj =         new Record('groups');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Group'][$ID] =  array('newID'=>$newID);
    }
  }

  private function _copy_group_assign(){
    $Obj =         new Record('group_assign');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Group_Assign'][$ID] =  array('newID'=>$newID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_groupIDs($Obj,$record);
    }
  }

  private function _copy_group_members(){
    $Obj =         new Record('group_members');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_personIDs($Obj,$record);
      $this->_copied_item_remap_groupIDs($Obj,$record);
    }
  }

  private function _copy_layouts(){
    $Obj =            new Record('layout');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =  $Obj->copy("",$this->_New_SystemID);
      $this->_map['Layout'][$ID] =  array('newID'=>$newID);
      // Assign default layout when found
      if ($this->_Old_System_Record['defaultLayoutID']==$ID) {
        $this->_Obj_New_System->set_field('defaultLayoutID',$this->_map['Layout'][$ID]['newID'],false);
      }
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_navsuites($Obj,$record);
    }
  }

  private function _copy_listdata(){
    $Obj =         new Record('listdata');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Listdata'][$ID] =  array('newID'=>$newID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_listtypes($Obj,$record);
    }
  }

  private function _copy_listtypes(){
    $Obj =         new Record('listtype');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Listtype'][$ID] =  array('newID'=>$newID);
    }
  }

  private function _copy_mail(){
    $this->_copy_mail_identities();
    $this->_copy_mail_templates();
    $this->_copy_mail_queues();
    $this->_copy_mail_queue_items();
  }

  private function _copy_mail_identities(){
    $Obj =         new Record('mailidentity');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Mail_Identity'][$ID] =  array('newID'=>$newID);
    }
  }

  private function _copy_mail_queues(){
    $Obj =         new Record('mailqueue');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Mail_Queue'][$ID] =  array('newID'=>$newID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_mail_identities($Obj,$record);
      $this->_copied_item_remap_mail_templates($Obj,$record);
    }
  }

  private function _copy_mail_queue_items(){
    $Obj =         new Record('mailqueue_item');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_mail_queues($Obj,$record);
    }
  }

  private function _copy_mail_templates(){
    $Obj =         new Record('mailtemplate');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Mail_Template'][$ID] =  array('newID'=>$newID);
    }
  }

  private function _copy_navbuttons(){
    $Obj =             new Record('navbuttons');
    $Items =                    $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID = $Obj->copy("",$this->_New_SystemID);
      $this->_map['Navbutton'][$ID] =  array('newID'=>$newID);
      // Get old suiteIDs from copied records
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $oldVal =                 $record['suiteID'];
      if (isset($this->_map['Navsuite'][$oldVal])) {
        $Obj->set_field('suiteID',$this->_map['Navsuite'][$oldVal]['newID'],false);
      }
      $this->_copied_item_remap_group_assign_csv($Obj,$record);
    }
  }

  private function _copy_navbutton_styles(){
    $Obj =       new Record('navstyle');
    $Items =                    $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =                  $Obj->copy("",$this->_New_SystemID);
      $this->_map['Navbutton_Style'][$ID] =  array('newID'=>$newID);
    }
    $Items =                    $Obj->get_IDs_by_system($this->_New_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $oldVal = $Obj->get_field('subnavStyleID');
      if ($oldVal!='1') {
        $Obj->set_field('subnavStyleID',$this->_map['Navbutton_Style'][$oldVal]['newID'],false);
      }
    }
  }

  private function _copy_navsuites(){
    $Obj =              new Record('navsuite');
    $Items =                    $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =                  $Obj->copy("",$this->_New_SystemID);
      $this->_map['Navsuite'][$ID] =   array('newID'=>$newID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_navstyles($Obj,$record);
    }
    $this->_remap_navsuites();
  }

  private function _copy_pages(){
    $Obj = new Record('pages');
    $Items =    $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =  $Obj->copy("",$this->_New_SystemID);
      $this->_map['Page'][$ID] =  array('newID'=>$newID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_components($Obj,$record);
      $this->_copied_item_remap_themes($Obj,$record);
      $this->_copied_item_remap_layouts($Obj,$record);
      $this->_copied_item_remap_navsuites($Obj,$record);
      $this->_copied_item_remap_group_assign_csv($Obj,$record);
    }
    $this->_remap_page_parents();
  }

  private function _copy_postings(){
    $Obj = new Record('postings');
    $Items =    $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =  $Obj->copy("",$this->_New_SystemID);
      $this->_map['Posting'][$ID] =  array('newID'=>$newID);
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_themes($Obj,$record);
      $this->_copied_item_remap_layouts($Obj,$record);
      $this->_copied_item_remap_group_assign_csv($Obj,$record);
    }
    $this->_remap_posting_parents();
  }

  private function _copy_Payment_Methods(){
    $Obj =         new Record('payment_method');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $Obj_PM = new Payment_Method($newID);
      $record = $Obj_PM->load();
      $this->_copied_item_remap_group_assign_csv($Obj,$record);
    }
  }

  private function _copy_persons(){
    $Obj =         new Record('person');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Person'][$ID] =  array('newID'=>$newID);
    }
  }

  private function _copy_reports(){
    $Obj_Report =         new Record('report');
    $Items =                $Obj_Report->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj_Report->_set_ID($ID);
      $newID =              $Obj_Report->copy("",$this->_New_SystemID);
      $this->_map['Report'][$ID] =  array('newID'=>$newID);
    }
  }

  private function _copy_system($new_name){
    $this->_Old_SystemID =      $this->_get_ID();
    $this->_Old_System_Record = $this->get_record();
    $Obj_Record =               new Record($this->_get_table_name(), $this->_Old_SystemID);
    $Obj_Record->_set_name_field($this->_get_name_field());
    $this->_New_SystemID =      $Obj_Record->copy($new_name);
    $this->_Obj_New_System =    new System($this->_New_SystemID);
    $data = array(
      'adminEmail' =>                   '',
      'bugs_password' =>                '',
      'bugs_username' =>                '',
      'cron_job_heartbeat_last_run' =>  '0000-00-00 00:00:00',
      'custom_1' =>                     '',
      'custom_2' =>                     '',
      'google_analytics_key' =>         '',
      'last_user_access' =>             '',
      'notes' =>                        '',
      'notify_email' =>                 '',
      'notify_triggers' =>              '',
      'piwik_id' =>                     '',
      'piwik_token' =>                  '',
      'piwik_user' =>                   '',
      'qbwc_AssetAccountRef' =>         '',
      'qbwc_COGSAccountRef' =>          '',
      'qbwc_IncomeAccountRef' =>        '',
      'smtp_password' =>                '',
      'smtp_username' =>                '',
      'URL' =>                          ''
    );
    $this->_Obj_New_System->update($data,true,false);
  }

  private function _copy_themes(){
    $Obj =             new Record('theme');
    $Items =                $Obj->get_IDs_by_system($this->_Old_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $newID =              $Obj->copy("",$this->_New_SystemID);
      $this->_map['Theme'][$ID] =  array('newID'=>$newID);
      // Assign default theme when found
      if ($this->_Old_System_Record['defaultThemeID']==$ID) {
        $this->_Obj_New_System->set_field('defaultThemeID',$this->_map['Theme'][$ID]['newID'],false);
      }
      $Obj->_set_ID($newID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_layouts($Obj,$record);
      $this->_copied_item_remap_navsuites($Obj,$record);
    }
  }

  public function _remap_category_assigns(){
    $Obj =              new Record('category_assign');
    $Items =            $Obj->get_IDs_by_system($this->_New_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_category_assign_assignID($Obj,$record);
    }
  }

  public function _remap_group_assigns(){
    $Obj =              new Record('group_assign');
    $Items =            $Obj->get_IDs_by_system($this->_New_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_group_assign_assignID($Obj,$record);
    }
  }

  public function _remap_navsuites(){
    $Obj =              new Record('navsuite');
    $Items =            $Obj->get_IDs_by_system($this->_New_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_navsuite_parentButtonID($Obj,$record);
      $this->_copied_item_remap_navsuite_childID_csv($Obj,$record);
    }
  }

  public function _remap_page_parents(){
    $Obj =              new Record('pages');
    $Items =            $Obj->get_IDs_by_system($this->_New_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_page_parent($Obj,$record);
    }
  }

  public function _remap_posting_parents(){
    $Obj =              new Record('postings');
    $Items =            $Obj->get_IDs_by_system($this->_New_SystemID);
    foreach ($Items as $ID) {
      $Obj->_set_ID($ID);
      $record = $Obj->get_record();
      $this->_copied_item_remap_posting_parent($Obj,$record);
    }
  }

  public function get_version(){
    return VERSION_SYSTEM_COPY;
  }
}
?>