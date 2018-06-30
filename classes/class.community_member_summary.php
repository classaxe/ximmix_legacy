<?php
define('COMMUNITY_MEMBER_SUMMARY_VERSION','1.0.17');
/*
Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)
*/
/*
Version History:
  1.0.17 (2013-10-28)
    1) Changes to support up to 8 contact names / email adreses in contact form

  (Older version history in class.community_member_summary.txt)
*/

class Community_Member_Summary extends Community_Member {
  var $_cp =                            '';
  var $_current_user_rights =           array();
  var $_html =                          '';
  var $_member_name =                   '';
  var $_member_page =                   '';
  var $_record =                        false;
  protected $_nav_prev =                false;
  protected $_nav_next =                false;
  protected $_Obj_Community;
  var $_selected_section =              '';
  var $_section_tabs_arr =              array();

  function draw($cp,$member_extension){
    $this->_setup($cp,$member_extension);
    $this->_draw_js();
    $this->_draw_css();
    $this->_html.= "<div class='summary'>\n";
    $this->_draw_nav();
    $this->_draw_letter();
    $this->_draw_checklist();
    $this->_html.= "</div>\n";
    return $this->_html;
  }

  protected function _draw_2col_entry($label, $content, $test, $c1='th'){
    if ($test==''){
      return;
    }
    return
       "  <tr>\n"
      ."    <".$c1.">".$label."</".$c1.">\n"
      ."    <td>".$content."</td>\n"
      ."  </tr>\n";
  }

