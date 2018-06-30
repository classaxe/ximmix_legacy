<?php
define('VERSION_AJAX','1.0.23');
/*
http://laptop.cicbv.ca/ajax/?rs=serve_lookup_report&rst=&rsrnd=12342c424&rsargs[]=1933367189&rsargs[]=%60PUsername%60&rsargs[]=3&rsargs[]=an
http://laptop.cicbv.ca/ajax/?rs=serve_config&rsrnd=12342c424&rsargs[]=http://www.ecclesiact.com
http://laptop.ilynx.ca/ajax/?rs=serve_shipping&rst=&rsrnd=1192135476767&rsargs[]=FEDEX&rsargs[]=%3C%3Fxml%20version%3D%221.0%22%20encoding%3D%22utf-8%22%20%3F%3E%0A%3Cship%3E%0A%20%20%3CSAddress1%3E264%20Conestoga%20Avenue%3C/SAddress1%3E%0A%20%20%3CSAddress2%3E%3C/SAddress2%3E%0A%20%20%3CSCity%3ERichmond%20Hill%3C/SCity%3E%0A%20%20%3CSPostal%3EL4C%202H2%3C/SPostal%3E%0A%20%20%3CSSpID%3E%3C/SSpID%3E%0A%20%20%3CSCountryID%3ECAN%3C/SCountryID%3E%0A%3Citems%3E1369016121%3C/items%3E%0A%3C/ship%3E%0A
http://testportal.auroraonline.com/cicbv/ajax/?rs=serve_lookup_report&rst=&rsrnd=12342c424&rsargs[]=1933367189&rsargs[]=%60PUsername%60&rsargs[]=3&rsargs[]=an


Version History:
  1.0.23 (2014-03-18)
    1) Ajax::get_report() now accepts

  (Older version history in class.ajax.txt)
*/
class Ajax {
  static $control_num = 1;

  function __construct() {
  }

  public static function generate_control_num(){
    return Ajax::$control_num++;
  }

  public function get_config($url){
    global $system_vars;
    $control_num = Ajax::generate_control_num();
    $build_version = System::get_item_version('codebase').".".$system_vars['db_version'];
    return
       "<script type=\"text/javascript\">\n"
      ."//<![CDATA[\n"
      ."ajax_post('/ajax/', 'ajax_".$control_num."', 'rs=serve_config&rsargs[]=' + '".$url."',\n"
      ."  function (response) {\n"
      ."    sajax.config_display(eval(response), 'ajax_result_".$control_num."','".$build_version."');\n"
      ."  }\n"
      .");\n"
      ."//]]>\n"
      ."</script>"
      ."<div id=\"ajax_result_$control_num\"></div>";
    return implode('',$out);
  }

  public function get_config_rows() {
    static $config;
    if (!isset($config)) {
      $Obj = new System(SYS_ID);
      $config_rows = $Obj->get_config();
      $config = array();
      $config_filtered = array();
      $ignore =
        explode(
          ',',
           'classes_cs_target,classes_detail,codebase_version,db_detail,db_cs_target,db_version,'
          .'icons_version,labels_version,libraries_cs_target,libraries_detail,reports_detail,reports_cs_target'
        );
      foreach ($config_rows as $config_item) {
        if (!in_array($config_item['title'],$ignore)){
          $config_filtered[] = $config_item;
        }
      }
      foreach ($config_filtered as $config_item) {
        if ($config_item['category']=='config') {
          $config[] = $config_item['title'];
        }
      }
    }
    $out =
       "<div><script type=\"text/javascript\">\n"
      ."var config_rows = [\"".implode("\",\"",$config)."\"];\n"
      ."</script>\n"
      ."<table cellpadding='2' cellspacing='0' class='report'>\n";
    foreach($config as $row) {
      $out.=
         "  <tr>\n"
        ."    <th class='txt_l' style='background-color: #e8e8e8;'>"
        .$row
        ."</th>\n"
        ."  </tr>\n";
    }
    $out.= "</table></div>\n";
    return $out;
  }

