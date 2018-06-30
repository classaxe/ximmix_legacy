<?php
define('VERSION_LINK','1.0.6');
/*
Version History:
  1.0.6 (2013-06-07)
    1) Changed the following CPs for listings mode:
         Old: 'grouping_tabs',    'filter_limit',  'filter_order_by', 'paging_controls'
         New: 'results_grouping', 'results_limit', 'results_order',   'results_paging'

  (Older version history in class.link.txt)
*/
class Link extends Posting_Container {
  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_type('link');
    $this->_set_assign_type('link');
    $this->_set_object_name('Link');
    $this->_set_container_object_type('Link');
    $this->_set_has_activity(false);
    $this->_set_has_categories(false);
    $this->_set_has_groups(true);
    $this->_set_has_keywords(false);
    $this->_set_has_publish_date(true);      // Do now allow item to be seen prior to publish date
    $this->set_edit_params(
      array(
        'report'=>                  'links',
        'report_rename'=>           true,
        'report_rename_label'=>     'new title',
        'icon_delete'=>             '[ICON]18 18 5284 Delete this '.$this->_get_object_name().'[/ICON]',
        'icon_edit'=>               '[ICON]19 19 5246 Edit this '.$this->_get_object_name().'[/ICON]',
        'icon_edit_disabled'=>      '[ICON]19 19 5265 (Edit this '.$this->_get_object_name().')[/ICON]',
        'icon_edit_popup'=>         '[ICON]19 19 4713 Edit this '.$this->_get_object_name().' in a popup window[/ICON]'
      )
    );
    $this->_cp_vars_detail = array(
      'block_layout'=>              array('match'=>'',                  'default'=>'Gallery Album', 'hint'=>'Name of Block Layout to use'),
      'contents_block_layout'=>     array('match'=>'',                  'default'=>'Gallery Image', 'hint'=>'Name of Block Layout to use for content listings'),
      'contents_results_limit'=>    array('match'=>'range|0,n',         'default'=>'10',            'hint'=>'0..n'),
      'contents_results_paging'=>   array('match'=>'enum|0,1,2',        'default'=>'2',             'hint'=>'0|1|2 - 1 for buttons, 2 for links'),
      'contents_show'=>             array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'Whether or not to list contents of album'),
      'contents_thumbnail_at_top'=> array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'0|1'),
      'contents_thumbnail_height'=> array('match'=>'range|1,n',         'default'=>'0',             'hint'=>'|1..n or blank - height in px to resize'),
      'contents_thumbnail_width'=>  array('match'=>'range|1,n',         'default'=>'100',           'hint'=>'|1..n or blank - width in px to resize'),
      'extra_fields_list'=>         array('match'=>'',                  'default'=>'',              'hint'=>'CSV list format: field|label|group,field|label|group...'),
      'item_footer_component'=>     array('match'=>'',                  'default'=>'',              'hint'=>'Name of component rendered below displayed Job Posting'),
      'thumbnail_height'=>          array('match'=>'range|1,n',         'default'=>'',              'hint'=>'|1..n or blank - height in px to resize'),
      'thumbnail_width'=>           array('match'=>'range|1,n',         'default'=>'',              'hint'=>'|1..n or blank - width in px to resize'),
      'title_linked'=>              array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'0|1'),
      'title_show'=>                array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'0|1')
    );
    $this->_cp_vars_listings = array(
      'background'=>                array('match'=>'hex3|',             'default'=>'',              'hint'=>'Hex code for background colour to use'),
      'block_layout'=>              array('match'=>'',                  'default'=>'Gallery Album', 'hint'=>'Name of Block Layout to use'),
      'box'=>                       array('match'=>'enum|0,1,2',        'default'=>'0',             'hint'=>'0|1|2'),
      'box_footer'=>                array('match'=>'',                  'default'=>'',              'hint'=>'Text below displayed Job Postings'),
      'box_header'=>                array('match'=>'',                  'default'=>'',              'hint'=>'Text above displayed Job Postings'),
      'box_rss_link'=>              array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'0|1'),
      'box_title'=>                 array('match'=>'',                  'default'=>'Gallery Albums','hint'=>'text'),
      'box_title_link'=>            array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'0|1'),
      'box_title_link_page'=>       array('match'=>'',                  'default'=>'gallery-albums','hint'=>'page'),
      'box_width'=>                 array('match'=>'range|0,n',         'default'=>'0',             'hint'=>'0..x'),
      'category_show'=>             array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'0|1'),
      'comments_link_show'=>        array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'0|1'),
      'content_char_limit'=>        array('match'=>'range|0,n',         'default'=>'0',             'hint'=>'0..n'),
      'content_plaintext'=>         array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'0|1'),
      'content_show'=>              array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'0|1'),
      'date_show'=>                 array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'0|1'),
      'extra_fields_list'=>         array('match'=>'',                  'default'=>'',              'hint'=>'CSV list format: field|label|group,field|label|group...'),
      'filter_category_list'=>      array('match'=>'',                  'default'=>'*',             'hint'=>'Optionally limits items to those in this gallery album - / means none'),
      'filter_category_master'=>    array('match'=>'',                  'default'=>'',              'hint'=>'Optionally INSIST on this category'),
      'filter_container_path'=>     array('match'=>'',                  'default'=>'',              'hint'=>'Optionally limits items to those contained in this folder'),
      'filter_container_subs'=>     array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'If filtering by container folder, enable this setting to include subfolders'),
      'filter_memberID'=>           array('match'=>'range|0,n',         'default'=>'',              'hint'=>'ID of Community Member to restrict by that criteria'),
      'filter_personID'=>           array('match'=>'range|0,n',         'default'=>'',              'hint'=>'ID of Person to restrict by that criteria'),
      'item_footer_component'=>     array('match'=>'',                  'default'=>'',              'hint'=>'Name of component rendered below each displayed Job Posting'),
      'more_link_text'=>            array('match'=>'',                  'default'=>'(More)',        'hint'=>'text for \'Read More\' link'),
      'results_grouping'=>          array('match'=>'enum|,month,year',  'default'=>'',              'hint'=>'|month|year'),
      'results_limit'=>             array('match'=>'range|0,n',         'default'=>'3',             'hint'=>'0..n'),
      'results_order'=>             array('match'=>'enum|date,title',   'default'=>'date',          'hint'=>'date|title'),
      'results_paging'=>            array('match'=>'enum|0,1,2',        'default'=>'0',             'hint'=>'0|1|2 - 1 for buttons, 2 for links'),
      'thumbnail_at_top'=>          array('match'=>'enum|0,1',          'default'=>'0',             'hint'=>'0|1'),
      'thumbnail_height'=>          array('match'=>'range|1,n',         'default'=>'',              'hint'=>'|1..n or blank - height in px to resize'),
      'thumbnail_link'=>            array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'0|1'),
      'thumbnail_width'=>           array('match'=>'range|1,n',         'default'=>'',              'hint'=>'|1..n or blank - width in px to resize'),
      'title_linked'=>              array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'0|1'),
      'title_show'=>                array('match'=>'enum|0,1',          'default'=>'1',             'hint'=>'0|1')
    );
  }

  public function get_children() {
    global $system_vars;
    $features = "'".implode("','",explode(",",str_replace(" ","",$system_vars['features'])))."'";
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `required_feature`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `type` = '".$this->_get_type()."' AND\n"
      ."  `parentID` = ".$this->_get_ID()." AND\n"
      ."  `systemID` IN(1,".SYS_ID.") AND\n"
      .(isset($_SESSION['person']) ?
         (!$_SESSION['person']['permMASTERADMIN']  ? "  `required_feature` IN ('',".$features.") AND\n" : "")
        ."  (\n"
        .($_SESSION['person']['permMASTERADMIN']   ? "    `permMASTERADMIN`='1' OR\n" : "")
        .($_SESSION['person']['permUSERADMIN']     ? "    `permUSERADMIN`='1' OR\n"    : "")
        .($_SESSION['person']['permSYSADMIN']      ? "    `permSYSADMIN`='1' OR\n"    : "")
        .($_SESSION['person']['permSYSAPPROVER']   ? "    `permSYSAPPROVER`='1' OR\n" : "")
        .($_SESSION['person']['permSYSEDITOR']     ? "    `permSYSEDITOR`='1' OR\n"   : "")
        .($_SESSION['person']['permSYSMEMBER']     ? "    `permSYSMEMBER`='1' OR\n"   : "")
        .($_SESSION['person']['permGROUPVIEWER']   ? "    `permGROUPVIEWER`='1' OR\n"  : "")
        .($_SESSION['person']['permGROUPEDITOR']   ? "    `permGROUPEDITOR`='1' OR\n"  : "")
        ."    0\n"
        ."  )\n"
       :
         "  `permPUBLIC`='1'\n"
       )
      ."ORDER BY\n"
      ."  `seq`";
    $records = $this->get_records_for_sql($sql);
    $out = array();
    foreach ($records as $record){
      $out[] = $record['ID'];
    }
    return $out;
  }

  public function draw_js($depth=1,$name='') {
    if (!$record = $this->get_record()){
      return "";
    }
    $visible = $this->is_visible($record);
    if (!$visible) {
      return "";  // Entire structute is invisible so quit now
    }
    $children = $this->get_children();
    $name = ($name=="" ? "L".$depth : $name);
    $hasChildren = count($children)>0;
    $icon = false;
    if ($record['icon']){
      $icon = str_replace('[/ICON]','',str_replace('[ICON]','',$record['icon']));
      $icon_arr = explode(' ',$icon);
      $icon = './img/icon/'.$icon_arr[2].'/'.$icon_arr[0].'/'.$icon_arr[1];
    }
    $html = "<ul id='help' class='list_folder_expander'>";
    $out =
       str_repeat("  ",$depth)
      .$name." = "
      .($depth==0 ?
         ""
       :
         ($hasChildren ? "insFldX" : "insDocX")
        ."("
        .($depth==1 ? "foldersTree" : "L".($depth-1))
        .", "
       )
      .($hasChildren ? "gFld" : "gLnk")
      ."("
      .($hasChildren==1 ? "" : "\"R\", ")
      ."\"".$record['title']."\", \"".$record['URL']."\")"
      .($depth==0 ? "" : ")")
      .";\n"
      .str_repeat("  ",$depth)
      .$name.".xID = \"".$record['URL']."\";\n"
      .($icon ? str_repeat("  ",$depth).$name.".iconSrc = \"".$icon."\";\n" : "");
    foreach($children as $child){
      $Obj_TN = new Link($child);
      $out.= $Obj_TN->draw_js($depth+1);
    }
    return $out;
  }

  function draw_treeview_js($open=false){
    return
       "USETEXTLINKS =    1;\n"
      ."STARTALLOPEN =    ".($open==false ? 0 : 1).";\n"
      ."HIGHLIGHT =       1;\n"
      ."PERSERVESTATE =   0;\n"
      ."HIGHLIGHT_COLOR = 'black';\n"
      ."HIGHLIGHT_BG =    '#ffff80';\n"
      ."ICONPATH =        \"".BASE_PATH."img/treeview/\";\n"
      ."USEICONS =        1;\n"
      ."WRAPTEXT =        0;\n"
      ."\n"
      ."var counterI =    0;\n"
      ."\n"
      ."function insFldX(parentOb, childOb) {\n"
      ."  childOb.xID = 'X' + counterI;\n"
      ."  counterI--;\n"
      ."  return insFld(parentOb, childOb);\n"
      ."}\n"
      ."function insDocX(parentOb, childOb) {\n"
      ."  childOb.xID = 'Y' + counterI;\n"
      ."  counterI--;\n"
      ."  return insDoc(parentOb, childOb);\n"
      ."}\n";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function manage_links(){
    if (get_var('command')=='report'){
      return draw_auto_report('links-for-link',1);
    }
    $out = "<h3 style='margin:0.25em'>Links inside this ".$this->_get_object_name().":</h3>";
    if (!get_var('selectID')) {
      $out.="<p style='margin:0.25em'>No contents - this ".$this->_get_object_name()." has not been saved yet.</p>";
    }
    else {
      $out.= draw_auto_report('links-for-link',1);
    }
    return $out;
  }

  public function get_version(){
    return VERSION_LINK;
  }
}

?>