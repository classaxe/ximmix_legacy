<?php
  define ("VERSION_COMPONENT_PERSONS_MAP","1.0.1");
/*
Version History:
  1.0.1 (2013-10-28)
    1) Added CP for filter_sp to filter on state / province
    2) CP for filter_category now defaults to '*' meaning everything

  (Older version history in class.component_persons_map.txt)
*/
class Component_Persons_Map extends Component_Base {
  protected $_person_IDs;
  protected $_Obj_Person;

  public function __construct(){
    $this->_ident =             "persons_map";
    $this->_parameter_spec =    array(
      'filter_category' =>  array('match' => 'html',        'default'=>'',                  'hint'=>'Category to filter by'),
      'filter_sp' =>        array('match' => 'html',        'default'=>'',                  'hint'=>'CSV list of States and Provinces to filter on'),
      'height' =>           array('match' => 'range|100,n', 'default'=>'600',               'hint'=>'Height of map and listings panel'),
      'map_title' =>        array('match' => 'html',        'default'=>'Person addresses',  'hint'=>'Label to use for items shown'),
      'maximize' =>         array('match' => 'range|0,1',   'default'=>'0',                 'hint'=>'Whether or not to maximize size'),
      'list_fixed_height' =>array('match' => 'range|0,1',   'default'=>'1',                 'hint'=>'Whether or not to limit the list to the overall height'),
      'show_home' =>        array('match' => 'range|0,1',   'default'=>'1',                 'hint'=>'Whether or not to include home addresses'),
      'show_home_phone' =>  array('match' => 'range|0,1',   'default'=>'1',                 'hint'=>'Whether or not to include home phone number'),
      'show_work' =>        array('match' => 'range|0,1',   'default'=>'1',                 'hint'=>'Whether or not to include work addresses'),
      'show_work_phone' =>  array('match' => 'range|0,1',   'default'=>'1',                 'hint'=>'Whether or not to include work phone number'),
      'width' =>            array('match' => 'range|100,n', 'default'=>'600',               'hint'=>'Width of map and listings panel')
    );
  }

  function draw($instance='',$args=array(),$disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_draw_css();
    $this->_draw_map();
    return $this->_html;
  }

  protected function _draw_css(){
    if (!$this->_cp['list_fixed_height']){
      return;
    }
    $css =
      "#google_map_".$this->_safe_ID."_listing { height: ".$this->_cp['height']."px; overflow:auto; }\n";
    Page::push_content('style',$css);
  }

  protected function _draw_map(){
    $this->_html.=          $this->_Obj_Person->draw_object_map_html($this->_safe_ID);
  }

  protected function _setup($instance,$args,$disable_params){
    parent::_setup($instance,$args,$disable_params);
    $this->_setup_load_person_IDs();
    $this->_setup_person_map_post_variables();
  }

  protected function _setup_load_person_IDs(){
    $this->_Obj_Person =   new Person;
    $this->_Obj_Person->set_group_concat_max_len(1000000);
    $sp_csv = implode("','",explode(',',str_replace(' ','',$this->_cp['filter_sp'])));
    $sql =
       "SELECT\n"
      ."  GROUP_CONCAT(`ID` ORDER BY `NFirst`,`NLast`) `ID_CSV`\n"
      ."FROM\n"
      ."  `person`\n"
      ."WHERE\n"
      .($this->_cp['filter_category'] && $this->_cp['filter_category']!='*' ?
          "  `category` LIKE \"%".$this->_cp['filter_category']."%\" AND\n"
       :
          ""
       )
      .($this->_cp['filter_sp'] && $this->_cp['filter_sp']!='*' ?
          ($this->_cp['show_home']  && !$this->_cp['show_work'] ? "  `ASpID` IN('".$sp_csv."') AND\n" : "")
         .(!$this->_cp['show_home'] &&  $this->_cp['show_work'] ? "  `WSpID` IN('".$sp_csv."') AND\n" : "")
         .($this->_cp['show_home']  &&  $this->_cp['show_work'] ? " (`ASpID` IN('".$sp_csv."') OR `WSpID` IN('".$sp_csv."')) AND\n" : "")
       :
          ""
       )
      ."  `systemID` = ".SYS_ID;
    $this->_person_IDs =        $this->_Obj_Person->get_field_for_sql($sql);
  }

  protected function _setup_person_map_post_variables(){
    $_POST['ID'] =              $this->_person_IDs;
    $_POST['height'] =          $this->_cp['height'];
    $_POST['width'] =           $this->_cp['width'];
    $_POST['map_title'] =       $this->_cp['map_title'];
    $_POST['show_home'] =       $this->_cp['show_home'];
    $_POST['show_home_phone'] = $this->_cp['show_home_phone'];
    $_POST['show_work'] =       $this->_cp['show_work'];
    $_POST['show_work_phone'] = $this->_cp['show_work_phone'];
    if($this->_cp['show_home'] && $this->_cp['show_work']){
      $_POST['lat_field'] =   'AMap_lat,WMap_lat';
      $_POST['lon_field'] =   'AMap_lon,WMap_lon';
      $_POST['loc_field'] =   'AMap_description,WMap_description';
    }
    else if($this->_cp['show_home'] && !$this->_cp['show_work']){
      $_POST['lat_field'] =   'AMap_lat';
      $_POST['lon_field'] =   'AMap_lon';
      $_POST['loc_field'] =   'AMap_description';
    }
    else if(!$this->_cp['show_home'] && $this->_cp['show_work']){
      $_POST['lat_field'] =   'WMap_lat';
      $_POST['lon_field'] =   'WMap_lon';
      $_POST['loc_field'] =   'WMap_description';
    }
    else {
      $_POST['lat_field'] =   '';
      $_POST['lon_field'] =   '';
      $_POST['loc_field'] =   '';
    }
    $_POST['maximize'] =    $this->_cp['maximize'];
  }

  public function get_version(){
    return VERSION_COMPONENT_PERSONS_MAP;
  }
}
?>