  public function get_report(
      $_field='',
      $_control_num,
      $_report_name,
      $_report_filter,
      $_report_matchmode='Contains',
      $_linked_field='',
      $_displayed_field='',
      $_autocomplete=0,
      $_row_js='',
      $_onematch_js='',
      $_nomatch_js=''
    ){
    $Obj_Report = new Report;
    $match_typeID = Report::get_match_mode($_report_matchmode);
    if ($match_typeID=="") {
      return "<b>Error:</b> Ajax::report() Invalid match type '".$_report_matchmode."'";
    }
    $reportID = $Obj_Report->get_ID_by_name($_report_name);
    if ($reportID=="") {
      return "<b>Error:</b> Ajax::report() Invalid report name '".$_report_name."'";
    }
    $Obj_Report->_set_ID($reportID);
    $columns = $Obj_Report->get_columns();
    $fields = array();
    $modes =  array();
    if (trim(substr($_report_filter,0,9)=='<options>')){
      $doc = simplexml_load_string($_report_filter);
      foreach($doc->option as $o){
        $fields[] = (string)$o->filter;
        $modes[] =  ((string)$o->mode ? Report::get_match_mode((string)$o->mode) : $this->_args['report_matchmode']);
      }
    }
    else {
      $fields[] = $_report_filter;
      $modes[] =  $match_typeID;
    }
    // Begin assembling output:
    Page::push_content(
      'javascript',
       "sajax.request_".$_control_num." = function(){\n"
      ."  sajax.lookup_wait(".$_control_num.",1);\n"
      ."  sajax.call_".$_control_num."(geid_val('".($_displayed_field!='' ? 'x_' : '').$_field."'));\n"
      ."}\n"
      ."sajax.call_".$_control_num." = function(value) {\n"
      ."  var idx = (geid('q_".$_field."') ? geid('q_".$_field."').selectedIndex : 0) ;\n"
      ."  var fields = [\n"
      ."    [\"".implode("\"],\n    [\"",$fields)."\"]\n"
      ."  ];\n"
      ."  var modes = [\n"
      ."    [\"".implode("\"],\n    [\"",$modes)."\"]\n"
      ."  ];\n"
      ."  sajax.lookup_handler(".$reportID.",fields[idx],modes[idx],value,sajax.handler_".$_control_num.");\n"
      ."}\n"
      ."sajax.handler_".$_control_num." = function(result) {\n"
      ."  var out = [];\n"
      ."  switch(parseInt(result[0])) {\n"
      ."    case -1:\n"
      ."      sajax.lookup_helper(".$_control_num.",0);\n"
      ."      geid('ajax_extra_".$_control_num."').innerHTML=\"(\"+result[1]+\" matches - enter more text)\";\n"
      ."      geid('ajax_result_".$_control_num."').innerHTML = \"\";\n"
      ."    break;\n"
      ."    case 0:\n"
      ."      sajax.lookup_helper(".$_control_num.",0);\n"
      ."      geid('ajax_extra_".$_control_num."').innerHTML=\"(No match found)\";\n"
      ."      geid('ajax_result_".$_control_num."').innerHTML=\"\";\n"
      .($_nomatch_js!='' ? $_nomatch_js.";\n" : "")
      ."    break;\n"
      ."    default:\n"
      ."      sajax.lookup_helper(".$_control_num.",1);\n"
      ."      if (parseInt(result[0])==1){\n"
      .($_autocomplete ?
          "        geid_set('".$_field."',result[1]['".$_linked_field."']);\n"
         ."        geid('ajax_extra_".$_control_num."').innerHTML=\"(Value selected)\";\n"
         ."        sajax.lookup_helper(".$_control_num.",0);\n"
         .($_displayed_field=='' || $_displayed_field==$_linked_field ?
           ""
          :
           "        geid_set('x_".$_field."',result[1]['".$_displayed_field."']);\n"
          )
       :
         "        geid('ajax_extra_".$_control_num."').innerHTML=\"(One match found)\";\n"
      )
      .($_onematch_js!='' ? "        ".$_onematch_js."//one match\n" : "")
      ."      }\n"
      ."      else {\n"
      ."        geid('ajax_extra_".$_control_num."').innerHTML=\"(\"+result[0]+\" matches found)\";\n"
      ."      }\n"
      ."      out.push(\n"
      ."        \"<table class='ajax_report'>\\n\"+\n"
      ."        \"  <thead>\\n\"+\n"
      ."        \"    <tr class='head'>\\n\"+\n"
      .Ajax::get_report_heads($columns)
      ."        \"    <\/tr>\\n\"+\n"
      ."        \"  <\/thead>\\n\"+\n"
      ."        \"  <tbody>\\n\");\n"
      ."      for(i=1; i<=result[0]; i++){\n"
      ."        out.push(\n"
      ."          \"    <tr title='Click to select'"
      ."  onclick=\\\""
      ."geid_set('".$_field."','\"+result[i]['".$_linked_field."']+\"');"
      .($_displayed_field!='' && $_displayed_field!=$_linked_field ?
         "geid_set('x_".$_field."','\"+result[i]['".$_displayed_field."']+\"');"
       :
         ""
      )
      .$_row_js
      ."sajax.lookup_helper(".$_control_num.",0);"
      ."sajax.request_".$_control_num."();"
      ."\\\""
      .">\\n\"+\n"
      .Ajax::get_report_data($columns)
      ."          \"    <\/tr>\\n\");\n"
      ."      }\n"
      ."      out.push(\n"
      ."        \"  <\/tbody>\\n\"+\n"
      ."        \"<\/table>\"\n"
      ."      );\n"
      ."      geid('ajax_result_".$_control_num."').innerHTML = out.join('');\n"
      ."    break;\n"
      ."  }\n"
      ."}\n"
    );
  }

