<?php
define ("VERSION_PASSWORD","1.0.0");
/*
Version History:
  1.0.4 (2012-03-28)
    1) Initial Release:
    2) Page_Vars::_password_check_history() now Password::check_csvlist_against_previous()
    3) Page_vars::_password_check() now Password::check_password_against_csvlist()
*/

class Password {

  public function check_password_against_csvlist($password, $csvlist){
    $match = false;
    $password_arr = explode(',',trim($csvlist));
    foreach ($password_arr as $pwd){
      if (trim(strToLower($password))===trim(strToLower($pwd))){
        $match = true;
        Password::push($password);
        break;
      }
    }
    return $match;
  }

  public function check_csvlist_against_previous($csvlist){
    $match = false;
    $password_arr = explode(',',trim($csvlist));
    $challenge_passwords = Password::get();
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

  public function do_commands($password,$password_csvlist){
    switch (get_var('command')){
      case 'challenge_password':
        if (Password::check_password_against_csvlist($password,$password_csvlist)){
          die('1');
        }
        die('0');
      break;
    }
  }
  public function get(){
    return History::get('challenge_passwords');
  }

  public function get_password_challenge_code($title,$type,$path){
    return array(
      'html' =>
         "<p><b>PASSWORD PROTECTED CONTENT</b></p>\n"
        ."<p><a href='.' onclick='return challenge()'>Click here</a> to provide a password.</p>",
      'javascript' =>
         "function challenge(){\n"
        ."  challenge_password("
        ."\"".$title."\","
        ."\"".strToLower($type)."\","
        .(History::get('url_previous')!==false ? 1 : 0).","
        ."\"".History::get('url_current')."\","
        ."\"".BASE_PATH.trim($path,'/')."\""
        .");\n"
        ."  return false;\n"
        ."}\n",
      'javascript_onload_bottom' =>
         "challenge();\n"
     );
  }

  public function push($password){
    History::push('challenge_passwords',trim($password));
  }

  public function get_version(){
    return VERSION_PASSWORD;
  }
}
?>