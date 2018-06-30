<?php
define('VERSION_HTML', '1.0.87');
/*
Version History:
  1.0.87 (2015-01-02)
    1) Class constants Community_Member::DASHBOARD_WIDTH and Community_Member::DASHBOARD_HEIGHT now uppercase
    2) Removed JS that sets window.status - this hasn't been supported in browsers for a long time now
    3) Fixed component icon for reports that use one
    4) Now PSR-2 Compliant

  (Older version history in class.html.txt)
*/
class HTML extends Record
{
    protected $_args =                      array();
    protected $_current_user_rights =       array();
    protected $_has_no_personal_toolbar =   0;

    public static function draw_form_box($title, $content = '', $help = '', $shadow = 0, $width = false, $footer = '')
    {
        global $report_name, $ID;
        return
         "<div class='std_control form_box constrain".($shadow ? " shadow" : "")."'"
        .($width!==false ? " style='width:".$width."'" : "")
        .">\n"
        ."  <div class='form_box_header'>\n"
        .$title
        .($help!="" ? " ".HTML::draw_icon('help', $help) : "")
        .($report_name!='' && $ID!='' ?
            " ".HTML::draw_icon('print_form', array('report_name'=>$report_name,'ID'=>$ID))
        :
            ""
        )
        ."</div>"
        ."  <div class='form_box_body table_border table_header'>\n"
        .$content
        ."  </div>\n"
        .($footer!=='' ?
           "  <div class='form_box_footer'>\n"
          .$footer
          ."  </div>\n"
         :
         ""
        )
        ."</div>";
    }

