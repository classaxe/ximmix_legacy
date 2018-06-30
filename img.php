<?php
define("VERSION","2.0.82");
/*
Version History:
  2.0.82 (2015-01-11)
    1) Now has Unix-style line endings

  (Older version history in img.txt)
*/
if (!defined("SYS_BUTTONS")){
  define("HELP_PAGE","http://www.ecclesiact.com/_help_img");
  define("MONO_FONT","veramono.ttf");
  define("SYS_BUTTONS",SYS_SHARED."buttons/");
  define("SYS_CLASSES",SYS_SHARED."classes/");
  define("SYS_FONTS",SYS_SHARED."fonts/");
  define("SYS_IMAGES",SYS_SHARED."images/");
  define("SYS_JAVA",SYS_SHARED."java/");
  define("SYS_JS",SYS_SHARED."js/");
  define("SYS_STYLE",SYS_SHARED."style/");
  define("SYS_SWF",SYS_SHARED."swf/");
  define("SYS_WS",SYS_SHARED."wowslider/");
}
$request =  explode("?",urldecode($_SERVER["REQUEST_URI"]));
$request =  trim($request[0],'/');
// Next line removes the base path portion of the request following a
// mod rewrite to a file called streamer.php in that base directory.

if (strlen($_SERVER['SCRIPT_NAME'])-(strlen('streamer.php'))-1){
  define('BASE_PATH',substr($_SERVER['SCRIPT_NAME'],0,strlen($_SERVER['SCRIPT_NAME'])-(strlen('streamer.php'))));
}
else {
  define('BASE_PATH','/');
}
$request =  substr($request,strlen(BASE_PATH)-1);


$request_arr = explode("/",$request);

if (isset($_REQUEST['mode'])) {
  $request_arr[0]='img';
  $request_arr[1]=$_REQUEST['mode'];
}


if (!isset($request_arr[1])){
  $request_arr[1] = "";
}

switch ($request_arr[0]) {
  case "ajax":
    $_REQUEST['submode']="";
    if (isset($request_arr[1])) { $_REQUEST['submode'] =    $request_arr[1]; }
    ajax();
  break;
  case "cron":
    cron();
  break;
  case "css":
    $_REQUEST['submode']="";
    if (isset($request_arr[1])) { $_REQUEST['submode'] =    $request_arr[1]; }
    if (isset($request_arr[2])) { $_REQUEST['ID'] =         $request_arr[2]; }
    if (isset($request_arr[3])) { $_REQUEST['map'] =        $request_arr[3]; }
    css();
  break;
  case "facebook":
    facebook();
  break;
  case "img":
    switch($request_arr[1]){
      case "barcode":
        if (isset($request_arr[2])) { $_REQUEST['number'] = $request_arr[2]; }
        if (isset($request_arr[3])) { $_REQUEST['scale'] =  $request_arr[3]; }
        if (isset($request_arr[4])) { $_REQUEST['color'] =  $request_arr[4]; }
        if (isset($request_arr[5])) { $_REQUEST['bgcolor'] =$request_arr[5]; }
        if (isset($request_arr[6])) { $_REQUEST['height'] = $request_arr[6]; }
        barcode();
      break;
      case "beacon":
        if (isset($request_arr[2])) { $_REQUEST['ID'] = $request_arr[2]; }
        beacon();
      break;
      case "button":
        if (isset($request_arr[2])) { $_REQUEST['ID'] = $request_arr[2]; }
        button();
      break;
      case "color":
      case "colour":
        if (isset($request_arr[2])) { $_REQUEST['color'] =      $request_arr[2]; }
        color();
      break;
      case "custom_button":
        custom_button();
      break;
      case "encoded":
        if (isset($request_arr[2])) { $_REQUEST['color'] =      $request_arr[2]; }
        if (isset($request_arr[3])) { $_REQUEST['size'] =       $request_arr[3]; }
        if (isset($request_arr[4])) { $_REQUEST['code'] =       $request_arr[4]; }
        encoded();
      break;
      case "height":
        $_REQUEST['resize'] = 1;
        $_REQUEST['maintain'] = 1;
        if (isset($request_arr[2])) { $_REQUEST['height'] =  $request_arr[2]; }
        if (isset($request_arr[3])) {
          array_splice($request_arr,0,3);
          $_REQUEST['img']=BASE_PATH.trim(implode('/',$request_arr),'/');
        }
        sysimg();
      break;
      case "icon":
        if (isset($request_arr[2])) { $_REQUEST['offset'] =         $request_arr[2]; }
        if (isset($request_arr[3])) { $_REQUEST['width'] =          $request_arr[3]; }
        if (isset($request_arr[4])) { $_REQUEST['width_total'] =    $request_arr[4]; }
        icon();
      break;
      case "max":
        $_REQUEST['resize'] = 1;
        $_REQUEST['maintain'] = 1;
        if (isset($request_arr[2])) { $_REQUEST['max'] =  $request_arr[2]; }
        if (isset($request_arr[3])) {
          array_splice($request_arr,0,3);
          $_REQUEST['img']=BASE_PATH.trim(implode('/',$request_arr),'/');
        }
        sysimg();
      break;
      case "qrcode":
        if (isset($request_arr[2])) { $_REQUEST['ecc'] =  $request_arr[2]; }
        if (isset($request_arr[3])) { $_REQUEST['size'] = $request_arr[3]; }
        if (isset($request_arr[4])) {
          array_splice($request_arr,0,4);
          $_REQUEST['text']=implode('/',$request_arr);
        }
        qrcode();
      break;
      case "resize":
        $_REQUEST['resize'] = 1;
        $_REQUEST['maintain'] = 1;
        if (isset($request_arr[2])) {
          array_splice($request_arr,0,2);
          $_REQUEST['img']=BASE_PATH.trim(implode('/',$request_arr),'/');
        }
        sysimg();
      break;
      case "rss_proxy":
        rss_proxy();
      break;
      case "sample":
        if (isset($request_arr[2])) { $_REQUEST['submode'] =    $request_arr[2]; }
        if (isset($request_arr[3])) { $_REQUEST['ID'] =         $request_arr[3]; }
        if (isset($request_arr[4])) { $_REQUEST['cs'] =         $request_arr[4]; }
        button_sample();
      break;
      case "spacer":
        spacer();
      break;
      case "sysimg":
        if (isset($request_arr[2])) { $_REQUEST['img']=$request_arr[2]; }
        sysimg();
      break;
      case "template":
        if (isset($request_arr[2])) { $_REQUEST['ID'] =     $request_arr[2]; }
        template();
      break;
      case "text":
        if (isset($request_arr[2])) { $_REQUEST['color'] =  $request_arr[2]; }
        if (isset($request_arr[3])) { $_REQUEST['bgcolor'] =$request_arr[3]; }
        if (isset($request_arr[4])) { $_REQUEST['size'] =   $request_arr[4]; }
        if (isset($request_arr[5])) { $_REQUEST['font'] =   $request_arr[5]; }
        if (isset($request_arr[6])) { $_REQUEST['bold'] =   $request_arr[6]; }
        if (isset($request_arr[7])) { $_REQUEST['text'] =   $request_arr[7]; }
        text();
      break;
      case "ticket":
        if (isset($request_arr[2])) { $_REQUEST['ID'] =     $request_arr[2]; }
        ticket();
      break;
      case "treeview":
        if (isset($request_arr[2])) { $_REQUEST['img']="treeview/".$request_arr[2]; }
        sysimg();
      break;
      case "user":
        if (isset($request_arr[2])) {
          array_splice($request_arr,0,2);
          $_REQUEST['img']=BASE_PATH."UserFiles/Image/".trim(implode('/',$request_arr),'/');
        }
        sysimg();
      break;
      case "version":
        print VERSION;
      break;
      case "width":
        $_REQUEST['resize'] = 1;
        $_REQUEST['maintain'] = 1;
        if (isset($request_arr[2])) { $_REQUEST['width'] =  $request_arr[2]; }
        if (isset($request_arr[3])) {
          array_splice($request_arr,0,3);
          $_REQUEST['img']=BASE_PATH.trim(implode('/',$request_arr),'/');
        }
        sysimg();
      break;
      case "wm":
        $_REQUEST['resize'] = 1;
        $_REQUEST['maintain'] = 1;
        $_REQUEST['wm'] = 1;
        if (isset($request_arr[2])) {
          array_splice($request_arr,0,2);
          $_REQUEST['img']=BASE_PATH.trim(implode('/',$request_arr),'/');
        }
        sysimg();
      break;
      default:
        help();
      break;
    }
    die;
  break;
  case "java":
    $_REQUEST['submode']="";
    if (isset($request_arr[1])) { $_REQUEST['submode'] =   $request_arr[1]; }
    java();
    die;
  break;
  case "qbwc":
    qbwc();
  break;
  case "resource":
    $_REQUEST['submode']="";
    if (isset($request_arr[1])) { $_REQUEST['submode'] =    $request_arr[1]; }
    if (isset($request_arr[2])) {
      array_splice($request_arr,0,2);
      $_REQUEST['file'] =   BASE_PATH."UserFiles/File/protected/".trim(implode('/',$request_arr),'/');
    }
    resource();
  break;
  case "lib":
    lib($request_arr);
    die;
  break;
  case "sysjs":
    $_REQUEST['submode']="";
    $_REQUEST['level']="";
    if (isset($request_arr[1])) { $_REQUEST['submode'] =   $request_arr[1]; }
    if (isset($request_arr[2])) { $_REQUEST['level'] =     $request_arr[2]; }
    sysjs();
    die;
  break;
  case "osd":
    osd();
    die;
  break;
}

