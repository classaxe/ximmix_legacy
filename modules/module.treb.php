<?php
define('MODULE_TREB_VERSION', '1.0.25');
define('TREB_PROXY', 0);
define('TREB_DEBUG', 0);
define('TREB_PHOTOS_PATH', './UserFiles/Image/listings/');
define('TREB_SUBS_MUNICIP', 'Whit/Stouff|Whitchurch');
define('TREB_SUBS_ABBR', 'Sdrd|Sideroad');
define('TREB_DATA_USERNAME', 't007aol');
define('TREB_DATA_PASSWORD', 'Rs3618');

/*
Version History:
  1.0.25 (2015-01-31)
    1) Renamed function
         from TREB::_get_records_sort_records_using_filter_order_by()
         to   TREB::_get_records_sort_records_using_results_order()
    2) Changed call in TREB::_get_records_sort_records()
         from $this->_get_records_sort_records_using_filter_order_by()
         to   $this->_get_records_sort_records_using_results_order()
    3) Changes to internal arguments for TREB::_get_records_sort_records_using_results_order()
         Old: order_by
         New: results_order

  (Older version history in module.treb.txt)
*/

class TREB extends Gallery_Image
{
    public static $data_url =    "http://3pv.torontomls.net/data3pv/DownLoad3PVAction.asp";
    public static $entry_url =   "http://trebdata.trebnet.com/";
    protected $_existing_photos_arr = array();
    protected $_available_mlsnums =   array();

