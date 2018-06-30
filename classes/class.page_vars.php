<?php
define('VERSION_PAGE_VARS', '1.0.25');
/*
Version History:
  1.0.24 (2015-01-02)
    1) Added provision to ban msnbot and bingbot if load is too high
    2) Now Page_Vars::_get_vars_for_mode() sets ID for details, export, print_form and report
    3) Now PSR-2 Compliant

  (Older version history in class.page_vars.txt)
*/
class Page_Vars extends Page
{
    private $_name;
    protected $_ObjKnownType =    false;
    protected $_resolved =        false;
    private $_vars;

    public function get_server_load()
    {
        $serverload = array();
        // DIRECTORY_SEPARATOR checks if running windows
        if (DIRECTORY_SEPARATOR != '\\') {
            if (function_exists("sys_getloadavg")) {
                // sys_getloadavg() will return an array with [0] being load within the last minute.
                $serverload = sys_getloadavg();
                $serverload[0] = round($serverload[0], 4);
            } elseif (@file_exists("/proc/loadavg") && $load = @file_get_contents("/proc/loadavg")) {
                $serverload = explode(" ", $load);
                $serverload[0] = round($serverload[0], 4);
            }
            if (!is_numeric($serverload[0])) {
                if (@ini_get('safe_mode') == 'On') {
                    return "Unknown";
                }

                // Suhosin likes to throw a warning if exec is disabled then die - weird
                if ($func_blacklist = @ini_get('suhosin.executor.func.blacklist')) {
                    if (strpos(",".$func_blacklist.",", 'exec') !== false) {
                        return "Unknown";
                    }
                }
                // PHP disabled functions?
                if ($func_blacklist = @ini_get('disable_functions')) {
                    if (strpos(",".$func_blacklist.",", 'exec') !== false) {
                        return "Unknown";
                    }
                }

                $load = @exec("uptime");
                $load = explode("load average: ", $load);
                $serverload = explode(",", $load[1]);
                if (!is_array($serverload)) {
                    return "Unknown";
                }
            }
        } else {
            return "Unknown";
        }

        $returnload = trim($serverload[0]);

        return $returnload;
    }

    public function get()
    {
        global $page;
        $cpus =     4;
        $max_load = 0.4;
        if (
            stripos($_SERVER["HTTP_USER_AGENT"], 'msnbot')  !== false ||
            stripos($_SERVER["HTTP_USER_AGENT"], 'bingbot') !== false
        ) {
            $load = sys_getloadavg();
            if ($load[0] > ($cpus * $max_load)) {
                header('HTTP/1.1 503 Service unavailable - server load too high');
                die('Load is too high');
            }
        }
        $this->_name = $page;
        $this->_get_vars_for_page();
        $this->_get_vars_for_page_substitution();    // e.g. non-existent 'forgotten_password'
        $this->_get_vars_for_type();
        $this->_get_vars_for_mode();
        $this->_load_user_rights();
        if (!$this->_current_user_rights['canEdit']) {
            if (isset($this->_vars['password']) && trim($this->_vars['password'])) {
                Password::do_commands(get_var('challenge_password'), $this->_vars['password']);
                if (!Password::check_csvlist_against_previous($this->_vars['password'])) {
                    $out = Password::get_password_challenge_code(
                        $this->_vars['title'],
                        $this->_vars['object_type'],
                        $this->_vars['path']
                    );
                    $this->_vars['content'] = $out['html'];
                    Page::push_content('javascript', $out['javascript']);
                    Page::push_content('javascript_onload_bottom', $out['javascript_onload_bottom']);
                } else {
                    History::track();
                }
            } else {
                if (!isset($this->_vars['path_extender']) || !$this->_vars['path_extender']) {
                  // Otherwise let the component take care of this
                    History::track();
                }
            }
        }
  //    y($_SESSION['history']);
        $this->_execute_component_pre();
        $this->_recalculate_path_if_extended();
        $this->_swap_theme_if_default();
        $this->_get_theme();
        $this->_swap_layout_for_theme();
        $this->_swap_layout_if_default();
        $this->_swap_layout_if_report_or_print();
        $this->_swap_layout_if_other_language();
        $this->_get_layout();
        $this->_get_colours();
        $this->_get_navsuiteIDs();
        $this->_get_language();
        $this->_get_content_zones();
        return $this->_vars;
    }