    public function draw_icon($type, $args = false)
    {
      // Handle simple disabled icons first:
        switch ($type) {
            case 'Disabled Edit Article':
                $pos = 2380;
                $width = 15;
                $text = "(Edit Article)";
                break;
            case 'Disabled Edit Event':
                $pos = 2425;
                $width = 20;
                $text = "(Edit Event)";
                break;
            case 'Disabled Edit Job Posting':
                $pos = 2410;
                $width = 15;
                $text = "(Edit Job Posting)";
                break;
            case 'Disabled Edit News Item':
                $pos = 2395;
                $width = 15;
                $text = "(Edit News Item)";
                break;
            case 'Disabled Edit Page':
                $pos = 2347;
                $width = 18;
                $text = "(Edit Page)";
                break;
            case 'Disabled Edit Podcast':
                $pos = 2445;
                $width = 17;
                $text = "(Edit Podcast)";
                break;
            case 'Popup Edit Article':
                $pos = 2462;
                $width = 18;
                $text = "Edit Article in a popup window";
                break;
            case 'Popup Edit Event':
                $pos = 2516;
                $width = 18;
                $text = "Edit Event in a popup window";
                break;
            case 'Popup Edit Job Posting':
                $pos = 2498;
                $width = 18;
                $text = "Edit Job Posting in a popup window";
                break;
            case 'Popup Edit News Item':
                $pos = 2480;
                $width = 18;
                $text = "Edit News Item in a popup window";
                break;
            case 'Popup Edit Podcast':
                $pos = 2534;
                $width = 18;
                $text = "Edit Podcast in a popup window";
                break;
            case 'Popup Edit Product':
                $pos = 4182;
                $width = 18;
                $text = "Edit Product in a popup window";
                break;
        }
        switch ($type) {
            case 'Disabled Edit Article':
            case 'Disabled Edit Event':
            case 'Disabled Edit Job Posting':
            case 'Disabled Edit News Item':
            case 'Disabled Edit Page':
            case 'Disabled Edit Podcast':
            case 'Popup Edit Article':
            case 'Popup Edit Event':
            case 'Popup Edit Job Posting':
            case 'Popup Edit News Item':
            case 'Popup Edit Podcast':
            case 'Popup Edit Product':
                return
                    "<img src='".BASE_PATH."img/spacer' class='icons fl'"
                    ." style='margin:1px;background-position:-".$pos."px 0px;'"
                    ." alt=\"".$text."\""
                    ." height=\"16\" width=\"".$width."\""
                    ."/>";
            break;
            case 'add_to_outlook':
                return
                     "<img alt='Add to Outlook' src='".BASE_PATH."img/spacer' class='icons std_control'"
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-3902px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-3932px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-3902px 0px';return true;\""
                    ." height=\"10\" width=\"30\""
                    ." />";
            break;
            case 'bookmark':
                global $page_vars;
                $ident =        "draw_bookmark_button";
                $parameter_spec = array(
                    'disable' =>      array('default'=>'0', 'hint'=>'0|1'),
                    'label'   =>      array('default'=>'Bookmark', 'hint'=>'Text for label (if shown)'),
                    'show_label'   => array('default'=>'0', 'hint'=>'0|1')
                );
                $cp_settings =  Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
                $cp_defaults =  $cp_settings['defaults'];
                $cp =           $cp_settings['parameters'];
                $URL =          ($_SERVER["SERVER_PORT"]==443 ?
                    "https://"
                :
                    "http://"
                ).$_SERVER["HTTP_HOST"].BASE_PATH.trim($page_vars['path'], '/');
                $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
                if (!$cp['disable']) {
                    $out.=
                         ($out ? "<br />" : "")
                        ."<span class='noprint' title='Click to add a bookmark'"
                        ." onmouseover=\"geid('icon_bookmark').style.backgroundPosition='-876px 0px';\""
                        ." onmouseout=\"geid('icon_bookmark').style.backgroundPosition='-861px 0px';\">"
                        ."<a href=\"#\""
                        ." onclick=\"add_bookmark('".$URL."','".addslashes($page_vars['title'])."');return false;\">"
                        ."<img alt='Add Bookmark' id='icon_bookmark' src='".BASE_PATH."img/spacer' class='toolbar_icon'"
                        ." style=\"background-position:-861px 0px;\""
                        ." height=\"15\" width=\"15\""
                        ."/></a>"
                        .($cp['show_label'] ?
                            "<a href=\"#\""
                            ." onclick=\"add_bookmark('".$URL."','".addslashes($page_vars['title'])."');return false;\""
                            ." style=\"float:left\">".$cp['label']."</a>"
                        :
                            ""
                        )
                        ."</span>";
                }
                return $out;
            break;
            case "buy_event":
                return
                     "<img alt='Buy Event' src='".BASE_PATH."img/spacer' class='icons std_control'"
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-4552px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-4583px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-4552px 0px';return true;\""
                    ." height=\"10\" width=\"31\""
                    ." />";
            break;
            case 'bugtracker':
                return
                     "<img src='".BASE_PATH."img/spacer' class='icons'"
                    ." style='display:block;background-position:-4314px 0px;'"
                    ." alt='File a Bug Report' title='File a Bug Report'"
                    ." height=\"16\" width=\"15\""
                    ."/>";
            break;
            case 'external':
                return
                     "<img src='".BASE_PATH."img/spacer' class='icons'"
                    ." style='margin-left:0.5em;display:inline;float:none;background-position:-3224px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-3224px -7px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-3224px 0px';return true;\""
                    ." alt='Opens in a new window'"
                    ." height=\"7\" width=\"9\""
                    ."/>";
            break;
            case 'help':
                if ($args=='') {
                    return '';
                }
                return
                     "<a href=\"#\" onclick=\"popup_help('".$args."');return false;\">"
                    ."<img src='".BASE_PATH."img/spacer' class='icons'"
                    ." style='display:inline;float:none;background-position:-2937px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-2948px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-2937px 0px';return true;\""
                    ." alt='?'"
                    ." height=\"12\" width=\"11\""
                    ."/></a>";
            break;
            case 'link':
                return
                     "<img alt='Link' src='".BASE_PATH."img/spacer' class='icons'"
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-1766px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-1799px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-1766px 0px';return true;\""
                    ." height=\"10\" width=\"".($args==true ? "33" : "22")."\""
                    ."/>";
            break;
            case 'map':
                return
                     "<img alt='View Map' src='".BASE_PATH."img/spacer' class='icons'"
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-1832px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-1865px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-1832px 0px';return true;\""
                    ." height=\"10\" width=\"".($args==true ? "33" : "22")."\""
                    ." />";
            break;
            case 'media_player':
                return
                    "<img alt='Media Player' src='".BASE_PATH."img/spacer' class='icons' "
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-2295px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-2307px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-2295px 0px';return true;\""
                    ." height=\"10\" width=\"12\""
                    ." />";
            break;
            case 'media_download':
                return
                    "<img alt='Download Media' src='".BASE_PATH."img/spacer' class='icons' "
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-3631px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-3647px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-3631px 0px';return true;\""
                    ." height=\"15\" width=\"16\""
                    ."/>";
            break;
            case 'media_download_mini':
                return
                    "<img alt='Download Media' src='".BASE_PATH."img/spacer' class='icons' "
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-3663px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-3676px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-3663px 0px';return true;\""
                    ." height=\"10\" width=\"13\""
                    ."/>";
            break;
            case 'more':
                return
                    "<img alt='Read More' src='".BASE_PATH."img/spacer' class='icons' "
                    ." style='margin-top:3px;margin-bottom:3px;background-position:-1638px 0px;'"
                    ." onmouseover=\"this.style.backgroundPosition='-1702px 0px';return true;\""
                    ." onmouseout=\"this.style.backgroundPosition='-1638px 0px';return true;\""
                    ." height=\"10\" width=\"".($args==true ? "64" : "53")."\""
                    ."/>";
            break;
            case 'print':
                return
                     "<span class='noprint'><a href=\"#\""
                    ." onclick=\"if(window.print){window.print();}else{"
                    ."alert('Please press the Print icon above.');}return false;\""
                    ." onmouseover=\"geid('icon_print').style.backgroundPosition='-1476px 0px';\""
                    ." onmouseout=\"geid('icon_print').style.backgroundPosition='-1456px 0px';\""
                    .">"
                    ."<img alt='Print this page' id='icon_print' src='".BASE_PATH."img/spacer' title='Print this page'"
                    ." class='toolbar_icon' style='background-position:-1456px 0px;' height=\"16\" width=\"20\"/>"
                    ."</a></span>";
            break;
            case 'print_calendar':
                return
                     "<span class='noprint'><a href=\"#\""
                    ." onclick=\"alert('The calendar prints out best in landscape mode -\\n\\n"
                    ."The easiest way to achieve this is choose\\n"
                    ."\'Print preview\' from this window\'s File menu\\n"
                    ."then change the orientation to landscape\\n"
                    ."before you actually print.\\n\\n"
                    ."(Press \'Alt\' if you can\'t see the File menu)');"
                    ."if(window.print){window.print();}else{alert('Please press the Print icon above.');}"
                    ."return false;\" "
                    ." onmouseover=\"geid('icon_print_calendar').style.backgroundPosition='-1476px 0px';\" "
                    ." onmouseout=\"geid('icon_print_calendar').style.backgroundPosition='-1456px 0px';\">"
                    ."<img alt='Print this calendar' id='icon_print_calendar' src='".BASE_PATH."img/spacer'"
                    ." title='Print this calendar' class='toolbar_icon' style='background-position:-1456px 0px;'"
                    ." height=\"16\" width=\"20\""
                    ."/></a></span>";
            break;
            case 'print_form':
                return
                     "<a href=\"#\" onclick=\"print_form('".$args['report_name']."','".$args['ID']."');return false;\""
                    ." onmouseover=\"geid('icon_print_form').style.backgroundPosition='-2824px 0px';\""
                    ." onmouseout=\"geid('icon_print_form').style.backgroundPosition='-2811px 0px';\">"
                    ."<img alt='Click to print' id='icon_print_form' src='".BASE_PATH."img/spacer'"
                    ." title='Click to print' class='icons'"
                    ." style='display:inline;float:none;background-position:-2811px 0px;'"
                    ." height=\"12\" width=\"13\""
                    ."/></a>";
            break;
            case 'print_friendly':
                global $page_vars;
                $ident =        "draw_print_friendly_button";
                $parameter_spec = array(
                    'disable'       => array('default'=>'0', 'hint'=>'0|1'),
                    'label'         => array('default'=>'Print Friendly', 'hint'=>'Text for label (if shown)'),
                    'show_label'    => array('default'=>'0', 'hint'=>'0|1')
                );
                $cp_settings =  Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
                $cp_defaults =  $cp_settings['defaults'];
                $cp =           $cp_settings['parameters'];
                $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
                if (!$cp['disable']) {
                    $out.=
                         ($out ? "<br />" : "")
                        ."<span class='noprint'"
                        ." title='Click to see a Print-Friendly \nversion of this ".$page_vars['object_name']."'"
                        ." onmouseover=\"geid('icon_print').style.backgroundPosition='-1476px 0px';\""
                        ." onmouseout=\"geid('icon_print').style.backgroundPosition='-1456px 0px';\">"
                        ."<a href=\"#\" onclick=\"print_friendly();return false;\">"
                        ."<img alt='Click to print' id='icon_print' src='".BASE_PATH."img/spacer' class='toolbar_icon'"
                        ." style='background-position:-1456px 0px;'"
                        ." height=\"16\" width=\"20\""
                        ."/></a>"
                        .($cp['show_label'] ?
                            "<a href=\"#\" style=\"float:left\" onclick=\"print_friendly();return false;\">"
                            .$cp['label']
                            ."</a>"
                        :
                            ""
                        )
                        ."</span>";
                }
                return $out;
            break;
            case "register_event":
                return
             "<img alt='Register for Event' src='".BASE_PATH."img/spacer' class='icons std_control'"
            ." style='margin-top:3px;margin-bottom:3px;background-position:-3962px 0px;'"
            ." onmouseover=\"this.style.backgroundPosition='-4017px 0px';return true;\""
            ." onmouseout=\"this.style.backgroundPosition='-3962px 0px';return true;\""
            ." height=\"10\" width=\"55\""
            ." />";
            break;
            case 'sitemap':
                $ident =        "draw_sitemap_button";
                $parameter_spec = array(
                'disable' =>      array('default'=>'0', 'hint'=>'0|1'),
                'label'   =>      array('default'=>'Sitemap', 'hint'=>'Text for label (if shown)'),
                'show_label'   => array('default'=>'0', 'hint'=>'0|1'),
                'URL' =>          array('default'=>BASE_PATH.'sitemap', 'hint'=>'URL to go to')
                );
                $cp_settings =  Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
                $cp_defaults =  $cp_settings['defaults'];
                $cp =           $cp_settings['parameters'];
                $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
                if (!$cp['disable']) {
                    $out.=
                        ($out ?
                           "<br />"
                        :
                           ""
                        )
                        ."<span class='noprint' title='Click to view Sitemap'"
                        ." onmouseover=\"geid('icon_sitemap').style.backgroundPosition='-1512px 0px';\""
                        ." onmouseout=\"geid('icon_sitemap').style.backgroundPosition='-1496px 0px';\">"
                        ."<a href=\"".$cp['URL']."\">"
                        ."<img alt='View sitemap' id='icon_sitemap' src='".BASE_PATH."img/spacer' class='toolbar_icon'"
                        ." style='background-position:-1496px 0px;'"
                        ." height=\"16\" width=\"16\""
                        ."/>"
                        ."</a>"
                        .($cp['show_label'] ?
                            "<a href=\"".$cp['URL']."\" style=\"float:left\">".$cp['label']."</a>"
                        :
                            ""
                        )
                        ."</span>";
                }
                return $out;
            break;
            case 'text_sizer':
                $ident =        "draw_text_sizer_button";
                $parameter_spec = array(
                'disable' =>      array('default'=>'0', 'hint'=>'0|1'),
                'label'   =>      array('default'=>'Text Size', 'hint'=>'Text for label (if shown)'),
                'show_label'   => array('default'=>'0', 'hint'=>'0|1')
                );
                $cp_settings =  Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
                $cp_defaults =  $cp_settings['defaults'];
                $cp =           $cp_settings['parameters'];
                $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
                $size = (isset($_COOKIE['textsize']) ? $_COOKIE['textsize'] : "small");
                if (!$cp['disable']) {
                    $out.=
                     ($out ? "<br />" : "")
                    ."<span id='text_sizer_enlarge' title='Click to Enlarge Text'"
                    ." onmouseover=\"geid('icon_textsizer_enlarge').style.backgroundPosition='-1576px 0px';\""
                    ." onmouseout=\"geid('icon_textsizer_enlarge').style.backgroundPosition='-1528px 0px';\""
                    .($size=='big' ? " style='display:none'" : "")
                    .">"
                    ."<a href=\"#\" onclick=\"toggleTextSize();return false;\""
                    .">"
                    ."<img alt='Enlarge Text' id='icon_textsizer_enlarge' src='".BASE_PATH."img/spacer'"
                    ." class='toolbar_icon' style='background-position:-1528px 0px;' height=\"16\" width=\"16\" /></a>"
                    .($cp['show_label'] ?
                        "<a href=\"#\" style=\"float:left\" onclick=\"toggleTextSize();return false;\">"
                        .$cp['label']
                        ."</a>"
                    :
                        ""
                    )
                    ."</span>"
                    ."<span id='text_sizer_reduce' title='Click to Reduce Text'"
                    ." onmouseover=\"geid('icon_textsizer_reduce').style.backgroundPosition='-1560px 0px';\""
                    ." onmouseout=\"geid('icon_textsizer_reduce').style.backgroundPosition='-1544px 0px';\""
                    .($size=='big' ? "" : " style='display:none'")
                    .">"
                    ."<a href=\"#\" onclick=\"toggleTextSize();return false;\""
                    .">"
                    ."<img alt='Reduce Text' id='icon_textsizer_reduce' src='".BASE_PATH."img/spacer'"
                    ." class='toolbar_icon' style='background-position:-1544px 0px;' height=\"16\" width=\"16\"/></a>"
                    .($cp['show_label'] ?
                        "<a href=\"#\" style=\"float:left\" onclick=\"toggleTextSize();return false;\">"
                        .$cp['label']
                        ."</a>"
                    :
                        ""
                    )
                    ."</span>";
                }
                return $out;
            break;
        }
    }

