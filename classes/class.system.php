<?php
define('VERSION_SYSTEM', '1.0.157');

/*
Version History:
  1.0.157 (2015-01-10)
    1) Now uses OPTION_SEPARATOR constant not option_separator in System::set_parameters_for_instance()
    2) Now PSR-2 Compliant

  (Older version history in class.system.txt)
*/
class System extends Record
{
    const FIELDS = 'ID, archive, textEnglish, debug, debug_no_internet, classes_cs_target, classes_detail, db_cs_target, db_detail, libraries_cs_target, libraries_detail, reports_cs_target, reports_detail, db_custom_tables, db_upgrade_flag, db_version, adminEmail, archiveID, deleted, adminName, akismet_api_key, bounce_email, bugs_password, bugs_username, bugs_url, cal_border, cal_current, cal_current_we, cal_days, cal_event, cal_head, cal_then, cal_then_we, cal_today, colour1, colour2, colour3, colour4, component_parameters, cron_job_heartbeat_last_run, custom_1, custom_2, defaultBgColor, defaultCurrencySuffix, defaultCurrencySymbol, defaultDateFormat, defaultLanguage, defaultLayoutID, defaultTaxZoneID, defaultThemeID, defaultTimeFormat, favicon, features, gatewayID, google_analytics_key, installed_modules, languages, last_user_access, membership_expiry_type, membership_rules, notes, notify_email, notify_triggers, piwik_id, piwik_token, piwik_user, posting_prefix, provider_list, qbwc_AssetAccountRef, qbwc_COGSAccountRef, qbwc_IncomeAccountRef, qbwc_export_orders, qbwc_export_orders_billing_addr, qbwc_export_orders_product_desc, qbwc_export_orders_taxcodes, qbwc_export_people, qbwc_export_products, qbwc_invoice_type, qbwc_user, qbwc_pass, smtp_authenticate, smtp_host, smtp_password, smtp_port, smtp_username, style, system_cancellation_days, system_signup, table_border, table_data, table_header, tax_benefit_1_name, tax_benefit_2_name, tax_benefit_3_name, tax_benefit_4_name, text_heading, timezone, URL, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
    const TABLES = 'action, activity, block_layout, case_tasks, cases, category_assign, colour_scheme, comment, community, community_member, community_membership, component, content_block, custom_form, ecl_tags, field_templates, gateway_settings, gateway_type, geocode_cache, group_assign, group_members, groups, keyword_assign, keywords, language_assign, layout, listdata, listtype, mailidentity, mailqueue, mailqueue_item, mailtemplate, membership_rule, module_credits, navbuttons, navstyle, navsuite, order_items, orders, pages, payment_method, person, poll, poll_choice, postings, product, product_grouping, product_relationship, push_product_assign, qb_config, qb_connection, qb_ident, qb_import, qb_log, qb_notify, qb_queue, qb_recur, qb_ticket, qb_user, registerevent, report, report_columns, report_defaults, report_filter, report_filter_criteria, report_settings, scheduled_task, system, tax_code, tax_regime, tax_rule, tax_zone, theme, widget';
    public static $cache_ID_by_URL_array =  array();
    public static $cache_version = array();
    public static $cache_version_hit = 0;
    public static $cache_version_miss = 0;
    public static $features = false;

    public $child_tables;
    public $colour_schemeID;

    public function __construct($ID = "")
    {
        parent::__construct("system", $ID);
        $this->_set_name_field('textEnglish');
        $this->_set_object_name('Site');
        $this->_set_message_associated('with all Page, Button, Layout, Content Block and Theme data have');
    }

    public function copy($new_name = false, $new_systemID = false, $new_date = true)
    {
        $Obj = new System_Copy($this->_get_ID());
        return $Obj->copy($new_name, $new_systemID, $new_date);
    }

