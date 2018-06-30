<?php
define("CODEBASE_VERSION", "3.2.0.b");
define("DEBUG_FORM", 0);
define("DEBUG_REPORT", 0);
define("DEBUG_MEMORY", 0);
define("PWD_LEN_MIN", 4);
define("SYS_LOG_SLOW", 5);  // Flag queries longer than this (mS) as SLOW in debug file
define("PIWIK_DEV", 1);     // '1' forces community modules to engage with Piwik stats
// test for RSS calendar feed:
//   http://desktop.stphilipsunionville.com/rss/shared_events?what=calendar&YYYY=2008&MM=12
//   http://desktop.westmountparkchurch.org/rss/shared_events?what=calendar&YYYY=2007&MM=09
define(
    "DOCTYPE",
    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'
);
//define("DOCTYPE", '<!DOCTYPE html SYSTEM "%HOST%/xhtml1-strict-with-iframe.dtd">');
/*
--------------------------------------------------------------------------------
3.2.0.b.2364b (2018-06-30)
Summary:
  1) Special release that adds CVV to beanstream for legacy 3.2.0.2364 build

Final Checksums:
  Classes     CS:27ac1f33
  Database    CS:48ba81d8
  Libraries   CS:99d06fbd
  Reports     CS:e64d2f5c

Code Changes:
  codebase.php                                                                                   3.2.0.b   (2018-06-30)
    1) Updated version information
  classes/class.beanstream_gateway.php                                                           1.0.4     (2014-01-06)
    1) Beanstream_Gateway::_setup_get_customer_name() now uses User class to
       load name of customer - contacts cannot place orders
  classes/class.component_collection_viewer.php                                                  1.0.50    (2015-02-11)
    1) Now serves 404 when visitor attempts to select an invalid podcast.
       This should dramatically reduce network traffic by search bots following invalid search paths.
  classes/class.page.php                                                                         1.0.118   (2015-01-01)
    1) Now uses globals contant for option_separator tag in Page::prepare_html_head() JS code
    2) Fixed print form functionality - broken for a while I suspect
    3) Now PSR-2 Compliant - except for line-length warning on Community::FIELDS
  classes/class.payment_method.php                                                               1.0.10    (2014-01-29)
    1) Payment_Method::draw_selector() changes to JS for loadTotalCost() to add extra newline
  classes/class.report_form.php                                                                  1.0.60    (2015-01-06)
    1) Report_Form::_do_update() now uses correct object to perform update and validates fields where possible
    2) Now uses OPTION_SEPARATOR constant not option_separator in Report_Form::_prepare_field() for 'option_list'
    3) Now PSR-2 Compliant
  js/ckeditor/plugins/more/plugin.js                                                             1.0.3     (2015-02-02)
    1) Now with unix-style line endings
  js/ckeditor/plugins/zonebreak/plugin.js                                                        1.0.2     (2015-02-02)
    1) Now with unix-style line endings
  js/functions.js                                                                                1.0.267   (2015-02-02)
    1) Now with unix-style line endings

2364.sql
  1) Set version information

Promote:
  codebase.php                                        3.2.0.b
  classes/  (5 files changed)
    class.beanstream_gateway.php                    * 1.0.4     CS:a5c00f6a   * PROBLEM - VERSION NUMBER DID NOT CHANGE
    class.component_collection_viewer.php             1.0.50    CS:bc06b2d6
    class.page.php                                    1.0.118   CS:2656006d
    class.payment_method.php                        * 1.0.10    CS:a227bebf   * PROBLEM - VERSION NUMBER DID NOT CHANGE
    class.report_form.php                             1.0.60    CS:7f043c77
  js/ckeditor/plugins/more/plugin.js                  1.0.3     CS:f8a47aed
  js/ckeditor/plugins/zonebreak/plugin.js             1.0.2     CS:6fc05eb3
  js/functions.js                                     1.1.267b3 CS:f9f20c2a

  Bug:
    where two postings (e.g. gallery album and article) have same name and date
    search results will be shown instead:
    http://www.armsofjesus.org/2009/03/14/kariobangi-youth-center
    An sql script could probably find more examples
    Provide disambiguation based on ID?

--------------------------------------------------------------------------------
TODO:
  IMPLEMENT NEW get_var() throughout to remove some crazy globals where possible
  1) Make changing a gallery album parent remap all descendents
  2) This should work: http://desktop.saministryresources.ca/page/784780593
     Page needs to have draw_detail() same as for any other displayable item
  3) When mysql 5.5 is on all servers, consider making tables InnoDB rather than MYISAM
     http://www.oracle.com/partners/en/knowledge-zone/mysql-5-5-innodb-myisam-522945.pdf
  4) Introduction of event registration limits to allow cutoff after so many bookings
     have been made / sold

BUGS:
1.12.11.1289
  Transformer button image files should ideally include system ID to prevent one
  system with a button style of 'Main' clearing out cached images for another system whose buttons
  use that systems 'Main' style when admin updates 'Main' style in the other system
  Also transformer buttons cannot have apostrophes in text nor spaces in button style
1.12.2.1280
  1) Report Filters do NOT work in Dashboard layers (or ajax reports come to that) -
1.9.12.1261
  1) If IE7's zoom mode is other than 100%, current rating highlights stars in wrong location
1.9.8.1257
  1) If a button is edited and moved from one suite to another and the original suite now
     has NO buttons, system should delete original suite and remove mapping from parent button
1.8.7.1235
  1) Doesn't prevent two postings with same name or posting with same name as a page

CODE CHANGES COMING:
  1) Consider keyword mapping by value NOT ID
  2) Look for newer version of treeview for XHTML Srict

PROPOSED ENHANCEMENTS:
  1)  Have sitemap show which pages are missing for admins - add option to generate missing pages
  2)  NEED TO TEST WITH MYSQL STRICT MODE - USE SERVER INSTANCE CONFIG
  3)  Ensure that orders for people are deleted when person is removed.
  4)  Make recursive copy / single item copy options for navbuttons and treenodes
  5)  Make recursive export for treenodes
  6)  Prevent deletions of events with actions
  7)  Consider default layout with signin for new system.
  8)  When you delete a navbar, have the system check for pages that reference it
      and have these IDs set to 1 (no navbar)
  9)  When you delete a button, have the system check for navbars that have that
      button as the parent button and have these set to 1 (no parent)
  10) When you delete a button style, have the system prevent this if there
      are navsuites defined using it.
  11) Facebook: when user with a facebook profile registers for an event, have facebook
      make an announcement
  12) Have ability to configure context menus to choose shift right or other
     combinations, based on user's preferences when editing (james' suggestion)

--------------------------------------------------------------------------------
Dialog selector colour, label and ordering conventions:
  Order: (default), (none), Global, everything else
--------------------------------------------------------------------------------
                 MASTERADMIN              SYSADMIN
--------------------------------------------------------------------------------
                 BGColor Label            BGColor Label
Default        = ffffff  (default)        ffffff  (default)
None           = d0d0d0  (none)           d0d0d0  (none)
All Systems    = e0e0ff  * Item           e0e0ff  * Item
This System    = c0ffc0  SYSTEM | Item    c0ffc0  Item
Other system   = ffe0e0  SYSTEM | Item    (-never shown-)
Other value    = ffe0c0  (Other...)       ffe0c0  (Other...)
--------------------------------------------------------------------------------
Filter Icons:
[ICON]14 14 6912 (All Records - Unfiltered View)[/ICON](All)
[ICON]17 17 6751 Created Today[/ICON]
[ICON]17 17 6768 Modified Today[/ICON]
[ICON]21 21 7338 Created This Week[/ICON]
[ICON]21 21 7359 Modified This Week[/ICON]
[ICON]23 23 7380 Created This Month[/ICON]
[ICON]23 23 7403 Modified This Month[/ICON]
[ICON]22 22 7426 Created This Year[/ICON]
[ICON]22 22 7448 Modified This Year[/ICON]
[ICON]13 13 6598 Marked as Important - Yes[/ICON]
[ICON]13 13 6611 Marked as Important - No[/ICON]
[ICON]11 11 6576 Has Categories - Yes[/ICON]
[ICON]11 11 6587 Has Categories - No[/ICON]
[ICON]21 21 7506 Has been Published to Public - Yes[/ICON]
[ICON]21 21 7527 Has been Published to Public - No[/ICON]
[ICON]22 22 7548 Has been Published to Site Users - Yes[/ICON]
[ICON]22 22 7570 Has been Published to Site Users - No[/ICON]
[ICON]22 22 7592 Has been Published to Approved Site Members - Yes[/ICON]
[ICON]22 22 7614 Has been Published to Approved Site Members - No[/ICON]
[ICON]19 19 6370 Has been Published to Groups - Yes[/ICON]
[ICON]19 19 6389 Has been Published to Groups - No[/ICON]
[ICON]21 21 7674 Has been Shared - Yes[/ICON]
[ICON]21 21 7695 Has been Shared - No[/ICON]
[ICON]18 18 6334 Has Received Comments - Yes[/ICON]
[ICON]18 18 6352 Has Received Comments - No[/ICON]
[ICON]22 22 6444 Has Recurrences - Yes[/ICON]
[ICON]22 22 6466 Has Recurrences - No[/ICON]
[ICON]22 22 6488 Is a Recurrence - Yes[/ICON]
[ICON]22 22 6510 Is a Recurrence - No[/ICON]
[ICON]18 18 6408 Has Related Products - Yes[/ICON]
[ICON]18 18 6426 Has Related Products - No[/ICON]
[ICON]22 22 6532 Has Registrants - Yes[/ICON]
[ICON]22 22 6554 Has Registrants - No[/ICON]
[ICON]21 21 7041 Is a Podcast Root Folder[/ICON]
[ICON]23 23 7062 Is a Podcast Sub Folder[/ICON]
[ICON]17 17 7007 Contains Podcasts - Yes[/ICON]
[ICON]17 17 7024 Contains Podcasts - No[/ICON]
[ICON]20 20 6967 Contains other Podcast Albums - Yes[/ICON]
[ICON]20 20 6987 Contains other Podcast Albums - No[/ICON]
[ICON]20 20 7085 Inside a Podcast Album - Yes[/ICON]
[ICON]20 20 7105 Inside a Podcast Album - No[/ICON]
[ICON]21 21 7169 Is a Gallery Root Folder[/ICON]
[ICON]21 21 7190 Is a Gallery Sub Folder[/ICON]
[ICON]22 22 7214 Contains Gallery Images - Yes[/ICON]
[ICON]22 22 7236 Contains Gallery Images - No[/ICON]
[ICON]20 20 7258 Contains other Gallery Albums - Yes[/ICON]
[ICON]20 20 7278 Contains other Gallery Albums - No[/ICON]
[ICON]20 20 7298 Inside a Gallery Album - Yes[/ICON]
[ICON]20 20 7318 Inside a Gallery Album - No[/ICON]
[ICON]17 17 7470 Is a Top-Level Parent Page[/ICON]
[ICON]19 19 7487 Is a Nested Child Page[/ICON]
[ICON]19 19 7636 Is an Administrator - Yes[/ICON]
[ICON]19 19 7655 Is an Administrator - No[/ICON]

*/

// For when codebase is included without functions.php - e.g. ajax or cron
if (!function_exists('mem')) {
    function mem($label = '')
    {
        static $mem_usage = array();
        if ($label=='') {
            return($mem_usage);
        }
        $mem_usage[] =
        array(
            'lbl' =>            $label,
            'mem' =>            number_format(memory_get_usage()),
            'class_files' =>    includes_monitor()
        );
        return;
    }
}