  protected function _draw_css(){
    $min_width =        ($this->_current_user_rights['canEdit'] ? "770" : "680");
    $width_c1 =         190;
    $width_c2_min =     450;
    $width_c3 =         95;
    $min_width =        $width_c1+($this->_current_user_rights['canEdit'] ? $width_c2_min : 0)+$width_c3;
    $color_heading =    "#aab";
    $color_columns =    "#c0c0c8";
    $color_entry =      "#d0d0d8";
    $color_data_head =  "#ccc";
    $color_data_body =  "#f8f8f8";
    $css =
       ".summary .nav{\n"
      ."  ".($this->letter_mode ? "position:absolute; top: 10px; " : "")."width: 100%; text-align:center;\n"
      ."}\n"
      .".summary .nav input { padding: 0.2em; margin: 0.1em; text-align:center;}\n"
      .".summary .letter a {\n"
      ."  color: #00f; font-weight: bold;\n"
      ."}\n"
      .".summary .letter p {\n"
      ."  margin: 0.75em 0; font-size: 100%; text-align: justify; \n"
      ."}\n"
      .".summary .letter .address_label {\n"
      ."  margin: 1em 0 0 0.5em; height: 95px; width: 400px; padding: 5px;\n"
      ."}\n"
      .".summary .letter h2.subject {\n"
      ."  color: #000; width: 100%; text-align: center; font-size: 110%; margin: 1em 0 0.5em 0; \n"
      ."}\n"
      ."@media screen {\n"
      ."  .summary .letter {\n"
      ."    margin: 0 0 2em 0; padding: 0 0 2em 0; border-bottom: 2px solid #000;\n"
      ."  }\n"
      ."}\n"
      .".summary .tagline{\n"
      ."  float: left;\n"
      ."}\n"
      .".summary .logo{\n"
      ."  margin: 5px 5px 5px 0; border: none;\n"
      ."  border-radius: 5px 5px 5px 5px; -webkit-border-radius: 5px 5px 5px 5px; -moz-border-radius: 5px 5px 5px 5px;\n"
      ."}\n"
      .".summary .tagline h1{\n"
      ."  float: none;\n"
      ."}\n"
      .".summary .tagline p{\n"
      ."  clear: both;margin: 0.25em;\n"
      ."}\n"
      .".summary address{\n"
      ."  float: right; margin: 0.25em; text-align: right;\n"
      ."}\n"
      .".summary h1{\n"
      ."  font-size: 120%; margin: 0; \n"
      ."}\n"
      .".summary h2{\n"
      ."  color: #000; width: 100%; text-align: center; font-size: 110%; margin: 0.25 0 0.5em 0; \n"
      ."}\n"
      .".summary p{\n"
      ."  color: #000; width: 100%; font-size: 100%; margin: 0.5em 0; \n"
      ."}\n"
      .".summary .letter_sponsors {\n"
      ."  width: 100%; border-top: 1px solid #aaa; margin: 0; padding: 0;\n"
      ."}\n"
      .".summary .letter_sponsors td{\n"
      ."  text-align: center; vertical-align: middle; margin: 0.5em 0 0 0; padding: 0.5em 0 0 0;\n"
      ."}\n"
      .".summary .letter_sponsors td img{\n"
      ."  border: none;\n"
      ."}\n"
      .".summary .letter_sponsors th a{\n"
      ."  font-weight: normal; font-size: 75%;\n"
      ."}\n"
      ."table.summary_head{\n"
      ."  width: 100%; min-width: ".$min_width."px; background: ".$color_heading.";\n"
      ."  border: 1px solid #444; border-radius: 15px 15px 0 0; -webkit-border-radius: 15px 15px 0 0; -moz-border-radius: 15px 15px 0 0;border-width: 1px 1px 0 1px;\n"
      ."}\n"
      ."table.summary_head h2{\n"
      ."  font-size: 100%; margin: 0; color: #000; width: 100%; text-align: center;\n"
      ."}\n"
      ."table.summary_data{\n"
      ."  width: 100%; min-width: ".$min_width."px; border-collapse: collapse; font-size: 80%; page-break-inside:auto; \n"
      ."}\n"
      ."table.summary_data tr{\n"
      ."   page-break-inside:avoid; page-break-after:auto;\n"
      ."}\n"
      ."table.summary_data thead .c1, table.summary_data thead .c2, table.summary_data thead .c3{\n"
      ."  vertical-align: middle; text-align: center; background: ".$color_columns.";\n"
      ."}\n"
      ."table.summary_data .c1{\n"
      ."  vertical-align: top; width: ".($width_c1-10)."px; min-width: ".($width_c1-10)."px; background: ".$color_entry."; text-align: left; border: 1px solid #444; padding:5px;\n"
      ."}\n"
      ."table.summary_data .c2{\n"
      ."  vertical-align: top; border: 1px solid #444; padding:5px; \n"
      ."}\n"
      ."table.summary_data .c2 .google_map{\n"
      ."  margin:0;\n"
      ."}\n"
      ."table.summary_data .c3{\n"
      ."  vertical-align: top; width: ".($width_c3-10)."px; min-width: 80px; border: 1px solid #444;\n"
      ."}\n"
      ."table.summary_data .c3 label{\n"
      ."  font-size: 90%;\n"
      ."}\n"
      ."table.summary_data .hide_hint {\n"
      ."  font-weight: bold; color: #888; font-style: italic;\n"
      ."}\n"
      ."table.summary_data .entry_note {\n"
      ."  padding: 0 0 0.25em 1.25em;\n"
      ."}\n"
      ."table.summary_data .entry_note p {\n"
      ."  font-style:italic; color:#800;\n"
      ."}\n"
      ."table.summary_data a {\n"
      ."  font-weight: normal; text-decoration: none; color: #000;\n"
      ."}\n"
      ."table.summary_data a:hover {\n"
      ."  font-weight: bold; text-decoration: underline; color: #00f;\n"
      ."}\n"
      ."table.summary_data .profile_photos img {\n"
      ."  border: 1px solid #888; margin:1px;\n"
      ."}\n"
      ."table.summary_data .profile_photos img.selected {\n"
      ."  border: 2px solid #f00; margin: 0;\n"
      ."}\n"
      ."table.summary_data .ctrl {\n"
      ."  float: right;\n"
      ."}\n"
      ."#google_map_community_member_summary{\n"
      ."  border: 1px solid #888;\n"
      ."}\n"
      .".table_details {\n"
      ."  border: 1px solid #444; border-collapse: collapse; margin: 0; width: 100%;\n"
      ."}\n"
      .".table_details th, .table_details td {\n"
      ."  border: 1px solid #444;\n"
      ."}\n"
      .".table_details th{\n"
      ."  background: ".$color_data_head."; text-align: left; vertical-align: top; padding: 0.15em 1em 0.15em 0.5em;\n"
      ."}\n"
      .".table_details td{\n"
      ."  background: ".$color_data_body."; text-align: left; padding: 0.15em 1em 0.15em 0.5em;\n"
      ."}\n"
      ."table.summary_signoff{\n"
      ."  width: 100%; margin: 0.5em 0 0 0; min-width: ".$min_width."px; border-collapse: collapse; font-size: 80%; page-break-inside:avoid;\n"
      ."}\n"
      ."table.summary_signoff .c1{\n"
      ."  vertical-align: top; width: ".($width_c1-10)."px; min-width: ".($width_c1-10)."px; background: ".$color_entry."; text-align: left; border: 1px solid #444; padding:5px;\n"
      ."}\n"
      ."table.summary_signoff .c2{\n"
      ."  vertical-align: top; border: 1px solid #444; padding:5px; \n"
      ."}\n"
      ."table.summary_signoff .ctrl {\n"
      ."  float: right;\n"
      ."}\n"
      ."table.summary_signoff .entry_note {\n"
      ."  padding: 0 0 0.5em 1.25em;\n"
      ."}\n"
      ."table.summary_signoff .entry_note p {\n"
      ."  font-style:italic; color:#800;\n"
      ."}\n"
      ."table.summary_signoff .hide_hint {\n"
      ."  font-weight: bold; color: #888; font-style: italic;\n"
      ."}\n"
      ."@media print {\n"
      ."  .pagebreak { page-break-after:always; }\n"
      ."  table.summary_data .ctrl,    table.summary_data .hide_hint    { display: none; }\n"
      ."  table.summary_signoff .ctrl, table.summary_signoff .hide_hint { display: none; }\n"
      ."}\n"
      ;
    Page::push_content('style',$css);
  }

  protected function _draw_js(){
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
    $js =
       "function edit_letter(){"
      ."  var h =\n"
      ."    \"<textarea id='letter_html' style='width:780px;height:560px;margin:5px'>\"+\n"
      ."    \$('#letter')[0].innerHTML+\n"
      ."    \"<\/textarea>\";\n"
      ."  popup_dialog('Edit Letter',h,800,600,'OK','Cancel','edited_letter()');\n"
      ."}\n"
      ."function edited_letter(){\n"
      ."  \$('#letter')[0].innerHTML = \$('#letter_html').val()\n"
      ."}\n"
      ."function toggle_q(question){\n"
      ."  div_toggle('qa_'+question);\n"
      ."  div_toggle('qb_'+question);\n"
      ."  div_toggle('qc_'+question);\n"
      ."  div_toggle('qd_'+question);\n"
      ."  div_toggle('qe_'+question);\n"
      ."}\n"
      ."function set_all(){\n"
      ."  var div, i, j, id, state;\n"
      ."  if (typeof set_all.state=='undefined'){\n"
      ."    set_all.state = false;\n"
      ."  }\n"
      ."  state = set_all.state;\n"
      ."  set_all.state = !set_all.state;\n"
      ."  geid('ctrl_show').style.display = (state ? 'none' : '');\n"
      ."  geid('ctrl_hide').style.display = (state ? '' : 'none');\n"
      ."  for(i=0; i<15; i++){\n"
      ."    if (div = geid('qa_'+i)){\n"
      ."      div.style.display=(state ? 'none' : '');\n"
      ."    }\n"
      ."    if (div = geid('qb_'+i)){\n"
      ."      div.style.display=(state ? '' : 'none');\n"
      ."    }\n"
      ."    if (div = geid('qc_'+i)){\n"
      ."      div.style.display=(state ? '' : 'none');\n"
      ."    }\n"
      ."    if (div = geid('qd_'+i)){\n"
      ."      div.style.display=(state ? '' : 'none');\n"
      ."    }\n"
      ."    if (div = geid('qe_'+i)){\n"
      ."      div.style.display=(state ? 'none' : '');\n"
      ."    }\n"
      ."  }\n"
      ."}\n";
    Page::push_content('javascript',$js);
  }

