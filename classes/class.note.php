<?php
define('VERSION_NOTE','1.0.3');
/*
Version History:
  1.0.3 (2013-06-07)
    1) Changed the following CPs for listings mode:
         Old: 'grouping_tabs',    'filter_limit',  'filter_order_by', 'paging_controls'
         New: 'results_grouping', 'results_limit', 'results_order',   'results_paging'

  (Older version history in class.note.txt)
*/
class Note extends Posting {
  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_type('note');
    $this->_set_assign_type('note');
    $this->_set_object_name('Note');
    $this->_set_has_publish_date(false);
    $this->_set_has_groups(false);
    $this->_set_has_keywords(false);
    $this->_set_message_associated('');
    $this->set_edit_params(
      array(
        'report' =>                 'notes',
        'report_rename' =>          true,
        'report_rename_label' =>    'new title',
        'icon_delete' =>            '[ICON]13 13 4421 Delete this Note[/ICON]',
        'icon_edit' =>              '[ICON]15 15 13 Edit this Note[/ICON]',
        'icon_edit_disabled' =>     '[ICON]15 15 2380 (Edit this Note)[/ICON]',
        'icon_edit_popup' =>        '[ICON]18 18 2462 Edit this Note in a popup window[/ICON]'
      )
    );
    $this->_cp_vars_detail = array(
      'author_show' =>              array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'block_layout' =>             array('match' => '',                'default'=>'Note',          'hint'=>'Name of Block Layout to use'),
      'category_show' =>            array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'comments_show' =>            array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'comments_link_show' =>       array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'date_show' =>                array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'extra_fields_list' =>        array('match' => '',                'default'=>'',              'hint'=>'CSV list format: field|label|group,field|label|group...'),
      'item_footer_component' =>    array('match' => '',                'default'=>'',              'hint'=>'Name of component rendered below displayed Article'),
      'keywords_show' =>            array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'links_open_image' =>         array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'related_show' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'subscribe_show' =>           array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1 - Whether or not to allow subscriptions'),
      'subtitle_show' =>            array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'thumbnail_at_top' =>         array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'thumbnail_height' =>         array('match' => 'range|1,n',       'default' =>'',             'hint'=>'|1..n or blank - height in px to resize'),
      'thumbnail_link' =>           array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'thumbnail_width' =>          array('match' => 'range|1,n',       'default' =>'',             'hint'=>'|1..n or blank - width in px to resize'),
      'title_linked' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'title_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1')
    );
    $this->_cp_vars_listings = array(
      'author_show' =>              array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'background' =>               array('match' => 'hex3|',           'default' =>'',             'hint'=>'Hex code for background colour to use'),
      'block_layout' =>             array('match' => '',                'default' =>'Note',         'hint'=>'Name of Block Layout to use'),
      'box' =>                      array('match' => 'enum|0,1,2',      'default' =>'0',            'hint'=>'0|1|2'),
      'box_footer' =>               array('match' => '',                'default' =>'',             'hint'=>'Text below displayed Articles'),
      'box_header' =>               array('match' => '',                'default' =>'',             'hint'=>'Text above displayed Articles'),
      'box_rss_link' =>             array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'box_title' =>                array('match' => '',                'default' =>'Notes',        'hint'=>'text'),
      'box_title_link' =>           array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'box_title_link_page' =>      array('match' => '',                'default' =>'all-notes',    'hint'=>'page'),
      'box_width' =>                array('match' => 'range|0,n',       'default' =>'0',            'hint'=>'0..x'),
      'category_show' =>            array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'comments_link_show' =>       array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'content_char_limit' =>       array('match' => 'range|0,n',       'default' =>'0',            'hint'=>'0..n'),
      'content_plaintext' =>        array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'content_show' =>             array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'content_use_summary' =>      array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'date_show' =>                array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'extra_fields_list' =>        array('match' => '',                'default' =>'',             'hint'=>'CSV list format: field|label|group,field|label|group...'),
      'filter_category_list' =>     array('match' => '',                'default' =>'*',            'hint'=>'*|CSV value list'),
      'filter_category_master' =>   array('match' => '',                'default' =>'',             'hint'=>'Optionally INSIST on this category'),
      'filter_memberID' =>          array('match' => 'range|0,n',       'default' =>'',             'hint'=>'ID of Community Member to restrict by that criteria'),
      'filter_personID' =>          array('match' => 'range|0,n',       'default' =>'',             'hint'=>'ID of Person to restrict by that criteria'),
      'item_footer_component' =>    array('match' => '',                'default' =>'',             'hint'=>'Name of component rendered below each displayed Article'),
      'keywords_show' =>            array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'links_point_to_URL' =>       array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1 - If there is a URL, both title and thumbnails links go to it'),
      'links_switch_video' =>       array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1 - If there is a video, both title and thumbnails links select it'),
      'more_link_text' =>           array('match' => '',                'default' =>'(More)',       'hint'=>'text for \'Read More\' link'),
      'related_show' =>             array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'results_grouping' =>         array('match' => 'enum|,month,year','default'=>'',              'hint'=>'|month|year'),
      'results_limit' =>            array('match' => 'range|0,n',       'default' =>'3',            'hint'=>'0..n'),
      'results_order' =>            array('match' => 'enum|date,name,title', 'default' =>'date',    'hint'=>'date|name|title'),
      'results_paging' =>           array('match' => 'enum|0,1,2',      'default' =>'0',            'hint'=>'0|1|2 - 1 for buttons, 2 for links'),
      'subscribe_show' =>           array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1 - Whether or not to allow subscriptions'),
      'subtitle_show' =>            array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'thumbnail_at_top' =>         array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
      'thumbnail_height' =>         array('match' => 'range|1,n',       'default' =>'',             'hint'=>'|1..n or blank - height in px to resize'),
      'thumbnail_link' =>           array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'thumbnail_width' =>          array('match' => 'range|1,n',       'default' =>'',             'hint'=>'|1..n or blank - width in px to resize'),
      'title_linked' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
      'title_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1')
    );
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_NOTE;
  }
}
?>