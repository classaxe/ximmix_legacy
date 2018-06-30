<?php
define("VERSION_COMPONENT_WOW_SLIDER", "1.0.8");
/*
Version History:
  1.0.8 (2014-01-31)
    1) Changes to internally used parameters in Component_WOW_Slider::_setup_load_records():
         Old: filter_limit,  filter_order_by
         New: results_limit, results_order
    2) Now PSR-2 Compliant

  (Older version history in class.component_wow_slider.txt)
*/
class Component_WOW_Slider extends Component_Base
{
    protected $_first_image = array();
    protected $_first_idx =   0;
    protected $_images =      array();
    protected $_records =     array();

    public function __construct()
    {
        $this->_ident = "wow_slider";
        $fx =
            "basic,basic_linear,blast,blinds,blur,fade,flip,fly,kenburns,rotate,slices,squares,stack,stack_vertical";
        $this->_parameter_spec =    array(
            'bullets_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'bullets_margin_top' =>         array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       'Give a value to alter distance of bullets from top of frame'
            ),
            'caption_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'content_background' =>         array(
                'match' =>      '',
                'default' =>    '000000',
                'hint' =>       'Background colour for caption'
            ),
            'content_color' =>              array(
                'match' =>      '',
                'default' =>    'ffffff',
                'hint' =>       'Text colour for caption'
            ),
            'content_opacity' =>            array(
                'match' =>      'range|0,100',
                'default' =>    '80',
                'hint' =>       'Opacity in % for caption area'
            ),
            'controls_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'effect' =>                     array(
                'match' =>      'enum|'.$fx,
                'default' =>    'fade',
                'hint' =>       str_replace(',', '|', $fx)
            ),
            'effect_reverse' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
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
            'hide_if_path_extended' =>      array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       'If set, then the slider will not show when the path is extended'),
