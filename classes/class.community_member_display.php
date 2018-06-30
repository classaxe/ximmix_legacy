<?php
define('COMMUNITY_MEMBER_DISPLAY_VERSION', '1.0.38');
/*
Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)
*/
/*
Version History:
  1.0.38 (2015-01-04)
    1) Changes to Contact form:
         a) Now includes website link in subject line
         b) Now BCCs messages to info@churchesInYourTown.ca
         c) Now CCs sent message to sender if they have logged in and have editor privileges
    2) Now PSR-2 Compliant

  (Older version history in class.community_member_display.txt)
*/

class Community_Member_Display extends Community_Member
{
    protected $_events =                  array();
    protected $_events_special =          array();
    protected $_nav_prev =                false;
    protected $_nav_next =                false;
    protected $_Obj_Community;
    protected $_sponsors_national_records =   array();
    protected $_sponsors_local_records =      array();
    protected $_sponsors_national_container = '';
    protected $_stats;

    public function draw($cp, $member_extension)
    {
        $this->_setup_initial($cp, $member_extension);
        if ($this->_print) {
            $Obj = new Community_Member_Summary;
            return $Obj->draw($cp, $member_extension);
        }
        if ($this->_member_page) {
            $Obj = new Community_Member_Resource;
            return $Obj->draw($cp, $member_extension);
        }
        $this->_setup($cp);
        $this->_draw_js();
        $this->_draw_css();
        $this->_draw_title();
        $this->_draw_community_navigation();
        $this->_draw_section_tabs();
        $this->_draw_frame_open();
        $this->_draw_section_container_open();
        $this->_draw_profile();
        $this->_draw_members();
        $this->_draw_map();
        $this->_draw_contact();
        $this->_draw_articles();
        $this->_draw_events();
        $this->_draw_events_special();
        $this->_draw_calendar();
        $this->_draw_news();
        $this->_draw_podcasts();
        $this->_draw_stats();
        $this->_draw_about();
        $this->_draw_section_container_close();
        $this->_draw_frame_shut();
        return $this->_html;
    }

    protected function _draw_2col_entry($label, $content, $test)
    {
        if ($test=='') {
            return;
        }
        return
         "  <tr>\n"
        ."    <th>".$label."</th>\n"
        ."    <td>".$content."</td>\n"
        ."  </tr>\n";
    }

    protected function _draw_css()
    {
        Page::push_content(
            'style_include',
            "<link rel=\"stylesheet\" type=\"text/css\""
            ." href=\"/css/community/".System::get_item_version('css_community')."\" />"
        );
        $css =
             ".profile_frame .member_slideshow                { width: "
            .(($this->_cp['profile_photo_width'])+20)."px; }\n"
            .".profile_frame .photo_frame .member_photo_frame { width: "
            .(($this->_cp['profile_photo_width']))."px; height:".(($this->_cp['profile_photo_height']))."px; }\n"
            ."#section_special_heading { background-color: #ffd0d0; }\n"
            ."#section_special_heading.tab_selected { background-color: #ffa0a0; }\n";
        Page::push_content('style', $css);
    }

    protected function _draw_js()
    {
        $selected_section = (get_var('selected_section') ?
            get_var('selected_section')
        :
            $this->_section_tabs_arr[0]['ID']
        );
        Page::push_content(
            'javascript_onload',
            "  show_section_onhashchange_setup(spans_".$this->_safe_ID.");\n"
            ."  window.setTimeout(\n"
            ."    \"var tab='".$selected_section."';"
            .(get_var('anchor') ? "" : "if(document.location.hash){tab=document.location.hash.substr(1);};")
            ."show_section_tab(spans_".$this->_safe_ID.",tab);\",\n"
            ."    500\n"
            ."  );\n"
        );
    }

    protected function _draw_about()
    {
        if (!$this->_cp['show_about']) {
            return;
        }
        $Obj_Page =         new Page;
        $this->_pageID =    $Obj_Page->get_ID_by_path('//'.trim($this->_cp['template_member_page'], '/').'/');
        $Obj_Page->_set_ID($this->_pageID);
        $content =          $Obj_Page->get_field('content');
        $this->_html.=
             HTML::draw_section_tab_div('about', $this->_selected_section)
            ."<div class='inner'>"
            ."<h2>"
            .($this->_current_user_rights['canEdit'] && $this->_pageID ?
               "<a"
              ." href=\"".BASE_PATH.'details/'.$this->_edit_form['pages'].'/'.$this->_pageID.'"'
              ." onclick=\"details('".$this->_edit_form['pages']."','".$this->_pageID."',"
              ."'".$this->_popup['pages']['h']."','".$this->_popup['pages']['w']."');return false;\""
              .">".$this->_cp['label_about']."</a>"
            :
               $this->_cp['label_about']
            )
            ."</h2>\n"
            .($this->_cp['header_about'] ? "<div class='section_header'>".$this->_cp['header_about']."</div>\n" : "")
            .($this->_pageID ?
                $this->_draw_about_items($content)
            :
                 "<b>Error </b>: The 'About' section template page //".trim($this->_cp['template_member_page'], '/')."/"
                ." wasn't found."
            )
            ."<div class='clear'>&nbsp;</div>"
            ."<div class='section_footer'>".$this->_cp['footer_about']."</div>"
            ."</div>"
            ."</div>";
    }

    protected function _draw_about_items($content)
    {
        $replace = array(
            '[[COMMUNITY_NAME]]' =>     $this->_community_record['name'],
            '[[COMMUNITY_TITLE]]' =>    $this->_community_record['title'],
            '[[COMMUNITY_URL]]' =>      $this->_community_record['URL_external'],
            '[[MEMBER_TITLE]]' =>       $this->_record['title'],
            '[[MEMBER_URL]]' =>
                 BASE_PATH.trim($this->_community_record['URL'], '/').'/'
                .trim($this->_record['name'], '/'),
            '[[SPONSORS_LOCAL]]' =>     $this->_draw_sponsors_local(),
            '[[SPONSORS_NATIONAL]]' =>  $this->_draw_sponsors_national()
        );
        return strtr($content, $replace);
    }

    protected function _draw_articles()
    {
        if (!$this->_cp['show_articles'] || !$this->_record['full_member']) {
            return;
        }
        $Obj = new Community_Member_Article;
        $Obj->communityID =     $this->_record['communityID'];
        $Obj->memberID =        $this->_record['ID'];
        $Obj->partner_csv =     $this->_record['partner_csv'];
        $Obj->community_URL =   $this->_community_record['URL'];
        $args = array(
            'author_show' =>          $this->_cp['listing_show_author'],
            'category_show' =>        $this->_cp['listing_show_category'],
            'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
            'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
            'content_show' =>         $this->_cp['listing_show_content'],
            'results_limit' =>        $this->_cp['listing_results_limit'],
            'results_paging' =>       $this->_cp['listing_results_paging'],
            'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
            'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
            'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
        );
        $this->_html.=
            HTML::draw_section_tab_div('articles', $this->_selected_section)
            ."<div class='inner'>"
            .$this->_draw_web_share('articles', 'articles')
            ."<h2>Articles for ".$this->_record['title']."</h2>"
            .$Obj->draw_listings('member_articles', $args, false)
            ."</div>\n"
            ."</div>\n";
    }

    protected function _draw_calendar()
    {
        if (
            !$this->_cp['show_calendar'] ||
            !($this->_record['full_member'] ||
            $this->_record['primary_ministerialID'])
        ) {
            return;
        }
        $Obj_CMC = new Community_Member_Calendar;
        $Obj_CMC->communityID = $this->_record['communityID'];
        $Obj_CMC->community_record = $this->_community_record;
        $Obj_CMC->memberID = $this->_record['ID'];
        $Obj_CMC->partner_csv = $this->_record['partner_csv'];
        $args = array(
            'show_controls' => 0,
            'show_heading' => 0
        );
        $this->_html.=
             HTML::draw_section_tab_div('calendar', $this->_selected_section)
            ."<div class='inner'>"
            .$this->_draw_web_share('events', 'calendar')
            ."<h2>Monthly Calendar for ".$this->_record['title']."</h2>"
            .$Obj_CMC->draw('community_member', $args, false)
            ."</div>\n"
            ."</div>\n";
    }