if (!function_exists('includes_monitor')) {
    function includes_monitor($class_file = '')
    {
        static $includes = array();
        if ($class_file=='') {
            return $includes;
        }
        $includes[] = $class_file;
    }
}

if (!function_exists('memory_get_usage')) {
    function memory_get_usage()
    {
        if (substr(PHP_OS, 0, 3) == 'WIN') {
            if (substr(PHP_OS, 0, 3) == 'WIN') {
                $output = array();
                exec('tasklist /FI "PID eq ' . getmypid() . '" /FO LIST', $output);
                return preg_replace('/[\D]/', '', $output[5]) * 1024;
            }
        } else {
            $pid = getmypid();
            exec("ps -eo%mem,rss,pid | grep $pid", $output);
            $output = explode("  ", $output[0]);
            return $output[1] * 1024;
        }
    }
}




// ************************************
// * Definitions and Includes         *
// ************************************
session_name("ECC_".SYS_ID);
session_cache_limiter('must-revalidate');
ini_set('session.use_only_cookies', 1);
ini_set('session.use_trans_sid', false);
session_start();
if (!defined("SYS_BUTTONS")) {
    define("MONO_FONT", "veramono.ttf");
    define("SYS_BUTTONS", SYS_SHARED."buttons/");
    define("SYS_CLASSES", SYS_SHARED."classes/");
    define("SYS_FONTS", SYS_SHARED."fonts/");
    define("SYS_IMAGES", SYS_SHARED."images/");
    define("SYS_JS", SYS_SHARED."js/");
    define("SYS_STYLE", SYS_SHARED."style/");
    define("SYS_SWF", SYS_SHARED."swf/");
}
define(
    "SYS_RESERVED_URL_PARTS",
    "_popup_layer, .htaccess, article, ajax, category, command, cron, css, db_export,"
    ." details, email-view, event, export,"
    ." facebook, fck, img, index.php, java, job, js, js_context, logs, mode, news,"
    ." osd, page, piwik, podcast, print_form, report, resource, robots.txt, rss,"
    ." sitemap.xml, sysjs, tags, _ticket, userfiles, webmail, xhtml1-strict-with-iframe.dtd"
);
define("SYS_BUTTON_TEMPLATES", SYS_SHARED."button_templates/");
define("SYS_EXPORT", dirname($_SERVER["SCRIPT_FILENAME"])."/export/");
define("SYS_EXPORT_CODE", SYS_SHARED."export_code/");
define("SYS_FACEBOOK", SYS_SHARED."facebook/");
define("SYS_LOGS", "./logs/");
define("SYS_LOG_FILE", "debug.txt");
define("SYS_MODULES", SYS_SHARED."modules/");
define("OPTION_SEPARATOR", "[ECL]option_separator[/ECL]");
define(
    "SYS_STANDARD_FIELDS",
    "anchor,bulk_update,command,component_help,DD,filterExact,"
    ."filterField,filterValue,goto,limit,memberID,MM,mode,offset,print,report_name,rnd,"
    ."search_categories,search_date_end,search_date_start,search_keywords,search_offset,"
    ."search_text,search_type,selected_section,selectID,sortBy,source,submode,targetField,"
    ."targetFieldID,targetID,targetReportID,targetValue,topbar_search,YYYY"
);
define("SYS_UPGRADE_URL", "http://www.ecclesiact.com/");

mem('in codebase-1');
$_old_path = ini_get('include_path');
// Do this because encoded system.php blows away include_path for PEAR
include_once(SYS_SHARED."system.php");
ini_set('include_path', $_old_path);

include_once(SYS_SHARED."adodb-time.inc.php");
$custom_file = (defined("AJAX_VERSION") ? "../" : "./")."custom.php";
if (file_exists($custom_file)) {
    include_once($custom_file);
}

// This is called whenever system trys to create a non existant class of the given name
function __autoload($class_name)
{
    return portal_autoload($class_name);
}

function portal_autoload($class_name)
{
    if (class_exists($class_name)) {
        return;
    }
    $class_php = 'class.'.strToLower($class_name).'.php';
    $include_file = SYS_CLASSES.$class_php;
    if (file_exists($include_file)) {
        require_once($include_file);
        includes_monitor($include_file);
        return;
    }
    $include_file = SYS_SHARED.$class_php;
    if (file_exists($include_file)) {
        require_once($include_file);
        includes_monitor($include_file);
        return;
    }
}

spl_autoload_register('portal_autoload');

mem('in codebase-2');
$component_result = array();
// ************************************
// * Extract variables                *
// ************************************
extract($_REQUEST);        // Extracts all request variables (GET, COOKIE and POST) into global scope.
Cart::initialise();


// Added for PCI Compliance, 2006/01/06
// Test: enter this URL:
// http://www.auroraonline.com/?report_name="><script>alert('ScanAlert')</script><"
if (isset($anchor)) {
    $anchor =             sanitize('html', $anchor);
}
if (isset($bulk_update)) {
    $bulk_update =        sanitize('range', $bulk_update, 0, 1, 0);
}
if (isset($columnID)) {
    $columnID =           sanitize('ID', $columnID);
}
if (isset($command)) {
    $command =            sanitize('html', $command);
}
if (isset($component_help)) {
    $component_help =     sanitize('range', $component_help, 0, 1, 0);
}
if (isset($DD)) {
    $DD =                 sanitize('range', $DD, 1, 31, date('d'));
}
if (isset($filterExact)) {
    $filterExact =        sanitize('html', $filterExact);
}
if (isset($filterField)) {
    $filterField =        sanitize('html', $filterField);
}
if (isset($filterValue)) {
    $filterValue =        sanitize('html', $filterValue);
}
if (isset($goto)) {
    $goto =               sanitize('html', $goto);
}
if (isset($limit)) {
    $limit =              sanitize('html', $limit);
}
if (isset($memberID)) {
    $memberID =           sanitize('range', $memberID, 0, 'n', '');
}
if (isset($MM)) {
    $MM =                 sanitize('range', $MM, 1, 12, date('m'));
}
if (isset($mode)) {
    $mode =               sanitize('html', $mode);
}
if (isset($offset)) {
    $offset =             sanitize('html', $offset);
}
if (isset($page)) {
    $page =               sanitize('html', (substr($page, -1)=='\\' ? '' : $page));
}
if (isset($print)) {
    $print =              sanitize('html', $print);
}
if (isset($report_name)) {
    $report_name =        sanitize('html', $report_name);
}
if (isset($search_categories)) {
    $search_categories =  sanitize('html', $search_categories);
}
if (isset($search_date_end)) {
    $search_date_end =    sanitize('date-stamp', $search_date_end);
}
if (isset($search_date_start)) {
    $search_date_start =  sanitize('date-stamp', $search_date_start);
}
if (isset($search_keywords)) {
    $search_keywords =    sanitize('html', $search_keywords);
}
if (isset($search_name)) {
    $search_name =        sanitize('html', $search_name);
}
if (isset($search_offset)) {
    $search_offset =      sanitize('range', $search_offset, 0, 65535, 0);
}
if (isset($search_text)) {
    $search_text =        sanitize('html', $search_text);
}
if (isset($search_type)) {
    $search_type =        sanitize('enum', $search_type, array(
        '*','article','event','gallery-image','news-item','job-posting','page','podcast','product'
    ));
}
if (isset($selectID)) {
    $selectID =           sanitize('ID', $selectID);
}
if (isset($selected_section)) {
    $selected_section =   sanitize('html', $selected_section);
}
if (isset($sortBy)) {
    $sortBy =             sanitize('html', $sortBy);
}
if (isset($source)) {
    $source =             sanitize('html', $source);
}
if (isset($submode)) {
    $submode =            sanitize('html', $submode);
}
if (isset($targetID)) {
    $targetID =           sanitize('ID', $targetID);
}
if (isset($targetField)) {
    $targetField =        sanitize('html', $targetField);
}
if (isset($targetFieldID)) {
    $targetFieldID =      sanitize('ID', $targetFieldID);
}
if (isset($targetReportID)) {
    $targetReportID =     sanitize('ID', $targetReportID);
}
if (isset($targetValue)) {
    $targetValue =        sanitize('html', $targetValue);
}
if (isset($topbar_search)) {
    $topbar_search =      sanitize('html', $topbar_search);
}
if (isset($YYYY)) {
    $YYYY =               sanitize('range', $YYYY, 100, 3000, date('Y'));
}

 mem('in codebase-3');
//$memberID=564563;

// ************************************
// * Set Global Variables             *
// ************************************
global $anchor,$YYYY,$MM,$DD,$mode,$page,$goto,$img,$color,$command,$username,$password,$bulk_update,$system_vars;

$now =          time();
$YYYY =         ($YYYY=="" ? adodb_date('Y', $now) : $YYYY);
$MM =           ($MM==""   ? adodb_date('m', $now) : $MM);
$MM =           (strlen($MM)==1 ? "0".$MM : $MM);
$DD =           ($DD==""   ? adodb_date('d', $now) : $DD);
$DD =           (strlen($DD)==1 ? "0".$DD : $DD);

$Obj = new System(SYS_ID);
$Obj->define_URL_params();
$system_vars = get_system_vars();
date_default_timezone_set($system_vars['timezone']);
Base::registerModules();
Portal::parse_request();
mem('in codebase-4');

function absolute_path($html, $host_url)
{
    $html = str_replace('popWin(\'/', 'popWin(\''.$host_url, $html);
    $html = str_replace('/resource/', $host_url.'resource/', $html);
    $html = str_replace('&amp;soundFile=', '&amp;soundFile='.urlencode($host_url), $html);
    return preg_replace(
        "#(\/resource\/|action='|action=\"|href='|href=\"|src='|src=\"|background='|background=\"|url\()(\/)#",
        "$1".$host_url,
        $html
    );
}

function array_remove_value(&$array, $val = '')
{
    if (empty($array) || !is_array($array)) {
        return;
    }
    if (!in_array($val, $array)) {
        return;
    }
    $arr = $array;
    foreach ($arr as $key => $value) {
        if ($value == $val) {
            unset($arr[$key]);
        }
    }
    $array = array_values($arr);
}

function component_result($what, $supress_error = false)
{
    global $component_result;
    if (isset($component_result[$what])) {
        return $component_result[$what];
    }
    if ($supress_error) {
        return false;
    }
    return "<span style='color:#ff0000;font-weight:bold;'>".$what."</span>";
}

function component_result_set($var, $what)
{
    global $component_result;
    $component_result[$var] = $what;
}

function context($text, $search, $limit = -1)
{
    $text =   trim($text);
    $search = trim($search);
    $length = strlen($text);
    if ($search=='') {
        if ($limit==-1) {
            return $text;
        }
        $context =  substr($text, 0, $limit);
        return $context.($text==$context ? "" : "&hellip;");
    }
    $text = convert_html_to_safe_view($text);
    if ($limit==-1) {
        return preg_replace("/($search)/i", "<span class='highlight'>\\1</span>", $text);
    }
    $pos =    strpos(strtolower($text), strtolower($search));
    if ($pos>($limit/2)) {
        $pos =    strpos($text, " ", ($pos-($limit/2)));
    } else {
        $pos = 0;
    }
    $search_preg_safe =
    str_replace(
        array(
        '[',']','(',')','*'
        ),
        array(
        '\[','\]','\(','\)','\*'
        ),
        $search
    );
    $out =
    preg_replace(
        "/($search_preg_safe)/i",
        "<span class='highlight' style='color:red'>\\1</span>",
        substr($text, $pos, $limit)
    );
    if (strip_tags($out)!=$text) {
        if ($length> ($limit/2)+1+($pos>$limit? $pos : 0)) {
            $out .= "&hellip;";
        }
        if ($pos>0) {
            $out =    "&hellip;".$out;
        }
    }
    return $out;
}

