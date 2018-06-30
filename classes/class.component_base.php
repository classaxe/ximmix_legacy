<?php
define("VERSION_COMPONENT_BASE", "1.0.18");
/*
Version History:
  1.0.18 (2015-01-02)
    1) Now uses OPTION_SEPARATOR constant not option_separator in various CP parsing methods
    2) Now PSR-2 Compliant

  (Older version history in class.component_base.txt)
*/

class Component_Base extends Record
{
    public static $help_div_id = 0;
    protected $_args;
    protected $_current_user_groups = array();
    protected $_current_user_groups_access_csv = '';
    protected $_current_user_rights = array();
    protected $_cp;
    protected $_cp_defaults;
    protected $_disable_params;
    protected $_instance;
    protected $_css =             '';
    protected $_html =            '';
    protected $_js =              '';
    protected $_msg =             '';
    protected $_Obj_Block_Layout = false;

    public function __construct($ID = "")
    {
        parent::__construct("component", $ID);
        $this->_set_object_name('Component');
        $this->set_edit_params(
            array(
            'report_rename' =>          true,
            'report_rename_label' =>    'new name'
            )
        );
    }

    protected function _draw_control_panel($extra_break = false)
    {
        $html =
        Component_Base::get_help(
            $this->_ident,
            $this->_instance,
            $this->_disable_params,
            $this->_parameter_spec,
            $this->_cp_defaults
        );
        if ($html && $extra_break) {
            $html.="<br />\n";
        }
        $this->_html.= $html;
    }

    protected function _draw_section_container_close()
    {
        $this->_html.= "</div>\n";
    }

    protected function _draw_section_container_open()
    {
        Page::push_content(
            'javascript_onload',
            "  show_section_tab('".$this->_safe_ID."','".$this->_selected_section."');\n"
        );
        $this->_html.= "<div id='".$this->_safe_ID."_container' style='position:relative;'>\n";
    }

    protected function _draw_status()
    {
        $this->_html.=      HTML::draw_status($this->_safe_ID, $this->_msg);
    }

    public static function get_help($ident, $instance, $force_values, $parameter_spec, $cp_defaults)
    {
        if (get_var('component_help')!=1 || $force_values) {
            return;
        }
        $_params_arr = array();
        foreach ($parameter_spec as $_param => $_spec) {
            $_params_arr[] =
             $ident.'.'.$_param
            .'='
            .'['.$_spec['hint'].']'
            .'{'.$cp_defaults[$_param].'}';
        }
        return
        Component_Base::help(
            $ident,
            implode('[;]', $_params_arr),
            $instance
        );
    }

    public function get_parameter($parameters, $key, $default = false)
    {
      // Deprecated - use get_parameter_for_instance() for independent addressing
        if ($parameters==="") {
            return $default;
        }
        $arr = explode(OPTION_SEPARATOR, $parameters);
        for ($i=0; $i<count($arr); $i++) {
            $row = trim($arr[$i]);
            if ($row==="?") {
                return $row;
            }
            $pair = explode("=", $row);
            if (strToUpper($key)===strToUpper($pair[0])) {
                array_shift($pair);
                $result = implode("=", $pair);
                if ($default && $result=='') {
                    return $default;
                }
                return $result;
            }
        }
        return $default;
    }