//          'maintain_aspect' =>            array(
//              'match' =>      'enum|0,1',
//              'default' =>    '1',
//              'hint' =>       'If set to 0 the image will be resized to the exact width and height given'
//          ),
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
//          'onchange' =>                   array(
//              'match' =>      '',
//              'default' =>    '',
//              'hint' =>       'javascript to execute when an image changes'
//          ),
            'random_order' =>               array(
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
            'secCaption' =>                 array(
                'match' =>      'range|0,n',
            'default'=>'0.5',
                'hint' =>       'Decimal time in seconds for fade'
            ),
            'secFade' =>                    array(
                'match' =>      'range|0,n',
            'default'=>'1',
                'hint' =>       'Decimal time in seconds for fade'
            ),
            'secShow' =>                    array(
                'match' =>      'range|0,n',
            'default'=>'4',
                'hint' =>       'Decimal time in seconds for show'
            ),
            'title' =>                      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'If given, set title of all images to this'
            ),
            'title_linked' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
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
            'thumbnail_maintain_aspect' =>  array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'Maximum height in pixels'
            ),
            'thumbnail_height' =>           array(
                'match' =>      'range|1,n',
                'default' =>    '19',
                'hint' =>       'Maximum height in pixels'
            ),
            'thumbnail_width' =>            array(
                'match' =>      'range|1,n',
                'default' =>    '80',
                'hint' =>       'Maximum width in pixels'
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
        global $page_vars;
        $this->_setup($instance, $args, $disable_params);
        $this->_draw_control_panel($this->_cp['hide_if_path_extended'] ? 1 : 0);
        $this->_draw_status();
        if (!count($this->_records)) {
            $this->_html.="(No images to show)";
            return $this->_render();
        }
        if ($this->_cp['hide_if_path_extended'] && $page_vars['path_extension']!='') {
            return $this->_html;
        }
        $this->_draw_css_include();
        $this->_draw_js();
        $this->_draw_images();
        $this->_draw_image_bullets();
        $this->_html.= "  <div class=\"ws_shadow\"></div>\n";
        return $this->_render();
    }

    protected function _draw_css_include()
    {
        global $page_vars;
        $url =      BASE_PATH.trim($page_vars['path'], '/').'?submode=css&amp;targetValue='.$this->_safe_ID;
        Page::push_content(
            'head_top',
            "<link rel=\"stylesheet\" type=\"text/css\" href=\"".$url."\" />"
        );
    }

    protected function _draw_css()
    {
        $zindex = 10;
        header("Content-type: text/css", true);
        print
              "#".$this->_safe_ID." {\n"
             ."	zoom: 1;\n"
             ."	position: relative;\n"
             ."	max-width:".$this->_cp['max_width']."px;\n"
             ."	margin:0px auto;\n"
             ."	border:9px solid #FFFFFF;\n"
             ."	text-align:left; /* reset align=center */\n"
             ."}\n"
             ."* html #".$this->_safe_ID."{ width:".$this->_cp['max_width']."px }\n"
             ."#".$this->_safe_ID." .ws_images ul{\n"
             ."	position:relative;\n"
             ."	width: 10000%;\n"
             ."	height:auto;\n"
             ."	left:0;\n"
             ."	list-style:none;\n"
             ."	margin:0;\n"
             ."	padding:0;\n"
             ."	border-spacing:0;\n"
             ."	overflow: visible;\n"
             ."	/*table-layout:fixed;*/\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_images ul li{\n"
             ."	width:1%;\n"
             ."	line-height:0; /*opera*/\n"
             ."	float:left;\n"
             ."	font-size:0;\n"
             ."	padding:0 0 0 0 !important;\n"
             ."	margin:0 0 0 0 !important;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_images{\n"
             ."	position: relative;\n"
             ."	left:0;\n"
             ."	top:0;\n"
             ."	width:100%;\n"
             ."	height:100%;\n"
             ."	overflow:hidden;\n"
             ."}#".$this->_safe_ID." .ws_images a{\n"
             ."	width:100%;\n"
             ."	display:block;\n"
             ."	color:transparent;\n"
             ."}\n"
             ."#".$this->_safe_ID." img{\n"
             ."	max-width: none !important;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_images img{\n"
             ."	width:100%;\n"
             ."	border:none 0;\n"
             ."	max-width: none;\n"
             ."	padding:0;\n"
             ."}\n"
             ."#".$this->_safe_ID." a{\n"
             ."	text-decoration: none;\n"
             ."	outline: none;\n"
             ."	border: none;\n"
             ."}\n"
             ."#".$this->_safe_ID."  .ws_bullets {\n"
             ."	font-size: 0px;\n"
             ."	float: left;\n"
             ."   margin-top: ".$this->_cp['bullets_margin_top']."px;\n"
             ."	position:absolute;\n"
             ."	z-index:".($zindex+2).";\n"
             ."}\n"
             ."#".$this->_safe_ID."  .ws_bullets div{\n"
             ."	position:relative;\n"
             ."	float:left;\n"
             ."}\n"
             ."#".$this->_safe_ID."  .wsl{\n"
             ."	display:none;\n"
             ."}\n"
             ."#".$this->_safe_ID." sound,\n"
             ."#".$this->_safe_ID." object{\n"
             ."	position:absolute;\n"
             ."}#".$this->_safe_ID."  .ws_bullets {\n"
             ."	padding: 10px;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_bullets a {\n"
             ."	margin-left:5px;\n"
             ."	width:20px;\n"
             ."	height:19px;\n"
             ."	background: url(".BASE_PATH."lib/ws/backgnd/studio/bullet.png) left top;\n"
             ."	float: left;\n"
             ."	text-indent: -4000px;\n"
             ."	position:relative;\n"
             ."	color:transparent;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_bullets a.ws_selbull, #".$this->_safe_ID." .ws_bullets a:hover{\n"
             ."	background-position: 0 100%;\n"
             ."}\n"
             ."#".$this->_safe_ID." a.ws_next, #".$this->_safe_ID." a.ws_prev {\n"
             ."	position:absolute;\n"
             ."	display:none;\n"
             ."	top:50%;\n"
             ."	margin-top:-28px;\n"
             ."	z-index:".($zindex+1).";\n"
             ."	height: 60px;\n"
             ."	width: 34px;\n"
             ."	background-image: url(".BASE_PATH."lib/ws/backgnd/studio/arrows.png);\n"
             ."	opacity: 0.8;\n"
             ."}\n"
             ."#".$this->_safe_ID." a.ws_next{\n"
             ."	background-position: 100% 0;\n"
             ."	right:10px;\n"
             ."}\n"
             ."#".$this->_safe_ID." a.ws_prev {\n"
             ."	left:10px;\n"
             ."	background-position: 0 0;\n"
             ."}\n"
             ."#".$this->_safe_ID." a.ws_next:hover{\n"
             ."	background-position: 100% 100%;\n"
             ."	opacity: 1;\n"
             ."}\n"
             ."#".$this->_safe_ID." a.ws_prev:hover {\n"
             ."	background-position: 0 100%;\n"
             ."	opacity: 1;\n"
             ."}\n"
             ."* html #".$this->_safe_ID." a.ws_next,* html #".$this->_safe_ID." a.ws_prev{display:block}\n"
             ."#".$this->_safe_ID.":hover a.ws_next, #".$this->_safe_ID.":hover a.ws_prev {display:block}\n"
             ."/* bottom center */\n"
             ."#".$this->_safe_ID."  .ws_bullets {\n"
             ."	top:0px;\n"
             ."	left:50%;\n"
             ."}\n"
             ."#".$this->_safe_ID."  .ws_bullets div{\n"
             ."	left:-50%;\n"
             ."}\n"
             ."/* separate */\n"
             ."#".$this->_safe_ID." .ws-title{\n"
             ."	position: absolute;\n"
             ."	bottom:0;\n"
             ."	left: 0;\n"
             ."	z-index: ".($zindex).";\n"
             ."	padding:10px 1%;\n"
             ."	color: #".$this->_cp['content_color'].";\n"
             ."	text-transform:none;\n"
             ."	background:#".$this->_cp['content_background'].";\n"
             ."	font-size: 18px;\n"
             ."	line-height: 26px;\n"
             ."	text-align: center;\n"
             ."	font-weight: normal;\n"
             ."	width: 98%;\n"
             ."	border-radius:0;\n"
             ."	opacity:".($this->_cp['content_opacity']/100).";\n"
             ."	filter:progid:DXImageTransform.Microsoft.Alpha(opacity=".$this->_cp['content_opacity'].");\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws-title div{\n"
             ."	padding-top:5px;\n"
             ."	font-size: 15px;\n"
             ."	line-height: 17px;\n"
             ."	text-transform:none;\n"
             ."}\n"
             ."#".$this->_safe_ID.":hover .ws-title {\n"
             ."	opacity:0.8;\n"
             ."}\n"
             ."#".$this->_safe_ID."  .ws_thumbs {\n"
             ."	font-size: 0px;\n"
             ."	position:absolute;\n"
             ."	overflow:auto;\n"
             ."	z-index:".($zindex+2).";\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_thumbs a {\n"
             ."	position:relative;\n"
             ."	text-indent: -4000px;\n"
             ."	color:transparent;\n"
             ."	opacity:0.85;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_thumbs a:hover{\n"
             ."	opacity:1;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_thumbs a:hover img{\n"
             ."	visibility:visible;\n"
             ."}\n"
             ."#".$this->_safe_ID."  .ws_thumbs {\n"
             ."    bottom: -80px;\n"
             ."    left: 0;\n"
             ."	width:100%;\n"
             ."	height:".$this->_cp['thumbnail_height']."px;\n"
             ."}#".$this->_safe_ID."  .ws_thumbs div{\n"
             ."\n"
             ."	position:relative;\n"
             ."	height:100%;\n"
             ."	letter-spacing:-4px;\n"
             ."	width:".$this->_cp['max_width']."px;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_thumbs .ws_selthumb img{\n"
             ."	opacity: 1;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_thumbs  a img{\n"
             ."	margin:3px;\n"
             ."	text-indent:0;\n"
             ."	border:4px solid #FFFFFF;\n"
             ."	box-shadow: 0 1px 1px #FFFFFF inset, 0 1px 3px rgba(0, 0, 0, 0.4);\n"
             ."	max-width:none;\n"
             ."	opacity: 0.5;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_images ul{\n"
             ."	animation: wsBasic 24s infinite;\n"
             ."	-moz-animation: wsBasic 24s infinite;\n"
             ."	-webkit-animation: wsBasic 24s infinite;\n"
             ."}\n"
             ."@keyframes wsBasic{0%{left:-0%} 8.33%{left:-0%} 16.67%{left:-100%} 25%{left:-100%}"
             ." 33.33%{left:-200%} 41.67%{left:-200%} 50%{left:-300%} 58.33%{left:-300%} 66.67%{left:-400%}"
             ." 75%{left:-400%} 83.33%{left:-500%} 91.67%{left:-500%} }\n"
             ."@-moz-keyframes wsBasic{0%{left:-0%} 8.33%{left:-0%} 16.67%{left:-100%} 25%{left:-100%}"
             ." 33.33%{left:-200%} 41.67%{left:-200%} 50%{left:-300%} 58.33%{left:-300%} 66.67%{left:-400%}"
             ." 75%{left:-400%} 83.33%{left:-500%} 91.67%{left:-500%} }\n"
             ."@-webkit-keyframes wsBasic{0%{left:-0%} 8.33%{left:-0%} 16.67%{left:-100%} 25%{left:-100%}"
             ." 33.33%{left:-200%} 41.67%{left:-200%} 50%{left:-300%} 58.33%{left:-300%} 66.67%{left:-400%}"
             ." 75%{left:-400%} 83.33%{left:-500%} 91.67%{left:-500%} }\n"
             ."#".$this->_safe_ID." {\n"
             ."	box-shadow: 0 1px 1px #FFFFFF inset, 0 1px 3px rgba(0, 0, 0, 0.4);\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_bullets  a img{\n"
             ."	text-indent:0;\n"
             ."	display:block;\n"
             ."	bottom:16px;\n"
             ."	left:-50px;\n"
             ."	visibility:hidden;\n"
             ."	position:absolute;\n"
             ."   border: 5px solid #ffffff;\n"
             ."	-moz-box-shadow: 0 0 5px #000000;\n"
             ."   box-shadow: 0 0 5px #000000;\n"
             ."	max-width:none;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_bullets a:hover img{\n"
             ."	visibility:visible;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_bulframe div div{\n"
             ."	height:".$this->_cp['thumbnail_height']."px;\n"
             ."	overflow:visible;\n"
             ."	position:relative;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_bulframe div {\n"
             ."	left:0;\n"
             ."	overflow:hidden;\n"
             ."	position:relative;\n"
             ."	width:".$this->_cp['thumbnail_width']."px;\n"
             ."	background-color:#ffffff;\n"
             ."}\n"
             ."#".$this->_safe_ID."  .ws_bullets .ws_bulframe{\n"
             ."	display:none;\n"
             ."	bottom:24px;\n"
             ."	overflow:visible;\n"
             ."	position:absolute;\n"
             ."	cursor:pointer;\n"
             ."    border: 5px solid #ffffff;\n"
             ."	-moz-box-shadow: 0 0 5px #000000;\n"
             ."    box-shadow: 0 0 5px #000000;\n"
             ."}\n"
             ."#".$this->_safe_ID." .ws_bulframe span{\n"
             ."	display:block;\n"
             ."	position:absolute;\n"
             ."	bottom:-10px;\n"
             ."	left:".(0.5*$this->_cp['thumbnail_width'])."px;\n"
             ."	background:url(".BASE_PATH."lib/ws/backgnd/studio/triangle-bottom.png);\n"
             ."	width:15px;\n"
             ."	height:6px;\n"
             ."}";
    }

    protected function _draw_images()
    {
        $Obj_GI = new Gallery_Image;
        $Obj_GI->_set('_current_user_rights', $this->_get('_current_user_rights'));
        $Obj_GI->_set('_safe_ID', $this->_get('_safe_ID'));
        $Obj_GI->_set('_context_menu_ID', 'gallery_image');
        $this->_html.=
             "  <div class=\"ws_images\">\n"
            ."    <ul>\n";
        for ($i=0; $i<count($this->_images); $i++) {
            $image = $this->_images[$i];
    //      y($image);die;
            $Obj_GI->load($image);
            $this->_html.=
                 "    <li>"
                .($image['url'] ?
                     "<a href=\"".$image['url']."\""
                    .($image['url_popup'] ? " rel='external'" : '')
                    .">"
                  :
                    ""
                 )
                .$Obj_GI->convert_Block_Layout("[BL]context_selection_start[/BL]")
                ."<img"
                ." id=\"".$this->_safe_ID."_".$i."\""
                ." src=\"".htmlentities($image['image'])."\""
                ." alt=\"".$image['title']."\""
                ." height=\"".$image['image_h']."\""
                ." width=\"".$image['image_w']."\""
                .($this->_cp['title_show']=='1' ? " title=\"".$image['title']."\"" : '')
                ."/>"
                .$Obj_GI->convert_Block_Layout("[BL]context_selection_end[/BL]")
                .($image['url'] ? "</a>" : "")
                .($this->_cp['caption_show']=='1' ? $image['caption'] : '')
                ."</li>\n";
        }
        $this->_html.=
         "    </ul>\n"
        ."  </div>\n";
    }

    protected function _draw_image_bullets()
    {
        if (!$this->_cp['bullets_show']) {
            return;
        }
        $this->_html.=
             "  <div class=\"ws_bullets\">\n"
            ."    <div>\n";
        for ($i=0; $i<count($this->_images); $i++) {
            $image = $this->_images[$i];
            $this->_html.=
                 "      <a href=\"#\" title=\"".$image['title']."\">"
                ."<img src=\"".htmlentities($image['thumbnail'])."\""
                ." alt=\"".$image['title']."\""
                ." height=\"".$image['thumbnail_h']."\""
                ." width=\"".$image['thumbnail_w']."\""
                ."/>".($i+1)
                ."</a>\n";
        }
        $this->_html.=
             "    </div>\n"
            ."  </div>\n";
    }

    protected function _draw_js()
    {
        Page::push_content(
            "javascript_top",
            "<script type=\"text/javascript\" src=\"".BASE_PATH."lib/ws/common/wowslider.js\"></script>\n"
            ."<script type=\"text/javascript\" src=\"/lib/ws/effects/".$this->_cp['effect']."\"></script>\n"
        );
        Page::push_content(
            "javascript_onload",
            "  jQuery('#".$this->_safe_ID."').wowSlider({\n"
            ."    autoPlay:         true,\n"
            ."    bullets:          false,\n"
            ."    caption:          true,\n"
            ."    captionDuration:  ".(1000*$this->_cp['secCaption']).",\n"
            ."    captionEffect:    'move',  // fade|move|slide\n"
            ."    controls:         ".($this->_cp['controls_show']=='1' ? 'true' : 'false').",\n"
            ."    delay:            ".(1000*$this->_cp['secShow']).",\n"
            ."    duration:         ".(1000*$this->_cp['secFade']).",\n"
            ."    effect:           '".$this->_cp['effect']."',\n"
            ."    height:           ".$this->_cp['max_height'].",\n"
            ."    images:           0,\n"
            ."    loop:             false,   // if a number is given, will stop when that number is reached\n"
            ."    next:             '',\n"
            ."    onBeforeStep:     "
            .($this->_cp['random_order']=='1' ?
                "function(curIdx,count){return(curIdx+1 + Math.floor((count-1)*Math.random()));}"
              :
                "0"
             )
            .",\n"
            ."    prev:             '',\n"
            ."    preventCopy:      ".($this->_isAdmin ? '0' : '1').",\n"
            //      ."    startSlide:1,\n"
            //      ."    startSlide:Math.round(Math.random()*99999),\n"
            //      ."    stopOn:4,\n"
            ."    revers:           ".$this->_cp['effect_reverse'].",\n"
            ."    stopOnHover:      false,\n"
            //      ."    thumbRate:        1,\n"
            ."    width:            ".$this->_cp['max_width']."\n"
            ."  });\n"
        );
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
        $this->_setup_load_user_rights();
        $this->_setup_do_submode();
        $this->_setup_load_records();
        $this->_setup_images();
    }

    protected function _setup_do_submode()
    {
        if ($this->_isAdmin && get_var('source')==$this->_safe_ID) {
            $Obj = new Gallery_Image;
            $this->_msg = $Obj->do_submode();
        }
        switch(get_var('submode')){
            case 'css':
                if (get_var('targetValue')==$this->_safe_ID) {
                    $this->_draw_css();
                    die;
                }
                break;
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
            $_enabled =   $record['enabled'];
            $_image =
                 trim($record['systemURL'], '/')
                ."/img/sysimg"
                ."?resize=1"
                ."&maintain=0"
                ."&height=".$this->_cp['max_height']
                ."&width=".$this->_cp['max_width']
                ."&img=".$record['thumbnail_small']
                .(isset($record['thumbnail_cs_small']) && $record['thumbnail_cs_small'] ?
                    "&cs=".$record['thumbnail_cs_small']
                  :
                    ""
                 );
            $_thumbnail =
                 trim($record['systemURL'], '/')
                ."/img/sysimg"
                ."?resize=1"
                ."&maintain=".$this->_cp['thumbnail_maintain_aspect']
                ."&height=".$this->_cp['thumbnail_height']
                ."&width=".$this->_cp['thumbnail_width']
                ."&img=".$record['thumbnail_small']
                .(isset($record['thumbnail_cs_small']) && $record['thumbnail_cs_small'] ?
                    "&cs=".$record['thumbnail_cs_small']
                  :
                    ""
                 );
            $_parentID =  ($this->_cp['filter_container_path'] ? $record['parentID'] : 0);
            $_parentTitle =  ($this->_cp['filter_container_path'] ? $record['parentTitle'] : '');
            $_subtype =   $record['subtype'];
            $_title =     ($this->_cp['title_prefix'] ? $this->_cp['title_prefix'] : "").$record['title'];
            $_url =       ($this->_cp['URL'] ?          $this->_cp['URL'] :       $record['URL']);
            $_url_popup = ($this->_cp['URL_popup']==1 ? $this->_cp['URL_popup'] : $record['popup']);
            $this->_images[] = array(
                'ID' =>             $_ID,
                'category' =>       $_category,
                'caption' =>        $_caption,
                'enabled' =>        $_enabled,
                'image' =>          $_image,
                'image_h' =>        $this->_cp['max_height'],
                'image_w' =>        $this->_cp['max_width'],
                'parentID' =>       $_parentID,
                'parentTitle' =>    $_parentTitle,
                'systemID' =>       $record['systemID'],
                'systemURL' =>      $record['systemURL'],
                'systemTitle' =>    $record['systemTitle'],
                'subtype' =>        $_subtype,
                'title' =>          $_title,
                'thumbnail' =>      $_thumbnail,
                'thumbnail_h' =>    $this->_cp['thumbnail_height'],
                'thumbnail_w' =>    $this->_cp['thumbnail_width'],
                'url' =>            $_url,
                'url_popup' =>      $_url_popup
            );
        }
    }

    public function get_version()
    {
        return VERSION_COMPONENT_WOW_SLIDER;
    }
}