  protected function _draw_nav(){
    global $page_vars;
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
    $this->_html.=
       "<div class='nav noprint'>\n"
      ."<input type=\"button\" class=\"form_button\" style=\"width:3.2em\" title=\"View ".$this->_nav_first['title']."\" value=\"&lt;&lt\""
      ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'],'/')."/".$this->_nav_first['name']
      ."?print=1&amp;letter_mode=".$this->letter_mode."'\" />"
      ."<input type=\"button\" class=\"form_button\" style=\"width:2.6em\" title=\"View ".$this->_nav_prev['title']."\" value=\"&lt;\""
      ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'],'/')."/".$this->_nav_prev['name']
      ."?print=1&amp;letter_mode=".$this->letter_mode."'\" />"
      ."<input type=\"button\" class=\"form_button\" style=\"width: 6em\" title=\"Toggle Letter Mode\" value=\""
      .($this->letter_mode=='1' ? 'Summary' : 'Letter')
      ."\""
      ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'],'/')."/".$this->_record['name']
      ."?print=1".($this->letter_mode=='1' ? '' : '&amp;letter_mode=1')."'\" />"
      ."<input type=\"button\" class=\"form_button\" style=\"width:2.6em\" title=\"View ".$this->_nav_next['title']."\" value=\"&gt;\""
      ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'],'/')."/".$this->_nav_next['name']
      ."?print=1&amp;letter_mode=".$this->letter_mode."'\" />"
      ."<input type=\"button\" class=\"form_button\" style=\"width:3.2em\" title=\"View ".$this->_nav_last['title']."\" value=\"&gt;&gt;\""
      ." onclick=\"document.location='".BASE_PATH.trim($page_vars['relative_URL'],'/')."/".$this->_nav_last['name']
      ."?print=1&amp;letter_mode=".$this->letter_mode."'\" />\n"
      .($this->letter_mode=='1' ?
          "<br />\n"
         ."<input type=\"button\" class=\"form_button\" style=\"width: 6em\" title=\"Edit Member\" value=\"Member\""
         ." onclick=\"details('community_member',".$this->_record['ID'].",780,1020,'','');return false\" />"
         .($this->_record['link_website'] ?
              "<input type=\"button\" class=\"form_button\" style=\"width: 6em\" title=\"View website\" value=\"Website\""
             ." onclick=\"popWin('".$this->_record['link_website']."','website_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',920,620,1);return false\" />"
           :
              ""
          )
         ."<input type=\"button\" class=\"form_button\" style=\"width: 6em\" title=\"Edit Gallery Profile Images\" value=\"Photos\""
         ." onclick=\"popWin('".BASE_PATH.'communities/'.$this->_cp['community_name']."/gallery/".$this->_record['name']."/profile?print=1','gallery_profile_'+_CM.ID,'location=1,status=1,scrollbars=1,resizable=1',920,620,1);return false\" />\n"
         ."<br /><input type=\"button\" class=\"form_button\" style=\"width: 6em;\" title=\"Edit form letter\" value=\"Letter\""
         ." onclick=\"edit_letter()\" />"
         ."<input type=\"button\" class=\"form_button\" style=\"width: 6em\" title=\"Edit letter template\" value=\"Template\""
         ." onclick=\"details('pages',".$this->_pageID.",620,865,'','');return false;\" />\n"
       :
          ""
       )
      ."</div>\n";
  }

  protected function _draw_page_header($pagebreak=false){
    if ($this->_current_user_rights['canEdit'] && !$this->letter_mode){
      return;
    }
    $c_arr = explode('//',$this->_community_record['URL_external']);
    return
       "<div>\n"
      ."<div class='tagline'>\n"
      ."<a href=\"/\" rel=\"external\"><img alt=\"Ecclesiact ChurchesInYourTown.ca - Connecting The Body of Christ\""
      ." class=\"css3 logo\" height=\"84\" width=\"270\""
      ." src=\"/img/user/layout/ecclesiact_churches_in_your_town.gif\" /></a><br />\n"
      ."<h1><i>Connecting Churches...<br />&nbsp; &nbsp; &nbsp; &nbsp; Witnessing to Communities</i></h1>\n"
      ."</div>\n"
      ."<address><b>ChurchesInYourTown.ca</b><br />\n"
      ."c/o ClassAxe Multimedia Inc.<br />\n"
      ."264 Conestoga Avenue<br />\n"
      ."Richmond Hill, ON, L4C 2H2\n"
      ."<a style=\"display:block; color:#00f;font-style:normal;margin:0.25em 0\" href=\"mailto:info@ChurchesInYourTown.ca?subject=".str_replace(' ','%20',$c_arr[1].' - '.$this->_record['title']." - Member Checklist")."\">info@ChurchesInYourTown.ca</a>\n"
      ."Phone / Fax: <b>(416) 410-9240</b>\n"
      ."<span style='display:block;height:0.5em'>&nbsp;</span>\n<span style='font-style:normal'>"
      .format_date(get_timestamp(),'l j\<\s\u\p\>S\</\s\u\p\> M Y')
      ."</span></address>\n"
      ."</div>"
      ."<div class='clear'>&nbsp;</div>\n";
  }