function convert_a2r($num, $lower = false)
{
  // Converts arabic to roman numerals
  // Not optimised - 999 = CMXCIX not IM
  // Only goes up to 1000
  // To improve see http://www.howtocreate.co.uk/php/dnld.php?file=2&action=1
    $a2r[1] =       "i";
    $a2r[5] =       "v";
    $a2r[10] =      "x";
    $a2r[50] =      "l";
    $a2r[100] =     "c";
    $a2r[500] =     "d";
    $a2r[1000] =    "m";
    $out = "";
    for ($n = 1000; $n >= 1; $n=$n/10) {
        switch(floor($num / $n)) {
            case 1:
                $out .= $a2r[$n];
                break;
            case 2:
                $out .= $a2r[$n].$a2r[$n];
                break;
            case 3:
                $out .= $a2r[$n].$a2r[$n].$a2r[$n];
                break;
            case 4:
                $out .= $a2r[$n].$a2r[5 * $n];
                break;
            case 5:
                $out .= $a2r[5 * $n];
                break;
            case 6:
                $out .= $a2r[5 * $n].$a2r[$n];
                break;
            case 7:
                $out .= $a2r[5 * $n].$a2r[$n].$a2r[$n];
                break;
            case 8:
                $out .= $a2r[5 * $n].$a2r[$n].$a2r[$n].$a2r[$n];
                break;
            case 9:
                $out .= $a2r[$n].$a2r[10 * $n];
                break;
        }
      //Repeat with what is left
        $num = $num % $n;
    }
    return $lower ? $out : strToUpper($out);
}


function convert_embedded_audio($string)
{
//  return $string;
//  Replaces [audio: file.mp3|param-1|param-n]
    $pagebits = preg_split("/\[audio:/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $out = "";
    $plaintext = true;
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
            $bit_arr =    preg_split("/\]/", $bit);
            $params =     trim(urldecode(array_shift($bit_arr)), ": {}");
            $Obj_AP =     new Media_Audioplayer($params);
            $out .=       $Obj_AP->draw_clip().implode("]", $bit_arr);
        }
        $plaintext = false;
    }
    return $out;
}

function convert_embedded_video($string)
{
//  Replaces [video: file.flv|file.jpg|width|height]
    $pagebits = preg_split("/\[video:/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $out = "";
    $plaintext = true;
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
            $bit_arr =    preg_split("/\]/", $bit);
            $params =     trim(urldecode(array_shift($bit_arr)), ": {}");
            $params_arr = explode('|', $params);
            $Obj_VP =     new Component_Video_Player;
            $params = array(
            'path_flv' =>   (isset($params_arr[0]) ? $params_arr[0] : ''),
            'path_jpg' =>   (isset($params_arr[1]) ? $params_arr[1] : ''),
            'width' =>      (isset($params_arr[2]) ? $params_arr[2] : ''),
            'height' =>     (isset($params_arr[3]) ? $params_arr[3] : '')
            );
            $out.= $Obj_VP->draw('', $params, true).implode("]", $bit_arr);
        }
        $plaintext = false;
    }
    return $out;
}

function convert_embedded_youtube($string)
{
//  Replaces [youtube: URL|width|height|start]
    $pagebits = preg_split("/\[youtube/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $out = "";
    $plaintext = true;
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
            $bit_arr =    preg_split("/\]/", $bit);
            $params =     trim(urldecode(array_shift($bit_arr)), ": {}");
            $params_arr = explode('|', $params);
            $url =        (isset($params_arr[0]) ? $params_arr[0] : '');
            $width =      (isset($params_arr[1]) ? $params_arr[1] : 240);
            $height =     (isset($params_arr[2]) ? $params_arr[2] : 180);
            $start =      (isset($params_arr[3]) ? $params_arr[3] : 0);
            $Obj_YT =     new Media_Youtube($url, $width, $height, $start);
            $out .=       $Obj_YT->draw_clip().implode("]", $bit_arr);
        }
        $plaintext = false;
    }
    return $out;
}


function convert_html_to_plaintext($html)
{
    return nl2br(strip_tags(preg_replace('`<br(?: /)?>([\\n\\r])`', '$1', $html)));
}

function convert_html_to_safe_view($value, $limit = 500)
{
    $value =    preg_replace('/\[LANG\]([^\|]*)\|/i', '&#91;LANG&#93;${1}|', $value);
    $value =    preg_replace('/\[\/LANG\]/i', '&#91;/LANG&#93;', $value);
    $value =    preg_replace('/\[ECL\]option_separator\[\/ECL\]/i', '[br]', $value);
    $value =    preg_replace('/<img[^>]*>/', '&lt;img&gt; ', $value);
    $value =    preg_replace('/<h([1-6])[^>]*>([^<]*)<\/h([1-6])>/', '[h${1}]${2}[/h${3}]', $value);
    $value =    preg_replace('/\[ECL\]field_([^\[]*)\[\/ECL\]/is', '&#91;FIELD&#93;${1}&#91;/FIELD&#93;', $value);
    $value =    preg_replace('/\[ECL\]([^\[]*)\[\/ECL\]/is', '&#91;ECL&#93;${1}&#91;/ECL&#93;', $value);
    $value =    preg_replace('/(\[ICON\]([^\[]*)\[\/ICON\])/is', '${1} &#91;ICON&#93;${2}&#91;/ICON&#93;', $value);
    $value =    preg_replace(
        '/(\[TRANSFORM\]([^\[]*)\[\/TRANSFORM\])/is',
        '&#91;TRANSFORM&#93;',
        $value
    ); // Strip Transformer tags
    $value =    preg_replace(
        '/(\[audio([^\[]*)\])/is',
        '&#91;AUDIO&#93;',
        $value
    ); // Strip Audio
    $value =    preg_replace(
        '/(\[youtube([^\[]*)\])/is',
        '&#91;YOUTUBE&#93;',
        $value
    ); // Strip Audio
    $value =    preg_replace(
        '/\s\s+/',
        ' ',
        strip_tags($value)
    );
    $value =    preg_replace(
        '/(&#91;LANG&#93;)([^\|]*)\|/i',
        "<span style='background-color:#ff8080;font-weight:bold;'>\${1}\${2}</span>",
        $value
    ); // Represent Lang tags
    $value =    preg_replace(
        '/(&#91;\/LANG&#93;)/i',
        "",
        $value
    ); // Represent Lang tags
    $value =    preg_replace(
        '/&lt;img&gt;/',
        "<span style='background-color:#c0ffc0;font-weight:bold;'>&lt;img&gt;</span>",
        $value
    );
    $value =    preg_replace(
        '/\[h([1-6]*)\]([^\[]*)\[\/h([1-6])\]/',
        "<span style='background-color:#c0c0ff;font-weight:bold;'>&lt;h\${1}&gt;\${2}&lt;/h\${3}&gt;</span>",
        $value
    );
    $value =    preg_replace(
        '/(&#91;FIELD&#93;)([^&]*)(&#91;\/FIELD&#93;)/is',
        "<span style='background-color:#ffe0e0;color:#ff0000;font-weight:bold;'>\${2}</span>",
        $value
    ); // Represent ECL Script tags
    $value =    preg_replace(
        '/(&#91;ECL&#93;)([^&]*)(&#91;\/ECL&#93;)/is',
        "<span style='background-color:#ffff00;font-weight:bold;'>\${1}\${2}\${3}</span>",
        $value
    ); // Represent ECL Script tags
    $value =    preg_replace(
        '/(&#91;ICON&#93;)([^\[]*)(&#91;\/ICON&#93;)/is',
        "<span style='background-color:#ffc0ff;font-weight:bold;'>\${1}\${2}\${3}</span>",
        $value
    ); // Represent ICON tags
    $value =    preg_replace(
        '/(&#91;AUDIO&#93;)/is',
        "<span style='background-color:#ffc0ff;font-weight:bold;'>\${1}</span>",
        $value
    ); // Display AUDIO tags
    $value =    preg_replace(
        '/(&#91;YOUTUBE&#93;)/is',
        "<span style='background-color:#ffc0ff;font-weight:bold;'>\${1}</span>",
        $value
    ); // Display YOUTUBE tags
    $value =    preg_replace('/\[br\]/is', "<br />", $value); // Strip ICON tags
    if ($limit) {
        $value =    (strlen($value) > $limit ? substr($value, 0, $limit)."..." : substr($value, 0, $limit));
    }
    return $value;
}


function convert_icons($string)
{
//  Replaces [ICON]aa bb cc title[/ICON]
//    aa=width
//    bb=padded width
//    cc=source image offset
//    title=title
    $pagebits = preg_split("/\[ICON\]|\[\/ICON\]/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $checksum = System::get_item_version('icons');
    $renderedbit = array();
    $out = "";
    $plaintext = true;
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
            if (in_array($bit, $renderedbit)) {
                $out.= $renderedbit[$bit];
            } else {
                $bit_arr =      explode(' ', $bit);
                $width =        array_shift($bit_arr);
                $padded_width = array_shift($bit_arr);
                $padding =      $padded_width-$width;
                $offset =       array_shift($bit_arr);
                $text =         implode(' ', $bit_arr);
                $out.=
                "<img class=\"toolbar_icon\" src=\"".BASE_PATH."img/spacer\""
                ." alt=\"".$bit."\" title=\"".$text."\" width=\"".$width."\" height=\"16\""
                ." style=\"background-position:-".$offset."px 0px\" />"
                .($padding>0 ?
                    "<img class='b fl' style=\"border:none;\" src=\"".BASE_PATH."img/spacer\""
                    ." width=\"".$padding."\" height=\"16\" alt=\"\"/>"
                 :
                    ""
                 );
            }
        }
        $plaintext = !$plaintext;
    }
    return $out;
}

function convert_labels($string)
{
//  Replaces [LBL]aa|bb|title[/LBL]
//    aa =    classname
//    bb =    height (blank if not forced)
//    title = title
    $pagebits = preg_split("/\[LBL\]|\[\/LBL\]/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $out = "";
    $renderedbit = array();
    $plaintext = true;
    $checksum = System::get_item_version('labels');
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
            if (in_array($bit, $renderedbit)) {
                $out.= $renderedbit[$bit];
            } else {
                $bit_arr =  explode('|', $bit);
                $class =    array_shift($bit_arr);
                $height =   array_shift($bit_arr);
                $text =     str_replace("\\n", "\n", implode(' ', $bit_arr));
                $out.=
                 "<img class='label lbl_".$class."'"
                .($height ? " style=\"height:".$height."px\"" : "")
                ." src=\"".BASE_PATH."img/spacer\""
                ." title=\"".$text."\""
                ." alt=\"".$class."\" />";
            }
        }
        $plaintext = !$plaintext;
    }
    return $out;
}

function convert_language_tags($string)
{
    return Language::convert_tags($string);
}

