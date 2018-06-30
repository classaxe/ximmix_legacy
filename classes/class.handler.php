<?php
define('VERSION_HANDLER','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class Handler {
  private $context;
  function Handler($context=''){
    $this->context = $context;
  }
  function handle($args) {
    $modules_arr = explode(',',str_replace(' ','',Base::get_modules_available()));
    foreach ($modules_arr as $module){
      if ($result = Base::module_handle($module,$this->context,$args)) {
        if (isset($result['handled']) && $result['handled']){
          return $result;
        }
      }
    }

    if (function_exists("cus_handler")) {
      return cus_handler($this->context,$args);
    }
    return array('handled'=>false);
  }
  public function get_version(){
    return VERSION_HANDLER;
  }
}
?>