  protected function _draw_items($content){
    $c_arr = explode('//',$this->_community_record['URL_external']);
    $r =    $this->_record;
    $prefix = 'mailing_addr_';
    if ($r[$prefix.'line1']=='' && $r[$prefix.'line2']=='' && $r[$prefix.'city']=='' && $r[$prefix.'sp']=='' && $r[$prefix.'postal']==''){
      $prefix = 'service_addr_';
    }
    $ATTN =
       (trim($r['contact_NTitle'].$r['contact_NFirst'].$r['contact_NLast'])!='' ?
         $r['contact_NTitle'].' '
        .$r['contact_NFirst'].' '
        .$r['contact_NLast']
       :
         "Minister in Charge"
       );
    $ADDRESS =
       ($r['mailing_addr_line1']!='' && $r['mailing_addr_line1']!=$r['service_addr_line1'] ? "c/o: " : "")
      .$r[$prefix.'line1']."<br />\n"
      .($r[$prefix.'line2']!='' ? $r[$prefix.'line2']."<br />\n" : "")
      .$r[$prefix.'city'].", ".$r[$prefix.'sp'].($r[$prefix.'postal'] ? ", ".$r[$prefix.'postal'] : "");
    $ADDRESS_BLOCK =
       "<b>Attn: ".$ATTN."</b><br />\n"
      .$r['title']."<br />\n"
      .$ADDRESS
      ."<br />\n";
    $CHURCH_NAME =      $r['title'];
    $GREETING_NAME =    ($r['contact_NGreeting'] ? $r['contact_NGreeting'] : "Sir or Madam");
    $HEADER =           $this->_draw_page_header();
    $SITE_LINK =        "<a href=\"".$this->_community_record['URL_external']."\" rel=\"external\">".$c_arr[1]."</a>";
    $SITE_SERVER =      $c_arr[1];
    $SITE_TITLE =       $this->_community_record['title'];
    $SPONSORS =         $this->_draw_letter_sponsors(800);
    $replace = array(
      '[[ADDRESS_BLOCK]]' =>    $ADDRESS_BLOCK,
      '[[CHURCH_NAME]]' =>      $CHURCH_NAME,
      '[[GREETING_NAME]]' =>    $GREETING_NAME,
      '[[HEADER]]' =>           $HEADER,
      '[[SITE_LINK]]' =>        $SITE_LINK,
      '[[SITE_SERVER]]' =>      $SITE_SERVER,
      '[[SITE_TITLE]]' =>       $SITE_TITLE,
      '[[SPONSORS]]' =>         $SPONSORS
    );
    return strtr($content,$replace);
  }

  protected function _draw_letter(){
    if (!$this->_current_user_rights['canEdit'] || !$this->letter_mode){
      return;
    }
    $Obj_Page =         new Page;
    $this->_pageID =    $Obj_Page->get_ID_by_path('//template-welcome-letter/');
    $Obj_Page->_set_ID($this->_pageID);
    $content =          $Obj_Page->get_field('content');
    $this->_html.=      "<div id='letter'>".$this->_draw_items($content)."</div>";
  }

  protected function _draw_letter_sponsors($width){
    if (!$images = $this->get_sponsors_national()){
      return;
    }
    $cell_width =   $width/count($images);
    $image_width =  $cell_width-20;
    $out =
       "<table cellpadding='0' cellspacing='0' border='0' class='letter_sponsors'>"
      ."  <tbody>\n"
      ."    <tr>\n";
    foreach ($images as $image){
      $img =
        ($image['thumbnail_small'] && file_exists('.'.$image['thumbnail_small']) ?
          $image['thumbnail_small']
        :
          '/640x480-photo-unavailable.png'
        );
      $out.=
         "      <td style='width:".(100/count($images))."%'>\n"
        ."<a href=\"".$image['URL']."/\" rel=\"external\">"
        ."<img src=\"".BASE_PATH."img/resize/".trim($img,'/')."?width=".$image_width."&amp;height=100&amp;maintain=1\""
        ." alt=\"".$image['title']."\" />"
        ."</a>\n"
        ."</td>\n";
    }
    $out.=
       "    </tr>\n"
      ."    <tr>\n";
    foreach ($images as $image){
      $url_arr = explode('//',$image['URL']);
      $out.= "      <th><a href=\"".$image['URL']."/\" rel=\"external\">".$url_arr[1]."</a></th>\n";
    }
    $out.=
       "    </tr>\n"
      ."  </tbody>\n"
      ."</table>\n";
    return $out;
  }

  protected function _draw_checklist(){
    $this->_html.= $this->_draw_page_header(true);
    $this->_draw_checklist_preamble();
    $this->_draw_checklist_table_heading();
    $this->_draw_checklist_table_open('summary_data');
    $this->_draw_checklist_table_head();
    $this->_draw_checklist_member_name();
    $this->_draw_checklist_denomination();
    $this->_draw_checklist_languages();
    $this->_draw_checklist_photos();
    $this->_draw_checklist_service_address();
    $this->_draw_checklist_office_address();
    $this->_draw_checklist_mailing_address();
    $this->_draw_checklist_service_map();
    $this->_draw_checklist_internet_services();
    $this->_draw_checklist_service_notes();
    $this->_draw_checklist_service_times();
    $this->_draw_checklist_office_notes();
    $this->_draw_checklist_office_hours();
    $this->_draw_checklist_office_phone();
    $this->_draw_checklist_office_email();
    $this->_draw_checklist_designated_contact();
    $this->_draw_checklist_table_close();
    $this->_draw_checklist_comments_signoff();

  }

