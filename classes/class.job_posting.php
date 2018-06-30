<?php
define('VERSION_JOB_POSTING','1.0.20');
/*
Version History:
  1.0.20 (2013-06-07)
    1) Changed the following CPs for listings mode:
         Old: 'grouping_tabs',    'filter_limit',  'filter_order_by', 'paging_controls'
         New: 'results_grouping', 'results_limit', 'results_order',   'results_paging'

  (Older version history in class.job_posting.txt)
*/
class Job_Posting extends Posting {
//  static $_cp_vars_listings =              array();

  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_type('job-posting');
    $this->_set_assign_type('job-posting');
    $this->_set_has_publish_date(true);      // Do now allow item to be seen prior to publish date
    $this->_set_object_name('Job Posting');
    $this->set_edit_params(
      array(
        'command_for_delete' =>     'job_delete',
        'report' =>                 'job-postings',
        'report_rename' =>          true,
        'report_rename_label' =>    'new title',
        'icon_delete' =>            '[ICON]13 13 4460 Delete this Job Posting[/ICON]',
        'icon_edit' =>              '[ICON]15 15 69 Edit this Job Posting[/ICON]',
        'icon_edit_disabled' =>     '[ICON]15 15 2410 (Edit this Job Posting)[/ICON]',
        'icon_edit_popup' =>        '[ICON]18 18 2498 Edit this Job Posting in a popup window[/ICON]'
      )
    );
    $this->_cp_vars_detail = array(
      'block_layout' =>             array('match' => '',                'default' => 'Job Posting',     'hint' => 'Name of Block Layout to use'),
      'comments_show' =>            array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'comments_link_show' =>       array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'extra_fields_list' =>        array('match' => '',                'default' => '',                'hint' => 'CSV list format: field|label|group,field|label|group...'),
      'item_footer_component' =>    array('match' => '',                'default' => '',                'hint' => 'Name of component rendered below displayed Job Posting'),
      'subscribe_show' =>           array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1 - Whether or not to allow subscriptions'),
      'title_linked' =>             array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'title_show' =>               array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1')
    );
    $this->_cp_vars_listings = array(
      'background' =>               array('match' => 'hex3|',           'default' => '',                'hint' => 'Hex code for background colour to use'),
      'block_layout' =>             array('match' => '',                'default' => 'Job Posting',     'hint' => 'Name of Block Layout to use'),
      'box' =>                      array('match' => 'enum|0,1,2',      'default' => '0',               'hint' => '0|1|2'),
      'box_footer' =>               array('match' => '',                'default' => '',                'hint' => 'Text below displayed Job Postings'),
      'box_header' =>               array('match' => '',                'default' => '',                'hint' => 'Text above displayed Job Postings'),
      'box_rss_link' =>             array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'box_title' =>                array('match' => '',                'default' => 'Jobs',            'hint' => 'text'),
      'box_title_link' =>           array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'box_title_link_page' =>      array('match' => '',                'default' => 'all_jobs',        'hint' => 'page'),
      'box_width' =>                array('match' => 'range|0,n',       'default' => '0',               'hint' => '0..x'),
      'comments_link_show' =>       array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'content_char_limit' =>       array('match' => 'range|0,n',       'default' => '0',               'hint' => '0..n'),
      'content_plaintext' =>        array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'content_show' =>             array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'extra_fields_list' =>        array('match' => '',                'default' => '',                'hint' => 'CSV list format: field|label|group,field|label|group...'),
      'filter_category_list' =>     array('match' => '',                'default' => '*',               'hint' => '*|CSV value list'),
      'filter_important' =>         array('match' => 'enum|,0,1',       'default' => '',                'hint' => 'Blank to ignore, 0 for not important, 1 for important'),
      'filter_memberID' =>          array('match' => 'range|0,n',       'default' => '',                'hint' => 'ID of Community Member to restrict by that criteria'),
      'filter_personID' =>          array('match' => 'range|0,n',       'default' => '',                'hint' => 'ID of Person to restrict by that criteria'),
      'item_footer_component' =>    array('match' => '',                'default' => '',                'hint' => 'Name of component rendered below each displayed Job Posting'),
      'more_link_text' =>           array('match' => '',                'default' => '(More)',          'hint' => 'text for \'Read More\' link'),
      'results_grouping' =>         array('match' => 'enum|,month,year','default' => '',                'hint' => '|month|year'),
      'results_limit' =>            array('match' => 'range|0,n',       'default' => '3',               'hint' => '0..n'),
      'results_paging' =>           array('match' => 'enum|0,1,2',      'default' => '0',               'hint' => '0|1|2 - 1 for buttons, 2 for links'),
      'subscribe_show' =>           array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1 - Whether or not to allow subscriptions'),
      'title_linked' =>             array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'title_show' =>               array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1')
    );
  }
  public function get_version(){
    return VERSION_JOB_POSTING;
  }
}
?>