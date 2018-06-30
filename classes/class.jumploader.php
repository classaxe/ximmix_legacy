<?php
define('VERSION_JUMPLOADER','1.0.7');
define('VERSION_JUMPLOADER_JAR','2.19.0.b');
/*
Version History:
  1.0.7 (2011-07-05)
    1) Jumploader JS now registers presence of java applet to allow it to be
       hidden if required, e.g. with dropdown menu or layer-based divs.
  1.0.6 (2011-04-12)
    1) Now has no-logo version of jumploader installed
  1.0.5 (2011-03-30)
    1) Added Jumploader::sort_status() as an overrideable comparison function
       for sorting uploaded files
    2) Jumploader::get_status() now sorts files using Jumploader::sort_status()
  1.0.4 (2011-03-24)
    1) New methods added to allow info to be returned on any items uploaded:
       Jumploader::clear_status(), Jumploader::get_status() and
       Jumploader::get_uploaded_count()
    2) Jumploader::init() method now includes all options required to display
       Jumploader::files_uploader() - EXCEPT for final destination folder
    3) Added Jumploader::isUploading() to determine whether an upload operation
       is in progress or not - this can be used to clear an upload status once
       all items have been uploaded
  1.0.3 (2011-03-18)
    1) Tweaks to jumploader::get_code() JS to conform to JSLINT
  1.0.2 (2011-03-03)
    1) Jumploader::draw() now includes jumploader's jar version to cache-bust
       when newer versions of the applet are uploaded
  1.0.1 (2011-03-03)
    1) Added on_uploaded() method for the purpose of overriding to allow other
       actions to occur after an upload
    2) Bug fix for Jumploader::get_code() to ensure that multiple options for
       filetypes are all processed correctly (had error in regexp definition)
  1.0.0 (2010-12-09)
    Initial release
*/
class Jumploader{
  private $_extensions;
  private $_height;
  private $_html;
  private $_js;
  private $_mode;
  private $_safe_ID;
  private $_show_summary;
  private $_type;
  private $_URL;
  private $_width;

  public function __construct(){
  }

  public function init($safe_ID,$width=140,$height=44,$mode='framed',$type='image',$ext='jpg|jpeg|gif|png',$show_summary=false){
    global $page_vars;
    $this->_safe_ID =       $safe_ID;
    $this->_ext =           $ext;
    $this->_height =        $height;
    $this->_mode =          $mode;
    $this->_show_summary =  $show_summary;
    $this->_type =          $type;
    $this->_URL =           BASE_PATH.trim($page_vars['path'],'/')."?submode=".$this->_safe_ID."_upload";
    $this->_width =         $width;
  }

  public function draw(){
    $this->setup_code();
    Page::push_content('javascript',$this->get_js());
    return $this->get_html();
  }

  public function clear_status(){
    unset($_SESSION[$this->_safe_ID.'_results']);
  }

  public function get_html(){
    return $this->_html;
  }

  public function get_js(){
    return $this->_js;
  }

  public function get_status(){
    if (!isset($_SESSION[$this->_safe_ID.'_results'])){
      $_SESSION[$this->_safe_ID.'_results'] = array();
    }
    usort($_SESSION[$this->_safe_ID.'_results'], array($this, "sort_status"));
    return $_SESSION[$this->_safe_ID.'_results'];
  }

  public function sort_status($a,$b){
    $al = strtolower($a['filename']);
    $bl = strtolower($b['filename']);
    if ($al == $bl) {
      return 0;
    }
    return ($al > $bl) ? +1 : -1;
  }

  public function get_uploaded_count(){
    $result = $this->get_status();
    return count($result);
  }

  public function files_upload($mode,$path){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $this->_isAdmin =   ($isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER);
    if ($this->_isAdmin){
      mkdirs('.'.$path,0777);
      $Obj_Uploader = new Uploader($mode,$path);
      $result = $Obj_Uploader->do_upload();
    }
    else {
      $result = array('status'=>'403', 'message'=>'Unauthorised');
    }
    switch ($result['status']){
      case '100':
        // In progress - do nothing
      break;
      case '200':
        $this->on_uploaded($result);
      break;
      default:
        header("HTTP/1.0 200",$result['status']);
        header('Content-type: text/plain');
        print "Error: ".$result['status']." ".$result['message']."\n";
        die();
      break;
    }
  }

  public function files_uploader($folder){
    if ($this->isUploading()){
      $this->files_upload($this->_type,$folder);
      die();
    }
  }

  public function isUploading(){
    return get_var('submode')==$this->_safe_ID."_upload";
  }

  protected function on_uploaded($result){
    if (!isset($_SESSION[$this->_safe_ID.'_results'])){
      $_SESSION[$this->_safe_ID.'_results'] = array();
    }
    $_SESSION[$this->_safe_ID.'_results'][] = $result;
    do_log(1,__CLASS__.'::'.__FUNCTION__.'()','','Result: '.print_r($result,1));
  }

  public function setup_code(){
    $this->_js.=
       "function uploaderStatusChanged( uploader ) {\n"
      ."  if(uploader.getStatus()===0){\n"
      ."    if (uploader.getFileCountByStatus(2)===uploader.getFileCount()){\n"
      ."      geid('form').submit();\n"
      ."    }\n"
      ."  }\n"
      ."}\n"
      ."applet_register(\"".$this->_safe_ID."\");\n"
      ;
    $extensions_arr = explode(',',$this->_ext);
    $this->_html.=
       "<div id=\"container_".$this->_safe_ID."\" style='width:".$this->_width."px;height:".$this->_height."px;margin:auto'>\n"
      ."<applet style=\"margin:auto;display:block;\" id=\"".$this->_safe_ID."\" name=\"".$this->_safe_ID."\""
      ." code=\"jmaster.jumploader.app.JumpLoaderApplet.class\""
      ." archive=\"".BASE_PATH."java/jumploader_z.jar/".VERSION_JUMPLOADER_JAR."\""
      ." width=\"".$this->_width."\" height=\"".$this->_height."\" mayscript>\n"
      ."  <param name=\"uc_uploadUrl\" value=\"".$this->_URL."\" />\n"
      ."  <param name=\"uc_partitionLength\" value=\"".get_max_upload_size()."\"/>\n"
      ."  <param name=\"uc_fileNamePattern\" value=\"^.+\.(?i)(".implode('|',$extensions_arr).")\$\"/>\n"
      ."  <param name=\"vc_fileNamePattern\" value=\"^.+\.(?i)(".implode('|',$extensions_arr).")\$\"/>\n"
      ."  <param name=\"vc_uploadViewFilesSummaryBarVisible\" value=\"".($this->_show_summary ? "true" : "false")."\"/>\n"
      ."  <param name=\"vc_mainViewShowUploadErrors\" value=\"".(1 ? "true" : "false")."\"/>\n"
      ."  <param name=\"vc_mainViewLogoEnabled\" value=\"0\" />\n"
      ."  <param name=\"ac_fireUploaderStatusChanged\" value=\"true\"/>\n"
      ."  <param name=\"ac_mode\" value=\"".$this->_mode."\" />\n"
      ."</applet>\n"
      ."</div>";
  }

  public function get_version(){
    return VERSION_JUMPLOADER;
  }
}
?>