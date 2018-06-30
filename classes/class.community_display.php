<?php
define('COMMUNITY_DISPLAY_VERSION','1.0.37');
/* Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)

/*
Version History:
  1.0.37 (2014-04-29)
    1) Context menus for members now passes primary_communityID to allow this to be
       set automatically when new items are added
    2) Community_Display::_draw_meetings() now includes primary_communityID field
    3) All listing panels having 'add' icons for admins now set communityID when adding

  (Older version history in class.community_display.txt)
*/

class Community_Display extends Community{
  protected $_events =                      array();
  protected $_events_special =              array();
  protected $_member_types =                array();
  protected $_Obj_Map =                     false;
  protected $_sponsors_national_records =   array();
  protected $_sponsors_local_records =      array();
  protected $_sponsors_national_container = '';

  public function __construct(){
    global $page_vars;
    parent::__construct();
    $this->_cp_vars = array(
      'community_name' =>                   array('default'=>'',                        'hint'=>'Name (not title!) of community to view'),
      'community_title' =>                  array('default'=>'',                        'hint'=>'Title of community as shown in remote JS panels'),
      'community_URL' =>                    array('default'=>'',                        'hint'=>'URL of community as shown in remote JS panels'),
      'category_events_special' =>          array('default'=>'easter',                  'hint'=>'CSV list of categories to use when displaying special events'),
      'detail_audioplayer_width' =>         array('default'=>400,                       'hint'=>'0..x'),
      'detail_content_char_limit' =>        array('default'=>500,                       'hint'=>'0..x'),
      'detail_content_plaintext' =>         array('default'=>1,                         'hint'=>'0|1'),
      'detail_results_limit' =>             array('default'=>10,                        'hint'=>'1..x'),
      'detail_results_paging' =>            array('default'=>2,                         'hint'=>'0|1|2'),
      'detail_show_author' =>               array('default'=>1,                         'hint'=>'0|1'),
      'detail_show_category' =>             array('default'=>1,                         'hint'=>'0|1'),
      'detail_show_content' =>              array('default'=>1,                         'hint'=>'0|1'),
      'detail_show_thumbnails' =>           array('default'=>1,                         'hint'=>'0|1'),
      'detail_thumbnail_height' =>          array('default'=>150,                       'hint'=>'1..x'),
      'detail_thumbnail_width' =>           array('default'=>200,                       'hint'=>'1..x'),
      'dropbox_check_frequency' =>          array('match'=>'range:0,n',  'default'=>60, 'hint'=>'Check dropbox for new content every (n) seconds'),
      'dropbox_notify_emails' =>            array('default'=>'',                        'hint'=>'CSV list of email addresses to notify whenever new Dropbox content is added'),
      'enforce_sharing' =>                  array('default'=>0,                         'hint'=>'0|1'),
      'footer_about' =>                     array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_articles' =>                  array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_calendar' =>                  array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_events' =>                    array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_events_special' =>            array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_gallery' =>                   array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_map' =>                       array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_meetings' =>                  array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_members' =>                   array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_news' =>                      array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_podcasts' =>                  array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_sponsors' =>                  array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_stats' =>                     array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'footer_welcome' =>                   array('default'=>'',                        'hint'=>'Footer to place at bottom of section'),
      'header_about' =>                     array('default'=>'',                        'hint'=>'Header to place at top of section'),
      'header_articles' =>                  array('default'=>'Featured articles from all members.',                'hint'=>'Header to place at top of section'),
      'header_calendar' =>                  array('default'=>'Monthly Calendar for all members.',            'hint'=>'Header to place at top of section'),
      'header_events' =>                    array('default'=>'Featured events from all members. See <a href="#calendar">Calendar</a> for monthly calendar view.',                'hint'=>'Header to place at top of section'),
      'header_events_special' =>            array('default'=>'Upcoming Easter services from all members. See <a href="#calendar">Calendar</a> for monthly calendar view.',              'hint'=>'Header to place at top of section'),
      'header_gallery' =>                   array('default'=>'View photos associated with this community.',                'hint'=>'Header to place at top of section'),
      'header_map' =>                       array('default'=>'Click on any name to find on map.',                'hint'=>'Header to place at top of section'),
      'header_meetings' =>                  array('default'=>'Showing regular public meetings. See <a href=\'#events\'>Events</a> for other events.',                'hint'=>'Header to place at top of section'),
      'header_members' =>                   array('default'=>'Click on any photo for details.',                'hint'=>'Header to place at top of section'),
      'header_news' =>                      array('default'=>'Featured news from all members.',                'hint'=>'Header to place at top of section'),
      'header_podcasts' =>                  array('default'=>'Latest Sermon from each member - <a href=\''.BASE_PATH.trim($page_vars['path'],'/').'/sermons\'><b>click here</b></a> to access all sermons for this community.',         'hint'=>'Header to place at top of section'),
      'header_stats' =>                     array('default'=>'',                        'hint'=>'Header to place at top of section'),
      'header_sponsors_local' =>            array('default'=>'',                        'hint'=>'Header to place at top of section'),
      'header_sponsors_national' =>         array('default'=>'We gratefully acknowledge our National Partners for their faithful support - without their help this website would not even have been possible.<br />Please <b>click their logos</b> to learn more about each of them, and please mention us whenever you respond to them.',                        'hint'=>'Header to place at top of section'),
      'header_welcome' =>                   array('default'=>'',                        'hint'=>'Header to place at top of section'),
      'label_about' =>                      array('default'=>'About This Website',      'hint'=>'Text for Label'),
      'label_articles' =>                   array('default'=>'Featured Articles',       'hint'=>'Text for Label'),
      'label_calendar' =>                   array('default'=>'Monthly Calendar',        'hint'=>'Text for Label'),
      'label_events' =>                     array('default'=>'Featured Events',         'hint'=>'Text for Label'),
      'label_events_special' =>             array('default'=>'Easter Services',         'hint'=>'Text for Label'),
      'label_gallery' =>                    array('default'=>'Gallery',                 'hint'=>'Text for Label'),
      'label_members' =>                    array('default'=>'Members',                 'hint'=>'Text for Label'),
      'label_map' =>                        array('default'=>'Members Map',             'hint'=>'Text for Label'),
      'label_meetings' =>                   array('default'=>'Regular Meeting Times',   'hint'=>'Text for Label'),
      'label_news' =>                       array('default'=>'Featured News',           'hint'=>'Text for Label'),
      'label_podcasts' =>                   array('default'=>'Latest Audio',            'hint'=>'Text for Label'),
      'label_sponsors_local' =>             array('default'=>'Local Community Partners','hint'=>'Text for Label'),
      'label_sponsors_national' =>          array('default'=>'National Site Partners',  'hint'=>'Text for Label'),
      'label_stats' =>                      array('default'=>'Visitor Statistics',      'hint'=>'Text for Label'),
      'label_welcome' =>                    array('default'=>'About this Community',    'hint'=>'Text for Label'),
      'listing_audioplayer_width' =>        array('default'=>400,                       'hint'=>'0..x'),
      'listing_content_char_limit' =>       array('default'=>500,                       'hint'=>'0..x'),
      'listing_content_plaintext' =>        array('default'=>1,                         'hint'=>'0|1'),
      'listing_results_limit' =>            array('default'=>10,                        'hint'=>'1..x'),
      'listing_results_paging' =>           array('default'=>2,                         'hint'=>'0|1|2'),
      'listing_show_author' =>              array('default'=>1,                         'hint'=>'0|1'),
      'listing_show_category' =>            array('default'=>1,                         'hint'=>'0|1'),
      'listing_show_content' =>             array('default'=>1,                         'hint'=>'0|1'),
      'listing_show_thumbnails' =>          array('default'=>1,                         'hint'=>'0|1'),
      'listing_thumbnail_height' =>         array('default'=>150,                       'hint'=>'1..x'),
      'listing_thumbnail_width' =>          array('default'=>200,                       'hint'=>'1..x'),
      'members_spacing' =>                  array('default'=>4,                         'hint'=>'1..x'),
      'members_padding' =>                  array('default'=>3,                         'hint'=>'1..x'),
      'members_header_height' =>            array('default'=>65,                        'hint'=>'1..x'),
      'members_height' =>                   array('default'=>210,                       'hint'=>'1..x'),
      'members_width' =>                    array('default'=>190,                       'hint'=>'1..x'),
      'members_photo_lock_aspect' =>        array('default'=>0,                         'hint'=>'0|1'),
      'members_photo_height' =>             array('default'=>135,                       'hint'=>'1..x'),
      'members_photo_width' =>              array('default'=>180,                       'hint'=>'1..x'),
      'members_show_address' =>             array('default'=>1,                         'hint'=>'0|1'),
      'members_show_map' =>                 array('default'=>1,                         'hint'=>'0|1'),
      'members_show_name' =>                array('default'=>1,                         'hint'=>'0|1'),
      'members_show_phone' =>               array('default'=>1,                         'hint'=>'0|1'),
      'members_show_photo' =>               array('default'=>1,                         'hint'=>'0|1'),
      'members_show_website' =>             array('default'=>1,                         'hint'=>'0|1'),
      'members_text_address_size' =>        array('default'=>8,                         'hint'=>'1..x'),
      'members_text_name_bold' =>           array('default'=>1,                         'hint'=>'0|1'),
      'members_text_name_size' =>           array('default'=>8,                         'hint'=>'1..x'),
      'members_text_phone_size' =>          array('default'=>8,                         'hint'=>'1..x'),
      'map_height' =>                       array('default'=>600,                       'hint'=>'1..x'),
      'map_photo_width' =>                  array('default'=>80,                        'hint'=>'1..x'),
      'profile_photo_height' =>             array('default'=>318,                       'hint'=>'1..x'),
      'profile_photo_width' =>              array('default'=>425,                       'hint'=>'1..x'),
      'profile_map_height' =>               array('default'=>500,                       'hint'=>'1..x'),
      'profile_map_photo_width' =>          array('default'=>80,                        'hint'=>'1..x'),
      'profile_map_zoom' =>                 array('default'=>14,                        'hint'=>'1..19'),
      'profile_page_layout' =>              array('default'=>'',                        'hint'=>'Name of layout to use when rendering member profiles'),
      'show_about' =>                       array('default'=>1,                         'hint'=>'0|1'),
      'show_articles' =>                    array('default'=>1,                         'hint'=>'0|1'),
      'show_calendar' =>                    array('default'=>1,                         'hint'=>'0|1'),
      'show_contact' =>                     array('default'=>1,                         'hint'=>'0|1'),
      'show_events' =>                      array('default'=>1,                         'hint'=>'0|1'),
      'show_events_special' =>              array('default'=>1,                         'hint'=>'0|1'),
      'show_gallery' =>                     array('default'=>0,                         'hint'=>'0|1'),
      'show_members' =>                     array('default'=>1,                         'hint'=>'0|1'),
      'show_map' =>                         array('default'=>1,                         'hint'=>'0|1'),
      'show_meetings' =>                    array('default'=>1,                         'hint'=>'0|1'),
      'show_news' =>                        array('default'=>1,                         'hint'=>'0|1'),
      'show_podcasts' =>                    array('default'=>1,                         'hint'=>'0|1'),
      'show_sponsors' =>                    array('default'=>1,                         'hint'=>'0|1'),
      'show_stats' =>                       array('default'=>1,                         'hint'=>'0|1'),
      'show_welcome' =>                     array('default'=>1,                         'hint'=>'0|1'),
      'tab_about' =>                        array('default'=>'About...',                'hint'=>'Text for Label'),
      'tab_articles' =>                     array('default'=>'Articles',                'hint'=>'Text for Label'),
      'tab_audio' =>                        array('default'=>'Audio',                   'hint'=>'Text for Label'),
      'tab_calendar' =>                     array('default'=>'Calendar',                'hint'=>'Text for Label'),
      'tab_contact' =>                      array('default'=>'Contact',                 'hint'=>'Text for Label'),
      'tab_events' =>                       array('default'=>'Events',                  'hint'=>'Text for Label'),
      'tab_events_special' =>               array('default'=>'Easter',                  'hint'=>'Text for Label'),
      'tab_gallery' =>                      array('default'=>'Gallery',                 'hint'=>'Text for Label'),
      'tab_map' =>                          array('default'=>'Map',                     'hint'=>'Text for Label'),
      'tab_meetings' =>                     array('default'=>'Meetings',                'hint'=>'Text for Label'),
      'tab_members' =>                      array('default'=>'Members',                 'hint'=>'Text for Label'),
      'tab_news' =>                         array('default'=>'News',                    'hint'=>'Text for Label'),
      'tab_profile' =>                      array('default'=>'Profile',                 'hint'=>'Text for Label'),
      'tab_podcasts' =>                     array('default'=>'Sermons',                 'hint'=>'Text for Label'),
      'tab_stats' =>                        array('default'=>'Stats',                   'hint'=>'Text for Label'),
      'tab_welcome' =>                      array('default'=>'Welcome',                 'hint'=>'Text for Label'),
      'template_community_page' =>          array('default'=>'//template-about-community/', 'hint'=>'Web page to use as a template for the \'about\' section for the community'),
      'template_member_page' =>             array('default'=>'//template-about-member/',    'hint'=>'Web page to use as a template for the \'about\' section for members'),
      'width' =>                            array('default'=>1005,                      'hint'=>'Overall width from which tab sizes are calculated'),
    );
  }

