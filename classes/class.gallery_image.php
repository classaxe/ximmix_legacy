<?php
define('VERSION_GALLERY_IMAGE','1.0.23');
/*
Version History:
  1.0.23 (2013-06-07)
    1) Changed the following CPs for listings mode:
         Old: 'grouping_tabs',    'filter_limit',  'filter_order_by', 'paging_controls'
         New: 'results_grouping', 'results_limit', 'results_order',   'results_paging'

  (Older version history in class.gallery_image.txt)
*/
class Gallery_Image extends Posting_Contained {
//  static $_cp_vars_listings =              array();

  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_type('gallery-image');
    $this->_set_assign_type('gallery-image');
    $this->_set_has_publish_date(true);      // Do now allow item to be seen prior to publish date
    $this->_set_object_name('Gallery Image');
    $this->_set_container_object_type('Gallery_Album');
    $this->set_edit_params(
      array(
        'report' =>                 'gallery-images',
        'report_rename' =>          true,
        'report_rename_label' =>    'new title',
        'icon_delete' =>            '[ICON]20 20 5163 Delete this Gallery Image[/ICON]',
        'icon_edit' =>              '[ICON]21 21 4634 Edit this Gallery Image[/ICON]',
        'icon_edit_disabled' =>     '[ICON]21 21 4655 (Edit this Gallery Image)[/ICON]',
        'icon_edit_popup' =>        '[ICON]19 19 4676 Edit this Gallery Image in a popup window[/ICON]'
      )
    );
    $this->_cp_vars_detail = array(
      'block_layout' =>             array('match' => '',                'default' => 'Gallery Image',   'hint' => 'Name of Block Layout to use'),
      'extra_fields_list' =>        array('match' => '',                'default' => '',                'hint' => 'CSV list format: field|label|group,field|label|group...'),
      'item_footer_component' =>    array('match' => '',                'default' => '',                'hint' => 'Name of component rendered below displayed Job Posting'),
      'links_open_image' =>         array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'products' =>                 array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'products_msg_howto' =>       array('match' => '',                'default' => '<p>Click the shopping-cart icon to select payment option, then proceed to the Checkout to place your order.<br />Use the +/- buttons to adjust quantities.</p>',  'hint' => 'Describes how to place items in cart and checkout'),
      'products_msg_none' =>        array('match' => '',                'default' => '',                'hint' => 'Message shown if there ARE no products'),
      'products_msg_signin' =>      array('match' => '',                'default' => '<p>There are products that cannot be seen unless you sign in.<br />If you have an account you may sign in to access to member-rate pricing.</p>',                 'hint' => 'Message shown if there are products that public member cannot see'),
      'products_signin' =>          array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1 - whether or not to show a signin dialog if products available for members but user has not signed in'),
      'products_signin_button' =>   array('match' => '',                'default' => 'Sign In ',        'hint' => "Label to show for button in 'Sign In' dialog"),
      'products_signin_pwd' =>      array('match' => '',                'default' => 'Password ',       'hint' => "Label to show for 'Password' in 'Sign In' dialog"),
      'products_signin_title' =>    array('match' => '',                'default' => 'Sign In',         'hint' => "Title to show on 'Sign In' dialog"),
      'products_signin_user' =>     array('match' => '',                'default' => 'Username ',       'hint' => "Label to show for 'Username' in 'Sign In' dialog"),
      'show_watermark' =>           array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'thumbnail_height' =>         array('match' => 'range|1,n',       'default' => '300',             'hint' => '|1..n or blank - height in px to resize'),
      'thumbnail_image' =>          array('match' => 'enum|s,m,l',      'default' => 's',               'hint' => 's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'),
      'thumbnail_link' =>           array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'thumbnail_show' =>           array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'thumbnail_width' =>          array('match' => 'range|1,n',       'default' => '400',             'hint' => '|1..n or blank - width in px to resize'),
      'title_linked' =>             array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'title_show' =>               array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1')
    );
    $this->_cp_vars_listings = array(
      'background' =>               array('match' => 'hex3|',           'default' => '',                'hint' => 'Hex code for background colour to use'),
      'block_layout' =>             array('match' => '',                'default' => 'Gallery Image',   'hint' => 'Name of Block Layout to use'),
      'block_layout_for_associated' => array('match' => '',             'default' => 'Gallery Image for Associated', 'hint' => 'Name of Block Layout to use when this item is seen as associated with something else'),
      'box' =>                      array('match' => 'enum|0,1,2',      'default' => '0',               'hint' => '0|1|2'),
      'box_footer' =>               array('match' => '',                'default' => '',                'hint' => 'Text below displayed Job Postings'),
      'box_header' =>               array('match' => '',                'default' => '',                'hint' => 'Text above displayed Job Postings'),
      'box_rss_link' =>             array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'box_title' =>                array('match' => '',                'default' => 'Gallery Images',  'hint' => 'text'),
      'box_title_link' =>           array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'box_title_link_page' =>      array('match' => '',                'default' => 'gallery-images',  'hint' => 'page'),
      'box_width' =>                array('match' => 'range|0,n',       'default' => '0',               'hint' => '0..x'),
      'category_show' =>            array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'comments_link_show' =>       array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'content_char_limit' =>       array('match' => 'range|0,n',       'default' => '0',               'hint' => '0..n'),
      'content_plaintext' =>        array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'content_show' =>             array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'date_show' =>                array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'extra_fields_list' =>        array('match' => '',                'default' => '',                'hint' => 'CSV list format: field|label|group,field|label|group...'),
      'filter_category_list' =>     array('match' => '',                'default' => '*',               'hint' => 'Optionally limits items to those in this gallery album - / means none'),
      'filter_category_master' =>   array('match' => '',                'default' => '',                'hint' => 'Optionally INSIST on this category'),
      'filter_container_path' =>    array('match' => '',                'default' => '',                'hint' => 'Optionally limits items to those contained in this folder'),
      'filter_container_subs' =>    array('match' => 'enum|0,1',        'default' => '0',               'hint' => 'If filtering by container folder, enable this setting to include subfolders'),
      'filter_important' =>         array('match' => 'enum|,0,1',       'default' => '',                'hint' => 'Blank to ignore, 0 for not important, 1 for important'),
      'filter_memberID' =>          array('match' => 'range|0,n',       'default' => '',                'hint' => 'ID of Community Member to restrict by that criteria'),
      'filter_personID' =>          array('match' => 'range|0,n',       'default' => '',                'hint' => 'ID of Person to restrict by that criteria'),
      'item_footer_component' =>    array('match' => '',                'default' => '',                'hint' => 'Name of component rendered below each displayed Job Posting'),
      'more_link_text' =>           array('match' => '',                'default' => '(More)',          'hint' => 'text for \'Read More\' link'),
      'results_grouping' =>         array('match' => 'enum|,month,year','default' => '',                'hint' => '|month|year'),
      'results_limit' =>            array('match' => 'range|0,n',       'default' => '3',               'hint' => '0..n'),
      'results_order' =>            array('match' => 'enum|date,name,title', 'default' => 'date',       'hint' => 'date|name|title'),
      'results_paging' =>           array('match' => 'enum|0,1,2',      'default' => '0',               'hint' => '0|1|2 - 1 for buttons, 2 for links'),
      'thumbnail_at_top' =>         array('match' => 'enum|0,1',        'default' => '0',               'hint' => '0|1'),
      'thumbnail_height' =>         array('match' => 'range|1,n',       'default' => '150',             'hint' => '|1..n or blank - height in px to resize'),
      'thumbnail_image' =>          array('match' => 'enum|s,m,l',      'default' => 's',               'hint' => 's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'),
      'thumbnail_link' =>           array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'thumbnail_show' =>           array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'thumbnail_width' =>          array('match' => 'range|1,n',       'default' => '200',             'hint' => '|1..n or blank - width in px to resize'),
      'title_linked' =>             array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1'),
      'title_show' =>               array('match' => 'enum|0,1',        'default' => '1',               'hint' => '0|1')
    );
  }

  protected function BL_prev_next_buttons(){
    return "";
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function set_path(){
    $parent_path =  '';
    $this->load();
    $name =         $this->record['name'];
    $parentID =     $this->record['parentID'];
    if ($parentID){
      $Obj = new Gallery_Album($parentID);
      $parent_path.= trim($Obj->get_field('path'),'/').'/';
    }
    $path =             "//".$parent_path.$name;
    $this->set_field('path',$path);
  }

  public function get_version(){
    return VERSION_GALLERY_IMAGE;
  }
}
?>