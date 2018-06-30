<?php
define('MODULE_CHURCH_VERSION','1.0.16');
/*
Version History:
  1.0.16 (2012-09-09)
    1) Changes to Church::install() and Church::uninstall() to avoid native DB access
  1.0.15 (2012-09-07)
    1) Changes to Church_Component::bible_links() to use https for script download
       if site is secure
  1.0.14 (2012-03-14)
    1) Moved sql for installer and uninstaller into external sql files
  1.0.13 (2011-12-29)
    1) Constructor now calls Component_Base::registerMethod(), not
       Component::registerMethod() which eliminates a needless invokation
  1.0.12 (2011-08-22)
    1) Refreshed installer sql for reports 'module.church.prayer-requests-report',
       'module.church.prayer_requests_icon' and 'module.church.prayer-request-form'
  1.0.11 (2011-07-17)
    1) Changed one reference from Component::function_name() to
       Component_Base::function_name()

  (Older version history in module.church.txt)
*/
class Church extends Posting {

  function __construct($ID=""){
    $this->set_module_version(MODULE_CHURCH_VERSION);
    Component_Base::registerMethod("component_daily_bible_verse");
    parent::__construct('postings',$ID);
    $this->_set_type('prayer-request');
    $this->_set_object_name('Prayer Request');
    $this->_set_message_associated('');
  }

  function bible_links($instance='', $args=array(), $disable_params=false){
    $Obj = new Church_Component;
    return $Obj->bible_links($instance, $args, $disable_params);
  }

  function install() {
    $sql = str_replace('$systemID',SYS_ID,file_get_contents(SYS_MODULES.'module.church.install.sql'));
    $this->uninstall();
    $commands = Backup::db_split_sql($sql);
    foreach ($commands as $command) {
      $this->do_sql_query($command);
    }
    return 'Installed Module '.$this->_get_object_name();
  }

  function uninstall() {
    $sql = str_replace('$systemID',SYS_ID,file_get_contents(SYS_MODULES.'module.church.uninstall.sql'));
    $commands = Backup::db_split_sql($sql);
    foreach ($commands as $command) {
      $this->do_sql_query($command);
    }
    return 'Uninstalled Module '.$this->_get_object_name();
  }
}

class Church_Component extends Component_Base{
  function bible_links($instance='', $args=array(), $disable_params=false) {
    $ident =            "bible_links";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'bible_version' =>  array('match' => 'enum|AB,ASV,DAR,ESV,GW,HCSB,KJV,LEB,MESSAGE,NASB,NCV,NIV,NIRV,NKJV,NLT,TNIV,YLT',  	'default'=>'NIV',    'hint'=>'Version of bible to use'),
      'link_bold' =>      array('match' => 'enum|0,1',  	'default'=>'0',         'hint'=>'0|1'),
      'link_color' =>     array('match' => 'hex3|#0000ff',	'default'=>'#0000ff',   'hint'=>'Hex Colour for links'),
      'link_popup' =>     array('match' => 'enum|0,1',  	'default'=>'1',         'hint'=>'0|1')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    Page::push_content(
      'style',
       "a.lbsBibleRef       { text-decoration: none; "
      .($cp['link_bold'] ? "font-weight: bold; " : "")
      .($cp['link_color'] ? "color:".$cp['link_color']."; " : "")
      ."}\n"
      ."a.lbsBibleRef:hover { text-decoration: underline; }\n"
    );
    Page::push_content(
       "body_bottom",
       "<script src=\""
      .($_SERVER["SERVER_PORT"]==443 ? "https://" : "http://")
      ."bible.logos.com/jsapi/referencetagging.js\" type=\"text/javascript\"></script>"
      ."<script type=\"text/javascript\">\n"
      ."Logos.ReferenceTagging.lbsBibleVersion = \"".$cp['bible_version']."\";\n"
      ."Logos.ReferenceTagging.lbsLinksOpenNewWindow = ".($cp['link_popup'] ? "true" : "false").";\n"
      ."Logos.ReferenceTagging.lbsLogosLinkIcon = \"light\";\n"
      ."Logos.ReferenceTagging.lbsNoSearchTagNames = [ \"h1\", \"h2\", \"h3\" ];\n"
      ."Logos.ReferenceTagging.lbsTargetSite = \"biblia\";\n"
      ."Logos.ReferenceTagging.tag();"
      ."</script>\n"
    );
    return $out;
  }