  public function draw($instance='',$args=array(), $disable_params=false){
    try{
      $this->_setup($instance,$args,$disable_params);
      if ($this->_path_extension){
        $Obj = new Community_Resource();
        return $Obj->draw($this->_cp,$this->_path_extension,$this->_community_record);
      }
      $this->_setup_listings();
      $this->_draw_css();
      $this->_draw_js();
      $this->_draw_section_tabs();
      $this->_draw_frame_open();
      $this->_draw_section_container_open();
      $this->_draw_welcome();
      $this->_draw_members();
      $this->_draw_map();
      $this->_draw_meetings();
      $this->_draw_articles();
      $this->_draw_events();
      $this->_draw_events_special();
      $this->_draw_calendar();
      $this->_draw_news();
      $this->_draw_podcasts();
      $this->_draw_gallery_embedded();
      $this->_draw_stats();
      $this->_draw_about();
      $this->_draw_section_container_close();
      $this->_draw_frame_shut();
    }
    catch (Exception $e){
      $this->_html.=  "<b>Error:</b><br />\n".$e->getMessage();
    }
    return $this->_html;
  }

  protected function _draw_css(){
    Page::push_content(
      'style_include',
       "<link rel=\"stylesheet\" type=\"text/css\""
      ." href=\"/css/community/".System::get_item_version('css_community')."\" />"
    );
    $css =
       ".community_frame .member_gallery_entry {\n"
      ."  height: ".$this->_cp['members_height']."px; width: ".$this->_cp['members_width']."px; float: left;\n"
      ."  margin: ".$this->_cp['members_spacing']."px ".$this->_cp['members_spacing']."px 0 0; padding: ".$this->_cp['members_padding']."px; text-align: center;\n"
      ."  background-color: #fff; border: 1px solid #888;\n"
      ."}\n"
      .".community_frame .member_gallery_entry_header  { height: ".$this->_cp['members_header_height']."px; }\n"
      .".community_frame .member_gallery_entry_name    { font-size: ".$this->_cp['members_text_name_size']."pt; font-weight: ".($this->_cp['members_text_name_bold'] ? 'bold' : 'normal')."; }\n"
      .".community_frame .member_gallery_entry_address { font-size: ".$this->_cp['members_text_address_size']."pt; }\n"
      .".community_frame .member_gallery_entry_phone   { font-size: ".$this->_cp['members_text_phone_size']."pt; }\n"
      ."#section_special_heading { background-color: #ffd0d0; }\n"
      ."#section_special_heading.tab_selected { background-color: #ffa0a0; }\n"
      .".community_frame .member_gallery_entry_update_status .grey,\n"
      .".community_frame .member_gallery_entry_update_status .red,\n"
      .".community_frame .member_gallery_entry_update_status .green{\n"
      ."  background: url(".BASE_PATH."img/sysimg/leds.png);\n"
      ."}\n";
    Page::push_content('style',$css);
  }

  protected function _draw_js(){
    global $page_vars;
    if ($this->_cp['members_show_map']){
      Page::push_content(
        "javascript",
         "function map_highlight(id){\n"
        ."  show_section_tab(spans_".$this->_safe_ID.",'map');\n"
        ."  return ecc_map.point.i(window['_google_map_community_members_marker_'+id]);\n"
        ."}\n"
      );
    }
    if ($this->_current_user_rights['canEdit']) {
      Page::push_content(
        "javascript_onload",
         "community_dropbox_check('".BASE_PATH.$page_vars['relative_URL']."');"
      );
    }
    $selected_section = (get_var('selected_section') ? get_var('selected_section') : $this->_section_tabs_arr[0]['ID']);
    Page::push_content(
      'javascript_onload',
       "  show_section_onhashchange_setup(spans_".$this->_safe_ID.");\n"
      ."  window.setTimeout(\"var tab='".$selected_section."';"
      .(get_var('anchor') ?
         ''
       :
         'if(document.location.hash){tab=document.location.hash.substr(1);}'
       )
      ."show_section_tab(spans_".$this->_safe_ID.",tab);\",500);"
    );
  }

