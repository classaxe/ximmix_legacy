<?php
define ("VERSION_COMPONENT_EVENT_REGISTRATION","1.0.7");
/*
Version History:
  1.0.7 (2014-02-06)
    1) Now invokes Report_Form_Field_Lookup class to handle ajax lookup

  (Older version history in class.component_event_registration.txt)
*/
class Component_Event_Registration extends Component_Base {
  protected $_msg =                 '';
  protected $_eventID =             false;
  protected $_personID =            false;
  protected $_PUsername =           '';
  protected $_user_is_registered =  false;
  protected $_Obj_Person_att =      false;
  protected $_Obj_Person_inv =      false;
  protected $_Obj_Event =           false;
  protected $_Obj_Register_Event =  false;

  public function __construct(){
    $this->_ident =             'register_event';
    $this->_parameter_spec = array(
      'guest' =>                            array('match' => 'enum|0,1',    'default'=>'0',             'hint'=>'0|1'),
      'guest_lookup_username' =>            array('match' => '',            'default'=>'0',             'hint'=>'0|1'),
      'guest_get_company' =>                array('match' => 'enum|0,1',    'default'=>'0',             'hint'=>'0|1'),
      'guest_search' =>                     array('match' => 'enum|0,1',    'default'=>'0',             'hint'=>'0|1'),
      'guest_search_header' =>              array('match' => '',            'default'=>'',              'hint'=>'HTML content to display before member search control'),
      'guest_search_footer_initial' =>      array('match' => '',            'default'=>'',              'hint'=>'Initial HTML content to display after member search control until user begins a search'),
      'guest_search_report' =>              array('match' => '',            'default'=>'User Lookup',   'hint'=>'Name of report to use for lookup'),
      'guest_search_report_field' =>        array('match' => '',            'default'=>'PUsername',     'hint'=>'Field (or concatenation of fields) to match against'),
      'guest_search_report_operator' =>     array('match' => '',            'default'=>'Starts with',   'hint'=>'Contains|Starts with|Contains this word|Contains a word beginning with'),
      'shadow' =>                           array('match' => 'enum|0,1',    'default'=>'1',             'hint'=>'0|1'),
      'signin_page' =>                      array('match' => '',            'default'=>'',              'hint'=>'Page whose content to show when not signed in'),
      'text_password' =>                    array('match' => '',            'default'=>'Password',      'hint'=>'Label for Password'),
      'text_username' =>                    array('match' => '',            'default'=>'Username',      'hint'=>'Label for Username'),
      'text_signin' =>                      array('match' => '',            'default'=>"<h4>Please Sign in</h4>",      'hint'=>'Initial text shown when creating a new account'),
      'text_signup_confirm' =>              array('match' => '',            'default'=>"<h4>Confirm New Account Details</h4><p>Please confirm the details you have given or go <a href='#' onclick='history.go(-1);return false'><b>back</b></a> and make changes as required.</p>",      'hint'=>'Confirmation text shown when creating a new account'),
      'text_signup_done' =>                 array('match' => '',            'default'=>"Signin then Register",      'hint'=>'Text to place on button once signup is completed'),
      'text_signup_initial' =>              array('match' => '',            'default'=>"<h4>...Or create a new profile</h4>",      'hint'=>'Initial text shown when creating a new account'),
      'text_signup_success' =>              array('match' => '',            'default'=>"<h4>Success!</h4><p>Your initial password has been emailed to the address you gave.</p>",      'hint'=>'Text shown when new account has been created')
    );
  }

  public function draw($instance='',$args=array(),$disable_params=false){
    try{
      $this->_setup($instance,$args,$disable_params);
    }
    catch (Exception $e){
      $this->_draw_control_panel(true);
      switch ($e->getPrevious()->getMessage()){
        case 'Not Registerable':
          return $this->_html;
        break;
        default:
          $this->_draw_error_invalid_event();
          return $this->_html;
        break;
      }
    }
    $this->_draw_control_panel(true);
    $this->_do_submode();
    $this->_draw_heading();
    $this->_draw_status();
    $this->_draw_signin();
    $this->_draw_signup();
    $this->_draw_registration_list();
    $this->_draw_register_myself_button();
    $this->_draw_search_control();
    $this->_draw_invite_guest();
    return $this->_html;
  }

  public function set_eventID($eventID){
    $this->_eventID = $eventID;

  }

  protected function _draw_error_invalid_event(){
    $this->_html.=
       "<h3>Event Registration</h3>"
      ."<p>Sorry - you must provide a valid event code</p>";
  }

