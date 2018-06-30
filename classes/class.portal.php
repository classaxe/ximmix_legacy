<?php
define('VERSION_PORTAL', '1.0.33');
/*
Version History:
  1.0.33 (2015-01-04)
    1) Portal::_parse_request_mode_prefix() for export now sets targetID and show_fields if
       path includes these as slashed path parameters
    2) Now PSR-2 Compliant

  (Older version history in class.portal.txt)
*/
class Portal extends Base
{
    private static $_path_date_prefixed_types =
    array(
      'Article', 'Event', 'Job_Posting', 'News_Item', 'Podcast', 'Survey'
    );
    // Objects viewable in single-item mode by entering custom path prefix, e.g. '/2009/06/29/posting-name'
    // To append, add this to custom.php:
    //   Portal::portal_param_push('path_date_prefixed_types','Team');

    private static $_path_type_prefixed_types = array(
      'Article',
      'Event',
      'Gallery_Album',
      'Gallery_Image',
      'Job_Posting',
      'News_Item',
      'Page',
      'Podcast',
      'Podcast_Album',
      'Product',
      'Survey'
    );
    // Objects viewable in single-item mode by entering type path prefix, e.g. '/event/123456'
    // To append, add this to custom.php:
    //   Portal::portal_param_push('path_type_prefixed_types','Team');


    protected static function get_request_path($request = false)
    {
        if ($request===false) {
            $request = $_SERVER["REQUEST_URI"];
        }
        $request =      urldecode($request);
        if (strpos($request, '"')!==false) {
            return "";
        }
        if (strpos($request, '\\')!==false) {
            return "";
        }
        $request =      explode("?", $request);
        $request =      trim($request[0], "/");
        $request =      substr($request, strlen(BASE_PATH)-1);
        if ($request != strip_tags($request)) {
            return "";
        }
        return $request;
    }

    public static function parse_request()
    {
        global $goto, $ID, $mode, $submode, $page, $report_name, $targetID;
        global $search_categories, $search_date_start, $search_date_end, $search_keywords;
        global $search_offset, $search_sites, $search_type, $show_fields;
        if (!isset($_SERVER["REQUEST_URI"])) {
            return false;   // for jobs running in shell that include codebase
        }
        if ($mode != '') {
            return false;   // Used for when we went to a page that didn't exist, then created it
        }
        if ($goto != '') {
            $page =   $goto;
            $goto =   "";
            $mode =   "";
            return true;
        }
        $request = Portal::get_request_path();
        Portal::_parse_request_special($request);
        if (Portal::_parse_request_type_prefix($request, $mode, $ID, $page, $search_type)) {
            return true;
        }
        if (Portal::_parse_request_mode_prefix(
            $request,
            $mode,
            $submode,
            $ID,
            $page,
            $report_name,
            $targetID,
            $search_categories,
            $search_keywords,
            $search_offset,
            $search_type,
            $show_fields
        )) {
            return true;
        }
        if (Portal::_parse_request_product($request, $mode, $ID)) {
            return true;
        }
        if (Portal::_parse_request_posting($request, $mode, $ID)) {
            return true;
        }
        if (Portal::_parse_request_search_range($request, $page, $search_date_start, $search_date_end, $search_type)) {
            return true;
        }
        if (Portal::_parse_request_page($page, $mode)) {
            return true;
        }
        return false;
    }

    protected static function _parse_request_mode_prefix(
        $request,
        &$mode,
        &$submode,
        &$ID,
        &$page,
        &$report_name,
        &$targetID,
        &$search_categories,
        &$search_keywords,
        &$search_offset,
        &$search_type,
        &$show_fields
    ) {
        $request_arr =  explode("/", $request);
        $path_prefix =  strToLower($request_arr[0]);
        $path_val_1 =   (isset($request_arr[1]) ? $request_arr[1] : false);
        $path_val_2 =   (isset($request_arr[2]) ? $request_arr[2] : false);
        $path_val_3 =   (isset($request_arr[3]) ? $request_arr[3] : false);
        $path_val_4 =   (isset($request_arr[4]) ? $request_arr[4] : false);
        switch ($path_prefix) {
            case "_popup_layer":
                System::draw_popup_layer();
                return true;
            break;
            case "category":
                if ($path_val_1) {
                    $page="search_results";
                    $search_categories = $path_val_1;
                    $search_offset=0;
                    if ($search_type=='') {
                        $search_type='*';
                    }
                } else {
                    $page="categories";
                }
                return true;
            break;
            case "details":
                $mode = $path_prefix;
                if ($path_val_1) {
                    $report_name = $path_val_1;
                }
                if ($path_val_2) {
                    $ID = $path_val_2;
                }
                return true;
            break;
            case "email-view":
                $mode = $path_prefix;
                if ($path_val_1) {
                    $ID = $path_val_1;
                }
                return true;
            break;
            case "export":
                $mode = $path_prefix;
                if ($path_val_1) {
                    $submode = $path_val_1;
                    switch ($submode) {
                        case "icalendar":
                            if ($path_val_2) {
                                $targetID = $path_val_2;
                            }
                            break;
                        default:
                            if ($path_val_2) {
                                $report_name = $path_val_2;
                            }
                            if ($path_val_3) {
                                $targetID = $path_val_3;
                            }
                            if ($path_val_3) {
                                $show_fields = $path_val_4;
                            }

                            break;
                    }
                }
                return true;
            break;
    /*
    // More needs to be done to make this work convincingly.
          case "help":
          $mode = $request_arr[0];
          if (isset($request_arr[1])) {
            $page = $request_arr[1];
          }
          return;
          break;
    */
            case "keywords":
            case "tags":
                if ($path_val_1) {
                    $page="search_results";
                    $search_keywords = $path_val_1;
                    $search_offset=0;
                    $search_type='*';
                    $mode="";
                } else {
                    $page="tags";
                }
                return true;
            break;
            case "print_form":
                $mode = $path_prefix;
                if ($path_val_1) {
                    $report_name = $path_val_1;
                }
                if (isset($request_arr[2])) {
                    $ID = $request_arr[2];
                }
                return true;
            break;
            case "report":
                $mode = $path_prefix;
                if ($path_val_1) {
                    array_shift($request_arr);
                    $report_name = implode("/", $request_arr);
                }
                return true;
            break;
            case "rss":
                $mode = $path_prefix;
                return true;
            break;
        }
    }

