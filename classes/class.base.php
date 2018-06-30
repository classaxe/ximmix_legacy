<?php
define('VERSION_BASE','1.0.12');
/*
Version History:
  1.0.12 (2013-06-13)
    1) Base::_get_args() now configurable to NOT raise errors if unexpected
       arguments are encountered

  (Older version history in class.base.txt)
*/
class Base{
  protected static $methods = array();
  protected static $module_version;
  protected $object_name;         // e.g. 'Job Posting'

  public function __call($method, $args) {
    if (in_array($method, self::$methods)) {
      return call_user_func($method, $this, $args);
    }
    else {
      do_log(3,get_class($this)."::".$method."()",'(none)',"The ".get_class($this)." class doesn't have a ".$method." method.");
      $dev_status =
        $_SERVER["SERVER_NAME"]=='localhost' ||
        substr($_SERVER["SERVER_NAME"],0,8)=='desktop.' ||
        substr($_SERVER["SERVER_NAME"],0,4)=='dev.' ||
        substr($_SERVER["SERVER_NAME"],0,7)=='laptop.';
      die(
         "<h1>Technical Fault</h1>\n"
        .($dev_status ?
           "<p>The following method does not exist: <b>".get_class($this)."</b>::<b>".$method."</b>()</p>"
         :
           "<p>Sorry, we have just experienced a technical fault with the page you just tried to access.<br />\n"
          ."Our technicians have now been alerted to the issue.</p>\n"
          ."<p>If you wish to contact us about this, please quote the following reference number<br />\n"
          ."to help us better assist you in dealing with this matter:</p>\n"
          ."<quote>".CODEBASE_VERSION." - ".get_timestamp()."</quote>"
         )
      );
    }
  }

  protected function _get_args($args=false,&$vars,$deprecate_lists=false,$debug=true){
    if ($args===false || !isset($args[0])){
      return false;
    }
    $out = array();
    if(is_string($args[0])){
      if ($deprecate_lists){
        deprecated();
      }
      $n = -1;
      foreach ($vars as $key=>$default){
        if(isset($args[++$n])){
          $out[$key] = $args[$n];
        }
        else {
          $out[$key] = $default;
        }
      }
      $vars = $out;
      return true;
    }
    // Single argument with array of parameters
    $args =             $args[0];
    $keys_expected =    array_keys($vars);
    $keys_given =       array_keys($args);
    if ($debug){
      foreach ($keys_given as $key){
        if (!in_array($key,$keys_expected)){
          do_log(3,__CLASS__.'::'.__FUNCTION__.'()','Argument check','unexpected argument "'.$key.'" given');
          die(__CLASS__.'::'.__FUNCTION__.'() Unexpected input '.$key.' given.<br />'.x());
        }
      }
    }
    foreach ($vars as $key=>$default){
      if(isset($args[$key])){
        $out[$key] = $args[$key];
      }
      else {
        $out[$key] = $default;
      }
    }
    $vars = $out;
    return true;
  }

  public static function get_modules_available() {
    $Obj = dir(SYS_MODULES);
    $out = array();
    while (($entry = $Obj->read())!== false) {
      $entry_bits = explode('.',$entry);
      if (count($entry_bits)==3 && $entry_bits[0]=='module' && $entry_bits[2]=='php') {
        $out[] = $entry_bits[1];
      }
    }
    return implode(", ",$out);
  }

  public static function get_modules_installed() {
    global $system_vars;
    return $system_vars['installed_modules'];
  }

  public static function get_module_version() {
    return self::$module_version;
  }

  public function _get_object_name()                { return $this->object_name; }
  public function _get_object_type()                { return get_class($this); }
  public function _set_object_name($value)          { $this->object_name = $value; }

  public static function module_handle($module,$context,$args) {
    $Obj = Base::use_module($module);
    if ($Obj instanceOf Base_Error){
//      print "module_handle - no such module as ".$module;
      return false;
    }
    if (!method_exists($Obj,'handler')){
//      print "module_handle - no such method as handler";
      return false;
    }
    return $Obj->handler($context,$args);
  }

  public static function module_install($module) {
    $Obj = Base::use_module($module);
    if ($Obj instanceOf Base_Error){
//      print "module_install - no such module as ".$module;
      return false;
    }
    if (!method_exists($Obj,'install')){
//      print "module_install - no such method as install";
      return false;
    }
    return $Obj->install();
  }

  public static function module_uninstall($module) {
    $Obj = Base::use_module($module);
    if ($Obj instanceOf Base_Error){
//      print "module_uninstall - no such module as ".$module;
      return false;
    }
    if (!method_exists($Obj,'uninstall')){
//      print "module_uninstall - no such method as uninstall";
      return false;
    }
    return $Obj->uninstall();
  }

  public static function module_test($module){
    global $system_vars;
    $installed_modules_arr = explode(',',str_replace(' ','',strToLower($system_vars['installed_modules'])));
    return (in_array(strToLower($module),$installed_modules_arr) ? true : false);
  }

  public static function registerMethod($method) {
    if (!in_array($method, self::$methods)) {
      self::$methods[] = $method;
    }
  }

  public static function registerModules() {
    $modules = explode(",",str_replace(' ','',Base::get_modules_installed()));
    foreach($modules as $module){
      Base::use_module($module);
    }
  }

  public static function set_module_version($version) {
    self::$module_version = $version;
  }

  public static function use_module($name) {
    if (!in_array(strToLower($name),explode(",",str_replace(' ','',Base::get_modules_installed())))){
      $Obj =  new Base_Error("The ".$name." module is not installed.");
      return $Obj;
    }
    $module_file = strToLower(SYS_MODULES.'module.'.$name.".php");
    if (file_exists($module_file)){
      include_once($module_file);
      $Obj =  new $name;
      return $Obj;
    }
    return new Base_Error("The ".$name." module does not exist.");
  }

  public function get_version(){
    return VERSION_BASE;
  }
}

?>