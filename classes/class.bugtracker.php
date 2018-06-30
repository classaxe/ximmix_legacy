<?php
define('VERSION_BUGTRACKER','1.0.6');
/*
Version History:
  1.0.6 (2012-11-28)
    1) BugTracker::draw_form() now uses System::get_item_version() not
       System::get_version() as before

  (Older version history in class.bug_tracker.txt)
*/
class BugTracker{
  private $url =        "";
  private $username =   "";
  private $password =   "";
  private $cookies =    '/tmp/bugtracker_cookies.txt';

  function __construct($url=false,$username=false,$password=false) {
    global $system_vars;
    $this->url =        ($url ?      $url :      $system_vars['bugs_url']);
    $this->username =   ($username ? $username : $system_vars['bugs_username']);
    $this->password =   ($password ? $password : $system_vars['bugs_password']);
  }

  public function connect(){
    $request =
       'email='.urlencode($this->username)
      .'&password='.urlencode($this->password);
    $url =          trim($this->url,'/')."/login.php";
    $ObjCurl =      new Curl($url,$request,$this->cookies);
    $result =       $ObjCurl->get();
    return $result;
  }

  public function disconnect(){
    $url =          trim($this->url,'/')."/logout.php";
    $ObjCurl =      new Curl($url,'',$this->cookies);
    $result =       $ObjCurl->get();
    return $result;
  }

  public function get_project_id(){
    $url =          trim($this->url,'/')."/index.php";
    $ObjCurl =      new Curl($url,'',$this->cookies);
    $result =       $ObjCurl->get();
    preg_match_all("/<a href=\"project_list.php\?project_id=([^>]+)\">([^<]+)<\/a>/",$result,$results,PREG_SET_ORDER);
    $projects  = array();
    foreach($results as $result){
      if(strToLower($result[2])==strToLower(SYSTEM_FAMILY)){
        return array('name'=>$result[2],'project_id'=>$result[1]);
      }
    }
    foreach($results as $result){
      if(strToLower($result[2])=='ecclesiact' || strToLower($result[2])=='ximmix'){
        return array('name'=>$result[2],'project_id'=>$result[1]);
      }
    }
    return false;
  }

  public function post_bug_report($projectID,$args){
    $url =          trim($this->url,'/')."/report_donew.php";
    $request =
       'project_id='.urlencode($projectID)
      .'&description='.urlencode($args['bugtracker_form_description'])
      .'&priority='.urlencode($args['bugtracker_form_priority'])
      .'&reproducibility='.urlencode($args['bugtracker_form_reproducibility'])
      .'&summary='.urlencode($args['bugtracker_form_summary'])
      .'&type='.urlencode($args['bugtracker_form_type'])
      .'&version='.urlencode($args['bugtracker_form_version'])
      ;
    $ObjCurl =      new Curl($url,$request,$this->cookies);
    $result =       $ObjCurl->get();
  }