  protected function _draw_heading(){
    $this->_html.=
        "<h3 style='margin:0.25em 0'>"
       ."Registering for Event '".title_case_string($this->_Obj_Event->record['title'])
       ."' (".$this->_Obj_Event->record['effective_date_start'].")"
       ."</h3>\n";
  }

  protected function _draw_invite_guest(){
    global $PUsername, $PEmail, $ATelephone, $NFirst, $NMiddle, $NLast, $WCompany;
    if (!$this->_personID) {
      return;
    }
    if (!$this->_cp['guest']){
      return;
    }
    $content =
       "<table cellpadding='1' cellspacing='0' border='0'>\n"
      ."  <tr>\n"
      ."    <td style='width:170px'>".$this->_cp['text_username']." (if applicable)</td>\n"
      ."    <td><input type=\"text\" id=\"PUsername\" name=\"PUsername\" value=\"".$PUsername."\" style=\"width: ".($this->_cp['guest_lookup_username'] ? "180" : "255")."px;\"/>"
      .($this->_cp['guest_lookup_username'] ?
          "<img src='".BASE_PATH."img/spacer' width='5' height='1' alt=''/>\n"
         ."<input type='button' value='Lookup' title='Use this to find members in this system'"
         ."onclick=\"if (geid_val('PUsername')!='') { geid_set('submode','lookup');geid('form').submit();}else{alert('Please enter ".$this->_cp['text_username']." to search for')}\" "
         ."class='formbutton' style='width: 60px;'/>\n"
       :
         ""
       )
      ."    </td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>Name</td>\n"
      ."    <td class='nowrap'><input type=\"text\" name=\"NFirst\" value=\"".$NFirst."\" style=\"width: 100px;\"/>\n"
      ."<input type=\"text\" name=\"NMiddle\" value=\"".$NMiddle."\" size=\"1\" maxlength=\"1\" style=\"width: 20px;\"/>\n"
      ."<input type=\"text\" name=\"NLast\" value=\"".$NLast."\" style=\"width: 115px;\"/></td>\n"
      ."  </tr>\n"
      .($this->_cp['guest_get_company'] ?
         "  <tr>\n"
        ."    <td>Company</td>\n"
        ."    <td><input type=\"text\" name=\"WCompany\" value=\"".$WCompany."\" style=\"width: 255px;\"/></td>\n"
        ."  </tr>\n"
      : "")
      ."  <tr>\n"
      ."    <td>Contact Email</td>\n"
      ."    <td><input type=\"text\" name=\"PEmail\" value=\"".$PEmail."\" style=\"width: 255px;\"/></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td>Contact Telephone</td>\n"
      ."    <td><input type=\"text\" name=\"ATelephone\" value=\"".$ATelephone."\" style=\"width: 255px;\"/></td>\n"
      ."  </tr>\n"
      ."</table>\n";
    $controls =
       "<input type='button' value='Clear' class='formbutton' style='width: 80px;' onclick=\""
      ."geid_set('PUsername','');"
      ."geid_set('NFirst','');"
      ."geid_set('NMiddle','');"
      ."geid_set('NLast','');"
      ."geid_set('PEmail','');"
      ."geid_set('ATelephone','');"
      .($this->_cp['guest_get_company'] ? "geid_set('WCompany','');" : "")
      ."\"/>\n"
      ."<input type='button' value='Invite' class='formbutton' style='width: 80px;' onclick=\""
      ."if (register_required(geid('form'))) { geid_set('submode','register');geid('form').submit();}"
      ."\"/>\n";
    $this->_html.=
       HTML::draw_form_box("Invite Guest",$content,'',$this->_cp['shadow'],false,$controls)
      ."<br />";

  }

  protected function _draw_registration_list(){
    if (!$this->_personID){
      return;
    }
    $this->_html.=
       "<h4>Your Registration list for this event:</h4>\n"
      .draw_auto_report('my_registration_list_for_event',0);
  }

  protected function _draw_register_myself_button(){
    if (!$this->_personID){
      return;
    }
    if ($this->_user_is_registered){
      return;
    }
    $this->_html.=
       "<p class='noprint'>\n"
      ."<input type='button' value='Register myself' onclick=\"geid_set('submode','register_me');geid('form').submit()\"/>\n"
      ."</p>\n";
  }

