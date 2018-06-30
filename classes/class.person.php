<?php
define('VERSION_PERSON', '1.0.122');
/*
Version History:
  1.0.122 (2015-02-17)
    1) Added support for communityId field in FIELDS list
    2) Now PSR-2 Compliant

  (Older version history in class.person.txt)
*/
class Person extends Displayable_Item
{
    const FIELDS = 'ID, archive, archiveID, about, deleted, type, about, active_date_from, active_date_to, AAddress1, AAddress2, ACellphone, ACity, ACountryID, AEmail, AFacebook, AFax, AGooglePlus, ALinkedIn, AMap_description, AMap_geocodeID, AMap_geocode_area, AMap_geocode_quality, AMap_geocode_type, AMap_lat, AMap_lon, AMap_location, APostal, ASpID, ATelephone, ATwitter, AWeb, AYoutube, avatar, category, communityID, custom_1, custom_2, custom_3, custom_4, custom_5, custom_6, custom_7, custom_8, custom_9, custom_10, custom_11, custom_12, custom_13, custom_14, custom_15, custom_16, custom_17, custom_18, custom_19, custom_20, custom_21, custom_22, custom_23, custom_24, custom_25, custom_26, custom_27, custom_28, custom_29, custom_30, groups_list, image, keywords, memberID, module_creditsystem_balance, NDob, NFirst, NGender, NGreetingName, NLast, NMiddle, NProfDes, NTitle, notes, notes2, notes3, notes4, PFMWebUsername, PFMWebPassword, PEmail, PLogonCount, PLogonLastDate, PLogonLastHost, PLogonLastIP, PLogonLastMethod, PMemberType, PPassword, PUsername, PPrefLang, PSearchListing, PWidgets_csv, permACTIVE, permCOMMUNITYADMIN, permMASTERADMIN, permSYSADMIN, permSYSAPPROVER, permSYSEDITOR, permSYSMEMBER, permUSERADMIN, privacy_about, privacy_address_home, privacy_address_work, privacy_cell_home, privacy_cell_work, privacy_email_home, privacy_email_work, privacy_phone_home, privacy_phone_work, privacy_web_home, privacy_web_work, profile_locked, qb_ident, qb_name, process_maps, systemID, tax_codeID, WAddress1, WAddress2, WBusinessType, WCellphone, WCity, WCompany, WCountryID, WDepartment, WDivision, WEmail, WFacebook, WFax, WGooglePlus, WJobTitle, WLinkedIn, WMap_description, WMap_geocodeID, WMap_geocode_area, WMap_geocode_quality, WMap_geocode_type, WMap_lat, WMap_lon, WMap_location, WPostal, WSpID, WTelephone, WTelephoneExt, WTwitter, WWeb, WYoutube, XML_data, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

