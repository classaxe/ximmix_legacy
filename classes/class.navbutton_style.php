<?php
define('VERSION_NAVBUTTON_STYLE','1.0.8');
/*
Version History:
  1.0.8 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.navbutton_style.txt)
*/
class Navbutton_Style extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, button_spacing, dropdownArrow, img_checksum, img_height, img_width, name, orientation, overlay_ba_img, overlay_ba_img_align, overlay_bm_img, overlay_bm_img_align, overlay_bz_img, overlay_bz_img_align, subnavOffsetX, subnavOffsetY, subnavStyleID, templateFile, text1_effect_color_active, text1_effect_color_down, text1_effect_color_normal, text1_effect_color_over, text1_effect_level_active, text1_effect_level_down, text1_effect_level_normal, text1_effect_level_over, text1_effect_type_active, text1_effect_type_down, text1_effect_type_normal, text1_effect_type_over, text1_font_color_active, text1_font_color_down, text1_font_color_normal, text1_font_color_over, text1_font_face, text1_font_size, text1_h_align, text1_h_offset, text1_uppercase, text1_v_offset, text2_effect_color_active, text2_effect_color_down, text2_effect_color_normal, text2_effect_color_over, text2_effect_level_active, text2_effect_level_down, text2_effect_level_normal, text2_effect_level_over, text2_effect_type_active, text2_effect_type_down, text2_effect_type_normal, text2_effect_type_over, text2_font_color_active, text2_font_color_down, text2_font_color_normal, text2_font_color_over, text2_font_face, text2_font_size, text2_h_align, text2_h_offset, text2_uppercase, text2_v_offset, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  public $file_prefix = "btn_style_";

  public function __construct($ID="") {
    parent::__construct('navstyle',$ID);
    $this->_set_object_name('Navbutton Style');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  function clear_cache() {
    $navstyle_name = $this->get_field('name');
    $single_buttons = FileSystem::dir_wildcard_search(SYS_BUTTONS,'custom_button_*'.$navstyle_name.'.png');
    foreach ($single_buttons as $single_button) {
      unlink($single_button);
    }
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `navsuite`\n"
      ."WHERE\n"
      ."  `buttonStyleID` IN(".$this->_get_ID().")";
    $records = $this->get_records_for_sql($sql);
    if ($records) {
      foreach ($records as $record) {
        $Obj = new Navsuite($record['ID']);
        $Obj->clear_cache();
      }
    }
  }

  function export_sql($targetID,$show_fields){
    return $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function make_images() {
    $width=100;
    $this->sample($width,true);
  }

  function on_delete() {
    global $action_parameters;
    $ID_arr = explode(",",$action_parameters['triggerID']);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $this->clear_cache();
    }
  }

  function on_update() {
    global $action_parameters;
    $ID_arr = explode(",",$action_parameters['triggerID']);
    foreach ($ID_arr as $ID) {
      $this->_set_ID($ID);
      $this->clear_cache();
      $this->make_images();
    }
  }

  function sample($width=100, $no_show=false, $text1='auto', $filename='', $text2='') {
    if (!$this->_get_ID()) {
      return false;
    }
    $ID =                       $this->_get_ID();
    $data =                     $this->get_record();
    $data['navsuite_width'] =   0;
    $data['width'] =            $width;
    $data['text1'] =            $text1;
    $data['text2'] =            $text2;
    $data['childID_csv'] =      "";
    $Obj_Navbutton_Image =      new Navbutton_Image;
    $navstyleID =               ($filename ? false : $ID);
    $filename =                 ($filename ? $filename : SYS_BUTTONS.$this->file_prefix.$ID.".png");
    return $Obj_Navbutton_Image->draw($data,$filename,$no_show,$navstyleID);
  }

  public function get_version(){
    return VERSION_NAVBUTTON_STYLE;
  }
}
?>