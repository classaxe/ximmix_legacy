<?php
define("VERSION_POSTING_CONTAINED", "1.0.242");
/*
Version History:
  1.0.242 (2015-02-01)
    1) Changed call in Posting_Contained::_get_records_sort_records()
         from $this->_get_records_sort_records_using_filter_order_by()
         to   $this->_get_records_sort_records_using_results_order()
    2) Changed call in Posting_Contained::_get_records_sort_records_by_sequence()
         from $this->_get_records_sort_records_using_filter_order_by()
         to   $this->_get_records_sort_records_using_results_order()
    3) Changes internal arguments for Posting_Contained::get_records_matching()
         Old: filter_limit,  filter_order_by
         New: results_limit, results_order
    4) Changes to internal arguments for Posting_Contained::_draw_listings_load_records()
         Old: filter_limit,  filter_order_by
         New: results_limit, results_order
    5) Now PSR-2 Compliant

  (Older version history in class.posting_contained.txt)
*/

class Posting_Contained extends Posting
{
    private $_container_object_type;

    public function __construct($ID = '', $systemID = SYS_ID)
    {
        parent::__construct($ID, $systemID);
    }

    public function _get_container_object_type()
    {
        return $this->_container_object_type;
    }

    public function _set_container_object_type($type)
    {
        $this->_container_object_type = $type;
    }

    protected function BL_author_linked()
    {
        if (!isset($this->record['author'])) {
            return;
        }
        if (!isset($this->record['author_link'])) {
            return $this->BL_author();
        }
        return
        "<a href=\"".$this->record['author_link']."\">".$this->BL_author()."</a>";
    }

    protected function BL_parent_field($name, $match = '', $found_text = '', $not_found_text = '')
    {
        if (!$name) {
            return "&#91;BL&#93;parent_field(<b>name</b>)&#91;BL&#93; - <b>name</b> is required";
        }
        if (!isset($this->record[$name])) {
            if (substr($name, 0, 4)=='xml:') {
                return "";
            }
            return "&#91;BL&#93;parent_field('<b>".$name."</b>')&#91;BL&#93; - <b>".$name."</b> is not available";
        }
        if (!$this->record['parentID']) {
            return "";
        }
        $type = $this->_get_container_object_type();
        $Obj_Container = new $type($this->record['parentID']);
        $Obj_Container->load();
        if ($match=='') {
            return $Obj_Container->get_field($name);
        }
        if (strpos($Obj_Container->get_field($name), $match)===false) {
            return $not_found_text;
        }
        return $found_text;
    }

    protected function BL_parent_title()
    {
        if (isset($this->record['parentTitle'])) {
            return $this->record['parentTitle'];
        }
    }

    protected function BL_parent_title_linked()
    {
        if (!isset($this->record['parentTitle'])) {
            return;
        }
        if (!isset($this->record['parent_link'])) {
            return $this->BL_parent_title();
        }
        return
            "<a href=\"".$this->record['parent_link']."\">".$this->BL_parent_title()."</a>";
    }