  protected function _draw_about(){
    global $page_vars;
    if ($this->_cp['show_about']!=1){
      return;
    }
    $Obj_Page =         new Page;
    $this->_pageID =    $Obj_Page->get_ID_by_path('//'.trim($this->_cp['template_community_page'],'/').'/');
    $Obj_Page->_set_ID($this->_pageID);
    $content =          $Obj_Page->get_field('content');
    $this->_html.=
       HTML::draw_section_tab_div('about',$this->_selected_section)
      ."<h2>"
      .($this->_current_user_rights['canEdit'] && $this->_pageID ?
           "<a"
          ." href=\"".BASE_PATH.'details/'.$this->_edit_form['pages'].'/'.$this->_pageID.'"'
          ." onclick=\"details('".$this->_edit_form['pages']."','".$this->_pageID."','".$this->_popup['pages']['h']."','".$this->_popup['pages']['w']."');return false;\""
          .">".$this->_cp['label_about']."</a>"
        :
           $this->_cp['label_about']
       )
      ."</h2>\n"
      .($this->_cp['header_about'] ? "<div class='section_header'>".$this->_cp['header_about']."</div>\n" : "")
      .($this->_pageID ?
          $this->_draw_about_items($content)
       :
          "<b>Error </b>: The 'About' section template page //".trim($this->_cp['template_community_page'],'/')."/ wasn't found."
       )
      ."<div class='clear'>&nbsp;</div>"
      ."<div class='section_footer'>".$this->_cp['footer_about']."</div>"
      ."</div>";
  }

  protected function _draw_about_items($content){
    $replace = array(
      '[[COMMUNITY_NAME]]' =>       $this->_community_record['name'],
      '[[COMMUNITY_TITLE]]' =>      $this->_community_record['title'],
      '[[COMMUNITY_URL]]' =>        $this->_community_record['URL_external'],
      '[[SPONSORS_LOCAL]]' =>       $this->_draw_sponsors_local(),
      '[[SPONSORS_NATIONAL]]' =>    $this->_draw_sponsors_national()
    );
    return strtr($content,$replace);
  }

