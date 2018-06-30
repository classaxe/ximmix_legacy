<?php
  define ("VERSION_COMPONENT_CONTENT_GROUP_MEMBER_MIRROR","1.0.3");
/*
Version History:
  1.0.3 (2013-11-20)
    1) Component_Content_Group_Member_Mirror::draw() removed support for
       permADMIN and permAPPROVER

  (Older version history in class.component_content_group_member_mirror.txt)
*/
class Component_Content_Group_Member_Mirror extends Component_Base {

  public function draw($instance='', $args=array(), $disable_params=false) {
    $ident =            "content_group_member_mirror";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'page_choices_csv' => array('default'=>'',    'hint'=>'CSV list format: page|group,page|group...')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $page_choice_arr = explode(",",$cp['page_choices_csv']);
    $Obj_Group =    new Group;
    $personID =     get_userID();
    foreach ($page_choice_arr as $page_choice) {
      $show =       true;
      $choice =     explode("|",$page_choice);
      $path =       '//'.trim($choice[0],'/').'/';
      $group =      (isset($choice[1]) ? $choice[1] : false);
      if ($group){
        $show = false;
        if ($groupID = $Obj_Group->get_ID_by_name($group)) {
          $Obj_Group->_set_ID($groupID);
          if ($perms = $Obj_Group->member_perms($personID)) {
            if ($perms['permVIEWER']==1 || $perms['permEDITOR']==1) {
              $show = true;
            }
          }
        }
      }
      if ($show){
        $Obj_Page = new Page;
        $Obj_Page->_set_ID($Obj_Page->get_ID_by_path($path));
        $content = $Obj_Page->get_field('content');
        if ($content!==false) {
          return $out.$content;
        }
        return $out.$ident.": Page not found - ".$path;
      }
    }
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_CONTENT_GROUP_MEMBER_MIRROR;
  }
}
?>