    public function do_submode()
    {
        switch (get_var('submode')) {
            case "gallery_album_delete":
            case "podcast_album_delete":
                $parent_type = $this->_get_container_object_type();
                $Obj_parent = new $parent_type(get_var('targetID'));
                if ($Obj_parent->get_field('systemID')==SYS_ID) {
                    $Obj_parent->delete();
                    return
                        "<b>Success:</b> ".str_replace('_', ' ', $this->_get_container_object_type())." was deleted.";
                }
                break;
            case "gallery_image_cover":
                $this->_set_ID(get_var('targetID'));
                if ($this->load() && $this->record['systemID']==SYS_ID) {
                    $image =    $this->record['thumbnail_small'];
                    $parentID = $this->record['parentID'];
                    $parent_type = $this->_get_container_object_type();
                    $Obj_parent = new $parent_type($parentID);
                    $Obj_parent->set_field('thumbnail_small', $image);
                    $Obj_parent->set_field('childID_featured', get_var('targetID'));
                    return
                        "<b>Success:</b> ".$this->_get_object_name()." now featured on "
                       .str_replace('_', ' ', $this->_get_container_object_type()).".";
                }
                break;
            case "gallery_image_delete":
                $this->_set_ID(get_var('targetID'));
                if ($this->get_field('systemID')==SYS_ID) {
                    $this->delete();
                    return
                        "<b>Success:</b> ".$this->_get_object_name()." was deleted.";
                }
                break;
            case "gallery_image_rotate_left":
                $degrees = 90;
                $this->_set_ID(get_var('targetID'));
                if ($this->load() && $this->record['systemID']==SYS_ID) {
                    if (!$filename = Image_Factory::rotate($this->record['thumbnail_small'], $degrees)) {
                        return
                            "<b>Error:</b> ".$this->_get_object_name()." '<b>".$this->record['title']."</b>'"
                           ." is of incorrect file type.";
                    }
                    $cs = FileSystem::get_file_checksum($filename);
                    $this->set_field('thumbnail_cs_small', $cs);
                    return
                        "<b>Success:</b> ".$this->_get_object_name()." '<b>".$this->record['title']."</b>'"
                       ." was rotated left.";
                }
                break;
            case "gallery_image_rotate_right":
                $degrees = -90;
                $this->_set_ID(get_var('targetID'));
                if ($this->load() && $this->record['systemID']==SYS_ID) {
                    if (!$filename = Image_Factory::rotate($this->record['thumbnail_small'], $degrees)) {
                        return
                            "<b>Error:</b> ".$this->_get_object_name()." '<b>".$this->record['title']."</b>'"
                           ." is of incorrect file type.";
                    }
                    $cs = FileSystem::get_file_checksum($filename);
                    $this->set_field('thumbnail_cs_small', $cs);
                    return
                        "<b>Success:</b> ".$this->_get_object_name()." '<b>".$this->record['title']."</b>'"
                       ." was rotated right.";
                }
                break;
            case "gallery_image_set_content":
                $this->_set_ID(get_var('targetID'));
                if ($this->load() && $this->record['systemID']==SYS_ID) {
                    $this->set_field('content', get_var('targetValue'));
                    print 'done';
                }
                die();
            break;
            case "gallery_image_set_title":
                $this->_set_ID(get_var('targetID'));
                if ($this->load() && $this->record['systemID']==SYS_ID) {
                    $this->set_field('title', get_var('targetValue'));
                    print 'done';
                }
                die();
            break;
            case "gallery_sequence":
                $parent_type = $this->_get_container_object_type();
                $Obj_parent = new $parent_type(get_var('targetID'));
                $Obj_parent->set_field('childID_csv', get_var('targetValue'));
                print 'done';
                die();
            break;
        }
        $modules = explode(",", str_replace(' ', '', Base::get_modules_installed()));
        foreach ($modules as $module) {
            if (method_exists($module, 'do_submode_extra')) {
                $Obj_Module = new $module;
                $result = $Obj_Module->do_submode_extra();
                if ($result) {
                    return $result;
                }
            }
        }
    }

    public function get_records_matching($args)
    {
        global $YYYY, $MM;
        $Obj_Parent_type =  $this->_get_container_object_type();
        $results = $this->get_records(
            array(
                'YYYY' =>               $YYYY,
                'MM' =>                 $MM,
                'category' =>           $args['filter_category_list'],
                'offset' =>             $this->_filter_offset,
                'category_master' =>    $args['filter_category_master'],
                'memberID' =>           $args['filter_memberID'],
                'personID' =>           $args['filter_personID'],
                'container_path' =>     $args['filter_container_path'],
                'container_subs' =>     $args['filter_container_subs'],
                'results_limit' =>      $args['results_limit'],
                'results_order' =>      $args['results_order']
            )
        );
        $parentTitles = array();
        foreach ($results['data'] as &$result) {
            $result['available'] =    $this->is_available($result);
            if (!array_key_exists($result['parentID'], $parentTitles)) {
                $Obj_Parent = new $Obj_Parent_type($result['parentID']);
                $parentTitles[$result['parentID']] = $Obj_Parent->get_field('title');
            }
            $result['parentTitle'] = $parentTitles[$result['parentID']];
        }
  //    y($results);
        return $results;
    }

