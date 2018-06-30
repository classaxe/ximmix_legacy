<?php
define("VERSION_COMPONENT_ARTICLES_ROTATOR", "1.0.7");
/*
Version History:
  1.0.7 (2015-01-31)
    1) Changes to internally used parameters in Component_Articles_Rotator::draw():
         Old: limit,         order_by
         New: results_limit, results_order
    2) Now PSR-2 Compliant

  (Older version history in class.component_articles_rotator.txt)
*/

class Component_Articles_Rotator extends Component_Base
{

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        $ident =            "articles_rotator";
        $safe_ID =          Component_Base::get_safe_ID($ident, $instance);
        $parameter_spec =   array(
            'author_show' =>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'block_layout' =>           array(
                'match' =>      '',
                'default' =>    'Articles',
                'hint' =>       'Name of Block Layout to use'
            ),
            'category_show' =>          array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_char_limit' =>     array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..n'
            ),
            'content_plaintext' =>      array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_use_summary' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'date_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'extra_fields_list' =>      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'filter_category_list' =>   array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       '*|CSV value list'
            ),
            'filter_category_master' => array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'filter_memberID' =>        array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Community Member to restrict by that criteria'
            ),
            'filter_personID' =>        array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Person to restrict by that criteria'
            ),
            'headers_show' =>           array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2'
            ),
            'item_footer_component' =>  array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below each displayed Article'
            ),
            'keywords_show' =>          array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'links_point_to_URL' =>     array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1 - If there is a URL, both title and thumbnails links go to it'
            ),
            'limit_featured' =>         array(
                'match' =>      'range|0,n',
                'default' =>    '1',
                'hint' =>       '0..n'
            ),
            'limit_rotated' =>          array(
                'match' =>      'range|0,n',
                'default' =>    '1',
                'hint' =>       '0..n'
            ),
            'limit_other' =>            array(
                'match' =>      'range|0,n',
                'default' =>    '1',
                'hint' =>       '0..n'
            ),
            'more_link_text' =>         array(
                'match' =>      '',
                'default' =>    '(More)',
                'hint' =>       'text for \'Read More\' link'
            ),
            'related_show' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'results_order' =>          array(
                'match' =>      'enum|date,title',
                'default' =>    'date',
                'hint' =>       'date|title'
            ),
            'results_limit' =>          array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'subtitle_show' =>          array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_at_top' =>       array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_image' =>        array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_link' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_show' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>        array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       '|0..n - give width in px to resize'
            ),
            'title_featured' =>         array(
                'match' =>      '',
                'default' =>    'Featured Article',
                'hint' =>       'title (not plural)'
            ),
            'title_rotated' =>          array(
                'match' =>      '',
                'default' =>    'Other Article',
                'hint' =>       'title (not plural)'
            ),
            'title_other' =>            array(
                'match' =>      '',
                'default' =>    'Additional Article',
                'hint' =>       'title (not plural)'
            )
        );
        $cp_settings =
        Component_Base::get_parameter_defaults_and_values(
            $ident,
            $instance,
            $disable_params,
            $parameter_spec,
            $args
        );
        $cp_defaults =  $cp_settings['defaults'];
        $cp =           $cp_settings['parameters'];
        $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
        $Obj_Article = new Article;
    // Get last n articles
        $filter_offset =   (isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0);
        $results = $Obj_Article->get_records(
            array(
                'category' =>           $cp['filter_category_list'],
                'category_master' =>    $cp['filter_category_master'],
                'memberID' =>           $cp['filter_memberID'],
                'personID' =>           $cp['filter_personID'],
                'offset' =>             $filter_offset,
                'results_limit' =>      $cp['results_limit'],
                'results_order' =>      $cp['results_order']
            )
        );
        $records =  $results['data'];
        $items_featured_arr =       array();
        $items_rotated_arr =        array();
        if (count($records)<$cp['limit_featured']) {
            $cp['limit_featured'] = count($records);
        }
        if (count($records)>=$cp['limit_featured']) {
            for ($i=0; $i<$cp['limit_featured']; $i++) {
                $items_featured_arr[] =     array_shift($records);
            }
        }
        if (count($records)>=$cp['limit_featured']+$cp['limit_rotated']) {
            for ($i=0; $i<$cp['limit_rotated']; $i++) {
                $n =            rand(0, count($records)-1);
                $items_rotated_arr[] =  $records[$n];
                array_splice($records, $n, 1);
            }
        }
        $args = array(
            'author_show' =>            $cp['author_show'],
            'block_layout' =>           $cp['block_layout'],
            'category_show' =>          $cp['category_show'],
            'content_char_limit' =>     $cp['content_char_limit'],
            'content_plaintext' =>      $cp['content_plaintext'],
            'content_show' =>           $cp['content_show'],
            'content_use_summary' =>    $cp['content_use_summary'],
            'extra_fields_list' =>      $cp['extra_fields_list'],
            'date_show' =>              $cp['date_show'],
            'item_footer_component' =>  $cp['item_footer_component'],
            'links_point_to_URL' =>     $cp['links_point_to_URL'],
            'more_link_text' =>         $cp['more_link_text'],
            'related_show' =>           $cp['related_show'],
            'subtitle_show' =>          $cp['subtitle_show'],
            'thumbnail_at_top' =>       $cp['thumbnail_at_top'],
            'thumbnail_image' =>        $cp['thumbnail_image'],
            'thumbnail_link' =>         $cp['thumbnail_link'],
            'thumbnail_show' =>         $cp['thumbnail_show'],
            'thumbnail_width' =>        $cp['thumbnail_width']
        );

        $out.=
            "<div id=\"".$ident."_".$instance."\">"
            .(count($items_featured_arr) ?
                 "<div class='articles_featured'>\n"
                .($cp['headers_show']>1 ?
                    "<h2 class='header'>".$cp['title_featured'].(count($items_featured_arr)==1 ? '' : 's')."</h2>"
                 :
                    ""
                 )
                .$Obj_Article->draw_from_recordset($items_featured_arr, $args)
                ."<div class='clr_b'></div></div>\n"
                .(count($items_rotated_arr) || count($records) ? "<hr />\n" : "")
             :
                ""
            )
            .(count($items_rotated_arr) ?
                 "<div class='articles_rotated'>\n"
                .($cp['headers_show']>1 ?
                    "<h2 class='header'>".$cp['title_rotated'].(count($items_rotated_arr)==1 ? '' : 's')."</h2>\n"
                :
                    ""
                 )
                .$Obj_Article->draw_from_recordset($items_rotated_arr, $args)
                ."<div class='clr_b'></div></div>\n"
                .(count($records) && $cp['limit_other'] ?
                    "<hr />\n"
                 :
                    ""
                 )
             :
                ""
            );
        if (count($records) && $cp['limit_other']) {
            $isMASTERADMIN =    get_person_permission("MASTERADMIN");
            $isSYSADMIN =        get_person_permission("SYSADMIN");
            $isSYSEDITOR =      get_person_permission("SYSEDITOR");
            $canEdit =          ($isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR);
            $out.=
                 "<div class='articles_other'>\n"
                .($cp['headers_show']>0 ?
                     "<h2 class='header'>"
                    .$cp['title_other']
                    .(count($records)==1 || $cp['limit_other']==1 ? '' : 's')
                    ."</h2>"
                  :
                    ""
                 );
            foreach ($records as $record) {
                if ($cp['limit_other']-- == 0) {
                    break;
                }
                $systemID =   $record['systemID'];
                $ID =         $record['ID'];
                $URL =        $Obj_Article->get_URL($record);
                $out.=
                     "<div"
                    .(
                        $canEdit &&
                        isset($record['ID']) &&
                        $record['ID'] &&
                        ($record['systemID']==SYS_ID || $isMASTERADMIN)
                     ?
                          " onmouseover=\""
                         ."if(!CM_visible('CM_article')) {"
                         ."this.style.backgroundColor='"
                         .($record['systemID']==SYS_ID ? '#ffff80' : '#ffe0e0')
                         ."';"
                         ."_CM.type='article';"
                         ."_CM.ID=".$ID.";"
                         ."_CM_text[0]='&quot;"
                         .str_replace(array("'","\""), array('','&quot;'), $record['title'])
                         ."&quot;';"
                         ."_CM_text[1]=_CM_text[0];}\" "
                         ." onmouseout=\"this.style.backgroundColor='';_CM.type='';\""
                      :
                        ""
                     )
                    .">\n"
                    ."<h2 class='title'>"
                    ."<a href=\"".$URL."\""
                    .($systemID!=SYS_ID ?
                        " rel='external' title=\"Read ".$Obj_Article->_get_object_name()." (opens in a new window)\""
                      :
                        " title=\"Read ".$Obj_Article->_get_object_name()."\""
                     )
                    .">"
                    .$record['title']
                    ."</a>"
                    ."</h2>\n"
                    .($cp['date_show'] ?
                         "<div class='subhead' style='padding-bottom: 0.5em;'>"
                        .format_date($record['date'])
                        .($record['comments_count'] ?
                             " | <a href=\"".$URL."#anchor_comments_list\">".$record['comments_count']." comment"
                            .($record['comments_count']==1 ? "" : "s")
                            ." &raquo;</a>"
                          :
                            ""
                         )
                         ."</div>\n"
                      :
                        ""
                     )
                     ."</div>\n";
            }
            $out.="</div>";
        }
        $out.="</div>";
        if (isset($_REQUEST['command']) && $_REQUEST['command']=="articles_panel_".$instance."_load") {
            print $out;
            die;
        }
        return $out;
    }

    public function get_version()
    {
        return VERSION_COMPONENT_ARTICLES_ROTATOR;
    }
}