function ajax(){
  use_codebase();
  $Obj_Ajax = new Ajax;
  $Obj_Ajax->serve();
}

function barcode() {
  require_once(SYS_CLASSES."class.image_factory.php");
  require_once(SYS_CLASSES."class.barcode.php");
  $font =       "arial.ttf";
  $color =      (isset($_REQUEST['color'])  ? $_REQUEST['color']  : "000000");
  $bgcolor =    (isset($_REQUEST['bgcolor'])? $_REQUEST['bgcolor']: "ffffff");
  $scale =      (isset($_REQUEST['scale'])   ? $_REQUEST['scale'] : "2");
  $height =     (isset($_REQUEST['height'])  ? $_REQUEST['height'] : (int)$scale*50);
  $number =     $_REQUEST['number'];
  $Obj = new BarCode($bgcolor, $color, $color, $font, $scale, $height);
  $img = $Obj->image($number);
  header("Content-Type: image/png; name=\"barcode.png\"");
  imagepng($img);
}

function beacon(){
  if (!$ID = (isset($_REQUEST['ID']) ? $_REQUEST['ID']  : false)){
    return;
  }
  $ID_arr = explode('.',$ID);
  $_ID = $ID_arr[0];
  use_codebase();
  $Obj_Mail_Queue_Item = new Mail_Queue_Item($_ID);
  $Obj_Mail_Queue_Item->track_beacon();
  header("Content-type: image/gif");
  print
     "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff\x21\xf9"
    ."\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x40\x02\x01\x44\x00\x3b";
  die;
}

function button() {
  $filename = SYS_BUTTONS."btn_".$_REQUEST['ID'].".png";
  if (file_exists($filename)) {
    img_set_cache(3600*24*1); // expire in one week
    header("Content-type: image/png");
    readfile($filename);
    die;
  }
  header("Location: ../../../img_button/".$_REQUEST['ID']);
}

function button_sample() {
  $submode =    $_REQUEST['submode'];
  $ID =         $_REQUEST['ID'];
  $filename =   SYS_BUTTONS.$submode."_".$ID.".png";
  if (file_exists($filename)) {
    img_set_cache(3600*24*7); // expire in one week
    header("Content-type: image/png");
    readfile($filename);
    die;
  }
  header(
     "Location: ../../../../img_button_sample/".$ID
    .(isset($_REQUEST['cs']) ? "/".$_REQUEST['cs'] : "")
  );
}

function color() {
  if (!isset($_REQUEST['color'])){
    spacer();
  }
  $R = chr(hexdec(substr($_REQUEST['color'], 0, 2)));
  $G = chr(hexdec(substr($_REQUEST['color'], 2, 2)));
  $B = chr(hexdec(substr($_REQUEST['color'], 4, 2)));
  img_set_cache(3600*24*365); // expire in one year
  header("Content-type: image/gif");
  print
     "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00$R$G$B\xFF\xFF"
    ."\xFF\x2C\x00\x00\x00\x00\x01\x00\x01\x00\x00\x02\x01\x44\x00\x3B";
  die;
}

function cron(){
  use_codebase();
  CRON::heartbeat();
}

