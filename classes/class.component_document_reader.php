<?php
  define ("VERSION_COMPONENT_DOCUMENT_READER","1.0.0");
/*
Version History:
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Document_Reader extends Component_Base {

  function draw($instance='', $args=array(), $disable_params=false) {
    $ident =            "document_reader";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'cover_file' =>           array('match' => '',									'default'=>'',                                    'hint'=>'Path and filename for cover image'),
      'named_pages' =>          array('match' => '',									'default'=>'1|Front Cover,-1|Back Cover',         'hint'=>'csv list of pages'),
      'number_offset' =>        array('match' => '',									'default'=>'0',                                   'hint'=>'Page on which to start numbering'),
      'pages_filepath' =>       array('match' => '',									'default'=>'/online_',                            'hint'=>'path prefix to read'),
      'pages_filetype' =>       array('match' => 'enum|.gif,.jpg,.png',					'default'=>'.png',                                'hint'=>'.gif|.jpg|.png'),
      'pages_per_image' =>      array('match' => '',									'default'=>'1',                                   'hint'=>'pages shown per page image'),
      'pages_total' =>          array('match' => '',									'default'=>'',                                    'hint'=>'total number of pages')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);

    $named_pages_arr =  explode(",",$cp['named_pages']);
    $tmp_page_arr = array();
    foreach ($named_pages_arr as $tmp) {
      $tmp_arr = explode('|',$tmp);
      $tmp_page_arr[] = "    ".pad("'".$tmp_arr[0]."':",6)."'".$tmp_arr[1]."'";
    }
    $cp['named_pages'] = implode(",\n",$tmp_page_arr);

    Page::push_content(
      "javascript",
       "\n// Support for document_reader:\n"
      ."var doc = {\n"
      ."  cover_file:  '".BASE_PATH."img/sysimg/?img=".BASE_PATH.trim($cp['cover_file'],'/')."',\n"
      ."  named_pages: {\n".$cp['named_pages']."\n  },\n"
      ."  number_offset:   ".$cp['number_offset'].",\n"
      ."  pages_filepath:  '".BASE_PATH."img/sysimg/?img=".BASE_PATH.trim($cp['pages_filepath'].'/')."',\n"
      ."  pages_filetype:  '".$cp['pages_filetype']."',\n"
      ."  pages_per_image: ".$cp['pages_per_image'].", \n"
      ."  pages_total:     ".$cp['pages_total']."\n"
      ."}\n"
    );
    $out.=
       "<div id='div_document_reader'>Loading...</div>"
      ."<script type='text/javascript'>document_reader('div_document_reader')</script>";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_DOCUMENT_READER;
  }
}
?>