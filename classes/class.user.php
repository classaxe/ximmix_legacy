<?php
define('VERSION_USER','1.0.7');
/*
Version History:
  1.0.7 (2014-02-17)
    1) User::update_logon_count() now validates updated fields against field list

  (Older version history in class.user.txt)
*/
class User extends Person {
  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_object_name('User');
    $this->_set_type('user');
    $this->set_edit_params(
      array(
        'report' =>                 'user',
        'report_rename' =>          true,
        'report_rename_label' =>    'new username'
      )
    );
    $this->_cp_vars_listings['block_layout']['default'] = 'User';
  }

  function do_signin() {
    global $page_vars;
    $vars = array();
    foreach ($_GET as $req=>$val){
      $vars[$req] = $val;
    }
    foreach ($_POST as $req=>$val){
      $vars[$req] = $val;
    }
    $query_variables = array();
    foreach ($vars as $req=>$val){
      switch ($req){
        case "anchor":
        case "command":
        case "goto":
        case "msg":
        case "rnd":
        case "password":
        case "topbar_password":
        case "topbar_username":
        case "username":
        case "":
        break;
        default:
          if ($val!==''){
            $query_variables[] = $req.'='.$val;
          }
        break;
      }
    }
    $query = (count($query_variables) ? "&".implode('&',$query_variables) : "");
    $username =         sanitize('html',trim(get_var('username')));
    $password =         sanitize('html',trim(get_var('password')));
    $password_enc =     ($password ? encrypt(strToLower($password)) : "");
    $result =           $this->get_person_for_signin($username,$password_enc);
    $url =              Page::get_URL($page_vars);
    switch($result['status']['code']){
      case 100:
        header(
           "Location: ".$url
          ."?msg=missing"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
          .$query
          .get_var('anchor')
        );
      break;
      case 101:
        header(
           "Location: ".$url
          ."?msg=invalid"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
          .$query
          .get_var('anchor')
        );
      break;
      case 102:
        header(
           "Location: ".$url
          ."?msg=inactive"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
          .$query
          .get_var('anchor')
        );
      break;
      case 200:
        $proxy_user = get_userPUsername();
        $this->_set_ID($result['data']['ID']);
        $this->get_person_to_session($username,$password_enc);
        $this->update_logon_count('Sign In',$proxy_user);
        header(
           "Location: ".$url
          ."?msg=success"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
          .$query
          .get_var('anchor')
        );
      break;
      default:
        die('Unexpected signin result - '.$result['status']['code']);
      break;
    }
    print "";die;
  }

  function do_signout(){
    session_unset();
    header("Location: ".BASE_PATH);
  }

  public function get_ssi_token_for_email(){
    return $this->single_signin_encode(component_result('personID'));
  }

  public function single_signin(){
    // http://laptop.ovationmeetings.ca/forms/Bayer-CC-NBM-2011-02-28/survey/?command=ssi&token=VgwHXhpGDAYDEU0WDRYKCgoSUl9dGwtEDQUMEUMTWkINBANeQwIEAQsGDwMOUVIOUQcCBlQMCQFRAQUEAQRUEwxL
    global $page_vars;
    if (!$token = get_var('token')){
      print "Single Signin token is missing"; die;
    }
    if (!$credentials = $this->single_signin_decode($token)){
      print "Single Signin token is invalid"; die;
    }
    $credentials['PUsername'] = '';
    $Obj = new User($credentials['ID']);
    $Obj->load();
    if ($Obj->record['PPassword']==$credentials['PPassword']){
      $credentials['PUsername'] = $Obj->record['PUsername'];
    }
    $url =      BASE_PATH.trim($page_vars['path'],'/');
    $result =   $this->get_person_for_signin($credentials['PUsername'],$credentials['PPassword']);
    switch($result['status']['code']){
      case 100:
        header(
           "Location: ".$url
          ."?msg=missing"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
        );
        die('Missing Username / Password');
      break;
      case 101:
        header(
           "Location: ".$url
          ."?msg=invalid"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
        );
        die('Invalid Username / Password');
      break;
      case 102:
        header(
           "Location: ".$url
          ."?msg=inactive"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
        );
        die('Your account is inactive');
      break;
      case 200:
        $proxy_user = get_userPUsername();
        $this->get_person_to_session($credentials['PUsername'],$credentials['PPassword']);
        $this->_set_ID($result['data']['ID']);
        $this->update_logon_count('SSI',$proxy_user);
        header(
           "Location: ".$url
          ."?msg=success"
          ."&rnd=".dechex(mt_rand(0,mt_getrandmax()))
        );
        die('Signing in...');
      break;
    }
  }

  public static function single_signin_decode($string){
    $credentials = @unserialize(XORDecrypt($string));
    if (!is_array($credentials)){
      return false;
    }
    return array(
      'ID' =>           sanitize('html',$credentials['i']),
      'PPassword' =>    sanitize('html',$credentials['p'])
    );
  }

  public static function single_signin_encode($ID){
    global $component_result;
    if (isset($component_result['PPassword'])){
      $PPassword = encrypt($component_result['PPassword']);
    }
    else {
      $Obj =          new User($ID);
      $PPassword =    $Obj->get_field('PPassword');
    }
    $credentials = serialize(
      array(
        'i' =>  sanitize('html',$ID),
        'p' =>  sanitize('html',$PPassword)
      )
    );
    return XOREncrypt($credentials);
  }

  public function update_logon_count($method=false,$proxy_user=false){
    $PLogonCount =  (int)$this->get_field('PLogonCount');
    $IP =           $_SERVER['REMOTE_ADDR'];
    $host =         gethostbyaddr($IP);
    $data =
      array(
        'PLogonCount' =>        $PLogonCount+1,
        'PLogonLastIP' =>       $IP,
        'PLogonLastHost' =>     $host,
        'PLogonLastMethod' =>   $method.($proxy_user ? ' by '.$proxy_user : ""),
        'PLogonLastDate' =>     get_timestamp()
      );
    $this->update($data,true);
  }

  public function get_version(){
    return VERSION_USER;
  }
}
?>