function css(){
  if (!stristr(strtolower(@$_SERVER['HTTP_USER_AGENT']),"msie 8.0")){
    ob_start("ob_gzhandler");
  }
  img_set_cache(3600*24*7); // expire in one year
  header("Content-type: text/css");
  switch ($_REQUEST['submode']){
    case "breadcrumbs":
      if (!isset($_REQUEST['ID'])){
        return "";
      }
      $file = file_get_contents(SYS_STYLE."breadcrumbs.css");
      if (isset($_REQUEST['map'])){
        $map_arr = explode(',',$_REQUEST['map']);
        for($i=0; $i<count($map_arr); $i++){
          $file = str_replace('%%'.($i+1).'%%',$map_arr[$i],$file);
        }
      }
      print $file;
    break;
    case "block_layout_detail":
      if (!isset($_REQUEST['ID'])){
        return "";
      }
      $record =     get_css_for_block_layout($_REQUEST['ID']);
      print $record['single_item_css'];
    break;
    case "block_layout_listings":
      if (!isset($_REQUEST['ID'])){
        return "";
      }
      $record =     get_css_for_block_layout($_REQUEST['ID']);
      print $record['listings_css'];
    break;
    case "community":
      readfile(SYS_STYLE."community.css");
    break;
    case "labels":
      readfile(SYS_STYLE."labels.css");
    break;
    case "layout":
      if (!isset($_REQUEST['ID'])){
        return "";
      }
      $record =     get_layout($_REQUEST['ID']);
      $out =
         ($record['colour1'] ?
           ".t_bgcol1 { background-color: #".$record['colour1']."; }\r\n"
          .".t_col1   { color: #".$record['colour1']."; }\r\n"
          .".t_bdcol1 { border: solid 1px #".$record['colour1']."; }\r\n"
         : "")
        .($record['colour2'] ?
           ".t_bgcol2 { background-color: #".$record['colour2']."; }\r\n"
          .".t_col2   { color: #".$record['colour2']."; }\r\n"
          .".t_bdcol2 { border: solid 1px #".$record['colour2']."; }\r\n"
         : "")
        .($record['colour3'] ?
           ".t_bgcol3 { background-color: #".$record['colour3']."; }\r\n"
          .".t_col3   { color: #".$record['colour3']."; }\r\n"
          .".t_bdcol3 { border: solid 1px #".$record['colour3']."; }\r\n"
         : "")
        .($record['colour4'] ?
           ".t_bgcol4 { background-color: #".$record['colour4']."; }\r\n"
          .".t_col4   { color: #".$record['colour4']."; }\r\n"
          .".t_bdcol4 { border: solid 1px #".$record['colour4']."; }\r\n"
         : "")
        .($record['style']!="" ? "\r\n".$record['style'] : "")
        ;
      if (trim($out)!='') {
        $out = "/* [Layout Style] */\r\n".$out;
      }
      print $out;
    break;
    case "pie":
      header( 'Content-type: text/x-component' );
      readfile(SYS_STYLE."pie.htc");
    break;
    case "spectrum":
      readfile(SYS_STYLE."spectrum.css");
    break;
    case "system":
      $isIE_lt7 =
        stristr(strtolower(@$_SERVER['HTTP_USER_AGENT']),"msie 5.0")||
        stristr(strtolower(@$_SERVER['HTTP_USER_AGENT']),"msie 5.5")||
        (preg_match('/^Mozilla\/4\.0 \(compatible; MSIE 6/', $_SERVER['HTTP_USER_AGENT']) && !preg_match('/\bopera/i', $_SERVER['HTTP_USER_AGENT']));
      $record =     get_system();
      $cs_icons =   get_checksum(SYS_IMAGES."icons.gif");
      $cs_icons_big =   get_checksum(SYS_IMAGES."icons-big.gif");
      $cs_labels =  get_checksum(SYS_IMAGES."labels.gif");
      $rel_path =  BASE_PATH;
      print
        "/* [Shared Icons and Labels] */\r\n"
        ."img.icon              { background-image: url(".$rel_path."img/sysimg/icons.gif/".$cs_icons.");}\r\n"
        ."img.icon-big          { background-image: url(".$rel_path."img/sysimg/icons-big.gif/".$cs_icons_big.");}\r\n"
        ."img.label             { background-image: url(".$rel_path."img/sysimg/labels.gif/".$cs_labels.");}\r\n"
        ."ul.breadcrumbs li.sub { background-image: url(".$rel_path."img/sysimg/icon_path_separator.gif);}\r\n"
        ."div.rating .img       { background-image: url(".$rel_path."img/sysimg/icon_ratings_13x13.gif);}\r\n"
        .".icons                { background-image:url(".$rel_path."img/sysimg/icons.gif/".$cs_icons.");}\r\n"
        .".icons-big            { background-image:url(".$rel_path."img/sysimg/icons-big.gif/".$cs_icons_big.");}\r\n"
        .".toolbar_icon         { background-image:url(".$rel_path."img/sysimg/icons.gif/".$cs_icons.");}\r\n"
        .".admin_toolbartable, a.ti { background: url(".$rel_path."img/sysimg/icon_toolbar_background.gif) top; }\r\n"
        ."#popupMask            { "
        .($isIE_lt7 ? "opacity:.40;filter:alpha(opacity=40);" : "")
        ."background-image: url(".$rel_path."img/sysimg/maskbg.png) !important; } /* For browsers Moz, Opera, etc. */\r\n"
        ."\r\n"
        ."/* [System Colour Scheme settings] */\r\n"
        ."h1,h2,h3          { color: #".$record['text_heading'].";}\r\n"
        .".highlight        { font-weight: bold; background-color: #".$record['table_header']."; color: #".$record['text_heading'].";}\r\n"
        .".cal_table        { border: solid 1px #".$record['cal_border']."; }\r\n"
        .".cal_enlarge      { background: url(".$rel_path."img/sysimg/calendar_enlarge.gif) no-repeat;}\r\n"
        .".cal_help         { background: url(".$rel_path."img/sysimg/calendar_help.gif) no-repeat;}\r\n"
        .".cal_head         { background-color: #".$record['cal_head']."; height: 2em;}\r\n"
        .".cal_days         { border-top: solid 1px #".$record['cal_border']."; border-bottom: solid 1px #".$record['cal_border']."; border-right: solid 1px #".$record['cal_border']."; background-color: #".$record['cal_days'].";}\r\n"
        .".cal_days_s       { border-right: none;}\r\n"
        .".cal_current      { background-color: #".$record['cal_current'].";}\r\n"
        .".cal_current_we   { background-color: #".$record['cal_current_we'].";}\r\n"
        .".cal_then         { background-color: #".$record['cal_then'].";}\r\n"
        .".cal_then_we      { background-color: #".$record['cal_then_we'].";}\r\n"
        .".cal_today        { background-color: #".$record['cal_today'].";}\r\n"
        .".cal_has_event    { background-image: url(".$rel_path."img/sysimg/calendar_event_indicator.gif,".$record['cal_event'].") !important; }\r\n"
        .".cal_has_events   { background-image: url(".$rel_path."img/sysimg/calendar_events_indicator.gif,".$record['cal_event'].") !important; }\r\n"
        .".calendar_mini .cal_has_event,\n"
        .".calendar_mini .cal_has_events { color: #".$record['cal_event']." !important; }\r\n"
        .".cal_nav          { background: url(".$rel_path."img/sysimg/calendar_menu_arrow.gif) no-repeat 100% 100%;}\r\n"
        .".table_border     { border-style: solid; border-width: 1px; border-color: #".$record['table_border']."; border-collapse: collapse;}\r\n"
        .".table_data       { background-color: #".$record['table_data'].";}\r\n"
        .".table_header     { background-color: #".$record['table_header'].";}\r\n"
        ."\r\n/* [System Style] */\r\n"
        ."body { margin: 0; padding: 0; "
        .($record['defaultBgColor']!='' ?
           "background-color: #".$record['defaultBgColor']."; "
         : "")
        ."}\r\n"
        .($record['colour1'] ?
           ".t_bgcol1 { background-color: #".$record['colour1']."; }\r\n"
          .".t_col1   { color: #".$record['colour1']."; }\r\n"
          .".t_bdcol1 { border: solid 1px #".$record['colour1']."; }\r\n"
         : "")
        .($record['colour2'] ?
           ".t_bgcol2 { background-color: #".$record['colour2']."; }\r\n"
          .".t_col2   { color: #".$record['colour2']."; }\r\n"
          .".t_bdcol2 { border: solid 1px #".$record['colour2']."; }\r\n"
         : "")
        .($record['colour3'] ?
           ".t_bgcol3 { background-color: #".$record['colour3']."; }\r\n"
           .".t_col3  { color: #".$record['colour3']."; }\r\n"
          .".t_bdcol3 { border: solid 1px #".$record['colour3']."; }\r\n"
         : "")
        .($record['colour4'] ?
           ".t_bgcol4 { background-color: #".$record['colour4']."; }\r\n"
          .".t_col4   { color: #".$record['colour4']."; }\r\n"
          .".t_bdcol4 { border: solid 1px #".$record['colour4']."; }\r\n"
         : "")
        .($record['style']!='' ?
            "\r\n/* [System Custom Style] */\r\n"
           .$record['style']."\r\n"
         : "");
        return;
    break;
    case "tcal":
      readfile(SYS_STYLE."tcal.css");
    break;
    case "theme":
      if (!isset($_REQUEST['ID'])){
        return "";
      }
      $record =     get_css_for_theme($_REQUEST['ID']);
      print $record['style'];
    break;
    default:
      readfile(SYS_STYLE."default.css");
    break;
  }
  die;
}