    public function __construct($ID = "")
    {
        parent::__construct("person", $ID);
        $this->_set_name_field('PUsername');
        $this->_set_assign_type('person');
        $this->_set_object_name('Person');
        $this->_set_has_categories(true);
        $this->_set_has_keywords(true);
        $this->_set_message_associated('and associated Group Membership records have');
        $this->set_edit_params(
            array(
                'report' =>                 'contact:contact,user:user',
                'report_rename' =>          true,
                'report_rename_label' =>    'new username'
            )
        );
        $this->_cp_vars_listings = array(
            'background' =>               array(
                'match' =>      'hex3|',
                'default' =>    '',
                'hint' =>       'Hex code for background colour to use'
            ),
            'block_layout' =>             array(
                'match' =>      '',
                'default' =>    'Person',
                'hint' =>       'Name of Block Layout to use'
            ),
            'box' =>                      array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2'
            ),
            'box_footer' =>               array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text below displayed Events'
            ),
            'box_header' =>               array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text above displayed Events'
            ),
            'box_rss_link' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title' =>                array(
                'match' =>      '',
                'default' =>    'People',
                'hint' =>       'text'
            ),
            'box_title_link' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title_link_page' =>      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'page'
            ),
            'box_width' =>                array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..x'
            ),
            'filter_category_list' =>     array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       '*|CSV value list'
            ),
            'filter_category_master' =>   array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'filter_groups_list' =>       array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'format_name' =>              array(
                'match' =>      'enum_csv|NTitle,NFirst,NMiddle,NLast',
                'default' =>    'NTitle,NFirst,NMiddle,NLast',
                'hint' =>       'CSV list with any combination of NTitle,NFirst,NMiddle,NLast'
            ),
            'keywords_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'map_default_zoom' =>         array(
                'match' =>      'range|1,15',
                'default' =>    14,
                'hint' =>       'Map zoom to apply if there is only one point to show'
            ),
            'map_height' =>               array(
                'match' =>      'range|1,n',
                'default' =>    540,
                'hint' =>       'Height of map in pixels (if shown)'
            ),
            'map_width' =>                array(
                'match' =>      'range|1,n',
                'default' =>    540,
                'hint' =>       'Width of map in pixels (if shown)'
            ),
            'results_limit' =>            array(
                'match' =>      'range|0,n',
                'default' =>    '10',
                'hint' =>       '0..n'
            ),
            'results_order' =>            array(
                'match' =>      'enum|NFirst,NLast,PUsername',
                'default' =>    'NFirst',
                'hint' =>       'firstname|lastname|username'
            ),
            'results_paging' =>           array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2 - 1 for buttons, 2 for links'
            ),
            'show_about' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_address' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_address_map_link' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_cellphone' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_company' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_email' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_letter_anchors' =>      array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_map' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_name' =>                array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'show_phone' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'text_map_link' =>            array(
                'match' =>      '',
                'default' =>    'Map',
                'hint' =>       'Text to use in map link'
            )
        );
    }

    protected function _check_view_permissions($r, $type)
    {
        switch ($r[$type]){
            case '':
                return false;
            break;
            case 'A':
                return true;
            break;
        }
        if ($this->_current_user_rights['isPUBLIC']) {
          // Site or group - both invalid for public
            return false;
        }
        if ($r[$type]=='S' && $this->_current_user_rights['isSYSMEMBER']) {
          // Recognised approved Site Users
            return true;
        }
        if ($this->_current_user_groups_access_csv=='' || $r['groups']=='') {
            return false;
        }
        $visitor_groups = explode(',', $this->_current_user_groups_access_csv);
        $member_groups =  explode(',', $r['groups']);
        foreach ($visitor_groups as $v) {
            if (in_array($v, $member_groups)) {
                return true;
            }
        }
        return false;
    }

    public function _set_type($value)
    {
        $this->_type = $value;
        $this->_push_fixed_field('type', $value);
    }

    protected function _about()
    {
        if (isset($this->_cp['show_about']) && $this->_cp['show_about']=='0') {
            return;
        }
        if (!$this->_check_view_permissions($this->record, 'privacy_about')) {
            return;
        }
        return $this->record['about'];
    }

    protected function _address($prefix)
    {
        if (isset($this->_cp['show_address']) && $this->_cp['show_address']=='0') {
            return;
        }
        $type =     ($prefix=='A' ? 'home' : 'work');
        if (!$this->_check_view_permissions($this->record, 'privacy_address_'.$type)) {
            return;
        }
        $map_link = "";
        if ($this->_cp['show_address_map_link'] && $link=$this->_map_link($prefix)) {
            $map_link = " <span style='font-size:80%;color#00f;font-style:italic;'>(".$link.")</span>";
        }
        if (!(
            ($prefix=='W' && $this->record[$prefix.'Company'])||
            $this->record[$prefix.'Address1']||
            $this->record[$prefix.'Address2']||
            $this->record[$prefix.'City']||
            $this->record[$prefix.'SpID']||
            $this->record[$prefix.'Postal']||
            $this->record[$prefix.'CountryID']
        )) {
            return;
        }
        return
            ($prefix=='W' && $this->record[$prefix.'Company'] && $this->_cp['show_company'] ?
                $this->record[$prefix.'Company']."<br />\n"
             :
                ''
            )
            .($this->record[$prefix.'Address1'] ?  $this->record[$prefix.'Address1']."<br />\n" : '')
            .($this->record[$prefix.'Address2'] ?  $this->record[$prefix.'Address2']."<br />\n" : '')
            .($this->record[$prefix.'City'] ?      $this->record[$prefix.'City'].", " : '')
            .($this->record[$prefix.'SpID'] ?      $this->record[$prefix.'SpID']." " : '')
            .($this->record[$prefix.'Postal'] ?    $this->record[$prefix.'Postal'] : '')
            ."<br />\n"
            .($this->record[$prefix.'CountryID'] ? $this->record[$prefix.'CountryID'] : '')
            .$map_link;
    }

    protected function _cellphone($prefix)
    {
        if (isset($this->_cp['show_cellphone']) && $this->_cp['show_cellphone']=='0') {
            return;
        }
        $type =     ($prefix=='A' ? 'home' : 'work');
        if (!$this->_check_view_permissions($this->record, 'privacy_cell_'.$type)) {
            return;
        }
        return $this->record[$prefix.'Cellphone'];
    }

    protected function _email($prefix, $bare = false)
    {
        if (isset($this->_cp['show_email']) && $this->_cp['show_email']=='0') {
            return;
        }
        $type =     ($prefix=='A' ? 'home' : 'work');
        if (!$this->_check_view_permissions($this->record, 'privacy_email_'.$type)) {
            return;
        }
        if (!$result=$this->record[$prefix.'Email']) {
            return;
        }
        if ($bare) {
            return $url;
        }
        return get_emailAddressAsGif($result, 10, "000000");
    }

    protected function _facebook($prefix, $bare = false)
    {
        return $this->_social($prefix, 'Facebook', $bare);
    }

    protected function _fax($prefix)
    {
        if (isset($this->_cp['show_phone']) && $this->_cp['show_phone']=='0') {
            return;
        }
        $type =     ($prefix=='A' ? 'home' : 'work');
        if (!$this->_check_view_permissions($this->record, 'privacy_phone_'.$type)) {
            return;
        }
        return $this->record[$prefix.'Fax'];
    }

    protected function _linkedin($prefix, $bare = false)
    {
        return $this->_social($prefix, 'LinkedIn', $bare);
    }

    protected function _phone($prefix)
    {
        if (isset($this->_cp['show_phone']) && $this->_cp['show_phone']=='0') {
            return;
        }
        $type =     ($prefix=='A' ? 'home' : 'work');
        if (!$this->_check_view_permissions($this->record, 'privacy_phone_'.$type)) {
            return;
        }
        return $this->record[$prefix.'Telephone'];
    }

    protected function _map_link($prefix)
    {
        if (isset($this->_cp['show_map']) && $this->_cp['show_map']=='0') {
            return;
        }
        if (!($this->record[$prefix.'Map_lat'] && $this->record[$prefix.'Map_lon'])) {
            return;
        }
        $type =     ($prefix=='A' ? 'home' : 'work');
        if (!$this->_check_view_permissions($this->record, 'privacy_address_'.$type)) {
            return;
        }
        return
             "<a href='#google_map_".$this->_safe_ID."_frame'"
            ." title=\"Click to identify on Map\""
            ." onclick=\"ecc_map.point.i(_google_map_".$this->_safe_ID."_marker_".$prefix.$this->record['ID'].")\""
            .">"
            .$this->_cp['text_map_link']
            ."</a>";
    }

    protected function _social($prefix, $field, $bare = false)
    {
        if (isset($this->_cp['show_web']) && $this->_cp['show_web']=='0') {
            return;
        }
        $type =     ($prefix=='A' ? 'home' : 'work');
        if (!$this->_check_view_permissions($this->record, 'privacy_web_'.$type)) {
            return;
        }
        if (!$url = $this->record[$prefix.$field]) {
            return;
        }
        if (substr($url, 0, 4)!='http') {
            $url = "http://".$url;
        }
        if ($bare) {
            return $url;
        }
        return "<a href=\"".$url."\" rel=\"external\">".$url."</a>";
    }

    protected function _twitter($prefix, $bare = false)
    {
        return $this->_social($prefix, 'Twitter', $bare);
    }

    protected function _youtube($prefix, $bare = false)
    {
        return $this->_social($prefix, 'Youtube', $bare);
    }

    protected function _website($prefix, $bare = false)
    {
        return $this->_social($prefix, 'Web', $bare);
    }

    protected function BL_about()
    {
        if (!System::has_feature('show-about')) {
            return;
        }
        return $this->_about();
    }

    protected function BL_address()
    {
        if (!(System::has_feature('show-home-address') xor System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_address($prefix);
    }

    protected function BL_address_home()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'A';
        return $this->_address($prefix);
    }

    protected function BL_address_work()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'W';
        return $this->_address($prefix);
    }

    protected function BL_cellphone()
    {
        if (!(System::has_feature('show-home-address') xor System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_cellphone($prefix);
    }

    protected function BL_cellphone_home()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'A';
        return $this->_cellphone($prefix);
    }

    protected function BL_cellphone_work()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'W';
        return $this->_cellphone($prefix);
    }

    protected function BL_context_selection_start()
    {
        $canEdit =  (
            $this->record['ID'] &&
            $this->_current_user_rights['canEdit'] &&
            ($this->record['systemID']==SYS_ID || $this->_current_user_rights['isMASTERADMIN'])
        );
        if (!$canEdit) {
            return;
        }
        $canEditBlockLayout = (
            $this->_current_user_rights['isSYSADMIN'] &&
            isset($this->_block_layout['systemID']) &&
            (
                $this->_block_layout['systemID']==SYS_ID ||
                ($this->_current_user_rights['isMASTERADMIN'] && isset($this->_block_layout['ID']))
            )
        );
        return
            "<div onmouseover=\""
            ."if(!CM_visible('CM_".$this->_context_menu_ID."')) {"
            ."this.style.backgroundColor='"
            .($this->record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
            ."';"
            ."_CM.source='".$this->_safe_ID."';"
            ."_CM.type='".$this->record['type']."';"
            ."_CM.ID='".$this->record['ID']."';"
            ."_CM_text[0]='&quot;".str_replace(array("'","\""), '', $this->record['PUsername'])."&quot;';"
            ."_CM_text[1]=_CM_text[0];"
            .($canEditBlockLayout ?
                 "_CM_ID[3]='"
                .$this->_block_layout['ID']
                ."';"
                ."_CM_text[3]='&quot;".str_replace("'", '', $this->_block_layout['name'])."&quot;';"
              :
                ''
             )
            ."}\" "
            .($this->record['systemID']!=SYS_ID ?
                " title='This ".$this->record['type']." belongs to another site'"
              :
                ''
             )
            ." onmouseout=\"this.style.backgroundColor='';_CM.type=''\">";
    }

    protected function BL_distance()
    {
        if (isset($this->record['AMap_range']) && $this->record['AMap_range']!=100000) {
            return
                 $this->record['AMap_range'].' '
                .($this->_filter_radius_units=="km" ? "km" : "mile")
                .($this->record['AMap_range']==1 ? '' : 's');
        }
        if (isset($this->record['WMap_range']) && $this->record['WMap_range']!=100000) {
            return
                $this->record['WMap_range'].' '
               .($this->_filter_radius_units=="km" ? "km" : "mile")
               .($this->record['WMap_range']==1 ? '' : 's');
        }
    }

    protected function BL_email($bare = false)
    {
        if (!(System::has_feature('show-home-address') xor System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_email($prefix, $bare);
    }

    protected function BL_email_home($bare = false)
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'A';
        return $this->_email($prefix, $bare);
    }

    protected function BL_email_work($bare = false)
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'W';
        return $this->_email($prefix, $bare);
    }

    protected function BL_facebook($bare = false)
    {
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_facebook($prefix, $bare);
    }

    protected function BL_facebook_home($bare = false)
    {
        $prefix =   'A';
        return $this->_facebook($prefix, $bare);
    }

    protected function BL_facebook_work($bare = false)
    {
        $prefix =   'W';
        return $this->_facebook($prefix, $bare);
    }

    protected function BL_fax()
    {
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_fax($prefix);
    }

    protected function BL_fax_home()
    {
        $prefix =   'A';
        return $this->_fax($prefix);
    }

    protected function BL_fax_work()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'W';
        return $this->_fax($prefix);
    }

    protected function BL_linkedin($bare = false)
    {
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_linkedin($prefix, $bare);
    }

    protected function BL_linkedin_home($bare = false)
    {
        $prefix =   'A';
        return $this->_linkedin($prefix, $bare);
    }

    protected function BL_linkedin_work($bare = false)
    {
        $prefix =   'W';
        return $this->_linkedin($prefix, $bare);
    }

    protected function BL_map()
    {
        if (!$this->_cp['show_map']) {
            return;
        }
        if ($this->_current_user_rights['canEdit']) {
            $this->_popup['contact'] =  get_popup_size('contact');
            $this->_popup['user'] =     get_popup_size('user');
        }
        $this->_popup['contact'] =  get_popup_size('contact');
        $this->_popup['user'] =     get_popup_size('user');
        $this->_show_home = System::has_feature('show-home-address');
        $this->_show_work = System::has_feature('show-work-address');
        $this->_Obj_Map = new Google_Map($this->_safe_ID, SYS_ID);
        $this->_Obj_Map->add_control_type();
        $this->_Obj_Map->add_control_large();
        foreach ($this->_records as $r) {
            $this->_draw_item_map_point(
                $r,
                'home',
                (isset($this->_cp['marker_home']) ? $this->_cp['marker_home'] : '')
            );
            $this->_draw_item_map_point(
                $r,
                'work',
                (isset($this->_cp['marker_work']) ? $this->_cp['marker_work'] : '')
            );
        }
        if (!isset($this->_map_points) || !$this->_map_points) {
            return;
        }
        if (isset($this->_search_lat) && $this->_search_lat && $this->_search_lon) {
            $has_range_ring = (isset($this->_cp['show_range_ring']) && $this->_cp['show_range_ring'] ? 1 : 0);
            $has_start_ring = (isset($this->_cp['show_start_ring']) && $this->_cp['show_start_ring'] ? 1 : 0);
            if ($has_start_ring || $has_range_ring) {
                if ($has_start_ring) {
                    $start_radius =   (isset($this->_search_area) ? sqrt($this->_search_area/pi()) : 0);
                }
                if ($has_range_ring) {
                    $range_radius =
                        $this->_filter_radius_distance * ($this->_filter_radius_units=='km' ? 1000 : 1609.34);
                }
                if ($has_start_ring && !$has_range_ring) {
                    $rings = array('start');
                } elseif ($has_range_ring && !$has_start_ring) {
                    $rings = array('range');
                } else {
                    if ($start_radius>$range_radius) {
                        $rings = array('range','start');
                    } else {
                        $rings = array('start','range');
                    }
                }
                $message =
                     'Centre of searched area'
                    .($has_range_ring ? '\nGREEN area shows searched region' : '')
                    .($has_start_ring ? '\nRED area shows possible range of start location' : '');
                foreach ($rings as $ring) {
                    switch($ring){
                        case 'range':
                            $circle_id =
                            $this->_Obj_Map->add_circle(
                                $this->_search_lat,
                                $this->_search_lon,
                                $range_radius,
                                '#008000',
                                0.5,
                                0.8,
                                '#00ff00',
                                0.1,
                                $message,
                                'green'
                            );
                            break;
                        case 'start':
                            $circle_id =
                            $this->_Obj_Map->add_circle(
                                $this->_search_lat,
                                $this->_search_lon,
                                $start_radius,
                                '#ff0000',
                                1,
                                0.8,
                                '#ff0000',
                                0.1,
                                $message,
                                'red'
                            );
                            break;
                    }
                }
            }
        }
        $range = Google_Map::get_bounds($this->_map_points);
        if ($range) {
            $this->_Obj_Map->map_zoom_to_fit($range);
            $this->_Obj_Map->add_control_scale();
        } else {
            $this->_Obj_Map->map_centre(
                $this->_map_points[0]['map_lat'],
                $this->_map_points[0]['map_lon'],
                $this->_cp['map_default_zoom']
            );
        }
        $args = array(
            'map_width' =>  $this->_cp['map_width'],
            'map_height' => $this->_cp['map_height']
        );
        return
             "<div class='google_map_frame' id='google_map_".$this->_safe_ID."_frame'>\n"
            .$this->_Obj_Map->draw($args)
            ."</div>\n<br class='clear' /><br />";
    }

    protected function BL_map_link()
    {
        if (!(System::has_feature('show-home-address') xor System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_map_link($prefix);
    }

    protected function BL_map_link_home()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'A';
        return $this->_map_link($prefix);
    }

    protected function BL_map_link_work()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'W';
        return $this->_map_link($prefix);
    }

    protected function BL_name()
    {
        if (isset($this->_cp['show_name']) && $this->_cp['show_name']=='0') {
            return;
        }
        $parts = explode(',', $this->_cp['format_name']);
        $out = array();
        foreach ($parts as $p) {
            $t = trim($this->record[$p]);
            if ($t) {
                $out[] = $t;
            }
        }
        return implode(' ', $out);
    }

    protected function BL_phone()
    {
        if (!(System::has_feature('show-home-address') xor System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_phone($prefix);
    }

    protected function BL_phone_home()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'A';
        return $this->_phone($prefix);
    }

    protected function BL_phone_work()
    {
        if (!(System::has_feature('show-home-address') && System::has_feature('show-work-address'))) {
            return;
        }
        $prefix =   'W';
        return $this->_phone($prefix);
    }

    protected function BL_twitter($bare = false)
    {
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_twitter($prefix, $bare);
    }

    protected function BL_twitter_home($bare = false)
    {
        $prefix =   'A';
        return $this->_twitter($prefix, $bare);
    }

    protected function BL_twitter_work($bare = false)
    {
        $prefix =   'W';
        return $this->_twitter($prefix, $bare);
    }

    protected function BL_youtube($bare = false)
    {
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_youtube($prefix, $bare);
    }

    protected function BL_youtube_home($bare = false)
    {
        $prefix =   'A';
        return $this->_youtube($prefix, $bare);
    }

    protected function BL_youtube_work($bare = false)
    {
        $prefix =   'W';
        return $this->_youtube($prefix, $bare);
    }

    protected function BL_website($bare = false)
    {
        $prefix =   (System::has_feature('show-home-address') ? 'A' : 'W');
        return $this->_website($prefix, $bare);
    }

    protected function BL_website_home($bare = false)
    {
        $prefix =   'A';
        return $this->_website($prefix, $bare);
    }

    protected function BL_website_work($bare = false)
    {
        $prefix =   'W';
        return $this->_website($prefix, $bare);
    }

    protected function _draw_item_map_point($r, $type, $icon = '')
    {
        if (!$this->_cp['show_address']) {
            return;
        }
        if (!($type=='home' ? $this->_show_home : $this->_show_work)) {
            return;
        }
        if (!$this->privacy_check_viewer_access($r, 'privacy_address_'.$type)) {
            return;
        }
        $prefix =       ($type=='home' ? 'A' : 'W');
        if ($r[$prefix.'Map_lat']==0 && $r[$prefix.'Map_lon']==0) {
            return;
        }
        $this->_map_points[] = array(
            'map_lat' =>  $r[$prefix.'Map_lat'],
            'map_lon' =>  $r[$prefix.'Map_lon']
        );
        $description = str_replace(array("\r\n","\r","\n"), '<br />', trim($r[$prefix.'Map_description']));
        $description_arr = explode("<br />", $description);
        $description =
             "<a href='#item_".$r['ID']."'"
            .($this->_current_user_rights['canEdit'] ?
                " onclick=\\\"details('".$r['type']."',".$r['ID'].","
                .$this->_popup[$r['type']]['h'].",".$this->_popup[$r['type']]['w'].",'','');\\\""
              :
                ""
             )
            .">"
            ."<b>".array_shift($description_arr)."</b></a><br />"
            .implode("<br />", $description_arr);
        $this->_Obj_Map->add_marker_with_html(
            $r[$prefix.'Map_lat'],
            $r[$prefix.'Map_lon'],
            $description,
            $prefix.$r['ID'],
            false,
            false,
            $icon,
            (count($this->_records)==1 ? true : false),
            $r['name']
        );
    }

    protected function _draw_listings_setup($instance = '', $args = array(), $cp_are_fixed = false)
    {
        parent::_draw_listings_setup($instance, $args, $cp_are_fixed);
        $this->_setup_check_privacy_controls();
    }

    protected function _setup_check_privacy_controls()
    {
        if (!System::has_feature('person-privacy-controls')) {
            $this->_msg.="<b>Error:</b> This component requires privacy controls to be enabled.";
            throw new Exception;
        }
    }

    protected function _draw_listings_load_records()
    {
        global $YYYY, $MM;
        $results = $this->get_records(
            array(
                'filter_category' =>        $this->_cp['filter_category_list'],
                'filter_category_master' => (isset($this->_cp['filter_category_master']) ?
                    $this->_cp['filter_category_master']
                  :
                    false
                 ),
                'filter_groups_list' =>     (isset($this->_cp['filter_groups_list']) ?
                    $this->_cp['filter_groups_list']
                  :
                    ''
                 ),
                'results_limit' =>          $this->_cp['results_limit'],
                'results_offset' =>         $this->_filter_offset,
                'results_order' =>          (isset($this->_cp['results_order']) ?
                    $this->_cp['results_order']
                  :
                    'NLast'
                 )
            )
        );
        $this->_records =           $results['data'];
        $this->_records_total =     $results['total'];
    }

    protected function _draw_object_map_html_setup()
    {
        parent::_draw_object_map_html_setup();
        $this->_show_home =        sanitize(
            'range',
            get_var('show_home'),
            0,
            1,
            System::has_feature('show-home-address')
        );
        $this->_show_home_phone =  sanitize(
            'range',
            get_var('show_home_phone'),
            0,
            1,
            System::has_feature('show-home-address')
        );
        $this->_show_work =        sanitize(
            'range',
            get_var('show_work'),
            0,
            1,
            System::has_feature('show-work-address')
        );
        $this->_show_work_phone =  sanitize(
            'range',
            get_var('show_work_phone'),
            0,
            1,
            System::has_feature('show-home-address')
        );
        if ($this->_current_user_rights['canEdit']) {
            $edit_params =            $this->get_edit_params();
            $this->_form =            array();
            $this->_form['contact'] = array('contact', get_popup_size('contact'));
            $this->_form['user'] =    array('user', get_popup_size('user'));
        }
    }

    protected function _draw_object_map_html_get_data()
    {
        if (!$this->load()) {
            return;   // No persons matched criteria
        }
        if (!$this->_show_home && !$this->_show_work) {
            return;   // Nothing to show
        }
        if ($this->_show_home && !$this->_show_work) {
            $lat_field_arr =    array('AMap_lat');
            $lon_field_arr =    array('AMap_lon');
            $loc_field_arr =    array('AMap_location');
            $des_field_arr =    array('AMap_description');
            $area_field_arr =   array('AMap_geocode_area');
        }
        if (!$this->_show_home && $this->_show_work) {
            $lat_field_arr =    array('WMap_lat');
            $lon_field_arr =    array('WMap_lon');
            $loc_field_arr =    array('WMap_location');
            $des_field_arr =    array('WMap_description');
            $area_field_arr =   array('WMap_geocode_area');
        }
        if ($this->_show_home && $this->_show_work) {
            $lat_field_arr =    array('AMap_lat','WMap_lat');
            $lon_field_arr =    array('AMap_lon','WMap_lon');
            $loc_field_arr =    array('AMap_location','WMap_location');
            $des_field_arr =    array('AMap_description','WMap_description');
            $area_field_arr =   array('AMap_geocode_area','WMap_geocode_area');
        }
        for ($i=0; $i<count($lat_field_arr); $i++) {
            $lat_field =    $lat_field_arr[$i];
            $lon_field =    $lon_field_arr[$i];
            $area_field =   $area_field_arr[$i];
            $loc_field =    $loc_field_arr[$i];
            $des_field =    $des_field_arr[$i];
            $person_name = trim(
                ($this->record['NFirst'] ? $this->record['NFirst'].' ' : '')
                .($this->record['NLast'] ? $this->record['NLast'].' ' : '')
            );
            switch (substr($lat_field, 0, 4)){
                case 'AMap':
                    $map_icon = ($this->_show_work ? 'h' : '');
                    $map_name =
                    trim(
                        $person_name
                        .($this->_show_work ? ' [H]' : '')
                    );
                    break;
                case 'WMap':
                    $map_icon = ($this->_show_home ? 'w' : '');
                    $map_name =
                    trim(
                        $person_name
                        .($person_name!='' && $this->record['WCompany'] ? ' - ' : '')
                        .($this->record['WCompany'] ? strToUpper($this->record['WCompany']).' ' : '')
                        .($this->_show_home ? ' [W]' : '')
                    );
                    break;
            }
            if (
            isset($this->record[$lat_field]) &&
            isset($this->record[$lon_field]) &&
            isset($this->record[$loc_field]) &&
            ($this->record[$lat_field] || $this->record[$lon_field])
            ) {
                $map_location =     str_replace("\r\n", "\n", $this->record[$des_field]);
                if (trim($map_location=='')) {
                    $map_location =
                        $this->record['NFirst'].' '.$this->record['NLast']."\n".$this->record[$loc_field];
                }
                $map_location_arr = explode("\n", $map_location);
                $map_location =
                 "<div style='font-size:80%;white-space:nowrap'>"
                 ."<b>".array_shift($map_location_arr)."</b>"
                 .($this->_current_user_rights['canEdit'] ?
                      "<a class='edit' href='"
                     .BASE_PATH."details/".$this->_form[$this->record['type']][0]."/".$this->record['ID']."'"
                     ." onclick=\\\"details('".$this->_form[$this->record['type']][0]."','".$this->record['ID']."','"
                     .$this->_form[$this->record['type']][1]['h']."','".$this->_form[$this->record['type']][1]['w']
                     ."');return false;\\\">"
                     ."[Edit]</a>"
                   :
                     ""
                 )
                ."\n"
                .implode("\n", $map_location_arr)
                .($this->_show_home_phone && $this->record['ATelephone'] ?
                    "\n<b>H: ".$this->record['ATelephone']."</b>"
                  :
                    ""
                 )
                .($this->_show_work_phone && $this->record['WTelephone'] ?
                    "\n<b>W: ".$this->record['WTelephone']."</b>"
                  :
                    ""
                 )
                ."</div>";
                $this->_data_items[] = array(
                    'ID' =>       $this->record['ID'].'_'.strToLower(substr($lat_field, 0, 1)),
                    'map_lat' =>  $this->record[$lat_field],
                    'map_lon' =>  $this->record[$lon_field],
                    'map_area' => $this->record[$area_field],
                    'map_loc' =>  $map_location,
                    'map_icon' => $map_icon,
                    'map_name' => $map_name,
                    'record' =>   $this->record
                );
            }
        }
    }

    public function delete()
    {
        $sql =
             "DELETE FROM\n"
            ."  `group_members`\n"
            ."WHERE\n"
            ."  `personID` IN (".$this->_get_ID().")";
        $this->do_sql_query($sql);
        parent::delete();
    }

    public function do_email_signup($template_name = 'user_signup')
    {
        $Obj_MT = new Mail_Template;
        $mailtemplateID =   $Obj_MT->get_ID_by_name($template_name);
        if (!$mailtemplateID) {
            do_log(
                3,
                __CLASS__."::".__FUNCTION__.'()',
                '(none)',
                'Invalid template specified - \''.$template_name.'\''
            );
            return false;
        }
        $Obj_MT->_set_ID($mailtemplateID);
        $result = $Obj_MT->send_email($this->_get_ID());
        do_log(
            (substr($result, 0, 11)=='Message-ID:' ? 0 : 2),
            __CLASS__."::".__FUNCTION__.'()',
            '(none)',
            'Signup for '.$this->_get_ID().' using template \''.$template_name.'\' '
            .'(ID:'.$this->_get_ID().') - result: '.$result
        );
        return $result;
    }

    public function do_group_unsubscribe()
    {
        global $page_vars,$targetID,$targetValue,$email;
        global $submode;
        $ident =        "group_unsubscribe";
        $parameter_spec = array(
            'groups' =>           array(
                'default' =>    '*',
                'hint' =>       'group1,group2,group3|*'
            ),
            'shadow' =>           array(
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'steps' =>            array(
                'default' =>    '1',
                'hint' =>       '1|2 (1:instant, 2:email confirm)'
            ),
            'template_confirm' => array(
                'default' =>    'unsubscribe_confirm',
                'hint' =>       'template name - only for 2-step process'
            ),
            'template_done' =>    array(
                'default' =>    'unsubscribe_done',
                'hint' =>       'template name'
            )
        );
        $cp_settings =  Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
        $cp_defaults =  $cp_settings['defaults'];
        $cp =           $cp_settings['parameters'];
        $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
        switch ($submode) {
            case "confirm":
                $out.= "<p>The two-stage optout model is not yet implemented.</p>";
                break;
            case "unsubscribe":
                if ($targetID!='') {
                    $Obj = new Group($targetID);
                    if (!$Obj->exists()) {
                        $out.=
                        status_message(
                            2,
                            true,
                            'group',
                            '',
                            'There is no such group',
                            $targetID
                        );
                    } else {
                        $perms_arr =
                        array(
                            'permEMAILRECIPIENT' => 0,
                            'permEMAILOPTOUT' =>    1
                        );
                        $Obj->member_assign($targetValue, $perms_arr);
                        $group_name = $Obj->get_name();
                        $perms_arr = $Obj->member_perms($targetValue);
                        $relevant = false;
                        foreach ($perms_arr as $perm => $value) {
                            if ($value) {
                                $relevant = true;
                            }
                        }
                        if ($relevant) {
                            $out.= "Unsubscribed from email sent to ".$group_name."<br /><br />\n";
                        } else {
                            $out.= "Unsubscribed from ".$group_name."<br /><br />\n";
                            $Obj->member_unassign($targetValue);
                        }
                    }
                }
                break;
        }
        $out.=
             "<h3>Unsubscribe</h3>\n"
            ."<p>This page allows you to unsubscribe from any of the groups you are currently subscribed "
            ."to as an email recipient.</p>\n"
            .($cp['steps']==1 ?
                ""
             :
                "<p>This is a two-step process - "
               ."a confirmation email will be sent to allow you to confirm your choice.</p>"
            );
        if ($email=='') {
            $targetValue = get_userID();
            if ($targetValue) {
                $Obj = new Person($targetValue);
                $email = $Obj->get_field('PEmail');
            }
        }
        $content =
            "<label for='email' style='width:10em'>Email Address</label>\n"
            ."<input type='text' id='email' value=\"".$email."\" name='email' size='20' style='width: 120px;'/>"
            ."<div class='clr'>&nbsp;</div>\n"
            ."<div class='controls'>\n"
            ."<input type='submit' onclick=\"if (geid_val('email').indexOf('@')==-1) {"
            ." alert('Please enter your email address');return false; };geid('btnSubmit').value='Please Wait';"
            ."this.disabled=1;geid('command').value='new_password';geid('form').submit();\""
            ." value='Submit' style='formButton'/>"
            ."</div>\n";
        $out.=
             HTML::draw_form_box('Your Subscription', $content, '', $cp['shadow'])
            ."<script type='text/javascript'>geid('email').focus();</script>\n";
        $sql =
             "SELECT\n"
            ."  `groups`.`name` AS `group_name`,\n"
            ."  `groups`.`ID`   AS `group_ID`,\n"
            ."  `person`.`ID`   AS `personID`,\n"
            ."  `person`.`PUserName`\n"
            ."FROM\n"
            ."  `group_members`\n"
            ."INNER JOIN `groups` ON\n"
            ."  `group_members`.`groupID` = `groups`.`ID`\n"
            ."INNER JOIN `person` ON\n"
            ."  `group_members`.`personID` = `person`.`ID`\n"
            ."WHERE\n"
            ."  `groups`.`systemID` = ".SYS_ID." AND\n"
            ."  `group_members`.`permEMAILRECIPIENT` = 1 AND\n"
            ."  `person`.`PEmail` = \"".$email."\"";
        $records = $this->get_records_for_sql($sql);
        if ($email!='') {
            $out.=
                "<h3>Your current email subscriptions</h3>\n";
            if (count($records)) {
                $out.=
                     "<table cellpadding='2' cellspacing='0' border='1' bordercolor='#808080'"
                    ." style='border-collapse: collapse;'>\n"
                    ."  <tr>\n"
                    ."    <th valign='bottom'>Group</th>\n"
                    ."    <th valign='bottom'>Username</th>\n"
                    ."    <th valign='bottom'>Unsubscribe<br />from email?</th>\n"
                    ."  </tr>\n";
                foreach ($records as $record) {
                    $out.=
                         "  <tr>\n"
                        ."    <td>".$record['group_name']."</td>\n"
                        ."    <td>".$record['PUserName']."</td>\n"
                        ."    <td><a href=\"#\" onclick=\""
                        ."if (confirm('Unsubscribe from email sent to ".$record['group_name']."?')) {"
                        .($cp['steps']==1 ?
                            "  geid('submode').value='unsubscribe';"
                         :
                            "  geid('submode').value='confirm';"
                         )
                        ."  geid('targetID').value='".$record['group_ID']."';"
                        ."  geid('targetValue').value='".$record['personID']."';"
                        ."  geid('form').submit();"
                        ."} else {alert('Action cancelled.');};return false;\">Yes</a></td>\n"
                        ."  </tr>\n";
                }
                $out.=
                    "</table>\n";
            } else {
                $out.=
                    "<p>You are not currently subscribed to any groups to receive email from us.</p>\n";
            }
        }
        return $out;
    }

    public function draw_forgotten_password($initialText = '', $failureText = '', $successText = '', $shadow = 0)
    {
        global $mode, $command, $email, $msg, $system_vars;
        $msg = "";
        $initialText = ($initialText=='' ?
             "<p>Use this form if you have forgotten your password."
            ." A new password will be created and emailed to you.</p>"
         :
             $initialText
        );
        $failureText = ($failureText=='' ?
             "Sorry - we were unable to deliver your password to the address you gave."
         :
             $failureText
        );
        $successText = ($successText=='' ?
            "Success: your new password has been emailed to your address."
         :
            $successText
        );
        switch ($command) {
            case "new_password":
                $Obj = new Captcha();
                if (!$Obj->isKeyRight(isset($_REQUEST['captcha_key']) ? $_REQUEST['captcha_key'] : "")) {
                    $msg = "<b>Error:</b> The verification code you entered didn't match the image we showed you.";
                    break;
                }
                $sql =
                     "SELECT\n"
                    ."  `ID`\n"
                    ."FROM\n"
                    ."  `person`\n"
                    ."WHERE\n"
                    ."  `PEmail` =\"".addslashes($email)."\"";
                $personID = $this->get_field_for_sql($sql);
                if (!$personID) {
                    $msg = "<b>Error:</b> ".$failureText;
                    break;
                }
                $Obj_Mail_Template = new Mail_Template;
                $Obj_Mail_Template->_set_ID($Obj_Mail_Template->get_ID_by_name('user_forgotten_password'));
                $mail_result = $Obj_Mail_Template->send_email($personID);
                if (substr($mail_result, 0, 12)!="Message-ID: ") {
                    $msg =
                        "<b>Error:</b> We were not able to send to the email address you gave:<br />".$mail_result;
                    break;
                }
                return
                     $successText
                    ."<p><input type='button' onclick='document.location=\"./\"' value='Done'/></p>";
            break;
        }
        $content =
             "  <label for='email' style='width:10em'>Email Address</label>\n"
            ."  <input type='text' id='email' name='email' size='20' style='width: 180px;' value=\"".$email."\"/>\n"
            ."  <div class='clr'>&nbsp;</div>\n"
            ."  <label style='width:10em'>Verification Image</label>\n"
            ."  <img class='formField' style='border:1px solid #7F9DB9;padding:2px;'"
            ." src='./?command=captcha_img' alt='Verification Image' />\n"
            ."  <div class='clr'>&nbsp;</div>\n"
            ."  <label for='captcha_key' style='width:10em'>Verification Code</label>\n"
            ."  <input type='text' id='captcha_key' name='captcha_key' size='20' value=\"\""
            ."style='width: 180px;display:block;' />\n"
            ."  <div class='clr'>&nbsp;</div>\n"
            ."  <div class='controls'>\n"
            ."    <input type='submit' value='Submit' id='btnSubmit' name='btnSubmit' style='formButton' "
            ."onclick=\"if (geid_val('email').indexOf('@')==-1) { "
            ."alert('Please enter your email address');return false; };geid('btnSubmit').value='Please Wait';"
            ."this.disabled=1;geid('command').value='new_password';geid('form').submit();\"/>"
            ."  </div>\n";
        Page::push_content(
            'javascript_onload',
            "  geid('email').focus();\n"
        );
        return
           ($msg ?
                 "<span style='background-color:#ffe0e0; color:#ff0000; border:solid 1px #ff0000;"
                ." padding-left:0.25em;padding-right:0.25em'>".$msg."</span>"
              :
                ""
           )
           .$initialText
           .HTML::draw_form_box(
               'Send New Password',
               $content,
               '_help_user_signin_existing_forgotten',
               $shadow
           );
    }

    public function draw_person_info()
    {
        $record = $this->get_record();
        return
             "<table cellpadding='4' cellspacing='0' border='0' width='100%'>\n"
            ."  <tr>\n"
            ."    <td width='70'><b>Name</b></td>"
            ."    <td>"
            .$record['NTitle']." "
            .$record['NFirst']." "
            .$record['NMiddle']." "
            .$record['NLast']
            ."</td>"
            ."    <td width='70'><b>Username</b></td>"
            ."    <td>".$record['PUsername']."</td>"
            ."  </tr>\n"
            ."</table>";
    }

    public function draw_rights()
    {
        return
             "<ul>\n"
            ."  <li>"
            .implode(
                "</li>\n<li>",
                explode(", ", get_user_status_text(get_user_status()))
            )
            ."</li>\n"
            ."</ul>\n";
    }

    public function draw_signup(
        $initialText = '',
        $confirmText = '',
        $failureText = '',
        $successText = '',
        $emailTo = 0,
        $report_name = 'signup',
        $mail_template = 'user_signup',
        $shadow = false
    ) {
        deprecated();
        $Obj = new Component_Signup;
        $args = array(
            'email_for_offline' =>    $emailTo,
            'email_template' =>       $mail_template,
            'report_name' =>          $report_name,
            'shadow' =>               $shadow,
            'text_confirm' =>         $confirmText,
            'text_initial' =>         $initialText,
            'text_success' =>         $successText
        );
        return $Obj->draw('', $args, false);
    }

    public function exists_emailaddress($value)
    {
        $sql =
             "SELECT\n"
            ."  COUNT(*) AS `count`\n"
            ."FROM\n"
            ."  `person`\n"
            ."WHERE\n"
            ."  `PEmail` = \"".$value."\" AND\n"
            ."  `systemID` IN(1,".SYS_ID.")";
        return $this->get_field_for_sql($sql);
    }

    public function exists_username($value)
    {
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `person`\n"
            ."WHERE\n"
            ."  `PUsername` = \"".$value."\" AND\n"
            ."  `systemID` IN(1,".SYS_ID.")";
        return $this->get_field_for_sql($sql);
    }

    public function export_sql($targetID, $show_fields)
    {
        $header =
             "Selected ".$this->_get_object_name().$this->plural($targetID)
            ." with Group Membership records and Keywords";
        $extra_delete =
             "DELETE FROM `group_members`          WHERE `personID` IN (".$targetID.");\n"
            ."DELETE FROM `postings`               WHERE `type`='note' AND `personID` IN (".$targetID.");\n";
        $Obj = new Backup;
        $extra_select =
            $Obj->db_export_sql_query(
                "`group_members`         ",
                "SELECT * FROM `group_members` WHERE `personID` IN(".$targetID.");",
                $show_fields
            )
            .$Obj->db_export_sql_query(
                "`postings`              ",
                "SELECT * FROM `postings` WHERE `type`='note' AND `personID` IN(".$targetID.");",
                $show_fields
            );
        return parent::sql_export($targetID, $show_fields, $header, '', $extra_delete, $extra_select);
    }

    public function get_coords()
    {
      // overrides Record::get_coords() since we may have two locations
        $prefixes = array('AMap_','WMap_');
        $result =   array();
        foreach ($prefixes as $p) {
            $geocode = parent::get_coords($this->get_field($p.'location'));
            $result[$p.'geocodeID'] =           $geocode['ID'];
            $result[$p.'geocode_area'] =        $geocode['match_area'];
            $result[$p.'geocode_type'] =        $geocode['match_type'];
            $result[$p.'geocode_quality'] =     $geocode['match_quality'];
            $result[$p.'lat'] =                 $geocode['lat'];
            $result[$p.'lon'] =                 $geocode['lon'];
        }
  //    y($result);
        return $result;
    }

    public static function get_email_opt_in_link_for_group()
    {
        $ID = component_result('ID', true);
        return component_result('system_URL').'/email-opt-in/'.($ID!==false ? $ID : '12345678');
    }

    public static function get_email_opt_out_link_for_group()
    {
        $ID = component_result('ID', true);
        return component_result('system_URL').'/email-opt-out/'.($ID!==false ? $ID : '12345678');
    }

    public function get_email_message_list()
    {
        $sql =
             "SELECT\n"
            ."  `mailqueue_item`.`ID`,\n"
            ."  `mailqueue`.`subject`,\n"
            ."  `mailqueue`.`sender_name`,\n"
            ."  `mailqueue`.`sender_email`,\n"
            ."  `mailqueue`.`date_started`,\n"
            ."  `mailqueue_item`.`PEmail`,\n"
            ."  (SELECT `groups`.`ID` FROM `groups` WHERE `groups`.`ID` = `mailqueue`.`groupID`) `mail_groupID`,\n"
            ."  (SELECT `groups`.`name` FROM `groups` WHERE `groups`.`ID` = `mailqueue`.`groupID`) `mail_name`\n"
            ."FROM\n"
            ."  `mailqueue_item`\n"
            ."INNER JOIN `mailqueue` ON\n"
            ."  `mailqueue`.`ID` = `mailqueue_item`.`mailqueueID`\n"
            ."WHERE\n"
            ."  `mailqueue`.`systemID`=".SYS_ID." AND\n"
            ."  `mailqueue_item`.`personID` IN(".$this->_get_ID().")"
            ."ORDER BY\n"
            ."  `mailqueue`.`date_started` DESC";
        return $this->get_records_for_sql($sql);
    }

    public function get_group_membership()
    {
        $sql =
             "SELECT\n"
            ."  `groups`.`ID` `groupID`,\n"
            ."  `groups`.`name`,\n"
            ."  `groups`.`description`,\n"
            ."  `group_members`.`ID`,\n"
            ."  `group_members`.`systemID`,\n"
            ."  `group_members`.`permVIEWER`,\n"
            ."  `group_members`.`permEMAILRECIPIENT`,\n"
            ."  `group_members`.`permEMAILOPTOUT`,\n"
            ."  `group_members`.`permEDITOR`\n"
            ."FROM\n"
            ."  `groups`\n"
            ."INNER JOIN `group_members` ON\n"
            ."  `groups`.`ID` = `group_members`.`groupID`\n"
            ."WHERE\n"
            ."  `group_members`.`personID` IN(".$this->_get_ID().")";
        return $this->get_records_for_sql($sql);
    }

    public static function get_group_permissions()
    {
        if (!isset($_SESSION['person']['permissions'])) {
            return array();
        }
        return $_SESSION['person']['permissions'];
    }

    public function get_named_group_rights($name)
    {
        $ObjGroup =     new Group;
        $ObjGroup->_set_ID($ObjGroup->get_ID_by_name($name));
        $perms_arr =      $ObjGroup->member_perms($this->_get_ID());
        return
            ($perms_arr!==false) &&
            ($perms_arr['permVIEWER']==1 ||
             $perms_arr['permEDITOR']==1);
    }

    public function get_notification_summary($datetime, $systemID, $base_url)
    {
        $filter =   "  (`history_created_by`=`ID` OR `history_created_by`=0)";
        $records =  $this->get_records_since($datetime, $systemID, $filter);
        if (!count($records)) {
            return;
        }
        $out =
             "<h2>New ".$this->_get_object_name().$this->plural('1,2')."</h2>"
            ."<table cellpadding='2' cellspacing='0' border='1'>\n"
            ."  <thead>\n"
            ."    <th>Name</th>\n"
            ."    <th>Email</th>\n"
            ."    <th>Home Address</th>\n"
            ."    <th>Work Address</th>\n"
            ."    <th class='datetime'>Created</th>\n"
            ."  </thead>\n"
            ."  <tbody>\n";
        foreach ($records as $record) {
            $NName =
                 $record['NFirst']." "
                .$record['NMiddle']." "
                .$record['NLast']
                .($record['PUsername'] ? " (".$record['PUsername'].")" : "");
            $AAddress = substr(
                ($record['AAddress1']   ? $record['AAddress1'].", "   : "")
                .($record['AAddress2']   ? $record['AAddress2'].", "   : "")
                .($record['ACity']       ? $record['ACity'].", "       : "")
                .($record['ASpID']       ? $record['ASpID'].", "       : "")
                .($record['ACountryID']  ? $record['ACountryID'].", "  : ""),
                0,
                -2
            );
            $WAddress = substr(
                ($record['WAddress1']   ? $record['WAddress1'].", "   : "")
                .($record['WAddress2']   ? $record['WAddress2'].", "   : "")
                .($record['WCity']       ? $record['WCity'].", "       : "")
                .($record['WSpID']       ? $record['WSpID'].", "       : "")
                .($record['WCountryID']  ? $record['WCountryID'].", "  : ""),
                0,
                -2
            );
            $User_URL =   $base_url.'details/user/'.$record['ID'];
            $out.=
                 "  <tr>\n"
                ."    <td><a target=\"_blank\" href=\"".$User_URL."\">".$NName."</a></td>\n"
                ."    <td>".($record['PEmail'] ? "<a href=\"mailto:".$record['PEmail']."\">"
                .$record['PEmail']."</a>" : "")
                ."</td>\n"
                ."    <td>".($AAddress ? $AAddress : "&nbsp;")."</td>\n"
                ."    <td>".($WAddress ? $WAddress : "&nbsp;")."</td>\n"
                ."    <td class='datetime'>".$record['history_created_date']."</td>\n"
                ."  </tr>\n";
        }
        $out.=
         "  </tbody>\n"
        ."</table>\n";
        return $out;
    }

    public function get_number_of_locked()
    {
        $sql =
         "SELECT\n"
        ."  COUNT(*) `count`\n"
        ."FROM\n"
        ."  `person`\n"
        ."WHERE\n"
        ."  `person`.`profile_locked`=1 AND\n"
        ."  `person`.`ID` IN(".$this->_get_ID().")";
        return $this->get_field_for_sql($sql);
    }

    public static function get_permission($permission, $group_list = "")
    {
        if ($permission=="PUBLIC") {
            return !isset($_SESSION['person']) ? 1 : 0;
        }
        if (!isset($_SESSION['person'])) {
            return 0;
        }
        switch ($permission) {  // Exit promptly for these
            case 'SYSLOGON':
                return true;
            break;
            case 'PUBLIC':
                return 0;
            break;
            case 'MASTERADMIN':
                return $_SESSION['person']['permMASTERADMIN'];
            break;
            case 'USERADMIN':
                return $_SESSION['person']['permUSERADMIN'];
            break;
            case 'COMMUNITYADMIN':
                return $_SESSION['person']['permCOMMUNITYADMIN'];
            break;
            case 'GROUPEDITOR':
                return $_SESSION['person']['permGROUPEDITOR'];
            break;
            case 'GROUPVIEWER':
                return $_SESSION['person']['permGROUPVIEWER'];
            break;
        }
        $ADMIN =        false;
        $APPROVER =     false;
        $EDITOR =       false;
        $VIEWER =       false;
        if ($group_list!="" && isset($_SESSION['person']['permissions'])) {
  // find any additional permissions from session:
            $group_list_arr = explode(",", $group_list);
            for ($i=0; $i<count($group_list_arr); $i++) {
                if (in_array($group_list_arr[$i], $_SESSION['person']['permissions']['EDITOR'])) {
                    $EDITOR = true;
                }
                if (in_array($group_list_arr[$i], $_SESSION['person']['permissions']['VIEWER'])) {
                    $VIEWER = true;
                }
            }
        }
        switch ($permission) {
            case 'SYSADMIN':
                return $ADMIN || $_SESSION['person']['permSYSADMIN'];
            break;
            case 'SYSAPPROVER':
                return $APPROVER || $_SESSION['person']['permSYSAPPROVER'];
            break;
            case 'SYSEDITOR':
                return $EDITOR || $_SESSION['person']['permSYSEDITOR'];
            break;
            case 'SYSMEMBER':
                return $VIEWER || $_SESSION['person']['permSYSMEMBER'];
            break;
            case 'EDITOR':
                return $EDITOR;
            break;
            case 'VIEWER':
                return $VIEWER || $EDITOR ;
            break;
        }
        return false;
    }


    public function get_person_for_signin($username, $password_enc)
    {
        global $page, $system_vars;
        $out = array(
        'status' => array('code' => 0, 'message' => ''),
        'data' =>   array()
        );
        if ($username=="" || $password_enc=="") {
            $out['status']['code'] =       100;
            $out['status']['message'] =    "Missing Username or Password";
            return $out;
        }
        $sql =
            "SELECT\n"
            ."  `ID`,\n"
            ."  `PUsername`,\n"
            ."  `NFirst`,\n"
            ."  `NMiddle`,\n"
            ."  `NLast`,\n"
            ."  `memberID`,\n"
            ."  `permACTIVE`,\n"
            ."  `permMASTERADMIN`,\n"
            ."  `permCOMMUNITYADMIN`,\n"
            ."  `permSYSADMIN`,\n"
            ."  `permSYSAPPROVER`,\n"
            ."  `permSYSEDITOR`,\n"
            ."  `permSYSMEMBER`,\n"
            ."  `permUSERADMIN`,\n"
            ."  `PLogonCount`,\n"
            ."  `profile_locked`,\n"
            ."  IF(`active_date_from`!='0000-00-00' AND `active_date_from` >= CURDATE(),1,0) AS `not_active_yet`,\n"
            ."  IF(`active_date_to`!='0000-00-00' AND `active_date_to` < CURDATE(),1,0) AS `not_active_still`\n"
            ."FROM\n"
            ."  `person`\n"
            ."WHERE\n"
            ."  `person`.`PUsername` = \"".$username."\" AND\n"
            ."  `person`.`PPassword` = \"".$password_enc."\" AND\n"
            ."  (\n"
            ."    `person`.`systemID` = \"".SYS_ID."\" OR\n"
            ."    `person`.`permMASTERADMIN`\n"
            ."  )\n";
        if (!$record = $this->get_record_for_sql($sql)) {
            $out['status']['code'] =       101;
            $out['status']['message'] =    "Invalid Username or Password";
            return $out;
        }
        $permACTIVE =           $record['permACTIVE'];
        $not_active_yet =       $record['not_active_yet'];
        $not_active_still =     $record['not_active_still'];
        if (!$permACTIVE || $not_active_yet || ($not_active_still && $system_vars['membership_expiry_type']=='h')) {
            $out['status']['code'] =       102;
            $out['status']['message'] =    "User account is inactive";
            return $out;
        }
        $data =
        array(
            'ID' =>                 $record['ID'],
            'memberID' =>           $record['memberID'],
            'PUsername' =>          $record['PUsername'],
            'PPassword' =>          $password_enc,
            'profile_locked' =>     $record['profile_locked'],
            'NFirst' =>             $record['NFirst'],
            'NMiddle' =>            $record['NMiddle'],
            'NLast' =>              $record['NLast'],
            'NFull' =>              $record['NFirst'].' '.$record['NMiddle'].' '.$record['NLast'],
            'permMASTERADMIN' =>    $record['permMASTERADMIN'],
            'permUSERADMIN' =>      $record['permUSERADMIN'],
            'permCOMMUNITYADMIN' => $record['permCOMMUNITYADMIN'],
            'permSYSADMIN' =>       $record['permSYSADMIN'],
            'permSYSAPPROVER' =>    $record['permSYSAPPROVER'],
            'permSYSEDITOR' =>      $record['permSYSEDITOR'],
            'permSYSMEMBER' =>      $record['permSYSMEMBER'],
            'permSYSLOGON' =>       1,
            'permGROUPEDITOR' =>    0,
            'permGROUPVIEWER' =>    0
        );
        $sql =
             "SELECT\n"
            ."  `groups`.`name`,\n"
            ."  `group_members`.`groupID`,\n"
            ."  `group_members`.`permEDITOR`,\n"
            ."  `group_members`.`permEMAILOPTOUT`,\n"
            ."  `group_members`.`permEMAILRECIPIENT`,\n"
            ."  `group_members`.`permVIEWER`\n"
            ."FROM\n"
            ."  `group_members`\n"
            ."INNER JOIN `groups` ON\n"
            ."  `group_members`.`groupID` = `groups`.`ID`\n"
            ."WHERE\n"
            ."  `personID` = ".$data['ID'];
        $records = $this->get_records_for_sql($sql);
        $data['groups'] =                           array();
        $data['permissions'] =                      array();
        $data['permissions']['EMAILOPTOUT'] =       array();
        $data['permissions']['EMAILRECIPIENT'] =    array();
        $data['permissions']['EDITOR'] =            array();
        $data['permissions']['VIEWER'] =            array();
        foreach ($records as $record) {
            $data['groups'][] =
            array(
              'groupID'=>               $record['groupID'],
              'name'=>                  $record['name'],
              'permEDITOR'=>            $record['permEDITOR'],
              'permEMAILOPTOUT'=>       $record['permEMAILOPTOUT'],
              'permEMAILRECIPIENT'=>    $record['permEMAILRECIPIENT'],
              'permVIEWER'=>            $record['permVIEWER']
            );
            if ($record['permEDITOR']) {
                $data['permissions']['EDITOR'][] = $record['groupID'];
                $data['permGROUPEDITOR'] =      1;
            }
            if ($record['permEMAILOPTOUT']) {
                $data['permissions']['EMAILOPTOUT'][] = $record['groupID'];
            }
            if ($record['permEMAILRECIPIENT']) {
                $data['permissions']['EMAILRECIPIENT'][] = $record['groupID'];
            }
            if ($record['permVIEWER']) {
                $data['permissions']['VIEWER'][] = $record['groupID'];
                $data['permGROUPVIEWER'] =      1;
            }
        }
        $out['status']['code'] =       200;
        $out['status']['message'] =    "Success";
        $out['data'] = $data;
        return $out;
    }

    public function get_person_to_session($username, $password_enc)
    {
        $result =         $this->get_person_for_signin($username, $password_enc);
        if ($result['status']['code']!=200) {
            return false;
        }
        if (
            preg_match('/^Mozilla\/4\.0 \(compatible; MSIE 6/', $_SERVER['HTTP_USER_AGENT']) &&
            !preg_match('/\bopera/i', $_SERVER['HTTP_USER_AGENT'])
        ) {
            session_regenerate_id(); // For security against session fixation attacks
        }
        $_SESSION['person'] = $result['data'];
        return true;
    }

    public function get_records()
    {
        global $system_vars, $page;
        $args = func_get_args();
        $vars = array(
            'filter_category' =>          '*',
            'filter_category_master' =>   '',
            'filter_groups_list' =>       '',
            'results_limit' =>            0,
            'results_offset' =>           0,
            'results_order' =>            'NLast'
        );
        if (!$this->_get_args($args, $vars)) {
            die('Error - no parameters passed');
        }
        $limit =            $vars['results_limit'];
        $offset =           $vars['results_offset'];
        $order_by =         $vars['results_order'];
        if ($vars['filter_groups_list']!="") {
            $Obj_Group =      new Group;
            $group_names =    explode(',', $vars['filter_groups_list']);
            $group_IDs =      array();
            foreach ($group_names as $g) {
                if ($groupID =   $Obj_Group->get_ID_by_name($g)) {
                    $group_IDs[] = $groupID;
                }
            }
            if (!count($group_IDs)) {
                $group_IDs[]= -1;
            }
        }
        $sql =
             "SELECT\n"
            ."  `person`.*,\n"
            ."  CONCAT(\n"
            ."    `NFirst`,\n"
            ."     IF(`NMiddle`!='', CONCAT(' ',`NMiddle`), ''),\n"
            ."     IF(`NLast`!='',CONCAT(' ',`NLast`),'')\n"
            ."  ) AS `name`,\n"
            ."  `system`.`textEnglish` `systemTitle`,\n"
            ."  `system`.`URL` `systemURL`\n"
            ."FROM\n"
            ."  `person`\n"
            ."INNER JOIN `system` ON\n"
            ."  `person`.`systemID` = `system`.`ID`\n"
            ."WHERE\n"
            ."  `person`.`systemID` = ".$this->_get_systemID()." AND\n"
            .($vars['filter_category']!="*" ?
                "  `person`.`category` REGEXP \"".implode("|", explode(',', $vars['filter_category']))."\" AND\n"
              :
                ""
             )
            .($vars['filter_category_master'] ?
                 "  `person`.`category` REGEXP \""
                .implode("|", explode(',', $vars['filter_category_master']))."\" AND\n"
              :
                ""
             )
            .($vars['filter_groups_list']!="" ?
                "  `person`.`ID` IN(SELECT `personID` FROM `group_members` `gm` WHERE `gm`.`groupID` IN("
                .implode($group_IDs)
                .")) AND\n"
              :
                ""
             )
            ."  1\n";
        $records =  $this->get_records_for_sql($sql);
        if ($records === false) {
            return;
        }
        $out = array();
        foreach ($records as $row) {
            $out[] = $row;
        }
        switch($order_by){
            case "NFirst":
                $order_arr = array(
                    array('NFirst', 'a'),
                    array('NLast', 'a')
                );
                break;
            case "NLast":
                $order_arr = array(
                    array('NLast', 'a'),
                    array('NFirst', 'a')
                );
                break;
            case "PUsername":
                $order_arr = array(
                    array('PUsername', 'a')
                );
                break;
        }
        $out = $this->sort_records($out, $order_arr);
        $total = count($out);
        if ($limit!=0 || $offset) {
            $out = array_slice($out, ($offset ? $offset : 0), $limit);
        }
        $out = array('total'=>$total,'data'=>$out);
        return $out;
    }

    public function get_selector_sql()
    {
        return
             "SELECT\n"
            ."  0 `seq`,\n"
            ."  '' `value`,\n"
            ."  '(None)' `text`,\n"
            ."  'd0d0d0' `color_background`\n"
            ."UNION SELECT\n"
            ."  1 `seq`,\n"
            ."  `ID`,\n"
            ."  TRIM(\n"
            ."    CONCAT(\n"
            ."      `NFirst`,' ',\n"
            ."      `NMiddle`,' ',\n"
            ."      `NLast`,\n"
            ."      IF(`NTitle`='','',CONCAT(' (',`NTitle`,')')),\n"
            ."      ' | ',\n"
            ."      '\'',`NGreetingName`,'\' | ',\n"
            ."      `PEmail`, ' | ',\n"
            ."      'H:',`ATelephone`,' | ',\n"
            ."      'W:',`WTelephone`\n"
            ."    )\n"
            ."  ),\n"
            ."  'e0ffe0'\n"
            ."FROM\n"
            ."  `person`\n"
            ."WHERE\n"
            ."  `ID` NOT IN(_ID_) AND\n"
            .($this->_get_type() ? "  `type` = '".$this->_get_type()."' AND\n" : "")
            ."  `systemID` IN(1,".SYS_ID.")\n"
            ."ORDER BY\n"
            ."  `seq`,`text`;";
    }

    public static function get_user_status()
    {
        if (!isset($_SESSION['person'])) {
            return "PUBLIC";
        }
        $out =    array();
        $out[] =    "SYSLOGON";
        if ($_SESSION['person']['permMASTERADMIN']) {
            $out[] = "MASTERADMIN";
        }
        if ($_SESSION['person']['permUSERADMIN']) {
            $out[] = "USERADMIN";
        }
        if ($_SESSION['person']['permSYSADMIN']) {
            $out[] = "SYSADMIN";
        }
        if ($_SESSION['person']['permSYSAPPROVER']) {
            $out[] = "SYSAPPROVER";
        }
        if ($_SESSION['person']['permSYSEDITOR']) {
            $out[] = "SYSEDITOR";
        }
        if ($_SESSION['person']['permSYSMEMBER']) {
            $out[] = "SYSMEMBER";
        }
        if ($_SESSION['person']['permSYSLOGON']) {
            $out[] = "SYSLOGON";
        }
        if ($_SESSION['person']['permGROUPEDITOR']) {
            $out[] = "GROUPEDITOR";
        }
        if ($_SESSION['person']['permGROUPVIEWER']) {
            $out[] = "GROUPVIEWER";
        }
        return implode($out, "|");
    }

    public static function get_user_status_text()
    {
        if (!isset($_SESSION['person'])) {
            return "Public";
        }
        $out = array();
        if ($_SESSION['person']['permMASTERADMIN']) {
            $out[] = "Master Administrator";
        }
        if ($_SESSION['person']['permUSERADMIN']) {
            $out[] = "User Administrator";
        }
        if ($_SESSION['person']['permSYSADMIN']) {
            $out[] = "Site Administrator";
        }
        if ($_SESSION['person']['permSYSAPPROVER']) {
            $out[] = "Site Approver";
        }
        if ($_SESSION['person']['permSYSEDITOR']) {
            $out[] = "Site Editor";
        }
        if ($_SESSION['person']['permSYSMEMBER']) {
            $out[] = "Site Member";
        }
        if ($_SESSION['person']['permSYSLOGON']) {
            $out[] = "Site User";
        }
        if ($_SESSION['person']['permGROUPEDITOR']) {
            $out[] = "Group Editor";
        }
        if ($_SESSION['person']['permGROUPVIEWER']) {
            $out[] = "Group Viewer";
        }
        return implode($out, ", ");
    }

    public function handle_report_copy(&$newID, &$msg, &$msg_tooltip, $name)
    {
        return parent::try_copy($newID, $msg, $msg_tooltip, $name);
    }

    public function handle_report_delete(&$msg)
    {
        $targetID = $this->_get_ID();
        if (!$num_locked = $this->get_number_of_locked()) {
            return parent::try_delete($msg);
        }
        $msg =
        status_message(
            2,
            true,
            "selected ".$this->_get_object_name(),
            '',
            " include ".$num_locked." whose "
            .($num_locked==1 ? 'profile has' : 'profiles have')
            ." been locked - your deletion has therefore been cancelled.",
            $targetID
        );
        return false;
    }

    public function load_profile_fields()
    {
        $record = $this->load();
        component_result_set('personID', $record['ID']);
        component_result_set('NName', $record['NFirst']." ".$record['NMiddle']." ".$record['NLast']);
        component_result_set('NGreetingName', $record['NGreetingName']);
        component_result_set('PEmail', $record['PEmail']);
        component_result_set('PUsername', $record['PUsername']);
        component_result_set('NTitle', $record['NTitle']);
        component_result_set('WCompany', $record['WCompany']);
        component_result_set('Community_Member_Name', '');
        component_result_set('Community_Member_Title', '');
        if ($record['memberID'] && class_exists('Community_Member')) {
            $Obj_CM = new Community_Member($record['memberID']);
            $Obj_CM->load();
            component_result_set('Community_Member_Name', $Obj_CM->record['name']);
            component_result_set('Community_Member_Title', $Obj_CM->record['title']);
        }
    }

    public function lookup($criteria)
    {
        $sql_arr = array();
        foreach ($criteria as $key => $value) {
            $sql_arr[] = "  `person`.`".$key."` = \"".$value."\" AND\n";
        }
        $sql =
             "SELECT\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `person`\n"
            ."WHERE\n"
            .implode('', $sql_arr)
            .($this->_get_type() ? "  `person`.`type`=\"".$this->_get_type()."\" AND\n" : "")
            ."  `person`.`systemID`=".SYS_ID;
        return $this->get_records_for_sql($sql);
    }


    public function manage_emails()
    {
        if (!$personID = get_userID()) {
            return "";
        }
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isUSERADMIN =      get_person_permission("USERADMIN");
        $isSYSADMIN =       get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isAPPROVER =       ($isMASTERADMIN || $isUSERADMIN || $isSYSADMIN || $isSYSAPPROVER);
        if (!$isAPPROVER && $personID!=get_var('selectID')) {
            return "";
        }
        if (get_var('command')=='report') {
            return draw_auto_report('person_emails', 1);
        }
        $out = "<h3 style='margin:0.25em'>Emails sent to this ".$this->_get_object_name()."</h3>";
        if (!get_var('selectID')) {
            $out .=
                "<p style='margin:0.25em'>No Email history -"
               ." this ".$this->_get_object_name()." has not been saved yet.</p>";
        } else {
            $out.= draw_auto_report('person_emails', 1);
        }
        return $out;
    }

    public function manage_groups()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('person_groups', 1);
        }
        $out = "<h3 style='margin:0.25em'>Groups to which this ".$this->_get_object_name()." belongs</h3>";
        if (!get_var('selectID')) {
            $out .=
                "<p style='margin:0.25em'>No member Groups -"
               ." this ".$this->_get_object_name()." has not been saved yet.</p>";
        } else {
            $out.= draw_auto_report('person_groups', 1);
        }
        return $out;
    }

    public function manage_notes()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('person_notes', 1);
        }
        $out= "<h3 style='margin:0.25em 0'>Notes relating to this ".$this->_get_object_name()."</h3>";
        if (!get_var('selectID')) {
            $out .=
                "<p style='margin:0.25em 0'>No Notes - this ".$this->_get_object_name()." has not been saved yet.</p>";
        } else {
            $Obj_LNT = new lst_note_person_type;
            $subtypes = $Obj_LNT->get_listdata();
            $out.=
                 "<div style='padding: 0.25em 2px 0.25em 0' class='admin_toolbartable noprint'>\n"
                ."<img class='fl' src='".BASE_PATH."img/sysimg/icon_toolbar_end_left.gif'"
                ." style='height:16px;width:6px;padding-left:2px;' alt='|' />"
                ."<div class='toolbar_text' style='float:left;margin-right:0.25em'><b>Add new</b></a></div>";
            foreach ($subtypes as $subtype) {
                $out.=
                     "<a href=\"#\" onclick=\"details('person_notes','','560','960','',"
                    .get_var('selectID').",0,'subtype=".$subtype['value']."')\">"
                    .$subtype['content']
                    ."</a>";
            }
            $out.=
            "</div><br class='clear' />"
            .draw_auto_report('person_notes', 1);
        }
        return $out;
    }

    public function manage_orders()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('person_orders', 1);
        }
        $out = "<h3 style='margin:0.25em'>Orders placed for this ".$this->_get_object_name()."</h3>";
        if (!get_var('selectID')) {
            $out .=
                 "<p style='margin:0.25em'>No associated Orders - this"
                ." ".$this->_get_object_name()." has not been saved yet.</p>";
        } else {
            $out.= draw_auto_report('person_orders', 1);
        }
        return $out;
    }

    public function on_action_set_group_membership_description($reveal_modification = false)
    {
        $ID_arr = explode(',', str_replace(' ', '', $this->_get_ID()));
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $this->set_groups_list_description($reveal_modification);
        }
    }

    public function on_action_pre_update_set_map_coordinates($reveal_modification = false)
    {
        global $action_parameters;
        if ($action_parameters['sourceTrigger']!=='report_update_pre') {
            die("This component should be called only before a record update.");
        }
        $prefixes = array('AMap_','WMap_');
        $ID_arr = explode(',', str_replace(' ', '', $this->_get_ID()));
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $r = $this->load();
            foreach ($prefixes as $p) {
                if ($action_parameters['data'][$p.'location']!==$r[$p.'location']) {
                    $geocode = parent::get_coords($action_parameters['data'][$p.'location']);
                    $data = array(
                        $p.'geocodeID' =>           $geocode['ID'],
                        $p.'geocode_area' =>        $geocode['match_area'],
                        $p.'geocode_type' =>        $geocode['match_type'],
                        $p.'geocode_quality' =>     $geocode['match_quality'],
                        $p.'lat' =>                 $geocode['lat'],
                        $p.'lon' =>                 $geocode['lon']
                    );
                    $this->update($data, false, $reveal_modification);
                }
            }
        }
    }

    public function on_action_set_map_descriptions_and_coordinates($reveal_modification = false)
    {
        $ID_arr = explode(',', str_replace(' ', '', $this->_get_ID()));
        static $countries = array();
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $r = $this->load();
            $AMap_location_changed = false;
            $WMap_location_changed = false;
            $NFull = $r['NTitle']." ".title_case_string($r['NFirst']." ".$r['NMiddle']." ".$r['NLast']);
            $data = array();
            if ($r['ACountryID'] && !isset($countries[$r['ACountryID']])) {
                $Obj = new Country;
                $countries[$r['ACountryID']] = $Obj->get_text_for_value($r['ACountryID']);
            }
            if ($r['WCountryID'] && !isset($countries[$r['WCountryID']])) {
                $Obj = new Country;
                $countries[$r['WCountryID']] = $Obj->get_text_for_value($r['WCountryID']);
            }
            if ($r['AMap_description']=='' && ($r['AAddress1']||$r['AAddress2']||$r['ACity']||$r['APostal'])) {
                $data['AMap_description'] =
                trim(
                    str_replace(
                        array("  ","\r\n\r\n","\r\n "),
                        array(" ","\r\n","\r\n"),
                        (trim($NFull) ? $NFull."\r\n" : "")
                        .title_case_string(
                            ($r['AAddress1'] ? $r['AAddress1']."\r\n" : "")
                            .($r['AAddress2'] ? $r['AAddress2']."\r\n" : "")
                        )
                        .$r['ACity']." ".$r['ASpID']." ".$r['APostal']
                        .($r['ACountryID']=='CAN' ?
                            ''
                         :
                            (isset($countries[$r['ACountryID']]) ?
                                "\r\n".$countries[$r['ACountryID']]
                             :
                                ''
                            )
                        )
                    )
                );
            }
            if ($r['AMap_location']=='' && ($r['AAddress1']||$r['AAddress2']||$r['ACity']||$r['APostal'])) {
                $data['AMap_location'] =
                trim(
                    str_replace(
                        "  ",
                        " ",
                        title_case_string(
                            $r['AAddress1']." ".$r['AAddress2']." ".$r['ACity']
                        )
                        ." ".$r['ASpID']." ".$r['APostal']
                        .($r['ASpID']=='PR' ?
                            ''
                         :
                            (isset($countries[$r['ACountryID']]) ? ' '.$countries[$r['ACountryID']] : '')
                        )
                    ),
                    " "
                );
                $AMap_location_changed = true;
            }
            if ($r['AMap_location']=='' && $r['AMap_lat']!='0') {
                $WMap_location_changed = true;
            }
            if ($r['AMap_location']!='' && $r['AMap_lat']=='0') {
                $WMap_location_changed = true;
            }
            if ($r['WMap_description']=='' && ($r['WAddress1']||$r['WAddress2']||$r['WCity']||$r['WPostal'])) {
                $data['WMap_description'] =
                trim(
                    str_replace(
                        array("  ","\r\n\r\n","\r\n ","\n\n"),
                        array(" ","\r\n","\r\n","\n"),
                        (trim($NFull) ? $NFull."\r\n" : "")
                        .($r['WCompany'] ? $r['WCompany']."\r\n" : "")
                        .($r['WDepartment'] ? $r['WDepartment']."\r\n" : "")
                        .title_case_string(
                            ($r['WAddress1'] ? $r['WAddress1']."\r\n" : "")
                            .($r['WAddress2'] ? $r['WAddress2']."\r\n" : "")
                        )
                        .$r['WCity']." ".$r['WSpID']." ".$r['WPostal']
                        .($r['WCountryID']=='CAN' ?
                            ''
                         :
                            (isset($countries[$r['WCountryID']]) ?
                                "\r\n".$countries[$r['WCountryID']]
                             :
                                ''
                            )
                        )
                    )
                );
            }
            if ($r['WMap_location']=='' && ($r['WAddress1']||$r['WAddress2']||$r['WCity']||$r['WPostal'])) {
                $data['WMap_location'] =
                trim(
                    str_replace(
                        "  ",
                        " ",
                        title_case_string(
                            $r['WAddress1']." ".$r['WAddress2']." ".$r['WCity']
                        )
                        ." ".$r['WSpID']." ".$r['WPostal']
                        .($r['WSpID']=='PR' ?
                            ''
                         :
                            (isset($countries[$r['WCountryID']]) ?
                                ' '.$countries[$r['WCountryID']]
                             :
                                ''
                            )
                        )
                    ),
                    " "
                );
                $WMap_location_changed = true;
            }
            if ($r['WMap_location']=='' && $r['WMap_lat']!='0') {
                $WMap_location_changed = true;
            }
            if ($r['WMap_location']!='' && $r['WMap_lat']=='0') {
                $WMap_location_changed = true;
            }
            if ($data) {
                $this->update($data, true, $reveal_modification);
            }
            if ($AMap_location_changed || $WMap_location_changed) {
                $coords = $this->get_coords();
                $this->update($coords, true, $reveal_modification);
            }
        }
    }

    public function on_schedule_update_pending($limit = 10, $reveal_modification = false)
    {
        if (!$IDs_csv =  $this->get_IDs_requiring_map_updates(false, $limit)) {
            return;
        }
        $IDs =      explode(',', $IDs_csv);
        foreach ($IDs as $ID) {
            $this->_set_ID($ID);
            $this->load();
            $data = array();
            $prefixes = array('AMap_','WMap_');
            foreach ($prefixes as $p) {
                $r = parent::get_coords($this->get_field($p.'location'));
                if (
                    $r['code']==='OVER_DAILY_LIMIT' ||
                    $r['code']==='OVER_QUERY_LIMIT' ||
                    $r['code']==='Connection Error'
                ) {
                    return;
                }
                $data[$p.'geocodeID'] =           $r['ID'];
                $data[$p.'geocode_area'] =        $r['match_area'];
                $data[$p.'geocode_type'] =        $r['match_type'];
                $data[$p.'geocode_quality'] =     $r['match_quality'];
                $data[$p.'lat'] =                 $r['lat'];
                $data[$p.'lon'] =                 $r['lon'];
            }
            $data['process_maps'] = 0;
            $this->update($data, true, $reveal_modification);
        }
    }

    public function privacy_check_viewer_access($r, $type)
    {
        switch ($r[$type]){
            case '':
                return false;
            break;
            case 'A':
                return true;
            break;
        }
        if ($this->_current_user_rights['isPUBLIC']) {
          // Site or group - both invalid for public
            return false;
        }
        if ($r[$type]=='S' && $this->_current_user_rights['isSYSMEMBER']) {
          // Recognised approved Site Users
            return true;
        }
        if ($this->_current_user_groups_access_csv=='' || $r['groups']=='') {
            return false;
        }
        $visitor_groups = explode(',', $this->_current_user_groups_access_csv);
        $member_groups =  explode(',', $r['groups']);
        foreach ($visitor_groups as $v) {
            if (in_array($v, $member_groups)) {
                return true;
            }
        }
        return false;
    }

    public static function send_email_for_new_user()
    {
        global $action_parameters;
      //y($action_parameters);die;
      // Used with product actions to send new signup details
        if (!$_SESSION['user_created']) {
            return true;
        }
        $personID =             $_SESSION['user_created'];
        $template_name =        $action_parameters['destinationValue'];
        $Obj_Mail_Template =    new Mail_Template;
        $templateID =           $Obj_Mail_Template->get_ID_by_name($template_name, SYS_ID);
        if (!$templateID) {
            do_log(
                3,
                __CLASS__."::".__FUNCTION__.'()',
                '(none)',
                'Invalid template specified in component - '.$template_name
            );
            return false;
        }
        $Obj_Mail_Template->_set_ID($templateID);
        $result = $Obj_Mail_Template->send_email($personID);
        do_log(
            1,
            __CLASS__."::".__FUNCTION__.'()',
            '(none)',
            'Signup for '.$personID.' using template '.$template_name.' - result: '.$result
        );
        unset($_SESSION['user_created']);
    }

    public function send_email_wizard()
    {
        return "Send Email Wizard";
    }

    public function set_groups_list_description($reveal_modification = false)
    {
        $perms = $this->get_group_membership();
        $entries = array();
        foreach ($perms as $perm) {
            $entries[]=
                 $perm['name']
                ." ["
                .($perm['permVIEWER'] ?         'V' : '-')
                .($perm['permEDITOR'] ?         'E' : '-')
                .($perm['permEMAILRECIPIENT'] ? 'R' : '-')
                .($perm['permEMAILOPTOUT'] ?    'O' : '-')
                .']';
            sort($entries);
        }
      // Limit entry to 1000 characters
        $value =    implode('<br />', $entries);
        if (strlen($value)>987) {
            $value = substr($value, 0, 984).'...';
        }
        $this->set_field('groups_list', '<nobr>'.$value.'</nobr>', true, $reveal_modification);
    }

    public function set_random_password()
    {
        $pwd = get_random_password();
        component_result_set('PPassword', $pwd);     // so we know what to say in email
        $this->set_field('PPassword', encrypt($pwd));
    }

    public function uniq_PUsername($prefix = "")
    {
        while (true) {
            $newPUsername =  $prefix.mt_rand(0, mt_getrandmax());
            $sql =
                 "SELECT\n"
                ."  COUNT(*)\n"
                ."FROM\n"
                ."  `".$this->table."`\n"
                ."WHERE\n"
                ."  `PUsername` = \"$newPUsername\"";
            if ($this->get_field_for_sql($sql)==0) {
                return $newPUsername;
            }
        }
    }

    public function view_profile()
    {
        if ($this->_get_ID()=="") {
            return false;
        }
        $Obj = new Report();
        return $Obj->draw_form_view('user', $this->_get_ID(), true, true);
    }

    public function get_version()
    {
        return VERSION_PERSON;
    }
}