    public function __construct($ID = "", $systemID = SYS_ID)
    {
        parent::__construct($ID, $systemID);
        $this->_set_subtype('treb-listing');
        $this->_set_path_prefix('treb-listing'); // Required to override parent 'gallery-image'
        $this->_set_assign_type('treb-listing');
        $this->_set_object_name('TREB Listing');
        $this->_set_has_categories(true);
        $this->_set_has_groups(false);
        $this->_set_has_keywords(false);
        $this->_set_message_associated('and associated rooms have');
        Portal::portal_param_push('path_type_prefixed_types', 'TREB');
        $this->set_module_version(MODULE_TREB_VERSION);
        $this->Obj_TREB_Room = new Treb_Room;
        $this->set_edit_params(
            array(
            'report' =>                 'module.treb.listings',
            'report_rename' =>          true,
            'report_rename_label' =>    'new title',
            'icon_delete' =>            '[ICON]13 13 5150 Delete this TREB Listing[/ICON]',
            'icon_edit' =>              '[ICON]15 15 5120 Edit this TREB Listing[/ICON]',
            'icon_edit_disabled' =>     '[ICON]15 15 5135 (Edit this TREB Listing)[/ICON]',
            'icon_edit_popup' =>        '[ICON]19 19 5082 Edit this TREB Listing in a popup window[/ICON]'
            )
        );
        $this->_cp_vars_detail = array(
        'block_layout' =>             array('match' => '',                'default'=>'TREB Listing',  'hint'=>'Name of Block Layout to use'),
        'date_show' =>                array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'extra_fields_list' =>        array('match' => '',                'default'=>'',              'hint'=>'CSV list format: field|label|group,field|label|group...'),
        'item_footer_component' =>    array('match' => '',                'default'=>'',              'hint'=>'Name of component rendered below displayed Job Posting'),
        'map_height' =>               array('match' => 'range|1,n',       'default' =>'400',          'hint'=>'Height of map in pixels'),
        'map_show' =>                 array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'map_text' =>                 array('match' => '',                'default' =>'',             'hint'=>'Text to place on map marker'),
        'map_width' =>                array('match' => 'range|1,n',       'default' =>'560',          'hint'=>'Width of map in pixels'),
        'photo_height' =>             array('match' => 'range|1,n',       'default' =>'',             'hint'=>'|1..n or blank - height in px to resize'),
        'photo_show' =>               array('match' => 'range|1,n',       'default' =>'1',            'hint'=>'|1..n or blank - height in px to resize'),
        'photo_width' =>              array('match' => 'range|1,n',       'default' =>'300',          'hint'=>'|1..n or blank - width in px to resize'),
        'price_prefix' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'price_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'rooms_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'summary_color_1' =>          array('match' => 'hex3|#c0c0ff',    'default' =>'#c0c0ff',      'hint'=>'Hex colour code for colour 1 in summary table'),
        'summary_color_2' =>          array('match' => 'hex3|#e0e0ff',    'default' =>'#e0e0ff',      'hint'=>'Hex colour code for colour 2 in summary table'),
        'summary_show' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'title_linked' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'title_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1')
        );
        $this->_cp_vars_listings = array(
        'block_layout' =>             array('match' => '',                'default' =>'TREB Listing', 'hint'=>'Name of Block Layout to use'),
        'box' =>                      array('match' => 'enum|0,1,2',      'default' =>'0',            'hint'=>'0|1|2'),
        'box_footer' =>               array('match' => '',                'default' =>'',             'hint'=>'Text below displayed Job Postings'),
        'box_header' =>               array('match' => '',                'default' =>'',             'hint'=>'Text above displayed Job Postings'),
        'box_rss_link' =>             array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
        'box_title' =>                array('match' => '',                'default' =>'TREB Listings','hint'=>'text'),
        'box_title_link' =>           array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
        'box_title_link_page' =>      array('match' => '',                'default' =>'treb-listings','hint'=>'page'),
        'box_width' =>                array('match' => 'range|0,n',       'default' =>'0',            'hint'=>'0..x'),
        'category_show' =>            array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
        'comments_link_show' =>       array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
        'content_char_limit' =>       array('match' => 'range|0,n',       'default' =>'0',            'hint'=>'0..n'),
        'content_plaintext' =>        array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
        'content_show' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'date_show' =>                array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'extra_fields_list' =>        array('match' => '',                'default' =>'',             'hint'=>'CSV list format: field|label|group,field|label|group...'),
        'filter_category_list' =>     array('match' => '',                'default' =>'*',            'hint'=>'Optionally limits items to those in this gallery album - / means none'),
        'filter_category_master' =>   array('match' => '',                'default' =>'',             'hint'=>'Optionally INSIST on this category'),
        'filter_container_path' =>    array('match' => '',                'default' =>'',             'hint'=>'Optionally limits items to those contained in this folder'),
        'filter_container_subs' =>    array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'If filtering by container folder, enable this setting to include subfolders'),
        'filter_memberID' =>          array('match' => 'range|0,n',       'default' =>'0',            'hint'=>'ID of Community Member to restrict by that criteria'),
        'filter_personID' =>          array('match' => 'range|0,n',       'default' =>'0',            'hint'=>'ID of Person to restrict by that criteria'),
        'grouping_tabs' =>            array('match' => 'enum|,month,year','default' =>'',             'hint'=>'|month|year'),
        'item_footer_component' =>    array('match' => '',                'default' =>'',             'hint'=>'Name of component rendered below each displayed Job Posting'),
        'more_link_text' =>           array('match' => '',                'default' =>'(More)',       'hint'=>'text for \'Read More\' link'),
        'price_prefix' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'price_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'results_limit' =>            array('match' => 'range|0,n',       'default' =>'3',            'hint'=>'0..n'),
        'results_order_by' =>         array('match' => 'enum|date,title,price_a,price_d', 'default' =>'price_a',         'hint'=>'date|title|price_a|price_d'),
        'results_paging_controls' =>  array('match' => 'enum|0,1,2',      'default' =>'0',            'hint'=>'0|1|2 - 1 for buttons, 2 for links'),
        'thumbnail_at_top' =>         array('match' => 'enum|0,1',        'default' =>'0',            'hint'=>'0|1'),
        'thumbnail_height' =>         array('match' => 'range|1,n',       'default' =>'',             'hint'=>'|1..n or blank - height in px to resize'),
        'thumbnail_image' =>          array('match' => 'enum|,s,m,l',     'default' =>'',             'hint'=>'|s|m|l'),
        'thumbnail_link' =>           array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'thumbnail_width' =>          array('match' => 'range|1,n',       'default' =>'',             'hint'=>'|1..n or blank - width in px to resize'),
        'title_linked' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'title_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1')
        );
    }

    protected function _get_context_menu_ID()
    {
        return get_js_safe_ID($this->_get_subtype());
    }

    protected function _get_records_sort_records()
    {
        $this->_get_records_sort_records_using_results_order();
    }

    protected function _get_records_sort_records_using_results_order()
    {
        switch($this->_get_records_args['results_order']){
            case "date":
            case "date_a":
            case "title":
                parent::_get_records_sort_records_using_results_order();
                return;
            break;
            case "price_a":
                $order_arr =
                array(
                array('xml:price','a')
                );
                break;
            case "price_d":
                $order_arr =
                array(
                array('xml:price','d')
                );
                break;
        }
        $this->_get_records_records = $this->sort_records($this->_get_records_records, $order_arr);
    }


    protected function BL_detail_summary()
    {
        if (!isset($this->_cp['summary_show']) || $this->_cp['summary_show']!='1') {
            return;
        }
        switch ($this->record['xml:FreeCondComm']) {
            case "Comm":
                $listingType = "Commercial";
                break;
            case "Cond":
                $listingType = "Condominium";
                break;
            default:
                $listingType = "Freehold";
                break;
        }
        $lotsize =
         $this->record['xml:lotfront']
        .($this->record['xml:lotdepth'] ? " (front) x ".$this->record['xml:lotdepth']." (depth)" : "")
        .($this->record['xml:lotsizecode'] ? " ".$this->record['xml:lotsizecode'] : "");
        return
         "<table cellpadding='2' cellspacing='0' border='0'>\n"
        .($this->record['name'] ?             $this->BL_detail_summary_row('MLS Num:', strToUpper($this->record['name'])) : "")
        .($this->record['xml:municip'] ?      $this->BL_detail_summary_row('Location:', $this->record['xml:municip']) : "")
        .                                     $this->BL_detail_summary_row('Listing Type:', $listingType)
        .($this->record['xml:type'] ?         $this->BL_detail_summary_row('Type:', $this->record['xml:type']) : "")
        .($this->record['xml:style'] ?        $this->BL_detail_summary_row('Style:', $this->record['xml:style']) : "")
        .($this->record['xml:bedrooms'] ?     $this->BL_detail_summary_row('Bedrooms:', $this->record['xml:bedrooms']) : "")
        .($this->record['xml:washrooms'] ?    $this->BL_detail_summary_row('Bathrooms:', $this->record['xml:washrooms']) : "")
        .($this->record['xml:cac'] ?          $this->BL_detail_summary_row('Central AC:', $this->record['xml:cac']) : "")
        .($this->record['xml:heat'] ?         $this->BL_detail_summary_row('Heating:', $this->record['xml:heat']) : "")
        .($lotsize ?                          $this->BL_detail_summary_row('Lot Size:', $lotsize) : "")
        ."</table>\n";
    }

    protected function BL_detail_summary_row($header, $text)
    {
        static $row_color=1;
        if ($row_color==1) {
            $color = $this->_cp['summary_color_1'];
            $row_color=2;
        } else {
            $color = $this->_cp['summary_color_2'];
            $row_color=1;
        }
        return
         "      <tr style='background:".$color."'>\n"
        ."        <th>".$header."</th>\n"
        ."        <td>".$text."</td>\n"
        ."      </tr>\n";
    }

    protected function BL_map()
    {
        if (!isset($this->_cp['map_show']) || $this->_cp['map_show']!='1') {
            return;
        }
        $msg = "";
        if (!$this->record['map_lat']) {
            return;
        }
        $userIsAdmin =    $this->_current_user_rights['canEdit'];
        $msg = "";
        if ($userIsAdmin) {
            $canEdit = true;
            switch (get_var('submode')) {
                case "map_reset":
                    $msg = "No changes were saved.";
                    break;
                case "google_map_treb_map_save":
                    $targetValue = get_var('targetValue');
                    if ($targetValue=='') {
                        $msg = "No changes have been made.";
                    } else {
                        $coords_arr = explode(",", $targetValue);
                        $lat = $coords_arr[0];
                        $lon = $coords_arr[1];
                        $data =
                        array(
                        'map_lat'=>$lat,
                        'map_lon'=>$lon,
                        'map_location'=>$targetValue
                        );
                        $this->update($data);
                        print "<b>[ Map marker: <span style='color:#008000'>Location Saved</span> ]</b>";
                        die;
                    }
                    break;
            }
        } else {
            $canEdit = false;
        }
        $Obj_Map = new Google_Map('treb', SYS_ID);
        $Obj_Map->map_load();
        $Obj_Map->map_centre($this->record['map_lat']+0.005, $this->record['map_lon'], 13);
        $imageURL =   BASE_PATH."/UserFiles/Image/listings/".strToLower($this->record['name']).".jpg";
        $altURL =     "./UserFiles/Image/listings/image_unavailable.jpg";
        $html =
         "<span style='font-family:arial;font-size:8pt;'>"
        ."<img src='".BASE_PATH."img/spacer' width='300' height='1' style='display:block;' alt='' />"
        ."<img src='".BASE_PATH."img/width/100".$imageURL."?alt=".$altURL."' style='display:block;' hspace='5' align='left' alt='Image of property' />"
        ."<b>".$this->BL_price()."</b><br />"
        .$this->_cp['map_text']
        ."</span>";
        $Obj_Map->add_marker_with_html(
            $this->record['map_lat'],
            $this->record['map_lon'],
            $html,
            $this->_get_ID(),
            $this->_current_user_rights['canEdit']
        );
        $args = array('width'=>$this->_cp['map_width'], 'height'=>$this->_cp['map_height']);
        $map =  $Obj_Map->draw($args);
        return
        "\n"
        ."<b>Map for this property:</b><br />\n"
        .$map;
    }

    protected function BL_photo()
    {
        $img = $this->record['thumbnail_small'];
        $thumbnail_file = (substr($img, 0, strlen(BASE_PATH))==BASE_PATH ? './'.substr($img, strlen(BASE_PATH)) : $img);
        if (!$img || !file_exists($thumbnail_file)) {
            $img = false;
        }
        if (!$img) {
            return;
        }
        $path_arr =     explode('/', $thumbnail_file);
        $filename =     array_pop($path_arr);
        $path =         implode('/', $path_arr).'/';
        $filename_arr = explode('.', $filename);
        $ext =          array_pop($filename_arr);
        $file =         implode('.', $filename_arr);
        $pattern =      $path.$file.'*.'.$ext;
        $files =        safe_glob($pattern);
        foreach ($files as &$file) {
            $file = substr($path.$file, strlen(BASE_PATH));
        }
        $thumbnail_path =
        ($this->_cp['photo_width'] ?
         ($this->_cp['photo_height'] ?
            BASE_PATH."img/resize/?width=".$this->_cp['photo_width']."&amp;height=".$this->_cp['photo_height']."&amp;img=".$thumbnail_file
          :
            BASE_PATH."img/resize/?width=".$this->_cp['photo_width']."&amp;img="
         )
         :
         ($this->_cp['photo_height'] ?
            BASE_PATH."img/resize/?height=".$this->_cp['photo_height']."&amp;img="
          :
            BASE_PATH."img/sysimg/?img="
         )
        );
        $js =
        "function treb_photo_viewer(i){\n"
        ."  var photos = [\n"
        ."    \"".implode("\",\n    \"", $files)."\"\n"
        ."  ];\n"
        ."  \$('treb_photo_main').src = \"".str_replace('&amp;', '&', $thumbnail_path)."\"+photos[i];\n"
        ."  return false;\n"
        ."}\n";
        Page::push_content('javascript', $js);
        $html =
        "<div>\n"
        ."<img id=\"treb_photo_main\" alt=\"".$this->record['title']."\" src=\"".$thumbnail_path.$thumbnail_file."\" /><br />\n"
        ."<div class=\"treb_photo_thumbs\" style=\"width:".$this->_cp['photo_width']."px\" >";
        $width = ($this->_cp['photo_width'] ? ($this->_cp['photo_width']/5)-7  : 90);
        for ($i=0; $i<count($files); $i++) {
            $html.=
             "<a href=\"#\" onclick=\"return false\" onmouseover=\"return treb_photo_viewer(".$i.")\">\n"
            ."<img src=\"".BASE_PATH."img/width/".$width."/?img=".$files[$i]."\" alt='View ".(1+$i)." of ".$this->record['title']."' />\n"
            ."</a>\n";
        }
        $html .=
         "</div>"
        ."</div>";
        return $html;
    }

    protected function BL_price()
    {
        global $system_vars;
        if (!isset($this->_cp['price_show']) || $this->_cp['price_show']!='1') {
            return;
        }
        if (!isset($this->record['xml:price'])) {
            return;
        }
        $price = (float)$this->record['xml:price'];
        $prefix = ($this->_cp['price_prefix'] ? ($price>10000 ? "Sale" : "Rent") : "");
        return $prefix." ".$system_vars['defaultCurrencySymbol'].number_format($price);
    }

    protected function BL_room_count()
    {
        if (!isset($this->_cp['rooms_show']) || $this->_cp['rooms_show']!='1') {
            return;
        }
        if (!$rooms = $this->count_children()) {
            return;
        }
        return "<b>".$rooms." rooms</b> (Click headings to sort)";
    }

    protected function BL_room_listings()
    {
        global $selectID;
        if (!isset($this->_cp['rooms_show']) || $this->_cp['rooms_show']!='1') {
            return;
        }
        if (!$rooms = $this->count_children()) {
            return;
        }
        $_old =      $selectID;
        $selectID = $this->_get_ID();
        $out =      draw_auto_report('module.treb.rooms', 0);
        $selectID = $_old;
        return      $out;
    }

    public function do_download()
    {
        set_time_limit(600);
        $this->_start_date =    '20060101';
        $start_time =           get_timestamp();
        $listings_start =       $this->count_all_for_system($this->_systemID_csv);
        $rooms_start =          $this->Obj_TREB_Room->count_all_for_system($this->_systemID_csv);
        if (!$this->_manage_get_listings_unavailable_ID_csv()) {
            do_log(3, __FUNCTION__.'()', '', "Error: Bad TREB Username / Password");
            $this->_html.=
             "<div style='background-color:#FFE1E1; color:#ff0000; border: solid 1px #ff0000; padding: 1px;margin-bottom: 5px;'>"
            ."<b>Error downloading MLS Listings</b><br />\n"
            ."The TREB Data Username and Password credentials are invalid - no data was retrieved.\n"
            ."</div>";
            break;
        }
        $this->_manage_delete_unavailable();
        $this->_manage_get_listings_available();
        $this->_manage_insert_available();
        if (get_var('submode')=="download_listings_and_photos") {
            $this->_manage_download_photos();
        }
        $listings_end =         $this->count_all_for_system($this->_systemID_csv);
        $rooms_end =            $this->Obj_TREB_Room->count_all_for_system($this->_systemID_csv);
        $end_time =             get_timestamp();
        $msg =
         "Status:\n"
        ."  Began at ".$start_time."\n"
        ."  Sites updated:     ".$this->_system_titles_csv."\n"
        ."  Listings at start: ".$listings_start."\n"
        ."  Listings at end:   ".$listings_end."\n"
        ."  Rooms at start:    ".$rooms_start."\n"
        ."  Rooms at end:      ".$rooms_end."\n"
        ."  Task Execution:    from ".substr($start_time, 11)." to ".substr($end_time, 11)."\n";
        do_log(1, __CLASS__.'::'.__FUNCTION__.'()', '', $msg);
        $this->_html.=
         "<p><b>Download Completed</b><br />"
        ."&bull; Start: ".$listings_start." Listing"
        .($listings_start==1 ? '' : 's')
        ." and ".$rooms_start." room"
        .($rooms_start==1 ? '' : 's')
        .",<br />\n"
        ."&bull; End: ".$listings_end." listing"
        .($listings_end==1 ? '' : 's')
        ." and ".$rooms_end." room"
        .($rooms_end==1 ? '' : 's')
        ."</p>";

    }

    public function do_download_for_cron()
    {
        set_time_limit(600);
        $this->_isMASTERADMIN = true;
        $this->_manage_get_sites_represented();
        $this->_start_date =    '20060101';
        $start_time =           get_timestamp();
        $listings_start =       $this->count_all_for_system($this->_systemID_csv);
        $rooms_start =          $this->Obj_TREB_Room->count_all_for_system($this->_systemID_csv);
        if (!$this->_manage_get_listings_unavailable_ID_csv()) {
            $msg = "Error: Bad TREB Username / Password";
            do_log(3, __FUNCTION__.'()', '', $msg);
            return $msg;
        }
        $this->_manage_delete_unavailable();
        $this->_manage_get_listings_available();
        $this->_manage_insert_available();
        $this->_manage_download_photos();
        $listings_end =         $this->count_all_for_system($this->_systemID_csv);
        $rooms_end =            $this->Obj_TREB_Room->count_all_for_system($this->_systemID_csv);
        $end_time =             get_timestamp();
        $msg =
         "Status:\n"
        ."  Began at ".$start_time."\n"
        ."  Sites updated:     ".$this->_system_titles_csv."\n"
        ."  Listings at start: ".$listings_start."\n"
        ."  Listings at end:   ".$listings_end."\n"
        ."  Rooms at start:    ".$rooms_start."\n"
        ."  Rooms at end:      ".$rooms_end."\n"
        ."  Task Execution:    from ".substr($start_time, 11)." to ".substr($end_time, 11)."\n";
        do_log(1, __CLASS__.'::'.__FUNCTION__.'()', '', $msg);
        return $msg;
    }

    public function do_submode_extra()
    {
        switch (get_var('submode')) {
            case 'treb_listing_feature':
                $category = 'featured';
                $this->_set_ID(get_var('targetID'));
                $categories = $this->get_field('category');
                $categories_arr = ($categories=='' ? array() : explode(',', $categories));
                if (in_array($category, $categories_arr)) {
                    array_remove_value($categories_arr, $category);
                    $msg = "<b>Success:</b> 'Featured' setting turned OFF";
                } else {
                    $categories_arr[] = $category;
                    $msg = "<b>Success:</b> 'Featured' setting turned ON";
                }
                $categories_csv =   implode(',', $categories_arr);
                $this->set_field('category', $categories_csv);
                $this->category_assign($categories_csv, SYS_ID);
                return $msg;
            break;
            case 'treb_listing_openhouse':
                $category = 'open-house';
                $this->_set_ID(get_var('targetID'));
                $categories = $this->get_field('category');
                $categories_arr = ($categories=='' ? array() : explode(',', $categories));
                if (in_array($category, $categories_arr)) {
                    array_remove_value($categories_arr, $category);
                    $msg = "<b>Success:</b> 'Open House' setting turned OFF";
                } else {
                    $categories_arr[] = $category;
                    $msg = "<b>Success:</b> 'Open House' setting turned ON";
                }
                $categories_csv =   implode(',', $categories_arr);
                $this->set_field('category', $categories_csv);
                $this->category_assign($categories_csv, SYS_ID);
                return $msg;
            break;
        }
    }

    public function export_sql($targetID, $show_fields)
    {
        $header =
        "Selected ".$this->_get_object_name().$this->plural($targetID)."\n"
        ."(with Rooms)";
        $extra_delete =
         "DELETE FROM `postings`               WHERE `parentID` IN (".$targetID.") AND `type`='treb-room';\n"
        ;
        $Obj = new Backup;
        $extra_select =
         $Obj->db_export_sql_query("`postings`             ", "SELECT * FROM `postings` WHERE `parentID` IN (".$targetID.") AND `type`='treb-room' ORDER BY `ID`", $show_fields)
        ;
        return parent::sql_export($targetID, $show_fields, $header, '', $extra_delete, $extra_select);
    }

    public function delete()
    {
        $ID_csv =   $this->_get_ID();
        $ID_arr =   explode(',', $ID_csv);
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $mlsnum = strtolower($this->get_name());
            array_map("unlink", safe_glob(TREB_PHOTOS_PATH.$mlsnum.".jpg", GLOB_PATH));
            array_map("unlink", safe_glob(TREB_PHOTOS_PATH.$mlsnum."_*.jpg", GLOB_PATH));
            $this->Obj_TREB_Room->delete_for_parentID($ID);
            parent::delete();
        }
        $this->_set_ID($ID_csv);
    }

    protected function download_listings($listing_type)
    {
        $Obj_Snoopy = new Snoopy;
        $Obj_Snoopy->fetch(Treb::$entry_url);
        $Obj_Snoopy->submit(
            Treb::$data_url,
            array(
            "user_code" =>  TREB_DATA_USERNAME,
            "password" =>   TREB_DATA_PASSWORD,
            "sel_fields" => "*",
            "order_by" =>   "",
            "au_both" =>    $listing_type,
            "dl_type" =>    "file",
            "incl_names" => "yes",
            "use_table" =>  "MLS",
            "send_done" =>  "no",
            "query_str" =>  "lud>='".$this->_start_date."'",
            "submit1" =>    "Submit"
            )
        );
        $results = $Obj_Snoopy->results;
        if (strpos($results, 'Invalid User Code')!==false) {
            return "logon_fail";
        }
        $lines =            explode("\n", $results);
        $header =           trim(array_shift($lines));
        $columns =          explode(",", $header);
        $out =              array();
        foreach ($lines as $result) {
            $line =            trim($result);
            if ($line != "") {
                $line_arr =  explode("\",\"", $line);
                $fields = array();
                for ($i = 0; $i<count($columns); $i++) {
                    $fields[trim($columns[$i], '"')] = trim($line_arr[$i], '"');
                }
                $out[] = $fields;
            }
        }
        return $out;
    }

    public function get_URL($record)
    {
        return
        ($record['systemID']==SYS_ID ?
        BASE_PATH
        :
        (isset($record['systemURL']) ? trim($record['systemURL'], '/') : "")."/"
        )
        .$this->_get_path_prefix()
        ."/"
        .trim($record['path'], "/");
    }

    public function manage()
    {
        $this->_manage_get_agent_details();
        $this->_manage_get_sites_represented();
        $this->_html =
         "<p><b>"
        .($this->_isMASTERADMIN ? "Signed in as Master Admin" : "Your TREB AgentID is ".$this->_TREB_AgentID)
        .", representing ".$this->_system_titles_csv.":</b></p>";

        switch (get_var('submode')){
            case "download_listings":
            case "download_listings_and_photos":
                $this->do_download();
                break;
            default:
                $this->_html.=
                "<p>Listings: ".$this->count_all_for_system($this->_systemID_csv)
                 .", Rooms: ".$this->Obj_TREB_Room->count_all_for_system($this->_systemID_csv)
                 ."</p>";
                break;
        }
        $this->_html.=
         "<p class='txt_c'>"
        ."<input type=\"button\" value=\"Download Listings\" onclick=\"if(confirm('TREB DOWNLOAD CONFIRMATION\\nDelete unavailable listings and get new ones from TREB?')) { geid('submode').value='download_listings';this.disabled=1;geid('form').submit();} else {alert('Operation cancelled.'); return false;}\" class='formButton' style='width:180px' />"
        ."<input type=\"button\" value=\"Download with Photos\" onclick=\"if(confirm('TREB DOWNLOAD CONFIRMATION\\nDelete unavailable listings and get new ones with photos from TREB?\\n(This can be a time-consuming process)')) { geid('submode').value='download_listings_and_photos';this.disabled=1;geid('form').submit();} else {alert('Operation cancelled.'); return false;}\" class='formButton' style='width:180px' />"
        ."</p>"
        .draw_auto_report('module.treb.listings', 1);
        return $this->_html;
    }

    protected function _manage_delete_unavailable()
    {
        if (!$this->_unavailable_IDs) {
            return;
        }
        $this->_set_ID($this->_unavailable_IDs);
        $this->delete();
        do_log(1, __CLASS__.'::'.__FUNCTION__.'()', '', 'Purged unavailable listings and photos');
    }

    protected function _manage_download_photos()
    {
        $conn_id = ftp_connect("3pv.torontomls.net") or die("Couldn't connect");
        $login_result = ftp_login($conn_id, TREB_DATA_USERNAME."@photos", TREB_DATA_PASSWORD);
        ftp_pasv($conn_id, true);
        foreach ($this->_available_mlsnums as $mlsnum) {
            $lastPhoto = 0;
            while (++$lastPhoto > 0) {
                $mls_num =        substr($mlsnum, 1);
                $mls_dir =        substr($mls_num, count($mls_num)-4, 3);
                $serverfile =     "/mlsmultiphotos/".$lastPhoto."/".$mls_dir."/".$mlsnum.($lastPhoto > 1 ? "_".$lastPhoto : "").".jpg";
                $localfile =      TREB_PHOTOS_PATH.strtolower($mlsnum.($lastPhoto > 1 ? "_".$lastPhoto : "").".jpg");
                if (file_exists($localfile)) {
                    unlink($localfile);
                }
                @ftp_get($conn_id, $localfile, $serverfile, FTP_BINARY) or ($lastPhoto = -1);
            }
        }
        ftp_close($conn_id);
        do_log(1, __CLASS__.'::'.__FUNCTION__.'()', '', 'Downloaded photos');
    }

    protected function _manage_get_agent_details()
    {
        $Obj_Person =           new Person(get_userID());
        $this->_TREB_AgentID =  $Obj_Person->get_field('custom_1');
        $this->_isMASTERADMIN =    get_person_permission("MASTERADMIN");
    }

    protected function _manage_get_listings_available()
    {
        $results = $this->download_listings('avail');
        if ($results=='logon_fail') {
            return 'logon_fail';
        }
        $mls_nums = array();
        foreach ($results as $result) {
            foreach ($this->_systems_arr as $key => $value) {
                if (in_array($result['agent_id'], $value) || in_array($result['co_lagt_id'], $value)) {
                    $result['systemID'] = $key;
                    $this->_available_records[] = $result;
                    $this->_available_mlsnums[] = $result["ml_num"];
                }
            }
        }
        return true;
    }

    protected function _manage_get_listings_unavailable_ID_csv()
    {
        $results = $this->download_listings('unavail');
        if ($results=='logon_fail') {
            return false;
        }
        $mls_nums = array();
        foreach ($results as $result) {
            $mls_nums[] = $result["ml_num"];
        }
        $this->_unavailable_mlsnums =   $mls_nums;
        $this->_unavailable_IDs =       $this->get_ID_csv_for_name_csv(implode(',', $mls_nums), $this->_systemID_csv);
        return true;
    }

    protected function _manage_get_sites_represented()
    {
        $Obj_System = new System;
        $sql =
         "SELECT\n"
        ."  `ID`,\n"
        ."  `custom_1`,\n"
        ."  `textEnglish`\n"
        ."FROM\n"
        ."  `system`\n"
        ."WHERE\n"
        ."  `custom_1`!=''";
        $records = $Obj_System->get_records_for_sql($sql);
        $systems_arr =      array();
        $systems_title =    array();
        $systemID_arr =     array();
        if ($this->_isMASTERADMIN) {
            foreach ($records as $r) {
                $systems_arr[$r['ID']] = explode(",", $r['custom_1']);
                $systems_title[] = $r['textEnglish'];
                $systemID_arr[] = $r['ID'];
            }
        } else {
            foreach ($records as $r) {
                $agents_arr = explode(",", $r['custom_1']);
                if (in_array($this->_TREB_AgentID, $agents_arr)) {
                    $systems_arr[$r['ID']] = explode(",", $r['custom_1']);
                    $systems_title[] = $r['textEnglish'];
                    $systemID_arr[] = $r['ID'];
                }
            }
        }
        $this->_systems_arr =       $systems_arr;
        $this->_systemID_csv =      implode(",", $systemID_arr);
        $this->_system_titles_csv = implode(", ", $systems_title);
    }

    protected function _manage_insert_available()
    {
        $container_path =       '//listings';
        $Obj_Gallery_Album =    new Gallery_Album;
        $parentID =             $Obj_Gallery_Album->get_ID_by_path($container_path);
        foreach ($this->_available_records as $r) {
            $name =               strtolower($r['ml_num']);
            $title =              strtoupper($r['ml_num']);
            $path =               $container_path."/".$name;
            $ID =   $this->get_ID_by_name($r['ml_num'], $r['systemID'], true); // no cache on this!
            $this->_set_ID($ID);
            $st_sfx_replacements = explode(',', TREB_SUBS_ABBR);
            foreach ($st_sfx_replacements as $term) {
                $term_arr = explode('|', $term);
                $r['st_sfx'] = str_replace($term_arr[0], $term_arr[1], $r['st_sfx']);
            }
            $municip_replacements = explode(',', TREB_SUBS_MUNICIP);
            foreach ($municip_replacements as $term) {
                $term_arr = explode('|', $term);
                $r['town'] = str_replace($term_arr[0], $term_arr[1], $r['town']);
            }
            $data = array(
            'container_path' =>             $container_path,
            'enabled' =>                    1,
            'name' =>                       Record::escape_string($name),
            'parentID' =>                   $parentID,
            'path' =>                       $path,
            'permPUBLIC' =>                 1,
            'permSYSLOGON' =>               1,
            'permSYSMEMBER' =>              1,
            'subtype' =>                    Record::escape_string($this->_get_subtype()),
            'systemID' =>                   Record::escape_string($r['systemID']),
            'thumbnail_small' =>            Record::escape_string(trim(TREB_PHOTOS_PATH, '.').strtolower($r['ml_num']).'.jpg'),
            'title' =>                      Record::escape_string($title),
            'type' =>                       Record::escape_string($this->_get_type()),
            'URL' =>                        BASE_PATH.$this->_get_path_prefix().'/'.trim($path, '/'),
            'xml:agentID' =>                Record::escape_string($r['agent_id']),
            'xml:agent2ID' =>               Record::escape_string($r['co_lagt_id']),
            'xml:FreeCondComm' =>           Record::escape_string($r['class']),
            'xml:abbr' =>                   Record::escape_string($r['st_sfx']),
            'xml:approxsquarefootage' =>    Record::escape_string($r['sqft']),
            'xml:bedrooms' =>               Record::escape_string($r['br'].($r['br_plus']!='' ? " + ".$r['br_plus'] : '')),
            'xml:cac' =>                    Record::escape_string($r['cac']),
            'xml:dir' =>                    Record::escape_string($r['st_dir']),
            'xml:dis' =>                    Record::escape_string($r['zn']),
            'xml:extras' =>                 Record::escape_string($r['extras']),
            'xml:fam' =>                    Record::escape_string($r['den_fr']),
            'xml:gartype' =>                Record::escape_string($r['gar_type']),
            'xml:heat' =>                   Record::escape_string($r['heating']),
            'xml:kit' =>                    Record::escape_string($r['num_kit']),
            'xml:lotfront' =>               Record::escape_string($r['front_ft']),
            'xml:lotdepth' =>               Record::escape_string($r['depth']),
            'xml:lotsizecode' =>            Record::escape_string($r['lotsz_code']),
            'xml:municip' =>                Record::escape_string($r['town']),
            'xml:postalcode' =>             Record::escape_string($r['zip']),
            'xml:price' =>                  Record::escape_string($r['lp_dol']),
            'xml:remarksforclients' =>      Record::escape_string($r['ad_text']),
            'xml:style' =>                  Record::escape_string($r['style']),
            'xml:type' =>                   Record::escape_string($r['type_own1_out']),
            'xml:stnum' =>                  Record::escape_string($r['st_num']),
            'xml:s_r' =>                    Record::escape_string($r['s_r']),
            'xml:streetname' =>             Record::escape_string($r['st']),
            'xml:streetaddress' =>          Record::escape_string(str_replace('  ', ' ', $r['st_num'].' '.$r['st'].' '.$r['st_dir'].' '.$r['st_sfx'].' '.$r['town'].' '.$r['zip'].' ON Canada')),
            'xml:virtualtoururl' =>         Record::escape_string($r['tour_url']),
            'xml:washrooms' =>              Record::escape_string($r['bath_tot'])
            );
            if (!$ID) {
                $data['date'] =     get_timestamp();
                $data['content'] =  Record::escape_string($r['ad_text'].($r['extras'] ? ' '.$r['extras'] : ''));
                $data['category'] = $r['class'];
                $data['xml:openhouse'] = '';
                $Obj_Map =      new Google_Map(0, SYS_ID);
                $address =      str_replace('  ', ' ', $r['st_num'].' '.$r['st'].' '.$r['st_dir'].' '.$r['st_sfx'].' '.$r['town']);
                $address_1 =    $address." ".$r['zip']." ON Canada";
                $address_2 =    $address." ON Canada";
                $address_3 =    $r['zip']." ON Canada";
                if ($result =   $Obj_Map->get_geocode($address_1)) {
                    $data['map_location'] =   $address_1;
                    $data['map_lat'] =        $result['lat'];
                    $data['map_lon'] =        $result['lon'];
                } elseif ($result =   $Obj_Map->get_geocode($address_2)) {
                    $data['map_location'] =   $address_2;
                    $data['map_lat'] =        $result['lat'];
                    $data['map_lon'] =        $result['lon'];
                } elseif ($result =   $Obj_Map->get_geocode($address_3)) {
                    $data['map_location'] =   $address_3;
                    $data['map_lat'] =        $result['lat'];
                    $data['map_lon'] =        $result['lon'];
                }
            }
            $this->update($data);
            if (!$ID) {
                $ID =   $this->get_ID_by_name($r['ml_num'], $r['systemID'], true); // no cache on this!
                $this->_set_ID($ID);
                $this->category_assign($data['category'], $r['systemID']);
            }
            if ($ID) {
                $this->Obj_TREB_Room->delete_for_parentID($ID);
            }
            for ($i=1; $i<=12; $i++) {
                if ($r["rm".$i."_out"] != "") {
                    $data = array(
                    'type' =>               Record::escape_string('treb-room'),
                    'systemID' =>           Record::escape_string($r['systemID']),
                    'parentID' =>           Record::escape_string($ID),
                    'xml:level' =>          Record::escape_string($r['level'.$i]),
                    'xml:roomtype' =>       Record::escape_string($r['rm'.$i.'_out']),
                    'xml:description' =>
                    Record::escape_string(
                        $r['rm'.$i.'_dc1_out']
                        .($r['rm'.$i.'_dc2_out']!='' ? ', '.$r['rm'.$i.'_dc2_out'] : '')
                        .($r['rm'.$i.'_dc3_out']!='' ? ', '.$r['rm'.$i.'_dc3_out'] : '')
                    ),
                    'xml:length' =>         Record::escape_string($r['rm'.$i.'_len']),
                    'xml:width' =>          Record::escape_string($r['rm'.$i.'_wth'])
                    );
                    $this->Obj_TREB_Room->insert($data);
                }
            }
        }
        do_log(1, __CLASS__.'::'.__FUNCTION__.'()', '', 'Inserted available listings');
    }

    public function on_action_set_path()
    {
        global $action_parameters;
        $ID =           $action_parameters['triggerID'];
        $container_path =       '//listings';
        $Obj_Gallery_Album =    new Gallery_Album;
        $parentID =             $Obj_Gallery_Album->get_ID_by_path($container_path);
        $Obj_Treb =     new TREB;
        $ID_arr =       explode(",", $ID);
        foreach ($ID_arr as $ID) {
            $Obj_Treb->_set_ID($ID);
            $Obj_Treb->load();
            $Obj_Treb->xmlfields_decode($Obj_Treb->record);
            $r =      $Obj_Treb->record;
            $name =   strToLower($r['title']);
            $data =   array(
            'permPUBLIC' =>         1,
            'permSYSLOGON' =>       1,
            'permSYSMEMBER' =>      1,
            'container_path' =>     $container_path,
            'name' =>               $name,
            'parentID' =>           $parentID,
            'path' =>               $container_path.'/'.$name,
            );
            $Obj_Treb->update($data);
        }
    }

    public function install()
    {
        $this->uninstall();
        $sql = str_replace('$systemID', SYS_ID, file_get_contents(SYS_MODULES.'module.treb.install.sql'));
        $commands = Backup::db_split_sql($sql);
        foreach ($commands as $command) {
            $this->do_sql_query($command);
        }
        return 'Loaded data';
    }

    public function uninstall()
    {
        $sql = str_replace('$systemID', SYS_ID, file_get_contents(SYS_MODULES.'module.treb.install.sql'));
        $commands = Backup::db_split_sql($sql);
        foreach ($commands as $command) {
            $this->do_sql_query($command);
        }
        return 'Removed Module';
    }
}