    protected static function _parse_request_page(&$page, $mode)
    {
        if ($page=="" && $mode=="") {
            $page = Portal::get_request_path();
            if ($page=='') {
                $page="home";
            }
            return true;
        }
        return false;
    }

    protected static function _parse_request_posting($request, &$mode, &$ID)
    {
        if ($request=='') {
            return false;
        }
        return Posting::get_match_for_name($request, $mode, $ID);
    }

    protected static function _parse_request_product($request, &$mode, &$ID)
    {
        if ($request=='') {
            return false;
        }
        if (Product::get_match_for_name($request, $mode, $ID)) {
            return true;
        }
    }

    protected static function _parse_request_search_range(
        $request,
        &$page,
        &$search_date_start,
        &$search_date_end,
        &$search_type
    ) {
        if ($request=='') {
            return false;
        }
        $request_arr =  explode("/", $request);
        if (strlen($request_arr[0])!=4) {
            return false;
        }
        if (!is_numeric($request_arr[0])) {
            return false; // excludes non numbers
        }
        if ((string)$request_arr[0] !== (string)(int)$request_arr[0]) {
            return false; // excludes 20e2 which otherwise equates to 2000 triggering a search
        }
        $path_YYYY =    (int)(float)$request_arr[0];
        $path_MM =      (isset($request_arr[1]) ? (int)(float)$request_arr[1] : false);
        $path_DD =      (isset($request_arr[2]) ? (int)(float)$request_arr[2] : false);
        if (!sanitize('range', $path_YYYY, 1990, 2200, false)) {
            return false;
        }
        $search_date_start =      $path_YYYY;
        $search_date_end =        $path_YYYY;
        if (sanitize('range', $path_MM, 1, 12, false)) {
            $search_date_start.=    "-".lead_zero($path_MM, 2);
            $search_date_end.=      "-".lead_zero($path_MM, 2);
        }
        if (sanitize('range', $path_DD, 1, 31, false)) {
            $search_date_start.=    "-".lead_zero($path_DD, 2);
            $search_date_end.=      "-".lead_zero($path_DD, 2);
        }
      // Fix start date if it's too short:
        $search_date_start = substr($search_date_start."-01-01", 0, 10);
      // Fix end date if it's too short
        if (strlen($search_date_end)==4) {
            $search_date_end.="-12-31";
        } elseif (strlen($search_date_end)==7) {
            switch(substr($search_date_end, 5, 2)) {
              // Pay special attention to February:
                case "02":
                    $_yyyy = (int)substr($search_date_end, 0, 4);
                    $_leap = (($_yyyy%4==0) && ($_yyyy%100!=0)) || ($_yyyy%400==0);
                    $search_date_end .= ($_leap ? "-29" : "-28");
                    break;
                case "04":
                case "06":
                case "09":
                case "11":
                    $search_date_end .= "-30";
                    break;
                default:
                    $search_date_end .= "-31";
                    break;
            }
        }
        $page =           "search_results";
        $search_type =    '*';
        return true;
    }

    protected static function _parse_request_special($request)
    {
        $request_arr =  explode('/', $request);
        switch($request_arr[0]){
            case 'piwik':
                header("Status: 404 Not Found", true, 404);
                die('// Piwik not installed');
            break;
            case 'quickbooks.qwc':
                $Obj = new Quickbooks;
                $Obj->get_qwc_xml();
                die;
            break;
            case 'robots.txt':
                $Obj = new XML_Sitemap;
                $Obj->draw_robots_txt();
                die;
            break;
            case 'sitemap.xml':
                $Obj = new XML_Sitemap;
                $Obj->draw();
                die;
            break;
            case 'xhtml1-strict-with-iframe.dtd':
                $Obj = new DTD;
                $Obj->draw();
                die;
            break;
        }

    }