    protected function _draw_community_navigation()
    {
        global $page_vars;
        $this->_html.=
             "<div class='profile_nav_outer'>"
            ."<img src='".BASE_PATH."img/icon/2600/11' alt='Member Profile'"
            ." style='vertical-align:middle;margin-right: 2px;'/>"
            ."Use these controls to navigate around community."
            ."<div class='profile_nav'>\n"
            ."<input type=\"button\" class=\"form_button\" style=\"width:5em;text-align:left\""
            ." title=\"View ".$this->_nav_prev['title']."\" value=\"&lt; Prev\""
            ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'], '/')."/"
            .$this->_nav_prev['name']."'+document.location.hash\" />\n"
            ."<input type=\"button\" class=\"form_button\" style=\"width:5em;\""
            ." title=\"View entire ".$this->_Obj_Community->record['title']." community\" value=\"Up\""
            ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'], '/')."'\" />\n"
            ."<input type=\"button\" class=\"form_button\" style=\"width:5em;text-align:right\""
            ." title=\"View ".$this->_nav_next['title']."\" value=\"Next &gt;\""
            ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'], '/')."/"
            .$this->_nav_next['name']."'+document.location.hash\" />\n"
            ."</div>"
            ."</div>\n";
    }

    protected function _draw_contact()
    {
        if (!$this->_cp['show_contact']) {
            return;
        }
        $this->_draw_contact_form_setup();
        $office =   $this->_draw_address('office_addr_');
        $mailing =  $this->_draw_address('mailing_addr_');
        $phone =    $this->_draw_office_phone();
        $notes =    $this->_draw_office_notes();
        $hours =    $this->_draw_office_hours();
        $this->_html.=
             HTML::draw_section_tab_div('contact', $this->_selected_section)
            ."<div class='inner'>"
            ."<h2>Contact ".htmlentities($this->_record['title'])."</h2><br />\n"
            ."<div class='addresses'>\n"
            .($phone || $notes || $hours ? "<div>" : "")
            .($phone ? "<h3>Telephone</h3>".$phone."\n" : "")
            .($notes ? "<h3>Notes</h3>".$notes."\n" : "")
            .($hours ? "<h3>Office Hours</h3>".$hours."\n" : "")
            .($phone || $notes || $hours ? "</div>" : "")
            .($office ? "<div><h3>Office Address</h3>".$office."</div>\n" : "")
            .($mailing && $mailing!=$office ? "<div class='addr'><h3>Mailing Address</h3>".$mailing."</div>\n" : "")
            ."</div>\n";
        if (count($this->_contacts)) {
            $this->_html.=
                 "<hr />\n"
                .HTML::draw_status('contact_form_status', $this->_msg);
        }
        $this->_draw_contact_form();
        $this->_html.=
             "</div>\n"
            ."</div>\n";
    }

    protected function _draw_contact_form()
    {
        global $page_vars;
        if (!count($this->_contacts)) {
            return;
        }
        if ($this->submode == "community_member_contact_sent") {
            $this->_draw_contact_form_draw_result();
            return;
        }
        $this->_draw_contact_form_draw_js();
        $this->_draw_contact_form_draw_form();
    }

    protected function _draw_contact_form_draw_form()
    {
        $width = 400;
        $this->_html.=
             "<div class='contact_form'>\n"
            ."<h2>Send us a Message:</h2>\n"
            .Report_Column::draw_label('Message to:', '', 'contact_send_to', 200)
            .draw_form_field(
                'contact_send_to',
                $this->contact_send_to,
                'selector_csvlist',
                $width,
                '',
                '',
                '',
                '',
                '',
                '',
                $this->_contacts_csv
            )
            ."<br class='clr_b' />"
            .Report_Column::draw_label('Your Name:', '', 'contact_sender_name', 200)
            .draw_form_field(
                'contact_sender_name',
                $this->contact_sender_name,
                'text',
                $width,
                '',
                '',
                " onkeypress=\"return keytest_enter_execute(event,function(){geid('contact_sender_email').focus();})\""
            )
            ."<br class='clr_b' />"
            .Report_Column::draw_label('Your Email:', '', 'contact_sender_email', 200)
            .draw_form_field(
                'contact_sender_email',
                $this->contact_sender_email,
                'text',
                $width,
                '',
                '',
                " onkeypress=\"return keytest_enter_execute(event,function(){geid('contact_message').focus();})\""
            )
            ."<br class='clr_b' />"
            .Report_Column::draw_label('Your Message:', '', 'contact_message', 200)
            .draw_form_field(
                'contact_message',
                $this->contact_message,
                'textarea',
                $width,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                150
            )
            ."<br class='clr_b' />"
            .Report_Column::draw_label('Verification Image:', '', 'captcha_key', 200)
            ."<img class='formField std_control' style='border:1px solid #7F9DB9;padding:2px;'"
            ." src='".BASE_PATH."?command=captcha_img' alt='Verification Image' />"
            ."<br class='clr_b' />"
            .Report_Column::draw_label('Verification Code:', '', 'captcha_key', 200)
            .draw_form_field(
                'captcha_key',
                '',
                'text',
                182,
                '',
                '',
                " onkeypress=\"keytest_enter_execute(event,function(){geid('contact_form_send').focus();})\""
            )
            ."<br class='clr_b' />"
            ."<p class='txt_c'>\n"
            ."  <input type='button' id='contact_form_send' value='Send Message'"
            ." onclick='return contact_form_verify();'/>\n"
            ."</p>\n"
            ."</div>";
    }

    protected function _draw_contact_form_draw_js()
    {
        $js =
             "function email_check(val){\n"
            ."  return !(val.length<6 || val.indexOf('@')<1 || val.lastIndexOf('.')-2<val.lastIndexOf('@'));\n"
            ."}\n"
            ."function contact_form_verify(){\n"
            ."  var err_arr = [];\n"
            ."  var n = 1;\n"
            ."  if (geid_val(\"contact_send_to\")=='0') {\n"
            ."    err_arr.push((n++)+\") Message To\");\n"
            ."  }\n"
            ."  if (geid_val(\"contact_sender_name\")=='') {\n"
            ."    err_arr.push((n++)+\") Your Name\");\n"
            ."  }\n"
            ."  if (!email_check(geid_val(\"contact_sender_email\"))) {\n"
            ."    err_arr.push((n++)+\") Your Email\");\n"
            ."  }\n"
            ."  if (geid_val(\"contact_message\")=='') {\n"
            ."    err_arr.push((n++)+\") Your Message\");\n"
            ."  }\n"
            ."  if (geid_val(\"captcha_key\").length!=6) {\n"
            ."    err_arr.push((n++)+\") Characters shown in the image (slows down spammers!)\");\n"
            ."  }\n"
            ."  var err = err_arr.join('\\n');\n"
            ."  if (err==''){\n"
            ."    geid_set('submode','community_member_contact');\n"
            ."    geid('form').submit();\n"
            ."    return;\n"
            ."  }\n"
            ."  alert(\n"
            ."    '-----------------------\\n'+\n"
            ."    'Attention Required\\n'+\n"
            ."    '-----------------------\\n'+\n"
            ."    'The following required fields were not provided:\\n'+\n"
            ."    err +\n"
            ."    '\\n\\nPress [OK] to continue.'\n"
            ."  );\n"
            ."}\n";
        Page::push_content('javascript', $js);
    }

    protected function _draw_contact_form_draw_result()
    {
        $this->_html.=
             "<div class='contact_form'>\n"
            ."<h1 style='margin: 0.25em 0em'>You Sent us a Message:</h1>\n"
            .Report_Column::draw_label('Message to:', '', 'contact_send_to', 200)
            ."<div class='fl'>".$this->contact_recipient_name."</div>\n"
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label('Your Name:', '', 'contact_sender_name', 200)
            ."<div class='fl'>".$this->contact_sender_name."</div>\n"
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label('Your Email:', '', 'contact_sender_email', 200)
            ."<div class='fl'>".$this->contact_sender_email."</div>"
            ."<div class='clr_b'></div>"
            .Report_Column::draw_label('Your Message:', '', 'contact_message', 200)
            ."<div class='fl' style='width:400px'>".nl2br($this->contact_message)."</div>"
            ."<div class='clr_b'></div>"
            ."<p class='txt_c'>"
            ."<input type='button' value='Done'"
            ." onclick=\"document.location='".BASE_PATH.trim($this->_community_record['URL'], '/').'/'
            .trim($this->_record['name'], '/')."#contact'\" />"
            ."</p>\n"
            ."</div>";
    }