function convert_ssi_tokens($string)
{
//  Replaces [ssi:ID|PUsername] with SSI link
    $pagebits = preg_split("/\[ssi:/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $out = "";
    $plaintext = true;
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
            $bit_arr =    preg_split("/\]/", $bit);
            $params =     explode('|', array_shift($bit_arr));
            if (count($params)==2) {
                $credentials = serialize(
                    array(
                    'i' =>  sanitize('html', $params[0]),
                    'p' =>  sanitize('html', $params[1])
                    )
                );
                $out.=
                BASE_PATH
                ."?command=ssi&amp;token="
                .XOREncrypt($credentials)
                .implode("]", $bit_arr);
            }
        }
        $plaintext = false;
    }
    return $out;
}

function convert_transforms($string)
{
  // J.F. - added in version 1.9.17
  // replaces [TRANSFORM]JSON Parameters[/TRANSFORM]
  // with its output as we want it to be seen
    $pagebits = preg_split("/\[TRANSFORM\]|\[\/TRANSFORM\]/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $out = "";
    $plaintext = true;
  // set up a variable to contain and keep track of any javascript that needs added
    $transformJS = array();
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
          // we have to keep transforms in single-quoted strings or FCKEditor messes them up:
          // [TRANSFORM]'method':'fieldtype','name':'customButton','data':"
          // {'text':'Smiles','width':'70','style':'Main','height':'26','cssclass':'','url':'http://www.cicbv.ca'}
          // [/TRANSFORM]
          // Problem is, this is not valid JSON - see Example 3 at http://php.net/manual/en/function.json-decode.php
          // So we have to play this game to convert them here before attempting to JSON decode
            $bit = str_replace("'", '"', $bit);
            $params = json_decode("{".$bit."}", true);
            if ($params['method'] == 'fieldtype') {
             // we use the fieldtype class to transform this thing
                $context = isset($params['data']['context']) ? $params['data']['context'] : 'page';
                $ft = new Transformer($context, $params['name'], $params['data']);
                if ($ft->error) {
                    $out .= "Transform error: " . $ft->error;
                } else {
                    if (!array_key_exists($params['name'], $transformJS)) {
                        $out .= $ft->JS;
                        $transformJS[$params['name']] = 'done';
                    }
                    $out .= $ft->HTML;
                }
            }
        }
        $plaintext = !$plaintext;
    }
    return $out;
}

function convert_safe_to_php($string)
{
  // May occur more than once - ECL tags can recurse
    $string = convert_icons($string);
    $string = convert_labels($string);
    $string = convert_embedded_audio($string);
    $string = convert_embedded_youtube($string);
    $string = convert_embedded_video($string);
    $string = convert_ssi_tokens($string);
    $string = convert_transforms($string);
    $string = convert_ecl_tags($string);
    $string = convert_language_tags($string);
    return $string;
}

function convert_ecl_tags($string)
{
  // Thanks Vic for the great ideas here!
    static $global_ECL_arr;
  // If first pass, load ECL tags and PHP code into $global_ECL_arr array:
    if (!isset($global_ECL_arr)) {
        $Obj =              new ECL_Tag;
        $global_ECL_arr =   $Obj->get_all();
    }
  // Split on any remaining unconverted ECL tags
  // Exit if none found
    $pagebits =       preg_split("/\[ECL\]|\[\/ECL\]/", $string);
    if (count($pagebits)<=1) {
        return $string;
    }
    $out =            "";
    $renderedbit =    array();
    $plaintext =      true;   // Assume we are starting as plain text
    foreach ($pagebits as $bit) {
        if ($plaintext) {
            $out.= $bit;
        } else {
            $instance_name = '';
            $_bit =       $bit;
            $bit_arr =    explode(":", $bit);
            if (count($bit_arr)>1) {
                $bit =              array_shift($bit_arr);
                $instance_name =    implode(":", $bit_arr);
            }
            $bit = stripslashes(strToLower($bit));
            $idx = array_search($bit, $global_ECL_arr['tag']);
            if ($idx !== false) {
                $expr =
                 "\$instance_name = \"".$instance_name."\";\n"
                .$global_ECL_arr['php'][$idx];
                $out.= convert_safe_to_php(eval($expr));
            } else {
                $out.=
                 "<span style=\"background-color:#ffe0e0;color:#ff0000\""
                ." title=\"This ECL tag is not defined: [ECL]".$bit."[/ECL]\">"
                ."[ECL]".$_bit."[/ECL]</span>";
            }
        }
        $plaintext = !$plaintext;
    }
    return $out;
}

function deprecated($max_depth = 20)
{
    global $system_vars;
    $trace =      debug_backtrace();
    $message =    "";
    for ($i=1; $i<count($trace)&&$i<=$max_depth; $i++) {
        $message .=
         ($i>1 ? ", " : "")
        ."[".(count($trace)-$i-1)."] "
        .(isset($trace[$i]['class']) ? $trace[$i]['class']."::" : "")
        .$trace[$i]['function']."()"
        ." via ".$trace[$i]['file']
        ." line ".$trace[$i]['line'];
    }
    $level =      3;
    $operation =  'DEPRECATED';
    $source =     trim($system_vars['URL'], '/');
    do_log($level, $source, $operation, $message);
}

function do_log($level, $source, $operation, $message)
{
    global $system_vars;
    if (isset($system_vars)) {
        $build =        System::get_item_version('build');
        $URL =          $system_vars['URL'];
    } else {
// Only if failed even to read system
        $build =        CODEBASE_VERSION;
        $URL =          "";
    }
    switch ($level) {
        case 0:
        case 1:
        case 2:
            $log_prefix = "log_";
            break;
        case 3:
            $log_prefix = "error_";
            break;
    }
    mkdirs(SYS_LOGS, 0777);
    if (!file_exists(SYS_LOGS.".htaccess")) {
        $handle = fopen(SYS_LOGS.".htaccess", 'w');
        fwrite($handle, "order deny,allow\ndeny from all");
        fclose($handle);
    }
    $log_date =   date('Y-m-d');
    $log_file =   $log_prefix.$log_date.".txt";
    if (!file_exists(SYS_LOGS.$log_file)) {
        $header =
         "**********************************************************\r\n"
        .($URL ? "* ".pad($URL, 55)."*\r\n" : "")
        ."* ".pad($log_file, 55)."*\r\n"
        ."* Severity levels 0=info, 1=warning, 2=error, 3=critical *\r\n"
        ."**********************************************************\r\n"
        ."\r\n"
        ."Codes 0, 1 and 2 in log file\r\n"
        ."Code 3 in error file\r\n"
        ."\r\n"
        ."-------------------------------------------------------------------------------------------------------------"
        ."--------------------------------------------------------------------\r\n"
        ."YYYY-MM-DD hh:mm:ss SystemID     Version:        Lvl IP                PersonID     Source                   "
        ."               Operation            Message \r\n"
        ."-------------------------------------------------------------------------------------------------------------"
        ."--------------------------------------------------------------------\r\n";
        $handle = fopen(SYS_LOGS.$log_file, 'wa');
        fwrite($handle, $header);
        fclose($handle);
    }
    $now =        get_timestamp();
    $IP =         (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "No remote IP");
    $personID =   get_userID();
    $line =
     $now." "
    .pad("S:".SYS_ID, 13)
    ."V:".pad($build, 14)
    ."L:".$level." "
    ."I:".pad($IP, 16)
    ."P:".pad($personID, 11)
    .pad($source, 40)
    .pad($operation, 20)
    ." ".$message
    ." URL:".$_SERVER["REQUEST_URI"]
    .(isset($_SERVER["HTTP_REFERER"]) ? " Referer:".$_SERVER["HTTP_REFERER"] : "")
    ."\r\n"
    .($level==3 ? strip_tags(str_replace('<br />', "\r\n  ", x())) : "")
    ."\r\n";
    if (is_writable(SYS_LOGS.$log_file)) {
        if (!$handle = fopen(SYS_LOGS.$log_file, 'a')) {
            return false;
        } else {
            if (fwrite($handle, $line) === false) {
                echo "Cannot write to file ".SYS_LOGS.$log_file;
            }
            fclose($handle);
            return false;
        }
        return true;
    } else {
        echo "The file ".SYS_LOGS.$log_file." is not writable";
        return false;
    }
}

function do_sql_query($sql)
{
    deprecated();
    $Obj = new Record;
    return $Obj->do_sql_query($sql);
}

function do_tracking($status, $allow_redirect = true)
{
    return System::do_tracking();
}

function do_trace_log($operation = 'TRACE', $max_depth = 20)
{
    $trace = debug_backtrace();
    $message =    "";
    for ($i=1; $i<count($trace)&&$i<=$max_depth; $i++) {
        $message .=
         ($i>1 ? ", " : "")
        ."[".(count($trace)-$i-1)."] "
        .(isset($trace[$i]['class']) ? $trace[$i]['class']."::" : "")
        .$trace[$i]['function']."()"
        ." via ".$trace[$i]['file']
        ." line ".$trace[$i]['line'];
    }
    $level =      3;
    $source =     trim($system_vars['URL'], '/');
    do_log($level, $source, $operation, $message);
}

function draw_auto_form($report_name, $controls = 1, $alt_controls = '', $show_header = true)
{
    $Obj = new Report_Form;
    return $Obj->draw($report_name, $controls, $alt_controls, $show_header);
}

function draw_auto_form_inpage($report_name, $for_person = false)
{
    global $ID, $page_vars;
    if ($for_person) {
        $personID = get_userID();
        if ($personID == 0) {
            header("Location: ".BASE_PATH."signin");
        }
        $oldID = $ID;
        $ID =    $personID;
    }
    $out =
     $page_vars['content']
    ."<table width='400' class='minimal'>\n"
    ."  <tr>\n"
    ."    <td>".draw_auto_form($report_name, 0)."</td>\n"
    ."  </tr>\n"
    ."  <tr>\n"
    ."    <td class='table_admin_h txt_c'>\n"
    ."<input type='button' value='Submit' "
    ."onclick=\"geid('submode').value='save';geid('form').submit();\" class='formbutton' style='width: 60px;'/>\n"
    ."</td>\n"
    ."  </tr>\n"
    ."</table>\n";
    if ($for_person) {
        $ID = $oldID;
    }
    return $out;
}

function draw_auto_report($report_name, $toolbar = 1, $ajax_popup_url = false, $header = '')
{
    $Obj =        new Report_Report;
    $reportID =   $Obj->get_ID_by_name($report_name);
    if ($reportID) {
        $Obj->_set_ID($reportID);
        $content = $Obj->draw($report_name, $toolbar, $ajax_popup_url);
    } else {
        $content =
        ($ajax_popup_url ?
        array('html'=>'<h1>Problem:</h1><p>There is no such report as <b>'.$report_name.'</b></p>','js'=>'')
        : '<h1>Problem:</h1><p>There is no such report as <b>'.$report_name.'</b></p>'
        );
    }
    if ($ajax_popup_url) {
        return
        array(
        'html'=>
           "<div id='report_".$reportID."' style='margin-bottom: 1em;'>"
          .$header
          .$content['html']
          ."</div>",
        'js'=> $content['js']
        );
    }
    return
     "<div id='report_".$reportID."' style='margin-bottom: 1em;'>"
    .$header
    .$content
    ."</div>";
}

function draw_component($ID)
{
    if ($ID=="1") {
        return "";
    }
    $Obj_Component = new Component($ID);
    if ($Obj_Component->exists()) {
        return $Obj_Component->execute();
    }
    return "<h3>Error</h3><p>Component '".$ID."' is not currently available for use on this system:<br />\n".x();
}

