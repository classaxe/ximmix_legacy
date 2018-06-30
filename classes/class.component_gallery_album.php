<?php
define ("VERSION_COMPONENT_GALLERY_ALBUM","1.0.71");
/*
Version History:
  1.0.71 (2014-04-03)
    1) Last build prevented valid configuration of NO root folder which caused some issues
       This build now correctly handles that circumstance

  (Older version history in class.component_gallery_album.txt)
*/

class Component_Gallery_Album extends Component_Base {
  private $_Obj_JL =                false;
  private $_albums =                false;
  private $_album_ID =              false;
  private $_album_default_folder =  false;
  private $_album_image =           false;
  private $_album_title =           false;
  private $_current_album =         false;
  private $_images =                false;
  private $_mode =                  '';
  private $_records;
  private $_structure;

  public function __construct(){
    $this->_ident =             "gallery_album";
    $this->_parameter_spec =    array(
      'caption_height' =>           array('match' => 'range|1,n',       'default' =>'60',               'hint'=>'Height for caption area under main image in album mode'),
      'caption_width' =>            array('match' => 'range|1,n',       'default' =>'500',              'hint'=>'Width for caption area under main image in album mode'),
      'folder_tree_speed' =>        array('match' => 'range|0,2',       'default' =>'0.25',             'hint'=>'Speed to expand / contract folders in seconds'),
      'folder_tree_height' =>       array('match' => 'range|100,n',     'default' =>'520',              'hint'=>'Maximum height of folder tree navigation area - will scroll if insufficient'),
      'folder_tree_width' =>        array('match' => 'range|100,n',     'default' =>'225',              'hint'=>'Maximum width of folder tree navigation area - will scroll if insufficient'),
      'filter_root_path' =>         array('match' => '',                'default' =>'',                 'hint'=>'Optionally limits items to those contained in this folder'),
      'path_prefix' =>              array('match' => '',                'default' =>'',                 'hint'=>'Optional prefix to add to all links - used when integrating in Community module'),
      'hover_size' =>               array('match' => 'range|1,n',       'default' =>'300',              'hint'=>'Maximum height or width in pixels to make hover image'),
      'indicated_root_folder' =>    array('match' => '',                'default' =>'',                 'hint'=>'Optionally simulates this folder as the container for all displayed albums'),
      'label_checkout_button' =>    array('match' => '',                'default' =>'Checkout',         'hint'=>'Label to place on \'Checkout\' button'),
      'label_empty_cart_button' =>  array('match' => '',                'default' =>'Empty Cart',       'hint'=>'Label to place on \'Empty Cart\' button'),
      'label_next_button' =>        array('match' => '',                'default' =>'Next',             'hint'=>'Label to place on \'Next\' button'),
      'label_prev_button' =>        array('match' => '',                'default' =>'Prev',             'hint'=>'Label to place on \'Previous\' button'),
      'label_slideshow_button' =>   array('match' => '',                'default' =>'Slideshow',        'hint'=>'Label to place on \'Slideshow\' button'),
      'show_album_date' =>          array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show date of album or not'),
      'show_captions' =>            array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show captions beneath main image or not'),
      'show_checkout' =>            array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show checkout button if items are in cart'),
      'show_empty_cart' =>          array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show empty cart button if items are in cart'),
      'show_folder_tree' =>         array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show folder tree or not'),
      'show_folder_icons' =>        array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show folders icons in the expander section or not'),
      'show_hover_image' =>         array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show hover image'),
      'show_image_counts' =>        array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show number of images in each album'),
      'show_new_for_x_days' =>      array('match' => 'range|0,n',       'default' =>'30',               'hint'=>'Show \'New\' beside albums less than x days old (0 for none)'),
      'show_slideshow' =>           array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show slideshow button'),
      'show_sort_options' =>        array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show sort options control'),
      'show_uploader' =>            array('match' => 'enum|0,1',        'default' =>'1',                'hint'=>'Show uploader control for administrators'),
      'show_watermark' =>           array('match' => 'enum|0,1',        'default' =>'0',                'hint'=>'Whether or not to watermark large images'),
      'skin_album_image' =>         array('match' => '',                'default' =>'/img/sysimg/album_128x96.png',        'hint'=>'Path for image to use to represent folders'),
      'skin_album_image_size' =>    array('match' => 'range|0,n',       'default' =>'76',               'hint'=>'Maximum size of thumbnail image'),
      'skin_album_image_x' =>       array('match' => 'range|0,n',       'default' =>'5',                'hint'=>'Horizontal offset in pixels to position thumbnail image'),
      'skin_album_image_y' =>       array('match' => 'range|0,n',       'default' =>'0',                'hint'=>'Vertical offset in pixels to position thumbnail image'),
      'skin_folder_image' =>        array('match' => '',                'default' =>'/img/sysimg/folder_128x96.png',        'hint'=>'Path for image to use to represent folders'),
      'skin_folder_image_size' =>   array('match' => 'range|0,n',       'default' =>'68',               'hint'=>'Maximum size of thumbnail image'),
      'skin_folder_image_x' =>      array('match' => 'range|0,n',       'default' =>'0',                'hint'=>'Horizontal offset in pixels to position thumbnail image'),
      'skin_folder_image_y' =>      array('match' => 'range|0,n',       'default' =>'6',                'hint'=>'Vertical offset in pixels to position thumbnail image'),
      'slideshow_height' =>         array('match' => 'range|1,n',       'default' =>'500',              'hint'=>'Maximum height in pixels to make slideshow image'),
      'slideshow_width' =>          array('match' => 'range|1,n',       'default' =>'660',              'hint'=>'Maximum width in pixels to make slideshow image'),
      'sort_by' =>                  array('match' => 'enum|date,name,title', 'default' =>'name',        'hint'=>'Sort by date (most recent first), name (a-z) or title (a-z)'),
      'text_no_such_root_album' =>  array('match' => '',                'default' =>'<p><b>Sorry!</b><br />The specified root album does not exist.</p>',         'hint'=>'Message to show when someone selects a non-existant album or image'),
      'text_no_such_resource' =>    array('match' => '',                'default' =>'<p><b>Sorry!</b><br />The specified resource does not exist.</p>',           'hint'=>'Message to show when the control is configured for a non-existant root album'),
      'total_width' =>              array('match' => 'range|100,n',     'default' =>'800',              'hint'=>'Total width of Gallery Album'),
      'thumb_size' =>               array('match' => 'range|1,n',       'default' =>'100',              'hint'=>'Maximum height or width in pixels to make thumbnail images'),
      'thumb_border' =>             array('match' => 'hex3|#c0c0c0',    'default' =>'#c0c0c0',          'hint'=>'Colour for thumbnail image borders'),
      'thumb_border_active' =>      array('match' => 'hex3|#ff0000',    'default' =>'#ff0000',          'hint'=>'Colour for thumbnail image borders if active'),
      'thumb_border_in_cart' =>     array('match' => 'hex3|#ff0000',    'default' =>'#ff0000',          'hint'=>'Colour for thumbnail image borders if in cart'),
      'uploaded_image_max' =>       array('match' => 'range|1,n',       'default' =>'1024',             'hint'=>'Maximum height or width in pixels of image when stored on server')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_html.=      "<div class=\"gallery_album\" id=\"".$this->_safe_ID."\">\n";
    $this->_draw_control_panel(true);
    $this->_draw_status_message();
    switch($this->_mode){
      case 'image':
        $this->_draw_image();
      break;
      default:
        $this->_draw_gallery();
      break;
    }
    $this->_html.=      "</div><div class='clear'>&nbsp;</div>\n";
    return $this->_html;
  }

  protected function _do_add_image(){
    if ($this->_isAdmin){
      $path = $this->_album_default_folder;
      mkdirs('.'.$path,0777);
      $Obj_Uploader = new Uploader("Image",$path);
      $result = $Obj_Uploader->do_upload();
    }
    else {
      $result = array('status'=>'403', 'message'=>'Unauthorised');
    }
    switch ($result['status']){
      case '100':
        // In progress - do nothing
      break;
      case '200':
        $this->_do_gallery_image_add($result);
      break;
      default:
        header("HTTP/1.0 200 ".$result['message'],$result['status']);
        header('Content-type: text/plain');
        print "Error: ".$result['status']." ".$result['message']."\n";
      break;
    }
  }

  protected function _do_add_sub_album(){
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
    $path = "//".trim($Obj_Gallery_Album->get_path($ID,''),'/');
    $Obj_Gallery_Album->set_field('path',$path,true);
    $default_base =     $Obj_Gallery_Album->_get_default_enclosure_base_folder();
    $default_folder =   $default_base.$ID.'/';
    $Obj_Gallery_Album->set_field('enclosure_url',$default_folder,true);

  }

  protected function _do_gallery_image_add($result){
    $Obj_Gallery_Album = new Gallery_Album($this->_album_ID);
    $Obj_Gallery_Album->load();
    $Obj_Gallery_Image = new Gallery_Image;
    $path =         $result['path'];
    $path_arr =     explode('/',$path);
    $file =         array_pop($path_arr);
    $file_arr =     explode('.',$file);
    $tmp =          array_shift($file_arr);
    $name =         str_replace('_','-',get_web_safe_ID($tmp));
    $title =        title_case_string(str_replace('_',' ',$tmp));
    $date =         (System::has_feature('Posting-default-publish-now') ? get_timestamp() : '0000-00-00');
    $data = array(
      'communityID' =>      $Obj_Gallery_Album->record['communityID'],
      'date' =>             $date,
      'enabled' =>          1,
      'memberID' =>         $Obj_Gallery_Album->record['memberID'],
      'name' =>             $name,
      'parentID' =>         $this->_album_ID,
      'permPUBLIC' =>       1,
      'permSYSLOGON' =>     1,
      'permSYSMEMBER' =>    1,
      'systemID' =>         SYS_ID,
      'themeID' =>          1,
      'thumbnail_small' =>  $path,
      'title' =>            $title,
      'type' =>             $Obj_Gallery_Image->_get_type()
    );
    $ID = $Obj_Gallery_Image->insert($data);
    $Obj_Gallery_Image->_set_ID($ID);
    $Obj_Gallery_Image->set_container_path();
    $Obj_Gallery_Image->set_path();
    $Obj_Gallery_Image->sequence_append();
    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Added image','Result: '.print_r($result,1));
    $max = $this->_cp['uploaded_image_max'];
    $size = Image_Factory::get_dimensions('.'.$result['path']);
    if ($size[0]>$max || $size[1]>$max){
      Image_Factory::resize_to_max('.'.$result['path'],$max);
      do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Resizing image to max '.$max,'Original size: '.$size[0].'x'.$size[1].' and '.$result['size'] .' for '.$result['path']);
    }
    $_SESSION[$this->_safe_ID.'_results'][] = $result;
    return $ID;
  }

  protected function _do_slideshow(){
    $out = array();
    $out['js'] =
       "obj_".$this->_safe_ID."_slideshow_viewer =\n"
      ."  new image_rotator(\n"
      ."    '".$this->_safe_ID."_slideshow_viewer',\n" // id
      ."    0,\n"                                      // image_idx
      ."    1,\n"                                      // controls_show
      ."    1,\n"                                      // count_show
      ."    2,\n"                                      // dwell_time
      ."    0.5,\n"                                    // fade_time
      ."    ".$this->_safe_ID."_image_list,\n"         // image_arr
      ."    ".($this->_isAdmin ? 1 : 0).",\n"          // isAdmin
      ."    'm',\n"                                    // controls_size
      ."    1,\n"                                      // caption_show
      ."    1,\n"                                      // title_show
      ."    '',\n"                                     // onchange
      ."    ".$this->_safe_ID."_sequence\n"            // sequence
      ."  );\n"
      ."obj_".$this->_safe_ID."_slideshow_viewer.do_setup();\n"
      ;
    $src =
       BASE_PATH."img/resize"
      .$this->_images[0]['thumbnail_small']
      ."?width=".$this->_cp['slideshow_width']
      ."&amp;height=".$this->_cp['slideshow_height']
      .($this->_images[0]['thumbnail_cs_small'] ? "&amp;cs=".$this->_images[0]['thumbnail_cs_small'] : '');
    $_ident = $this->_safe_ID."_slideshow_viewer";
    $out['html'] =
       "<div id=\"".$_ident."\">\n"
      ."  <div id=\"".$_ident."_controls\" style=\"opacity:0;filter:alpha(opacity=0);\"></div>\n"
      ."  <div title=\"Slideshow\" id=\"".$this->_safe_ID."_slideshow_viewer_mask\">\n"
      ."    <div id=\"".$_ident."_status\"></div>"
      ."    <div id=\"".$_ident."_overlay\"></div>"
      ."    <img id=\"".$_ident."_1\" src=\"".$src."\" alt=\"\" />\n"
      ."    <img id=\"".$_ident."_2\" src=\"/img/spacer\" alt=\"\" style=\"opacity:0;filter:alpha(opacity=0);\" />\n"
      ."    <div id=\"".$_ident."_content\" >\n"
      ."      <h2>".$this->_images[0]['title']."<span class='fr' style='font-weight:normal'>(1 of ".count($this->_images).")</span></h2>\n"
      .$this->_images[0]['content']."\n"
      ."    </div>\n"
      ."    <div id=\"".$this->_safe_ID."_slideshow_viewer_content_bg\">&nbsp;</div>\n"
      ."  </div>\n"
      ."</div>\n";
    $Obj_json =     new Services_JSON(SERVICES_JSON_LOOSE_TYPE); // so we get an assoc array as output instead of some weird object
    header('Content-Type: application/json');
    print $Obj_json->encode($out);
    die;
  }

  protected function _do_submode(){
    switch (get_var('submode')) {
      case "gallery_slideshow":
        $this->_do_slideshow();
        return;
      break;
    }
    if (!$this->_isAdmin){
      return false;
    }
    $this->_Obj_JL = new Jumploader;
    $this->_Obj_JL->init($this->_safe_ID);
    if ($this->_Obj_JL->isUploading()){
      $this->_do_add_image();
      die();
    }
    $count =                $this->_Obj_JL->get_uploaded_count();
    if ($count){
      $this->_msg.= "<b>Success:</b> Uploaded ".$count." image".($count==1 ? '' : 's');
      $this->_Obj_JL->clear_status();
    }
    switch (get_var('submode')) {
      case "gallery_album_sub_album":
        $title =            get_var('targetValue');
        $this->_do_add_sub_album();
        $this->_msg.=       "<b>Success:</b> New Gallery Album '".$title."' was added.";
        set_var('submode','');
      break;
      case "gallery_image_cover":
        if ($this->_cover_parents('Gallery_Image',get_var('targetID'),true)){
          $this->_msg.= "<b>Success:</b> the image is now featured on its parent album, and on all unadorned ancestors.";
        }
        set_var('submode','');
      break;
    }
    $Obj_Gallery_Image =    new Gallery_Image;
    $this->_msg.=           $Obj_Gallery_Image->do_submode();
    if ($this->_msg){
      $this->_setup_load_structure();
      $this->_setup_load_album_details();
    }
  }

  protected function _cover_parents($object_type,$ID,$force){
    $Obj = new $object_type($ID);
    if (!$Obj->load() || $Obj->record['systemID']!=SYS_ID){
      return false;
    }
    $parent_type =      $Obj->_get_container_object_type();
    $image =            $Obj->record['thumbnail_small'];
    $parentID =         $Obj->record['parentID'];
    $Obj_P =            new $parent_type($parentID);
    if ($force || $Obj_P->get_field('thumbnail_small')==''){
      $Obj_P->set_field('thumbnail_small',$image);
      $Obj_P->set_field('childID_featured',$ID);
      if ($parentID){
        $this->_cover_parents($parent_type,$parentID,false);
      }
    }
    return true;
  }

  protected function _draw_gallery(){
    global $page_vars;
    $this->_draw_gallery_js();
    $this->_draw_gallery_css();
    $this->_html.=
       "<div id=\"".$this->_safe_ID."_outer\">\n";
    if ($this->_cp['show_folder_tree']==1){
      $this->_html.=    "<div class=\"gallery_album_tree\" id=\"".$this->_safe_ID."_tree\">\n";
      $this->_draw_gallery_uploader();
      $this->_draw_gallery_folders();
      $this->_html.=    "</div>";
    }
    $this->_html.=      "<div class=\"gallery_album_main\" id=\"".$this->_safe_ID."_main\">\n";
    if (!$this->_cp['show_folder_tree']==1){
      $this->_draw_gallery_uploader();
    }
    $this->_draw_gallery_album_title_and_counts();
    if (trim($this->_current_album['password'])!==''){
      Password::do_commands(get_var('challenge_password'),$this->_current_album['password']);
      if (!$this->_isAdmin){
        if (!Password::check_csvlist_against_previous($this->_current_album['password'])){
          $out = Password::get_password_challenge_code(
            $this->_current_album['title'],
            'Album',
            $page_vars['path']
          );
          $this->_html.= $out['html'];
          Page::push_content('javascript',$out['javascript']);
          Page::push_content('javascript_onload_bottom',$out['javascript_onload_bottom']);
        }
        else {
          History::track();
          $this->_draw_gallery_contents();
        }
      }
      else {
        History::track();
        $this->_draw_gallery_contents();
      }
    }
    else {
      History::track();
      $this->_draw_gallery_contents();
    }
    $this->_html.=
       "</div>\n"
      ."</div>\n";
  }

  protected function _draw_gallery_contents(){
    $this->_draw_gallery_controls();
    $this->_draw_gallery_album_thumbs();
    $this->_html.=      "<div id=\"".$this->_safe_ID."_images\">\n";
    $this->_draw_gallery_image_thumbs();
    $this->_html.=      "</div>\n";
  }

  protected function _draw_gallery_css(){
    $t_size = (20+$this->_cp['thumb_size'])."px";
    $css =
       "#".$this->_safe_ID."                             { }\n"
      ."#".$this->_safe_ID."_outer                       { }\n"
      ."#".$this->_safe_ID."_tree                        { display: table-cell; float: left; width:".$this->_cp['folder_tree_width']."px; margin-right: 20px; }\n"
      ."#".$this->_safe_ID."_folders                     { overflow: auto; height:".$this->_cp['folder_tree_height']."px; }\n"
      ."#".$this->_safe_ID."_main                        { display: table-cell; }\n"
      ."#".$this->_safe_ID." .img                        { display:table-cell; text-align: center; width: ".$t_size."; height: ".$t_size."; vertical-align:middle; float: left; }\n"
      ."#".$this->_safe_ID." .img_hidden                 { opacity:0.4;filter:alpha(opacity=40); }\n"
      ."#".$this->_safe_ID." .gallery_album_tree         { }\n"
      ."#".$this->_safe_ID."_slideshow_viewer            { border-bottom:1px solid #888; padding:4px; position:relative; width:".($this->_cp['slideshow_width']+8)."px; height:".($this->_cp['slideshow_height'])."px;}\n"
      ."#".$this->_safe_ID."_slideshow_viewer_controls   { position:absolute; top:0px; right:8px; height:50px; width:225px; padding:0;margin:5px; z-index:2;}\n"
      ."#".$this->_safe_ID."_slideshow_viewer_mask       { margin:auto; z-index:1; position:relative;}\n"
      ."#".$this->_safe_ID."_slideshow_viewer_status     { position:absolute; top:0; width:".$this->_cp['slideshow_width']."px; height:".($this->_cp['slideshow_height'])."px; z-index: 2;}\n"
      ."#".$this->_safe_ID."_slideshow_viewer_overlay    { position:absolute; top:0; width:".$this->_cp['slideshow_width']."px; height:".($this->_cp['slideshow_height'])."px; z-index: 2;}\n"
      ."#".$this->_safe_ID."_slideshow_viewer_1          { position:absolute; z-index:1; }\n"
      ."#".$this->_safe_ID."_slideshow_viewer_2          { position:absolute; z-index:1; }\n"
      ."#".$this->_safe_ID."_slideshow_viewer_content h2 { color: #fff; margin: 0px; padding: 0px; font-size:150%; }\n"
      ."#".$this->_safe_ID."_slideshow_viewer_content    { position:absolute; width:".($this->_cp['slideshow_width']-20)."px; height:50px; left:0px; top:".($this->_cp['slideshow_height']-58)."px; z-index:2; padding:5px; color:#fff; }\n"
      ."#".$this->_safe_ID."_slideshow_viewer_content_bg { position:absolute; width:".($this->_cp['slideshow_width']-10)."px; height:50px; left:0px; top:".($this->_cp['slideshow_height']-60)."px; z-index:1; padding:5px; opacity:0.7; filter: alpha(opacity=70); background:#404040;}\n"
      ;
    Page::push_content('style',$css);
  }

  protected function _draw_gallery_album_cover($album){
    global $page_vars;
    $type =         (count($album['albums'])==0 ? 'album' : 'folder');
    $image_src =    BASE_PATH.trim($this->_cp['skin_'.$type.'_image'],'/');
    $image_size =   $this->_cp['skin_'.$type.'_image_size'];
    $image_x =      $this->_cp['skin_'.$type.'_image_x'];
    $image_y =      $this->_cp['skin_'.$type.'_image_y'];
    $num =
      ($this->_cp['show_image_counts']==1 && count($album['images']) ?
        "\n(".count($album['images'])." image"
        .(count($album['images'])==1 ? '' : 's').")"
       :
          ""
       );
    $this->_html.=
       "<div class='gallery_album_album'"
      ." onclick=\"document.location='".BASE_PATH.trim($page_vars['path'],'/').'/'.$album['name']."'\""
      .($this->_isAdmin?
          " onmouseover=\""
         ."if(!CM_visible('CM_gallery_album')) {"
         ."this.style.backgroundColor='#ffff80';"
         ."_CM.type='gallery_album';"
         ."_CM.ID='".$album['ID']."';_CM_text[0]='&quot;".str_replace("'",'',$album['title'])."&quot;';_CM.can_delete=".(count($album['images'])||count($album['albums']) ? 0 : 1)
         ."}\""
         ." onmouseout=\"this.style.backgroundColor='';_CM.type='';\""
       :
         ""
      )
      .">"
      .($this->_cp['show_album_date']==1 ?
         "<div class='gallery_album_album_date'>"
         .($this->_cp['show_album_date']==1 ? format_date($album['date']) : "")
         ."</div>"
         .($this->_get_is_new($album['date'])   ? " <div class='new' title='Published within the last ".$this->_cp['show_new_for_x_days']." days'>New</div>" : "")
        :
          ""
       )
      .(isset($album['password']) && $album['password']   ? " <div class='pwd'>[ICON]16 16 2611 Password Protected[/ICON]</div>" : "")
      ."    <div class='gallery_album_album_bg'"
      ." style='background:url(".$image_src.")'"
      .">"
      ."    <img style=\""
      .($image_x ? 'margin-left: '.$image_x.'px;' : '')
      .($image_y ? 'margin-top: '.$image_y.'px;' : '')
      .($album['thumbnail_small'] ?
         "background-image: url(".BASE_PATH."img/max/".$image_size."/".trim($album['thumbnail_small'],'/').")"
       :
         ""
       )
      ."\""
      ." src=\"".BASE_PATH."img/spacer\" alt=\"Album cover for ".htmlentities($album['title'])."\" />"
      ."    </div>"
      ."  <div class='gallery_album_album_title' title=\"".htmlentities($album['title']).$num."\">"
      ."<a href=\"".BASE_PATH.trim($page_vars['path'],'/').'/'.$album['name']."\" onclick=\"return false\">"
      .htmlentities(strlen($album['title'])>45 ? substr($album['title'],0,42)."..." : $album['title'])
      ."</a>\n"
      ."  </div>"
      ."</div>";
  }

  protected function _draw_gallery_album_thumbs(){
    if (!$this->_albums){
      return;
    }
    $this->_html.=
       "<div style='display:inline-block;width:100%;"
      .($this->_images ? "border-bottom: 1px solid #c0c0c0" : "")
      ."'>\n";
    for($i=0; $i<count($this->_albums); $i++){
      $this->_html.= $this->_draw_gallery_album_cover($this->_albums[$i]);
    }
    $this->_html.= "</div><br /><br />";
  }

  protected function _draw_gallery_album_title_and_counts(){
    if ($this->_cp['filter_root_path'] && !$this->_structure){
      $this->_html.= $this->_cp['text_no_such_root_album'];
      return;
    }
    if (!$this->_album_title){
      $this->_html.= $this->_cp['text_no_such_resource'];
      return;
    }
    $stats = array();
    if ($this->_images){
      $count=0;
      foreach($this->_images as $image){
        if ($image['available']){
          $count++;
        }
      }
      $stats[] =
         $count." image".($count==1 ? '' : 's')
        .($this->_isAdmin && count($this->_images)!=$count ?
           " - and ".(count($this->_images)-$count)." normally hidden for you"
         :
           ""
         );
    }
    $collections =    0;
    $albums =         0;
    if ($this->_albums){
      foreach($this->_albums as $album){
        if (count($album['albums'])){
          $collections++;
        }
        else {
          $albums++;
        }
      }
    }
    if ($collections){
      $stats[] = $collections." collection".($collections==1 ? '' : 's');
    }
    if ($albums){
      $stats[] = $albums." album".($albums==1 ? '' : 's');
    }
    $this->_html.=
       "<div class='gallery_album_main_title'>"
      ."<h1>Viewing ".$this->_album_title."</h1>"
      ."<h2>("
      .(count($stats) ?
          implode(' and ',$stats)
         .(($albums || $collections) && $this->_cp['sort_by']=='date' ? ', newest shown first' : '')
       :
          'Empty'
       )
      .")</h2>"
      ."</div>";
  }

  protected function _draw_gallery_controls(){
    global $page_vars;
    if (!$this->_images){
      return;
    }
    $this->_html.=
      "<div style='margin:10px;text-align:center'>"
      .($this->_cp['show_slideshow'] ?
           "<input type='button' value=\"".$this->_cp['label_slideshow_button']."\" onclick=\"gallery_album_slideshow_loader('".addslashes($this->_album_title)."','".BASE_PATH.trim($page_vars['path'],'/')."',".($this->_cp['slideshow_width']+10).",".$this->_cp['slideshow_height'].");\" />"
        : ""
       )
      .($this->_cp['show_checkout'] && Cart::has_items() ?
           "<input type='button' value=\"".$this->_cp['label_checkout_button']."\" onclick=\"document.location='".BASE_PATH."checkout'\" />"
        : ""
       )
      .($this->_cp['show_empty_cart'] && Cart::has_items() ?
           "<input type='button' value=\"".$this->_cp['label_empty_cart_button']."\" onclick=\"if (confirm('Empty your cart? \\nThis will remove ALL items')) { geid('command').value='empty_cart';geid('form').submit();}\" />"
        : ""
       )

      ."</div>";
  }

  protected function _draw_gallery_folders(){
    global $page_vars;
    if ($this->_cp['show_folder_tree']!=1){
      return;
    }
    $js =           "var ".$this->_safe_ID." = new list_folder_expander('".$this->_safe_ID."_expander',".($this->_cp['show_folder_icons'] ? '1' : '0').");";
    $js_onload =    "  ".$this->_safe_ID.".init(".$this->_cp['folder_tree_speed'].");\n";
    Page::push_content('javascript',$js);
    Page::push_content('javascript_onload',$js_onload);
    $path = BASE_PATH.trim($this->_cp['path_prefix'] ? $this->_cp['path_prefix'] : $page_vars['path_real'],'/');
    $this->_html.=
       "<div id='".$this->_safe_ID."_folders'>"
      ."<ul id='".$this->_safe_ID."_expander' class='list_folder_expander'>\n"
      .$this->_draw_gallery_folders_branch($this->_structure,$path)
      ."</ul>\n"
      ."</div>";
  }

  protected function _draw_gallery_folders_branch($branch,$parent_path){
    global $page_vars;
    if (!isset($branch['albums'])){
      return '';
    }
    $out = "";
    foreach ($branch['albums'] as $album){
      $images_available = $this->_get_images_available_in_album($album);
      $path = $parent_path.($album['name'] ? '/'.$album['name'] : '');
      $selected_album = substr($page_vars['path'],1,strlen($path))==$path;
      $out.=
         "<li>\n"
        ."<a"
          .($this->_isAdmin?
              " onmouseover=\""
             ."if(!CM_visible('CM_gallery_album')) {"
             ."this.style.backgroundColor='#ffff80';"
             ."_CM.type='gallery_album';"
             ."_CM.ID='".$album['ID']."';_CM_text[0]='&quot;".str_replace("'",'',$album['title'])."&quot;';_CM.can_delete=".(count($album['images'])||count($album['albums']) ? 0 : 1)
             ."}\""
             ." onmouseout=\"this.style.backgroundColor='';_CM.type='';\""
           :
             ""
          )
        ." href=\"".$path
        ."\">"
        .($selected_album ? "<b>" : "")
        .htmlentities($album['title'])
        .($this->_cp['show_image_counts']==1 && $images_available ? " (".$images_available.")" : "")
        .($selected_album ? "</b>" : "")
        ."</a>";
      if (count($album['albums'])){
        $out .= "<ul>".$this->_draw_gallery_folders_branch($album,$path)."</ul>\n";
      }
      $out.= "</li>\n";
    }
    return $out;
  }

  protected function _draw_gallery_image_thumbs(){
    global $page_vars;
    if (!$this->_images){
      return;
    }
    for($i=0; $i<count($this->_images); $i++){
      $img = $this->_images[$i];
      if ($img['available'] || $this->_isAdmin) {
        $URL = BASE_PATH.trim($page_vars['path'],'/').'/'.$img['name'];
        $src =
           BASE_PATH."img/max/".$this->_cp['thumb_size']."/".trim($img['thumbnail_small'],'/')
          .($img['thumbnail_cs_small'] ? "?cs=".$img['thumbnail_cs_small'] : '');
        $this->_html.=
           "  <div id=\"".$this->_safe_ID."_".$img['ID']."\""
          ." class=\"img".($img['available'] ? '' : ' img_hidden')."\""
          .($img['available'] ? "" : " title=\"This image would normally be hidden to you\"")
          ."><a href='".$URL."'"
          .($this->_cp['show_hover_image'] ?
                " onmouseover=\"return gallery_album_image_mouseover(this,".$this->_cp['hover_size'].");\""
               ." onmouseout=\"return gallery_album_image_mouseout();\""
            :
               ""
           )
          .">"
          ."<img"
          ." src=\"".$src."\""
          ." style='margin:5px;"
          .($img['cart'] ?
              "border:2px dashed ".$this->_cp['thumb_border_in_cart']
           :
              "border:2px solid ".$this->_cp['thumb_border']
           )
          ."'"
          ." alt='' title='Preview Image".($img['cart'] ? ' (in your cart)' : '')."' />"
          ."</a>\n"
          ."  </div>\n";
      }
    }
  }

  protected function _draw_gallery_js(){
    global $page_vars;
    if (!$this->_images){
      return;
    }
    $js_arr = array();
    $id_arr = array();
    foreach ($this->_images as $image){
      $id_arr[] = $image['ID'];
      $js_arr[] =
        " {"
        ."ID:".pad($image['ID'].",",11)
        ." parentID:".pad(($image['parentID'] ? $image['parentID'] : 0).",",11)
        ." category:\"".$image['category']."\","
        ." caption:\"".str_replace(array("'","\"","\r","\n"),array("\'","\\\"","\\r","\\n"),$image['content'])."\","
        ." enabled:".$image['enabled'].","
        ." image:\"".BASE_PATH."img/resize".$image['thumbnail_small']."?width=".$this->_cp['slideshow_width']."&height=".$this->_cp['slideshow_height'].($image['thumbnail_cs_small'] ? "&cs=".$image['thumbnail_cs_small'] : '')."\","
        ." image3:\"".BASE_PATH."img/"
        .($this->_cp['show_watermark'] ? 'wm' : 'resize')
        .$image['thumbnail_small']
        ."?max=".$this->_cp['hover_size']
        .($image['thumbnail_cs_small'] ? "&cs=".$image['thumbnail_cs_small'] : '')."\","
        ." parentTitle:\"".str_replace(array("'","\"","\r","\n"),array("\'","&quot;","\\r","\\n"),$image['parentTitle'])."\","
        ." subtype:\"".$image['subtype']."\","
        ." title:\"".str_replace(array("'","\"","\r","\n"),array("\'","&quot;","\\r","\\n"),$image['title'])."\","
        ." url:\"\","
        ." url_popup:0"
        ."}";
    }
    $js =
       "var ".$this->_safe_ID."_sequence =   [".implode(',',$id_arr)."];\n"
      ."var ".$this->_safe_ID."_current_id = ".$this->_images[0]['ID'].";\n"
      ."var ".$this->_safe_ID."_borders =    ['".$this->_cp['thumb_border']."','".$this->_cp['thumb_border_active']."'];\n"
      ."var ".$this->_safe_ID."_image_list = [\n"
      .implode(",\n",$js_arr)
      ."\n];\n"
      ;
    Page::push_content('javascript',$js);
    if ($this->_isAdmin){
      $js_onload =
         "  gallery_album_sortable_setup(\n"
        ."    \"".$this->_safe_ID."\",\n"
        ."    \"".$this->_images[0]['parentID']."\",\n"
        ."    \"".BASE_PATH.trim($page_vars['path'],'/')."\"\n"
        ."  );\n";
      Page::push_content('javascript_onload',$js_onload);
    }
  }

  protected function _draw_gallery_uploader(){
    global $page_vars;
    if (!$this->_isAdmin || $this->_cp['show_uploader']!=1 || $this->_album_ID==false){
      return;
    }
    $this->_Obj_JL->setup_code();
    Page::push_content('javascript',$this->_Obj_JL->get_js());
    $this->_html.=  $this->_Obj_JL->get_html();
  }

  protected function _draw_image(){
    $Obj_CGA_GI =           new Component_Gallery_Album_Gallery_Image($this->_ID);
    $parentID =             $Obj_CGA_GI->get_field('parentID');
    $Obj_Gallery_Album =    new Gallery_Album;
    $Obj_CGA_GI->_images =  $Obj_Gallery_Album->get_images($parentID);
    $this->_html.=          $Obj_CGA_GI->draw_detail();
  }

  protected function _draw_status_message(){
    $this->_html.=      HTML::draw_status($this->_safe_ID,$this->_msg);
  }

  protected function _get_images_available_in_album($album){
    $images_available = 0;
    if (count($album['images'])){
      foreach ($album['images'] as $image){
        if ($image['available']){
          $images_available++;
        }
      }
    }
    return $images_available;
  }

  protected function _get_is_new($YYYYMMDD){
    if ($this->_cp['show_new_for_x_days']==0){
      return false;
    }
    sscanf($YYYYMMDD, "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
    $_date =  mktime(0, 0, 0, $_MM, $_DD, $_YYYY);
    return ($_date > time()-60*60*24*$this->_cp['show_new_for_x_days']);
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_load_permissions();
    $this->_setup_load_mode();
    if ($this->_mode=='image'){
      return;
    }
    $this->_setup_load_structure();
    $this->_setup_load_album_details();
    $this->_do_submode();
    $this->_setup_load_cart_quantities();
    $this->_setup_remember_shop_page();
  }

  private function _setup_load_album_details(){
    global $page_vars;
    if ($page_vars['path']==$page_vars['path_real']){
      $this->_images =        $this->_structure['images'];
      $this->_albums =        $this->_structure['albums'];
      $this->_album_title =   $page_vars['title'];
      return;
    }
    $Obj_Gallery_Album =    new Gallery_Album;
    if ($page_vars['path']==$page_vars['path_real'].trim($this->_cp['indicated_root_folder'],'/')){
      $subpath = '';
    }
    else {
      $subpath =
         "//"
        .($this->_cp['filter_root_path'] ? trim($this->_cp['filter_root_path'],'/').'/' : '')
        .substr(
        $page_vars['path'],
        strlen(
           $page_vars['path_real']
          .($this->_cp['indicated_root_folder'] ? trim($this->_cp['indicated_root_folder'],'/').'/' : '')
        )
      );
    }
    if (!$ID = $Obj_Gallery_Album->get_ID_by_path($subpath)){
      header("Status: 404 Not Found",true,404); // Keep those pesky bots from following dead links!
      return;
    }
    $Obj_Gallery_Album->_set_ID($ID);
    $this->_current_album = $Obj_Gallery_Album->load();
    foreach ($this->_structure['albums'] as $node){
      if ($node['ID']==$ID){
        $this->_images =                $node['images'];
        $this->_albums =                $node['albums'];
        $this->_album_ID =              $node['ID'];
        $this->_album_default_folder =  $node['enclosure_url'];
        $this->_album_image =           $node['thumbnail_small'];
        $this->_album_title =           $node['title'];
        return;
      }
      if (count($node['albums'])){
        $this->_setup_load_album_details_branch($node,$ID);
      }
    }
  }

  private function _setup_load_album_details_branch($node,$ID){
    foreach ($node['albums'] as $album){
      if ($album['ID']==$ID){
        $this->_images =                $album['images'];
        $this->_albums =                $album['albums'];
        $this->_album_ID =              $album['ID'];
        $this->_album_default_folder =  $album['enclosure_url'];
        $this->_album_image =           $album['thumbnail_small'];
        $this->_album_title =           $album['title'];
        return;
      }
      if (count($album['albums'])){
        $this->_setup_load_album_details_branch($album,$ID);
      }
    }
  }

  private function _setup_load_cart_quantities(){
    if (!$this->_images){
      return;
    }
    foreach ($this->_images as &$image){
      $image['cart'] = 0;
    }
    if (!$items = Cart::get_items()){
      return;
    }
    $cart_images = array();
    foreach ($items as $item){
      switch($item['related_object']){
        case 'Gallery_Image':
        case 'Component_Gallery_Album_Gallery_Image':
          $cart_images[$item['related_objectID']] = $item['qty'];
        break;
      }
    }
    foreach ($this->_images as &$image){
      $image['cart'] = (isset($cart_images[$image['ID']]) ? $cart_images[$image['ID']] : 0);
    }
  }

  private function _setup_load_mode(){
    global $page_vars;
    $subpath =
       "//"
      .($this->_cp['filter_root_path'] ? trim($this->_cp['filter_root_path'],'/').'/' : '')
      .substr(
      $page_vars['path'],
       strlen(
         $page_vars['path_real']
        .($this->_cp['indicated_root_folder'] ? trim($this->_cp['indicated_root_folder'],'/').'/' : '')
      )
    );
    $Obj_Gallery_Image =    new Gallery_Image;
    if ($ID = $Obj_Gallery_Image->get_ID_by_path($subpath)){
      $this->_ID =      $ID;
      $this->_mode =    'image';
      $Obj_Gallery_Image->_set_ID($ID);
      $page_vars['proxied_object'] =    'Gallery_Image';
      $page_vars['proxied_record'] =    $Obj_Gallery_Image->get_record();
      return;
    }
    $this->_mode =      'gallery';
  }

  private function _setup_load_permissions(){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $this->_isAdmin =   ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
  }

  private function _setup_load_structure(){
    switch($this->_cp['sort_by']){
      case 'date':
        $sortBy =  '`date` DESC,`name` ASC';
      break;
      case 'name':
        $sortBy =  '`name` ASC';
      break;
      case 'title':
        $sortBy =  '`title` ASC';
      break;
    }
    $Obj_Gallery_Album =    new Gallery_Album;
    $path = '//'.($this->_cp['filter_root_path'] ? trim($this->_cp['filter_root_path'],'/').'/' : '');
    if ($path==='//'){
      $albumID = 0;
    }
    else if (!$albumID = $Obj_Gallery_Album->get_ID_by_path($path)){
      return;
    }
    $this->_structure = array(
      'albums' => array(),
      'images' => array()
    );
    if ($this->_cp['indicated_root_folder']===''){
      $this->_structure['albums'] = $Obj_Gallery_Album->get_albums($albumID,$sortBy);
      $this->_structure['images'] = $Obj_Gallery_Album->get_images($albumID);
      return;
    }
    $r = $Obj_Gallery_Album->load();
    $this->_structure['images'] = $Obj_Gallery_Album->get_images(0);
    $this->_structure['albums'][] = array(
      'ID' =>               $albumID,
      'enclosure_url' =>    $r['enclosure_url'],
      'password' =>         $r['password'],
      'parentID' =>         0,
      'content' =>          '',
      'title' =>            get_title_for_path(trim($this->_cp['indicated_root_folder'],'/')),
      'name' =>             '',
      'date' =>             '0000-00-00',
      'group_assign_csv' => '',
      'permPUBLIC' =>       1,
      'permSYSLOGON' =>     1,
      'permSYSMEMBER' =>    1,
      'thumbnail_small' =>  '',
      'albums' =>           $Obj_Gallery_Album->get_albums($albumID,$sortBy),
      'images' =>           $Obj_Gallery_Album->get_images($albumID)
    );
  }

  private function _setup_remember_shop_page(){
    global $page_vars;
    History::set('shop',BASE_PATH.trim($page_vars['path'],'/'));
  }

  public function get_version(){
    return VERSION_COMPONENT_GALLERY_ALBUM;
  }
}

?>