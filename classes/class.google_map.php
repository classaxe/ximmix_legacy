<?php
define('VERSION_GOOGLE_MAP','1.0.45');
/*
Version History:
  1.0.45 (2014-03-28)
    1) Google_Map::add_circle() now has additional parameter 'markertype'

  (Older version history in class.google_map.txt)
*/
class Google_Map{
  var $function_code;
  var $function_code_loader;
  var $id;
  var $key;
  private $_map_options = array();
  static $js_lib_included = false;
  static $status_text =
    array(
      'OK' =>               'Success',
      'ZERO_RESULTS' =>     'Address unknown',
      'OVER_DAILY_LIMIT' => 'You issued too many requests for today',
      'OVER_QUERY_LIMIT' => 'You issued too many requests too quickly',
      'REQUEST_DENIED' =>   'Request denied - did you include the sensor parameter?',
      'INVALID_REQUEST' =>  'Some important information is missing in your request'
    );

  function __construct($id=false){
    $this->sourceID = $id;
    $this->id = "google_map_".$id;
    $this->function_code_loader = "";
  }
  function add_code_loader($code){
//    return;
    $this->function_code_loader.=$code;
  }
  function add_control_large() {
    $this->_map_options[] = "navigationControlOptions: { position: google.maps.ControlPosition.LEFT_TOP }";
  }

  function add_control_small() {
    $this->add_code_loader(
       "    _".$this->id.".addControl(new google.maps.SmallMapControl());\n"
    );
  }
  function add_control_scale() {
    $this->_map_options[] = "scaleControl: true";
    $this->_map_options[] = "scaleControlOptions: { position: google.maps.ControlPosition.BOTTOM_LEFT }";
  }
  function add_control_overview() {
    $this->add_code_loader(
       "    _".$this->id.".addControl(new google.maps.OverviewMapControl());\n"
    );
  }
  function add_control_type() {
    $this->_map_options[] = "mapTypeControl: true";
  }
  function add_control_zoom() {
    $this->add_code_loader(
       "    _".$this->id.".addControl(new google.maps.SmallZoomControl());\n"
    );
  }

  function add_control_zoom_dblclick() {
    $this->add_code_loader(
       "    _".$this->id.".enableContinuousZoom();\n"
      ."    _".$this->id.".enableDoubleClickZoom();\n"
    );
  }

  function add_control_zoom_scrollwheel() {
    $this->add_code_loader(
       "    _".$this->id.".enableContinuousZoom();\n"
      ."    _".$this->id.".enableScrollWheelZoom();\n"
    );
  }

  function add_icon($path,$name){
    // You can make these at http://www.powerhut.co.uk/googlemaps/custom_markers.php
    $doc =  file_get_contents(".".$path."icon.js");
    $doc =  str_replace("myIcon",$name,$doc);
    $doc =  str_replace("marker-images/",$path,$doc);
    $this->add_code_loader($doc."\n\n");
  }

  function add_circle(
    $lat,
    $lon,
    $circle_radius,
    $circle_line_color='#FF0000',
    $circle_line_width='2',
    $circle_line_opacity='0.3',
    $circle_fill_color='#ff0000',
    $circle_fill_opacity='0.1',
    $title='',
    $markertype='green'
  ){
    $map_id =       "_".$this->id;
    $shape_id =     "_".$this->id."_shape";
    $marker_id =    $this->add_marker($lat,$lon,false,$markertype,$title);
    $this->add_code_loader(
       "    var ".$shape_id." = new google.maps.Circle({\n"
      ."      map: ".$map_id.",\n"
      ."      radius: ".$circle_radius.",\n"
      ."      strokeColor: '".$circle_line_color."',\n"
      ."      strokeOpacity: ".$circle_line_opacity.",\n"
      ."      strokeWeight: ".$circle_line_width.",\n"
      ."      fillColor: '".$circle_fill_color."',\n"
      ."      fillOpacity: '".$circle_fill_opacity."'\n"
      ."    });\n"
      ."    ".$shape_id.".bindTo('center', ".$marker_id.", 'position');\n"
    );
    return $shape_id;
  }

