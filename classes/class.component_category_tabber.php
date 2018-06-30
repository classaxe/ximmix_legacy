<?php
  define ("VERSION_COMPONENT_CATEGORY_TABBER","1.0.4");
/*
Version History:
  1.0.4 (2013-06-07)
    1) Changed the following CPs:
         Old: 'filter_limit_per_category',  'filter_sort_by'
         New: 'results_limit_per_category', 'results_order'

  (Older version history in class.component_category_tabber.txt)
*/
class Component_Category_Tabber extends Component_Base {
  protected $_records = array();

  function __construct(){
    $this->_ident =            "category_tabber";
    $this->_parameter_spec =   array(
      'author_show' =>                          array('match' => 'enum|0,1',  			                'default' =>'0',          'hint'=>'0|1'),
      'block_layout' =>                         array('match' => '',                                    'default'=>'Category Tabber', 'hint'=>'Name of Block Layout to use'),
      'category_show' =>                        array('match' => 'enum|0,1',  			                'default' =>'0',          'hint'=>'0|1'),
      'content_char_limit' =>                   array('match' => 'range|0,n',							'default' =>'0',          'hint'=>'0..n'),
      'content_plaintext' =>                    array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'content_show' =>                         array('match' => 'enum|0,1',  							'default' =>'1',          'hint'=>'0|1'),
      'content_use_summary' =>                  array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'date_show' =>                            array('match' => 'enum|0,1',  							'default' =>'1',          'hint'=>'0|1'),
      'extra_fields_list' =>                    array('match' => '',									'default' =>'',           'hint'=>'CSV list format: field|label|group,field|label|group...'),
      'filter_category_list' =>                 array('match' => '',									'default' =>'',           'hint'=>'CSV list of categories'),
      'filter_category_master' =>               array('match' => '',									'default' =>'',           'hint'=>'Optionally INSIST on this category'),
      'filter_sites_list' =>                    array('match' => '',									'default' =>'',           'hint'=>'CSV list of site URLs'),
      'filter_type' =>                          array('match' => 'enum|Article,Product',                'default' =>'Article',    'hint'=>'Article|Product'),
      'links_point_to_URL' =>                   array('match' => 'enum|0,1',                            'default' =>'0',          'hint'=>'0|1 - If there is a URL, both title and thumbnails links go to it'),
      'more_link_text' =>                       array('match' => '',									'default' =>'(More)',     'hint'=>'text for more link'),
      'related_show' =>                         array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'results_limit_per_category' =>           array('match' => 'range|1,n',							'default' =>'1',          'hint'=>'Max articles per tab'),
      'results_order' =>                        array('match' => 'enum|latest,title',					'default' =>'latest',     'hint'=>'latest|title'),
      'subtitle_show' =>                        array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'thumbnail_at_top' =>                     array('match' => 'enum|0,1',   						    'default' =>'1',          'hint'=>'0|1'),
      'thumbnail_height' =>                     array('match' => 'range|1,n',							'default' =>'600',        'hint'=>'|1..n'),
      'thumbnail_image' =>                      array('match' => 'enum|s,m,l',							'default' =>'s',          'hint'=>'s|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'),
      'thumbnail_link' =>                       array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'thumbnail_show' =>                       array('match' => 'enum|0,1',                            'default' =>'1',          'hint'=>'0|1'),
      'thumbnail_width' =>                      array('match' => 'range|1,n',							'default' =>'200',        'hint'=>'|1..n'),
      'title_linked' =>                         array('match' => 'enum|0,1',  							'default' =>'1',          'hint'=>'0|1'),
      'title_show' =>                           array('match' => 'enum|0,1',  							'default' =>'1',          'hint'=>'0|1'),
    );
  }

  public function draw($instance='',$args=array(),$disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    if (!$this->_Obj_Block_Layout){
      $this->_draw_error_block_layout_missing();
      return $this->_render_error();
    }
    if (!$this->_records){
      $this->_draw_error_no_records();
      return $this->_render();
    }
    $this->_draw_tabs();
    foreach ($this->_tabs as $tab){
      $this->_Obj->_category = $tab;
      $records = array();
      foreach ($this->_records as $record) {
        if (strToLower($record['cat'])==strToLower($tab['value'])){
          $records[] = $record;
        }
      }
      $this->_html.=    draw_section_tab_div($tab['value'],$this->_selected_section);
      $this->_html.=    $this->_Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_group_header']);
      $this->_draw_add_icon($tab['value']);
      for ($i=0; $i<count($records); $i++) {
        $this->_Obj->record = $records[$i];
        $this->xmlfields_decode($this->_Obj->record);
        $this->_Obj->record['computed_sequence_value'] = $i+1;
        $this->_Obj->_set('_context_menu_ID',$record['type']);
        if ($i>0){
          $this->_html.=  $this->_Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_item_separator']);;
        }
        $this->_html.=  $this->_Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_item_detail']);
      }
      $this->_html.=    $this->_Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_group_footer']);
      $this->_html.=    "</div>\n";
    }
    return $this->_render();
  }

