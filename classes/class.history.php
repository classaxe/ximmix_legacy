<?php
define ("VERSION_HISTORY","1.0.5");
/*
Version History:
  1.0.5 (2012-09-20)
    1) History:initialise() now sets shop page to '' to allow this to be determined
       by CP values in checkout
  1.0.4 (2012-03-28)
    1) Moved Page_Vars::password_check_history() into here as
       History::password_check_previous()
  1.0.3 (2012-03-15)
    1) Changed 'resource_passwords' to 'challenge_passwords'
    2) Added new method History::push() to push item onto a property array
  1.0.2 (2012-03-14)
    1) Added 'resource_passwords' to initialialised properties list
  1.0.1 (2012-03-14)
    1) Added History::track() to remember current URL and update last URL in
       session. This now calls History::initialise() if required
  1.0.0 (2011-09-16)
    1) Initial release - this class will take care of remembering which page a
       product was selected from

*/

class History {
  public static function get($key){
    if (!isset($_SESSION['history'])){
      History::initialise();
    }
    return (isset($_SESSION['history'][$key]) ? $_SESSION['history'][$key] : false);
  }

  public static function set($key,$value){
    if (!isset($_SESSION['history'])){
      History::initialise();
    }
    $_SESSION['history'][$key] = $value;
  }

  public static function push($key,$value){
    if (!trim($value)){
      return;
    }
    if (!isset($_SESSION['history'])){
      History::initialise();
    }
    if (array_search($value, $_SESSION['history'][$key])){
      return;
    }
    $_SESSION['history'][$key][] = $value;
  }

  public function password_check_against_csvlist($password, $csvlist){
    $match = false;
    $password_arr = explode(',',trim($csvlist));
    foreach ($password_arr as $pwd){
      if (trim(strToLower($password))===trim(strToLower($pwd))){
        $match = true;
        break;
      }
    }
    return $match;
  }

  public function password_csvlist_check_against_previous($csvlist){
    $match = false;
    $password_arr = explode(',',trim($csvlist));
    $challenge_passwords = History::get('challenge_passwords');
    foreach ($password_arr as $pwd){
      if ($challenge_passwords){
        foreach ($challenge_passwords as $challenge){
          if (trim(strToLower($challenge))===trim(strToLower($pwd))){
            $match = true;
            break 2;
          }
        }
      }
    }
    return $match;
  }

  public static function initialise(){
    $_SESSION['history'] = array();
    History::set('checkout','/checkout');
    History::set('shop','');
    History::set('challenge_passwords',array());
    History::set('url_previous',false);
    History::set('url_current',$_SERVER["REQUEST_URI"]);
  }


  public static function track(){
    if (!isset($_SESSION['history'])){
      History::initialise();
    }
    History::set('url_previous',History::get('url_current'));
    History::set('url_current',$_SERVER["REQUEST_URI"]);
  }


  public function get_version(){
    return VERSION_HISTORY;
  }
}
?>