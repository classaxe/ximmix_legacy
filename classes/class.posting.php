<?php
define("VERSION_POSTING", "1.0.120");
/*
Version History:
  1.0.120 (2015-02-06)
    1) Posting::_get_records_sort_records_using_results_order() now allows for sorting by
       date_d_name_a and date_d_title_a

  (Older version history in class.posting.txt)
*/

class Posting extends Displayable_Item
{
    const FIELDS = 'ID, archive, archiveID, deleted, enabled, type, subtype, systemID, communityID, memberID, personID, group_assign_csv, name, path, container_path, active, author, canRegister, category, childID_csv, childID_featured, comments_allow, comments_count, component_parameters, contact_email, contact_info, contact_name, contact_phone, content, content_summary, content_text, custom_1, custom_2, custom_3, custom_4, custom_5, custom_6, custom_7, custom_8, custom_9, custom_10, date_end, date, effective_date_end, effective_date_start, effective_time_end, effective_time_start, enclosure_meta, enclosure_secs, enclosure_size, enclosure_type, enclosure_url, icon, image_templateID, important, keywords, layoutID, location, location_country, location_info, location_locale, location_region, location_zone, map_geocodeID, map_geocode_area, map_geocode_quality, map_geocode_type, map_lat, map_lon, map_location, max_sequence, meta_description, meta_keywords, no_email, notes1, notes2, notes3, notes4, number_of_views, orderID, parameters, parentID, password, permCOMMUNITYADMIN, permGROUPVIEWER, permGROUPEDITOR, permMASTERADMIN, permPUBLIC, permSHARED, permSYSADMIN, permSYSAPPROVER, permSYSEDITOR, permSYSLOGON, permSYSMEMBER, permUSERADMIN, process_maps, ratings_allow, recur_description, recur_mode, recur_daily_mode, recur_daily_interval, recur_weekly_interval, recur_weekly_days_csv, recur_monthly_mode, recur_monthly_dd, recur_monthly_interval, recur_monthly_nth, recur_monthly_day, recur_yearly_interval, recur_yearly_mode, recur_yearly_mm, recur_yearly_dd, recur_yearly_nth, recur_yearly_day, recur_range_mode, recur_range_count, recur_range_end_by, required_feature, popup, seq, status, subtitle, themeID, thumbnail_cs_small, thumbnail_cs_medium, thumbnail_cs_large, thumbnail_small, thumbnail_medium, thumbnail_large, time_end, time_start, title, URL, video, XML_data, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
    public $subtype;
    public $systemID;
    public $_get_records_args = array();
    public $_get_records_records = array();
    public $_get_records_total_count = 0;
    private $_default_enclosure_base_folder;

    public function __construct($ID = '', $systemID = SYS_ID)
    {
      // ID is used when copying records
      // systemID is used to allow remote access to shared events locally from same DB
        parent::__construct('postings', $ID);
        $this->_set_systemID($systemID);
        $this->_set_assign_type('event');
        $this->_set_has_activity(true);
        $this->_set_has_categories(true);
        $this->_set_has_groups(true);
        $this->_set_has_keywords(true);
        $this->_set_name_field('name');
        $this->_set_message_associated('and associated keyword, group and category mappings have');
        $this->set_edit_params(
            array(
                'report' =>                 'postings',
                'report_rename' =>          true,
                'report_rename_label' =>    'new title',
                'icon_delete' =>            '[ICON]13 13 4388 Delete this Posting[/ICON]',
                'icon_edit' =>              '[ICON]15 15 3854 Edit this Posting[/ICON]',
                'icon_edit_disabled' =>     '[ICON]15 15 3869 (Edit this Posting)[/ICON]',
                'icon_edit_popup' =>        '[ICON]18 18 3884 Edit this Posting in a popup window[/ICON]'
            )
        );
        $this->_block_layout['ID'] =    false;
        $this->_block_layout['name'] =  false;
        $this->_block_layout['single_item_detail'] =
            "<div class='panel_detail'>\n"
            ."[BL]title_linked[ARG]<h1 class='title'>[ARG]</h1>[/BL]\n"
            ."[BL]subtitle[ARG]<h2 class='subtitle'>[ARG]</h2>[/BL]"
            ."<div class='subhead'>\n"
            ."  [BL]author[ARG]<span class='author'>[ARG]</span> | [/BL]\n"
            ."  [BL]date[/BL]\n"
            ."  [BL]comments_link[ARG] | [/BL]\n"
            ."  [BL]event_times[/BL]\n"
            ."  [BL]event_cancellation_notice[ARG]<br />\n[/BL]\n"
            ."</div>"
            ."[BL]extra_fields[ARG]\n<div class='extra_fields'>[ARG]</div><br class='extra_fields' />\n[/BL]"
            ."[BL]links[/BL]"
            ."[BL]content[ARG]<div class='content'>[ARG]</div>[/BL]"
            ."[BL]item_footer_component[/BL]"
            ."[BL]location[ARG]<br /><div><b>Location:</b></div><div style='padding:0 10px'>[ARG]</div>[/BL]"
            ."[BL]map[ARG]<br />[/BL]"
            ."[BL]audio_clip[/BL]"
            ."[BL]rating[/BL]"
            ."[BL]keywords[/BL]"
            ."[BL]comments[/BL]"
            ."</div>";
        $this->_block_layout['listings_group_header'] =     "[BL]grouping_tab_header[/BL]";
        $this->_block_layout['listings_group_separator'] =  "[BL]grouping_tab_separator_if_needed[/BL]";
        $this->_block_layout['listings_group_footer'] =     "[BL]grouping_tab_footer[/BL]";
    }

    public function _get_default_enclosure_base_folder()
    {
        return $this->_default_enclosure_base_folder;
    }

    public function _get_subtype()
    {
        return $this->subtype;
    }

    public function _set_default_enclosure_base_folder($value)
    {
        $this->_default_enclosure_base_folder = $value;
    }

    public function _set_subtype($value)
    {
        $this->_push_fixed_field('subtype', $value);
        $this->subtype = $value;
    }

    public function _set_type($value)
    {
        $this->_type = $value;
        $this->_push_fixed_field('type', $value);
        $this->_set_path_prefix($value);      // Used to prefix items with IDs in path or to activate search
        $this->_set_search_type($value);      // Used to prefix items with IDs in path or to activate search
    }

    public static function check_posting_prefix($path, $posting_prefix)
    {
        $path_arr = explode("/", trim($path, "/"));
        switch ($posting_prefix) {
            case "":
                if (count($path_arr)!=1) {
                    return false;
                }
                break;
            case "YYYY":
                if (count($path_arr)!=2) {
                    return false;
                }
                $YYYY = $path_arr[0];
                if (!sanitize('range', $YYYY, 1990, 2200, false)) {
                    return false;
                }
                break;
            case "YYYY/MM":
                if (count($path_arr)!=3) {
                    return false;
                }
                $YYYY = $path_arr[0];
                $MM =   $path_arr[1];
                if (!sanitize('range', $YYYY, 1990, 2200, false) || !sanitize('range', $MM, 1, 12, false)) {
                    return false;
                }
                break;
            case "YYYY/MM/DD":
                if (count($path_arr)!=4) {
                    return false;
                }
                $YYYY = $path_arr[0];
                $MM =   $path_arr[1];
                $DD =   $path_arr[2];
                switch($DD) {
                  // Pay special attention to February:
                    case "02":
                        $_leap = (($YYYY%4==0) && ($YYYY%100!=0)) || ($YYYY%400==0);
                        $_DD = ($_leap ? 29 : 28);
                        break;
                    case "04":
                    case "06":
                    case "09":
                    case "11":
                        $_DD = 30;
                        break;
                    default:
                        $_DD = 31;
                        break;
                }
                if (
                    !sanitize('range', $YYYY, 1990, 2200, false) ||
                    !sanitize('range', $MM, 1, 12, false) ||
                    !sanitize('range', $DD, 1, $_DD, false)
                ) {
                    return false;
                }
                break;
        }
        return true;
    }

