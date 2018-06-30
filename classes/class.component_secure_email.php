<?php
  define ("VERSION_COMPONENT_SECURE_EMAIL","1.0.0");
/*
Version History:
  1.0.0 (2011-12-31)
    1) Initial release - moved from Component class
*/
class Component_Secure_Email extends Component_Base {

  function draw($instance='', $args=array(), $disable_params=false){
    // Used by TSM, THRPA, HSF and TUC
    global $system_vars;
    if (isset($_REQUEST['send_secure_email'])) {
      $recipientEmail =     $_REQUEST['secure_email_recipient'];
      $recipientName =      $_REQUEST['secure_email_rname'];
      $senderEmail =        $_REQUEST['secure_email_sender'];
      $senderName =         $_REQUEST['secure_email_sname'];
      $message =            $_REQUEST['secure_email_message'];
      $decryptedRecipient = XORDecrypt($recipientEmail);
      get_mailsender_to_component_results();
      $result = mailto(
        array(
          'PEmail' =>           $decryptedRecipient,
          'NName' =>            $recipientName,
          'replyto_email' =>    $senderEmail,
          'replyto_name' =>     $senderName,
          'subject' =>          'Message from a user of '.$system_vars['textEnglish'],
          'html' =>             $senderName . ' (' . $senderEmail . ') wrote:<br />' . $message,
          'text' =>             ''
        )
      );
      if (strpos($result, 'Message-ID') !== false) {
        $html =
           "<span onclick='self.close();' style='text-decoration:underline;cursor:pointer;'>"
          ."Your message was sent successfully.  Click to close this window</span>";
      }
      else {
        $html = "<span onclick='self.close();' style='text-decoration:underline;cursor:pointer;'>There was an error sending your message : " . $result . ".  Click to close this window</span>";
      }
      return $html;
    }
    else {
      $email = '';
      $name = '';
      if (isset($_SESSION['person'])) {
        $p = new Person(get_userID());
        $email = $p->get_field('PEmail');
        $name = $_SESSION['person']['NFull'];
      }
      return
         "<input type='hidden' name='secure_email_recipient' value='' />"
        ."<label>Your Email:</label><input type='text' name='secure_email_sender' value='" . $email . "' /><br />"
        ."<label>Your Name:</label><input type='text' name='secure_email_sname' value='" . $name . "' /><br />"
        ."<label>Recipient:</label><input type='text' readonly name='secure_email_rname' /><br />"
        ."<label>Message:</label><text" . "area name='secure_email_message'></text" . "area><br />"
        ."<input class='button' type='submit' name='send_secure_email' value='Send' />"
        ."<input class='button' type='reset' value='Cancel' onclick='self.close();' />";
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_SECURE_EMAIL;
  }
}
?>