class TREB_Room extends Posting
{
    public function __construct($ID = "", $systemID = SYS_ID)
    {
        parent::__construct($ID, $systemID);
        $this->_set_type('treb-room');
        $this->_set_assign_type('treb-room');
        $this->_set_object_name('TREB Room');
        $this->_set_has_categories(false);
        $this->_set_has_groups(false);
        $this->_set_has_keywords(false);
        $this->_set_message_associated('');
    }
}

class Treb_Virtual_Tour extends Component_Gallery_Thumbnails
{

    protected function _draw_invalid_album_message()
    {
        global $page_vars;
        if ($page_vars['path_extension']=='') {
            $this->_html.= "<p>No Listing number given</p>";
            return;
        }
        if (!$this->_isAdmin) {
            $this->_html.= "<p>Sorry - we cannot find a virtual tour for property ".strToUpper($page_vars['path_extension'])."</p>";
            return;
        }
        $Obj =              new Gallery_Image;
        $Container_type =   $Obj->_get_container_object_type();
        $Obj_Container =    new $Container_type;
        if (!$parentID = $Obj_Container->get_ID_by_path('//'.trim($this->_cp['virtual_tours_path'], '/'))) {
            $this->_html.= "<p><b>Error:</b><br />\nYou must first create a Gallery Album at <b>//".trim($this->_cp['virtual_tours_path'], '/')."/</b> to contain virtual tours in order to use this component.</p>";
            return;
        }
        $this->_html.=
         "<p><a href=\"#\" onclick=\""
        ."if (confirm('Create a virtual tour for this listing?')){"
        ."geid_set('targetValue','".$page_vars['path_extension']."');"
        ."geid_set('source','".$this->_safe_ID."');"
        ."geid_set('submode','gallery_album_sub_album');"
        ."geid_set('targetID','".$parentID."');"
        ."geid('form').submit()};return false;\">"
        ."<b>Click here</b></a>"
        ." to create a new virtual tour for listing ".strToUpper($page_vars['path_extension'])."</p>";
    }