    protected function _draw_listings_load_records()
    {
        global $YYYY, $MM;
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
        $results = $this->get_records_matching($args);
        $this->_records =           $results['data'];
        $this->_records_total =     $results['total'];
    }


    protected function _get_records_available()
    {
        $records = $this->_get_records_records;
        $this->_get_records_records = array();
        foreach ($records as $record) {
            if ($this->_isAdmin || $this->is_available($record)) {
                $this->_get_records_records[] = $record;
            }
        }
    }

    protected function _get_records_sort_records()
    {
        if ($this->_get_records_args['container_path']) {
            $this->_get_records_sort_records_by_sequence();
            return;
        }
        $this->_get_records_sort_records_using_results_order();
    }

    protected function _get_records_sort_records_by_sequence()
    {
        if (!$this->_get_records_records) {
            $this->_get_records_sort_records_using_results_order();
            return;
        }
        if (!$this->_get_records_records[0]['parentID']) {
            $this->_get_records_sort_records_using_results_order();
            return;
        }
        $records =          $this->_get_records_records;
        $parentID =         $records[0]['parentID'];
        $Container_Type =   $this->_get_container_object_type();
        $Obj_Container  =   new $Container_Type($parentID);
        $csv =              $Obj_Container->get_field('childID_csv');
        if (!$csv) {
            $this->_get_records_sort_records_using_results_order();
            return;
        }
        $csv_arr =  explode(',', $csv);
        $out =      array();
        foreach ($csv_arr as $ID) {
            foreach ($records as &$item) {
                if ($item['ID']==$ID) {
                    $out[] = $item;
                }
            }
        }
        foreach ($records as &$item) {
            if (!in_array($item['ID'], $csv_arr)) {
                $out[] = $item;
            }
        }
        $this->_get_records_records = $out;
    }

    protected function get_related_products()
    {
        if ($products = parent::get_related_products()) {
            return $products;
        }
        $parentID = $this->get_field('parentID');
        if ($parentID) {
            $type = $this->_get_container_object_type();
            $Obj_parent = new $type($parentID);
            return $Obj_parent->get_related_products();
        }
        return false;
    }

    public function set_container_path()
    {
        $container_path = "";
        $parentID = $this->get_field('parentID');
        if ($parentID) {
            $Container_Type = $this->_get_container_object_type();
            $Obj_Container  = new $Container_Type($parentID);
            $container_path = $Obj_Container->get_field('path');
        }
        $this->set_field('container_path', $container_path);
    }

    public function sequence_append()
    {
        $parentID = $this->get_field('parentID');
        if (!$parentID) {
            return;
        }
        $Container_Type =   $this->_get_container_object_type();
        $Obj_Container  =   new $Container_Type($parentID);
        $childID_csv =      $Obj_Container->get_field('childID_csv');
        $childID_arr =      explode(',', $childID_csv);
        if (!in_array($this->_get_ID(), $childID_arr)) {
            $childID_arr[] =    $this->_get_ID();
            $childID_csv =      implode(',', $childID_arr);
            $Obj_Container->set_field('childID_csv', $childID_csv);
        }
    }

    public function on_action_set_path()
    {
        global $action_parameters;
        $type =     $action_parameters['triggerObject'];
        $ID =       $action_parameters['triggerID'];
        $ID_arr =   explode(',', $ID);
        foreach ($ID_arr as $ID) {
            $Obj =      new $type($ID);
            $Obj->set_container_path();
            $Obj->set_path();
            $Obj->sequence_append();
        }
    }

    public function on_action_delete_pre()
    {
        global $action_parameters;
        $type =     $action_parameters['triggerObject'];
        $ID =       $action_parameters['triggerID'];
        $ID_arr =   explode(',', $ID);
        foreach ($ID_arr as $ID) {
            $Obj =      new $type($ID);
            $Obj->sequence_erase();
        }
    }

    public function get_version()
    {
        return VERSION_POSTING_CONTAINED;
    }
}
