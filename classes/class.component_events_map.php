<?php
  define("VERSION_COMPONENT_EVENTS_MAP", "1.0.2");
/*
Version History:
  1.0.2 (2015-01-31)
    1) Changes to internally used parameters in Component_Events_Map::_setup_load_event_IDs():
         Old: limit,         order_by
         New: results_limit, results_order
    2) Now PSR-2 Compliant

  (Older version history in class.component_events_map.txt)
*/
class Component_Events_Map extends Component_Base
{
    protected $_event_IDs;
    protected $_Obj_Event;

    public function __construct()
    {
        $this->_ident =             "events_map";
        $this->_parameter_spec = array(
            'filter_category_list' =>       array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       '*|CSV value list'
            ),
            'filter_category_master' =>     array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'filter_date_duration' =>       array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'If filter_what is future or past, this further limits to given date range'
            ),
            'filter_date_units' =>          array(
                'match' =>      'enum|,day,week,month,quarter,year',
                'default' =>    '',
                'hint' =>
                    ' |day|week|month|quarter|year -'
                   .' If filter_what is future or past, this provides units for given date range'
            ),
            'filter_important' =>           array(
                'match' =>      'enum|,0,1',
                'default' =>    '',
                'hint' =>       'Blank to ignore, 0 for not important, 1 for important'
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
            'filter_range_address' =>       array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Address to search for to obtain lat and lon'
            ),
            'filter_range_distance' =>      array(
                'match' =>      'range|0,n',
                'default' =>    '',
                'hint' =>       'Limits results to those events occuring within this distance of given location'
            ),
            'filter_range_lat' =>           array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Latitude of search point'
            ),
            'filter_range_lon' =>           array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Longitude of search point'
            ),
            'filter_range_units' =>         array(
                'match' =>      'enum|km,mile',
                'default' =>    'km',
                'hint' =>       'Units of measurement to search point'
            ),
            'filter_what' =>                array(
                'match' =>      'enum|all,future,month,past,year',
                'default' =>    'month',
                'hint' =>       'all|future|month|past'
            ),
            'height' =>                     array(
                'match' =>      'range|100,n',
                'default' =>    '600',
                'hint' =>       'Height of map and listings panel'
            ),
            'list_fixed_height' =>          array(
                'match' =>      'range|0,1',
                'default' =>    '1',
                'hint' =>       'Whether or not to limit the list to the overall height'
            ),
            'list_width' =>                 array(
                'match' =>      'range|100,n',
                'default' =>    '315',
                'hint' =>       'Width for listing column'
            ),
            'map_title' =>                  array(
                'match' =>      'html',
                'default' =>    'Person addresses',
                'hint' =>       'Label to use for items shown'
            ),
            'maximize' =>                   array(
                'match' =>      'range|0,1',
                'default' =>    '0',
                'hint' =>       'Whether or not to maximize size'
            ),
            'results_limit' =>              array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'width' =>                      array(
                'match' =>      'range|100,n',
                'default' =>    '600',
                'hint' =>       'Width of map and listings panel'
            )
        );
    }

    public function draw($instance = '', $args = array(), $disable_params = false)
    {
        $this->_setup($instance, $args, $disable_params);
        $this->_draw_control_panel(true);
        $this->_draw_css();
        $this->_draw_map();
        return $this->_html;
    }

    protected function _draw_css()
    {
        if (!$this->_cp['list_fixed_height']) {
            return;
        }
        $css =
             "#google_map_".$this->_safe_ID."_listing {\n"
            ."  height: ".$this->_cp['height']."px; width: ".$this->_cp['list_width']."px; overflow:auto;\n"
            ."}\n"
            ."#google_map_".$this->_safe_ID."_listing .when {\n"
            ."  display:inline-block; width:10.5em; font-weight: 900;\n"
            ."}\n";
        Page::push_content('style', $css);
    }

    protected function _draw_map()
    {
        $this->_html.=          $this->_Obj_Event->draw_object_map_html($this->_safe_ID);
    }

    protected function _setup($instance, $args, $disable_params)
    {
        parent::_setup($instance, $args, $disable_params);
        $this->_Obj_Event =   new Event;
        $this->_filter_offset =     (isset($_REQUEST['offset']) ? $_REQUEST['offset'] : 0);
        $this->_setup_load_event_IDs();
        $this->_setup_person_map_post_variables();
    }

    protected function _setup_load_event_IDs()
    {
        global $YYYY, $MM;
        $this->_Obj_Event->set_group_concat_max_len(1000000);
        $args = array(
            'byRemote' =>
                false,
            'category' =>
                $this->_cp['filter_category_list'],
            'category_master' =>
                (isset($this->_cp['filter_category_master']) ?    $this->_cp['filter_category_master'] : false),
            'container_path' =>
                (isset($this->_cp['filter_container_path']) ?     $this->_cp['filter_container_path'] : ''),
            'container_subs' =>
                (isset($this->_cp['filter_container_subs']) ?     $this->_cp['filter_container_subs'] : ''),
            'DD' =>
                '',
            'filter_date_duration' =>
                (isset($this->_cp['filter_date_duration']) ?      $this->_cp['filter_date_duration'] : ''),
            'filter_date_units' =>
                (isset($this->_cp['filter_date_units']) ?         $this->_cp['filter_date_units'] : ''),
            'filter_range_address' =>
                (isset($this->_cp['filter_range_address']) ?      $this->_cp['filter_range_address'] : ''),
            'filter_range_distance' =>
                (isset($this->_cp['filter_range_distance']) ?     $this->_cp['filter_range_distance'] : ''),
            'filter_range_lat' =>
                (isset($this->_cp['filter_range_lat']) ?          $this->_cp['filter_range_lat'] : ''),
            'filter_range_lon' =>
                (isset($this->_cp['filter_range_lon']) ?          $this->_cp['filter_range_lon'] : ''),
            'filter_range_units' =>
                (isset($this->_cp['filter_range_units']) ?        $this->_cp['filter_range_units'] : ''),
            'important' =>
                (isset($this->_cp['filter_important']) ?          $this->_cp['filter_important'] : ''),
            'memberID' =>
                (isset($this->_cp['filter_memberID']) ?           $this->_cp['filter_memberID'] : ''),
            'MM' =>
                $MM,
            'offset' =>
                $this->_filter_offset,
            'personID' =>
                (isset($this->_cp['filter_personID']) ?           $this->_cp['filter_personID'] : ''),
            'results_limit' =>
                $this->_cp['results_limit'],
            'results_order' =>
                (isset($this->_cp['results_order']) ?             $this->_cp['results_order'] : 'date'),
            'what' =>
                (isset($this->_cp['filter_what']) ?               $this->_cp['filter_what'] : 'all'),
            'YYYY' =>
                $YYYY
        );
        $results = $this->_Obj_Event->get_records($args);
        $this->_records =           $results['data'];
        $this->_records_total =     $results['total'];
        $IDs = array();
        foreach ($this->_records as $r) {
            $IDs[] = $r['ID'];
        }
        $this->_event_IDs =        implode(',', $IDs);
    }

    protected function _setup_person_map_post_variables()
    {
        $_POST['ID'] =              $this->_event_IDs;
        $_POST['height'] =          $this->_cp['height'];
        $_POST['width'] =           $this->_cp['width'];
        $_POST['map_title'] =       $this->_cp['map_title'];
        $_POST['lat_field'] =       'map_lat';
        $_POST['lon_field'] =       'map_lon';
        $_POST['loc_field'] =       'map_description';
        $_POST['maximize'] =        $this->_cp['maximize'];
    }

    public function get_version()
    {
        return VERSION_COMPONENT_EVENTS_MAP;
    }
}
