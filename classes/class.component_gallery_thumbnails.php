<?php
define("VERSION_COMPONENT_GALLERY_THUMBNAILS", "1.0.35");
/*
Version History:
  1.0.35 (2014-01-31)
    1) Changes to internally used parameters in Component_Gallery_Thumbnails::_setup_load_images():
         Old: filter_limit,  filter_order_by
         New: results_limit, results_order
    2) Now PSR-2 Compliant

  (Older version history in class.component_gallery_thumbnails.txt)
*/
class Component_Gallery_Thumbnails extends Component_Base
{
    protected $_css =     '';
    protected $_html =    '';
    protected $_albums =        false;
    protected $_album_default_folder =  false;
    protected $_album_ID =      false;
    protected $_images =        array();
    protected $_records;

    public function __construct()
    {
        $this->_ident =             "gallery_thumbnails";
        $this->_parameter_spec =    array(
            'caption_height' =>             array(
                'match' =>      'range|0,n',
                'default' =>    '55',
                'hint' =>       'Height of caption in pixels (if shown)'
            ),
            'color_background' =>           array(
                'match' =>      'hex3|',
                'default' =>    'ffffff',
                'hint' =>       'Hex code for background colour for each image block with title and caption, if shown'
            ),
            'color_border' =>               array(
                'match' =>      'hex3|',
                'default' =>    'c0c0c0',
                'hint' =>       'Hex code for colour to use for border if shown'
            ),
            'color_image_border' =>         array(
                'match' =>      'hex3|',
                'default' =>    'c0c0c0',
                'hint' =>       'Hex code for colour to use for image border if shown'
            ),
            'color_image_border_over' =>    array(
                'match' =>      'hex3|',
                'default' =>    '4040ff',
                'hint' =>       'Hex code for colour to use for image border on mouse over if shown'
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
            'image_padding_horizontal' =>   array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       'Space to pad around images horizontally'
            ),
            'image_padding_vertical' =>     array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       'Space to pad around images vertically'
            ),
            'image_spacing_horizontal' =>   array(
                'match' =>      'range|0,n',
                'default' =>    '10',
                'hint' =>       'Space to leave between images horizontally'
            ),
            'image_spacing_vertical' =>     array(
                'match' =>      'range|0,n',
                'default' =>    '10',
                'hint' =>       'Space to leave between images vertically'
            ),
            'max_height' =>                 array(
                'match' =>      'range|1,n',
                'default' =>    '100',
                'hint' =>       'Maximum width in pixels to make images'
            ),
            'max_width' =>                  array(
                'match' =>      'range|1,n',
                'default' =>    '100',
                'hint' =>       'Maximum height in pixels to make images'
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
            'show_background' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'show_border' =>                array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - places border around block containing title, image and caption '
            ),
            'show_image' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1 - remember a title and / or caption may still be shown'
            ),
            'show_image_border' =>          array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - places border around image'
            ),
            'show_caption' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - if shown admins can edit simply by clicking the content'
            ),
            'show_links' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'If any images have links, activate them'
            ),
            'show_title' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - if shown admins can edit simply by clicking the title'
            ),
            'show_tooltip' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - places Title for image as a tooltip'
            ),
            'show_uploader' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'Show uploader control for administrators'
            ),
            'title_height' =>               array(
                'match' =>      'range|0,n',
                'default' =>    '35',
                'hint' =>       'Height of title in pixels (if shown)'
            )
        );
    }

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        $this->_setup($instance, $args, $disable_params);
        $this->_do_submode();
        $count = $this->_Obj_JL->get_uploaded_count();
        if ($count) {
            $this->_msg = "<b>Success:</b> Uploaded ".$count." image".($count==1 ? '' : 's');
            $this->_Obj_JL->clear_status();
        }
        $this->_draw();
  //    y($this->_cp);die;
        return $this->_html;
    }

    protected function _add_image()
    {
        if ($this->_isAdmin) {
            $path = $this->_album_default_folder;
            mkdirs('.'.$path, 0777);
            $Obj_Uploader = new Uploader("Image", $path);
            $result = $Obj_Uploader->do_upload();
        } else {
            $result = array('status'=>'403', 'message'=>'Unauthorised');
        }
        switch ($result['status']){
            case '100':
              // In progress - do nothing
                break;
            case '200':
                $this->_gallery_image_add($result);
                break;
            default:
                header("HTTP/1.0 200 ".$result['message'], $result['status']);
                header('Content-type: text/plain');
                print "Error: ".$result['status']." ".$result['message']."\n";
                break;
        }
        die();
    }

    protected function _delete_image()
    {
        $targetID = get_var('targetID');
        $Obj_Gallery_Image = new Gallery_Image($targetID);
        $image = $Obj_Gallery_Image->get_field('thumbnail_small');
        $Obj_Gallery_Image->delete();
        unlink('.'.$image);
    }

    protected function _do_submode()
    {
        if (!$this->_isAdmin) {
            return false;
        }
        if ($this->_Obj_JL->isUploading()) {
            $this->_add_image();
            die();
        }
        if (get_var('source')==$this->_safe_ID) {
            $Obj = new Gallery_Image;
            switch (get_var('submode')) {
                case "gallery_album_sub_album":
                    $title =        get_var('targetValue');
                    $this->_add_sub_album();
                    global $page_vars;
                    header('Location: '.BASE_PATH.trim($page_vars['path'], '/'), 302);
                    die();
                break;
                default:
                    $this->_msg = $Obj->do_submode();
                    break;
            }
            $this->_setup_load();
        }
    }

    protected function _add_sub_album()
    {
        $Obj_Gallery_Album = new Gallery_Album;
        $parentID =     get_var('targetID');
        $date =         (System::has_feature('Posting-default-publish-now') ? get_timestamp() : '0000-00-00');
        $title =        get_var('targetValue');
        $name =         get_web_safe_ID($title);
        $data = array(
            'type' =>             $Obj_Gallery_Album->_get_type(),
            'date' =>             $date,
            'enabled' =>          1,
            'systemID' =>         SYS_ID,
            'permPUBLIC' =>       1,
            'permSYSLOGON' =>     1,
            'permSYSMEMBER' =>    1,
            'parentID' =>         $parentID,
            'title' =>            $title,
            'name' =>             $name
        );
        $ID = $Obj_Gallery_Album->insert($data);
        $Obj_Gallery_Album->_set_ID($ID);
        $path = "//".trim($Obj_Gallery_Album->get_path($ID, ''), '/');
        $Obj_Gallery_Album->set_field('path', $path);
    }

    protected function _draw()
    {
        $this->_draw_js();
        $this->_draw_css();
        $this->_html=      "<div class=\"".$this->_ident."\" id=\"".$this->_safe_ID."\">\n";
        $this->_draw_control_panel(true);
        $this->_draw_status();
        if ($this->_album_ID==false) {
            $this->_draw_invalid_album_message();
        } else {
            $this->_draw_admin_uploader();
            $this->_draw_image_thumbs();
        }
        $this->_html.=      "</div><div class='clr_b' style='overflow:hidden;height:0;width:0'>&nbsp;</div>\n";
    }

    protected function _draw_admin_uploader()
    {
        global $page_vars;
        if (!$this->_isAdmin || $this->_cp['show_uploader']!=1 || $this->_album_ID==false) {
            return;
        }
        $this->_Obj_JL->setup_code();
        Page::push_content('javascript', $this->_Obj_JL->get_js());
        $this->_html.=  $this->_Obj_JL->get_html()."<br class='clear' />";
    }

    protected function _draw_css()
    {
        $padding_h = ($this->_cp['image_padding_horizontal']);
        $padding_v = ($this->_cp['image_padding_vertical']);
        $spacing_h = ($this->_cp['image_spacing_horizontal']/2);
        $spacing_v = ($this->_cp['image_spacing_vertical']/2);
        $height =    ($this->_cp['max_height']);
        $width =     ($this->_cp['max_width']);
        $this->_css.=
             "#".$this->_safe_ID."_images div {\n"
            ."  padding:".$padding_v."px ".$padding_h."px; margin:".$spacing_v."px ".$spacing_h."px;\n"
            ."  width:". ($width) ."px;"
            ." height:"
            .(
                ($this->_cp['show_image'] ? $height : 0) +
                ($this->_cp['show_title'] ? 35 : 0) +
                ($this->_cp['show_caption'] ? 55 : 0)
             )."px;\n"
            .($this->_cp['show_background'] ?
                "  background-color: #".trim($this->_cp['color_background'], '#').";\n"
             :
                ""
             )
            .($this->_cp['show_border'] ?
                "  border: 1px solid #".trim($this->_cp['color_border'], '#').";\n"
             :
                ""
            )
            ."}\n"
            ."#".$this->_safe_ID."_images div table {\n"
            ."  width: 100%;\n"
            ."}\n"
            ."#".$this->_safe_ID."_images div table td.gi_title {\n"
            ."  height:".($this->_cp['title_height'])."px;\n"
            ."}\n"
            ."#".$this->_safe_ID."_images div table td.gi_title a {\n"
            ."  color: inherit; font-weight: bold; text-decoration:none;\n"
            ."}\n"
            ."#".$this->_safe_ID."_images div table td.gi_title a:hover {\n"
            ."  color: #0000ff; text-decoration:underline;\n"
            ."}\n"
            ."#".$this->_safe_ID."_images div table td.gi_image {\n"
            ."  width:".$width."px;\n"
            ."  height:".$height."px;\n"
            ."  margin: 0px;\n"
            ."}\n"
            .($this->_cp['show_image_border'] ?
                 "#".$this->_safe_ID."_images div table td.gi_image img{\n"
                ."  border: 1px solid #".trim($this->_cp['color_image_border'], '#').";\n"
                ."}\n"
                ."#".$this->_safe_ID."_images div table td.gi_image img:hover {\n"
                ."  border: 1px solid #".trim($this->_cp['color_image_border_over'], '#').";\n"
                ."}\n"
              :
                ""
             )
            ."#".$this->_safe_ID."_images div table td.gi_caption {\n"
            ."  height:".($this->_cp['caption_height'])."px;\n"
            ."}\n";
        Page::push_content('style', $this->_css);
    }

    protected function _draw_image_centered($image)
    {
        $content =  (trim($image['content']) == "&nbsp;" ? "" : $image['content']);
        $title =    (trim($image['title']) == "&nbsp;" ? "" : $image['title']);
        $height =   ($this->_cp['show_image_border'] ? $this->_cp['max_height']-2 : $this->_cp['max_height']);
        $width =    ($this->_cp['show_image_border'] ? $this->_cp['max_width']-2 : $this->_cp['max_width']);
        $src =
             BASE_PATH."img/resize"
            .$image['thumbnail_small']
            ."?width=".$width
            ."&amp;height=".$height
            .($image['thumbnail_cs_small'] ? "&amp;cs=".$image['thumbnail_cs_small'] : "");
        return
             "<table summary='Places image middle-center' cellpadding='0' cellspacing='0' border='0'>\n"
            .($this->_cp['show_title'] ?
                 "  <tr>\n"
                ."    <td class='gi_title'>\n"
                ."      <span class='".$this->_ident."_title' id='t_".$image['ID']."'"
                .($this->_isAdmin ?
                     " onclick=\"gallery_image_edit_click("
                    ."this,"
                    ."'".$this->_safe_ID."_params',"
                    ."'".$this->_cp['title_height']."',"
                    ."'".$this->_cp['caption_height']."'"
                    .")\""
                  :
                    ""
                 )
                .">\n"
                .(!$this->_isAdmin && $this->_cp['show_links'] && $image['URL'] ?
                     "<a"
                    ." href=\"".$image['URL']."\""
                    .($image['popup'] ? " rel=\"external\"" : "")
                    .">"
                  :
                    ""
                 )
                .($title ? htmlentities($title) : ($this->_isAdmin ? "Edit..." : ""))."\n"
                .(!$this->_isAdmin && $this->_cp['show_links'] && $image['URL'] ? "</a>" : "")
                ."      </span>"
                ."    </td>\n"
                ."  </tr>\n"
             :
                ""
            )
            .($this->_cp['show_image'] ?
                 "  <tr>\n"
                ."    <td class='gi_image'"
                .($this->_cp['show_tooltip'] ? " title=\"".htmlentities($title)."\"" : "")
                .">\n"
                .($this->_cp['show_links'] && $this->_cp['show_image'] &&  $image['URL'] ?
                    "<a"
                    ." href=\"".$image['URL']."\""
                    .($image['popup'] ? " rel=\"external\"" : "")
                    .">"
                  :
                    ""
                 )
                ."<img"
                ." src=\"".$src."\""
                ." alt=\"".htmlentities($title)."\"/>"
                .($this->_cp['show_links'] && $this->_cp['show_image'] && $image['URL'] ? "</a>" : "")
                ."</td>\n"
                ."  </tr>\n"
             :
                ""
            )
            .($this->_cp['show_caption'] ?
                 "  <tr>\n"
                ."    <td class='gi_caption'>\n"
                ."<span class='".$this->_ident."_content' id='c_".$image['ID']."'"
                .($this->_isAdmin ?
                     " onclick=\"gallery_image_edit_click("
                    ."this,"
                    ."'".$this->_safe_ID."_params',"
                    ."'".$this->_cp['title_height']."',"
                    ."'".$this->_cp['caption_height']."'"
                    .")\""
                  :
                    ""
                 )
                .">"
                .($content ? htmlentities($content) : ($this->_isAdmin ? "Edit..." : ""))
                ."</span>"
                ."</td>\n"
                ."  </tr>\n"
              :
                ""
             )
            ."</table>\n";
    }

    protected function _draw_image_thumbs()
    {
        $this->_html.=      "<div id=\"".$this->_safe_ID."_images\">\n";
        for ($i=0; $i<count($this->_images); $i++) {
            if ($this->_images[$i]['available'] || $this->_isAdmin) {
                $image = $this->_images[$i];
                if (substr($image['URL'], 0, 8)=='./?page=') {
                    $image['URL'] = BASE_PATH.substr($image['URL'], 8);
                }
                $this->_html.=
                "<div id=\"".$this->_safe_ID."_".$image['ID']."\""
                .(!$this->_images[$i]['available'] ?
                " class=\"gi_hidden\" title=\"This image would normally be hidden to you\""
                :
                ""
                )
                .">\n"
                .$this->_draw_image_centered($image)
                ."</div>";
            }
        }
        $this->_html.=      "</div>\n";
    }

    protected function _draw_invalid_album_message()
    {
        $this->_html.= "The gallery album ".$this->_cp['filter_container_path']." was not found.";
    }

    protected function _draw_js()
    {
        global $page_vars;
        if (!$this->_isAdmin) {
            return;
        }
        $js = "var ".$this->_safe_ID."_image_list = [\n";
        $js_arr = array();
        for ($i=0; $i<count($this->_images); $i++) {
            $image =  $this->_images[$i];
            if ($image['available'] || $this->_isAdmin) {
                $term = $this->_safe_ID."_images";
                $s_arr =  array("'","\r","\n");
                $r_arr =  array("\\\"","\\r","\\n");
                $content = str_replace($s_arr, $r_arr, $image['content']);
                $js_arr[]=
                     " {"
                    ."ID:".pad($image['ID'].",", 11)
                    ." category:\"".$image['category']."\","
                    ." content:\"".$content."\","
                    ." enabled:".$image['enabled'].","
                    ." parentID:".pad(($image['parentID'] ? $image['parentID'] : 0).",", 11)
                    ." subtype:\"".$image['subtype']."\","
                    ." title: \""
                    .str_replace(
                        array("'","\"","\r","\n"),
                        array("\'","&quot;","\\r","\\n"),
                        $image['title']
                    )
                    ."\","
                    ." parentTitle: \"".($image['parentID'] ? $image['parentTitle'] : '')."\""
                    ."}";
            }
        }
        $js.=
             implode(",\n", $js_arr)."\n];\n"
            ."var ".$this->_safe_ID."_params = {\n"
            ."  ident :  '".$this->_ident."',\n"
            ."  safeID : '".$this->_safe_ID."',\n"
            ."  url :    '"
            .BASE_PATH.trim($page_vars['path_real'], '/').'/'.trim($page_vars['path_extension'], '/')
            ."',\n"
            ."  images :  ".$this->_safe_ID."_image_list\n"
            ."}\n";
        Page::push_content('javascript', $js);
        if (count($this->_images)) {
            $js_onload =
                 "  gallery_album_sortable_setup(\"".$this->_safe_ID."\","
                ."\"".$this->_images[0]['parentID']."\","
                ."\"".BASE_PATH.trim($page_vars['path'], '/')."\");\n";
            Page::push_content('javascript_onload', $js_onload);
        }
    }

    protected function _draw_status()
    {
        $this->_html.=      HTML::draw_status($this->_safe_ID, $this->_msg);
    }

    protected function _gallery_image_add($result)
    {
        $Obj_Gallery_Image = new Gallery_Image;
        $path =     $result['path'];
        $path_arr = explode('/', $path);
        $file =     array_pop($path_arr);
        $file_arr = explode('.', $file);
        $tmp =      array_shift($file_arr);
        $name =     str_replace('_', '-', get_web_safe_ID($tmp));
        $title =    title_case_string(str_replace('_', ' ', $tmp));
        $data = array(
            'date' =>             get_timestamp(),
            'systemID' =>         SYS_ID,
            'type' =>             $Obj_Gallery_Image->_get_type(),
            'themeID' =>          1,
            'enabled' =>          1,
            'parentID' =>         $this->_album_ID,
            'permPUBLIC' =>       1,
            'permSYSLOGON' =>     1,
            'permSYSMEMBER' =>    1,
            'title' =>            $title,
            'name' =>             $name,
            'thumbnail_small' =>  $path
        );
        $ID = $Obj_Gallery_Image->insert($data);
        $Obj_Gallery_Image->_set_ID($ID);
        $Obj_Gallery_Image->set_container_path();
        $Obj_Gallery_Image->set_path();
        $Obj_Gallery_Image->sequence_append();
        do_log(1, __CLASS__.'::'.__FUNCTION__.'()', 'Added image', 'Result: '.print_r($result, 1));
        $_SESSION[$this->_safe_ID.'_results'][] = $result;
        return $ID;
    }

    protected function _get_max_height()
    {
        $this->_max_height = 0;
        foreach ($this->_images as $image) {
            $_filename =  '.'.trim($image['thumbnail_small'], '.');
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
                    $height =  $this->_cp['main_height'];
                    if ($height>$this->_max_height) {
                        $this->_max_height = $height;
                    }
                }
            }
        }
    }

    protected function _is_new($YYYYMMDD)
    {
        if ($this->_cp['show_new_for_x_days']==0) {
            return false;
        }
        sscanf($YYYYMMDD, "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
        $_date =  mktime(0, 0, 0, $_MM, $_DD, $_YYYY);
        return ($_date > time()-60*60*24*$this->_cp['show_new_for_x_days']);
    }

    protected function _setup($instance, $args, $disable_params)
    {
        parent::_setup($instance, $args, $disable_params);
        $this->_setup_load();
    }

    protected function _setup_load()
    {
        $this->_setup_load_permissions();
        $this->_setup_load_container();
        $this->_setup_load_images();
        $this->_Obj_JL = new Jumploader;
        $this->_Obj_JL->init($this->_safe_ID);
    }

    protected function _setup_load_container()
    {
        $Obj =    new Gallery_Album;
        if (!$this->_album_ID = $Obj->get_ID_by_path($this->_cp['filter_container_path'])) {
            return;
        }
        $Obj->_set_ID($this->_album_ID);
        $Obj->load();
        $this->_album_default_folder =  $Obj->record['enclosure_url'];
    }

    protected function _setup_load_images()
    {
        $Obj = new Gallery_Image;
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
                (isset($_REQUEST['offset']) ?                   $_REQUEST['offset'] : 0),
            'filter_personID' =>
                (isset($this->_cp['filter_personID']) ?         $this->_cp['filter_personID'] : ''),
            'results_limit' =>
                $this->_cp['results_limit'],
            'results_order' =>
                (isset($this->_cp['results_order']) ?           $this->_cp['results_order'] : 'date')
        );
        $results = $Obj->get_records_matching($args);
        $this->_images = $results['data'];
    }

    protected function _setup_load_permissions()
    {
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =       get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $this->_isAdmin =   ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    }

    public function get_version()
    {
        return VERSION_COMPONENT_GALLERY_THUMBNAILS;
    }
}
