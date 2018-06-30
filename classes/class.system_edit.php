<?php
define('VERSION_SYSTEM_EDIT', '1.0.31');

/*
Version History:
  1.0.31 (2014-12-31)
    1) Now uses OPTION_SEPARATOR constant not option_separator in System_Edit::_do_save()

  (Older version history in class.system_edit.txt)
*/
class System_Edit extends System
{
    private $_colour_schemeID;
    private $_html;
    private $_msg;
    private $_isMASTERADMIN;
    private $_posting_prefix_old;
    private $_posting_prefix_updates;
    private $_section_tabs_arr;
    private $_selected_section;
    private $_submode;

    private function _do_initial_actions()
    {
        switch ($this->_submode) {
            case "delete_file":
                $this->_do_delete_files();
                break;
            case "delete_scheme":
                $this->_do_delete_colourscheme();
                break;
            case "load_scheme":
                $this->_do_load_colourscheme();
                break;
            case "save_scheme":
                $this->_do_save_colourscheme();
                break;
        }
      // Check again incase submode changed
        switch ($this->_submode) {
            case 'save':
            case 'save_and_close':
                $this->_do_save();
                break;
        }
    }

    private function _do_delete_colourscheme()
    {
        $Obj_CS = new Colour_Scheme($this->_colour_schemeID);
        $Obj_CS->delete();
        do_log(0, __CLASS__.'::'.__FUNCTION__.'()', 'delete_scheme', 'Deleted colour scheme '.$this->_colour_schemeID);
    }

    private function _do_delete_files()
    {
        $Obj_FS = new FileSystem;
        foreach ($_REQUEST as $key => $value) {
            $Obj_FS->delete_dir_entry($key, $value);
        }
    }

    private function _do_load_colourscheme()
    {
        if ($this->_colour_schemeID!="") {
            $Obj_CS = new Colour_Scheme($this->_colour_schemeID);
            $record = $Obj_CS->get_record();
            if ($record) {
                foreach ($record as $key => $value) {
                    $_POST[$key] = $value;
                }
                $this->_submode='save';
            }
        }
        do_log(0, __CLASS__.'::'.__FUNCTION__.'()', 'load_scheme', 'Loaded colour scheme '.$this->_colour_schemeID);
    }

