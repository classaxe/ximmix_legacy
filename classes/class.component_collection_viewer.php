<?php
define("VERSION_COMPONENT_COLLECTION_VIEWER", "1.0.50");
/*
Version History:
  1.0.50 (2015-02-11)
    1) Now serves 404 when visitor attempts to select an invalid podcast.
       This should dramatically reduce network traffic by search bots following invalid search paths.
       Examples:
          http://www.makingjesusknown.com/sermons/series/1-peter/fiery-trials-be-prepared (valid)
          http://www.makingjesusknown.com/sermons/series/1-peter/fiery-trials-be-preparedxxxxx (now 404)
          http://www.makingjesusknown.com/sermons/series/1-peter/fiery-trials-be-prepared/xxx (now 404)
    2) Previously selecting a selected podcast for a given author failed to highlight the selected podcast.
       This now works correctly.
       Example:
          http://www.makingjesusknown.com/sermons/speaker/bruce-smith/freedom-gods-call-for-you

  (Older version history in class.component_collection_viewer.txt)
*/
class Component_Collection_Viewer extends Component_Base
{
    protected $_cm_podcast =                    '';
    protected $_cm_podcastalbum =               '';
    protected $_paging_controls_current_pos =   '';
    protected $_paging_controls_html =          '';
    protected $_path =                          '';
    protected $_path_ext =                      '';
    protected $_path_real =                     '';
    protected $_podcast_albums =                array();
    protected $_podcast_authors =               array();
    protected $_podcast_selected =              false;
    protected $_records =                       array();
    protected $_records_total =                 0;
    protected $_selected_album =                '';
    protected $_selected_album_ID =             0;
    protected $_selected_album_label =          false;
    protected $_selected_album_memberID =       0;
    protected $_selected_album_communityID =    0;
    protected $_selected_album_default_folder = '';
    protected $_selected_album_title =          '';
    protected $_selected_author =               '';
    protected $_selected_author_label =         false;
    protected $_selected_podcast =              '';

