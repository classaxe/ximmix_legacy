<?php
define ("FUNCTIONS_VERSION","1.0.15");
/*
Version History:
  1.0.15 (2015-01-11)
    1) Now uses Unix line endings

  (Older version history in functions.txt)
*/

if(!function_exists('memory_get_usage')){
  function memory_get_usage() {
    // Thanks to e.a.schultz@gmail.com for memory_usage_replacement for windows
    if ( substr(PHP_OS,0,3) == 'WIN') { // Windows XP Pro SP2. Should work on Win 2003 Server
      $output = array();
      exec( 'tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output );
      return preg_replace( '/[\D]/', '', $output[5] ) * 1024;
    }
    else { // UNIX Tested on Mac OS X 10.4.6 and Linux Red Hat Enterprise 4
      $pid = getmypid();
      exec("ps -eo%mem,rss,pid | grep $pid", $output);
      $output = explode("  ", $output[0]);
      return $output[1] * 1024;
    }
  }
}
function includes_monitor($class_file=''){
  static $includes = array();
  if ($class_file==''){
    return $includes;
  }
  $includes[] = $class_file;
}

function mem($label=''){
  static $mem_usage = array();
  if ($label!=''){
    $mem_usage[] =
      array(
        'lbl' =>            $label,
        'mem' =>            number_format(memory_get_usage()),
        'class_files' =>    includes_monitor(),
        'html_classes'=>    array()
      );
    return;
  }
  $out =
     "<div id='memory_monitor'>"
    ."<h1 id='memory_monitor_handle'>Memory Monitor</h1>"
    ."<h2 class='fl'>".SYSTEM_FAMILY."</h2><h2 class='fr'>".System::get_item_version('build')."</h2><div class='clr_b' style='height:0px;overflow:hidden'></div>"
    ."<h2 class='fl'>PHP</h2><h2 class='fr'>".System::get_item_version('php')."</h2><div class='clr_b' style='height:0px;overflow:hidden'></div>"
    ."<h2 class='fl'>MySQL</h2><h2 class='fr'>".System::get_item_version('mysql')."</h2><div class='clr_b' style='height:0px;overflow:hidden'></div>"
    ."<h2 class='fl'>HTTP</h2><h2 class='fr'>".System::get_item_version('http_software')."</h2><div class='clr_b' style='height:0px;overflow:hidden'></div>"
    ."<table>"
    ."  <tr>\n"
    ."    <th style='text-align:left;'>Marker</th>\n"
    ."    <th style='text-align:right;'>Memory</th>\n"
    ."  </tr>\n";
  foreach ($mem_usage as $mu) {
    $id = str_replace(array(' ','-',',','.','(',')','{','}','[',']'),'_',$mu['lbl']);
    sort($mu['class_files']);
    foreach ($mu['class_files'] as $class_file) {
      $size =           number_format(filesize($class_file));
      $path_arr =       explode('.',$class_file);
      $class =          $path_arr[count($path_arr)-2];
      $filename =       'class.'.$class.'.php';
      $file =           file_get_contents($class_file);
      $crc32 =          dechex(crc32($file));
      $Obj = new $class;
      $version = $Obj->get_version();
      $mu['html_classes'][] =
         "<tr>\n"
        ."  <td>".$filename."</td>\n"
        ."  <td class='num'>".$version."</td>\n"
        ."  <td class='cs'>".$crc32."</td>\n"
        ."  <td class='num'>".$size."</td>\n"
        ."</tr>";
    }
    $out.=
       "<tr>\n"
      ."  <td>"
      .(count($mu['class_files']) ?
          "<a href=\"#\" onclick=\"div_toggle(geid('classes_".$id."'));this.blur();return false;\">".$mu['lbl']."</a>"
        : $mu['lbl']
       )
      ."</td>\n"
      ."  <td style='text-align:right;'>".$mu['mem']."</td>\n"
      ."</tr>"
      ."<tr id='classes_".$id."' style='display:none'>\n"
      ."  <td colspan='2'>"
      .(count($mu['html_classes']) ?
          "<table border='0' cellpadding='0' cellspacing='0'  class='report'>\n"
         ."  <tr class='head'>\n"
         ."    <th>File</th>\n"
         ."    <th>Version</th>\n"
         ."    <th>Checksum</th>\n"
         ."    <th>Size</th>\n"
         ."  </tr>\n"
         .implode('',$mu['html_classes'])
         ."</table>"
        : ""
       )
      ."</td>\n"
      ."</tr>\n"
      ;
    }
  $memory_monitor_clipboard = "\t".System::get_item_version('build');
  foreach ($mem_usage as $mu) {
    $memory_monitor_clipboard.= "\n".$mu['lbl']."\t".$mu['mem'];
  }
  if (function_exists('memory_get_peak_usage')){
    $memory_monitor_clipboard.= "\nPeak Usage\t".number_format(memory_get_peak_usage());
    $out.=
       "  <tr>\n"
      ."    <th style='text-align:left;'>Peak Usage</th>\n"
      ."    <th style='text-align:right;'>".number_format(memory_get_peak_usage())."</th>\n"
      ."  </tr>\n";
  }
  $out.=
     "  <tr>\n"
    ."    <th colspan='2'>\n"
    ."      <input type=\"button\" onclick=\"copy_clip(geid_val('memory_monitor_data'))\" value='Copy' />\n"
    ."      <input type=\"button\" onclick=\"window.focus();geid('memory_monitor').style.display='none';\" value='Close' />\n"
    ."    </th>\n"
    ."  </tr>\n"
    ."</table>"
    .draw_form_field('memory_monitor_data',$memory_monitor_clipboard,'hidden')
    ."</div>";
  return $out;
}

mem("functions.php start");

ini_set('display-errors',1);
include_once(SYS_SHARED."db_connect.php");
include_once(SYS_SHARED.'codebase.php');
mem("codebase.php post-include");
if (isset($_SESSION['person'])) { // Refresh permissions:
  get_person_to_session(
    $_SESSION['person']['PUsername'],
    $_SESSION['person']['PPassword']
  );  // Updates permissions each page view
}
mem("After get_person_to_session()");
//$system_vars =  get_system_vars();
//mem("After get_system_vars()");
$page_vars =    get_page_vars();
mem("After get_page_vars");
$Obj =          new System(SYS_ID);
mem("After new System()");
$Obj->do_commands();    // execute commands (if any)
mem("After System->do_commands()");

main($mode);            // This function is in system.php
?>