    public function define_URL_params()
    {
        $record = $this->get_record();
        $path_arr =
        explode(
            "/",
            preg_replace(
                "#http://|https://#",
                "",
                trim($record['URL'], "/")
            )
        );
    // Clear out protocol and domain - this lets us join with a leading slash IF more follows (clever!)
        $path_arr[0] = "";
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', implode("/", $path_arr)."/");
        }
        define('POSTING_PREFIX', $record['posting_prefix']);
    }

    public function delete()
    {
        $system_tables = explode(',', str_replace(' ', '', System::TABLES));
        foreach ($system_tables as $table) {
            $sql =
             "DELETE FROM\n"
            ."  `".$table."`\n"
            ."WHERE\n"
            ."  `systemID` IN(".$this->_get_ID().")";
            $this->do_sql_query($sql);
        }
        parent::delete();
        return true;
    }

    public function do_commands()
    {
        global $page_vars;
      // Removed from functions.php in version 1.0.5
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =        get_person_permission("SYSEDITOR");
        $userIsAdmin =      ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
      // All that follows is inline-code:
        if (isset($_REQUEST['command'])) {
            $command = get_var('command');
            switch ($command) {
              // Delete operations on postings:
                case "article_delete":
                case "event_delete":
                case "job_delete":
                case "news_delete":
                case "podcast_delete":
                case "product_delete":
                    $targetID = get_var('targetID');
                    switch ($command) {
                        case "article_delete":
                            $Obj = new Article($targetID);
                            break;
                        case "event_delete":
                            $Obj = new Event($targetID);
                            break;
                        case "job_delete":
                            $Obj = new Job_Posting($targetID);
                            break;
                        case "news_delete":
                            $Obj = new News_Item($targetID);
                            break;
                        case "podcast_delete":
                            $Obj = new Podcast($targetID);
                            break;
                        case "product_delete":
                            $Obj = new Product($targetID);
                            break;
                    }
                    $Obj->try_delete_item();
                    break;
                case "article_toggle_enabled":
                case "event_toggle_enabled":
                case "gallery_album_toggle_enabled":
                case "gallery_image_toggle_enabled":
                case "job_toggle_enabled":
                case "news_toggle_enabled":
                case "podcast_toggle_enabled":
                case "product_toggle_enabled":
                    if (!$userIsAdmin) {
                        return;
                    }
                    $targetID = get_var('targetID');
                    switch ($command) {
                        case "article_toggle_enabled":
                            $Obj = new Article($targetID);
                            break;
                        case "event_toggle_enabled":
                            $Obj = new Event($targetID);
                            break;
                        case "gallery_album_toggle_enabled":
                            $Obj = new Gallery_Album($targetID);
                            break;
                        case "gallery_image_toggle_enabled":
                            $Obj = new Gallery_Image($targetID);
                            break;
                        case "job_toggle_enabled":
                            $Obj = new Job_Posting($targetID);
                            break;
                        case "news_toggle_enabled":
                            $Obj = new News_Item($targetID);
                            break;
                        case "podcast_toggle_enabled":
                            $Obj = new Podcast($targetID);
                            break;
                        case "product_toggle_enabled":
                            $Obj = new Product($targetID);
                            break;
                    }
                    $Obj->set_field('enabled', ($Obj->get_field('enabled') ? 0 : 1));
                    break;
                case "posting_toggle_important":
                    if (!$userIsAdmin) {
                        return;
                    }
                    $targetID = get_var('targetID');
                    $Obj = new Posting($targetID);
                    $Obj->set_field('important', ($Obj->get_field('important') ? 0 : 1));
                    break;
                case "posting_toggle_shared":
                    if (!$userIsAdmin) {
                        return;
                    }
                    $targetID = get_var('targetID');
                    $Obj = new Posting($targetID);
                    $Obj->set_field('permSHARED', ($Obj->get_field('permSHARED') ? 0 : 1));
                    break;
                case "product_toggle_important":
                    if (!$userIsAdmin) {
                        return;
                    }
                    $targetID = get_var('targetID');
                    $Obj = new Product($targetID);
                    $Obj->set_field('important', ($Obj->get_field('important') ? 0 : 1));
                    break;
                case "captcha_img":
                    $Obj = new Captcha;
                    $Obj->getCaptcha();
                    $_SESSION['captcha_valid'] = false;
                    die;
                break;
                case "captcha_test":
                    $Obj = new Captcha;
                    if (isset($_REQUEST['captcha_key']) && $Obj->isKeyRight($_REQUEST['captcha_key'])) {
                        $_SESSION['captcha_valid'] = true;
                    } else {
                        $_SESSION['captcha_valid'] = false;
                    }
                    break;
                case "captcha_status":
                    print (isset($_SESSION['captcha_valid']) && $_SESSION['captcha_valid'] == true ? "Okay" : "bad");
                    break;
              // other operations
                case "customise_colours":
                    Component_Customiser_Button::save();
                    break;
                case "cart":
                    Cart::update_cart();
                    break;
                case "cpt_cancel":
                    $Obj = new ChasePaymentech_Gateway;
                    $Obj->cancel();
                    break;
                case "cpt_notify":
                    $Obj = new ChasePaymentech_Gateway;
                    $Obj->notify();
                    break;
                case "cpt_receipt":
                    $Obj = new ChasePaymentech_Gateway;
                    $Obj->receipt();
                    break;
                case "comment":
                    $Obj = new Comment;
                    $Obj->do_commands();
                    break;
                case "delete_file":
                    if ($isSYSADMIN||$isMASTERADMIN) {
                        $Obj_fs = new FileSystem;
                        foreach ($_REQUEST as $key => $value) {
                            $Obj_fs->delete_dir_entry($key, $value);
                        }
                    }
                    break;
                case "donate":
                    $Obj = new Gateway_Setting;
                    print $Obj->do_donation();
                    die;
                break;
                case "download_custom_form_xml":
                    $Obj = new Custom_Form((isset($_REQUEST['targetID']) ? $_REQUEST['targetID'] : ""));
                    $Obj->xml_download();
                    die;
                break;
                case "download_data":
                    $Obj = new Report($_REQUEST['reportID']);
                    $Obj->download_data($_REQUEST['targetID'], $_REQUEST['targetValue']);
                    die;
                break;
                case "download_media":
                    $Obj = new Media($_REQUEST['targetID']);
                    $Obj->download_media();
                    die;
                break;
                case "download_order_pdf":
                    $ObjReportColumn = new Report_Column($_REQUEST['columnID']);
                    $params = $ObjReportColumn->get_field('reportFieldSpecial');
                    $Obj = new Order($_REQUEST['orderID']);
                    $Obj->download_pdf($params);
                    die;
                break;
                case "download_record_pdf":
                    $ObjRCDPDF = new Report_Column_download_pdf(get_var('columnID'));
                    $ObjRCDPDF->draw(get_var('targetID'));
                    die;
                break;
                case "download_userfile_data":
                    $Obj = new Report($_REQUEST['reportID']);
                    $Obj->download_userfile_data($_REQUEST['targetID'], $_REQUEST['targetValue']);
                    die;
                break;
                case "ecl_tags_get_js_options":
                    $Obj = new ECL_Tag;
                    print $Obj->get_js_options();
                    die;
                break;
                case "email_to_friend":
                    $Obj = new Component_Email_to_friend;
                    $Obj->draw();
                    die;
                break;
                case "empty_cart":
                    Cart::empty_cart();
                    break;
                case "get_bible_verse":
                    $url =
                     "http://www.christnotes.org/syndicate.php?"
                    ."content=dbv&type=js2&tw=auto&"
                    ."tbg=FFFFFF&bw=1&bc=000000&ta=C&tc=000000&"
                    ."tf=Arial&s=18&ty=B&va=L&vc=000000&"
                    ."vf=Arial&vs=12&tt=1&trn=".$_REQUEST['trn'];
                    $file = @stripcslashes(file_get_contents($url));
                    print substr($file, 16, strlen($file)-19);
                    die;
                break;
                case "get_table_structure":
                    global $db;
                    if (isset($_REQUEST['targetValue'])) {
                        $Obj = new Table;
                        print "<h1>".$_SERVER["HTTP_HOST"]." : ".$db." : ".$_REQUEST['targetValue']."</h1>";
                        print "<pre>".$Obj->get_table_create_sql($_REQUEST['targetValue'], false)."</pre>";
                        die;
                    }
                    break;
                case "get_file":
                    if (isset($_REQUEST['ID'])) {
                        $Obj = new FileSystem;
                        $Obj->get_file($_REQUEST['ID']);
                        die;
                    }
                    break;
                case "get_person_for_signin":
                    global $system_vars;
                    $username =       sanitize('html', trim(get_var('username')));
                    $password =       sanitize('html', trim(get_var('password')));
                    $password_enc =   sanitize('html', trim(get_var('password_enc')));
                    if ($password_enc=='') {
                        $password_enc =     ($password ? encrypt(strToLower($password)) : "");
                    }
                    $Obj_User =       new User;
                    $result =         $Obj_User->get_person_for_signin($username, $password_enc);
                    if ($result['status']['code']==200) {
                        $groups = array();
                        foreach ($result['data']['groups'] as $key => $group) {
                            $groups["group_".$group['groupID']] =$group;
                        }
                        $result['data']['groups'] = $groups;
                        unset($result['data']['permissions']);
                    }
                    $Obj = new Array2XML;
                    header('Content-Type: application/xml');
                    print $Obj->convert($result, 'result');
                    die;
                break;
                case "merge_profiles":
                    $Obj = new Person_Merge_Profiles;
                    $Obj->draw();
                    break;
                case "navbutton_delete":
                    if ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN) {
                        $Obj = new Navbutton($_REQUEST['targetID']);
                        $Obj->delete_and_cleanup();
                    }
                    break;
                case "navsuite_seq":
                    if ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN) {
                        $Obj = new Navsuite;
                        $Obj->ajax_set_seq();
                    }
                    break;
                case "order_issue_credit_memo":
                    $Obj = new Order;
                    $Obj->issue_credit_memo($_REQUEST['targetValue']);
                    break;
                case "order_item_refund_flag_set":
                    $Obj = new OrderItem($_REQUEST['targetID']);
                    $Obj->refund_flag_set($_REQUEST['targetValue']);
                    break;
                case "order_item_refund_flag_clear":
                    $Obj = new OrderItem($_REQUEST['targetID']);
                    $Obj->refund_flag_clear($_REQUEST['targetValue']);
                    break;
                case "page_content":
                    $Obj = new Page;
                    $Obj->serve_content();
                    break;
                case "paypal_ipn":
                    PayPal_Gateway::IPNPaymentVerify();
                    break;
                case "podcast_player":
                    $Obj = new Podcast($_REQUEST['targetID']);
                    print $Obj->draw_player();
                    die;
                break;
                case "poll_result":
                    $Obj = new Poll($_REQUEST['targetID']);
                    print $Obj->draw_result();
                    die;
                break;
                case "poll_show":
                    $Obj = new Poll($_REQUEST['targetID']);
                    print $Obj->draw_question();
                    die;
                break;
                case "poll_vote":
                    $Obj = new Poll($_REQUEST['targetID']);
                    $Obj->do_vote($_REQUEST['targetValue']);
                    die;
                break;
                case "print_form_data":
                    $Obj = new Page;
                    $Obj->print_form_data();
                    die;
                break;
                case "rating_submit":
                    $id_arr = explode("_", $_REQUEST['targetID']);
                    if (!count($id_arr)==2) {
                        die('Item not recognised');
                    }
                    $type = $id_arr[0];
                    $ID = trim($id_arr[1], "_");
                    $Obj = false;
                    $prefixed_types = Portal::portal_param_get('path_type_prefixed_types');
                    foreach ($prefixed_types as $objName) {
                        $_obj = new $objName($ID);
                        if ($_obj->_get_path_prefix()==$type) {
                            $Obj = $_obj;
                            break;
                        }
                    }
                    if ($Obj == false) {
                        die('Item not recognised');
                    }
                    print $Obj->draw_ratings_block('rate', $_REQUEST['targetValue']);
                    die;
                break;
                case "recurrence_settings":
                    $Obj = new Event_Recurrence;
                    $Obj->draw();
                    break;
                case "report":
                    $Obj = new Report_Report;
                    $Obj->do_commands();
                    die;
                break;
                case "report_config":
                    $Obj = new Report_Config;
                    print $Obj->draw();
                    die;
                break;
                case "report_filter_seq":
                    $Obj = new Report_Filter;
                    $Obj->ajax_set_seq();
                    break;
                case "set_language":
                    $Obj = new Language;
                    $Obj->set(get_var('targetValue'));
                    break;
                case "set_parameters":
                    $Obj = new Component_Base;
                    $Obj->set_parameters(get_var('targetValue'));
                    break;
                case "ssi":
                    $Obj = new User;
                    return $Obj->single_signin();
                break;
                case "subnav_add":
                    if ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN) {
                        $Obj = new Navbutton($_REQUEST['targetID']);
                        $Obj->subnav_add();
                    }
                    break;
                case "upgrade":
                    if (isset($_REQUEST['version'])) {
                        $Obj = new System(SYS_ID);
                        print $Obj->upgrade($_REQUEST['version'], "test");
                    }
                    break;
                case "version":
                    if ($userIsAdmin) {
                        print
                            "<p><b>".System::get_item_version('system_family')." version ".CODEBASE_VERSION."</b></p>";
                        phpinfo();
                        die;
                    }
                    break;
            }
        }
    }

    public function draw_css_include()
    {
        global $mode,$report_name,$page_vars,$system_vars;
        $editing_system =   ($mode=='details' && $report_name=='system');
        $cs_system =        $this->get_css_checksum(SYS_ID);
        return
             "<link rel=\"stylesheet\" type=\"text/css\""
            ." href=\"".BASE_PATH."css/".System::get_item_version('css')."\" />\n"
            ."<link rel=\"stylesheet\" type=\"text/css\""
            ." href=\"".BASE_PATH."css/labels/".System::get_item_version('css_labels')."\" />\n"
            .(!$editing_system ?
                "<link rel=\"stylesheet\" type=\"text/css\" href=\"".BASE_PATH."css/system/".$cs_system."\" />\n"
             :
                ''
            );
    }

    public function draw_js_include($full = false, $context_adminLevel = 0)
    {
        global $system_vars;
        return
         "<script type=\"text/javascript\""
        ." src=\""
         .($system_vars['debug_no_internet']==1 ?
            BASE_PATH."sysjs/jquery/1.11.0"
          :
            "//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"
         )
        ."\"></script>\r\n"
        ."<script type=\"text/javascript\""
        ." src=\""
        .($system_vars['debug_no_internet']==1 ?
            BASE_PATH."sysjs/jqueryui/1.10.4"
         :
            "//ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/jquery-ui.min.js"
        )
        ."\">"
        ."</script>\r\n"
        ."<script type=\"text/javascript\""
        ." src=\"".BASE_PATH."sysjs/jqueryjson/2.4"."\">"
        ."</script>\r\n"
        ."<script type=\"text/javascript\""
        ." src=\"".BASE_PATH."sysjs/sys/".System::get_item_version('js_functions')."\">"
        ."</script>\r\n"
        .($full || get_userID() ?
             "<script type=\"text/javascript\""
            ." src=\"".BASE_PATH."sysjs/member/".System::get_item_version('js_member')."\">"
            ."</script>\r\n"
         :
            ''
        )
        .($context_adminLevel>0 ?
             "<script type=\"text/javascript\""
            ." src=\"".BASE_PATH."sysjs/context/".$context_adminLevel."/".System::get_item_version('codebase')."\">"
            ."</script>\r\n"
         :
            ''
        );
    }
    public static function draw_popup_layer()
    {
        global $system_vars;
        $system_vars =  get_system_vars(); // needed to test for system features
        $request =      Portal::get_request_path();
        $args =         explode("/", $request);

        $mode =     $args[1];
        switch($mode){
            case "community_member_dashboard":
                $Obj = new Community_Member;
                $tab = (isset($args[4]) ? $args[4]: false);
                $out = $Obj->do_dashboard($tab);
                break;
            case "dashboard":
                $Obj =          new Widget;
                $width =        (int)Widget::$container_width-10;
                $height =       (int)Widget::$container_height-10;
                $out =          $Obj->view_dashboard();
                $out['html'] = convert_safe_to_php(
                    "<div style='width:".$width."px;height:".$height."px;"
                    ."overflow:auto;border-bottom:1px solid #888;padding:4px;'>"
                    .$out['html']
                    ."</div>"
                );
                $out['js'] .=   ";ToolTips.attachBehavior()";
                break;
        }
        $Obj_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
        // so we get an assoc array as output instead of some weird object
        header('Content-Type: application/json');
        print $Obj_json->encode($out);
        die;
    }

    public function draw_remote_config($systems, $componentID)
    {
        $servers = get_var('servers');
        if ($servers=='') {
            $out = "<h3>Systems Overview</h3>";
            $urls = array();
        } else {
            $out =  "<h3>".$systems[$servers]['title']." health check</h3>";
            $urls = $systems[$servers]['urls'];
        }
        $out.=
              "<a class='admin_toolbartable'"
             ." href=\"#\" onclick=\"details('component',".$componentID.",'455','800');return false;\">"
             ."<img class=\"toolbar_icon fl\" src=\"./img/spacer\" alt=\"\" title=\"Edit embedded component\""
             ." style=\"height:16px;width:17px;background-position: -1373px 0px;\" /></a>"
             ."<p>\n"
             ."<select class='formField' id='servers' name='servers'"
             ." onchange=\"geid('btn_reload').disabled=1;geid('form').submit();\">\n"
             ."  <option value=''".($servers==''? " selected='selected'":'').">"
             ."Please select group to monitor"
             ."</option>\n";
        foreach ($systems as $key => $value) {
            $out.= "  <option".($key==$servers ? " selected='selected'" : "").">".$key."</option>\n";
        }
        $out.=
             "</select> "
            ."<input type='submit' value='Reload' class='formButton' id='btn_reload'"
            ." onclick=\"this.disabled=1;geid('form').submit();\" disabled='disabled' /></p><br />";
        $Obj_Ajax = new Ajax;
        for ($i=0; $i<count($urls); $i+=4) {
            $sys_arr = array();
            for ($j=0; $j<4; $j++) {
                if ($j==0) {
                    $out.=
                         "<table cellpadding='0' cellspacing='0' border='0'>"
                        ."  <tr>\n"
                        ."    <td class='va_t'>"
                        .$Obj_Ajax->get_config_rows()
                        ."</td>\n";
                }
                if ($i+$j < count($urls)) {
                    $value =      $urls[$i+$j];
                    $out.=
                        "    <td class='va_t'>".$Obj_Ajax->get_config($value)."</td>\n";
                }
                if ($j==3) {
                    $out.= "</tr></table><br />";
                }
            }
        }
        Page::push_content(
            "javascript_onload",
            "geid('btn_reload').disabled=0;"
        );
        return $out;
    }

    public function draw_visitor_stats()
    {
        global $system_vars;
        $u_arr = explode('/', $system_vars['URL']);
        $url =
             $u_arr[0].'//'.$u_arr[2]
            .'/piwik/'
            .'?module=Login'
            .'&action=logme'
            .'&login='.$system_vars['piwik_user']
            .'&password='.$system_vars['piwik_token']
            .'&idSite='.$system_vars['piwik_id'];
        $js =
            "popWin('".$url."','Piwik','location=0,status=0,scrollbars=1,resizable=1',1000,800,1);";
        Page::push_content("javascript_onload", $js);
        return
             "<p><a rel=\"external\" "
            ."href=\"".str_replace('&', '&amp;', $url)."\""
            ." onclick=\""
            ."popWin(this.href,'Piwik','location=0,status=0,scrollbars=1,resizable=1',1000,800,1);return false;\">"
            ."<b>Click here</b></a> to access Piwik Analytics for this site.</p>";
    }

    public function edit()
    {
        $Obj_System_Edit = new System_Edit($this->_get_ID());
        return $Obj_System_Edit->draw();
    }

    public function export_sql($targetID, $show_fields)
    {
        $Obj = new System_Export($targetID);
        return $Obj->draw($show_fields);
    }

    public function get_css_checksum($ID)
    {
        global $system_vars, $print;
        if ($ID==SYS_ID) {
            $record = $system_vars;
        } else {
            $Obj = new System($ID);
            $record = $Obj->get_record();
        }
        $cs_icons =     System::get_item_version('icons');
        $cs_labels =    System::get_item_version('labels');
        $data =
              ($print=='' && $system_vars['defaultBgColor']!='' ? $system_vars['defaultBgColor'] : "")
             .BASE_PATH.$cs_icons.$cs_labels.$record['text_heading'].$record['table_header']
             .$record['cal_border'].$record['cal_head'].$record['cal_days']
             .$record['cal_current'].$record['cal_current_we'].$record['cal_then'].$record['cal_then_we']
             .$record['cal_today'].$record['cal_event']
             .$record['table_border'].$record['table_data'].$record['table_header']
             .$system_vars['colour1'].$system_vars['colour2'].$system_vars['colour3'].$system_vars['colour4']
             .$system_vars['style'];
        return dechex(crc32($data));
    }

    public function get_config()
    {
        $Obj_System_Health = new System_Health($this->_get_ID());
        return $Obj_System_Health->get_config();
    }

    public function get_display_title($withSystem = false)
    {
        global $page_vars, $system_vars;
        $out = '';
        if ($withSystem==1) {
            $out.=  $system_vars['textEnglish']." &gt; ";
        }
        $out.=     ($page_vars['title']!="" ? $page_vars['title'] : "");
        return $out;
    }

    public function get_gateway($gateway_settingsID = false)
    {
        global $system_vars;
        if (!$gateway_settingsID) {
            $gateway_settingsID =    $system_vars['gatewayID'];
        }
        if ($gateway_settingsID == '1') {
            return false;
        }
        $out = array();
        $Obj = new Gateway_Setting($gateway_settingsID);
        $out['settings'] = $Obj->get_record();
        $Obj = new Gateway_Type($out['settings']['gateway_typeID']);
        $out['type'] = $Obj->get_record();
        return $out;
    }

    public function get_global_date_YYYY_MM($min, $max, $systemIDs_csv = SYS_ID, $isADMIN = false)
    {
        $sql =
             "SELECT\n"
            ."  LEFT(`history_created_date`,7) `YYYY-MM`,\n"
            ."  COUNT(*) `count`\n"
            ."FROM\n"
            ."  `pages`\n"
            ."WHERE\n"
            ."  `history_created_date`>'1000-00-00' AND\n"
            ."  `systemID` IN(".$systemIDs_csv.")\n"
            ."GROUP BY\n"
            ."  LEFT(`history_created_date`,7)\n"
            ."ORDER BY\n"
            ."  LEFT(`history_created_date`,7)\n";
        $page_range = System::get_records_for_sql($sql);
        $sql =
             "SELECT\n"
            ."  LEFT(IF(`type`='event',`effective_date_start`,`date`),7) `YYYY-MM`,\n"
            ."  COUNT(*) `count`\n"
            ."FROM\n"
            ."  `postings`\n"
            ."WHERE\n"
            .($isADMIN ? "" : "  `date` < NOW() AND\n")
            ."  `systemID` IN(".$systemIDs_csv.")\n"
            ."GROUP BY\n"
            ."  `YYYY-MM`\n"
            ."ORDER BY\n"
            ."  `YYYY-MM`\n";
        $posting_range = System::get_records_for_sql($sql);
        $out = array();
        $min_year = substr($min, 0, 4);
        $max_year = substr($max, 0, 4);
        for ($YYYY=$min_year; $YYYY<=$max_year; $YYYY++) {
            for ($MM=1; $MM<=12; $MM++) {
                $YYYYMM = $YYYY.'-'.lead_zero($MM, 2);
                $out[$YYYYMM]= 0;
                foreach ($page_range as $p) {
                    if (isset($p['YYYY-MM']) && $p['YYYY-MM']==$YYYYMM) {
                        $out[$YYYYMM]+= $p['count'];
                    }
                }
                foreach ($posting_range as $p) {
                    if (isset($p['YYYY-MM']) && $p['YYYY-MM']==$YYYYMM) {
                        $out[$YYYYMM]+= $p['count'];
                    }
                }
            }
        }
        return $out;
    }

    public function get_global_date_range($systemIDs_csv = SYS_ID)
    {
        $sql =
         "SELECT\n"
        ."  COALESCE(MIN(DATE(`history_created_date`)),'0000-00-00') `min`,\n"
        ."  COALESCE(MAX(DATE(`history_created_date`)),'0000-00-00') `max`\n"
        ."FROM\n"
        ."  `pages`\n"
        ."WHERE\n"
        ."  `history_created_date`>'1000-00-00' AND\n"
        ."  `systemID` IN(".$systemIDs_csv.")";
        $page_range = System::get_record_for_sql($sql);
        $sql =
         "SELECT\n"
        ."  COALESCE(MIN(DATE(IF(`type`='event',`effective_date_start`,`date`))),'0000-00-00') `min`,\n"
        ."  COALESCE(MAX(DATE(IF(`type`='event',`effective_date_start`,`date`))),'0000-00-00') `max`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."WHERE\n"
        ."  IF(`type`='event',`effective_date_start`,`date`)>'1000-00-00' AND\n"
        ."  `systemID` IN(".$systemIDs_csv.")";
        $posting_range = System::get_record_for_sql($sql);
        $out = array(
        'min' => ($posting_range['min']<$page_range['min'] ? $posting_range['min'] : $page_range['min']),
        'max' => ($posting_range['max']>$page_range['max'] ? $posting_range['max'] : $page_range['max'])
        );
        return $out;
    }

    public static function get_IDs_for_URLs($URLs_csv, $no_cache = false)
    {
        if ($URLs_csv=='') {
            return '';
        }
        $URLs_arr = explode(",", $URLs_csv);
        $URLs_arr_find = array();
        $IDs_arr = array();
        foreach ($URLs_arr as $URL) {
            $URL = trim($URL);
            if (substr($URL, strlen($URL)-1)=='/') {
    // Remove any trailing slash for now:
                $URL = substr($URL, 0, strlen($URL)-1);
            }
            $key = $URL;
            if (isset(System::$cache_ID_by_URL_array[$key]) && !$no_cache) {
                $IDs_arr[] =  System::$cache_ID_by_URL_array[$key];
            } else {
                $URLs_arr_find[] = $URL;
                $URLs_arr_find[] = $URL."/";
            }
        }
        if (count($URLs_arr_find)) {
            $URLs_csv = "'".implode("','", $URLs_arr_find)."'";
            $sql =
             "SELECT\n"
            ."  `ID`,\n"
            ."  `URL`\n"
            ."FROM\n"
            ."  `system`\n"
            ."WHERE\n"
            ."  `URL` IN(".$URLs_csv.")";
    //      z($sql);
            $records = System::get_records_for_sql($sql);
            foreach ($records as $record) {
                $ID =     $record['ID'];
                $URL =    $record['URL'];
                if (substr($URL, strlen($URL)-1)=='/') {
      // Remove any trailing slash for now:
                    $URL = substr($URL, 0, strlen($URL)-1);
                }
                $key = $URL;
                System::$cache_ID_by_URL_array[$key] = $ID;
                $IDs_arr[] = $ID;
            }
        }
        return implode(",", $IDs_arr);
    }

    public static function get_item_version($what = '')
    {
        global $db, $system_vars;
        if (isset(System::$cache_version[$what])) {
            System::$cache_version_hit++;
            return System::$cache_version[$what];
        }
        switch (strToLower($what)) {
            case "akismet_version":
                new Akismet;
                System::$cache_version[$what] = (defined("VERSION_AKISMET") ? VERSION_AKISMET : "?");
                break;
            case "akismet_api_key":
                System::$cache_version[$what] = $system_vars['akismet_api_key'];
                break;
            case "akismet_key_status":
                if ($system_vars['akismet_api_key']=='') {
                    $status = 'No Key';
                } elseif ($system_vars['debug_no_internet']==1) {
                    $status = 'No Web';
                } else {
                    $Obj = new Akismet($system_vars['URL'], $system_vars['akismet_api_key']);
                    $status = $Obj->isKeyValid();
                }
                System::$cache_version[$what] = $status;
                break;
            case "bugtracker_status":
                if (
                    $system_vars['bugs_url']=='' ||
                    $system_vars['bugs_username']=='' ||
                    $system_vars['bugs_password']==''
                ) {
                    $status = 'None';
                } elseif ($system_vars['debug_no_internet']==1) {
                    $status = 'No Web';
                } else {
                    $ObjBugTracker =
                    new BugTracker(
                        $system_vars['bugs_url'],
                        $system_vars['bugs_username'],
                        $system_vars['bugs_password']
                    );
                    $status = $ObjBugTracker->connect();
                    if ($status=='1') {
                        $status = 'Pass';
                    }
                }
                System::$cache_version[$what] = $status;
                break;
            case "build":
                System::$cache_version[$what] = CODEBASE_VERSION.".".$system_vars['db_version'];
                break;
            case "db_cs_actual":
                System::$cache_version[$what] = "";
                $checksums = array();
                $Obj_System_Health = new System_Health($system_vars['ID']);
                $Obj_System_Health->_get_config_tables($checksums);
      //        y($checksums);die;
                foreach ($checksums as $checksum) {
                    if ($checksum['title']=='db_cs_actual') {
                        System::$cache_version[$what] = $checksum['content'];
                        break;
                    }
                }
                break;
            case "db_cs_status":
                System::$cache_version[$what] =
                    (System::get_item_version('db_cs_actual')==System::get_item_version('db_cs_target') ?
                        "Pass"
                     :
                        "Fail"
                    );
                break;
            case "db_cs_target":
                System::$cache_version[$what] = $system_vars['db_cs_target'];
                break;
            case "classes_cs_status":
                System::$cache_version[$what] =
                    (System::get_item_version('classes_cs_actual')==System::get_item_version('classes_cs_target') ?
                        "Pass"
                     :
                        "Fail"
                    );
                break;
            case "classes_cs_actual":
                $_cs_arr = array();
                $Obj = new System_Health($system_vars['ID']);
                $Obj->_get_config_classes($_cs_arr);
                foreach ($_cs_arr as $_cs) {
                    if ($_cs['title'] == 'classes_cs_actual') {
                        System::$cache_version[$what] = ($_cs['content']);
                        break;
                    }
                }
                break;
            case "classes_detail":
                System::$cache_version[$what] = ($system_vars['classes_detail']);
                break;
            case "classes_cs_target":
                System::$cache_version[$what] = ($system_vars['classes_cs_target']);
                break;
            case "ckfinder":
                $dir = "./js/";
                if (!file_exists($dir)) {
                    $dir = "../js/";
                }
                $path =     $dir."/ckfinder/ckfinder.html";
                $html =     @file_get_contents($path);
                $match =    preg_match("/<title>CKFinder ([^<]*)<\/title>/i", $html, $matches);
                $version =  ($match && count($matches)>1 ? $matches[1] : '?');
                System::$cache_version[$what] = $version;
                break;
            case "ckfinder_cs":
                $dir = "./js/";
                if (!file_exists($dir)) {
                    $dir = "../js/";
                }
                $path = $dir."/ckfinder/ckfinder.html";
                System::$cache_version[$what] = FileSystem::get_file_checksum($path);
                break;
            case "ckfinder_config":
                $dir = "./js/";
                if (!file_exists($dir)) {
                    $dir = "../js/";
                }
                $path =     $dir."/ckfinder/config.php";
                $line = trim(FileSystem::get_line($path, 1));
                $match =    preg_match("/define \(\"CKFINDER_VERSION\",\"([^\"]*)\"\);/i", $line, $matches);
                $version =  ($match && count($matches)>1 ? $matches[1] : '?');
                System::$cache_version[$what] = $version;
                break;
            case "ckfinder_config_cs":
                $dir = "./js/";
                if (!file_exists($dir)) {
                    $dir = "../js/";
                }
                $path = $dir."/ckfinder/config.php";
                System::$cache_version[$what] = FileSystem::get_file_checksum($path);
                break;
            case "codebase":
                return CODEBASE_VERSION;
            break;
            case "css":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_STYLE."default.css"), 3));
                break;
            case "css_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_STYLE."default.css");
                break;
            case "css_breadcrumbs":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_STYLE."breadcrumbs.css"), 3));
                break;
            case "css_breadcrumbs_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_STYLE."breadcrumbs.css");
                break;
            case "css_community":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_STYLE."community.css"), 3));
                break;
            case "css_community_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_STYLE."community.css");
                break;
            case "css_labels":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_STYLE."labels.css"), 3));
                break;
            case "css_labels_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_STYLE."labels.css");
                break;
            case "css_pie":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_STYLE."pie.htc"), 3));
                break;
            case "css_pie_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_STYLE."pie.htc");
                break;
            case "css_spectrum":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_STYLE."spectrum.min.css"), 3));
                break;
            case "css_spectrum_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_STYLE."spectrum.min.css");
                break;
            case "custom":
                System::$cache_version[$what] =
                    (defined('CUSTOM_VERSION') ?
                        CUSTOM_VERSION.(defined('TREB_VERSION') ? ", TREB: ".TREB_VERSION : "")
                    :
                        ""
                    );
                break;
            case "db_connect":
                return DB_CONNECT;
            break;
            case "db_connect_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_SHARED."/db_connect.php");
                break;
            case "db_name":
                System::$cache_version[$what] =
                    $db;
                break;
            case "db_detail":
                System::$cache_version[$what] =
                    $system_vars['db_detail'];
                break;
            case "db_cstarget":
                System::$cache_version[$what] =
                    $system_vars['db_cstarget'];
                break;
            case "db_version":
                System::$cache_version[$what] =
                    $system_vars['db_version'];
                break;
            case "document_root":
                System::$cache_version[$what] =
                    $_SERVER["DOCUMENT_ROOT"];
                break;
            case "fedex_rate":
                System::$cache_version[$what] =
                    FileSystem::get_file_version(SYS_SHARED."/fedex/rate.php", 1);
                break;
            case "fedex_rate_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_SHARED."/fedex/rate.php");
                break;
            case "functions":
                System::$cache_version[$what] =
                    FileSystem::get_file_version(SYS_SHARED."/functions.php", 1);
                break;
            case "functions_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_SHARED."/functions.php");
                break;
            case "getid3":
                System::$cache_version[$what] =
                    FileSystem::get_file_version(SYS_SHARED."/getid3/getid3.php", 1);
                break;
            case "getid3_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_SHARED."/getid3/getid3.php");
                break;
            case "http_software":
                System::$cache_version[$what] =
                    $_SERVER["SERVER_SOFTWARE"];
                break;
            case "icons":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_IMAGES."icons.gif");
                break;
            case "icons_big":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_IMAGES."icons-big.gif");
                break;
            case "img":
                System::$cache_version[$what] =
                    FileSystem::get_file_version(SYS_SHARED."img.php", 1);
                break;
            case "img_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_SHARED."img.php");
                break;
            case "js_cke":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/ckeditor.js"), 3));
                break;
            case "js_cke_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/ckeditor.js");
                break;
            case "js_cke_config":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/config.js"), 3));
                break;
            case "js_cke_config_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/config.js");
                break;
            case "js_cke_plugin_audio":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/plugins/audio/plugin.js"), 3));
                break;
            case "js_cke_plugin_audio_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/plugins/audio/plugin.js");
                break;
            case "js_cke_plugin_ecl":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/plugins/ecl/plugin.js"), 3));
                break;
            case "js_cke_plugin_ecl_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/plugins/ecl/plugin.js");
                break;
            case "js_cke_plugin_more":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/plugins/more/plugin.js"), 3));
                break;
            case "js_cke_plugin_more_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/plugins/more/plugin.js");
                break;
            case "js_cke_plugin_video":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/plugins/video/plugin.js"), 3));
                break;
            case "js_cke_plugin_video_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/plugins/video/plugin.js");
                break;
            case "js_cke_plugin_youtube":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/plugins/youtube/plugin.js"), 3));
                break;
            case "js_cke_plugin_youtube_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/plugins/youtube/plugin.js");
                break;
            case "js_cke_plugin_zonebreak":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ckeditor/plugins/zonebreak/plugin.js"), 3));
                break;
            case "js_cke_plugin_zonebreak_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ckeditor/plugins/zonebreak/plugin.js");
                break;
            case "js_ecc":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."ecc.js"), 3));
                break;
            case "js_ecc_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."ecc.js");
                break;
            case "js_functions":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."functions.js"), 3));
                break;
            case "js_functions_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."functions.js");
                break;
            case "js_jdplayer":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."jdplayer/mediaelement-and-player.min.js"), 3));
                break;
            case "js_jdplayer_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."jdplayer/mediaelement-and-player.min.js");
                break;
            case "js_jquery":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."jquery.min.js"), 3));
                break;
            case "js_jquery_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."jquery.min.js");
                break;
            case "js_jquery_ui":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."jquery-ui.min.js"), 3));
                break;
            case "js_jquery_ui_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."jquery-ui.min.js");
                break;
            case "js_member":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."member.js"), 3));
                break;
            case "js_member_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."member.js");
                break;
            case "js_rssreader_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."rss_reader.js");
                break;
            case "js_rssreader_version":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."rss_reader.js"), 3));
                break;
            case "js_spectrum_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."spectrum.min.js");
                break;
            case "js_spectrum_version":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."spectrum.min.js"), 3));
                break;
            case "js_treeview_cs":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_JS."treeview.js");
                break;
            case "js_treeview_version":
                System::$cache_version[$what] =
                    trim(substr(FileSystem::get_line(SYS_JS."treeview.js"), 3));
                break;
            case "labels":
                System::$cache_version[$what] =
                    FileSystem::get_file_checksum(SYS_IMAGES."labels.gif");
                break;
            case "libraries_cs_actual":
                $Obj_System_Health = new System_Health($system_vars['ID']);
                $lib_arr = $Obj_System_Health->_get_config_libraries_detail_array();
                System::$cache_version[$what] = dechex(crc32(implode(',', $lib_arr)));
                break;
            case "libraries_cs_status":
                System::$cache_version[$what] =
                    (System::get_item_version('libraries_cs_actual')==System::get_item_version('libraries_cs_target') ?
                        'Pass'
                     :
                        'Fail'
                    );
                break;
            case "libraries_cs_target":
                System::$cache_version[$what] =
                    $system_vars['libraries_cs_target'];
                break;
            case "libraries_detail":
                System::$cache_version[$what] =
                    $system_vars['libraries_detail'];
                break;
            case "mysql":
                $sql =      "SELECT VERSION()";
                $Obj = new System;
                System::$cache_version[$what] =
                    trim($Obj->get_field_for_sql($sql));
                break;
            case "php":
                System::$cache_version[$what] =
                    phpversion();
                break;
            case "reports_cs_target":
                System::$cache_version[$what] =
                    $system_vars['reports_cs_target'];
                break;
            case "reports_detail":
                System::$cache_version[$what] =
                    $system_vars['reports_detail'];
                break;
            case "server_name":
                System::$cache_version[$what] =
                    $_SERVER["SERVER_NAME"];
                break;
            case "system":
                System::$cache_version[$what] =
                    (defined("SYSTEM_VERSION") ? SYSTEM_VERSION : "?");
                break;
            case "system_family":
                System::$cache_version[$what] =
                    SYSTEM_FAMILY;
                break;
            case "system_family_url":
                System::$cache_version[$what] =
                    SYSTEM_FAMILY_URL;
                break;
            default:
                System::$cache_version[$what] =
                    "?";
                break;
        }
        System::$cache_version_miss++;
        return System::$cache_version[$what];
    }

    public function get_last_loggedin_access()
    {
        $last_user_access = unserialize($this->get_field('last_user_access'));
        if (isset($last_user_access['personID'])) {
            $Obj_Person = new Person($last_user_access['personID']);
            return array(
            'PUsername' => $Obj_Person->get_field('PUsername'),
            'history_datetime' => $last_user_access['history_datetime']
            );
        }
    }

    public function get_path()
    {
        $self = substr($_SERVER["PHP_SELF"], 0, strrpos($_SERVER["PHP_SELF"], "/"));
        $url = "http://".$_SERVER["HTTP_HOST"].$self.(substr($self, strlen($self)-1, 1)!='/' ? '/' : '');
        return $url;
    }

    public function get_selector_sql()
    {
        return
         "SELECT\n"
        ."  `ID` AS `value`,\n"
        ."  UPPER(`textEnglish`) AS `text`,\n"
        ."  IF(`ID`=1,'e0e0ff',IF(`ID`=1,'e0e0ff',IF(`ID`=SYS_ID,'c0ffc0','ffe0e0'))) AS `color_background`\n"
        ."FROM\n"
        ."  `system`\n"
        ."ORDER BY\n"
        ."  `ID`!=1,`text`\n";
    }

    public function handle_report_copy(&$newID, &$msg, &$msg_tooltip, $name)
    {
        return parent::try_copy($newID, $msg, $msg_tooltip, $name, true);
    }

    public static function has_feature($feature)
    {
        if (!System::$features) {
            System::$features = explode(",", str_replace(" ", "", $GLOBALS['system_vars']['features']));
        }
        return in_array($feature, System::$features);
    }

    public function set_parameters_for_instance($instance, $data)
    {
        $old_parameter_csv =    $this->get_field('component_parameters');
        $old_parameters_arr =   explode(OPTION_SEPARATOR, $old_parameter_csv);
        $new_parameters_arr =   array();
        foreach ($old_parameters_arr as $parameter) {
            $pair = explode("=", $parameter);
            $included = false;
            foreach ($data as $key => $value) {
                if (strToUpper(trim($instance.":".$key))==strToUpper(trim($pair[0]))) {
                    $included = true;
                }
            }
            if (!$included) {
                $new_parameters_arr[] = $parameter;
            }
        }
        foreach ($data as $key => $value) {
            $new_parameters_arr[] = $instance.":".$key."=".$value;
        }
        $new_parameters_csv = implode(OPTION_SEPARATOR, $new_parameters_arr);
        $this->set_field('component_parameters', $new_parameters_csv);
    }

    public function update_posting_prefix($posting_prefix)
    {
        $Obj = new Posting;
        $sql =
         "SELECT\n"
        ."  `postings`.`ID`,\n"
        ."  `postings`.`type`,\n"
        ."  `postings`.`name`,\n"
        ."  `postings`.`date`,\n"
        ."  `system`.`posting_prefix`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."INNER JOIN `system` ON\n"
        ."  `postings`.`systemID` = `system`.`ID`\n"
        ."WHERE\n"
        ."  `postings`.`systemID` IN(".$this->_get_ID().")\n"
        ."ORDER BY\n"
        ."  `postings`.`systemID`,  `postings`.`date`, `postings`.`name`";
        $postings = $Obj->get_records_for_sql($sql);
        foreach ($postings as $posting) {
            $posting['systemID'] = SYS_ID; // con-trick to force nice URLs without system URL prefix
            $Obj->_set_ID($posting['ID']);
            $path = $Obj->get_URL($posting);
            $Obj->set_field('path', "/".$path);
        }
        return count($postings);
    }

    public function upgrade($target_buildID)
    {
        $record =           $this->get_record();
        $start_buildID =    System::get_item_version('build');
        $check =            $this->upgrade_check($start_buildID, $target_buildID);
        switch (strtolower($check)) {
            case "redundant":
            case "regression":
            case "unsafe":
                return $check." - $start_buildID to $target_buildID";
            break;
        }
        return $this->upgrade_execute($target_buildID);
        return $result;
    }

    public function upgrade_check($start_buildID, $target_buildID)
    {
        $start_arr =    explode(".", trim($start_buildID));
        $end_arr =      explode(".", trim($target_buildID));
        while (count($start_arr)>count($end_arr)) {
            array_pop($start_arr);
        }
        if ((int)$start_arr[count($start_arr)-1]+1 == (int)$end_arr[count($end_arr)-1]) {
            return "Safe"; // build version one higher
        }
        if (implode(".", $start_arr) == implode(".", $end_arr)) {
            return "Redundant"; // buuild version identical
        }
        if ((int)$start_arr[count($start_arr)-1] > (int)$end_arr[count($end_arr)-1]) {
            return "Regression"; // build version less
        }
        if ((int)$start_arr[count($start_arr)-2]+1 == (int)$end_arr[count($end_arr)-2]) {
            return "Safe"; // db build version same but code build one bigger
        }
        return "Unsafe";
    }

    public function upgrade_execute($target_buildID)
    {
        $Obj = new gwSocket('');
        $path = SYS_UPGRADE_URL.'?page=upgrade&mode=list&version='.$target_buildID;
        $Obj->getUrl($path);
  //    print_r ($Obj->headers);
        return $Obj->page;
    }

    public function get_version()
    {
        return VERSION_SYSTEM;
    }
}
