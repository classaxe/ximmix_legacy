<?php
define('VERSION_PODCAST', '1.0.46');
/*
Version History:
  1.0.46 (2015-02-06)
    1) Now allows for ordering by date_d_name_a and date_d_title_a (for DCC AM / PM services on same day)

  (Older version history in class.podcast.txt)
*/
class Podcast extends Posting_Contained
{

    public function __construct($ID = "", $systemID = SYS_ID)
    {
        parent::__construct($ID, $systemID);
        $this->_set_type('podcast');
        $this->_set_assign_type('podcast');
        $this->_set_has_publish_date(true);      // Do now allow item to be seen prior to publish date
        $this->_set_object_name('Podcast');
        $this->_set_container_object_type('Podcast_Album');
        $this->set_edit_params(
            array(
                'command_for_delete' =>     'podcast_delete',
                'report' =>                 'podcasts',
                'report_rename' =>          true,
                'report_rename_label' =>    'new title',
                'icon_delete' =>            '[ICON]13 13 4473 Delete this Podcast[/ICON]',
                'icon_edit' =>              '[ICON]17 17 2278 Edit this Podcast[/ICON]',
                'icon_edit_disabled' =>     '[ICON]15 15 2445 (Edit this Podcast)[/ICON]',
                'icon_edit_popup' =>        '[ICON]18 18 2534 Edit this Podcast in a popup window[/ICON]'
            )
        );
        $this->_cp_vars_detail = array(
            'audioplayer_width' =>        array(
                'match' =>      'range|0,n',
                'default' =>    '360',
                'hint' =>       '0|x'
            ),
            'author_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'block_layout' =>             array(
                'match' =>      '',
                'default' =>    'Podcast',
                'hint' =>       'Name of Block Layout to use'
            ),
            'category_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'comments_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'comments_link_show' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'extra_fields_list' =>        array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'item_footer_component' =>    array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below displayed Podcast'
            ),
            'subscribe_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - Whether or not to allow subscriptions'
            ),
            'subtitle_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_at_top' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height' =>         array(
                'match' =>      'range|1,n',
                'default' =>    '300',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_link' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>          array(
                'match' =>      'range|1,n',
                'default' =>    '400',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'thumbnail_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - Whether or not to allow thumbnail to show'
            ),
            'title_linked' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'video_height' =>             array(
                'match' =>      'range|0,n',
                'default' =>    '300',
                'hint' =>       '0|x'
            ),
            'video_width' =>              array(
                'match' =>      'range|0,n',
                'default' =>    '400',
                'hint' =>       '0|x'
            ),
            'video_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1 - Whether or not to allow video to show'
            ),
        );
        $this->_cp_vars_listings = array(
            'audioplayer_width' =>        array(
                'match' =>      'range|0,n',
                'default' =>    '180',
                'hint' =>       '0..x'
            ),
            'author_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'background' =>               array(
                'match' =>      'hex3|',
                'default' =>    '',
                'hint' =>       'Hex code for background colour to use'
            ),
            'block_layout' =>             array(
                'match' =>      '',
                'default' =>    'Podcast',
                'hint' =>       'Name of Block Layout to use'
            ),
            'box' =>                      array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2'
            ),
            'box_footer' =>               array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text below displayed Podcasts'
            ),
            'box_header' =>               array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text above displayed Podcasts'
            ),
            'box_rss_link' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title' =>                array(
                'match' =>      '',
                'default' =>    'Podcasts',
                'hint' =>       'text'
            ),
            'box_title_link' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title_link_page' =>      array(
                'match' =>      '',
                'default' =>    'all_podcasts',
                'hint' =>       'page'
            ),
            'box_width' =>                array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..x'
            ),
            'category_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'comments_link_show' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_char_limit' =>       array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..n'
            ),
            'content_plaintext' =>        array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'extra_fields_list' =>        array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'filter_category_list' =>     array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       'Optionally limits items to those in this gallery album - / means none'
            ),
            'filter_category_master' =>   array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'filter_container_path' =>    array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally limits items to those contained in this folder'
            ),
            'filter_container_subs' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       'If filtering by container folder, enable this setting to include subfolders'
            ),
            'filter_important' =>         array(
                'match' =>      'enum|,0,1',
                'default' =>    '',
                'hint' =>       'Blank to ignore, 0 for not important, 1 for important'
            ),
            'filter_memberID' =>          array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Community Member to restrict by that criteria'
            ),
            'filter_personID' =>          array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Person to restrict by that criteria'
            ),
            'item_footer_component' =>    array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below each displayed Podcast'
            ),
            'links_point_to_URL' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - If there is a URL, both title and thumbnails links go to it'
            ),
            'links_switch_video' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - If there is a video, both title and thumbnails links select it'
            ),
            'more_link_text' =>           array(
                'match' =>      '',
                'default' =>    '(More)',
                'hint' =>       'text for \'Read More\' link'
            ),
            'results_grouping' =>         array(
                'match' =>      'enum|,month,year',
                'default' =>    '',
                'hint' =>       '|month|year'
            ),
            'results_limit' =>            array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'results_order' =>            array(
                'match' =>      'enum|date,date_a,date_d_title_a,name,title',
                'default' =>    'date',
                'hint' =>       'date|date_a|date_d_title_a|name|title'
            ),
            'results_paging' =>           array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2 - 1 for buttons, 2 for links'
            ),
            'subscribe_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - Whether or not to allow subscriptions'
            ),
            'subtitle_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'title_linked' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_at_top' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height' =>         array(
                'match' =>      'range|1,n',
                'default' =>    '',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_link' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>          array(
                'match' =>      'range|1,n',
                'default' =>    '',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'thumbnail_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '',
                'hint' =>       '0|1 - Whether or not to allow thumbnail to show'
            ),
            'video_height' =>             array(
                'match' =>      'range|0,n',
                'default' =>    '180',
                'hint' =>       '0|x'
            ),
            'video_width' =>              array(
                'match' =>      'range|0,n',
                'default' =>    '240',
                'hint' =>       '0|x'
            ),
            'video_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1 - Whether or not to allow video to show'
            )
        );
    }

    protected function BL_audio_clip()
    {
        if ($this->record['enclosure_url']!='') {
            $width = (isset($this->_cp['audioplayer_width']) ?
                $this->_cp['audioplayer_width']
             :
                $this->_cp_defaults['audioplayer_width']
            );
            return
                "[audio:"
                .($this->record['systemID']!=SYS_ID && substr($this->record['enclosure_url'], 0, 4)!="http" ?
                    trim($this->record['systemURL'], "/")."/".trim($this->record['enclosure_url'], "/")
                  :
                    $this->record['enclosure_url']
                 )
                .($width ? "|width=".$width : "")
                ."]";
        }
    }

    protected function BL_icon_for_audio_download()
    {
        return $this->draw_link('media_download', $this->record);
    }

    protected function BL_link_for_audioplayer()
    {
        if ($this->record['enclosure_url']=='') {
            return;
        }
        $url = ($this->record['systemID']!=SYS_ID && substr($this->record['enclosure_url'], 0, 4)!="http" ?
            trim($this->record['systemURL'], "/")."/"
          :
            ""
        )."?command=podcast_player&amp;targetID=".$this->record['ID'];
        return
             "<a class='item_download' href=\"".$url."\""
            ." onclick=\"popWin(this.href,'".$this->record['ID']."','resizable=1',320,120);return false;\" "
            ." rel='external'"
            ." title=\"Open Popup Media Player\">";
    }

    protected function BL_links()
    {
        $link_arr =     array();
        if (isset($this->record['URL']) && $this->record['URL']!='') {
            $link_arr[] = $this->draw_link('link');
        }
        if (isset($this->record['enclosure_url']) && $this->record['enclosure_url']!='') {
            $link_arr[] = $this->draw_link('media_download_mini');
            $link_arr[] = $this->draw_link('media_popup_player');
        }
        if (count($link_arr)) {
            return
            implode("<span>|</span>", $link_arr);
        }
    }

    protected function BL_links_for_listings()
    {
        $link_arr =     array();
        if (!isset($this->_cp['content_show']) || $this->_cp['content_show']=='1') {
            if ($truncated = $this->truncate_more($this->record['content'])) {
                $link_arr[] = $this->draw_link(
                    'read_more',
                    $this->record,
                    array('label'=>'')
                );
            }
        }
        if (
            isset($this->record['URL']) &&
            $this->record['URL']!='' &&
            !(isset($this->_cp['links_point_to_URL']) &&
            $this->_cp['links_point_to_URL']==1)
        ) {
            $link_arr[] = $this->draw_link('link');
        }
        if (isset($this->record['enclosure_url']) && $this->record['enclosure_url']!='') {
            $link_arr[] = $this->draw_link('media_download_mini');
            $link_arr[] = $this->draw_link('media_popup_player');
        }
        if (count($link_arr)) {
            return
            implode("<span>|</span>", $link_arr);
        }
    }

    public function draw_player()
    {
        global $system_vars;
        $record = $this->get_record();
        if ($record['enclosure_url']=='') {
            return "";
        }
        $Obj_System = new System(SYS_ID);
        $media_URL = ($record['systemID']!=SYS_ID && substr($record['enclosure_url'], 0, 4)!="http" ?
            trim($record['systemURL'], "/")."/".trim($record['enclosure_url'], "/")
         :
            $record['enclosure_url']
        );
        $Obj_AP =     new Media_Audioplayer($media_URL.'|width=300');
        $version = System::get_item_version('js_jdplayer');
        $path =   BASE_PATH."sysjs/jdplayer/".$version."/";
        return
            "<html>\n"
            ."<head>\n"
            ."<title>".$this->_get_object_name().": ".$record['title']."</title>\n"
            .$Obj_System->draw_css_include()
            .$Obj_System->draw_js_include()
            ."<script type=\"text/javascript\" src=\"".$path."mediaelement-and-player.min.js\"></script>\n"
            ."<link rel=\"stylesheet\" type=\"text/css\" href=\"".$path."mediaelementplayer.css\" />\n"
            ."</head>\n"
            ."<body style='padding:5px;margin:0;'>\n"
            ."<div style='float:left;width:20px;'>"
            .$this->draw_link('media_download', $record)
            ."</a>"
            ."</div>"
            ."<h1 style='margin:0 0 10px 0;font-size:14pt'>"
            .$system_vars['textEnglish'].":<br />".$record['title']."\n"
            ."</h1>"
            .$Obj_AP->draw_clip()
            ."<script type='text/javascript'>\$('audio').mediaelementplayer();</script>"
            ."</body></html>";
    }

    public function get_mp3_metadata()
    {
        global $msg;
        $out = array();
        $record = $this->get_record();
  /*
      y($record);
      if($record['enclosure_meta']!='') {
        $data_pos = strpos($record['enclosure_meta'],"data:");
        $data = substr($record['enclosure_meta'],$data_pos+5+strlen('url('),-1);
        $this->set_field('enclosure_url',$data);
        $record = $this->get_record(false);
      }
  */
        $url = urldecode($record['enclosure_url']);
        if (substr($url, 0, 4)!='http') {
            if ($record['systemID']!=SYS_ID) {
                $msg.= "<li>Cannot analyze remote file<br />&nbsp; ".$url."</li>";
                return $out;
            }
            clearstatcache();
          // Get file size
            $size = filesize(realpath(".".$url));
            $out['enclosure_size'] = $size;
            if (!$size) {
                $msg.= "<li>Cannot find ".$url."</li>";
                $out['enclosure_type'] = "";
                $out['enclosure_secs'] = 0;
                return $out;
            }
            require_once(SYS_SHARED."getid3/getid3.php");
            $Obj_ID3 = new getID3;
            $result = $Obj_ID3->analyze(realpath(".".$url));

          // Set publish date if not already given:
            if ($record['date']=='' || $record['date']=='0000-00-00') {
                if ($date = sanitize('date-stamp', substr($result['filename'], 0, 10))) {
                  // YYYY-MM-DD:
                    $out['date'] = $date;
                } elseif (
                    $date = sanitize(
                        'date-stamp',
                        substr($result['filename'], 0, 4).'-'
                        .substr($result['filename'], 4, 2).'-'
                        .substr($result['filename'], 6, 2)
                    )
                ) {
                  // YYYYMMDD:
                    $out['date'] = $date;
                } else {
                    $out['date'] = get_timestamp();
                }
            }

            if (isset($result['fileformat']) && $result['fileformat']=='mp3') {
                $out['enclosure_type'] = 'audio/mpeg';
                $out['enclosure_secs'] = ceil($result['playtime_seconds']);

                if ($record['title']=='' || $record['title']=='Untitled') {
                    $find = array('"');
                    $replace = array("'");
                    if (
                        isset($result['tags']['id3v2']['title'][0]) &&
                        trim($result['tags']['id3v2']['title'][0])!=''
                    ) {
                        $out['title'] =
                            trim(str_replace($find, $replace, $result['tags']['id3v2']['title'][0]));
                    } elseif (
                        isset($result['id3v1']['comments']['title'][0]) &&
                        trim($result['id3v1']['comments']['title'][0])!=''
                    ) {
                        $out['title'] =
                            trim(str_replace($find, $replace, $result['id3v1']['comments']['title'][0]));
                    } elseif (
                        isset($result['id3v1']['title']) &&
                        trim($result['id3v1']['title'])
                    ) {
                        $out['title'] =
                            trim(str_replace($find, $replace, $result['id3v1']['title']));
                    } else {
                        $out['title'] =
                            'Untitled';
                    }
                    if ($record['name']=='' || $record['name']=='untitled') {
                        $out['name'] =    get_web_safe_ID($out['title']);
                    }
                }

                if ($record['author']=='') {
                    $find = array('"');
                    $replace = array("'");
                    if (
                        isset($result['tags']['id3v2']['artist'][0]) &&
                        $result['tags']['id3v2']['artist'][0]!=''
                    ) {
                        $out['author'] =
                            str_replace($find, $replace, $result['tags']['id3v2']['artist'][0]);
                    } elseif (
                        isset($result['id3v1']['comments']['artist'][0]) &&
                        $result['id3v1']['comments']['artist'][0]!=''
                    ) {
                        $out['author'] =
                            str_replace($find, $replace, $result['id3v1']['comments']['artist'][0]);
                    } elseif (
                        isset($result['id3v1']['artist']) &&
                        $result['id3v1']['artist']!=''
                    ) {
                        $out['author'] =
                            str_replace($find, $replace, $result['id3v1']['artist']);
                    }
                }

                if ($record['content']=='') {
                    $find = array('"');
                    $replace = array("'");
                    if (
                        isset($result['tags']['id3v2']['comments'][0]) &&
                        $result['tags']['id3v2']['comments'][0]
                    ) {
                        $out['content'] =
                            str_replace($find, $replace, $result['tags']['id3v2']['comments'][0]);
                    } elseif (
                        isset($result['id3v1']['comments']['comments'][0]) &&
                        $result['id3v1']['comments']['comments'][0]
                    ) {
                        $out['content'] =
                            str_replace($find, $replace, $result['id3v1']['comments']['comments'][0]);
                    } elseif (
                        isset($result['id3v1']['comments'][0]) &&
                        $result['id3v1']['comments'][0]
                    ) {
                        $out['content'] =
                            str_replace($find, $replace, $result['id3v1']['comments'][0]);
                    } else {
                        $out['content'] =
                            '';
                    }
                    $out['content_text'] = $out['content'];
                }
            }
        }
        return $out;
    }

    protected function _get_records_sort_records()
    {
        $this->_get_records_sort_records_using_results_order();
    }

    public static function on_action_get_metadata()
    {
        global $action_parameters;
        $ID =           $action_parameters['triggerID'];
        $Obj_Podcast =  new Podcast;
        $ID_arr =       explode(",", $ID);
        foreach ($ID_arr as $ID) {
            $Obj_Podcast->_set_ID($ID);
            $data = $Obj_Podcast->get_mp3_metadata();
            $Obj_Podcast->update($data);
        }
    }

    public function get_version()
    {
        return VERSION_PODCAST;
    }
}