  public function get_report_heads($columns) {
    if ($columns===false) {
      return;
    }
    $out = '';
    foreach ($columns as $column) {
      $out.= "        \"      <th>".$column['reportLabel']."<\/th>\\n\"+\n";
    }
    return $out;
  }

  public function get_report_data($columns) {
    if ($columns===false) {
      return;
    }
    $out = '';
    foreach($columns as $column) {
      $out.= "          \"      <td>\"+result[i]['".$column['reportField']."']+\"<\/td>\\n\"+\n";
    }
    return $out;
  }

  public function get_shipping($method,$width,$id,$cp){
    global $system_vars,$total_ship_cost,$total_ship_taxes;
    $total_ship_cost =  (isset($total_ship_cost) ?  sanitize('html',$total_ship_cost)  : 0);
    $total_ship_error = (isset($total_ship_error) ? sanitize('range',$total_ship_error,0,1,0)  : 0);
    $total_ship_taxes = (isset($total_ship_taxes) ? sanitize('html',$total_ship_taxes) : 0);
    switch ($method) {
      case 'FEDEX':
        Page::push_content(
          'javascript',
           "sajax.callback_".$id." = function(_data) {\n"
          ."  geid('ajax_result_fees_shipping_wait').style.display = 'inline';\n"
          ."  geid('ajax_result_fees_shipping_method').innerHTML = 'Contacting FEDEX... ';\n"
          ."  geid('total_ship_error').value = 1;// set while waiting\n"
          ."  ajax_post(\n"
          ."    '/ajax/',\n"
          ."    'fedexShippingThing',\n"
          ."    'rs=serve_shipping'+\n"
          ."    '&rsargs[]=".$method."'+\n"
          ."    '&rsargs[]='+_data+\n"
          ."    '&rsargs[]='+'".serialize($cp)."',\n"
          ."    sajax.handle_".$id."\n"
          ."  );"
          ."}\n"
          ."sajax.handle_".$id." = function(res) {\n"
          ."  if (res==''){ return; }\n"
          ."  var result = eval('('+res+')');\n"
          ."  var out = [], tax;\n"
          ."  geid('ajax_result_".$id."_method').innerHTML = result['method'];\n"
          ."  geid('ajax_result_".$id."_cost').value = two_dp(result['cost']);\n"
          ."  geid('total_ship_error').value = result['error'];\n"
          ."  geid('ajax_result_fees_shipping_method').style.color = (result['error'] ? '#f00' : '');\n"
          ."  geid('ajax_result_fees_shipping_cost').style.borderColor = (result['error'] ? '#f00' : '');\n"
          ."  geid('ajax_result_fees_shipping_cost').style.backgroundColor = (result['error'] ? '#ffe8e8' : '#f0f0f0');\n"
          ."  geid('ajax_result_fees_shipping_wait').style.display = 'none';\n"
          ."  geid('total_ship_cost').value = result['cost'];\n"
          ."  for (var i=0; i < result['taxes'].length; i++) {\n"
          ."    out[out.length] = i+'='+parseFloat(result['taxes'][i]);\n"
          ."  }\n"
          ."  geid('total_ship_taxes').value = out.join(',');\n"
          ."  loadTotalCost();\n"
          ."}\n"
        );
        return
           "<div style='width:".$width."px;'>\n"
          ."  <div class='fl' style='width:20px;overflow:hidden;white-space:nowrap;'>\n"
          ."<img id='ajax_result_".$id."_wait' class='ajax_wait' style='display:none' align='top' src='".BASE_PATH."img/sysimg/icon_ajax_wait.gif' alt='Please wait...' />&nbsp;\n"
          ."  </div>\n"
          ."  <div id=\"ajax_result_".$id."_method\" class='fl txt_r' style='width:".(int)($width-89)."px;'>&nbsp;\n"
          ."  </div>"
          ."  <div class='fl txt_r' style='width: 15px'>"
          .$system_vars['defaultCurrencySymbol']
          ."</div>"
          ."  <input id=\"ajax_result_".$id."_cost\" class='fl formField txt_r' "
          ."style='width: 50px;background-color: #f0f0f0;color: #404040;' type='text' onfocus='blur()' />"
          .draw_form_field("total_ship_error",$total_ship_error,"hidden")
          .draw_form_field("total_ship_cost",$total_ship_cost,"hidden")
          .draw_form_field("total_ship_taxes",$total_ship_taxes,"hidden")
          ."<div class='clear'>&nbsp;</div>"
          ."</div>";
      break;
    }
  }