  function add_marker($lat, $lon, $dragable=true, $icon='', $title='') {
    $map_id =       "_".$this->id;
    $marker_id =    "_".$this->id."_marker";
    $i = $this->_get_marker_icon($icon);
    $this->add_code_loader(
       "    var ".$marker_id." = new google.maps.Marker({\n"
      ."      map: ".$map_id.",\n"
      .($i['icon'] ?   "      icon: ".$i['icon'].",\n" : "")
      .($i['shadow'] ? "      shadow: ".$i['shadow'].",\n" : "")
      .($i['shape'] ?  "      shape: ".$i['shape'].",\n" : "")
      .($title ?       "      title: \"".$title."\",\n" : "")
      ."      draggable: ".($dragable ? 'true' : 'false').",\n"
      ."      position: new google.maps.LatLng(".$lat.",".$lon.")\n"
      ."    });\n"
    );
    return $marker_id;
  }

  function add_marker_with_html(
    $lat,
    $lon,
    $html,
    $id,
    $dragable =             false,
    $REDUNDANT_PARAMETER =  false,
    $icon =                 '',
    $infoWindowOpen =       false,
    $title =                '',
    $circle_radius =        false,
    $circle_line_color =    '#FF0000',
    $circle_line_width =    2,
    $circle_line_opacity =  0.3,
    $circle_fill_color =    '#ff0000',
    $circle_fill_opacity=   0.1
  ) {
    global $page_vars;
    $map_id =               "_".$this->id;
    $marker_id =            "_".$this->id."_marker_".$id;
    $marker_actions_div =   "_".$this->id."_marker_".$id."_actions";
    $html_lines =           explode("<br />",$html);
    $html_sanitized =       array();
    foreach($html_lines as $h){
      if ($h!=''){
        $html_sanitized[] = $h;
      }
    }
    $html = implode("<br />",$html_sanitized);
    $html =
       "<div>"
      .$html
      .($dragable ?
           "<div id='".$marker_actions_div."' style='padding:0;margin:3px 0;clear:both;font-size:8pt;'>"
          ."<b>[ Map marker: "
          ."<a href='#' onclick='return ecc_map.point.s(".$id.",".$marker_id.",\\\"".BASE_PATH.trim($page_vars['relative_URL'],'/')."\\\",\\\"".$this->id."_map_save\\\",\\\"".$marker_actions_div."\\\")' title='Save changes to map marker'><b>Save</b></a>"
          ." | "
          ."<a href='#' onclick='return ecc_map.point.r(".$marker_id.")' title='Undo changes to map marker'><b>Reset</b></a>"
          ." ]</b></div>"
       : ""
       )
      ."</div>";
    $i = $this->_get_marker_icon($icon);
    $this->add_code_loader(
       "    ".$marker_id." = new ecc_map.point("
      ."_".$this->id.",".$lat.",".$lon.","
      ."\"".($title ? $title : 'Click for information')."\","
      ."\"".str_replace('/','\/',$html)."\""
      .($infoWindowOpen || $dragable || $icon ?
           ","
          .($infoWindowOpen ? '1' : '0').","
          .($dragable ? '1' : '0').","
          .($i['icon'] ? $i['icon'] : 0).","
          .($i['shadow'] ? $i['shadow'] : 0).","
          .($i['shape'] ? $i['shape'] : 0)
        :
           ""
       )
      .");\n"
    );
    if ($circle_radius!==false){
      $map_id =       "_".$this->id;
      $shape_id =     "_".$this->id."_circle";
      $this->add_code_loader(
         "    var ".$shape_id." = new google.maps.Circle({\n"
        ."      map: ".$map_id.",\n"
        ."      radius: ".$circle_radius.",\n"
        ."      strokeColor: '".$circle_line_color."',\n"
        ."      strokeOpacity: ".$circle_line_opacity.",\n"
        ."      strokeWeight: ".$circle_line_width.",\n"
        ."      fillColor: '".$circle_fill_color."',\n"
        ."      fillOpacity: '".$circle_fill_opacity."'\n"
        ."    });\n"
        ."    ".$shape_id.".bindTo('center', ".$marker_id.", 'position');\n"
      );
    }
    return $marker_id;
  }

