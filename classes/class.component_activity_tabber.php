<?php
  define ("VERSION_COMPONENT_ACTIVITY_TABBER","1.0.5");
/*
Version History:
  1.0.5 (2013-05-10)
    1) Tweak to Component_Activity_Tabber::_setup_load_tabs() for when there are
       no records to set tabs for
  1.0.4 (2013-05-09)
    1) Changes to make tabs select on last opened one if a post has occured
    2) Rendering now occurs within named container div
  1.0.3 (2012-09-16)
    1) Huge changes to use Block Layouts, be much more configurable and to allow
       context menu editing

  (Older version history in class.component_activity_tabber.txt)
*/
class Component_Activity_Tabber extends Component_Base {
  protected $_records = false;
  protected $_Obj_Displayable_Item;

  public function __construct(){
    $this->_ident =            "activity_tabber";
    $this->_parameter_spec =   array(
      'activity_list' =>        array('match' => 'enum_csv|comments,emails,ratings,visits','default'=>'visits,emails','hint'=>'CSV list may include: comments,emails,ratings,visits'),
      'block_layout' =>         array('match' => '',                    'default'=>'Activity Tabber', 'hint'=>'Name of Block Layout to use'),
      'comments_show' =>        array('match' => 'enum|0,1',            'default' =>'1',              'hint'=>'0|1'),
      'comments_link_show' =>   array('match' => 'enum|0,1',            'default' =>'1',              'hint'=>'0|1'),
      'content_char_limit' =>   array('match' => 'range|0,n', 			'default'=>'0',               'hint'=>'0..n'),
      'content_plaintext' =>    array('match' => 'enum|0,1',            'default'=>'0',               'hint'=>'0|1'),
      'content_show' =>         array('match' => 'enum|0,1',  			'default'=>'0',               'hint'=>'0|1'),
      'content_use_summary' =>  array('match' => 'enum|0,1',  			'default' =>'0',              'hint'=>'0|1'),
      'date_show' =>            array('match' => 'enum|0,1',  			'default'=>'1',               'hint'=>'0|1'),
      'exclude_list' =>         array('match' => '',      				'default'=>'',                'hint'=>'CSV list of page and postings to exclude'),
      'label_comments' =>       array('match' => '',                    'default'=>'Most Commented',  'hint'=>'Text for Label'),
      'label_emails' =>         array('match' => '',                    'default'=>'Most Emailed',    'hint'=>'Text for Label'),
      'label_ratings' =>        array('match' => '',                    'default'=>'Most Rated',      'hint'=>'Text for Label'),
      'label_visits' =>         array('match' => '',                    'default'=>'Most Viewed',     'hint'=>'Text for Label'),
      'limit_per_activity' =>   array('match' => 'range|1,n', 			'default'=>'5',               'hint'=>'1..n Max items to show per activity'),
      'more_link_text' =>       array('match' => '',                    'default'=>'(More)',          'hint'=>'text for \'Read More\' link'),
      'section_tabs_always' =>  array('match' => 'enum|0,1',            'default' =>'1',              'hint'=>'0|1 - if set will always show section tabs, even if there is only one of them'),
      'title_linked' =>         array('match' => 'enum|0,1',            'default' =>'1',              'hint'=>'0|1'),
      'title_show' =>           array('match' => 'enum|0,1',            'default' =>'1',              'hint'=>'0|1')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    if (!System::has_feature('Activity-Tracking')){
      $this->_draw_error_tracking_not_enabled();
      return $this->_render_error();
    }
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
      $this->_html.=
         draw_section_tab_div($tab['ID'],$this->_selected_section)."\n"
        ."<div class='items'>\n";
      foreach ($this->_records as $record) {
        if ($record['activity']==$tab['ID']){
          $this->_html.=  $this->_draw_item($record);
        }
      }
      $this->_html.="</div></div>";
    }
    return $this->_render();
  }

  protected function _draw_error_block_layout_missing(){
    $this->_html.= "<b>Error:</b> There is no such Block Layout as '".$this->_cp['block_layout']."'";
  }

  protected function _draw_error_no_records(){
    $this->_html.= "No records available to display.";
  }

  protected function _draw_error_tracking_not_enabled(){
    $this->_html.= "<b>Error:</b> Activity Tracking is not enabled.";
  }

  protected function _draw_item($record){
    $Obj =              new $record['object_type'];
    $Obj->record =      $record;
    $args = array(
      '_cp' =>                          $this->_cp,
      '_current_user_rights' =>         $this->_current_user_rights,
      '_block_layout' =>                $this->_Obj_Block_Layout->record,
      '_context_menu_ID' =>             $record['type'],
      '_mode' =>                        'list',
      '_safe_ID' =>                     $this->_safe_ID
    );
    $Obj->_set_multiple($args);
    return
       $Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_item_detail'])
      .$Obj->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_item_separator']);
  }

  protected function _draw_tabs(){
    if (!$this->_cp['section_tabs_always'] && count($this->_tabs)<2){
      return;
    }
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
     .$this->_Obj_Displayable_Item->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_panel_header'])
     .$this->_html
     .$this->_Obj_Displayable_Item->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_panel_footer'])
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
    $this->_Obj_Displayable_Item = new Displayable_Item;
    $this->_setup_load_records();
    $this->_setup_load_tabs();
  }

  protected function _setup_load_block_layout(){
    if ($this->_Obj_Block_Layout = parent::_setup_load_block_layout($this->_cp['block_layout'])){
      $this->_Obj_Block_Layout->draw_css_include('listings');
    }
  }

  protected function _setup_load_records(){
    $Obj = new Activity;
    $args =
      array(
        'activity_list' =>      $this->_cp['activity_list'],
        'exclude_list' =>       $this->_cp['exclude_list'],
        'limit_per_activity' => $this->_cp['limit_per_activity']
      );
    $this->_records = $Obj->get_n_per_activity($args);
  }

  protected function _setup_load_tabs(){
    $this->_activities =    array();
    $this->_tabs =          array();
    if (!$this->_records){
      return;
    }
    foreach ($this->_records as $record){
      $activity = $record['activity'];
      if (!in_array($activity,$this->_activities)) {
        $this->_activities[] = $activity;
        $this->_tabs[] = array(
          'ID' => $activity,
          'label'=>$this->_cp['label_'.$activity]
        );
      }
    }
    $temp = get_var('selected_section');
    $this->_selected_section = (in_array($temp,$this->_activities) ? $temp : $this->_activities[0]);
  }

  public function get_version(){
    return VERSION_COMPONENT_ACTIVITY_TABBER;
  }
}
?>