function custom_button() {
  $path = $_SERVER["REQUEST_URI"];
  $prefix = "custom_button_";
  $pathBits = explode("/", $path);
  $fileName = $pathBits[count($pathBits) - 1];
  if (file_exists(SYS_BUTTONS.$prefix.$fileName)){
    header("Content-type: image/png");
    readfile(SYS_BUTTONS.$prefix.$fileName);
    die;
  }
  require_once(SYS_SHARED."codebase.php");
  $debug = false;
  list($text, $width, $style) = explode("~", $fileName);
  $textFix = $prefix.$text;
  $textFix = str_replace("__", "^^", $text);
  $textFix = str_replace("_", " ", $textFix);
  $textFix = str_replace("^^", "_", $textFix);
  $extension = explode(".", $style);
  $style = $extension[0];
  $imageType = $extension[1];
  $buttonStyle = new Navbutton_style;
  if (($styleID = $buttonStyle->get_ID_by_name($style)) == false) {
  	$styleID = $buttonStyle->get_ID_by_name('brown_gradient');
  }
  $buttonStyle->set_ID($styleID);
  $buttonBinary = $buttonStyle->sample($width,false,$textFix,SYS_BUTTONS.$prefix.$fileName);
  header("Content-type: image/png");
  readfile(SYS_BUTTONS.$fileName);
  die;
}

function _db_connect(){
  include_once(SYS_SHARED."db_connect.php");
}

function encoded() {
  $code =   $_REQUEST['code'];
  $color =  $_REQUEST['color'];
  $size =   $_REQUEST['size'];
  $font =   SYS_FONTS.MONO_FONT;

  $email_arr = array();
  for ($i=0; $i<strlen($code)*2; $i+=2){
    $email_arr[] = chr(hexdec(substr($code,$i,2)));
  }
  $text = trim(implode("",array_reverse($email_arr)));

  $arr_bbox =	imagettfbbox($size,0,$font,$text);
  $width =      $arr_bbox[2]-$arr_bbox[6]; 		// Added 2 for movement on mouseover

  $arr_bbox =	imagettfbbox($size,0,$font,"y_@d|");
  $height =     $arr_bbox[3]-$arr_bbox[7];

  $img =        ImageCreate($width*1.05,$height*1.1);
  $RGB_transp =	ImageColorAllocate($img, HexDec("ff"),HexDec("ff"),HexDec("ff"));
  $RGB =        ImageColorAllocate($img, HexDec(substr($color,0,2)), HexDec(substr($color,2,2)), HexDec(substr($color,4,2)));
  ImageTTFText( $img,$size,0,1,($height*0.83),$RGB,$font,$text);
  ImageColorTransparent($img,$RGB_transp);
  header("Content-type: image/gif");
  img_set_cache(3600*24*7); // expire in one week
  ImageGIF($img);
}

function facebook(){
  use_codebase();
  $Obj_FB = new ECC_Facebook;
  $Obj_FB->handle_request();
}

function get_checksum($filepath) {
  return dechex(crc32(file_get_contents($filepath)));
}

function get_css_for_block_layout(){
  global $Obj_MySQLi;
  _db_connect();
  $sql =
     "SELECT\n"
    ."  `listings_css`,\n"
    ."  `single_item_css`\n"
    ."FROM\n"
    ."  `block_layout`\n"
    ."WHERE `systemID` IN (1,".SYS_ID.") AND `ID`=".$_REQUEST['ID'];
  $result = $Obj_MySQLi->query($sql);
  return $result->fetch_assoc();
}

function get_css_for_theme() {
  global $Obj_MySQLi;
  _db_connect();
  $sql =
     "SELECT\n"
    ."  `style`\n"
    ."FROM\n"
    ."  `theme`\n"
    ."WHERE `systemID`IN (1,".SYS_ID.") AND `ID`=".$_REQUEST['ID'];
  $result = $Obj_MySQLi->query($sql);
  return $result->fetch_assoc();
}

function get_layout() {
  global $Obj_MySQLi;
  _db_connect();
  $sql =
     "SELECT\n"
    ."  `colour1`,\n"
    ."  `colour2`,\n"
    ."  `colour3`,\n"
    ."  `colour4`,\n"
    ."  `style`\n"
    ."FROM\n"
    ."  `layout`\n"
    ."WHERE `systemID`IN (1,".SYS_ID.") AND `ID`=".$_REQUEST['ID'];
  $result = $Obj_MySQLi->query($sql);
  return $result->fetch_assoc();
}
function get_system() {
  global $Obj_MySQLi;
  _db_connect();
  $sql = "SELECT * FROM `system` WHERE `ID`=".SYS_ID;
  $result = $Obj_MySQLi->query($sql);
  return $result->fetch_assoc();
}

