<?php
  define ("VERSION_COMPONENT_EMAIL_OPT_OUT","1.0.0");
/*
Version History:
  1.0.0 (2014-06-22)
    1) Initial release
*/
class Component_Email_Opt_Out extends Component_Base {
  public function __construct(){
    $this->_ident =         'email_opt_in';
    $this->_parameter_spec = array(
      'show_subscription_details' =>    array('match' => 'enum|0,1',        'default'=>'1',                 'hint'=>'0|1'),
      'text_title' =>                   array('match' => '',                'default'=> "Cancel Email Subscription",  'hint'=>'Text to place above component'),
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    try{
      $this->_setup($instance,$args,$disable_params);
      $this->_draw_control_panel(true);
      $this->_draw_heading();
      $this->_draw_status();
      $this->_draw_subscription_details();
      $this->_draw_controls();
      return $this->_html;
    }
    catch (Exception $e){
      $this->_html.= "<p>".$e->getMessage()."</p>";
      return $this->_html;
    }
  }

  protected function _draw_controls(){
    $this->_html .=
       "<p><b>Please confirm that you do not wish to receive occasional emails from us:</b></p>\n"
      ."<p>\n"
      ."<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"".$this->_get_ID()."\"/>\n"
      ."<input type=\"button\" value=\"Confirm\" onclick=\"if(confirm('Do you wish to not receive messages from us?')){geid_set('submode','all');geid('form').submit();}\" style=\"width:8em\"/>\n"
      ."<input type=\"button\" value=\"Cancel\" onclick=\"window.location.assign('".BASE_PATH."')\" style=\"width:8em\"/>\n"
      ."</p>";
  }

  protected function _draw_heading(){
    $this->_html.=  "<h2 style='margin:0.25em 0'>".$this->_cp['text_title']."</h2>";
  }

  protected function _draw_status(){
    $this->_html.=      HTML::draw_status($this->_safe_ID,$this->_msg);
  }

  protected function _draw_subscription_details(){
    if (!$this->_cp['show_subscription_details']){
      return;
    }
    $this->_html.=
       "<p><b>Your subscription details:</b></p>"
      ."<table border='1' cellpadding='5' cellspacing='0' style='border-collapse:collapse'>\n"
      ."  <thead style='background:#d8d8d8'>\n"
      ."    <tr>\n"
      ."      <th>Your Name</th>\n"
      ."      <th>Your Email</th>\n"
      ."    </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n"
      ."    <tr>\n"
      ."      <td>".$this->_record['recipient_name']."</td>\n"
      ."      <td>".$this->_record['recipient_email']."</td>\n"
      ."    </tr>\n"
      ."  </tbody>\n"
      ."</table><br />\n";
  }


  protected function _setup($instance='', $args=array(), $disable_params=false){
    parent::_setup($instance, $args, $disable_params);
    global $page_vars;
    $this->_Obj_MQI =   new Mail_Queue_Item(sanitize('ID',$page_vars['path_extension']));
    if (!$this->_record = $this->_Obj_MQI->get_message_details()){
      throw new Exception('Sorry - that message is no longer on our server.');
    }
    $this->_setup_do_submode();
  }

  protected function _setup_do_submode() {
    switch (get_var('submode')){
      case 'all':
        $Obj_Person = new Person($this->_record['personID']);
        $records = $Obj_Person->get_group_membership();
        foreach($records as $r){
          if($r['systemID']==SYS_ID){
            $Obj_Group = new Group($r['groupID']);
            $current = $Obj_Group->member_perms($this->_record['personID']);
            if($current['permEMAILRECIPIENT']==1){
              $permArr = array('permEMAILOPTOUT'=>1,'permEMAILOPTIN'=>0, 'permEMAILRECIPIENT'=>0);
              $Obj_Group->member_assign($this->_record['personID'] ,$permArr, 'User Opt-Out via link');
            }
          }
        }
        $this->_msg= "<b>Success:</b> You have cancelled your email subscription with us.";
      break;
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_EMAIL_OPT_OUT;
  }
}
?>