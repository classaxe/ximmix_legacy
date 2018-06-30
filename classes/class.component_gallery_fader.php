<?php
define("VERSION_COMPONENT_GALLERY_FADER", "1.0.42");
/*
Version History:
  1.0.42 (2015-01-31)
    1) Changes to internally used parameters in Component_Gallery_Fader::_setup_load_records():
         Old: filter_limit,  filter_order_by
         New: results_limit, results_order
    2) Now PSR-2 Compliant

  (Older version history in class.component_gallery_fader.txt)
*/
class Component_Gallery_Fader extends Component_Base
{
    protected $_msg =         "";
    protected $_first_image = array();
    protected $_first_idx =   0;
    protected $_images =      array();
    protected $_images_js =   array();
    protected $_records =     array();

    public function __construct()
    {
        $this->_ident =             "gallery_fader";
        $this->_parameter_spec = array(
            'caption_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'content_background' =>         array(
                'match' =>      '',
                'default' =>    '404040',
                'hint' =>       'Background colour for caption'
            ),
            'content_color' =>              array(
                'match' =>      '',
                'default' =>    'ffffff',
                'hint' =>   'Text colour for caption'
            ),
            'content_height' =>             array(
                'match' =>      'range|1,n',
                'default' =>    '40',
                'hint' =>   'Height in pixels to make caption area'
            ),
            'content_opacity' =>            array(
                'match' =>      'range|0,100',
                'default' =>    '90',
                'hint' =>       'Opacity in % for caption area'
            ),
            'controls_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'controls_size' =>              array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l'
            ),
            'count_show' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1 - Showns (n of m) on title line'
            ),
            'cover_image' =>                array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Path to an image to use as the cover image - will show intially if given'
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
                'hint' =>   'Optionally limits items to those contained in this folder'
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
            'maintain_aspect' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'If set to 0 the image will be resized to the exact width and height given'
            ),
            'max_height' =>                 array(
                'match' =>      'range|1,n',
                'default' =>    '200',
                'hint' =>       'Maximum height in pixels'
            ),
            'max_width' =>                  array(
                'match' =>      'range|1,n',
                'default' =>    '200',
                'hint' =>       'Maximum width in pixels'
            ),
            'onchange' =>                   array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'javascript to execute when an image changes'
            ),
            'random_start' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - Whether to start with first image or play from a random position'
            ),
            'results_limit' =>              array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'results_order' =>              array(
                'match' =>      'enum|date,title',
                'default' =>    'date',
                'hint' =>       'date|title'
            ),
            'secFade' =>                    array(
                'match' =>      '',
                'default' =>    '0.5',
                'hint' =>       'Decimal time in seconds for fade'
            ),
            'secShow' =>                    array(
                'match' =>      '',
                'default' =>    '2',
                'hint' =>       'Decimal time in seconds for show'
            ),
            'title' =>                      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'If given, set title of all images to this'
            ),
            'title_prefix' =>               array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text to place before each title where shown'
            ),
            'title_show' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'URL' =>                        array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'If given, clicking on any image launches this URL'
            ),
            'URL_popup' =>                  array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - Used when URL is fixed'
            )
        );
    }

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        $this->_setup($instance, $args, $disable_params);
        $this->_draw_control_panel();
        $this->_draw_status();
        $this->_draw_css();
        if (!count($this->_records)) {
            $this->_html.="(No images to show)";
            return $this->_render();
        }
        $this->_draw_js();
        $this->_draw_controls();
        $this->_html.=
             "  <div title=\"".$this->_first_image['title']."\" id=\"".$this->_safe_ID."_mask\""
            .($this->_first_image['url'] ? " style=\"cursor:pointer;\"" : "")
            .($this->_first_image['url'] ?
                 " onclick=\""
                .($this->_first_image['url_popup'] ?
                     "popWin("
                    ."'".$this->_first_image['url']."',"
                    ."'url_".$instance."',"
                    ."'location=1,status=1,scrollbars=1,resizable=1',"
                    ."720,400,1"
                    .")"
                  :
                    "window.location='".$this->_first_image['url']."'"
                 )
                 ."\""
              :
                 ""
             )
            .">\n"
            ."  <div id=\"".$this->_safe_ID."_status\"></div>"
            ."  <div id=\"".$this->_safe_ID."_overlay\"></div>"
            ."  <img id=\"".$this->_safe_ID."_1\" src=\""
            .($this->_cp['cover_image'] ?
                 trim($this->_first_image['systemURL'], '/')
                ."/img/sysimg"
                ."?resize=1"
                ."&amp;maintain=".$this->_cp['maintain_aspect']
                ."&amp;height=".$this->_cp['max_height']
                ."&amp;width=".$this->_cp['max_width']
                ."&amp;img=".$this->_cp['cover_image']
              :
                str_replace('&', '&amp;', $this->_first_image['image'])
             )
            ."\" alt=\"Image Fader Canvas\""
            ." height=\"".$this->_cp['max_height']."\""
            ." width=\"".$this->_cp['max_width']."\""
            ."/>\n"
            ."    <img id=\"".$this->_safe_ID."_2\" src=\"".BASE_PATH."img/spacer\" alt=\"Image_Fader_Overlay\""
            ." style=\"opacity:0;filter:alpha(opacity = 0);\""
            ." height=\"".$this->_cp['max_height']."\" width=\"".$this->_cp['max_width']."\"/>\n"
            .($this->_cp['caption_show'] || $this->_cp['title_show'] || $this->_cp['count_show'] ?
                 "    <div id=\"".$this->_safe_ID."_background\"></div>"
                ."    <div id=\"".$this->_safe_ID."_content\">"
                .($this->_cp['title_show'] || $this->_cp['count_show'] ?
                    "<h2>"
                    .($this->_cp['title_show'] ?
                         "<span class='fl'>"
                        .($this->_cp['cover_image'] ? "" : stripslashes($this->_first_image['title']))
                        ."</span>"
                    :
                        ''
                    )
                    .($this->_cp['count_show'] ?
                         "<span class='fr' style='font-weight:normal'>"
                        .($this->_cp['cover_image'] ? "" : "(".($this->_first_idx+1)." of ".count($this->_images).")")
                        ."</span>"
                     :
                        ""
                    )
                    ."</h2>"
                 :
                    ""
                )
                .($this->_cp['caption_show'] ?
                    ($this->_cp['cover_image'] ? "" : "".stripslashes($this->_first_image['caption']))
                :
                    ""
                 )
                ."</div>"
              :
                ""
             )
            ."  </div>\n";
        return $this->_render();
    }

    protected function _draw_controls()
    {
        if (!$this->_cp['controls_show']) {
            return;
        }
        $this->_html.=
            "  <div id=\"".$this->_safe_ID."_controls\" style=\"opacity:0;filter:alpha(opacity=0);\"></div>\n";
    }

    protected function _draw_css()
    {
        switch($this->_cp['controls_size']){
            case 's':
                $btn_size = 25;
                break;
            case 'm':
                $btn_size = 40;
                break;
            case 'l':
                $btn_size = 50;
                break;
        }
        Page::push_content(
            'style',
            "#".$this->_safe_ID." {\n"
            ."  position:relative; margin:auto;"
            ." width:100%; height:".($this->_max_height ? $this->_max_height : $this->_cp['max_height'])."px;\n"
            ."}\n"
            ."#".$this->_safe_ID."_background {\n"
            ."  background: #".$this->_cp['content_background'].";\n"
            ."}\n"
            ."#".$this->_safe_ID."_content {\n"
            ."  position:absolute; z-index:1; padding:5px;"
            ." width:".($this->_cp['max_width']-10)."px; height:".$this->_cp['content_height']."px;"
            ." top:".($this->_max_height-10-$this->_cp['content_height'])."px;\n"
            ."  color: #".$this->_cp['content_color']."; \n"
            ."}\n"
            ."#".$this->_safe_ID."_content h2{\n"
            ."  margin: 0; font-size: 12pt; color: #".$this->_cp['content_color'].";\n"
            ."}\n"
            ."#".$this->_safe_ID."_controls{\n"
            ."  position:absolute; z-index:2; margin:5px; padding:0;"
            ." width:".(5*($btn_size+5))."px; height:".$btn_size."px;"
            ." left:".($this->_cp['max_width']-(5*($btn_size+5)+5))."px;\n"
            ."}\n"
            ."#".$this->_safe_ID."_mask{\n"
            ."  position:relative; z-index:1; margin:auto; width:100%; height:".$this->_max_height."px;\n"
            ."}\n"
            ."#".$this->_safe_ID."_status{\n"
            ."  position:absolute; z-index:2;"
            ." width:".$this->_cp['max_width']."px; height:".$this->_max_height."px; top:0;\n"
            ."}\n"
            ."#".$this->_safe_ID."_overlay{\n"
            ."  position:absolute; z-index:2;"
            ." width:".$this->_cp['max_width']."px; height:".$this->_max_height."px; top:0;\n"
            ."}\n"
            ."#".$this->_safe_ID."_1{\n"
            ."  position:absolute; z-index:1;\n"
            ."}\n"
            ."#".$this->_safe_ID."_2{\n"
            ."  position:absolute; z-index:1;\n"
            ."}\n"
            ."#".$this->_safe_ID."_background{\n"
            ."  position:absolute; z-index:1; padding:5px;"
            ." width:".($this->_cp['max_width']-10)."px; height:".$this->_cp['content_height']."px;"
            ." top:".($this->_max_height-10-$this->_cp['content_height'])."px;\n"
            ."  opacity:".($this->_cp['content_opacity']/100).";"
            ." filter: alpha(opacity=".$this->_cp['content_opacity'].");\n"
            ."}\n"
        );
    }

    protected function _draw_js()
    {
        Page::push_content(
            "javascript",
            "var obj_".$this->_safe_ID." =\n"
            ."  new image_rotator(\n"
            ."    '".$this->_safe_ID."',"
            .$this->_first_idx.","
            .$this->_cp['controls_show'].","
            .$this->_cp['count_show'].","
            .$this->_cp['secShow'].","
            .$this->_cp['secFade'].",\n"
            ."    [\n"
            .implode(",\n", $this->_images_js)."\n"
            ."    ],\n"
            ."   ".($this->_isAdmin ? 1 : 0).","
            ."'".$this->_cp['controls_size']."',"
            .$this->_cp['caption_show'].","
            .$this->_cp['title_show'].","
            ."\"".$this->_cp['onchange']."\","
            ."false,"
            ."".($this->_cp['cover_image'] ? '1' : '0')."\n"
            .");\n"
        );
        Page::push_content('javascript_onload', "  obj_".$this->_safe_ID.".do_setup();\n");
    }

    protected function _draw_status()
    {
        $this->_html.=      HTML::draw_status($this->_safe_ID.'_status', $this->_msg);
    }

    protected function _render()
    {
        return
         "<div id=\"".$this->_safe_ID."\">\n"
        .$this->_html
        ."</div>\n";
    }

    protected function _setup($instance, $args, $disable_params)
    {
        parent::_setup($instance, $args, $disable_params);
        $this->_cm_type =           "gallery_image";
        $this->_setup_load_user_rights();
        $this->_setup_do_submode();
        $this->_setup_load_records();
        $this->_setup_get_max_height();
        $this->_setup_images();
        $this->_setup_image_first();
    }

    protected function _setup_do_submode()
    {
        if ($this->_isAdmin && get_var('source')==$this->_safe_ID) {
            $Obj = new Gallery_Image;
            $this->_msg = $Obj->do_submode();
        }
    }

    public function _setup_get_max_height()
    {
        $this->_max_height = 0;
        foreach ($this->_records as $record) {
            $_filename =  '.'.trim($record['thumbnail_small'], '.');
            if (file_exists($_filename)) {
                $_ext_arr =   explode('.', $_filename);
                $_ext =       array_pop($_ext_arr);
                switch ($_ext){
                    case "gif":
                        $img = imagecreatefromgif($_filename);
                        break;
                    case "png":
                        $img = imagecreatefrompng($_filename);
                        break;
                    case "jpg":
                    case "jpeg":
                        $img = imagecreatefromjpeg($_filename);
                        break;
                    default:
                        $img = false;
                        break;
                }
                if ($img) {
                    if ($this->_cp['maintain_aspect']) {
                        $aspect =   imagesy($img) / imagesx($img);
                        if ($aspect<=1) {
                            $height = (int)($this->_cp['max_width'] * $aspect);
                        } else {
                            $height =  $this->_cp['max_height'];
                        }
                    } else {
                        $height =  $this->_cp['max_height'];
                    }
                    if ($height>$this->_max_height) {
                        $this->_max_height = $height;
                    }
                }
            }
        }
    }

    protected function _setup_load_records()
    {
        $Obj =              new Gallery_Image;
        $args =     array(
            'filter_category_list' =>
                $this->_cp['filter_category_list'],
            'filter_category_master' =>
                (isset($this->_cp['filter_category_master']) ?  $this->_cp['filter_category_master'] : false),
            'filter_container_path' =>
                (isset($this->_cp['filter_container_path']) ?   $this->_cp['filter_container_path'] : ''),
            'filter_container_subs' =>
                (isset($this->_cp['filter_container_subs']) ?   $this->_cp['filter_container_subs'] : ''),
            'filter_memberID' =>
                (isset($this->_cp['filter_memberID']) ?         $this->_cp['filter_memberID'] : ''),
            'filter_offset' =>
                (isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0),
            'filter_personID' =>
                (isset($this->_cp['filter_personID']) ?         $this->_cp['filter_personID'] : ''),
            'results_limit' =>
                $this->_cp['results_limit'],
            'results_order' =>
                (isset($this->_cp['results_order']) ?           $this->_cp['results_order'] : 'date')
        );
        $results = $Obj->get_records_matching($args);
        $this->_records = array();
        $this->_records_total = 0;
        foreach ($results['data'] as $record) {
            if ($record['enabled']) {
                $this->_records[] = $record;
                $this->_records_total++;
            }
        }
    }

    protected function _setup_images()
    {
        foreach ($this->_records as $record) {
            $_ID =        $record['ID'];
            $_category =  $record['category'];
            $_caption =   Language::convert_tags($record['content']);
            $_caption =   str_replace(array("'","\r","\n"), array("\'","\\r","\\n"), $_caption);
            $_enabled =   $record['enabled'];
            $_image =
             trim($record['systemURL'], '/')
            ."/img/sysimg"
            ."?resize=1"
            ."&maintain=".$this->_cp['maintain_aspect']
            ."&height=".$this->_cp['max_height']
            ."&width=".$this->_cp['max_width']
            ."&img=".$record['thumbnail_small']
            .(isset($record['thumbnail_cs_small']) && $record['thumbnail_cs_small'] ?
                "&cs=".$record['thumbnail_cs_small']
             :
                ""
            );
            $_parentID =  ($this->_cp['filter_container_path'] ? $record['parentID'] : 0);
            $_parentTitle =  ($this->_cp['filter_container_path'] ? $record['parentTitle'] : '');
            $_subtype =   $record['subtype'];
            $_title =     str_replace(
                array("'","\r","\n"),
                array("\'","\\r","\\n"),
                ($this->_cp['title_prefix'] ? $this->_cp['title_prefix'] : "")
                .$record['title']
            );
            $_url =       ($this->_cp['URL'] ?          $this->_cp['URL'] :       $record['URL']);
            $_url_popup = ($this->_cp['URL_popup']==1 ? $this->_cp['URL_popup'] : $record['popup']);
            $this->_images[] = array(
            'ID' =>             $_ID,
            'category' =>       $_category,
            'caption' =>        $_caption,
            'enabled' =>        $_enabled,
            'image' =>          $_image,
            'parentID' =>       $_parentID,
            'parentTitle' =>    $_parentTitle,
            'systemURL' =>      $record['systemURL'],
            'systemTitle' =>    $record['systemTitle'],
            'subtype' =>        $_subtype,
            'title' =>          $_title,
            'url' =>            $_url,
            'url_popup' =>      $_url_popup
            );
            $this->_images_js[] =
            "     {\n"
            ."       ID:        ".$_ID.",\n"
            ."       category:  \"".$_category."\",\n"
            ."       caption:   \"".$_caption."\",\n"
            ."       enabled:   ".$_enabled.",\n"
            ."       image:     \"".$_image."\",\n"
            ."       parentID:  \"".$_parentID."\",\n"
            ."       subtype:   \"".$_subtype."\",\n"
            ."       title:     \"".$_title."\",\n"
            ."       parentTitle: \"".$_parentTitle."\",\n"
            ."       url:       \"".$_url."\",\n"
            ."       url_popup: ".$_url_popup."\n"
            ."     }";
        }
    }

    protected function _setup_image_first()
    {
        $this->_first_idx =  ($this->_cp['random_start']==1 ? rand(0, count($this->_images)-1) : 0);
        $this->_first_image = $this->_images[$this->_first_idx];
    }

    public function get_version()
    {
        return VERSION_COMPONENT_GALLERY_FADER;
    }
}
