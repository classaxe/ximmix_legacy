<?php
  define ("VERSION_COMPONENT_GOOGLE_MAP","1.0.0");
/*
Version History:
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Google_Map extends Component_Base {

  function draw($instance='',$args=array(), $disable_params=false) {
    $ident = "google_map";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'control_large' =>                array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'control_overview' =>             array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'control_scale' =>                array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'control_small' =>                array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'control_type' =>                 array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'control_zoom' =>                 array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'control_zoom_ondblclick' =>      array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'control_zoom_onscrollwheel' =>   array('match' => 'enum|0,1',		'default'=>0,     'hint'=>'0|1'),
      'map_lat' =>                      array('match' => '',				'default'=>0,     'hint'=>'decimimal latitude'),
      'map_lon' =>                      array('match' => '',				'default'=>0,     'hint'=>'decimimal longitude'),
      'map_height' =>                   array('match' => '',				'default'=>420,   'hint'=>'n'),
      'map_width' =>                    array('match' => '',				'default'=>600,   'hint'=>'n'),
      'map_zoom' =>                     array('match' => 'range|0,19',		'default'=>13,    'hint'=>'0..19'),
      'marker_html' =>                  array('match' => '',				'default'=>'',    'hint'=>'html text for marker')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);

    $Obj_Map = new Google_Map($instance,SYS_ID);
    $Obj_Map->map_load();
    $Obj_Map->map_centre($cp['map_lat'],$cp['map_lon'],$cp['map_zoom']);
    if ($cp['marker_html']){
      $Obj_Map->add_marker_with_html($cp['map_lat'],$cp['map_lon'],$cp['marker_html'],"mymarker");
    }
    else {
      $Obj_Map->add_marker($cp['map_lat'],$cp['map_lon']);
    }
    $args =
       array(
         'control_large' =>             $cp['control_large'],
         'control_overview' =>          $cp['control_overview'],
         'control_scale' =>             $cp['control_scale'],
         'control_small' =>             $cp['control_small'],
         'control_type' =>              $cp['control_type'],
         'control_zoom' =>              $cp['control_zoom'],
         'control_zoom_ondblclick' =>   $cp['control_zoom_ondblclick'],
         'control_zoom_onscrollwheel' =>$cp['control_zoom_onscrollwheel'],
         'map_height' =>                $cp['map_height'],
         'map_width' =>                 $cp['map_width']
       );
    $out.= $Obj_Map->draw($args);
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_GOOGLE_MAP;
  }
}
?>