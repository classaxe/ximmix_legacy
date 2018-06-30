<?php
define ("VERSION_COMPONENT_FORM","1.0.0");
/*
Version History:
  1.0.0 (2010-10-12)
    1) Initial release
  0.
*/
class Component_Form extends Component_Base {
  function draw($instance='', $args=array(), $disable_params=false) {
    $ident =        "component_form";
    $parameter_spec = array(
      'report_name' =>      array('match' => '',            'default'=>'form-name',     'hint'=>'Name of report form here'),
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $ID =           Component_Base::get_safe_ID($ident,$instance);
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $Obj_Report_Form = new Report_Form;
    if (!$Obj_Report_Form->exists_named($cp['report_name'])){
      $out.= "<b>Error:</b> No such report form as '".$cp['report_name']."'";
      return $out;
    }
    $out.=
       "<div class='txt_c'>\n"
      .$Obj_Report_Form->draw($cp['report_name'],'')
      ."<div style='background-color:#707070'>\n"
      ."  <input type='button' value='Submit' onclick=\"geid('submode').value='save';geid('form').submit();\" class='formbutton' style='width: 60px;'/>\n"
      ."</div>\n"
      ."</div>\n";
    $msg =          get_var('msg');
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_FORM;
  }

}
?>