function draw_component_by_name($name, $args = array())
{
    if ($name=="") {
        return "";
    }
    $Obj_Component = new Component;
    if (!$componentID = $Obj_Component->get_ID_by_name($name)) {
        return "";
    }
    $Obj_Component->_set_ID($componentID);
    $php = $Obj_Component->get_field('php');
    return eval($php);
}

function draw_date($format, $now = false)
{
    $now = ($now ? $now : time());
    switch($format) {
        case "DD MMM YYYY":
            return date("j F Y", $now);
        break;
        case "MMM DD YYYY": // Includes <sup>th</sup> etc
            return date("F j\<\s\u\p\>S\<\/\s\u\p\> Y", $now);
        break;
    }
    return "";
}

function draw_form_header($title, $help = "", $shadow = 0)
{
    global $ID,$report_name;
    return
     "<table class='minimal' style='width:100%;' summary=''>\n"
    ."  <tr>\n"
    ."    <td class='va_t' style='width:15px;'>"
    ."<img class='std_control b' src='".BASE_PATH."img/sysimg/corner_top_left.gif' width='15' height='18' alt=''/>"
    ."</td>\n"
    ."    <td class='table_admin_h txt_c'>".$title
    .($help!="" ? " ".HTML::draw_icon('help', $help) : "")
    .($report_name!='' && $ID!='' ?
        " ".HTML::draw_icon('print_form', array('report_name'=>$report_name,'ID'=>$ID))
     :
        ""
     )
    ."</td>\n"
    ."    <td class='va_t' style='width:15px;'>"
    ."<img class='std_control b' src='".BASE_PATH."img/sysimg/corner_top_right.gif' width='15' height='18' alt=''/>"
    ."</td>\n"
    .($shadow ?
       "    <td style='width:14px;'>"
       ."<img class='std_control b' src='".BASE_PATH."img/spacer' width='14' height='1' alt='' />"
       ."</td>"
     : "")
    ."  </tr>\n"
    ."</table>\n";
}

function draw_form_field(
    $field,
    $value,
    $type,
    $width = "",
    $selectorSQL = "",
    $reportID = 0,
    $jsCode = "",
    $readOnly = 0,
    $bulk_update = 0,
    $label = "",
    $formFieldSpecial = '',
    $height = ''
) {
    if ($type=='hidden') {
        return "<input type=\"hidden\" id=\"".$field."\" name=\"".$field."\" value=\"".$value."\" />";
    }
    $Obj = new Report_Column;
    $row = array();
    return $Obj->draw_form_field(
        $row,
        $field,
        $value,
        $type,
        $width,
        $selectorSQL,
        $reportID,
        $jsCode,
        $readOnly,
        $bulk_update,
        $label,
        $formFieldSpecial,
        $height
    );
}

function draw_hide_show($div, $text, $expanded = 1)
{
    return
     "<div class='clr_b'></div>\n"
    ."<div id=\"".$div."_show\" "
    .($expanded ? "style=\"display:none\" " : "")
    ."onclick=\"setDisplay('".$div."_show',0);setDisplay('".$div."_hide',1);setDisplay('".$div."_region',1);\""
    ."><h3 style='margin:0;' title='Click to show detail'>"
    ."<img src='".BASE_PATH."img/spacer' class='icons std_control'"
    ." style='margin-top:4px;margin-right:2px;height:13px;width:13px;background-position:-2209px 0px;' alt='' />"
    .$text."</h3>"
    ."</div>\n"
    ."<div id=\"".$div."_hide\" "
    .($expanded ? "" : "style=\"display:none\" ")
    ."onclick=\"setDisplay('".$div."_show',1);setDisplay('".$div."_hide',0);setDisplay('".$div."_region',0);\""
    .">\n"
    ."<h3 style='margin:0;' title='Click to hide detail'>"
    ."<img src='".BASE_PATH."img/spacer' class='icons std_control'"
    ." style='margin-top:4px;margin-right:2px;height:13px;width:13px;background-position:-2196px 0px;' alt='' />"
    .$text."</h3>"
    ."</div>"
    ."<div id=\"".$div."_region\""
    .($expanded ? "" : " style=\"display:none;width:100%; margin:auto;\"")
    .">";
}

function draw_html_content($zone = 1)
{
    return Page::draw_html_content($zone);
}

function draw_html_error_403()
{
    header("Status: 403 Unauthorised", true, 403);
    return Page::draw_http_error('403');
}

function draw_html_error_404()
{
    header("Status: 404 Not Found", true, 404);
    return Page::draw_http_error('404');
}

function draw_section_tabs($arr, $divider_prefix, $selected_section, $js = "")
{
    deprecated();
    $Obj = new HTML;
    return $Obj->draw_section_tabs($arr, $divider_prefix, $selected_section, $js);
}

function draw_section_tab_div($ID, $selected_section)
{
    $safe_ID = str_replace(array('/'), '_', $ID);
    return
    "<div id='section_".$safe_ID."' style='display: ".($selected_section==$safe_ID ? "inline" : "none").";'>";
}

function draw_select_options($sql, $value)
{
    $Obj = new Report_Column();
    return $Obj->draw_select_options($value, $sql);
}

function draw_signin_link()
{
    if (get_userID()) {
        return "";
    }
    return "<a class='fl' href='".BASE_PATH."signin'>[ICON]16 16 2611 Sign In[/ICON]</a>";
}

function draw_signup(
    $initialText = '',
    $confirmText = '',
    $failureText = '',
    $successText = '',
    $emailTo = 0,
    $report_name = 'signup',
    $mail_template = 'user_signup'
) {
    $Obj = new Person;
    return $Obj->draw_signup(
        $initialText,
        $confirmText,
        $failureText,
        $successText,
        $emailTo,
        $report_name,
        $mail_template
    );
}

function draw_sql_debug($title, $sql, $error)
{
    return
     "<table border='1' summary='SQL Error Info'>\n"
    ."  <tr>\n"
    ."    <td bgcolor='#ffffff'><h1>SQL Statement Debugger</h1>\n"
    ."    <h3 style='margin:0;'>$title</h3>\n"
    ."    <pre>".$sql."</pre>\n"
    .($error!="" ? "    <h3 style='margin:0;'>Error:</h3>\n".$error."<br /><br />" : "")
    .x(2)
    ."</td>\n"
    ."  </tr>\n"
    ."</table>\n";
}

function draw_layout($layoutID)
{
    $Obj = new layout($layoutID);
    return $Obj->prepare();
}

function draw_layout_colour($number)
{
    global $system_vars, $page_vars;
    if (isset($page_vars)) {
        return $page_vars['colours']['colour'.$number];
    }
    return $system_vars['colour'.$number];
}

function email_list_manage()
{
    return admin_manage_email_list();
}

function fix_currency_symbols($val)
{
    switch($val) {
        case "£":
            $val = "&#163;";
            break;
    }
    return $val;
}

function fix_ampersands($string)
{
    return preg_replace('/&(?!(?:[a-z][a-z\d]*|#(?:\d+|[xX][a-f\d]+));)/i', '&amp;', $string);
}

function format_bytes($bytes, $precision = 3)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function format_date($YYYYMMDD, $format = false)
{
    global $system_vars;
    if ($YYYYMMDD=='0000-00-00') {
        return "";
    }
    $format = ($format ? $format : $system_vars['defaultDateFormat']);
    sscanf($YYYYMMDD, "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
    $_date =  mktime(0, 0, 0, $_MM, $_DD, $_YYYY);
    return date($format, $_date);
}

function format_datetime($YYYYMMDD_hhmm)
{
    global $system_vars;
    if ($YYYYMMDD_hhmm=='0000-00-00 00:00:00') {
        return "";
    }
    sscanf($YYYYMMDD_hhmm, "%04d-%02d-%02d %02d:%02d", $_YYYY, $_MM, $_DD, $_hh, $_mm);
    $_date =  mktime(0, 0, 0, $_MM, $_DD, $_YYYY);
    return
     date($system_vars['defaultDateFormat'], $_date)." "
    .hhmm_format($_hh.":".$_mm, $system_vars['defaultTimeFormat']==1 || $system_vars['defaultTimeFormat']==3);
}

function format_seconds($seconds)
{
    $hrs = 0;
    $mins = 0;
    $formatted = "";
    if ($seconds > 3600) {
        $hrs = intval($seconds / 3600);
        $seconds = $seconds % 3600;
    }
    if ($seconds >= 60) {
        $mins = intval($seconds / 60);
        $seconds = $seconds % 60;
    }
    if ($hrs > 0) {
        return sprintf("%d:%02d:%02d", $hrs, $mins, $seconds);
    } else {
        return sprintf("%d:%02d", $mins, $seconds);
    }
}

function format_time($hhmm)
{
    global $system_vars;
    if ($hhmm=='00:00:00') {
        return "";
    }
    sscanf($hhmm, "%02d:%02d", $_hh, $_mm);
    return hhmm_format($_hh.":".$_mm, $system_vars['defaultTimeFormat']==1 || $system_vars['defaultTimeFormat']==3);
}

function format_phone($phone)
{
    $number = preg_replace("/[^0-9]/", "", $phone);
    if (strlen($number)==10) {
        return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $number);
    }
    return $phone;
}


function get_browser_safe()
{
  // Uses simple version if browscap.ini not set for system
    if (@$out = get_browser(null, true)) {
        return $out;
    }
    $out = array();
    $browsers =
    array(
      "mozilla",
      "msie",
      "gecko",
      "firefox",
      "konqueror",
      "safari",
      "netscape",
      "navigator",
      "opera",
      "mosaic",
      "lynx",
      "amaya",
      "omniweb");
    $agent = strToLower(@$_SERVER['HTTP_USER_AGENT']);
    $l = strlen($agent);
    for ($i=0; $i<count($browsers); $i++) {
        $browser = $browsers[$i];
        $n = stristr($agent, $browser);
        if (strlen($n)>0) {
            $out["browser"] = $browser;
            $j=strpos($agent, $out["browser"])+$n+strlen($out["browser"])+1;
            preg_match("/([0-9.]+)/", substr($agent, $j, $l-$j), $ver);
            $out["version"] = (isset($ver[1]) ? $ver[1] : '');
        }
    }
    $out["platform"] = "unknown";
    $out["crawler"] = "unknown";
    return $out;
}

function get_color_for_weight($weight = 100, $mincolor = '#808080', $maxcolor = '#800000')
{
    $weight = $weight/100;
    sscanf($mincolor, "#%2x%2x%2x", $minr, $ming, $minb);
    sscanf($maxcolor, "#%2x%2x%2x", $maxr, $maxg, $maxb);
    $r = dechex(intval((($maxr - $minr) * $weight) + $minr));
    $g = dechex(intval((($maxg - $ming) * $weight) + $ming));
    $b = dechex(intval((($maxb - $minb) * $weight) + $minb));
    if (strlen($r) == 1) {
        $r = "0" . $r;
    }
    if (strlen($g) == 1) {
        $g = "0" . $g;
    }
    if (strlen($b) == 1) {
        $b = "0" . $b;
    }
    return "#$r$g$b";
}


function get_dates_in_range($start, $last, $step = '+1 day', $format = 'd/m/Y')
{
  // http://stackoverflow.com/questions/4312439/php-return-all-dates-between-two-dates-in-an-array
    $out =        array();
    $current =    strtotime($start);
    $last =       strtotime($last);
    while ($current <= $last) {
        $out[] =    date($format, $current);
        $current =  strtotime($step, $current);
    }
    return $out;
}

