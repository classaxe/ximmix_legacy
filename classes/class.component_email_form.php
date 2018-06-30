<?php
  define ("VERSION_COMPONENT_EMAIL_FORM","1.0.1");
/*
Version History:
  1.0.1 (2013-10-29)
    1) Brought component up to date with latest standards
    2) Now sets 'reply to' address to Email field if given
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Email_Form extends Component_Base {
  protected $_submode;
  protected $_email_body_html;
  protected $_email_body_text;
  protected $_email_errors = "";
  protected $_email_subject;

  public function __construct(){
    global $system_vars;
    $this->_ident =             'email_form';
    $this->_parameter_spec = array(
      'address' =>      array('match' => '',				'default'=>$system_vars['adminEmail'],    'hint'=>'user1@example.com, user2@example.com, ...'),
      'success' =>      array('match' => '',				'default'=>'contact-success',             'hint'=>'Page to display if succeeded'),
      'title' =>        array('match' => '',				'default'=>'Form submission from (system) via (page)',                            'hint'=>'Title to give email message - automatically set if not specified')
    );
  }

  function draw($instance='',$args=array(),$disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    if (!$this->_do_submode()){
      return $this->_html."<br />";
    }
    header("Location: ".BASE_PATH.$this->_cp['success']);
  }

  protected function _do_submode(){
    if (!$this->_submode=='send'){
      return false;
    }
    $this->_do_submode_prepare_message();
    $this->_do_submode_send_email();
    if ($this->_email_errors!=''){
      $msg = "<b>Error:</b><br />".str_replace("\n",'',trim(nl2br($this->_email_errors),"\n"));
      $this->_html.=    HTML::draw_status('contact_form_status',$msg);
      return false;
    }
    return true;
  }

  protected function _do_submode_prepare_message(){
    global $system_vars, $page_vars;
    $this->_email_subject =
      ($this->_cp['title']!='Form submission from (system) via (page)' ?
         $this->_cp['title']
       :
         "Form submission from ".$system_vars['textEnglish']." via ".trim($page_vars['path'],'/')
       );
    $this->_email_body_html =
      "<h1>".$this->_email_subject."</h1>\n"
     ."<table cellpadding='2' cellspacing='0' border='1' bordercolor='#808080' bgcolor='#ffffff'>\n"
     ."  <tr>\n"
     ."    <th align='left' style='text-align:left;background-color:#e0e0e0;'>Field</th>\n"
     ."    <th align='left' style='text-align:left;background-color:#e0e0e0;'>Value</th>\n"
     ."  </tr>\n";
    $this->_email_body_text =
       $this->_email_subject."\n"
      .pad("FIELD",25)."VALUE\n"
      ."---------------------------------------------------------\n";
    $ignore_arr =   explode(",",SYS_STANDARD_FIELDS);
    foreach ($_POST as $field=>$value) {
      if ($value!=="" && !in_array($field,$ignore_arr) && substr($field,0,19)!='poll_max_votes_for_'){
        $value = sanitize('html',$value);
        $this->_email_body_text.= pad($field,25).$value."\n";
        $this->_email_body_html.=
           "  <tr>\n"
          ."    <th align='left' style='text-align:left'>".$field."</th>\n"
          ."    <td>".($value=="" ? "&nbsp;" : $value)."</td>\n"
          ."  </tr>\n";
      }
    }
    $this->_email_body_html.= "</table>";
  }

  protected function _do_submode_send_email(){
    global $system_vars;
    get_mailsender_to_component_results(); // Use system default mail sender details
    component_result_set('from_name',$system_vars['adminName']);
    component_result_set('from_email',$system_vars['adminEmail']);
    $data =             array();
    $data['subject'] =  $this->_email_subject;
    $data['html'] =     $this->_email_body_html;
    $data['text'] =     $this->_email_body_text;
    if (get_var('Email')){
      $data['replyto_email'] = get_var('Email');
      if (get_var('Name')){
        $data['replyto_name'] = get_var('Name');
      }
    }
    $PEmail_arr =       explode(',',$this->_cp['address']);
    foreach($PEmail_arr as $PEmail){
      $data['PEmail'] = trim($PEmail);
      $data['NName'] =  trim($PEmail);
      $mail_result = mailto($data);
      if (substr($mail_result,0,12)!="Message-ID: ") {
        $this->_email_errors.= $mail_result."\n";
      }
    }
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_submode = get_var('submode');
  }

  public function get_version(){
    return VERSION_COMPONENT_EMAIL_FORM;
  }
}
?>