    protected function _execute_component_pre()
    {
        if ($this->_vars['componentID_pre'] && $this->_vars['componentID_pre']!=1) {
            $Obj_Component = new Component;
            $Obj_Component->_set_ID($this->_vars['componentID_pre']);
            $php = $Obj_Component->get_field('php');
            $out = $this->_vars;  // Allows component to interact with page_vars
            eval($php);
            $this->_vars = $out;  // Applies result of manipulation of page_vars
        }
    }

    protected function _get_colours()
    {
        global $system_vars;
        $this->_vars['colours'] = array();
        $this->_vars['colours']['colour1'] = (isset($system_vars['colour1']) ? $system_vars['colour1'] : '');
        $this->_vars['colours']['colour2'] = (isset($system_vars['colour2']) ? $system_vars['colour2'] : '');
        $this->_vars['colours']['colour3'] = (isset($system_vars['colour3']) ? $system_vars['colour3'] : '');
        $this->_vars['colours']['colour4'] = (isset($system_vars['colour4']) ? $system_vars['colour4'] : '');
        if ($this->_vars['layout']['colour1']!='') {
            $this->_vars['colours']['colour1'] = $this->_vars['layout']['colour1'];
        }
        if ($this->_vars['layout']['colour2']!='') {
            $this->_vars['colours']['colour2'] = $this->_vars['layout']['colour2'];
        }
        if ($this->_vars['layout']['colour3']!='') {
            $this->_vars['colours']['colour3'] = $this->_vars['layout']['colour3'];
        }
        if ($this->_vars['layout']['colour4']!='') {
            $this->_vars['colours']['colour4'] = $this->_vars['layout']['colour4'];
        }
    }

    protected function _get_content_zones()
    {
        $this->_vars['content_zones'] = explode("<!--zonebreak-->", $this->_vars['content_current_language']);
    }

    protected function _get_default_vars()
    {
        global $system_vars;
        $path = $_SERVER["REQUEST_URI"];
        $this->_vars = array(
            'ID' =>                   false,
            'component_parameters' => '',
            'componentID_post' =>     '1',
            'componentID_pre' =>      '1',
            'content' =>              '',
            'group_assign_csv' =>     '',
            'layoutID' =>             $system_vars['defaultLayoutID'],
            'locked' =>               '',
            'meta_description' =>     '',
            'meta_keywords' =>        '',
            'page' =>                 $this->_name,
            'path' =>                 $path,
            'path_extension' =>       '',
            'path_real' =>            $path,
            'permPUBLIC' =>           true,
            'permSYSLOGON' =>         true,
            'permSYSMEMBER' =>        true,
            'style' =>                '',
            'themeID' =>              $system_vars['defaultThemeID']
        );
    }

    protected function _get_language()
    {
        $this->_vars['content_current_language'] = Language::convert_tags($this->_vars['content']);
    }

    protected function _get_layout()
    {
        $layoutID =     $this->_vars['layoutID'];
        $Obj_layout =   new Layout($layoutID);
        $this->_vars['layout'] =                        $Obj_layout->get_record();
        $this->_vars['layout_component_parameters'] =   $this->_vars['layout']['component_parameters'];
    }

    protected function _get_navsuiteIDs()
    {
        $this->_vars['navsuite1ID'] =
        (isset($this->_vars['navsuite1ID']) && $this->_vars['navsuite1ID']!=0 ?
         $this->_vars['navsuite1ID']
         :
         (isset($this->_vars['theme']['navsuite1ID']) && $this->_vars['theme']['navsuite1ID']!=0 ?
            $this->_vars['theme']['navsuite1ID']
          :
            $this->_vars['layout']['navsuite1ID']
         )
        );
        $this->_vars['navsuite2ID'] =
        (isset($this->_vars['navsuite2ID']) && $this->_vars['navsuite2ID']!=0 ?
         $this->_vars['navsuite2ID']
        :
         (isset($this->_vars['theme']['navsuite2ID']) && $this->_vars['theme']['navsuite2ID']!=0 ?
            $this->_vars['theme']['navsuite2ID']
          :
            $this->_vars['layout']['navsuite2ID']
         )
        );
        $this->_vars['navsuite3ID'] =
        (isset($this->_vars['navsuite3ID']) && $this->_vars['navsuite3ID']!=0 ?
         $this->_vars['navsuite3ID']
        :
         (isset($this->_vars['theme']['navsuite3ID']) && $this->_vars['theme']['navsuite3ID']!=0 ?
            $this->_vars['theme']['navsuite3ID']
          :
            $this->_vars['layout']['navsuite3ID']
         )
        );
  //    y(array_keys($this->_vars)); y($this->_vars['navsuite1ID']);
    }