  protected function _draw_checklist_comments_signoff(){
    if (!$this->letter_mode){
      return;
    }
    $this->_draw_checklist_table_open('summary_signoff');
    $this->_draw_checklist_table_entry("Prayer Requests", "<div style='height:60px'>&nbsp;</div>", false, "<p>May we pray for you? Please let us know.</p>");
    $this->_draw_checklist_table_entry("Potential Partners", "<div style='height:60px'>&nbsp;</div>", false, "<p>Please call us if you'd like more information.</p>");
    $this->_draw_checklist_table_entry("Comments / Suggestions", "<div style='height:60px'>&nbsp;</div>", false, "<p>We'd love to hear from you. Attach another sheet if you like.</p>");
    $this->_draw_checklist_table_entry('Please sign and date', "<div style='height:30px'>&nbsp;</div>", false);
    $this->_draw_checklist_table_close();
  }

  protected function _draw_checklist_community_url(){
    $url =      trim($this->_community_record['URL_external'],'/').'/'.$this->_record['name'];
    $content =  "<a href=\"".$url."\" rel=\"external\">".$url."</a>";
    $this->_draw_checklist_table_entry('Community URL', $content);
  }

  protected function _draw_checklist_denomination(){
    $this->_draw_checklist_table_entry('Denomination', $this->_record['custom_1']);
  }

  protected function _draw_checklist_designated_contact(){
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
    $heading = "Our Designated Contact";
    $content =
       "<table class='table_details' cellpadding='0' cellspacing='0' border='1' summary='Table showing internet services'>"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th style='width:34%'>Name</th>\n"
      ."      <th style='width:33%'>Email Address</th>\n"
      ."      <th style='width:33%'>Telephone</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n"
      ."    <tr>\n"
      ."      <td>"
      .$this->_record['contact_NTitle'].' '
      .$this->_record['contact_NFirst'].' '
      .$this->_record['contact_NMiddle'].' '
      .$this->_record['contact_NLast'].'&nbsp;'
      ."</td>\n"
      ."      <td>".$this->_record['contact_PEmail']."</td>\n"
      ."      <td>".$this->_record['contact_Telephone']."</td>\n"
      ."    </tr>\n"
      ."  </tbody>\n"
      ."</table>\n";
    $note = "<p style='margin-bottom:0'>For internal use only.</p>";
    $this->_draw_checklist_table_entry($heading, $content, true, $note);
  }

  protected function _draw_checklist_languages(){
    $Obj_LA = new Language_Assign;
    $languages = $Obj_LA->get_text_csv_for_assignment($this->_get_assign_type(), $this->_record['ID']);
    $this->_draw_checklist_table_entry('Languages for Services', $languages);
  }