    protected static function _parse_request_type_prefix(
        $request,
        &$mode,
        &$ID,
        &$page,
        &$search_type
    ) {
        $request_arr =  explode("/", $request);
        $path_prefix =  strToLower(array_shift($request_arr));
        if ($path_prefix=='') {
            return false;
        }
        if ($path_prefix=='ajax') {
            return true;
        }
        $path =     implode('/', $request_arr);
        $posting_prefix_types = Portal::portal_param_get('path_type_prefixed_types');
        foreach ($posting_prefix_types as $_type) {
            $Obj = new $_type;
            if ($path_prefix==$Obj->_get_path_prefix()) {
                if ($path) {
                    $mode =   $path_prefix;
                    $ID = false;
                    if (is_a($Obj, 'Posting_Contained')) {
                        $ID = $Obj->get_ID_by_path('//'.$path, SYS_ID);
                    }
                    if (!$ID) {
                        $ID = $Obj->get_ID_by_name($path, SYS_ID, true);
                    };
                    if (!$ID) {
                        $ID =     $path;
                    }
                    return true;
                }
                $mode =             "";
                $page =             "search_results";
                $search_type =      $Obj->_get_search_type();
                return true;
            }
        }
        return false;
    }

    public static function portal_param_get($param)
    {
        switch ($param){
            case "path_date_prefixed_types":
                return Portal::$_path_date_prefixed_types;
            break;
            case "path_type_prefixed_types":
                return Portal::$_path_type_prefixed_types;
            break;
        }
        die(__CLASS__."::".__FUNCTION__."() - Parameter '".$param."' is not recognised.");
    }

    public static function portal_param_push($param, $value)
    {
        switch ($param){
            case "path_date_prefixed_types":
                array_push(Portal::$_path_date_prefixed_types, $value);
                return true;
            break;
            case "path_type_prefixed_types":
                array_push(Portal::$_path_type_prefixed_types, $value);
                return true;
            break;
        }
        die(__CLASS__."::".__FUNCTION__."() - Parameter '".$param."' is not recognised.");
    }

    public static function portal_upgrade()
    {
        $Obj_System =   new System(SYS_ID);
        $row =          $Obj_System->get_record();
        $version =      $row['db_version'];
        $complete = false;
        switch ($version) {
            case "2330":
                set_time_limit(600);    // Extend maximum execution time to 10 mins
                $Obj_GC =       new Geocode_cache();
                $records =      $Obj_GC->get_records();
                foreach ($records as $r) {
                    if ($r['match_type']!='') {
                        continue;
                    }
                    $output = unserialize($r['output_json']);
                    $match_type = $output['geometry']['location_type'];
                    switch($match_type){
                        case 'ROOFTOP':
                            $data = array(
                            'match_area' => 0,
                            'match_quality' => 100,
                            'match_type' => $match_type
                            );
                            break;
                        case 'RANGE_INTERPOLATED':
                        case 'GEOMETRIC_CENTER':
                        case 'APPROXIMATE':
                            if (isset($output['geometry']['bounds'])) {
                                $lat_1 = $output['geometry']['bounds']['northeast']['lat'];
                                $lon_1 = $output['geometry']['bounds']['northeast']['lng'];
                                $lat_2 = $output['geometry']['bounds']['southwest']['lat'];
                                $lon_2 = $output['geometry']['bounds']['southwest']['lng'];
                                $match_area = Google_Map::get_bounds_area($lat_1, $lon_1, $lat_2, $lon_2);
                                $quality = 100*((14.5 - log(1+$match_area, 10))/14.5);
                                $data = array(
                                'match_area' => $match_area,
                                'match_quality' => $quality,
                                'match_type' => $match_type
                                );
                            } else {
                                $data = array(
                                'match_area' => 0,
                                'match_quality' => 100,
                                'match_type' => $match_type
                                );
                            }
                            break;
                    }
                    $Obj_GC->_set_ID($r['ID']);
                    $Obj_GC->update($data, true, false);
                }
                $Obj_System = new System(SYS_ID);
                $Obj_System->set_field_for_all('db_upgrade_flag', 0);
                return true;
            break;
        }
        die(
         "<h1>Error - upgrade required</h1>\n"
        ."<p>The system must complete an important database upgrade to version ".$row['db_version'].".<br />\n"
        ."This site is running"
        ." <b>".System::get_item_version('system_family')." version ".System::get_item_version('codebase')."</b>"
        ." and a database upgrade to ".$row['db_version']." is not supported with this release.</p>\n"
        ."<p>Please contact <a href=\"mailto:".$row['adminEmail']."\"><b>".$row['adminName']."</b></a>"
        ." to report this issue if this message persists for more than a couple of minutes.</p>"
        );
    }

    public function get_version()
    {
        return VERSION_PORTAL;
    }
}
