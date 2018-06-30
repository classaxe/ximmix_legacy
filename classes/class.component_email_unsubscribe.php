<?php
  define ("VERSION_COMPONENT_EMAIL_UNSUBSCRIBE","1.0.0");
/*
Version History:
  1.0.0 (2013-10-04)
    1) Initial release
*/
class Component_Email_Unsubscribe extends Component_Base {
  public function __construct(){
    $this->_ident =         'email_unsubscribe';
    $this->_parameter_spec = array(
      'show_messages' =>                array('match' => 'enum|0,1',        'default'=>'1',                 'hint'=>'0|1'),
      'show_subscription_details' =>    array('match' => 'enum|0,1',        'default'=>'1',                 'hint'=>'0|1'),
      'text_title' =>                   array('match' => '',                'default'=> "Unsubscribe from email messages",  'hint'=>'Text to place above component'),
      'filter_podcast_order' =>         array('match' => 'enum|asc,desc',   'default' =>'asc',              'hint'=>'asc|desc'),
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    try{
      $this->_setup($instance,$args,$disable_params);
      $this->_draw_control_panel(true);
      $this->_draw_heading();
      $this->_draw_status();
      $this->_draw_subscription_details();
      $this->_draw_messages_overview();
      $this->_draw_controls();
      return $this->_html;
    }
    catch (Exception $e){
//      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','',$e->getMessage());
      $this->_html.= "<p>".$e->getMessage()."</p>";
      return $this->_html;
    }
  }

  protected function _draw_controls(){
    $this->_html .=
       "<p><b>Unsubscribe from messages sent to:</b></p>\n"
      ."<p>\n"
      ."<input type=\"hidden\" id=\"ID\" name=\"ID\" value=\"".$this->_get_ID()."\"/>\n"
      ."<input type=\"button\" value=\"This Group\" onclick=\"if(confirm('Unsubscribe from all mesages sent to this group?')){geid_set('submode','group');geid('form').submit();}\" style=\"width:8em\"/>\n"
      ."<input type=\"button\" value=\"All Groups\" onclick=\"if(confirm('Unsubscribe from all mesages sent by this site?')){geid_set('submode','all');geid('form').submit();}\" style=\"width:8em\"/>\n"
      ."<input type=\"button\" value=\"Cancel\" onclick=\"window.location.assign('".BASE_PATH."')\" style=\"width:8em\"/>\n"
      ."</p>";
  }

  protected function _draw_heading(){
    $this->_html.=  "<h2 style='margin:0.25em 0'>".$this->_cp['text_title']."</h2>";
  }

  protected function _draw_messages_overview(){
    if (!$this->_cp['show_messages']){
      return;
    }
    $this->_html.=
       "<p><b>All messages sent to your account:</b></p>"
      ."<div style='max-height:15em;overflow:auto'>\n"
      .$this->_Obj_MQI->view_message_list($this->_record['personID'])
      ."</div>";
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
        $count = 0;
        foreach($records as $r){
          if($r['systemID']==SYS_ID){
            $Obj_Group = new Group($r['groupID']);
            $current = $Obj_Group->member_perms($this->_record['personID']);
            if($current['permEMAILRECIPIENT']==1){
              $permArr = array('permEMAILOPTOUT'=>1,'permEMAILRECIPIENT'=>0);
              $Obj_Group->member_assign($this->_record['personID'],$permArr);
              $count++;
            }
          }
        }
        if ($count){
          $this->_msg= "<b>Success:</b> You have now been unsubscribed from ".$count." group".($count==1 ? '' : 's').".";
        }
        else {
          $this->_msg= "<b>Notice:</b> You have already been unsubscribed from all groups.";
        }
      break;
      case 'group':
        $Obj_Group = new Group($this->_record['groupID']);
        $current = $Obj_Group->member_perms($this->_record['personID']);
        if($current['permEMAILRECIPIENT']==0 && $current['permEMAILOPTOUT']==1){
          $this->_msg= "<b>Notice:</b> You have already been unsubscribed from this group.";
        }
        else {
          $permArr = array('permEMAILOPTOUT'=>1,'permEMAILRECIPIENT'=>0);
          $Obj_Group->member_assign($this->_record['personID'],$permArr);
          $this->_msg= "Success: You have now been unsubscribed from this group.";
        }
      break;
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_EMAIL_UNSUBSCRIBE;
  }
}
?>