  public function serve(){
    $submode = (isset($_REQUEST['submode']) ? $_REQUEST['submode'] : "");
    switch ($submode){
      case "events":
        $YYYYMMDD =     get_var('YYYYMMDD');
        $Obj_Event =    new Event;
        $data =         $Obj_Event->get_events_for_date($YYYYMMDD);
        print json_encode($data);
      break;
      case "usernameLookup":
        // Used on http://ta.auroraonline.com/signup
        // if the username already exists, return a suggested username
        // otherwise return __OK__
        $Obj_Person = new Person;
        $testName = $_REQUEST['username'];
        $suffix = 0;
        if ($Obj_Person->get_ID_by_name($testName) === false) {
          die('__OK__');
        }
        while (($testID = $Obj_Person->get_ID_by_name($testName)) !== false) {
          $suffix++;
          $testName = $_REQUEST['username'] . str_pad($suffix, 3, '0', STR_PAD_LEFT);
        }
        die($testName);
      break;
      case "version":
        print Ajax::get_version();
      break;
      default:
        $rs =   get_var('rs');
        $args = get_var('rsargs');
        if (!empty($_GET["rs"])) {
          header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
          header ("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
          header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
          header ("Pragma: no-cache");                          // HTTP/1.0
        }
        switch ($rs) {
          case 'serve_config':
            print json_encode(Ajax::_serve_config($args[0]));
            die;
          break;
          case 'serve_shipping':
            print json_encode(Ajax::_serve_shipping($args[0],$args[1],$args[2]));
            die;
          break;
          case 'serve_lookup_report':
            print json_encode(Ajax::_serve_lookup_report($args[0],$args[1],$args[2],$args[3]));
            die;
          break;
        }
      break;
    }
  }

  protected function _serve_config($url) {
    $Obj= new Remote($url);
    $config_rows = $Obj->get_items('config');
//    y($config_rows);die;
    if (!$config_rows) {
      return
        array(
          2,
          array(
            'category'=>'config',
            'title'=>'URL',
            'content'=>$url
          ),
          array(
            'category'=>'config',
            'title'=>'title',
            'content'=>'Error - No data'
          )
        );
    }
    $config_filtered = array();
    foreach ($config_rows as $config) {
      switch ($config['title']) {
        case 'codebase_version':
        case 'db_detail':
        case 'db_version':
        case 'classes_cstarget':
        case 'classes_detail':
          // Do nothing
        break;
        default:
          $config_filtered[] = $config;
        break;
      }
    }
    $out = array(count($config_filtered));
    foreach ($config_filtered as $config) {
      $out[] =
        array(
          'category'=>$config['category'],
          'title'=>$config['title'],
          'content'=>$config['content']
        );
    }
//    y($out);die;
    return $out;
  }

  protected function _serve_lookup_report($reportID, $filterField='', $filterExact='', $filterValue='') {
    $out =              array();
    $Obj_Report =       new Report($reportID);
    $report_record =    $Obj_Report->get_record();
    $count =            $Obj_Report->get_records_count($report_record,$filterField,$filterExact,$filterValue,false,false);
    if ($count>100) {
      return array(-1,$count);
    }
    $out[0] =           (int)$count;
    $records =          $Obj_Report->get_records($report_record,false,$filterField,$filterExact,$filterValue);
    sort($records);
    foreach ($records as $record){
      $out[] = $record;
    }
    return $out;
  }

  protected function _serve_shipping($method,$data,$cp) {
    $Obj = new Shipping;
    return $Obj->get_shipping($method,$data,unserialize($cp));
  }

  public function get_version(){
    return VERSION_AJAX;
  }
}
?>