  protected function _draw_search_control(){
    if (!$this->_personID) {
      return;
    }
    if (!$this->_cp['guest']) {
      return;
    }
    if (!$this->_cp['guest_search']) {
      return;
    }
    $ajax_row_js =              "geid_set('PUsername',geid_val('ajax_person_lookup'));geid_set('submode','lookup');geid('form').submit();";
    $ajax_one_match_js =        "geid_set('PUsername',geid_val('ajax_person_lookup'));geid_set('submode','lookup');geid('form').submit();";
    $ajax_no_match_js =         "";
    $control_num =              Ajax::generate_control_num();
    $autocomplete =             1;
    $_linked_field =            'PUsername';
    $Obj_RFFL =                 new Report_Form_Field_Lookup;
    $Obj_RFFL->init(
      'ajax_person_lookup',
      '',
      $control_num,
      $this->_cp['guest_search_report'],
      $this->_cp['guest_search_report_field'],
      $this->_cp['guest_search_report_operator'],
      $_linked_field,
      '',
      $autocomplete,
      $ajax_row_js,
      $ajax_one_match_js,
      $ajax_no_match_js,
      '',
      $this->_cp['guest_search_footer_initial']
    );
    $this->_html.=
      $this->_cp['guest_search_header']
      .$Obj_RFFL->draw()
     ."<br />\n";
  }

  protected function _draw_signin(){
    global $system_vars;
    if ($this->_personID){
      return;
    }
    if ($this->_submode!='') {
      return;
    }
    $Obj = new Component_Signin;
    $args = array(
      'text_password' =>    $this->_cp['text_password'],
      'text_username' =>    $this->_cp['text_username'],
      'shadow' =>           $this->_cp['shadow']
    );
    $this->_html.=
       $this->_cp['text_signin']
      .$Obj->draw('',$args,true)
      ."<br /><br />\n";
  }

  protected function _draw_signup(){
    global $system_vars;
    if ($this->_personID) {
      return;
    }
    if ($system_vars['system_signup']!=2) {
      return;
    }
  $Obj_Signup = new Component_Signup;
  $args = array(
    'shadow' =>         $this->_cp['shadow'],
    'text_confirm' =>   $this->_cp['text_signup_confirm'],
    'text_done' =>      $this->_cp['text_signup_done'],
    'text_initial' =>   $this->_cp['text_signup_initial'],
    'text_success' =>   $this->_cp['text_signup_success']

  );
  $this->_html.=
     $Obj_Signup->draw('',$args,false)
    ."<br /><br />";
  }

  protected function _draw_status(){
    if ($this->_msg==''){
      return;
    }
    $this->_html.=
       "<p style='color:"
      .(substr(strip_tags($this->_msg),0,5)=='Error' ? '#ff0000' : '#008000')
      ."'>".$this->_msg."</p>";
  }


  protected function _do_submode(){
    global $PUsername, $PEmail, $ATelephone, $NFirst, $NMiddle, $NLast, $WCompany;
    switch ($this->_submode) {
      case "lookup":
        $Obj_Person = new Person;
        if (!$personID = $Obj_Person->get_ID_by_name(get_var('PUsername'))){
          $this->_msg = "<b>Error:</b> there is no such user as ".get_var('PUsername').".";
        }
        else{
          $this->_msg = "<b>Success:</b> ".get_var('PUsername')." has been located in this site.<br />\nYou may now register them for this event.";
        }
        $Obj_Person->_set_ID($personID);
        $row =          $Obj_Person->get_record();
        $NFirst =       $row['NFirst'];
        $NMiddle =      $row['NMiddle'];
        $NLast =        $row['NLast'];
        $PEmail =       $row['PEmail'];
        $ATelephone =   $row['ATelephone'];
        $WCompany =     $row['WCompany'];
      break;
      case "register":
        if ($this->_Obj_Event->userIsRegistered(get_var('PUsername'))){
          $this->_msg = "<b>Error:</b> ".get_var('PUsername')." is already registered for this event";
          break;
        }
        $this->_do_submode_register();
        $this->_setup_is_user_registered();
      break;
      case "register_me":
        if ($this->_user_is_registered) {
          $this->_msg = "<b>Error:</b> You are already registered for this event";
          break;
        }
        $this->_do_submode_register_me();
        $this->_setup_is_user_registered();
      break;
    }
  }