  function prayer_request($instance='', $args=array(), $disable_params=false){
    global $system_vars;
    $ident =            "prayer_request";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'email' =>    array('match' => '',    'default'=>$system_vars['adminEmail'],   'hint'=>'Person to send email requests to'),
      'thanks' =>   array('match' => '',  	'default'=>'prayer-request/thanks',      'hint'=>'Page to show after sending request')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    switch(get_var('submode')){
      case 'send_prayer_request':
        $Obj = new Church;
        $data =
          array(
            'systemID' =>       SYS_ID,
            'content' =>        addslashes(get_var('description')),
            'type' =>           'prayer-request',
            'status' =>         'New',
            'xml:AEmail' =>     addslashes(get_var('AEmail')),
            'xml:ATelephone' => addslashes(get_var('ATelephone')),
            'xml:NName' =>      addslashes(get_var('NName'))
          );
        $ID = $Obj->insert($data);
        $Obj = new Report();
        $Obj->_set_ID($Obj->get_ID_by_name('module.church.prayer-requests-report'));
        $Obj->email_form($ID,"Prayer Request",$cp['email']);
        header('Location: '.BASE_PATH.trim($cp['thanks'],'/'));
        print('Redirecting...');
        die;
      break;
    }
    $out.=
       "<table width='400' cellpadding='0' border='0' cellspacing='0' align='center'>"
       ."<tr><td>"
       .draw_auto_form('module.church.prayer-request-form',0)."</td></tr>"
       ."  <tr>\n"
       ."    <td align='center' colspan='2' class='table_admin_h'>\n"
       ."<input type='button' value='Submit' onclick=\"if (confirm('Send prayer request?')){ geid_set('submode','send_prayer_request');geid('form').submit();}else{alert('Cancelled');}\" class='formbutton' style='width: 60px;'>\n"
       ."</td>\n"
       ."  </tr>\n"
       ."</table>";
    return $out;
  }
}

function component_daily_bible_verse($context){
  global $page_vars,$component_help;
  $out = "";
  $parameters =  Component_Base::get_parameters();
  if ($component_help==1) {
    $out.=
      Component_Base::help(
        __CLASS__."::".__FUNCTION__."()",
         "daily_bible_verse.version=[ASV|NIV|AMP|BRE|DRB|KJV|NASB|NIV|NKJV|NLT|NRSV|RSV|WEB|YLT]{NIV}"
      );
  }
  $daily_bible_verse_version = Component_Base::get_parameter($parameters,'daily_bible_verse.version','NIV');

  return
     "<div id='daily_bible_verse'>Loading from christnotes.org... please wait</div>"
    ."<script type=\"text/javascript\">//<![CDATA[\n"
    ."include('".BASE_PATH."?command=get_bible_verse&trn=".$daily_bible_verse_version."','daily_bible_verse');\n"
    ."//]]></script>"
    ."<p style=\"text-align:right;color:#000000;padding:0px;margin:3px 4px 0px;border:0px;font-family:Arial,sans-serif;font-size:12px;\">"
    ."Provided by <a href=\"http://www.christnotes.org/\" "
    ."style=\"color:#000000;font-family:Arial,sans-serif;font-size:12px;text-decoration:underline;padding:0px;margin:0px;border:0px;\" "
    ."onclick=\"javascript:window.open('http://www.christnotes.org/');return false\">Christ Notes</a> "
    ."<a href=\"http://www.christnotes.org/bible.php\" "
    ."style=\"color:#000000;font-family:Arial,sans-serif;font-size:12px;text-decoration:underline;padding:0px;margin:0px;border:0px;\" "
    ."onclick=\"javascript:window.open('http://www.christnotes.org/bible.php');return false\">Bible Search</a></p>";
}

function import_pr(){
  $Obj = new Record('cus_church_prayers');
  $records = $Obj->get_records();
  $Obj = new Church;
  foreach($records as $record){
    $data = array(
      'systemID' =>                 addslashes($record['systemID']),
      'content' =>                  addslashes($record['description']),
      'status' =>                   addslashes($record['status']),
      'type' =>                     'prayer-request',
      'xml:AEmail' =>               addslashes($record['AEmail']),
      'xml:ATelephone' =>           addslashes($record['ATelephone']),
      'xml:NName' =>                addslashes($record['NName']),
      'xml:notes' =>                addslashes($record['notes']),
      'xml:summary' =>              addslashes($record['summary']),
      'history_created_by' =>       addslashes($record['history_created_by']),
      'history_created_date' =>     addslashes($record['history_created_date']),
      'history_modified_by' =>      addslashes($record['history_modified_by']),
      'history_modified_date' =>    addslashes($record['history_modified_date'])
    );
    $Obj->insert($data);
  }
}
//import_pr();die;

?>