  function _get_marker_icon($name){
    $shadow =   '';
    $shape =    '';
    switch(strToLower($name)){
      case '':
        $icon =     '';
      break;
      case 'h':
        $icon =     "'".BASE_PATH."img/icon/6044/19'";
      break;
      case 'c':
      case 'w':
        $icon =     "'".BASE_PATH."img/icon/6063/19'";
      break;
      case 'red':
      case 'blue':
      case 'purple':
      case 'orange':
      case 'pink':
      case 'lightblue':
      case 'yellow':
      case 'green':
        $icon =     "'//maps.google.com/mapfiles/ms/icons/".strToLower($name).".png'";
      break;
      default:
        $icon =     $name.".image";
        $shadow =   $name.".shadow";
        $shape =    $name.".shape";
      break;
    }
    return array(
      'icon' =>     $icon,
      'shadow' =>   $shadow,
      'shape' =>    $shape
    );
  }

  function draw($args=array()) {
    global $system_vars;
    $map_height =                   (isset($args['map_height']) ?                   $args['map_height'] :               500);
    $map_width =                    (isset($args['map_width']) ?                    $args['map_width'] :                500);
    $control_large =                (isset($args['control_large']) ?                $args['control_large'] :            0);
    $control_overview =             (isset($args['control_overview']) ?             $args['control_overview'] :         0);
    $control_scale =                (isset($args['control_scale']) ?                $args['control_scale'] :            0);
    $control_small =                (isset($args['control_small']) ?                $args['control_small'] :            0);
    $control_type =                 (isset($args['control_type']) ?                 $args['control_type'] :             0);
    $control_zoom =                 (isset($args['control_zoom']) ?                 $args['control_zoom'] :             0);
    $control_zoom_ondblclick =      (isset($args['control_zoom_ondblclick']) ?      $args['control_zoom_ondblclick'] :  0);
    $control_zoom_onscrollwheel =   (isset($args['control_zoom_onscrollwheel']) ?   $args['control_zoom_onscrollwheel'] :  0);

    if ($control_large)             { $this->add_control_large(); }
    if ($control_small)             { $this->add_control_small(); }
    if ($control_overview)          { $this->add_control_overview(); }
    if ($control_scale)             { $this->add_control_scale(); }
    if ($control_type)              { $this->add_control_type(); }
    if ($control_zoom)              { $this->add_control_zoom(); }
    if ($control_zoom_ondblclick)   { $this->add_control_zoom_dblclick(); }
    if ($control_zoom_onscrollwheel){ $this->add_control_zoom_scrollwheel(); }

    $this->js_setup();
    if (!$system_vars['debug_no_internet']){
      Page::push_content(
        'javascript_onload',
         "  ".$this->id."_code.push(new function(){\n"
        .$this->function_code_loader."\n"
        ."  });\n"
      );
    }
    return
       "<div class=\"google_map\""
      ." id=\"".$this->id."\""
      ." style=\"width:".$map_width."px;height:".$map_height."px;"
      .($system_vars['debug_no_internet'] ? "background:#a0c0a0;" : "")
      ."\">"
      .($system_vars['debug_no_internet'] ? "<div style=\"line-height:".$map_height."px;text-align: center; font-size:24pt;\">(No Internet Connection)</div>" : "")
      ."</div>";
  }

