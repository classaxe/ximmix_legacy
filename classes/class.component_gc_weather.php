<?php
define ("VERSION_COMPONENT_GC_WEATHER","1.0.0");
/*
Version History:
  1.0.0 (2010-12-11)
    1) Initial release
  0.
*/
class Component_GC_Weather extends Component_Base {
  public function draw_current_conditions($instance='', $args=array(), $disable_params=false){
    $ident =            "gc_weather_current_conditions";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'ident' =>    array('match' => '',    'default'=>'Current Conditions from Environment Canada', 'hint'=>'Message to appear on link back to Environment Canada'),
      'station' =>  array('match' => '',    'default'=>'on-143',                                     'hint'=>'Weather office - e.g. on-43, on-145'),
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $Obj_CG_Weather = new GC_Weather;
    $out.=          $Obj_CG_Weather->draw_current_conditions($cp['station'],$cp['ident']);
    return $out;
  }

  public function draw_long_range_forecast($instance='', $args=array(), $disable_params=false){
    $ident =            "gc_weather_long_range_forecast";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'ident' =>    array('match' => '',    'default'=>'Forecast from Environment Canada ', 'hint'=>'Message to appear on link back to Environment Canada'),
      'station' =>  array('match' => '',    'default'=>'on-143',                            'hint'=>'Weather office - e.g. on-43, on-145'),
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $Obj_CG_Weather = new GC_Weather;
    $out.=          $Obj_CG_Weather->draw_long_range_forecast($cp['station'],$cp['ident']);
    return $out;
  }

  public function draw_radar($instance='', $args=array(), $disable_params=false){
    $ident =            "gc_weather_radar";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'ident' =>    array('match' => '',                                                                    'default'=>'Current Conditions from Environment Canada Weather Radar',  'hint'=>'Message to appear on link back to Environment Canada'),
      'station' =>  array('match' => 'enum|XFW,XWL,WBI,XDR,WSO,XFT,WKR,WGJ,XTI,XNI,WMB,XLA,WMN,XAM,WVY',    'default'=>'WKR',                                                       'hint'=>'Radar Station - e.g. WKR'),
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $Obj_CG_Weather = new GC_Weather;
    $out.=          $Obj_CG_Weather->draw_radar($cp['station'],$cp['ident']);
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_GC_WEATHER;
  }
}
?>