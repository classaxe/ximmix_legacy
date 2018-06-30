<?php
  define ("VERSION_COMPONENT_DOCUMENT_COVERS","1.0.0");
/*
Version History:
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Document_Covers extends Component_Base {

  function draw($instance='', $args=array(), $disable_params=false) {
    $ident =            "document_covers";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'list' =>                 array('match' => '',									'default'=>'',                                    'hint'=>'yyyymm read(y|n),yyyymm read'),
      'columns' =>              array('match' => 'range|1,n',							'default'=>'3',                                   'hint'=>'1..n'),
      'ext' =>                  array('match' => 'enum|.gif,.jpg,.png',					'default'=>'.jpg',                                'hint'=>'.gif|.jpg|.png'),
      'link_prefix' =>          array('match' => '',									'default'=>'/online_',                            'hint'=>'path prefix to read'),
      'link_date_separator' =>  array('match' => '',									'default'=>'_',                                   'hint'=>'-|_'),
      'months_span' =>          array('match' => '',									'default'=>'1',                                   'hint'=>'Number of months each issue spans'),
      'path' =>                 array('match' => '',									'default'=>BASE_PATH.'UserFiles/Image/covers/',   'hint'=>'path to images'),
      'path_date_separator' =>  array('match' => '',									'default'=>'_',                                   'hint'=>'-|_'),
      'width' =>                array('match' => '',									'default'=>'165',                                 'hint'=>'1..x')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);

    $covers_arr =     explode(",",$cp['list']);
    $covers_arr =     array_reverse($covers_arr);
    $thumbs_arr =     array();
    foreach ($covers_arr as $cover) {
      $cover_arr =    explode(" ",$cover);
      $MM =           substr($cover_arr[0],4,2);
      $YYYY =         substr($cover_arr[0],0,4);
      $read =         $cover_arr[1];
      $MM_end =       lead_zero((int)($MM + ($cp['months_span']-1))%12,2);
      $date =
         MM_to_MMM($MM)
        .($cp['months_span'] > 1 ?
            " / "
           .MM_to_MMM($MM_end)
         :  ""
         )
        ." ".$YYYY;
      $title =        ($read==1 ? "Click to read" : "Click to enlarge picture");
      $url =
         "<a href=\"".$cp['link_prefix']
        .$YYYY
        .$cp['link_date_separator']
        .$MM."\" title=\"".$title."\">";
      $picture =
         "<img alt=\"Cover for ".$date." edition\" class='b border_none' "
        ."src=\"".BASE_PATH."img/width/".$cp['width']
        .$cp['path'].$YYYY.$cp['path_date_separator'].$MM.$cp['ext']."\" />";
      $thumbs_arr[] =
         "  <div class='txt_c fl' style='padding-right:5px;'>"
        .$url.$picture.$date."</a></div>\n";
    }
    for ($i=0; $i<(count($thumbs_arr)/$cp['columns'])+$cp['columns']+$cp['columns']; $i+=$cp['columns']){
      $out.=    "<div class='clr_b'>";
      for ($j=0; $j<$cp['columns']; $j++) {
        $out.=  (isset($thumbs_arr[$i+$j]) ? $thumbs_arr[$i+$j] : "");
      }
      $out.=    "</div>";
    }
    $out.= "<div class='clear'></div>";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_DOCUMENT_COVERS;
  }
}
?>