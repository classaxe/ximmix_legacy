<?php
define("VERSION_COMPONENT_CUSTOMISER_BUTTON", "1.0.1");
/*
Version History:
  1.0.1 (2015-01-02)
    1) Now uses OPTION_SEPARATOR constant not option_separator in Component_Customiser_Button::save()
    2) Now PSR-2 Compliant
  1.0.0 (2014-11-15)
    1) Initial release - for AOJ facelift planning

*/
class Component_Customiser_Button extends Component_Base
{
    public function __construct()
    {
        $this->_ident =             "draw_customiser_button";
        $this->_parameter_spec =    array(
            'cancel' =>         array(
                'default'   => 'Cancel',
                'hint'      => 'Title for Cancel button in dialog'
            ),
            'ok' =>             array(
                'default'   => 'OK',
                'hint'      => 'Title for OK button in dialog'
            ),
            'presets' =>        array(
                'default'   => '',
                'hint'      => 'Preset sequences that have been defined'
            ),
            'disable' =>        array(
                'default'   => '0',
                'hint'      => '0|1'
            ),
            'targets' =>        array(
                'default'   => 'body|1|0,h1|0|1,h2|0|1',
                'hint'      => 'CSV list of css targets each with pipe-delimited parameters 0 or 1 '
                              .'for background-color and color palette tools'),
            'title' =>          array(
                'default'   => 'Site Customiser',
                'hint'      => 'Title for icon and for dialog'
            ),
        );
    }

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        $this->_setup($instance, $args, $disable_params);
        $this->_draw();
        return $this->_html;
    }

    protected function _draw()
    {
        $this->_draw_control_panel(true);
        if (Person::get_permission("SYSADMIN") && !$this->_cp['disable']) {
            $this->_html.=
             "<span class='noprint' title='Click to customise colour scheme'"
            ." onmouseover=\"geid('icon_customiser').style.backgroundPosition='-8111px 0px';return true;\""
            ." onmouseout=\"geid('icon_customiser').style.backgroundPosition='-8095px 0px';return true;\""
            .">"
            ."<a href=\"#\" onclick=\"customise_colours.dialog("
            ."'".htmlentities($this->_cp['targets'])."',"
            ."'".htmlentities($this->_cp['title'])."',"
            ."'".htmlentities($this->_cp['ok'])."',"
            ."'".htmlentities($this->_cp['cancel'])."'"
            .");return false;\">"
            ."<img id='icon_customiser' src='".BASE_PATH."img/spacer' class='toolbar_icon'"
            ." style='width:16px;background-position:-8095px 0px;' alt='".$this->_cp['title']."'/></a>"
            ."</span>";
            Page::push_content('javascript_top', '<script type="text/javascript" src="/sysjs/spectrum"></script>'."\n");
            Page::push_content('style_include', '<link rel="stylesheet" href="/css/spectrum" />'."\n");
            Page::push_content(
                'style',
                ".customiser label{ display: block; float: left; padding: 0 0.5em 0 0; }\n"
                .".customiser input.swatch { display: block; float: left; }\n"
                .".customiser input.spectrum { display: block; float: left; width:5em; height: 14px; line-height:14px;"
                ." padding: 0px; font-family: courier-new, monospace; font-size:8pt; }\n"
            );
            page::push_content('javascript', "customise_colours_presets = \"".$this->_cp['presets']."\";\n");
        }
    }

    public function save()
    {
        global $system_vars;
        $Obj_System =   new System(SYS_ID);
        $parameters = explode(OPTION_SEPARATOR, $system_vars['component_parameters']);
        $existing = false;
        foreach ($parameters as &$entry) {
            $bits = explode('=', $entry);
            if ($bits[0]==='draw_customiser_button.presets') {
                $existing = true;
                $entry.=','.urldecode(get_var('targetValue'));
            }
        }
        if (!$existing) {
            $parameters[] = 'draw_customiser_button.presets='.urldecode(get_var('targetValue'));
        }
        $Obj_System->set_field('component_parameters', addslashes(implode(OPTION_SEPARATOR, $parameters)));
        Page::push_content(
            'javascript_onload',
            "geid_set('command','');\n"
            ."geid('form').submit();\n"
        );
    }

    public function get_version()
    {
        return VERSION_COMPONENT_CUSTOMISER_BUTTON;
    }
}
