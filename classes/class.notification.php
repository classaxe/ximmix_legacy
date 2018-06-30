<?php
define('VERSION_NOTIFICATION','1.0.4');
/*
Version History:
  1.0.4 (2012-10-17)
    1) Split out css into static Notification::draw_css()
    2) Split out header to static Notification::draw_footer()
    3) Split out footer to static Notification::draw_footer()
  1.0.3 (2012-02-14)
    1) Complete revamp of notification system to allow notifications for multiple
       sites in a single database tobe handled by a single cron heartbeat
  1.0.2 (2011-11-10)
    1) Tweak to Notification::draw() to ensure that only valid trigger objects
       are invoked to determine their notify options
  1.0.1 (2011-10-06)
    1) Completed all code required to allow for implementation of reports by
       the objects to which the triggers refer.
       This SHOULD mean that this code doesn't need to be touched again.
  1.0.0 (2011-10-06)
    1) Initial release
*/
class Notification extends Record{
  private $_base_url;
  private $_emails;
  private $_triggers;
  private $_last_heartbeat;
  private $_html;
  private $_site;
  private $_sites;

  function __construct(){
    parent::__construct();
    $this->_set_object_name('Notification');
  }

  public function notify_all(){
    $this->_get_all_sites_to_notify();
    foreach ($this->_sites as &$this->_site){
      $this->_draw();
      $this->_send();
    }
  }

  public function _get_all_sites_to_notify(){
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `textEnglish`,\n"
      ."  `adminEmail`,\n"
      ."  `adminName`,\n"
      ."  `cron_job_heartbeat_last_run`,\n"
      ."  `defaultTimeFormat`,\n"
      ."  `notify_email`,\n"
      ."  `notify_triggers`,\n"
      ."  `URL`\n"
      ."FROM\n"
      ."  `system`\n"
      ."WHERE\n"
      ."  `notify_email`!='' AND\n"
      ."  `notify_triggers`!=''";
    $this->_sites = $this->get_records_for_sql($sql);
  }

  public function _get_emails(){
    $out =          array();
    $emails_csv =   $this->_site['notify_email'];
    $emails =       explode(',',$emails_csv);
    foreach($emails as $email){
      $out[] =      trim($email);
    }
    sort($out);
    $this->_emails =    $out;
  }

  public function _get_triggers(){
    $out =          array();
    if ($triggers_csv = $this->_site['notify_triggers']){
      $triggers =     explode(',',$triggers_csv);
      foreach($triggers as $trigger){
        $out[] =      trim($trigger);
      }
      sort($out);
    }
    $this->_triggers =    $out;
  }

  protected function _draw(){
    $this->_html = "";
    $this->_get_emails();
    $this->_get_triggers();
    foreach($this->_triggers as $trigger){
      if (class_exists($trigger)){
        $Obj = new $trigger;
        $this->_html.= $Obj->get_notification_summary(
          $this->_site['cron_job_heartbeat_last_run'],
          $this->_site['ID'],
          trim($this->_site['URL'],'/').BASE_PATH
        );
      }
    }
    if (!$this->_html){
      return;
    }
    $am_pm =    $this->_site['defaultTimeFormat']==1 || $this->_site['defaultTimeFormat']==3;
    $this->_html =
       Notification::draw_css()
      .Notification::draw_header($this->_site['textEnglish'],trim($this->_site['URL'],'/'))
      .$this->_html
      .Notification::draw_footer($am_pm);
  }

  public function _send(){
    if (!$this->_html){
      return;
    }
    get_mailsender_to_component_results(); // Use system default mail sender details
    component_result_set('from_name',$this->_site['adminName']);
    component_result_set('from_email',$this->_site['adminEmail']);
    $data =             array();
    $data['subject'] =      "Notification";
    $data['html'] =         $this->_html;
    foreach ($this->_emails as $email){
      $data['PEmail'] =       $email;
      $data['NName'] =        $email;
      $mail_result =          mailto($data);
      if (strpos($mail_result, 'Message-ID') === false) {
        do_log(3,__CLASS__.'::'.__FUNCTION__.'()','',$mail_result.' '.$this->_html);
      }
    }
  }

  public static function draw_css(){
    return
       "<style type='text/css'>\n"
      .".notifications h2 { margin: 1em 0 0 0}\n"
      .".notifications table { width: 100%; border-collapse: collapse; border: 1px solid #888}\n"
      .".notifications table .datetime { width: 150px; }\n"
      .".notifications table thead { background: #e0e0e0; }\n"
      .".notifications p.footer { text-align: center; }\n"
      ."</style>\n";
  }

  public static function draw_footer($am_pm=false){
    $now =  get_timestamp();
    return
       "<p class='footer'>(Generated "
      .format_date($now)." at ".hhmm_format(substr($now,10),$am_pm)
      .")</p>\n"
      ."</div>";
  }

  public static function draw_header($site_title, $site_URL){
    return
       "<div class='notifications'>\n"
      ."<h1>Notifications for"
      ." <a target=\"_blank\" href=\"".trim($site_URL,'/').BASE_PATH."\">"
      .$site_title
      ."</a>\n"
      ."</h1>";
  }

  public function get_version(){
    return VERSION_NOTIFICATION;
  }
}
?>