    protected function _draw_contact_form_handle_user_requests()
    {
        switch ($this->submode){
            case "community_member_contact":
                $Obj_Captcha = new Captcha;
                if (!$Obj_Captcha->isKeyRight(isset($_POST['captcha_key']) ? $_POST['captcha_key'] : "NOWAY")) {
                    $this->_msg =
                         "<span style='color:red'><b>Error</b>:"
                        ." You must enter the same characters shown in the image.</span>";
                    break;
                }
                $this->contact_recipient_email = "";
                foreach ($this->_contacts as $contact) {
                    if ($contact['idx']==$this->contact_send_to) {
                        $this->contact_recipient_email = $contact['email'];
                        $this->contact_recipient_name =  $contact['name'];
                        break;
                    }
                }
                get_mailsender_to_component_results();      // Use system default mail sender details
                $data =
                array(
                    'NName' =>            $this->contact_recipient_name.' <'.$this->contact_recipient_email.'>',
                    'PEmail' =>           $this->contact_recipient_email,
                    'bcc_email' =>        'info@churchesinyourtown.ca',
                    'bcc_name' =>         'Martin Francis',
                    'replyto_email' =>    $this->contact_sender_email,
                    'replyto_name' =>     $this->contact_sender_name,
                    'subject' =>
                         "Contact via ".$this->_record['title']." church profile at "
                        .$this->_community_record['URL_external']."/".$this->_record['name'].'#contact',
                    'html' =>             nl2br($this->contact_message),
                    'text' =>             wordwrap(html_entity_decode(strip_tags($this->contact_message)))
                );
                if ($this->_current_user_rights['isEditor']) {
                    $data['cc_email'] = $this->contact_sender_email;
                    $data['cc_name'] =  $this->contact_sender_name.' <'.$this->contact_sender_email.'>';
                }
                $mail_result =              mailto($data);
                if (substr($mail_result, 0, 12)=="Message-ID: ") {
                    $this->_msg = "<b>Success:</b> Your message has been sent.";
                    $this->submode = "community_member_contact_sent";
                } else {
                    $this->_msg = "<b>Error:</b> ".$mail_result;
                }
                break;
        }
    }

    protected function _draw_contact_form_setup()
    {
        if (!count($this->_contacts)) {
            return;
        }
        $this->submode =                get_var('submode');
        $this->contact_send_to =        get_var('contact_send_to');
        $this->contact_sender_email =   get_var('contact_sender_email');
        $this->contact_sender_name =    get_var('contact_sender_name');
        $this->contact_message =        get_var('contact_message');
        $this->msg = "";
        $this->_draw_contact_form_handle_user_requests();
        $this->_draw_contact_form_get_contacts_csv();
        if ($personID = get_userID()) {
            $Obj_User =     new User($personID);
            $Obj_User->load();
            $this->contact_sender_email = ($this->contact_sender_email ?
                $this->contact_sender_email
             :
                $Obj_User->record['PEmail']
            );
            $this->contact_sender_name =  ($this->contact_sender_name ?
                $this->contact_sender_name
             :
                 $Obj_User->record['NFirst']
                .($Obj_User->record['NMiddle'] ? " ".$Obj_User->record['NMiddle'] : "")
                .($Obj_User->record['NLast'] ? " ".$Obj_User->record['NLast'] : "")
            );
        }
    }

    protected function _draw_contact_form_get_contacts_csv()
    {
        if (!count($this->_contacts)) {
            return "";
        }
        $out = array();
        if (count($this->_contacts)>1) {
            array_unshift(
                $this->_contacts,
                array('idx' => 0, 'name' => '(Please choose a name from the list)', 'bgcolor' => 'd0d0d0')
            );
        }
        foreach ($this->_contacts as $contact) {
            $out[] = $contact['idx']."|".str_replace(',', '&comma;', $contact['name'])."|a0ffa0";
        }
        $this->_contacts_csv = implode(',', $out);
    }