function get_guid_from_string($string)
{
    $charid = strtoupper(md5($string));
    return
        "{"
        .substr($charid, 0, 8).'-'
        .substr($charid, 8, 4).'-'
        .substr($charid, 12, 4).'-'
        .substr($charid, 16, 4).'-'
        .substr($charid, 20, 12)
        ."}";
}

function get_iso3166_country($countryID)
{
    $Obj = new Country($countryID);
    return $Obj->get_iso3166($Obj->get_field('value'));
}

function get_emailAddressAsGif($text, $size = 8, $color = "000000")
{
    $code_arr = array();
    for ($i=0; $i<strlen($text); $i++) {
        $code = dechex(ord(substr($text, $i, 1)));
        if (strlen($code)==2) {
  // drops ascii codes like D and A
            $code_arr[] = $code;
        }
    }
    return
     "<img class='antispam' alt=\"email address\""
    ." src=\"".BASE_PATH."img/encoded/".$color."/".$size."/".implode("", array_reverse($code_arr))."\""
    ." title=\"To protect this email address against spam,\nthe address is shown as a non-machine-readable image.\""
    ." />";
}

function get_icon_for_extension($ext)
{
    switch ($ext){
        case "css":
        case "js":
        case "txt":
            $img = "iconTXT.gif";
            break;
        case "doc":
        case "docx":
        case "dot":
        case "rtf":
            $img = "iconDOC.gif";
            break;
        case "htm":
        case "html":
            $img = "iconHTM.gif";
            break;
        case "gif":
            $img = "iconGIF.gif";
            break;
        case "mp3":
            $img = "iconMP3.gif";
            break;
        case "wma":
            $img = "iconWMA.gif";
            break;
        case "jpg":
        case "jpe":
        case "jpeg":
            $img = "iconJPG.gif";
            break;
        case "php":
            $img = "iconPHP.gif";
            break;
        case "pdf":
            $img = "iconPDF.gif";
            break;
        case "sql":
            $img = "iconSQL.gif";
            break;
        case "csv":
        case "xls":
        case "xlsx":
            $img = "iconXLS.gif";
            break;
        case "zip":
            $img = "iconZIP.gif";
            break;
        default:
            $img = "iconUNKNOWN.gif";
            break;
    }
    return BASE_PATH."img/sysimg/".$img;
}

function get_image_alt($html)
{
    if (substr($html, 0, 4)=="<img") {
        $html = preg_replace('|<img.*?alt\s*=\s*\'(.*?)\'.*?>|', '\1', $html);
    }
    if (substr($html, 0, 5)=="[LBL]") {
        $html = substr($html, 5, strlen($html)-11);
        $html_bits = explode('|', $html);
        $html = array_pop($html_bits);
    }
    $html = convert_safe_to_php($html);
    return strip_tags(preg_replace('(<br />)', ' ', $html));
}

function get_mailsender_to_component_results($mailidentityID = 1)
{
    global $system_vars;
    if ($mailidentityID==1) {
        component_result_set('bounce_email', trim($system_vars['bounce_email']));
        component_result_set('from_email', trim($system_vars['adminEmail']));
        component_result_set('from_name', trim($system_vars['adminName']));
        component_result_set('smtp_authenticate', trim($system_vars['smtp_authenticate']));
        component_result_set('smtp_host', trim($system_vars['smtp_host']));
        component_result_set('smtp_password', trim($system_vars['smtp_password']));
        component_result_set('smtp_port', trim($system_vars['smtp_port']));
        component_result_set('smtp_username', trim($system_vars['smtp_username']));
        return;
    }
    $Obj_Mail_Identity = new Mail_Identity($mailidentityID);
    if (!$row = $Obj_Mail_Identity->load()) {
        return get_mailsender_to_component_results(1);
    }
    component_result_set('bounce_email', trim($row['bounce_email']));
    component_result_set('from_email', trim($row['email']));
    component_result_set('from_name', trim($row['name']));
    component_result_set('smtp_authenticate', trim($row['smtp_authenticate']));
    component_result_set('smtp_host', trim($row['smtp_host']));
    component_result_set('smtp_password', trim($row['smtp_password']));
    component_result_set('smtp_port', trim($row['smtp_port']));
    component_result_set('smtp_username', trim($row['smtp_username']));
}

function get_max_upload_size()
{
    $max_upload =     (int)(ini_get('upload_max_filesize'));
    $max_post =       (int)(ini_get('post_max_size'));
    $memory_limit =   (int)(ini_get('memory_limit'));
    return 1024 * 1024 * min($max_upload, $max_post, $memory_limit);
}

function get_number_with_ordinal($num)
{
    if (($num / 10) % 10 != 1) {
        switch($num % 10){
            case 1:
                return $num . 'st';
            case 2:
                return $num . 'nd';
            case 3:
                return $num . 'rd';
        }
    }
    return $num . 'th';
}

function get_page_vars()
{
    $Obj_Page_vars = new Page_Vars;
    return $Obj_Page_vars->get();
}

function get_person_permission($permission, $group_list = "")
{
    return Person::get_permission($permission, $group_list);
}

function get_person_to_session($username, $password_enc)
{
    $Obj_User =     new User;
    return $Obj_User->get_person_to_session($username, $password_enc);
}

function get_popup_params_for_report_form($report_name)
{
    $Obj = new Report;
    return $Obj->get_popup_params_for_report_form($report_name);
}

function get_popup_size($report_name)
{
    $Obj = new Report;
    return $Obj->get_popup_params_for_report_form($report_name);
}

function get_random_password()
{
    $out =    array();
    $salt = "abcdefghjkmnpqrstuvwxyz234578";
    srand((double)microtime()*1000000);
    $out = "";
    for ($i=0; $i<8; $i++) {
        $num = rand() % 33;
        $out.= substr($salt, $num, 1);
    }
    return $out;
}

function get_js_safe_ID($value)
{
    $value =  get_web_safe_ID($value);
    return str_replace('-', '_', $value);
}

function get_path_safe_filename($filename)
{
    $filename_arr =   explode('.', $filename);
    $ext =            strToLower(array_pop($filename_arr));
    return get_web_safe_ID(implode('.', $filename_arr)).'.'.$ext;
}

function get_title_for_path($value)
{
    $value = trim($value);
    if (strlen($value)>=10 && sanitize('date-stamp', substr($value, 0, 10))==substr($value, 0, 10)) {
        $value = substr($value, 0, 10).title_case_string(str_replace(array('-','_'), " ", substr($value, 10)));
    } else {
        $value = title_case_string(str_replace(array('-','_'), " ", $value));
    }
    $value = str_replace(
        array('1St','2Nd','3Rd','4Th','5Th','6Th','7Th','8Th','9Th','0Th'),
        array('1st','2nd','3rd','4th','5th','6th','7th','8th','9th','0th'),
        $value
    );
    return $value;
}


function get_web_safe_ID($text)
{
    return
    trim(
        preg_replace(
            "/(-)+/",
            '-',
            str_replace(
                array('$','&','%','@','/'),
                array('dollars','and','pc','at','-'),
                str_replace(
                    array('---','--'),
                    '-',
                    str_replace(
                        ' ',
                        '-',
                        str_replace(
                            array(':','!','"','\'',',','.','?','’','[',']','(',')','{','}','#'),
                            '',
                            strToLower($text)
                        )
                    )
                )
            )
        ),
        '-'
    );
}

function get_system_vars()
{
    global $page;
    $Obj_System = new System(SYS_ID);
    $row =        $Obj_System->get_record();
    if ($row===false) {
        switch ($page) {
            case "home":
                print
                     "<p><b>Configuration Issue</b><br />\n"
                    ."This system has an ID of ".SYS_ID.". "
                    ."The database does not contain an entry for that system.<br />\n"
                    ."To sign in anyway, click <a href='".BASE_PATH."signin?print=1'><b>here</b></a></p>";
                break;
            case "signed_in":
                print
                     "<p>You are signed in - click <a href='".BASE_PATH."report/system?print=1'><b>here</b></a>"
                    ." to view systems you can administer.</p>";
                break;
        }
        return false;
    }
    if ($row['db_upgrade_flag']==1) {
        Portal::portal_upgrade();
    }
    if (defined('DEBUG_NO_INTERNET')) {
        $row['akismet_key'] = '';
    }
    component_result_set('systemID', $row['ID']);
    component_result_set('system_URL', trim($row['URL'], '/'));
    component_result_set('system_title', $row['textEnglish']);
    return $row;
}

function get_sql_constants($sql)
{
    global $db, $ID,$selectID, $system_vars, $MM, $YYYY, $reportID;
    $s_arr =
    array(
      "DATABASE_NAME",
      "PERSON_ID",
      "_REPORT_ID",
      "_ID_",
      "MEMBER_ID",
      "REPORT_ID",
      "SELECT_ID",
      "SELECT_MM",
      "SELECT_YYYY",
      "SYS_ID",
      "SYS_URL"
    );
    $r_arr =
    array(
      $db,
      get_userID(),
      $reportID,
      ($ID!='' ? $ID : 0),
      (isset($_SESSION['person']) ? $_SESSION['person']['memberID'] : 0),
      $ID,
      ($selectID!='' ? $selectID : 0),
      $MM,
      $YYYY,
      SYS_ID,
      $system_vars['URL']
    );
    return str_replace($s_arr, $r_arr, $sql);
}

function get_timestamp($date = false)
{
// Don't use adodb for this - quote from lib file:
//   - 18 July 2004 0.15
//    All params in adodb_mktime were formerly compulsory.
//    Now only the hour, min, secs is compulsory.
//    This brings it more in line with mktime (still not identical).
//
// This means that unless given time defaults to 00:00:00
    if (!$date) {
        $date = time();
    }
    return date('Y-m-d H:i:s', $date);
}

function get_timestamp_extended($date = false)
{
// Don't use adodb for this - quote from lib file:
//   - 18 July 2004 0.15
//    All params in adodb_mktime were formerly compulsory.
//    Now only the hour, min, secs is compulsory.
//    This brings it more in line with mktime (still not identical).
//
// This means that unless given time defaults to 00:00:00
    if (!$date) {
        $date = time();
    }
    return date('Y-m-d H:i:s w ', $date).get_week_of_month($date);
}

function get_text_for_listdata($listtype_name, $value)
{
    $Obj = new lst_named_type(false, $listtype_name);
    return $Obj->get_text_for_value($value);
}

function get_userID()
{
    if (!isset($_SESSION['person'])) {
        return false;
    }
    return $_SESSION['person']['ID'];
}


function get_userFullName()
{
    if (!isset($_SESSION['person'])) {
        return false;
    }
    return $_SESSION['person']['NFull'];
}

function get_userPUsername()
{
    if (!isset($_SESSION['person'])) {
        return false;
    }
    return $_SESSION['person']['PUsername'];
}

function get_user_status()
{
    return Person::get_user_status();
}

function get_user_status_text()
{
    return Person::get_user_status_text();
}

function get_uuid()
{
    $chars = md5(uniqid(mt_rand(), true));
    return
     substr($chars, 0, 8).'-'
    .substr($chars, 8, 4).'-'
    .substr($chars, 12, 4).'-'
    .substr($chars, 16, 4).'-'
    .substr($chars, 20, 12);
}

function get_var($key, $default = false)
{
    return (isset($_GET[$key]) ? $_GET[$key] : (isset($_POST[$key]) ? $_POST[$key] : $default));
}

function set_var($key, $value)
{
    $_GET[$key] =     $value;
    $_POST[$key] =    $value;
}