    public function draw_info($title, $content)
    {
        return
         "<div class='info'>"
        ."<h1>".$title."</h1>"
        ."<img alt='Info' src='".BASE_PATH."img/spacer'"
        ." class='icons' style='height:11px;width:11px;background-position:-2600px 0px;' />\n"
        .$content
        ."</div>";
    }

    public function draw_section_tabs($arr, $divider_prefix, $selected_section, $js = "")
    {
        $divider_prefix = str_replace(array(' ',':','.','-'), array('_','_','_','_'), $divider_prefix);
        $ID_arr = array();
        foreach ($arr as $value) {
            $safe_ID = str_replace(array('/'), '_', $value['ID']);
            $ID_arr[] = $safe_ID;
        }
        if (
            count($ID_arr) &&
            (!isset($selected_section) || $selected_section=="" || !in_array($selected_section, $ID_arr))
        ) {
            $selected_section = $ID_arr[0];
        }
        Page::push_content('javascript', "window.spans_".$divider_prefix." = ['".implode("','", $ID_arr)."'];\n");
        $out = "<div class='section_tabs'>\n";
        foreach ($arr as $value) {
            if ($value['label']!="") {
                $safe_ID = str_replace(array('/'), '_', $value['ID']);
                $out.=
                 "  <div class=\"".($selected_section==$safe_ID? "tab_selected" : "tab")."\""
                ." id='section_".$safe_ID."_heading'"
                .(isset($value['width']) && $value['width'] ? " style=\"min-width:".$value['width']."px\"" : "")
                ." onclick=\"".($js ? $js.";" : "")."return show_section(spans_".$divider_prefix.",'".$safe_ID."')\""
                .">"
                ."<a "
                ."title=\"Click to view ".str_replace('<br />', ' ', $value['label'])."\""
                ." onclick='return false;'>".$value['label']."</a></div>\n";
            }
        }
        $out.=
         "<br class='clr_b' /></div>\n";
        return $out;
    }

    public function draw_section_tab_buttons($arr, $divider_prefix, $selected_section, $js = "")
    {
        $divider_prefix = str_replace(array(' ',':','.','-'), array('_','_','_','_'), $divider_prefix);
        $ID_arr = array();
        foreach ($arr as $value) {
            $safe_ID = str_replace(array('/'), '_', $value['ID']);
            $ID_arr[] = $safe_ID;
        }
        if (
            count($ID_arr) &&
            (!isset($selected_section) || $selected_section=="" || !in_array($selected_section, $ID_arr))
        ) {
            $selected_section = $ID_arr[0];
        }
        Page::push_content('javascript', "window.spans_".$divider_prefix." = ['".implode("','", $ID_arr)."'];\n");
        $out = "<div class='section_tabs'>\n";
        foreach ($arr as $value) {
            if ($value['label']!="") {
                $safe_ID = str_replace(array('/'), '_', $value['ID']);
                $out.=
                 "  <div class=\"".($selected_section==$safe_ID? "tab_selected" : "tab")."\""
                ." id='section_".$safe_ID."_heading'"
                .(isset($value['width']) && $value['width'] ? " style=\"min-width:".$value['width']."px\"" : "")
                ." onclick=\""
                .($js ? $js.";" : "")
                ."return show_section_tab(spans_".$divider_prefix.",'".$safe_ID."')\""
                .">"
                ."<a "
                ."title=\"Click to view ".str_replace('<br />', ' ', $value['label'])."\""
                ." onclick='return false;'>".$value['label']."</a></div>\n";
            }
        }
        $out.=
         "<br class='clr_b' /></div>\n";
        return $out;
    }

    public function draw_section_tab_div($ID, $selected_section)
    {
        $safe_ID = str_replace(array('/'), '_', $ID);
        return
            "<div id='section_".$safe_ID."' class='section_container' style='"
            .($selected_section==$safe_ID ? '' : 'left:-10000px;')
            .";'>";
    }

    public function draw_status($ID, $msg, $ajax_mode = false, $severity = false, $noclose = 0)
    {
        if (!$msg) {
            return "";
        }
        $severity=false;
        if ($severity === false) {
            if (substr(strip_tags($msg), 0, 5)=='Error') {
                $severity = 2;
            } elseif (substr(strip_tags($msg), 0, 6)=='Notice') {
                $severity = 1;
            } elseif (substr(strip_tags($msg), 0, 7)=='Warning') {
                $severity = 1;
            } else {
                $severity = 0;
            }
        }
        $html = "<div class='form_status' id='form_status_".$ID."' style='display:none;'></div>";
        $js =
             "  status_message_show("
            ."\"form_status_".$ID."\",\"".str_replace('/', '\/', $msg)."\",".$severity.",".$noclose
            .");\n";
        if (!$ajax_mode) {
            Page::push_content('javascript_onload', $js);
            return $html;
        }
        return array(
            'html' =>   $html,
            'js' =>     $js
        );
    }

    public function draw_toolbar($type, $args = array())
    {
        global $system_vars;
        $this->_args = $args;
        if (!isset($_SESSION['person'])) {
            return (isset($this->_args['ajax_mode']) && $this->_args['ajax_mode'] ? array('html'=>'','js'=>'') : '');
        }
        $this->_draw_toolbar_load_user_rights();
        $out =    array();
        switch (strToLower($type)) {
            case "admin":
                return $this->_draw_toolbar_type_admin();
            break;
            case "component":
                return $this->_draw_toolbar_type_component();
            break;
            case "custom_form":
                return $this->_draw_toolbar_type_custom_form();
            break;
            case "page_create":
                return $this->_draw_toolbar_type_page_create();
            break;
            case "page_edit":
                return $this->_draw_toolbar_type_page_edit();
            break;
            case "personal":
                return $this->_draw_toolbar_type_personal();
            break;
            case "posting_edit":
                return $this->_draw_toolbar_type_posting_edit();
            break;
            case "report":
                return $this->_draw_toolbar_type_report();
            break;
            case "with_selected":
                return $this->_draw_toolbar_type_with_selected();
            break;
        }
    }