    protected function _get_object_for_type()
    {
        global $ID, $mode;
        $this->_ObjKnownType = false;
        $prefixed_types = Portal::portal_param_get('path_type_prefixed_types');
        foreach ($prefixed_types as $objName) {
            $_obj = new $objName($ID);
            if ($_obj->_get_path_prefix()==$mode) {
                $this->_ObjKnownType = $_obj;
                $this->_resolved = true;
                break;
            }
        }
    }

    protected function _get_theme()
    {
        $Obj_Theme =            new Theme($this->_vars['themeID']);
        $this->_vars['theme'] = $Obj_Theme->get_record();
    }

    protected function _get_vars_for_mode()
    {
        global $mode, $ID, $report_name, $system_vars, $targetID;
        if ($this->_resolved) {
            return;
        }
        switch ($mode){
            case "email-view":
                return; //  Save time - we don't need page_vars for these
            break;
            case "rss":
                return; //  Save time - we don't need page_vars for these
            break;
            case "details":
            case "export":
            case "print_form":
            case "report":
                $this->_vars['ID'] =            $ID;
                $this->_vars['object_name'] =   'Report';
                $this->_vars['object_type'] =   'Report';
                $this->_vars['themeID'] =       $system_vars['defaultThemeID'];
                $this->_vars['path'] =          "//".$mode."/".$report_name."/".$ID;
                $Obj = new Report;
                if ($report_name && $report_row = $Obj->get_titles_for_name($report_name)) {
                    $this->_vars['title'] = ($mode=='report' ?
                        $report_row['reportTitle']
                     :
                        $report_row['formTitle']
                    );
                } else {
                    $this->_vars['title'] =     "404 - Report ".$report_name." not found";
                    $this->_vars['status'] =    '404';
                }
                break;
            default:
              // If page path contained a '.' we have an unmatched file resource request -
              // just exit quickly with http error
                if (strpos($this->_name, '.')) {
                    $this->do_tracking("404");
                    header("Status: 404 Not Found", true, 404);
                    print "<h1>404</h1><p>The resource ".$this->_name." wasn't found here.</p>";
                    die;
                }
                $this->_vars['absolute_URL'] =  "/".$this->_name;
                $this->_vars['assign_type'] =   "page";
                $this->_vars['object_name'] =   "Page";
                $this->_vars['object_type'] =   "Page";
                $this->_vars['status'] =        '404';
                $this->_vars['type'] =          'page';
                $this->_vars['systemID'] =      SYS_ID;
                $this->_vars['path'] =          "//".$this->_name."/";
                $this->_vars['title'] =         "404 - Page ".$this->_name." not found";
                $this->_vars['themeID'] =       $system_vars['defaultThemeID'];
                break;
        }
    }

    protected function _get_vars_for_page()
    {
        global $system_vars;
        if (!$this->_vars = $this->get_page_by_path($this->_name)) {
            $this->_get_default_vars();
            return;
        }
        $this->_vars['relative_URL'] = (trim($this->_vars['path'], '/')=='home' ? '' : trim($this->_vars['path'], '/'));
        $this->_vars['absolute_URL'] =
         trim($system_vars['URL'], '/')
        .'/'
        .$this->_vars['relative_URL'];
        $this->_vars['assign_type'] =   $this->_get_assign_type();
        $this->_vars['isPUBLIC'] =      $this->_vars['permPUBLIC'];
        $this->_vars['object_name'] =   'Page';
        $this->_vars['object_type'] =   'Page';
        $this->_resolved = true;
    }