function help(){
  session_name("ECC_".SYS_ID);
  session_cache_limiter('must-revalidate');
  ini_set('session.use_only_cookies', 1);
  ini_set('session.use_trans_sid', false);
  session_start();
  if (isset($_SESSION['person']) && (
      $_SESSION['person']['permMASTERADMIN'] ||
      $_SESSION['person']['permSYSADMIN']
      )
    ){
    $finput = fopen(HELP_PAGE,"rb");
    if ($finput===false) {
      print "Cannot access ".HELP_PAGE;
    }
    while (!feof($finput)) {
      print fread($finput, 1024);
    }
    return;
  }
  header("Status: 403 Not Permitted",true,403);
  print "<h1>403</h1><p>Not permitted. Please <a href=\"../signin\"><b>sign</b></a> in to access help.</p>";
}

function icon() {
  $offset =         (isset($_REQUEST['offset']) && (int)$_REQUEST['offset'] ? (int)$_REQUEST['offset'] : 0);
  $width =          (isset($_REQUEST['width']) && (int)$_REQUEST['width'] ? (int)$_REQUEST['width'] : 16);
  $width_total =    (isset($_REQUEST['width_total']) && (int)$_REQUEST['width_total'] ? (int)$_REQUEST['width_total'] : $width);
  $height =         (isset($_REQUEST['height']) && (int)$_REQUEST['height']  ? (int)$_REQUEST['height'] : 16);
  $filename =       SYS_IMAGES."icons.gif";
  $img =            ImageCreate($width_total,$height);
  $img2 =           ImageCreateFromGif($filename);
  $RGB_transp =	    ImageColorAllocate($img, HexDec("c0"),HexDec("ff"),HexDec("c0"));
  header("Content-type: image/gif");
  ImageCopyMerge($img,$img2,0,0,$offset,0,$width,$height,100);
  ImageColorTransparent($img,$RGB_transp);
  img_set_cache(3600*24*7); // expire in one week
  ImageGIF($img);
}

function img_ext_mime_header($ext){
  switch (strToLower($ext)){
    case "css":     $m = "text/css";            break;
    case "gif":     $m = "image/gif";           break;
    case "htm":     $m = "text/html";           break;
    case "html":    $m = "text/html";           break;
    case "ico":     $m = "image/x-icon";        break;
    case "jpg":     $m = "image/jpeg";          break;
    case "jpeg":    $m = "image/jpeg";          break;
    case "js":      $m = "text/javascript";     break;
    case "mp3":     $m = "audio/mpeg";          break;
    case "pdf":     $m = "application/pdf";     break;
    case "php":     $m = "text/html";           break;
    case "png":     $m = "image/png";           break;
    case "svg":     $m = "image/svg+xml";       break;
    case "xml":     $m = "application/xml";     break;
    default:        $m = "text/plain";          break;
  }
  header("Content-Type: ".$m);
}

function img_set_cache($expires,$useFile = false) {
  $exp_gmt = gmdate("D, d M Y H:i:s", time() + $expires)." GMT";  // don't refresh until expires
  if ($useFile && file_exists($useFile))  {
    // get the file modified date
	$mod_gmt = gmdate("D, d M Y H:i:s", filemtime($useFile)) . " GMT";
  }
  else {
  	$mod_gmt = gmdate("D, d M Y H:i:s", time() - 3600*10)." GMT";   // Modified 10 hours ago
  }
  // get the "If-Modified-Since" REQUEST header
  $clientHeaders =      apache_request_headers();
  $if_modified_since =
    (array_key_exists('If-Modified-Since', $clientHeaders) ? $clientHeaders['If-Modified-Since'] : '');
  if ($if_modified_since == $mod_gmt) {
	// files are the same, send 304 and no body
    header("HTTP/1.0 304 Not Modified");
    exit;
  }
  // HTTP 1.1
  @header("Expires: ".$exp_gmt);
  @header("Last-Modified: ".$mod_gmt);
  @header("Cache-Control: public, max-age=".$expires);
  // HTTP 1.0
  @header("Pragma: !invalid");
}

function img_setColourIndex(&$image,$i,$string) {
  sscanf($string, "%2x%2x%2x", $r, $g, $b);
  return imagecolorset($image,$i,$r,$g,$b);
}

function java(){
  img_set_cache(3600*24*365); // expire in one year
  header('Content-Type: application/java-archive');
  readfile(SYS_JAVA.$_REQUEST['submode']);
}

function js_compress($file){
  $resource =       $_REQUEST['submode'];
  $level =          $_REQUEST['level'];
  $filename =       SYS_JS."cache/".str_replace('.','_',$resource).$level.'.cache';
  if (file_exists($filename)){
    readfile($filename);
    return;
  }
  use_codebase();
  switch($resource){
    case "member":
    case "rss_reader":
    case "sys":
    case "treeview":
      $Obj_FS =     new FileSystem;
      $version =    '_'.trim(substr($Obj_FS->get_line($file),3));
    break;
    default:
      $version = "";
    break;
  }
  $filename =   SYS_JS."cache/".str_replace('.','_',$resource.$version).'.cache';
  if (file_exists($filename)){
    readfile($filename);
    return;
  }
  file_put_contents($filename,trim(JSMin::minify(file_get_contents($file))));
  readfile($filename);
}

function lib($request_arr){
  if (!isset($request_arr[1])){
    return;
  }
  switch($request_arr[1]){
    case 'ws':
      return lib_wowslider($request_arr);
    break;
  }
}

function lib_wowslider_js($file){
  $content = file_get_contents($file);
  return str_replace(
    array('$AppName$ $AppVersion$','$WmkT$'),
    array('Ecclesiact','#\"'),
    $content
  );
}

function lib_wowslider($request_arr){
  if (!isset($request_arr[3])){
    return;
  }
  switch($request_arr[2]){
    case 'backgnd':
      if (!isset($request_arr[4])){
        return;
      }
      img_set_cache(3600*1); // expire in one hour
      $file = $request_arr[4];
      $file_arr =   explode('.',$file);
      img_ext_mime_header(array_pop($file_arr));
      readfile(SYS_WS.$request_arr[2].'/'.$request_arr[3].'/'.$request_arr[4]);
    break;
    case 'common':
      img_set_cache(3600*1); // expire in one hour
      header('Content-Type: text/javascript');
      switch($request_arr[3]){
        case 'wowslider.js':
          print lib_wowslider_js(SYS_WS."common/js/wowslider.js");
        break;
      }
    break;
    case 'effects':
      img_set_cache(3600*1); // expire in one hour
      header('Content-Type: text/javascript');
      switch($request_arr[3]){
        case 'flip':
        case 'rotate':
          print lib_wowslider_js(SYS_WS.$request_arr[2].'/'.$request_arr[3]."/jquery.2dtransform.js");
        break;
        case 'squares':
          print lib_wowslider_js(SYS_WS.$request_arr[2].'/'.$request_arr[3]."/coin-slider.js");
        break;
      }
      switch($request_arr[3]){
        case 'basic':
        case 'basic_linear':
        case 'blast':
        case 'blinds':
        case 'blur':
        case 'fade':
        case 'flip':
        case 'fly':
        case 'kenburns':
        case 'rotate':
        case 'slices':
        case 'squares':
        case 'stack':
        case 'stack_vertical':
          print lib_wowslider_js(SYS_WS.$request_arr[2].'/'.$request_arr[3]."/script.js");
        break;
      }
    break;
  }
}

