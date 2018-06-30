<?php
define('VERSION_GALLERY_ALBUM_GALLERY_IMAGE','1.0.3');
/*
Version History:
  1.0.3 (2013-11-08)
    1) Now includes 'Up' button
  1.0.2 (2012-09-19)
    1) Removed localised version of BL_title_linked() -
       parent does a better job of this
  1.0.1 (2011-09-27)
    1) Added Component_Gallery_Album_Gallery_Image::BL_prev_next_buttons()
  1.0.0 (2011-09-19)
    1) Initial release
*/
class Component_Gallery_Album_Gallery_Image extends Gallery_Image {

  protected function BL_prev_next_buttons(){
    global $page_vars;
    $count =    count($this->_images);
//    y($this->_images);
    $current =  0;
    for ($i=0; $i<$count; $i++){
      if ($this->_images[$i]['ID'] == $this->_get_ID()){
        $current = $i;
        break;
      }
    }
    $path_arr = explode('/',$page_vars['path']);
    array_pop($path_arr);
    $path =     BASE_PATH.trim(implode('/',$path_arr),'/').'/';
    $prev = $path.$this->_images[($current>0 ? $current-1 : $count-1)]['name'];
    $next = $path.$this->_images[($current+1<$count ? $current+1 : 0)]['name'];
    return
       "Showing ".($current+1)." of ".$count."<br />"
      ."<input type='button' onclick=\"document.location='".$prev."'\" value=\"Previous\" />"
      ."<input type='button' onclick=\"document.location='".$path."'\" value=\"Up\" />"
      ."<input type='button' onclick=\"document.location='".$next."'\" value=\"Next\" />";
  }

  public function get_version(){
    return VERSION_GALLERY_ALBUM_GALLERY_IMAGE;
  }
}
?>