    private function _do_save()
    {
        global $system_vars;
        $this->_posting_prefix_old =    $this->get_field('posting_prefix');
        $data =
        array(
        'adminEmail'=>                  addslashes(get_var('adminEmail')),
        'adminName'=>                   addslashes(get_var('adminName')),
        'akismet_api_key'=>             addslashes(get_var('akismet_api_key')),
        'bounce_email'=>                addslashes(get_var('bounce_email')),
        'bugs_password' =>              addslashes(get_var('bugs_password')),
        'bugs_username' =>              addslashes(get_var('bugs_username')),
        'bugs_url' =>                   addslashes(get_var('bugs_url')),
        'cal_border'=>                  strToUpper(get_var('cal_border')),
        'cal_current'=>                 strToUpper(get_var('cal_current')),
        'cal_current_we'=>              strToUpper(get_var('cal_current_we')),
        'cal_days'=>                    strToUpper(get_var('cal_days')),
        'cal_event'=>                   strToUpper(get_var('cal_event')),
        'cal_head'=>                    strToUpper(get_var('cal_head')),
        'cal_then'=>                    strToUpper(get_var('cal_then')),
        'cal_then_we'=>                 strToUpper(get_var('cal_then_we')),
        'cal_today'=>                   strToUpper(get_var('cal_today')),
        'colour1'=>                     strToUpper(get_var('colour1')),
        'colour2'=>                     strToUpper(get_var('colour2')),
        'colour3'=>                     strToUpper(get_var('colour3')),
        'colour4'=>                     strToUpper(get_var('colour4')),
        'component_parameters'=>        addslashes(
            implode(OPTION_SEPARATOR, explode("\r\n", get_var('component_parameters')))
        ),
        'defaultBgColor'=>              strToUpper(get_var('defaultBgColor')),
        'defaultDateFormat'=>           addslashes(get_var('defaultDateFormat')),
        'defaultLayoutID'=>             addslashes(get_var('defaultLayoutID')),
        'defaultTimeFormat'=>           sanitize('range', get_var('defaultTimeFormat'), 0, 3, 0),
        'defaultThemeID'=>              addslashes(get_var('defaultThemeID')),
        'favicon'=>                     addslashes(get_var('favicon')),
        'google_analytics_key'=>        addslashes(get_var('google_analytics_key')),
        'notify_email'=>                addslashes(get_var('notify_email')),
        'notify_triggers'=>             addslashes(get_var('notify_triggers')),
        'piwik_id'=>                    addslashes(get_var('piwik_id')),
        'piwik_token'=>                 addslashes(get_var('piwik_token')),
        'piwik_user'=>                  addslashes(get_var('piwik_user')),
        'style'=>                       addslashes(get_var('style')),
        'system_cancellation_days'=>    addslashes(get_var('system_cancellation_days')),
        'system_signup'=>               addslashes(get_var('system_signup')),
        'table_border'=>                strToUpper(get_var('table_border')),
        'table_data'=>                  strToUpper(get_var('table_data')),
        'table_header'=>                strToUpper(get_var('table_header')),
        'text_heading'=>                strToUpper(get_var('text_heading')),
        'textEnglish'=>                 addslashes(get_var('textEnglish')),
        'timezone'=>                    addslashes(get_var('timezone')),
        'URL'=>                         addslashes(get_var('URL'))
        );
    //  Assign checksum and version by system creating target
        if ($this->_get_ID()=="") {
              $data['db_cstarget'] =            $system_vars['db_cstarget'];
              $data['db_version'] =             $system_vars['db_version'];
        }
        if ($this->_isMASTERADMIN || System::has_feature('Membership-Renewal')) {
            $data['membership_rules'] =       addslashes(get_var('membership_rules'));
            $data['membership_expiry_type'] = addslashes(get_var('membership_expiry_type'));
        }
        if ($this->_isMASTERADMIN || System::has_feature('multi-language')) {
            $data['defaultLanguage'] =        addslashes(get_var('defaultLanguage'));
            $data['languages'] =              addslashes(get_var('languages'));
        }
        $this->_set_ID($this->update($data));
        if ($this->_isMASTERADMIN) {
            $data = array(
                'db_custom_tables'=>                addslashes(get_var('db_custom_tables')),
                'debug'=>                           addslashes(get_var('debug')),
                'debug_no_internet'=>               addslashes(get_var('debug_no_internet')),
                'defaultCurrencySuffix'=>           addslashes(get_var('defaultCurrencySuffix')),
                'defaultCurrencySymbol'=>           addslashes(fix_currency_symbols(get_var('defaultCurrencySymbol'))),
                'defaultTaxZoneID'=>                addslashes(get_var('defaultTaxZoneID')),
                'features'=>                        addslashes(get_var('features')),
                'gatewayID'=>                       addslashes(get_var('gatewayID')),
                'installed_modules'=>               addslashes(get_var('installed_modules')),
                'notes'=>                           addslashes(get_var('notes')),
                'posting_prefix'=>                  addslashes(get_var('posting_prefix')),
                'provider_list'=>                   addslashes(implode(",", explode("\n", get_var('provider_list')))),
                'qbwc_user'=>                       addslashes(get_var('qbwc_user')),
                'qbwc_pass'=>                       addslashes(get_var('qbwc_pass')),
                'qbwc_invoice_type'=>               addslashes(
                    sanitize('enum', get_var('qbwc_invoice_type'), array('I','S'))
                ),
                'qbwc_AssetAccountRef'=>            addslashes(get_var('qbwc_AssetAccountRef')),
                'qbwc_COGSAccountRef'=>             addslashes(get_var('qbwc_COGSAccountRef')),
                'qbwc_IncomeAccountRef'=>           addslashes(get_var('qbwc_IncomeAccountRef')),
                'qbwc_export_orders'=>              addslashes(get_var('qbwc_export_orders')),
                'qbwc_export_orders_billing_addr'=> addslashes(get_var('qbwc_export_orders_billing_addr')),
                'qbwc_export_orders_product_desc'=> addslashes(get_var('qbwc_export_orders_product_desc')),
                'qbwc_export_orders_taxcodes'=>     addslashes(get_var('qbwc_export_orders_taxcodes')),
                'qbwc_export_people'=>              addslashes(get_var('qbwc_export_people')),
                'qbwc_export_products'=>            addslashes(get_var('qbwc_export_products')),
                'smtp_authenticate'=>               addslashes(get_var('smtp_authenticate')),
                'smtp_host'=>                       addslashes(get_var('smtp_host')),
                'smtp_password'=>                   addslashes(get_var('smtp_password')),
                'smtp_port'=>                       addslashes(get_var('smtp_port')),
                'smtp_username'=>                   addslashes(get_var('smtp_username')),
                'tax_benefit_1_name'=>              addslashes(get_var('tax_benefit_1_name')),
                'tax_benefit_2_name'=>              addslashes(get_var('tax_benefit_2_name')),
                'tax_benefit_3_name'=>              addslashes(get_var('tax_benefit_3_name')),
                'tax_benefit_4_name'=>              addslashes(get_var('tax_benefit_4_name')),
            );
            $this->update($data);
        }
        $old_modules = $system_vars['installed_modules'];
        do_log(0, __CLASS__.'::'.__FUNCTION__.'()', 'save', 'Saved system '.$this->_get_ID());
        $system_vars =  get_system_vars(); // Refresh system_vars after updating
        $new_modules = $system_vars['installed_modules'];
        if ($old_modules!=$new_modules) {
            $old_arr = explode(',', str_replace(' ', '', $old_modules));
            $new_arr = explode(',', str_replace(' ', '', $new_modules));
            foreach ($old_arr as $old) {
                if (!in_array($old, $new_arr)) {
                    Base::module_uninstall($old);
                }
            }
            foreach ($new_arr as $new) {
                if (!in_array($new, $old_arr)) {
                    Base::module_install($new);
                }
            }
        }
        if (
            $this->_isMASTERADMIN &&
            get_var('posting_prefix') &&
            get_var('posting_prefix')!=$this->_posting_prefix_old
        ) {
            $this->_posting_prefix_updates = $this->update_posting_prefix(get_var('posting_prefix'));
        }
    }

    private function _do_save_colourscheme()
    {
        if (!$targetValue=get_var('targetValue')) {
            return;
        }
        $Obj_CS =  new Colour_Scheme;
        if ($Obj_CS->exists_named($targetValue, SYS_ID)) {
            $this->_msg = "<b>Error</b> - the colour scheme <b>".$targetValue."</b> already exists for this site.";
            return;
        }
        $fields = array(
            'colour1', 'colour2', 'colour3', 'colour4', 'cal_border', 'cal_current', 'cal_current_we',
            'cal_days', 'cal_event', 'cal_head', 'cal_then', 'cal_then_we', 'cal_today', 'defaultBgColor',
            'table_border', 'table_data', 'table_header', 'text_heading'
        );
        $data = array(
        'systemID'=>        SYS_ID,
        );
        foreach ($fields as $field) {
            $data[$field] = strToUpper(sanitize('hex3', get_var($field), ''));
        }
        $test_result = $Obj_CS->Lookup($data);
        if ($test_result) {
            $this->_msg =
                "<b>Error</b> - you already have an identical colour scheme defined - <b>".$test_result['name']."</b>";
            $this->_colour_schemeID = $test_result['ID'];
            return;
        }
        $data['name'] = addSlashes($targetValue);
        $this->_colour_schemeID = $Obj_CS->insert($data);
        do_log(0, __CLASS__.'::'.__FUNCTION__.'()', 'save_scheme', 'Saved colour scheme '.$this->_colour_schemeID);
    }

