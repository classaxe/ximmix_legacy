<?php
define('VERSION_NAVBUTTON','1.0.16');
/*
Version History:
  1.0.16 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.navbutton.txt)
*/

class Navbutton extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, group_assign_csv, icon_over_h_align, icon_over_image, icon_under_h_align, icon_under_image, img_checksum, img_height, img_width, permPUBLIC, permSYSLOGON, permSYSMEMBER, popup, position, sitemap_frequency, sitemap_priority, suiteID, text1, text2, URL, width, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  protected $_file_prefix =      "btn_";

  public function __construct($ID=""){
    parent::__construct("navbuttons",$ID);
    $this->_set_assign_type('navbuttons');
    $this->_set_has_groups(true);
    $this->_set_object_name('Button');
  }

  public function clear_cache(){
    $ID_arr = explode(",",$this->_get_ID());
    foreach ($ID_arr as $ID) {
      $filename = SYS_BUTTONS.$this->_file_prefix.$ID.".png";
      if (file_exists($filename)) {
        unlink($filename);
      }
    }
  }

  public function count_sibblings($suiteID){
    if (!$suiteID){
      return 0;
    }
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."WHERE\n"
      ."  `suiteID` = ".$suiteID;
    return $this->get_field_for_sql($sql);
  }

  public function delete(){
    $this->group_unassign();
    $this->clear_cache();
    return parent::delete();
  }

  public function delete_and_cleanup(){
    $suiteID =      $this->get_field('suiteID');
    if (!$suiteID){
      return;
    }
    $sibblings =    $this->count_sibblings($suiteID);
    $this->group_unassign();
    $this->clear_cache();
    $result = parent::delete();
    $Obj_parent = new Navsuite($suiteID);
    if ($sibblings==1){
      $Obj_parent->delete();
    }
    else {
      $Obj_parent->clear_cache();   // If largest button now gone - resize others
    }
    return $result;
  }


  public function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  public function get_root_navsuiteID($ID,$depth=0){
    $max_depth = 3;
    $sql =
       "SELECT\n"
      ."  `navsuite`.`ID`,\n"
      ."  `navsuite`.`parentButtonID`\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."INNER JOIN `navsuite` ON\n"
      ."  `navbuttons`.`suiteID` = `navsuite`.`ID`\n"
      ."WHERE\n"
      ."  `navbuttons`.`ID` = \"".$ID."\"";
    $record = $this->get_record_for_sql($sql);
    if ($record['parentButtonID']==1){
      return $record['ID'];
    }
    if ($depth>=$max_depth){
      return 0;
    }
    return $this->get_root_navsuiteID($record['parentButtonID'],$depth++);
  }

  public function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }

  public function has_visible_children($ID) {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
    $isAdmin =	        ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER || $isSYSEDITOR);
    $sql =
       "SELECT\n"
      ."  `nb`.`ID`,\n"
      ."  `nb`.`text1`,\n"
      ."  `nb`.`text2`,\n"
      ."  `nb`.`group_assign_csv`,\n"
      ."  `nb`.`permPUBLIC`,\n"
      ."  `nb`.`permSYSLOGON`,\n"
      ."  `nb`.`permSYSMEMBER`\n"
      ."FROM\n"
      ."  `navbuttons` AS `nb`\n"
      ."INNER JOIN `navsuite` AS `ns` ON\n"
      ."  `ns`.`ID` = `nb`.`suiteID`\n"
      ."INNER JOIN `navbuttons` AS `np` ON\n"
      ."  `ns`.`parentButtonID` = `np`.`ID`\n"
      ."WHERE\n"
      ."  `np`.`ID` = ".$ID;
    $buttons = $this->get_records_for_sql($sql);
    if ($buttons==false || count($buttons)==0) {
      return false;
    }
    foreach ($buttons as $button){
      if ($this->is_visible($button) || $isAdmin){
        return true;
      }
    }
    return false;
  }

  public function image($no_show=0) {
    $filename = SYS_BUTTONS.$this->_file_prefix.$this->_get_ID().".png";
    if (file_exists($filename)) {
      if ($no_show==0) {
        set_cache(3600*24*7); // expire in one week
        readfile($filename);
        die;
      }
      else {
        return;
      }
    }
    $record =	$this->info();
    if (!$record) {
      if ($no_show==0) {
        readfile(SYS_IMAGES.'invalid_image.gif');
        return;
      }
    }
    $Obj_Navbutton_Image = new Navbutton_Image($this->_get_ID());
    return $Obj_Navbutton_Image->draw($record,$filename,$no_show);
  }

  public function info() {
    $sql =
       "SELECT\n"
  	  ."  (SELECT MIN(`position`) FROM `navbuttons` AS `nb` WHERE `nb`.`suiteID` = `navsuite`.`ID`) AS `min_seq`,\n"
      ."  (SELECT MAX(`position`) FROM `navbuttons` AS `nb` WHERE `nb`.`suiteID` = `navsuite`.`ID`) AS `max_seq`,\n"
      ."  `navstyle`.`dropdownArrow`,\n"
  	  ."  `navstyle`.`orientation`,\n"
      ."  `navstyle`.`overlay_ba_img`,\n"
      ."  `navstyle`.`overlay_ba_img_align`,\n"
      ."  `navstyle`.`overlay_bm_img`,\n"
      ."  `navstyle`.`overlay_bm_img_align`,\n"
      ."  `navstyle`.`overlay_bz_img`,\n"
      ."  `navstyle`.`overlay_bz_img_align`,\n"
  	  ."  `navstyle`.`templateFile`,\n"
  	  ."  `navstyle`.`text1_effect_color_active`,\n"
  	  ."  `navstyle`.`text1_effect_color_down`,\n"
  	  ."  `navstyle`.`text1_effect_color_normal`,\n"
  	  ."  `navstyle`.`text1_effect_color_over`,\n"
  	  ."  `navstyle`.`text1_effect_level_active`,\n"
  	  ."  `navstyle`.`text1_effect_level_down`,\n"
  	  ."  `navstyle`.`text1_effect_level_normal`,\n"
  	  ."  `navstyle`.`text1_effect_level_over`,\n"
  	  ."  `navstyle`.`text1_effect_type_active`,\n"
  	  ."  `navstyle`.`text1_effect_type_down`,\n"
  	  ."  `navstyle`.`text1_effect_type_normal`,\n"
  	  ."  `navstyle`.`text1_effect_type_over`,\n"
  	  ."  `navstyle`.`text1_font_color_active`,\n"
  	  ."  `navstyle`.`text1_font_color_down`,\n"
  	  ."  `navstyle`.`text1_font_color_normal`,\n"
  	  ."  `navstyle`.`text1_font_color_over`,\n"
  	  ."  `navstyle`.`text1_font_face`,\n"
  	  ."  `navstyle`.`text1_font_size`,\n"
  	  ."  `navstyle`.`text1_h_align`,\n"
  	  ."  `navstyle`.`text1_h_offset`,\n"
  	  ."  `navstyle`.`text1_v_offset`,\n"
  	  ."  `navstyle`.`text1_uppercase`,\n"
  	  ."  `navstyle`.`text2_effect_color_active`,\n"
  	  ."  `navstyle`.`text2_effect_color_down`,\n"
  	  ."  `navstyle`.`text2_effect_color_normal`,\n"
  	  ."  `navstyle`.`text2_effect_color_over`,\n"
  	  ."  `navstyle`.`text2_effect_level_active`,\n"
  	  ."  `navstyle`.`text2_effect_level_down`,\n"
  	  ."  `navstyle`.`text2_effect_level_normal`,\n"
  	  ."  `navstyle`.`text2_effect_level_over`,\n"
  	  ."  `navstyle`.`text2_effect_type_active`,\n"
  	  ."  `navstyle`.`text2_effect_type_down`,\n"
  	  ."  `navstyle`.`text2_effect_type_normal`,\n"
  	  ."  `navstyle`.`text2_effect_type_over`,\n"
  	  ."  `navstyle`.`text2_font_color_active`,\n"
  	  ."  `navstyle`.`text2_font_color_down`,\n"
  	  ."  `navstyle`.`text2_font_color_normal`,\n"
  	  ."  `navstyle`.`text2_font_color_over`,\n"
  	  ."  `navstyle`.`text2_font_face`,\n"
  	  ."  `navstyle`.`text2_font_size`,\n"
  	  ."  `navstyle`.`text2_h_align`,\n"
  	  ."  `navstyle`.`text2_h_offset`,\n"
  	  ."  `navstyle`.`text2_v_offset`,\n"
  	  ."  `navstyle`.`text2_uppercase`,\n"
  	  ."  `navsuite`.`childID_csv`,\n"
      ."  `navsuite`.`width` AS `navsuite_width`,\n"
  	  ."  `navbuttons`.`ID`,\n"
  	  ."  `navbuttons`.`systemID`,\n"
  	  ."  `navbuttons`.`icon_over_image`,\n"
  	  ."  `navbuttons`.`icon_over_h_align`,\n"
  	  ."  `navbuttons`.`icon_under_image`,\n"
  	  ."  `navbuttons`.`icon_under_h_align`,\n"
  	  ."  `navbuttons`.`position`,\n"
  	  ."  `navbuttons`.`text1`,\n"
  	  ."  `navbuttons`.`text2`,\n"
  	  ."  `navbuttons`.`width`,\n"
  	  ."  `navbuttons`.`img_checksum`,\n"
  	  ."  `navbuttons`.`img_width`,\n"
  	  ."  `navbuttons`.`img_height`\n"
  	  ."FROM\n"
  	  ."  `navsuite`\n"
      ."INNER JOIN `navbuttons` ON\n"
  	  ."  `navsuite`.`ID` = `navbuttons`.`suiteID`\n"
      ."INNER JOIN `navstyle` ON\n"
  	  ."  `navsuite`.`buttonStyleID` = `navstyle`.`ID`\n"
  	  ."WHERE\n"
  	  ."  `navsuite`.`systemID` IN(1,".SYS_ID.") AND\n"
  	  ."  `navbuttons`.`ID` =  ".$this->_get_ID();
//    z($sql);die;
    return $this->get_record_for_sql($sql);
  }

  public function is_active($url,$site_URL) {
    global $page;
    $url = trim($url,"/");
    $request = Portal::get_request_path();
    return
      ($url=='.' && $page=='home') ||
      (substr($url,0,8)=="./?page=" && $request==substr($url,8)) ||
      $url == $request ||
      $url == "./".$request ||
      (substr($url,0,strlen($site_URL))==$site_URL)
      ;
  }

  public function make_image() {
    $record = $this->info();
//    y($record);die;
    $filename = SYS_BUTTONS.$this->_file_prefix.$this->_get_ID().".png";
    $Obj_Navbutton_Image = new Navbutton_Image($this->_get_ID());
    $Obj_Navbutton_Image->draw($record,$filename,1);
  }

  public function on_delete() {
    global $action_parameters;
    $ID_arr = explode(",",$action_parameters['triggerID']);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $this->clear_cache();
    }
  }

  public function on_update() {
    global $action_parameters;
    $ID_csv =   $action_parameters['triggerID'];
    $sql =
       "SELECT\n"
      ."  `navbuttons`.`ID`,\n"
      ."  `navbuttons`.`suiteID`,\n"
      ."  `navsuite`.`width`,\n"
      ."  `navstyle`.`orientation`,\n"
      ."  IF (CONCAT(`overlay_ba_img`,`overlay_bm_img`,`overlay_bz_img`)='','0','1') `rules`\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."INNER JOIN `navsuite` ON\n"
      ."  `navsuite`.`ID` = `navbuttons`.`suiteID`\n"
      ."INNER JOIN `navstyle` ON\n"
      ."  `navstyle`.`ID` = `navsuite`.`buttonStyleID`\n"
      ."WHERE\n"
      ."  `navbuttons`.`ID` IN(".$ID_csv.")\n"
      ."GROUP BY\n"
      ."  `navbuttons`.`suiteID`";
    $records = $this->get_records_for_sql($sql);
    foreach ($records as $record) {
      $only_me = false;
      if ($record['rules']==0 && $record['orientation']=='---'){
        $only_me = true;
      }
      if ($record['rules']==0 && $record['width']>0){
        $only_me = true;
      }
      if ($only_me){
        $this->_set_ID($record['ID']);
        $this->clear_cache();
        $this->make_image();
      }
      else {
        $Obj_Navsuite = new Navsuite($record['suiteID']);
        $Obj_Navsuite->clear_cache();
      }
    }
  }

  public function parent() {
    $sql =
       "SELECT\n"
      ."  `parentButtonID`\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."INNER JOIN `navsuite` ON\n"
      ."  `navbuttons`.`suiteID` = `navsuite`.`ID`\n"
      ."WHERE\n"
      ."  `navbuttons`.`ID` = \"".$this->_get_ID()."\"";
    return $this->get_field_for_sql($sql);
  }

  public function save($destination) {
    $button = $this->_file_prefix.$this->_get_ID().".png";
    if (!file_exists($destination.$button)) {
      $this->image(true);
      if (!copy(SYS_BUTTONS.$button, $destination.$button)) {
        return "<b>Problem:</b> Unable to store Button ".$button;
      }
    }
    return "";
  }

  public function seq($offset) {
    $record =   $this->get_record();
    $suiteID =  $record['suiteID'];
    $position = $record['position'];
    $sql =
       "UPDATE\n"
      ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
      ."SET\n"
      ."  `history_modified_by` = ".get_userID().",\n"
      ."  `history_modified_date` = \"".get_timestamp()."\",\n"
      ."  `position` = `position` -(".$offset.")\n"
      ."WHERE\n"
      ."  `position` = ".$position."+(".$offset.") AND\n"
      ."  `suiteID` = ".$suiteID;
    $this->do_sql_query($sql);
    $this->set_field('position',$position+$offset,false);
    $sql =
       "SELECT\n"
      ."  IF (CONCAT(`overlay_ba_img`,`overlay_bm_img`,`overlay_bz_img`)='','0','1') `rules`\n"
      ."FROM\n"
      ."  `navsuite`\n"
      ."INNER JOIN `navstyle` ON\n"
      ."  `navstyle`.`ID` = `navsuite`.`buttonStyleID`\n"
      ."WHERE\n"
      ."  `navsuite`.`ID` = ".$suiteID;
    $rules = $this->get_field_for_sql($sql);
    if ($rules){
      $Obj = new Navsuite($suiteID);
      $Obj->clear_cache();
    }
  }

  public function subnav_add(){
    if ($this->_subnav_add_check_for_existing()){
      return;
    }
    $sql =
       "SELECT\n"
      ."  CONCAT(`navsuite`.`name`,' &gt; ',`navbuttons`.`text1`) AS `name`,\n"
      ."  `navstyle`.`orientation`,\n"
      ."  `navstyle`.`subnavStyleID`\n"
      ."FROM\n"
      ."  `navbuttons`\n"
      ."INNER JOIN `navsuite` ON\n"
      ."  `navbuttons`.`suiteID` = `navsuite`.`ID`\n"
      ."INNER JOIN `navstyle` ON\n"
      ."  `navsuite`.`buttonStyleID` = `navstyle`.`ID`\n"
      ."WHERE\n"
      ."  `navbuttons`.`ID` = ".$this->_get_ID();
    $record =               $this->get_record_for_sql($sql);
    $name =
      str_replace(
        array("\n"),
        array(" "),
        $record['name']
      );
    $buttonStyleID =        $record['subnavStyleID'];
    $orientation =          $record['orientation'];
    $ObjNS =                new Navsuite;
    switch ($orientation) {
      case "---":
        $data =
          array(
            'systemID' =>               SYS_ID,
            'buttonStyleID' =>          $buttonStyleID,
            'name' =>                   $name,
            'parentButtonID' =>         $this->_get_ID()
          );
      break;
      default:
        $data =
          array(
            'systemID' =>               SYS_ID,
            'buttonStyleID' =>          $buttonStyleID,
            'name' =>                   $name,
            'parentButtonID' =>         $this->_get_ID()
          );
      break;
    }
    $suiteID = $ObjNS->insert($data);
    $ObjNB = new NavButton;
    $data =
      array(
        'systemID' =>   SYS_ID,
        'position' =>   1,
        'suiteID' =>    $suiteID,
        'text1' =>       '[ New Button ]',
        'URL' =>        './'
      );
    if (System::has_feature('Button-default-all-user-perms')){
      $data['permPUBLIC'] = 1;
      $data['permSYSLOGON'] = 1;
      $data['permSYSMEMBER'] = 1;
    }
    $ObjNB->_set_ID($ObjNB->insert($data));
    $ObjNB->make_image();
  }

  protected function _subnav_add_check_for_existing(){
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `navsuite`\n"
      ."WHERE\n"
      ."  `parentButtonID`=".$this->_get_ID();
    if ($this->get_field_for_sql($sql)>0){
      return true;
    }
    return false;
  }

  public function get_version(){
    return VERSION_NAVBUTTON;
  }

}
?>