    protected function _draw_context_menu_member($record)
    {
        if (!$this->_current_user_rights['canEdit']) {
            return;
        }
        return
             " onmouseover=\""
            ."if(!CM_visible('CM_community_member')) {"
            ."this.style.backgroundColor='"
            .($record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
            ."';"
            ."_CM.type='community_member';"
            ."_CM.ID='".$record['ID']."';"
            ."_CM.full_member=".($record['full_member']=='1' ? '1' : '0').";"
            ."_CM.ministerial_member=".($record['primary_ministerialID'] ? '1' : '0').";"
            ."_CM_text[0]='&quot;".str_replace(array("'","\""), '', htmlentities($record['title']))."&quot;';"
            ."_CM.path='".$record['member_URL']."';"
            ."}\""
            ." onmouseout=\"this.style.backgroundColor='';_CM.type=''\"";
    }

    protected function _draw_frame_open()
    {
        $this->_html.="<div class='profile_frame'>";
    }

    protected function _draw_frame_shut()
    {
        $this->_html.="</div>";
    }

    protected function _draw_address($prefix)
    {
        $r = $this->_record;
        if (
            $r[$prefix.'line1']=='' &&
            $r[$prefix.'line2']=='' &&
            $r[$prefix.'city']=='' &&
            $r[$prefix.'sp']=='' &&
            $r[$prefix.'postal']==''
        ) {
            $prefix = 'service_addr_';
        }
        if (!trim($r[$prefix.'line1'].$r[$prefix.'line2'].$r[$prefix.'city'].$r[$prefix.'sp'].$r[$prefix.'postal'])) {
            return;
        }
        return
             "<p style='margin:0 0 2em 1em;'>\n"
            .$r[$prefix.'line1']."<br />"
            .($r[$prefix.'line2'] ? $r[$prefix.'line2']."<br />" : "")
            .$this->_record[$prefix.'city']."<br />"
            .($r[$prefix.'sp'] ? $r[$prefix.'sp']."<br />" : "")
            .($r[$prefix.'postal'] ? $r[$prefix.'postal'] : "")
            ."</p>";
    }


    protected function _draw_office_hours()
    {
        $days =         explode(',', 'Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
        $r =            $this->_record;
        $has_hours =    false;
        foreach ($days as $d) {
            if ($r['xml:Church_Office_'.substr($d, 0, 3)]) {
                $has_hours = true;
                break;
            }
        }
        if (!$has_hours) {
            return;
        }
        $out =
            "<table class='table_details' cellpadding='0' cellspacing='0' border='0'>";
        foreach ($days as $d) {
            $hours = $r['xml:Church_Office_'.substr($d, 0, 3)];
            if ($hours) {
                $out.= $this->_draw_2col_entry($d, $hours, $hours);
            }
        }
        $out.=
            "</table>\n";
        return $out;
    }

    protected function _draw_office_notes()
    {
        $r = $this->_record;
        if (!$r['office_notes']) {
            return;
        }
        return
            "<p style='margin:0 0 2em 1em;'>\n".$r['office_notes']."</p>";
    }

    protected function _draw_office_phone()
    {
        $r = $this->_record;
        if (!$r['office_phone1_lbl'] && !$r['office_phone2_lbl']) {
            return;
        }
        return
             "<table class='table_details' cellpadding='0' cellspacing='0' border='0'>"
            .$this->_draw_2col_entry($r['office_phone1_lbl'], $r['office_phone1_num'], $r['office_phone1_lbl'])
            .$this->_draw_2col_entry($r['office_phone2_lbl'], $r['office_phone2_num'], $r['office_phone2_lbl'])
            ."</table>\n";
    }
    protected function _draw_map()
    {
        if (!$this->_cp['show_map']) {
            return;
        }
        if ($this->_record['service_map_lat']==0 && $this->_record['service_map_lon']==0) {
            return;
        }
        $this->_html.=
             HTML::draw_section_tab_div('map', $this->_selected_section)
            ."<div class='inner'>"
            ."<h2>Map for ".htmlentities($this->_record['title'])."</h2>\n";
        $Obj_Map =      new Google_Map('community_member', SYS_ID);
        $Obj_Map->map_centre(
            $this->_record['service_map_lat'],
            $this->_record['service_map_lon'],
            $this->_cp['profile_map_zoom']
        );
        $img =
        ($this->_record['featured_image'] && file_exists('.'.$this->_record['featured_image']) ?
            $this->_record['featured_image']
        :
            '/640x480-photo-unavailable.png'
        );
        $featured_image =
             BASE_PATH
            ."img/width/"
            .$this->_cp['profile_map_photo_width'].$img;
            $Obj_Map->add_icon("/UserFiles/Image/map_icons/".$this->_record['type']."/", $this->_record['type']);
            $marker_html =
            "<img style='float:left;margin:0 4px 4px 0;border:1px solid #888'"
            ." width='".$this->_cp['profile_map_photo_width']."'"
            ." src='".$featured_image."' alt='".$this->_record['name']."'>"
            ."<div>"
            ."<strong>".htmlentities($this->_record['title'])."</strong><br />"
            .$this->_record['service_addr_line1']."<br />"
            .($this->_record['service_addr_line2'] ? $this->_record['service_addr_line2']."<br />" : '')
            ."<br />"
            .$this->_record['service_addr_city'].' &bull; '
            .$this->_record['service_addr_sp'].' &bull; '
            .$this->_record['service_addr_postal'];
        $Obj_Map->add_marker_with_html(
            $this->_record['service_map_lat'],
            $this->_record['service_map_lon'],
            $marker_html,
            $this->_record['ID'],
            $this->_current_user_rights['canEdit'],
            true,
            $this->_record['type'],
            htmlentities($this->_record['title']),
            'Click for Info'
        );
        $Obj_Map->add_control_type();
        $Obj_Map->add_control_large();
        $args =     array(
            'map_width'=>$this->_cp['width']-10,
            'map_height'=>$this->_cp['profile_map_height']
        );
        $this->_html.=
            $Obj_Map->draw($args)
            ."</div>\n"
            ."<div class='clear'>&nbsp;</div>"
            ."</div>\n";
    }

    protected function _draw_members()
    {
        if (!$this->_record['type']=='ministerium') {
            return;
        }
        $entries = array();
        foreach ($this->_members as $r) {
            if ($r['primary_ministerialID']==$this->_record['ID']) {
                $entries[] = $r;
            }
        }
        if (!count($entries)) {
            return;
        }
        $this->_html.=
             HTML::draw_section_tab_div('members', $this->_selected_section)
            ."<div class='inner'>"
            ."<h2>Members of ".$this->_record['title']."</h2>"
            ."<ul class=\"cross churches_spaced\">\n";
        foreach ($entries as $r) {
            $img = ($r['featured_image'] && file_exists('.'.$r['featured_image']) ?
                $r['featured_image']
             :
                '/640x480-photo-unavailable.png'
            );
            $featured_image =
                BASE_PATH."img/sysimg?img=".$img."&amp;resize=1&amp;maintain=0&amp;width=50&amp;height=40";
            $this->_html.=
                "  <li"
                .$this->_draw_context_menu_member($r)
                .">\n"
                ."    <a href=\"".$r['member_URL']."\">"
                ."<img alt=\"".str_replace('& ', '&amp; ', $r['title'])."\" src=\"".$featured_image."\""
                ." style=\"border:1px solid #888; float:left;margin:0 1em 0 0\"/></a>"
                ."<h3><a href=\"".$r['member_URL']."\">".$r['title']."</a></h3>\n"
                ."<address>"
                .$r['service_addr_line1'].","
                .($r['service_addr_line2'] ? $r['service_addr_line2'].', ' : '')
                .$r['service_addr_city'].' '
                .$r['service_addr_sp'].' '.$r['service_addr_postal']
                ."</address>\n"
                .str_replace(
                    '##LINKED_TITLE##',
                    "<a href=\"".$r['member_URL']."\">".str_replace('& ', '&amp; ', $r['title'])."</a>",
                    nl2br($r['summary'])
                )
                ."<br class='clear' />\n"
                ."  </li>\n";
        }
        $this->_html.=
             "</ul>\n"
            ."</div>\n"
            ."</div>\n";
    }

    protected function _draw_events()
    {
        if (
            !$this->_cp['show_events'] ||
            !($this->_record['full_member'] || $this->_record['primary_ministerialID'])
        ) {
            return;
        }
        $Obj = new Community_Member_Event;
        $Obj->communityID =     $this->_record['communityID'];
        $Obj->memberID =        $this->_record['ID'];
        $Obj->partner_csv =     $this->_record['partner_csv'];
        $Obj->community_URL =   $this->_community_record['URL'];
        $args = array(
            'author_show' =>          $this->_cp['listing_show_author'],
            'category_show' =>        $this->_cp['listing_show_category'],
            'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
            'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
            'content_show' =>         $this->_cp['listing_show_content'],
            'filter_what' =>          'future',
            'results_limit' =>        $this->_cp['listing_results_limit'],
            'results_paging' =>       $this->_cp['listing_results_paging'],
            'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
            'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
            'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
        );
        $this->_html.=
            HTML::draw_section_tab_div('events', $this->_selected_section)
            ."<div class='inner'>"
            .$this->_draw_web_share('events', 'events')
            ."<h2>Upcoming Events for ".htmlentities($this->_record['title'])."</h2>\n"
            .$Obj->draw_listings('member_events', $args, false)
            ."</div>\n"
            ."</div>\n";
    }

    protected function _draw_events_special()
    {
        if (!$this->_cp['show_events_special']) {
            return;
        }
        if (!$this->_events_special) {
            return;
        }
        $Obj = new Community_Member_Event;
        $Obj->communityID =     $this->_record['communityID'];
        $Obj->memberID =        $this->_record['ID'];
        $Obj->partner_csv =     $this->_record['partner_csv'];
        $Obj->community_URL =   $this->_community_record['URL'];
        $args = array(
            'author_show' =>          $this->_cp['listing_show_author'],
            'category_show' =>        false,
            'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
            'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
            'content_show' =>         $this->_cp['listing_show_content'],
            'filter_category_list' => $this->_cp['category_events_special'],
            'filter_what' =>          'future',
            'results_limit' =>        $this->_cp['listing_results_limit'],
            'results_paging' =>       $this->_cp['listing_results_paging'],
            'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
            'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
            'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
        );
        $this->_html.=
            HTML::draw_section_tab_div('special', $this->_selected_section)
            ."<div class='inner'>"
            ."<h2>".$this->_cp['label_events_special']." for ".htmlentities($this->_record['title'])."</h2>\n"
            .$Obj->draw_listings('member_events_special', $args, false)
            ."</div>\n"
            ."</div>\n";
    }

    protected function _draw_news()
    {
        if (
            !$this->_cp['show_news'] ||
            !($this->_record['full_member'] || $this->_record['primary_ministerialID'])
        ) {
            return;
        }
        $Obj = new Community_Member_News_Item;
        $Obj->communityID =     $this->_record['communityID'];
        $Obj->memberID =        $this->_record['ID'];
        $Obj->partner_csv =     $this->_record['partner_csv'];
        $Obj->community_URL =   $this->_community_record['URL'];
        $args = array(
            'author_show' =>          $this->_cp['listing_show_author'],
            'category_show' =>        $this->_cp['listing_show_category'],
            'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
            'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
            'content_show' =>         $this->_cp['listing_show_content'],
            'results_limit' =>        $this->_cp['listing_results_limit'],
            'results_paging' =>       $this->_cp['listing_results_paging'],
            'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
            'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
            'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
        );
        $this->_html.=
            HTML::draw_section_tab_div('news', $this->_selected_section)
            ."<div class='inner'>"
            .$this->_draw_web_share('news', 'news')
            ."<h2>Latest News for ".htmlentities($this->_record['title'])."</h2>\n"
            .$Obj->draw_listings('member_news', $args, false)
            ."</div>\n"
            ."</div>\n";
    }

    protected function _draw_podcasts()
    {
        if (!$this->_cp['show_podcasts'] || !$this->_record['full_member']) {
            return;
        }
        $Obj = new Community_Member_Podcast;
        $Obj->communityID =     $this->_record['communityID'];
        $Obj->memberID =        $this->_record['ID'];
        $Obj->partner_csv =     $this->_record['partner_csv'];
        $Obj->community_URL =   $this->_community_record['URL'];
        $args = array(
            'audioplayer_width' =>    $this->_cp['listing_audioplayer_width'],
            'author_show' =>          $this->_cp['listing_show_author'],
            'category_show' =>        $this->_cp['listing_show_category'],
            'content_char_limit' =>   $this->_cp['listing_content_char_limit'],
            'content_plaintext' =>    $this->_cp['listing_content_plaintext'],
            'content_show' =>         $this->_cp['listing_show_content'],
            'results_limit' =>        $this->_cp['listing_results_limit'],
            'results_paging' =>       $this->_cp['listing_results_paging'],
            'thumbnail_height' =>     $this->_cp['listing_thumbnail_height'],
            'thumbnail_show' =>       $this->_cp['listing_show_thumbnails'],
            'thumbnail_width' =>      $this->_cp['listing_thumbnail_width']
        );
        $this->_html.=
            HTML::draw_section_tab_div('podcasts', $this->_selected_section)
            ."<div class='inner'>"
            .$this->_draw_web_share('podcasts', 'podcasts')
            ."<h2>Latest "
            .($this->_record['type']=='ministerium' ? 'Audio' : 'Sermons')." from "
            .htmlentities($this->_record['title'])."</h2>\n"
            .$Obj->draw_listings('member_podcasts', $args, false)
            ."</div>\n"
            ."</div>\n";
    }


    protected function _draw_profile()
    {
        global $page_vars;
        $r =            $this->_record;
        $servicetimes = $this->_draw_service_times();
        $website =      (isset($r['link_website']) && $r['link_website'] ?
            substr($r['link_website'], 2+strpos($r['link_website'], '//'))
         :
            ""
        );
        $facebook =     (isset($r['link_facebook']) && $r['link_facebook'] ?
            substr($r['link_facebook'], 2+strpos($r['link_facebook'], '//'))
         :
            ""
        );
        $twitter =      (strpos($r['link_twitter'], '@')!==-1 ?
            substr($r['link_twitter'], 1+strpos($r['link_twitter'], '@'))
         :
            ""
        );
        $service_addr = $this->_draw_address('service_addr_');
        $verified =     ($r['date_survey_returned']!='0000-00-00' ? $r['date_survey_returned'] : false);
        $ministerial =  ($r['ministerial_title']!='' ? $r['ministerial_title'] : false);
        $video_icon =   "-7980px 0px";
        $video_label =  "Video";
        if ($r['link_video']) {
            $url_bits = explode('/', $r['link_video']);
            if (count($url_bits)>2) {
                switch ($url_bits[2]){
                    case 'livestream.com':
                    case 'www.livestream.com':
                    case 'new.livestream.com':
                        $video_icon =   "-7950px 0px";
                        $video_label =  "Livestream";
                        break;
                    case 'ustream.tv':
                    case 'www.ustream.tv':
                        $video_icon =   "-7966px 0px";
                        $video_label =  "UStream";
                        break;
                    case 'vimeo.com':
                    case 'www.vimeo.com':
                        $video_icon =   "-7934px 0px";
                        $video_label =  "Vimeo";
                        break;
                    case 'youtube.com':
                    case 'www.youtube.com':
                        $video_icon =   "-6154px 0px";
                        $video_label =  "Youtube";
                        break;
                }
            }
            $video = substr($r['link_video'], 2+strpos($r['link_video'], '//'));
        }
        $Obj_LA = new Language_Assign;
        $languages = $Obj_LA->get_text_csv_for_assignment($this->_get_assign_type(), $r['ID']);
        $this->_html.=
             HTML::draw_section_tab_div('profile', $this->_selected_section)
            ."<div class='inner'>"
            ."<h2>"
            .($this->_current_user_rights['isEditor'] ?
                 "<a href=\"".BASE_PATH."details/".$this->_edit_form['member']."/".$r['ID']."\""
                ." onclick=\"details('".$this->_edit_form['member']."',".$r['ID'].","
                .$this->_popup['member']['h'].",".$this->_popup['member']['w'].",'','');return false;\">"
             :
                ""
            )
            ."Profile for ".$r['title']
            .($this->_current_user_rights['isEditor'] ? "</a>" : "")
            ."</h2>"
            ."<div class='photo_frame'>"
            .$this->_draw_profile_image()
            .($r['full_member'] || $verified || $ministerial ?
                "<div class='member_icons'>"
               .($r['full_member']?
                     "<img src='".BASE_PATH."img/spacer' width='32' height='32' class='icons-big'"
                    ." style='background-position: -15692px 0px;' alt='Premium Listing - all features available'"
                    ." title='Premium Listing - all features available' />"
                  :
                    ""
               )
               .($verified ?
                     "<img src='".BASE_PATH."img/spacer' width='32' height='32' class='icons-big'"
                    ." style='background-position: -15996px 0px;' alt=\"Member Verified\""
                    ." title=\"Verified by ".$r['type']." on ".format_date($verified, 'l j F Y')."\" />"
                :
                    ""
               )
               .($ministerial ?
                     "<img src='".BASE_PATH."img/spacer' width='32' height='32' class='icons-big'"
                    ." style='background-position: -16030px 0px;'"
                    ." title=\"Member of ".$ministerial."\" alt=\"Member of ".$ministerial."\" />"
                :
                    ""
               )
               ."</div>"
            :
               ""
            )
            ."</div>"
            ."<div class='details'>\n"
            ."<div class='details_c1'>\n"
            .($r['date_survey_returned']!='0000-00-00' ?
                 "<h3>Information Verified by Member:</h3>"
                ."<p style='margin:0 0 2em 1em;'>".format_date($r['date_survey_returned'])."</p>"
             :
                ""
            )
            .($r['ministerial_title'] ?
                 "<h3>Member of:</h3>\n"
                ."<p style='margin:0 0 2em 1em;'>"
                ."<a href=\"".BASE_PATH.trim($this->_community_record['URL'], '/').'/'
                .trim($r['ministerial_name'], '/')."\" rel=\"external\">"
                .$r['ministerial_title']
                ."</a></p>"
             :
                ""
            )
            .($r['custom_1'] ? "<h3>Denomination:</h3><p style='margin:0 0 2em 1em;'>".$r['custom_1']."</p>" : "")
            ."</div>\n"
            ."<div class='details_c2'>\n"
            .($service_addr ?
                 "<h3>".($r['type']=='church' ? "Address for Services:" : "Our Address")."</h3>\n"
                .$service_addr
             :
                ""
            )
            .($r['languages'] ?
                "<h3>Languages for Services:</h3><p style='margin:0 0 2em 1em;'>"
                .$languages
                ."</p>"
             :
                ""
            )
            .($r['service_notes'] ?
                "<h3>Notes:</h3><p style='margin:0 0 2em 1em;'>\n".$r['service_notes']."</p>"
             :
                ""
            )
            ."</div>\n"
            ."<div class='clear'>&nbsp;</div>\n"
            .($servicetimes ?
                "<h3>Regular Meeting Times:</h3><div style='margin:0 0 2em 1em;'>".$servicetimes."</div>"
             :
                ""
            )
            .($r['link_website'] ?
                 "<div class='label'><img class='icon' src='/img/spacer' alt='' title=''"
                ." style='width:16px;height:16px;margin:0px 5px 2px 0px;background-position:-800px 0px;float:left;' />"
                ."Web Site:</div>\n"
                ."<div class='value'><a rel=\"external\" href=\"".$r['link_website']."\">".$website."</a></div>"
             :
                ""
            )
            .($r['link_facebook']?
                 "<div class='label'><img class='icon' src='/img/spacer' alt='' title=''"
                ." style='width:14px;height:14px;margin:0px 5px 2px 0px;background-position:-3147px 0px;float:left;' />"
                ."Facebook:</div>\n"
                ."<div class='value'><a rel=\"external\" href=\""
                .$r['link_facebook']."\">"
                .$facebook
                ."</a></div>"
             :
                ""
            )
            .($r['link_twitter']?
                 "<div class='label'><img class='icon' src='/img/spacer' alt='' title=''"
                ." style='width:14px;height:14px;margin:2px 5px 2px 0px;background-position:-5420px 0px;float:left;' />"
                ."Twitter:</div>\n"
                ."<div class='value'><a rel=\"external\" href=\""
                .$r['link_twitter']."\">"
                .$twitter
                ."</a></div>"
             :
                ""
            )
            .($r['link_video']?
                 "<div class='label'><img class='icon' src='/img/spacer' alt='' title=''"
                ." style='width:16px;height:16px;margin:0px 5px 2px 0px;float:left;"
                ."background-position:".$video_icon."' />"
                .$video_label.":</div>\n"
                ."<div class='value'><a rel=\"external\" href=\""
                .$r['link_video']."\">"
                .$video
                ."</a></div>"
             :
                ""
            )
            .($r['full_member'] || $r['primary_ministerialID'] ?
             "<div class='label'>"
            ."<img class='icon' src='/img/spacer' alt='' title=''"
            ." style='width:16px;height:16px;margin:0px 5px 2px 0px;background-position:-8047px 0px;float:left;' />"
            ."RSS:</div>"
            ."<div class='value'>["
            .($r['full_member'] ?
                " <a rel=\"external\" href=\"".$this->_base_path."/rss/articles\">Articles</a> |\n"
             :
                ""
            )
            ." <a rel=\"external\" href=\"".$this->_base_path."/rss/events\">Events</a> |\n"
            ." <a rel=\"external\" href=\"".$this->_base_path."/rss/news\">News</a>\n"
            .($r['full_member'] ?
                "| <a rel=\"external\" href=\"".$this->_base_path."/rss/podcasts\">Sermons</a>\n"
             :
                ""
            )
            ."]"
            ."</div>"
            :
             ""
            )
            .($r['full_member'] ?
                 "<div class='label'><img class='icon' src='/img/spacer' alt='' title=''"
                ." style='width:16px;height:16px;margin:0px 5px 4px 0px;background-position:-6170px 0px;float:left;' />"
                ."Share:</div> "
                ."<div class='value'><a href=\"#\""
                ." onclick=\"return community_embed('".addslashes(htmlentities($r['title']))."',"
                ."'".$this->_base_path."')\">Show live updates on your website...</a></div>\n"
            :
             ""
            )
            ."</div>\n"
            ."<div class='clear'>&nbsp;</div>\n"
            ."[ECL]component_share_this[/ECL]"
            ."</div>\n"
            ."</div>\n"
            ;
    }

    protected function _draw_profile_image()
    {
        if ($this->get_member_profile_images()) {
            return $this->_draw_profile_image_slideshow();
        }
        return $this->_draw_profile_image_single();
    }

    protected function _draw_profile_image_single()
    {
        $img = ($this->_record['featured_image'] && file_exists('.'.$this->_record['featured_image']) ?
            $this->_record['featured_image']
         :
            '/640x480-photo-unavailable.png'
        );
        $featured_image =   BASE_PATH."img/width/".$this->_cp['profile_photo_width'].$img;
        return
            "<div class=\"member_photo_frame\">\n"
            ."<img src=\"".$featured_image."\" class=\"member_photo\""
            ." width=\"".$this->_cp['profile_photo_width']."\""
            ." alt=\"".$this->_record['name']."\" />"
            ."</div>\n";
    }

    protected function _draw_profile_image_slideshow()
    {
        $Obj_WS =   new Component_WOW_Slider;
        $path =     '//communities/'.$this->_community_record['name'].'/members/'.$this->_record['name'].'/profile';
        $args = array(
            'bullets_margin_top' =>       $this->_cp['profile_photo_height']-40,
            'caption_show' =>             0,
            'effect' =>                   'basic_linear',
            'filter_container_path' =>    $path,
            'max_height' =>               $this->_cp['profile_photo_height'],
            'max_width' =>                $this->_cp['profile_photo_width'],
            'results_limit' =>            0,
            'thumbnail_height' =>         (int)($this->_cp['profile_photo_height']/4),
            'thumbnail_width' =>          (int)($this->_cp['profile_photo_width']/4),
            'title_show' =>               0
        );
        return "<div class='member_slideshow'>".$Obj_WS->draw('profile', $args, true)."</div>";
    }

    protected function _draw_section_tabs()
    {
        $this->_html.=
            HTML::draw_section_tab_buttons(
                $this->_section_tabs_arr,
                $this->_safe_ID,
                $this->_selected_section,
                "document.location.hash='#'+this.id.substr(8).split('_')[0]"
            );
    }

    protected function _draw_section_container_close()
    {
        $this->_html.= "</div>\n";
    }

    protected function _draw_section_container_open()
    {
        Page::push_content(
            'javascript_onload',
            "  show_section_tab('spans_".$this->_safe_ID."','".$this->_selected_section."');\n"
        );
        $this->_html.= "<div id='".$this->_safe_ID."_container' style='position:relative;'>\n";
    }

    protected function _draw_service_times()
    {
        $days = explode(',', 'Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
        $dd =   '';
        $out =  '';
        $servicetimes_known = false;
        foreach ($days as $day) {
            if (
                isset($this->_record['service_times_'.strToLower(substr($day, 0, 3))]) &&
                trim($this->_record['service_times_'.strToLower(substr($day, 0, 3))])
            ) {
                $entries = explode("\n", $this->_record['service_times_'.strToLower(substr($day, 0, 3))]);
                $rowspan = 0;
                for ($i=0; $i<count($entries); $i++) {
                    $servicetimes_known = true;
                    $entry = $entries[$i];
                    if (trim($entry)!='') {
                        $rowspan++;
                    }
                }
                for ($i=0; $i<count($entries); $i++) {
                    $entry = $entries[$i];
                    if (trim($entry)!='') {
                        $bits = explode(' ', $entry);
                        $out.=
                             "  <tr>\n"
                            .($i==0 ?
                                "    <th class='s_day'".($rowspan>1 ? " rowspan='".$rowspan."'" : "").">".$day."</th>\n"
                            :
                                ''
                            )
                            ."    <td class='s_time'>".array_shift($bits)."</td>\n"
                            ."    <td class='s_detail'>".implode('/<br />', explode('/', implode(' ', $bits)))."</td>\n"
                            ."  </tr>\n";
                        $dd= $day;
                    }
                }
            }
        }
        if (!$out) {
            return;
        }
        return
             "<table class='service_details' cellpadding='2' cellspacing='0' border='1'"
            ." summary='Table showing meeting times'>"
            ."  <thead>\n"
            ."    <tr>\n"
            ."      <th>Day</th>\n"
            ."      <th>Time</th>\n"
            ."      <th>Details</th>\n"
            ."    </tr>\n"
            ."  </thead>\n"
            ."  <tbody>\n"
            .$out
            ."  </tbody>\n"
            ."</table>";
    }

    protected function _draw_sponsors_national()
    {
        if ($this->_cp['show_sponsors']!=1) {
            return;
        }
        if (!count($this->_sponsors_national_records)) {
            return;
        }
        $Obj_CGT = new Component_Gallery_Thumbnails;
        $args = array(
            'color_background' =>           'f8f8ff',
            'color_border' =>               '695137',
            'filter_category_master' =>     '',
            'filter_container_path' =>      $this->_sponsors_national_container,
            'image_padding_horizontal' =>   10,
            'image_padding_vertical' =>     5,
            'max_height' =>                 210,
            'max_width' =>                  165,
            'results_limit' =>              0,
            'show_background' =>            1,
            'show_border' =>                1,
            'show_image_border' =>          1,
            'show_caption' =>               0,
            'show_image' =>                 1,
            'show_title' =>                 1,
            'show_uploader' =>              0,
            'title_height' =>               50
        );
        return
             "<hr />\n"
            ."<h3>".$this->_cp['label_sponsors_national']."</h3>"
            ."<div class='section_header'>".$this->_cp['header_sponsors_national']."</div>"
            .$Obj_CGT->draw('national_sponsors', $args, false);
    }

    protected function _draw_sponsors_local()
    {
        if ($this->_cp['show_sponsors']!=1) {
            return;
        }
        if (!$this->_community_record['sponsorship_gallery_albumID']) {
            return;
        }
        $Obj_GA = new Gallery_Album;
        $Obj_GA->_set_ID($this->_community_record['sponsorship_gallery_albumID']);
        $path = $Obj_GA->get_field('path');
        $Obj_SP = new Sponsorship_Plan;
        $args = array(
            'container_path' =>  $path
        );
        $result = $Obj_SP->get_records($args);
        $Obj_CGT = new Component_Gallery_Thumbnails;
        $out = "";
        foreach ($result['data'] as $plan) {
            $Obj_SP->xmlfields_decode($plan);
            $Obj_SP->_set_ID($plan['ID']);
            if ($this->_current_user_rights['canEdit'] || $Obj_SP->get_children()) {
                $args = array(
                    'color_background' =>       'ffffff',
                    'filter_category_master' => '',
                    'filter_container_path' =>  $plan['path'],
            //          'max_height' =>             $plan['xml:width'],
                    'max_width' =>              $plan['xml:width'],
                    'results_limit' =>          0,
                    'show_background' =>        1,
                    'show_caption' =>           $plan['xml:show_description'],
                    'show_image' =>             $plan['xml:show_logo'],
                    'show_title' =>             $plan['xml:show_name'],
                    'show_uploader' =>          1,
                );
                $result = $Obj_CGT->draw($plan['name'], $args, false);
                if ($result) {
                    $out.=
                        ($out ? "<hr />\n" : "")
                        .($this->_current_user_rights['canEdit'] ?
                         "<h4>"
                        ."<a href='#' onclick=\"details('".$this->_edit_form['sponsor_plan']."','".$plan['ID']."',"
                        ."'".$this->_popup['sponsor_plan']['h']."','".$this->_popup['sponsor_plan']['w']."');"
                        ."return false;\">"
                        .$plan['title']." ($".$plan['xml:cost'].")"
                        ."</a>"
                        ."</h4>"
                        :
                        "<h4>".$plan['title']." ($".$plan['xml:cost'].")"."</h4>\n"
                        )
                        .$result;
                }
            }
        }
        if ($out) {
            return
                 "<hr />\n"
                ."<h3>".$this->_cp['label_sponsors_local']."</h3>"
                ."<div class='section_header'>".$this->_cp['header_sponsors_local']."</div>"
                .$this->_community_record['sponsorship']
                .$out;
        }
    }

    protected function _draw_stats()
    {
        if (!$this->_current_user_rights['canViewStats']) {
            return;
        }
        if (
            !PIWIK_DEV &&
            (substr($_SERVER["SERVER_NAME"], 0, 8)=='desktop.' || substr($_SERVER["SERVER_NAME"], 0, 7)=='laptop.')
        ) {
            return;
        }
        $r =    $this->_record;
        $this->_html.=
             HTML::draw_section_tab_div('stats', $this->_selected_section)
            ."<h2>".$this->_cp['label_stats']."</h2>"
            ."<table cellpadding='2' cellspacing='0' border='1' class='member_stats'"
            ." summary='Table showing statistics for this member'>\n"
            ."  <thead>\n"
            ."    <tr>\n"
            ."      <th rowspan='3' class='st_date st_line st_bord_l st_bord_t'>Month</th>\n"
            ."      <th colspan='4' class='st_comm st_line st_bord_t'>"
            ."Community of ".$this->_community_record['title']
            ."</th>\n"
            ."      <th colspan='4' class='st_prof st_line st_bord_t'>".$r['title']." Profile</th>\n"
            ."      <th colspan='8' class='st_link st_bord_t st_bord_r'>"
            ."Link referrals for<br />\n".$r['title']
            ."</th>\n"
            ."    </tr>\n"
            ."    <tr>\n"
            ."      <th rowspan='2' class='st_comm st_bord_b'>Hits</th>\n"
            ."      <th rowspan='2' class='st_comm st_bord_b'>Visits</th>\n"
            ."      <th colspan='2' class='st_comm st_line'>Visit Time</th>\n"
            ."      <th rowspan='2' class='st_prof st_bord_b'>Hits</th>\n"
            ."      <th rowspan='2' class='st_prof st_bord_b'>Visits</th>\n"
            ."      <th colspan='2' class='st_prof st_line'>Visit Time</th>\n"
            ."      <th rowspan='2' class='st_link"
            .($r['link_website']   ? '' : " st_void")
            ."'>Website</th>\n"
            ."      <th rowspan='2' class='st_link"
            .($r['link_facebook']  ? '' : " st_void")
            ."'>Facebook</th>\n"
            ."      <th rowspan='2' class='st_link"
            .($r['link_twitter']   ? '' : " st_void")
            ."'>Twitter</th>\n"
            ."      <th rowspan='2' class='st_link st_bord_r"
            .($r['link_video']     ? '' : " st_void")
            ."'>Video</th>\n"
            ."    </tr>\n"
            ."    <tr>\n"
            ."      <th class='st_comm st_bord_b'>Avg</th>\n"
            ."      <th class='st_comm st_bord_b st_line'>Tot</th>\n"
            ."      <th class='st_prof st_bord_b'>Avg</th>\n"
            ."      <th class='st_prof st_bord_b st_line'>Tot</th>\n"
            ."    </tr>\n"
            ."  </thead>\n"
            ."  <tbody>\n";
        $community_url =    BASE_PATH.trim($this->_community_record['URL'], '/');
        $member_url =       $community_url.'/'.trim($r['name'], '/');
        for ($i=count($this->_stats_dates)-1; $i>=0; $i--) {
            $YYYYMM = $this->_stats_dates[$i];
            $comm =   $this->_stats[$YYYYMM]['visits'][$community_url];
            $prof =   $this->_stats[$YYYYMM]['visits'][$member_url];
            $link =   $this->_stats[$YYYYMM]['links'];
            $bord_b = ($i==0 ? " st_bord_b" : "");
    //      y($links);die;
            $this->_html.=
                 "    <tr>\n"
                ."      <td class='st_date st_bord_l st_line".$bord_b."'>".$YYYYMM."</td>\n"
                ."      <td class='st_comm".$bord_b."'>"
                .$comm['hits']
                ."</td>\n"
                ."      <td class='st_comm".$bord_b."'>"
                .$comm['visits']
                ."</td>\n"
                ."      <td class='st_comm".$bord_b."'>"
                .($comm['time_a'] ? format_seconds($comm['time_a']) : "&nbsp;")
                ."</td>\n"
                ."      <td class='st_comm st_line".$bord_b."'>"
                .($comm['time_t'] ? format_seconds($comm['time_t']) : "&nbsp;")
                ."</td>\n"
                ."      <td class='st_prof".$bord_b."'>"
                .$prof['hits']
                ."</td>\n"
                ."      <td class='st_prof".$bord_b."'>"
                .$prof['visits']
                ."</td>\n"
                ."      <td class='st_prof".$bord_b."'>"
                .($prof['time_a'] ? format_seconds($prof['time_a']) : "&nbsp;")
                ."</td>\n"
                ."      <td class='st_prof st_line".$bord_b."'>"
                .($prof['time_t'] ? format_seconds($prof['time_t']) : "&nbsp;")
                ."</td>\n"
                ."      <td class='st_link".$bord_b."'>"
                .($r['link_website']  && $link[$r['link_website']]['hits'] ?
                    $link[$r['link_website']]['hits']
                 :
                    "&nbsp;"
                )
                ."</td>\n"
                ."      <td class='st_link".$bord_b."'>"
                .($r['link_facebook'] && $link[$r['link_facebook']]['hits'] ?
                    $link[$r['link_facebook']]['hits']
                 :
                    "&nbsp;"
                )
                ."</td>\n"
                ."      <td class='st_link".$bord_b."'>"
                .($r['link_twitter']  && $link[$r['link_twitter']]['hits'] ?
                    $link[$r['link_twitter']]['hits']
                 :
                    "&nbsp;"
                )
                ."</td>\n"
                ."      <td class='st_link st_bord_r".$bord_b."'>"
                .($r['link_video']    && $link[$r['link_video']]['hits'] ?
                    $link[$r['link_video']]['hits']
                 :
                    "&nbsp;"
                )
                ."</td>\n"
                ."    </tr>\n";
        }
        $this->_html.=
             "  </tbody>\n"
            ."</table>\n"
            ."<div class='section_footer'>".$this->_cp['footer_stats']."</div>"
            ."</div>\n";
    }

    protected function _draw_title()
    {
        $this->_html.=
             "<h1 class='title'>"
            ."<a href=\"".BASE_PATH.trim($this->_community_record['URL'], '/')."\">"
            .$this->_community_record['title']."</a>"
            .": "
            ."<a href=\"".BASE_PATH.trim($this->_community_record['URL'], '/').'/'
            .trim($this->_record['name'], '/')."\">"
            .$this->_record['title']."</a>"
            ."</h1>";
    }

    protected function _draw_web_share($rss = '', $embed = '')
    {
        return
             "<div class='web_share'>"
            .($rss ?
                "<img class='icon rss' src='/img/spacer' alt='' title='RSS Feed'  />"
                ."<a rel=\"external\" title=\"Click to subscribe to this RSS Feed\""
                ." href=\"".$this->_base_path."/rss/".$rss."\">RSS</a>\n"
             :
                ""
            )
            .($embed ?
                 "<img class='icon share' src='/img/spacer' alt='' title='Embed on your website'  />"
                ."<a title=\"Embed this information on your website\" href=\"#\""
                ." onclick=\"return community_embed('".addslashes(htmlentities($this->_record['title']))."',"
                ."'".$this->_base_path."','".$embed."')\">Web</a>\n"
            :
                ""
            )
            ."</div>";
    }

    protected function _setup_initial($cp, $member_extension)
    {
        $this->_cp =    $cp;
        $this->_setup_initial_load_member($member_extension);
        $this->_print = get_var('print')=='1';
    }

    protected function _setup_initial_load_member($member_extension)
    {
        global $page_vars;
        $this->_ident =             "community_member_display";
        $this->_safe_ID =           Component_Base::get_safe_ID($this->_ident, $this->_instance);
        $this->_base_path =         BASE_PATH.trim($page_vars['path'], '/');
        $this->_member_extension =  $member_extension;
        $member_page_arr =      explode('/', $this->_member_extension);
        $this->_member_name =   array_shift($member_page_arr);
        $this->_member_page =   implode('/', $member_page_arr);
        if (!$this->get_member_profile($this->_cp['community_name'], $this->_member_name)) {
            header("Status: 404 Not Found", true, 404);
            throw new Exception("Member \"".$this->_member_name."\" not found.");
        }
    }

    protected function _setup($cp)
    {
        global $page_vars;
        $this->_cp =                $cp;
        $this->_setup_load_email_contacts();
        $this->_setup_load_user_rights();
        $this->_setup_load_edit_parameters();
        $this->_setup_load_community_record();
        $this->_setup_load_events_special();
        $this->_setup_load_community_members();
        $this->_setup_load_sponsors();
        $this->_setup_load_stats();
        $this->_setup_load_navigation_position();
        $this->_setup_tabs();
    }

    protected function _setup_load_community_record()
    {
        $community_name =   $this->_cp['community_name'];
        $this->_Obj_Community = new Community;
        $this->_Obj_Community->set_ID_by_name($community_name);
        if (!$this->_community_record = $this->_Obj_Community->load()) {
            header("Status: 404 Not Found", true, 404);
            throw new Exception("Community \"".$community_name."\" not found.");
        }
    }

    protected function _setup_load_community_members()
    {
        $this->_members =   $this->_Obj_Community->get_members();
    }

    protected function _setup_load_edit_parameters()
    {
        if (!$this->_current_user_rights['isEditor']) {
            return;
        }
        $this->_edit_form['pages'] =          'pages';
        $this->_edit_form['community'] =      'community';
        $this->_edit_form['member'] =         'community_member';
        $this->_edit_form['sponsor_plan'] =   'community.sponsorship-plans';
        $this->_popup['pages'] =              get_popup_size($this->_edit_form['pages']);
        $this->_popup['community'] =          get_popup_size($this->_edit_form['community']);
        $this->_popup['member'] =             get_popup_size($this->_edit_form['member']);
        $this->_popup['sponsor_plan'] =       get_popup_size($this->_edit_form['sponsor_plan']);
    }

    protected function _setup_load_email_contacts()
    {
        $this->_contacts =          $this->get_email_contacts();
    }

    protected function _setup_load_events_special()
    {
        $this->_events_special =          $this->get_events_upcoming($this->_cp['category_events_special']);
    }

    protected function _setup_load_navigation_position()
    {
        for ($i=0; $i<count($this->_members); $i++) {
            $m = $this->_members[$i];
            if ($m['ID'] == $this->_get_ID()) {
                $this->_nav_prev = ($i-1<0 ? $this->_members[count($this->_members)-1] : $this->_members[$i-1]);
                $this->_nav_next = ($i+1>count($this->_members)-1 ? $this->_members[0] : $this->_members[$i+1]);
                return;
            }
        }
    }

    protected function _setup_load_sponsors()
    {
        $Obj_GA = new Gallery_Album;
        $this->_sponsors_national_container = '//sponsors/national';
        $Obj_GA = new Gallery_Album;
        if (!$ID = $Obj_GA->get_ID_by_path($this->_sponsors_national_container)) {
            return;
        }
        $Obj_GA->_set_ID($ID);
        $this->_sponsors_national_records = $Obj_GA->get_children();
    }

    protected function _setup_load_stats()
    {
        global $system_vars;
        if (
            !PIWIK_DEV &&
            (substr($_SERVER["SERVER_NAME"], 0, 8)=='desktop.' || substr($_SERVER["SERVER_NAME"], 0, 7)=='laptop.')
        ) {
            return;
        }
        if (!$this->_current_user_rights['canViewStats']) {
            return;
        }
        $this->get_stats();
    }

    protected function _setup_load_user_rights()
    {
        $this->_current_user_rights['isEditor'] =
            get_person_permission("SYSEDITOR") ||
            get_person_permission("SYSAPPROVER") ||
            get_person_permission("SYSADMIN") ||
            get_person_permission("MASTERADMIN");
        $this->_current_user_rights['canEdit'] =
            $this->_current_user_rights['isEditor'] ||
            (get_person_permission("COMMUNITYADMIN") && $_SESSION['person']['memberID']==$this->_record['ID']);
        $this->_current_user_rights['canViewStats'] =
            $this->_current_user_rights['canEdit'];
    }

    protected function _setup_tabs()
    {
        $this->_section_tabs_arr[] =    array('ID'=>'profile','label'=>'Profile');
        if ($this->_record['type'] == 'ministerium') {
            $this->_section_tabs_arr[] =    array('ID'=>'members', 'label'=>$this->_cp['tab_members']);
        }
        if (
            $this->_cp['show_events_special']==1 &&
            $this->_events_special
        ) {
            $this->_section_tabs_arr[] =    array('ID'=>'special', 'label'=>$this->_cp['tab_events_special']);
        }
        if (
            $this->_cp['show_map']==1 &&
            ($this->_record['service_map_lat']!=0 || $this->_record['service_map_lon']!=0)
        ) {
            $this->_section_tabs_arr[] =    array('ID'=>'map','label'=>'Map');
        }
        if (
            $this->_cp['show_contact']==1
        ) {
            $this->_section_tabs_arr[] =     array('ID'=>'contact', 'label'=>$this->_cp['tab_contact']);
        }
        if (
            $this->_cp['show_articles']==1 &&
            ($this->_record['full_member'])
        ) {
            $this->_section_tabs_arr[] =   array('ID'=>'articles', 'label'=>$this->_cp['tab_articles']);
        }
        if (
            $this->_cp['show_events']==1 &&
            ($this->_record['full_member'] || $this->_record['primary_ministerialID'])
        ) {
            $this->_section_tabs_arr[] =   array('ID'=>'events', 'label'=>$this->_cp['tab_events']);
        }
        if (
            $this->_cp['show_calendar']==1 &&
            ($this->_record['full_member'] || $this->_record['primary_ministerialID'])
        ) {
            $this->_section_tabs_arr[] =   array('ID'=>'calendar', 'label'=>$this->_cp['tab_calendar']);
        }
        if (
            $this->_cp['show_news']==1 &&
            ($this->_record['full_member'] || $this->_record['primary_ministerialID'])
        ) {
            $this->_section_tabs_arr[] =   array('ID'=>'news', 'label'=>$this->_cp['tab_news']);
        }
        if (
            $this->_cp['show_podcasts']==1 &&
            ($this->_record['full_member'])
        ) {
            $this->_section_tabs_arr[] =   array(
                'ID'=>'podcasts',
                'label'=>($this->_record['type'] == 'church' ?
                    $this->_cp['tab_podcasts']
                 :
                    $this->_cp['tab_audio']
                )
            );
        }
        if (
            $this->_cp['show_stats']==1 &&
            $this->_current_user_rights['canViewStats'] && (
                PIWIK_DEV || (
                    substr($_SERVER["SERVER_NAME"], 0, 8)!=='desktop.' &&
                    substr($_SERVER["SERVER_NAME"], 0, 7)!=='laptop.'
                )
            )
        ) {
            $this->_section_tabs_arr[] =   array('ID'=>'stats', 'label'=>$this->_cp['tab_stats']);
        }
        if (
            $this->_cp['show_about']==1
        ) {
            $this->_section_tabs_arr[] =   array('ID'=>'about', 'label'=>$this->_cp['tab_about']);
        }
        $extra_space = 11;
        $width = floor($this->_cp['width']/count($this->_section_tabs_arr))-$extra_space;
        $total = 0;
        for ($i=0; $i<count($this->_section_tabs_arr); $i++) {
            $w =  floor($width);
            $total+=$w+$extra_space;
            if ($i==count($this->_section_tabs_arr)-1) {
                $w+=5+$extra_space+$this->_cp['width']-$total;
            }
            $this->_section_tabs_arr[$i]['width'] = $w;
        }
    }

    public function get_version()
    {
        return COMMUNITY_MEMBER_DISPLAY_VERSION;
    }
}
