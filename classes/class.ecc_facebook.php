<?php
define('VERSION_FACEBOOK','1.0.0');
/*
Version History:
  1.0.0 (2010-05-03)
    Initial release
  .
*/

class ECC_Facebook{
  private $Obj_System;
  private $_base_url =  "";
  private $_css =       "";
  private $_html =      "";

  function __construct(){
  }

  function handle_request(){
    include_once(SYS_FACEBOOK.'appinclude.php');
    $this->initialise();
    $this->rss_to_table("All recent news from");
    print $this->render();
    $this->initialise();
    $this->rss_to_table("Latest news stories from", 3);
    $markup = $this->render();
    $facebook->api_client->profile_setFBML($markup);
  }

  function initialise(){
    $this->_css = "";
    $this->_html = "";
    $this->Obj_System = new System(SYS_ID);
    $this->Obj_System->load();
    $this->_base_url =  trim($this->Obj_System->record['URL'],'/');
  }

  function get_rss(){
    switch(get_var('what')){
      case 'articles':
        $rss_url =  $this->_base_url."/rss/articles";
      break;
      case 'events':
        $rss_url =  $this->_base_url."/rss/events";
      break;
      case 'podcasts':
        $rss_url =  $this->_base_url."/rss/podcasts";
      break;
      case 'news':
        $rss_url =  $this->_base_url."/rss/news";
      break;
      default:
        $rss_url =  $this->_base_url."/rss/articles";
      break;
    }
    return file_get_contents($rss_url);
  }

  function get_title(){
    switch(get_var('what')){
      case 'articles':
        return 'Recent Articles';
      break;
      case 'events':
        return 'Upcoming Events';
      break;
      case 'podcasts':
        return 'Recent Podcasts';
      break;
      case 'news':
        return 'Latest News Items';
      break;
      default:
        return 'Recent Articles';
      break;
    }
  }

  function rss_to_table($title='', $limit=false) {
    $xml = $this->get_rss();
    $ObjXML = new SimpleXMLElement($xml);
    $ecc_channel = $ObjXML->channel->children('http://www.ecclesiact.com/help_rss_ns');
    $this->_css .=
       "  .clr { clear:both;height:0;width:0;overflow:hidden; }\n"
      ."  .ecc_feed { background-color: #f8f8f8; padding: 2px;}\n"
      ."  .ecc_feed .ecc_item { border: 1px solid #ccc; padding: 2px; margin: 5px; background-color: #fff;}\n"
      ."  .ecc_feed .ecc_item .ecc_title { font-weight: bold;}\n"
      ."  .ecc_feed .ecc_item .ecc_content { font-weight: normal;}\n"
      ."  .ecc_feed .ecc_item .ecc_content img { float:left; border:0; margin:5px 10px 5px 0; }\n"
      ."  .ecc_feed .ecc_poweredby { font-weight: bold; text-align: center; font-size: 80%;}\n";
    $this->_html.=
       "<div class='ecc_feed'>\n"
      ."<a href='".$this->_base_url."'>"
      ."<img src='http://www.ecclesiact.com/assets/ecclesiact_ws.gif' style='border:0' width='100%'>"
      ."</a>\n"
      ."<h1>"
      .$this->get_title()." from <a href='".$ObjXML->channel->link."'>".$ecc_channel->title."</a>"
      ."</h1>\n"
      ."<p>[ "
      ."<a href=\"http://apps.facebook.com/ecclesiact/?what=articles\">Articles</a> | "
      ."<a href=\"http://apps.facebook.com/ecclesiact/?what=events\">Events</a> | "
      ."<a href=\"http://apps.facebook.com/ecclesiact/?what=news\">News Items</a> | "
      ."<a href=\"http://apps.facebook.com/ecclesiact/?what=podcasts\">Podcasts</a>"
      ." ]</p>"
      ;
    if ($limit){
      $count = $limit;
    }
    foreach ($ObjXML->channel->item as $item) {
      $ecc_detail = $item->children('http://www.ecclesiact.com/help_rss_ns');
      $date =   date('M d, Y', strtotime($ecc_detail->date));
      $icon =   (string)$ecc_detail->icon;
      $this->_html.=
         "<div class='ecc_item'>\n"
        ."  <div class='ecc_title'>".$date." - "
        ."<a href='".$item->link."' target='_blank'>"
        .$ecc_detail->title
        ."</a></div>\n"
        ."  <div class='ecc_content'>"
        .($icon ? "<a href='".$item->link."' target='_blank'><img src=\"".$icon."\" /></a>" : "")
        .strip_tags((string)$ecc_detail->content)
        ."</div>\n"
        ."<div class='clr'>&nbsp;</div>\n"
        ."</div>";
      if ($limit) {
        $count--;
        if (!$count){
          break;
        }
      }
    }
    $this->_html.=
       "  <div class='ecc_poweredby'>\n"
      ."  Powered by <a href=\"".$ecc_channel->system_url."\">".$ecc_channel->system_name."</a>\n"
      ."  </div>\n"
      ."</div>\n";
  }

  function render(){
    return
      "<style type='text/css'>\n"
     .$this->_css
     ."</style>"
     .$this->_html;
  }

  public function get_version(){
    return VERSION_FACEBOOK;
  }
}
?>