    protected function _setup($instance, $args, $disable_params)
    {
        $this->_instance =          $instance;
        $this->_args =              $args;
        $this->_disable_params =    $disable_params;
        $this->_ident =             "treb_virtual_tour";
        $this->_msg =               "";
        $this->_safe_ID =           Component_Base::get_safe_ID($this->_ident, $this->_instance);
        $this->_setup_load();
    }

    protected function _setup_load()
    {
        global $page_vars;
        $this->_setup_load_cp_spec();
        $this->_setup_load_parameters();
        $this->_cp['filter_container_path'] = '//'.trim($this->_cp['virtual_tours_path'], '/').'/'.$page_vars['path_extension'];
        $this->_cp['filter_category_list'] = '*';
        $this->_cp['image_grid'] = 1;
        $this->_cp['show_links'] = 0;
        $this->_setup_load_permissions();
        $this->_setup_load_images();
        $this->_Obj_JL = new Jumploader;
        $this->_Obj_JL->init($this->_safe_ID);
    }


    protected function _setup_load_cp_spec()
    {
        $this->_parameter_spec =   array(
        'content_show' =>             array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'filter_limit' =>             array('match' => 'range|0,n',       'default' =>'3',            'hint'=>'0..n'),
        'filter_order_by' =>          array('match' => 'enum|date,title', 'default' =>'date',         'hint'=>'date|title'),
        'image_padding_horizontal' => array('match' => 'range|0,n',       'default' =>'10',           'hint'=>'Space to leave around images horizontally'),
        'image_padding_vertical' =>   array('match' => 'range|0,n',       'default' =>'10',           'hint'=>'Space to leave around images vertically'),
        'max_height' =>               array('match' => 'range|1,n',       'default' =>'100',          'hint'=>'Maximum width in pixels to make images'),
        'max_width' =>                array('match' => 'range|1,n',       'default' =>'100',          'hint'=>'Maximum height in pixels to make images'),
        'show_uploader' =>            array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'Show uploader control for administrators'),
        'title_show' =>               array('match' => 'enum|0,1',        'default' =>'1',            'hint'=>'0|1'),
        'virtual_tours_path' =>       array('match' => '',                'default' =>'//virtual-tours/',  'hint'=>'Gallery album containing all virtual tours')
        );
    }
}