    public static function get_parameter_defaults_and_values(
        $ident,
        $instance,
        $force_values,
        $parameter_spec,
        $presets = array()
    ) {
        // Most current system, usually invoked via:
        // Component_Base::_setup_load_parameters()
        $result = array(
            'defaults' =>   array(),
            'parameters' => array()
        );
        foreach ($parameter_spec as $_param => $_spec) {
            $result['defaults'][$_param] =
            (isset($presets[$_param]) ? $presets[$_param] : $_spec['default']);
        }
        foreach ($parameter_spec as $_param => $_spec) {
            if ($force_values) {
                $result['parameters'][$_param] =
                 $result['defaults'][$_param];
            } else {
                $result['parameters'][$_param] =
                Component_Base::get_parameter_for_instance(
                    $instance,
                    Component_Base::get_parameters(),
                    $ident.".".$_param,
                    $result['defaults'][$_param]
                );
            }
        }
        foreach ($parameter_spec as $_param => $_spec) {
            if (isset($_spec['match'])) {
                $match_arr = explode('|', $_spec['match']);
                switch($match_arr[0]){
                    case "date-format":
                        $result['parameters'][$_param] =
                        sanitize(
                            $match_arr[0],
                            $result['parameters'][$_param],
                            $match_arr[1]
                        );
                        if ($result['parameters'][$_param]=='') {
                            $result['parameters'][$_param]=$result['defaults'][$_param];
                        }
                        break;
                    case "enum":
                        $val_arr = explode(',', $match_arr[1]);
                        $result['parameters'][$_param] =
                        sanitize(
                            $match_arr[0],
                            $result['parameters'][$_param],
                            $val_arr
                        );
                        break;
                    case "enum_csv":
                        $val_arr = explode(',', $match_arr[1]);
                        $result['parameters'][$_param] =
                        sanitize(
                            $match_arr[0],
                            $result['parameters'][$_param],
                            $val_arr
                        );
                        if ($result['parameters'][$_param]=='') {
                            $result['parameters'][$_param]=$result['defaults'][$_param];
                        }
                        break;
                    case "hex3":
                        $result['parameters'][$_param] =
                        sanitize(
                            $match_arr[0],
                            $result['parameters'][$_param],
                            $match_arr[1]
                        );
                        if ($result['parameters'][$_param]=='') {
                            $result['parameters'][$_param]=$result['defaults'][$_param];
                        }
                        break;
                    case "range":
                        $val_arr = explode(',', $match_arr[1]);
                        $result['parameters'][$_param] =
                        sanitize(
                            $match_arr[0],
                            $result['parameters'][$_param],
                            $val_arr[0],
                            $val_arr[1],
                            $result['defaults'][$_param]
                        );
                        break;
                }
            }
        }
        return $result;
    }

    public function get_parameter_for_instance($instance, $parameter_csv, $key, $default = false)
    {
        if ($parameter_csv==="") {
            return $default;
        }
        $parameters = explode(OPTION_SEPARATOR, $parameter_csv);
      // First check for exact match:
        foreach ($parameters as $parameter) {
            $pair = explode("=", $parameter);
            if (strToUpper($instance.":".$key)===strToUpper($pair[0])) {
                array_shift($pair);
                $result = implode("=", $pair);
                if ($default && $result=='') {
                    return $default;
                }
                return $result;
            }
        }
      // Now check for less specific matches:
        foreach ($parameters as $parameter) {
            $pair = explode("=", $parameter);
            if (strToUpper($key)===strToUpper($pair[0]) || strToUpper("*.".$key)===strToUpper($pair[0])) {
                array_shift($pair);
                $result = implode("=", $pair);
                if ($default && $result==='') {
                    return $default;
                }
                return $result;
            }
        }
        return $default;
    }


    public static function get_parameters($page = false, $systemID = SYS_ID)
    {
        global $system_vars,$page_vars;
        switch ($page) {
            case false:
                $_page_vars =   $page_vars;
                $_system_vars = $system_vars;
                break;
            default:
                $_page_vars =           array();
                $_system_vars =         array();
                $Obj_system =           new System($systemID);
                $record =               $Obj_system->get_record();
                $_system_vars['component_parameters'] = $record['component_parameters'];
                $defaultLayoutID =    $record['defaultLayoutID'];

                $Obj_page =             new Page();
                $record =               $Obj_page->get_record_by_name($page, $systemID);
                $_page_vars['component_parameters'] = $record['component_parameters'];
                $_layoutID =          $record['layoutID'];
                if ($_layoutID==1) {
                    $_layoutID = $defaultLayoutID;
                }

                $Obj_layout =         new layout($_layoutID);
                $_page_vars['layout_component_parameters'] = $Obj_layout->get_field('component_parameters');
                break;
        }
        return
            ($_page_vars['component_parameters']!="" ?
                $_page_vars['component_parameters'].OPTION_SEPARATOR
             :
                ""
            )
            .($_page_vars['layout_component_parameters']!="" ?
                $_page_vars['layout_component_parameters'].OPTION_SEPARATOR
            :
                ""
            )
            .($_system_vars['component_parameters']!="" ?
                $_system_vars['component_parameters']
            :
                ""
            );
    }

    public static function get_safe_ID($ident, $instance = '')
    {
        return str_replace(
            array('-',' '),
            array('_','_'),
            $ident."_".strToLower($instance)
        );
    }