  public static function draw_object_map_html(){
    $type =     sanitize('html',get_var('type'));
    if (!$type){
      $reportID = sanitize('ID',get_var('reportID'));
      if (!$reportID){
        print __CLASS__."::".__FUNCTION__."()<br />\nNeither type nor reportID was provided.";
        die;
      }
      $Obj_Report = new Report($reportID);
      $type =       $Obj_Report->get_field('primaryObject');
    }
    if (class_exists($type)){
      $Obj = new $type;
      if (method_exists($Obj,'draw_object_map_html')){
        return $Obj->draw_object_map_html();
      }
    }
    print __CLASS__."::".__FUNCTION__."()<br />\nNot implemented for ".$type;
    die;
  }

  public static function find_geocode($address){
    global $msg;
    if (trim($address)=='') {
      return array(
        'ID' =>                     '',
        'lat' =>                    '',
        'lon' =>                    '',
        'code' =>                   '',
        'error' =>                  '',
        'match_area' =>             '',
        'match_type' =>             '',
        'match_quality' =>          '',
        'partial' =>                ''
      );
    }
    if ($result = Google_Map::_get_geocode_test_literal($address)){
      return $result;
    }
    $Obj_GC = new Geocode_Cache;
    $cache = $Obj_GC->get_cached_location($address);
    if ($cache){
      $date = new DateTime('-'.Geocode_Cache::max_cache_age.' days');
      if ($cache['query_date']>$date->format('Y-m-d')){
        return array(
          'ID' =>                   $cache['ID'],
          'lat' =>                  $cache['output_lat'],
          'lon' =>                  $cache['output_lon'],
          'code' =>                 'cached',
          'error' =>                '',
          'match_area' =>           $cache['match_area'],
          'match_type' =>           $cache['match_type'],
          'match_quality' =>        $cache['match_quality'],
          'partial' =>              $cache['partial_match']
        );
      }
      if($Obj_GC->get_daily_count()>Geocode_Cache::queries_per_day){
        return array(
          'ID' =>                   $cache['ID'],
          'lat' =>                  $cache['output_lat'],
          'lon' =>                  $cache['output_lon'],
          'code' =>                 'old_result',
          'error' =>                '',
          'match_area' =>           $cache['match_area'],
          'match_type' =>           $cache['match_type'],
          'match_quality' =>        $cache['match_quality'],
          'partial' =>              $cache['partial_match']
        );
      }
      $Obj_GC->_set_ID($cache['ID']);
      $Obj_GC->delete();
    }
    if ($Obj_GC->get_daily_count()>=Geocode_Cache::queries_per_day){
      return array(
        'ID' =>                     '',
        'lat' =>                    '',
        'lon' =>                    '',
        'code' =>                   'OVER_DAILY_LIMIT',
        'error' =>                  'Lookup prevented - we are close to exceeding the maximum number of lookup per day',
        'match_area' =>             '',
        'match_type' =>             '',
        'match_quality' =>          '',
        'partial' =>                ''
      );
    }
    $url =  "http://maps.googleapis.com/maps/api/geocode/json?address=".str_replace(' ', '+',urlencode(trim($address)))."&sensor=false";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if (!$response = json_decode(curl_exec($ch), true)){
      return array(
        'ID' =>                     '',
        'lat' =>                    '',
        'lon' =>                    '',
        'code' =>                   "Connection Error",
        'error' =>                  "Couldn't connect to server. Please try again later.",
        'match_area' =>             '',
        'match_type' =>             '',
        'match_quality' =>          '',
        'partial' =>                ''
      );
    }
//    y($response);
    if ($response['status']!='OK') {
      return array(
        'ID' =>                     '',
        'lat' =>                    '',
        'lon' =>                    '',
        'code' =>                   trim($response['status']),
        'error' =>                  Google_Map::$status_text[trim($response['status'])]."<br />Address: ".$address,
        'match_area' =>             '',
        'match_type' =>             '',
        'match_quality' =>          '',
        'partial' =>                ''
      );
    }
    if (count($response['results'])>1){
      $error =
         count($response['results'])
        ." possible matches for &quot;".$address."&quot;"
        ." - please refine your search:<ol class='map_choices'>";
      foreach($response['results'] as $idx=>$r){
        $point = $r['geometry']['location']['lat'].' '.$r['geometry']['location']['lng'];
        $error.=
           "<li>"
          ."<a href='"
          ."https://maps.google.com/maps?q=".$point."&amp;ie=UTF8&amp;z=17"
          ."' onclick='popWin(this.href,\\\"map_".$idx."\\\",\\\"location=1,status=0,scrollbars=0,resizable=1\\\",800,600,1);return false;'"
          .">"
          .$r['formatted_address']
          ."</a></li>";
      }
      $error.=          "</ol>";
      return array(
        'ID' =>                     '',
        'lat' =>                    '',
        'lon' =>                    '',
        'code' =>                   'Multiple',
        'error' =>                  $error,
        'match_area' =>             '',
        'match_type' =>             '',
        'match_quality' =>          '',
        'partial' =>                ''
      );
    }
//    y($response);
    $result =   $response['results'][0]['geometry'];
    $lat =      $response['results'][0]['geometry']['location']['lat'];
    $lon =      $response['results'][0]['geometry']['location']['lng'];
    $partial =  (isset($response['results'][0]['partial_match']) ? $response['results'][0]['partial_match'] : 0);
    $match_type =       $response['results'][0]['geometry']['location_type'];
    $match_area =       0;
    $match_quality =  0;
    switch($match_type){
      case 'ROOFTOP':
        $match_quality = 100;
      break;
      case 'RANGE_INTERPOLATED':
      case 'GEOMETRIC_CENTER':
      case 'APPROXIMATE':
        if (isset($response['results'][0]['geometry']['bounds'])){
          $lat_1 = $response['results'][0]['geometry']['bounds']['northeast']['lat'];
          $lon_1 = $response['results'][0]['geometry']['bounds']['northeast']['lng'];
          $lat_2 = $response['results'][0]['geometry']['bounds']['southwest']['lat'];
          $lon_2 = $response['results'][0]['geometry']['bounds']['southwest']['lng'];
          $match_area = Google_Map::get_bounds_area($lat_1, $lon_1, $lat_2, $lon_2);
          $match_quality = 100*((14.5 - log(1+$match_area,10))/14.5);
        }
        else {
          $match_quality = 100;
        }
      break;
    }
    $data = array(
      'systemID' =>             SYS_ID,
      'input_address' =>        $Obj_GC->escape_string($address),
      'output_json' =>          $Obj_GC->escape_string(serialize($response['results'][0])),
      'output_lat' =>           $lat,
      'output_lon' =>           $lon,
      'partial_match' =>        $partial,
      'match_area' =>           $match_area,
      'match_type' =>           $match_type,
      'match_quality' =>        $match_quality,
      'query_date' =>           date('Y-m-d',time())
    );
    $ID = $Obj_GC->insert($data);
    return array(
      'ID' =>                   $ID,
      'lat' =>                  $lat,
      'lon' =>                  $lon,
      'code' =>                 'live',
      'error' =>                '',
      'match_area' =>           $match_area,
      'match_type' =>           $match_type,
      'match_quality' =>        $match_quality,
      'partial' =>              $partial
    );
  }