    public function draw()
    {
        set_time_limit(600);    // Extend maximum execution time to 10 mins
        $this->_submode =           get_var('submode');
        $this->_colour_schemeID =   get_var('colour_schemeID');
        $this->_selected_section =  get_var('selected_section', 'general');
        $this->_isMASTERADMIN =     get_person_permission("MASTERADMIN");
        $this->_msg =               "";
        $this->_height =            475;
        $this->_width =             800;
        $this->_do_initial_actions();
        $this->_draw_js();
        if ($this->_submode=='save_and_close' && $this->_msg=='') {
            return $this->_html;
        }
        $this->_setup_colour_schemeID();
        $this->_setup_section_tabs();
        $this->load();
        $this->_draw_css();
        $this->_html.=
         draw_form_header("Site Settings", "_help_admin_sites", 0)
        ."<div style='background:#f0f0ff;width:".$this->_width."px;height:".$this->_height."px;'>\n"
        .draw_form_field('ID', $this->_get_ID(), 'hidden')."\n"
        .HTML::draw_section_tabs($this->_section_tabs_arr, 'system', $this->_selected_section);
        $this->_draw_section_general();
        $this->_draw_section_colours();
        $this->_draw_section_css();
        $this->_draw_section_parameters();
        $this->_draw_section_membership_rules();
        $this->_draw_section_advanced();
        $this->_draw_section_notes();
        $this->_draw_section_logs();
        $this->_draw_section_features();
        $this->_draw_section_status();
        $this->_html.=
             "<div style='clear:both;text-align:center;margin:0.25em 0 0 0;'>"
            ."<input type='button' id='close_btn' value='Close' onclick=\"window.close()\""
            ." class='formbutton' style='width: 60px;'/>\n"
            ."<input type='button' id='save_btn' value='Save' onclick=\""
            ."geid('close_btn').disabled=1;geid('save_and_close_btn').disabled=1;this.disabled=1;"
            ."show_popup_please_wait();geid('submode').value='save';geid('form').submit();"
            ."\" class='formbutton' style='width: 60px;'/>\n"
            ."<input type='button' id='save_and_close_btn' value='Save and Close'"
            ." onclick=\"geid('close_btn').disabled=1;geid('save_btn').disabled=1;this.disabled=1;"
            ."show_popup_please_wait();geid('submode').value='save_and_close';geid('form').submit();"
            ."\" class='formbutton' style='width: 120px;'/>\n"
            ."</div>\n"
            ."</div>\n";
        return $this->_html;
    }

    private function _draw_css()
    {
        $base_path = ($this->record['ID']==SYS_ID ? "/" : trim($this->record['URL'], '/')."/");
        Page::push_content(
            'style_include',
            "<link rel=\"stylesheet\" type=\"text/css\""
            ." href=\"".$base_path."css/system/nocache/".dechex(mt_rand(0, mt_getrandmax()))."\" />"
        );
        Page::push_content(
            'style',
            ".settings_group_header { padding:1px 2px 1px 2px; font-size: 80%; }\n"
            .".settings_group { margin: 0 2px 10px 2px; padding:1px; border:1px solid #c0c0c0;"
            ." background-color:#fff; font-size: 80% }\n"
            .".settings_group label { float: left; display: block; padding: 0 0.5em;}\n"
            .".settings_group .lbl { float: left; }\n"
            .".settings_group .val { float: left; }\n"
            ."#checkbox_csv_notify_triggers { border: none; }\n"
            ."#checkbox_csv_notify_triggers div { padding: 0; }"
            ."#checkbox_csv_notify_triggers label { padding: 0 5px 0 0; float: left; }"
        );
    }

    private function _draw_js()
    {
        switch ($this->_submode) {
            case '':
                return;
            break;
            case 'save_and_close':
                if ($this->_msg=='') {
                    Page::push_content(
                        'javascript_onload',
                        "  if (window.opener && window.opener.geid('form')){\n"
                        ."    window.opener.geid('anchor').value='row_".$this->_get_ID()."';\n"
                        ."    window.opener.geid('form').action='#row_".$this->_get_ID()."';\n"
                        ."    window.opener.geid('form').submit();\n"
                        ."    window.close();\n"
                        ."  }"
                    );
                }
                break;
            default:
                Page::push_content(
                    'javascript_onload',
                    "  if (window.opener && window.opener.geid('form')){\n"
                    ."    window.opener.geid('anchor').value='row_".$this->_get_ID()."';\n"
                    ."    window.opener.geid('form').action='#row_".$this->_get_ID()."';\n"
                    ."    window.opener.geid('form').submit();\n"
                    ."  }"
                );
                break;
        }
    }

