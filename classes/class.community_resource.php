<?php
define('COMMUNITY_RESOURCE_VERSION', '1.0.2');
/* Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)

/*
Version History:
  1.0.2 (2015-01-31)
    1) Community_Resource::_draw_jsonp() bug fix - now correctly allows for paging controls
    2) Community_Resource::_draw_rss() - some internal changes to correct misleading variable names
       and make 'help' text title more conformant to other RSS help wording
    3) Now PSR-2 Compliant

*/

class Community_Resource extends Community_Display
{

    public function draw($cp, $path_extension, $community_record)
    {
        $this->_setup($cp, $path_extension, $community_record);
        if ($this->_path_extension=='js' || substr($this->_path_extension, 0, 3)=='js/') {
            return $this->_serve_jsonp();
        }
        if ($this->_path_extension=='rss' || substr($this->_path_extension, 0, 4)=='rss/') {
            return $this->_draw_rss();
        }
        if ($this->_path_extension=='sermons' || substr($this->_path_extension, 0, 8)=='sermons/') {
            return $this->_draw_sermons();
        }
        if ($this->_path_extension=='gallery' || substr($this->_path_extension, 0, 8)=='gallery/') {
            return $this->_draw_gallery();
        }
        if (Posting::get_match_for_name($this->_path_extension, $type, $ID)) {
            return $this->_draw_posting($type, $ID);
        }
        if (
        Portal::_parse_request_search_range(
            $this->_path_extension,
            $page,
            $search_date_start,
            $search_date_end,
            $search_type
        )) {
            return $this->_draw_search_results($search_date_start, $search_date_end, $search_type);
        }
        return $this->_draw_profile();
    }

    protected function _draw_gallery()
    {
        $Obj = new Component_Gallery_Album;
        $args = array(
             'filter_root_path' =>        '//communities/'.$this->_cp['community_name'].'/members/',
             'folder_tree_height' =>      800,
             'folder_tree_width' =>       300,
             'indicated_root_folder' =>   'members',
             'path_prefix' =>             '//communities/'.$this->_cp['community_name'].'/gallery',
             'show_folder_tree' =>        1,
             'show_folder_icons' =>       1,
             'show_watermark' =>          1,
             'sort_by' =>                 'name'
        );
        return $Obj->draw($this->_instance, $args, true);
    }

    protected function _draw_posting($type, $ID)
    {
        global $page_vars;
        switch ($type){
            case 'article':
                $Obj = new Article($ID);
                break;
            case 'event':
                $Obj = new Event($ID);
                break;
            case 'news-item':
                $Obj = new News_Item($ID);
                break;
            case 'podcast':
                $Obj = new Podcast($ID);
                break;
            default:
                throw new Exception("Unknown Community Posting type \"".$type."\"");
            break;
        }
        $page_vars['object_name'] =   $Obj->_get_object_name();
        $page_vars['object_type'] =   get_class($Obj);
        $page_vars['ID']=$ID;
        return $Obj->draw_detail();
    }

    protected function _draw_profile()
    {
        $Obj_CMD = new Community_Member_Display;
        return $Obj_CMD->draw($this->_cp, $this->_path_extension);
    }

    protected function _draw_rss()
    {
        global $page_vars;
        $path_arr =  explode('/', $this->_path_extension);
        $submode =  (isset($path_arr[1]) ? $path_arr[1] : '');
        $Obj_RSS =  new RSS;
        $args =
        array(
            'base_path' =>      $this->_community_record['URL_external'].'/rss/',
            'feed_title' =>     $page_vars['title']." &gt; RSS Service",
            'isShared'=>        1,
            'communityID' =>    $this->_get_ID(),
            'MM' =>             '',
            'offset' =>         (get_var('offset') ? get_var('offset') : 0),
            'render' =>         true,
            'request' =>        $this->_path_extension,
            'title' =>          $page_vars['title']." > RSS".($submode ? " > ".title_case_string($submode) : ""),
            'what' =>           (get_var('what') ? get_var('what') : 'future'),
            'YYYY' =>           '',
        );
        $Obj_RSS->serve($args);
    }