    public function help($name, $params, $instance = '')
    {
        global $page_vars, $system_vars;
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =        get_person_permission("SYSEDITOR");
        $userIsAdmin =      ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
        if (!$userIsAdmin) {
            return "";
        }
        $parameters =   Component_Base::get_parameters();
        $ID_safe_name = Component_Base::$help_div_id++;
        $param_arr =    explode("[;]", $params);
        $cp_params =    array();
        $cp_defaults =  array();
        $cp_hints =     array();
        $cp_site =      array();
        $cp_layout =    array();
        $cp_item =      array();
        foreach ($param_arr as $param) {
            $temp = preg_split('/=/', $param);
            $cp_params[] =    (substr($temp[0], 0, strLen($name))==$name ? substr($temp[0], strLen($name)) : $temp[0]);
            $cp_site[] =
                Component_Base::get_parameter_for_instance(
                    $instance,
                    $system_vars['component_parameters'],
                    $temp[0],
                    ''
                );
            $cp_layout[] =
                Component_Base::get_parameter_for_instance(
                    $instance,
                    $page_vars['layout_component_parameters'],
                    $temp[0],
                    ''
                );
            $cp_item[] =
                Component_Base::get_parameter_for_instance(
                    $instance,
                    $page_vars['component_parameters'],
                    $temp[0],
                    ''
                );
            if (isset($temp[1])) {
                $temp = preg_split('/\[/', $temp[1]);
                if (count($temp)>1) {
                    $temp = preg_split('/\]/', $temp[1]);
                    $cp_hints[] = $temp[0];
                    $temp = preg_split('/\{/', $temp[1]);
                    if (count($temp)>1) {
                        $temp = preg_split('/\}/', $temp[1]);
                        $cp_defaults[] = $temp[0];
                    } else {
                        $cp_defaults[] = '';
                    }
                } else {
                    $cp_hints[] = '';
                    $cp_defaults[] = '';
                }
            } else {
                $cp_defaults[] = '';
            }
        }
        $cp_args =
        array(
        'id' =>         $ID_safe_name,
        'ident' =>      ($instance=='*' || $instance=='' ? '' : $instance.":").$name,
        'headings' =>   array('Parameter','Default','Site','Layout',$page_vars['object_type']),
        'params' =>     $cp_params,
        'hints' =>      $cp_hints,
        'defaults' =>   $cp_defaults,
        'site' =>       $cp_site,
        'layout' =>     $cp_layout,
        'item' =>       $cp_item
        );
        if ($ID_safe_name==0) {
            Page::push_content(
                'javascript',
                "cp_params=[];\n"
            );
        }
        Page::push_content(
            'javascript',
            "cp_params[".$ID_safe_name."] = ".json_encode($cp_args).";\n"
        );
        $out =
        "<div class='cp_icon' title='Click to adjust settings for\n".$name."'>\n"
        ."<a href='#'"
        ." onmouseover=\"this.parentNode.getElementsByTagName('span')[0].style.display='';\" "
        ." onmouseout=\"this.parentNode.getElementsByTagName('span')[0].style.display='none';\" "
        ." onclick='cp_popup();var a=new cp_matrix(\"cp_config\",cp_params[\"".$ID_safe_name."\"]);return false;'>"
        ."<img src='".BASE_PATH."img/spacer' class='icon' alt='' />"
        ."<span style='display:none;' class='cp_icon_description'>"
        .($instance=='*' || $instance=='' ? '' : "<span class='instance'>"
        .$instance.":</span>")
        ."<span class='ident'>".$name."</span>\n"
        ."</span></a></div>";
        return $out;
    }

    protected function _setup($instance, $args, $disable_params)
    {
        $this->_instance =          $instance;
        $this->_args =              $args;
        $this->_disable_params =    $disable_params;
        $this->_safe_ID =           Component_Base::get_safe_ID($this->_ident, $this->_instance);
        $this->_js_safe_ID =        get_js_safe_ID($this->_safe_ID);
        $this->_setup_load_parameters();
    }

    protected function _setup_load_block_layout($blockLayoutName)
    {
        $Obj_BlockLayout =  new Block_Layout;
        if (!$blockLayoutID = $Obj_BlockLayout->get_ID_by_name($blockLayoutName)) {
            return false;
        }
        $Obj_BlockLayout->_set_ID($blockLayoutID);
        $Obj_BlockLayout->load();
        return $Obj_BlockLayout;
    }

    protected function _setup_load_parameters()
    {
        $cp_settings =
        Component_Base::get_parameter_defaults_and_values(
            $this->_ident,
            $this->_instance,
            $this->_disable_params,
            $this->_parameter_spec,
            $this->_args
        );
        $this->_cp =            $cp_settings['parameters'];
        $this->_cp_defaults =   $cp_settings['defaults'];
    }

    protected function _setup_load_user_groups()
    {
        $this->_current_user_groups = Person::get_group_permissions();
        if (!$this->_current_user_groups) {
            return;
        }
        $groups = array();
        foreach ($this->_current_user_groups['VIEWER'] as $ID) {
            $groups[] = $ID;
        }
        foreach ($this->_current_user_groups['EDITOR'] as $ID) {
            $groups[] = $ID;
        }
        $this->_current_user_groups_access_csv = implode(',', array_unique($groups));
    }