function get_week_of_month($dateTimestamp)
{
    $d = date('j', $dateTimestamp);
    $w = date('w', $dateTimestamp)+1; //add 1 because date returns value between 0 to 6
    $dt= (floor($d % 7)!=0) ? floor($d % 7) : 7;
    $k = ($w-$dt);
    $W= ceil(($d+$k)/7);
    return $W ;
}

function hhmm_format($hhmm, $use_am_pm = false)
{
    if ($hhmm=="") {
        return "";
    }
    $hhmm_arr = explode(':', $hhmm);
    $hh = (int)$hhmm_arr[0];
    $mm = (isset($hhmm_arr[1]) ? lead_zero($hhmm_arr[1], 2) : "00");
    if (!$use_am_pm) {
        return lead_zero($hh, 2).":".$mm;
    }
    if ($hhmm=='12:00') {
        return 'Noon';
    }
    if ($hhmm=='00:00') {
        return 'Midnight';
    }
    if ($hh<12) {
        $ampm = 'am';
    } else {
        if ($hh!=12) {
            $hh = $hh-12;
        }
        $ampm = 'pm';
    }
    return $hh.":".$mm.$ampm;
}

function hhmmss_to_seconds($time)
{
    $timeArr = array_reverse(explode(":", $time));
    $seconds = 0;
    foreach ($timeArr as $key => $value) {
        if ($key > 2) {
            break;
        }
        $seconds += pow(60, $key) * $value;
    }
    return $seconds;
}

function highlight($string, $find)
{
    $find = str_replace("_", "[A-Z0-9]", $find);
    return ($find ? (preg_replace("/(".$find.")/i", "<span class='search_match'>\\1</span>", $string)): $string);
}

function img($submode, $ID, $no_show = 0)
{
    switch ($submode) {
        case "btn_style":
            header("Content-type: image/gif");
            $Obj = new Navbutton_Style;
            $Obj->_set_ID($ID);
            $Obj->sample();
            die;
        break;
    }
}

function img_button($ID, $no_show = 0)
{
    $Obj = new Navbutton($ID);
    return $Obj->Image($no_show);
}

function img_button_sample($ID)
{
    $Obj = new Navbutton_style($ID);
    return $Obj->make_images(false);
}

function lead_zero($text, $places)
{
    return (substr("0000", 0, $places-strlen($text)).$text);
}