    public function count_all_for_system($systemID = SYS_ID)
    {
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `systemID` IN(".$systemID.") AND\n"
            .($this->_get_subtype() ? "  `subtype` = '".$this->_get_subtype()."' AND\n" : "")
            ."  `type` = '".$this->_get_type()."' AND\n"
            ."  1";
        return (int)$this->get_field_for_sql($sql);
    }

    public function delete_for_parentID($parentID, $systemID = false)
    {
        $sql =
             "DELETE FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($systemID ? "  `systemID` IN(".$systemID.") AND\n" : "")
            ."  `type` = '".$this->_get_type()."' AND\n"
            ."  `parentID` IN(".$parentID.")";
        $result = $this->do_sql_query($sql);
        return Record::get_affected_rows();
    }

    public function draw_product_catalogue()
    {
        $out = "";
        if (!$records =  $this->get_related_products()) {
            $out.= $this->_cp['products_msg_none'];
            return $out;
        }
        $filtered = array();
        foreach ($records as $record) {
            if (Product::is_visible($record)) {
                $record['quantity'] =
                    Cart::item_get_quantity($record['ID'], $this->_get_object_type(), $this->_get_ID());
                $filtered[] = $record;
            }
        }
        if (!get_userID() && count($records)!=count($filtered)) {
            $out.= $this->_cp['products_msg_signin']."<br />";
            if ($this->_cp['products_signin']) {
                $Obj_CSignin =    new Component_Signin;
                $args = array(
                    'text_button' =>      $this->_cp['products_signin_button'],
                    'text_password' =>    $this->_cp['products_signin_pwd'],
                    'text_title' =>       $this->_cp['products_signin_title'],
                    'text_username' =>    $this->_cp['products_signin_user']
                );
                $out.= $Obj_CSignin->draw('event_products', $args, true)."<br />";
            }
        }
        if (!count($filtered)) {
            return $out;
        }
        $out.= $this->_cp['products_msg_howto']."<br />";
        $Obj_Product_Catalogue = new Product_Catalogue_Shop;
        $args =
        array(
            'items' =>          $filtered,
            'paymentStatus' =>  '',
            'BCountryID' =>     '',
            'BSpID' =>          '',
            'related_type' =>   $this->_get_object_type(),
            'related_ID' =>     $this->_get_ID(),
            '_orderID' =>       ''
        );
        $shop = History::get('shop');
        $out.=
            $Obj_Product_Catalogue->draw($args)
            .(Cart::has_items() || $shop ?
                 "<p class='txt_c'>"
                .($shop ?
                     "<input type='button' style='width:150px' value='Continue Shopping'"
                    ." onclick=\"document.location='".$shop."'\"/> "
                :
                    ""
                )
                .(Cart::has_items() ?
                     "<input type='button' style='width:100px' value='Checkout'"
                    ." onclick=\"document.location='".BASE_PATH."checkout'\"/> "
                    ."<input type='button' style='width:100px' value='Empty Cart'"
                    ." onclick=\"if (confirm('Empty your cart? \\nThis will remove ALL items')) { "
                    ."geid('command').value='empty_cart';geid('form').submit();"
                    ."}\"/> "
                 :
                    ""
                )
                ."</p>"
             :
                ""
            );
        return $out;
    }

    public function draw_search_results($result)
    {
        $out = "";
        $offset =       $result['offset'];
        $found =        $result['count'];
        $limit =        $result['limit'];
        $search_text =  $result['search_text'];
        $search_name =  $result['search_name'];
        $retrieved =    count($result['results']);
        if ($found) {
            $out.=
                 $this->draw_search_results_paging_nav($result, $search_text)
                ."<table cellpadding='2' cellspacing='0' border='1' class='table_border' width='100%'>\n"
                ."  <tr class='table_header'>\n"
                .(isset($result['results'][0]['textEnglish']) ?
                 "    <th class='table_border txt_l'>Site</th>\n"
                 : "")
                .($search_name ? "    <th class='table_border txt_l'>".$result['search_name_label']."</th>\n" : "")
                ."    <th class='table_border txt_l'>Title</th>\n"
                ."    <th class='table_border txt_l'>Summary</th>\n"
                ."    <th class='table_border'>Date</th>\n"
                ."  </tr>\n";
            foreach ($result['results'] as $row) {
                $title =        context($row['title'], $search_text, 30);
                $text =         context($row['content_text'], $search_text, 60);
                $name =         context($row['name'], $search_name, 60);
                $date =            ($row['type']=='event' ? $row['effective_date_start'] : $row['date']);
                $active =       $this->test_publish_date($row);
                $non_enabled =  $row['enabled']==0;
                $systemID =     $row['systemID'];
                $URL =          $this->get_URL($row);
                $local =        $systemID == SYS_ID;
                if (substr($date, 0, 4)=="0000") {
      // repeating item
                    $now =    time();
                    $YYYY =   date('Y', $now);
                    $MM =        substr($date, 6, 2);
                } else {
                    $YYYY = substr($date, 0, 4);
                    $MM =    substr($date, 5, 2);
                }
                $out.=
                     "  <tr class='table_data'"
                    .($non_enabled ? " style='color:#808080' title='(Non-enabled publication)'" : "")
                    .(!$non_enabled && $active=='expired' ? " style='color:#808080' title='(Expired publication)'" : "")
                    .(!$non_enabled && $active=='pending' ? " style='color:#808080' title='(Future publication)'" : "")
                    .">\n"
                    .(isset($row['textEnglish']) ?
                    "    <td class='table_border va_t'>".$row['textEnglish']."</td>\n"
                    : "")
                    .($search_name!=="" ? "    <td class='table_border va_t'>".$name."</td>" : "")
                    ."    <td class='table_border va_t'><a"
                    .($row['title']!=strip_tags($title) ? " title=\"".$row['title']."\"" : "")
                    ." href=\"".$URL."\""
                    .($local ?
                    ""
                    :
                    " rel='external'"
                    )
                    ."><b>"
                    .($title!="" ? $title : "(Untitled)")
                    ."</b></a>"
                    ."</td>\n"
                    ."    <td class='table_border va_t'>".($text!="" ? $text : " ")."</td>\n"
                    ."    <td class='table_border va_t txt_r nowrap'>".format_date($date)."</td>\n"
                    ."  </tr>\n";
            }
            $out.=     "</table>\n<br />";
        }
        return $out;
    }

    public function export_sql($targetID, $show_fields)
    {
        return $this->sql_export($targetID, $show_fields);
    }

    public function get_community_path($communityID)
    {
        if ($communityID==0) {
            return '/';
        }
        $Obj = new Community($communityID);
        return '/'.$Obj->get_field('URL');
    }

