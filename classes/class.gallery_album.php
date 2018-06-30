<?php
define('VERSION_GALLERY_ALBUM', '1.0.33');
/*
Version History:
  1.0.33 (2015-01-31)
    1) Changes to internally used parameters in Gallery_Album::BL_contained_items():
         Old: filter_limit,  paging_controls
         New: results_limit, results_paging
    2) Now PSR-2 Compliant

  (Older version history in class.gallery_album.txt)

*/
class Gallery_Album extends Posting_Container
{
    public function __construct($ID = "", $systemID = SYS_ID)
    {
        parent::__construct($ID, $systemID);
        $this->_set_default_enclosure_base_folder(
            (defined('BASE_PATH') ? BASE_PATH : '/').'UserFiles/Image/gallery-images/'
        );
        $this->_set_type('gallery-album');
        $this->_set_assign_type('gallery-album');
        $this->_set_has_publish_date(true);      // Do now allow item to be seen prior to publish date
        $this->_set_object_name('Gallery Album');
        $this->_set_container_object_type('Gallery_Album');
        $this->set_edit_params(
            array(
            'report'=>
                'gallery-albums',
            'report_rename'=>
                true,
            'report_rename_label'=>
                'new title',
            'icon_delete'=>
                '[ICON]18 18 5284 Delete this '.$this->_get_object_name().'[/ICON]',
            'icon_edit'=>
                '[ICON]19 19 5246 Edit this '.$this->_get_object_name().'[/ICON]',
            'icon_edit_disabled'=>
                '[ICON]19 19 5265 (Edit this '.$this->_get_object_name().')[/ICON]',
            'icon_edit_popup'=>
                '[ICON]19 19 4713 Edit this '.$this->_get_object_name().' in a popup window[/ICON]'
            )
        );
        $this->_cp_vars_detail = array(
            'block_layout'=>                array(
                'match' =>      '',
                'default' =>    'Gallery Album',
                'hint' =>       'Name of Block Layout to use'
            ),
            'contents_block_layout'=>       array(
                'match' =>      '',
                'default' =>    'Gallery Image',
                'hint' =>       'Name of Block Layout to use for content listings'
            ),
            'contents_results_limit'=>      array(
                'match' =>      'range|0,n',
                'default' =>    '10',
                'hint' =>       '0..n'
            ),
            'contents_results_paging'=>     array(
                'match' =>      'enum|0,1,2',
                'default' =>    '2',
                'hint' =>       '0|1|2 - 1 for buttons, 2 for links'
            ),
            'contents_show'=>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       'Whether or not to list contents of album'
            ),
            'contents_thumbnail_at_top'=>   array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'contents_thumbnail_height'=>   array(
                'match' =>      'range|1,n',
                'default' =>    '0',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'contents_thumbnail_width'=>    array(
                'match' =>      'range|1,n',
                'default' =>    '100',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'extra_fields_list'=>           array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'item_footer_component'=>       array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below displayed Job Posting'
            ),
            'thumbnail_height'=>            array(
                'match' =>      'range|1,n',
                'default' =>    '300',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_image' =>            array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width'=>             array(
                'match' =>      'range|1,n',
                'default' =>    '400',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'title_linked'=>                array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show'=>                  array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            )
        );
        $this->_cp_vars_listings = array(
            'background'=>                  array(
                'match' =>      'hex3|',
                'default' =>    '',
                'hint' =>       'Hex code for background colour to use'
            ),
            'block_layout'=>                array(
                'match' =>      '',
                'default' =>    'Gallery Album',
                'hint' =>       'Name of Block Layout to use'
            ),
            'box'=>                         array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2'
            ),
            'box_footer'=>                  array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text below displayed Job Postings'
            ),
            'box_header'=>                  array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text above displayed Job Postings'
            ),
            'box_rss_link'=>                array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title'=>                   array(
                'match' =>      '',
                'default' =>    'Gallery Albums',
                'hint' =>       'text'
            ),
            'box_title_link'=>              array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title_link_page'=>         array(
                'match' =>      '',
                'default' =>    'gallery-albums',
                'hint' =>       'page'
            ),
            'box_width'=>                   array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..x'
            ),
            'category_show'=>               array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'comments_link_show'=>          array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_char_limit'=>          array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..n'
            ),
            'content_plaintext'=>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_show'=>                array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'date_show'=>                   array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'extra_fields_list'=>           array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'filter_category_list'=>        array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       'Optionally limits items to those in this gallery album - / means none'
            ),
            'filter_category_master'=>      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'filter_container_path'=>       array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally limits items to those contained in this folder'
            ),
            'filter_container_subs'=>       array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       'If filtering by container folder, enable this setting to include subfolders'
            ),
            'filter_important' =>           array(
                'match' =>      'enum|,0,1',
                'default' =>    '',
                'hint' =>       'Blank to ignore, 0 for not important, 1 for important'
            ),
            'filter_memberID'=>             array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Community Member to restrict by that criteria'
            ),
            'filter_personID'=>             array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'ID of Person to restrict by that criteria'
            ),
            'item_footer_component'=>       array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below each displayed Posting'
            ),
            'more_link_text'=>              array(
                'match' =>      '',
                'default' =>    '(More)',
                'hint' =>       'text for \'Read More\' link'
            ),
            'results_grouping'=>            array(
                'match' =>      'enum|,month,year',
                'default' =>    '',
                'hint' =>       '|month|year'
            ),
            'results_limit'=>               array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'results_order'=>               array(
                'match' =>      'enum|date,title',
                'default' =>    'date',
                'hint' =>       'date|title'
            ),
            'results_paging'=>              array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2 - 1 for buttons, 2 for links'
            ),
            'thumbnail_at_top'=>            array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height'=>            array(
                'match' =>      'range|1,n',
                'default' =>    '150',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_image' =>            array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_link'=>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width'=>             array(
                'match' =>      'range|1,n',
                'default' =>    '200',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'title_linked'=>                array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show'=>                  array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            )
        );
    }

    protected function BL_contained_items()
    {
        if (!isset($this->_cp['contents_show']) || $this->_cp['contents_show']!='1') {
            return;
        }
        $Obj_Contained = new Gallery_Image;
        $args = array(
            'block_layout'=>              $this->_cp['contents_block_layout'],
            'filter_container_path'=>     $this->record['path'],
            'results_paging'=>            $this->_cp['contents_results_paging'],
            'results_limit'=>             $this->_cp['contents_results_limit'],
            'thumbnail_at_top'=>          $this->_cp['contents_thumbnail_at_top'],
            'thumbnail_height'=>          $this->_cp['contents_thumbnail_height'],
            'thumbnail_width'=>           $this->_cp['contents_thumbnail_width']
        );
        return $Obj_Contained->draw_listings($this->_instance, $args, true);
    }

    public function get_albums($parentID = 0, $sortBy = '`name` ASC')
    {
        $out = array();
        $sql =
             "SELECT\n"
            ."  `ID`,\n"
            ."  `parentID`,\n"
            ."  `content`,\n"
            ."  `date`,\n"
            ."  `enclosure_url`,\n"
            ."  `name`,\n"
            ."  `title`,\n"
            ."  `path`,\n"
            ."  `thumbnail_small`,\n"
            ."  `group_assign_csv`,\n"
            ."  `password`,\n"
            ."  `permPUBLIC`,\n"
            ."  `permSYSLOGON`,\n"
            ."  `permSYSMEMBER`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `type` = 'gallery-album' AND\n"
            .($parentID!==false ? "  `parentID` IN(".$parentID.") AND\n" : "")
            .$this->_get_records_get_sql_filter_publish_date()
            ."  `systemID` IN (1,".SYS_ID.")\n"
            ."ORDER BY\n"
            ."  ".$sortBy;
        $records = $this->get_records_for_sql($sql);
        foreach ($records as $record) {
            if ($this->is_visible($record)) {
                $out[] = $record;
            }
        }
        foreach ($out as &$album) {
            $album['albums'] = $this->get_albums($album['ID'], $sortBy);
            $album['images'] = $this->get_images($album['ID']);
        }
        return $out;
    }

    public function get_images($parentID = 0)
    {
        $csv = '';
        if ($parentID) {
            $Obj_Gallery_Album = new Gallery_Album($parentID);
            $csv = $Obj_Gallery_Album->get_field('childID_csv');
            $Obj_Parent_type =  $Obj_Gallery_Album->_get_container_object_type();
        }
        $out = array();
        $sql =
             "SELECT\n"
            ."  `ID`,\n"
            ."  `parentID`,\n"
            ."  `category`,\n"
            ."  `content`,\n"
            ."  `date`,\n"
            ."  `date_end`,\n"
            ."  `enabled`,\n"
            ."  `name`,\n"
            ."  `path`,\n"
            ."  `title`,\n"
            ."  `URL`,\n"
            ."  `popup`,\n"
            ."  `subtype`,\n"
            ."  `thumbnail_cs_small`,\n"
            ."  `thumbnail_small`,\n"
            ."  `group_assign_csv`,\n"
            ."  `password`,\n"
            ."  `permPUBLIC`,\n"
            ."  `permSYSLOGON`,\n"
            ."  `permSYSMEMBER`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `type` = 'gallery-image' AND\n"
            ."  `parentID` !=0 AND\n"
            .($parentID!==false ? "  `parentID` IN(".$parentID.") AND\n" : "")
            .$this->_get_records_get_sql_filter_publish_date()
            ."  `systemID` IN (1,".SYS_ID.")";
  //    z($sql);
        $records = $this->get_records_for_sql($sql);
        $parentTitles = array();
        foreach ($records as $record) {
            $record['available'] = $this->is_available($record);
            if ($parentID) {
                if (!array_key_exists($record['parentID'], $parentTitles)) {
                    $Obj_Parent = new $Obj_Parent_type($record['parentID']);
                    $parentTitles[$record['parentID']] = $Obj_Parent->get_field('title');
                }
                $record['parentTitle'] = $parentTitles[$record['parentID']];
            } else {
                $record['parentTitle'] = "";
            }
            $out[] = $record;
        }
        if (!$csv) {
            return $out;
        }
        $csv_arr =  explode(',', $csv);
        $tmp =      array();
        foreach ($csv_arr as $ID) {
            foreach ($out as &$item) {
                if ($item['ID']==$ID) {
                    $tmp[] = $item;
                }
            }
        }
        foreach ($out as &$item) {
            if (!in_array($item['ID'], $csv_arr)) {
                $tmp[] = $item;
            }
        }
        return $tmp;
    }

    public function handle_report_copy(&$newID, &$msg, &$msg_tooltip, $name)
    {
        return parent::try_copy($newID, $msg, $msg_tooltip, $name);
    }

    public function manage_gallery_images()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('gallery-images-for-gallery-album', 1);
        }
        return
            "<h3 style='margin:0.25em'>Gallery Images inside this ".$this->_get_object_name().":</h3>"
            .(get_var('selectID') ?
                draw_auto_report('gallery-images-for-gallery-album', 1)
             :
                "<p style='margin:0.25em'>No contents - this ".$this->_get_object_name()." has not been saved yet.</p>"
             );
    }

    public function manage_gallery_albums()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('gallery-albums-for-gallery-album', 1);
        }
        return
            "<h3 style='margin:0.25em'>Gallery Albums inside this ".$this->_get_object_name().":</h3>"
            .(get_var('selectID') ?
                draw_auto_report('gallery-albums-for-gallery-album', 1)
             :
                "<p style='margin:0.25em'>No contents - this ".$this->_get_object_name()." has not been saved yet.</p>"
             );
    }

    public function get_version()
    {
        return VERSION_GALLERY_ALBUM;
    }
}
