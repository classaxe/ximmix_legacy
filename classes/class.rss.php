<?php
define('VERSION_RSS', '1.0.29');
/*
Version History:
  1.0.29 (2015-01-31)
    1) Changes to RSS::_serve_get_records() to rename internal arguments for getting records:
         Old: limit,         order_by
         New: results_limit, results_order
    2) Changes to RSS::_serve_setup() to rename internal arguments for getting records:
         Old: limit
         New: results_limit
    3) Moved RSS_Help into its own class file
    4) Now PSR-2 Compliant

  (Older version history in class.rss.txt)
*/
class RSS extends Record
{
    public $url;

    public function __construct($url = '')
    {
        $this->url = $url;
    }

    public function do_rss()
    {
        RSS::serve();
    }

    public function get_items()
    {
  //    print($this->url);die;
        $xml_doc = $this->get_remote_xml_file($this->url);
  //    print $xml_doc; die;
        $xml = new SimpleXMLElement($xml_doc);
        $out = array();
        foreach ($xml->channel->item as $item) {
            $ns_attributes = $item->children('http://www.ecclesiact.com/help_rss_ns');
            $enclosure_url =  "";
            $enclosure_secs = "";
            $enclosure_size =  "";
            if (isset($item->enclosure)) {
                $attributes = $item->enclosure->attributes();
                if (isset($attributes->url)) {
                    $enclosure_url =  (string)$attributes->url;
                }
                if (isset($attributes->length)) {
                    $enclosure_size = (string)$attributes->length;
                }
                if (isset($attributes->duration)) {
                    $enclosure_secs = (string)$attributes->duration;
                }
            }
            $out[] = array(
                'canRegister' =>
                    (isset($ns_attributes->canRegister) ? (string)$ns_attributes->canRegister : ""),
                'category' =>
                    (isset($item->category) ? (string)$item->category : ""),
                'content' => (isset($ns_attributes->content) ?
                    (string)$ns_attributes->content
                 :
                    (string)$ns_attributes->summary
                ),
                'date' =>
                    (isset($ns_attributes->date) ?  (string)$ns_attributes->date : ""),
                'effective_date_end' =>
                    (isset($ns_attributes->effective_date_end) ?     (string)$ns_attributes->effective_date_end : ""),
                'effective_date_start' =>
                    (isset($ns_attributes->effective_date_start) ?  (string)$ns_attributes->effective_date_start : ""),
                'effective_time_end' =>
                    (isset($ns_attributes->effective_time_end) ?    (string)$ns_attributes->effective_time_end : ""),
                'effective_time_start' =>
                    (isset($ns_attributes->effective_time_start) ?  (string)$ns_attributes->effective_time_start : ""),
                'enclosure_secs' =>
                    $enclosure_secs,
                'enclosure_size' =>
                    $enclosure_size,
                'enclosure_url' =>
                    $enclosure_url,
                'ID' =>
                    (isset($ns_attributes->ID) ?            (string)$ns_attributes->ID : ""),
                'icon' =>
                    (isset($ns_attributes->icon) ?          (string)$ns_attributes->icon : ""),
                'location' =>
                    (isset($ns_attributes->location) ?      (string)$ns_attributes->location : ""),
                'map_lat' =>
                    (isset($ns_attributes->map_lat) ?       (string)$ns_attributes->map_lat : ""),
                'map_lon' =>
                    (isset($ns_attributes->map_lon) ?       (string)$ns_attributes->map_lon : ""),
                'popup' =>
                    (isset($ns_attributes->popup) ?         (string)$ns_attributes->popup : ""),
                'systemID' =>
                    (isset($ns_attributes->systemID) ?      (string)$ns_attributes->systemID : ""),
                'systemTitle' =>
                    (isset($ns_attributes->systemTitle) ?   (string)$ns_attributes->systemTitle : ""),
                'systemURL' =>
                    (isset($ns_attributes->systemURL) ?     (string)$ns_attributes->systemURL : ""),
                'title' =>
                    (isset($ns_attributes->title) ?         (string)$ns_attributes->title : ""),
                'URL' =>
                    (isset($item->link) ?                   (string)$item->link : "")
            );
        }
        return $out;
    }

