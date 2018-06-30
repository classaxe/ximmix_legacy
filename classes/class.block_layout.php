<?php
define('VERSION_BLOCK_LAYOUT','1.0.61');
/*
Version History:
  1.0.61 (2014-11-27)
    1) BL tag for BL_link() where the links_switch_video cp feature is enabled now sets
       the rel=0 flag to prevent related content from showing.

  (Older version history in class.block_layout.txt)
*/
class Block_Layout extends Record{
  function __construct($table='block_layout', $ID='', $systemID=SYS_ID) {
    parent::__construct($table, $ID);
    $this->_set_systemID($systemID);
    $this->_set_object_name('Block Layout');
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  protected function BL_author(){
    if (!isset($this->_cp['author_show']) || $this->_cp['author_show']!='1'){
      return;
    }
    return $this->record['author'];
  }

  protected function BL_category(){
    if (!isset($this->_cp['category_show']) || $this->_cp['category_show']!='1'){
      return;
    }
    if ($this->record['category']==''){
      return '';
    }
    $Obj_Category = new Category;
    $categories = array();
    $category_csv = explode(",",$this->record['category']);
    foreach ($category_csv as $cat){
      $categories[$cat] = $cat;
    }
    $categories =
      $Obj_Category->get_labels_for_values(
        "'".implode("','",array_keys($categories))."'",
        "'".$this->_get_type()." Category'");
    $category_text = implode(", ",$categories);
    return $category_text;
  }

  protected function BL_comments(){
    if (!isset($this->_cp['comments_show']) || $this->_cp['comments_show']!='1'){
      return;
    }
    return $this->draw_comments_block($this->record['comments_allow']);
  }

  protected function BL_comments_link(){
    if (!isset($this->_cp['comments_link_show']) || $this->_cp['comments_link_show']!='1'){
      return;
    }
    if ($this->record['comments_allow']=='none'){
      return;
    }
    if ($this->record['comments_allow']=='registered' && !get_userID()){
      return;
    }
    $_label =
      ($this->record['comments_count']>0 ?
         $this->record['comments_count']." comment".($this->record['comments_count']==1 ? "" : "s")
      :
         "Add comment"
      );
    switch ($this->_mode){
      case "detail":
        $_href =    "#anchor_comments_list";
        $_onclick = " onclick=\"comment('".get_js_safe_ID($this->_get_type())."',".$this->record['ID'].",'new')\"";
        $_popup =   false;
      break;
      case "list":
        $_href =    $this->get_URL($this->record)."#anchor_comments_list";
        $_popup =   $this->record['systemID']!=SYS_ID;
        $_onclick = "";
      break;
    }
    return  "<a href=\"".$_href."\"".($_popup ? " rel=\"external\"" : "").$_onclick.">".$_label."</a>";
  }

  protected function BL_content(){
    if ($this->_current_user_rights['canEdit'] && get_var('submode')=='edit') {
      $edit_params =    $this->get_edit_params();
      $edit_params =    $this->get_edit_params();
      $Obj_RC =         new Report_Column;
      $column =         $Obj_RC->get_column_for_report($edit_params['report'],'content');
      $toolbarSet =     $column['formFieldSpecial'];
      $Obj_FCK =        new FCK;
      return
         $Obj_FCK->draw_editor('content',$this->record['content'],'100%',300,$toolbarSet)
        ."<input type='button' name='save' id='save' value='Save'"
        ." class='formButton' style='width: 100px;'"
        ." onclick=\""
        ."if (confirm('SAVE CHANGES\\n\\nAre you sure you wish to save changes to this "
        .strtolower($this->_get_object_name())."?\\nThis change cannot be undone.')){"
        ."geid_set('submode','save');geid('form').submit();}"
        ."else {alert('SAVE CHANGES\\n\\nNo changes have been saved.'); }\"/>"
        ."<input type='button' class='formButton' value='Cancel' style='width:100px;'"
        ." onclick=\"geid_set('submode','');geid('form').submit();\"/>"
        ."<br />\n<br />\n";
    }
    return str_replace('<!--more-->','',$this->record['content']);
  }

  protected function BL_content_truncated(){
    if (!isset($this->_cp['content_show']) || $this->_cp['content_show']!='1'){
      return;
    }
    if (isset($this->_cp['content_use_summary']) && $this->_cp['content_use_summary']=='1' && trim($this->record['content_summary'])){
      return
         $this->record['content_summary']
        ." <span title=\"Continues&hellip;\">&hellip;</span> "
        .$this->draw_link(
          'read_more',
          $this->record,
          array('label'=>$this->_cp['more_link_text'])
        );
    }
    $content =        preg_replace('/\s+/', ' ',$this->record['content']);
    $this->truncate_more($content);
    if (!isset($this->_cp['content_plaintext']) || $this->_cp['content_plaintext']=='0'){
      return $content;
    }
    $content =  convert_html_to_plaintext($content);
    if (isset($this->_cp['content_char_limit']) && (int)$this->_cp['content_char_limit']>0) {
      if ($this->truncate_text($content,$this->_cp['content_char_limit'])) {
        $content=
           $content
          ." <span title=\"Continues&hellip;\">&hellip;</span> "
          .$this->draw_link(
            'read_more',
            $this->record,
            array('label'=>$this->_cp['more_link_text'])
          );
      }
    }
    if (Page::hasDynamicTags($content)){
      return "(Preview not available)";
    }
    return $content;
  }

  protected function BL_context_selection_end(){
    $ID =       $this->record['ID'];
    $canEdit =  ($ID && $this->_current_user_rights['canEdit'] && ($this->record['systemID']==SYS_ID || $this->_current_user_rights['isMASTERADMIN']));
    if (!$canEdit){
      return "";
    }
    return "</div>";
  }

  protected function BL_context_selection_start(){
    $canEdit =  ($this->record['ID'] && $this->_current_user_rights['canEdit'] && ($this->record['systemID']==SYS_ID || $this->_current_user_rights['isMASTERADMIN']));
    if (!$canEdit){
      return;
    }
    if ($this->_get_type()=='event'){
      $Obj = new Event($this->record['ID']);
      $registrations = $Obj->count_registrations();
    }
    return
      "<div onmouseover=\""
     ."if(!CM_visible('CM_".$this->_context_menu_ID."')) {"
     ."this.style.backgroundColor='"
     .($this->record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
     ."';"
     .(isset($this->_safe_ID) ? "_CM.source='".$this->_safe_ID."';" : "")
     .(isset($this->record['category']) ? "_CM.category='".$this->record['category']."';" : "")
     ."_CM.type='".$this->_get_context_menu_ID()."';"
     ."_CM.ID='".$this->record['ID']."';"
     .(isset($this->record['enabled']) ? "_CM.enabled=".$this->record['enabled'].";" : "")
     .(isset($this->record['permSHARED']) ? "_CM.shared=".$this->record['permSHARED'].";" : "")
     .(isset($this->record['important']) ? "_CM.important=".$this->record['important'].";" : "")
     ."_CM_text[0]='&quot;".str_replace(array("'","\""),'',$this->record['title'])."&quot;';"
     ."_CM_text[1]=_CM_text[0];"
     .(isset($this->record['parentID']) ? "_CM_ID[2]='".$this->record['parentID']."';" : "")
     ."_CM_text[2]='".(isset($this->record['parentTitle']) && $this->record['parentTitle']!='' ? "&quot;".str_replace(array("'","\""),'',$this->record['parentTitle'])."&quot;" : "")."';"
     .($this->_current_user_rights['isSYSADMIN'] && isset($this->_block_layout['systemID']) && $this->_block_layout['systemID']==SYS_ID || $this->_current_user_rights['isMASTERADMIN'] ?
        (isset($this->_block_layout['ID']) ? "_CM_ID[3]='".$this->_block_layout['ID']."';" : "")
       .(isset($this->_block_layout['name']) ? "_CM_text[3]='&quot;".str_replace("'",'',$this->_block_layout['name'])."&quot;';" : "")
      : '')
     .($this->_get_type()=='event' ? "_CM.event_registrants=".$registrations.";" : "")
     ."}\" "
     ." onmouseout=\"this.style.backgroundColor='';_CM.type=''\">";
  }

  protected function BL_CP_footer(){
    if (!isset($this->_cp['box_footer'])){
      return;
    }
    return $this->_cp['box_footer'];
  }

  protected function BL_CP_header(){
    if (!isset($this->_cp['box_header'])){
      return;
    }
    return $this->_cp['box_header'];
  }

  protected function BL_category_label(){
    if (!isset($this->_category['label'])){
      return;
    }
    return $this->_category['label'];
  }

  protected function BL_category_value(){
    if (!isset($this->_category['value'])){
      return;
    }
    return $this->_category['value'];
  }

  protected function BL_date($format=false){
    if (isset($this->_cp['date_show']) && $this->_cp['date_show']!='1'){
      return;
    }
    return format_date($this->record['date'], $format);
  }

  protected function BL_date_field($field=false, $format=false){
    if (!$field){
      return "&#91;BL&#93;date_field(<b>name</b>)&#91;BL&#93; - <b>name</b> is required";
    }
    if (!isset($this->record[$field])){
      if (substr($field,0,4)=='xml:'){
        return "";
      }
      return "&#91;BL&#93;date_field('<b>".$field."</b>')&#91;BL&#93; - <b>".$field."</b> is not available";
    }
    return format_date($this->record[$field], $format);
  }

  protected function BL_date_heading_if_changed($format=false){
    static $old_YYYYMMDD;
    if (isset($this->_cp['date_show']) && $this->_cp['date_show']!='1'){
      return;
    }
    $date = $this->record['date'];
    if ($old_YYYYMMDD == $date) {
      return;
    }
    return format_date($date, $format);
  }

  protected function BL_extra_fields(){
    // Good for single and listings modes
    if (!isset($this->_cp['extra_fields_list']) || $this->_cp['extra_fields_list']=='') {
      return "";
    }
    $extra_fields = "";
    $extra_fields_arr = explode(",",$this->_cp['extra_fields_list']);
    $ObjGroup =   new Group;
    $personID =   get_userID();
    foreach ($extra_fields_arr as $field_pair) {
      $field_arr = explode("|",$field_pair);
      $field =    $field_arr[0];
      $label =    (isset($field_arr[1]) ? $field_arr[1] : "");
      $group =    (isset($field_arr[2]) ? $field_arr[2] : false);
      $show =     true;
      if ($group){
        $show = false;
        if ($groupID = $ObjGroup->get_ID_by_name($group)) {
          $ObjGroup->_set_ID($groupID);
          if ($perms = $ObjGroup->member_perms($personID)) {
            if ($perms['permVIEWER']==1 || $perms['permEDITOR']==1) {
              $show = true;
            }
          }
        }
      }
      $value = $this->record[$field];
      if ($show && $value) {
        $extra_fields.=
           "<div class=\"".$field."\">\n"
          ."  <div class='label'>".$label."</div>"
          ."  <div class='value'>".$value."</div>"
          ."</div><br />";
      }
    }
    return $extra_fields;
  }

  protected function BL_field($field=false,$match='',$found_text='',$not_found_text='',$pre_line='', $post_line=''){
    if (!$field){
      return "&#91;BL&#93;field(<b>name</b>)&#91;BL&#93; - <b>name</b> is required";
    }
    if (!isset($this->record[$field])){
      if (substr($field,0,4)=='xml:'){
        return "";
      }
      return "&#91;BL&#93;field('<b>".$field."</b>')&#91;BL&#93; - <b>".$field."</b> is not available";
    }
    if ($match==''){
      if($this->record[$field]==''){
        return;
      }
      $lines_arr = preg_split('/\R/',$this->record[$field]);
      return $pre_line.implode($post_line.$pre_line,$lines_arr).$post_line;
    }
    if (strpos($this->record[$field],$match)===false){
      return $not_found_text;
    }
    return $found_text;
  }

  protected function BL_field_for_group_member($field=false,$groups_csv=''){
    if (!$personID = get_userID()){
      return;
    }
    if (!$field){
      return "&#91;BL&#93;BL_field_for_group_member(<b>name</b>, <b>groups_csv</b>)&#91;BL&#93; - <b>name</b> is required";
    }
    if (!$groups_csv){
      return "&#91;BL&#93;BL_field_for_group_member(<b>name</b>, <b>groups_csv</b>)&#91;BL&#93; - <b>groups_csv</b> is required";
    }
    $valid =    false;
    $groups =   explode(",",$groups_csv);
    foreach($groups as $group) {
      $Obj_Group =  new Group;
      $groupID =    $Obj_Group->get_ID_by_name($group);
      if ($groupID!==false) {
        $Obj_Group->_set_ID($groupID);
        $perms =      $Obj_Group->member_perms($personID);
        if (!$perms===false) {
          if ($perms['permVIEWER']==1 || $perms['permEDITOR']==1) {
            $valid = true;
            break;
          }
        }
      }
    }
    if (!$valid){
      return '';
    }
    if (!isset($this->record[$field])){
      if (substr($field,0,4)=='xml:'){
        return "";
      }
      return "&#91;BL&#93;BL_field_for_group_member('<b>".$field."</b>, <b>groups_csv</b>')&#91;BL&#93; - <b>".$field."</b> is not available";
    }
    return $this->record[$field];
  }

  protected function BL_field_for_site_member($field=false){
    if (!get_person_permission("SYSMEMBER")){
      return;
    }
    if (!$field){
      return "&#91;BL&#93;BL_field_for_site_member(<b>name</b>)&#91;BL&#93; - <b>name</b> is required";
    }
    if (!isset($this->record[$field])){
      if (substr($field,0,4)=='xml:'){
        return "";
      }
      return "&#91;BL&#93;BL_field_for_site_member('<b>".$field."</b>')&#91;BL&#93; - <b>".$field."</b> is not available";
    }
    return $this->record[$field];
  }

  protected function BL_letter_anchor(){
    static $old_letter = false;
    if (!isset($this->_cp['show_letter_anchors']) || $this->_cp['show_letter_anchors']=='0') {
      return;
    }
    if (!isset($this->record[$this->_cp['results_order']])){
      return;
    }
    $letter = substr($this->record[$this->_cp['results_order']],0,1);
    if ($old_letter==$letter){
      return;
    }
    $old_letter = $letter;
    return "<a name=\"#".$letter."\">".$letter."</a>";
  }

  protected function BL_letter_anchor_quicklinks(){
    if (!isset($this->_cp['show_letter_anchors']) || $this->_cp['show_letter_anchors']=='0') {
      return "";
    }
    if (!isset($this->_records)){
      return;
    }
    if (!isset($this->_records[0][$this->_cp['results_order']])){
      return;
    }
    $old_letter = false;
    $letters = array();
    foreach($this->_records as $record){
      $letter = substr($record[$this->_cp['results_order']],0,1);
      if ($old_letter!=$letter){
        $old_letter = $letter;
        $letters[] = $letter;
      }
    }
    $out = array();
    foreach ($letters as $letter){
      $out[] = "<a href=\"#".$letter."\">".$letter."</a>";
    }
    return implode(' ',$out);
  }

  protected function BL_grouping_tab_controls(){
    return
      HTML::draw_section_tabs(
        $this->_grouping_tabs,
        $this->_ident."_tabs_".strToLower($this->_instance),
        $this->_grouping_tab_selected
      );
  }

  protected function BL_grouping_tab_div_close(){
    return "</div>";
  }

  protected function BL_grouping_tab_div_open(){
    return
      draw_section_tab_div(
        $this->_grouping_tab_current,
        $this->_grouping_tab_selected
      );
  }

  protected function BL_grouping_tab_footer(){
    if (count($this->_grouping_tabs)) {
      return "</div>";
    }
  }

  protected function BL_grouping_tab_header(){
    if (count($this->_grouping_tabs)) {
      return
        $this->BL_grouping_tab_controls()
       .$this->BL_grouping_tab_div_open();
    }
  }

  protected function BL_grouping_tab_separator_if_needed(){
    global $YYYY;
    $results_grouping = (isset($this->_cp['results_grouping']) ? $this->_cp['results_grouping'] : false);
    if (!count($this->_grouping_tabs)) {
      return;
    }
    sscanf($this->record['date'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
    $_YYYY =  ($_YYYY == "0000" ? $YYYY : $_YYYY);
    switch ($results_grouping){
      case "month":
        $idx =          $this->_ident."_".$_YYYY."_".$_MM;
        if ($idx != $this->_grouping_tab_current){
          $this->_grouping_tab_current = $idx;
          return
            $this->BL_grouping_tab_div_close()
           .$this->BL_grouping_tab_div_open();
        }
      break;
      case "year":
        $idx =          $this->_ident."_".$_YYYY;
        if ($idx != $this->_grouping_tab_current){
          $this->_grouping_tab_current = $idx;
          return
            $this->BL_grouping_tab_div_close()
           .$this->BL_grouping_tab_div_open();
        }
      break;
    }
  }

  protected function BL_item_footer_component(){
    if (!isset($this->_cp['item_footer_component']) ||$this->_cp['item_footer_component']==''){
      return "";
    }
    return draw_component_by_name($this->_cp['item_footer_component'],$this->record);
  }

  protected function BL_link(){
    if (isset($this->_cp['links_point_to_URL']) && $this->_cp['links_point_to_URL']==1 && isset($this->record['URL']) && $this->record['URL']!=''){
      $URL =        $this->record['URL'];
      $URL_popup =  ($this->record['popup']==1 ? true : false);
      $URL_title =  " title=\"Linked content".($URL_popup ? " (opens in a new window)" : "")."\"";
      return
         "<a href=\"".BASE_PATH.trim($URL,'/')."\""
        .$URL_title
        .($URL_popup ? " rel='external'" : "")
        .">";
    }
    if (isset($this->_cp['links_switch_video']) && $this->_cp['links_switch_video']==1 && isset($this->record['video']) && $this->record['video']!='') {
      $URL =        $this->record['video'];
      $URL_title =  " title=\"View Video\"";
      return
         "<a href=\"".$URL."\""
        ." onclick=\"return video_setup('lyo_video_large','".$URL."')\""
        .$URL_title
        .">";
    }
    $URL =        $this->get_URL($this->record);
    $URL_popup =  $this->record['systemID']!=SYS_ID;
    $URL_title =  " title=\"View Details".($URL_popup ? " (opens in a new window)" : "")."\"";
    return
       "<a href=\"".BASE_PATH.trim($URL,'/')."\""
      .$URL_title
      .($URL_popup ? " rel='external'" : "")
      .">";
  }

  protected function BL_links(){
    $link_arr =     array();
    if(isset($this->record['URL']) && $this->record['URL']!='') {
      $link_arr[] = $this->draw_link('link');
    }
    if (count($link_arr)){
      return
        implode("<span>|</span>",$link_arr);
    }
  }

  protected function BL_links_for_listings(){
    $link_arr =     array();
    if (!isset($this->_cp['content_show']) || $this->_cp['content_show']=='1'){
      if ($truncated = $this->truncate_more($this->record['content'])) {
        $link_arr[] =
          $this->draw_link(
            'read_more',
            $this->record,
            array('label'=>'')
          );
      }
    }
    if (isset($this->record['URL']) && $this->record['URL']!='' && !(isset($this->_cp['links_point_to_URL']) && $this->_cp['links_point_to_URL']==1)){
      $link_arr[] = $this->draw_link('link');
    }
    if (count($link_arr)){
      return
        implode("<span>|</span>",$link_arr);
    }
  }

  protected function BL_paging_controls(){
    return
      $this->_paging_controls_html;
  }

  protected function BL_paging_controls_current_position(){
    return
      $this->_paging_controls_current_pos;
  }

  protected function BL_path_safe_author(){
    if (isset($this->record['author'])) {
      return get_web_safe_ID($this->record['author']);
    }
  }

  protected function BL_path_safe_container_path(){
    if (isset($this->record['container_path'])) {
      $path_arr = explode('/',$this->record['container_path']);
      return get_web_safe_ID(array_pop($path_arr));
    }
  }

  protected function BL_path_safe_name(){
    if (isset($this->record['name'])) {
      return get_web_safe_ID($this->record['name']);
    }
  }

  protected function BL_QRCode($ecc='M',$size=4){
    global $system_vars;
    $URL =  trim($system_vars['URL'],'/').BASE_PATH.trim($this->get_URL($this->record),'/');
    return
       "<a class='qrcode' href=\"".BASE_PATH."img/qrcode/".$ecc."/40/".$URL."\" rel=\"external\">
       <img src=\"".BASE_PATH."img/qrcode/".$ecc."/".$size."/".$URL."\""
      ." title=\"QR Code - click to enlarge\n(".$URL.")\" style='border:0'/>"
      ."</a>";
  }

  protected function BL_rating(){
    if ($this->_current_user_rights['canRate']){
      return $this->draw_ratings_block();
    }
  }

  protected function BL_related() {
    return $this->draw_related_block();
  }

  protected function BL_related_products(){
    if (!isset($this->_cp['products']) || $this->_cp['products']!='1'){
      return;
    }
    return $this->draw_product_catalogue();
  }

  protected function BL_sequence(){
    if (isset($this->record['computed_sequence_value'])){
      return $this->record['computed_sequence_value'];
    }
  }

  protected function BL_shared_source_link(){
    if ($this->record['systemID']==SYS_ID){
      return;
    }
    return
      "<a href=\"".$this->record['systemURL']."\""
     ." title=\"Shared by ".$this->record['systemTitle']." - click to visit\""
     ." rel=\"external\">"
     ."<img src='".BASE_PATH."img/spacer' class='icons' style='padding:0;margin:0 2px 0 0;height:13px;width:15px;background-position:-1173px 0px;' alt=\"External content from ".$this->record['systemTitle']."\" />\n"
     ."</a> "
     ."<b>".$this->record['systemTitle']."</b>";
  }

  protected function BL_subtitle(){
    if (!isset($this->_cp['subtitle_show']) || $this->_cp['subtitle_show']!='1'){
      return;
    }
    return $this->record['subtitle'];
  }

  protected function BL_time_field($field=false){
    if (!$field){
      return "&#91;BL&#93;time_field(<b>name</b>)&#91;BL&#93; - <b>name</b> is required";
    }
    if (!isset($this->record[$field])){
      if (substr($field,0,4)=='xml:'){
        return "";
      }
      return "&#91;BL&#93;time_field('<b>".$field."</b>')&#91;BL&#93; - <b>".$field."</b> is not available";
    }
    return format_time($this->record[$field]);
  }

  protected function BL_title(){
    $enabled = (isset($this->record['enabled']) && $this->record['enabled']==0 ? false : true);
    return
       trim($this->record['title'])
      .(!$enabled ? " <em>(Non-enabled Publication)</em>" : "")
      .($enabled && $this->_is_expired_publication ? " <em>(Expired Publication)</em>" : "")
      .($enabled && $this->_is_pending_publication ? " <em>(Future Publication)</em>" : "");
  }

  protected function BL_title_linked(){
    global $page_vars;
    if (!isset($this->_cp['title_show']) || $this->_cp['title_show']!='1'){
      return;
    }
    if (!isset($this->_cp['title_linked']) || $this->_cp['title_linked']!='1'){
      return $this->BL_title();
    }
    return $this->BL_link().$this->BL_title()."</a>\n";
  }

  protected function BL_title_for_listing(){
    if (substr($this->record['URL'],0,8)=='./?page='){
      $this->record['URL'] = BASE_PATH.substr($this->record['URL'],8);
    }
    if (trim($this->record['URL'])==''){
//      y($this->record);die;
      return $this->record['title'];
    }
    return
      "<a href=\"".$this->record['URL']."\""
     .($this->record['popup']==1 || substr($this->record['URL'],0,8)!=BASE_PATH."?page=" ?
       " rel='external'" : "")
     .">".$this->record['title']."</a>";
  }

  protected function BL_thumbnail_image(){
    if (isset($this->_cp['thumbnail_show']) && $this->_cp['thumbnail_show']=='0'){
      return;
    }
    if (!isset($this->_cp['thumbnail_image'])){
      $image_letter = 's';
      $image_name = 'small';
    }
    else{
      $image_letter = $this->_cp['thumbnail_image'];
      switch ($this->_cp['thumbnail_image']){
        case "s":
          $image_name = 'small';
        break;
        case "m":
          $image_name = 'medium';
        break;
        case "l":
          $image_name = 'large';
        break;
        default:
          return;
        break;
      }
    }
    $wm =   isset($this->_cp['show_watermark']) && $this->_cp['show_watermark']==1;
    $img =  $this->BL_thumbnail_image_filepath();
    $cs =   (isset($this->record['thumbnail_cs_'.$image_name]) ? $this->record['thumbnail_cs_'.$image_name] : '');
    $thumbnail_file = (substr($img,0,strlen(BASE_PATH))==BASE_PATH ? BASE_PATH.substr($img,strlen(BASE_PATH)) : $img);
    if (!$img || !file_exists('.'.$thumbnail_file)){
      $img = false;
    }
    if (!$img){
      return;
    }
    if (isset($this->_cp['links_open_image']) && $this->_cp['links_open_image']==1) {
      $URL =        ($wm ? BASE_PATH."img/wm" : "").$img;
      $URL_title =  " title=\"View full-sized image for ".$this->record['title']."\"";
      $read_link =
         "<a href=\"".$URL."\""
        .$URL_title
        ." rel='external'"
        .">";
    }
    else {
      $read_link = $this->BL_link();
    }
    $thumbnail_img =
      ($this->_cp['thumbnail_width'] ?
         ($this->_cp['thumbnail_height'] ?
             BASE_PATH."img/".($wm ? "wm" : "resize").$thumbnail_file
            ."?width=".$this->_cp['thumbnail_width']
            ."&amp;height=".$this->_cp['thumbnail_height']
            .($cs ? "&amp;cs=".$cs : "")
          :
             BASE_PATH."img/".($wm ? "wm" : "resize").$thumbnail_file
            ."?width=".$this->_cp['thumbnail_width']
            .($cs ? "&amp;cs=".$cs : "")
         )
       :
         ($this->_cp['thumbnail_height'] ?
             BASE_PATH."img/".($wm ? "wm" : "resize").$thumbnail_file
            ."?height=".$this->_cp['thumbnail_height']
            .($cs ? "&amp;cs=".$cs : "")
          :
             BASE_PATH."img/".($wm ? "wm" : "resize").$thumbnail_file
            ."?"
            .($cs ? "&amp;cs=".$cs : "")
         )
      );
    return
      "<div class='thumbnail'>"
     .(isset($this->_cp['thumbnail_link']) && $this->_cp['thumbnail_link'] ? $read_link : "")
     ."<img class='thumbnail_".$image_letter."' alt=\"".$this->record['title']."\""
     ." src=\"".$thumbnail_img."\" />"
     .(isset($this->_cp['thumbnail_link']) && $this->_cp['thumbnail_link'] ? "</a>" : "")
     ."</div>";
  }

  protected function BL_thumbnail_image_filename(){
    $img = $this->BL_thumbnail_image_filepath();
    if ($img){
      $thumbnail_file_arr = explode('/',$img);
      return array_pop($thumbnail_file_arr);
    }
  }

  protected function BL_thumbnail_image_filepath(){
    $img = $this->record['thumbnail_small'];
    if (!isset($this->_cp['thumbnail_image'])){
      return $this->record['thumbnail_small'];
    }
    switch ($this->_cp['thumbnail_image']){
      case "s":   $img = $this->record['thumbnail_small'];  break;
      case "m":   $img = $this->record['thumbnail_medium']; break;
      case "l":   $img = $this->record['thumbnail_large'];  break;
      default:    $img = false;                       break;
    }
    return $img;
  }

  protected function BL_thumbnail_image_at_bottom(){
    if (isset($this->_cp['thumbnail_at_top']) && $this->_cp['thumbnail_at_top']=='0'){
      return $this->BL_thumbnail_image();
    }
  }

  protected function BL_thumbnail_image_at_top(){
    if (isset($this->_cp['thumbnail_at_top']) && $this->_cp['thumbnail_at_top']=='1'){
      return $this->BL_thumbnail_image();
    }
  }

  protected function BL_video(){
    if (!isset($this->_cp['video_show']) || $this->_cp['video_show']=='0'){
      return;
    }
    if (!isset($this->record['video']) || !$this->record['video']){
      return;
    }
    $width =    (isset($this->_cp['video_width']) && (int)$this->_cp['video_width'] ? $this->_cp['video_width'] : 240);
    $height =   (isset($this->_cp['video_height']) && (int)$this->_cp['video_height'] ? $this->_cp['video_height'] : 180);
    $video =    $this->record['video'];
    return "[youtube: ".$video."|".$width."|".$height."]";
  }

  public function convert_Block_Layout($string){
    $string = convert_ecl_tags($string);
    $pagebits =       preg_split("/\[BL\]|\[\/BL\]/",$string);
    if (count($pagebits)<=1) {
      return $string;
    }
    $out =            "";
    $plaintext =      true;   // Assume we are starting as plain text
    foreach($pagebits as $bit){
      if ($plaintext){
        $out.= $bit;
      }
      else {
        $bit_arr =      explode('[ARG]',$bit);
        $bit_name_bits =    explode('(',$bit_arr[0]);
        $bit_name =     $bit_name_bits[0];
        $bit_arg =      (isset($bit_name_bits[1]) ? trim($bit_name_bits[1],')') : '');
        $bit_prefix =   (isset($bit_arr[1]) ? $bit_arr[1] : "");
        $bit_suffix =   (isset($bit_arr[2]) ? $bit_arr[2] : "");
        $bit_else =     (isset($bit_arr[3]) ? $bit_arr[3] : "");
        if (method_exists($this,"BL_".$bit_name)){
          $expr = "return trim(\$this->BL_".$bit_name."(".$bit_arg."));";
          $result = eval($expr);
          if ($result) {
            $out.=$bit_prefix.$result.$bit_suffix;
          }
          else {
            $out.=$bit_else;
          }
        }
        else {
          $out.=
             "<div style='display:inline-block;background-color:#ffe0e0;color:#ff0000'"
            ." title=\"This Block Layout tag is not defined: [BL]".strip_tags($bit)."[/BL]\">"
            ."[BL]".strip_tags($bit)."[/BL]</div>";
        }
      }
      $plaintext = !$plaintext;
    }
    return $out;
  }

  public function draw_Block_Layout(){
    $out =
       $this->convert_Block_Layout($this->_block_layout['listings_panel_header'])
      .$this->convert_Block_Layout($this->_block_layout['listings_group_header']);
    for ($i=0; $i<count($this->_records); $i++) {
      $this->record = $this->_records[$i];
      $this->xmlfields_decode($this->record);
      $this->record['computed_sequence_value'] = $i+1+$this->_filter_offset;
      if ($i>0 && !$this->_draw_detail_test_if_grouping_has_changed()){
        $out.=  $this->convert_Block_Layout($this->_block_layout['listings_item_separator']);
      }
      $out.=    $this->convert_Block_Layout($this->_block_layout['listings_group_separator']);
      $out.=    $this->convert_Block_Layout($this->_block_layout['listings_item_detail']);
    }
    $out.=
       $this->convert_Block_Layout($this->_block_layout['listings_group_footer'])
      .$this->convert_Block_Layout($this->_block_layout['listings_panel_footer']);
    return $out;
  }

  function draw_css_include($type){
    static $css_included;
    switch ($type){
      case "detail":
        $field = 'single_item_css';
      break;
      case "listings":
        $field = 'listings_css';
      break;
      default:
        print "Invalid type $type given:<br />".x();
        return;
      break;
    }
    $ID = $this->_get_ID();
    $key = $ID."_".$type;
    if (isset($css_included[$key])){
      return;
    }
    $crc32 = dechex(crc32($this->record[$field]));
    Page::push_content(
      'style_include',
       "<link rel=\"stylesheet\" type=\"text/css\""
      ." href=\"/css/block_layout_".$type."/".$ID."/".$crc32."\" />"
      ."<!-- Block Layout for ".$this->record['name']." ".$type." -->"

    );
    $css_included[$key] = true;
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_BLOCK_LAYOUT;
  }
}
?>