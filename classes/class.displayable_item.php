<?php
define('VERSION_DISPLAYABLE_ITEM', '1.0.151');
/*
Version History:
  1.0.151 (2014-01-31)
    1) Changes to internally used parameters in Displayable_Item::_draw_listings_load_records():
         Old: limit,            order_by
         New: results_limit,    results_order
    2) Now PSR-2 Compliant

  (Older version history in class.displayable_item.txt)
*/
class Displayable_Item extends Block_Layout
{
    protected $_type =                          '';
    protected $_ajax_mode =                     false;
    protected $_args =                          array();
    protected $_block_layout =                  array();
    protected $_block_layout_name =             '';
    protected $_categories_arr =                array();
    protected $_context_menu_ID =               '';
    protected $_cp =                            array();    // Component parameters
    protected $_cp_are_fixed =                  false;      // CPs only passed via args
    protected $_cp_defaults =                   array();
    protected $_cp_vars =                       array();
    protected $_css =                           '';
    protected $_current_user_rights =           array();
    protected $_filter_offset =                 0;
    protected $_grouping_tabs =                 array();
    protected $_grouping_tab_current =          '';
    protected $_grouping_tab_selected =         '';
    protected $_groups_arr =                    array();
    protected $_has_enable_flag =               false;
    protected $_has_publish_date =              false;
    protected $_html =                          '';
    protected $_ident =                         '';
    protected $_is_expired_publication =        false;
    protected $_is_pending_publication =        false;
    protected $_instance =                      '';
    protected $_js =                            '';
    protected $_letter_anchors =                array();
    protected $_letter_anchor_current =         '';
    protected $_mode =                          '';
    protected $_msg =                           '';
    protected $_paging_controls_current_pos =   '';
    protected $_paging_controls_html =          '';
    protected $_records =                       array();
    protected $_records_total =                 0;
    protected $_search_field =                  '';
    protected $_search_value =                  '';

    public function __construct($table = '', $ID = '', $systemID = SYS_ID)
    {
        parent::__construct($table, $ID, $systemID);
    }

    public function _get_type()
    {
        return $this->_type;
    }
    public function _set_type($type)
    {
        $this->_type = $type;
    }
    protected function _get_context_menu_ID()
    {
        return $this->_context_menu_ID ? $this->_context_menu_ID : get_js_safe_ID($this->_get_type());
    }
    protected function _get_has_enable_flag()
    {
        return $this->_has_enable_flag;
    }
    protected function _get_has_publish_date()
    {
        return $this->_has_publish_date;
    }
    protected function _set_context_menu_ID($value)
    {
        $this->_context_menu_ID = $value;
    }
    protected function _set_has_enable_flag($value)
    {
        $this->_has_enable_flag = $value;
    }
    protected function _set_has_publish_date($value)
    {
        $this->_has_publish_date = $value;
    }

    protected function _common_draw_help()
    {
        $this->_html.=
        Component_Base::get_help(
            $this->_ident,
            $this->_instance,
            $this->_cp_are_fixed,
            $this->_cp_vars,
            $this->_cp_defaults
        );
    }

    protected function _common_draw_status()
    {
        $this->_html.=      HTML::draw_status($this->_safe_ID, $this->_msg);
    }

    public function _common_load_block_layout()
    {
        $Obj_BlockLayout =          new Block_Layout;
        $blockLayoutID =            $Obj_BlockLayout->get_ID_by_name($this->_get('_block_layout_name'));
        if (!$blockLayoutID) {
            $this->_msg.=
                 "<b>Error:</b> There is no such Block Layout as '".$this->_get('_block_layout_name')."'";
            $this->_html.=
                 "<div>"
                ."&nbsp;<b>Error:</b> There is no such Block Layout as '".$this->_get('_block_layout_name')."'"
                ."</div>\n";
            throw new Exception($this->_msg);
        }
        $Obj_BlockLayout->_set_ID($blockLayoutID);
        $this->_block_layout =    $Obj_BlockLayout->load();
        $css =                    ($this->_mode=='detail' ? 'detail' : 'listings');
        $Obj_BlockLayout->draw_css_include($css);
    }

    protected function _common_load_parameters()
    {
        $settings =
        Component_Base::get_parameter_defaults_and_values(
            $this->_ident,
            $this->_instance,
            $this->_cp_are_fixed,
            $this->_cp_vars,
            $this->_args
        );
        $this->_cp_defaults =   $settings['defaults'];
        $this->_cp =            $settings['parameters'];
    }