  protected function _draw_articles(){
    if ($this->_cp['show_articles']!=1){
      return;
    }
    $Obj = new Community_Article;
    $Obj->community_record =    $this->_community_record;
    $Obj->communityID =         $this->_community_record['ID'];
    $args = array(
      'author_show' =>          $this->_cp['listing_show_author'],
      'category_show' =>        $this->_cp['listing_show_category'],
      'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
      'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
      'content_show' =>         $this->_cp['listing_show_content'],
      'results_limit' =>        $this->_cp['listing_results_limit'],
      'results_paging' =>       $this->_cp['listing_results_paging'],
      'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
      'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
      'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
    );
    $this->_html.=
       HTML::draw_section_tab_div('articles',$this->_selected_section)
      .$this->_draw_web_share('articles','articles')
      ."<h2>".$this->_cp['label_articles']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_articles']."</div>"
      .$Obj->draw_listings('community_articles',$args,false)
      ."<div class='section_footer'>".$this->_cp['footer_articles']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _draw_context_menu_member($record){
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
//    y($record);die;
    return
       " onmouseover=\""
      ."if(!CM_visible('CM_community_member')) {"
      ."this.style.backgroundColor='"
      .($record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
      ."';"
      ."_CM.type='community_member';"
      ."_CM.ID='".$record['ID']."';"
      ."_CM.communityID='".$record['primary_communityID']."';"
      ."_CM.full_member=".($record['full_member']=='1' ? '1' : '0').";"
      ."_CM.ministerial_member=".($record['primary_ministerialID'] ? '1' : '0').";"
      ."_CM.map_location=".($record['service_map_loc'] ? '1' : '0').";"
      ."_CM_text[0]='&quot;".str_replace(array("'","\""),'',htmlentities($record['title']))."&quot;';"
      ."_CM.path='".$record['member_URL']."';"
      ."}\""
      ." onmouseout=\"this.style.backgroundColor='';_CM.type=''\"";
  }

  protected function _draw_context_menu_sponsor($record){
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
    return
       " onmouseover=\""
      ."if(!CM_visible('CM_community_sponsor')) {"
      ."this.style.backgroundColor='"
      .($record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
      ."';"
      ."_CM.type='community_sponsor';"
      ."_CM.enabled=".($record['enabled'] ? 1 : 0).";"
      ."_CM.ID='".$record['ID']."';"
      ."_CM_text[0]='&quot;".str_replace(array("'","\""),'',htmlentities($record['title']))."&quot;';"
      ."}\""
      ." onmouseout=\"this.style.backgroundColor='';_CM.type=''\"";
  }

  protected function _draw_disclaimer(){
    return
       "<div class='community_help' style='margin-top:2em;'>"
      ."<div>\n"
      ."<b>Disclaimer:</b> &nbsp; The values or activities represented here do not necessarily reflect those of all other members, or of <a href='http://www.ecclesiact.com' rel='external' style='color:#0000ff'><b>Ecclesiact</b></a> and its ministry partners.</div>\n"
      ."</div>\n";
  }

  protected function _draw_calendar(){
    if ($this->_cp['show_calendar']!=1){
      return;
    }
    $Obj_Community_Component_Calendar_Large = new Community_Component_Calendar_Large;
    $Obj_Community_Component_Calendar_Large->community_record = $this->_community_record;
    $this->_html.=
       HTML::draw_section_tab_div('calendar',$this->_selected_section)
      .$this->_draw_web_share('events','calendar')
      ."<h2>".$this->_cp['label_calendar']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_calendar']."</div>"
      .$Obj_Community_Component_Calendar_Large->draw()
      ."<div class='section_footer'>".$this->_cp['footer_calendar']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _draw_events(){
    if ($this->_cp['show_events']!=1){
      return;
    }
    $Obj = new Community_Event;
    $Obj->community_record =    $this->_community_record;
    $Obj->communityID =         $this->_community_record['ID'];
    $args = array(
      'author_show' =>          $this->_cp['listing_show_author'],
      'category_show' =>        $this->_cp['listing_show_category'],
      'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
      'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
      'content_show' =>         $this->_cp['listing_show_content'],
      'filter_what' =>          'future',
      'results_limit' =>        $this->_cp['listing_results_limit'],
      'results_paging' =>       $this->_cp['listing_results_paging'],
      'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
      'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
      'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
    );
    $this->_html.=
       HTML::draw_section_tab_div('events',$this->_selected_section)
      .$this->_draw_web_share('events','events')
      ."<h2>".$this->_cp['label_events']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_events']."</div>"
      .$Obj->draw_listings('community_events',$args,false)
      ."<div class='section_footer'>".$this->_cp['footer_events']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _draw_events_special(){
    if ($this->_cp['show_events_special']!=1){
      return;
    }
    if (!$this->_events_special){
      return;
    }
    $Obj = new Community_Event;
    $Obj->community_record =    $this->_community_record;
    $Obj->communityID =         $this->_community_record['ID'];
    $args = array(
      'author_show' =>          $this->_cp['listing_show_author'],
      'category_show' =>        $this->_cp['listing_show_category'],
      'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
      'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
      'content_show' =>         $this->_cp['listing_show_content'],
      'filter_category_list' => $this->_cp['category_events_special'],
      'filter_what' =>          'future',
      'results_limit' =>        $this->_cp['listing_results_limit'],
      'results_paging' =>       $this->_cp['listing_results_paging'],
      'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
      'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
      'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
    );
    $this->_html.=
       HTML::draw_section_tab_div('special',$this->_selected_section)
      ."<h2>".$this->_cp['label_events_special']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_events_special']."</div>"
      .$Obj->draw_listings('community_events',$args,false)
      ."<div class='section_footer'>".$this->_cp['footer_events_special']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _draw_frame_open(){
    $this->_html.= "<div class='community_frame'>";
  }

  protected function _draw_frame_shut(){
    $this->_html.= "</div>";
  }

  protected function _draw_gallery_embedded(){
    global $page_vars;
    if ($this->_cp['show_gallery']!=1){
      return;
    }
    $this->_html.=
       HTML::draw_section_tab_div('gallery',$this->_selected_section)
      ."<h2>".$this->_cp['label_gallery']."</h2>\n"
      ."<div class='section_header'>".$this->_cp['header_gallery']."</div>\n"
      ."<a href=\"".BASE_PATH.trim($page_vars['path'],'/')."/gallery\">Click here</a> to view the gallery."
      ."<div class='clear'>&nbsp;</div>"
      ."<div class='section_footer'>".$this->_cp['footer_gallery']."</div>"
      ."</div>";
  }

  protected function _draw_map(){
    if ($this->_cp['show_map']!=1){
      return;
    }
    $targetID =     get_var('targetID');
    $targetValue =  get_var('targetValue');
    $submode =      get_var('submode');
    if ($this->_current_user_rights['canEdit']) {
      switch ($submode) {
        case "google_map_community_member_map_save":
        case "google_map_community_members_map_save":
          if ($targetValue=='') {
            print "<b>[ Map marker: <span style='color:#ff0000'>Error saving</span> ]</b>";
            die();
          }
          else {
            $coords_arr = explode(",",$targetValue);
            $lat = $coords_arr[0];
            $lon = $coords_arr[1];
            $data =
              array(
                'service_map_lat'=>$lat,
                'service_map_lon'=>$lon,
                'service_map_loc'=>$targetValue
              );
            $Obj = new Community_Member($targetID);
            $Obj->update($data,false);
            print "<b>[ Map marker: <span style='color:#008000'>Location Saved</span> ]</b>";
            die();
          }
        break;
      }
    }
    $this->_Obj_Map =      new Google_Map('community_members',SYS_ID);
    $this->_Obj_Map->add_icon("/UserFiles/Image/map_icons/church/","church");
    $this->_Obj_Map->add_icon("/UserFiles/Image/map_icons/organisation/","organisation");
    $this->_Obj_Map->add_icon("/UserFiles/Image/map_icons/ministerium/","ministerium");
    if ($range = Google_Map::get_bounds($this->_records,'service_')){
      $this->_Obj_Map->map_zoom_to_fit($range);
    }
    else {
      $this->_Obj_Map->map_centre($this->_records[0]['service_map_lat'],$this->_records[0]['service_map_lon'],14);
    }
    $this->_draw_map_points_all();
    $args =     array(
      'map_width'=>($this->_cp['width']-360),
      'map_height'=>$this->_cp['map_height']
    );
    $this->_html.=
       HTML::draw_section_tab_div('map',$this->_selected_section)
      ."<div id='community_map_map_frame'>\n"
      .$this->_Obj_Map->draw($args)
      ."</div>\n"
      ."<h2>".str_replace('<br />',' ',$this->_cp['label_map'])."</h2>"
      ."<div class='section_header'>".$this->_cp['header_map']."</div>"
      ."<div id='community_map_listing_frame' style='height:".($this->_cp['map_height']-50)."px'>\n"
      .$this->_draw_map_listing_names_all()
      ."</div>\n"
      ."<div class='clear'>&nbsp;</div>"
      ."<div class='section_footer'>".$this->_cp['footer_map']."</div>"
      ."</div>\n";
  }

  protected function _draw_map_listing_name($r){
    return
       "<li>"
      ."<a href='#'"
      .$this->_draw_context_menu_member($r)
      ." onclick=\"return ecc_map.point.i(_google_map_community_members_marker_".$r['ID'].");\""
      ." title=\"Identify ".htmlentities($r['title'])." on map!\""
      .">"
      .htmlentities($r['title'])
      ."</a>"
      ."</li>";
  }

  protected function _draw_map_listing_names($set){
    $out = ($this->_current_user_rights['canEdit'] ? "<ol>" : "<ul>")."\n";
    foreach($set as $r){
      $out.= $this->_draw_map_listing_name($r);
    }
    $out.= ($this->_current_user_rights['canEdit'] ? "</ol>" : "</ul>\n");
    return $out;
  }


  protected function _draw_map_listing_names_all(){
    if (count($this->_member_types)==1){
      return $this->_draw_map_listing_names($this->_records);
    }
    $out = "";
    if (isset($this->_member_types['ministerium'])){
      $out.=
         "<h3>Ministerial Association".(count($this->_member_types['ministerium'])>1 ? 's' : '')."</h3>"
        .$this->_draw_map_listing_names($this->_member_types['ministerium']);
    }
    if (isset($this->_member_types['church'])){
      $out.=
         "<h3>Church".(count($this->_member_types['church'])>1 ? 'es' : '')."</h3>"
        .$this->_draw_map_listing_names($this->_member_types['church']);
    }
    if (isset($this->_member_types['organisation'])){
      $out.=
         "<h3>Organisation".(count($this->_member_types['organisation'])>1 ? 's' : '')."</h3>"
        .$this->_draw_map_listing_names($this->_member_types['organisation']);
    }
    return $out;
  }

  protected function _draw_map_point($r){
    if ($r['service_map_lat']==0 && $r['service_map_lon']==0){
      return;
    }
    $img =
      ($r['featured_image'] && file_exists('.'.$r['featured_image']) ?
        $r['featured_image']
      :
        '/640x480-photo-unavailable.png'
      );
    $featured_image =
       BASE_PATH
      ."img/width/"
      .$this->_cp['map_photo_width']
      ."/?img=".$img;
    $marker_html =
       "<img src='/img/spacer' style='width:1px;height:70px;float:left;' />"
      ."<img src='/img/spacer' style='width:320px;height:1px;display:block;' />"
      ."<a href='".$r['member_URL']."' title='View Profile'>"
      ."<img style='float:left;margin:0 4px 4px 0;border:0;' width='".$this->_cp['map_photo_width']."'"
      ." src='".$featured_image."' alt='".$r['name']."' /></a>"
      ."<div>"
      ."<strong><a href='".$r['member_URL']."' title='View Profile'>".htmlentities($r['title'])."</a></strong><br />"
      .$r['service_addr_line1']
      .($r['service_addr_line2'] ? '<br />'.$r['service_addr_line2'] : '')
      ."<br />".$r['service_addr_city'].' &bull; '.$r['service_addr_postal'];
    $this->_Obj_Map->add_marker_with_html(
      $r['service_map_lat'],
      $r['service_map_lon'],
      $marker_html,
      $r['ID'],
      $this->_current_user_rights['canEdit'],
      true,
      $r['type'],
      (count($this->_records)==1 ? true : false),
      htmlentities($r['title'])
    );
  }

  protected function _draw_map_points_all(){
    if (count($this->_member_types)==1){
      foreach ($this->_records as $r){
        $this->_draw_map_point($r);
      }
      return;
    }
    if (isset($this->_member_types['ministerium'])){
      foreach ($this->_member_types['ministerium'] as $r){
        $this->_draw_map_point($r);
      }
    }
    if (isset($this->_member_types['organisation'])){
      foreach ($this->_member_types['organisation'] as $r){
        $this->_draw_map_point($r);
      }
    }
    if (isset($this->_member_types['church'])){
      foreach ($this->_member_types['church'] as $r){
        $this->_draw_map_point($r);
      }
    }
  }

  protected function _draw_meetings(){
    if ($this->_cp['show_meetings']!=1){
      return;
    }
    $days = explode(',','Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
    $parsed_days = array();
    foreach($days as $day){
      $parsed_days[$day] = array();
    }
    foreach ($this->_records as $r){
      foreach($days as $day){
        if ($r['service_times_'.strToLower(substr($day,0,3))]){
          $slots = explode("\r\n",$r['service_times_'.strToLower(substr($day,0,3))]);
          foreach ($slots as $slot){
            $parsed_days[$day][] = array(
              'title' =>                    htmlentities($r['title']),
              'slot' =>                     $slot,
              'full_member' =>              $r['full_member'],
              'service_map_loc' =>          $r['service_map_loc'],
              'member_URL' =>               $r['member_URL'],
              'member_ID' =>                $r['ID'],
              'primary_communityID' =>      $r['primary_communityID'],
              'primary_ministerialID' =>    $r['primary_ministerialID'],
              'systemID' =>                 $r['systemID']
            );
          }
        }
      }
    }
    $this->_html.=
       HTML::draw_section_tab_div('meetings',$this->_selected_section)
      ."<h2>".$this->_cp['label_meetings']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_meetings']."</div>";
    foreach ($parsed_days as $day=>$items){
      $this->_html.=
        "<h3>".$day."</h3>\n";
      if (!$items){
        $this->_html.= "<div>(No scheduled meetings)</div>";
      }
      if ($items){
        $title = '';
        $members = array();
        $entries = array();
        foreach ($items as $item){
//          y($item);die;
          $slot_arr = explode(' ',$item['slot']);
          if ($item['title']!=$title){
            $members[$item['title']] = array(
              'ID' =>                       $item['member_ID'],
              'full_member' =>              $item['full_member'],
              'service_map_loc' =>          $item['service_map_loc'],
              'member_URL' =>               $item['member_URL'],
              'primary_communityID' =>      $item['primary_communityID'],
              'primary_ministerialID' =>    $item['primary_ministerialID'],
              'title' =>                    $item['title'],
              'slots' =>                    array(),
              'systemID' =>                 $item['systemID'],
              'url' =>                      $item['member_URL']
            );
            $title = $item['title'];
          }
          $slot_arr = explode(' ',$item['slot']);
          $members[$item['title']]['slots'][] = array(str_replace(',','',array_shift($slot_arr)),trim(implode(' ',$slot_arr),','));
        }
        $this->_html.=
           "<table cellpadding='1' cellspacing='0' border='0' class='meetings'>\n"
          ."  <thead>\n"
          ."    <tr>\n"
          ."      <th>Church or Organisation</th>\n"
          ."      <th>Time</th>\n"
          ."      <th>Details</th>\n"
          ."    </tr>\n"
          ."  </thead>\n"
          ."  <tbody>\n";
        foreach ($members as $member=>$data){
//          y($slots);die;
          for($i=0; $i<count($data['slots']); $i++){
            $slot = $data['slots'][$i];
            if ($i==0){
              $this->_html.=
                 "  <tr style='border-top:1px solid #000'>\n"
                ."    <td class='venue'"
                .$this->_draw_context_menu_member($data)
                .(count($data['slots']>1) ? " rowspan='".count($data['slots'])."'" : "")
                .">"
                ."<a href=\"".$data['url']."\" title=\"See details for ".$data['title']."\">"
                .$member
                ."</a>"
                ."</td>\n";
            }
            else {
              $this->_html.=
                 "  <tr>\n";
            }
            $this->_html.=
               "    <td class='time'>".$slot[0]."</td>\n"
              ."    <td class='detail'>".htmlentities($slot[1])."</td>\n"
              ."  </tr>\n";
          }
        }
        $this->_html .=
           "  </tbody>\n"
          ."</table>\n";
      }
    }
    $this->_html.=
       "<div class='section_footer'>".$this->_cp['footer_meetings']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _draw_member($r,&$number=false){
    if ($r['compliance']==0 && $this->_cp['enforce_sharing']==1){
      return;
    }
    $verified =
      ($r['date_survey_returned']!='0000-00-00' ? $r['date_survey_returned'] : false);
    $ministerial =
      ($r['ministerial_title']!='' ? $r['ministerial_title'] : false);
    $img =
      ($r['featured_image'] && file_exists('.'.$r['featured_image']) ?
        $r['featured_image']
      :
        '/640x480-photo-unavailable.png'
      );
    $featured_image =
       BASE_PATH
      ."img/sysimg"
      ."?img=".$img
      ."&amp;resize=1"
      ."&amp;maintain=".$this->_cp['members_photo_lock_aspect']
      ."&amp;width=".$this->_cp['members_photo_width']
      ."&amp;height=".$this->_cp['members_photo_height'];
    $address =
       $r['service_addr_line1']."<br />"
      .($r['service_addr_line2'] ? $r['service_addr_line2'].' &nbsp; ' : '')
      .$r['service_addr_city'].'<br />'
      .$r['service_addr_sp'].' &nbsp; '.$r['service_addr_postal'];
    $this->_draw_member_header($r);
    $this->_html.=
       ($this->_cp['members_show_name'] ?
           "<div class='member_gallery_entry_name' title=\"See complete profile for '".htmlentities($r['title'])."'\">"
          ."<a href=\"".$r['member_URL']."\">"
          .($this->_current_user_rights['canEdit'] ? ($number++).'. ' : '').htmlentities($r['title'])
          ."</a>"
          ."</div>"
        :
           ""
       )
      .($this->_cp['members_show_address'] ?
           "<div class='member_gallery_entry_address' title=\"".str_replace('<br />',"\n",$address)."\">".$address."</div>"
        :  ""
       )
      .($this->_cp['members_show_phone'] && $r['office_phone1_num'] ?
           "<div class='member_gallery_entry_phone'>".$r['office_phone1_num']."</div>"
        :
           ""
       )
      ."</div>"
      ."<div class='member_gallery_entry_photo_area'>"
      .($this->_cp['members_show_photo'] && $featured_image ?
          "<a href=\"".$r['member_URL']."\" title=\"See complete profile for '".htmlentities($r['title'])."'\">"
         ."<img class='member_gallery_entry_photo' src=\"".$featured_image."\" alt=\"".$r['name']."\" /></a>"
       :
         ""
       )
      .($this->_current_user_rights['isSYSADMIN'] || $verified || $r['full_member'] || $ministerial ?
         "<div class='member_gallery_entry_update_status'>"
        .($this->_current_user_rights['isSYSADMIN'] ?
            "<img id='dropbox_status_".$r['ID']."' src='".BASE_PATH."img/sysimg/icon_ajax_wait_16x16.gif' alt='' style='float:left;' title=\"Checking Status...\" />"
          : ""
         )
        .($r['full_member']?
               "<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='background-position: -7846px 0px;' alt='Premium Listing - all features available' title='Premium Listing - all features available'/>"
            : ""
         )
        .($verified ?
            "<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='background-position: -7998px 0px;' title=\"Verified by ".$r['type']." on ".format_date($verified,'l j F Y')."\" alt=\"Member Verified\" />"
          : ""
         )
        .($ministerial ?
            "<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='background-position: -8015px 0px;' title=\"Member of ".$ministerial."\" alt=\"Member of ".$ministerial."\" />"
          : ""
         )
        ."</div>"
       :
         ""
      )
      ."<div class='member_gallery_entry_icons'>\n"
      .($this->_cp['members_show_website'] && $r['link_website']?
           "<a rel='external' href='".htmlentities($r['link_website'])."' onclick=\"\" title=\"View ".$r['type']." website\">"
          ."<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='background-position: -5374px 0px;float:right;' alt='View Website' />"
          ."</a>"
        : ""
       )
      .($this->_cp['members_show_map'] ?
           "<a href='#map' onclick=\"return map_highlight('".$r['ID']."')\" title=\"View ".$r['type']." on community map\">"
          ."<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='background-position: -5358px 0px;float:right;' alt='View Map' />"
          ."</a>"
        : ""
       )
      ."<a href=\"".$r['member_URL']."\" title=\"View ".$r['type']." profile\">"
      ."<img src='".BASE_PATH."img/spacer' width='11' height='16' class='icons' style='background-position: -2600px 2px;float:right;' alt='View Profile'/>"
      ."</a>"
      ."</div>"
      ."</div>"
      ."</div>"
      ;
  }

  protected function _draw_members(){
    if ($this->_cp['show_members']!=1){
      return;
    }
//    y($this->_popup);die;
    $uri_args =
       "primary_communityID=".$this->_community_record['ID']
      ."&amp;service_addr_sp=".(isset($this->_records[0]['service_addr_sp']) ? $this->_records[0]['service_addr_sp'] : "ON")
      ."&amp;service_addr_country=".(isset($this->_records[0]['service_addr_country']) ? $this->_records[0]['service_addr_country'] : "CAN")
      ;
    $this->_html.=
       HTML::draw_section_tab_div('members',$this->_selected_section)
      .($this->_current_user_rights['canEdit'] ?
          "<a class=\"fl icon_add_new\""
         ." href=\"".BASE_PATH."details/".$this->_edit_form['member']."/?".$uri_args."\""
         ." onclick=\"details('".$this->_edit_form['member']."','','".$this->_popup['member']['h']."','".$this->_popup['member']['w']."','','','','".$uri_args."');return false;\""
         ." title=\"Add new member for this community\">"
         ."<img src=\"".BASE_PATH."img/spacer\" class=\"icons\" style=\"margin-left:3px;width:12px;height:13px;background-position: -3512px 0px;\" alt=\"\" />"
         ."</a>"
        :
          ""
       )
      ."<h2>"
      .($this->_current_user_rights['canEdit'] ?
           "<a"
          ." href=\"".BASE_PATH.'details/'.$this->_edit_form['community'].'/'.$this->_community_record['ID'].'"'
          ." onclick=\"details('".$this->_edit_form['community']."','".$this->_community_record['ID']."','".$this->_popup['community']['h']."','".$this->_popup['community']['w']."');return false;\""
          .">"
        :
           ""
       )
      .$this->_cp['label_members']
      .($this->_current_user_rights['canEdit'] ? "</a>" : "")
      ."</h2>\n"
      ."<div class='section_header'>".$this->_cp['header_members']."</div>"
      .$this->_draw_members_help();
    if (count($this->_member_types)==1){
      $i=1;
      foreach ($this->_records as $record){
        $this->_draw_member($record,$i);
      }
    }
    else {
      if (isset($this->_member_types['church'])){
        $i=1;
        $this->_html.= "<h2>Churches</h2>";
        foreach ( $this->_member_types['church'] as $record){
          $this->_draw_member($record,$i);
        }
        $this->_html.="<br class='clear' />";
      }
      if (isset($this->_member_types['organisation'])){
        $i=1;
        $this->_html.= "<h2>Organisations</h2>";
        foreach ( $this->_member_types['organisation'] as $record){
          $this->_draw_member($record,$i);
        }
        $this->_html.="<br class='clear' />";
      }
      if (isset($this->_member_types['ministerium'])){
        $i=1;
        $this->_html.= "<h2>Ministerial Association".(count($this->_member_types['ministerium'])>1 ? "s" : "")."</h2>";
        foreach ( $this->_member_types['ministerium'] as $record){
          $this->_draw_member($record,$i);
        }
        $this->_html.="<br class='clear' />";
      }
    }
    $this->_html.=
       "<div class='clear'>&nbsp;</div>"
      ."<div class='section_footer'>".$this->_cp['footer_members']."</div>"
      ."</div>\n";
  }

  protected function _draw_member_header($record){
    $this->_html.=
       "<div class='member_gallery_entry'>"
      ."<div class='member_gallery_entry_header'"
      .$this->_draw_context_menu_member($record)
      .">";
  }

  protected function _draw_members_help(){
    return
       "<div class='community_help'>"
      ."<div style='width: 3em; height:1.3em; float:left'>\n"
      ."<b>Help:</b>\n"
      ."</div>\n"
      ."<div style='width: 9em; float:left'>\n"
      ."<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='margin:0 2px;background-position: -7846px 0px;' alt='Premium Listing - all features available' title='Premium Listing - all features available'/>"
      ."Premium Listing"
      ."</div>\n"
      ."<div style='width: 8em; float:left'>\n"
      ."<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='margin:0 2px;background-position: -7998px 0px;' title=\"Fully Verified\" alt=\"Fully Verified\" />"
      ."Fully Verified"
      ."</div>\n"
      ."<div style='width: 10.5em; float:left'>\n"
      ."<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='margin:0 2px;background-position: -8015px 0px;' alt='Ministerial Member' title='Ministerial Member'/>"
      ."Ministerial Member"
      ."</div>\n"
      ."<div style='width: 7em; float:left'>\n"
      ."<img src='".BASE_PATH."img/spacer' width='11' height='16' class='icons' style='margin:2px 2px;background-position: -2600px 0px;' alt='View Profile' title='View Profile'/>"
      ."View Profile"
      ."</div>\n"
      ."<div style='width: 6.5em; float:left'>\n"
      ."<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='margin:2px 2px;background-position: -5358px 0px;' alt='View Map' title='View Map'/>"
      ."View map"
      ."</div>\n"
      ."<div style='width: 7.5em; float:left'>\n"
      ."<img src='".BASE_PATH."img/spacer' width='16' height='16' class='icons' style='margin:2px 2px;background-position: -5374px 0px;' alt='View Website' title='View Website'/>"
      ."View website"
      ."</div>\n"
      ."<br class='clear' />\n"
      ."</div>";
  }

  protected function _draw_members_signatories(){
    $entries = array();
    foreach ($this->_records as $record){
      if (trim($record['signatories'])){
        $entries[] = $record;
      }
    }
    if (!count($entries)){
      return;
    }
    $html = "<ul class=\"cross signatories\">\n";
    foreach ($entries as $r){
      $html.=
         "  <li"
        .$this->_draw_context_menu_member($r)
        .">\n"
        ."    <strong>".str_replace(" and ","</strong> and <strong>",str_replace('& ','&amp; ',$r['signatories']))."</strong>, "
        ."    <a href=\"".$r['member_URL']."\">".str_replace('& ','&amp; ',$r['title'])."</a>\n"
        ."  </li>\n";
    }
    $html.= "</ul>\n";
    return $html;
  }

  protected function _draw_members_summaries(){
    $entries = array();
    foreach ($this->_records as $record){
      if (trim($record['summary'])){
        $entries[] = $record;
      }
    }
    if (!count($entries)){
      return;
    }
    $html = "<ul class=\"cross churches_spaced\">\n";
    foreach ($entries as $r){
    $img =
      ($r['featured_image'] && file_exists('.'.$r['featured_image']) ?
        $r['featured_image']
      :
        '/640x480-photo-unavailable.png'
      );
      $featured_image = BASE_PATH."img/sysimg?img=".$img."&amp;resize=1&amp;maintain=0&amp;width=50&amp;height=40";
      $html.=
         "  <li"
        .$this->_draw_context_menu_member($r)
        .">\n"
        ."    <a href=\"".$r['member_URL']."\">"
        ."<img alt=\"".str_replace('& ','&amp; ',$r['title'])."\" src=\"".$featured_image."\" /></a>"
        .str_replace(
          '##LINKED_TITLE##',
          "<a href=\"".$r['member_URL']."\">".str_replace('& ','&amp; ',$r['title'])."</a>",
          nl2br($r['summary'])
        )
        ."\n"
        ."  </li>\n";
    }
    $html.= "</ul>\n";
    return $html;
  }

  protected function _draw_news(){
    if ($this->_cp['show_news']!=1){
      return;
    }
    $Obj = new Community_News_Item;
    $Obj->community_record =    $this->_community_record;
    $Obj->communityID =         $this->_community_record['ID'];
    $args = array(
      'author_show' =>          $this->_cp['listing_show_author'],
      'category_show' =>        $this->_cp['listing_show_category'],
      'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
      'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
      'content_show' =>         $this->_cp['listing_show_content'],
      'results_limit' =>        $this->_cp['listing_results_limit'],
      'results_paging' =>       $this->_cp['listing_results_paging'],
      'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
      'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
      'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
    );
    $this->_html.=
       HTML::draw_section_tab_div('news',$this->_selected_section)
      .$this->_draw_web_share('news','news')
      ."<h2>".$this->_cp['label_news']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_news']."</div>"
      .$Obj->draw_listings('community_news',$args,false)
      ."<div class='section_footer'>".$this->_cp['footer_news']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _draw_podcasts(){
    if ($this->_cp['show_podcasts']!=1){
      return;
    }
    $Obj = new Community_Podcast;
    $Obj->community_record =    $this->_community_record;
    $Obj->communityID =         $this->_community_record['ID'];
    $args = array(
      'author_show' =>          $this->_cp['listing_show_author'],
      'audioplayer_width' =>    $this->_cp['listing_audioplayer_width'],
      'category_show' =>        $this->_cp['listing_show_category'],
      'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
      'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
      'content_show' =>         $this->_cp['listing_show_content'],
      'filter_what' =>          'future',
      'results_limit' =>        $this->_cp['listing_results_limit'],
      'results_paging' =>       $this->_cp['listing_results_paging'],
      'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
      'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
      'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
    );
    $this->_html.=
       HTML::draw_section_tab_div('podcasts',$this->_selected_section)
      .$this->_draw_web_share('podcasts','podcasts')
      ."<h2>".$this->_cp['label_podcasts']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_podcasts']."</div>"
      .$Obj->draw_listings('community_podcasts',$args,false)
      ."<div class='section_footer'>".$this->_cp['footer_podcasts']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _draw_section_container_close(){
    $this->_html.= "</div>\n";
  }

  protected function _draw_section_container_open(){
    Page::push_content(
      'javascript_onload',
      "  show_section_tab('spans_".$this->_safe_ID."','".$this->_selected_section."');"
    );
    $this->_html.= "<div id='".$this->_safe_ID."_container' style='position:relative;'>\n";
  }

  protected function _draw_sponsors_national(){
    if ($this->_cp['show_sponsors']!=1){
      return;
    }
    if (!count($this->_sponsors_national_records)){
      return;
    }
    $out =
       "<hr />\n"
      ."<h3>".$this->_cp['label_sponsors_national']."</h3>"
      ."<div class='section_header'>".$this->_cp['header_sponsors_national']."</div>";
    $Obj_CGT = new Component_Gallery_Thumbnails;
    $args = array(
        'color_background' =>           'f8f8ff',
        'color_border' =>               '695137',
        'filter_category_master' =>     '',
        'filter_container_path' =>      $this->_sponsors_national_container,
        'image_padding_horizontal' =>   10,
        'image_padding_vertical' =>     5,
        'max_height' =>                 210,
        'max_width' =>                  170,
        'results_limit' =>              0,
        'show_background' =>            1,
        'show_border' =>                1,
        'show_image_border' =>          1,
        'show_caption' =>               0,
        'show_image' =>                 1,
        'show_title' =>                 1,
        'show_uploader' =>              0,
        'title_height' =>               50
      );
    $out.=
      $Obj_CGT->draw('national_sponsors',$args,false);
    return $out;
  }

  protected function _draw_sponsors_local(){
    if ($this->_cp['show_sponsors']!=1){
      return;
    }
    if (!$this->_community_record['sponsorship_gallery_albumID']){
      return;
    }
    $Obj_GA = new Gallery_Album;
    $Obj_GA->_set_ID($this->_community_record['sponsorship_gallery_albumID']);
    $path = $Obj_GA->get_field('path');
    $Obj_SP = new Sponsorship_Plan;
    $args = array(
      'container_path' =>  $path
    );
    $result = $Obj_SP->get_records($args);
    $Obj_CGT = new Component_Gallery_Thumbnails;
    $out = "";
    foreach ($result['data'] as $plan){
      $Obj_SP->xmlfields_decode($plan);
      $Obj_SP->_set_ID($plan['ID']);
      if ($this->_current_user_rights['canEdit'] || $Obj_SP->get_children()){
        $args = array(
            'color_background' =>       'ffffff',
            'filter_category_master' => '',
            'filter_container_path' =>  $plan['path'],
  //          'max_height' =>             $plan['xml:width'],
            'max_width' =>              $plan['xml:width'],
            'results_limit' =>          0,
            'show_background' =>        1,
            'show_caption' =>           $plan['xml:show_description'],
            'show_image' =>             $plan['xml:show_logo'],
            'show_title' =>             $plan['xml:show_name'],
            'show_uploader' =>          1,
        );
        $result = $Obj_CGT->draw($plan['name'],$args,false);
        if ($result){
          $out.=
            ($out ? "<hr />\n" : "")
           .($this->_current_user_rights['canEdit'] ?
               "<h4>"
              ."<a href='#' onclick=\"details('".$this->_edit_form['sponsor_plan']."','".$plan['ID']."','".$this->_popup['sponsor_plan']['h']."','".$this->_popup['sponsor_plan']['w']."');return false;\">"
              .$plan['title']." ($".$plan['xml:cost'].")"
              ."</a>"
              ."</h4>"
            :
              "<h4>".$plan['title']." ($".$plan['xml:cost'].")"."</h4>\n"
            )
            .$result;
        }
      }
    }
    if ($out){
      return
         "<hr />\n"
        ."<h3>".$this->_cp['label_sponsors_local']."</h3>"
        ."<div class='section_header'>".$this->_cp['header_sponsors_local']."</div>"
        .$this->_community_record['sponsorship']
        .$out;
    }
  }

  protected function _draw_section_tabs(){
    $this->_html.=
      HTML::draw_section_tab_buttons(
        $this->_section_tabs_arr,
        $this->_safe_ID,
        $this->_selected_section,
        "document.location.hash='#'+this.id.substr(8).split('_')[0]"
      );
  }

  protected function _draw_stats(){
    if (!$this->_current_user_rights['canViewStats']){
      return;
    }
    if (!PIWIK_DEV && (substr($_SERVER["SERVER_NAME"],0,8)=='desktop.' || substr($_SERVER["SERVER_NAME"],0,7)=='laptop.')){
      return;
    }
    $this->_html.=
       HTML::draw_section_tab_div('stats',$this->_selected_section)
      ."<h2>".$this->_cp['label_stats']."</h2>"
      ."<div class='section_header'>".$this->_cp['header_stats']."</div>"
      ."<fieldset>\n"
      ."<legend>Report Settings</legend>\n"
      .Report_Column::draw_label('Start Date','Start date for report','stats_date_start',false, 100)
      .draw_form_field('stats_date_start',$this->_stats_date_start,'date')
      ."<br class='clr_b' />\n"
      .Report_Column::draw_label('End Date','End date for report','stats_date_end', false, 100)
      .draw_form_field('stats_date_end',$this->_stats_date_end,'date')
      ."<br class='clr_b' />\n"
      ."<div class='txt_c'><input type='submit' value='Update' /></div>\n"
      ."</fieldset>\n"
      ."<h3>Members</h3>\n"
      ."<table cellpadding='2' cellspacing='0' border='1' class='community_stats' summary='Table showing statistics for each member'>\n"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th rowspan='2' style='width:400px'>Member</th>\n"
      ."      <th colspan='2'>Profile</th>\n"
      ."      <th colspan='2'>Website</th>\n"
      ."      <th colspan='2'>Facebook</th>\n"
      ."      <th colspan='2'>Twitter</th>\n"
      ."      <th colspan='2'>Video</th>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <th>Hits</th>\n"
      ."      <th>Visits</th>\n"
      ."      <th>Hits</th>\n"
      ."      <th>Visits</th>\n"
      ."      <th>Hits</th>\n"
      ."      <th>Visits</th>\n"
      ."      <th>Hits</th>\n"
      ."      <th>Visits</th>\n"
      ."      <th>Hits</th>\n"
      ."      <th>Visits</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach($this->_records as $r){
      $this->_html.=
         "    <tr>\n"
        ."      <th".$this->_draw_context_menu_member($r).">"
        ."<a href=\"".$r['member_URL']."\">".$r['title']."</a>"
        ."</th>\n"
        ."      <td>".$r['profile_hits']."</td>\n"
        ."      <td>".$r['profile_visits']."</td>\n"
        ."      <td>".($r['link_website'] ?  $r['links']['website']['hits'] :    '')."</td>\n"
        ."      <td>".($r['link_website'] ?  $r['links']['website']['visits'] :  '')."</td>\n"
        ."      <td>".($r['link_facebook'] ? $r['links']['facebook']['hits']  :  '')."</td>\n"
        ."      <td>".($r['link_facebook'] ? $r['links']['facebook']['visits'] : '')."</td>\n"
        ."      <td>".($r['link_twitter'] ?  $r['links']['twitter']['hits']  :  '')."</td>\n"
        ."      <td>".($r['link_twitter'] ?  $r['links']['twitter']['visits'] : '')."</td>\n"
        ."      <td>".($r['link_video'] ?    $r['links']['video']['hits']  :  '')."</td>\n"
        ."      <td>".($r['link_video'] ?    $r['links']['video']['visits'] : '')."</td>\n"
        ."    </tr>\n";
    }
    $this->_html.=
       "  </tbody>\n"
      ."</table>\n"
      ."<h3>National Sponsors</h3>\n"
      ."<table cellpadding='2' cellspacing='0' border='1' class='community_stats' summary='Table showing statistics for each national sponsor'>\n"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th rowspan='2' style='width:400px'>Sponsor</th>\n"
      ."      <th colspan='2'>Website</th>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <th>Hits</th>\n"
      ."      <th>Visits</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach($this->_sponsors_national_records as $r){
      $this->_html.=
         "    <tr>\n"
        ."      <th".$this->_draw_context_menu_sponsor($r).">"
        .$r['title']
        ."</th>\n"
        ."      <td>".$r['links']['hits']."</td>\n"
        ."      <td>".$r['links']['visits']."</td>\n"
        ."    </tr>\n";
    }
    $this->_html.=
       "  </tbody>\n"
      ."</table>\n"
      ."<div class='section_footer'>".$this->_cp['footer_stats']."</div>"
      ."</div>\n";
  }

  protected function _draw_web_share($rss='', $embed=''){
    global $page_vars;
    return
       "<div class='web_share'>"
      .($rss ?
          "<img class='icon rss' src='/img/spacer' alt='' title='RSS Feed'  />"
         ."<a rel=\"external\" title=\"Click to subscribe to this RSS Feed\" href=\"".BASE_PATH.trim($page_vars['path'],'/')."/rss/".$rss."\">RSS</a>\n"
       :
          ""
       )
      .($embed ?
          "<img class='icon share' src='/img/spacer' alt='' title='Embed on your website'  />"
         ."<a title=\"Embed this information on your website\" href=\"#\" onclick=\"return community_embed('".addslashes(htmlentities("The Community of ".$this->_community_record['title']))."','".BASE_PATH.trim($page_vars['path'],'/')."','".$embed."')\">Web</a>\n"
       :
          ""
       )
      ."</div>";
  }
  protected function _draw_welcome(){
    if ($this->_cp['show_welcome']!=1 || $this->_community_record['welcome']==''){
      return;
    }
    $welcome =
      str_replace(
        array('##MEMBER_SUMMARIES##','##MEMBER_SIGNATORIES##'),
        array($this->_draw_members_summaries(),$this->_draw_members_signatories()),
        $this->_community_record['welcome']
      );
    $this->_html.=
       HTML::draw_section_tab_div('welcome',$this->_selected_section)
      ."<div class='section_header'>".$this->_cp['header_welcome']."</div>"
      .($this->_current_user_rights['canEdit'] ?
        "<a href=\"#\" style=\"display: block;float:right;font-weight:bold;background:#ff8;padding:5px;border:1px solid #440;\" onclick=\"details('".$this->_edit_form['community']."',".$this->_community_record['ID'].",".$this->_popup['community']['h'].",".$this->_popup['community']['w'].",'','');return false;\">Edit...</a>"
       : ""
       )
      .$welcome
      ."<div class='section_footer'>".$this->_cp['footer_welcome']."</div>"
      .$this->_draw_disclaimer()
      ."</div>\n";
  }

  protected function _setup($instance,$args,$disable_params){
    global $page_vars;
    $this->_ident =             "module_community";
    $this->_safe_ID =           Component_Base::get_safe_ID($this->_ident,$this->_instance);
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $this->_ident, $instance, $disable_params, $this->_cp_vars, $args
      );
    $this->_cp_defaults =       $cp_settings['defaults'];
    $this->_cp =                $cp_settings['parameters'];
    $this->_html.=
      Component_Base::get_help(
        $this->_ident, $instance, $disable_params, $this->_cp_vars, $this->_cp_defaults
      );
    $this->_path_extension =    $page_vars['path_extension'];
    $this->_setup_listings_load_community_record();
  }

  protected function _setup_listings(){
    $this->_stats_date_start = (get_var('stats_date_start') ? get_var('stats_date_start') : substr(get_timestamp(),0,10));
    $this->_stats_date_end =   (get_var('stats_date_end') ?   get_var('stats_date_end') :   substr(get_timestamp(),0,10));
    $this->_setup_listings_load_user_rights();
    $this->_setup_listings_connect_to_dropbox();
    $this->_setup_listings_load_records();
    $this->_setup_listings_categorise_records();
    $this->_setup_listings_load_events_special();
    $this->_setup_sponsors();
    $this->_setup_listings_load_piwik_stats();
    $this->_setup_listings_tabs();
    if (get_var('check_dropbox')){
      $this->_check_dropbox();
    }
  }

  protected function _setup_listings_categorise_records(){
    foreach ($this->_records as $r){
      if (!isset($this->_member_types[$r['type']])){
        $this->_member_types[$r['type']] = array();
      }
      $this->_member_types[$r['type']][] = $r;
    }
 }

  protected function _setup_listings_connect_to_dropbox(){
    if (!$this->_community_record['dropbox_access_token_key']){
      return;
    }
    $params = array(
      'consumerKey' =>    $this->_community_record['dropbox_app_key'],
      'consumerSecret' => $this->_community_record['dropbox_app_secret'],
      'tokenKey' =>       $this->_community_record['dropbox_access_token_key'],
      'tokenSecret' =>    $this->_community_record['dropbox_access_token_secret'],
      'sslCheck' =>       false
    );
    $this->_Obj_DropLib = new DropLib($params);
  }

  protected function _setup_listings_load_community_record(){
    $community_name =   $this->_cp['community_name'];
    $this->set_ID_by_name($community_name);
    if (!$this->_community_record = $this->get_record()){
      header("Status: 404 Not Found",true,404); // Keep those pesky bots from following dead links!
      throw new Exception("Community \"".$community_name."\" not found.");
    }
  }

  protected function _setup_listings_load_events_special(){
    $this->_events_special =          $this->get_events_upcoming($this->_cp['category_events_special']);
  }

  protected function _setup_listings_load_piwik_stats(){
    global $system_vars;
    if (!PIWIK_DEV && (substr($_SERVER["SERVER_NAME"],0,8)=='desktop.' || substr($_SERVER["SERVER_NAME"],0,7)=='laptop.')){
      return;
    }
    if (!$this->_current_user_rights['canViewStats']){
      return;
    }
    $Obj_Piwik = new Piwik;
    $names = array();
    foreach($this->_records as $r){
      $names[] = '/'.$r['name'];
    }
    $find = implode('|',$names);
//    $find = 'apostolic-christian-church-of-the-nazarene|bethel';
//    $find = "/aurora-cornerstone-church";
    $this->_track_outlinks = $Obj_Piwik->get_outlinks($this->_stats_date_start,$this->_stats_date_end,'');
    $this->_track_views =    $Obj_Piwik->get_visits($this->_stats_date_start,$this->_stats_date_end,$find);
//y($this->_track_outlinks);
//y($this->_track_views);die;
    foreach ($this->_records as &$r){
      $link_types = array('website','facebook','twitter','video');
      $r['links'] = array();
      foreach($link_types as $l){
        $r['links'][$l] = array('hits'=>'-', 'visits' => '-');
        if ($r['link_'.$l]){
          $url =    trim($r['link_'.$l],'/');
          if (isset($this->_track_outlinks[$url])){
            $r['links'][$l] = $this->_track_outlinks[$url];
          }
          if (isset($this->_track_outlinks[$url.'/'])){
            $r['links'][$l] = $this->_track_outlinks[$url.'/'];
          }
        }
      }
      $r['profile_hits'] =      '-';
      $r['profile_visits'] =    '-';
//      y($r);die;
      foreach($this->_track_views as $site=>$data){
        if (trim($site,'/') == trim($system_vars['URL'],'/').'/'.trim($r['member_URL'],'/')){
          $r['profile_hits'] =     $data['hits'];
          $r['profile_visits'] =   $data['visits'];
        }
      }
    }
    foreach ($this->_sponsors_national_records as &$r){
      $r['links'] =     array('hits'=>'-', 'visits' => '-');
      $url =    trim($r['URL'],'/');
      if (isset($this->_track_outlinks[$url])){
        $r['links'] = $this->_track_outlinks[$url];
      }
      if (isset($this->_track_outlinks[$url.'/'])){
        $r['links'] = $this->_track_outlinks[$url.'/'];
      }
    }
  }

  protected function _setup_listings_load_records(){
    $this->_records = $this->get_members();
  }

  protected function _setup_listings_load_user_rights(){
    parent::_setup_load_user_rights();
  }

  protected function _setup_listings_tabs(){
    if ($this->_cp['show_welcome']==1 && $this->_community_record['welcome']!=''){
      $this->_section_tabs_arr[] =   array('ID'=>'welcome','label'=>$this->_cp['tab_welcome']);
    }
    if ($this->_cp['show_members']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'members','label'=>$this->_cp['tab_members']);
    }
    if ($this->_cp['show_events_special']==1 && $this->_events_special){
      $this->_section_tabs_arr[] =   array('ID'=>'special','label'=>$this->_cp['tab_events_special']);
    }
    if ($this->_cp['show_map']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'map','label'=>$this->_cp['tab_map']);
    }
    if ($this->_cp['show_meetings']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'meetings','label'=>$this->_cp['tab_meetings']);
    }
    if ($this->_cp['show_articles']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'articles','label'=>$this->_cp['tab_articles']);
    }
    if ($this->_cp['show_events']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'events','label'=>$this->_cp['tab_events']);
    }
    if ($this->_cp['show_calendar']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'calendar','label'=>$this->_cp['tab_calendar']);
    }
    if ($this->_cp['show_news']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'news','label'=>$this->_cp['tab_news']);
    }
    if ($this->_cp['show_podcasts']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'podcasts','label'=>$this->_cp['tab_podcasts']);
    }
    if ($this->_cp['show_gallery']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'gallery','label'=>$this->_cp['tab_gallery']);
    }
    if ($this->_cp['show_stats']==1 && $this->_current_user_rights['canViewStats'] && (PIWIK_DEV || (substr($_SERVER["SERVER_NAME"],0,8)!=='desktop.' && substr($_SERVER["SERVER_NAME"],0,7)!=='laptop.'))){
      $this->_section_tabs_arr[] =   array('ID'=>'stats','label'=>$this->_cp['tab_stats']);
    }
    if ($this->_cp['show_about']==1){
      $this->_section_tabs_arr[] =   array('ID'=>'about','label'=>$this->_cp['tab_about']);
    }
    $width = floor($this->_cp['width']/count($this->_section_tabs_arr))-10;
    foreach($this->_section_tabs_arr as &$t){
      $t['width'] = $width;
    }
    $selected_section = (get_var('selected_section') ? get_var('selected_section') : $this->_section_tabs_arr[0]['ID']);
    $this->_selected_section = $selected_section;
  }

  protected function _setup_sponsors(){
    $Obj_GA = new Gallery_Album;
    $this->_sponsors_national_container = '//sponsors/national';
    $Obj_GA = new Gallery_Album;
    if (!$ID = $Obj_GA->get_ID_by_path($this->_sponsors_national_container)){
      return;
    }
    $Obj_GA->_set_ID($ID);
    $this->_sponsors_national_records = $Obj_GA->get_children();
  }

  public function get_version(){
    return COMMUNITY_DISPLAY_VERSION;
  }
}

?>