function osd() {
  $record = get_system();
  img_set_cache(3600*24*365); // expire in one year
  header ('Content-type: application/opensearchdescription+xml');
  print
     "<"."?xml version=\"1.0\" encoding=\"UTF-8\" ?".">\r\n"
    ."<OpenSearchDescription xmlns=\"http://a9.com/-/spec/opensearch/1.1/\">\r\n"
    ."<ShortName>".$record['textEnglish']." Search</ShortName>\r\n"
    ."<Description>".$record['textEnglish']." Search provider</Description>\r\n"
    ."<InputEncoding>UTF-8</InputEncoding>\r\n"
    ."<Url type=\"text/html\" "
    ."template=\"".trim($record['URL'],'/')."/search_results?search_text={searchTerms}\" />\r\n"
    ."</OpenSearchDescription>";
  flush();
}
function path_safe($path) {
  return preg_replace('/\.\./','',$path);
}

function qbwc(){
  use_codebase();
  $Obj = new QuickBooks;
  $Obj->qbwc();
}

function qrcode() {
  require_once(SYS_CLASSES."class.qrcode.php");
  $ecc =        (isset($_REQUEST['ecc'])  ?     $_REQUEST['ecc']  : 'M');
  $size =       (isset($_REQUEST['size'])  ?    $_REQUEST['size']  : 4);
  $text =       (isset($_REQUEST['text'])  ?    $_REQUEST['text']  : "");
  $Obj =        new QRCode;
  $Obj->setup($text,$ecc);
  header("Content-Type: image/gif; name=\"qrcode.gif\"");
  $Obj->image($size);
}

function resource(){
  switch ($_REQUEST['submode']){
    case "audioplayer":
      header("Content-Type: application/x-shockwave-flash");
      img_set_cache(3600*24*365, SYS_SWF.'audioplayer.swf'); // expire in one year
      readfile(SYS_SWF."audioplayer.swf");
      die;
    break;
    case "protected":
      if (!file_exists('.'.BASE_PATH."UserFiles/File/protected/.htaccess")) {
        $handle = fopen('.'.BASE_PATH."UserFiles/File/protected/.htaccess",'w');
        fwrite($handle, "order deny,allow\ndeny from all");
        fclose($handle);
      }
      if (!isset($_REQUEST['file'])){
        header("Status: 404 Not Found",true,404);
        die('404 File not found '.$_SERVER["REQUEST_URI"]);
      }
      session_name("ECC_".SYS_ID);
      session_cache_limiter('must-revalidate');
      ini_set('session.use_only_cookies', 1);
      ini_set('session.use_trans_sid', false);
      session_start();
      if (!isset($_SESSION['person'])) {
        header("Status: 403 Unauthorised",true,403);
        die('You must sign in to access '.$_SERVER["REQUEST_URI"]);
      }
      if (!$_SESSION['person']['permSYSMEMBER'] &&
          !$_SESSION['person']['permSYSEDITOR'] &&
          !$_SESSION['person']['permSYSAPPROVER'] &&
          !$_SESSION['person']['permSYSADMIN'] &&
          !$_SESSION['person']['permCOMMUNITYADMIN'] &&
          !$_SESSION['person']['permUSERADMIN'] &&
          !$_SESSION['person']['permMASTERADMIN']
      ) {
        header("Status: 403 Unauthorised",true,403);
        die('Permission denied for '.$_SERVER["REQUEST_URI"]);
      }
      $filename =       '.'.$_REQUEST['file'];
      if (!file_exists($filename)) {
        header("Status: 404 Not Found",true,404);
        die('404 File not found '.$filename.'('.$_SERVER["REQUEST_URI"]);
      }
      $file_ext_arr =   explode(".",$filename);
      $ext =            strToLower(array_pop($file_ext_arr));
      img_ext_mime_header($ext);
      readfile($filename);
      die;
    break;
    case "jwplayer":
      header("Content-Type: application/x-shockwave-flash");
      img_set_cache(3600*24*365, SYS_SWF.'jwplayer.swf'); // expire in one year
      readfile(SYS_SWF."jwplayer.swf");
      die;
    break;
  }
}

function rss_proxy(){
  require_once(SYS_CLASSES."class.rss_proxy.php");
  print RSS_Proxy::get($_REQUEST["url"]);
}

function spacer() {
  header("Content-type: image/gif");
  img_set_cache(3600*24*365); // expire in one year
  print
     "\x47\x49\x46\x38\x39\x61\x01\x00\x01\x00\x80\x00\x00\x00\x00\x00\xff\xff\xff\x21\xf9"
    ."\x04\x01\x00\x00\x00\x00\x2c\x00\x00\x00\x00\x01\x00\x01\x00\x40\x02\x01\x44\x00\x3b";
  die;
}