  public static function get_bounds($records=array(),$prefix=''){
    $valid = false;
    $range =
      array(
        'min_lat'=>90,
        'max_lat'=>-90,
        'min_lon'=>180,
        'max_lon'=>-180
      );
    $field_lat =    $prefix.'map_lat';
    $field_lon =    $prefix.'map_lon';
    foreach ($records as $item){
      if ($item[$field_lat]!=0 || $item[$field_lon]!=0){
        $valid = true;
        if ($item[$field_lat]<$range['min_lat']){
          $range['min_lat'] = $item[$field_lat];
        }
        if ($item[$field_lat]>$range['max_lat']){
          $range['max_lat'] = $item[$field_lat];
        }
        if ($item[$field_lon]<$range['min_lon']){
          $range['min_lon'] = $item[$field_lon];
        }
        if ($item[$field_lon]>$range['max_lon']){
          $range['max_lon'] = $item[$field_lon];
        }
      }
    }
    if ($range['min_lat']==$range['max_lat'] && $range['min_lon']==$range['max_lon']){
      return false;
    }
    return $range;
    return ($valid ? $range : false);
  }

  public static function get_bounds_area($lat_a, $lon_a, $lat_b, $lon_b){
    $radius = 6372795.477598; // at equator;
    $circum = 2*$radius*M_PI;
    $lat_mid = ($lat_a+$lat_b)/2;
    $width =
      (2*$radius * asin(
        min(
          1,
          sqrt(
            cos(deg2rad($lat_mid)) *
            cos(deg2rad($lat_mid)) *
            sin(deg2rad(($lon_b - $lon_a)/2)) *
            sin(deg2rad(($lon_b - $lon_a)/2))
          )
        )
      ));
    $lat_a_pos = (($lat_a*($radius*M_PI))/180);
    $lat_b_pos = (($lat_b*($radius*M_PI))/180);
    $height =     abs($lat_a_pos-$lat_b_pos);
    return $width*$height;
  }

