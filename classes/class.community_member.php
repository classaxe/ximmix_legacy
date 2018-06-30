<?php
define('COMMUNITY_MEMBER_VERSION', '1.0.105');
/*
Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)
*/
/*
Version History:
  1.0.105 (2015-01-01)
    1) Now uses OPTION_SEPARATOR constant not option_separator in Community_Member::set_up_member_page_vars()
    2) Class Constants DASHBOARD_WIDTH and DASHBOARD_HEIGHT now uppercase (PSR-2 requirement)
    3) Bug fix for Dashboard - get_partner_selector_sql() - following changes to address fields a while ago
    4) Updated editable fields in Dashboard - many have changed since this last worked
    5) Removed avatar support for community members - now multiple images are held in a gallery
    6) Now PSR-2 Compliant (except for const FIELDS length warning)

  (Older version history in class.community_member.txt)
*/

class Community_Member extends Displayable_Item
{
    const FIELDS = 'ID, archive, archiveID, deleted, systemID, gallery_albumID, podcast_albumID, primary_communityID, primary_ministerialID, admin_notes, attention_required, contact_history, date_photo_taken, date_survey_returned, date_welcome_letter, date_went_live, languages, link_facebook, link_twitter, link_video, link_website, mailing_addr_line1, mailing_addr_line2, mailing_addr_city, mailing_addr_country, mailing_addr_postal, mailing_addr_sp, office_addr_line1, office_addr_line2, office_addr_city, office_addr_country, office_addr_postal, office_addr_sp, office_fax, office_map_desc, office_map_geocodeID, office_map_geocode_area, office_map_geocode_quality, office_map_geocode_type, office_map_loc, office_map_lat, office_map_lon, office_notes, office_phone1_lbl, office_phone1_num, office_phone2_lbl, office_phone2_num, office_times_sun, office_times_mon, office_times_tue, office_times_wed, office_times_thu, office_times_fri, office_times_sat, service_addr_line1, service_addr_line2, service_addr_city, service_addr_country, service_addr_postal, service_addr_sp, service_map_desc, service_map_geocodeID, service_map_geocode_area, service_map_geocode_quality, service_map_geocode_type, service_map_loc, service_map_lat, service_map_lon, service_notes, service_times_sun, service_times_mon, service_times_tue, service_times_wed, service_times_thu, service_times_fri, service_times_sat, stats_cache, name, title, category, contactID, contact_NFirst, contact_NGreeting, contact_NLast, contact_NMiddle, contact_NTitle, contact_PEmail, contact_Telephone, custom_1, custom_2, custom_3, custom_4, custom_5, custom_6, custom_7, custom_8, custom_9, custom_10, date_verified, dropbox_folder, dropbox_last_checked, dropbox_last_filelist, dropbox_last_status, featured_image, full_member, partner_csv, PEmail, shortform_name, signatories, summary, type, URL, XML_data, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
    const DASHBOARD_HEIGHT = 500;
    const DASHBOARD_WIDTH =  860;