function sysimg(){
  $max =            (isset($_REQUEST['max'])      && (int)$_REQUEST['max'] ?      (int)$_REQUEST['max'] :      false);
  $width =          (isset($_REQUEST['width'])    && (int)$_REQUEST['width'] ?    (int)$_REQUEST['width'] :    false);
  $height =         (isset($_REQUEST['height'])   && (int)$_REQUEST['height'] ?   (int)$_REQUEST['height'] :   false);
  $resize =         (isset($_REQUEST['resize'])   && (int)$_REQUEST['resize'] ?   (int)$_REQUEST['resize'] :   false);
  $maintain =       (isset($_REQUEST['maintain']) && (int)$_REQUEST['maintain'] ? (int)$_REQUEST['maintain'] : false);
  $wm =             (isset($_REQUEST['wm'])       && (int)$_REQUEST['wm'] ?       (int)$_REQUEST['wm'] :       false);
  $alt =            (isset($_REQUEST['alt']) ?          path_safe($_REQUEST['alt']) : false);
  $border =         (isset($_REQUEST['border']) ?       $_REQUEST['border'] : false);
  $file =           path_safe(isset($_REQUEST['img']) ? $_REQUEST['img'] :    '');
  $file_arr =       explode(",",$file);
  $file =           trim(array_shift($file_arr),'/');
  $filename =       "./".$file;
  $file_ext_arr =   explode(".",$filename);
  $ext =            strToLower(array_pop($file_ext_arr));
  switch($ext){
    case "gif":
    case "ico":
    case "jpg":
    case "jpeg":
    case "png":
      // carry on
    break;
    default:
      header("Status: 500 Invalid file");
      header("HTTP/1.0 500 Invalid File");
      die('Cannot open '.$file);
    break;
  }
  if (!file_exists($filename)) {
    $filename = SYS_IMAGES.$file;
    if (!file_exists($filename) && $alt!='') {
      $filename = "./".$alt;
      if (!file_exists($filename)) {
        $filename = SYS_IMAGES.$alt;
      }
    }
  }
  if (!file_exists($filename)) {
    header("Status: 404 Not Found");
    header("HTTP/1.0 404 Not Found");
    die('<h1>404</h1><p>Cannot open '.$_REQUEST['img'].'</p>');
  }
  switch ($ext){
    // Check to see if image was misnamed
    case "gif":
    case "jpg":
    case "jpeg":
    case "png":
      $data = getimagesize($filename);
      $mime = $data['mime'];
      switch($mime){
        case 'image/gif':
          $ext = 'gif';
        break;
        case 'image/jpeg':
          $ext = 'jpg';
        break;
        case 'image/png':
          $ext = 'png';
        break;
      }
    break;
  }
  img_ext_mime_header($ext);
  img_set_cache(3600*24*7); // expire in one week
  if (!count($file_arr) && !$max && !$width && !$height && !$wm) {
    if (!readfile($filename)) {
      header("Status: 404 Not Found");
      header("HTTP/1.0 404 Not Found");
      die('Cannot open '.$file);
    }
    return;
  }
  switch ($ext){
    case "gif":
      $img =    imageCreateFromGif($filename);
      img_setColourIndex( $img,0,"123456");
      for ($i=0; $i<count($file_arr); $i++) {
        img_setColourIndex( $img,$i+1,$file_arr[$i]);
      }
    break;
    case "jpg":
      $img =    imageCreateFromJpeg($filename);
    break;
    case "png":
      $img =    imageCreateFromPNG($filename);
    break;
  }
  $aspect =   imagesx($img) / imagesy($img);
  if ($max && !$width && !$height){
    $width =    $max;
    $height =   $max;
  }
  if ($width && $height && $maintain!=0){
    if ($width/$aspect>$height){
      $width =   ($height*$aspect);
    }
    else{
      $height = (int)($width/$aspect);
    }
  }
  else if ($width && !$height){
    $height = (int)($width / $aspect);
  }
  else if (!$width && $height){
    $width =   (int)($height * $aspect);
  }
  if ($width || $height) {
    switch ($ext){
      case "gif":
        $img2 = imageCreate($width,$height);
        $RGB_transp =	ImageColorAllocate($img2, HexDec("12"),HexDec("34"),HexDec("56"));
        if ($resize) {
          ImageCopyResized($img2,$img,0,0,0,0,$width,$height,imagesx($img),imagesy($img));
        }
        else {
          ImageCopyMerge($img2,$img,0,0,0,0,imagesx($img),imagesy($img),100);
        }
        ImageColorTransparent($img2,$RGB_transp);
        $img = imageCreate($width,$height);
        $RGB_transp =	ImageColorAllocate($img, HexDec("12"),HexDec("34"),HexDec("56"));
        ImageCopyMerge($img,$img2,0,0,0,0,imagesx($img),imagesy($img),100);
        ImageColorTransparent($img,$RGB_transp);
        $img = $img2;
      break;
      case "png":
        $img2 = ImageCreateTruecolor($width,$height);
        imagecolortransparent($img2, imagecolorallocatealpha($img2, 0, 0, 0, 127));
        imagealphablending($img2, false);
        imagesavealpha($img2, true);
        if ($resize) {
          imagecopyresampled($img2,$img,0,0,0,0,$width,$height,imagesx($img),imagesy($img));
        }
        else {
          ImageCopyMerge($img2,$img,0,0,0,0,imagesx($img),imagesy($img),100);
        }
        $img = $img2;
      break;
      case "jpg":
      case "jpeg":
        $img2 = ImageCreateTruecolor($width,$height);
        if ($resize) {
          imagecopyresampled($img2,$img,0,0,0,0,$width,$height,imagesx($img),imagesy($img));
        }
        else {
          ImageCopyMerge($img2,$img,0,0,0,0,imagesx($img),imagesy($img),100);
        }
        $img = $img2;
      break;
    }
  }
  if ($wm){
    $img_width = imagesx($img);
    $record = get_system();
    $text =     $record['textEnglish'];
    $font =     SYS_FONTS."arialbd.ttf";
    $color =    "ffffff";
    $bgcolor =  "404040";
    $size =     (int)$img_width/(strlen($text));
    $arr_bbox =	imagettfbbox($size,0,$font,$text.' ');
    $width =    $arr_bbox[2]-$arr_bbox[6]; 		// Added 2 for movement on mouseover
    $arr_bbox =	imagettfbbox($size,0,$font,"y_@d|");
    $height =   1.3*($arr_bbox[3]-$arr_bbox[7]);
    $img2 =         ImageCreateTruecolor($width+5,$height+5);
    $RGB_transp =   imagecolorallocatealpha($img2, HexDec(substr($bgcolor,0,2)), HexDec(substr($bgcolor,2,2)), HexDec(substr($bgcolor,4,2)),60);
    $RGB =        ImageColorAllocate($img2, HexDec(substr($color,0,2)), HexDec(substr($color,2,2)), HexDec(substr($color,4,2)));
    ImageFilledRectangle($img2, 2, 2, $width, $height, $RGB_transp);
    ImageTTFText($img2,$size,0,3,($height*0.75),$RGB,$font,$text);
//    ImageColorTransparent($img2,$RGB_transp);
    switch ($ext){
      case "gif":
        ImageCopyMerge($img,$img2,(imagesx($img)/2)-($width/2),imagesy($img)-$height,0,0,imagesx($img2),imagesy($img2),80);
      break;
      case "png":
      case "jpg":
      case "jpeg":
        ImageCopyMerge($img,$img2,(imagesx($img)/2)-($width/2),imagesy($img)-$height,0,0,imagesx($img2),imagesy($img2),80);
      break;
    }
  }
  if ($border && strlen($border)==6){
    $RGB_border =	ImageColorAllocate($img, HexDec(substr($border,0,2)),HexDec(substr($border,2,2)),HexDec(substr($border,4,2)));
    ImagePolygon(
      $img,
      array(
        0,0,
        $width-1,0,
        $width-1,$height-1,
        0,$height-1
      ),
      4,
      $RGB_border
    );
  }
  switch ($ext){
    case "gif":
      ImageGIF($img);
    break;
    case "png":
      ImagePNG($img);
    break;
    case "jpg":
    case "jpeg":
      ImageJPEG($img,null,100);
    break;
  }
}