    public function serve($args = false)
    {
        $this->_serve_setup($args);
        $this->_serve_get_records();
        $this->_serve_set_title();
        $this->_serve_set_namespace();
        $this->_serve_open_channel();
        $this->_serve_data();
        $this->_serve_close_channel();
        $this->_serve_render();
    }

    private function _serve_get_records()
    {
        switch ($this->args['submode']) {
            case "articles":
                $results = $this->Obj->get_records(
                    array(
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'isShared' =>       (isset($this->args['isShared']) ? $this->args['isShared'] : 0),
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'results_limit' =>  $this->args['results_limit']
                    )
                );
                $this->records = $results['data'];
                break;
            case "shared_articles":
                $results = $this->Obj->get_records(
                    array(
                        'byRemote' =>       true,
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'results_limit' =>  $this->args['results_limit']
                    )
                );
                $this->records = $results['data'];
                break;
            case "config":
                $this->records = $this->Obj->get_config();
                break;
            case "events":
                $results = $this->Obj->get_records(
                    array(
                        'byRemote' =>       (isset($this->args['byRemote']) ? $this->args['byRemote'] : 0),
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'isShared' =>       (isset($this->args['isShared']) ? $this->args['isShared'] : 0),
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'results_limit' =>  $this->args['results_limit'],
                        'what' =>           $this->args['what']
                    )
                );
                $this->records = $results['data'];
                break;
            case "shared_events":
                $results = $this->Obj->get_records(
                    array(
                        'byRemote' =>       true,
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'DD' =>             $this->args['DD'],
                        'memberID' =>       $this->args['memberID'],
                        'MM' =>             $this->args['MM'],
                        'offset' =>         $this->args['offset'],
                        'personID' =>       $this->args['personID'],
                        'results_limit' =>  $this->args['results_limit'],
                        'results_order' =>  'date',
                        'what' =>           $this->args['what'],
                        'YYYY' =>           $this->args['YYYY']
                    )
                );
                $this->records = $results['data'];
                break;
            case "gallery_images":
                $results = $this->Obj->get_records(
                    array(
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'container_path' => $this->args['container_path'],
                        'container_subs' => $this->args['container_subs'],
                        'isShared' =>       (isset($this->args['isShared']) ? $this->args['isShared'] : 0),
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'personID' =>       $this->args['personID'],
                        'results_limit' =>  $this->args['results_limit'],
                        'results_order' =>  'date'
                    )
                );
                $this->records = $results['data'];
                break;
            case "shared_gallery_images":
                $results = $this->Obj->get_records(
                    array(
                        'byRemote' =>       true,
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'container_path' => $this->args['container_path'],
                        'container_subs' => $this->args['container_subs'],
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'personID' =>       $this->args['personID'],
                        'results_limit' =>  $this->args['results_limit'],
                        'results_order' =>  'date'
                    )
                );
                $this->records = $results['data'];
                break;
            case "jobs":
                $results = $this->Obj->get_records(
                    array(
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'isShared' =>       (isset($this->args['isShared']) ? $this->args['isShared'] : 0),
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'results_limit' =>  $this->args['results_limit']
                    )
                );
                $this->records = $results['data'];
                break;
            case "shared_jobs":
                $results = $this->Obj->get_records(
                    array(
                        'byRemote' =>       true,
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'results_limit' =>  $this->args['results_limit']
                    )
                );
                $this->records = $results['data'];
                break;
            case "news":
                $results = $this->Obj->get_records(
                    array(
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'isShared' =>       (isset($this->args['isShared']) ? $this->args['isShared'] : 0),
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'results_limit' =>  $this->args['results_limit']
                    )
                );
                $this->records = $results['data'];
                break;
            case "shared_news":
                $results = $this->Obj->get_records(
                    array(
                        'byRemote' =>       true,
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'results_limit' =>  $this->args['results_limit']
                    )
                );
                $this->records = $results['data'];
                break;
            case "podcasts":
                $results = $this->Obj->get_records(
                    array(
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'container_path' => $this->args['container_path'],
                        'container_subs' => $this->args['container_subs'],
                        'isShared' =>       (isset($this->args['isShared']) ? $this->args['isShared'] : 0),
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'personID' =>       $this->args['personID'],
                        'results_limit' =>  $this->args['results_limit'],
                        'results_order' =>  'date'
                    )
                );
                $this->records = $results['data'];
                break;
            case "shared_podcasts":
                $results = $this->Obj->get_records(
                    array(
                        'byRemote' =>       true,
                        'category' =>       $this->args['category'],
                        'communityID' =>    $this->args['communityID'],
                        'container_path' => $this->args['container_path'],
                        'container_subs' => $this->args['container_subs'],
                        'memberID' =>       $this->args['memberID'],
                        'offset' =>         $this->args['offset'],
                        'personID' =>       $this->args['personID'],
                        'results_limit' =>  $this->args['results_limit'],
                        'results_order' =>  'date'
                    )
                );
                $this->records = $results['data'];
                break;
            case "product_parent_itemcodes":
                $this->records =    $this->Obj->get_parent_itemcodes();
                break;
            default:
                global $system_vars;
                $help =
                     "<b>".$this->args['feed_title']."</b>\n"
                    ."<i>The following RSS feed <b>modes</b> are available:</i>"
                    ."\n"
                    .(System::has_feature('Articles')   ?
                        "* <a href=\"".$this->args['base_path']."articles\"><b>Articles</b></a><br />\n"
                      :
                        ""
                     )
                    .(System::has_feature('Events')   ?
                        "* <a href=\"".$this->args['base_path']."events\"><b>Events</b></a><br />\n"
                      :
                        ""
                     )
                    .(System::has_feature('Gallery-Images')   ?
                        "* <a href=\"".$this->args['base_path']."gallery_images\"><b>Gallery Images</b></a><br />\n"
                      :
                        ""
                     )
                    .(System::has_feature('Jobs')   ?
                        "* <a href=\"".$this->args['base_path']."jobs\"><b>Jobs</b></a><br />\n"
                      :
                        ""
                     )
                    .(System::has_feature('News')   ?
                        "* <a href=\"".$this->args['base_path']."news\"><b>News</b></a><br />\n"
                      :
                        ""
                     )
                    .(System::has_feature('Podcasting')   ?
                        "* <a href=\"".$this->args['base_path']."podcasts\"><b>Podcasts</b></a><br />\n"
                      :
                        ""
                     )
                    ."\n<b>Syntax:</b><br />\n"
                    .$this->args['base_path']
                    ."<b>mode</b>/category/<b>category-value</b>/limit/<b>limit-value</b>";
                $this->records = array(
                    array(
                        'date' =>       get_timestamp(),
                        'content' =>    $help
                    )
                );
                break;
        }
    }