  public function draw_form(){
    $msg =          "";
    $html =         "";
    $js =           "";
    $css =          "";
    $ajax_mode =    (isset($_REQUEST['ajax']) ? $_REQUEST['ajax'] : "");
    $submode =      (isset($_REQUEST['submode']) ? $_REQUEST['submode'] : "");
    $bugtracker_link =
       "Click <b>"
      ."<a style='color:blue;text-decoration:underline;'"
      ." href=\"".$this->url."?v1=".base64_encode($this->username)."&v2=".base64_encode($this->password)."\""
      ." onclick=\"popWin(this.href,'bugs','resizable,scrollbars',800,600);return false;\">"
      ."here</a></b> to Login to Bug Tracker to see the status of previously posted bug reports.<br />"
      ."Remember to Logout of Bug Tracker once you have finished reviewing your reports.";
    switch ($submode){
      case "bugtracker_submit":
        $result =   $this->connect();
        if ($result!=='1'){
          $msg =      "<b>Error:</b> Unable to connect to Bug Tracker: ".$result;
          $js.=       "  status_message_show('form_status_bugtracker',\"".$msg."\",2);\n";
          $html =    ($ajax_mode ? "<p class='txt_c'><input class='formButton' type='button' value='Close' onclick='hidePopWin(null);response=false;' /></p>" : "");
          break;
        }
        if (!$project = $this->get_project_id()){
          $this->disconnect();
          $msg =      "<b>Error:</b> Unable to locate a relevant project at Bug Tracker";
          $js.=       "  status_message_show('form_status_bugtracker',\"".$msg."\",2);\n";
          $html =    ($ajax_mode ? "<p class='txt_c'><input class='formButton' type='button' value='Close' onclick='hidePopWin(null);response=false;' /></p>" : "");
          break;
        }
        $ObjSystem =    new System(SYS_ID);
        $ObjSystem->load();
        $browser =  get_browser_safe();
        $personID = get_userID();
        $_rights =  explode('|',get_user_status());
        $rights =   "";
        for($i=0; $i<count($_rights); $i+=3){
          $rights .=
             ($i==0 ? "" : "*          ")
            .pad((isset($_rights[$i]) ?    $_rights[$i]   : ""),15)
            .pad((isset($_rights[$i+1]) ?  $_rights[$i+1] : ""),15)
            .pad((isset($_rights[$i+2]) ?  $_rights[$i+2] : ""),15)
            ."*\n";
        }
        $description =
           "<pre style='line-height:1em;color:#f44;'>*********************************************************\n"
          ."* URL:     ".pad($ObjSystem->record['URL'],45)."*\n"
          ."* Agent:   ".pad($browser['browser']." ".$browser['version'],45)."*\n"
          ."* Perms:   ".$rights
          ."*********************************************************\n</pre>";
        $project_name = $project['name'];
        $project_id =   $project['project_id'];
        $args =
          array(
            'bugtracker_form_description' =>        $description.nl2br(get_var('bugtracker_form_description')),
            'bugtracker_form_priority' =>           get_var('bugtracker_form_priority'),
            'bugtracker_form_reproducibility' =>    get_var('bugtracker_form_reproducibility'),
            'bugtracker_form_summary' =>            get_var('bugtracker_form_summary'),
            'bugtracker_form_type' =>               get_var('bugtracker_form_type'),
            'bugtracker_form_version' =>            get_var('bugtracker_form_version'),
          );
        $result = $this->post_bug_report($project_id,$args);
        $this->disconnect();
        $msg =      "";
        $js.=       "  status_message_show('form_status_bugtracker',\"".$msg."\",0);\n";
        $html.=
           "<h1 style='margin:0;'>Bug Tracker</h1>\n"
          ."<p>Thanks for submitting a bug report.<br />\n"
          .$bugtracker_link
          ."</p>"
          .($ajax_mode ?
               "<div style=\"text-align:center;padding:5px 0 0 0;\">\n"
              ."<input type='button' id='bugtracker_form_cancel' class='formButton' style='width:100px' value='Done'"
              ." onclick=\"hidePopWin(null);response=false;\" />\n"
              ."</div>"
            : ""
           );
      break;
      default:
        $label_width =  105;
        $field_width =  360;
        $status = System::get_item_version('bugtracker_status');
        if ($status=='Pass'){
          $html.=
             "<h1 style='margin:0;'>Submit a Report to Bug Tracker</h1>\n"
            ."<p>".$bugtracker_link."</p>"
            ."<div class='fl' style='width:".$label_width."px'><label for='bugtracker_form_summary'><b>Summary</b></label></div>\n"
            ."<div class='fl'>".draw_form_field('bugtracker_form_summary','','text',$field_width)."</div>"
            ."<div class='clr_b'></div>\n"
            ."<div class='fl' style='width:".$label_width."px'><b>Version</b></div>\n"
            ."<div class='fl'>".draw_form_field('bugtracker_form_version',System::get_item_version('build'),'hidden')."<div class='formField' style='width:".$field_width."px;background-color:#f0f0f0;color:#808080'>".System::get_item_version('build')."</div></div>"
            ."<div class='clr_b'></div>\n"
            ."<div class='fl' style='width:".$label_width."px'><b>Type</b></div>\n"
            ."<div class='fl'>".draw_form_field('bugtracker_form_type','1','selector_csvlist',$field_width,'','','','','','','1|Bug|ffb0b0,3|Usability Issue|ffe080,2|Feature Request|e0ffe0')."</div>"
            ."<div class='clr_b'></div>\n"
            ."<div class='fl' style='width:".$label_width."px'><b>Priority</b></div>\n"
            ."<div class='fl'>".draw_form_field('bugtracker_form_priority','3','selector_csvlist',$field_width,'','','','','','','1|Very Low|c0ffc0,2|Low|e0ffd0,3|Normal|ffffd0,4|High|ffe0d0,5|Very High|ffb0b0')."</div>"
            ."<div class='clr_b'></div>\n"
            ."<div class='fl' style='width:".$label_width."px'><b>Reproducibility</b></div>\n"
            ."<div class='fl'>".draw_form_field('bugtracker_form_reproducibility','0','selector_csvlist',$field_width,'','','','','','','0|Untried|d0d0d0,1|Rarely|d0b0b0,2|Sometimes|f0a0a0,3|Always|ffb0b0')."</div>"
            ."<div class='clr_b'></div>\n"
            ."<div><b>Details with steps to reproduce</b></div>\n"
            ."<div>"
            .draw_form_field('bugtracker_form_description','','textarea',($label_width+$field_width),'','','','','','','',260)
            ."</div>"
            ."<div style=\"width:200px;margin:auto;\">\n"
            .($ajax_mode ?
                 "<input type='button' id='bugtracker_form_cancel' class='fl formButton' style='width:100px' value='Cancel'"
                ." onclick=\"hidePopWin(null);response=false;\" />\n"
              : ""
             )
            ."<input type='button' id='bugtracker_form_submit' class='fl formButton' style='width:100px' value='Submit'"
            ." onclick=\""
            ."if(geid_val('bugtracker_form_summary')==''){alert('You must provide a summary');return false}"
            ."if(geid_val('bugtracker_form_description')==''){alert('Please provide some details - if this is a bug report please include steps to reproduce the problem noted.');return false}"
            .($ajax_mode ? "bugtracker_form_onsubmit();" : "geid_set('submode','bugtracker_submit');geid('form').submit()")
            ."\" /><br class='clr_b' />\n"
            ."</div>\n";
        }
        else {
          $html.=
             "<h1 style='margin:0;'>Bug Tracker</h1>\n"
            ."<p>".$status."<br />\nPlease check your account settings.</p>"
            ."<div style=\"width:100px;margin:auto;\">\n"
            .($ajax_mode ?
                 "<input type='button' id='bugtracker_form_cancel' class='fl formButton' style='width:100px' value='Close'"
                ." onclick=\"hidePopWin(null);response=false;\" />\n"
              : ""
             )
            ."<br class='clr_b' /></div>";
        }
      break;
    }
    $html =
       HTML::draw_status('bugtracker',$msg)
      .$html;
    if ($ajax_mode) {
      $Obj_json =     new Services_JSON(SERVICES_JSON_LOOSE_TYPE); // so we get an assoc array as output instead of some weird object
      header('Content-Type: application/json');
      $html =  convert_safe_to_php($html);
      print $Obj_json->encode(
        array(
          'css' =>  $css,
          'html' => str_replace("\n","\r\n",$html),
          'js' =>   str_replace("\n","\r\n",$js)
        )
      );
      die;
    }
    Page::push_content('javascript_onload',$js);
    return $html;
  }

  public function get_version(){
    return VERSION_BUGTRACKER;
  }
}

?>