    public function get_community_member_path($communityID)
    {
        if ($communityID==0) {
            return '/';
        }
        $sql =
             "SELECT\n"
            ."  CONCAT('/',`community`.`URL`,'/',`community_member`.`name`) `path`\n"
            ."FROM\n"
            ."  `community_member`\n"
            ."INNER JOIN `community_membership` ON\n"
            ."  `community_member`.`ID` = `community_membership`.`memberID`\n"
            ."INNER JOIN `community` ON\n"
            ."  `community_membership`.`communityID` = `community`.`ID`\n"
            ."WHERE\n"
            ."  `community_member`.`ID` = ".$communityID."\n"
            ."ORDER BY\n"
            ."  `community_membership`.`history_created_date`\n"
            ."LIMIT 0,1";
        $path =  $this->get_field_for_sql($sql);
        return ($path ? $path : '/');
    }

    public function get_coords($address = false)
    {
        if (!$address) {
            $address = $this->get_field('map_location');
        }
        $geocode = parent::get_coords($address);
        return array(
            'map_geocodeID' =>          $geocode['ID'],
            'map_geocode_area' =>       $geocode['match_area'],
            'map_geocode_type' =>       $geocode['match_type'],
            'map_geocode_quality' =>    $geocode['match_quality'],
            'map_lat' =>                $geocode['lat'],
            'map_lon' =>                $geocode['lon']
        );
    }

    public function get_ID_by_name($name, $systemID = false, $no_cache = false)
    {
        $key = $this->_get_object_name()."_".$systemID."_".$name;
        if (isset(Record::$cache_ID_by_name_array[$key]) && !$no_cache) {
            return Record::$cache_ID_by_name_array[$key];
        }
        $sql =
             "SELECT\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `type` = '".$this->_get_type()."' AND\n"
            .($systemID ?
              "  `systemID` IN(".$systemID.") AND\n"
            : "  `systemID` IN(1," . SYS_ID . ") AND\n"
            )
            ."  `".$this->_get_name_field()."` = \"".$name."\"\n"
            ."ORDER BY\n"
            ."  `systemID` = ".SYS_ID." DESC\n"
            ."LIMIT 0,1";
  //    z($sql);
        $value = $this->get_field_for_sql($sql);
        Record::$cache_ID_by_name_array[$key] = $value;
        return $value;
    }

