<?php
  define ("VERSION_COMPONENT_FOLDER_VIEWER","1.0.0");
/*
Version History:
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Folder_Viewer extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false) {
    // CANNOT be instance-namable - or get_file fails
    $ident =            "folder_viewer";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'dir' =>          array('match' => '',		'default'=>'/UserFiles/File/',    'hint'=>'Directory'),
      'folder' =>       array('match' => '',		'default'=>'Files',               'hint'=>'Label to place on top-level folder')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $path =     './'.trim($cp['dir'],'./').'/';
    if (!is_dir($path)){
      $out.= "<b>Error:</b> ".$cp['dir']." is not a valid path.";
      return $out;
    }
    $Obj_FS =   new FileSystem;
    $dirtree =  $Obj_FS->get_dir_tree($path);
    $out.=      $Obj_FS->draw_dir_tree($dirtree,0,0,$cp['folder'],0);
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_FOLDER_VIEWER;
  }
}
?>