    protected function _draw_toolbar_load_user_rights()
    {
        global $page_vars;
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isUSERADMIN =      get_person_permission("USERADMIN");
        $isSYSADMIN =       get_person_permission("SYSADMIN", $page_vars['group_assign_csv']);
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER", $page_vars['group_assign_csv']);
        $isSYSEDITOR =      get_person_permission("SYSEDITOR", $page_vars['group_assign_csv']);
        $isSYSMEMBER =      get_person_permission("SYSMEMBER");
        $isCOMMUNITYADMIN = get_person_permission("COMMUNITYADMIN");
        $isADMIN =          ($isMASTERADMIN || $isSYSADMIN);
        $isAPPROVER =       ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
        $isEDITOR =         ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER || $isSYSEDITOR);
        $isAPPROVED =
            ($isMASTERADMIN || $isSYSADMIN || $isUSERADMIN || $isSYSAPPROVER || $isSYSEDITOR || $isSYSMEMBER);
        $isLOCKED =         $_SESSION['person']['profile_locked'];
        $this->_current_user_rights['isAPPROVED'] =         $isAPPROVED;
        $this->_current_user_rights['isEDITOR'] =           $isEDITOR;
        $this->_current_user_rights['isAPPROVER'] =         $isAPPROVER;
        $this->_current_user_rights['isADMIN'] =            $isADMIN;
        $this->_current_user_rights['isCOMMUNITYADMIN'] =   $isCOMMUNITYADMIN;
        $this->_current_user_rights['isSYSAPPROVER'] =      $isSYSAPPROVER;
        $this->_current_user_rights['isSYSADMIN'] =         $isSYSADMIN;
        $this->_current_user_rights['isMASTERADMIN'] =      $isMASTERADMIN;
        $this->_current_user_rights['isLOCKED'] =           $isLOCKED;
        $this->_current_user_rights['isUSERADMIN'] =        $isUSERADMIN;
        $this->_current_user_rights['canEdit'] =
            ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER || $isSYSEDITOR ? 1 : 0);
        $this->_isAdmin =
            ($this->_current_user_rights['canEdit'] || $isUSERADMIN ? 1 :0);
        $this->_has_no_personal_toolbar = System::has_feature('no-personal-toolbar');
    }

    protected function _draw_toolbar_type_admin()
    {
        global $system_vars;
        $Obj_System = new System;
        $Obj_Report = new Report();
        $records = $Obj_Report->get_report_icons();
        if ($records===false) {
            return "Problems getting report icons";
        }
        $html = "";
        $this_tab =     false;
        foreach ($records as $record) {
            if ($record['tab'] !== $this_tab) {
                if ($this_tab!==false) {
                    $html.=  "    </ul>\n  </li>\n";
                }
                $this_tab = $record['tab'];
                $label_arr = explode('.', $record['tab']);
                array_shift($label_arr);
                $label = implode('.', $label_arr);
                $html.=
                "  <li><label>".$label."</label>\n"
                ."    <ul class='css3'>\n";
            }
            $html.=
            "      <li>\n"
            ."        <a"
            .($record['help'] ? " class='am_help'" : "")
            ." href=\"".BASE_PATH."report/".$record['name']."\""
            .($this->_current_user_rights['isMASTERADMIN'] ?
                " onmouseover=\"_CM.type='report';_CM.ID='".$record['ID']."';\""
            :
                ""
            )
            .($this->_current_user_rights['isMASTERADMIN'] ?
                " onmouseout=\"_CM.type=''\""
            :
                ""
            )
            ." onclick=\"show_popup_please_wait();return true;\">"
            ."          <span class='ami'>".$record['icon']."</span>\n"
            ."          <span class='aml'>".$record['label']."</span>\n"
            ."        </a>\n"
            .($record['help'] ?
                 "        <a class='amh' href=\"".BASE_PATH.$record['help']."\""
                ." onclick=\"popup_help('".$record['help']."');return false\">"
                ."[ICON]14 14 1085 Get Help for this topic...[/ICON]"
                ."</a>"
            :
                ""
            )
            ."      </li>\n";
        }
        if ($this->_isAdmin && ($this->_has_no_personal_toolbar || System::has_feature('no-personal-toolbar'))) {
            $html.=
             "    </ul>\n"
            ."  </li>\n"
            ."  <li><label>Personal</label>\n"
            ."    <ul class='css3'>\n"
            ."      <li><a href=\"".BASE_PATH."\">\n"
            ."        <span class='ami_w'>[ICON]15 15 950 See Home Page[/ICON]</span>\n"
            ."        <span class='aml_n'>Home Page</span>\n"
            ."      </a></li>\n"
            .($this->_current_user_rights['isLOCKED']==0 ?
            "     <li><a href='".BASE_PATH."password'>\n"
             ."       <span class='ami_w'>[ICON]32 32 965 Change your Password[/ICON]</span>\n"
             ."       <span class='aml_n'>Change Password</span>\n"
             ."     </a></li>"
             :
            ""
            )
            .($this->_current_user_rights['isLOCKED']==0 ?
            "     <li><a href='".BASE_PATH."manage_profile'>\n"
            ."       <span class='ami_w'>[ICON]16 16 997 Manage your Profile[/ICON]</span>\n"
            ."       <span class='aml_n'>Manage Your Profile</span>\n"
            ."     </a></li>"
            :
            ""
            )
            .($system_vars['bugs_url']!='' ?
             "      <li><a href=\"".BASE_PATH."_bug\" onclick=\"return bugtracker_form(location)\">\n"
            ."        <span class='ami_w'>".HTML::draw_icon('bugtracker')."</span>\n"
            ."        <span class='aml_n'>Report a bug</span>\n"
            ."      </a></li>\n"
            :
            ""
            )
            ."      <li><a href=\"".BASE_PATH."?command=signout\">\n"
            ."        <span class='ami_w'>[ICON]29 29 1056 Sign Out[/ICON]</span>\n"
            ."        <span class='aml_n'>Sign Out</span>\n"
            ."      </a></li>\n";
        }
        return
         ($html ?
         "<div id='am' class='zoom_text'>\n"
        ."<div class='admin_toolbartable'>\n"
        ."<img class=\"toolbar_left fl\" alt=\"|\" src=\"".BASE_PATH."img/sysimg/icon_toolbar_end_left.gif\" />"
        ."<ul>\n"
        .$html
        ."    </ul>\n"
        ."  </li>\n"
        ."</ul>\n"
        ."<div class='clear'>&nbsp;</div></div></div>\n"
         : ""
         );

    }

    protected function _draw_toolbar_type_component()
    {
        global $page_vars;
        if (
            !$this->_current_user_rights['isMASTERADMIN'] ||
            !isset($this->_args['componentID']) ||
            $this->_args['componentID']==1
        ) {
            return '';
        }
        $popup_c = get_popup_size('component');
        $content =
             "<a class='ti' href=\"".BASE_PATH."details/component/".$this->_args['componentID']."\""
            ." onclick=\"details('component',".$this->_args['componentID'].",'".$popup_c['h']."','".$popup_c['w']."'"
            .");return false;\">"
            ."[ICON]17 17 1373 Edit embedded component[/ICON]"
            ."</a>";
        return HTML::draw_toolbar_frame($content, 'left');
    }

    protected function _draw_toolbar_type_custom_form()
    {
        if (!$this->_current_user_rights['isAPPROVER']) {
            return "";
        }
        if (!$this->_args['ID']) {
            return $this->_draw_toolbar_type_custom_form_new($this->_args['name']);
        }
        $popup_c = get_popup_size('custom_forms');
        return
             HTML::draw_toolbar_separator()
            .HTML::draw_toolbar_text("<b>Custom Form</b> \"".$this->_args['name']."\"")
            ."<a class='ti' href=\"".BASE_PATH."details/custom_forms/".$this->_args['ID']."\""
            ." onclick=\"details('custom_forms','".$this->_args['ID']."','".$popup_c['h']."','".$popup_c['w']."');"
            ."return false;\">"
            ."[ICON]17 17 2627 Edit Custom Form[/ICON]"
            ."</a>\n"
            ."<a class='ti'"
            ." href=\"http://validator.w3.org/check?uri="
            .urlencode(
                "http://".$_SERVER["SERVER_NAME"]."?command=download_custom_form_xml&targetID=".$this->_args['ID']
            )
            ."\""
            ." onclick=\"validate_at_w3c('".$this->_args['ID']."',600,800);return false;\">"
            ."[ICON]17 17 2644 Validate Custom Form[/ICON]"
            ."</a>\n"
            .HTML::draw_toolbar_separator()
            ."<a class='ti' rel='external'"
            ." href=\"".BASE_PATH."export/sql/custom_forms/".$this->_args['ID']."/1\""
            ." onclick=\"export_sql('custom_forms',".$this->_args['ID'].");return false;\""
            .">"
            ."[ICON]25 25 2661 Export Custom Form[/ICON]"
            ."</a>\n"
            ."<a class='ti' rel='external'"
            ." href=\"".BASE_PATH."?command=download_custom_form_xml&amp;targetID=".$this->_args['ID']."\">"
            ."[ICON]32 32 2686 View XML for Custom Form[/ICON]"
            ."</a>\n"
            .HTML::draw_toolbar_end()
            ."<div class='clr_b'></div>\n";
        return HTML::draw_toolbar_frame($content, 'left');
    }

    protected function _draw_toolbar_type_custom_form_new($name = '')
    {
        if (
            !$this->_current_user_rights['isMASTERADMIN'] &&
            !$this->_current_user_rights['isSYSADMIN'] &&
            !$this->_current_user_rights['isSYSAPPROVER']
        ) {
            return "";
        }
        $popup_c = get_popup_size('custom_forms');
        return
             HTML::draw_toolbar_separator()
            .HTML::draw_toolbar_text("<b>Create new Custom Form</b> \"".$name."\"")
            ."<a class='ti' href=\"#\""
            ." onclick=\"popWin('"
            .BASE_PATH."details/custom_forms?name=".$name."','pop_page',"
            ."'scrollbars=0,resizable=1',".$popup_c['w'].",".$popup_c['h'].",'centre');return false;\">"
            ."[ICON]17 17 5046 Create a brand new custom form\nnamed &quot;".$name."&quot;[/ICON]"
            ."</a>\n"
            .HTML::draw_toolbar_end()
            ."<div class='clr_b'></div>\n"
            ."<h3 class='margin_none padding_none'>Custom Form ".$name." not found</h3>"
            ."<p>This Custom Form does not yet exist for this Site.<br />\n"
            ."You now have the option to create it.</p>";
    }

    protected function _draw_toolbar_type_page_create()
    {
        global $page;
        $popup =        get_popup_size('pages');
        $Obj_Page =     new Page;
        $path =         $Obj_Page->get_resolved_path($page);
        $path_bits =    explode('|', $path);
        $page_arr =     explode('/', $path_bits[1]);
        $has_parent =   $path_bits[0]!='0';
        $default =      0;
        $extender =     0;
        switch($page_arr[0]){
            case 'checkout':
                $default =  "&#91;ECL&#93;component_checkout&#91;/ECL&#93;";
                break;
            case 'email-opt-in':
                $default =  "&#91;ECL&#93;component_email_opt_in&#91;/ECL&#93;";
                $extender = 1;
                break;
            case 'email-opt-out':
                $default =  "&#91;ECL&#93;component_email_opt_out&#91;/ECL&#93;";
                $extender = 1;
                break;
            case 'email-unsubscribe':
                $default =  "&#91;ECL&#93;component_email_unsubscribe&#91;/ECL&#93;";
                $extender = 1;
                break;
            case 'forgotten_password':
                $default = "&#91;ECL&#93;component_forgotten_password&#91;/ECL&#93;";
                break;
            case 'manage_profile':
                $default = "&#91;ECL&#93;edit_your_profile&#91;/ECL&#93;";
                break;
            case 'password':
                $default = "&#91;ECL&#93;draw_change_password&#91;/ECL&#93;";
                break;
            case 'paypal_cancel':
                $default = "&#91;ECL&#93;paypal_cancel_repopulate_cart&#91;/ECL&#93;";
                break;
            case 'paypal_return':
                $default = "&#91;ECL&#93;paypal_return_check_payment&#91;/ECL&#93;";
                break;
            case 'sitemap':
                $default = "&#91;ECL&#93;draw_html_sitemap(1)&#91;/ECL&#93;";
                break;
            case 'your_order_history':
                $default = "&#91;ECL&#93;your_order_history&#91;/ECL&#93;";
                break;
            case 'your_registered_events':
                $default = "&#91;ECL&#93;component_your_registered_events&#91;/ECL&#93;";
                break;
        }
        return
             HTML::draw_toolbar_separator()
            .HTML::draw_toolbar_text("<b>Create new Page</b> \"".$page_arr[0]."\"")
            ."<a class='ti' href=\"".BASE_PATH.$page."\""
            ." onclick=\"popWin('".BASE_PATH."details/pages?page=".$page_arr[0]
            .($has_parent ? "&amp;parentID=".$path_bits[0] : "")
            .($default ?  "&amp;content=".$default : "")
            .($extender ? "&amp;path_extender=1" : "")
            ."','pop_page',"
            ."'scrollbars=0,resizable=1',".$popup['w'].",".$popup['h'].",'centre');return false;\">"
            ."[ICON]18 18 2090 Create a brand new page named &quot;".$page_arr[0]."&quot;[/ICON]"
            ."</a>\n"
            .HTML::draw_toolbar_end()
            ."<div class='clear'>&nbsp;</div>\n"
            ."<h3 class='margin_none padding_none'>Page ".$page." not found</h3>"
            ."<p>This Page does not yet exist for this Site.<br />\n"
            ."You now have the option to create it.</p>"
            .($this->_args['wasSubstituted'] ?
                "<p>(A default version has been substituted.)</p>"
             :
                ""
            );
    }

    protected function _draw_toolbar_type_page_edit()
    {
        global $page_vars, $system_vars, $component_help;
        $allowEdit =        $this->_args['allowPopupEdit'];
        $popup_arr = array();
        if ($allowEdit) {
            $popup_arr['pages'] =     get_popup_size($this->_args['edit_params']['report']);
            $popup_arr['layouts'] =   get_popup_size('layouts');
            $popup_arr['theme'] =     get_popup_size('theme');
        }
        if ($this->_current_user_rights['isADMIN']) {
            $popup_arr['system'] = get_popup_size('system');
        }
        if ($this->_current_user_rights['isMASTERADMIN']) {
            $popup_arr['component'] = get_popup_size('component');
        }
        $componentID_post = $page_vars['componentID_post'];
        $out = HTML::draw_toolbar_separator();
        if ($this->_args['withCopy']) {
            $out.=
                 HTML::draw_toolbar_text("<b>Original:</b>")."\n"
                ."<a class='ti' href=\"#\""
                ." onclick=\"geid('submode').value='edit';geid('form').submit();return false;\">"
                ."[ICON]18 18 279 Edit original page[/ICON]"
                ."</a>\n"
                .($this->_args['allowPopupEdit']==1 ?
                    "<a class='ti' href=\".\""
                   ." onclick=\"details('pages','".$page_vars['ID']."',"
                   ."'".$popup_arr['pages']['h']."','".$popup_arr['pages']['w']."');return false;\">"
                   ."[ICON]18 18 297 Edit original page in a popup window[/ICON]"
                   ."</a>\n"
                 :
                    ""
                )
                .($this->_args['allowSaveAs']==1 ?
                    "<a class='ti' href=\".\""
                   ." onclick=\"geid('submode').value='save_as';geid('form').submit();return false;\">"
                   ."[ICON]16 16 315 Make another copy of the original page[/ICON]"
                   ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isMASTERADMIN']==1 ?
                     HTML::draw_toolbar_separator()
                    ."<a class='ti' href=\"".BASE_PATH."export/sql/pages/".$page_vars['ID']."/1\""
                    ." onclick=\"export_sql('pages',".$page_vars['ID'].");return false;\">"
                    ."[ICON]22 22 839 Export original page as SQL[/ICON]"
                    ."</a>\n"
                 :
                    ""
                )
                .HTML::draw_toolbar_separator()
                .HTML::draw_toolbar_text("<b>Copy:</b>")."\n"
                ."<a class='ti' href=\"#\""
                ." onclick=\"geid('submode').value='edit';geid('goto').value='".$this->_args['newPage']."';"
                ."geid('form').submit();return false;\">"
                ."[ICON]18 18 279 Edit copied page[/ICON]"
                ."</a>\n"
                .($this->_args['allowPopupEdit']==1 ?
                     "<a class='ti' href=\".\" onclick=\"details('pages','".$this->_args['newPageID']."',"
                    ."'".$popup_arr['pages']['h']."','".$popup_arr['pages']['w']."');return false;\">"
                    ."[ICON]18 18 297 Edit copied page in a popup window[/ICON]"
                    ."</a>\n"
                :
                    ""
                );
        } else {
            $layoutID =   $page_vars['layoutID'];
            $Obj_Layout = new Layout($layoutID);
            $usage =      $Obj_Layout->usage();
            $Obj_System = new System(SYS_ID);
            if ($usage['internal']==0 && $usage['system']==0 && $usage['page']<2) {
                $msg = false;
            } else {
                $msg =
                     "WARNING:\\n\\nThis Layout is "
                     .($usage['internal']>0 ?
                        " an internal Layout"
                     :
                        ""
                     )
                     .($usage['system']>0 ?
                         ($usage['internal']>0 ? ", " : "")
                        ."defined by ".$usage['system']." Site".($usage['system']==1 ? "" : "s")
                        ." as ".($usage['system']==1 ? "its" : "their")
                        ." Default Layout"
                     :
                        ""
                     )
                     .($usage['page']>0 ?
                         ($usage['system']>0 || $usage['internal']>0 ? "\\nand " : "")
                        ."explicitly specified for use by ".$usage['page']." Page".($usage['page']==1 ? "" : "s")
                     :
                        ""
                     )
                     .".\\n\\n"
                     ."Do you wish to proceed?";
            }

            $out.=
                 "<a class='ti' href=\".\""
                ." onclick=\"geid('submode').value='edit';geid('form').submit();return false;\">"
                .$this->_args['edit_params']['icon_edit']
                ."</a>\n"
                .($this->_current_user_rights['isEDITOR'] && $this->_args['allowPopupEdit']==1  ?
                     "<a class='ti' href=\".\""
                    ." onclick=\"details('".$this->_args['edit_params']['report']."','".$this->_args['ID']."',"
                    ."'".$popup_arr['pages']['h']."','".$popup_arr['pages']['w']."');return false;\">"
                    .$this->_args['edit_params']['icon_edit_popup']
                    ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isAPPROVER'] && $this->_args['allowSaveAs']==1 ?
                     HTML::draw_toolbar_separator()
                    ."<a class='ti' href=\".\""
                    ." onclick=\"geid('submode').value='save_as';geid('form').submit();return false;\">"
                    ."[ICON]16 16 315 Make a copy of this page[/ICON]"
                    ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isEDITOR'] ?
                     "<a class='ti' href=\".\""
                    ." onclick=\"if(confirm('Delete this page?\\nThis change cannot be undone.')){"
                    ."geid('submode').value='delete_page';geid('form').submit();};return false;\">"
                    ."[ICON]16 16 3542 Delete this page[/ICON]"
                    ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isEDITOR'] ?
                     "<a class='ti' href=\".\""
                    ." onclick=\"popup_page_create('".($layoutID!=$system_vars['defaultLayoutID'] ? $layoutID : 1)."',"
                    ."'".$popup_arr['pages']['w']."','".$popup_arr['pages']['h']."');return false;\">"
                    ."[ICON]18 18 2090 Create a brand new page[/ICON]"
                    ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isADMIN'] ?
                    HTML::draw_toolbar_separator()
                 :
                    ""
                )
                .($this->_current_user_rights['isMASTERADMIN'] && $componentID_post!=1 ?
                     "<a class='ti' href=\".\""
                    ." onclick=\"details('component',".$componentID_post.","
                    ."'".$popup_arr['component']['h']."','".$popup_arr['component']['w']."');return false;\">"
                    ."[ICON]17 17 1373 Edit embedded component[/ICON]"
                    ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isADMIN'] && $allowEdit==1  ?
                    "<a class='ti' href=\".\""
                   ." onclick=\""
                   .($msg ?
                        "if (confirm('$msg')){"
                    :
                        ""
                   )
                   ."details('layouts','".$layoutID."',"
                   ."'".$popup_arr['layouts']['h']."','".$popup_arr['layouts']['w']."');"
                   .($msg!='' ?
                        "}"
                    :
                        ""
                   )
                   .";return false;\">"
                   ."[ICON]24 24 1273 Edit current Layout in a popup window[/ICON]"
                   ."</a>\n"
                 :
                    ""
                )
                .((
                    $this->_current_user_rights['isMASTERADMIN'] ||
                    ($this->_current_user_rights['isADMIN'] && $Obj_System->has_feature('Themes'))
                ) ?
                    "<a class='ti' href=\".\""
                   ." onclick=\"details('theme',".$page_vars['themeID'].","
                   ."'".$popup_arr['theme']['h']."','".$popup_arr['system']['w']."');return false;\">"
                   ."[ICON]18 18 2133 Edit current Theme in a popup window[/ICON]"
                   ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isADMIN'] ?
                    "<a class='ti' href=\".\""
                   ." onclick=\"details('system',".SYS_ID.","
                   ."'".$popup_arr['system']['h']."','".$popup_arr['system']['w']."');return false;\">"
                   ."[ICON]17 17 1331 Edit current Site Settings in a popup window[/ICON]"
                   ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isAPPROVER'] ?
                      HTML::draw_toolbar_separator()
                     ."<a class='ti' href=\".\" onclick=\"popup_fileviewer();return false;\">"
                     ."[ICON]18 18 2552 Work with files on the server[/ICON]"
                     ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isADMIN'] ? HTML::draw_toolbar_separator() : "")
                .($this->_current_user_rights['isMASTERADMIN'] ?
                     "<a class='ti' href=\"".BASE_PATH."export/sql/pages/".$page_vars['ID']."/1\""
                    ." onclick=\"export_sql('pages',".$page_vars['ID'].");return false;\">"
                    ."[ICON]22 22 839 Export this page as SQL[/ICON]"
                    ."</a>\n"
                    .($componentID_post!=1 ?
                        "<a class='ti' href=\"".BASE_PATH."export/sql/component/".$componentID_post."/1\""
                        ." onclick=\"export_sql('component',".$componentID_post.");return false;\">"
                        ."[ICON]23 23 816 Export embedded component[/ICON]"
                        ."</a>\n"
                     :
                        ""
                    )
                    ."<a class='ti' href=\"".BASE_PATH."export/sql/layouts/".$layoutID."/1\""
                    ." onclick=\"export_sql('layouts',".$layoutID.");return false;\">"
                    ."[ICON]34 34 1297 Export current layout as SQL[/ICON]"
                    ."</a>\n"
                    ."<a class='ti' href=\"".BASE_PATH."export/sql/theme/".$page_vars['themeID']."/1\""
                    ." onclick=\"export_sql('theme',".$page_vars['themeID'].");return false;\">"
                    ."[ICON]33 33 2163 Export current theme as SQL[/ICON]"
                    ."</a>\n"
                :
                    ""
                )
                .($this->_current_user_rights['isADMIN'] ?
                    "<a class='ti' href=\"".BASE_PATH."export/sql/system/".SYS_ID."/1\""
                   ." onclick=\"export_sql('system',".SYS_ID.");return false;\">"
                   ."[ICON]25 25 1348 Export entire Site as SQL[/ICON]"
                   ."</a>\n"
                 :
                    ""
                )
                .($this->_current_user_rights['isEDITOR']==1 ?
                    HTML::draw_toolbar_separator()
                   .($component_help ?
                        "<a class='ti' href=\".\""
                        ." onclick=\"geid_set('component_help','0');geid('form').submit();return false;\">"
                        ."[ICON]18 18 1231 Hide Controls\nfor components[/ICON]"
                        ."</a>\n"
                     :
                        "<a class='ti' href=\".\""
                       ." onclick=\"geid_set('component_help','1');geid('form').submit();return false;\">"
                       ."[ICON]18 18 1213 Show Controls\nfor components[/ICON]"
                       ."</a>\n"
                     )
                 :
                    ""
                );
        }
        $out.=
             HTML::draw_toolbar_end()
            ."<div class='clr_b'></div>\n";
        return $out;
    }

    protected function _draw_toolbar_type_personal()
    {
        global $system_vars;
        if ($this->_has_no_personal_toolbar) {
            return "";
        }
        if (System::has_feature('no-personal-toolbar')) {
            return "";
        }
        $checkout_page = (History::get('checkout') ?
            History::get('checkout')
         :
            BASE_PATH."checkout"
        );
        $out =
             "<a class='ti' href='".BASE_PATH."'>[ICON]15 15 950 See Home Page[/ICON]</a>"
            .HTML::draw_toolbar_separator()
            .($this->_current_user_rights['isLOCKED']==0 ?
                 "<a class='ti' href='".BASE_PATH."password'>"
                ."[ICON]32 32 965 Change your Password[/ICON]"
                ."</a>"
             :
                 "<a class='ti' href=\".\""
                ." onclick=\"alert('You cannot change the password for this account');return false;\">"
                ."[ICON]32 32 2959 Change your Password (Disabled)[/ICON]"
                ."</a>"
            )
            .($this->_current_user_rights['isLOCKED']==0 ?
                 "<a class='ti' href='".BASE_PATH."manage_profile'>"
                ."[ICON]16 16 997 Manage your Profile[/ICON]"
                ."</a>"
             :
                 "<a class='ti' href=\".\""
                ." onclick=\"alert('You cannot administer the profile for this account');return false;\">"
                ."[ICON]16 16 2991 Manage your Profile (Disabled)[/ICON]"
                ."</a>"
            )
            .(System::has_feature('module-community') && $this->_current_user_rights['isCOMMUNITYADMIN'] ?
                 "<a class='ti' href=\".\""
                ." onclick=\"popup_layer('Community Member Dashboard','community_member_dashboard',"
                .Community_Member::DASHBOARD_WIDTH.",".Community_Member::DASHBOARD_HEIGHT
                .");return false;\">"
                ."[ICON]15 15 3558 Access your Community Member Dashboard[/ICON]"
                ."</a>"
             :
                ""
            )
            .(System::has_feature('Dashboard') && $this->_current_user_rights['isEDITOR'] ?
                ($this->_current_user_rights['isLOCKED']==0 ?
                    "<a class='ti' href=\".\""
                   ." onclick=\"popup_layer('Your Dashboard','dashboard',"
                   .Widget::$container_width.",".Widget::$container_height.");return false;\">"
                   ."[ICON]28 28 3027 View your Personal Dashboard[/ICON]"
                   ."</a>"
                 :
                    "<a class='ti' href=\".\""
                   ." onclick=\"alert('You cannot access the Dashboard for this account');return false;\">"
                   ."[ICON]28 28 3055 View your Personal Dashboard (Disabled)[/ICON]"
                   ."</a>"
                )
                :
                ""
            )
            .(System::has_feature('Event-Registration') ?
                 HTML::draw_toolbar_separator()
                .($this->_current_user_rights['isLOCKED']==0 ?
                     "<a class='ti' href='".BASE_PATH."your_registered_events'>"
                    ."[ICON]20 20 124 See Events you are Registered for[/ICON]"
                    ."</a>"
                 :
                     "<a class='ti' href=\".\""
                    ." onclick=\"alert('You cannot register for events using this account');return false;\">"
                    ."[ICON]20 20 3007 See Events you are Registered for (Disabled)[/ICON]"
                    ."</a>"
                )
            :
                ""
            )
            .(System::has_feature('E-Commerce') && $this->_current_user_rights['isLOCKED']==0 ?
                  HTML::draw_toolbar_separator()
                 .(Cart::has_items() ?
                     "<a class='ti' href='".$checkout_page."'>"
                    ."[ICON]14 14 1027 See items in your shopping cart[/ICON]"
                    ."</a>"
                  :
                     "<a class='ti' href='".BASE_PATH."checkout'>"
                    ."[ICON]14 14 1013 There are no items in your shopping cart[/ICON]"
                    ."</a>"
                 )
                 ."<a class='ti' href='".BASE_PATH."your_order_history'>"
                 ."[ICON]15 15 1041 See your Order History[/ICON]"
                 ."</a>"
            :
                ""
            )
            .($system_vars['bugs_url']!='' ?
               HTML::draw_toolbar_separator()
              ."<a class='ti' href=\"".BASE_PATH."_bug\" onclick=\"return bugtracker_form(location)\">"
              .HTML::draw_icon('bugtracker')
              ."</a>"
            :
               ""
            )
            .HTML::draw_toolbar_separator()
            ."<a class='ti' href='".BASE_PATH."?command=signout'>"
            ."[ICON]29 29 1056 Sign Out[/ICON]"
            ."</a>\n"
            .HTML::draw_toolbar_separator()
            ."<a class='ti' href=\".\""
            ." onclick=\"popup_help('_help_user_toolbar');return false;\">"
            ."[ICON]14 14 1085 Help[/ICON]"
            ."</a>"
            .HTML::draw_toolbar_end();
        return
             "<div style='width:100%;'>"
            ."  <div style='float:left;font-size:10pt;' title=\""
            .System::get_item_version('system_family').' System Build Version '.System::get_item_version('build')
            ."\">\n"
            ."Signed in".(trim(get_userFullName()) ? " as <b>".trim(get_userFullName())."</b>" : "")
            .(System::has_feature('Pending-Members') && !$this->_current_user_rights['isAPPROVED'] ?
                " <span style='color:red'>(Full Member access pending)</span>"
             :
                ""
            )
            ."</div>\n"
            ." ".HTML::draw_toolbar_frame($out, 'right')
            ."</div>"
            ."<div class='clr_b'></div>\n"
            ;

    }

    protected function _draw_toolbar_type_posting_edit()
    {
        global $page_vars, $system_vars, $component_help;
        $allowEdit =        $this->_args['allowPopupEdit'];
        $popup_arr = array();
        if ($allowEdit) {
            $popup_arr['posting'] =       get_popup_size($this->_args['edit_params']['report']);
        }
        if ($this->_current_user_rights['isADMIN']) {
            $popup_arr['block_layout'] =  get_popup_size('block_layout');
            $popup_arr['layout'] =        get_popup_size('layouts');
            $popup_arr['system'] =        get_popup_size('system');
            $popup_arr['theme'] =         get_popup_size('theme');
        }
        $layoutID = ($page_vars['layoutID']!='1' ? $page_vars['layoutID'] : $system_vars['defaultLayoutID']);
        $out = HTML::draw_toolbar_separator();
        $Obj_Layout = new layout($layoutID);
        $usage = $Obj_Layout->usage();
        $msg = ($usage['internal']==0 && $usage['system']==0 && $usage['page']==1 ?
            false
         :
            "WARNING:\\n\\nThis is the Default layout for this Site - Do you wish to proceed?"
        );
        $out.=
            "<a class='ti' href=\"#\" onclick=\"geid('submode').value='edit';geid('form').submit();return false;\">"
            .$this->_args['edit_params']['icon_edit']
            ."</a>\n"
            .($this->_current_user_rights['isEDITOR'] && $this->_args['allowPopupEdit']==1  ?
                 "<a class='ti' href=\"#\""
                ." onclick=\"details('".$this->_args['edit_params']['report']."','".$this->_args['ID']."',"
                ."'".$popup_arr['posting']['h']."','".$popup_arr['posting']['w']."');return false;\">"
                .$this->_args['edit_params']['icon_edit_popup']
                ."</a>\n"
             :
                ""
            )
            .($this->_current_user_rights['isEDITOR'] && $this->_args['edit_params']['icon_delete']?
                  HTML::draw_toolbar_separator()
                 ."<a class='ti' href=\"#\""
                 ." onclick=\"if(confirm('Delete this ".$this->_args['object_name']."?\\n"
                 ."This change cannot be undone.')){"
                 ."geid_set('targetID','".$this->_args['ID']."');"
                 ."geid_set('command','".$this->_args['edit_params']['command_for_delete']."');"
                 ."geid('form').submit();}"
                 ."return false;\">"
                 .$this->_args['edit_params']['icon_delete']
                 ."</a>\n"
             :
                ""
            )
            .($this->_current_user_rights['isADMIN'] ?
                HTML::draw_toolbar_separator()
             :
                ""
            )
            .($this->_current_user_rights['isADMIN'] && $allowEdit==1  ?
                 "<a class='ti' href=\"#\" onclick=\""
                .($msg ?
                     "if (confirm('".$msg."')){"
                  :
                     ""
                 )
                 ."details('layouts','".$layoutID."','".$popup_arr['layout']['h']."','".$popup_arr['layout']['w']."');"
                 .($msg!='' ? "}" : "").";return false;\">"
                 ."[ICON]24 24 1273 Edit current Layout in a popup window[/ICON]"
                 ."</a>\n"
             :
                ""
            )
            .((
                isset($page_vars['block_layout']['systemID']) && (
                    $this->_current_user_rights['isADMIN'] &&
                    $page_vars['block_layout']['systemID']==SYS_ID ||
                    $this->_current_user_rights['isMASTERADMIN'])
            ) ?
                 "<a class='ti' href=\"#\""
                ." onclick=\"details('block_layout',".$page_vars['block_layout']['ID'].","
                ."'".$popup_arr['block_layout']['h']."','".$popup_arr['block_layout']['w']."');return false;\">"
                ."[ICON]18 18 4130 Edit"
                .($page_vars['block_layout']['systemID']==SYS_ID ? "" : " Global")
                ." Block Layout for this \n".$this->_args['object_name']." in a popup window[/ICON]"
                ."</a>\n"
             :
                 ""
            )
            .($this->_current_user_rights['isADMIN'] ?
                 "<a class='ti' href=\"#\""
                ." onclick=\"details('theme',".$page_vars['themeID'].","
                ."'".$popup_arr['theme']['h']."','".$popup_arr['theme']['w']."');return false;\">"
                ."[ICON]17 17 2132 Edit current Theme in a popup window[/ICON]"
                ."</a>\n"
             :
                ""
            )
            .($this->_current_user_rights['isADMIN'] ?
                 "<a class='ti' href=\"#\""
                ." onclick=\"details('system',".SYS_ID.","
                ."'".$popup_arr['system']['h']."','".$popup_arr['system']['w']."');return false;\">"
                ."[ICON]17 17 1331 Edit current Site Settings in a popup window[/ICON]"
                ."</a>\n"
             :
                ""
            )
            .($this->_current_user_rights['isAPPROVER'] ?
                 HTML::draw_toolbar_separator()
                ."<a class='ti' href=\"#\" onclick=\"popup_fileviewer();return false;\">"
                ."[ICON]18 18 2552 Work with files on the server[/ICON]"
                ."</a>\n"
            :
                ""
            )
            .($this->_current_user_rights['isEDITOR']==1 ?
                 HTML::draw_toolbar_separator()
                .($component_help ?
                    "<a class='ti' href=\"#\""
                   ." onclick=\"geid_set('component_help','0');geid('form').submit();return false;\""
                   .">"
                   ."[ICON]18 18 1231 Hide Controls\nfor components[/ICON]"
                   ."</a>\n"
                 :
                    "<a class='ti' href=\"#\""
                   ." onclick=\"geid_set('component_help','1');geid('form').submit();return false;\""
                   .">"
                   ."[ICON]18 18 1213 Show Controls\nfor components[/ICON]"
                   ."</a>\n"
                )
             :
                ""
            )
            .HTML::draw_toolbar_end()
            ."<div class='clr_b'></div>\n";
        return $out;
    }

    protected function _draw_toolbar_type_report()
    {
        global $targetReportID, $YYYY, $MM, $db, $submode;
        $communityID =          get_var('communityID');
        $selectID =             get_var('selectID');
        $Obj_Report =           new Report($this->_args['reportID']);
        $row =                  $Obj_Report->get_record();
        $reportTitle =
             $row['reportTitle']
            .($this->_args['report_name']=='system'?
                " in database <font color='#ff0000'>$db</font> (Codebase: ".CODEBASE_VERSION.")"
             :
                ""
             );
        $addPopupWidth =        $row['popupFormWidth'];
        $addPopupHeight =       $row['popupFormHeight'];
        $reportID =             $row['ID'];
        $report_name =          $row['name'];
        $canEditReport =        get_person_permission("MASTERADMIN");
        $toolbar =              $this->_args['toolbar'];
        $canAdd =               $Obj_Report->test_feature('button_add_new');
        $canAddToGroup =        $Obj_Report->test_feature('selected_add_to_group');
        $canExportExcel =       $Obj_Report->test_feature('selected_export_excel');
        $canViewEmails =        $Obj_Report->test_feature('selected_view_email_addresses');
        if (!(
            ($canAdd) ||
            ($canViewEmails) ||
            ($canExportExcel && $this->_args['record_count']>0) ||
            ($canAddToGroup && $this->_args['record_count']>0) ||
            ($canEditReport)
        )) {
            return "";
        }
        if ($canEditReport) {
            $reportPopup =          get_popup_size('report');
        }
        $out =
             ($reportTitle!="" ?
                 HTML::draw_toolbar_text("<b>Reports &gt; ".$reportTitle."</b>")
              :
                 ""
             )
             .($this->_args['help']!="" ?
                 "<a class='ti' href=\"".BASE_PATH.$this->_args['help']."\""
                ." onclick=\"popup_help('".$this->_args['help']."');return false;\">"
                ."<img src='".BASE_PATH."img/spacer' class='icons'"
                ." style='display:inline;float:none;height:12px;width:11px;margin:2px;background-position:-2937px 0px;'"
                ." onmouseover=\"this.style.backgroundPosition='-2948px 0px';return true;\""
                ." onmouseout=\"this.style.backgroundPosition='-2937px 0px';return true;\""
                ." alt='?' /></a>"
              :
                 ""
             )
             .HTML::draw_toolbar_separator()
             .($canExportExcel!==false ?
                 "<a class='ti' href=\"#\""
                ." onclick=\"export_excel(".$this->_args['reportID'].");return false;\">"
                ."[ICON]16 16 1099 Export to Excel -\n"
                ."apply filters or select individual records\n"
                ."to customise your results[/ICON]"
                ."</a>\n"
              :
                 ""
             )
             .($canAddToGroup!==false ?
                 "<a class='ti' href=\"#\""
                ." onclick=\"add_to_group(".$this->_args['reportID'].",580,460);return false;\">"
                ."[ICON]17 17 767 Add people to group or adjust their membership permissions[/ICON]</a>"
              :
                 ""
             );
        if ($canViewEmails!==false) {
            if ($submode!='show_addresses') {
                $out.=
                    "<a class='ti' href=\"#\""
                   ." onclick=\"return selected_view_email_addresses(1,'".$this->_args['reportID']."',"
                   ."'".$report_name."','".$this->_args['toolbar']."','".$this->_args['ajax_mode']."');\">"
                   ."[ICON]21 21 1131 View Email Addresses matching report filter or selection criteria[/ICON]"
                   ."</a>";
            } else {
                $out.=
                    "<a class='ti' href=\"#\""
                   ." onclick=\"return selected_view_email_addresses(0,'".$this->_args['reportID']."',"
                   ."'".$report_name."','".$this->_args['toolbar']."','".$this->_args['ajax_mode']."');\">"
                   ."[ICON]21 21 1152 Hide Email Addresses[/ICON]"
                   ."</a>";
            }
        }
        $out.=
            ($canAdd!==false ?
                 "<a class='ti' href=\"#\""
                ." onclick=\"details('".$this->_args['report_name']."','','".$addPopupHeight."','".$addPopupWidth."',"
                .($this->_args['selectedReportID']!=0 ?
                    $this->_args['selectedReportID']
                 :
                    "''"
                )
                .","
                .($selectID!="" ?
                    $selectID
                :
                    "''"
                )
                .","
                ."0,"
                .($communityID!="" ?
                    "'communityID=".$communityID."'"
                :
                    "''"
                )
                .");return false;\">"
                ."[ICON]33 33 917 Add...[/ICON]"
                ."</a>"
             :
                ""
            )
            .(($canAdd==1 || $canExportExcel==1 || $canAddToGroup==1) && $canEditReport==1 ?
                HTML::draw_toolbar_separator()
             :
                ""
             )
            .($canEditReport==1 ?
                  "<a class='ti' href=\"#\""
                 ." onclick=\"details('report','".$this->_args['reportID']."',"
                 ."'".$reportPopup['h']."','".$reportPopup['w']."','".$this->_args['reportID']."');return false;\">"
                 ."[ICON]15 15 1976 Edit this report[/ICON]"
                 ."</a>"
                 ."<a class='ti' rel='external'"
                 ." href=\"".BASE_PATH."report/report_columns_for_report/?selectID=".$this->_args['reportID']."\">"
                 ."[ICON]15 15 566 Show columns for this report[/ICON]"
                 ."</a>"
                 ."<a class='ti' href=\"#\" "
                 ."onclick=\""
                 ."var new_name=(prompt('Please enter new name','".$this->_args['report_name']."'));"
                 ."if (new_name!=null) {"
                 ."geid_set('targetValue',new_name);"
                 ."geid_set('targetReportID','".$this->_args['reportID']."');"
                 ."geid_set('submode','copy_report');"
                 ."geid('form').submit();"
                 ."}else{"
                 ."alert('Report copy cancelled');"
                 ."};return false;\">"
                 ."[ICON]15 15 596 Copy this report and all columns[/ICON]"
                 ."</a>"
                 ."<a class='ti' href=\"".BASE_PATH."export/sql/report/".$this->_args['reportID']."/1\""
                 ." onclick=\"export_sql('report',".$this->_args['reportID'].");return false;\">"
                 ."[ICON]20 20 611 Export this report as SQL[/ICON]"
                 ."</a>"
             :
                ""
             );
        return HTML::draw_toolbar_frame($out, 'left');
    }

    protected function _draw_toolbar_type_with_selected()
    {
        $Obj_Report =  new Report($this->_args['reportID']);
        $features = $Obj_Report->test_feature(Report::REPORT_FEATURES);
        if (!$features) {
            return ($this->_args['ajax_mode'] ? array('html'=>'','js'=>'') : "");
        }
        $Obj_Report_Column = new Report_Column;
        $content =
            $Obj_Report_Column->draw_selector_with_selected(
                $this->_args['report_name'],
                $this->_args['reportID'],
                $this->_args['ajax_mode'],
                $this->_args['toolbar']
            );
        $html =
             "<span class='admin_toolbartable'>"
            .($this->_args['ajax_mode'] ?
                $content['html']
             :
                $content
            )
            ."</span>";
        $html = HTML::draw_toolbar_frame($html, 'left');
        return
            ($this->_args['ajax_mode'] ?
                array('html'=>$html,'js'=>$content['js'].";")
             :
                $html
            );
    }

    public function draw_toolbar_end()
    {
        return
            "<div class='admin_toolbartable noprint'>"
           ."<img alt='|' class='toolbar_right' src='".BASE_PATH."img/color/404080'/>"
           ."</div>";
    }

    public function draw_toolbar_frame($content = '', $align = 'left')
    {
        return
         "<div style='float:".$align."' class='noprint'>"
        .HTML::draw_toolbar_separator()
        .$content
        ."</div>"
        ;
    }

    public function draw_toolbar_text($text)
    {
        return
         "<div class='admin_toolbartable noprint'>"
        ."<div class='toolbar_text'>"
        .$text
        ."</div>"
        ."</div>"
        ;
    }

    public function draw_toolbar_separator()
    {
        return
        "<div class='admin_toolbartable noprint'>"
        ."<img class='toolbar_left' src='".BASE_PATH."img/sysimg/icon_toolbar_end_left.gif' alt='|'/>"
        ."</div>";
    }

    public function get_version()
    {
        return VERSION_HTML;
    }
}