    public function get_ID_by_path($path, $systemID = false, $no_cache = false)
    {
        $path = '//'.trim($path, '/');
        $key = $this->_get_object_name()."_".$systemID."_".str_replace('/', '_', trim($path, '/'));
        if (isset(Record::$cache_ID_by_name_array[$key]) && !$no_cache) {
            return Record::$cache_ID_by_name_array[$key];
        }
        $sql =
             "SELECT\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($systemID ?
                "  `systemID` IN(".$systemID.") AND\n"
              :
                "  `systemID` IN(1," . SYS_ID . ") AND\n"
             )
            ."  `type` = \"".$this->_get_type()."\" AND\n"
            ."  (`path` = \"".$path."\" OR CONCAT(`container_path`,'/',`name`) = \"".$path."\")\n"
            ."ORDER BY\n"
            ."  `systemID` = ".SYS_ID." DESC\n"
            ."LIMIT 0,1";
  //    z($sql);
        $value = $this->get_field_for_sql($sql);
        Record::$cache_ID_by_name_array[$key] = $value;
        return $value;
    }

    public function get_ID_csv_for_name_csv($name_csv, $systemID = false)
    {
        $sql =
             "SELECT\n"
            ."  GROUP_CONCAT(`ID`)\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($systemID ? "  `systemID` IN(".$systemID.") AND\n" : "")
            ."  `type` = '".$this->_get_type()."' AND\n"
            ."  `".$this->_get_name_field()."` IN(\"".implode("\",\"", explode(",", $name_csv))."\");";
        return $this->get_field_for_sql($sql);
    }

    public static function get_match_for_name($path, &$type, &$ID)
    {
        if (!Posting::check_posting_prefix($path, POSTING_PREFIX)) {
            return false;
        }
        $name_arr = explode("/", trim($path, "/"));
        switch (POSTING_PREFIX) {
            case "":
                $name = urlencode($name_arr[0]);
                break;
            case "YYYY":
                $YYYY = $name_arr[0];
                $name = urlencode($name_arr[1]);
                break;
            case "YYYY/MM":
                $YYYY = $name_arr[0];
                $MM =   $name_arr[1];
                $name = urlencode($name_arr[2]);
                break;
            case "YYYY/MM/DD":
                $YYYY = $name_arr[0];
                $MM =   $name_arr[1];
                $DD =   $name_arr[2];
                $name = urlencode($name_arr[3]);
                break;
        }
        $sql =
             "SELECT\n"
            ."  `type`,\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `postings`\n"
            ."WHERE\n"
            ."  `type`!= 'event' AND\n"
            ."  `name` = \"".$name."\" AND\n"
            .(isset($YYYY) ? "  YEAR(`date`)= ".$YYYY." AND\n" : "")
            .(isset($MM) ?   "  MONTH(`date`)=".$MM."   AND\n" : "")
            .(isset($DD) ?   "  DAY(`date`)=  ".$DD."   AND\n" : "")
            ."  `systemID` = ".SYS_ID."\n"
            ."UNION SELECT\n"
            ."  `type`,\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `postings`\n"
            ."WHERE\n"
            ."  `type`= 'event' AND\n"
            ."  `name` = \"".$name."\" AND\n"
            .(isset($YYYY) ? "  YEAR(`effective_date_start`)= ".$YYYY." AND\n" : "")
            .(isset($MM) ?   "  MONTH(`effective_date_start`)=".$MM."   AND\n" : "")
            .(isset($DD) ?   "  DAY(`effective_date_start`)=  ".$DD."   AND\n" : "")
            ."  `systemID` = ".SYS_ID;
  //    z($sql);
        $record = Posting::get_record_for_sql($sql);
        if (!$record) {
            return false;
        }
        $posting_date_prefix_types = Portal::portal_param_get('path_date_prefixed_types');
        foreach ($posting_date_prefix_types as $_type) {
            $Obj = new $_type;
            if ($record['type']==$Obj->_get_search_type()) {
                $type = $Obj->_get_path_prefix();
                $ID =   $record['ID'];
                return true;
            }
        }
        return false;
    }

    public function get_n_per_category($args)
    {
        $category_list =        $args['category_list'];
        $category_master =      (isset($args['category_master']) ? $args['category_master'] : "");
        $systemIDs_csv =        ($args['systemIDs_csv'] ? $args['systemIDs_csv'] : SYS_ID);
        $limit_per_category =   $args['limit_per_category'];
        $order =                $args['order'];
        $sql_arr =              array();
        $category_arr =         explode(",", str_replace(" ", "", $category_list));
        foreach ($category_arr as $category) {
            $sql_arr[] =
                 "(SELECT\n"
                ."  '".$category."' `cat`,\n"
                ."  `postings`.*\n"
                ."FROM\n"
                ."  `postings`\n"
                ."WHERE\n"
                .$this->get_permission_sql_for_viewer()
                ."  `systemID` IN(".$systemIDs_csv.") AND\n"
                ."  `archive` = 0 AND\n"
                ."  `enabled` = 1 AND\n"
                ."  `type` = '".$this->_get_type()."' AND\n"
                ."  `date` < NOW() AND\n"
                ."  (`date_end` ='0000-00-00' OR `date_end` > NOW())"
                .($category ? "AND\n  `category` REGEXP('".$category."')\n" : "")
                .($category_master ? "AND\n  `category` REGEXP('".$category_master."')\n" : "")
                .($order ? "ORDER BY\n  ".$order."\n" : "")
                .($limit_per_category ? "LIMIT 0,".$limit_per_category.")\n" : "");
        }
        $sql =  implode("UNION\n", $sql_arr);
  //    y($_SESSION);
  //    z($sql);die;
        $records = $this->get_records_for_sql($sql);

        $Obj_Category = new Category;
        $categories = array();
        foreach ($records as $record) {
            $categories[$record['cat']] = $record['cat'];
        }
        $categories = $Obj_Category->get_labels_for_values(
            "'".implode("','", array_keys($categories))."'",
            "'".get_class($this)." category'"
        );
  //     y($categories);
        foreach ($records as &$record) {
            $record['cat_label'] =    $categories[$record['cat']];
        }
        return $records;
    }

    public function get_permission_sql_for_viewer()
    {
        if (!get_userID()) {
            return "  `permPUBLIC` = 1 AND\n";
        }
        $sql =
             "  (\n"
            ."    `permSYSLOGON` = 1"
            .($_SESSION['person']['permSYSMEMBER']==1 ? " OR\n    `permSYSMEMBER` = 1" : "");
        $group_IDs = array();
        foreach ($_SESSION['person']['permissions'] as $level => $groups) {
            switch($level){
                case "VIEWER":
                case "EDITOR":
                case "APPROVER":
                case "ADMIN":
                    foreach ($groups as $group) {
                        $group_IDs[$group] = true;
                    }
                    break;
            }
        }
        $group_IDs = array_keys($group_IDs);
        foreach ($group_IDs as $groupID) {
            $sql.= " OR\n    ".$groupID." IN(`group_assign_csv`)";
        }
        $sql.=
         "\n  ) AND\n";
        return $sql;
    }

    public function get_random_record($category_csv = "", $systemID = SYS_ID)
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($category_csv!="" ?
                "  `category` REGEXP \"".implode("|", explode(',', str_replace(" ", "", $category_csv)))."\" AND\n"
              :
                ""
             )
            ."  `systemID` = ".$systemID." AND\n"
            ."  `type` = '".$this->_get_type()."'\n"
            ."ORDER BY\n"
            ."  RAND()\n"
            ."LIMIT 1";
        return $this->get_record_for_sql($sql);
    }

    public function get_record()
    {
        $record = parent::get_record();
        if (!is_subclass_of($this, 'Posting')) {
            return $record;
        }
        if ($record['type'] == $this->_get_type()) {
            return $record;
        }
        return false;
    }

    public function get_records()
    {
        $args = func_get_args();
        $vars = array(
            'byRemote' =>                 0,
            'category' =>                 '*',
            'category_master' =>          '',
            'communityID' =>              '',
            'container_path' =>           '',
            'container_subs' =>           0,
            'DD' =>                       '',
            'filter_date_duration' =>     '',
            'filter_date_units' =>        '',
            'filter_range_address' =>     '',
            'filter_range_distance' =>    '',
            'filter_range_lat' =>         '',
            'filter_range_lon' =>         '',
            'filter_range_units' =>       '',
            'important' =>                '',
            'isShared' =>                 '',
            'memberID' =>                 '',
            'MM' =>                       '',
            'offset' =>                   0,
            'personID' =>                 '',
            'results_limit' =>            0,
            'results_order' =>            'date',
            'what' =>                     '',
            'YYYY' =>                     ''
        );
        if (!$this->_get_args($args, $vars, true)) {
            die('Error - no parameters passed');
        }
        $_args = array(
            'byRemote' =>               $vars['byRemote'],
            'category' =>               $vars['category'],
            'category_master' =>        $vars['category_master'],
            'communityID' =>            $vars['communityID'],
            'container_path' =>         $vars['container_path'],
            'container_subs' =>         $vars['container_subs'],
            'DD' =>                     $vars['DD'],
            'filter_date_duration' =>   $vars['filter_date_duration'],
            'filter_date_units' =>      $vars['filter_date_units'],
            'filter_range_address' =>   $vars['filter_range_address'],
            'filter_range_distance' =>  $vars['filter_range_distance'],
            'filter_range_lat' =>       $vars['filter_range_lat'],
            'filter_range_lon' =>       $vars['filter_range_lon'],
            'filter_range_units' =>     $vars['filter_range_units'],
            'important' =>              $vars['important'],
            'isShared' =>               $vars['isShared'],
            'memberID' =>               $vars['memberID'],
            'MM' =>                     $vars['MM'],
            'offset' =>                 $vars['offset'],
            'personID' =>               $vars['personID'],
            'results_limit' =>          $vars['results_limit'],
            'results_order' =>          $vars['results_order'],
            'what' =>                   $vars['what'],
            'YYYY' =>                   $vars['YYYY']
        );
  //    y($_args);die;
        $this->_get_records_args = $_args;
        $this->_get_records_get_initial_records();
        $this->_get_records_load_permissions();
        $this->_get_records_available();
        $this->_get_records_from_partners();
        $this->_get_records_sort_records();
        $this->_get_records_set_total_count();
        $this->_get_records_apply_offset_and_limits();
        return array('total'=>$this->_get_records_total_count,'data'=>$this->_get_records_records);
    }

    protected function _get_records_apply_offset_and_limits()
    {
        if ($this->_get_records_args['results_limit']==0 && $this->_get_records_args['offset']==0) {
            return;
        }
        $this->_get_records_records = array_slice(
            $this->_get_records_records,
            ($this->_get_records_args['offset'] ? $this->_get_records_args['offset'] : 0),
            $this->_get_records_args['results_limit']
        );
    }

    protected function _get_records_available()
    {
        $records = $this->_get_records_records;
        $this->_get_records_records = array();
        foreach ($records as $record) {
            if ($this->is_available($record)) {
                $this->_get_records_records[] = $record;
            }
        }
    }

    protected function _get_records_from_partners()
    {
        global $system_vars;
        $byRemote =         $this->_get_records_args['byRemote'];
        $category =         $this->_get_records_args['category'];
        $category_master =  $this->_get_records_args['category_master'];
        $DD =               $this->_get_records_args['DD'];
        $memberID =         $this->_get_records_args['memberID'];
        $MM =               $this->_get_records_args['MM'];
        $offset =           $this->_get_records_args['offset'];
        $personID =         $this->_get_records_args['personID'];
        $what =             $this->_get_records_args['what'];
        $YYYY =             $this->_get_records_args['YYYY'];
        $container_path =   $this->_get_records_args['container_path'];
        $container_subs =   $this->_get_records_args['container_subs'];
        if ($byRemote || $system_vars['provider_list']=='') {
            return;
        }
        $provider_list = $system_vars['provider_list'];
        $local_providers_arr = array();
        $provider_arr = explode(",", $provider_list);
        foreach ($provider_arr as $url) {
            if (trim($url)!='') {
                $url = trim($url);
                if (substr($url, 0, 1)=='(') {
                    $local_providers_arr[] = str_replace(array('(',')'), array('',''), $url);
                }
            }
        }
  //    y($provider_arr); y($local_providers_arr); die;
        if (count($local_providers_arr)) {
            $Obk_System = new System;
            $Obk_System->get_IDs_for_URLs(implode(',', $local_providers_arr)); // fill cache with all values at once
        }
  //      y(System::$cache_ID_by_URL_array);
        foreach ($provider_arr as $url) {
            if (trim($url)!='') {
                $url =          trim($url);
                $isLocal =      (substr($url, 0, 1)=='(');
                $url =          str_replace(array('(',')'), array('',''), $url);
                $Obj_Remote =   new Remote(trim($url), $isLocal);
                switch($this->_get_type()) {
                    case 'article':
                        $results = $Obj_Remote->get_items(
                            'articles',
                            '',
                            '',
                            '',
                            0,
                            $category,
                            0,
                            $category_master,
                            $memberID,
                            $personID,
                            $DD
                        );
                        break;
                    case 'event':
                        $results = $Obj_Remote->get_items(
                            'events',
                            $what,
                            $YYYY,
                            $MM,
                            0,
                            $category,
                            0,
                            $category_master,
                            $memberID,
                            $personID,
                            $DD
                        );
                        break;
                    case 'gallery-image':
                        $results = $Obj_Remote->get_items(
                            'gallery-images',
                            '',
                            '',
                            '',
                            0,
                            $category,
                            0,
                            $category_master,
                            $memberID,
                            $personID,
                            $DD,
                            $container_path,
                            $container_subs
                        );
                        break;
                    case 'job':
                        $results = $Obj_Remote->get_items(
                            'jobs',
                            '',
                            '',
                            '',
                            0,
                            $category,
                            0,
                            $category_master,
                            $memberID,
                            $personID,
                            $DD
                        );
                        break;
                    case 'news':
                        $results = $Obj_Remote->get_items(
                            'news',
                            '',
                            '',
                            '',
                            0,
                            $category,
                            0,
                            $category_master,
                            $memberID,
                            $personID,
                            $DD
                        );
                        break;
                    case 'podcast':
                        $results = $Obj_Remote->get_items(
                            'podcasts',
                            '',
                            '',
                            '',
                            0,
                            $category,
                            0,
                            $category_master,
                            $memberID,
                            $personID,
                            $DD,
                            $container_path,
                            $container_subs
                        );
                        break;
                }
                if (isset($results) && is_array($results)) {
                    foreach ($results as &$result) {
                        $result['content'] = absolute_path($result['content'], trim($url, " /")."/");
                    }
                    $this->_get_records_records = array_merge($results, $this->_get_records_records);
                }
            }
        }
    }

    protected function _get_records_get_initial_records()
    {
        $sql = $this->_get_records_get_sql();
        $this->_get_records_records = $this->get_records_for_sql($sql);
    }

    protected function _get_records_get_sql()
    {
        $sql =
             "SELECT\n"
            ."  `postings`.*,\n"
            ."  `system`.`textEnglish` `systemTitle`,\n"
            ."  `system`.`URL` `systemURL`\n"
            ."FROM\n"
            ."  `postings`\n"
            ."INNER JOIN `system` ON\n"
            ."  `postings`.`systemID` = `system`.`ID`\n"
            ."WHERE\n"
            ."  `postings`.`type` = '".$this->_get_type()."' AND\n"
            .($this->_get_subtype() ?
                "  `postings`.`subtype` = '".$this->_get_subtype()."' AND\n"
              :
                ""
             )
            .$this->_get_records_get_sql_filter_publish_date()
            .$this->_get_records_get_sql_filter_date()
            .$this->_get_records_get_sql_filter_range()
            .($this->_get_records_args['byRemote']==true ?
                "  `permSHARED` = 1 AND\n"
              :
                ""
             )
            .($this->_get_records_args['category']!=='*' && $this->_get_records_args['category']!==''?
                 "  `postings`.`category` REGEXP \""
                .implode("|", explode(',', $this->_get_records_args['category']))
                ."\" AND\n"
              :
                ""
             )
            .($this->_get_records_args['category_master'] ?
                 "  `postings`.`category` REGEXP \""
                .implode("|", explode(',', $this->_get_records_args['category_master']))
                ."\" AND\n"
              :
                ""
             )
            .($this->_get_records_args['container_path'] ?
                ($this->_get_records_args['container_subs'] ?
                     "  `postings`.`container_path` LIKE \"//"
                    .trim($this->_get_records_args['container_path'], '/')."%"
                    ."\" AND\n"
                 :
                     "  `postings`.`container_path` = \"//"
                    .trim($this->_get_records_args['container_path'], '/')
                    ."\" AND\n"
                )
                :
                ""
             )
            .($this->_get_records_args['important']!=='' ?
                "  `postings`.`important`=".$this->_get_records_args['important']." AND\n"
              :
                ""
             )
            .($this->_get_records_args['communityID']!=='' ?
                "  `postings`.`communityID` IN(".$this->_get_records_args['communityID'].") AND\n"
              :
                ""
             )
            .($this->_get_records_args['memberID']!=='' ?
                "  `postings`.`memberID` IN(".$this->_get_records_args['memberID'].") AND\n"
              :
                ""
             )
            .($this->_get_records_args['isShared']==true ?
                "  `permSHARED` = 1 AND\n"
              :
                ""
             )
            .($this->_get_records_args['personID'] ?
                "  `postings`.`personID` IN(".$this->_get_records_args['personID'].") AND\n"
                :
                ""
             )
            ."  `postings`.`systemID` = '".$this->_get_systemID()."'\n";
  //    z($sql);
        return $sql;
    }

    protected function _get_records_get_sql_filter_date()
    {
        return "";
    }

    protected function _get_records_get_sql_filter_range()
    {
        if (!$this->_get_records_args['filter_range_distance']) {
            return "";
        }
        switch ($this->_get_records_args['filter_range_units']){
            case 'mile':
                $dx =   (float)$this->_get_records_args['filter_range_distance'];
                break;
            case 'km':
                $dx =   (float)$this->_get_records_args['filter_range_distance'] * 0.621371192;
                break;
        }
        if ($this->_get_records_args['filter_range_address']) {
            $result = Google_Map::find_geocode($this->_get_records_args['filter_range_address']);
            if ($result['error']) {
                return "";
            }
            $lat =    $result['lat'];
            $lon =    $result['lon'];
        } else {
            $lat =      $this->_get_records_args['filter_range_lat'];
            $lon =      $this->_get_records_args['filter_range_lon'];
        }
        $lat_dx =   $dx / ((6076 / 5280) * 60);
        $lon_dx =   $dx / (((cos($lat * 3.141592653589 / 180) * 6076) / 5280) * 60);
        return
             "  (\n"
            ."    `map_lat` >= ".($lat-$lat_dx)." AND `map_lat` <= ".($lat+$lat_dx)." AND\n"
            ."    `map_lon` >= ".($lon-$lon_dx)." AND `map_lon` <= ".($lon+$lon_dx)."\n"
            ."  ) AND\n";
    }

    protected function _get_records_get_sql_filter_publish_date()
    {
        if (!$this->_get_has_publish_date()) {
            return "";
        }
        $now =              get_timestamp();
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
        return
             "  (\n"
            ."    (`postings`.`date` < \"".$now_YYYY."-".$now_MM."-".$now_DD."\") OR\n"
            ."    (`postings`.`date` = \"".$now_YYYY."-".$now_MM."-".$now_DD."\" AND"
            ." `postings`.`time_start`<=\"".$now_hh.":".$now_mm."\") OR\n"
            ."    (`postings`.`date`='0000-00-00')\n"
            ."  ) AND\n"
            ."  (\n"
            ."    `postings`.`date_end` > \"".$now_YYYY."-".$now_MM."-".$now_DD."\" OR\n"
            ."    `postings`.`date_end`='0000-00-00'\n"
            ."  ) AND\n";
    }

    protected function _get_records_load_permissions()
    {
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $this->_isAdmin =   ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    }

    protected function _get_records_set_total_count()
    {
        $this->_get_records_total_count = count($this->_get_records_records);
    }

    protected function _get_records_sort_records()
    {
        $this->_get_records_sort_records_using_results_order();
    }

    protected function _get_records_sort_records_using_results_order()
    {
        switch($this->_get_records_args['results_order']){
            case "date":
                switch($this->_get_records_args['what']) {
                    case "year":
                    case "future":
                    case "month":
                        $order_direction =  'a';
                        break;
                    default:
                        $order_direction =  'd';
                        break;
                }
                $date_field = (is_a($this, 'Event') ? 'effective_date_start' :'date');
                $order_arr = array(
                    array($date_field, $order_direction),
                    array('systemTitle', 'a'),
                    array('effective_time_start', 'a'),
                    array('effective_time_end', 'a'),
                    array('title', $order_direction)
                );
                break;
            case "date_a":
                $order_direction =  'a';
                $date_field = 'date';
                $order_arr = array(
                    array($date_field, $order_direction),
                    array('systemTitle', 'a'),
                    array('effective_time_start', 'a'),
                    array('effective_time_end', 'a'),
                    array('title', 'a')
                );
                break;
            case 'date_d_name_a':
                $order_arr = array(
                    array('date', 'd'),
                    array('systemTitle', 'd'),
                    array('effective_time_start', 'd'),
                    array('effective_time_end', 'a'),
                    array('name', 'a')
                );
                break;
            case 'date_d_title_a':
                $order_arr = array(
                    array('date', 'd'),
                    array('systemTitle', 'd'),
                    array('effective_time_start', 'd'),
                    array('effective_time_end', 'a'),
                    array('title', 'a')
                );
                break;
            case "itemCode":
                $order_arr =
                array(
                array('itemCode', 'a')
                );
                break;
            case "name":
                $order_arr =
                array(
                array('name', 'a')
                );
                break;
            case "title":
                $order_arr =
                array(
                array('title', 'a')
                );
                break;
        }
        $this->_get_records_records = $this->sort_records($this->_get_records_records, $order_arr);
    }

    protected function get_related_products()
    {
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
        ."INNER JOIN `product_relationship` ON\n"
        ."  `product_relationship`.`related_objectID` = `postings`.`ID` AND\n"
        ."  `product_relationship`.`related_object` =   '".$this->_get_type()."'\n"
        ."INNER JOIN `product` ON\n"
        ."  `product_relationship`.`productID` = `product`.`ID`\n"
        ."WHERE\n"
        ."  `postings`.`type`='".$this->_get_type()."' AND\n"
        ."  `postings`.`ID` = ".$this->_get_ID();
        $records = $this->get_records_for_sql($sql);
        $out = array();
        foreach ($records as $record) {
            if (Product::is_enabled($record) && Product::is_in_active_date_range($record)) {
                $out[] = $record;
            }
        }
        return $out;
    }

    public function get_root_ID($ID, $depth = 0)
    {
        $max_depth = 3;
        $sql =
         "SELECT"
        ."  `postings`.`parentID`\n"
        ."FROM\n"
        ."  `".$this->_get_db_name()."`.`postings`\n"
        ."WHERE\n"
        ."  `systemID`=".SYS_ID." AND\n"
        ."  `type` = '".$this->_get_type()."' AND\n"
        ."  `ID` = ".$ID;
        if (!$result = $this->get_record_for_sql($sql)) {
            return 0;
        }
        $parentID = $result['parentID'];
        if ($parentID && $depth < $max_depth) {
            $ID = $this->get_root_ID($parentID, $depth++);
        }
        return $ID;
    }

    public function get_search_results($args, $cp)
    {
        $search_categories =
            (isset($args['search_categories']) ?    $args['search_categories'] : "");
        $search_communityID =
            (isset($args['search_communityID']) ?   $args['search_communityID'] : 0);
        $search_date_end =
            (isset($args['search_date_end']) ?      $args['search_date_end'] : "");
        $search_date_start =
            (isset($args['search_date_start']) ?    $args['search_date_start'] : "");
        $search_keywordIDs =
            (isset($args['search_keywordIDs']) ?    $args['search_keywordIDs'] : "");
        $search_memberID =
            (isset($args['search_memberID']) ?      $args['search_memberID'] : 0);
        $search_name =
            (isset($args['search_name']) ?          $args['search_name'] : "");
        $search_name_label =
            (isset($args['search_name_label']) ?    $args['search_name_label'] : "");
        $search_offset =
            (isset($args['search_offset']) ?        $args['search_offset'] : 0);
        $search_sites =
            (isset($args['search_sites']) ?         $args['search_sites'] : "");
        $search_text =
            (isset($args['search_text']) ?          $args['search_text'] : "");
        $search_type =
            (isset($args['search_type']) ?          $args['search_type'] : "*");
        $systems_csv =
            (isset($args['systems_csv']) ?          $args['systems_csv'] : "");
        $systemIDs_csv =
            (isset($args['systemIDs_csv']) ?        $args['systemIDs_csv'] : "");
        $limit =
            (isset($args['search_results_page_limit']) ?    $args['search_results_page_limit'] : false);
        $sortBy =
            (isset($args['search_results_sortBy']) ?        $args['search_results_sortBy'] : 'relevance');
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =       get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =      get_person_permission("SYSEDITOR");
        $userIsAdmin =      ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
        if (strlen($search_date_end)==4) {
            $search_date_end = $search_date_end."-12-31";
        }
        if (strlen($search_date_end)==7) {
            $search_date_end = $search_date_end."-31";
        }
        $structure = array(
            'count' =>              0,
            'limit' =>              $limit,
            'offset' =>             $search_offset,
            'results' =>            array(),
            'search_name' =>        $search_name,
            'search_name_label' =>  $search_name_label,
            'search_text' =>        $search_text
        );
        $out = array(
            'article' =>        $structure,
            'event' =>          $structure,
            'gallery-image' =>  $structure,
            'job-posting' =>    $structure,
            'news-item' =>      $structure,
            'podcast' =>        $structure
        );
        $search_types = array();
        if ($cp['search_articles']) {
            $search_types[] = "'article'";
        }
        if ($cp['search_events']) {
            $search_types[] = "'event'";
        }
        if ($cp['search_gallery_images']) {
            $search_types[] = "'gallery-image'";
        }
        if ($cp['search_jobs']) {
            $search_types[] = "'job-posting'";
        }
        if ($cp['search_news']) {
            $search_types[] = "'news-item'";
        }
        if ($cp['search_podcasts']) {
            $search_types[] = "'podcast'";
        }
        if (count($search_types)==0) {
            return $out;
        }
        switch ($sortBy) {
            case 'date':
                $order = "  `search_date` DESC\n";
                break;
            case 'relevance':
                $order = ($search_text ?
                    "  `p`.`name` LIKE \"".$search_text."%\" DESC,\n"
                   ."  `p`.`title` LIKE \" %".$search_text." %\" DESC,\n"
                   ."  `p`.`content` LIKE \" %".$search_text." %\" DESC,\n"
                   ."  `search_date` DESC,\n"
                   ."  `p`.`content` LIKE \"%".$search_text." %\" DESC,\n"
                   ."  `p`.`title` LIKE \"%".$search_text." %\" DESC\n"
                :
                    "  `search_date` DESC, `p`.`title`\n"
                );
                break;
            case 'title':
                $order = "  `p`.`title`\n";
                break;
        }
        $search_offset = (int)$search_offset;
        $sql =
             "SELECT\n"
            ."  `p`.`ID`,\n"
            ."  `p`.`systemID`,\n"
            ."  `p`.`communityID`,\n"
            ."  `p`.`memberID`,\n"
            ."  `p`.`content_text`,\n"
            ."  `p`.`date`,\n"
            ."  `p`.`date_end`,\n"
            ."  `p`.`effective_date_start`,\n"
            ."  `p`.`enabled`,\n"
            ."  IF(`p`.`type`='event',`effective_date_start`,`date`) `search_date`,\n"
            ."  `p`.`group_assign_csv`,\n"
            ."  `p`.`name`,\n"
            ."  `p`.`path`,\n"
            ."  `p`.`permPUBLIC`,\n"
            ."  `p`.`permSYSLOGON`,\n"
            ."  `p`.`permSYSMEMBER`,\n"
            ."  `p`.`time_start`,\n"
            ."  `p`.`type`,\n"
            ."  `p`.`title`,\n"
            ."  `p`.`URL`,\n"
            .((string)$systemIDs_csv!=(string)SYS_ID ?
                 "  `s`.`textEnglish`,\n"
                ."  `s`.`URL` AS `systemURL`,\n"
              :
                ""
             )
            ."  `p`.`history_created_date`\n"
            ."FROM\n"
            ."  `postings` `p`\n"
            .((string)$systemIDs_csv!=(string)SYS_ID ?
                 "INNER JOIN `system` `s` ON\n"
                ."  `p`.`systemID` = `s`.`ID`\n"
              :
                ""
             )
            .($search_keywordIDs!="" ?
                 "INNER JOIN `keyword_assign` `k` ON\n"
                ."  `p`.`ID` = `k`.`assignID`\n"
              :
                ""
             )
            ."WHERE\n"
            ."  `p`.`systemID` IN (".$systemIDs_csv.") AND\n"
            ."  `p`.`type` IN(".implode(',', $search_types).") AND\n"
            .($search_communityID!=0 ?
                 "  (`p`.`communityID`=".$search_communityID." OR"
                ." `p`.`memberID` IN("
                ."SELECT `memberID` FROM `community_membership` WHERE `communityID`=".$search_communityID
                .")) AND\n"
              :
                ""
             )
            .($search_memberID!=0 ?
                "  `p`.`memberID` IN(".$search_memberID.") AND\n"
              :
                ""
             )
            .($userIsAdmin ?
                ""
              :
                "  IF(`p`.`type`='event',`effective_date_start`,`date`)<= NOW() AND\n"
             )
            .($search_date_start!="" ?
                "  IF(`p`.`type`='event',`effective_date_start`,`date`) >= '".$search_date_start."' AND\n"
              :
                ""
             )
            .($search_date_end!="" ?
                 "  IF(`p`.`type`='event',`effective_date_start`,`date`)"
                ." < CONVERT(DATE_ADD('".$search_date_end."',INTERVAL 1 DAY) USING utf8) AND\n"
              :
                ""
             )
            .($search_keywordIDs!="" ?
                "  `k`.`keywordID` IN(".$search_keywordIDs.") AND\n"
              :
                ""
             )
            .($search_type=='' || $search_type=='*' ?
                ""
              :
                "  `p`.`type` = '".$search_type."' AND\n"
             )
            .($search_text ?
                 "(\n"
                .($search_name=='' ? "" : "  `p`.`name` LIKE \"%".$search_name."%\" OR\n")
                ."  `p`.`content_text` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`content_text` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`content_text` LIKE \"".$search_text."%\" OR\n"
                ."  `p`.`meta_description` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`meta_description` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`meta_description` LIKE \"".$search_text."%\" OR\n"
                ."  `p`.`meta_keywords` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`meta_keywords` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`meta_keywords` LIKE \"".$search_text."%\" OR\n"
                ."  `p`.`title` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`title` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`title` LIKE \"".$search_text."%\"\n"
                .") AND\n"
             :
               ($search_name=='' ? "" : "  `p`.`name` LIKE \"%".$search_name."%\" AND\n")
            )
            .($search_categories!="" ?
                "  `category` REGEXP \"".implode("|", explode(', ', $search_categories))."\" AND\n"
              :
                ""
             )
            ."  (`p`.`systemID`=".SYS_ID." OR `p`.`permPUBLIC` = 1)\n"
            .($search_keywordIDs!="" ? "GROUP BY `p`.`ID`\n" : "")
            ."ORDER BY ".$order;
  //    z($sql);
        $records = $this->get_records_for_sql($sql);
        if ($records) {
            foreach ($records as $row) {
                if ($row['systemID']==SYS_ID) {
                    $visible = $userIsAdmin || $this->is_available($row);
        //          $visible = $this->is_visible($row);
                } else {
                    $visible = $row['permPUBLIC'];
                }
                if ($visible) {
                    switch($row['type']) {
                        case 'article':
                            if (
                                $out['article']['count']>=$search_offset &&
                                ($limit==0 || count($out['article']['results'])<$limit)
                            ) {
                                $out['article']['results'][] = $row;
                            }
                            $out['article']['count']++;
                            break;
                        case 'event':
                            if (
                                $out['event']['count']>=$search_offset &&
                                ($limit==0 || count($out['event']['results'])<$limit)
                            ) {
                                $out['event']['results'][] = $row;
                            }
                            $out['event']['count']++;
                            break;
                        case 'gallery-image':
                            if (
                                $out['gallery-image']['count']>=$search_offset &&
                                ($limit==0 || count($out['gallery-image']['results'])<$limit)
                            ) {
                                $out['gallery-image']['results'][] = $row;
                            }
                            $out['gallery-image']['count']++;
                            break;
                        case 'job-posting':
                            if (
                                $out['job-posting']['count']>=$search_offset &&
                                ($limit==0 || count($out['job-posting']['results'])<$limit)
                            ) {
                                $out['job-posting']['results'][] = $row;
                            }
                            $out['job-posting']['count']++;
                            break;
                        case 'news-item':
                            if (
                                $out['news-item']['count']>=$search_offset &&
                                ($limit==0 || count($out['news-item']['results'])<$limit)
                            ) {
                                $out['news-item']['results'][] = $row;
                            }
                            $out['news-item']['count']++;
                            break;
                        case 'podcast':
                            if (
                                $out['podcast']['count']>=$search_offset &&
                                ($limit==0 || count($out['podcast']['results'])<$limit)
                            ) {
                                $out['podcast']['results'][] = $row;
                            }
                            $out['podcast']['count']++;
                            break;
                    }
                }
            }
        }
        return $out;
    }

    public function get_unique_name($name, $container_path = '', $systemID = SYS_ID)
    {
        $name=str_replace(array(' - ',' ','/'), array('-'), $name);
        $sql =
             "SELECT\n"
            ."  `name`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `systemID` = ".$systemID." AND\n"
            .($container_path ? "  `container_path`=\"".$container_path."\" AND\n" : "")
            ."  (`name` = \"".$name."\" OR `name` REGEXP \"^".$name."-[0-9]+$\")\n"
            ."ORDER BY\n"
            ."  `name`";
        $records = $this->get_records_for_sql($sql);
        if (!count($records)) {
            return $name;
        }
        $max = 1;
        foreach ($records as $record) {
            $_name = $record['name'];
            $name_arr = explode('-', $_name);
            if (count($name_arr)>1 && is_numeric($name_arr[count($name_arr)-1])) {
                $idx = (int)$name_arr[count($name_arr)-1];
                if ($idx>$max) {
                    $max = $idx;
                }
            }
        }
  //    y($max);y($records);
        return $name."-".(1+$max);
    }

    public function handle_report_copy(&$newID, &$msg, &$msg_tooltip, $name)
    {
        return parent::try_copy($newID, $msg, $msg_tooltip);
    }

    protected function handle_video()
    {
        $this->load();
        if ($this->record['video']=='') {
            return;
        }
        $url_arr = explode('/', $this->record['video']);
        $ident = false;
        if ($url_arr[2]=='youtu.be') {
            $ident =        (isset($url_arr[3]) ? $url_arr[3] : false);
        }

        if ($url_arr[2]=='www.youtube.com' || $url_arr[2]=='youtube.com') {
            $query_arr =    preg_split('/\?|&/', $url_arr[3]);
            $ident =        '';
            if (in_array('watch', $query_arr)) {
                foreach ($query_arr as $q) {
                    if (substr($q, 0, 2)=='v=') {
                        $ident = substr($q, 2);
                        break;
                    }
                }
            }
        }
        if (!$ident) {
            return;
        }
        $video =  "http://www.youtube.com/embed/".$ident;
        $data =   array();
        if ($this->record['thumbnail_small']=='') {
            $path =   "/UserFiles/Video/";
            $img =    "http://img.youtube.com/vi/".$ident."/0.jpg";
            $contents = file_get_contents($img);
            $file =   '.'.$path.'youtube_'.$ident.'.jpg';
            if (file_exists($file)) {
                unlink($file);
            }
            FileSystem::write_file($file, $contents);
            $data['thumbnail_small'] = $path.'youtube_'.$ident.'.jpg';
        }
        $data['video'] = $video;
        $this->update($data, false);
  //    y($query_arr);y($video);y($img);
    }

    protected function is_available($record)
    {
        return (
            $this->is_enabled($record) &&
            $this->is_in_active_date_range($record) &&
            $this->is_visible($record)
        );
    }

    public static function is_enabled($record)
    {
        return $record['enabled'];
    }

    public static function is_in_active_date_range($record)
    {
        $now = date('Y-m-d', time());
        return (
            ( $record['date']=='0000-00-00' || $now >= $record['date']) &&
            ( $record['date_end']=='0000-00-00' ||   $now < $record['date_end']) ? 1 : 0
        );
    }

    public function manage_product_relationships()
    {
        $report = 'product-relationships-for-'.$this->_get_type();
        if (get_var('command')=='report') {
            return draw_auto_report($report, 1);
        }
        return
             "<h3 style='margin:0.25em'>Product Relationships for ".$this->_get_object_name()."</h3>"
            .(get_var('selectID') ?
                draw_auto_report($report, 1)
              :
                 "<p style='margin:0.25em'>No Product Relationships -"
                ." this ".$this->_get_object_name()." has not been saved yet.</p>"
             );
    }

    public function manage_products()
    {
        $params = $this->get_edit_params();
        if (get_var('command')=='report') {
            return draw_auto_report($params['report_related_products'], 1);
        }
        return
             "<h3 style='margin:0.25em'>Products associated with ".$this->_get_object_name()."</h3>"
            .(get_var('selectID') ?
                draw_auto_report($params['report_related_products'], 1)
              :
                 "<p style='margin:0.25em'>No associated products -"
                ." this ".$this->_get_object_name()." has not been saved yet.</p>"
             );
    }

    public function on_action_set_path()
    {
        global $action_parameters;
        $type =     $action_parameters['triggerObject'];
        $ID =       $action_parameters['triggerID'];
        $ID_arr =   explode(',', $ID);
        foreach ($ID_arr as $ID) {
            $Obj =      new $type($ID);
            $Obj->set_path();
        }
    }

    public function on_action_handle_video()
    {
        global $action_parameters;
        $type =     $action_parameters['triggerObject'];
        $ID =       $action_parameters['triggerID'];
        $ID_arr =   explode(',', $ID);
        foreach ($ID_arr as $ID) {
            $Obj =      new $type($ID);
            $Obj->handle_video();
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
          // Don't use $this->load() as Posting::get_record() is type specific!
            $r = parent::get_coords($this->record['map_location']);
            if (
                $r['code']==='OVER_DAILY_LIMIT' ||
                $r['code']==='OVER_QUERY_LIMIT' ||
                $r['code']==='Connection Error'
            ) {
                return;
            }
            $data = array(
                'map_geocodeID' =>          $r['ID'],
                'map_geocode_area' =>       $r['match_area'],
                'map_geocode_type' =>       $r['match_type'],
                'map_geocode_quality' =>    $r['match_quality'],
                'map_lat' =>                $r['lat'],
                'map_lon' =>                $r['lon'],
                'process_maps' =>          0
            );
            $this->update($data, false, $reveal_modification);
        }
    }

    public function set_path($reveal_modification = false)
    {
        $this->load();
        $this->record['path'] =   "";
        // reset this so we can calculate from scratch
        if ($this->record['systemID']!=SYS_ID) {
            $ObjSystem = new System($this->record['systemID']);
            $this->record['posting_prefix'] = $ObjSystem->get_field('posting_prefix');
            $this->record['systemID'] = SYS_ID;
            // This is a total con-trick to prevent system URL from appearing in path
        }
        $prefix = "/";
        if ($this->record['memberID']) {
            $Obj_Posting = new Posting;
            $prefix = $Obj_Posting->get_community_member_path($this->record['memberID']);
        }
        if ($this->record['communityID'] && !$this->record['memberID']) {
            $Obj_Posting = new Posting;
            $prefix = $Obj_Posting->get_community_path($this->record['communityID']);
        }
        $path = $prefix.$this->get_URL($this->record);
        $this->set_field('path', $path, true, $reveal_modification);
    }

    protected function test_publish_date($record = false)
    {
        if (!$record) {
            $record = $this->record;
        }
        if ($record['date']!='0000-00-00') {
            sscanf($record['date'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
            sscanf($record['time_start'], "%02d:%02d", $_hh, $_mm);
            if (mktime($_hh, $_mm, 0, $_MM, $_DD, $_YYYY)>time()) {
                return "pending";
            }
        }
        if ($record['date_end']!='0000-00-00') {
            sscanf($record['date_end'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
            if (mktime(0, 0, 0, $_MM, $_DD, $_YYYY)<time()) {
                return "expired";
            }
        }
        return "good";
    }

    public function get_version()
    {
        return VERSION_POSTING;
    }
}
