<?php
  define ("VERSION_COMPONENT_GROUP_MEMBER_REDIRECTOR","1.0.1");
/*
Version History:
  1.0.1 (2013-11-20)
    1) Component_Group_Member_Redirector::draw() removed support for
       permADMIN and permAPPROVER
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Group_Member_Redirector extends Component_Base {

  function draw($instance='',$args=array(), $disable_params=false) {
    global $component_help; // To prevent redirect if help is on
    $ident =            "group_member_redirector";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'group_member_pages' => array('match' => '',		'default'=>'',    'hint'=>'CSV list format: page|group,page|group...'),
      'default_page' =>       array('match' => '',		'default'=>'',    'hint'=>'Page to show if no others match')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =
       Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults)
      ."<br />\n"
      ."<b>Note to administrator</b>: this component would normally force a redirect.<br />";
    if ($component_help==1){
      return $out;
    }
    $personID =       get_userID();
    $options =    explode(",",$cp['group_member_pages']);
    $default =        false;
    foreach($options as $option) {
      $bits =         explode("|",$option);
      if (count($bits)==2) {
        $page =       $bits[0];
        $group =      $bits[1];
        $Obj_Group =  new Group;
        $groupID =    $Obj_Group->get_ID_by_name($group);
        if ($groupID!==false) {
          $Obj_Group->_set_ID($groupID);
          $perms =      $Obj_Group->member_perms($personID);
          if (!$perms===false) {
            if ($perms['permVIEWER']==1 || $perms['permEDITOR']==1) {
              header("Location: ".BASE_PATH.$page);
              return;
            }
          }
        }
      }
    }
    // still here?   See if we had a default page.
    if ($cp['default_page']!='') {
      $page = $cp['default_page'];
      header("Location: ".BASE_PATH.$page);
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_GROUP_MEMBER_REDIRECTOR;
  }
}
?>