  protected function _draw_checklist_internet_services(){
    $entries = array(
      array('link_website',  'Website',  'width:16px;height:16px;margin:0px 5px 2px 0px;background-position:-800px 0px;float:left;'),
      array('link_facebook', 'Facebook', 'width:14px;height:14px;margin:0px 5px 2px 0px;background-position:-3147px 0px;float:left;'),
      array('link_twitter',  'Twitter',  'width:14px;height:14px;margin:2px 5px 2px 0px;background-position:-5420px 0px;float:left;'),
      array('link_video',    'Video',    'width:16px;height:16px;margin:0px 5px 2px 0px;background-position:-7966px 0px;float:left;')
    );
    $content =
       "<table class='table_details' cellpadding='0' cellspacing='0' border='1' summary='Table showing internet services'>"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th style='width:7em;'>Category</th>\n"
      ."      <th>URL</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach($entries as $e){
      $label =  "<img class='icon' src='/img/spacer' alt='".$e[1]."' title='".$e[1]."' style='".$e[2]."'/>".$e[1];
      $entry =  ($this->_record[$e[0]] ? "<a rel=\"external\" href=\"".$this->_record[$e[0]]."\">".$this->_record[$e[0]]."</a>" : "");
      $content.= $this->_draw_2col_entry($label,$entry,true);
    }
    $content.=
       "  </tbody>\n"
      ."</table>\n";
    $this->_draw_checklist_table_entry('Internet Presence', $content,true,'<p>* Video is for Youtube, UStream, Livestream, Vimeo or other video provider channel page</p>');
  }

  protected function _draw_checklist_mailing_address(){
    $r = $this->_record;
    $prefix = 'mailing_addr_';
    $content =
       $r[$prefix.'line1']
      .($r[$prefix.'line2']!='' ? ", ".$r[$prefix.'line2'] : "")
      .($r[$prefix.'city'] ? ", ".$r[$prefix.'city'] : "")
      .($r[$prefix.'sp'] ? ", ".$r[$prefix.'sp'] : "")
      .($r[$prefix.'postal'] ? ", ".$r[$prefix.'postal'] : "");
    if ($r[$prefix.'line1']=='' && $r[$prefix.'line2']=='' && $r[$prefix.'city']=='' && $r[$prefix.'sp']=='' && $r[$prefix.'postal']==''){
      $content = "(Same as for services)";
    }
    $this->_draw_checklist_table_entry('Address for Mail', $content);
  }

  protected function _draw_checklist_member_name(){
    $content =
      ($this->_current_user_rights['isEditor'] ?
          "<a"
         ." title=\"Click to edit this member\""
         ." href=\"".BASE_PATH."details/".$this->_report."/".$this->_record['ID']."\""
         ." onclick=\"details('".$this->_report."',".$this->_record['ID'].",".$this->_popup['h'].",".$this->_popup['w'].",'','');return false;\">"
         .htmlentities($this->_record['title'])
         ."</a>"
       : htmlentities($this->_record['title'])
      );
    $this->_draw_checklist_table_entry('Church Name', "<b>".$content."</b>");
  }

  protected function _draw_checklist_office_address(){
    $r = $this->_record;
    $prefix = 'office_addr_';
    $content =
       $r[$prefix.'line1']
      .($r[$prefix.'line2']!='' ? ", ".$r[$prefix.'line2'] : "")
      .($r[$prefix.'city'] ? ", ".$r[$prefix.'city'] : "")
      .($r[$prefix.'sp'] ? ", ".$r[$prefix.'sp'] : "")
      .($r[$prefix.'postal'] ? ", ".$r[$prefix.'postal'] : "");
    if ($r[$prefix.'line1']=='' && $r[$prefix.'line2']=='' && $r[$prefix.'city']=='' && $r[$prefix.'sp']=='' && $r[$prefix.'postal']==''){
      $content = '(Same as for services)';
    }
    $this->_draw_checklist_table_entry('Address for Office', $content);
  }

  protected function _draw_checklist_office_email(){
    if (!$this->_current_user_rights['canEdit']){
      return;
    }
    $heading =  "Web Contact Form";
    $content =
       "<table class='table_details' cellpadding='0' cellspacing='0' border='1' summary='Table showing internet services'>"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th style='width:50%'>Name / Department</th>\n"
      ."      <th style='width:50%'>Email Address <span style='color:#800'>(*See note)</span></th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    for ($i=0; $i<8; $i++){
      $name =   (isset($this->_contacts[$i]) ? $this->_contacts[$i]['name'] : "&nbsp;");
      $email =  (isset($this->_contacts[$i]) ? $this->_contacts[$i]['email'] : "&nbsp;");
      if ($i<5 || isset($this->_contacts[$i])){
        $content.= $this->_draw_2col_entry($name,$email,true,"td");
      }
    }
    $content.=
       "  </tbody>\n"
      ."</table>\n";
    $note =     "<p>* Email addresses are not shown on the website.</p><p>\nThis is to help protect you from spam.</p>";
    $this->_draw_checklist_table_entry($heading, $content, true, $note);
  }

  protected function _draw_checklist_office_hours(){
    $days =         explode(',','Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
    $r =            $this->_record;
    $has_hours =    false;
    $content =
       "<table class='table_details' cellpadding='0' cellspacing='0' border='1'>"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th style='width:7em;'>Day</th>\n"
      ."      <th>Times</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach($days as $d){
      $hours = $r['xml:Church_Office_'.substr($d,0,3)];
      $content.= $this->_draw_2col_entry($d,$hours,true);
    }
    $content.=
       "  </tbody>\n"
      ."</table>\n";
    $this->_draw_checklist_table_entry('Office Hours', $content);
  }

  protected function _draw_checklist_office_notes(){
    $this->_draw_checklist_table_entry('Office Notes', $this->_record['office_notes']);
  }

  protected function _draw_checklist_office_phone(){
    $r =            $this->_record;
    $content =
       "<table class='table_details' cellpadding='0' cellspacing='0' border='1'>"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th style='width:50%'>Name / Department</th>\n"
      ."      <th style='width:50%'>Telephone</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n"
      .$this->_draw_2col_entry(($r['office_phone1_lbl'] ? $r['office_phone1_lbl'] : "&nbsp;"),$r['office_phone1_num'],true,'td')
      .$this->_draw_2col_entry(($r['office_phone2_lbl'] ? $r['office_phone2_lbl'] : "&nbsp;"),$r['office_phone2_num'],true,'td')
      ."  </tbody>\n"
      ."</table>\n";
    $this->_draw_checklist_table_entry('Telephone Numbers', $content);
  }

  protected function _draw_checklist_photos(){
    $photos = array();
    if ($slides = $this->get_member_profile_images()){
      foreach ($slides as $slide){
        if ($slide['enabled']){
          $img =
            ($slide['thumbnail_small'] && file_exists('.'.$slide['thumbnail_small']) ?
              $slide['thumbnail_small']
          :
            '/640x480-photo-unavailable.png'
          );
          $photos[] =
             "<a href=\"".BASE_PATH."img/wm".$img."/\" rel=\"external\">"
            ."<img src=\"".BASE_PATH."img/width/108/".$img."\""
            .($this->_record['featured_image']==$img ? " class=\"selected\" title=\"Featured Image\"" : "")
            ." width=\"108\""
            ." alt=\"".$this->_record['name']."\" />"
            ."</a>\n";
        }
      }
      $heading =  "Profile Photos";
      $content =  "<div class='profile_photos'>".implode($photos)."</div>\n";
      $note =     "<p>Photos by our staff.</p><p>Your main profile image<br /> is shown highlighted.</p>";
    }
    else {
      $img =
        ($this->_record['featured_image'] && file_exists('.'.$this->_record['featured_image']) ?
          $this->_record['featured_image']
        :
          '/640x480-photo-unavailable.png'
        );
    $photos[] =
       "<a href=\"".BASE_PATH."img/wm".$img."/\" rel=\"external\">"
      ."<img src=\"".BASE_PATH."img/width/200/".$img."\""
      ." width=\"200\""
      ." alt=\"".$this->_record['name']."\" />"
      ."</a>\n";
      $heading =  "Profile Photos";
      $content =  "<div class='profile_photos'>".implode($photos)."</div>\n";
      $note =
        $this->_record['featured_image'] && file_exists('.'.$this->_record['featured_image']) ?
          "<p>Photo by our staff.</p>"
        :
          "<p>(No photo yet)</p>";
    }
    $this->_draw_checklist_table_entry($heading, $content, true, $note);
  }

  protected function _draw_checklist_preamble(){
    if ($this->_current_user_rights['canEdit'] && !$this->letter_mode){
      return;
    }
    $this->_html.=
       "<h2>".$this->_record['title']."</h2>"
      .($this->_current_user_rights['canEdit']?
         "<p>Please help us to ensure the accuracy of your community website profile by reviewing this checklist.</p>"
        ."<p>Kindly indicate any changes required (include a separate sheet if necessary), then sign and date the form"
        ." and mail it to us at the address above using the self-addressed stamped envelope provided.</p>\n"
        ."<p>We sincerely appreciate your participation in this matter.</p>\n"
       :
         ""
       )
       ."";
  }

  protected function _draw_checklist_service_address(){
    $r = $this->_record;
    $content =
       $r['service_addr_line1']
      .($r['service_addr_line2'] ? ", ".$r['service_addr_line2'] : "")
      .", ".$r['service_addr_city']
      .", ".$r['service_addr_sp']
      .($r['service_addr_postal'] ? ", ".$r['service_addr_postal'] : "");
    $this->_draw_checklist_table_entry('Address for Services', $content);
  }

  protected function _draw_checklist_service_map(){
    $Obj_Map =      new Google_Map('community_member_summary',SYS_ID);
    $Obj_Map->map_centre($this->_record['service_map_lat'],$this->_record['service_map_lon'],16);
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
    $Obj_Map->add_icon("/UserFiles/Image/map_icons/".$this->_record['type']."/",$this->_record['type']);
    $marker_html =
       "<img style='float:left;margin:0 4px 0 0;border:1px solid #888'"
      ." width='".$this->_cp['profile_map_photo_width']."'"
      ." src='".$featured_image."' alt='".$this->_record['name']."'>"
      ."<strong>".htmlentities($this->_record['title'])."</strong><br />"
      ."<div>"
      .$this->_record['service_addr_line1']
      .($this->_record['service_addr_line2'] ? ' &bull; '.$this->_record['service_addr_line2'] : '')
      ."<br />".$this->_record['service_addr_city'].' &bull; '.$this->_record['service_addr_postal'];


     $Obj_Map->add_marker_with_html(
      $this->_record['service_map_lat'],
      $this->_record['service_map_lon'],
      $marker_html,
      $this->_record['ID'],
      false,
      true,
      $this->_record['type'],
      true,
      'Click for info'
    );
//    $Obj_Map->add_control_type();
//    $Obj_Map->add_control_large();
    $args =     array(
      'map_width'=>460,
      'map_height'=>280

    );
    $this->_draw_checklist_table_entry('Map for Services', $Obj_Map->draw($args));
  }

  protected function _draw_checklist_service_notes(){
    $this->_draw_checklist_table_entry('Service Notes', $this->_record['service_notes']);
  }

  protected function _draw_checklist_service_times(){
    $days = explode(',','Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday');
    $dd =   '';
    $services = "";
    $servicetimes_known = false;
    foreach($days as $day){
      $entries = explode("\n", trim($this->_record['service_times_'.strToLower(substr($day,0,3))]));
      $rowspan = 0;
      for($i=0; $i<count($entries); $i++){
        $servicetimes_known = true;
        $entry = $entries[$i];
        if (trim($entry)!=''){
          $rowspan++;
        }
      }
      for($i=0; $i<count($entries); $i++){
        $entry = $entries[$i];
        if (trim($entry)!=''){
          $bits = explode(' ',$entry);
          $services.=
             "  <tr>\n"
            .($i==0 ? "    <th".($rowspan>1 ? " rowspan='".$rowspan."'" : "").">".$day."</th>\n" : '')
            ."    <td>".array_shift($bits)."</td>\n"
            ."    <td>".implode('/<br />',explode('/',implode(' ',$bits)))."</td>\n"
            ."  </tr>\n";
          $dd= $day;
        }
      }
    }
    if ($services==''){
      $services =
         "  <tr>\n"
        ."    <th>&nbsp;<br /><br /><br /></th>\n"
        ."    <td>&nbsp;</td>\n"
        ."    <td>&nbsp;</td>\n"
        ."  </tr>\n";
    }
    $content =
       "<table class='table_details' cellpadding='0' cellspacing='0' border='1' summary='Table showing service times'>"
      ."  <thead>\n"
      ."    <tr>\n"
      ."      <th style='width:7em'>Day</th>\n"
      ."      <th style='width:8.5em'>Time</th>\n"
      ."      <th>Details</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n"
      .$services
      ."  </tbody>\n"
      ."</table>";
    $this->_draw_checklist_table_entry('Service Times', $content);
  }

  protected function _draw_checklist_table_close(){
    $this->_html.=
       "</table>\n";
  }

  protected function _draw_checklist_table_entry($label,$content,$controls=true,$note=false){
    if (!$this->_current_user_rights['canEdit']){
      $this->_html.=
         "  <tr>\n"
        ."    <th class='c1'>".$label."</th>\n"
        ."    <td class='c2'>".$content."</td>\n"
        ."  </tr>\n";
      return;
    }
    if (!isset($this->_q)){
      $this->_q = 1;
    }
    $q =  $this->_q;
    $this->_html.=
       "  <tr>\n"
      ."    <th class='c1' onclick=\"toggle_q(".$q.")\">\n"
      ."<div class='ctrl'>["
      ."<span id='qa_".$q."' style='display:none'>+</span>"
      ."<span id='qb_".$q."'>-</span>"
      ."]</div>\n"
      ."      ".$q.'. '.$label
      .($note ? "      <div id=\"qc_".$q."\" class='entry_note'>".$note."</div>\n" : "")
      ."    </th>\n"
      ."    <td class='c2'".($controls ? "" : " colspan='2'").">\n"
      ."      <div id=\"qd_".$q."\">".$content."</div>\n"
      ."      <div id=\"qe_".$q."\" style='display:none' class='hide_hint'>Hidden - click heading to show</div>\n"
      ."    </td>\n"
      .($controls ?
         "    <td class='c3'>\n"
        ."      <label><input type='radio' name='q_".$q."'/>Yes</label>\n"
        ."      <label><input type='radio'  name='q_".$q."'/>No</label>\n"
        ."    </td>\n"
        :
         ""
       )
      ."  </tr>\n";
    $this->_q++;
    return;
  }

  protected function _draw_checklist_table_head(){
    if (!$this->_current_user_rights['canEdit']){
      $this->_html.=
         "<thead>\n"
        ."  <tr>\n"
        ."    <th class='c1'>Entry</th>\n"
        ."    <th class='c2'>Setting</th>\n"
        ."  </tr>\n"
        ."</thead>\n";
      return;
    }
    $this->_html.=
       "<thead>\n"
      ."  <tr>\n"
      ."    <th class='c1' onclick=\"set_all(0)\">"
      ."Entry"
      ."<div class='ctrl'>["
      ."<span id='ctrl_show' style='display:none'>+</span>"
      ."<span id='ctrl_hide'>-</span>"
      ."]</div>\n"
      ."</th>\n"
      ."    <th class='c2'>Current Setting</th>\n"
      ."    <th class='c3'>Correct?</th>\n"
      ."  </tr>\n"
      ."</thead>\n";
  }

  protected function _draw_checklist_table_heading(){
    $c_arr = explode('//',$this->_community_record['URL_external']);
    if (!$this->_current_user_rights['canEdit']){
      $this->_html.=
         "<table class='summary_head' border='0' cellpadding='0' cellspacing='0'>\n"
        ."  <tr>\n"
        ."    <th colspan='2'><h2>".$c_arr[1]." - Print-Friendly View</h2></th>\n"
        ."  </tr>\n"
        ."</table>\n";
      return;
    }
    $this->_html.=
       "<table class='summary_head' border='0' cellpadding='0' cellspacing='0'>\n"
      ."  <tr>\n"
      ."    <th colspan='3'><h2>".$c_arr[1]." - Member Checklist</h2></th>\n"
      ."  </tr>\n"
      ."</table>\n";
  }

  protected function _draw_checklist_table_open($classname){
    $this->_html.=
       "<table class='".$classname."' border='1' cellpadding='0' cellspacing='0'>\n";
  }

  protected function _setup($cp,$member_extension){
    global $page_vars;
    $this->_base_path =         BASE_PATH.trim($page_vars['path'],'/');
    $this->_cp =                $cp;
    $this->_member_extension =  $member_extension;
    $this->_setup_load_member();
    $this->_setup_load_email_contacts();
    $this->_setup_load_user_rights();
    $this->_setup_load_edit_parameters();
    $this->_setup_load_community_record();
    $this->_setup_load_community_members();
    $this->_setup_load_navigation_position();
    $this->_setup_load_page_ID();
    $this->_setup_load_mode();
    $this->letter_mode = get_var('letter_mode');
  }

  protected function _setup_load_community_record(){
    $community_name =   $this->_cp['community_name'];
    $this->_Obj_Community = new Community;
    $this->_Obj_Community->set_ID_by_name($community_name);
    if (!$this->_community_record = $this->_Obj_Community->load()){
      throw new Exception("Community \"".$community_name."\" not found.");
    }
  }

  protected function _setup_load_community_members(){
    $this->_members =   $this->_Obj_Community->get_members();
  }

  protected function _setup_load_edit_parameters(){
    if (!$this->_current_user_rights['isEditor']){
      return;
    }
    $temp =             $this->get_edit_params();
    $this->_report =    $temp['report'];
    $this->_popup =     get_popup_size($this->_report);
  }

  protected function _setup_load_email_contacts(){
    $this->_contacts =  $this->get_email_contacts();
  }

  protected function _setup_load_member(){
    $member_page_arr =      explode('/',$this->_member_extension);
    $this->_member_name =   array_shift($member_page_arr);
    $this->_member_page =   implode('/',$member_page_arr);
    if (!$this->get_member_profile($this->_cp['community_name'],$this->_member_name)){
      throw new Exception("Member \"".$this->_member_name."\" not found.");
    }
  }

  protected function _setup_load_mode(){
    $this->letter_mode = get_var('letter_mode');
  }

  protected function _setup_load_navigation_position(){
    $this->_nav_first = $this->_members[0];
    $this->_nav_last =  $this->_members[count($this->_members)-1];
    for($i=0; $i<count($this->_members); $i++){
      $m = $this->_members[$i];
      if ($m['ID'] == $this->_get_ID()){
        $this->_nav_prev = ($i-1<0 ? $this->_members[count($this->_members)-1] : $this->_members[$i-1]);
        $this->_nav_next = ($i+1>count($this->_members)-1 ? $this->_members[0] : $this->_members[$i+1]);
        return;
      }
    }
  }

  protected function _setup_load_page_ID(){
    $Obj_Page =         new Page;
    $this->_pageID =    $Obj_Page->get_ID_by_path('//template-welcome-letter/');
  }


  protected function _setup_load_user_rights(){
    $this->_current_user_rights['isEditor'] =   get_person_permission("SYSEDITOR") || get_person_permission("SYSAPPROVER") ||  get_person_permission("SYSADMIN") || get_person_permission("MASTERADMIN");
    $this->_current_user_rights['canEdit'] =    $this->_current_user_rights['isEditor'] || (get_person_permission("COMMUNITYADMIN") && $_SESSION['person']['memberID']==$this->_record['ID']);
  }

  public function get_version(){
    return COMMUNITY_MEMBER_SUMMARY_VERSION;
  }
}
?>