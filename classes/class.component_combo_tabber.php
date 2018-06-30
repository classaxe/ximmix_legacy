<?php
define("VERSION_COMPONENT_COMBO_TABBER", "1.0.9");
/*
Version History:
  1.0.9 (2015-02-06)
    1) Events, Podcasts and News panels may now have their orders specified
    2) Now PSR-2 Compliant

  (Older version history in class.component_combo_tabber.txt)
*/
class Component_Combo_Tabber extends Component_Base
{
    protected $_tabs = array();

    public function __construct()
    {
        $this->_ident =            "combo_tabber";
        $this->_parameter_spec =   array(
            'box' =>                                  array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'box_rss_link' =>                         array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'box_shadow' =>                           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_width' =>                            array(
                'match' =>      '',
                'default' =>    '200',
                'hint' =>       '0..x'
            ),
            'calendar_small.filter_category_list' =>  array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       'CSV Category List for Events'
            ),
            'calendar_small.filter_memberID' =>       array(
                'match' =>      'ID',
                'default' =>    '',
                'hint' =>
                    'ID of Community Member to restrict by that criteria - or zero to exclude all member content'
            ),
            'calendar_small.show' =>                  array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'list_events.box_footer' =>               array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Footer for Events'
            ),
            'list_events.box_header' =>               array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Header for Events'
            ),
            'list_events.box_title' =>                array(
                'match' =>      '',
                'default' =>    'Events',
                'hint' =>       'Title for Events'
            ),
            'list_events.filter_category_list' =>     array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       'CSV Category List for Events'
            ),
            'list_events.filter_memberID' =>          array(
                'match' =>      'ID',
                'default' =>    '',
                'hint' =>
                    'ID of Community Member to restrict by that criteria - or zero to exclude all member content'
            ),
            'list_events.filter_what' =>              array(
                'match' =>      'enum|all,future,month,past',
                'default' =>    'month',
                'hint' =>       'all|future|month|past'
            ),
            'list_events.results_limit' =>            array(
                'match' =>      '',
                'default' =>    '5',
                'hint' =>       '0..n Max Events to show'
            ),
            'list_events.results_order' =>            array(
                'match' =>      'enum|date,date_a,date_d_name_a,date_d_title_a,name,title',
                'default' =>    'date',
                'hint' =>       'date|date_a|date_d_name_a|date_d_title_a|name|title'
            ),
            'list_events.show' =>                     array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'list_news.box_footer' =>                 array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Footer for News'
            ),
            'list_news.box_header' =>                 array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Header for News'
            ),
            'list_news.box_title' =>                  array(
                'match' =>      '',
                'default' =>    'News',
                'hint' =>       'Title for News'
            ),
            'list_news.filter_category_list' =>       array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       'CSV Category List for News'
            ),
            'list_news.filter_memberID' =>            array(
                'match' =>      'ID',
                'default' =>    '',
                'hint' =>
                    'ID of Community Member to restrict by that criteria - or zero to exclude all member content'
            ),
            'list_news.results_limit' =>              array(
                'match' =>      '',
                'default' =>    '5',
                'hint' =>       '0..n Max News Items to show'
            ),
            'list_news.results_order' =>            array(
                'match' =>      'enum|date,date_a,date_d_name_a,date_d_title_a,name,title',
                'default' =>    'date',
                'hint' =>       'date|date_a|date_d_name_a|date_d_title_a,name|title'
            ),
            'list_news.show' =>                       array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'list_podcasts.audioplayer_height' =>     array(
                'match' =>      '',
                'default' =>    '24',
                'hint' =>       '0..n'
            ),
            'list_podcasts.audioplayer_width' =>      array(
                'match' =>      '',
                'default' =>    '180',
                'hint' =>       '0..n'
            ),
            'list_podcasts.box_header' =>             array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Header for Podcasts'
            ),
            'list_podcasts.box_footer' =>             array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Footer for Podcasts'
            ),
            'list_podcasts.box_header' =>             array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Header for Podcasts'
            ),
            'list_podcasts.box_title' =>              array(
                'match' =>      '',
                'default' =>    'Audio',
                'hint' =>       'Title for Podcasts'
            ),
            'list_podcasts.filter_category_list' =>   array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       'CSV Category List for Podcasts'
            ),
            'list_podcasts.filter_memberID' =>        array(
                'match' =>      'ID',
                'default' =>    '',
                'hint' =>
                    'ID of Community Member to restrict by that criteria - or zero to exclude all member content'
            ),
            'list_podcasts.results_limit' =>          array(
                'match' =>      '',
                'default' =>    '5',
                'hint' =>       '0..n Max Podcasts to show'
            ),
            'list_podcasts.results_order' =>            array(
                'match' =>      'enum|date,date_a,date_d_name_a,date_d_title_a,name,title',
                'default' =>    'date',
                'hint' =>       'date|date_a|date_d_name_a|date_d_title_a|name|title'
            ),
            'list_podcasts.show' =>                   array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'section_tab_order' =>                    array(
                'match' =>      '',
                'default' =>    'events,news,podcasts',
                'hint' =>       'CSV List of tabs to determine displayed order'
            ),
        );
    }

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        try {
            $this->_setup($instance, $args, $disable_params);
        } catch (Exception $e) {
            $this->_draw_control_panel(true);
            $this->_msg.= $e->getMessage();
            $this->_draw_status();
            return $this->_html;
        }
        $this->_draw_control_panel(true);
        $this->_draw_calendar_small();
        $this->_draw_section_tabs();
        $this->_draw_section_container_open();
        $this->_draw_events();
        $this->_draw_news();
        $this->_draw_podcasts();
        $this->_draw_section_container_close();
        return $this->_render();
    }

    protected function _draw_calendar_small()
    {
        if (!$this->_cp['calendar_small.show']) {
            return;
        }
        $args = array(
            'filter_memberID' =>    $this->_cp['calendar_small.filter_memberID'],
            'link_enlarge' =>       'calendar',
            'shadow' =>             $this->_cp['box_shadow'],
            'show' =>               'events',
            'width' =>              $this->_cp['box_width']
        );
        $this->_html.= Component_Calendar_Small::draw($args, true);
    }

    protected function _draw_events()
    {
        if (!$this->_cp['list_events.show']) {
            return;
        }
        $args = array(
            'box' =>                    ($this->_cp['box'] ? ($this->_cp['box_shadow'] ? 2 : 1) : 0),
            'box_footer' =>             $this->_cp['list_events.box_footer'],
            'box_header' =>             $this->_cp['list_events.box_header'],
            'box_rss_link' =>           $this->_cp['box_rss_link'],
            'box_title' =>              $this->_cp['list_events.box_title'],
            'box_width' =>              $this->_cp['box_width'],
            'filter_category_list' =>   $this->_cp['list_events.filter_category_list'],
            'filter_memberID' =>        $this->_cp['list_events.filter_memberID'],
            'filter_what' =>            $this->_cp['list_events.filter_what'],
            'results_limit' =>          $this->_cp['list_events.results_limit'],
            'results_order' =>          $this->_cp['list_events.results_order']
        );
        $Obj = new Event;
        $this->_html.=
             HTML::draw_section_tab_div($this->_safe_ID.'_events', $this->_selected_section)
            .$Obj->draw_listings('', $args, true)
            ."</div>";
    }

    protected function _draw_news()
    {
        if (!$this->_cp['list_news.show']) {
            return;
        }
        $args = array(
            'box' =>                    ($this->_cp['box'] ? ($this->_cp['box_shadow'] ? 2 : 1) : 0),
            'box_footer' =>             $this->_cp['list_news.box_footer'],
            'box_header' =>             $this->_cp['list_news.box_header'],
            'box_rss_link' =>           $this->_cp['box_rss_link'],
            'box_title' =>              "Latest ".$this->_cp['list_news.box_title'],
            'box_width' =>              $this->_cp['box_width'],
            'filter_category_list' =>   $this->_cp['list_news.filter_category_list'],
            'filter_memberID' =>        $this->_cp['list_news.filter_memberID'],
            'results_limit' =>          $this->_cp['list_news.results_limit'],
            'results_order' =>          $this->_cp['list_news.results_order']
        );
        $Obj = new News_Item;
        $this->_html.=
             HTML::draw_section_tab_div($this->_safe_ID.'_news', $this->_selected_section)
            .$Obj->draw_listings('', $args, true)
            ."</div>";
    }

    protected function _draw_podcasts()
    {
        if (!$this->_cp['list_podcasts.show']) {
            return;
        }
        $args = array(
            'audioplayer_height' =>     $this->_cp['list_podcasts.audioplayer_height'],
            'audioplayer_width' =>      $this->_cp['list_podcasts.audioplayer_width'],
            'box' =>                    ($this->_cp['box'] ? ($this->_cp['box_shadow'] ? 2 : 1) : 0),
            'box_footer' =>             $this->_cp['list_podcasts.box_footer'],
            'box_header' =>             $this->_cp['list_podcasts.box_header'],
            'box_rss_link' =>           $this->_cp['box_rss_link'],
            'box_title' =>
                 "Latest "
                .($this->_cp['list_podcasts.box_title']=='Sermons' && $this->_cp['list_podcasts.results_limit']==1 ?
                    'Sermon'
                 :
                    $this->_cp['list_podcasts.box_title']
                ),
            'box_width' =>              $this->_cp['box_width'],
            'filter_category_list' =>   $this->_cp['list_podcasts.filter_category_list'],
            'filter_memberID' =>        $this->_cp['list_podcasts.filter_memberID'],
            'results_limit' =>          $this->_cp['list_podcasts.results_limit'],
            'results_order' =>          $this->_cp['list_podcasts.results_order']
        );
        $Obj = new Podcast;
        $this->_html.=
             HTML::draw_section_tab_div($this->_safe_ID.'_podcasts', $this->_selected_section)
            .$Obj->draw_listings('', $args, true)
            ."</div>";
    }

    protected function _draw_section_tabs()
    {
        if ($this->_count_sections>1) {
            $this->_html.=
                 "<div style='padding:5px 0;'>"
                .HTML::draw_section_tab_buttons($this->_tabs, $this->_safe_ID, $this->_selected_section)
                ."</div>";
            return;
        }
        if ($this->_cp['calendar_small.show']) {
            $this->_html.= "<div style='padding:5px 0;'></div>";
        }
        Page::push_content(
            'javascript_onload',
            "  $('#section_".$this->_selected_section."').parent()."
            ."height($('#section_".$this->_selected_section."').height());\n"
        );
    }

    protected function _render()
    {
        return
             "<div id=\"".$this->_safe_ID."\" style='width:".$this->_cp['box_width']."px;'>\n"
            .$this->_html
            ."</div>\n";
    }

    protected function _setup($instance, $args, $disable_params)
    {
        parent::_setup($instance, $args, $disable_params);
        $this->_setup_count_sections();
        $this->_setup_tabs();
    }

    protected function _setup_count_sections()
    {
        $this->_count_sections =
             ($this->_cp['list_events.show'] ? 1 : 0)
            +($this->_cp['list_news.show'] ? 1 : 0)
            +($this->_cp['list_podcasts.show'] ? 1 : 0);
        if ($this->_count_sections==0) {
            throw new Exception("<b>Error:</b><br />".$this->_safe_ID." has nothing to show.");
        }
    }

    protected function _setup_tabs()
    {
        $_width =  (int)(($this->_cp['box_width'])/$this->_count_sections)-14;
      // provides enough space even in IE6 with its double margin bug
        $tabs = explode(',', $this->_cp['section_tab_order']);
        foreach ($tabs as $tab) {
            $tab = trim($tab);
            if (isset($this->_cp['list_'.$tab.'.show']) && $this->_cp['list_'.$tab.'.show']) {
                $this->_tabs[] =
                array(
                    'ID'=>$this->_safe_ID.'_'.$tab,
                    'label'=>$this->_cp['list_'.$tab.'.box_title'],'width'=>$_width
                );
            }
        }
        $this->_selected_section = $this->_tabs[0]['ID'];
    }

    public function get_version()
    {
        return VERSION_COMPONENT_COMBO_TABBER;
    }
}