    protected function _setup_load_user_rights()
    {
        $isPUBLIC =         Person::get_permission("PUBLIC");
        $isMASTERADMIN =    Person::get_permission("MASTERADMIN");
        $isSYSADMIN =        Person::get_permission("SYSADMIN");
        $isSYSAPPROVER =    Person::get_permission("SYSAPPROVER");
        $isSYSEDITOR =      Person::get_permission("SYSEDITOR", $this->record['group_assign_csv']);
        $isSYSMEMBER =        Person::get_permission("SYSMEMBER");
        $isUSERADMIN =        Person::get_permission("USERADMIN");
        $this->_current_user_rights['canRate'] =
            0;
        $this->_current_user_rights['canEdit'] =
            ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER || $isSYSEDITOR ? 1 : 0);
        $this->_current_user_rights['canPublish'] =
            ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER ? 1 : 0);
        $this->_current_user_rights['isPUBLIC'] =
            ($isPUBLIC);
        $this->_current_user_rights['isSYSMEMBER'] =
            ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER || $isSYSEDITOR || $isSYSMEMBER || $isUSERADMIN ? 1 : 0);
        $this->_current_user_rights['isSYSADMIN'] =
            ($isSYSADMIN || $isMASTERADMIN ? 1 : 0);
        $this->_current_user_rights['isUSERADMIN'] =
            ($isUSERADMIN);
        $this->_current_user_rights['isMASTERADMIN'] =
            ($isMASTERADMIN);
        $this->_isAdmin =
            ($this->_current_user_rights['canEdit'] || $isUSERADMIN ? 1 :0);
    }

    public function set_parameters($values)
    {
        global $system_vars, $page_vars;
        $settings = (json_decode(urldecode($values)));
        $Obj_System =   new System(SYS_ID);
        $change_arr =   $settings->site;
        $original_arr = explode(OPTION_SEPARATOR, $system_vars['component_parameters']);
        $merged_arr =   $this->_set_parameters_merge($original_arr, $change_arr);
        $Obj_System->set_field('component_parameters', addslashes(implode(OPTION_SEPARATOR, $merged_arr)));
        $Obj_Layout =   new Layout($page_vars['layout']['ID']);
        $change_arr =   $settings->layout;
        $original_arr = explode(OPTION_SEPARATOR, $page_vars['layout_component_parameters']);
        $merged_arr =   $this->_set_parameters_merge($original_arr, $change_arr);
        $Obj_Layout->set_field('component_parameters', addslashes(implode(OPTION_SEPARATOR, $merged_arr)));
        switch ($page_vars['object_type']){
            case 'Page':
                $Obj =     new $page_vars['object_type'];
                $Obj->_set_ID($page_vars['ID']);
                $change_arr =   $settings->item;
                $original_arr = explode(OPTION_SEPARATOR, $page_vars['component_parameters']);
                $merged_arr =   $this->_set_parameters_merge($original_arr, $change_arr);
                $Obj->set_field('component_parameters', addslashes(implode(OPTION_SEPARATOR, $merged_arr)));
                break;
            default:
                $Obj =     new $page_vars['object_type'];
                $Obj->_set_ID($page_vars['ID']);
                $change_arr =   $settings->item;
                $original_arr = explode(OPTION_SEPARATOR, $page_vars['component_parameters']);
                $merged_arr =   $this->_set_parameters_merge($original_arr, $change_arr);
                $Obj->set_field('component_parameters', addslashes(implode(OPTION_SEPARATOR, $merged_arr)));
                break;
        }
        Page::push_content(
            'javascript_onload',
            "geid_set('command','');\n"
            ."geid('form').submit();\n"
        );
    }

    private function _set_parameters_merge($original_arr, $change_arr)
    {
        $original_indexed = array();
        foreach ($original_arr as $entry) {
            $bits =   explode('=', $entry);
            $key =    array_shift($bits);
            $value =  implode('=', $bits);
            $original_indexed[$key] = $value;
        }
        $new_indexed =  array();
        foreach ($change_arr as $entry) {
            $bits =   explode('=', $entry);
            $key =    array_shift($bits);
            $value =  implode('=', $bits);
            $new_indexed[$key] = $value;
        }
        $combined_arr = array_merge($original_indexed, $new_indexed);
        $out = array();
        foreach ($combined_arr as $key => $value) {
            if ($value!=='') {
                $out[] = $key."=".$value;
            }
        }
        sort($out);
        return $out;
    }

    public function get_version()
    {
        return VERSION_COMPONENT_BASE;
    }
}