  public static function get_geocode($address) {
    global $msg;
    $result = Google_Map::find_geocode($address);
    if ($result['error']==''){
      return $result;
    }
    $msg.= "<li>Google Maps Lookup error:<br />".$result['error']."</li>";
    return false;
  }

  protected static function _get_geocode_test_literal($address){
    $a = preg_split('/[, ]+/',$address);
    if (count($a)!=2){
      return false;
    }
    if (sanitize('range',trim($a[0]),-90,90,false)===false){
      return false;
    }
    if (sanitize('range',trim($a[1]),-180,180,false)===false){
      return false;
    }
    return array(
      'ID' =>                     '',
      'lat' =>                    trim($a[0]),
      'lon' =>                    trim($a[1]),
      'code' =>                   '',
      'error' =>                  '',
      'match_area' =>             0,
      'match_type' =>             'ACTUAL',
      'match_quality' =>          100,
      'partial' =>                0
    );
  }


  public static function get_sql_map_range($args){
    if (
      !isset($args) ||
      !isset($args['lat']) ||
      !isset($args['lon']) ||
      !isset($args['units']) ||
      !isset($args['lat_field']) ||
      !isset($args['lon_field'])
    ){
      die(__CLASS__."::".__FUNCTION__."() expects array with lat, lon, units (km|mile), lat_field, lon_field");
    }
    switch(strToLower($args['units'])){
      case "km":
        $multiplier = 111.05;
      break;
      case "mile":
        $multiplier = 69;
      break;
      default:
        die(__CLASS__."::".__FUNCTION__."() Units must be either km or mile");
      break;
    }
    return
       "  DEGREES(\n"
      ."    ACOS(\n"
      ."      SIN(\n"
      ."        RADIANS(".$args['lat'].")\n"
      ."      ) *\n"
      ."      SIN(\n"
      ."        RADIANS(".$args['lat_field'].")\n"
      ."      ) +\n"
      ."      COS(\n"
      ."        RADIANS(".$args['lat'].")\n"
      ."      ) *\n"
      ."      COS(\n"
      ."        RADIANS(".$args['lat_field'].")\n"
      ."      ) *\n"
      ."      COS(\n"
      ."        RADIANS(".$args['lon']." - ".$args['lon_field'].")\n"
      ."      )\n"
      ."    )\n"
      ."  ) * ".$multiplier;
  }

