<?php
define('VERSION_FCK','1.0.22');
/*
Version History:
  1.0.22 (2014-04-17)
    1) Changes to include indenting rules for parsed code

  (Older version history in class.fck.txt)
*/


class FCK extends Record{
  function attach_ckfinder(){
    static $ckfinder_js_included = false;
    if (!$ckfinder_js_included){
      Page::push_content('javascript_top',"<script type='text/javascript' src='".BASE_PATH."js/ckfinder/ckfinder.js'></script>\n");
      $ckfinder_js_included = true;
    }
  }
  function do_fck() {
    switch (get_var('submode')){
      case "ecl":
        header("Content-type: application/json");
        print FCK::draw_plugin_ecl();
        die;
      break;
      case "transformer":
        Transformer::admin();
        die();
      break;
    }
  }

  function draw_editor($field,$value,$width,$height,$toolbar="Page"){
    static $js_included = false;
    if (!$js_included){
      $js_included=true;
      Page::push_content(
        'javascript_top',
         "<script type=\"text/javascript\""
        ." src=\"".BASE_PATH."sysjs/ckeditor/ckeditor.js\">"
        ."</script>\n"
      );
    }
    $sanitized = str_replace('[','{{[}}',str_replace('textarea','sanitizedtextarea',$value));
    $jq_field =   str_replace(array('.',':'),array('\\\\.','\\\\:'),$field);
    Page::push_content(
      'javascript_onload',
       "  \$J('#".$jq_field."')[0].value=".json_encode($sanitized).";\n"
      ."  CKEDITOR.timestamp = '".VERSION_FCK."';\n"
      ."  ckeditor_".$field." = CKEDITOR.replace( \"".$field."\", { toolbar: '".$toolbar."',height: 0});\n"
      ."  ckeditor_".$field.".on('instanceReady',\n"
      ."    function(e) {\n"
      ."      var instance = e.editor;\n"
      ."      instance.setData(instance.getData().replace(/\{{\[}}/g,'[').replace(/sanitizedtextarea/g,'textarea'));\n"
      ."      instance.resize("
      .(preg_match('/%/',$width) ? "'".$width."'" : (int)$width)
      .","
      .(preg_match('/%/',$height) ? "'".$height."'" : (int)$height)
      .");\n"
      ."      var rules = {\n"
      ."        indent: true,\n"
      ."        breakBeforeOpen: true,\n"
      ."        breakAfterOpen: true,\n"
      ."        breakBeforeClose: true,\n"
      ."        breakAfterClose: false\n"
      ."      }\n"
      ."      instance.dataProcessor.writer.indentationChars = '  ';\n"
      ."      instance.dataProcessor.writer.setRules( 'p',rules);\n"
      ."      instance.dataProcessor.writer.setRules( 'div',rules);\n"
      ."    }\n"
      ."  );\n"
    );

    return
       "<textarea id=\"".$field."\" name=\"".$field."\" style='display:none;' rows=\"4\" cols=\"80\">"
//       .$sanitized
       ."</textarea>\n";

  }

  function draw_plugin_ecl() {
    $Obj =      new ECL_Tag;
    $out =      array();
    $tags =     $Obj->get_all();
    for($i=0; $i<count($tags['nameable']); $i++){
      $out[] = array($tags['tag'][$i], $tags['text'][$i], $tags['nameable'][$i]);
    }
	return json_encode($out);
  }

  public function get_version(){
    return VERSION_FCK;
  }
}
?>