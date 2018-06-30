<?php
  define ("VERSION_COMPONENT_SIGNUP","1.0.2");
/*
Version History:
  1.0.2 (2013-06-03)
    1) Added new CP for width
  1.0.1 (2013-05-28)
    1) Split into smaller functions and corrected workflow
  1.0.0 (2013-05-25)
    1) Initial release - moved from Person class
*/
class Component_Signup extends Component_Base {
  protected $_msg = '';
  protected $_submode;

  public function __construct(){
    $this->_ident =         'signup';
    $this->_parameter_spec = array(
      'email_for_offline' =>    array('match' => 'enum|0,1',    'default'=>'0',                     'hint'=>'Email address to send details if new accounts require administrator setup'),
      'email_template' =>       array('match' => '',            'default'=>'user_signup',           'hint'=>'Email template to send new account details to upon successful registration'),
      'report_name' =>          array('match' => '',            'default'=>'signup',                'hint'=>'Name of report to use for signup form fields'),
      'shadow' =>               array('match' => 'enum|0,1',    'default'=>'0',                     'hint'=>'Whether or not to draw shadow round dialog'),
      'text_done' =>            array('match' => '',            'default'=>'Done',                  'hint'=>'Your Text Here'),
      'text_initial' =>         array('match' => '',            'default'=>'<p>Create a new access account using this form.   Full membership access may be granted or denied by the site administrator.   Until then you will be able to update your profile or set your password after logging in, but full member access will not be available.</p>',                      'hint'=>'Your Text Here'),
      'text_confirm' =>         array('match' => '',            'default'=>'<h3>Confirm New Account Details</h3><p>Please confirm the details you have given or go <a href=\'#\' onclick=\'history.go(-1);return false;\'><b>back</b></a> and make changes as required.</p>',                      'hint'=>'Your Text Here'),
      'text_success' =>         array('match' => '',            'default'=>"<h3>Success!</h3><p>Your initial password has been emailed to the address you gave.</p>",                      'hint'=>'Your Text Here'),
      'width' =>                array('match' => 'range|0,n',   'default' =>'',                     'hint'=>'Blank to accept default, or number to specify form width')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_do_submode();
    return $this->_html;
  }

  protected function _draw_signup_confirmation(){
    $Obj_Report = new Report;
    $this->_html.=
       $this->_cp['text_confirm']
      ."<table style='background-color:#ffffff;margin:auto;' cellpadding='2' cellspacing='0' border='1' class='table_border'>\n"
      ."  <tr>\n"
      ."    <td>".$Obj_Report->draw_form_view($this->_cp['report_name'],0,false,false)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td class='txt_c' style='background-color:#ffffff'><img style='border:2px dashed #f00' src='./?command=captcha_img' alt='Verification Image' /><br />"
      ."<label>Type the letters and numbers you see<br />"
      ."(Only lowercase letters are shown)</label><br />"
      ."<input type='text' style='width:80px;background-color:#e0ffe0;border:1px dashed #f00' name='captcha_key' value='' />"
      ."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td align='center'>"
      ."<input type='button' value='Confirm' onclick=\"geid_set('submode','confirm');geid('form').submit();\" style=\"width:80px;\" /> "
      ."<input type='button' value='Go Back' onclick=\"history.back();\" style=\"width:80px;\" />"
      ."    </td>\n"
      ."  </tr>\n"
      ."</table>\n"
      ."<br /><br />\n";
  }

  protected function _draw_signup_form(){
    $Obj_Report_Form =  new Report_Form;
    $content =          $Obj_Report_Form->draw($this->_cp['report_name'],false,false,false,$this->_cp['width']);
    $controls =         "<input type='button' value='Submit' onclick=\"if (signup_required(geid('form'))) { geid('submode').value='save';geid('form').submit();}\" class='formbutton' style='width: 60px;'/>";
    $this->_html.=
       $this->_cp['text_initial']
      .HTML::draw_form_box("Signup",$content,'_help_user_signin_new',$this->_cp['shadow'],false,$controls);
  }

  protected function _draw_status(){
    if ($this->_msg==''){
      return;
    }
    $error =        substr(strip_tags($this->_msg),0,5)=='Error';
    $text_color =   ($error ? '#ff0000' : '#008000');
    $back_color =   ($error ? '#ffe0e0' : '#e0ffe0');
    $this->_html.=
       "<div style='color:".$text_color.";border:solid 1px ".$text_color."; background:".$back_color."; padding: 0.25em; margin:1em 0'>"
      .$this->_msg
      ."</div>";
  }

  protected function _do_submode(){
    global $system_vars;
    switch($this->_submode) {
      case "":
        $this->_draw_signup_form();
      break;
      case "save":
        $this->_do_submode_check_unique(1);
        if ($this->_submode==''){
          break;
        }
        $this->_draw_signup_confirmation();
      break;
      case "confirm":
        $this->_do_submode_check_unique(2);
        if ($this->_submode==''){
          break;
        }
        $this->_do_submode_check_captcha();
        if ($this->_submode==''){
          break;
        }
        switch ($system_vars['system_signup']) {
          case 1:
          case "Email":
            $this->_do_submode_admin_email();
          break;
          case 2:
          case "Online":
            $this->_do_submode_create_user();
          break;
          default:
            $this->_msg = "<b>Error:</b> This site is not currently set up to allow registration online";
            $this->_draw_status();
          break;
        }
      break;
    }
  }

  protected function _do_submode_admin_email(){
    global $system_vars;
    get_mailsender_to_component_results(); // Use system default mail sender details
    if ($emailTo==0) {
      $emailTo = $system_vars['adminEmail'];
    }
    component_result_set('PEmail',$emailTo);
    component_result_set('NName',$emailTo);
    component_result_set('from_name',(isset($_POST['NFirst']) ? $_POST['NFirst']." " : "").$_POST['NLast']);
    component_result_set('from_email',$_POST['PEmail']);
    $text_arr =     array();
    $html_arr =     array();
    $Obj_Report =   new Report;
    $reportID =     $Obj_Report->get_ID_by_name($this->_cp['report_name']);
    $Obj_Report->_set_ID($reportID);
    $columns = $Obj_Report->get_columns();
    for ($i=0; $i<count($columns); $i++) {
      $label = $columns[$i]['formLabel'];
      $field = $columns[$i]['formField'];
      if ($columns[$i]['permPUBLIC']=='1') {
        $value = $_POST[$field];
        $text_arr[] = pad($label,25).$value."\n";
        $html_arr[] = "  <tr><th align='left'>$label</th><td>".($value=="" ? "&nbsp;" : $value)."</td></tr>\n";
      }
    }
    component_result_set('content_html',
      "<h3>Signup Request Received</h3>\n"
     ."<table cellpadding='2' cellspacing='0' border='1' bordercolor='#808080' bgcolor='#ffffff'>"
     ."  <tr>\n"
     ."    <th align='left'>Field</th>\n"
     ."    <th align='left'>Value</th>\n"
     ."  </tr>\n"
     .implode("",$html_arr)
     ."</table>"
    );
    component_result_set('content_text',
       "Signup Request Received\n"
      .pad("FIELD",25)."VALUE\n"
      ."---------------------------------------------------------\n"
     .implode("",$text_arr)
    );
    $data =               array();
    $data['PEmail'] =     component_result('PEmail');
    $data['NName'] =      component_result('NName');
    $data['subject'] =    "New User Signup Request";
    $data['html'] =       component_result('content_html');
    $data['text'] =       component_result('content_text');
    $mail_result = mailto($data);
    if (substr($mail_result,0,12)=="Message-ID: ") {
      return  $successText;
    }

    return $mail_result;
  }

  protected function _do_submode_check_captcha(){
    $Obj = new Captcha;
    if (!$Obj->isKeyRight(isset($_REQUEST['captcha_key']) ? $_REQUEST['captcha_key'] : "")) {
      $this->_submode=  '';
      $this->_msg.=     "<b>Error:</b> The verification text you entered didn't match the picture we showed you.";
      $this->_draw_status();
      $this->_html.=
        "<p>Please go <a href='#' onclick='history.go(-1);return false'><b>back</b></a>"
       ." and make changes as required.</p>";
    }
  }

  protected function _do_submode_check_unique($step=1){
    if (get_var('PEmail') && $this->_Obj_Person->exists_emailaddress(get_var('PEmail'))) {
      $this->_submode = '';
      $this->_msg.=     "<b>Error:</b> The email address you gave is already in use.<br />\n";
    }
    if (get_var('PUsername') && $this->_Obj_Person->exists_username(get_var('PUsername'))) {
      $this->_submode = '';
      $this->_msg.=     "<b>Error:</b> The username you requested is already in use.<br />\n";
    }
    if (!$this->_submode){
      $this->_draw_status();
      $this->_html.=
        "<p>Please go <a href='#' onclick='history.go(-".$step.");return false'><b>back</b></a>"
       ." and make changes as required.</p>";
      return;
    }
  }

  protected function _do_submode_create_user(){
    global $page_vars;
    $data =
      array(
        'permACTIVE' => 1,
        'systemID' =>   SYS_ID
      );
    $Obj_Report =   new Report;
    $reportID =     $Obj_Report->get_ID_by_name($this->_cp['report_name']);
    $Obj_Report->_set_ID($reportID);
    $columns =      $Obj_Report->get_columns();
    $Obj_Person =   new Person;
    foreach ($columns as $column) {
      $field = $column['formField'];
      switch ($field){
        case 'systemID':
        break;
        default:
          $data[$field] = (isset($_POST[$field]) ? addslashes($_POST[$field]) : "");
        break;
      }
    }
    $personID = $Obj_Person->insert($data);
    $Obj_Person->_set_ID($personID);
    $Obj_Report =       new Report;
    $reportID =         $Obj_Report->get_ID_by_name($this->_cp['report_name']);
    $Obj_Action =       new Action;
    $sourceID =         $reportID;
    $sourceType =       'report';
    $sourceTrigger =    'report_insert';
    $triggerID =        $personID;
    $triggerType =      'person';
    $triggerObject =    'Person';
    $Obj_Action->execute($sourceType,$sourceID,$sourceTrigger,$personID,$triggerType,$triggerObject,$triggerID);
    $mail_result = $Obj_Person->do_email_signup($this->_cp['email_template']);
    if (substr($mail_result,0,12)=="Message-ID: ") {
      $this->_html.=
        $this->_cp['text_success']
        ."<p><input type='button' onclick=\"window.location='".BASE_PATH.trim($page_vars['path'],'/')."'\" value=\"".$this->_cp['text_done']."\"></p>\n";
    }
    else {
      $Obj_Person->delete();
      $this->_msg =
         "<b>Error:</b> There was a problem sending to your email address:<br />\n"
        .$mail_result."<br />\n"
        ."The new account was not completed.";
      $this->_draw_status();
      $this->_html.=
        "<p>Please go <a href='#' onclick='history.go(-2);return false'><b>back</b></a>"
       ." and make changes as required.</p>";

    }
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_Obj_Person = new Person;
    $this->_submode = get_var('submode');
  }

  public function get_version(){
    return VERSION_COMPONENT_SIGNUP;
  }
}
?>