    protected function _draw_search_results($search_date_start, $search_date_end, $search_type)
    {
        $args = array(
             'search_date_end' =>             $search_date_end,
             'search_date_start' =>           $search_date_start,
             'search_communityID' =>          $this->_get_ID(),
             'search_offset' =>               get_var('search_offset', 0),
             'search_results_page_limit' =>   10,
             'search_type' =>                 get_var('search_type', $search_type),
             'systemIDs_csv' =>               SYS_ID
        );
        $Obj_Search = new Search(SYS_ID);
        $cps = array(
            'controls' =>                 false,
            'search_articles' =>          true,
            'search_events' =>            true,
            'search_jobs' =>              false,
            'search_news' =>              true,
            'search_jobs' =>              false,
            'search_gallery_images' =>    true,
            'search_pages' =>             false,
            'search_podcasts' =>          true,
            'search_products' =>          false
        );
        $Obj_Search->_set_cp($cps);
        $search_results = $Obj_Search->_get_results($args);
  //      y($search_results);
        return $Obj_Search->_draw_results($search_results, $args);
    }

    protected function _draw_sermons()
    {
        $Obj = new Community_Component_Collection_Viewer;
        $args = array(
             'text_prompt_to_choose' =>
                '<h2>Sermons</h2><p>Please choose a member or speaker to view their sermons.</p>',
             'controls_albums_heading' =>     'Members',
             'controls_albums_show' =>        1,
             'controls_albums_url' =>         'sermons/member',
             'controls_authors_heading' =>    'Speakers',
             'controls_authors_show' =>       1,
             'controls_authors_url' =>        'sermons/speaker',
             'controls_width' =>              380,
             'filter_album_order_by' =>       'title',
             'filter_container_path' =>       '//communities/'.$this->_cp['community_name'].'/members/',
             'filter_podcast_order' =>        'desc',
             'results_limit' =>               10,
             'results_paging' =>              2
        );
        return $Obj->draw($this->_instance, $args, false);
    }

    protected function _serve_jsonp()
    {
        $type = substr($this->_path_extension, 3);
        switch($type){
            case 'articles':
                $Obj = new Community_Article;
                break;
            case 'calendar':
                $Obj = new Community_Component_Calendar_Large;
                break;
            case 'events':
                $Obj = new Community_Event;
                break;
            case 'news':
                $Obj = new Community_News_Item;
                break;
            case 'podcasts':
                $Obj = new Community_Podcast;
                break;
            default:
                throw new Exception("Unknown Community JSONP request".($type ? " \"".$type."\"" : ""));
            break;
        }
        $Obj->community_record = $this->_community_record;
        switch($type){
            case 'calendar':
                $args = array(
                    'show_controls' =>    0,
                    'show_heading' =>     0,
                );
                $out =                  $Obj->draw_json('', $args, true);
                break;
            default:
                $args = array(
                    'author_show' =>      1,
                    'content_show' =>     1,
                    'results_limit' =>    get_var('limit', 10),
                    'results_paging' =>   2
                );
                $out = $Obj->draw_listings_json('', $args, true);
                break;
        }
        if ($this->_cp['community_title']) {
            $sourceline =
                 "<div style='text-align:center'>\n"
                ."<a target='_blank' href=\"".$this->_cp['community_URL']."\">"
                .$this->_cp['community_title']
                ."</a>\n"
                ."</div>";
            $pos =  strrpos($out['html'], '</div>');
            $html = substr($out['html'], 0, $pos).$sourceline.substr($out['html'], $pos);
            $out['html'] = $html;
        }
        header('Content-Type: application/javascript;charset=utf-8');
        print get_var('callback')."(".json_encode($out).");\n";
        die;
    }

    protected function _setup($cp, $path_extension, $community_record)
    {
        $this->_cp =                $cp;
        $this->_path_extension =    $path_extension;
        $this->_community_record =  $community_record;
        $this->_set_ID($this->_community_record['ID']);
    }

    public function get_version()
    {
        return COMMUNITY_RESOURCE_VERSION;
    }
}