function sysjs() {
//  if (!stristr(strtolower(@$_SERVER['HTTP_USER_AGENT']),"msie 8.0")){
    ob_start("ob_gzhandler");
//  }
  $submode = $_REQUEST['submode'];
  switch ($submode) {
    case "member":
    case "rss_reader":
    case "treeview":
      img_set_cache(3600*24*365, SYS_JS.$submode.'.js'); // expire in one year
      header('Content-Type: text/javascript');
      js_compress(SYS_JS.$submode.'.js');
    break;
    case "jdplayer":
      img_set_cache(3600*24*7); // expire in one week
      $request =  explode("?",urldecode($_SERVER["REQUEST_URI"]));
      $request =  trim($request[0],'/');
      $request =  substr($request,strlen(BASE_PATH)-1);
      $request_arr = explode("/",$request);
      $path =   SYS_JS.$request_arr[1].'/'.$request_arr[3];
      $bits =   explode('.',$path);
      $ext =    array_pop($bits);
      $file =   file_get_contents($path);
      img_ext_mime_header($ext);
      print($file);
    break;
    case "jquery":
      img_set_cache(3600*24*7); // expire in one week
      header('Content-Type: text/javascript');
      print file_get_contents(SYS_JS.'jquery.min.js');
    break;
    case "jqueryui":
      img_set_cache(3600*24*7); // expire in one week
      header('Content-Type: text/javascript');
      print file_get_contents(SYS_JS.'jquery-ui.min.js');
    break;
    case "jqueryjson":
      img_set_cache(3600*24*7); // expire in one week
      header('Content-Type: text/javascript');
      print file_get_contents(SYS_JS.'jquery.json-2.4.min.js');
    break;
    case "zrssfeed":
      img_set_cache(3600*24*7); // expire in one week
      header('Content-Type: text/javascript');
      print file_get_contents(SYS_JS.'jquery.zrssfeed.min.js');
    break;
    case "spectrum":
      img_set_cache(3600*24*7); // expire in one week
      header('Content-Type: text/javascript');
      print file_get_contents(SYS_JS.'spectrum.min.js');
    break;
    case "ckeditor":
      img_set_cache(3600*24*7); // expire in one week
      $request =  explode("?",urldecode($_SERVER["REQUEST_URI"]));
      $request =  trim($request[0],'/');
      $request =  substr($request,strlen(BASE_PATH)-1);
      $request_arr = explode("/",$request);
      array_shift($request_arr);
      $path =   implode('/',$request_arr);
      $bits =   explode('.',$path);
      $ext =    array_pop($bits);
      $file =   file_get_contents(SYS_JS.$path);
      switch($ext){
        case "php":
          include(SYS_JS.$path);
        break;
        default:
          img_ext_mime_header($ext);
          print($file);
        break;
      }
    break;
    case "sys":
      img_set_cache(3600*24*365, SYS_JS.'functions.js'); // expire in one year
      header('Content-Type: text/javascript');
      js_compress(SYS_JS.'functions.js');
    break;
    case "context":
      use_codebase();
      set_cache(3600*1); // expire in one hour
      header('Content-Type: text/javascript');
      $Obj_CM = new Context_Menu;
      print JSMin::minify($Obj_CM->draw_JS(sanitize('range',$_REQUEST['level'],0,3,0)));
      die;
    break;
    case "ecc":
      use_codebase();
      img_set_cache(3600*1); // expire in one hour
      header('Content-Type: text/javascript');
      print JSMin::minify(
        str_replace(
          '%SYS_URL%',
          ($_SERVER["SERVER_PORT"]==443 ? 'https://' : 'http://').$_SERVER["HTTP_HOST"],
          file_get_contents(SYS_JS.'ecc.js')
        )
      );
    break;
  }
}

function template(){
  $ID =         (isset($_REQUEST['ID'])  ?      $_REQUEST['ID']  : '');
  if (!$ID){
    spacer();
  }
  use_codebase();
  $Obj_IT = new Image_Template($ID);
  if (!$xml_doc = $Obj_IT->get_field('content')){
    spacer();
  }
  $data =       array();
  $result =     Image_Factory::xml_to_image($xml_doc,$data);
}

function text() {
  $text =       $_REQUEST['text'];
  $font =       SYS_FONTS.path_safe($_REQUEST['font']);
  $bold =       (isset($_REQUEST['bold'])   ? $_REQUEST['bold']   : 0);
  $color =      (isset($_REQUEST['color'])  ? $_REQUEST['color']  : "000000");
  $bgcolor =    (isset($_REQUEST['bgcolor'])? $_REQUEST['bgcolor']: "FFFFFF");
  $size =       (isset($_REQUEST['size'])   &&(int)$_REQUEST['size'] ? (int)$_REQUEST['size']   : "12");
  $arr_bbox =	imagettfbbox($size,0,$font,$text);
  $width =      ($arr_bbox[2]-$arr_bbox[0])+4;
  $height =     ($arr_bbox[3]-$arr_bbox[5])+4;
  $dx =         1-($arr_bbox[0]);
  $dy =         1-$arr_bbox[5];
  $img =        ImageCreate($width,$height);
  $RGB_transp = ImageColorAllocate($img, HexDec(substr($bgcolor,0,2)), HexDec(substr($bgcolor,2,2)), HexDec(substr($bgcolor,4,2)));
  $RGB =        ImageColorAllocate($img, HexDec(substr($color,0,2)), HexDec(substr($color,2,2)), HexDec(substr($color,4,2)));
  switch ($bold) {
    case "1":
      $_x = array(0, 0, 1, 1);
      $_y = array(0, 1, 0, 1);
      for($n=0;$n<=3;$n++){
        ImageTTFText($img,$size,0, $dx+$_x[$n],$dy+$_y[$n],$RGB,$font,$text);
      }
    break;
    default:
      ImageTTFText( $img,$size,0,$dx,$dy,$RGB,$font,$text);
    break;
  }
  header("Content-type: image/gif");
  ImageColorTransparent($img,$RGB_transp);
  img_set_cache(3600*24*7); // expire in one week
  ImageGIF($img);
}

function ticket(){
  use_codebase();
  $ID =         (isset($_REQUEST['ID'])  ?      $_REQUEST['ID']  : "");
  $Obj_RE =     new Register_Event($ID);
  $Obj_RE->draw_ticket_image();
}

function use_codebase(){
  require_once('./config.php');
  _db_connect();
  include_once(SYS_SHARED."codebase.php");
}
?>