    private function _draw_section_advanced()
    {
        if (!$this->_isMASTERADMIN) {
            return;
        }
        $this->_html.=
             draw_section_tab_div('advanced', $this->_selected_section)
            ."  <div class='settings_group'>\n"
            ."    <label style='width:150px' for='debug'><b>SQL Debug File</b><br />"
            ."(Affects performance)</label>"
            ."    <div class='val'>"
            .draw_form_field("debug", $this->record['debug'], "bool")
            ."</div>\n"
            ."    <label style='width:200px' for='debug'><b>No Internet Mode</b><br />"
            ."(Disables external services)</label>"
            ."    <div class='val'>"
            .draw_form_field("debug_no_internet", $this->record['debug_no_internet'], "bool")
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div><b>SMTP Mail Settings</b></div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:80px;' for='smtp_host'>Host</label>\n"
            ."    <div class='val'>"
            .draw_form_field("smtp_host", $this->record['smtp_host'], "text", "125")
            ."</div>"
            ."    <label style='width:40px; text-align:right' for='smtp_port'>Port</label>\n"
            ."    <div class='val'>"
            .draw_form_field("smtp_port", $this->record['smtp_port'], "int", "25")
            ."</div>"
            ."    <label style='width:40px; text-align:right' for='smtp_authenticate'>Auth.</label>\n"
            ."    <div class='val'>"
            .draw_form_field("smtp_authenticate", $this->record['smtp_authenticate'], "bool")
            ."</div>\n"
            ."    <label style='width:80px;text-align:right;' for='smtp_username'>Username</label>\n"
            ."    <div class='val'>"
            .draw_form_field("smtp_username", $this->record['smtp_username'], "text", "100")
            ."</div>"
            ."    <label style='width:86px;text-align: right;' for='smtp_password'>Password</label>\n"
            ."    <div class='val'>
            ".draw_form_field("smtp_password", $this->record['smtp_password'], "password", "100")
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div><b>E-Commerce Options</b></div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:80px;' for='gatewayID'>Gateway</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "gatewayID",
                $this->record['gatewayID'],
                "selector",
                "130px",
                Gateway_Setting::get_selector_sql()
            )
            ."</div>"
            ."    <label style='width:110px;text-align:right;' for='defaultCurrencySymbol'>Currency Symbol</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "defaultCurrencySymbol",
                $this->record['defaultCurrencySymbol'],
                "selector_listdata",
                '30',
                '',
                '',
                '',
                '',
                '',
                '',
                'lst_currency_symbols'
            )
            ."</div>"
            ."    <label style='width:110px;text-align:right;' for='defaultCurrencySuffix'>Currency Suffix</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "defaultCurrencySuffix",
                $this->record['defaultCurrencySuffix'],
                "text",
                "30px"
            )
            ."</div>"
            ."    <label style='width:80px;text-align:right;' for='defaultTaxZoneID'>Tax Zone</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "defaultTaxZoneID",
                $this->record['defaultTaxZoneID'],
                "selector",
                "130px",
                Tax_Regime::get_selector_sql()
            )
            ."</div>"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:80px;'>Tax Benefits</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "tax_benefit_1_name",
                $this->record['tax_benefit_1_name'],
                'text',
                '130px'
            )
            ."</div>"
            ."    <label style='width:20px;' for='tax_benefit_1_name'>#1</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "tax_benefit_2_name",
                $this->record['tax_benefit_2_name'],
                'text',
                '130px'
            )
            ."</div>"
            ."    <label style='width:20px;' for='tax_benefit_2_name'>#2</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "tax_benefit_3_name",
                $this->record['tax_benefit_3_name'],
                'text',
                '130px'
            )
            ."</div>"
            ."    <label style='width:20px;' for='tax_benefit_3_name'>#3</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "tax_benefit_4_name",
                $this->record['tax_benefit_4_name'],
                'text',
                '130px'
            )
            ."</div>"
            ."    <label style='width:20px;' for='tax_benefit_4_name'>#4</label>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div><b>Quickbooks Support</b></div>\n"
            ."    <label style='width:90px' for='qbwc_user'>QBWC User</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_user",
                $this->record['qbwc_user'],
                'text',
                '130px'
            )
            ."</div>\n"
            ."    <label style='width:90px' for='qbwc_pass'>Password</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_pass",
                $this->record['qbwc_pass'],
                'password',
                '130px'
            )
            ."</div>\n"
            ."    <label style='width:90px' for='qbwc_invoice_type'>Invoice Type</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_invoice_type",
                $this->record['qbwc_invoice_type'],
                'radio_csvlist',
                '',
                '',
                0,
                '',
                0,
                0,
                '',
                'I|Invoice,S|Sales Order'
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:90px' for='qbwc_AssetAccountRef'>Asset Account</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_AssetAccountRef",
                $this->record['qbwc_AssetAccountRef'],
                'text',
                '130px'
            )
            ."</div>\n"
            ."    <label style='width:90px' for='qbwc_COGSAccountRef'>GOGS Account</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_COGSAccountRef",
                $this->record['qbwc_COGSAccountRef'],
                'text',
                '130px'
            )
            ."</div>\n"
            ."    <label style='width:100px' for='qbwc_IncomeAccountRef'>Income Account</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_IncomeAccountRef",
                $this->record['qbwc_IncomeAccountRef'],
                'text',
                '130px'
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:90px' for='qbwc_export_orders'>Export Orders</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_export_orders",
                $this->record['qbwc_export_orders'],
                'selector_csvlist',
                '130px',
                '',
                '',
                '',
                '',
                '',
                '',
                '0|(None)|d0d0d0,2|All|e0ffe0'
            )
            ."</div>\n"
            ."    <label style='width:90px' for='qbwc_export_people'>Export People</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_export_people",
                $this->record['qbwc_export_people'],
                'selector_csvlist',
                '130px',
                '',
                '',
                '',
                '',
                '',
                '',
                '0|(None)|d0d0d0,1|Customers|ffe0c0,2|All|e0ffe0'
            )
            ."</div>\n"
            ."    <label style='width:100px' for='qbwc_export_products'>Export Products</label>"
            ."    <div class='val'>"
            .draw_form_field(
                "qbwc_export_products",
                $this->record['qbwc_export_people'],
                'selector_csvlist',
                '130px',
                '',
                '',
                '',
                '',
                '',
                '',
                '0|(None)|d0d0d0,1|Ordered|ffe0c0,2|All|e0ffe0'
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:90px' for='qbwc_export_orders_billing_addr'>Billing Addr.</label>"
            ."    <div class='val' style='width:134px'>"
            .draw_form_field(
                "qbwc_export_orders_billing_addr",
                $this->record['qbwc_export_orders_billing_addr'],
                'radio_csvlist',
                '',
                '',
                0,
                '',
                0,
                0,
                '',
                '0|QB,1|Send'
            )
            ."</div>\n"
            ."    <label style='width:90px' for='qwbc_export_orders_product_desc'>Product Desc.</label>"
            ."    <div class='val' style='width:134px'>"
            .draw_form_field(
                "qbwc_export_orders_product_desc",
                $this->record['qbwc_export_orders_product_desc'],
                'radio_csvlist',
                '',
                '',
                0,
                '',
                0,
                0,
                '',
                '0|QB,1|Send'
            )
            ."</div>\n"
            ."    <label style='width:100px' for='qbwc_export_orders_taxcodes'>Item Taxes.</label>"
            ."    <div class='val' style='width:130px'>"
            .draw_form_field(
                "qbwc_export_orders_taxcodes",
                $this->record['qbwc_export_orders_taxcodes'],
                'radio_csvlist',
                '',
                '',
                0,
                '',
                0,
                0,
                '',
                '0|QB,1|Send'
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div class='lbl' style='width:200px'><b>Custom tables used (CSV list)</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "db_custom_tables",
                $this->record['db_custom_tables'],
                "text",
                550
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div class='lbl' style='width:200px'><b>Posting Prefix Format</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "posting_prefix",
                $this->record['posting_prefix'],
                "selector_listdata",
                550,
                '',
                '',
                '',
                '',
                '',
                '',
                'lst_posting_prefix|0'
            )
            .(
                get_var('posting_prefix') &&
                $this->_posting_prefix_old &&
                get_var('posting_prefix')!=$this->_posting_prefix_old ?
                   "<br /><span style='color:#ff0000'><b>Posting Prefix was updated from "
                  ."'".($this->_posting_prefix_old ? $this->_posting_prefix_old : '(none)')."' to '"
                  .($this->record['posting_prefix'] ? $this->record['posting_prefix'] : '(none)')."' - "
                  .$this->_posting_prefix_updates." posting paths have changed.</b></span>\n"
                :
                   ""
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group_header'>"
            ."<b>News and Event sources - get shared news and events from these sites:</b><br />\n"
            ."(<b>Performance Tip:</b> Enclose source URL in brackets if the site lives in current database.)<br />"
            .draw_form_field(
                "provider_list",
                $this->record['provider_list'],
                "csv",
                ($this->_width-10),
                '',
                '',
                '',
                '',
                false,
                '',
                '',
                60
            )."\n"
            ."  </div>\n"
            ."</div>";
    }

    private function _draw_section_css()
    {
        $this->_html.=
             draw_section_tab_div('style', $this->_selected_section)
            ."  <div class='settings_group_header'>"
            ."<b>These styles apply any page in this site but can be overridden at the layout or page level.</b>"
            ."<br />\n"
            .draw_form_field(
                "style",
                $this->record['style'],
                "textarea",
                ($this->_width-10),
                '',
                '',
                '',
                '',
                false,
                '',
                '',
                355
            )
            ."  </div>"
            ."</div>\n";
    }

    private function _draw_section_colours()
    {
        $this->_html.=
         draw_section_tab_div('colours', $this->_selected_section)
        ."  <div class='settings_group'>\n"
        ."    <div class='lbl'><b>Colour Scheme</b>"
        .($this->_msg!="" ? " &nbsp; <span style='color:#f00'>".$this->_msg."</span>" : "")
        ."</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."    <label style='width:120px' for='colour_schemeID'>Use this:</label>\n"
        ."    <div class='val'>"
        .draw_form_field(
            "colour_schemeID",
            $this->_colour_schemeID,
            "selector",
            "350px",
            Colour_Scheme::get_selector_sql()
        )
        ." "
        ."<input type='button' class='formButton' style='width: 90px' value='Load' "
        ."onclick=\"if (geid_val('colour_schemeID')==1) { alert('Please select a colour scheme to load'); } else { "
        ."if(confirm('This action will overwrite the current scheme - continue?')) { "
        ."geid_set('submode','load_scheme');geid('form').submit();} else {alert('Action Cancelled')}}\"/>\n"
        ."<input type='button' class='formButton' style='width: 90px' value='Save As...' "
        ."onclick=\"var cs_name=prompt('Please enter a name for this scheme','');"
        ."if(cs_name){geid_set('targetValue',cs_name);geid_set('submode','save_scheme');geid('form').submit();}\"/>\n"
        ."<input type='button' class='formButton' style='width: 90px' value='Delete' "
        ."onclick=\"if (geid_val('colour_schemeID')==1) { alert('Please select a colour scheme to delete'); } "
        ."else if(geid('colour_schemeID').options[geid('colour_schemeID').selectedIndex].text.substr(0,2)=='* ') { "
        ."alert('You cannot delete a globally defined colour scheme'); } "
        ."else if(confirm('Delete this colour scheme?')) {"
        ."geid('submode').value='delete_scheme';geid('form').submit();}\"/>\n"
        ."</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."  </div>\n"
        ."  <div class='settings_group'>\n"
        ."    <div class='lbl'><b>Colours</b></div>\n"
        ."    <div class='clr_b'></div>\n"
        ."    <label style='width:120px' for='defaultBgColor' title='Page Background Colour'>Page BG</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("defaultBgColor", $this->record['defaultBgColor'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='text_heading' title='Text Heading Colour'>Text Headings</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("text_heading", $this->record['text_heading'], "swatch", "60px")
        ."</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."  </div>\n"
        ."  <div class='settings_group'>\n"
        ."    <div class='lbl'><b>Table Colours</b></div>\n"
        ."    <div class='clr_b'></div>\n"
        ."    <label style='width:120px' for='table_border' title='Table Border Colour'>Table Borders</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("table_border", $this->record['table_border'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='table_header' title='Table Header Cells Colour'>Table Headers</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("table_header", $this->record['table_header'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='table_data' title='Table Data Cells Colour'>Table Data</label>\n"
        ."    <div class='val' style='width:100px'>"
        .draw_form_field("table_data", $this->record['table_data'], "swatch", "60px")
        ."</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."  </div>\n"
        ."  <div class='settings_group'>\n"
        ."    <div class='lbl'><b>Accent Colours</b> (may be overridden by layout or page settings)</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."    <label style='width:120px' for='colour1' title='Accent Colour #1'>Accent # 1</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("colour1", $this->record['colour1'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='colour2' title='Accent Colour #2'>Accent # 2</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("colour2", $this->record['colour2'], "swatch", "60px")
        ."</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."    <label style='width:120px' for='colour3' title='Accent Colour #3'>Accent # 3</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("colour3", $this->record['colour3'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='colour4' title='Accent Colour #4'>Accent # 4</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("colour4", $this->record['colour4'], "swatch", "60px")
        ."</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."  </div>\n"
        ."  <div class='settings_group'>\n"
        ."    <div class='lbl'><b>Calendar Colour Scheme</b></div>\n"
        ."    <div class='clr_b'></div>\n"
        ."    <div style='float:right;height:200px'><div class='constrain'>"
        ."<div style='position:relative;padding:0px 10px'>"
        .Component_Calendar_Small::draw(array('shadow'=>1,'show'=>'sample'))
        ."</div></div></div>\n"
        ."    <label style='width:120px' for='cal_border' title='Calendar Border'>Border</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_border", $this->record['cal_border'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_current' title='Current Weekday'>Current</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_current", $this->record['cal_current'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_head' title='Calendar Heading'>Heading</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_head", $this->record['cal_head'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_days' title='Day Headings'>Day Headings</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_days", $this->record['cal_days'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_current_we' title='Current Weekend'>Current Wknd</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_current_we", $this->record['cal_current_we'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_today' title=\"Today's date\">Today's Date</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_today", $this->record['cal_today'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_then' title='Inactive Weekday'>Inactive Weekday</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_then", $this->record['cal_then'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_event' title='Event indicator ring'>Event</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_event", $this->record['cal_event'], "swatch", "60px")
        ."</div>\n"
        ."    <label style='width:120px' for='cal_then_we' title='Inactive Weekend'>Inactive Weekend</label>\n"
        ."    <div class='val' style='width:130px'>"
        .draw_form_field("cal_then_we", $this->record['cal_then_we'], "swatch", "60px")
        ."</div>\n"
        ."    <div class='clr_b'></div>\n"
        ."  </div>\n"
        ."</div>";
    }

    private function _draw_section_features()
    {
        if (!$this->_isMASTERADMIN) {
            return;
        }
        $this->_html.=
             draw_section_tab_div('features', $this->_selected_section)
            ."                <table class='minimal' width='100%'>\n"
            ."                  <tr>\n"
            ."                    <td class='va_t txt_c' style='height:300px;'>\n"
            ."<b>&nbsp;Features available on this site</b><br />\n"
            .draw_form_field(
                "features",
                $this->record['features'],
                "checkbox_listdata_csv",
                "360",
                '',
                '',
                '',
                '',
                false,
                '',
                'lst_features|1',
                340
            )
            ."</td>\n"
            ."                    <td class='va_t txt_c' style='height:300px;'>\n"
            ."<b>&nbsp;Modules installed on this site</b><br />\n"
            .draw_form_field(
                "installed_modules",
                $this->record['installed_modules'],
                "checkbox_csvlist_scrollbox",
                "360",
                '',
                '',
                '',
                '',
                false,
                '',
                $this->get_modules_available(),
                340
            )
            ."</td>\n"
            ."                  </tr>\n"
            ."                </table></div>";

    }

    private function _draw_section_general()
    {
        $this->_html.=
             draw_section_tab_div('general', $this->_selected_section)
            ."  <div class='settings_group'>\n"
            ."    <div class='lbl' style='width:160px'><b>Title</b></div>\n"
            ."    <div class='val' style='float:left;width:250px;'>"
            .draw_form_field(
                "textEnglish",
                $this->record['textEnglish'],
                "text",
                "250"
            )
            ."</div>"
            ."    <div style='float:left;width:362px;text-align:right'><b>URL</b> "
            .draw_form_field(
                "URL",
                $this->record['URL'],
                "text",
                "250"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:160px'><b>Admin Name</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "adminName",
                $this->record['adminName'],
                "text",
                "250"
            )
            ."</div>"
            ."    <div class='lbl' style='width:104px;text-align:right'><b>Admin Email</b>&nbsp;</div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "adminEmail",
                $this->record['adminEmail'],
                "text",
                "250"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:414px'>&nbsp;</div>\n"
            ."    <div class='lbl' style='width:104px;text-align:right'><b>Bounce Email</b>&nbsp;</div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "bounce_email",
                $this->record['bounce_email'],
                "text",
                "250"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:160px'><b>New Access Accounts</b></div>\n"
            ."    <div class='val'>"
            ."      <div style='float:left;width:250px;'>"
            .draw_form_field(
                "system_signup",
                $this->record['system_signup'],
                "selector_listdata",
                "250",
                '',
                '',
                '',
                '',
                '',
                '',
                'lst_system_signup_options'
            )
            ."</div>"
            ."      <div style='float:left;width:362px;text-align:right'><b>Event Cancellation notice (days) </b> "
            .draw_form_field(
                "system_cancellation_days",
                $this->record['system_cancellation_days'],
                "int",
                "20"
            )
            ."</div>\n"
            ."    </div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div><b>Notification Options</b></div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:200px' for='notify_email'>Notify Email Address List</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "notify_email",
                $this->record['notify_email'],
                "text",
                550
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <label style='width:200px' for='notify_triggers'>Notify on these Triggers</label>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "notify_triggers",
                $this->record['notify_triggers'],
                "checkbox_listdata_csv",
                550,
                '',
                '',
                '',
                '',
                false,
                '',
                'lst_notify_triggers|1',
                20
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div class='lbl' style='width:170px'><b>Default Layout</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "defaultLayoutID",
                $this->record['defaultLayoutID'],
                "selector",
                "608px",
                Layout::get_selector_sql(false)
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:170px'><b>Default Theme</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "defaultThemeID",
                $this->record['defaultThemeID'],
                "selector",
                "608px",
                Theme::get_selector_sql(false)
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div class='lbl' style='width:170px'><b>Displayed Date Format</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "defaultDateFormat",
                $this->record['defaultDateFormat'],
                "combo_listdata",
                "608px",
                "",
                "",
                "",
                "",
                "",
                "",
                "lst_date_format"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:170px'><b>Displayed Time Format</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "defaultTimeFormat",
                $this->record['defaultTimeFormat'],
                "selector_listdata",
                "608px",
                "",
                "",
                "",
                "",
                "",
                "",
                "lst_time_format"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:170px'><b>Default Timezone</b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "timezone",
                $this->record['timezone'],
                "selector_timezone",
                "608px"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div class='lbl' style='width:170px'><b>Akismet Key "
            ."<a href='http://wordpress.com/signup/' rel='external'>Get WP Key</a></b></div>\n"
            ."    <div class='val' style='width:510px'>"
            .draw_form_field(
                "akismet_api_key",
                $this->record['akismet_api_key'],
                "text",
                "100px"
            )
            ."</div>\n"
            ."    <div class='val' style='width:102px'><b>Status:</b> ".$this->_get_akismet_key_status()."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:170px'><b>Bug Tracker</b></div>"
            ."    <div class='val' style='width:200px;'>"
            .draw_form_field(
                "bugs_url",
                $this->record['bugs_url'],
                "selector_listdata",
                "200px",
                '',
                '',
                '',
                '',
                '',
                '',
                'lst_bugtracker_URLs'
            )
            ."</div>\n"
            ."    <div class='lbl txt_r' style='width:50px;'><b>Email</b>&nbsp;</div>\n"
            ."    <div class='val' style='width:155px;'>"
            .draw_form_field(
                "bugs_username",
                $this->record['bugs_username'],
                "text",
                "155px"
            )
            ."</div>\n"
            ."    <div class='lbl txt_r' style='width:50px'><b>Pwd</b>&nbsp;</div>\n"
            ."    <div class='val' style='width:55px;'>"
            .draw_form_field(
                "bugs_password",
                $this->record['bugs_password'],
                "password",
                "45px"
            )
            ."</div>\n"
            ."    <div class='val' style='width:102px'><b>Status:</b> ".$this->_get_bugtracker_status()."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:170px'><b>Piwik ID</b></div>\n"
            ."    <div class='val' style='width:200px;'>"
            .draw_form_field(
                "piwik_id",
                $this->record['piwik_id'],
                "int",
                "100px"
            )
            ."</div>\n"
            ."    <div class='lbl txt_r' style='width:50px'><b>User</b>&nbsp;</div>\n"
            ."    <div class='val' style='width:155px;'>"
            .draw_form_field(
                "piwik_user",
                $this->record['piwik_user'],
                "text",
                "155px"
            )
            ."</div>\n"
            ."    <div class='lbl txt_r' style='width:50px'><b>Token</b>&nbsp;</div>\n"
            ."    <div class='val' style='width:155px;'>"
            .draw_form_field(
                "piwik_token",
                $this->record['piwik_token'],
                "text",
                "155px"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."    <div class='lbl' style='width:170px'><b>Google Analytics "
            ."<a href='http://www.google.com/analytics' rel='external'>Get ID</a></b></div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "google_analytics_key",
                $this->record['google_analytics_key'],
                "text",
                "100px"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            ."  <div class='settings_group'>\n"
            ."    <div class='lbl' style='width:170px'><b>Path to Favicon file</b><br />(16x16px .ico format)</div>\n"
            ."    <div class='val'>"
            .draw_form_field(
                "favicon",
                $this->record['favicon'],
                "server_file",
                "600px"
            )
            ."</div>\n"
            ."    <div class='clr_b'></div>\n"
            ."  </div>\n"
            .($this->_isMASTERADMIN || System::has_feature('multi-language') ?
                 "  <div class='settings_group'>\n"
                ."    <div class='lbl' style='width:170px'><b>Default Language</b></div>\n"
                ."    <div class='val' style='width:150px'>"
                .draw_form_field(
                    "defaultLanguage",
                    $this->record['defaultLanguage'],
                    "selector_listdata",
                    "150px",
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'lst_iso-639-1'
                )
                ."</div>\n"
                ."    <div class='lbl txt_r' style='width:85px'><b>Available</b>&nbsp;</div>\n"
                ."    <div class='val'>"
                .draw_form_field(
                    "languages",
                    $this->record['languages'],
                    "selector_listdata_csv",
                    "360px",
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    'lst_iso-639-1',
                    '30'
                )
                ."</div>\n"
                ."    <div class='clr_b'></div>\n"
                ."  </div>\n"
             :
                ""
            )
            ."</div>\n";
    }

    private function _draw_section_logs()
    {
        if (!$this->_isMASTERADMIN) {
            return;
        }
        $Obj_FS = new FileSystem;
        $dirTree = false;
        if ($this->_get_ID()==SYS_ID) {
            @mkdirs(SYS_LOGS, 0777) or die('Cannot create /logs directory');
            @$dirTree = $Obj_FS->get_dir_tree(SYS_LOGS);
        }
        $this->_html.=
        draw_section_tab_div('logs', $this->_selected_section)
        ."  <div class='settings_group'>\n"
        ."    <div class='lbl'>Files in Log Directory "
        ."<input type=\"button\" value=\"Delete selected\""
        ." onclick=\"geid_set('submode','delete_file');geid('form').submit();\"/>\n"
        ."    </div>\n"
        ."    <div class='clr_b'></div>\n"
        ."    <div>"
        .($dirTree!==false ?
            $Obj_FS->draw_dir_tree($dirTree, 0, true, 'Log Files', true)
         :
            "Sorry - you cannot yet view logs for another system."
        )
        ."    </div>\n"
        ."  </div>"
        ."</div>";
    }


    private function _draw_section_membership_rules()
    {
        if (!$this->_isMASTERADMIN || System::has_feature('Membership-Renewal')) {
            return;
        }
        $this->_html.=
             draw_section_tab_div('membership', $this->_selected_section)
            ."  <div class='settings_group'>\n"
            ."<b>Membership System Enabled: "
            .draw_form_field(
                "membership_rules",
                $this->record['membership_rules'],
                "bool"
            )
            ." "
            ."&nbsp;Membership Expiry: "
            .draw_form_field(
                "membership_expiry_type",
                $this->record['membership_expiry_type'],
                "selector_listdata",
                "200px",
                '',
                '',
                '',
                '',
                '',
                '',
                'lst_membership_expiry_type'
            )
            ."</b><br />\n"
            ."  </div>\n"
            ."<a class=\"iframe\" href=\""
            .BASE_PATH."report/membership_rules_for_system?print=2&amp;selectID=".$this->record['ID']."\""
            ." rel=\"style=width:".($this->_width-10)."px;height:400px;\">Embedded Content</a>\n"
            ."</div>";
    }

    private function _draw_section_notes()
    {
        if (!$this->_isMASTERADMIN) {
            return;
        }
        $this->_html.=
             draw_section_tab_div('notes', $this->_selected_section)
            ."  <div class='settings_group_header'><b>Notes:</b></div>"
            .draw_form_field(
                "notes",
                $this->record['notes'],
                "textarea",
                ($this->_width-10),
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                400
            )
            ."\n"
            ."</div>";
    }

    private function _draw_section_parameters()
    {
        $this->_html.=
             draw_section_tab_div('parameters', $this->_selected_section)
            ."  <div class='settings_group_header'>"
            ."<b>These Parameters apply any page in this site but can be overridden at the layout or page level.</b>"
            ."<br />\n"
            .draw_form_field(
                "component_parameters",
                $this->record['component_parameters'],
                "option_list",
                ($this->_width-10),
                '',
                '',
                '',
                '',
                false,
                '',
                '',
                360
            )
            ."  </div>\n"
            ."</div>";
    }

    private function _draw_section_status()
    {
        $remote_url = get_var('remote_url', 'http://');
        $Obj_System_Health = new System_Health($this->_get_ID());
        $config = $Obj_System_Health->get_config();
        $config2 = false;
        if ($remote_url!="" && $remote_url!="http://") {
            $Obj =      new Remote($remote_url);
            $config2 =  $Obj->get_items('config');
        }
        $this->_html.=
        draw_section_tab_div('status', $this->_selected_section)
        ."  <table cellpadding='2' border='0' cellspacing='0'"
        ." class='admin_containerpanel' width='".($this->_width-10)."'>\n"
        ."    <tr>\n"
        ."      <td style='width:50%'><b>This site</b></td>\n"
        ."      <td style='width:50%'><b>Remote site</b> "
        ."<input class='formField' type=\"text\" name=\"remote_url\" id=\"remote_url\" value=\"".$remote_url."\""
        ." style=\"width: 220px;\""
        ." onkeypress=\"keytest_enter_transfer(event,'go_btn')\" /> "
        ."<input class='formButton' type='button' id=\"go_btn\" value='Go' style='width:25px;'"
        ." onclick=\"geid('save_btn').disabled=1;geid('close_btn').disabled=1;"
        ."geid('save_and_close_btn').disabled=1;geid('go_btn').value='...';geid('go_btn').disabled=1;"
        ."geid('submode').value='connect';geid('form').submit()\"/>"
        ."</td>\n"
        ."    </tr>\n"
        ."    <tr>\n"
        ."      <td>"
        ."<div class='scrollbox fl' style='width: 390px; height: 380px;'>\n"
        .$Obj_System_Health->draw($config, 'local')
        ."</div><div style='float:left;width:20px'></div></td>\n";
        if ($config2!==false) {
            $this->_html.=
             "      <td>"
            ."<div class='scrollbox' style='width: 390px; height: 380px;'>\n"
            .$Obj_System_Health->draw($config2, 'remote')
            ."</div></td>\n";
        }
        $this->_html.=
         "    </tr>\n"
        ."  </table>\n"
        ."</div>\n";
    }

    private function _get_akismet_key_status()
    {
        $status = System::get_item_version('akismet_key_status');
        return
        ($status == 'Pass' ?
         "<span style='color:green'>".$status."</span>"
         :
         "<span style='color:red'>".$status."</span>"
        );
    }

    private function _get_bugtracker_status()
    {
        $status = System::get_item_version('bugtracker_status');
        return
        ($status == 'Pass' ?
         "<span style='color:green'>".$status."</span>"
         :
         "<span style='color:red'>".$status."</span>"
        );
    }

    private function _setup_colour_schemeID()
    {
        $this->_colour_schemeID =       Colour_Scheme::get_match($this->_get_ID());
    }

    private function _setup_section_tabs()
    {
        if ($this->_isMASTERADMIN) {
            $this->_section_tabs_arr =
            array(
                array('ID' => 'general',    'label' => 'General'),
                array('ID' => 'colours',    'label' => 'Colours'),
                array('ID' => 'style',      'label' => 'CSS Style'),
                array('ID' => 'parameters', 'label' => 'Parameters'),
                array('ID' => 'membership', 'label' => 'Membership'),
                array('ID' => 'advanced',   'label' => 'Advanced'),
                array('ID' => 'notes',      'label' => 'Notes'),
                array('ID' => 'logs',       'label' => 'Logs'),
                array('ID' => 'features',   'label' => 'Features'),
                array('ID' => 'status',     'label' => 'Status')
            );
        } else {
            $this->_section_tabs_arr = array();
            $this->_section_tabs_arr[] = array('ID'=>'general','label'=>'General');
            $this->_section_tabs_arr[] = array('ID'=>'colours', 'label'=>'Colours');
            $this->_section_tabs_arr[] = array('ID'=>'style', 'label'=>'Style');
            $this->_section_tabs_arr[] = array('ID'=>'parameters', 'label'=>'Parameters');
            if ($this->_isMASTERADMIN || System::has_feature('Membership-Renewal')) {
                $this->_section_tabs_arr[] = array('ID'=>'membership', 'label'=>'Membership');
            }
            $this->_section_tabs_arr[] = array('ID'=>'status','label'=>'Status');
        }
    }

    public function get_version()
    {
        return VERSION_SYSTEM_EDIT;
    }
}