  public static function get_sql_map_range_filter($args){
    if (
      !isset($args) ||
      !isset($args['lat']) ||
      !isset($args['lon']) ||
      !isset($args['range']) ||
      !isset($args['units']) ||
      !isset($args['lat_field']) ||
      !isset($args['lon_field'])
    ){
      die(__CLASS__."::".__FUNCTION__."() expects array with lat, lon, range, units (km|mile), lat_field, lon_field");
    }
    switch(strToLower($args['units'])){
      case "km":
        $multiplier = 111.05;
      break;
      case "mile":
        $multiplier = 69;
      break;
      default:
        die(__CLASS__."::".__FUNCTION__."() Units must be either km or mile");
      break;
    }
    return
       "  ROUND(\n"
      ."    DEGREES(\n"
      ."      ACOS(\n"
      ."        SIN(\n"
      ."          RADIANS(".$args['lat'].")\n"
      ."        ) *\n"
      ."        SIN(\n"
      ."          RADIANS(".$args['lat_field'].")\n"
      ."        ) +\n"
      ."        COS(\n"
      ."          RADIANS(".$args['lat'].")\n"
      ."        ) *\n"
      ."        COS(\n"
      ."          RADIANS(".$args['lat_field'].")\n"
      ."        ) *\n"
      ."        COS(\n"
      ."          RADIANS(".$args['lon']." - ".$args['lon_field'].")\n"
      ."        )\n"
      ."      )\n"
      ."    ) *\n"
      ."    ".$multiplier." < ".$args['range']."\n"
      ."  )";
  }

  function js_setup(){
    global $system_vars;
    if ($system_vars['debug_no_internet']){
      Page::push_content(
        'javascript',
         "var ".$this->id."_code=[];\n"
      );
      return "";
    }
    if (!Google_Map::$js_lib_included){
      Page::push_content('javascript_top',"<script type=\"text/javascript\" src=\"//maps.google.com/maps/api/js?sensor=false\"></script>\n");
      Google_Map::$js_lib_included = true;
    }
    Page::push_content(
      'javascript',
       "var ".$this->id."_code=[];\n"
      ."function ".$this->id."_load(){\n"
      ."  var options = {\n"
      ."    mapTypeId: google.maps.MapTypeId.ROADMAP".($this->_map_options ? ',' : '')."\n"
      ."    ".implode(",\n    ",$this->_map_options)."\n"
      ."  };\n"
      ."  _".$this->id." = new google.maps.Map(geid(\"".$this->id."\"),options);\n"
      ."  infoWindow = new google.maps.InfoWindow();\n"
      ."  for(var i=0; i<".$this->id."_code.length; i++){\n"
      ."     ".$this->id."_code[i]();\n"
      ."  }\n"
      ."}\n"
    );
    Page::push_content("javascript_onload","  try{".$this->id."_load();}catch(err){}\n");
  }

  function map_load() {
    return;
  }

  function map_centre($lat,$lon,$zoom=13){
    $this->_map_options[] = "center: new google.maps.LatLng(".$lat.",".$lon.")";
    $this->_map_options[] = "zoom: ".$zoom;
  }

  function map_zoom_to_fit($range){
    $this->add_code_loader(
       "    var bounds = new google.maps.LatLngBounds(\n"
      ."      new google.maps.LatLng(".$range['min_lat'].",".$range['min_lon']."),\n"
      ."      new google.maps.LatLng(".$range['max_lat'].",".$range['max_lon'].")\n"
      ."    );\n"
      ."    _".$this->id.".fitBounds(bounds);\n"
    );
  }

  function map_zoom_to_fit_shape($shape_id){
    $this->add_code_loader(
       "    _".$this->id.".fitBounds($shape_id.getBounds());\n"
    );
  }

  public static function on_schedule_update_pending(){
    $Obj = new Person;
    $Obj->on_schedule_update_pending(15);
    $Obj = new Posting;
    $Obj->on_schedule_update_pending(15);
  }

  public function get_version(){
    return VERSION_GOOGLE_MAP;
  }
}
?>