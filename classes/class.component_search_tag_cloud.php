<?php
  define ("VERSION_COMPONENT_SEARCH_TAG_CLOUD","1.0.0");
/*
Version History:
  1.0.0 (2011-12-31)
    1) Initial release - moved from Component class
*/
class Component_Search_Tag_Cloud extends Component_Base {

  function draw($systemID=false,$color_min='#999999',$color_max='#A30709'){
    global $system_vars;
    $ident =            "search_results"; // This is the ident for the CPs used - we can do that if we want :-)
    $safe_ID =          Component_Base::get_safe_ID($ident,'');
    $parameter_spec =   array(
      'sites_list' =>   array('default'=>$system_vars['URL'], 'hint'=>"CSV list of local site URLs\nThis control uses the setting provided for search results")
    );
    $cp_settings =      Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
    $cp_defaults =      $cp_settings['defaults'];
    $cp =               $cp_settings['parameters'];
    $out =              Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
    $systemIDs_csv =    System::get_IDs_for_URLs($cp['sites_list']);
    $records = Keyword::get_keyword_list_with_weight($systemIDs_csv,2);
    foreach ($records as $record) {
      $out.=
         "<a href=\"".(BASE_PATH."tags/".$record['keyword'])."\""
        ." style=\"font-size:".(100+$record['weight'])."%;"
        ."color:".get_color_for_weight($record['weight'], $color_min, $color_max)
        ."\""
        ." title=\"".$record['keyword']." (".$record['count'].")\">"
        .$record['keyword']
        ."</a> ";
    }
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_SEARCH_TAG_CLOUD;
  }
}
?>