  protected function _do_submode_register(){
    $Obj_Person = new Person;
    $attender_personID = $Obj_Person->get_ID_by_name(get_var('PUsername'));
    if ($attender_personID==0) {
      $this->_msg="Non-Member invited";
    }
    $data =
      array(
        'systemID'=>              SYS_ID,
        'eventID'=>               $this->_eventID,
        'attender_personID'=>     $attender_personID,
        'attender_PEmail'=>       get_var('PEmail'),
        'attender_ATelephone'=>   get_var('ATelephone'),
        'attender_NFirst'=>       get_var('NFirst'),
        'attender_NMiddle'=>      get_var('NMiddle'),
        'attender_NLast'=>        get_var('NLast'),
        'attender_PUsername'=>    get_var('PUsername'),
        'attender_WCompany'=>     get_var('WCompany'),
        'inviter_personID'=>      $this->_personID
      );
    $ID =   $this->_Obj_Register_Event->insert($data);
    $this->_Obj_Register_Event->_set_ID($ID);
    $this->_Obj_Register_Event->assign_booking_number();
    $this->_get_registration_status();
  }

  protected function _do_submode_register_me(){
    $data =
      array(
        'systemID'=>              SYS_ID,
        'eventID'=>               $this->_eventID,
        'attender_personID'=>     $this->_personID,
        'attender_PEmail'=>       $this->_Obj_Person_inv->record['PEmail'],
        'attender_ATelephone'=>   $this->_Obj_Person_inv->record['ATelephone'],
        'attender_NFirst'=>       $this->_Obj_Person_inv->record['NFirst'],
        'attender_NMiddle'=>      $this->_Obj_Person_inv->record['NMiddle'],
        'attender_NLast'=>        $this->_Obj_Person_inv->record['NLast'],
        'attender_PUsername'=>    $this->_Obj_Person_inv->record['PUsername'],
        'attender_WCompany'=>     $this->_Obj_Person_inv->record['WCompany'],
        'inviter_personID'=>      $this->_personID
      );
    $ID =   $this->_Obj_Register_Event->insert($data);
    $this->_Obj_Register_Event->_set_ID($ID);
    $this->_Obj_Register_Event->assign_booking_number();
    $this->_get_registration_status();
  }

  protected function _get_registration_status(){
    if ($this->_Obj_Event->record['no_email']==1) {
      $this->_msg =
         "<b>Success:</b> The registration was successful.<br />\n"
        ."No confirmation email was sent as this event doesn't require it.";
      return;
    }
    $result = $this->_Obj_Register_Event->send_email();
    if (substr($result,0,12)=="Message-ID: ") {
      $this->_msg =
         "<b>Success:</b> The registration was successful and details have been sent"
        ." to the contact address provided.";
      return;
    }
    $this->_msg =
       "<b>Error:</b> You have successfully registered the event, however"
      ." we were not able to email confirmation to the address you gave.<br />\n"
      ."<b>Error Code:</b> ".$result."<br />\n<br />\n"
      ."You have the option if you wish to cancel this registration, change the"
      ." contact address and try again.";
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_submode = get_var('submode');
    try{
      $this->_setup_load_event();
      $this->_setup_check_if_registerable();
      $this->_setup_load_current_user();
      $this->_setup_object_register_event();
      $this->_setup_do_submode_initial();
      $this->_setup_is_user_registered();
    }
    catch(Exception $e){
      throw new Exception('Aborting',0,$e);
    }
  }

  protected function _setup_check_if_registerable(){
    if (!$this->_Obj_Event->isRegisterable()){
      throw new Exception('Not Registerable');
    }
  }

  protected function _setup_do_submode_initial(){
    global $submode;
    switch ($this->_submode) {
      case "delete": // caused by Registration List but handled here instead
        $this->_Obj_Register_Event->_set_ID(get_var('targetID'));
        $this->_Obj_Register_Event->delete();
        $this->_msg = '<b>Success:</b> The event registration has been cancelled';
        $submode = '';
      break;
    }
  }

  protected function _setup_is_user_registered(){
    $this->_user_is_registered =    $this->_Obj_Event->userIsRegistered(get_userPUsername());
  }

  protected function _setup_load_current_user(){
    $this->_personID =              get_userID();
    $this->_Obj_Person_inv =        new Person($this->_personID);
    $this->_Obj_Person_inv->load();
  }

  protected function _setup_load_event(){
    if (!$this->_eventID){
      if (!$this->_eventID = get_var('selectID')){
        throw new Exception('Invalid Event code');
      }
    }
    $this->_Obj_Event =             new Event($this->_eventID);
    if (!$this->_Obj_Event->load()){
      $this->_Obj_Event = false;
      throw new Exception('Invalid Event code');
    }
  }

  protected function _setup_object_register_event(){
    $this->_Obj_Register_Event =    new Register_Event;
  }

  public function get_version(){
    return VERSION_COMPONENT_EVENT_REGISTRATION;
  }

}
?>