    public $module_community_member_dashboard_css =
    "[
  ['#module_community',{
    position:           'relative',
    width:              (#WIDTH# - 10 + (isIE_lt6 ? 8 : 0))+'px',
    backgroundColor:    '#E9EEF7',
    padding:            '4px'
  }],
  ['#module_community div.content',{
    width:              (#WIDTH# - 106 + (isIE_lt6 ? 8 : 0))+'px',
    height:             (#HEIGHT# - 30)+'px',
    padding:            '4px',
    border:             '1px solid #486CAE',
    float:              'left',
    backgroundColor:    '#ffffff'
  }],
  ['#module_community div.content h1',{
    margin:             '0',
    fontSize:           '14pt'
  }],
  ['#module_community .admin_formLabel',{
    width:              '130px', float: 'left'
  }],
  ['#module_community .contact_addresses',{
    padding:              '0 0 0 65px'
  }],
  ['#module_community .contact_addresses .admin_formLabel',{
    width:              '65px', float: 'left'
  }],
  ['#module_community div.tabs',{
    position:           'absolute',
    left:               (#WIDTH# - 92 + (isIE_lt6 ? -2 : 0) + (isIE_lt7 ? 2 : 0))+'px'
  }],
  ['#module_community div.tabs img',{
    display:            'block',
    border:             '0',
    position:           'relative',
    left:               (isIE_lt7 ? '-3px' : '-1px'),
    width:              '83px',
    backgroundImage:    'url(/img/sysimg/?img=module_community/dashboard_tabs.png)'
  }],
  ['#module_community_profile_settings',{
    border:             '1px solid #486CAE',
    padding:            '5px',
    backgroundColor:    '#DBE3F1',
    height:             (#HEIGHT# - 90) +'px',
    width:              (#WIDTH# - 120) +'px'
  }],
  ['#module_community_profile_settings .section_tabs',{
    height:             '35px'
  }]";


    protected $_member_name =       '';
    protected $_member_page =       '';
    protected $_record =            false;
    protected $_selected_section =  '';
    protected $_section_tabs_arr =  array();

    public function __construct($ID = "")
    {
        parent::__construct('community_member', $ID);
        $this->_set_assign_type('Community Member');
        $this->_set_object_name('Community Member');
        $this->_set_has_categories(true);
        $this->_set_has_languages(true);
        $this->_set_context_menu_ID('community_member');
        $this->_dashboard_url =
            BASE_PATH
            .'_popup_layer/community_member_dashboard/'
            .self::DASHBOARD_WIDTH.'/'
            .self::DASHBOARD_HEIGHT.'/';
        $this->module_community_member_dashboard_css =
            str_replace(
                array('#WIDTH#','#HEIGHT#'),
                array(self::DASHBOARD_WIDTH, self::DASHBOARD_HEIGHT),
                $this->module_community_member_dashboard_css
            );
        $this->set_edit_params(
            array(
            'report' =>                 'community_member',
            'report_rename' =>          false,
            'report_rename_label' =>    ''
            )
        );
    }

    protected function _draw_object_map_html_get_data()
    {
        if (!$this->load()) {
            return;
        }
        if ($this->record[$this->_field_lat] || $this->record[$this->_field_lon]) {
            $this->_data_items[] = array(
            'ID' =>         $this->record['ID'],
            'map_lat' =>    $this->record[$this->_field_lat],
            'map_lon' =>    $this->record[$this->_field_lon],
            'map_area' =>   $this->record[$this->_field_area],
            'map_loc' =>    $this->record[$this->_field_info],
            'map_icon' =>   '',
            'map_name' =>   trim(title_case_string($this->record['title'])),
            'record' =>     $this->record
            );
        }
    }

    public function do_dashboard($selected = '')
    {
        $msg = "<b>Please wait</b>: Saving profile";
        $severity = 1;
        $abort = false;
        $memberID = (isset($_SESSION['person']) && isset($_SESSION['person']['memberID']) ?
            $_SESSION['person']['memberID']
        :
            0
        );
        if (!$memberID) {
            return
            array(
            'html' =>
             "<div style='width:300px;padding:10px;border:1px solid #000080;margin:10px;background-color:#e0e0ff'>"
            ."<h1>Error</h1>"
            ."<p>Your signin account has not been assigned to any specific community member.</p>"
            ."<p class='txt_c'>"
            ."<input style='width:73px;' type='button' onclick='hidePopWin(null)' value='Close' class='formButton' />"
            ."</p></div>"
            );
        }
        $submode = (isset($_REQUEST['submode']) ? $_REQUEST['submode'] : "");
        switch ($submode){
            case "module_community_save":
                $fields = explode(
                    ',',
                    'title,AAddress1,AAddress2,ACity,AStateProvince,APostal,ACountryID,custom_1,'
                    .'AFacebook,ATwitter,AWeb,AYoutube,map_location,'
                    .'weekly_sun,weekly_mon,weekly_tue,weekly_wed,weekly_thu,weekly_fri,weekly_sat,'
                    .'xml:Church_Office_Mon,xml:Church_Office_Tue,xml:Church_Office_Wed,xml:Church_Office_Thu,'
                    .'xml:Church_Office_Fri,xml:Church_Office_Sat,xml:Church_Office_Sun,'
                    .'xml:contact:name_1,xml:contact:name_2,xml:contact:name_3,xml:contact:name_4,'
                    .'xml:contact:name_5,xml:contact:email_1,xml:contact:email_2,xml:contact:email_3,'
                    .'xml:contact:email_4,xml:contact:email_5,ATelephone,partner_csv'
                );
                $this->_set_ID($memberID);
                $data =     array();
                foreach ($fields as $field) {
                    $data[$field] =   addslashes(sanitize('html', get_var($field)));
                }
                $this->update($data);
                $this->on_action_set_map_location($memberID);
                break;
        }
        return $this->draw_dashboard($selected);
    }

    public function draw_dashboard($selected = '')
    {
        $selected = ($selected=='' ? 'settings' : $selected);
        $out =
        array(
        'css' =>    '',
        'js' =>     '',
        'html' =>   ''
        );
        $toolbar = (get_person_permission("MASTERADMIN") ? 1 : 5);
        $report = array(
            'articles' => draw_auto_report(
                'community_member.dashboard.articles',
                $toolbar,
                $this->_dashboard_url.'articles'
            ),
            'events' => draw_auto_report(
                'community_member.dashboard.events',
                $toolbar,
                $this->_dashboard_url.'events'
            ),
            'news-items' => draw_auto_report(
                'community_member.dashboard.news-items',
                $toolbar,
                $this->_dashboard_url.'news-items'
            ),
            'podcasts' => draw_auto_report(
                'community_member.dashboard.podcasts',
                $toolbar,
                $this->_dashboard_url.'podcasts'
            )
        );
        $popup = array(
            'articles' =>   get_popup_size('community_member.dashboard.articles'),
            'events' =>     get_popup_size('community_member.dashboard.events'),
            'news-items' => get_popup_size('community_member.dashboard.news-items'),
            'podcasts' =>   get_popup_size('community_member.dashboard.podcasts')
        );
        $out['js'] =
            "function setup_tab(id){\n"
            ."  obj =   geid('tab_'+id);\n"
            ."  obj.onclick =     function(e){show_tab(id)}\n"
            ."  if (id!=global_active_tab) {\n"
            ."    obj.onmousedown = function(e){img_state_v('tab_img_'+id,'d');return false;};\n"
            ."    obj.onmouseover = function(e){img_state_v('tab_img_'+id,'o');return false;};\n"
            ."    obj.onmouseout  = function(e){\n"
            ."      img_state_v('tab_img_'+id,(id==global_active_tab ? 'a' : 'n'));\n"
            ."      return false;\n"
            ."    };\n"
            ."  }\n"
            ."  else {\n"
            ."    obj.onmousedown = obj.onmouseover = obj.onmouseout = function(e){}\n"
            ."  }\n"
            ."}\n"
            ."function setup_tabs() {\n"
            ."  var fn, id, i;\n"
            ."  id_arr = tabs_csv.split(',');\n"
            ."  for(i=0;i<id_arr.length; i++){\n"
            ."    id = id_arr[i];\n"
            ."    setup_tab(id);\n"
            ."  }\n"
            ."}\n"
            ."function show_tab(which){\n"
            ."  global_active_tab=which;\n"
            ."  setup_tabs();\n"
            ."  id_arr = tabs_csv.split(',');\n"
            ."  for(i=0;i<id_arr.length; i++){\n"
            ."    id = id_arr[i];\n"
            ."    geid('content_'+id).style.display=(id==which? '' : 'none');\n"
            ."    img_state_v('tab_img_'+id,(id==global_active_tab ? 'a' : 'n'));\n"
            ."  }\n"
            ."  status_message_hide('form_status_module_community_settings');\n"
            ."}\n"
            ."window.save_community_settings = function(msg,severity){\n"
            ."  if (typeof msg=='undefined') {\n"
            ."    msg = '<b>Please wait</b>: Saving changes';\n"
            ."  }\n"
            ."  if (typeof severity=='undefined') {\n"
            ."    severity = 1;\n"
            ."  }\n"
            ."  status_message_show('form_status_module_community_settings',msg,severity,1);\n"
            ."  geid_set('submode','module_community_save');\n"
            ."  popup_layer_submit('".$this->_dashboard_url."settings');\n"
            ."}\n"
            ."var tabs_csv = 'settings,articles,events,news,audio';\n"
            ."var global_active_tab = 'settings';\n"
            ."setup_tabs();\n"
            ."show_tab('".$selected."');\n"
            .$report['articles']['js']
            .$report['events']['js']
            .$report['news-items']['js']
            .$report['podcasts']['js'];
        $out['html'].=
             "<div id='module_community'>\n"
            ."  <div class='content'>\n"
            ."    <div id='content_settings'>\n"
            ."      <h1>Your Community Member Settings</h1>\n"
            .$this->_draw_dashboard_settings()
            ."    </div>\n"
            ."    <div id='content_articles'>\n"
            ."      <h1 class='fl'>Manage Articles</h1>\n"
            ."      <div class='fl' style='padding:3px 0 0 10px'>"
            ."<a href=\"javascript:details('community_member.dashboard.articles','',"
            ."'".$popup['articles']['h']."','".$popup['articles']['w']."','','')\">"
            ."[ICON]12 12 3512 Add New Article[/ICON]</a></div><br class='clr_b' />\n"
            ."      <div style='overflow:auto;height:320px;'>"
            .convert_safe_to_php($report['articles']['html'])
            ."</div>\n"
            ."    </div>\n"
            ."    <div id='content_events'>\n"
            ."      <h1 class='fl'>Manage Events</h1>\n"
            ."      <div class='fl' style='padding:3px 0 0 10px'>"
            ."<a href=\"javascript:details('community_member.dashboard.events','',"
            ."'".$popup['events']['h']."','".$popup['events']['w']."','','')\">"
            ."[ICON]12 12 3512 Add New Event[/ICON]</a></div><br class='clr_b' />\n"
            ."      <div style='overflow:auto;height:320px;'>"
            .convert_safe_to_php($report['events']['html'])
            ."</div>\n"
            ."    </div>\n"
            ."    <div id='content_news'>\n"
            ."      <h1 class='fl'>Manage News Items</h1>\n"
            ."      <div class='fl' style='padding:3px 0 0 10px'>"
            ."<a href=\"javascript:details('community_member.dashboard.news-items','',"
            ."'".$popup['news-items']['h']."','".$popup['news-items']['w']."','','')\">"
            ."[ICON]12 12 3512 Add New Event[/ICON]</a></div><br class='clr_b' />\n"
            ."      <div style='overflow:auto;height:320px;'>"
            .convert_safe_to_php($report['news-items']['html'])
            ."</div>\n"
            ."    </div>\n"
            ."    <div id='content_audio'>\n"
            ."      <h1 class='fl'>Manage Audio Podcasts</h1>\n"
            ."      <div class='fl' style='padding:3px 0 0 10px'>"
            ."<a href=\"javascript:details('community_member.dashboard.podcasts','',"
            ."'".$popup['podcasts']['h']."','".$popup['podcasts']['w']."','','')\">"
            ."[ICON]12 12 3512 Add New Podcast[/ICON]</a></div><br class='clr_b' />\n"
            ."      <div style='overflow:auto;height:320px;'>"
            .convert_safe_to_php($report['podcasts']['html'])
            ."</div>\n"
            ."    </div>\n"
            ."  </div>\n"
            ."  <div class='tabs'>\n"
            ."    <a id='tab_settings' href='#settings'><img id='tab_img_settings'"
            ." src='/img/spacer' style='height:68px;background-position:0% 0px'"
            ." title='View your Community Settings' /></a>\n"
            ."    <a id='tab_articles' href='#articles'><img id='tab_img_articles'"
            ." src='/img/spacer' style='height:69px;background-position:-200% -68px'"
            ." title='Manage Articles' /></a>\n"
            ."    <a id='tab_events'   href='#events'><img id='tab_img_events'"
            ." src='/img/spacer' style='height:66px;background-position:-200% -137px'"
            ." title='Manage Events' /></a>\n"
            ."    <a id='tab_news'     href='#news'><img id='tab_img_news'"
            ." src='/img/spacer' style='height:55px;background-position:-200% -203px'"
            ." title='Manage News Items' /></a>\n"
            ."    <a id='tab_audio'    href='#podcasts'><img id='tab_img_audio'"
            ." src='/img/spacer' style='height:75px;background-position:-200% -258px'"
            ." title='Manage Podcasts' /></a>\n"
            ."  </div>\n"
            ."    <input style='width:73px;left:8px;position:relative;top:350px;'"
            ." type='button' onclick='hidePopWin(null)' value='Close' class='formButton' />\n"
            ."  <div class='clr_b'></div>"
            ."</div>";
        $out['html'] = convert_safe_to_php($out['html']);
        foreach (Page::$css_colors as $idx => $style) {
            $this->module_community_member_dashboard_css.=
                "['#module_community .color_".$idx."',"
               ."{ color: '#".$style['color']."', backgroundColor: '#".$style['bgcolor']."'}"
               ."],";
        }
        $out['css'] = $this->module_community_member_dashboard_css."]";
        $out['js'].=Page::pop_content('javascript')."\n".Page::pop_content('javascript_onload');
        return $out;
    }

    private function _draw_dashboard_settings()
    {
        $tab_width =    70;
        $label_width =  130;
        $field_width =  500;
        $memberID = $_SESSION['person']['memberID'];
        $this->_set_ID($memberID);
        $msg =          "<b>Success</b>: Loaded current Community Member Settings";
        $msg_status =   0;
        if (isset($_REQUEST['submode']) && $_REQUEST['submode']== "module_community_save") {
            $msg = "<b>Success</b>: Saved changes to your Community Member Settings";
            $msg_status =   0;
        }
        $record = $this->get_record();
        $this->xmlfields_decode($record);
        $section_tabs_arr = array(
            array('ID'=>'dashboard_general',  'label'=>'General',  'width'=>$tab_width),
            array('ID'=>'dashboard_services', 'label'=>'Services', 'width'=>$tab_width),
            array('ID'=>'dashboard_contact',  'label'=>'Contact',  'width'=>$tab_width),
            array('ID'=>'dashboard_partners', 'label'=>'Partners', 'width'=>$tab_width)
        );
        $selected_section = $section_tabs_arr[0]['ID'];
        $partner_sql = $this->get_partner_selector_sql($memberID);
        $out =
            "<div id='module_community_profile_settings'>"
            .HTML::draw_section_tabs(
                $section_tabs_arr,
                'module_community',
                $selected_section,
                "status_message_hide('form_status_module_community_settings')"
            )
            .HTML::draw_status('module_community_settings', $msg)
            ."<div class='clr_b'></div>"
            .draw_section_tab_div('dashboard_general', $selected_section)
            .Report_Column::draw_label('Church Name', 'Will be shown on your site', 'title', $label_width)
            .draw_form_field('title', $record['title'], 'text', $field_width)
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Denomination',
                'May be shown on your community pages depending on your other settings',
                'custom_1',
                $label_width
            )
            .draw_form_field(
                'custom_1',
                $record['custom_1'],
                'combo_listdata',
                $field_width,
                '',
                '',
                '',
                0,
                0,
                '',
                'module.community.lst_denomination'
            )
            ."<div class='clr_b'></div>"
/*
            .Report_Column::draw_label(
                'Languages Spoken',
                'May be shown on your community pages depending on your other settings',
                'custom_1',
                $label_width
            )
            .draw_form_field(
                'custom_1',
                $record['custom_2'],
                'selector_listdata_csv',
                $field_width,
                '',
                '',
                '',
                0,
                0,
                '',
                'module.community.lst_languages'
            )
            ."<div class='clr_b'></div>"
*/
            .Report_Column::draw_label(
                'Address',
                'Will be shown on your site',
                'service_addr_line1',
                $label_width
            )
            .draw_form_field(
                'service_addr_line1',
                $record['service_addr_line1'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                '&nbsp;',
                '',
                '',
                $label_width
            )
            .draw_form_field(
                'service_addr_line2',
                $record['service_addr_line2'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Town / City',
                'Will be shown on your profile',
                'service_addr_city',
                $label_width
            )
            .draw_form_field(
                'service_addr_city',
                $record['service_addr_city'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'State / Province',
                'Will be shown on your profile',
                'service_addr_sp',
                $label_width
            )
            .draw_form_field(
                'service_addr_sp',
                $record['service_addr_sp'],
                'combo_listdata',
                $field_width,
                '',
                '',
                '',
                0,
                0,
                '',
                'lst_sp'
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Postal Code',
                'Will be shown on your profile',
                'service_addr_postal',
                $label_width
            )
            .draw_form_field(
                'service_addr_postal',
                $record['service_addr_postal'],
                'text',
                60
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Country',
                'Will be shown on your profile',
                'service_addr_country',
                $label_width
            )
            .draw_form_field(
                'service_addr_country',
                $record['service_addr_country'],
                'selector_listdata',
                $field_width,
                '',
                '',
                '',
                0,
                0,
                '',
                'lst_country'
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Map Location',
                'Used to position marker on map',
                'service_map_loc',
                $label_width
            )
            .draw_form_field(
                'service_map_loc',
                $record['service_map_loc'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Latitude',
                'Set for you automatically from Map Location',
                'service_map_lat',
                $label_width
            )
            ."<div style='float:left;width:60px;border:1px solid #ccc;background-color:#f0f0f0;'>"
            .$record['service_map_lat']
            ."</div>"
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Longitude',
                'Set for you automatically from Map Location',
                'service_map_lon',
                $label_width
            )
            ."<div style='float:left;width:60px;border:1px solid #ccc;background-color:#f0f0f0;'>"
            .$record['service_map_lon']
            ."</div>"
            ."<div class='clr_b' /></div>"
            ."</div>"
            .draw_section_tab_div('dashboard_services', $selected_section);
        $days = explode(',', 'Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
        foreach ($days as $day) {
            $out.=
                 Report_Column::draw_label($day, '', 'weekly_'.strToLower(substr($day, 0, 3)), $label_width)
                .draw_form_field(
                    'weekly_'.strToLower(substr($day, 0, 3)),
                    (isset($record['weekly_'.strToLower(substr($day, 0, 3))]) ?
                        $record['weekly_'.strToLower(substr($day, 0, 3))]
                    :
                        ""
                    ),
                    'textarea',
                    $field_width,
                    '',
                    0,
                    '',
                    0,
                    0,
                    '',
                    '',
                    40
                )
                ."<div class='clr_b'></div>";
        }
        $out.=
             "</div>"
            .draw_section_tab_div('dashboard_contact', $selected_section)
            .Report_Column::draw_label(
                'Telephone',
                'Will be shown on your site',
                'contact_Telephone',
                $label_width
            )
            .draw_form_field(
                'contact_Telephone',
                $record['contact_Telephone'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Official Website',
                'Will be shown on your site if given',
                'link_website',
                $label_width
            )
            .draw_form_field(
                'link_website',
                $record['link_website'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Facebook Page',
                'Will be shown on your site if given',
                'link_facebook',
                $label_width
            )
            .draw_form_field(
                'link_facebook',
                $record['link_facebook'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Twitter Handle',
                'Will be shown on your site if given',
                'link_twitter',
                $label_width
            )
            .draw_form_field(
                'link_twitter',
                $record['link_twitter'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label(
                'Video Channel',
                'Will be shown on your site if given',
                'link_video',
                $label_width
            )
            .draw_form_field(
                'link_video',
                $record['link_video'],
                'text',
                $field_width
            )
            ."<div class='clr_b'></div>"
            ."<div class='txt_c' style='margin:5px;width:".($label_width+$field_width)."px;'><b>Office Hours</b></div>";
        foreach ($days as $day) {
            $out.=
                 Report_Column::draw_label($day, '', 'xml:Church_Office_'.substr($day, 0, 3), $label_width)
                .draw_form_field(
                    'xml:Church_Office_'.substr($day, 0, 3),
                    (isset($record['xml:Church_Office_'.substr($day, 0, 3)]) ?
                        $record['xml:Church_Office_'.substr($day, 0, 3)]
                    :
                        ""
                    ),
                    'text',
                    $field_width
                )
                ."<div class='clr_b'></div>";
        }
        $out.=
             "<div class='txt_c' style='margin:5px;width:".($label_width+$field_width)."px;'>"
            ."<b>Contact form Names and Email addresses</b></div>"
            ."<div class='contact_addresses'>\n"
            ."<div class='clr_b'></div>";
        for ($i=1; $i<=5; $i++) {
            $out.=
                Report_Column::draw_label(
                    'Name #'.$i,
                    'Will be used for Contact form',
                    'xml:contact:name_'.$i,
                    $label_width
                )
                ."<div class='fl'>"
                .draw_form_field(
                    'xml:contact:name_'.$i,
                    (isset($record['xml:contact:name_'.$i]) ?
                        $record['xml:contact:name_'.$i]
                    :
                        ""
                    ),
                    'text',
                    (int)($field_width/2)-35
                )
                ."</div>"
                .Report_Column::draw_label(
                    'Email',
                    '',
                    'xml:contact:email_'.$i
                )
                ."<div class='fl'>"
                .draw_form_field(
                    'xml:contact:email_'.$i,
                    (isset($record['xml:contact:email_'.$i]) ?
                        $record['xml:contact:email_'.$i]
                    :
                        ""
                    ),
                    'text',
                    (int)($field_width/2)-35
                )
                ."</div>"
                ."<div class='clr_b'></div>";
        }
        $out.=
            "</div>"
            ."</div>"
            .draw_section_tab_div('dashboard_partners', $selected_section)
            .Report_Column::draw_label(
                'Partners',
                'Accept shared postings from these members\nChoose at least the number of partners specified in'
                .' \'Minimum Partners\' to qualify for this service',
                'partner_csv',
                $label_width
            )
            ."<div id='div_partner_csv'>"
            .draw_form_field(
                'partner_csv',
                $record['partner_csv'],
                'checkbox_sql_csv',
                $field_width,
                $partner_sql,
                '',
                '',
                0,
                0,
                '',
                '',
                260
            )
            ."<br />"
            ."</div>"
            ."</div>"
            ."</div>"
            ."<div style='width:60px;height:25px;margin:auto'>\n"
            ."  <input type='button' id='module_community_update' class='formButton' value='Update'"
            ." onclick=\"this.disabled=true;save_community_settings()\" />\n"
            ."</div>\n";
        return $out;
    }

    public function export_sql($targetID, $show_fields)
    {
        $header =
             "Selected ".$this->_get_object_name().$this->plural($targetID)."\n"
            ."with Membership and postings";
        $extra_delete =
             "DELETE FROM `person`                 WHERE `ID` IN("
            ."SELECT `contactID` FROM `community_member` WHERE `ID` IN (".$targetID.") AND `systemID` IN (1,".SYS_ID.")"
            .") AND `systemID` IN (1,".SYS_ID.");\n"
            ."DELETE FROM `community_membership`   WHERE `memberID` IN (".$targetID.");\n"
            ."DELETE FROM `category_assign`        WHERE `assignID` IN ("
            ."SELECT `ID` FROM `postings` where `memberID` IN(".$targetID.")"
            .");\n"
            ."DELETE FROM `postings`               WHERE `memberID` IN (".$targetID.");\n";
        $extra_select =
            Backup::db_export_sql_query(
                "`community_membership`  ",
                "SELECT * FROM `community_membership` WHERE `memberID` IN(".$targetID.") ORDER BY `memberID`",
                $show_fields
            )
            .Backup::db_export_sql_query(
                "`category_assign`       ",
                "SELECT * FROM `category_assign` WHERE `assignID` IN ("
                ."SELECT `ID` FROM `postings` where `memberID` IN(".$targetID.")"
                .");\n",
                $show_fields
            )
            .Backup::db_export_sql_query(
                "`postings`              ",
                "SELECT * FROM `postings` WHERE `memberID` IN(".$targetID.") ORDER BY `memberID`",
                $show_fields
            )
            .Backup::db_export_sql_query(
                "`person`                ",
                "SELECT * FROM `person` WHERE `ID` IN ("
                ."SELECT `contactID` FROM `community_member` WHERE `ID` IN (".$targetID.") AND "
                ."`systemID` IN (1,".SYS_ID.")"
                .") AND `systemID` IN (1,".SYS_ID.")",
                $show_fields
            );
        return parent::sql_export($targetID, $show_fields, $header, '', $extra_delete, $extra_select);
    }

    public function get_member_profile($community_name, $member_name)
    {
        $sql =
             "SELECT\n"
            ."  `community`.`ID` `communityID`,\n"
            ."  CONCAT(`community`.`URL_external`,'/',`community_member`.`name`) `URL_external`,\n"
            ."  COALESCE(\n"
            ."    (SELECT\n"
            ."      `shortform_name`\n"
            ."    FROM\n"
            ."      `community_member` `cm2`\n"
            ."    WHERE\n"
            ."      `cm2`.`ID` = `community_member`.`primary_ministerialID`\n"
            ."    ),\n"
            ."    ''\n"
            ."  ) `ministerial_title`,\n"
            ."  COALESCE(\n"
            ."    (SELECT\n"
            ."      `name`\n"
            ."    FROM\n"
            ."      `community_member` `cm2`\n"
            ."    WHERE\n"
            ."      `cm2`.`ID` = `community_member`.`primary_ministerialID`\n"
            ."    ),\n"
            ."    ''\n"
            ."  ) `ministerial_name`,\n"
            ."  `community_member`.*\n"
            ."FROM\n"
            ."  `community`\n"
            ."INNER JOIN `community_membership` ON\n"
            ."  `community`.`ID` = `community_membership`.`communityID`\n"
            ."INNER JOIN `community_member` ON\n"
            ."  `community_membership`.`memberID` = `community_member`.`ID`\n"
            ."WHERE\n"
            ."  `community`.`name` = \"".$community_name."\" AND\n"
            ."  `community_member`.`name` = \"".$member_name."\"";
        if (!$this->_record = $this->get_record_for_sql($sql)) {
            return false;
        };
        $this->_set_ID($this->_record['ID']);
        $this->xmlfields_decode($this->_record);
        return true;
    }

    public function get_member_profile_images()
    {
        $Obj_GA = new Gallery_Album;
        $path =   '//communities/'.$this->_community_record['name'].'/members/'.$this->_record['name'].'/profile';
        if ($ID = $Obj_GA->get_ID_by_path($path)) {
            return $Obj_GA->get_images($ID);
        }
        return false;
    }

    public function get_sponsors_national()
    {
        $Obj_GA =   new Gallery_Album;
        $path =     "//sponsors/national";
        if ($ID = $Obj_GA->get_ID_by_path($path)) {
            return $Obj_GA->get_images($ID);
        }
        return false;
    }

    public function get_communities($memberID)
    {
        $sql =
         "SELECT\n"
        ."  `community`.`ID`,\n"
        ."  `community`.`name`,\n"
        ."  `community`.`title`\n"
        ."FROM\n"
        ."  `community`\n"
        ."INNER JOIN `community_membership` ON\n"
        ."  `community`.`ID` = `community_membership`.`communityID`\n"
        ."WHERE\n"
        ."  `community_membership`.`memberID`=".$memberID;
        $records = $this->get_records_for_sql($sql);
        $out = array();
        foreach ($records as $record) {
            $out[] = $record;
        }
        return $out;
    }

    public function get_coords($service_addr = false)
    {
        if (!$service_addr) {
            $service_address = $this->get_field('service_map_loc');
            $office_address =  $this->get_field('office_map_loc');
        }
        $s =    parent::get_coords($service_address);
        $o =    parent::get_coords($office_address);
        return
        array(
        'office_map_geocodeID' =>          $o['ID'],
        'office_map_geocode_area' =>       $o['match_area'],
        'office_map_geocode_type' =>       $o['match_type'],
        'office_map_geocode_quality' =>    $o['match_quality'],
        'office_map_lat' =>                $o['lat'],
        'office_map_lon' =>                $o['lon'],
        'service_map_geocodeID' =>         $s['ID'],
        'service_map_geocode_area' =>      $s['match_area'],
        'service_map_geocode_type' =>      $s['match_type'],
        'service_map_geocode_quality' =>   $s['match_quality'],
        'service_map_lat' =>               $s['lat'],
        'service_map_lon' =>               $s['lon']
        );
    }

    public function get_email_contacts()
    {
        $out = array();
        for ($i=1; $i<=8; $i++) {
            if (isset($this->_record['xml:contact:email_'.$i]) &&
            isset($this->_record['xml:contact:name_'.$i]) &&
            $this->_record['xml:contact:email_'.$i]!='' &&
            $this->_record['xml:contact:name_'.$i]!=''
            ) {
                $out[] =
                array(
                'idx' =>        $i,
                'email' =>      $this->_record['xml:contact:email_'.$i],
                'name' =>       $this->_record['xml:contact:name_'.$i]
                );
            }
        }
        return $out;
    }

    public function get_events_upcoming($category_csv = false)
    {
        $sql =
         "SELECT\n"
        ."  *\n"
        ."FROM\n"
        ."  `postings`\n"
        ."WHERE\n"
        ."  `systemID`=".SYS_ID." AND\n"
        ."  `type`='event' AND\n"
        .($category_csv!="" ?
            "  `category` REGEXP \"".implode("|", explode(',', str_replace(" ", "", $category_csv)))."\" AND\n"
         :
            ""
        )
        ."  `effective_date_start` >= '".date('Y')."-".date('m')."-".date('d')."' AND\n"
        ."  `memberID` = ".$this->_get_ID();
        return $this->get_records_for_sql($sql);
    }

    public function get_partner_selector_sql($memberID)
    {
        $communities = $this->get_communities($memberID);
        $sql = "";
        $seq = 0;
        foreach ($communities as $community) {
            $sql.=
                 ($seq==0 ? "" : "UNION ")
                ."SELECT\n"
                ."  ".($seq++)." `seq`,\n"
                ."  '' `value`,\n"
                ."  \"Members of '".$community['title']."'\" `text`,\n"
                ."  'ffffff' `color_background`,\n"
                ."  '000080' `color_text`,\n"
                ."   1 `isHeader`\n"
                ."UNION SELECT\n"
                ."  ".($seq++).",\n"
                ."  `community_member`.`ID`,\n"
                ."  CONCAT(\n"
                ."      `service_addr_country`,\n"
                ."      ' | ',\n"
                ."      IF(`service_addr_sp`!='', CONCAT(`service_addr_sp`,' | '), ''),\n"
                ."      `service_addr_city`,\n"
                ."      ' | ',\n"
                ."      '<a href=\"',\n"
                ."      `community`.`URL`,\n"
                ."      `community_member`.`name`,\n"
                ."      '\"',"
                ."      ' title=\"Denomination: ',\n"
                ."      `custom_1`,\n"
                ."      '\" style=\"color:#00f\" target=\"_blank\">',\n"
                ."      `community_member`.`title`,\n"
                ."      '</a>'\n"
                ."   ),\n"
                ."  'ffffff',\n"
                ."  '000000',\n"
                ."  0\n"
                ."FROM\n"
                ."  `community`\n"
                ."INNER JOIN `community_membership` ON\n"
                ."  `community`.`ID` = `community_membership`.`communityID`\n"
                ."INNER JOIN `community_member` ON\n"
                ."  `community_member`.`ID` = `community_membership`.`memberID`\n"
                ."WHERE\n"
                ."  `community_member`.`ID` NOT IN(".$memberID.") AND\n"
                ."  `community_membership`.`communityID`=".$community['ID']."\n";
        }
        $sql.=
             "ORDER BY\n"
            ."  `seq`,`text`\n";
        return $sql;
    }

    public function get_stats()
    {
        set_time_limit(600);    // Extend maximum execution time to 10 mins
        $r =        $this->_record;
        $start =    '2013-07-01';
        $end =      date('Y-m-d', time());
        $step =     '+1 month';
        $format =   'Y-m';
        $this->_stats_dates =    get_dates_in_range($start, $end, $step, $format);
        $find =
            BASE_PATH.trim($this->_community_record['URL'], '/').'|'
           .BASE_PATH.trim($this->_community_record['URL'], '/').'/'.trim($r['name'], '/');
        $links =    array();
        if ($r['link_website']) {
            $links[] = $r['link_website'];
        }
        if ($r['link_facebook']) {
            $links[] = $r['link_facebook'];
        }
        if ($r['link_twitter']) {
            $links[] = $r['link_twitter'];
        }
        if ($r['link_video']) {
            $links[] = $r['link_video'];
        }
        $links =    implode('|', $links);
        if ($r['stats_cache']) {
            $this->_stats = unserialize($r['stats_cache']);
            if (isset($this->_stats['cache_date']) && $this->_stats['cache_date']==$end) {
                return;
            }
            $YYYYMM = date('Y-m', time());
            if (isset($this->_stats[$YYYYMM])) {
                $Obj_Piwik = new Piwik;
                unset($this->_stats['cache_date']);
                $this->_stats[$YYYYMM] = array(
                'visits' => $Obj_Piwik->get_visit($YYYYMM, '', $find),
                'links' =>  $Obj_Piwik->get_outlink($YYYYMM, '', $links)
                );
                $this->_stats['cache_date'] = $end;
                $this->set_field('stats_cache', Record::escape_string(serialize($this->_stats), true, false));
                return;
            }
        }
        $Obj_Piwik = new Piwik;
        foreach ($this->_stats_dates as $YYYYMM) {
            $this->_stats[$YYYYMM] = array(
            'visits' => $Obj_Piwik->get_visit($YYYYMM, '', $find),
            'links' =>  $Obj_Piwik->get_outlink($YYYYMM, '', $links)
            );
        }
        $this->_stats['cache_date'] = $end;
        $this->set_field('stats_cache', Record::escape_string(serialize($this->_stats), true, false));
    }

    public function handle_report_copy(&$newID, &$msg, &$msg_tooltip, $name)
    {
        return parent::try_copy($newID, $msg, $msg_tooltip, $name);
    }

    public function on_action_pre_update_set_map_coordinates($reveal_modification = false)
    {
        global $action_parameters;
        $action_parameters;
        if ($action_parameters['sourceTrigger']!=='report_update_pre') {
            die("This component should be called only before a record update.");
        }
        $prefixes = array('service_','office_');
        $ID_arr = explode(',', str_replace(' ', '', $this->_get_ID()));
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $r = $this->load();
            foreach ($prefixes as $p) {
                if ($action_parameters['data'][$p.'map_loc']!==$r[$p.'map_loc']) {
                    $geocode = parent::get_coords($action_parameters['data'][$p.'map_loc']);
                    $data = array(
                    $p.'map_geocodeID' =>           $geocode['ID'],
                    $p.'map_geocode_area' =>        $geocode['match_area'],
                    $p.'map_geocode_type' =>        $geocode['match_type'],
                    $p.'map_geocode_quality' =>     $geocode['match_quality'],
                    $p.'map_lat' =>                 $geocode['lat'],
                    $p.'map_lon' =>                 $geocode['lon']
                    );
                    $this->update($data, false, $reveal_modification);
                }
            }
        }
    }

    public function on_action_member_setup()
    {
        $ID_arr = explode(',', str_replace(' ', '', $this->_get_ID()));
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $this->load();
            $this->xmlfields_decode($this->record);
            $this->_on_action_member_setup_community_membership();
            $this->_on_action_member_setup_addresses();
            $this->_on_action_member_setup_telephone();
            $this->_on_action_member_setup_map('service_');
            $this->_on_action_member_setup_map('office_');
            $this->_on_action_member_setup_contact();
            $this->_on_action_member_setup_gallery_album_root();
            $this->_on_action_member_setup_podcast_album_root();
            $this->_on_action_member_setup_featured_image();
        }
        $this->_set_ID(implode(',', $ID_arr));
    }

    protected function _on_action_member_setup_addresses()
    {
        $r = $this->record;
        $data = array();
        if ($r['name']!=='' && $r['shortform_name']=='') {
            $data['shortform_name'] = $r['title'];
        }
        if ($r['office_addr_line1']=='' && $r['service_addr_line1']!=='') {
            $data['office_addr_line1'] =      $r['service_addr_line1'];
            $data['office_addr_line2'] =      $r['service_addr_line2'];
            $data['office_addr_city'] =       $r['service_addr_city'];
            $data['office_addr_postal'] =     $r['service_addr_postal'];
            $data['office_addr_sp'] =         $r['service_addr_sp'];
            $data['office_addr_country'] =    $r['service_addr_country'];
        }
        if ($r['mailing_addr_line1']=='' && ($r['service_addr_line1']!=='' || $r['office_addr_line1']!=='')) {
            $source = ($r['office_addr_line1']!=='' ? 'office_addr_' : 'service_addr_');
            $data['mailing_addr_line1'] =      $r[$source.'line1'];
            $data['mailing_addr_line2'] =      $r[$source.'line2'];
            $data['mailing_addr_city'] =       $r[$source.'city'];
            $data['mailing_addr_postal'] =     $r[$source.'postal'];
            $data['mailing_addr_sp'] =         $r[$source.'sp'];
            $data['mailing_addr_country'] =    $r[$source.'country'];
        }
        if (count($data)) {
            $this->update($data, true, false);
            $this->load();
        }
    }

    protected function _on_action_member_setup_contact()
    {
        if (get_var('bulk_update')==1 && !get_var('contactID_apply')) {
            return;
        }
        $contact_changed = false;
        $r = $this->record;
        $data = array();
        $Obj_Contact = new Contact;
        if ($r['contactID']=='0') {
            if ($r['contact_PEmail']!='' || $r['contact_Telephone']!='') {
                $criteria =   array();
                if ($r['contact_PEmail']) {
                    $criteria['PEmail'] = $r['contact_PEmail'];
                }
                if ($r['contact_Telephone']) {
                    $criteria['WTelephone'] = $r['contact_Telephone'];
                }
                if ($lookup = $Obj_Contact->lookup($criteria)) {
                    if (count($lookup)==1) {
                        $this->set_field('contactID', $lookup[0]['ID'], true, false);
                    }
                    return;
                }
            }
            $data = array();
            if (
                $r['contact_NTitle']=='' &&
                $r['contact_NFirst']=='' &&
                $r['contact_NMiddle']=='' &&
                $r['contact_NLast']=='' &&
                $r['contact_NGreeting']==''
            ) {
                $data['contact_NTitle'] =       'Minister In Charge';
                $data['contact_NGreeting'] =    'Sir or Madam';
            }
            if ($r['contact_PEmail']=='' && $r['xml:contact:email_1']!='') {
                $data['contact_PEmail'] =       $r['xml:contact:email_1'];
            }
            if ($r['contact_Telephone']=='' && $r['office_phone1_num']!='') {
                $data['contact_Telephone'] =       $r['office_phone1_num'];
            }
            if ($r['contact_NGreeting']=='' && $r['contact_NFirst']!='') {
                $data['contact_NGreeting'] =       $r['contact_NFirst'];
            }
            if (count($data)) {
                $this->update($data, true, false);
                $this->load();
                $r = $this->record;
            }
            $data = array(
            'systemID' =>           SYS_ID,
            'NTitle' =>             addslashes($r['contact_NTitle']),
            'NFirst' =>             addslashes($r['contact_NFirst']),
            'NMiddle' =>            addslashes($r['contact_NMiddle']),
            'NLast' =>              addslashes($r['contact_NLast']),
            'NGreetingName' =>      addslashes($r['contact_NGreeting']),
            'PEmail' =>             addslashes($r['contact_PEmail']),
            'PUsername' =>          addslashes($r['contact_PEmail']),
            'WCompany' =>           addslashes($r['title']),
            'WAddress1' =>          addslashes($r['office_addr_line1']),
            'WAddress2' =>          addslashes($r['office_addr_line2']),
            'WCity' =>              addslashes($r['office_addr_city']),
            'WCountryID' =>         addslashes($r['office_addr_country']),
            'WMap_description' =>   addslashes($r['office_map_desc']),
            'WMap_lat' =>           addslashes($r['office_map_lat']),
            'WMap_lon' =>           addslashes($r['office_map_lon']),
            'WMap_location' =>      addslashes($r['office_map_loc']),
            'WPostal' =>            addslashes($r['office_addr_postal']),
            'WSpID' =>              addslashes($r['office_addr_sp']),
            'WTelephone' =>         addslashes($r['contact_Telephone'])
            );
            $ID = $Obj_Contact->insert($data);
            $this->set_field('contactID', $ID, true, false);
        } elseif (
        $r['contact_NTitle']=='' &&
        $r['contact_NFirst']=='' &&
        $r['contact_NMiddle']=='' &&
        $r['contact_NLast']=='' &&
        $r['contact_NGreeting']=='' &&
        $r['contact_PEmail']=='' &&
        $r['contact_Telephone']==''
        ) {
            $Obj_Contact = new Contact($r['contactID']);
            $Obj_Contact->load();
            $c = $Obj_Contact->record;
            $data = array(
            'contact_NTitle' =>     $c['NTitle'],
            'contact_NFirst' =>     $c['NFirst'],
            'contact_NMiddle' =>    $c['NMiddle'],
            'contact_NLast' =>      $c['NLast'],
            'contact_NGreeting' =>  $c['NGreetingName'],
            'contact_PEmail' =>     $c['PEmail'],
            'contact_Telephone' =>  $c['WTelephone']
            );
            $this->update($data, true, false);
        }
    }

    protected function _on_action_member_setup_community_membership()
    {
        $r = $this->record;
        if (!$r['primary_communityID']) {
            return;
        }
        $sql =
         "SELECT\n"
        ."  COUNT(*)\n"
        ."FROM\n"
        ."  `community_membership`\n"
        ."WHERE\n"
        ."  `memberID` = ".$r['ID']." AND\n"
        ."  `communityID` = ".$r['primary_communityID'];
        if ($this->get_field_for_sql($sql)) {
            return;
        }
        $Obj_C_M =  new Community_Membership;
        $data = array(
        'systemID' => SYS_ID,
        'memberID' => $r['ID'],
        'communityID' => $r['primary_communityID']
        );
        $Obj_C_M->insert($data);
    }

    protected function _on_action_member_setup_featured_image()
    {
        $r = $this->record;
        if ($r['featured_image']) {
            return;
        }
        if (!$r['gallery_albumID']) {
            return;
        }
        $Obj_GA =   new Gallery_Album($r['gallery_albumID']);
        if (!$image = $Obj_GA->get_field('thumbnail_small')) {
            return;
        }
        $this->set_field('featured_image', $image, true, false);
    }
    protected function _on_action_member_setup_gallery_album_root()
    {
        $r = $this->record;
        if ($r['gallery_albumID']) {
            return;
        }
        if (!$communityID = $r['primary_communityID']) {
            return;
        }
        $Obj_C = new Community($communityID);
        $Obj_C->load();
        if (!$parentID = $Obj_C->record['gallery_album_rootID']) {
            return;
        }
        $Obj_GA_P =   new Gallery_Album($parentID);
        $Obj_GA_P->load();
        $path =       $Obj_GA_P->record['path'].'/members';
        $parentID =   $Obj_GA_P->get_ID_by_path($path);
        $Obj_GA_P->_set_ID($parentID);
        $Obj_GA_P->load();
        $Obj_GA =     new Gallery_Album;
        if ($albumID = $Obj_GA->get_ID_by_path($path.'/'.$r['name'])) {
            $this->set_field('gallery_albumID', $albumID, true, false);
            return;
        }
        $data = array(
        'communityID' =>    $communityID,
        'container_path' => $path,
        'content' =>        'Gallery Album Sub Folder for '.$r['title'],
        'date' =>           date('Y-m-d', time()),
        'enabled' =>        1,
        'enclosure_url' =>  $Obj_GA_P->record['enclosure_url'].$r['name'].'/',
        'layoutID' =>       1,
        'memberID' =>       $r['ID'],
        'name' =>           $r['name'],
        'parentID' =>       $parentID,
        'path' =>           $path.'/'.$r['name'],
        'permPUBLIC' =>     1,
        'permSYSMEMBER' =>  1,
        'permSYSLOGON' =>   1,
        'systemID' =>       SYS_ID,
        'themeID' =>        1,
        'title' =>          $r['title'],
        );
        $albumID = $Obj_GA->insert($data);
        $this->set_field('gallery_albumID', $albumID, true, false);
        if (!$Obj_GA->get_ID_by_path($Obj_GA_P->record['path'].'/'.$r['name'].'/profile')) {
            $data = array(
            'communityID' =>    $communityID,
            'container_path' => $Obj_GA_P->record['path'].'/'.$r['name'],
            'content' =>        'Profile photos for '.$r['title'],
            'date' =>           date('Y-m-d', time()),
            'enabled' =>        1,
            'enclosure_url' =>  $Obj_GA_P->record['enclosure_url'].$r['name'].'/profile/',
            'layoutID' =>       1,
            'memberID' =>       $r['ID'],
            'name' =>           'profile',
            'parentID' =>       $albumID,
            'path' =>           $Obj_GA_P->record['path'].'/'.$r['name'].'/profile',
            'permPUBLIC' =>     1,
            'permSYSMEMBER' =>  1,
            'permSYSLOGON' =>   1,
            'systemID' =>       SYS_ID,
            'themeID' =>        1,
            'title' =>          'Profile Photos',
            );
            $Obj_GA->insert($data);
        }
    }

    protected function _on_action_member_setup_map($prefix)
    {
        static $countries = array();
        $location_changed = false;
        $r = $this->record;
        $data = array();
        if ($r[$prefix.'addr_country'] && !isset($countries[$r[$prefix.'addr_country']])) {
            $Obj_Country = new Country;
            $countries[$r[$prefix.'addr_country']] = $Obj_Country->get_text_for_value($r[$prefix.'addr_country']);
        }
        if (
            $r[$prefix.'map_desc']=='' &&
            ($r[$prefix.'addr_line1']||$r[$prefix.'addr_line2']||$r[$prefix.'addr_city']||$r[$prefix.'addr_postal'])
        ) {
            $data[$prefix.'map_desc'] =
            trim(
                str_replace(
                    array("  ","\r\n\r\n","\r\n "),
                    array(" ","\r\n","\r\n"),
                    title_case_string(
                        $r['title']
                    )."\r\n"
                    .title_case_string(
                        $r[$prefix.'addr_line1']."\r\n".$r[$prefix.'addr_line2']."\r\n".$r[$prefix.'addr_city']
                    )
                    ."\r\n".$r[$prefix.'addr_sp']." ".$r[$prefix.'addr_postal']." "
                    .$countries[$r[$prefix.'addr_country']]
                ),
                " \n"
            );
        }
        if (
            $r[$prefix.'map_loc']=='' &&
            ($r[$prefix.'addr_line1']||$r[$prefix.'addr_line2']||$r[$prefix.'addr_city']||$r[$prefix.'addr_postal'])
        ) {
            $data[$prefix.'map_loc'] =
            trim(
                str_replace(
                    "  ",
                    " ",
                    title_case_string(
                        $r[$prefix.'addr_line1']." ".$r[$prefix.'addr_line2']." ".$r[$prefix.'addr_city']
                    )
                    ." ".$r[$prefix.'addr_sp']." ".$r[$prefix.'addr_postal']
                    .($r[$prefix.'addr_sp']=='PR' ? '' : ' '.$countries[$r[$prefix.'addr_country']])
                ),
                " "
            );
            $location_changed = true;
        }
        if ($data) {
            $this->update($data, true, false);
        }
        if ($location_changed) {
            $coords = $this->get_coords();
            $this->update($coords, true, false);
            $Obj_C = new Community($r['primary_communityID']);
            $data =  $Obj_C->get_bounding_box();
            $Obj_C->update($data, false, false);
        }
    }

    protected function _on_action_member_setup_podcast_album_root()
    {
        $r = $this->record;
        if ($r['podcast_albumID']) {
            return;
        }
        if (!$communityID = $r['primary_communityID']) {
            return;
        }
        $Obj_C = new Community($communityID);
        $Obj_C->load();
        if (!$parentID = $Obj_C->record['podcast_album_rootID']) {
            return;
        }
        $Obj_PA_P = new Podcast_Album($parentID);
        $Obj_PA_P->load();
        $path =       $Obj_PA_P->record['path'];
        $parentID =   $Obj_PA_P->get_ID_by_path($path.'/members/');
        $Obj_PA_P->_set_ID($parentID);
        $Obj_PA_P->load();
        $Obj_PA =   new Podcast_Album;
        if ($ID = $Obj_PA->get_ID_by_path($Obj_PA_P->record['path'].'/'.$r['name'])) {
            $this->set_field('podcast_albumID', $ID, true, false);
        } else {
            $data = array(
            'communityID' =>    $communityID,
            'container_path' => $Obj_PA_P->record['path'],
            'content' =>        'Sermons by '.$r['title'],
            'date' =>           date('Y-m-d', time()),
            'enabled' =>        1,
            'enclosure_url' =>  $Obj_PA_P->record['enclosure_url'].$r['name'].'/',
            'layoutID' =>       1,
            'memberID' =>       $r['ID'],
            'name' =>           $r['name'],
            'parentID' =>       $parentID,
            'path' =>           $Obj_PA_P->record['path'].'/'.$r['name'],
            'permPUBLIC' =>     1,
            'permSYSMEMBER' =>  1,
            'permSYSLOGON' =>   1,
            'systemID' =>       SYS_ID,
            'themeID' =>        1,
            'title' =>          $r['title'],
            );
            $ID = $Obj_PA->insert($data);
            $this->set_field('podcast_albumID', $ID, true, false);
        }
    }

    public function _on_action_member_setup_telephone()
    {
        $r = $this->record;
        $data = array();
        if ($r['office_phone1_num']) {
            $data['office_phone1_num'] = format_phone($r['office_phone1_num']);
        }
        if ($r['office_phone2_num']) {
            $data['office_phone2_num'] = format_phone($r['office_phone2_num']);
        }
        if (count($data)) {
            $this->update($data, false, false);
        }
    }

    public function set_up_member_page_vars(&$page_vars)
    {
        $page_vars['path_real'] =   $page_vars['path'];
        if ($page_vars['path_extension']=='') {
            return;
        }
        if (substr($page_vars['path_extension'], 0, 3)=='js/') {
            return;
        }
        if (substr($page_vars['path_extension'], 0, 4)=='rss/') {
            return;
        }
        if ($page_vars['path_extension']=='sermons' || substr($page_vars['path_extension'], 0, 8)=='sermons/') {
            $page_vars['title']=       'Sermons from '.$page_vars['title'];
            $page_vars['path'] .=       $page_vars['path_extension'];
            return;
        }
      // Uses 'bare-metal' techniques - this has to operate BEFORE ECL tags are converted
        $page_vars['include_title_heading'] =   0;
        $params =       explode(OPTION_SEPARATOR, $page_vars['component_parameters']);
        $idx_name =     'participating_churches:module_community.community_name=';
        foreach ($params as $key => $value) {
            if (substr($value, 0, strlen($idx_name))==$idx_name) {
                $community_name = substr($value, strlen($idx_name));
                break;
            }
        }
        $idx_name =     'participating_churches:module_community.profile_page_layout=';
        foreach ($params as $key => $value) {
            if (substr($value, 0, strlen($idx_name))==$idx_name) {
                $layout_name = substr($value, strlen($idx_name));
                break;
            }
        }
        $path_extension_arr =   explode('/', $page_vars['path_extension']);
        $member_name =          array_shift($path_extension_arr);
        $page =                 implode('/', $path_extension_arr);   // Not used here
        $Obj_community =        new Community;
        $Obj_community->set_ID_by_name($community_name);
        $community_title =  $Obj_community->get_field('title');
        $this->get_member_profile($community_name, $member_name);
        if (!$this->_record) {
            $page_vars['content'] =
                "<h2>Sorry!</h2><p>The <b>".$community_title."</b> community does not recognise this member.";
            return;
        }
        $page_vars['title'] =       htmlentities($this->_record['title']);
        $page_vars['path'] .=       $page_vars['path_extension'];
        $Obj_Layout =               new Layout;
        $page_vars['layoutID'] =    $Obj_Layout->get_ID_by_name($layout_name);
        $Obj_Layout->_set_ID($page_vars['layoutID']);
        $page_vars['layout_component_parameter'] = $Obj_Layout->get_field('component_parameters');
    }

    public function get_version()
    {
        return COMMUNITY_MEMBER_VERSION;
    }
}
