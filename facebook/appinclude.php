<?php
require_once SYS_FACEBOOK.'facebook.php';
$appapikey = '65746d78558d1484ab6d1462412c11e3';
$appsecret = '4d29b8d5c04d4517f5af1651e3f09b0d';
$facebook = new Facebook($appapikey, $appsecret);
$user = $facebook->require_login();
$appcallbackurl = 'http://www.ecclesiact.com/facebook/';
//catch the exception that gets thrown if the cookie has an invalid session_key in it
try {
  if (!$facebook->api_client->users_isAppAdded()) {
    $facebook->redirect($facebook->get_add_url());
  }
}
catch (Exception $ex) {
  //this will clear cookies for your application and redirect them to a login prompt
  $facebook->set_user(null, null);
  $facebook->redirect($appcallbackurl);
}
?>