    public function __construct()
    {
        $this->_ident =             'collection_viewer';
        $this->_cm_podcast =        'podcast';
        $this->_cm_podcastalbum =   'podcastalbum';
        $this->_parameter_spec = array(
            'audioplayer_width' =>          array(
                'match' =>      'range|160,n',
                'default' =>    '320',
                'hint' =>       '0..x'
            ),
            'author_show' =>                array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'block_layout_albums' =>        array(
                'match' =>      '',
                'default' =>    'Podcast Album',
                'hint' =>       'Block Layout to use to render albums'
            ),
            'block_layout_items' =>         array(
                'match' =>      '',
                'default' =>    'Podcast',
                'hint' =>       'Block Layout to use to render items'
            ),
            'box' =>                        array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2'
            ),
            'box_footer' =>                 array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text below displayed Podcasts'
            ),
            'box_header' =>                 array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text above displayed Podcasts'
            ),
            'box_rss_link' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title' =>                  array(
                'match' =>      '',
                'default' =>    'Podcasts',
                'hint' =>       'text'
            ),
            'box_title_link' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title_link_page' =>        array(
                'match' =>      '',
                'default' =>    'all_podcasts',
                'hint' =>       'page'
            ),
            'box_width' =>                  array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..x'
            ),
            'comments_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'comments_link_show' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_char_limit' =>         array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..n'
            ),
            'content_plaintext' =>          array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'controls_albums_heading' =>    array(
                'match' =>      '',
                'default' =>    'Albums',
                'hint' =>       'Label to place above podcast albums where listed'
            ),
            'controls_albums_show' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'Whether or not to include podcast albums in list'
            ),
            'controls_albums_url' =>        array(
                'match' =>      '',
                'default' =>    'album',
                'hint' =>       'URL portion to make album selection'
            ),
            'controls_authors_heading' =>   array(
                'match' =>      '',
                'default' =>    'Authors',
                'hint' =>       'Label to place above podcast authors where listed'
            ),
            'controls_authors_show' =>      array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'Whether or not to include podcast authors in list'
            ),
            'controls_authors_url' =>       array(
                'match' =>      '',
                'default' =>    'author',
                'hint' =>       'URL portion to make author selection'
            ),
            'controls_color_active' =>      array(
                'match' =>      'hex3|#e0e0ff',
                'default' =>    '#e0e0ff',
                'hint' =>       'Hex colour code for control button when active'
            ),
            'controls_color_active_over' => array(
                'match' =>      'hex3|#e8e8a0',
                'default' =>    '#e8e8a0',
                'hint' =>       'Hex colour code for control button when active and hovered over'
            ),
            'controls_color_border' =>      array(
                'match' =>      'hex3|#c0c0ff',
                'default' =>    '#c0c0ff',
                'hint' =>       'Hex colour code for control button borders'
            ),
            'controls_color_normal' =>      array(
                'match' =>      'hex3|#ffffff',
                'default' =>    '#ffffff',
                'hint' =>       'Hex colour code for control button when inactive'
            ),
            'controls_color_over' =>        array(
                'match' =>      'hex3|#ffff80',
                'default' =>    '#ffff80',
                'hint' =>       'Hex colour code for control button when inactive and hovered'
            ),
            'controls_important_heading' => array(
                'match' =>      '',
                'default' =>    'Featured Albums',
                'hint' =>       'Label to place above podcast albums listed as important'
            ),
            'controls_important_show' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'Whether or not to break out albums flagged as important'
            ),
            'controls_width' =>             array(
                'match' =>      'range|0,n',
                'default' =>    '250',
                'hint' =>       '0..x'
            ),
            'extra_fields_list' =>          array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'filter_album_order_by' =>      array(
                'match' =>      'enum|date,title',
                'default' =>    'date',
                'hint' =>       'date|title'
            ),
            'filter_category_list' =>       array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       'Optionally limits items to those in this gallery album - / means none'
            ),
            'filter_category_master' =>     array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'filter_container_path' =>      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally limits items to those contained in this folder'
            ),
            'filter_container_subs' =>      array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       'If filtering by container folder, enable this setting to include subfolders'
            ),
            'filter_memberID' =>            array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Community Member to restrict by that criteria'
            ),
            'filter_personID' =>            array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Person to restrict by that criteria'
            ),
            'filter_podcast_order' =>       array(
                'match' =>      'enum|asc,desc',
                'default' =>    'asc',
                'hint' =>       'asc|desc'
            ),
            'item_footer_component' =>      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below each displayed Podcast'
            ),
            'more_link_text' =>             array(
                'match' =>      '',
                'default' =>    '(More)',
                'hint' =>       'text for \'Read More\' link'
            ),
            'results_limit' =>              array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'results_paging' =>             array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2 - 1 for buttons, 2 for links'
            ),
            'selected_thumbnail_at_top' =>  array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'selected_thumbnail_height' =>  array(
                'match' =>      'range|1,n',
                'default' =>    '300',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'selected_thumbnail_image' =>   array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'selected_thumbnail_link' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'selected_thumbnail_show' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'selected_thumbnail_width' =>   array(
                'match' =>      'range|1,n',
                'default' =>    '400',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'selected_video_height' =>      array(
                'match' =>      'range|0,n',
                'default' =>    '300',
                'hint' =>       '0|x'
            ),
            'selected_video_width' =>       array(
                'match' =>      'range|0,n',
                'default' =>    '400',
                'hint' =>       '0|x'
            ),
            'selected_video_show' =>        array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1 - Whether or not to allow video to show'
            ),
            'show_uploader' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'subscribe_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - Whether or not to allow subscriptions'
            ),
            'subtitle_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'text_prompt_to_choose' =>      array(
                'match' =>      '',
                'default' =>    '<h2>Podcasts</h2><p>Please make a selection from the choices shown',
                'hint' =>       'Text to display if not choice has been made'
            ),
            'thumbnail_at_top' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height' =>           array(
                'match' =>      'range|1,n',
                'default' =>    '150',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_image' =>            array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_link' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>            array(
                'match' =>      'range|1,n',
                'default' =>    '200',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'title_linked' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'video_height' =>               array(
                'match' =>      'range|0,n',
                'default' =>    '180',
                'hint' =>       '0|x'
            ),
            'video_width' =>                array(
                'match' =>      'range|0,n',
                'default' =>    '240',
                'hint' =>       '0|x'
            ),
            'video_show' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1 - Whether or not to allow video to show'
            ),
        );
    }

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        $this->_setup($instance, $args, $disable_params);
        $this->_do_submode();
        $this->_draw_css();
        $this->_draw_control_panel(true);
        $this->_html.=
         "<table style='width:100%'>\n"
        ."  <tr>\n"
        ."    <td style='vertical-align:top;width:".$this->_cp['controls_width']."px'>";
        $this->_draw_admin_uploader();
        $this->_draw_navigation();
        $this->_html.="</td><td style='vertical-align:top'>";
        $count = $this->_Obj_JL->get_uploaded_count();
        if (!$this->_Obj_Block_Layout) {
            $this->_html.=
             "<div>"
            ."&nbsp;<b>Error:</b> There is no such Block Layout as '".$this->_cp['block_layout_items']."'"
            ."</div>\n";
        }
        if ($count) {
            $this->_msg = "<b>Success:</b> Uploaded ".$count." item".($count==1 ? '' : 's');
            $this->_Obj_JL->clear_status();
        }
        $this->_draw_status();
        $this->_draw_podcasts();
        $this->_html.="</td></tr></table>";
        return $this->_render();
    }

    private function _add_podcast()
    {
        if ($this->_isAdmin) {
            $path = $this->_selected_album_default_folder;
            mkdirs('.'.$path, 0777);
            $Obj_Uploader = new Uploader("media", $path);
            $result = $Obj_Uploader->do_upload();
        } else {
            $result = array('status'=>'403', 'message'=>'Unauthorised');
        }
        switch ($result['status']){
            case '100':
              // In progress - do nothing
                break;
            case '200':
                $this->_podcast_add($result);
                break;
            default:
                header("HTTP/1.0 200 ".$result['message'], $result['status']);
                header('Content-type: text/plain');
                print "Error: ".$result['status']." ".$result['message']."\n";
                break;
        }
    }

    private function _do_submode()
    {
        if (!get_var('submode')) {
            return;
        }
        if (!$this->_isAdmin) {
            return;
        }
        if ($this->_Obj_JL->isUploading()) {
            $this->_add_podcast();
            die();
        }
        $Obj = new Podcast;
        $this->_msg = $Obj->do_submode();
        $this->_load_data();
    }

    protected function _draw_admin_uploader()
    {
        if (
            !$this->_current_user_rights['canEdit'] ||
            $this->_cp['show_uploader']!=1 ||
            $this->_selected_album_ID==false
        ) {
            return;
        }
        $this->_Obj_JL->setup_code();
        Page::push_content('javascript', $this->_Obj_JL->get_js());
        $this->_html.=  $this->_Obj_JL->get_html();
    }

    protected function _draw_css()
    {
        $this->_css =
             "#".$this->_safe_ID." .collection_viewer_content {\n"
            ."  margin: 0 0 0 10px;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_content h1{\n"
            ."  font-size: 120%; margin: 10px 0 0 0;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_content h2{\n"
            ."  font-size: 100%; margin: 0; font-weight: normal;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav {\n"
            ."  width: ".$this->_cp['controls_width']."px;\n"
            ."  margin: 0 10px 0 0;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul {\n"
            ."  list-style-type: none; margin: 0; padding: 0;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul li {\n"
            ."  line-height: 20px; background: ".$this->_cp['controls_color_normal'].";\n"
            ."  border-left: 1px solid ".$this->_cp['controls_color_border'].";\n"
            ."  border-right: 1px solid ".$this->_cp['controls_color_border'].";\n"
            ."  border-bottom: 1px solid ".$this->_cp['controls_color_border'].";\n"
            ."  padding: 0 0.25em;\n"
            ."  margin: 0;\n"
            ."}\n"
            ."#".$this->_safe_ID." ul li.collection_viewer_nav_label {\n"
            ."  font-weight: bold; text-align: center;margin: 10px 0 0 0;\n"
            ."  border-left: none; border-right: none; background: transparent;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul li.collection_viewer_nav_label:hover {\n"
            ."  background: none;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul li:hover {\n"
            ."  background: ".$this->_cp['controls_color_over'].";\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul li.selected{\n"
            ."  background: ".$this->_cp['controls_color_active'].";\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul li.selected:hover{\n"
            ."  background: ".$this->_cp['controls_color_active_over'].";\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul li a{\n"
            ."  color: #000000; text-decoration: none;\n"
            ."}\n"
            ."#".$this->_safe_ID." .collection_viewer_nav ul li.selected a{\n"
            ."  font-weight: bold;\n"
            ."}";
    }

    protected function _draw_navigation()
    {
        if (!$this->_cp['controls_albums_show'] && !$this->_cp['controls_authors_show']) {
            return;
        }
        $this->_html.= "<div class='collection_viewer_nav' id=\"".$this->_safe_ID."_nav\">\n";
        $this->_draw_navigation_podcast_albums();
        $this->_draw_navigation_podcast_authors();
        $this->_html.= "</div>\n";
        Page::push_content('javascript_onload', "list_set_onclick_items('".$this->_safe_ID."_nav');\n");
    }

    protected function _draw_navigation_podcast_albums()
    {
        if (!$this->_cp['controls_albums_show']=='1') {
            return;
        }
        if (!count($this->_podcast_albums)) {
            return;
        }
        $regular =      array();
        $important =    array();
        foreach ($this->_podcast_albums as $record) {
            if ($this->_cp['controls_important_show']=='1' && $record['important']=='1') {
                $important[] = $record;
            } else {
                $regular[] = $record;
            }
        }
        $this->_html.=
            "<ul class='collection_viewer_nav_albums' id=\"".$this->_safe_ID."_nav_albums\">\n";
        if (count($important)>0) {
            $this->_html.=
                "  <li class='collection_viewer_nav_label'>".$this->_cp['controls_important_heading']."</li>\n";
            foreach ($important as $record) {
                $this->_draw_navigation_podcast_album($record);
            }
        }
        $this->_html.=
            "  <li class='collection_viewer_nav_label'>".$this->_cp['controls_albums_heading']."</li>\n";
        foreach ($regular as $record) {
            $this->_draw_navigation_podcast_album($record);
        }
        $this->_html.= "</ul>\n";
    }

    protected function _draw_navigation_podcast_album($record)
    {
        if (!$this->_current_user_rights['canEdit'] && $record['count']==0) {
            return;
        }
        $count =        $record['count'];
        $tooltip =      $record['date'].' '.$record['title'].' ('.$count.' item'.($count==1 ? '' : 's').')';
        $url =
        $this->_path_real.'/'
        .$this->_cp['controls_albums_url'].'/'
        .trim(substr($record['path'], strlen($this->_cp['filter_container_path'])), '/');
        $selected =     $this->_path == $url;
        $this->_html.=
             "  <li".($selected ? " class='selected'" : "").">"
            ."<a title=\"".$tooltip."\" href=\"".$url."\""
            .($this->_current_user_rights['canEdit'] ?
              " onmouseover=\""
             ."if(!CM_visible('CM_".$this->_cm_podcastalbum."')) {"
             ."_CM.type='".$this->_cm_podcastalbum."';"
             ."_CM.ID='".$record['ID']."';"
             ."_CM.can_delete=".($record['count'] ? "0" : "1").";"
             .(isset($record['important']) ? "_CM.important=".$record['important'].";" : "")
             ."_CM_ID[2]='".$record['parentID']."';"
             ."_CM_text[0]='&quot;".str_replace("'", '', $record['title'])."&quot;';"
             ."}\""
             ." onmouseout=\"_CM.type='';\""
             :
             ""
            )
            .">"
            .$record['title']." (".$count.")"
            ."</a>"
            ."</li>\n";
    }

    protected function _draw_navigation_podcast_authors()
    {
        if (!$this->_cp['controls_authors_show']=='1') {
            return;
        }
        if (count($this->_podcast_authors)) {
            $this->_html.=
                 "<ul class='collection_viewer_nav_authors' id=\"".$this->_safe_ID."_nav_authors\">"
                ."<li class='collection_viewer_nav_label'>".$this->_cp['controls_authors_heading']."</li>";
            foreach ($this->_podcast_authors as $key => $value) {
                $label =        $value['label'];
                $count =        $value['count'];
                $url =          $this->_path_real.'/'.$this->_cp['controls_authors_url'].'/'.$key;
                $selected =     $this->_path == $url;
                $this->_html.=
                     "<li".($selected ? " class='selected'" : "").">"
                    ."<a href=\"".$url."\">"
                    .$label." (".$count.")"
                    ."</a>"
                    ."</li>";
            }
            $this->_html.= "</ul>";
        }
    }

    protected function _draw_podcasts()
    {
        if (!$this->_Obj_Block_Layout) {
            return;
        }
        $Obj_Podcast = new Podcast;
        $args = array(
        '_cp' =>                          $this->_cp,
        '_paging_controls_current_pos' => $this->_paging_controls_current_pos,
        '_paging_controls_html' =>        $this->_paging_controls_html,
        '_current_user_rights' =>         $this->_current_user_rights,
        '_block_layout' =>                $this->_Obj_Block_Layout->record,
        '_context_menu_ID' =>             $this->_cm_podcast,
        '_safe_ID' =>                     $this->_safe_ID
        );
        $Obj_Podcast->_set_multiple($args);
        $html = $this->_draw_podcast_selected();
        $html.= (isset($this->_Obj_Block_Layout->record['listings_panel_header']) ?
            $Obj_Podcast->convert_Block_Layout($this->_Obj_Block_Layout->record['listings_panel_header'])
         :
            ''
        );
        if ($this->_selected_album!='' && $this->_selected_album_label===false) {
            $html.= "<b>Error:</b><br />No such album as ".$this->_selected_album;
            header("Status: 404 Not Found", true, 404); // Keep those pesky bots from following dead links!
        } elseif ($this->_selected_author!='' && $this->_selected_author_label===false) {
            $html.= "<b>Error:</b><br />No such author as ".$this->_selected_author;
            header("Status: 404 Not Found", true, 404); // Keep those pesky bots from following dead links!
        } elseif ($this->_selected_podcast && !$this->_podcast_selected) {
            $html.= "<b>Error:</b><br />No such podcast as ".$this->_selected_podcast;
            header("Status: 404 Not Found", true, 404); // Keep those pesky bots from following dead links!
        } elseif ($this->_selected_author!='' || $this->_selected_album!='') {
            $html.= $Obj_Podcast->convert_Block_Layout(
                $this->_Obj_Block_Layout->record['listings_group_header']
            );
            $path_bits = explode('/', $this->_path);
            if ($this->_podcast_selected) {
                array_pop($path_bits);
            }
            $path = implode('/', $path_bits);
            $i = 0;
            foreach ($this->_podcasts_selected as $podcast) {
                $Obj_Podcast->load($podcast);
                $Obj_Podcast->xmlfields_decode($Obj_Podcast->record);
                $Obj_Podcast->record['path'] = BASE_PATH.trim($path, '/').'/'.$Obj_Podcast->record['name'];
                if ($i>0 && !$Obj_Podcast->_draw_detail_test_if_grouping_has_changed()) {
                    $html.= $Obj_Podcast->convert_Block_Layout(
                        $this->_Obj_Block_Layout->record['listings_group_separator']
                    );
                }
                if ($i>0) {
                    $html.= $Obj_Podcast->convert_Block_Layout(
                        $this->_Obj_Block_Layout->record['listings_item_separator']
                    );
                }
                $html.= $Obj_Podcast->convert_Block_Layout(
                    $this->_Obj_Block_Layout->record['listings_item_detail']
                );
                $i++;
            }
            $html.= $Obj_Podcast->convert_Block_Layout(
                $this->_Obj_Block_Layout->record['listings_group_footer']
            );
        }
        $html.= (isset($this->_Obj_Block_Layout->record['listings_panel_footer']) ?
            $Obj_Podcast->convert_Block_Layout(
                $this->_Obj_Block_Layout->record['listings_panel_footer']
            )
         :
            ''
        );
        if (isset($_REQUEST['command'])) {
            switch($_REQUEST['command']){
                case $this->_safe_ID."_cart":
                case $this->_safe_ID."_empty":
                case $this->_safe_ID."_load":
                    $out = array(
                        'js' =>   $this->_js,
                        'html' => convert_safe_to_php($html)
                    );
                    $Obj_json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
                    // so we get an assoc array as output instead of some weird object
                    header('Content-Type: application/json');
                    print $Obj_json->encode($out);
                    die;
                break;
            }
        }
        if ($this->_selected_author=='' && $this->_selected_album=='') {
            $this->_html.= $this->_cp['text_prompt_to_choose'];
        } else {
    //      y($this);
            $this->_html.=
                 "<h1>"
                .($this->_selected_author!='' ? $this->_selected_author_label : "")
                .($this->_selected_album_label!='' ? $this->_selected_album_label : "")
                ."<br />\n"
                ."("
                .$this->_records_total." item".($this->_records_total==1 ? '' : 's')
                .", showing "
                .($this->_cp['filter_podcast_order']=='asc' ? 'earliest first' : 'latest first')
                .")"
                ."</h1>";
            if ($this->_selected_album_ID!='') {
                $Obj_Album_type =   $Obj_Podcast->_get_container_object_type();
                $Obj_Album =        new $Obj_Album_type($this->_selected_album_ID);
                $Obj_Album->_set('_context_menu_ID', 'podcastalbum');
                $Obj_Album->_set('_current_user_rights', $Obj_Podcast->_get('_current_user_rights'));
                $Obj_Album->load();
                $Obj_BlockLayout =  new Block_Layout;
                $layoutName =       $this->_cp['block_layout_albums'];
                $blockLayoutID =    $Obj_BlockLayout->get_ID_by_name($layoutName);
                if (!$blockLayoutID) {
                    $this->_html.=
                         "<div>"
                        ."&nbsp;<b>Error:</b> There is no such Block Layout as '".$this->_cp['block_layout_albums']."'"
                        ."</div>\n";
                    return false;
                }
                $Obj_BlockLayout->_set_ID($blockLayoutID);
                $Obj_Album->_set('_block_layout', $Obj_BlockLayout->load());
                $Obj_BlockLayout->draw_css_include('detail');
                if (System::has_feature('Activity-Tracking')) {
                    $Obj_Activity = new Activity;
                    $Obj_Activity->do_tracking('visits', $Obj_Album->_get_object_name(), $Obj_Album->_get_ID(), 1);
                }
                $Obj_Album->_set('_safe_ID', $this->_safe_ID);
                $Album_BL = $Obj_Album->_get('_block_layout');
                $this->_html.=
                $Obj_Album->convert_Block_Layout(
                    "[BL]context_selection_start[/BL]"
                    .$Album_BL['single_item_detail']
                    ."<div class='clear'>&nbsp;</div>"
                    ."[BL]context_selection_end[/BL]"
                )
                ."<hr />";
            }
        }
        $this->_html.=
             "<div class='collection_viewer_content' id=\"".$this->_safe_ID."_content_inner\">\n"
            .$html
            ."</div>";
    }

    protected function _draw_podcast_selected()
    {
        if (!$this->_podcast_selected) {
            return;
        }
        $Obj_Podcast = new Podcast;
        $args = array(
            '_block_layout' =>        $this->_Obj_Block_Layout->record,
            '_cp' =>                  $this->_cp,
            '_context_menu_ID' =>     'podcast',
            '_current_user_rights' => $this->_current_user_rights,
            '_mode' =>                'detail',
            '_safe_ID' =>             $this->_safe_ID
        );
        $args['_cp']['thumbnail_at_top'] =  $this->_cp['selected_thumbnail_at_top'];
        $args['_cp']['thumbnail_height'] =  $this->_cp['selected_thumbnail_height'];
        $args['_cp']['thumbnail_image'] =   $this->_cp['selected_thumbnail_image'];
        $args['_cp']['thumbnail_link'] =    $this->_cp['selected_thumbnail_link'];
        $args['_cp']['thumbnail_show'] =    $this->_cp['selected_thumbnail_show'];
        $args['_cp']['thumbnail_width'] =   $this->_cp['selected_thumbnail_height'];
        $args['_cp']['video_show'] =        $this->_cp['selected_video_show'];
        $args['_cp']['video_width'] =       $this->_cp['selected_video_width'];
        $args['_cp']['video_height'] =      $this->_cp['selected_video_height'];
        $Obj_Podcast->_set_multiple($args);
        $Obj_Podcast->record = $this->_podcast_selected;
        $Obj_Podcast->record['path'] = $this->_path;
        $Obj_Podcast->record['parentTitle'] = ($this->_selected_album!='' ?
            $this->_selected_album_label
         :
            $this->_selected_author_label
        );
        $Obj_Podcast->xmlfields_decode($Obj_Podcast->record);
        $Obj_Podcast->_set_ID($Obj_Podcast->record['ID']);
        $Obj_Podcast->_draw_detail_include_js();
        $Obj_Podcast->_draw_detail_load_publication_status();
        if ($Obj_Podcast->_get('_is_expired_publication') && !$this->_current_user_rights['canPublish']) {
            return "The selected item is no longer available";
        }
        if ($Obj_Podcast->_get('_is_pending_publication') && !$this->_current_user_rights['canPublish']) {
            return "The selected item is not yet available";
        }
        $Obj_Podcast->_draw_detail_handle_changes();
        $Obj_Podcast->_draw_detail_include_og_support();
        $html=
            "<input type='hidden' name='ID' id='ID' value='".$this->_get_ID()."' />"
            .$Obj_Podcast->convert_Block_Layout(
                "[BL]context_selection_start[/BL]"
                .$this->_Obj_Block_Layout->record['single_item_detail']
                ."[BL]context_selection_end[/BL]"
            )
           ."<hr />\n";
        if (System::has_feature('Activity-Tracking')) {
            $Obj_Activity = new Activity;
            $Obj_Activity->do_tracking('visits', $Obj_Podcast->_get_object_name(), $Obj_Podcast->_get_ID(), 1);
        }
        return $html;
    }

    protected function _draw_status()
    {
        $this->_html.=      HTML::draw_status($this->_safe_ID, $this->_msg);
    }

    protected function _load_podcast_albums()
    {
        $Obj = new Podcast_Album;
        $result = $Obj->get_records(
            array(
                'category' =>
                    ($this->_cp['filter_category_list']!='' ? $this->_cp['filter_category_list'] : ''),
                'category_master' =>
                    (isset($this->_cp['filter_category_master']) ?    $this->_cp['filter_category_master'] : ''),
                'container_path' =>
                    (isset($this->_cp['filter_container_path']) ?     $this->_cp['filter_container_path'] : ''),
                'container_subs' =>
                    (isset($this->_cp['filter_container_subs']) ?     $this->_cp['filter_container_subs'] : ''),
                'memberID' =>
                    (isset($this->_cp['filter_memberID']) ?           $this->_cp['filter_memberID'] : 0),
                'personID' =>
                    (isset($this->_cp['filter_personID']) ?           $this->_cp['filter_personID'] : 0),
                'results_order' =>
                    (isset($this->_cp['results_order']) ?             $this->_cp['results_order'] : 'date')
            )
        );
        $this->_podcast_albums = array();
        foreach ($result['data'] as $record) {
            if ($Obj->is_visible($record)) {
                $this->_podcast_albums[] = $record;
            }
        }
        foreach ($this->_podcast_albums as &$album) {
            if ($this->_selected_album ==             $album['path']) {
                $this->_selected_album_ID =             $album['ID'];
                $this->_selected_album_default_folder = $album['enclosure_url'];
                $this->_selected_album_communityID =    $album['communityID'];
                $this->_selected_album_memberID =       $album['memberID'];
                $this->_selected_album_label =          $album['title'];
                break;
            }
        }
        switch ($this->_cp['filter_album_order_by']){
            case 'date':
                usort($this->_podcast_albums, array("Component_Collection_Viewer", "_load_podcast_albums_sort_date"));
                break;
            case 'title':
                usort($this->_podcast_albums, array("Component_Collection_Viewer", "_load_podcast_albums_sort_title"));
                break;
        }
    }

    protected function _load_podcast_album_counts()
    {
        foreach ($this->_podcast_albums as &$album) {
            if (!isset($album['count'])) {
                $album['count'] = 0;
            }
            foreach ($this->_podcasts as $podcast) {
                if ($album['path']==$podcast['container_path']) {
                    $album['count']++;
                }
            }
        }
    }

    protected static function _load_podcast_albums_sort_date($a, $b)
    {
        if ($a['date'] == $b['date']) {
            return 0;
        }
        return ($a['date'] < $b['date']) ? +1 : -1;
    }

    protected static function _load_podcast_albums_sort_title($a, $b)
    {
        if ($a['title'] == $b['title']) {
            return 0;
        }
        return ($a['title'] > $b['title']) ? +1 : -1;
    }

    protected function _load_podcast_authors()
    {
        $out = array();
        foreach ($this->_podcasts as $record) {
            $label = trim($record['author']);
            if (!$label) {
                $label = "(Not Specified)";
            }
            $value =  get_web_safe_ID($label);
            if (!isset($out[$value])) {
                $out[$value] = array(
                'count' => 1,
                'label' => $label
                );
            } else {
                $out[$value]['count']++;
            }
        }
        uksort($out, array("Component_Collection_Viewer", "_load_podcast_authors_sort"));
        $ns = false;
        foreach ($out as $key => $value) {
            if ($key=='not-specified') {
                $ns = $value;
            }
        }
        if ($ns) {
            $this->_podcast_authors['not-specified']=$ns;
        }
        foreach ($out as $key => $value) {
            $this->_podcast_authors[$key]=$value;
        }
        foreach ($out as $key => $value) {
            if ($this->_selected_author == $key) {
                $this->_selected_author_label = $value['label'];
                break;
            }
        }
    }

    protected static function _load_podcast_authors_sort($a, $b)
    {
        $al = strtolower($a['label']);
        $bl = strtolower($b['label']);
        if ($al == $bl) {
            return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }

    protected function _load_podcast_selected()
    {
        if (!$this->_selected_podcast) {
            return;
        }
        $matched = false;
        if ($this->_selected_album_ID) {
            foreach ($this->_podcasts as $record) {
                if (
                    $this->_selected_album_ID==$record['parentID'] &&
                    trim(strToLower($record['name']))==trim(strToLower($this->_selected_podcast))
                ) {
                    $this->_podcast_selected = $record;
                    $matched = true;
                    break;
                }
            }
        }
        if ($this->_selected_author) {
            foreach ($this->_podcasts as $record) {
                if (trim(strToLower($record['name']))==trim(strToLower($this->_selected_podcast))) {
                    $this->_podcast_selected = $record;
                    $matched = true;
                    break;
                }
            }
        }

        if (!$matched) {
            $this->_podcast_selected = false;
        }

    }

    protected function _load_podcasts()
    {
        $this->_filter_offset = (isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0);
        $Obj = new Podcast;
        $container_path = $this->record['path'];
        $result = $Obj->get_records(
            array(
                'container_path' =>   $container_path,
                'results_order' =>    ($this->_cp['filter_podcast_order']=='asc' ? 'date_a' : 'date')
            )
        );
        $this->_podcasts = array();
        foreach ($result['data'] as $record) {
            $matched = false;
            $record['author_link'] =
                 $this->_path_real.'/'
                .$this->_cp['controls_authors_url'].'/'.get_web_safe_ID($record['author']);
            foreach ($this->_podcast_albums as $p) {
                if ($record['parentID']==$p['ID']) {
                    $record['parent_link'] =
                         $this->_path_real.'/'
                        .$this->_cp['controls_albums_url'].'/'
                        .trim(substr($p['path'], strlen($this->_cp['filter_container_path'])), '/');
                    $matched = true;
                    break;
                }
            }
            if ($matched) {
                if ($Obj->is_visible($record)) {
                    $this->_podcasts[] = $record;
                }
            }
        }
        $Obj_Parent_type =  $Obj->_get_container_object_type();
        $parentTitles = array();
        $this->_podcasts_selected = array();
        $i=0;
        if ($this->_selected_album=='' && $this->_selected_author=='') {
            $this->_records_total = 0;
            return;
        }
        foreach ($this->_podcasts as $p) {
            if (
                $this->_selected_author=='' ||
                $this->_selected_author=='*' ||
                get_web_safe_ID($p['author'])==$this->_selected_author ||
                ($p['author']=='' && $this->_selected_author=='not-specified')
            ) {
                if ($this->_selected_album=='' || $p['container_path']==$this->_selected_album) {
                    if ($i>=$this->_filter_offset && $i<$this->_filter_offset+$this->_cp['results_limit']) {
                        if (!array_key_exists($p['parentID'], $parentTitles)) {
                            $Obj_Parent = new $Obj_Parent_type($p['parentID']);
                            $parentTitles[$p['parentID']] = $Obj_Parent->get_field('title');
                        }
                        $p['parentTitle'] = $parentTitles[$p['parentID']];
                        $this->_podcasts_selected[] = $p;
                    }
                    $i++;
                }
            }
        }
        $this->_records_total = $i;
        $this->_setup_get_computed_sequence_numbers();
    }

    protected function _load_data()
    {
        $this->_load_podcast_albums();
        $this->_load_podcasts();

        $this->_load_podcast_authors();
        $this->_load_podcast_album_counts();
        $this->_load_podcast_selected();
        $this->_draw_listings_load_paging_controls();
    }

    protected function _draw_listings_load_paging_controls()
    {
        global $page_vars;
        $results_limit =    $this->_cp['results_limit'];
        $results_paging =   $this->_cp['results_paging'];
        if (!$results_paging || !$results_limit || $this->_records_total==count($this->_podcasts_selected)) {
            return;
        }
        $this->_paging_controls_current_pos =
             "<div class=\"".$this->_ident."_nav_pos\">"
            ."Showing ".($this->_filter_offset+1)." to "
            .($this->_filter_offset+$results_limit>$this->_records_total ?
             $this->_records_total
              :
                $this->_filter_offset+$results_limit
             )
            ." of ".$this->_records_total
            ."</div>";
        if (!isset($_REQUEST['command']) || $_REQUEST['command']!=$this->_safe_ID."_load") {
            Page::push_content(
                "javascript",
                "function ".$this->_safe_ID."_paging(offset) {\n"
                ."  var post_vars='command=".$this->_safe_ID."_load&offset='+offset;\n"
                ."  window.focus();\n"
                ."  if (btn=geid('".$this->_safe_ID."_previous')) {btn.disabled=true;}\n"
                ."  if (btn=geid('".$this->_safe_ID."_next')) {btn.disabled=true;}\n"
                ."  var fn = function(){hidePopWin(null);externalLinks();"
                ."\$('#".$this->_safe_ID."_content_inner audio').mediaelementplayer();"
                .(Base::module_test('Church') ?
                    "Logos.ReferenceTagging.tag(\$J('#".$this->_safe_ID."_content_inner')[0])"
                  :
                    ""
                 )
                ."};\n"
                ."  show_popup_please_wait();\n"
                ."  ajax_post_streamed("
                ."base_url+'".trim($page_vars['path'], '/')."','".$this->_safe_ID."_content_inner',post_vars,fn"
                .");\n"
                ."  return false;\n"
                ."}"
            );
        }
        switch($results_paging){
            case 1:
                $this->_paging_controls_html=
                    "<div class=\"".$this->_ident."_nav\">"
                     .($this->_filter_offset>0 ?
                     "<input id=\"".$this->_safe_ID."_previous\" type='button' value='Previous'"
                    ." onclick=\""
                    .$this->_safe_ID."_paging(".($this->_filter_offset+$this->_cp['results_limit']*-1).");"
                    ."\""
                    ." />"
                     : ""
                     )
                    .($this->_records_total > $this->_filter_offset+$this->_cp['results_limit'] ?
                    "<input id=\"".$this->_safe_ID."_next\" type='button' value='Next'"
                    ." onclick=\""
                    .$this->_safe_ID."_paging(".($this->_filter_offset+$this->_cp['results_limit']*1).");"
                    ."\""
                    ." />"
                    : ""
                    )
                    ."</div>";
                break;
            case 2:
                $pages = ceil($this->_records_total/$this->_cp['results_limit']);
                $this->_paging_controls_html.=
                     "<table class=\"pnav\" cellspacing=\"0\" cellpadding=\"0\""
                    ." summary=\"Paging controls for ".$this->_ident."\">\n"
                    ."  <tr>\n"
                    ."    <td class=\"pnav_pn\"><div>\n"
                    ."<a"
                    .($this->_filter_offset>0 ?
                          " title=\"View Previous Page\""
                         ." href=\""
                         .BASE_PATH.trim($page_vars['path'], '/')
                         ."?offset=".($this->_filter_offset+$this->_cp['results_limit']*-1)
                         ."\""
                         ." onclick=\""
                         ."return ".$this->_safe_ID."_paging("
                         .($this->_filter_offset+$this->_cp['results_limit']*-1)
                         .");"
                         ."\""
                      :
                        " title=\"(View Previous Page)\" class='disabled' href=\"#\" onclick='return false;'"
                     )
                    .">Prev</a></div></td>\n"
                    ."<td class=\"pnav_num\"><div>\n";
                for ($i=0; $i<$pages; $i++) {
                    $this->_paging_controls_html.=
                        "<a title=\"View page ".($i+1)."\""
                        ." href=\""
                        .BASE_PATH.trim($page_vars['path'], '/')
                        ."?offset=".($this->_cp['results_limit']*$i)
                        ."\""
                        ." class='"
                        .($this->_filter_offset==$i*$this->_cp['results_limit'] ? "current " : "")
                        .($this->_records_total==$i*$this->_cp['results_limit'] ? "last " : "")
                        ."'"
                        ." onclick=\"return ".$this->_safe_ID."_paging(".($this->_cp['results_limit']*$i).");\">"
                        .($i+1)
                        ."</a> ";
                }
                $this->_paging_controls_html.=
                    "</div></td>\n"
                    ."<td class=\"pnav_pn\"><div>"
                    ."<a"
                    .($this->_filter_offset+$this->_cp['results_limit']<$this->_records_total ?
                         " title=\"View Next Page\""
                        ." href=\""
                        .BASE_PATH.trim($page_vars['path'], '/')
                        ."?offset=".($this->_filter_offset+$this->_cp['results_limit'])
                        ."\""
                        ." onclick=\""
                        ."return ".$this->_safe_ID."_paging("
                        .($this->_filter_offset+$this->_cp['results_limit'])
                        .");"
                        ."\""
                     :
                        " title=\"(View Next Page)\" class='disabled' href=\"#\" onclick='return false;'"
                    )
                    .">Next</a></div></td>\n"
                    ."</tr>\n"
                    ."</table>\n";
                break;
        }
    }

    private function _podcast_add($result)
    {
        $Obj_Podcast = new Podcast;
        $path =         $result['path'];
        $path_arr =     explode('/', $path);
        $file =         array_pop($path_arr);
        $file_arr =     explode('.', $file);
        $tmp =          array_shift($file_arr);
        $date =         sanitize('date-stamp', substr($file, 0, 10));
        if (!$date) {
            $date = sanitize('date-stamp', substr($file, 0, 4).'-'.substr($file, 4, 2).'-'.substr($file, 6, 2));
        }
        if (!$date) {
            (System::has_feature('Posting-default-publish-now') ? get_timestamp() : '0000-00-00');
        }
        $data = array(
            'communityID' =>      $this->_selected_album_communityID,
            'date' =>             $date,
            'enabled' =>          1,
            'enclosure_url' =>    $path,
            'memberID' =>         $this->_selected_album_memberID,
            'parentID' =>         $this->_selected_album_ID,
            'permPUBLIC' =>       1,
            'permSYSLOGON' =>     1,
            'permSYSMEMBER' =>    1,
            'systemID' =>         SYS_ID,
            'themeID' =>          1,
            'type' =>             $Obj_Podcast->_get_type()
        );
        $ID = $Obj_Podcast->insert($data);
        $Obj_Podcast->_set_ID($ID);
        $data = $Obj_Podcast->get_mp3_metadata();
        if ($data['title']=='') {
            $data['title'] = title_case_string(str_replace('_', ' ', $tmp));
        }
        if ($data['name']=='') {
            $data['name'] = str_replace('_', '-', get_web_safe_ID($tmp));
        }
        $Obj_Podcast->update($data);
        $Obj_Podcast->set_container_path();
        $Obj_Podcast->set_path();
  //    $Obj_Podcast->sequence_append();
        do_log(1, __CLASS__.'::'.__FUNCTION__.'()', 'Added podcast', 'Result: '.print_r($result, 1));
        $_SESSION[$this->_safe_ID.'_results'][] = $result;
        return $ID;
    }

    protected function _render()
    {
        global $print;
        Page::push_content('style', $this->_css);
        $Obj_Podcast = new Podcast;
        return $Obj_Podcast->draw_panel_box(
            $this->_cp['box'],
            $this->_cp['box_title'],
            $this->_html,
            $this->_cp['box_title_link'],
            $this->_cp['box_title_link_page'],
            $this->_cp['box_rss_link'],
            $this->_safe_ID,
            $this->_cp['box_width'],
            $this->_cp['box']==2&&$print!="1"
        );
    }

    protected function _setup($instance, $args, $disable_params)
    {
        parent::_setup($instance, $args, $disable_params);
        $this->_setup_load_block_layout($this->_cp['block_layout_items']);
        $this->_setup_load_user_rights();
        $this->_setup_get_path();
        $this->_Obj_JL = new Jumploader;
        $this->_Obj_JL->init($this->_safe_ID);
        $this->_Obj_JL->_ext = 'mp3';
        $this->_load_data();
    }

    protected function _setup_get_computed_sequence_numbers()
    {
        switch($this->_cp['filter_podcast_order']){
            case 'asc':
                $i=1;
                foreach ($this->_podcasts_selected as &$p) {
                    $p['computed_sequence_value'] = $i+$this->_filter_offset;
                    $i++;
                }
                break;
            case 'desc':
                $i=$this->_records_total;
                foreach ($this->_podcasts_selected as &$p) {
                    $p['computed_sequence_value'] = $i-$this->_filter_offset;
                    $i--;
                }
                break;
        }
    }

    protected function _setup_get_path()
    {
        global $page_vars;
        $this->_path =      BASE_PATH.trim($page_vars['path'], '/');
        $this->_path_real = BASE_PATH.trim($page_vars['path_real'], '/');
        $this->_path_ext =  trim(substr($this->_path, strlen($this->_path_real)), '/');
        $this->_selected_album = '';
        $this->_selected_author = '';
        if (
            substr($this->_path_ext, 0, strlen($this->_cp['controls_albums_url']))==$this->_cp['controls_albums_url']
        ) {
            $bits = explode('/', substr($this->_path_ext, strlen($this->_cp['controls_albums_url'])+1));
            $this->_selected_album =
            (!isset($bits[0]) || $bits[0]=='' ? '' : $this->_cp['filter_container_path'].$bits[0]);
        }
        if (
            substr($this->_path_ext, 0, strlen($this->_cp['controls_authors_url']))==$this->_cp['controls_authors_url']
        ) {
            $bits = explode('/', substr($this->_path_ext, strlen($this->_cp['controls_authors_url'])+1));
            $this->_selected_author =
            (!isset($bits[0]) ? '' : $bits[0]);
        }
        if (isset($bits[1])) {
            $this->_selected_podcast = $bits[1];
        }
        if (isset($bits[2])) {
            $this->_selected_podcast = implode('/', array_slice($bits, 1));
        }
    }

    protected function _setup_load_block_layout($blockLayoutName)
    {
        if ($this->_Obj_Block_Layout = parent::_setup_load_block_layout($blockLayoutName)) {
            $this->_Obj_Block_Layout->draw_css_include('listings');
            $this->_Obj_Block_Layout->draw_css_include('detail');
        }
    }

    public function get_version()
    {
        return VERSION_COMPONENT_COLLECTION_VIEWER;
    }
}
