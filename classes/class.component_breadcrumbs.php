<?php
define ("VERSION_COMPONENT_BREADCRUMBS","1.0.4");
/*
Version History:
  1.0.4 (2012-11-28)
    1) Component_Breadcrumbs::draw() now uses System::get_item_version() not
       System::get_version() as before

  (Older version history in class.component_breadcrumbs.txt)

*/
class Component_Breadcrumbs extends Component_Base {
  function draw($instance='', $args=array(), $disable_params=false) {
    global $page_vars, $selectID;
    $ident =            "breadcrumbs";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'skin' =>                     array('match' => 'range|0,1',  	'default'=>'0',         'hint'=>'0..1'),
      'color_background' =>         array('match' => '',            'default'=>'FFFFFF',    'hint'=>'Colour for background on which breadcrumbs sit'),
      'color_button' =>             array('match' => '',            'default'=>'004584',    'hint'=>'csv list of hex colour codes'),
      'color_button_over' =>        array('match' => '',            'default'=>'007CF0',    'hint'=>'csv list of hex colour codes'),
      'color_house' =>              array('match' => '',            'default'=>'FFFFFF',    'hint'=>'csv list of hex colour codes'),
      'color_house_over' =>         array('match' => '',            'default'=>'FFFF40',    'hint'=>'csv list of hex colour codes'),
      'color_text' =>               array('match' => '',            'default'=>'FFFFFF',    'hint'=>'csv list of hex colour codes'),
      'color_text_over' =>          array('match' => '',            'default'=>'FFFFFF',    'hint'=>'csv list of hex colour codes')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    if ($cp['skin']){
      $css =
         BASE_PATH.'css/breadcrumbs/'
        .System::get_item_version('css_breadcrumbs').'/'
        .$cp['color_button'].','
        .$cp['color_button_over'].','
        .$cp['color_background'].','
        .$cp['color_house'].','
        .$cp['color_house_over'].','
        .$cp['color_text'].','
        .$cp['color_text_over']
        ;
      Page::push_content("style_include","<link rel=\"stylesheet\" type=\"text/css\" href=\"".$css."\" />");
    }
    $URL = Page::get_URL($page_vars);
    $URL =
      (substr($URL,0,strlen(BASE_PATH))==BASE_PATH ?
         substr($URL,strlen(BASE_PATH))
       :
         $URL
      );
    $path_arr = array();
    $url_arr = explode("/",$URL);
    $path = "";
    $path_arr[] = "  <li class='root'><a href='/".trim(BASE_PATH,'/')."'>Home</a></li>\n";
    if ($URL=='home') {
      return
         $out
        ."<ul id='breadcrumbs' class='breadcrumbs'>\n"
        .implode('',$path_arr)
        ."</ul>\n";
    }
    for ($i=0; $i<count($url_arr); $i++) {
      $u = $url_arr[$i];
      $path.= trim($u,'/').'/';
      $lbl = get_title_for_path($u);
      if ($path == 'register/' && $selectID){
        $Obj_Event = new Event($selectID);
        $Obj_Event->load();
        $path_arr[] =
           "  <li class='sub'><a href='/".trim(BASE_PATH.$path,'/')."?selectID=".$selectID."'>"
          ."Registering for Event '".title_case_string($Obj_Event->record['title'])."'"
          ." (".$Obj_Event->record['effective_date_start'].")</a></li>\n";
      }
      else {
        if ($i!=0 || $u!='home'){
          $path_arr[] =
            "  <li class='sub'><a href='/".trim(BASE_PATH.$path,'/')."'>".$lbl."</a></li>\n";
        }
      }
    }
    return
       $out
      ."<ul id='breadcrumbs' class='breadcrumbs'>\n"
      .implode('',$path_arr)
      ."</ul>\n";
  }

  public function get_version(){
    return VERSION_COMPONENT_BREADCRUMBS;
  }

}
?>