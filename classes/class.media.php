<?php
define('VERSION_MEDIA','1.0.3');
/*
Version History:
  1.0.3 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.2 (2010-07-15)
    1) Now references Component_Base:: where needed not Component::
  1.0.1 (2009-11-21)
    1) Rationalised CPs for Media::draw_list() -
       media_draw_list_categories now becomes media_draw_list.categories
  1.0.0 (2009-07-02)
    Initial release
*/
class Media extends Record {
  function __construct($ID="") { // ID is used when copying records
    global $db;
    parent::__construct("media",$ID);
    $this->_set_db_name($db."_media");
    $this->_set_assign_type('media');
    $this->_set_has_groups(true);
    $this->_set_name_field('title');
    $this->_set_object_name('Media Item');
  }

  function draw_list($destinationType,$destinationID) {
    global $page_vars,$selectID,$print;
    global $filterField,$filterExact,$filterValue;
    $ident =        "media_draw_list";
    $parameter_spec = array(
      'categories' => array('default'=>'*', 'hint'=>'*|cat1,cat2...')
    );
    $cp_settings =  Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
    $isADMIN =
      get_person_permission("MASTERADMIN") ||
      get_person_permission("SYSADMIN");
    if ($cp['categories']!='*') {
      $_filterField = $filterField;
      $_filterExact = $filterExact;
      $_filterValue = $filterValue;
      $filterField =  '`media`.`category`';
      $filterExact =  12;
      $filterValue =  $cp['categories'];
      $out.= draw_auto_report('media_for_person',($isADMIN ? 1 : 3));  // No toolbar, but use the values anyway:
      $filterField = $_filterField;
      $filterExact = $_filterExact;
      $filterValue = $_filterValue;
    }
    else {
      $out.= draw_auto_report('media_for_person',1);
    }
    return $out;
  }

  function download_media() {
    if (!$this->exists()) {
      print draw_html_error_404();
      die;
    }
    $record = $this->get_record();
    switch ($record['destinationType']) {
      case 'person':
        $isMASTERADMIN =	get_person_permission("MASTERADMIN");
        $isSYSADMIN =		get_person_permission("SYSADMIN");
        $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
        $userIsAdmin = ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
        $personID = get_userID();
        if (!$userIsAdmin && $personID!=$record['destinationID']) {
          print draw_html_error_403();
          die;
        }
        $ext_arr = explode(".",$record['fileName']);
        $ext = array_pop($ext_arr);
        header_mimetype_for_extension($ext);
        header("Content-Disposition: attachment;filename=\"".$record['fileName']."\"");
        header('Content-Length: '.$record['size']);
        print $record['data'];
      break;
    }
  }
  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }
  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,false);
  }
  public function get_version(){
    return VERSION_MEDIA;
  }
}
?>