    private function _serve_data()
    {
        global $system_vars;
        foreach ($this->records as $r) {
            $ID =
                (isset($r['ID']) ?                      $r['ID'] : "");
            $canRegister =
                (isset($r['canRegister']) &&            $r['canRegister'] ? "1" : "0");
            $category =
                (isset($r['category']) ?                sanitize('html', $r['category']) : "");
            $content =
                (isset($r['content']) ?                 sanitize('rss', $r['content']) : "");
            $date =
                (isset($r['date']) ?                    $r['date'] : "");
            $effective_date_end =
                (isset($r['effective_date_end']) ?      $r['effective_date_end'] : "");
            $effective_date_start =
                (isset($r['effective_date_start']) ?    $r['effective_date_start'] : "");
            $effective_time_end =
                (isset($r['effective_time_end']) ?      $r['effective_time_end'] : "");
            $effective_time_start =
                (isset($r['effective_time_start']) ?    $r['effective_time_start'] : "");
            $enclosure_secs =
                (isset($r['enclosure_secs']) ?          $r['enclosure_secs'] : "");
            $enclosure_size =
                (isset($r['enclosure_size']) ?          $r['enclosure_size'] : "");
            $enclosure_type =
                (isset($r['enclosure_type']) ?          $r['enclosure_type'] : "");
            $enclosure_url =
                (isset($r['enclosure_url']) ?           $r['enclosure_url'] : "");
            $history_modified_date =
                (isset($r['history_modified_date']) ?   $r['history_modified_date'] : "0000-00-00 00:00:00");
            $history_created_date =
                (isset($r['history_created_date']) ?    $r['history_created_date'] : "0000-00-00 00:00:00");
            $icon =
                (isset($r['thumbnail_small']) ?         sanitize('rss', $r['thumbnail_small']) : "");
            $location =
                (isset($r['location']) ?                $r['location'] : "");
            $map_lat =
                (isset($r['map_lat']) ?                 $r['map_lat'] : "");
            $map_lon =
                (isset($r['map_lon']) ?                 $r['map_lon'] : "");
            $systemID =
                (isset($r['systemID']) ?                $r['systemID'] : "");
            $systemTitle =
                (isset($r['systemTitle']) ?             $r['systemTitle'] : "");
            $systemURL =
                (isset($r['systemURL']) ?               $r['systemURL'] : "");
            $time_end =
                (isset($r['time_end']) ?                $r['time_end'] : "");
            $time_start =
                (isset($r['time_start']) ?              $r['time_start'] : "");
            $title =
                (isset($r['title']) ?                   sanitize('rss', $r['title']) : "");
            if (isset($r['systemID'])) {
                $URL = $this->Obj->get_URL($r);
            } else {
                $URL = (isset($r['URL']) ? sanitize('html', $r['URL']) : "");
                if (substr($URL, 0, 2)=="./") {
                    $URL = substr($URL, 2);
                }
            }
            if (substr($URL, 0, 4)!="http") {
                $URL = trim($systemURL, '/').'/'.trim($URL, '/');
            }
            if ($icon && substr($icon, 0, 4)!="http") {
                $icon = trim($systemURL, '/').'/img/width/80/?img='.trim($icon, '/');
            }
            $content = absolute_path($content, trim($systemURL, '/').'/');
            if ($effective_date_start != "0000-00-00 00:00:00") {
                sscanf($effective_date_start, "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
                $_YYYY =  ($_YYYY == "0000" ? $this->args['YYYY'] : $_YYYY);
                $effective_date_start =    adodb_mktime(0, 0, 0, $_MM, $_DD, $_YYYY);
            } else {
                $effective_date_start = "";
            }
            if ($effective_date_end != "0000-00-00 00:00:00") {
                sscanf($effective_date_end, "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
                $_YYYY =  ($_YYYY == "0000" ? $this->args['YYYY'] : $_YYYY);
                $effective_date_end =    adodb_mktime(0, 0, 0, $_MM, $_DD, $_YYYY);
            } else {
                $effective_date_end = "";
            }
            if ($this->Obj && $this->Obj->_get_object_name()=="Event") {
                $date = $r['effective_date_start'].' '.$r['effective_time_start'];
                $content =
                     "<p>"
                    ."Date: ".date($system_vars['defaultDateFormat'], $effective_date_start)."<br />\n"
                    ."Starts: ".$effective_time_start."<br />\n"
                    ."Ends: ".$effective_time_end."</p>\n"
                    .$content;
            }
            if ($date != "0000-00-00 00:00:00") {
                sscanf($date, "%04d-%02d-%02d %02d:%02d:%02d", $_YYYY, $_MM, $_DD, $_hh, $_mm, $_ss);
                $date =    mktime($_hh, $_mm, $_ss, $_MM, $_DD, $_YYYY);
            } else {
                $date = "";
            }
            $pubdate = $date;
            $this->_xml.=
                 "  <item>\r\n"
                ."    <title><![CDATA["
                .($this->Obj && $this->Obj->_get_object_name()=="Event" && $effective_date_start!="" ?
                    adodb_date('D, d M Y', $effective_date_start)." : "
                  :
                    ""
                 )
                 .$title."]]></title>\r\n"
                ."    <description><![CDATA[".$content."]]></description>\r\n"
                ."    <link><![CDATA[".$URL."]]></link>\r\n"
                ."    <category><![CDATA["
                .($category ? $category : '(None)')
                ."]]></category>\r\n"
                .($category!='checksum' && $category!='config' ?
                    "    <guid isPermaLink=\"false\">".$ID."</guid>\r\n"
                  :
                    ""
                 )
                .($category=='checksum' || $category=='config' ?
                    "    <guid isPermaLink=\"false\">".$title.":".$content."</guid>\r\n"
                  :
                    ""
                 )
                .($pubdate!="" ?
                    "    <pubDate>".date(DATE_RSS, $pubdate)."</pubDate>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Podcast" && $enclosure_url ?
                     "    <enclosure url=\"".trim($systemURL, '/').'/'.trim($enclosure_url, '/')."\""
                    ." length=\"".$enclosure_size."\" type=\"".$enclosure_type."\"/>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Podcast" && $enclosure_secs ?
                    "    <itunes:duration>".$enclosure_secs."</itunes:duration>\r\n"
                  :
                    ""
                 )
                .($systemTitle!="" ?
                    "    <ecc_detail:systemTitle><![CDATA[".$systemTitle."]]></ecc_detail:systemTitle>\r\n"
                  :
                    ""
                 )
                .($systemURL!="" ?
                    "    <ecc_detail:systemURL><![CDATA[".$systemURL."]]></ecc_detail:systemURL>\r\n"
                  :
                    ""
                 )
                .($systemID!="" ?
                    "    <ecc_detail:systemID>".$systemID."</ecc_detail:systemID>\r\n"
                  :
                    ""
                 )
                .($ID!="" ?
                    "    <ecc_detail:ID>".$ID."</ecc_detail:ID>\r\n"
                  :
                    ""
                 )
                .($canRegister!="" ?
                    "    <ecc_detail:canRegister>".$canRegister."</ecc_detail:canRegister>\r\n"
                  :
                    ""
                 )
                .($date!="" ?
                    "    <ecc_detail:date>".adodb_date('Y-m-d', $date)."</ecc_detail:date>\r\n"
                  :
                    ""
                 )
                .($icon!="" ?
                    "    <ecc_detail:icon><![CDATA[".$icon."]]></ecc_detail:icon>\r\n"
                  :
                    ""
                 )
                .($location!="" ?
                    "    <ecc_detail:location><![CDATA[".$location."]]></ecc_detail:location>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Event" ?
                    "    <ecc_detail:map_lat>".$map_lat."</ecc_detail:map_lat>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Event" ?
                    "    <ecc_detail:map_lon>".$map_lon."</ecc_detail:map_lon>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Event" ?
                     "    <ecc_detail:effective_date_start>"
                    .$effective_date_start
                    ."</ecc_detail:effective_date_start>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Event" ?
                     "    <ecc_detail:effective_date_end>"
                    .$effective_date_end
                    ."</ecc_detail:effective_date_end>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Event" ?
                     "    <ecc_detail:effective_time_start>"
                    .$effective_time_start
                    ."</ecc_detail:effective_time_start>\r\n"
                  :
                    ""
                 )
                .($this->Obj && $this->Obj->_get_object_name()=="Event" ?
                     "    <ecc_detail:effective_time_end>"
                    .$effective_time_end
                    ."</ecc_detail:effective_time_end>\r\n"
                  :
                    ""
                 )
                ."    <ecc_detail:title><![CDATA[".$title."]]></ecc_detail:title>\r\n"
                ."    <ecc_detail:content><![CDATA[".$content."]]></ecc_detail:content>\r\n"
                ."  </item>\r\n";
        }
    }

    private function _serve_open_channel()
    {
        global $system_vars;
        switch (strToLower(System::get_item_version('system_family'))) {
            case "ximmix":
                $system_family_url =    "http://www.auroraonline.com";
                $system_family_name =   "Ximmix Web Portal";
                break;
            default:
                $system_family_url =    "http://www.ecclesiact.com";
                $system_family_name =   "Ecclesiact Web System for Churches";
                break;
        }
        $this->_xml=
             "<?xml version=\"1.0\" encoding=\"ISO-8859-1\" ?".">\r\n"
            ."<!--\r\n"
            ."\tIf you are reading this, you probably clicked on the link\r\n"
            ."\tfor an RSS feed from ".$system_vars['textEnglish'].".\r\n\r\n"
            ."\tInstead you should paste the address from the link you\r\n"
            ."\tclicked on into your media player or RSS reader software.\r\n"
            ."-->\r\n"
            ."<rss version=\"2.0\" ".$this->_namespace.">\r\n"
            ."<channel>\r\n"
            ."  <title><![CDATA[".$this->title."]]></title>\r\n"
            ."  <atom:link href=\""
            .htmlentities(trim($system_vars['URL'], "/").$_SERVER["REQUEST_URI"])
            ."\" rel=\"self\" type=\"application/rss+xml\" />\r\n"
            ."  <language>en</language>\r\n"
            .($this->Obj && $this->Obj->_get_object_name()=="Podcast" ?
                 "  <itunes:explicit>No</itunes:explicit>\r\n"
                ."  <itunes:category text=\"Religion &amp; Spirituality\">\r\n"
                ."    <itunes:category text=\"Christianity\" />\r\n"
                ."  </itunes:category>\r\n"
                ."  <itunes:owner>\r\n"
                ."    <itunes:name>".$system_vars['adminName']."</itunes:name>\r\n"
                ."    <itunes:email>".$system_vars['adminEmail']."</itunes:email>\r\n"
                ."  </itunes:owner>\r\n"
              :
                ""
             )
            ."  <link><![CDATA[".trim($system_vars['URL'], "/").$_SERVER["REQUEST_URI"]."]]></link>\r\n"
            ."  <description><![CDATA[RSS Feed for ".$system_vars['textEnglish']."]]></description>\r\n"
            ."  <copyright><![CDATA[Copyright: (C) ".$system_vars['textEnglish'].", see "
            .$system_vars['URL']." for terms and conditions of reuse]]></copyright>\r\n"
            ."  <ttl>60</ttl>\r\n"
            ."  <ecc_channel:title><![CDATA[".$system_vars['textEnglish']."]]></ecc_channel:title>\r\n"
            ."  <ecc_channel:system_name><![CDATA[".$system_family_name."]]></ecc_channel:system_name>\r\n"
            ."  <ecc_channel:system_url><![CDATA[".$system_family_url."]]></ecc_channel:system_url>\r\n"
      //      ."  <ecc_channel:system_memory>".memory_get_usage()."</ecc_channel:system_memory>\r\n"
            ."  <cf:listinfo xmlns:cf=\"http://www.microsoft.com/schemas/rss/core/2005\">\r\n"
            ."    <cf:group ns=\"ecc\" element=\"systemTitle\" label=\"Provider\"/>\r\n"
            ."    <cf:group element=\"category\" label=\"Category\"/>\r\n"
            ."  </cf:listinfo>\r\n";
    }

    private function _serve_close_channel()
    {
        $this->_xml.=
             "</channel>\r\n"
            ."</rss>\r\n";
    }

    private function _serve_render()
    {
        global $system_vars;
        $site_url = trim($system_vars['URL'], '/').'/';
        if (!$this->args['render']) {
            $this->_xml;
        }
        Page::do_tracking('200', false);
        header("Content-type: text/xml");
        print $this->_xml;
        die;
    }

    private function _serve_set_namespace()
    {
        $this->_namespace =
             "xmlns:atom=\"http://www.w3.org/2005/Atom\" "
            .($this->Obj && $this->Obj->_get_object_name()=="Podcast" ?
                "xmlns:itunes=\"http://www.itunes.com/dtds/podcast-1.0.dtd\" "
              :
                ""
             )
            ."xmlns:ecc_channel=\"http://www.ecclesiact.com/help_rss_ns\" "
            ."xmlns:ecc_detail=\"http://www.ecclesiact.com/help_rss_ns\"";
    }

    private function _serve_set_object()
    {
        switch ($this->args['submode']){
            case "":
                $this->Obj =    new RSS_Help;
                break;
            case "articles":
            case "shared_articles":
                $this->Obj =    new Article;
                break;
            case "events":
            case "shared_events":
                $this->Obj =    new Event;
                break;
            case "gallery_images":
            case "shared_gallery_images":
                $this->Obj =    new Gallery_Image;
                break;
            case "jobs":
            case "shared_jobs":
                $this->Obj =    new Job_Posting;
                break;
            case "news":
            case "shared_news":
                $this->Obj =    new News_Item;
                break;
            case "podcasts":
            case "shared_podcasts":
                $this->Obj =    new Podcast;
                break;
            case "config":
                $this->Obj =    new System(SYS_ID);
                break;
            case "product_parent_itemcodes":
                $this->Obj =    new Product;
                break;
            case "tables_and_fields":
                $this->Obj =    new Table;
                break;
            default:
                $this->Obj =    false;
                break;
        }
    }

    private function _serve_set_title()
    {
        if ($this->args['title']) {
            $this->title = $this->args['title'];
            return;
        }
        global $system_vars;
        $title =   $system_vars['textEnglish']." > RSS";
        switch ($this->args['submode']){
            case "config":
                $ok =
                System::get_item_version('classes_cs_status')=='Pass' &&
                System::get_item_version('libraries_cs_status')=='Pass' &&
                System::get_item_version('reports_cs_status')=='Pass' &&
                System::get_item_version('db_cs_status')=='Pass';
                $title =
                     ($ok ? 'Pass' : 'Fail')
                    .": ".System::get_item_version('build').", "
                    ."Class ".System::get_item_version('classes_cs_status').":"
                    .System::get_item_version('classes_cs_actual').", "
                    ."Lib ".System::get_item_version('libraries_cs_status').":"
                    .System::get_item_version('libraries_cs_actual').", "
                    ."Rpt ".System::get_item_version('reports_cs_status').":"
                    .System::get_item_version('reports_cs_actual').", "
                    ."DB ".System::get_item_version('db_cs_status').":"
                    .System::get_item_version('db_cs_actual');
                break;
            case "product_parent_itemcodes":
                $title.=    " > Product Parent Itemcodes";
                break;
            case "tables_and_fields":
                $title.=    " > Tables and Fields";
                break;
            default:
                if ($this->Obj) {
                    $title.= " > "
                    .(substr($this->args['submode'], 0, 7)=='shared_' ? 'Shared ' : '')
                    .$this->Obj->_get_object_name().$this->Obj->plural('1,2');
                }
                break;
        }
        $this->title = $title;
    }

    private function _serve_setup($args)
    {
        global $communityID, $container_path, $container_subs, $submode, $DD, $memberID, $MM, $offset;
        global $page_vars, $personID, $system_vars, $what, $YYYY;
        $path =     (isset($args['request']) ? $args['request'] : substr($_SERVER["REQUEST_URI"], strlen(BASE_PATH)));
        $path_arr = explode('?', $path);
        $path_arr = explode('/', $path_arr[0]);
        array_shift($path_arr);
        if (isset($path_arr[0])) {
            $submode=array_shift($path_arr);
            for ($i=0; $i<count($path_arr); $i+=2) {
                if (isset($path_arr[$i]) && isset($path_arr[$i+1])) {
                    switch ($path_arr[$i]){
                        case 'category':
                            $category = $path_arr[$i+1];
                            break;
                        case 'limit':
                            $limit = $path_arr[$i+1];
                            break;
                    }
                }
            }
        }
  //    y($args);
        $category = (isset($category) ? $category : get_var('category', '*'));
        $limit =    (isset($limit) ?    $limit :    get_var('limit', 10));
        $this->args = array(
            'base_path' =>      (isset($args['base_path']) ?
                $args['base_path']
              :
                trim($system_vars['URL'], '/').BASE_PATH.'rss/'
            ),
            'byRemote' =>       (isset($args['byRemote']) ?
                $args['byRemote']
              :
                false
             ),
            'category' =>       $category,
            'communityID' =>    (isset($args['communityID']) ?
                $args['communityID']
              :
                $communityID
             ),
            'container_path' => (isset($args['container_path']) ?
                $args['container_path']
              :
                $container_path
             ),
            'container_subs' => (isset($args['container_subs']) ?
                $args['container_subs']
              :
                $container_subs
             ),
            'DD' =>             (isset($args['DD']) ?
                $args['DD']
              :
                $DD
             ),
            'feed_title' =>     (isset($args['feed_title']) ?
                $args['feed_title']
              :
                "RSS feeds for ".$system_vars['textEnglish']
             ),
            'isShared' =>       (isset($args['isShared']) ?
                $args['isShared']
              :
                false
             ),
            'results_limit' =>  $limit,
            'memberID' =>       (isset($args['memberID']) ?
                $args['memberID']
              :
                $memberID
             ),
            'MM' =>             (isset($args['MM']) ?
                $args['MM']
              :
                $MM
             ),
            'offset' =>         (isset($args['offset']) ?
                $args['offset']
              :
                $offset
             ),
            'personID' =>       (isset($args['personID']) ?
                $args['personID']
              :
                $personID
             ),
            'render' =>         (isset($args['render']) ?
                $args['render']
              :
                true
             ),
            'submode' =>        (isset($args['submode']) ?
                $args['submode']
              :
                $submode
             ),
            'title' =>          (isset($args['title']) ?
                $args['title']
              :
                false
             ),
            'what' =>           (isset($args['what']) ?
                $args['what']
              :
                $what
             ),
            'YYYY' =>           (isset($args['YYYY']) ?
                $args['YYYY']
              :
                $YYYY
             ),
        );
  //    y($this->args);die;
        $this->_serve_set_object();
    }

    public function get_version()
    {
        return VERSION_RSS;
    }
}