    protected function _get_vars_for_page_substitution()
    {
        global $system_vars;
        if ($this->_resolved) {
            return; // we got it already
        }
  //    return;
        $path_arr = explode('/', $this->_name);
        switch($path_arr[0]){
            case 'checkout':
            case 'forgotten_password':
            case 'manage_profile':
            case 'password':
            case 'paypal_cancel':
            case 'paypal_return':
            case 'sitemap':
            case 'your_order_history':
            case 'your_registered_events':
                $this->_vars['absolute_URL'] =  $this->_name;
                $this->_vars['object_name'] =   "Page";
                $this->_vars['status'] =        '404';
                $this->_vars['type'] =          'page';
                $this->_vars['systemID'] =      SYS_ID;
                $this->_vars['path'] =          "//".$this->_name;
                $this->_vars['title'] =         title_case_string(str_replace('_', ' ', $this->_name));
                $this->_vars['themeID'] =       $system_vars['defaultThemeID'];
                $this->_resolved = true;
                break;
            case 'email-unsubscribe':
            case 'email-opt-in':
            case 'email-opt-out':
                $this->_vars['absolute_URL'] =  $this->_name;
                $this->_vars['object_name'] =   "Page";
                $this->_vars['path_extension'] = (isset($path_arr[1]) ? $path_arr[1] : "");
                $this->_vars['path_real'] =     $path_arr[0];
                $this->_vars['status'] =        '404';
                $this->_vars['type'] =          'page';
                $this->_vars['systemID'] =      SYS_ID;
                $this->_vars['path'] =          "//".$path_arr[0].'/';
                $this->_vars['title'] =         title_case_string(str_replace('_', ' ', $this->_name));
                $this->_vars['themeID'] =       $system_vars['defaultThemeID'];
                $this->_resolved = true;
                break;
        }

    }

    protected function _get_vars_for_type()
    {
        global $system_vars, $mode, $ID;
        if ($this->_resolved) {
            return; // we got it already
        }
        $this->_get_object_for_type();
        if (!$this->_ObjKnownType) {
            return;
        }
        $record =   $this->_ObjKnownType->get_record();
        if (!$record && is_a($this->_ObjKnownType, 'Posting_Contained')) {
            $this->_redirect_if_valid_container_path($ID);
        }
        $this->_vars['object_type'] =   get_class($this->_ObjKnownType);
        $this->_vars['object_name'] =   $this->_ObjKnownType->_get_object_name();
        $this->_vars['type'] =          $mode;
        if ($record) {
            $this->_vars['ID'] =            $record['ID'];
            $this->_vars['absolute_URL'] =  trim($system_vars['URL'], '/').$this->_ObjKnownType->get_URL($record);
            $this->_vars['assign_type'] =   $this->_ObjKnownType->_get_assign_type();
            $this->_vars['comments_allow'] =(isset($record['comments_allow']) ? $record['comments_allow'] : 0);
            $this->_vars['component_parameters'] =  (isset($record['component_parameters']) ?
                $record['component_parameters']
            :
                ""
            );
            $this->_vars['content_text'] =  (isset($record['content_text']) ? $record['content_text'] : "");
            $this->_vars['date'] =          (isset($record['date']) ? $record['date'] : '');
            $this->_vars['isPUBLIC'] =      $record['permPUBLIC'];
            $this->_vars['layoutID'] =      (isset($record['layoutID']) ? $record['layoutID'] : 1);
            $this->_vars['meta_description'] = (isset($record['meta_description']) ? $record['meta_description'] : '');
            $this->_vars['meta_keywords'] = (isset($record['meta_keywords']) ? $record['meta_keywords'] : '');
            $this->_vars['name'] =          (isset($record['name']) ? $record['name'] : $record['itemCode']);
            $this->_vars['navsuite1ID'] =   (isset($record['navsuite1ID']) ? $record['navsuite1ID'] : 0);
            $this->_vars['navsuite2ID'] =   (isset($record['navsuite2ID']) ? $record['navsuite2ID'] : 0);
            $this->_vars['navsuite3ID'] =   (isset($record['navsuite3ID']) ? $record['navsuite3ID'] : 0);
            $this->_vars['path'] =          (isset($record['path']) ?
                $record['path']
             :
                $record['type']."/".$record['ID']
            );
            $this->_vars['ratings_allow'] = (isset($record['ratings_allow']) ? $record['ratings_allow'] : 0);
            $this->_vars['relative_URL'] =  $this->_ObjKnownType->get_URL($record);
            $this->_vars['systemID'] =      $record['systemID'];
            $this->_vars['themeID'] =       $record['themeID'];
            $this->_vars['title'] =
                 $this->_ObjKnownType->_get_object_name().": "
                .(isset($record['title']) ? $record['title'] : $record['titleEnglish']);
        } else {
            $this->_vars['absolute_URL'] =  trim($system_vars['URL'], '/').$this->_ObjKnownType->get_URL($record);
            $this->_vars['isPUBLIC'] =      false;
            $this->_vars['component_parameters'] =  "";
            $this->_vars['name'] =          '';
            $this->_vars['path'] =          BASE_PATH.($mode ? $mode : '');
            $this->_vars['relative_URL'] =  $this->_ObjKnownType->get_URL();
            $this->_vars['systemID'] =      SYS_ID;
            $this->_vars['themeID'] =       $system_vars['defaultThemeID'];
            $this->_vars['title'] =         $this->_ObjKnownType->_get_object_name()." is unavailable";
        }
    }