    protected function _common_load_user_rights()
    {
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isUSERADMIN =        get_person_permission("USERADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =      get_person_permission("SYSEDITOR", $this->record['group_assign_csv']);
        $isPUBLIC =         get_person_permission("PUBLIC");
        $this->_current_user_rights['canPublish'] =
            $isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER;
        $this->_current_user_rights['canEdit'] =
            $isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER || $isSYSEDITOR;
        $this->_current_user_rights['canRate'] =
            $this->record['ratings_allow']=='all' || ($this->record['ratings_allow']=='registered' && !$isPUBLIC);
        $this->_current_user_rights['isUSERADMIN'] =
            $isSYSADMIN || $isSYSAPPROVER || $isUSERADMIN || $isMASTERADMIN;
        $this->_current_user_rights['isSYSADMIN'] =
            $isSYSADMIN || $isMASTERADMIN;
        $this->_current_user_rights['isMASTERADMIN'] =
            $isMASTERADMIN;
    }

    protected function _common_render()
    {
        return $this->_html;
    }


    public function do_tracking($status, $allow_redirect = true)
    {
        global $page_vars;
        if ($personID = get_userID()) {
            $IP =         $_SERVER['REMOTE_ADDR'];
            $now =        date("Y-m-d H:i:s", time());
            $referer =    @$_SERVER["HTTP_REFERER"];
            $browser =    get_browser_safe();
            $personID =   get_userID();
            $data = array(
                'browser_platform' =>   @$browser['platform'],
                'browser_type' =>       @$browser['browser'],
                'browser_version' =>    @$browser['version'],
                'history_datetime' =>   $now,
                'IP' =>                 $IP,
                'path' =>               (isset($page_vars['path']) ? $page_vars['path'] : $_SERVER["REQUEST_URI"]),
                'personID' =>           $personID,
                'referer' =>            $referer,
                'status' =>             $status
            );
            $value = addslashes(serialize($data));
            $Obj_System = new System(SYS_ID);
            $Obj_System->set_field('last_user_access', $value, true, false);
        }
        if ($status=='200' && isset($page_vars['object_type']) && System::has_feature('Activity-Tracking')) {
            $Obj_Activity = new Activity;
            $Obj_Activity->do_tracking('visits', $page_vars['object_type'], $page_vars['ID'], 1);
        }
    }

    public function draw_associated()
    {
        if (!$this->_draw_associated_setup()) {
            return $this->_common_render();
        }
        $this->_html.= $this->convert_Block_Layout($this->_block_layout['single_item_detail']);
        return $this->_common_render();
    }

    protected function _draw_associated_setup()
    {
        global $page_vars;
        if ($this->record===false && !$this->load()) {
            $this->_html.=    $this->_draw_detail_draw_error('404');
            return false;
        }
        $this->_cp_vars =   $this->_cp_vars_listings;
        $this->xmlfields_decode($this->record);
        $this->_mode =      'detail';
        $this->_ident =     get_js_safe_ID(
            $this->_mode.'_'.$this->_get_type().(substr($this->_get_type(), -1, 1)=='s' ? '' : 's')
        );
        $this->_common_load_user_rights();
        $this->_common_load_parameters();
        $this->_block_layout_name = $this->_cp['block_layout_for_associated'];
        $this->_cp['thumbnail_image'] = 's';
        $this->_cp['thumbnail_width'] = 100;
        $this->_common_load_block_layout();
        if (!$this->_block_layout['ID']) {
            $this->_html.=
                 "<div>"
                ."&nbsp;<b>Error:</b> There is no such Block Layout as '".$this->_cp['block_layout_for_associated']."'"
                ."</div>\n";
            return false;
        }
        $this->_draw_detail_load_publication_status();
        if ($this->_is_expired_publication && !$this->_current_user_rights['canPublish']) {
            $this->_html.= $this->_draw_detail_draw_error('403');
            return false;
        }
        if ($this->_is_pending_publication && !$this->_current_user_rights['canPublish']) {
            $this->_html.= $this->_draw_detail_draw_error('403');
            return false;
        }
        $enabled = (isset($this->record['enabled']) && $this->record['enabled']==0 ? false : true);
        if (!$enabled && !$this->_current_user_rights['canPublish']) {
            $this->_html.= $this->_draw_detail_draw_error('403');
            return false;
        }
        return true;
    }

    public function draw_detail()
    {
        if (!$this->_draw_detail_setup()) {
            return $this->_common_render();
        }
        $this->_html.= $this->convert_Block_Layout($this->_block_layout['single_item_detail']);
        $this->do_tracking("200");
        return $this->_common_render();
    }

    public function _draw_detail_draw_context_toolbar()
    {
        global $submode, $print;
        $edit_params =  $this->get_edit_params();
        $args =
        array(
            'allowPopupEdit' =>     $this->_current_user_rights['canEdit'],
            'object_type' =>        get_class($this),
            'object_name' =>        $this->_get_object_name(),
            'ID' =>                 $this->_get_ID(),
            'edit_params' =>        $edit_params,
            'group_assign_csv' =>   $this->record['group_assign_csv']
        );
        $Obj_HTML = new HTML;
        if ($this->_current_user_rights['canEdit'] && $submode!='edit' && $print!=1) {
            return $Obj_HTML->draw_toolbar('posting_edit', $args);
        }
    }

    protected function _draw_detail_draw_error($type)
    {
        switch($type) {
            case "403":
                $this->do_tracking($type);
                $this->_html.= "<h1>".$this->_get_object_name()." is unavailable</h1>\n";
                break;
            case "404":
                $this->do_tracking($type);
                $this->_html.=
                     "<h1>".$this->_get_object_name()." is unavailable</h1>\n"
                    ."<p>Sorry, we have no record of the ".$this->_get_object_name()." requested.</p>";
                break;
        }
    }


    public function _draw_detail_test_if_grouping_has_changed()
    {
        $results_grouping = isset($this->_cp['results_grouping']) ? $this->_cp['results_grouping'] : false;
        global $YYYY;
        if (!count($this->_grouping_tabs)) {
            return false;
        }
        sscanf($this->record['date'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
        $_YYYY =  ($_YYYY == "0000" ? $YYYY : $_YYYY);
        switch ($results_grouping){
            case "month":
                $idx =          $this->_ident."_".$_YYYY."_".$_MM;
                if ($idx != $this->_grouping_tab_current) {
                    return true;
                }
                break;
            case "year":
                $idx =          $this->_ident."_".$_YYYY;
                if ($idx != $this->_grouping_tab_current) {
                    return true;
                }
                break;
        }
        return false;
    }

    public function _draw_detail_handle_changes()
    {
        global $submode;
        if ($submode=='save' && $this->_current_user_rights['canEdit']) {
            $content =        $_REQUEST['content'];
            $content_text =   strip_tags($content);
            $data =
            array(
                'content' =>      addslashes($content),
                'content_text' => addslashes($content_text)
            );
            $this->update($data);
            $this->record['content'] =    $content;
        }
    }
    public function _draw_detail_include_og_support()
    {
        global $system_vars, $page_vars;
        $img = false;
        $thumbnail_fields = array('thumbnail_large','thumbnail_medium','thumbnail_small');
        foreach ($thumbnail_fields as $field) {
            if (isset($this->record[$field]) && $this->record[$field]!='') {
                $img = $this->record[$field];
                break; // use the largest available
            }
        }
        $summary =  trim(preg_replace('/\s+/', ' ', str_replace('&nbsp;', ' ', $page_vars['content_text'])));
        if (strlen($summary)>1000) {
            $summary = substr($summary, 0, 1000)."...";
        }
        Page::push_content(
            'head_top',
            "<meta property=\"og:description\" content=\"".str_replace('"', '&quot;', $summary)."\" />\n"
            .($img ?
                 "<meta property=\"og:image\""
                ." content=\"".trim($system_vars['URL'], "/").BASE_PATH.trim($img, "/")."\""
                ." />\n"
              :
                ""
             )
            ."<meta property=\"og:site_name\" content=\"".$system_vars['textEnglish']."\" />\n"
            ."<meta property=\"og:title\" content=\"".$page_vars['title']."\" />\n"
            ."<meta property=\"og:type\" content=\"website\" />\n"
            ."<meta property=\"og:url\" content=\"".$page_vars['absolute_URL']."\" />\n"
        );
    }

    public function _draw_detail_include_js()
    {
      // Extended if needed
    }

    public function _draw_detail_load_publication_status()
    {
        if ($this->_get_has_publish_date()) {
            $status = $this->test_publish_date();
            switch ($status){
                case "good":
                    return;
                break;
                case "expired":
                    $this->_is_expired_publication = true;
                    return;
                break;
                case "pending":
                    $this->_is_pending_publication = true;
                    return;
                break;
            }
        }
    }

    protected function _draw_detail_setup()
    {
        global $page_vars;
        if ($this->record===false && !$this->load()) {
            $this->_html.=    $this->_draw_detail_draw_error('404');
            return false;
        }
        $this->_cp_vars =           $this->_cp_vars_detail;
        $this->xmlfields_decode($this->record);
        $this->_mode =              'detail';
        $this->_ident =             $this->_get_object_name()."_".$this->_mode;
        $this->_common_load_user_rights();
        $this->_common_load_parameters();
        $this->_block_layout_name = $this->_cp['block_layout'];
        $this->_common_load_block_layout();
        $page_vars['block_layout']= $this->_block_layout;
        $this->_draw_detail_include_js();
        $this->_html.= $this->_draw_detail_draw_context_toolbar();
        $this->_common_draw_help();
        if (!$this->_block_layout['ID']) {
            $this->_html.=
                 "<div>"
                ."&nbsp;<b>Error:</b> There is no such Block Layout as '".$this->_cp['block_layout']."'"
                ."</div>\n";
            return false;
        }
        if (method_exists($this, 'is_visible') && !$this->is_visible($this->record)) {
            $this->_html.= $this->_draw_detail_draw_error('403');
            return false;
        }
        $this->_draw_detail_load_publication_status();
        if ($this->_is_expired_publication && !$this->_current_user_rights['canPublish']) {
            $this->_html.= $this->_draw_detail_draw_error('403');
            return false;
        }
        if ($this->_is_pending_publication && !$this->_current_user_rights['canPublish']) {
            $this->_html.= $this->_draw_detail_draw_error('403');
            return false;
        }
        if (
            isset($this->record['enabled']) &&
            $this->record['enabled']==0 &&
            !$this->_current_user_rights['canPublish']
        ) {
            $this->_html.= $this->_draw_detail_draw_error('403');
            return false;
        }
        $this->_draw_detail_handle_changes();
        $this->_draw_detail_include_og_support();
        $anchor_ID = System::get_item_version('system_family').'_main_content';
        $this->_html.=
             "<input type='hidden' name='ID' id='ID' value='".$this->_get_ID()."' />"
            ."<a name=\"".$anchor_ID."\" id=\"".$anchor_ID."\"></a>\n";
        return true;
    }

    protected function _draw_detail_test_ratings_allow()
    {
        $isPUBLIC = get_person_permission("PUBLIC");
        return
        $this->record['ratings_allow']=='all' ||
        ($this->record['ratings_allow']=='registered' && !$isPUBLIC);
    }

    public function draw_link($type, $record = false, $args = array())
    {
        if (!$record) {
            $record = $this->record;
        }
        $ID =           $record['ID'];
        $systemID =     $record['systemID'];
        $local =        $systemID == SYS_ID;
        if (!$local && !isset($record['systemURL'])) {
          // Only when viewing an item on another system in single-item mode
            $Obj = new System($systemID);
            $systemURL =  $Obj->get_field('URL');
            $record['systemURL'] = $systemURL;
        }
        $popup =        (isset($record['popup']) ? ($record['popup']) : 0);
        $_URL =         (isset($record['URL']) ? ($record['URL']) : "");
        if (substr($_URL, 0, 8)=='./?page=') {
            $_URL = BASE_PATH.substr($_URL, 8);
        }
        $relLink =      substr($_URL, 0, 4)!="http";
        $URL =          $this->get_URL($record);
        switch ($type) {
            case 'add_to_outlook':
                return
                     "<a href=\""
                    .trim(($local ? BASE_PATH : $record['systemURL']), "/")
                    ."/export/icalendar/".$ID."\" "
                    ."title=\"Add this ".$this->_get_object_name().$this->plural(1)
                    ." to your Outlook Calendar (Requires Outlook 2000 or later)"
                    ."\" rel='external'"
                    .">"
                    .HTML::draw_icon('add_to_outlook', true)
                    ."</a>";
            break;
            case "buy_event":
                return
                     "<a href=\"".$URL."#purchase\""
                    ." title=\"Pay to register for this ".$this->_get_object_name().$this->plural(1)."\">"
                    .HTML::draw_icon('buy_event', true)
                    ."</a>";
            break;
            case "link":
                return
                     "<a href=\""
                    .($systemID!=SYS_ID && $relLink ? $record['systemURL'] : "")
                    .$_URL
                    ."\""
                    ." rel='external'"
                    ." title=\""
                    .($systemID==SYS_ID && $relLink ? "Link" : "Link to external content")
                    .($popup || !$local ? " (opens in a new window)" : "")
                    ."\">"
                    .HTML::draw_icon('link', $popup||$systemID!=SYS_ID)."</a>";
            break;
            case 'map':
                return
                     "<a href=\"#\" onclick=\"popup_map('"
                    .(isset($record['systemURL']) ?
                        trim($record['systemURL'], '/')
                      :
                        ''
                     )
                     ."','event_map','".$ID."');return false;\""
                    ." title=\"View map (opens in a new window)\">"
                    .HTML::draw_icon('map', true)."</a>";
            break;
            case "media_download":
                $media_URL =
                ($record['enclosure_url']!='' ?
                ($systemID!=SYS_ID && substr($record['enclosure_url'], 0, 4)!="http" ?
                 trim($record['systemURL'], "/")."/".trim($record['enclosure_url'], "/")
                :
                 $record['enclosure_url']
                )
                : false);
                return
                     "<a href=\"".$media_URL."\" rel='external'"
                    ." title=\"Save Media"
                    .($record['enclosure_secs'] || $record['enclosure_size'] ?
                         " ("
                        .((int)$record['enclosure_secs']!=0 ?
                            "length ".seconds_to_hhmmss($record['enclosure_secs'])
                          :
                            ""
                         )
                        .((int)$record['enclosure_size']!=0 ?
                            ((int)$record['enclosure_secs']!=0 ? ", size " : "")
                            .number_format(two_dp($record['enclosure_size']/1024))."kb"
                         :
                            ""
                         )
                        .")"
                     :
                        ""
                     )
                    ."\">"
                    .HTML::draw_icon('media_download')."</a>";
            break;
            case "media_download_mini":
                $media_URL =
                ($record['enclosure_url']!='' ?
                    ($systemID!=SYS_ID && substr($record['enclosure_url'], 0, 4)!="http" ?
                        trim($record['systemURL'], "/")."/".trim($record['enclosure_url'], "/")
                     :
                        $record['enclosure_url']
                    )
                    :
                    false
                );
                return
                     "<a href=\"".$media_URL."\" rel='external'"
                    ." title=\"Save Media"
                    .($record['enclosure_secs'] || $record['enclosure_size'] ?
                        " ("
                        .((int)$record['enclosure_secs']!=0 ?
                            "length ".seconds_to_hhmmss($record['enclosure_secs'])
                          :
                            ""
                         )
                        .((int)$record['enclosure_size']!=0 ?
                            ((int)$record['enclosure_secs']!=0 ? ", size " : "")
                            .number_format(two_dp($record['enclosure_size']/1024))."kb"
                         :
                            ""
                         )
                        .")"
                      :
                        ""
                     )
                    ."\">"
                    .HTML::draw_icon('media_download_mini')."</a>";
            break;
            case "media_popup_player":
                $media_popup_URL =
                    ($record['enclosure_url']!='' ?
                        ($systemID!=SYS_ID ? trim($record['systemURL'], "/")."/" : BASE_PATH)
                        ."?command=podcast_player&amp;targetID=".$ID
                     :
                        false
                    );

                return
                     "<a href=\"".$media_popup_URL."\""
                    ." onclick=\"popWin('".$media_popup_URL."','".$ID."','resizable=1',320,120);return false;\" "
                    ." rel='external'"
                    ." title=\"Open Popup Media Player\">"
                    .HTML::draw_icon('media_player')."</a>";
            break;
            case 'read_more':
                return
                     "<a href=\"".$URL."\""
                    .($systemID!=SYS_ID ?
                        " rel='external' title=\"Read more (opens in a new window)\""
                      :
                        " title=\"Read more\""
                     )
                    .">"
                    .(isset($args['label']) && $args['label'] ?
                        $args['label']
                     :
                        HTML::draw_icon('more', $systemID!=SYS_ID)
                     )
                    ."</a>";

            break;
            case "register_event":
                return
                     "<a href=\"".$URL."#register\""
                    ." title=\"Register for this ".$this->_get_object_name().$this->plural(1)."\">"
                    .HTML::draw_icon('register_event', true)
                    ."</a>";
            break;
            case "register_event_large":
                return
                     "<a href=\""
                    .($systemID==SYS_ID ? BASE_PATH : trim($record['systemURL'], '/').'/')
                    ."register?selectID=".$ID."\""
                    .($systemID==SYS_ID ? '' : " rel='external'")
                    ." title=\"Register for this ".$this->_get_object_name().$this->plural(1)."\">"
                    ."<img src='".BASE_PATH."img/spacer' class='icons icon_register_event'"
                    ." alt='Register for this event"
                    .($systemID==SYS_ID ? "" : "\n(opens in a new window)")."'"
                    ." style='height:16px; width:20px; background-position:-124px 0px; border:0'"
                    ." /></a>";
        }
    }

    public function draw_listings($instance = '', $args = array(), $cp_are_fixed = false)
    {
        try {
            $this->_draw_listings_setup($instance, $args, $cp_are_fixed);
        } catch (Exception $e) {
            $this->_common_draw_help();
            $this->_draw_listings_draw_status();
            return $this->_draw_listings_render();
        }
        $this->_draw_listings_draw_status();
        $this->_common_draw_help();
        $this->_draw_listings_draw_add_icon();
        if (count($this->_records)==0) {
            $this->_draw_listings_draw_no_results();
            return $this->_draw_listings_render();
        }
        $this->_html.= $this->draw_Block_Layout();
        return $this->_draw_listings_render();
    }

    public function draw_listings_json($instance = '', $args = array(), $cp_are_fixed = false)
    {
        global $system_vars;
        $out = array(
        'css' =>  '',
        'js' =>   '',
        'html' => ''
        );
        $base = trim($system_vars['URL'], '/').'/';
        $cs_icons =   dechex(crc32(file_get_contents(SYS_IMAGES."icons.gif")));
        Page::$content = array(); // flush existing content
        Page::push_content('javascript_onload', "ecc.externalLinks()");
        Page::push_content(
            'style_include',
            "<link rel=\"stylesheet\" type=\"text/css\" href=\"/css/".System::get_item_version('css')."\" />"
        );
        Page::push_content(
            'style',
            "img.icon              { background-image:url(".$base."img/sysimg/icons.gif/".$cs_icons.");}\r\n"
            ."div.rating .img       { background-image:url(".$base."img/sysimg/icon_ratings_13x13.gif);}\r\n"
            .".icons                { background-image:url(".$base."img/sysimg/icons.gif/".$cs_icons.");}\r\n"
        );
        $html = convert_safe_to_php($this->draw_listings($instance, $args, $cp_are_fixed));
        if (isset(Page::$content['style'])) {
            $html.= "<style type='text/css'>".implode("\n", Page::$content['style'])."</style>";
        }
        if (isset(Page::$content['style_include'])) {
            $html.= implode("\n", Page::$content['style_include']);
        }
        if (isset(Page::$content['javascript_onload'])) {
            $out['js'] = implode("\n", Page::$content['javascript_onload']);
        }
        $html = absolute_path($html, $base);
        $html = preg_replace("/ onmouseover=[^\>]+\>/", ">", $html);
        $html = preg_replace("/<a class=\"icon_add_new\"(.+)><\/a>/", "", $html);
        $out['html'] = $html;
  //    $out['html'] = "<textarea style='width:100%;height:600px;'>".print_r($out,true)."</textarea>";
        return $out;
    }

    protected function do_submode()
    {
      // Overridden as required
    }

    protected function _draw_listings_draw_add_icon()
    {
        if (!$this->_current_user_rights['canEdit'] || !$this->_cp['box']==0) {
            return;
        }
        $edit_params =  $this->get_edit_params();
        $add_form =     $edit_params['report'];
        $uri_args =
             ($this->_categories_arr ? "&amp;category=".implode(',', $this->_categories_arr) : "")
            .(isset($this->communityID) && $this->communityID ? "&amp;communityID=".$this->communityID : "")
            .(isset($this->memberID) && $this->memberID ? "&amp;memberID=".$this->memberID : "");
        $popup =    get_popup_size($add_form);
        $this->_html.=
             "<a class=\"icon_add_new\" href=\"".BASE_PATH."details/".$add_form."/\""
            ." onclick=\""
            ."details('".$add_form."','','".$popup['h']."','".$popup['w']."','','','','".$uri_args."');return false;"
            ."\">"
            ."[ICON]11 11 1188 Add ".$this->_get_object_name()."&hellip;[/ICON]</a>";
    }

    protected function _draw_listings_draw_no_results()
    {
        $this->_groups_arr = (isset($this->_cp['filter_groups_list']) && $this->_cp['filter_groups_list'] ?
            explode(',', $this->_cp['filter_groups_list'])
         :
            array()
        );
        $this->_html.=
             "<div class='no_items'>"
            ."There are no "
            .(isset($this->_cp['filter_important']) && $this->_cp['filter_important']=='1' ?
                "important "
              :
                ""
             )
            .(isset($this->_cp['filter_important']) && $this->_cp['filter_important']=='0' ?
                "unimportant "
              :
                ""
             )
            .$this->_get_object_name().$this->plural('1,2')
            .(count($this->_categories_arr) || count($this->_groups_arr) ?
                " in "
              :
                ""
             )
            .(count($this->_categories_arr)==1 ?
                " the <b>".implode('', $this->_categories_arr)."</b> category"
              :
                ""
             )
            .(count($this->_categories_arr)==2 ?
                " either the <b>".implode('</b> or <b>', $this->_categories_arr)."</b> category"
              :
                ""
             )
            .(count($this->_categories_arr)>2 ?
                " any of the <b>".implode(', ', $this->_categories_arr)."</b> categories"
              :
                ""
             )
            .(count($this->_categories_arr) &&  count($this->_groups_arr) ?
                " and "
              :
                ""
             )
            .(count($this->_groups_arr)==1 ?
                " the <b>".implode('', $this->_groups_arr)."</b> group"
              :
                ""
             )
            .(count($this->_groups_arr)==2 ?
                " either the <b>".implode('</b> or <b>', $this->_groups_arr)."</b> group"
              :
                ""
             )
            .(count($this->_groups_arr)>2 ?
                " any of the <b>".implode(', ', $this->_groups_arr)."</b> groups"
              :
                ""
             )
            ." at this time."
            ."</div>\n";
    }

    protected function _draw_listings_draw_status()
    {
        $this->_html.= HTML::draw_status($this->_safe_ID, $this->_msg);
    }

    protected function _draw_listings_load_grouping_tabs()
    {
        $results_grouping = isset($this->_cp['results_grouping']) ? $this->_cp['results_grouping'] : false;
        $results_paging =   isset($this->_cp['results_paging']) ? $this->_cp['results_paging'] : false;
        if (!$results_grouping) {
            return;
        }
        if ($results_grouping && $results_paging) {
            $this->_html.=
                 "<p><b>Error</b>"
                ." You cannot use Grouping Tabs WITH Paging Controls - please choose one or the other.</p>";
            return;
        }
        global $YYYY;
        $tab_items = false;
        switch ($results_grouping){
            case "month":
                $old_MM = false;
                $old_YYYY = false;
                $tab_items = array();
                foreach ($this->_records as $record) {
                    sscanf($record['date'], "%04d-%02d", $_YYYY, $_MM);
                    $_YYYY =  ($_YYYY=="0000" ? $YYYY : $_YYYY);
                    $idx =     $_YYYY."_".$_MM;
                    if (!isset($tab_items[$idx])) {
                        $tab_items[$idx] = MM_to_MMM($_MM)." ".$_YYYY;
                    }
                }
                break;
            case "year":
                $old_YYYY = false;
                $tab_items = array();
                foreach ($this->_records as $record) {
                    sscanf($record['date'], "%04d", $_YYYY);
                    $_YYYY =  ($_YYYY=="0000" ? $YYYY : $_YYYY);
                    $idx =     $_YYYY;
                    if (!isset($tab_items[$idx])) {
                        $tab_items[$idx] = $_YYYY;
                    }
                }
                break;
        }
        if ($tab_items) {
            foreach ($tab_items as $key => $value) {
                $this->_grouping_tabs[] =
                array(
                    'ID' =>       $this->_ident."_".$key,
                    'label' =>    $value
                );
            }
            $this->_grouping_tab_selected =   $this->_grouping_tabs[0]['ID'];
            $this->_grouping_tab_current =    $this->_grouping_tabs[0]['ID'];
        }
    }

    protected function _draw_listings_include_js()
    {
      // Extended if needed
    }

    protected function _draw_listings_load_categories()
    {
        $this->_categories_arr = array();
        if (isset($this->_cp['filter_category_master']) && ($this->_cp['filter_category_master'])!='') {
            $c_arr = explode(',', $this->_cp['filter_category_master']);
            foreach ($c_arr as $c) {
                $this->_categories_arr[] = $c;
            }
        }
        if (isset($this->_cp['filter_category_list']) && $this->_cp['filter_category_list']!='*') {
            $c_arr = explode(',', $this->_cp['filter_category_list']);
            foreach ($c_arr as $c) {
                $this->_categories_arr[] = $c;
            }
        }
        sort($this->_categories_arr);
    }

    protected function _draw_listings_load_paging_controls()
    {
        global $page_vars;
        $results_limit =    isset($this->_cp['results_limit']) ? $this->_cp['results_limit'] : '';
        $results_paging =   isset($this->_cp['results_paging']) ? $this->_cp['results_paging'] : '';
        if (!$results_paging || $this->_records_total==count($this->_records)) {
            return;
        }
        $URL =
             BASE_PATH
            .trim($page_vars['relative_URL'], '/')
            .($page_vars['path_extension'] ? '/'.$page_vars['path_extension'] : '');
        $this->_paging_controls_current_pos =
             "<div class=\"".$this->_safe_ID."_nav_pos\">"
            ."Showing ".($this->_filter_offset+1)." to "
            .($this->_filter_offset+$results_limit>$this->_records_total ?
                $this->_records_total
              :
                $this->_filter_offset+$results_limit
             )
            ." of ".$this->_records_total
            ."</div>";
        if (!isset($_REQUEST['command']) || $_REQUEST['command']!=$this->_safe_ID."_load") {
            Page::push_content(
                "javascript",
                "function ".$this->_safe_ID."_paging(offset) {\n"
                ."  var post_vars='command=".$this->_safe_ID."_load&offset='+offset;\n"
                ."  window.focus();\n"
                ."  if (btn=geid('".$this->_safe_ID."_previous')) {btn.disabled=true;}\n"
                ."  if (btn=geid('".$this->_safe_ID."_next')) {btn.disabled=true;}\n"
                ."  var fn = function(){\n"
                ."    hidePopWin(null);\n"
                ."    externalLinks();\n"
                ."    \$('#".$this->_safe_ID."_content audio').mediaelementplayer();\n"
                .(Base::module_test('Church') ?
                    "    Logos.ReferenceTagging.tag(\$J('#".$this->_safe_ID."_content')[0])"
                  :
                    ""
                 )
                ."};\n"
                ."  show_popup_please_wait();\n"
                ."  ajax_post_streamed('".$URL."','".$this->_safe_ID."_content',post_vars,fn);\n"
                ."  return false;\n"
                ."}"
            );
        }
        switch($results_paging){
            case 1:
                $this->_paging_controls_html=
                    "<div class=\"".$this->_safe_ID."_nav\">"
                     .($this->_filter_offset>0 ?
                     "<input id=\"".$this->_safe_ID."_previous\" type='button' value='Previous'"
                    ." onclick=\"".$this->_safe_ID."_paging(".($this->_filter_offset+$results_limit*-1).");\""
                    ." />"
                     : ""
                     )
                    .($this->_records_total > $this->_filter_offset+$results_limit ?
                    "<input id=\"".$this->_safe_ID."_next\" type='button' value='Next'"
                    ." onclick=\"".$this->_safe_ID."_paging(".($this->_filter_offset+$results_limit*1).");\""
                    ." />"
                    : ""
                    )
                    ."</div>";
                break;
            case 2:
                $pages = ceil($this->_records_total/$results_limit);
                $this->_paging_controls_html.=
                     "<table class=\"pnav\" cellspacing=\"0\" cellpadding=\"0\""
                    ." summary=\"Paging controls for ".$this->_ident."\">\n"
                    ."  <tr>\n"
                    ."    <td class=\"pnav_pn\"><div>\n"
                    ."<a"
                    .($this->_filter_offset>0 ?
                         " title=\"View Previous Page\""
                        ." href=\"".$URL."?offset=".($this->_filter_offset+$results_limit*-1)."\""
                        ." onclick=\""
                        ."return ".$this->_safe_ID."_paging(".($this->_filter_offset+$results_limit*-1).");"
                        ."\""
                      :
                         " title=\"(View Previous Page)\" class='disabled' href=\"#\" onclick='return false;'"
                     )
                    .">Prev</a></div></td>\n"
                    ."<td class=\"pnav_num\"><div>\n";
                for ($i=0; $i<$pages; $i++) {
                    $this->_paging_controls_html.=
                        "<a title=\"View page ".($i+1)."\""
                        ." href=\"".$URL."?offset=".($results_limit*$i)."\""
                        ." class='"
                        .($this->_filter_offset==$i*$results_limit ? "current " : "")
                        .($this->_records_total==$i*$results_limit ? "last " : "")
                        ."'"
                        ." onclick=\"return ".$this->_safe_ID."_paging(".($results_limit*$i).");\">"
                        .($i+1)
                        ."</a> ";
                }
                $this->_paging_controls_html.=
                    "</div></td>\n"
                    ."<td class=\"pnav_pn\"><div>"
                    ."<a"
                    .($this->_filter_offset+$results_limit<$this->_records_total ?
                         " title=\"View Next Page\""
                        ." href=\"".$URL."?offset=".($this->_filter_offset+$results_limit)."\""
                        ." onclick=\"return ".$this->_safe_ID."_paging(".($this->_filter_offset+$results_limit).");\""
                      :
                        " title=\"(View Next Page)\" class='disabled' href=\"#\" onclick='return false;'"
                     )
                    .">Next</a></div></td>\n"
                    ."</tr>\n"
                    ."</table>\n";
                break;
        }
    }

    protected function _draw_listings_load_records()
    {
        global $YYYY, $MM;
        $results = $this->get_records(
            array(
                'byRemote' =>
                    false,
                'category' =>
                    $this->_cp['filter_category_list'],
                'category_master' =>
                    (isset($this->_cp['filter_category_master']) ?    $this->_cp['filter_category_master'] : false),
                'container_path' =>
                    (isset($this->_cp['filter_container_path']) ?     $this->_cp['filter_container_path'] : ''),
                'container_subs' =>
                    (isset($this->_cp['filter_container_subs']) ?     $this->_cp['filter_container_subs'] : ''),
                'DD' =>
                    '',
                'filter_date_duration' =>
                    (isset($this->_cp['filter_date_duration']) ?      $this->_cp['filter_date_duration'] : ''),
                'filter_date_units' =>
                    (isset($this->_cp['filter_date_units']) ?         $this->_cp['filter_date_units'] : ''),
                'filter_range_address' =>
                    (isset($this->_cp['filter_range_address']) ?      $this->_cp['filter_range_address'] : ''),
                'filter_range_distance' =>
                    (isset($this->_cp['filter_range_distance']) ?     $this->_cp['filter_range_distance'] : ''),
                'filter_range_lat' =>
                    (isset($this->_cp['filter_range_lat']) ?          $this->_cp['filter_range_lat'] : ''),
                'filter_range_lon' =>
                    (isset($this->_cp['filter_range_lon']) ?          $this->_cp['filter_range_lon'] : ''),
                'filter_range_units' =>
                    (isset($this->_cp['filter_range_units']) ?        $this->_cp['filter_range_units'] : ''),
                'important' =>
                    (isset($this->_cp['filter_important']) ?          $this->_cp['filter_important'] : ''),
                'memberID' =>
                    (isset($this->_cp['filter_memberID']) ?           $this->_cp['filter_memberID'] : ''),
                'MM' =>
                    $MM,
                'offset' =>
                    $this->_filter_offset,
                'personID' =>
                    (isset($this->_cp['filter_personID']) ?           $this->_cp['filter_personID'] : ''),
                'results_limit' =>
                    $this->_cp['results_limit'],
                'results_order' =>
                    (isset($this->_cp['results_order']) ?             $this->_cp['results_order'] : 'date'),
                'what' =>
                    (isset($this->_cp['filter_what']) ?               $this->_cp['filter_what'] : 'all'),
                'YYYY' =>
                    $YYYY
            )
        );
        $this->_records =           $results['data'];
        $this->_records_total =     $results['total'];
    }

    protected function _draw_listings_render()
    {
        global $print;
        if (isset($_REQUEST['command'])) {
            switch($_REQUEST['command']){
                case $this->_safe_ID."_cart":
                case $this->_safe_ID."_empty":
                case $this->_safe_ID."_load":
                    $out =
                    array(
                        'js' =>   $this->_js,
                        'html' => convert_safe_to_php($this->_html)
                    );
                    $Obj_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                    // so we get an assoc array as output instead of some weird object
                    header('Content-Type: application/json');
                    print $Obj_json->encode($out);
                    die;
                break;
            }
        }
        $content =
        $this->draw_panel_box(
            $this->_cp['box'],
            $this->_cp['box_title'],
            $this->_html,
            $this->_cp['box_title_link'],
            $this->_cp['box_title_link_page'],
            $this->_cp['box_rss_link'],
            $this->_safe_ID,
            $this->_cp['box_width'],
            $this->_cp['box']==2&&$print!="1"
        );
        return $content;
    }

    protected function _draw_listings_setup($instance = '', $args = array(), $cp_are_fixed = false)
    {
        $this->_filter_offset =     (isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0);
        $this->_args =              $args;
        $this->_instance =          $instance;
        $this->_cp_are_fixed =      $cp_are_fixed;
        $this->_cp_vars =           $this->_cp_vars_listings;
        $this->_mode =              'list';
        $this->_ident =
            get_js_safe_ID($this->_mode.'_'.$this->_get_type().(substr($this->_get_type(), -1, 1)=='s' ? '' : 's'));
        $this->_safe_ID =
            get_js_safe_ID($this->_ident."_".$this->_instance);
        $this->_context_menu_ID =   $this->_get_context_menu_ID();
        $this->_draw_listings_set_shop_page_if_relevant();
        $this->_common_load_user_rights();
        $this->_common_load_parameters();
        $this->_draw_listings_load_categories();
        $this->_draw_listings_include_js();
        $this->_block_layout_name = $this->_cp['block_layout'];
        $this->_common_load_block_layout();
        if ($this->_current_user_rights['canEdit'] && get_var('source')==$this->_safe_ID) {
            $this->_msg = $this->do_submode();
        }
        $this->_draw_listings_load_records();
        $this->_draw_listings_load_grouping_tabs();
        $this->_draw_listings_load_paging_controls();
        $this->_draw_listings_update_cart();
    }

    protected function _draw_listings_set_shop_page_if_relevant()
    {
      // Do nothing - override if needed
    }

    protected function _draw_listings_update_cart()
    {
        if (isset($_REQUEST['command']) && $_REQUEST['command']==$this->_safe_ID."_cart") {
            if ($_REQUEST['targetID']!="") {
                if ((int)$_REQUEST['targetValue']<1) {
                    Cart::item_remove($_REQUEST['targetID']);
                } else {
                    Cart::item_set_quantity($_REQUEST['targetID'], $_REQUEST['targetValue']);
                }
            }
        }
        if (isset($_REQUEST['command']) && $_REQUEST['command']==$this->_safe_ID."_empty") {
            Cart::empty_cart();
        }
    }

    public function draw_comments_block($allow_add = 'none')
    {
        $Obj = new Comment;
        return
             "<div class='clr_b'></div>\n"
            ."<div id='comments_list' class='comments_list'>"
            ."<a name='anchor_comments_list' id='anchor_comments_list'></a>"
            .$Obj->get_comments_all_html($this->_get_object_name(), $this->_get_ID(), $allow_add)
            ."<div class='clr_b'></div>\n"
            ."</div>"
            .(System::has_feature('Comments') &&
             ($allow_add=='all' || ($allow_add=='registered' &&  get_person_permission("SYSLOGON"))) ?
                 "<div id='comment_new' class='comments_list'>"
                .$Obj->get_new_comment_link($this->_get_object_name(), $this->_get_ID())
                ."</div>"
             :
                ""
             );
    }

    public function draw_from_recordset($records, $args)
    {
      // Used by the following functions:
      //   Component_Articles_Rotator::draw()
      //   Component::category_tabber()
      //   Component::category_stacker()
        $out = "";
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSEDITOR =      get_person_permission("SYSEDITOR");
        $isSYSMEMBER =      get_person_permission("SYSMEMBER");
        $canEdit =          ($isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR);
        switch(get_class($this)){
            case "Product":
                $category_type =    "Product Category";
                $cm_type =          "product";
                $head_class =       "product_head";
                $body_class =       "product_body";
                break;
            case "Article":
                $category_type =    "Article Category";
                $cm_type =          "article";
                $head_class =       "article_head";
                $body_class =       "article_body";
                break;
        }
        for ($i=0; $i<count($records); $i++) {
            $record = $records[$i];
            $last = ($i==count($records)-1);
            $this->record = $record;
            $link_arr =   array();
            $_URL =       $record['URL'];
            $URL =        $this->get_URL($record);
            $read_more_URL = $URL;
            if ($_URL!='' && $args['links_point_to_URL']==1) {
                $URL =          $_URL;
                $URL_popup =    ($record['popup']==1 ? true : false);
                $URL_title =    "Linked content".($URL_popup ? " (opens in a new window)" : "");
            } else {
                $URL =        $this->get_URL($record);
                $URL_popup =  $record['systemID']!=SYS_ID;
                $URL_title =  "Read More".($URL_popup ? " (opens in a new window)" : "");
            }
            $ID =         $record['ID'];
            $relLink =    substr($_URL, 0, 4)!="http";
            $popup =      $record['popup'];
            $this->_set_ID($ID);
            $read_more = false;
    //      if ($args['content_use_summary'] && trim($record['content_summary'])) {
            $read_link =
            "<a href=\"".$URL."\""
            .($URL_popup ? " rel='external'" : "")
            ." title=\"".$URL_title."\""
            .">";
            $read_more_link =
            "<a href=\"".$read_more_URL."\""
            .($record['systemID']!=SYS_ID ?
             " rel='external' title=\"Read more (opens in a new window)\"" :
             " title=\"Read more\""
            )
            .">";
            if ($args['content_use_summary']==1 && trim($record['content_summary'])) {
                $content =
                 $record['content_summary']
                ."<p><a class=\"more\" href=\"".$URL."\">"
                .($args['more_link_text'] ? $args['more_link_text'] : "(More)")
                ."</a></p>"
                ;
            } else {
                $content = $record['content'];
                if ($args['content_plaintext']) {
                    $content = strip_tags($content);
                }
                if ($args['content_char_limit']) {
                    if ($this->truncate_text($content, $args['content_char_limit'])) {
                        $content =
                         "<p>"
                        .$content
                        ." <span title=\"Continues&hellip;\">&hellip;</span> "
                        .$read_more_link
                        .($args['more_link_text'] ? $args['more_link_text'] : "(More)")
                        ."</a></p>"
                        ;
                    }
                }
                if ($this->truncate_more($content)) {
                    $read_more = "<span class='nowrap'>".$read_link."(Read More)</a></span>";
                    $content =
                    str_replace(
                        "<span title='Continues&hellip;'>&hellip;</span></p>",
                        "&hellip; ".$read_more."</p>",
                        $content
                    );
                }
            }
        // Link:
            if ($_URL!='' && $args['links_point_to_URL']!=1) {
                $link_arr[] =
                 "<a href=\""
                .($record['systemID']!=SYS_ID && $relLink ? $record['systemURL'] : "")
                .$record['URL']
                ."\""
                .($popup || $record['systemID']!=SYS_ID ? " rel='external'" : "")
                ." title=\""
                .($record['systemID']==SYS_ID && $relLink ? "Link" : "Link to external content")
                .($popup || $record['systemID']!=SYS_ID ? " (opens in a new window)" : "")
                ."\">"
                .HTML::draw_icon('link', $popup||$record['systemID']!=SYS_ID)."</a>";
            }
            $img = false;
            if ($args['thumbnail_show']) {
                switch ($args['thumbnail_image']){
                    case "s":   $img = $record['thumbnail_small'];
                        break;
                    case "m":   $img = $record['thumbnail_medium'];
                        break;
                    case "l":   $img = $record['thumbnail_large'];
                        break;
                    default:    $img = false;
                        break;
                }
            }
            $thumbnail_file = (substr($img, 0, strlen(BASE_PATH))==BASE_PATH ?
                './'.substr($img, strlen(BASE_PATH))
             :
                $img
            );
            if (!$img || !file_exists($thumbnail_file)) {
                $img = false;
            } else {
                $thumbnail_img =
                BASE_PATH."img/sysimg?img=".$thumbnail_file
                .($args['thumbnail_width'] ?
                "&amp;resize=1&amp;maintain=1&amp;width=".$args['thumbnail_width']
                : ""
                );
            }
            $extra_fields = "";
            if ($args['extra_fields_list']!='') {
                $extra_fields_arr = explode(",", $args['extra_fields_list']);
                $ObjGroup =   new Group;
                $personID =   get_userID();
                foreach ($extra_fields_arr as $field_pair) {
                    $field_arr = explode("|", $field_pair);
                    $field = $field_arr[0];
                    $label = (isset($field_arr[1]) ? $field_arr[1] : "");
                    $group =    (isset($field_arr[2]) ? $field_arr[2] : false);
                    $show =     true;
                    if ($group) {
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
                    $value = $record[$field];
                    if ($show && $value) {
                        $extra_fields .=
                         "<div class=\"".$field."\">\n"
                        ."<div class='label'>".$label."</div>"
                        ."<div class='value'>".$value."</div>"
                        ."</div><br />";
                    }
                }
                $extra_fields ="<div class='extra_fields'>".$extra_fields."<br class='extra_fields' /></div>\n";
            }
            if ($args['category_show']) {
                $Obj_Category = new Category;
                $categories = array();
                $category_csv = explode(",", $record['category']);
                foreach ($category_csv as $cat) {
                    $categories[$cat] = $cat;
                }
                $categories = $Obj_Category->get_labels_for_values(
                    "'".implode("','", array_keys($categories))."'",
                    "'".$category_type."'"
                );
      //        y($categories);die;
                $record['cat_label'] =    implode(", ", $categories);
            }
            $out.=
                 "<div class='item'>\n"
                ."  <div class='summary'"
                .($canEdit && isset($record['ID']) && $record['ID'] && ($record['systemID']==SYS_ID || $isMASTERADMIN) ?
                     " onmouseover=\""
                    ."if(!CM_visible('CM_".$cm_type."')) {"
                    ."this.style.backgroundColor='"
                    .($record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
                    ."';"
                    ."_CM.type='".$cm_type."';"
                    ."_CM.ID=".$ID.";"
                    ."_CM_text[0]='&quot;".str_replace(array("'","\""), array('','&quot;'), $record['title'])."&quot;';"
                    ."_CM_text[1]=_CM_text[0];_CM_ID[3]='';_CM_text[3]='';}\" "
                    ." onmouseout=\"this.style.backgroundColor='';_CM.type=''\""
                  :
                    ""
                 )
                .">\n"
                .($img && $args['thumbnail_at_top']==1?
                     ($args['thumbnail_link'] ? $read_link : "")
                    ."<img class='thumbnail_".$args['thumbnail_image']."' alt=\"".$record['title']."\""
                    ." src=\"".$thumbnail_img."\" />"
                    .($args['thumbnail_link'] ? "</a>" : "")
                  :
                    ""
                 )
                ."<div class='".$head_class."'>"
                .($args['category_show'] ?
                    "<h2 class='category'>".$record['cat_label']."</h2>"
                  :
                    ""
                 )
                ."<h2 class='title'>".$read_link.$record['title']."</a></h2>\n"
                .($args['subtitle_show'] && $record['subtitle'] ?
                    "<h2 class='subtitle'>".$record['subtitle']."</h2>"
                  :
                    ""
                 )
                .($args['date_show'] && substr($record['date'], 0, 10)!="0000-00-00"?
                     "<div class='subhead'>"
                    .format_date($record['date'])
                    .($record['comments_count'] ?
                         " | <a href=\"".$URL."#anchor_comments_list\">".$record['comments_count']." comment"
                        .($record['comments_count']==1 ? "" : "s")
                        ." &raquo;</a>"
                      :
                        ""
                     )
                    ."</div>\n"
                  :
                    ""
                 )
                ."</div>"
                .($img && $args['thumbnail_at_top']==0 ?
                     ($args['thumbnail_link'] ? $read_link : "")
                    ."<img class='thumbnail_".$args['thumbnail_image']."' alt=\"".$record['title']."\""
                    ." src=\"".$thumbnail_img."\" />"
                    .($args['thumbnail_link'] ? "</a>" : "")
                  :
                    ""
                 )
                .($extra_fields ? $extra_fields : "")
                ."<div class='".$body_class."'>"
                .($args['content_show'] ?
                    $content
                 :
                    ""
                 )
                .(isset($args['item_footer_component']) && $args['item_footer_component'] ?
                    draw_component_by_name($args['item_footer_component'], $record)
                  :
                    ""
                 )
                ."<div class='clr_b'></div>"
                ."</div>"
                .($args['author_show'] && $record['author'] ? "<address>".$record['author']."</address>" : "")
                .(count($link_arr) ?
                     "<div class='fl clr_b link'>\n"
                    .implode("<span class='fr txt_c' style='font-size:8pt;width:10px;#888;'>|</span>", $link_arr)
                    ."</div>\n"
                  :
                    ""
                 )
                ."<div class='clr_b' style='height:0;overflow:hidden;'>&nbsp;</div>\n"
                ."</div>\n"
                ."</div>\n"
                .($last ? "" : "<div class='clr_b item_spacer'></div>\n");
        }
        return $out;
    }

    public function draw_object_map_html($ident = false)
    {
        $this->_ident = ($ident ? $ident : $this->_get_type());
        $this->_draw_object_map_html_setup();
        foreach ($this->_ID_arr as $ID) {
            $this->_set_ID($ID);
            $this->_draw_object_map_html_get_data();
        }
        $this->_draw_object_map_html_sort_data();
        if (!count($this->_data_items)) {
            $this->_html.=
                 "<h2>Sorry!</h2>\n"
                ."<p>There are no map locations defined for the "
                .(count($this->_ID_arr)!=1 ? count($this->_ID_arr) : '')
                ." selected item"
                .(count($this->_ID_arr)==1 ? '' : 's')
                .".</p>\n";
            return $this->_html;
        }
        $this->_draw_object_map_html_get_range();
        if ($this->_range) {
            $this->_Obj_Map->map_zoom_to_fit($this->_range);
            $this->_Obj_Map->add_control_scale();
        } else {
            $this->_Obj_Map->map_centre(
                $this->_data_items[0]['map_lat'],
                $this->_data_items[0]['map_lon'],
                $this->_default_zoom
            );
        }
        $this->_Obj_Map->add_control_type();
        $this->_Obj_Map->add_control_large();
        $this->_draw_object_map_html_draw_map_points();
        $this->_draw_object_map_html_draw_frame_open();
        $this->_draw_object_map_html_draw_map();
        $this->_draw_object_map_html_draw_map_listing();
        $this->_draw_object_map_html_draw_frame_close();
        $this->_draw_object_map_html_draw_js_on_resize();
        return $this->_html;
    }

    protected function _draw_object_map_html_draw_frame_close()
    {
        $this->_html.=  "<div class='clear'>&nbsp;</div></div>";
    }

    protected function _draw_object_map_html_draw_frame_open()
    {
        $this->_html.=  "<div class='google_map_frame' id='google_map_".$this->_ident."_frame'>\n";
    }

    protected function _draw_object_map_html_draw_js_on_resize()
    {
        if (!$this->_map_maximize) {
            return;
        }
        Page::push_content(
            'javascript_onload',
            "popup_map_general_maximize('".$this->_ident."');"
            ."\$J(window).bind('resize', function(){popup_map_general_maximize('".$this->_ident."');});"
        );
    }

    protected function _draw_object_map_html_draw_map()
    {
        $args = array(
            'map_width' =>    $this->_map_width-(count($this->_data_items)>1 ? 325 : 0),
            'map_height' =>   $this->_map_height
        );
        $this->_html.= $this->_Obj_Map->draw($args);

    }

    protected function _draw_object_map_html_draw_map_listing()
    {
        if (count($this->_data_items)<2) {
            return;
        }
        $this->_html.=
            "<div class='google_map_listing' id='google_map_".$this->_ident."_listing'>\n";
        $this->_draw_object_map_html_draw_title();
        $type =     get_class($this);
        $Obj_BL = new $type;
        $Obj_BL->_safe_ID =           get_js_safe_ID($this->_ident."_".$this->_instance);
        $Obj_BL->_context_menu_ID =   $this->_get_context_menu_ID();
        $Obj_BL->_common_load_user_rights();
        foreach ($this->_data_items as $item) {
            $Obj_BL->load($item['record']);
            if (is_a($this, 'Person')) {
                $Obj_BL->_context_menu_ID =   $item['record']['type'];
            }
            $label = str_replace(array("&AMP;","& "), array("&amp;","&amp; "), trim($item['map_name']));
            $this->_html.=
                 $Obj_BL->BL_context_selection_start()
                ."<a href='#'"
                ." title=\"".strip_tags($label)." - Click to identify on Map\""
                ." onclick=\"return ecc_map.point.i(_google_map_".$this->_ident."_marker_".$item['ID'].");\""
                .">"
                .$label
                ."</a>\n"
                .$Obj_BL->BL_context_selection_end();
        }
        $this->_html.=
            "<br /></div>\n";
    }

    protected function _draw_object_map_html_draw_map_points()
    {
        foreach ($this->_data_items as $item) {
            $desc =
                trim(isset($item['map_desc']) && $item['map_desc'] ? $item['map_desc'] : $item['map_loc']);
            $circle_radius =
                (isset($item['map_area']) && $item['map_area']>0 ? sqrt(($item['map_area']/pi())) : false);
            $circle_line_color =      '#808080';
            $circle_line_width =      1;
            $circle_line_opacity =    0.6;
            $circle_fill_color =      '#808080';
            $circle_fill_opacity =    0.2;
            $this->_Obj_Map->add_marker_with_html(
                $item['map_lat'],
                $item['map_lon'],
                str_replace(
                    array("\r\n","\r","\n"),
                    array('<br />','<br />','<br />'),
                    $desc
                ),
                $item['ID'],
                false,
                false,
                $item['map_icon'],
                (count($this->_data_items)==1 ? true : false),
                strip_tags($item['map_name']),
                $circle_radius,
                $circle_line_color,
                $circle_line_width,
                $circle_line_opacity,
                $circle_fill_color,
                $circle_fill_opacity
            );
        }
    }

    protected function _draw_object_map_html_draw_title()
    {
        $this->_html.= "<h2>".$this->_map_title." (".count($this->_data_items).")</h2>";
    }

    protected function _draw_object_map_html_get_range()
    {
        $this->_range = Google_Map::get_bounds($this->_data_items);
    }

    protected function _draw_object_map_html_get_data()
    {
        if (!$this->load()) {
            return;
        }
        if ($this->record['map_lat'] || $this->record['map_lon']) {
            $this->_data_items[] = array(
                'ID' =>         $this->record['ID'],
                'map_area' =>   $this->record['map_geocode_area'],
                'map_lat' =>    $this->record['map_lat'],
                'map_lon' =>    $this->record['map_lon'],
                'map_loc' =>    $this->record['location'],
                'map_icon' =>   '',
                'map_name' =>   trim(title_case_string($this->record['title'])),
                'record' =>     $this->record
            );
        }
    }

    protected function _draw_object_map_html_setup()
    {
        $this->_Obj_Map =           new Google_Map($this->_ident, SYS_ID);
        $this->_data_items =        array();
        $this->_default_zoom =      14;
        $this->_ID_arr =            explode(',', sanitize('ID', get_var('ID')));
        $this->_map_height =        sanitize('range', get_var('height'), 100, 'n', 600);
        $this->_map_width =         sanitize('range', get_var('width'), 100, 'n', 600);
        $this->_map_maximize =      sanitize('range', get_var('maximize'), 0, 1, 1);
        $this->_map_title =
            (get_var('map_title') ? sanitize('html', get_var('map_title')) : $this->_get_object_name().' addresses');
        $this->_field_lat =         sanitize('html', get_var('field_lat'));
        $this->_field_lon =         sanitize('html', get_var('field_lon'));
        $this->_field_area =        sanitize('html', get_var('field_area'));
        $this->_field_info =        sanitize('html', get_var('field_info'));
        $this->_range =             false;
        $this->_common_load_user_rights();
    }

    protected function _draw_object_map_html_sort_data()
    {
        usort($this->_data_items, array($this,'_draw_object_map_html_sort_data_fn'));
    }

    protected function _draw_object_map_html_sort_data_fn($a, $b)
    {
        if (is_numeric($a['map_name']) && is_numeric($b['map_name'])) {
            return (float)$a['map_name'] - (float)$b['map_name'];
        }
        return strcmp(strtolower($a['map_name']), strtolower($b['map_name']));
    }

    public function draw_panel_box(
        $box,
        $title,
        $content,
        $link,
        $link_page,
        $rss_link,
        $ID = '',
        $width = 0,
        $shadow = 0
    ) {
        global $system_vars;
        $edit_params =    $this->get_edit_params();
        $add_form = $edit_params['report'];
        switch ($this->_get_type()){
            case "news":
                $rss_url =  $this->_get_type();
                break;
            default:
                $rss_url =  $this->_get_type().'s';
                break;
        }
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =       get_person_permission("SYSADMIN");
        $isSYSEDITOR =      get_person_permission("SYSEDITOR");
        $isSYSMEMBER =      get_person_permission("SYSMEMBER");
        $canEdit =          ($isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR);
        if (!$add_form) {
            $canEdit = false;
        }
        if ($canEdit) {
            $Obj =    new Report;
            $popup =  $Obj->get_popup_params_for_report_form($add_form);
            $categories_arr = array();
            if (isset($this->_cp['filter_category_master']) && ($this->_cp['filter_category_master'])!='') {
                $categories_arr = explode(',', $this->_cp['filter_category_master']);
            } elseif (isset($this->_cp['filter_category_list']) && $this->_cp['filter_category_list']!='*') {
                $categories_arr = explode(',', $this->_cp['filter_category_list']);
            }
            $uri_args =
            ($categories_arr ? "&amp;category=".implode(',', $categories_arr) : "")
            .(isset($this->memberID) && $this->memberID ? "&amp;memberID=".$this->memberID : "");
        }
        $color = (isset($this->_cp['background']) ? $this->_cp['background'] : false);
        return
             "<div"
            .($ID ? " id=\"".$ID."\"" : "")
            ." class='panel txt_c".($shadow ? ' shadow' : '')."' style='"
            .($color ? "background: ".$color.";" : "")
            .($width ? "width:".$width."px;" : "")
            .($box==1 || $box==2 ? "border:1px solid #".$system_vars['cal_border'].";" : "")
            ."'>\n"
            .($box!=0 ?
                 "  <table".($box==1 || $box==2 ? " class='panel_head cal_head'" : "")." summary=''>\n"
                ."    <tr>\n"
                .($canEdit ?
                      "    <td class='add'>"
                     ."<a href=\"".BASE_PATH."details/".$add_form."/\""
                     ." onclick=\"details("
                     ."'".$add_form."','','".$popup['h']."','".$popup['w']."','','','','".$uri_args."'"
                     .");return false;\"  title='Add ".$this->_get_object_name()."&hellip;'>"
                     ."[ICON]11 11 1188 [/ICON]</a></td>\n"
                  :
                    ""
                 )
                ."    <td class='title' style='height:18px;'>"
                .($link ?
                    "<a href=\"".BASE_PATH.$link_page."\""
                   ." title='View all ".$this->_get_object_name()."s'>"
                   .$title
                   ."</a>"
                  :
                    $title
                 )
                ."</td>\n"
                .($rss_link==1?
                    "  <td class='rss'>"
                   ."<a rel=\"external\" href=\""
                   .BASE_PATH."rss/".$rss_url."\" title='[View RSS ".$this->_get_object_name()." Feed]'>"
                   .($this->_get_type()!='podcast' ? "[ICON]14 14 1199 [/ICON]" : "[ICON]15 15 2263 [/ICON]")
                   ."</a>"
                   ."</td>"
                  :
                    ""
                 )
                ."</tr></table>\n"
              :
                ""
             )
            ."  <div class='panel_content txt_l'"
            .($ID ? " id=\"".$ID."_content\"" : "")
            .">".$content."</div>\n"
            ."</div>\n";
    }


    public function draw_ratings_block($submode = false, $value = false)
    {
        return Rating::draw_block($submode, $value);
    }

    public function draw_related_block()
    {
        if (!System::has_feature('Keywords')) {
            return "";
        }
        $Obj = new Component_Related_Block;
        return $Obj->draw();
    }

    public function draw_search_results_paging_nav($result, $find)
    {
        global $page_vars;
        $nav =          array();
        $retrieved =    count($result['results']);
        $limit =        $result['limit'];
        $offset =       $result['offset'];
        $found =        $result['count'];
        $classname =    get_class($this);
        $Obj =          new $classname;
        $type =         $Obj->_get_type();
        $URL =
             BASE_PATH
            .trim($page_vars['path'], '/')
            .'/?search_type='.$type
            .'&amp;search_limit='.$limit
            .'&amp;search_offset=';
        if ($retrieved && $limit) {
            $result_pages = ceil($found/$limit);
            if ($offset>0) {
                $nav[] =
                     "<a href=\"".$URL.$offset."\""
                    ." onclick=\"return search_offset('".($offset-$limit)."','".$type."')\""
                    ." title=\"Previous results for ".$this->_get_object_name()."\">&lt;</a>";
            } else {
                $nav[] =
                     "<span title=\"(Previous results for ".$this->_get_object_name().")\">"
                    ."&lt;"
                    ."</span>";
            }
            for ($i=0; $i<$result_pages; $i++) {
                $title = ($i*$limit == $found ?
                    $this->_get_object_name()." Result ".(1+$i*$limit)
                 :
                     $this->_get_object_name()." Results ".(1+$i*$limit)." - "
                    .((1+$i)*$limit<$found ? (1+$i)*$limit : $found)
                );
                if ($i*$limit == $offset) {
                    $nav[] =
                         "<span title=\"".$title."\">"
                        ."<b>".($i+1)."</b>"
                        ."</span>";
                } else {
                    $nav[] =
                        "<a href=\"".$URL.($i*$limit)."\""
                        ." onclick=\"return search_offset('".($i*$limit)."','".$type."')\""
                        ." title=\"".$title."\">".($i+1)."</a>";
                }
            }
            if ($offset+$limit<$found) {
                $nav[] =
                     "<a href=\"".$URL.($offset+$limit)."\""
                    ." onclick=\"return search_offset('".($offset+$limit)."','".$type."')\""
                    ." title=\"Next results for ".$this->_get_object_name()."\">&gt;</a>";

            } else {
                $nav[] =
                    "<span title=\"(Next results for ".$this->_get_object_name().")\">"
                    ."&gt;"
                    ."</span>";
            }
        }
        // print count($nav);die;
        // Reduce results navigation pages if too many
        if (count($nav)>12) {
            $page = $offset/$limit;
            // If at start or end:
            if ($page<5 || $page+5>=$result_pages) {
                array_splice($nav, 7, count($nav)-14, array('&hellip;'));
            } else {
                $nav = array(
                    $nav[0],
                    $nav[1],
                    $nav[2],
                    '&hellip;',
                    $nav[$page-1],
                    $nav[$page],
                    $nav[$page+1],
                    $nav[$page+2],
                    $nav[$page+3],
                    '&hellip;',
                    $nav[count($nav)-3],
                    $nav[count($nav)-2],
                    $nav[count($nav)-1]
                );
            }
        }
        return
             "<div class='paging'>"
            ."<div class='fl'>Found "
            .($found==1 ? "one ".$this->_get_object_name() : $found." ".$this->_get_object_name()."s")
            .":"
            .($found != $retrieved ?
                " showing "
                .($offset+1==$found ?
                    "last match"
                :
                     (1+$offset)
                    .($limit+$offset<$found ?
                        " to ".($offset+$limit)
                     :
                         " to ".$found
                     )
                )
                ."</div>"
                .(count($nav) ?
                     "<div class='fl va_t' style='padding-left:20px;padding-bottom:5px;'>"
                    .implode(" ", $nav)
                    ."</div>"
                  :
                    ""
                 )
              :
                "</div>"
             )
            ."<div class='clr_b'></div>"
            ."</div>";
    }


    public function get_IDs_requiring_map_updates($systemID = false, $limit = 10)
    {
        $sql =
             "SELECT\n"
            ."  GROUP_CONCAT(`ID`)\n"
            ."FROM\n"
            ."  (SELECT\n"
            ."    `ID`\n"
            ."   FROM\n"
            ."    `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."  WHERE\n"
            .($systemID!==false ? "    `systemID` IN(".$systemID.") AND\n" : "")
            ."    `process_maps` = 1\n"
            .($limit!==false ? "  LIMIT\n    ".$limit."\n" : "")
            ."  ) `matches`";
  //    z($sql);
        return $this->get_field_for_sql($sql);
    }

    public function get_keywords()
    {
        $sql =
             "SELECT\n"
            ."  `keywords`.`ID`,\n"
            ."  `keywords`.`keyword`,\n"
            ."  (SELECT COUNT(`ka`.`ID`) FROM `keyword_assign` AS `ka` WHERE `keywords`.`ID` = `ka`.`keywordID`)"
            ." AS `count`\n"
            ."FROM\n"
            ."  `keywords`\n"
            ."INNER JOIN `keyword_assign` ON\n"
            ."  `keywords`.`ID` = `keyword_assign`.`keywordID`\n"
            ."WHERE\n"
            ."  `keyword_assign`.`assign_type` = '".$this->_get_assign_type()."' AND\n"
            ."  `keyword_assign`.`assignID` = ".$this->_get_ID()."\n"
            ."ORDER BY\n"
            ."  `keywords`.`keyword`";
        return $this->get_records_for_sql($sql);
    }

    protected function get_related_products()
    {
        return array();
    }

    public function get_URL($record = false)
    {
        $type = (isset($record['type']) ? $record['type'] : $record['object_type']);
        switch ($type) {
            case "page":
                return BASE_PATH.trim($record['path'], "/");
            break;
            case "product":
                return (isset($record['relative_URL']) ?
                    $record['relative_URL']
                 :
                    BASE_PATH.trim($record['path'], '/')
                );
            break;
            default:
                if (isset($record['path']) && $record['path']) {
                    $prefix = "";
                    if (isset($record['type'])) {
                        switch($record['type']){
                            case 'gallery-album':
                            case 'gallery-image':
                            case 'podcast-album':
                                $prefix = $record['type'].'/';
                                break;
                        }
                    }
                    return
                    ($record['systemID']==SYS_ID ?
                    BASE_PATH
                    :
                    (isset($record['systemURL']) ? trim($record['systemURL'], '/') : "")."/"
                    )
                    .$prefix.trim($record['path'], "/");
                }
              // THIS MUST REMAIN - if we are recreating path from scratch $record['path']==''
                $ID =   (isset($record['ID']) && $record['ID'] ? $record['ID'] : false);
                $path = "";
                $date = false;
                if ($record['type']=='event' && isset($record['effective_date_start'])) {
                    $date = $record['effective_date_start'];
                }
                if ($record['type']!='event' && isset($record['date'])) {
                    $date = $record['date'];
                }
                if ($date) {
                    $YYYY = substr($date, 0, 4);
                    $MM =   substr($date, 5, 2);
                    $DD =   substr($date, 8, 2);
                    $posting_prefix = (isset($record['posting_prefix']) ? $record['posting_prefix'] : POSTING_PREFIX);
                    switch ($posting_prefix){
                        case "YYYY":
                            $path = $YYYY."/";
                            break;
                        case "YYYY/MM":
                            $path = $YYYY."/".$MM."/";
                            break;
                        case "YYYY/MM/DD":
                            $path = $YYYY."/".$MM."/".$DD."/";
                            break;
                    }
                }
                switch ($record['type']) {
                    case 'article':
                    case 'event':
                    case 'gallery-image':
                    case 'news':
                    case 'job':
                    case 'podcast':
                        $type = $record['type'];
                        break;
                    default:
                        if (method_exists($this, 'get_type')) {
                            $type = $this->_get_type();
                        }
                        break;
                }
                $name_field =    ($this->_get_name_field() ? $this->_get_name_field() : 'name');
                $local =         $record['systemID']==SYS_ID;
                return
             ($record['systemID']==SYS_ID ?
             BASE_PATH
             :
             trim(isset($record['systemURL']) ? $record['systemURL'] : "", '/')."/"
             )
             .($local && $record[$name_field]!='' ? $path.$record[$name_field] : $type."/".$ID);
             break;
        }
    }

    public function isPurchasable()
    {
        $products = $this->get_related_products();
        return      (count($products) ? true : false);
    }

    public function isRegisterable($YYYYMMDD = false, $hhmm = false)
    {
        return false;
    }

    protected function _draw_render_JSON()
    {
        $Obj_JSON =     new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        // so we get an assoc array as output instead of some weird object
        header('Content-Type: application/json');
        $this->_html =  convert_safe_to_php($this->_html);
        $this->_js.=
            Page::pop_content('javascript')."\n"
           .Page::pop_content('javascript_onload');
        print $Obj_JSON->encode(
            array(
                'css' =>  $this->_css,
                'html' => str_replace("\n", "\r\n", $this->_html),
                'js' =>   str_replace("\n", "\r\n", $this->_js)
            )
        );
        die;
    }

    public function try_delete_item()
    {
        global $page_vars;
        if (!$this->_try_delete_item_check_user_rights()) {
            $this->_try_delete_item_msg_insufficient_rights();
            return false;
        }
        $this->delete();
        header('Location: '.BASE_PATH.trim($page_vars['path'], '/').'?msg=posting_deleted');
        die;
    }

    protected function _try_delete_item_check_user_rights()
    {
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =        get_person_permission("SYSEDITOR");
        $userIsAdmin =      ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
        if ($isMASTERADMIN || ($userIsAdmin && $this->get_field('systemID')==SYS_ID)) {
            return true;
        }
        return false;
    }

    protected function _try_delete_item_msg_insufficient_rights()
    {
        $msg =
         "<b>Error</b><br /><br />"
        ."You have insufficient rights to delete this ".$this->_get_object_name().".";
        Page::push_content(
            'javascript_onload',
            "  popup_msg=\"".$msg."\";"
            ."popup_dialog('Item Delete',\"<div style='padding:4px'>\"+popup_msg+\"</div>\",'320',120,'OK','','');"
        );
    }

    public function truncate_more(&$text)
    {
        $text = Language::convert_tags($text);
        if (strpos($text, '<!--more-->')===false) {
            return false;
        }
      // Close paragraph if open:
        $tags = explode('<', strToLower($text));
        $opened=0;
        $closed=0;
        foreach ($tags as $tag) {
            if (substr($tag, 0, 2)=='p>' || substr($tag, 0, 2)=='p ') {
                $opened++;
            }
        }
        foreach ($tags as $tag) {
            if (substr($tag, 0, 2)=='/p>') {
                $closed++;
            }
        }
        $text_arr = explode('<!--more-->', $text);
        $text = $text_arr[0];

        if ($opened == $closed+1) {
            $text .= "</p>";
        }
        return true;
    }

    public function truncate_text(&$text, $len)
    {
        if (strlen($text)>$len) {
            $text = substr($text, 0, $len);
            $i = strrpos($text, " ");
            $text = substr($text, 0, $i);
            return true;
        }
        return false;
    }

    public function get_version()
    {
        return VERSION_DISPLAYABLE_ITEM;
    }
}
