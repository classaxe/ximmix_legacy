<?php
define('VERSION_PAGE_EDIT', '1.0.16');
/*
Version History:
  1.0.16 (2015-01-03)
    1) Now uses OPTION_SEPARATOR constant not option_separator in Page_Edit::draw() for saving
    2) Removed 'yellow fever' correction cde - not needed with new CK Editor code
    3) Now PSR-2 Compliant

  (Older version history in class.page_edit.txt)
*/
class Page_Edit extends Page
{
    public function draw()
    {
        global $system_vars;
        global $selectID,$targetID;
        global $mode, $submode, $report_name;
        global $systemID, $page_name, $page, $path_extender, $title, $subtitle, $content, $keywords;
        global $componentID_pre, $componentID_post, $component_parameters, $style, $layoutID, $themeID;
        global $navsuite1ID, $navsuite2ID, $navsuite3ID, $groups_assign, $memberID, $parentID, $password;
        global $permPUBLIC, $permSYSLOGON, $permSYSMEMBER, $locked, $comments_allow, $comments_count;
        global $meta_keywords, $meta_description, $include_title_heading, $ratings_allow;
        global $selected_section, $bulk_update;
        $bulk_update = (isset($bulk_update) ? $bulk_update : 0);
        if ($bulk_update && $targetID && !$this->_get_ID()) {
            $this->_set_ID($targetID);
        }
        $ID = $this->_get_ID();
        $group_assign_csv = $this->get_field('group_assign_csv');
        $isMASTERADMIN =    get_person_permission("MASTERADMIN", $group_assign_csv);
        $isSYSADMIN =       get_person_permission("SYSADMIN", $group_assign_csv);
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER", $group_assign_csv);
        $isSYSEDITOR =      get_person_permission("SYSEDITOR", $group_assign_csv);
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isAPPROVER = (
            $isMASTERADMIN ||
            $isSYSADMIN ||
            $isSYSAPPROVER
        );
        $isEDITOR = ($isAPPROVER || $isSYSEDITOR);
        $canAdd = $isEDITOR;
        $page_allow_buttonsuite =       System::has_feature('page-allow-buttonsuite-override') || $isMASTERADMIN;
        $page_allow_css =               System::has_feature('page_allow_css') || $isMASTERADMIN;
        $page_allow_password =          System::has_feature('password-protection') || $isMASTERADMIN;
        $page_allow_subtitle =          System::has_feature('page_allow_subtitle') || $isMASTERADMIN;
        $page_allow_memberID =
            System::has_feature('module-community') && ($isAPPROVER || $isSYSADMIN || $isMASTERADMIN);
        $page_no_default_content =      System::has_feature('page_no_default_content');
        $page_default_all_user_perms =  System::has_feature('Page-default-all-user-perms');
        $page_include_title_heading =   System::has_feature('page_default_title_heading');
    // Note: use $page_name for page as $page gets overwritten by goto switching.
        $msg = "";
        $ObjReport = new Report();
        $ObjReport->_set_ID($ObjReport->get_ID_by_name($report_name));
        switch ($submode) {
            case 'save':
            case 'save_and_new':
            case 'save_and_close':
                $systemID = (!$isMASTERADMIN || $systemID=="" ? SYS_ID : $systemID);
              // ******************************
              // * Pre-operation pase Actions *
              // ******************************
                if ($ID!="") {
                    global $msg;
                    $ObjReport->actions_execute('report_update_pre', $this->_get_table_name(), 'Page', $ID, $_POST);
                    $action = 'update';
                } else {
                    $action = 'insert';
                }
                $Obj_Lang = new Language;
                $content =  $Obj_Lang->prepare_field('content');
                $data =     array();
                $Obj_RC = new Report_Column();
                $Obj_RC->bulk_update($data, $bulk_update, 'systemID', $systemID);
                $Obj_RC->bulk_update($data, $bulk_update, 'page', $page_name);
                $Obj_RC->bulk_update($data, $bulk_update, 'componentID_pre', $componentID_pre);
                $Obj_RC->bulk_update($data, $bulk_update, 'componentID_post', $componentID_post);
                $Obj_RC->bulk_update(
                    $data,
                    $bulk_update,
                    'component_parameters',
                    implode(OPTION_SEPARATOR, explode("\r\n", $component_parameters))
                );
                $Obj_RC->bulk_update($data, $bulk_update, 'content', $content);
                $Obj_RC->bulk_update(
                    $data,
                    $bulk_update,
                    'content_text',
                    preg_replace("/\[ECL\]([^\[])*(\[\/ECL\])*/", "", strip_tags($content))
                );
                $Obj_RC->bulk_update($data, $bulk_update, 'meta_keywords', $meta_keywords);
                $Obj_RC->bulk_update($data, $bulk_update, 'meta_description', $meta_description);
                if ($page_allow_buttonsuite) {
                    $Obj_RC->bulk_update($data, $bulk_update, 'navsuite1ID', $navsuite1ID);
                    $Obj_RC->bulk_update($data, $bulk_update, 'navsuite2ID', $navsuite2ID);
                    $Obj_RC->bulk_update($data, $bulk_update, 'navsuite3ID', $navsuite3ID);
                }
                $Obj_RC->bulk_update($data, $bulk_update, 'layoutID', $layoutID);
                if ($page_allow_subtitle) {
                    $Obj_RC->bulk_update($data, $bulk_update, 'subtitle', $subtitle);
                }
                $Obj_RC->bulk_update($data, $bulk_update, 'themeID', $themeID);
                $Obj_RC->bulk_update($data, $bulk_update, 'title', $title);
                $Obj_RC->bulk_update($data, $bulk_update, 'include_title_heading', $include_title_heading);
                if ($page_allow_css) {
                    $Obj_RC->bulk_update($data, $bulk_update, 'style', $style);
                }
                if ($isAPPROVER) {
                    $Obj_RC->bulk_update($data, $bulk_update, 'permPUBLIC', $permPUBLIC);
                    $Obj_RC->bulk_update($data, $bulk_update, 'permSYSLOGON', $permSYSLOGON);
                    $Obj_RC->bulk_update($data, $bulk_update, 'permSYSMEMBER', $permSYSMEMBER);
                    if ($page_allow_password) {
                        $Obj_RC->bulk_update($data, $bulk_update, 'password', $password);
                    }
                    if ($isMASTERADMIN || System::has_feature('Comments')) {
                        $Obj_RC->bulk_update($data, $bulk_update, 'comments_allow', $comments_allow);
                    }
                    if ($isMASTERADMIN || System::has_feature('Ratings')) {
                        $Obj_RC->bulk_update($data, $bulk_update, 'ratings_allow', $ratings_allow);
                    }
                }
                if ($isMASTERADMIN) {
                    $Obj_RC->bulk_update($data, $bulk_update, 'locked', $locked);
                }
                if ($isSYSADMIN || $isMASTERADMIN) {
                    $Obj_RC->bulk_update($data, $bulk_update, 'path_extender', $path_extender);
                }
                $Obj_RC->bulk_update($data, $bulk_update, 'parentID', $parentID);
                if ($page_allow_memberID) {
                    $Obj_RC->bulk_update($data, $bulk_update, 'memberID', $memberID);
                }
                $ID = $this->update($data);
                if ($ID===false) {
                    if (Record::get_last_db_error_msg_generic()=='DUPLICATE_ENTRY') {
                        $msg =
                             "<b>Error:</b> A page already exists with the name <b>".$page_name."</b>"
                            ." and the same parent on this site. Please use a different name or parent.";
                        $ID =   "";
                        $page = $page_name;
                    }
                } else {
                    $ObjReport->actions_execute(
                        (isset($action) && $action=="insert" ?
                            'report_insert_post'
                         :
                            'report_update_post'
                        ),
                        $this->_get_table_name(),
                        'Page',
                        $ID,
                        $_POST
                    );
                    if (!$bulk_update) {
                        if ($isAPPROVER) {
                            $this->_set_ID($ID);
                            $this->group_assign($groups_assign);
                        }
                        $keywords_arr = explode(", ", $keywords);
                        sort($keywords_arr);
                        $this->keyword_assign(implode(", ", $keywords_arr));
                    }
                }
                break;
        }
        $out =    "";
        $js = Report_Form::_get_js_form_code(
            $mode,
            $submode,
            $ID,
            $msg,
            $report_name,
            $selectID,
            (isset($action) ? $action : false)
        );
        if ($js!='') {
            Page::push_content('javascript', $js);
        }
        if ($submode=='save_and_close' && $msg=='') {
            return "";
        }
        $height = 457;
        $width = 840;
        if ($ID!="" && !$bulk_update) {
            $this->_set_ID($ID);
            $row =                    $this->get_record();
            $systemID =               $row['systemID'];
            $page_name =              $row['page'];
            $include_title_heading =  $row['include_title_heading'];
            $layoutID =               $row['layoutID'];
            $comments_allow =         $row['comments_allow'];
            $componentID_pre =        $row['componentID_pre'];
            $componentID_post =       $row['componentID_post'];
            $component_parameters =   $row['component_parameters'];
            $groups_assign =          $this->get_group_assign_csv();
            $parentID =               $row['parentID'];
            $password =               $row['password'];
            $path_extender =          $row['path_extender'];
            $permPUBLIC =             $row['permPUBLIC'];
            $permSYSLOGON =           $row['permSYSLOGON'];
            $permSYSMEMBER =          $row['permSYSMEMBER'];
            $style =                  $row['style'];
            $subtitle =               $row['subtitle'];
            $title =                  $row['title'];
            $themeID =                $row['themeID'];
            $locked =                 $row['locked'];
            $content =                $row['content'];
            $keywords =               $row['keywords'];
            $meta_description =       $row['meta_description'];
            $meta_keywords =          $row['meta_keywords'];
            $navsuite1ID =            $row['navsuite1ID'];
            $navsuite2ID =            $row['navsuite2ID'];
            $navsuite3ID =            $row['navsuite3ID'];
            $ratings_allow =          $row['ratings_allow'];
        } else {
            $comments_allow =         'none';
            $navsuite1ID =            0; // Set to use template default
            $navsuite2ID =            0;
            $navsuite3ID =            0;
            $permPUBLIC =             ($page_default_all_user_perms ? 1 : 0);
            $permSYSLOGON =           ($page_default_all_user_perms ? 1 : 0);
            $permSYSMEMBER =          ($page_default_all_user_perms ? 1 : 0);
            $include_title_heading =  $page_include_title_heading;
            $systemID  =              SYS_ID;
            $page_name =
                ($page!='' || $bulk_update ? $page : $this->get_unique_name('new page', $systemID));
            $ratings_allow =          'none';
            $themeID =                1; // Set to use system default
            $title = get_title_for_path($page_name);
            $content = ($page_no_default_content ?
                $content
            :
                 ($page_include_title_heading ? "" : "<h1>Main Heading</h1>\n")
                ."<p>This is where you add content for the new page.\n"
                ."<ul>\n"
                ."  <li>Set <b><span style='border:#808080 1px solid; background-color:#c0c0f0; padding:2px;'>"
                ."Permissions</span></b> to decide who has access to this page otherwise no-one will see it,"
                ." assuming your account allows you to do this.</li>\n"
                ."  <li><b>Don't change the font size</b> in this editor panel if you want to allow visitors to"
                ." resize text using the text resizer "
                ."<img class='toolbar_icon' src='".BASE_PATH."img/spacer' alt='Text Resizer icon' "
                ."style='height:16px;width:16px;background-position: -861px 0px;' />.</li>\n"
                ."<li>If you want to make a heading, select the appropriate <b>Heading</b>"
                ." level in the Format dropdown box instead.<br />"
                ."Search engines rank sites that use headings correctly higher than those which simply vary text"
                ." size to indicate headings.</li>\n"
                ."</ul>"
            );
        }
        $formSelectorLayoutSQL =        Layout::get_selector_sql();
        $formSelectorThemeSQL =         Theme::get_selector_sql();
        $formSelectorComponentSQL =     Component::get_selector_sql();
        $formSelectorNavsuiteSQL =      Navsuite::get_selector_sql(true);
        $formSelectorGroupSQL =         Group_Assign::get_selector_sql();
        $formSelectorParentIDSQL =      Page::get_selector_sql_parents($ID);
        if ($isMASTERADMIN) {
            $formSelectorSystemIDSQL =    System::get_selector_sql();
        }
        if ($page_allow_memberID) {
            $formSelectorMemberIDSQL =    Community::get_selector_sql();
        }
        $Obj_RC = new Report_Column;
        $column = $Obj_RC->get_column_for_report($report_name, 'content');
        $toolbarSet = $column['formFieldSpecial'];
        $section_tabs_arr =     array();
        $section_tabs_arr[] =   array('ID'=>'general','label'=>'General','width'=>100);
        $section_tabs_arr[] =   array('ID'=>'publish','label'=>'Publish','width'=>100);
        if (System::has_feature('page_allow_css') || $isMASTERADMIN) {
            $section_tabs_arr[] =   array('ID'=>'style',  'label'=>'CSS Style','width'=>100);
        }
        $Obj_System = new System($systemID);
        if ($Obj_System->has_feature('Keywords') || $isMASTERADMIN) {
            $section_tabs_arr[] =   array('ID'=>'keywords',  'label'=>'Keywords','width'=>100);
        }
        $section_tabs_arr[] =   array('ID'=>'advanced',  'label'=>'Advanced','width'=>100);
        $section_tabs_arr[] =   array('ID'=>'meta_data',  'label'=>'Meta Data','width'=>100);

        if (!isset($selected_section)|| $selected_section=='') {
            $selected_section=$section_tabs_arr[0]['ID'];
        }


        $out.=
        "<table class='minimal'>\n"
        ."  <tr>\n"
        ."    <td>"
        .draw_form_field('ID', $ID, 'hidden')
        ."<table class='minimal'>\n"
        ."      <tr>\n"
        ."        <td>".draw_form_header("Edit Page", "./?page=_help_admin_pages", 0)
        ."        <table class='minimal'>\n"
        ."          <tr>\n"
        ."            <td valign='top' class='admin_containertable'><table class='minimal'>\n"
        .($msg!="" ? "<tr><td><font color='#ff0000' size='2'>".$msg."</font></td></tr>" : "")
        ."              <tr>\n"
        ."                <td><table cellpadding='1' border='0' cellspacing='0'"
        ." class='auto_report admin_containerpanel'>\n"
        .($isMASTERADMIN ?
             "               <tr><td class='nowrap'><div class='lbl' style='width: 60px;'>"
            ."<label for='systemID'>Site</label></div></td>\n"
            ."                    <td>"
            .draw_form_field(
                "systemID",
                $systemID,
                "selector",
                "760",
                $formSelectorSystemIDSQL,
                '',
                '',
                '',
                $bulk_update
            )
            ."  </td></tr>\n"
        :
            ""
        )
        ."                  <tr>\n"
        ."                    <td class='nowrap'><div class='lbl' style='width: 60px;'>"
        ."<label for='title'>Title</label></div></td>\n"
        ."                    <td>"
        ."<div class='fl'>".draw_form_field("title", $title, "text", 270, '', '', '', '', $bulk_update)."</div>"
        ."<div class='lbl fl txt_r' style='width:40px;padding-right:5px'>"
        ."<label for='include_title_heading'>Show</label></div>"
        ."<div class='fl' style='width:15px'>"
        .draw_form_field("include_title_heading", $include_title_heading, "bool", 0, '', '', '', '', $bulk_update)
        ."</div>\n"
        ."<div class='lbl fl' style='width: 60px;'><div class='lbl'><label for='page_name'>Name</label></div></div>\n"
        ."<div class='fl' style='width: 350px'>\n"
        .(!$bulk_update ?
          draw_form_field("page_name", $page_name, "posting_name_unprefixed", "345", '', '', '', '', $bulk_update)
        : "<i>(Cannot set <b>Name</b> in Bulk Update mode)</i>"
        )
        ."  </div></td>\n"
        ."                  </tr>\n"
        ."  <tr>\n"
        ."    <td><div class='lbl'><label for='parentID' style='width: 60px;'>Parent</label></div></td>"
        ."    <td>"
        .draw_form_field("parentID", $parentID, "selector", "760", $formSelectorParentIDSQL, '', '', '', $bulk_update)
        ."</td>\n"
        ."  </tr>\n"
        ."                  <tr>\n"
        ."                    <td class='nowrap'><div class='lbl' style='width: 60px;'>"
        ."<label for='layoutID'>Layout</label></div></td>\n"
        ."                    <td>\n"
        .($Obj_System->has_feature('Themes') || $isMASTERADMIN ?
         "<div class='fl' style='width: 350px;'>\n"
        .draw_form_field("layoutID", $layoutID, "selector", "345", $formSelectorLayoutSQL, '', '', '', $bulk_update)
        ."  </div>\n"
        ."<div class='lbl fl' style='width: 55px;'><label for='themeID'>Theme</label></div>\n"
        ."<div class='fl' style='width: 350px'>\n"
        .draw_form_field("themeID", $themeID, "selector", "345", $formSelectorThemeSQL, '', '', '', $bulk_update)
        ."</div>\n"
        :
         "<div class='fl'>\n"
        .draw_form_field("layoutID", $layoutID, "selector", "760", $formSelectorLayoutSQL, '', '', '', $bulk_update)
        .draw_form_field("themeID", $system_vars['defaultThemeID'], 'hidden')
        ."  </div>\n"
        )
        ."</td>\n"
        ."                  </tr>\n"
        ."                </table></td>\n"
        ."              </tr>\n"
        ."              <tr>\n"
        ."		          <td><img src='".BASE_PATH."img/spacer' height='5' class='b' alt=''/></td>\n"
        ."              </tr>\n"
        ."              <tr>\n"
        ."                <td>"
        .HTML::draw_section_tabs($section_tabs_arr, 'page', $selected_section)
        ."</td>\n"
        ."              </tr>\n"
        ."              <tr>\n"
        ."                <td>"
        ."<table cellpadding='2' border='0' cellspacing='0' class='auto_report admin_containerpanel'>\n"
        ."                  <tr>\n"
        ."                    <td>"

  // General
        .draw_section_tab_div('general', $selected_section)
        ."<div style='height:".$height."px;width:".$width."px;'>"
        .($page_allow_subtitle ?
           "<div class='lbl' style='width:60px;'><label for='subtitle'>Subtitle </label></div>"
          ."<div class='fl'>"
          .draw_form_field("subtitle", $subtitle, "text", 760, '', '', '', '', $bulk_update)."</div>"
          ."<div class='clr_b'></div>"
        : ""
        )
        ."<div class='lbl'>Content</div><br />\n"
        ."<div class='fl'>"
        .draw_form_field(
            "content",
            $content,
            "html_multi_language",
            840,
            '',
            '',
            '',
            '',
            $bulk_update,
            '',
            $toolbarSet,
            ($page_allow_subtitle ? 380 : 400)
        )
        ."</div>"
        ."</div>"
        ."</div>"

  // Publish
        .draw_section_tab_div('publish', $selected_section)
        ."<div style='height:".$height."px;width:".$width."px;'>"
        .($isMASTERADMIN || (System::has_feature('Comments') && $isAPPROVER) ?
             "<div class='lbl' style='width:220px;'>Allow comments for this Page</div>"
            .draw_form_field(
                "comments_allow",
                $comments_allow,
                "radio_listdata",
                '',
                '',
                '',
                '',
                '',
                $bulk_update,
                '',
                'lst_comment_allow'
            )
            ."<div class='clr_b pixel'></div>"
         :
            ""
        )
        .($isMASTERADMIN || (System::has_feature('Ratings') && $isAPPROVER) ?
             "<div class='lbl' style='width:220px;'>Allow ratings for this Page</div>"
            .draw_form_field(
                "ratings_allow",
                $ratings_allow,
                "radio_listdata",
                '',
                '',
                '',
                '',
                '',
                $bulk_update,
                '',
                'lst_rating_allow'
            )
            ."<div class='clr_b pixel'></div>"
         :
            ""
        )
        .($isAPPROVER && $page_allow_password ?
             "<div class='lbl' style='width:220px;'>Passwords (csv list)</div>"
            .draw_form_field("password", $password, "text", 600, '', '', '', '', $bulk_update)
            ."<div class='clr_b pixel'></div>"
         :
            ""
        )
        .($isMASTERADMIN ?
             "<div class='fl' style='padding-left:10px;'>"
            .draw_form_field("locked", $locked, "bool", '', '', '', '', '', $bulk_update)
            ."</div>"
            ."<div class='lbl'><label for='locked'>Page is Locked for Modifications</label></div>"
            ."<div class='clr_b pixel'></div>\n"
         :
            ""
        )
        .($isAPPROVER ?
             "<div class='fl' style='padding-left:10px;'>"
            .draw_form_field("permPUBLIC", $permPUBLIC, "bool", '', '', '', '', '', $bulk_update)
            ."</div>"
            ."<div class='lbl'><label for='permPUBLIC'>Public</label></div><div class='clr_b pixel'></div>\n"
            ."<div class='fl' style='padding-left:10px;'>"
            .draw_form_field("permSYSLOGON", $permSYSLOGON, "bool", '', '', '', '', '', $bulk_update)
            ."</div>"
            ."<div class='lbl'><label for='permSYSLOGON'>Site Users</label></div><div class='clr_b pixel'></div>\n"
            ."<div class='fl' style='padding-left:10px;'>"
            .draw_form_field("permSYSMEMBER", $permSYSMEMBER, "bool", '', '', '', '', '', $bulk_update)
            ."</div>"
            ."<div class='lbl'><label for='permSYSMEMBER'>Site Members</label></div><div class='clr_b pixel'></div>\n"
            ."<br /><div class='lbl'>"
            ."Page is also available to group members having 'Viewer' or 'Editor' rights in these groups:"
            ."</div><br /><br />\n"
            ."<div style='padding-left:10px;'>"
            .(!$bulk_update ?
                draw_form_field(
                    "groups_assign",
                    $groups_assign,
                    "groups_assign",
                    800,
                    $formSelectorGroupSQL,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    215
                )
            :
                "<i>(Cannot set <b>Groups</b> in Bulk Update mode)</i>"
            )
            ."</div>"
         :
             "<div style='padding-top:10px; padding-left: 10px;'>"
            ."<b>&nbsp;You cannot assign permissions without Approver rights.</b>"
            ."</div>"
        )
        ."</div>\n"
        ."</div>"

  // Style
        .draw_section_tab_div('style', $selected_section)
        ."<div style='height:".$height."px;width:".$width."px;'>"
        ."<div class='lbl'>These style sheet entries apply only to this page.</div><br /><br />\n"
        ."<div class='txt_c'>"
        .draw_form_field("style", $style, "textarea", "780px", '', '', '', '', $bulk_update, '', '', 400)
        ."</div>\n"
        ."</div>\n"
        ."</div>"

  // Keywords
        .($Obj_System->has_feature('Keywords') || $isMASTERADMIN ?
             draw_section_tab_div('keywords', $selected_section)
            ."<div style='height:".$height."px;width:".$width."px;'>"
            ."<div class='lbl'>Keywords for on-site searches</div><div class='clr_b pixel'></div><br />\n"
            .(!$bulk_update ?
                 "<div>"
                .draw_form_field(
                    "keywords",
                    $keywords,
                    "keywords_assign",
                    780,
                    '',
                    '',
                    '',
                    '',
                    $bulk_update,
                    '',
                    '',
                    320
                )
                ."</div>\n"
              : "<i>(Cannot set <b>Keywords</b> in Bulk Update mode)</i>"
             )
            ."</div>\n"
            ."</div>"
         :
            ""
        )

  // Advanced
        .draw_section_tab_div('advanced', $selected_section)
        ."<div style='height:".$height."px;width:".$width."px;'>"
        ."<div class='lbl'>Page Parameters</div><div class='clr_b'></div>\n"
        ."<div class='txt_c'>"
        .draw_form_field(
            "component_parameters",
            $component_parameters,
            "option_list",
            780,
            '',
            '',
            '',
            '',
            $bulk_update,
            '',
            '',
            240
        )
        ."</div>\n"
        ."<table>\n"
        .($page_allow_memberID ?
             "  <tr>\n"
            ."    <td><img src='".BASE_PATH."img/spacer' height='5' class='b' alt=''/></td>\n"
            ."  </tr>\n"
            ."  <tr>\n"
            ."    <td><table cellpadding='1' border='0' cellspacing='0' width='100%' class='admin_containerpanel'>\n"
            ."      <tr>\n"
            ."        <td class='nowrap' style='width:115px;'><div class='lbl'><label for='memberID'>"
            ."Owner Member"
            ."</label></div></td>"
            ."        <td>"
            .draw_form_field(
                "memberID",
                $memberID,
                "selector",
                "690",
                $formSelectorMemberIDSQL,
                '',
                '',
                '',
                $bulk_update
            )
            ."</td>\n"
            ."      </tr>\n"
            ."    </table></td>\n"
            ."  </tr>\n"
         :
            ""
        )
        .($isSYSADMIN || $isMASTERADMIN ?
             "  <tr>\n"
            ."    <td><img src='".BASE_PATH."img/spacer' height='5' class='b' alt=''/></td>\n"
            ."  </tr>\n"
            ."  <tr>\n"
            ."    <td><table cellpadding='1' border='0' cellspacing='0' width='100%' class='admin_containerpanel'>\n"
            ."      <tr>\n"
            ."        <td class='nowrap' style='width:115px;'><div class='lbl'><label for='path_extender'>"
            ."Path Extender"
            ."</label></div></td>"
            ."        <td>"
            .draw_form_field("path_extender", $path_extender, "bool", "", '', '', '', '', $bulk_update)
            ."</td>\n"
            ."      </tr>\n"
            ."    </table></td>\n"
            ."  </tr>\n"
         :
            ""
        )

        .($page_allow_buttonsuite ?
             "  <tr>\n"
            ."    <td><img src='".BASE_PATH."img/spacer' height='5' class='b' alt=''/></td>\n"
            ."  </tr>\n"
            ."  <tr>\n"
            ."    <td><table cellpadding='1' border='0' cellspacing='0' width='100%' class='admin_containerpanel'>\n"
            ."      <tr>\n"
            ."        <td style='width:115px;'><div class='lbl'>Button Suite 1</div></td>"
            ."        <td>"
            .draw_form_field(
                "navsuite1ID",
                $navsuite1ID,
                "selector",
                "690",
                $formSelectorNavsuiteSQL,
                '',
                '',
                '',
                $bulk_update
            )
            ."</td>\n"
            ."      </tr>\n"
            ."      <tr>\n"
            ."        <td><div class='lbl'>Button Suite 2</div></td>"
            ."        <td>"
            .draw_form_field(
                "navsuite2ID",
                $navsuite2ID,
                "selector",
                "690",
                $formSelectorNavsuiteSQL,
                '',
                '',
                '',
                $bulk_update
            )
            ."</td>\n"
            ."      </tr>\n"
            ."      <tr>\n"
            ."        <td><div class='lbl'>Button Suite 3</div></td>"
            ."        <td>"
            .draw_form_field(
                "navsuite3ID",
                $navsuite3ID,
                "selector",
                "690",
                $formSelectorNavsuiteSQL,
                '',
                '',
                '',
                $bulk_update
            )
            ."</td>\n"
            ."      </tr>\n"
            ."    </table></td>\n"
            ."  </tr>\n"
         :
            ""
        )
        ."  <tr>\n"
        ."    <td><img src='".BASE_PATH."img/spacer' height='5' class='b' alt=''/></td>\n"
        ."  </tr>\n"
        ."  <tr>\n"
        ."    <td><table cellpadding='1' border='0' cellspacing='0' width='100%' class='admin_containerpanel'>\n"
        ."      <tr>\n"
        ."        <td style='width:125px;'><div class='lbl'>Component (pre)</div></td>"
        ."        <td>"
        .draw_form_field(
            "componentID_pre",
            $componentID_pre,
            "selector",
            "680",
            $formSelectorComponentSQL,
            '',
            '',
            '',
            $bulk_update
        )
        ."</td>\n"
        ."      </tr>\n"
        ."      <tr>\n"
        ."        <td style='width:125px;'><div class='lbl'>Component (late)</div></td>"
        ."        <td>"
        .draw_form_field(
            "componentID_post",
            $componentID_post,
            "selector",
            "680",
            $formSelectorComponentSQL,
            '',
            '',
            '',
            $bulk_update
        )
        ."</td>\n"
        ."      </tr>\n"
        ."    </table></td>\n"
        ."  </tr>\n"
        ."</table>"
        ."</div>\n"
        ."</div>"

  // Meta Data
        .draw_section_tab_div('meta_data', $selected_section)
        ."<div style='height:".$height."px;width:".$width."px;'>"
        ."<div class='lbl'>Metadata is used by search engines to help them catalogue the page."
        ." These metadata entries apply to this page.</div><div class='clr_b'></div><br />\n"
        ."<div style='padding-left:20px;'>"
        ."<span class='formLabel'>Description</span><br />\n"
        .draw_form_field(
            "meta_description",
            $meta_description,
            "textarea",
            "780px",
            '',
            '',
            '',
            '',
            $bulk_update,
            '',
            '',
            160
        )
        ."<br />\n"
        ."</div>\n"
        ."<div style='padding-left:20px;padding-top:30px;'>"
        ."<span class='formLabel'>Keywords</span><br />\n"
        .draw_form_field(
            "meta_keywords",
            $meta_keywords,
            "textarea",
            "780px",
            '',
            '',
            '',
            '',
            $bulk_update,
            '',
            '',
            160
        )
        ."<br />\n"
        ."</div>\n"
        ."</div>\n"
        ."</div>"
        ."</td>\n"
        ."                  </tr>\n"
        ."                </table></td>\n"
        ."              </tr>\n"
        ."              <tr>\n"
        ."		          <td><img src='".BASE_PATH."img/spacer' height='5' class='b' alt=''/></td>\n"
        ."              </tr>\n"
        ."              <tr>\n"
        ."                <td align='center'>"
        ."<input type='button' value='Close'"
        ." onclick=\"window.close()\" class='formbutton' style='width: 60px;'/>\n"
        ."<input type='button' value='Save'"
        ." onclick=\"this.value='Please Wait...';this.disabled=1;show_popup_please_wait();"
        ."geid_set('submode','save');geid('form').submit();\""
        ." class='formbutton' style='width: 60px;'/>\n"
        ."<input type='button' value='Save and Close'"
        ." onclick=\"this.value='Please Wait...';this.disabled=1;show_popup_please_wait();"
        ."geid_set('submode','save_and_close');geid('form').submit();\""
        ." class='formbutton' style='width: 120px;'/>\n"
        .($canAdd ?
             "<input type='button' value='Save and New...'"
            ." onclick=\"this.value='Please Wait...';this.disabled=1;show_popup_please_wait();"
            ."geid_set('submode','save_and_new');geid('form').submit();\""
            ." class='formbutton' style='width: 120px;'/>\n"
         :
            ""
        )
        ."</td>\n"
        ."              </tr>\n"
        ."            </table></td>\n"
        ."          </tr>\n"
        ."        </table></td>\n"
        ."      </tr>\n"
        ."    </table></td>\n"
        ."  </tr>\n"
        ."</table>\n";
        return $out;
    }

    public function get_version()
    {
        return VERSION_PAGE_EDIT;
    }
}