  protected function _draw_add_icon($category){
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
    $this->_html.=
      "<a href=\".\" onclick=\"details("
     ."'".$this->_add_form."','','".$this->_popup['h']."','".$this->_popup['w']."','','','','&amp;category=".$category."'"
     .");return false;\"  title='Add ".$this->_cp['filter_type']." for ".$category." category&hellip;'>"
     ."[ICON]11 11 1188 Add ".$this->_cp['filter_type']." for ".$category." category&hellip;[/ICON]</a>\n";
  }

  protected function _draw_error_block_layout_missing(){
    $this->_html.= "<b>Error:</b> There is no such Block Layout as '".$this->_cp['block_layout']."'";
  }

  protected function _draw_error_no_records(){
    $this->_html.= "No records available to display.";
  }

  protected function _draw_tabs(){
    $this->_html.=
      HTML::draw_section_tabs(
        $this->_tabs,
        $this->_safe_ID,
        $this->_selected_section
      );
  }


  protected function _render(){
    return
      "<div id=\"".$this->_safe_ID."\">\n"
     .$this->_Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_panel_header'])
     .$this->_html
     .$this->_Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_panel_footer'])
     ."</div>";
  }

  protected function _render_error(){
    return
       "<div id=\"".$this->_safe_ID."\">\n"
      .$this->_html
      ."</div>";
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_load_block_layout();
    $this->_setup_load_user_rights();
    $this->_setup_load_popup_spec();
    $this->_setup_load_object();
    $this->_setup_load_systemIDs();
    $this->_setup_load_records();
    $this->_setup_load_categories();
    $this->_setup_load_tabs();
  }

  protected function _setup_load_block_layout(){
    if ($this->_Obj_Block_Layout = parent::_setup_load_block_layout($this->_cp['block_layout'])){
      $this->_Obj_Block_Layout->draw_css_include('listings');
    }
  }

  protected function _setup_load_categories(){
    if (!$this->_records){
      return;
    }
    $this->_categories = array();
    foreach($this->_records as $record) {
      $this->_categories[$record['cat']] = $record['cat_label'];
    }
  }

  protected function _setup_load_object(){
    $this->_Obj = new $this->_cp['filter_type'];
    $args = array(
      '_cp' =>                          $this->_cp,
      '_current_user_rights' =>         $this->_current_user_rights,
      '_block_layout' =>                $this->_Obj_Block_Layout->record,
      '_mode' =>                        'list',
      '_safe_ID' =>                     $this->_safe_ID
    );
    $this->_Obj->_set_multiple($args);
  }

  protected function _setup_load_popup_spec(){
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
    switch ($this->_cp['filter_type']){
      case 'Article':
        $this->_add_form =  'articles';
      break;
      case 'Product':
        $this->_add_form =  'product';
      break;
    }
    $this->_popup =    get_popup_size($this->_add_form);
  }

  protected function _setup_load_records(){
    switch ($this->_cp['results_order']) {
      case "latest":
        $this->_order = "`date` DESC";
      break;
      default:
        $this->_order = false;
      break;
    }
    $args =
      array(
        'category_list' =>      $this->_cp['filter_category_list'],
        'category_master' =>    $this->_cp['filter_category_master'],
        'systemIDs_csv' =>      $this->_systemIDs_csv,
        'limit_per_category' => $this->_cp['results_limit_per_category'],
        'order' =>              $this->_order
      );
    $this->_records = $this->_Obj->get_n_per_category($args);
  }

  protected function _setup_load_systemIDs(){
    $this->_systemIDs_csv = System::get_IDs_for_URLs($this->_cp['filter_sites_list']);
  }

  protected function _setup_load_tabs(){
    if (!$this->_records){
      return;
    }
    $this->_tabs = array();
    $this->_category_arr = array();
    foreach($this->_categories as $value=>$label) {
      $this->_category_arr[] = $value;
      $this->_tabs[] =
        array(
          'ID' =>       $value,     // Used by HTML::draw_section_tabs()
          'value' =>    $value,     // used in BL tag
          'label' =>    $label
        );
    }
    $temp = get_var('selected_section');
    $this->_selected_section = (in_array($temp,$this->_category_arr) ? $temp : $this->_category_arr[0]);
  }


  public function get_version(){
    return VERSION_COMPONENT_CATEGORY_TABBER;
  }
}
?>