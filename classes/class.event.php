<?php
define('VERSION_EVENT', '1.0.104');
/*
Version History:
  1.0.104 (2015-02-06)
    1) New CP for listings - results_order - previously not possible to change display order
    2) Now PSR-2 Compliant

  (Older version history in class.event.txt)
*/
class Event extends Posting
{

    public static $cache_event_registrant_count =      array();

    public function __construct($ID = "", $systemID = SYS_ID)
    {
        parent::__construct($ID, $systemID);
        $this->_set_type('event');
        $this->_set_assign_type('event');
        $this->_set_has_publish_date(true);      // Do now allow item to be seen prior to publish date
        $this->_set_object_name('Event');
        $this->set_edit_params(
            array(
                'command_for_delete' =>     'event_delete',
                'report' =>                 'events',
                'report_related_products' =>'products_for_event',
                'report_rename' =>          false,
                'report_rename_label' =>    '',
                'icon_delete' =>            '[ICON]20 20 4401 Delete this Event[/ICON]',
                'icon_edit' =>              '[ICON]20 20 104 Edit this Event[/ICON]',
                'icon_edit_disabled' =>     '[ICON]20 20 2425 (Edit this Event)[/ICON]',
                'icon_edit_popup' =>        '[ICON]18 18 2516 Edit this Event in a popup window[/ICON]'
            )
        );
        $this->_cp_vars_detail = array(
            'block_layout' =>             array(
                'match' =>      '',
                'default' =>    'Event',
                'hint' =>       'Name of Block Layout to use'
            ),
            'category_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'comments_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'comments_link_show' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'extra_fields_list' =>        array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'item_footer_component' =>    array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below displayed Event'
            ),
            'map_height' =>               array(
                'match' =>      'range|1,n',
                'default' =>    '450',
                'hint' =>       'Height of Map if a location is given and found'
            ),
            'map_width' =>                array(
                'match' =>      'range|1,n',
                'default' =>    '450',
                'hint' =>       'Width of Map if a location is given and found'
            ),
            'map_zoom' =>                 array(
                'match' =>      'range|1,18',
                'default' =>    '16',
                'hint' =>       'Zoom for Map if a location is given and found'
            ),
            'products' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'products_msg_howto' =>       array(
                'match' =>      '',
                'default' =>
                     '<p>Click the shopping-cart icon to select payment option, then proceed to the Checkout '
                    .'to place your order.<br />Use the +/- buttons to adjust quantities.</p>',
                'hint' =>       'Describes how to place items in cart and checkout'
            ),
            'products_msg_none' =>        array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Message shown if there ARE no products'
            ),
            'products_msg_signin' =>      array(
                'match' =>      '',
                'default' =>
                     '<p>There are registration options that cannot be seen unless you sign in.<br />'
                    .'If you have an account you may sign in to access to member-rate pricing.</p>',
                'hint' =>       'Message shown if there are products that public member cannot see'
            ),
            'products_signin' =>          array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>
                     '0|1 - whether or not to show a signin dialog if products available for members'
                    .' but user has not signed in'
            ),
            'products_signin_button' =>   array(
                'match' =>      '',
                'default' =>    'Sign In ',
                'hint' =>       "Label to show for button in 'Sign In' dialog"
            ),
            'products_signin_pwd' =>      array(
                'match' =>      '',
                'default' =>    'Password ',
                'hint' =>       "Label to show for 'Password' in 'Sign In' dialog"
            ),
            'products_signin_title' =>    array(
                'match' =>      '',
                'default' =>    'Sign In',
                'hint' =>       "Title to show on 'Sign In' dialog"
            ),
            'products_signin_user' =>     array(
                'match' =>      '',
                'default' =>    'Username ',
                'hint' =>       "Label to show for 'Username' in 'Sign In' dialog"
            ),
            'registration' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_at_top' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height' =>         array(
                'match' =>      'range|1,n',
                'default' =>    '300',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_image' =>          array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_link' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>          array(
                'match' =>      'range|1,n',
                'default' =>    '400',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'title_linked' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
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
                'default' =>    'Event',
                'hint' =>       'Name of Block Layout to use'
            ),
            'block_layout_for_associated' => array(
                'match' =>      '',
                'default' =>    'Event for Associated',
                'hint' =>       'Name of Block Layout to use when this item is seen as associated with something else'
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
                'default' =>    'Events',
                'hint' =>       'text'
            ),
            'box_title_link' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title_link_page' =>      array(
                'match' =>      '',
                'default' =>    'all_events',
                'hint' =>       'page'
            ),
            'box_width' =>                array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..x'
            ),
            'category_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'comments_link_show' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_char_limit' =>       array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..n'
            ),
            'content_plaintext' =>        array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'extra_fields_list' =>        array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
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
            'filter_date_duration' =>     array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'If filter_what is future or past, this further limits to given date range'
            ),
            'filter_date_units' =>        array(
                'match' =>      'enum|,day,week,month,quarter,year',
                'default' =>    '',
                'hint' =>
                     ' |day|week|month|quarter|year - If filter_what is future or past, '
                    .'this provides units for given date range'
            ),
            'filter_important' =>         array(
                'match' =>      'enum|,0,1',
                'default' =>    '',
                'hint' =>       'Blank to ignore, 0 for not important, 1 for important'
            ),
            'filter_memberID' =>          array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Community Member to restrict by that criteria'
            ),
            'filter_personID' =>          array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Person to restrict by that criteria'
            ),
            'filter_range_address' =>     array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Address to search for to obtain lat and lon'
            ),
            'filter_range_distance' =>    array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'Limits results to those events occuring within this distance of given location'
            ),
            'filter_range_lat' =>         array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Latitude of search point'
            ),
            'filter_range_lon' =>         array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Longitude of search point'
            ),
            'filter_range_units' =>       array(
                'match' =>      'enum|km,mile',
                'default' =>    'km',
                'hint' =>       'Units of measurement to search point'
            ),
            'filter_what' =>              array(
                'match' =>      'enum|all,future,month,past,year',
                'default' =>    'month',
                'hint' =>       'all|future|month|past'
            ),
            'item_footer_component' =>    array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below each displayed Event'
            ),
            'keywords_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'more_link_text' =>           array(
                'match' =>      '',
                'default' =>    '(More)',
                'hint' =>       'text for \'Read More\' link'
            ),
            'products' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'registration' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'results_grouping' =>         array(
                'match' =>      'enum|,month,year',
                'default' =>    '',
                'hint' =>       '|month|year'
            ),
            'results_limit' =>            array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'results_order' =>            array(
                'match' =>      'enum|date,date_a,date_d_name_a,date_d_title_a,name,title',
                'default' =>    'date',
                'hint' =>       'date|date_a|date_d_name_a|date_d_title_a|name|title'
            ),
            'results_paging' =>           array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2 - 1 for buttons, 2 for links'
            ),
            'thumbnail_at_top' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height' =>         array(
                'match' =>      'range|1,n',
                'default' =>    '150',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_image' =>          array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_link' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>          array(
                'match' =>      'range|1,n',
                'default' =>    '',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'title_linked' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            )
        );
    }

    protected function BL_date($format = false)
    {
        global $system_vars, $YYYY;
        if (isset($this->_cp['date_show']) && $this->_cp['date_show']!='1') {
            return;
        }
        $date =     $this->record['effective_date_start'];
        $date_end = $this->record['effective_date_end'];
        if ($date_end=='0000-00-00' || $date_end==$date) {
            return format_date($date, $format);
        }
        return format_date($date, $format).' - '.format_date($date_end, $format);
    }

    protected function BL_date_heading_if_changed($format = false)
    {
        if (isset($this->_cp['date_show']) && $this->_cp['date_show']!='1') {
            return;
        }
        if (!isset($this->_old_date_start)) {
            $this->_old_date_start = false;
        }
        if (!isset($this->_old_date_end)) {
            $this->_old_date_end = false;
        }
        $date =     $this->record['effective_date_start'];
        $date_end = $this->record['effective_date_end'];
        if ($this->_old_date_start == $date && $this->_old_date_end == $date_end) {
            return;
        }
        $this->_old_date_start = $date;
        $this->_old_date_end = $date_end;
        if ($date_end=='0000-00-00' || $date_end==$date) {
            return format_date($date, $format);
        }
        return format_date($date, $format).' - '.format_date($date_end, $format);
    }

    protected function BL_event_cancellation_notice()
    {
        global $system_vars;
        if ($system_vars['system_cancellation_days']!=0 && $this->isRegisterable()) {
            return $system_vars['system_cancellation_days'];
        }
    }

    protected function BL_event_register_icon()
    {
        if ($this->isRegisterable()) {
            return $this->draw_link('register_event_large');
        }
    }

    protected function BL_event_registration()
    {
        global $selectID;
        if (!isset($this->_cp['registration']) || $this->_cp['registration']!='1') {
            return;
        }
        $selectID = $this->_get_ID();
        $cp = array(
            'show_event_summary' =>   0
        );
        $Obj = new Component_Event_Registration;
        $Obj->set_eventID($this->_get_ID());
        return $Obj->draw('', $cp);
    }

    protected function BL_event_times($timeFormatCode = false)
    {
        $result =
        Event::format_times(
            $this->record['effective_time_start'],
            $this->record['effective_time_end'],
            $timeFormatCode
        );
        if (
            $result=='(All day)' &&
            $this->record['effective_date_start']!=$this->record['effective_date_end']
        ) {
            return '';
        }
        return $result;
    }

    protected function BL_filter_date_range($format = false)
    {
        if (
            isset($this->_cp['filter_date_duration']) && $this->_cp['filter_date_duration']!=='' &&
            isset($this->_cp['filter_date_units']) && $this->_cp['filter_date_units']!==''
        ) {
            $now =    get_timestamp();
            $units =  $this->_cp['filter_date_units'];
            $dur =    $this->_cp['filter_date_duration'];
            switch ($this->_cp['filter_what']){
                case 'future':
                    if ($units==='quarter') {
                        return 'next '.($dur>1 ? $dur.' quarters' : 'quarter');
                    }
                    $d1 =     get_timestamp();
                    $d2 =     new DateTime($d1.' + '.$dur.' '.$units);
                    $d2 =     $d2->format('Y-m-d H:i:s');
                    return format_date($d1, $format).' - '.format_date($d2, $format);
                break;
                case 'past':
                    if ($units==='quarter') {
                        return 'last '.($dur>1 ? $dur.' quarters' : 'quarter');
                    }
                    $d1 =     get_timestamp();
                    $d2 =     new DateTime($d1.' - '.$dur.' '.$units);
                    $d2 =     $d2->format('Y-m-d H:i:s');
                    return format_date($d2, $format).' - '.format_date($d1, $format);
                break;
            }
        }
    }

    protected function BL_links()
    {
        $link_arr =     array();

        if (isset($this->record['URL']) && $this->record['URL']!='') {
            $link_arr[] = $this->draw_link('link');
        }
        if (isset($this->record['map_lat']) && $this->record['map_lat']) {
            $link_arr[] = $this->draw_link('map');
        }
        if (isset($this->_cp['registration']) && $this->_cp['registration']=='1' && $this->isRegisterable()) {
            $link_arr[] = $this->draw_link('register_event');
        }
        if (isset($this->_cp['products']) && $this->_cp['products']=='1' && $this->isPurchasable()) {
            $link_arr[] = $this->draw_link('buy_event');
        }
        $link_arr[] = $this->draw_link('add_to_outlook');
        if (count($link_arr)) {
            return implode("<span>|</span>", $link_arr);
        }
    }

    protected function BL_links_for_listings()
    {
        $link_arr =     array();
        $this->_set_ID($this->record['ID']);
        if (!isset($this->_cp['content_show']) || $this->_cp['content_show']=='1') {
            if ($truncated = $this->truncate_more($this->record['content'])) {
                $link_arr[] =
                $this->draw_link(
                    'read_more',
                    $this->record,
                    array('label'=>'')
                );
            }
        }
        if (
            isset($this->record['URL']) &&
            $this->record['URL']!='' &&
            !(isset($this->_cp['links_point_to_URL']) && $this->_cp['links_point_to_URL']==1)
        ) {
            $link_arr[] = $this->draw_link('link');
        }
        if (isset($this->record['map_lat']) && $this->record['map_lat']) {
            $link_arr[] = $this->draw_link('map');
        }
        if (isset($this->_cp['registration']) && $this->_cp['registration']=='1' && $this->isRegisterable()) {
            $link_arr[] = $this->draw_link('register_event');
        }
        if (isset($this->_cp['products']) && $this->_cp['products']=='1' && $this->isPurchasable()) {
            $link_arr[] = $this->draw_link('buy_event');
        }
        if (is_a($this, 'Event')) {
            $link_arr[] = $this->draw_link('add_to_outlook');
        }
        if (count($link_arr)) {
            return implode("<span>|</span>", $link_arr);
        }
    }

    protected function BL_location()
    {
        return $this->record['location'];
    }

    protected function BL_map()
    {
        if ($this->record['map_lat'] || $this->record['map_lon']) {
            return
            $this->draw_map(
                false,
                $this->_cp['map_width'],
                $this->_cp['map_height'],
                $this->_cp['map_zoom']
            );
        }
    }

    protected function BL_products_for_event()
    {
        if (!isset($this->_cp['products']) || $this->_cp['products']!='1') {
            return;
        }
        return $this->draw_product_catalogue();
    }

    public function add_product_action()
    {
        global $action_parameters, $selectID, $ID;
        $triggerID =        $action_parameters['triggerID'];
        $triggerType =      $action_parameters['triggerType'];
        $sourceTrigger =    $action_parameters['sourceTrigger'];
        $sourceID =         $action_parameters['sourceID'];
        if ($triggerType!='product') {
            print "This component is used for adding actions to link a new product to an existing event..";
        }
        if ($sourceTrigger!='report_insert_post') {
            print "This component should be called only after a record insertion.";
        }
        $Obj = new Action;
        $data = array(
            'systemID'=>SYS_ID,
            'destinationOperation'=>'event_register',
            'destinationID'=>$selectID,
            'sourceType'=>'product',
            'sourceTrigger'=>'product_pay',
            'sourceID'=>$triggerID
        );
        $Obj->insert($data);
    }

    public function count_registrations()
    {
        $ID = $this->_get_ID();
        if (isset(Event::$cache_event_registrant_count[$ID])) {
            return Event::$cache_event_registrant_count[$ID];
        }
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `registerevent`\n"
            ."WHERE\n"
            ."  `eventID` IN(".$this->_get_ID().")";
        $count = $this->get_field_for_sql($sql);
        Event::$cache_event_registrant_count[$ID] = $count;
        return $count;
    }

    public function draw_map($extra = true, $map_width = 570, $map_height = 500, $map_zoom = 13)
    {
        global $system_vars;
        if (!$this->_get_ID()) {
            if (!$ID = get_var('ID')) {
                return "Error - no event ID given";
            }
            $this->_set_ID($ID);
        }
        if (!$record = $this->get_record()) {
            return "Error - no valid event ID given";
        }
        $Obj_Map =      new Google_Map($record['ID'], SYS_ID);
        $Obj_Map->map_centre($record['map_lat'], $record['map_lon'], $map_zoom);
        $systemID =     $record['systemID'];
        $local =        $systemID == SYS_ID;
        sscanf($record['effective_date_start'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
        $_YYYY =  ($_YYYY == "0000" ? $YYYY : $_YYYY);
        $_date =  mktime(0, 0, 0, $_MM, $_DD, $_YYYY);
        $date_txt =date($system_vars['defaultDateFormat'], $_date);
        $link_arr = array();
        $link_arr[] = $this->draw_link('link', $record);
        $link_arr[] = $this->draw_link('add_to_outlook', $record);
        $link =
             "<div class='link'><span class='fl txt_c' style='font-size:8pt;width:8px;color:#888;'>[</span>"
             .implode("<span class='fl txt_c' style='font-size:8pt;width:8px;color:#888;'>|</span>", $link_arr)
             ."<span class='fl txt_c' style='font-size:8pt;width:8px;color:#888;'>]</span></div>";
        $html =
             "<strong>".$date_txt."<br />"
            .$record['title']."</strong><br />"
            .$record['content']."<br />"
            .$record['location'];
        $map_html =
             "<strong>".$date_txt."<br />"
            .$record['title']."</strong><br />"
            .($record['thumbnail_small'] ?
                 "<img style='float:left;border:1px solid #888;margin:0 5px 0 0;'"
                ." src='".BASE_PATH."img/resize".$record['thumbnail_small']."?width=100&height=100' alt='' />"
              :
                ""
             )
            .$record['location'];
        $Obj_Map->add_marker_with_html(
            $record['map_lat'],
            $record['map_lon'],
            str_replace(array("\r\n","\r","\n"), '<br />', $map_html),
            "mymarker",
            false,
            false,
            '',
            true
        );
        $Obj_Map->add_control_type();
        $Obj_Map->add_control_large();
        $args =     array(
            'map_width' =>  $map_width,
            'map_height' => $map_height
        );
        return
        ($extra ?
             $html."<br />\n"
            .($link ? $link."<br />" : "")
         :
            ""
        )
        .$Obj_Map->draw($args);
    }

    public function draw_registrants()
    {
        global $user_status;
        $isMASTERADMIN = get_person_permission("MASTERADMIN");
        $sql =
             "SELECT\n"
            ."  `postings`.`ID`,\n"
            ."  `postings`.`effective_date_start`,\n"
            ."  `postings`.`effective_date_end`,\n"
            ."  `postings`.`effective_time_start`,\n"
            ."  `postings`.`effective_time_end`,\n"
            ."  `postings`.`title`,\n"
            ."  `system`.`textEnglish`\n"
            ."FROM\n"
            ."  `postings`\n"
            ."INNER JOIN `system` ON\n"
            ."  `postings`.`systemID` = `system`.`ID`\n"
            ."WHERE\n"
            ."  `postings`.`ID` = ".$this->_get_ID()." AND\n"
            ."  `postings`.`type` = '".$this->_get_type()."'\n"
            ."ORDER BY\n"
            ."  `postings`.`effective_date_start`";
      //print "<pre>$sql</pre>";
        if (!$row = $this->get_record_for_sql($sql)) {
            return
                 "<h3 class='admin_heading'>Error</h3>"
                ."<p>The requested event doen't exist - perhaps it was deleted?</p>";
        }
        return
            "<h3 class='admin_heading'>"
            ."Event Registrants for "
            .($isMASTERADMIN  ? $row['textEnglish']." : " : "")
            .$row['title']." on ".$row['effective_date_start']." "
            .Event::format_times($row['effective_time_start'], $row['effective_time_end'])
            ."</h3>"
            .draw_auto_report('event_registrants', 1);
    }

    protected function _draw_object_map_html_setup()
    {
        parent::_draw_object_map_html_setup();
        if ($this->_current_user_rights['canEdit']) {
            $edit_params =        $this->get_edit_params();
            $this->_form =        $edit_params['report'];
            $this->_popup =       get_popup_size($this->_form);
        }
    }

    protected function _draw_object_map_html_draw_title()
    {
        $this->_html.= "<h2>Event Locations by date (".count($this->_data_items).")</h2>";
    }

    protected function _draw_object_map_html_get_data()
    {
        if (!$this->load()) {
            return;
        }
        $map_icon =     '';
        $event_date =   format_date($this->record['effective_date_start']);
        $event_times =  $this->format_times(
            $this->record['effective_time_start'],
            $this->record['effective_time_end']
        );
        $map_name =
             "<span class='when'>".$this->record['effective_date_start']
            .($this->record['effective_time_start'] ? ' ('.$this->record['effective_time_start'].')' : '')
            ."</span> ".$this->record['title'];
        $map_location =
             $event_date
            .($event_times ? " (".trim($event_times, '()').")" : "")
            ."\n"
            .$this->record['title']."\n"
            .$this->record['location'];
        $map_location =     str_replace("\r\n", "\n", $map_location);
        $map_location_arr = explode(
            "\n",
            $map_location
        );
        $map_location =
             "<div style='font-size:80%;white-space:nowrap'>"
            ."<a href='#' onclick=\\\"popWin("
            ."'".BASE_PATH."event/".$this->record['ID']."?print=1','event_".$this->record['ID']."',"
            ."'location=1,status=1,scrollbars=1,resizable=1',600,600,1"
            .");return false\\\">"
            ."<b>".array_shift($map_location_arr)."</b>"
            ."</a>"
            .($this->_current_user_rights['canEdit'] ?
                 " <a class='edit' href='".BASE_PATH."details/".$this->_form."/".$this->record['ID']."'"
                ." onclick=\\\"details("
                ."'".$this->_form."','".$this->record['ID']."','".$this->_popup['h']."','".$this->_popup['w']."'"
                .");return false;\\\">"
                ."[Edit]</a>"
              :
                ""
             )
            ."\n"
            .implode("\n", $map_location_arr)
            ."</div>";
        if ($this->record['map_lat'] || $this->record['map_lon']) {
            $this->_data_items[] = array(
                'ID' =>         $this->record['ID'],
                'map_area' =>   $this->record['map_geocode_area'],
                'map_lat' =>    $this->record['map_lat'],
                'map_lon' =>    $this->record['map_lon'],
                'map_loc' =>    $map_location,
                'map_icon' =>   $map_icon,
                'map_name' =>   $map_name,
                'record' =>     $this->record
            );
        }
    }

    public function export_icalendar()
    {
        global $system_vars;
        $record =   $this->get_record();
        $find = array(
            "\r\n",
            "&nbsp;",
            "&amp;"
        );
        $replace = array(
            "  ",
            " ",
            "&"
        );
        $url =      trim($system_vars['URL'], '/').$this->get_url($record);
        $title =    $record['title'];
        $content =  str_replace($find, $replace, strip_tags($record['content']));
        $location = str_replace($find, $replace, strip_tags($record['location']));
        $all_day =  (
            ($record['effective_time_start']=='' || $record['effective_time_start']=='00:00') &&
            ($record['effective_time_end']=='' || $record['effective_time_end']=='23:59')
        );
        $_start =
             $record['effective_date_start']." "
            .($record['effective_time_start'] ? $record['effective_time_start'] : "00:00")
            .":00";
        $_end =
             $record['effective_date_end']  ." "
            .($record['effective_time_end']   ? $record['effective_time_end']   : "23:59")
            .":00";
        $_stamp = ($record['history_modified_date']!='0000-00-00 00:00:00' ?
            $record['history_modified_date']
         :
            $record['history_created_date']
        );
        $start =   new DateTime($_start, new DateTimeZone(date_default_timezone_get()));
        $start->setTimezone(new DateTimeZone('UTC'));
        $end =     new DateTime($_end, new DateTimeZone(date_default_timezone_get()));
        $end->setTimezone(new DateTimeZone('UTC'));
        $stamp =    new DateTime($_stamp, new DateTimeZone(date_default_timezone_get()));
        $stamp->setTimezone(new DateTimeZone('UTC'));
        header("Content-Type: text/Calendar");
        header("Content-Disposition: inline; filename=".strToLower(SYSTEM_FAMILY)."-calendar.ics");
        print
            "BEGIN:VCALENDAR\r\n"
            ."VERSION:2.0\r\n"
            ."PRODID:-//".SYSTEM_FAMILY." ".CODEBASE_VERSION."//EN\r\n"
            ."CALSCALE:GREGORIAN\r\n"
            ."METHOD:PUBLISH\r\n"
      //      ."X-WR-CALNAME:".$system_vars['textEnglish']."\r\n"
      //      ."X-WR-RELCALID:".$system_vars['ID']."\r\n"
            ."X-WR-TIMEZONE:".date_default_timezone_get()."\r\n"
            ."BEGIN:VEVENT\r\n"
            ."UID:".$record['ID']."\r\n"
            ."TRANSP:OPAQUE\r\n"
            ."STATUS:CONFIRMED\r\n"
            ."SEQUENCE:0\r\n"
            ."CLASS:".($record['permPUBLIC'] ? "PUBLIC" : "PRIVATE")."\r\n"
            .($all_day ?
                 "DTSTART;VALUE=DATE:".$start->format('Ymd')."\r\n"
                ."DTEND;VALUE=DATE:".$end->format('Ymd')."\r\n"
             :
                 "DTSTART:".$start->format('Ymd\THis')."Z\r\n"
                ."DTEND:".$end->format('Ymd\THis')."Z\r\n"
             )
            ."DTSTAMP:".$stamp->format('Ymd\THis')."Z\r\n"
            ."SUMMARY:".$title."\r\n"
            ."DESCRIPTION:".$content."  ".$url."\r\n"
            .($location ? "LOCATION:".$location."\r\n" : "")
            ."END:VEVENT\r\n"
            ."END:VCALENDAR\r\n";
    }

    public static function form_field_end_date_and_time($width, $bulk_update, $row)
    {
        $_width =         ((int)$width/2)-($bulk_update ? 21 : 0);
        $_div_open =      "<div style='float:left;height:21px;'>";
        $field_ede =      'effective_date_end';
        $value_ede =        (isset($row[$field_ede]) ?
            $row[$field_ede]
         :
            (isset($_REQUEST[$field_ede]) ? $_REQUEST[$field_ede] : '')
        );
        $field_ete =      'effective_time_end';
        $value_ete =      (isset($row[$field_ete]) ? $row[$field_ete] : '');
        return
            ($bulk_update ?
                "<input id=\"".$field_ede."_apply\" name=\"".$field_ede."_apply\""
                ." title=\"Apply changes to this field\" type='checkbox' value=\"1\""
                ." class=\"fl formField\" style=\"background-color: #60a060;  margin: 0 2px 0 0px;\">"
             :
            "   "
            )
            .$_div_open.draw_form_field($field_ede, $value_ede, "date", $_width)
            ."&nbsp; "
            ."</div>"
            .($bulk_update ?
                 "<input id=\"".$field_ete."_apply\" name=\"".$field_ete."_apply\""
                ." title=\"Apply changes to this field\" type='checkbox' value=\"1\""
                ." class=\"fl formField\" style=\"background-color: #60a060;  margin: 0 2px 0 2px;\">"
              :
                ""
             )
            .$_div_open.draw_form_field($field_ete, $value_ete, "hh:mm", $_width)."</div>"
            ."<div class='clear'>&nbsp;</div>";
    }

    public static function form_field_start_date_and_time($width, $bulk_update, $row)
    {
        $_width =         ((int)$width/2)-($bulk_update ? 21 : 0);
        $_div_open =      "<div style='float:left;height:20px;'>";
        $field_eds =      'effective_date_start';
        $value_eds =      (isset($row[$field_eds]) ?
            $row[$field_eds]
         :
            (isset($_REQUEST[$field_eds]) ? $_REQUEST[$field_eds] : '')
        );
        $field_ets =      'effective_time_start';
        $value_ets =      (isset($row[$field_ets]) ? $row[$field_ets] : '');
        $field_ete =      'effective_time_end';
        $value_ete =      (isset($row[$field_ete]) ? $row[$field_ete] : '');
        $field_ade =      'all_day_event';
        $value_ade =      (
            isset($row[$field_ets]) &&
            ($row[$field_ets]=='' || $row[$field_ets]=='00:00') &&
            isset($row[$field_ete]) &&
            ($row[$field_ete]=='' || $row[$field_ete]=='23:59') ?
                1
            :
                0
        );
        Page::push_content('javascript_onload', "  onclick_alldayevent(".$value_ade.");\n");
        return
            ($bulk_update ?
                 "<input id=\"".$field_eds."_apply\" name=\"".$field_eds."_apply\""
                ." title=\"Apply changes to this field\" type='checkbox' value=\"1\""
                ." class=\"fl formField\" style=\"background-color: #60a060;  margin: 0 2px 0 0px;\">"
             :
                ""
            )
            .$_div_open.draw_form_field(
                $field_eds,
                $value_eds,
                "date",
                $_width
            )
            ."&nbsp; "
            ."</div>"
            .($bulk_update ?
                 "<input id=\"".$field_ets."_apply\" name=\"".$field_ets."_apply\""
                ." title=\"Apply changes to this field\" type='checkbox' value=\"1\""
                ." class=\"fl formField\" style=\"background-color: #60a060;  margin: 0 2px 0 2px;\">"
             :
                ""
            )
            .$_div_open.draw_form_field(
                $field_ets,
                $value_ets,
                "hh:mm",
                $_width
            )
            ."&nbsp; "
            ."</div>"
            .$_div_open.draw_form_field(
                $field_ade,
                $value_ade,
                "bool",
                $_width,
                "",
                "",
                "onclick='onclick_alldayevent(this.checked)'",
                0,
                0
            )
            ." <label for='".$field_ade."'>All day event</label>"
            ."</div>"
            ."<div class='clear'>&nbsp;</div>";
    }

    public static function format_times($start, $end, $timeFormatCode = false)
    {
        global $system_vars;
        if ($timeFormatCode===false) {
            $timeFormatCode = $system_vars['defaultTimeFormat'];
        }
        if (($start=='' || $start=='00:00') && ($end=='' || $end=='23:59')) {
            return ($timeFormatCode==0 || $timeFormatCode==1 ? '(All day)' : '');
        }
        if (($start==$end)) {
            return hhmm_format($start, $timeFormatCode==1 || $timeFormatCode==3);
        }
        if (($start=='' || $start=='00:00') && $end!='' && $end!='23:59') {
            return 'Until '.hhmm_format($end, $timeFormatCode==1 || $timeFormatCode==3);
        }
        if (($start=='' || $start=='00:00') && $end!='' && $end!='23:59') {
            return 'Until '.hhmm_format($end, $timeFormatCode==1 || $timeFormatCode==3);
        }
        if ($start!='' && $start!='00:00' && ($end=='' || $end=='23:59')) {
            return 'From '.hhmm_format($start, $timeFormatCode==1 || $timeFormatCode==3);
        }
        return
             hhmm_format($start, $timeFormatCode==1 || $timeFormatCode==3)
            .'&#8201;-&#8201;'
            .hhmm_format($end, $timeFormatCode==1 || $timeFormatCode==3);
    }

    public function get_calendar_dates($MM, $YYYY, $memberID = '', $category = '*')
    {
        $results =          $this->get_records(
            array(
                'what' =>           'calendar',
                'YYYY' =>           $YYYY,
                'MM' =>             $MM,
                'category' =>       $category,
                'memberID' =>       $memberID
            )
        );
        $records =          $results['data'];
        $last_month =        adodb_mktime(0, 0, 0, $MM, 0, $YYYY);
        $this_month =        adodb_mktime(0, 0, 0, $MM+1, 0, $YYYY);
        $next_month =        adodb_mktime(0, 0, 0, $MM+1, 1, $YYYY);
        sscanf(
            adodb_date('Y-m-d w', $last_month),
            "%4s-%2s-%2s %1s",
            $last_month_YYYY,
            $last_month_MM,
            $last_month_DD,
            $last_month_w
        );
        sscanf(
            adodb_date('Y-m-d', $this_month),
            "%4s-%2s-%2s",
            $this_month_YYYY,
            $this_month_MM,
            $this_month_DD
        );
        sscanf(
            adodb_date('Y-m-d', $next_month),
            "%4s-%2s",
            $next_month_YYYY,
            $next_month_MM
        );
        $arr_cal =                array();
        $_DD =            lead_zero($last_month_DD-$last_month_w, 2);
        $_MM =            $last_month_MM;
        $_YYYY =          $last_month_YYYY;
        for ($i=0; $i<42; $i++) {
            $slot_date =  adodb_mktime(0, 0, 0, $_MM, $_DD+$i, $_YYYY);
            $slot =       adodb_date('Y-m-d', $slot_date);
            sscanf($slot, '%4s-%2s-%2s', $slot_YYYY, $slot_MM, $slot_DD);
            $events = array();
            foreach ($records as $record) {
                sscanf($record['effective_date_start'], '%4s-%2s-%2s', $start_YYYY, $start_MM, $start_DD);
                sscanf($record['effective_date_end'], '%4s-%2s-%2s', $end_YYYY, $end_MM, $end_DD);
                $start_date =   adodb_mktime(0, 0, 0, $start_MM, $start_DD, $start_YYYY);
                $end_date =     adodb_mktime(0, 0, 0, $end_MM, $end_DD, $end_YYYY);
                $show = false;
                if ($record['effective_date_start']==$slot) {
                    $show = true;
                    if (
                        $record['effective_date_end']!='0000-00-00' &&
                        $slot_date < $end_date
                    ) {
                        $record['effective_time_end']='';
                    }
                }
                if (
                    $record['effective_date_end']!='0000-00-00' &&
                    $slot_date > $start_date &&
                    $slot_date == $end_date
                ) {
                    $show = true;
                    $record['effective_time_start']='';
                }
                if (
                    $record['effective_date_end']!='0000-00-00' &&
                    $slot_date > $start_date &&
                    $slot_date < $end_date
                ) {
                    $show = true;
                    $record['effective_time_start']='';
                    $record['effective_time_end']='';
                }
                if (
                    $record['effective_date_end']!='0000-00-00' &&
                    $slot_date > $start_date &&
                    $slot_date == $end_date
                ) {
                    $show = true;
                    $record['effective_time_start']='';
                }
                if ($show) {
                    $events[] = array(
                        "ID" =>                   $record['ID'],
                        "category" =>             $record['category'],
                        "content" =>              $record['content'],
                        "effective_date_start" => $record['effective_date_start'],
                        "effective_date_end" =>   $record['effective_date_end'],
                        "effective_time_start" => $record['effective_time_start'],
                        "effective_time_end" =>   $record['effective_time_end'],
                        "enabled" =>              (
                            isset($record['enabled']) ?
                                $record['enabled']
                             :
                                ""
                            ),
                        "icon" =>                 (
                            isset($record['thumbnail_small']) ?
                                $record['thumbnail_small']
                             :
                                $record['icon']
                            ),
                        "important" =>            (
                            isset($record['important']) ?
                                $record['important']
                             :
                                ""
                            ),
                        "memberID" =>             (
                            isset($record['memberID']) ?
                                $record['memberID']
                             :
                                ""
                            ),
                        "member_shortform" =>     (
                            isset($record['member_shortform']) ?
                                $record['member_shortform']
                             :
                                ""
                            ),
                        "member_title" =>         (
                            isset($record['member_title']) ?
                                $record['member_title']
                             :
                                ""
                            ),
                        "member_url" =>           (
                            isset($record['member_url']) ?
                                $record['member_url']
                             :
                                ""
                            ),
                        "permSHARED" =>           (
                            isset($record['permSHARED']) ?
                                $record['permSHARED']
                             :
                                ""
                            ),
                        "systemID" =>             $record['systemID'],
                        "systemTitle" =>          $record['systemTitle'],
                        "systemURL" =>            $record['systemURL'],
                        "title" =>                $record['title']
                    );
                }
            }
            $arr_cal[$i] = array(
                "YYYY"=>    $slot_YYYY,
                "MM"  =>    $slot_MM,
                "DD"  =>    $slot_DD,
                "evt" =>    $events
            );
        }
        return $arr_cal;
    }

    public function get_children()
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `systemID` = ".SYS_ID." AND\n"
            ."  `parentID` = ".$this->_get_ID()."\n"
            ."ORDER BY\n"
            ."  `effective_date_start`";
        return $this->get_records_for_sql($sql);
    }

    public function get_events_for_date($YYYYMMDD, $memberID = '')
    {
        sscanf($YYYYMMDD, "%4s-%2s-%2s", $YYYY, $MM, $DD);
        $result = $this->get_records(
            array(
                'what' =>       'date',
                'YYYY' =>       $YYYY,
                'MM' =>         $MM,
                'DD' =>         $DD,
                'memberID' =>   $memberID
            )
        );
        $records = $result['data'];
        $out = array();
        foreach ($records as $record) {
            $icon =   (isset($record['thumbnail_small']) ? $record['thumbnail_small'] : $record['icon']);
            if ($icon) {
                $icon =
                (substr($icon, 0, 5)=='http:' || substr($icon, 0, 5)=='https:' ?
                $icon
                 :
                trim($record['systemURL'], '/').'/img/max/80'.$icon
                );
            }
            $out[] = array(
                'ID' =>                     $record['ID'],
                'content' =>                convert_safe_to_php($record['content']),
                'date' =>                   $record['effective_date_start'],
                'icon' =>                   $icon,
                'location' =>               $record['location'],
                'map_lat' =>                $record['map_lat'],
                'map_lon' =>                $record['map_lon'],
                'path' =>                   (
                    isset($record['path']) ?
                        trim($record['systemURL'], '/').'/'.trim($record['path'], '/')
                    :
                        $record['URL']
                    ),
                'shared' =>                 ($record['systemID']==SYS_ID ? '0' : '1'),
                'systemID' =>               $record['systemID'],
                'systemURL' =>              $record['systemURL'],
                'systemTitle' =>            $record['systemTitle'],
                'effective_time_start' =>   $record['effective_time_start'],
                'effective_time_end' =>     $record['effective_time_end'],
                'title' =>                  $record['title']
            );
        }
        return $out;
    }

    protected function _get_records_get_sql_filter_date()
    {
  //    y($this->_get_records_args);die;
        $YYYY = $this->_get_records_args['YYYY'];
        $now =  get_timestamp();
        sscanf(
            $now,
            "%4s-%2s-%2s %2s:%2s:%2s",
            $now_YYYY,
            $now_MM,
            $now_DD,
            $now_hh,
            $now_mm,
            $now_ss
        );
        switch($this->_get_records_args['what']){
            case 'all':
                return '';
            break;
            case 'calendar':
                $date_last_month =  adodb_mktime(
                    0,
                    0,
                    0,
                    $this->_get_records_args['MM'],
                    0,
                    $this->_get_records_args['YYYY']
                );
                $last_month_w =     adodb_date("w", $date_last_month);
                $last_month_DD =    adodb_date("j", $date_last_month);
                $last_month_MM =    adodb_date("m", $date_last_month);
                $last_month_YYYY =  adodb_date("Y", $date_last_month);
                $date_next_month =  adodb_mktime(
                    0,
                    0,
                    0,
                    $this->_get_records_args['MM'],
                    42-$last_month_w,
                    $this->_get_records_args['YYYY']
                );
                $next_month_DD =    adodb_date("j", $date_next_month);
                $next_month_MM =    adodb_date("m", $date_next_month);
                $next_month_YYYY =  adodb_date("Y", $date_next_month);
                return
                     "  (\n"
                    ."    (\n"
                    ."      `postings`.`effective_date_start` >= '"
                    .$last_month_YYYY."-".$last_month_MM."-".($last_month_DD-$last_month_w)."' AND\n"
                    ."      `postings`.`effective_date_start` <  '"
                    .$next_month_YYYY."-".$next_month_MM."-".(lead_zero($next_month_DD, 2))."'\n"
                    ."    ) OR\n"
                    ."    (\n"
                    ."      `postings`.`effective_date_end` >= '"
                    .$last_month_YYYY."-".$last_month_MM."-".($last_month_DD-$last_month_w)."' AND\n"
                    ."      `postings`.`effective_date_end` <= '"
                    .$next_month_YYYY."-".$next_month_MM."-".(lead_zero($next_month_DD, 2))."'\n"
                    ."    )\n"
                    ."  ) AND\n";
            break;
            case 'date':
                return
                     "  (\n"
                    ."    `postings`.`effective_date_start` = '"
                    .$this->_get_records_args['YYYY']."-"
                    .$this->_get_records_args['MM']."-"
                    .$this->_get_records_args['DD']."' OR\n"
                    ."    (\n"
                    ."      `postings`.`effective_date_start` < '"
                    .$this->_get_records_args['YYYY']."-"
                    .$this->_get_records_args['MM']."-"
                    .$this->_get_records_args['DD']."' AND\n"
                    ."      `postings`.`effective_date_end` >= '"
                    .$this->_get_records_args['YYYY']."-"
                    .$this->_get_records_args['MM']."-"
                    .$this->_get_records_args['DD']."'\n"
                    ."    )\n"
                    ."  ) AND\n";
            break;
            case 'future':
                return
                     "  (\n"
                    ."    (\n"
                    ."      (`postings`.`effective_date_start` = '"
                    .$now_YYYY."-".$now_MM."-".$now_DD."' AND `postings`.`effective_time_start` > '"
                    .$now_hh.":".$now_mm.":".$now_ss."') OR\n"
                    ."      (`postings`.`effective_date_start` > '".$now_YYYY."-".$now_MM."-".$now_DD."')\n"
                    ."    )"
                    .($this->_get_records_args['filter_date_duration'] ?
                         " AND\n"
                        ."    (`postings`.`effective_date_start` <  DATE_ADD('"
                        .$now_YYYY."-".$now_MM."-".$now_DD."', INTERVAL "
                        .$this->_get_records_args['filter_date_duration']." "
                        .$this->_get_records_args['filter_date_units']."))\n"
                    :
                        "\n"
                    )
                    ."  ) AND\n";
            break;
            case 'month':
                return
                     "  (\n"
                    ."    `postings`.`effective_date_start` >= '"
                    .$this->_get_records_args['YYYY']."-".$this->_get_records_args['MM']."-01' AND\n"
                    ."    `postings`.`effective_date_start` <= DATE_ADD('"
                    .$this->_get_records_args['YYYY']."-".$this->_get_records_args['MM']."-01',INTERVAL 1 MONTH)\n"
                    ."  ) AND\n";
            break;
            case 'past':
                return
                     "  (\n"
                    ."    (\n"
                    ."      (`postings`.`effective_date_start` = '"
                    .$now_YYYY."-".$now_MM."-".$now_DD."' AND `postings`.`effective_time_start` < '"
                    .$now_hh.":".$now_mm.":".$now_ss."') OR\n"
                    ."      (`postings`.`effective_date_start` < '".$now_YYYY."-".$now_MM."-".$now_DD."')\n"
                    ."    )"
                    .($this->_get_records_args['filter_date_duration'] ?
                          "AND\n"
                         ."    `postings`.`effective_date_start` >=  DATE_SUB('"
                         .$now_YYYY."-".$now_MM."-".$now_DD."', INTERVAL "
                         .$this->_get_records_args['filter_date_duration']." "
                         .$this->_get_records_args['filter_date_units'].")\n"
                    :
                        "\n"
                    )
                    ."  ) AND\n";
            break;
            case 'year':
                return
                     "  (\n"
                    ."    `postings`.`effective_date_start` >= '".$this->_get_records_args['YYYY']."-01-01' AND\n"
                    ."    `postings`.`effective_date_start` <  '".$this->_get_records_args['YYYY']."-12-01'\n"
                    ."  ) AND\n";
            break;
        }
    }

    protected function get_related_products()
    {
        $out = array();
        $sql =
             "SELECT\n"
            ."  (SELECT\n"
            ."    `product_grouping`.`name`\n"
            ."  FROM\n"
            ."    `product_grouping`\n"
            ."  WHERE\n"
            ."    `product_grouping`.`ID`=`product`.`groupingID`\n"
            ."  ) `product_grouping_name`,\n"
            ."  `product`.*\n"
            ."FROM\n"
            ."  `postings`\n"
            ."INNER JOIN `action` ON\n"
            ."  `action`.`destinationID` = `postings`.`ID` AND\n"
            ."  `action`.`destinationOperation` = 'event_register'\n"
            ."INNER JOIN `product` ON\n"
            ."  `action`.`sourceID` = `product`.`ID` AND\n"
            ."  `action`.`sourceType` = 'product' AND\n"
            ."  `action`.`sourceTrigger` IN('product_order','product_pay')\n"
            ."WHERE\n"
            ."  `postings`.`type`='".$this->_get_type()."' AND\n"
            ."  `postings`.`ID` = ".$this->_get_ID();
        $records = $this->get_records_for_sql($sql);
        foreach ($records as $record) {
            if (Product::is_enabled($record) && Product::is_in_active_date_range($record)) {
                $out[] = $record;
            }
        }
        return $out;
    }

    public function get_yearly_dates($YYYY, $memberID = 0)
    {
        $records = $this->get_records(
            array(
                'what' =>     'year',
                'YYYY' =>     $YYYY,
                'memberID' => $memberID
            )
        );
        $days_in_year = date("z", adodb_mktime(0, 0, 0, 12, 31, $YYYY))+1;
        $arr =          array();
        for ($i=0; $i<$days_in_year; $i++) {
            $slot_date = adodb_mktime(0, 0, 0, 1, 1+$i, $YYYY);
            $slot =       adodb_date('Y-m-d', $slot_date);
            $arr[$slot] = array();
            sscanf($slot, '%4s-%2s-%2s', $slot_YYYY, $slot_MM, $slot_DD);
            $events = array();
            foreach ($records['data'] as $record) {
                sscanf($record['effective_date_start'], '%4s-%2s-%2s', $start_YYYY, $start_MM, $start_DD);
                sscanf($record['effective_date_end'], '%4s-%2s-%2s', $end_YYYY, $end_MM, $end_DD);
                $start_date =   adodb_mktime(0, 0, 0, $start_MM, $start_DD, $start_YYYY);
                $end_date =     adodb_mktime(0, 0, 0, $end_MM, $end_DD, $end_YYYY);
                $show = false;
                if ($record['effective_date_start']==$slot) {
                    $show = true;
                    if ($record['effective_date_end']!='0000-00-00' && $slot_date < $end_date) {
                        $record['effective_time_end']='';
                    }
                }
                if (
                    $record['effective_date_end']!='0000-00-00' &&
                    $slot_date > $start_date &&
                    $slot_date == $end_date
                ) {
                    $show = true;
                    $record['effective_time_start']='';
                }
                if (
                    $record['effective_date_end']!='0000-00-00' &&
                    $slot_date > $start_date &&
                    $slot_date < $end_date
                ) {
                    $show = true;
                    $record['effective_time_start']='';
                    $record['effective_time_end']='';
                }
                if (
                    $record['effective_date_end']!='0000-00-00' &&
                    $slot_date > $start_date &&
                    $slot_date == $end_date
                ) {
                    $show = true;
                    $record['effective_time_start']='';
                }
                if ($show) {
                    $events[] = array(
                        "ID" =>                   $record['ID'],
                        "category" =>             $record['category'],
                        "content" =>              $record['content'],
                        "icon" =>                 (
                            isset($record['thumbnail_small']) ?
                                $record['thumbnail_small']
                            :
                                $record['icon']
                            ),
                        "member_title" =>         (isset($record['member_title']) ? $record['member_title'] : ""),
                        "member_url" =>           (isset($record['member_url']) ? $record['member_url'] : ""),
                        "systemID" =>             $record['systemID'],
                        "systemTitle" =>          $record['systemTitle'],
                        "systemURL" =>            $record['systemURL'],
                        "effective_date_start" => $record['effective_date_start'],
                        "effective_date_end" =>   $record['effective_date_end'],
                        "effective_time_start" => $record['effective_time_start'],
                        "effective_time_end" =>   $record['effective_time_end'],
                        "title" =>                $record['title']
                    );
                }
            }
            $arr[$slot]  = $events;
        }
        return $arr;
    }

    public function handle_report_delete(&$msg)
    {
        if ($registrations = $this->count_registrations()) {
            $msg =
            status_message(
                2,
                true,
                'Event',
                '',
                'has '.$registrations.' registrant'.($registrations==1 ? '' : 's')
                .' registered. Deletion has been cancelled.',
                $this->_get_ID()
            );
            return false;
        }
        if ($children = $this->count_children()) {
            $parents = $this->count_parents();
            $selected = count(explode(",", $this->_get_ID()));
            if ($selected==1) {
                $this->_try_delete_item_dialog_has_children($children, $this->_get_ID());
                $msg =
                status_message(
                    1,
                    true,
                    'selected Event',
                    '',
                    'is a Master Event with '.$children.' linked recurrence'.($children==1 ? '' : 's')
                    .'. Further clarification is needed.',
                    $this->_get_ID()
                );
                return false;
            }
            if ($selected == $parents) {
                $msg =
                status_message(
                    2,
                    true,
                    'selected Event',
                    '',
                    'are all Master Events having a total of '.$children.' linked recurrence'
                    .($children==1 ? '' : 's').'. Deletion has been cancelled.',
                    $this->_get_ID()
                );
                return false;
            }
            $msg =
            status_message(
                2,
                true,
                'selected Event',
                '',
                'include '.$parents." Master Event".($parents==1 ? '' : 's').' having a total of '
                .$children.' linked recurrence'.($children==1 ? '' : 's').'. Deletion has been cancelled.',
                $this->_get_ID()
            );
            return false;
        }
        return parent::try_delete($msg);
    }

    public function isRegisterable($YYYYMMDD = false, $hhmm = false)
    {
        if (!isset($this->record['canRegister']) || $this->record['canRegister']==0) {
            return false;
        }
        if ($YYYYMMDD===false) {
            $YYYYMMDD = $this->record['effective_date_start'];
        }
        if ($hhmm===false) {
            $hhmm = $this->record['effective_time_start'];
        }
        $now =        time();
        sscanf($YYYYMMDD, "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
        sscanf($hhmm, "%02d:%02d", $_hh, $_mm);
        $_YYYY =  ($_YYYY == "0000" ? date("Y", $now) : $_YYYY);
        $_starts =  mktime($_hh, $_mm, 0, $_MM, $_DD, $_YYYY);
        return $_starts > $now;
    }

    public function manage_recurrences()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('event_recurrences', 1);
        }
        if (!$selectID = get_var('selectID')) {
            return
             "<h3 style='margin:0.25em'>Recurrences for ".$this->_get_object_name()."</h3>"
            ."<p style='margin:0.25em'>No Recurrences - this ".$this->_get_object_name()." has not been saved yet.</p>";
        }
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `postings`\n"
            ."WHERE\n"
            ."  `postings`.`parentID` = ".$selectID;
      //print "<pre>$sql</pre>";
        if (!$row = $this->get_record_for_sql($sql)) {
            return
                 "<h3 style='margin:0.25em'>Recurrences for ".$this->_get_object_name()."</h3>"
                ."<p style='margin:0.25em'>Sorry - the related ".$this->_get_object_name()
                ." cannot be found - perhaps it was deleted?</p>";
        }
        $isMASTERADMIN = get_person_permission("MASTERADMIN");
        return draw_auto_report('event_recurrences', 1);
    }

    public function manage_registrants()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('event_registrants', 1);
        }
        if (!$selectID = get_var('selectID')) {
            return
                 "<h3 style='margin:0.25em'>Registrants for ".$this->_get_object_name()."</h3>"
                ."<p style='margin:0.25em'>No registrants - this ".$this->_get_object_name()
                ." has not been saved yet.</p>";
        }
        $sql =
             "SELECT\n"
            ."  `postings`.`ID`,\n"
            ."  `postings`.`effective_date_start`,\n"
            ."  `postings`.`effective_time_start`,\n"
            ."  `postings`.`effective_time_end`,\n"
            ."  `postings`.`title`,\n"
            ."  `system`.`textEnglish`\n"
            ."FROM\n"
            ."  `postings`\n"
            ."INNER JOIN `system` ON\n"
            ."  `postings`.`systemID` = `system`.`ID`\n"
            ."WHERE\n"
            ."  `postings`.`ID` = ".$selectID." AND\n"
            ."  `postings`.`type` = '".$this->_get_type()."'\n"
            ."ORDER BY\n"
            ."  `postings`.`effective_date_start`";
      //print "<pre>$sql</pre>";
        if (!$row = $this->get_record_for_sql($sql)) {
            return
                 "<h3 style='margin:0.25em'>Registrants for ".$this->_get_object_name()."</h3>"
                ."<p style='margin:0.25em'>Sorry - the related ".$this->_get_object_name()
                ." cannot be found - perhaps it was deleted?</p>";
        }
        $isMASTERADMIN = get_person_permission("MASTERADMIN");
        return
             "<h3 style='margin:0.25em'>Registrants for the ".$this->_get_object_name()."<br />"
            .($isMASTERADMIN  ? $row['textEnglish']." : " : "")
            .$row['title']." on ".$row['effective_date_start']." "
            .Event::format_times($row['effective_time_start'], $row['effective_time_end'])
            ."</h3>"
            .draw_auto_report('event_registrants', 1);
    }

    public function on_action_warn_if_non_registerable()
    {
        $msg =  "";
        $ID_arr = explode(',', str_replace(' ', '', $this->_get_ID()));
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $this->load();
            if ($this->record['canRegister'] && !$this->isRegisterable()) {
                $msg.=
                     "<li>".$this->record['effective_date_start']." - '"
                    .$this->record['title']."' isn't registerable - the date has already passed.</li>";
            }
        }
        return $msg;
    }

    public function userIsRegistered($PUsername)
    {
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `registerevent`\n"
            ."WHERE\n"
            ."  `systemID`=".SYS_ID." AND\n"
            ."  `eventID` = ".$this->_get_ID()." AND\n"
            ."  `attender_PUsername` = \"".$PUsername."\"";
        return $this->get_field_for_sql($sql);
    }

    public function try_delete_item()
    {
        if (!$this->_try_delete_item_check_user_rights()) {
            $this->_try_delete_item_msg_insufficient_rights();
            return false;
        }
        if ($registrant_count = $this->count_registrations()) {
            $this->_try_delete_item_dialog_has_registrants($registrant_count);
            return false;
        }
        if ($child_count = $this->count_children()) {
            switch (get_var('submode')){
                case '':
                    $this->_try_delete_item_dialog_has_children($child_count, get_var('targetID'));
                    return false;
                break;
                case 'delete_event_promote_first_child':
                    $Obj_Event_Recurrence = new Event_Recurrence($this->_get_ID());
                    $new_ID = $Obj_Event_Recurrence->promote_first_child();
                    $Obj_promoted = new Event($new_ID);
                    $child_count = $Obj_promoted->count_children();
                    $this->_try_delete_item_msg_promoted_first_child($child_count);
                    $this->delete();
                    return true;
                break;
                case 'delete_event_delete_series':
                    $this->_try_delete_item_delete_children();
                    $this->_try_delete_item_msg_deleted_series();
                    $this->delete();
                    return true;
                break;
            }
        }
        $this->delete();
        return true;
    }

    protected function _try_delete_item_delete_children()
    {
        $children = $this->get_children();
        foreach ($children as $child) {
            $Obj = new Event($child['ID']);
            $Obj->delete();
        }
    }

    protected function _try_delete_item_dialog_has_children($child_count, $targetID)
    {
        $msg =
            "<b>Clarification Needed</b><br /><br />"
            ."This is a Master Event with "
            .($child_count==1 ? "one Recurrence" : $child_count." Recurrences").".<br /><br />"
            ."Remove just this one Event "
            .($child_count==1 ?
                "(promotes the Recurrence to a stand-alone Event)"
              :
                "(promotes the first Recurrence to 'Master')"
             )
            .",  or the whole series?"
            ."<div id='popup_buttons' style='padding-top:2em;margin:auto' class='txt_c'>"
            ."<input type='button' class='formButton' style='width:100px;' value='Cancel'"
            ." onclick=\\\"geid_set('command','');"
            ."geid_set('submode','');geid_set('targetID','');geid('form').submit();\\\""
            ." /> "
            ."<input type='button' class='formButton' style='width:100px;' value='Delete Event'"
            ." onclick=\\\"hidePopWin(null);"
            ."geid_set('command','event_delete');"
            ."geid_set('targetID','".$targetID."');"
            ."geid_set('submode','delete_event_promote_first_child');"
            ."geid('form').submit();\\\""
            ." /> "
            ."<input type='button' class='formButton' style='width:100px;' value='Delete Series'"
            ." onclick=\\\"hidePopWin(null);"
            ."geid_set('command','event_delete');"
            ."geid_set('targetID','".$targetID."');"
            ."geid_set('submode','delete_event_delete_series');"
            ."geid('form').submit();\\\""
            ." />";
        Page::push_content(
            'javascript_onload',
            "  popup_msg=\"".$msg."\";\n"
            ."  popup_dialog('Event Delete',\"<div style='padding:4px'>\"+popup_msg+\"</div>\",'320',120,'','','');\n"
        );
    }

    protected function _try_delete_item_dialog_has_registrants($registrant_count)
    {
        $msg =
            "<b>Deletion Cancelled</b><br /><br />"
            .($registrant_count==1 ? "One person has" : $registrant_count." people have")
            ." registered to attend this Event.<br /><br />"
            ."Please cancel "
            .($registrant_count==1 ? "this registration" : "these registrations")
            ." first, then try your operation again.";
        Page::push_content(
            'javascript_onload',
            "  popup_msg=\"".$msg."\";popup_dialog('Event Delete',"
            ."\"<div style='padding:4px;'>\"+popup_msg+\"</div>\",'300',120,'OK','','');\n"
        );
    }

    protected function _try_delete_item_msg_deleted_series()
    {
        $msg =
         "<b>Event Series Deleted</b><br /><br />"
        ."The Master Event and all Recurrences have been deleted.";
        Page::push_content(
            'javascript_onload',
            "  popup_msg=\"".$msg."\";popup_dialog('Event Delete',"
            ."\"<div style='padding:4px'>\"+popup_msg+\"</div>\",'320',120,'Done','','');\n"
        );
    }

    protected function _try_delete_item_msg_promoted_first_child($child_count)
    {
        $msg =
            "<b>Master Event Deleted</b><br /><br />"
            .($child_count==0 ?
                "The only Recurrence for this Event has now been converted to a stand-alone Event."
             :
                 "The first Recurrence has been promoted to Master Event for the "
                .($child_count==1 ? 'one' : '')
                .($child_count==2 ? 'two' : '')
                .($child_count>3 ? $child_count : '')
                ." remaining Recurrences."
            );
        Page::push_content(
            'javascript_onload',
            "  popup_msg=\"".$msg."\";popup_dialog('Event Delete',"
            ."\"<div style='padding:4px'>\"+popup_msg+\"</div>\",'320',120,'Done','','');\n"
        );
    }


    public function get_version()
    {
        return VERSION_EVENT;
    }
}