    protected function _load_user_rights()
    {
        $isMASTERADMIN =    get_person_permission("MASTERADMIN", $this->_vars['group_assign_csv']);
        $isSYSADMIN =        get_person_permission("SYSADMIN", $this->_vars['group_assign_csv']);
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER", $this->_vars['group_assign_csv']);
        $isSYSEDITOR =      get_person_permission("SYSEDITOR", $this->_vars['group_assign_csv']);
        $canEdit =
        ($this->_vars['layoutID']!=2 && (
         $isMASTERADMIN ||
         ($this->_vars['locked']==0 && ($isSYSADMIN || $isSYSAPPROVER || $isSYSEDITOR)))
        );
        $this->_current_user_rights['canEdit'] = $canEdit;

    }

    protected function _recalculate_path_if_extended()
    {
        if (isset($this->_vars['path_extension']) && $this->_vars['componentID_pre']==1) {
            $this->_vars['path_real'] =   $this->_vars['path'];
            $this->_vars['path'].=        $this->_vars['path_extension'];
        }
    }

    protected function _redirect_if_valid_container_path($ID)
    {
        $type = $this->_ObjKnownType->_get_container_object_type();
        $Obj_Container = new $type($ID);
        $record = $Obj_Container->get_record();
        if ($record) {
            $url =
                 BASE_PATH
                .$Obj_Container->_get_type()
                .'/'
                .(isset($record['path']) ? trim($record['path'], '/') : $record['ID']);
            header('Location: '.$url, 302);
            die;
        }
    }

    protected function _swap_layout_for_theme()
    {
        if ($this->_vars['layoutID']=='1') {
            $this->_vars['layoutID'] = $this->_vars['theme']['layoutID'];
        }
    }

    protected function _swap_layout_if_default()
    {
        global $system_vars;
        if ($this->_vars['layoutID']=='0' || $this->_vars['layoutID']=='1') {
            $this->_vars['layoutID'] =    $system_vars['defaultLayoutID'];
        }
    }

    protected function _swap_layout_if_other_language()
    {
        global $system_vars;
        $supported = explode(', ', $system_vars['languages']);
        if (!System::has_feature('multi-language')) {
            return;
        }
        if (!isset($_SESSION['lang'])) {
            return;
        }
        if ($_SESSION['lang']==$system_vars['defaultLanguage']) {
            return;
        }
        if (!in_array($_SESSION['lang'], $supported)) {
            return;
        }
        $Obj_Layout = new Layout($this->_vars['layoutID']);
        if ($_SESSION['lang']==$Obj_Layout->get_field('language')) {
            return;
        }
        $options = $Obj_Layout->get_language_options();
        foreach ($options as $option) {
            if ($_SESSION['lang']==$option['language']) {
                $this->_vars['layoutID'] =    $option['ID'];
            }
        }
    }

    protected function _swap_layout_if_report_or_print()
    {
        global $mode, $print;
        if ($mode!='print_form' && $mode!='report' && $print!=1 && $print!=2) {
            return;
        }
        switch ($print){
            case "1":
                $layout_name = "_print";
                break;
            case "2":
                $layout_name = "_popup";
                break;
            default:
                switch ($mode){
                    case 'print_form':
                        $layout_name = "_print";
                        break;
                    case 'report':
                        $layout_name = "_report";
                        break;
                }
                break;
        }
        $Obj_layout = new Layout;
        $layoutID =   $Obj_layout->get_ID_by_name($layout_name, SYS_ID.',1');
        if ($layoutID) {
            $Obj_layout =       new Layout($layoutID);
            $this->_vars['layoutID'] =  $layoutID;
            $this->_vars['style'] =     $Obj_layout->get_field('style');
        } else {
            do_log(3, __CLASS__."::".__FUNCTION__."():", '', "System standard layout ".$layout_name." is missing");
        }
    }

    protected function _swap_theme_if_default()
    {
        global $system_vars;
        if ($this->_vars['themeID']=='0' || $this->_vars['themeID']=='1') {
            $this->_vars['themeID'] =    $system_vars['defaultThemeID'];
        }
    }

    public function get_version()
    {
        return VERSION_PAGE_VARS;
    }
}
