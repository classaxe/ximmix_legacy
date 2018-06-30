<?php
define('VERSION_MEDIA_YOUTUBE','1.0.6');
/*
Version History:
  1.0.6 (2014-01-20)
    1) Now accepts optional fourth parameter to determine start time for clip

  (Older version history in class.media_youtube.txt)
*/
class Media_Youtube{
  protected $url;
  protected $width;
  protected $height;
  protected $start = 0;
  public function __construct($url="",$width=425,$height=350,$start=0) {
    $this->url = $url;
    $this->width = $width;
    $this->height = $height;
    if ($start){
      $this->start = hhmmss_to_seconds($start);
    }
  }

  public function draw_clip(){
    return
       "<a class=\"iframe\""
      ." href=\"".$this->url."?wmode=transparent&amp;rel=0".($this->start ? "&amp;start=".$this->start : "")."\""
      ." rel=\"frameborder=0|height=".$this->height."|scrolling=no|width=".$this->width."\""
      .">Embedded Content</a>";
  }

  public function get_version(){
    return VERSION_MEDIA_YOUTUBE;
  }
}
?>