function mailto($data)
{
    global $system_vars;
    $mail = new PHPMailer(true);  // We want to throw exceptions
    try {
        $mail->IsSMTP();
        $mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
        $mail->IsHtml(true);
        $mail->CharSet =    (ini_get('default_charset') ? ini_get('default_charset') : "UTF-8");
        $mail->Host =        component_result('smtp_host');
        $mail->SMTPAuth =   component_result('smtp_authenticate');
        $mail->Username =   component_result('smtp_username');
        $mail->Password =   component_result('smtp_password');
        $mail->SetFrom(component_result('from_email'), component_result('from_name'), false);
        if (component_result('bounce_email')!='') {
            $mail->Sender =   component_result('bounce_email');
            $mail->AddCustomHeader('Errors-To:'.component_result('bounce_email'));
        }
        if (isset($data['replyto_email'])) {
            $mail->AddReplyTo(
                $data['replyto_email'],
                (isset($data['replyto_name']) ? $data['replyto_name']: $data['replyto_email'])
            );
        } else {
            $mail->AddReplyTo(
                component_result('from_email'),
                component_result('from_name')
            );
        }
        $mail->AddAddress($data['PEmail'], $data['NName']);
        if (isset($data['cc_email'])) {
            $mail->AddCC($data['cc_email'], $data['cc_name']);
        }
        if (isset($data['bcc_email'])) {
            $mail->AddBCC($data['bcc_email'], $data['bcc_name']);
        }
        $subject =         convert_safe_to_php(str_replace("<br />", "\n", $data['subject']));
        $mail->Subject =     utf8_encode(html_entity_decode($subject));
        $html =
         "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\""
        ." \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
        ."<html xmlns=\"http://www.w3.org/1999/xhtml\""
        ." lang=\"".$system_vars['defaultLanguage']."\" xml:lang=\"".$system_vars['defaultLanguage']."\">\n"
        ."<head>\n"
        ."  <title>".$subject."</title>\n"
        ."  <meta http-equiv=\"Content-Type\" content=\"text/html;"
        ." charset=".(ini_get('default_charset') ? ini_get('default_charset') : "UTF-8")."\"/>\n"
        ."  <meta http-equiv=\"Generator\" content=\""
        .System::get_item_version('system_family')." "
        .System::get_item_version('codebase').".".$system_vars['db_version']
        ."\"/>\r\n"
        .(isset($data['style']) ? "  <style type=\"text/css\">".$data['style']."</style>\n" : "")
        ."</head>\n"
        ."<body>\n"
        .$data['html']."\n"
        ."</body>\n"
        ."</html>\n";
        $mail->Body =         convert_safe_to_php($html);
        if (isset($data['text'])) {
            $mail->AltBody =    convert_safe_to_php(str_replace("<br />", "\n", $data['text']));
        }
        $mail->Send();
        return "Message-ID: ".$mail->MessageID;
    } catch (phpmailerException $e) {
        return $e->getMessage();
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function header_mimetype_for_extension($ext)
{
    switch ($ext){
        case "doc":
            header("Content-type: application/msword");
            break;
        case "mp3":
            header("Content-type: audio/mpeg");
            break;
        case "pdf":
            header("Content-type: application/pdf");
            break;
        case "ppt":
            header("Content-type: application/vnd.ms-powerpoint");
            break;
        case "xls":
            header("Content-type: application/vnd.ms-excel");
            break;
    }
}

// ************************************
// * mkdirs()                         *
// ************************************
// Thanks to baldurien@club-internet.fr for example contributed to
// http://ca.php.net/manual/en/function.mkdir.php
function mkdirs($dir, $mode = 0755)
{
    if (is_dir($dir)) {
        return true;
    }
    $stack = array(basename($dir));
    $path = null;
    while (($d = dirname($dir) )) {
        if (!is_dir($d)) {
            $stack[] = basename($d);
            $dir = $d;
        } else {
            $path = $d;
            break;
        }
    }
    if (( $path = realpath($path) ) === false) {
        return false;
    }

    $created = array();
    for ($n = count($stack) - 1; $n >= 0; $n--) {
        $s = $path . '/'. $stack[$n];
        if (@!mkdir($s, $mode)) {
            for ($m = count($created) - 1; $m >= 0; $m--) {
                rmdir($created[$m]);
            }
            return false;
        }
        $created[] = $s;
        $path = $s;
    }
    return true;
}


// ************************************
// * MM_to_MMM()                      *
// ************************************
function MM_to_MMM($MM)
{
    switch ($MM) {
        case "01":
            return "Jan";
        break;
        case "02":
            return "Feb";
        break;
        case "03":
            return "Mar";
        break;
        case "04":
            return "Apr";
        break;
        case "05":
            return "May";
        break;
        case "06":
            return "Jun";
        break;
        case "07":
            return "Jul";
        break;
        case "08":
            return "Aug";
        break;
        case "09":
            return "Sep";
        break;
        case "10":
            return "Oct";
        break;
        case "11":
            return "Nov";
        break;
        case "12":
            return "Dec";
        break;
    }
}


// ************************************
// * MM_to_MMMM()                     *
// ************************************
function MM_to_MMMM($MM)
{
    switch ($MM) {
        case "01":
            return "January";
        break;
        case "02":
            return "February";
        break;
        case "03":
            return "March";
        break;
        case "04":
            return "April";
        break;
        case "05":
            return "May";
        break;
        case "06":
            return "June";
        break;
        case "07":
            return "July";
        break;
        case "08":
            return "August";
        break;
        case "09":
            return "September";
        break;
        case "10":
            return "October";
        break;
        case "11":
            return "November";
        break;
        case "12":
            return "December";
        break;
    }
}

function pad($text, $places)
{
    $padding = (strLen($text)>$places ?
        " "
     :
        (substr(str_repeat(" ", 120), 0, $places-strLen($text)))
    );
    return $text.$padding;
}

/**
 * A safe empowered glob().
 *
 * Function glob() is prohibited on some server (probably in safe mode)
 * (Message "Warning: glob() has been disabled for security reasons in
 * (script) on line (line)") for security reasons as stated on:
 * http://seclists.org/fulldisclosure/2005/Sep/0001.html
 *
 * safe_glob() intends to replace glob() using readdir() & fnmatch() instead.
 * Supported flags: GLOB_MARK, GLOB_NOSORT, GLOB_ONLYDIR
 * Additional flags: GLOB_NODIR, GLOB_PATH, GLOB_NODOTS, GLOB_RECURSE
 * (not original glob() flags)
 * @author BigueNique AT yahoo DOT ca
 * @updates
 * - 080324 Added support for additional flags: GLOB_NODIR, GLOB_PATH,
 *   GLOB_NODOTS, GLOB_RECURSE
 */
define('GLOB_NODIR', 256);
define('GLOB_PATH', 512);
define('GLOB_NODOTS', 1024);
define('GLOB_RECURSE', 2048);
function safe_glob($pattern, $flags = 0)
{
    $split=explode('/', str_replace('\\', '/', $pattern));
    $mask=array_pop($split);
    $path=implode('/', $split);
    if (($dir=opendir($path))!==false) {
        $glob=array();
        while (($file=readdir($dir))!==false) {
          // Recurse subdirectories (GLOB_RECURSE)
            if (($flags&GLOB_RECURSE) && is_dir($file) && (!in_array($file, array('.','..')))) {
                $glob = array_merge(
                    $glob,
                    array_prepend(
                        safe_glob($path.'/'.$file.'/'.$mask, $flags),
                        ($flags&GLOB_PATH?'':$file.'/')
                    )
                );
            }
          // Match file mask
            if (fnmatch($mask, $file)) {
                if (
                ((!($flags&GLOB_ONLYDIR)) || is_dir($path.'/'.$file)) &&
                ((!($flags&GLOB_NODIR)) || (!is_dir($path.'/'.$file))) &&
                ((!($flags&GLOB_NODOTS)) || (!in_array($file, array('.','..'))))
                ) {
                    $glob[] = ($flags&GLOB_PATH?$path.'/':'') . $file . ($flags&GLOB_MARK?'/':'');
                }
            }
        }
        closedir($dir);
        if (!($flags&GLOB_NOSORT)) {
            sort($glob);
        }
        return $glob;
    } else {
        return false;
    }
}

function sanitize()
{
    $args = func_get_args();
    if (count($args)<2) {
        die ("sanitize requires at least 2 arguments - type and first parameter");
    }
    switch ($args[0]){
        case "ID":
            if (count($args)!=2) {
                die ("Syntax: sanitize('".$args[0]."',\$input)");
            }
            $value_arr = explode(",", str_replace(' ', '', $args[1]));
            foreach ($value_arr as &$value) {
                if ($value>2147483647) {
                    print "Sanitize Error: ID ".$value." is too high - value was set to zero\n\n";
                    $value = 0;
                }
                if ((int)$value<0) {
                    print "Sanitize Error: ID ".$value." is too low - value was set to zero\n\n";
                    $value = 0;
                }
                $value = (int)$value;
            }
            return implode(",", array_unique($value_arr));
        break;
        case "date-format":
            if (count($args)!=3) {
                die ("Syntax: sanitize('".$args[0]."','\$input','\$default')");
            }
            switch($args[1]){
                case "MM DD YYYY":
                case "MM DD, YYYY":
                case "MM DD YYYY h:mmXM":
                case "MM DDD YYYY":
                case "MM DDD YYYY hh:mm":
                case "MM DDD YYYY h:mmXM":
                case "MMM DD, YYYY":
                case "MMM DDD YYYY":
                    return $args[1];
                break;
            }
            return $args[2];
        break;
        case "date-stamp":
            if (count($args)!=2) {
                die ("Syntax: sanitize('".$args[0]."',\$input)");
            }
            $string = trim($args[1], '-');
            if (!$string) {
                return "";
            }
            $bits_arr = explode("-", $string);
            $YYYY  = sanitize('range', $bits_arr[0], 100, 3000, false);
            if (!$YYYY) {
                return '';
            }
            $out = lead_zero($YYYY, 4);
            if (count($bits_arr)==1) {
                return $out;
            }
            $MM = sanitize('range', $bits_arr[1], 1, 12, false);
            if (!$MM) {
                return '';
            }
            $out.= '-'.lead_zero($MM, 2);
            if (count($bits_arr)==2) {
                return $out;
            }
            $DD = sanitize('range', $bits_arr[2], 1, 31, false);
            if (!$DD) {
                return '';
            }
            $out.= '-'.lead_zero($DD, 2);
            if (count($bits_arr)==3) {
                return $out;
            }
            return '';
        break;
        case "enum":
            if (count($args)!=3) {
                die ("Syntax: sanitize('".$args[0]."',\$find,\$array)");
            }
            if (in_array($args[1], $args[2])) {
                return $args[1];
            }
            return $args[2][0];
        break;
        case "enum_csv":
            if (count($args)!=3) {
                die ("Syntax: sanitize('".$args[0]."',\$find,\$array)");
            }
            $value_arr = explode(",", str_replace(' ', '', $args[1]));
            $out = array();
            foreach ($value_arr as &$value) {
                if (in_array($value, $args[2])) {
                    $out[] = $value;
                }
            }
            $result =(count($out) ? implode(",", array_unique($out)) : '');
            return $result;
        break;
        case "hex3":
            if (count($args)!=3) {
                die ("Syntax: sanitize('".$args[0]."','\$input','\$default')");
            }
            return (preg_match('/^(#)?[a-f0-9]{6}$/i', $args[1]) ?  $args[1] :  $args[2]);
        break;
        case "html":
            if (count($args)!=2) {
                die ("Syntax: sanitize('".$args[0]."',\$input)");
            }
            $charset = (ini_get('default_charset') ? ini_get('default_charset') : "UTF-8");
            return htmlentities($args[1], ENT_COMPAT, $charset);
        break;
        case "range":
            if (count($args)!=5) {
                die ("Syntax: sanitize('".$args[0]."',\$input,\$min,\$max,\$default) - \$max may be given as 'n'");
            }
            if (!is_numeric($args[1]) || $args[1]<$args[2] || ($args[3]!='n' && $args[1]>$args[3])) {
                return $args[4];
            }
            return $args[1];
        break;
        case "rss":
            $allowable_tags =
            "<br /><a><b><i><u><strong><em><p><img>";
            $find =
            array(
            "& ",
            "\n",
            "<br />",
            "<br/>",
            "&bull;",
            "•",
            "&copy;",
            "&ccedil;",
            "&eacute;",
            "&ecirc;",
            "&egrave;",
            "&Eacute",
            "&frac12;",
            "&hellip;",
            "&ldquo;",
            "&lsquo;",
            "&mdash;",
            "&ensp;",
            "&nbsp;",
            "&ndash;",
            "&Otilde;",
            "&ocirc;",
            "&rdquo;",
            "&rsquo;",
            "&upsilon;",
            "&mu;",
            "&nu;",
            "&iota;",
            "&sigma;",
            "&sigmaf;",
            );
            $repl =
            array(
            "&amp; ",
            "<br />\n",
            "<br />\n",
            "<br />\n",
            "*",
            "*",
            "<br />\n",
            "(c)",
            "c",
            "e",
            "e",
            "e",
            "E",
            "1/2",
            "...",
            "'",
            "'",
            " ",
            " ",
            " ",
            "-",
            "O",
            "o",
            "'",
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            );
            return str_replace($find, $repl, strip_tags($args[1], $allowable_tags));
        break;
        default:
            die("sanitize doesn't recocognise mode of ".$args[0]);
        break;
    }
}

function SXML_attribute(SimpleXMLElement $object, $attribute)
{
    if (isset($object[$attribute])) {
        return (string)$object[$attribute];
    }
}

function seconds_format($seconds)
{
    $h = floor($seconds/3600);
    $m = floor(($seconds-($h*3600))/60);
    $s = $seconds -($h*3600)-($m*60);
    if ($h==0) {
        return $m.':'.lead_zero($s, 2);
    }
    return $h.':'.lead_zero($m, 2).':'.lead_zero($s, 2);
}

function seconds_to_hhmmss($seconds)
{
    return gmdate("H:i:s", $seconds);
}

function set_cache($expires, $useFile = false, $useDate = false)
{
    $exp_gmt = gmdate("D, d M Y H:i:s", time() + $expires)." GMT";  // don't refresh until expires
    $mod_gmt = gmdate("D, d M Y H:i:s", time() - 3600*10)." GMT";   // Modified 10 hours ago
    if ($useDate) {
     // use the date we were given
        $mod_gmt = gmdate("D, d M Y H:i:s", strtotime($useDate)) . " GMT";
    } elseif ($useFile && file_exists($useFile)) {
      // get the file modified date
        $mod_gmt = gmdate("D, d M Y H:i:s", filemtime($useFile)) . " GMT";
    }
  // get the "If-Modified-Since" REQUEST header
    $clientHeaders = apache_request_headers();
    $if_modified_since =
        array_key_exists('If-Modified-Since', $clientHeaders) ? $clientHeaders['If-Modified-Since'] : '';
    if ($if_modified_since == $mod_gmt) {
   // files are the same, send 304
        header("HTTP/1.0 304 Not Modified");
        exit;
    }
    @header("Expires: $exp_gmt");
    @header("Last-Modified: $mod_gmt");
    @header("Cache-Control: public, max-age=$expires");
    @header("Pragma: !invalid");
}


function setColourIndex(&$image, $i, $string)
{
    sscanf($string, "%2x%2x%2x", $r, $g, $b);
    return Imagecolorset($image, $i, $r, $g, $b);
}

function status_message($status, $html_format, $object, $extras, $operation, $targetID)
{
    $out = "";
    $qty =    count(explode(",", $targetID));
    if ($qty==0 || $targetID=="") {
        $thisthese =    "No";
        $plural =       "s";
        $havehas =      "have";
    } elseif ($qty==1) {
        $thisthese =    "The";
        $plural =       "";
        $havehas =      "has";
    } else {
        $thisthese =    "The ".$qty;
        $plural =       "s";
        $havehas =      "have";
    }
    if ($html_format) {
        switch ($status) {
            case 0:
                $out.=    "<span style='background-color:#e0ffe0; color:#008000; border: solid 1px #008000;'>&nbsp;<b>";
                break;
            case 1:
                $out.=    "<span style='background-color:#FFEACD; color:#c07000; border: solid 1px #c07000;'>&nbsp;<b>";
                break;
            case 2:
                $out.=    "<span style='background-color:#FFE1E1; color:#ff0000; border: solid 1px #ff0000;'>&nbsp;<b>";
                break;
        }
    }
    switch ($status) {
        case 0:
            $out.=    "Success:";
            break;
        case 1:
            $out.=    "Warning:";
            break;
        case 2:
            $out.=    "Error:";
            break;
    }
    $out.=
     ($html_format ? "</b>" : "")
    ." ".$thisthese." ".$object.$plural." "
    .($status==0 && $extras=="" ? $havehas : $extras)
    ." ".$operation
    .($html_format ? "&nbsp;</span>" : "")
    ;
    return $out;
}

function strip_only($str, $tags_arr)
{
    if (!is_array($tags_arr)) {
        $tags_arr = (strpos($str, '>') !== false ? explode('>', str_replace('<', '', $tags_arr)) : array($tags_arr));
        if (end($tags_arr) == '') {
            array_pop($tags_arr);
        }
    }
    foreach ($tags_arr as $tag) {
        $str = preg_replace('#</?'.$tag.'[^>]*>#is', '', $str);
    }
    return $str;
}

function table_uniqID($table, $db = '')
{
    $Obj = new Record($table);
    if ($db!='') {
        $Obj->set_db_name($db);
    }
    return $Obj->uniqID();
}

function title_case_string($text)
{
    // Ref: http://www.sitepoint.com/blogs/2005/03/15/title-case-in-php
    // Our array of 'small words' which shouldn't be capitalised if
    // they aren't the first word. Add your own words to taste.
    $smallwordsarray =
    array(
      'of','a','the','and','an','or','nor','but','is','if','then','else','when',
      'at','from','by','on','off','for','in','out','over','to','into','with'
    );
    // Split the string into separate words
    $words = explode(' ', $text);
    foreach ($words as $key => $word) {
        // If this word is the first, or it's not one of our small words, capitalise it
        if ($key == 0 or !in_array($word, $smallwordsarray)) {
            $words[$key] = mb_convert_case($word, MB_CASE_TITLE);
        }
    }
    return implode(' ', $words);
}

function x($shift = 0)
{
    $trace = debug_backtrace();
    if (!isset($trace[1])) {
        return "";
    }
    for ($i=0; $i<$shift; $i++) {
        array_shift($trace);
    }
    $message =    "<pre><b>Call stack trace:</b>\n";
    for ($i=1; $i<count($trace)&&$i<=50; $i++) {
        $message.=
         "[".lead_zero(count($trace)-$i-1, 2)."] "
        ."<span style='color:#ff0000'><b>"
        .(isset($trace[$i]['class']) ? $trace[$i]['class']."::" : "")
        .$trace[$i]['function']."()"
        ."</b></span>"
        .(isset($trace[$i]['file']) ? " via <span style='color:#008080'><b>".$trace[$i]['file']."</b></span>" : '')
        .(isset($trace[$i]['line']) ? " line <span style='color:#000080'><b>".$trace[$i]['line']."</b></span>" : '')
        ."\n";
    }
    $message.= "</pre>";
    return $message;
}

function y()
{
    $args = func_get_args();
    $out = "";
    foreach ($args as $var) {
        $out.="<pre>".print_r($var, true)."</pre>\n";
    }
    print $out;
    return $out;
}

function z($sql)
{
    print "<pre>".$sql."</pre>";
}

function two_dp($value)
{
    return number_format($value, 2, '.', '');
}

function three_dp($value)
{
    return number_format($value, 3, '.', '');
}

function XOREncryption($input, $key)
{
    $keyLen = strlen($key);
    for ($i = 0; $i < strlen($input); $i++) {
        $rPos = $i % $keyLen;
        $r = ord($input[$i]) ^ ord($key[$rPos]);
        $input[$i] = chr($r);
    }
    return $input;
}

function XOREncrypt($input)
{
    $key = md5(component_result('systemID'));
    $output = XOREncryption($input, $key);
    $output = base64_encode($output);
    return $output;
}

function XORDecrypt($input)
{
    $key = md5(component_result('systemID'));
    $output = base64_decode($input);
    $output = XOREncryption($output, $key);
    return $output;
}
