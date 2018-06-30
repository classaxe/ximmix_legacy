<?php
  define ("VERSION_COMPONENT_EMAIL_TO_FRIEND","1.0.0");
/*
Version History:
  1.0.0 (2011-12-29)
    1) Initial release - moved from Component class
*/
class Component_Email_To_Friend extends Component_Base {
  function draw() {
    global $system_vars, $mode, $submode, $command, $page_vars, $ID;
    $max_chars = 100;
    if ($personID = get_userID()) {
      $Obj = new Person($personID);
      $record = $Obj->get_record();
      if ($record['profile_locked']) {
        $personID = false;
      }
    }
    $msg = "";
    $email =        (isset($_POST['email']) ?           sanitize('html',$_POST['email'])  :      "");
    $your_email =   (isset($_POST['your_email']) ?      sanitize('html',$_POST['your_email']) :  ($personID ? $record['PEmail'] : ""));
    $your_message = (isset($_POST['your_message']) ?    sanitize('html',$_POST['your_message'])  :   "");
    $your_name =    (isset($_POST['your_name']) ?       sanitize('html',$_POST['your_name'])  :  ($personID ? trim($record['NFirst']." ".$record['NLast']) : ""));
    switch ($submode){
      case "send":
        $Obj = new Captcha;
        if (!$Obj->isKeyRight(isset($_POST['captcha_key']) ? $_POST['captcha_key'] : "NOWAY")) {
          $msg =	"<span style='color:red'><b>Error</b>: You must enter the same characters shown in the image.</span>";
          break;
        }
        component_result_set('system_title',$system_vars['textEnglish']);
        component_result_set('system_URL',$system_vars['URL']);
        get_mailsender_to_component_results();      // Use system default mail sender details
        $content =
          trim(
            strlen($page_vars['content_text'])>$max_chars ?
              substr($page_vars['content_text'],0,$max_chars)."\r\n(continues...)"
            :
              $page_vars['content_text']
            );
        $data =                     array();
        $data['NName'] =            "";
        $data['PEmail'] =           $email;
        $data['replyto_email'] =    $_POST['your_email'];
        $data['replyto_name'] =     $_POST['your_name'];
        $data['subject'] =          $system_vars['textEnglish']." E-mail: ".$page_vars['title'];
        $data['html'] =
          wordwrap(
             "<p><b>".$_POST['your_name']." [".$_POST['your_email']."]</b> wanted to share this ".$page_vars['object_name']
            ." from the <b>".$system_vars['textEnglish']."</b> website with you.</p>\r\n\r\n"
            .($_POST['your_message'] ?
                "<p><b>*** Message from ".$_POST['your_name']." ***</b><br />\r\n"
               .substr(strip_tags($_POST['your_message']),0,$max_chars)
               ."</p>\r\n\r\n\r\n" : "")
            ."<p><b>*** ".$page_vars['title']." ***</b><br />\r\n"
            ."<p>".nl2br($content)."</p>\r\n\r\n"
            ."<p>To read the complete ".$page_vars['object_name'].", please visit<br />\r\n"
            ."<a href=\"".trim($page_vars['absolute_URL'],'/').(isset($page_vars['path_extension']) ? "/".$page_vars['path_extension'] : "")."\"><b>".trim($page_vars['absolute_URL'],'/').(isset($page_vars['path_extension']) ? "/".$page_vars['path_extension'] : "")."</b></a></p>\r\n\r\n\r\n"
            ."<p><b>*** Disclaimer ***</b><br />\r\n"
            ."Please note that we cannot verify the name or email of the person who sent this message to you, "
            ."nor are we responsible for the contents of this email.</p>\r\n\r\n"
            ."<p>Sincerely,<br />\r\n<br />\r\n"
            .$system_vars['textEnglish']." 'E-mail a Friend' service.</p>"
          );
        $data['text'] = wordwrap(html_entity_decode(strip_tags($data['html'])));
        $result = mailto($data);
        if (System::has_feature('Activity-Tracking')){
          $ObjActivity = new Activity;
          $ObjActivity->do_tracking('emails',$page_vars['object_type'],$page_vars['ID'],1);
        }
        $submode='sent';
      break;
    }
    $head =
       DOCTYPE."\n"
      ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n"
      ."<head>\n"
      ."<title>Email '".$page_vars['title']."' to a friend</title>\n"
      ."<style type='text/css'>\n"
      ."form { padding: 0; margin: 0; }\n"
      ."h1 {font-size: 120%}\n"
      ."span.req { color: red; }\n"
      ."table.box { border-collapse: collapse; margin-bottom: 1em; border: 1px solid #888;}\n"
      ."th, td { vertical-align: top; text-align: left; padding: 2px; }\n"
      ."th { border: 1px solid #888; background-color: #eef; vertical-align: top; }\n"
      ."td.req { width: 0.5em; color: red; float: right; }\n"
      .".lbl { width: 10em; }\n"
      ."</style>"
      ."<script type='text/javascript'>\n"
      ."function char_counter(input,max,countID) {\n"
      ."  var _count = document.getElementById(countID);\n"
      ."  if (input.value.length>max) {input.value = input.value.substr(0,max); }\n"
      ."  _count.innerHTML = max - input.value.length + ' character' + (input.value.length==max-1 ? '' : 's')+' left';\n"
      ."}\n"
      ."function email_check(val){\n"
      ."  return !(val.length<6 || val.indexOf('@')<1 || val.lastIndexOf('.')-2<val.lastIndexOf('@'));\n"
      ."}\n"
      ."function check(frm){\n"
      ."  var out = [];\n"
      ."  if (!email_check(frm.elements['email'].value))      { out.push((1+out.length) + \") Recipient's Email Address\"); }\n"
      ."  if (!frm.elements['your_name'].value.length) { out.push((1+out.length) + \") Your Name (so the recipient knows who sent this)\"); }\n"
      ."  if (!email_check(frm.elements['your_email'].value)) { out.push((1+out.length) + \") Your OWN Email Address (this is also required)\"); }\n"
      ."  if (!frm.elements['captcha_key'].value.length) { out.push((1+out.length) + \") Characters shown in the image (slows down spammers!)\"); }\n"
      ."  if (out.length>0) {\n"
      ."    alert('Please check the following details and try again:\\n\\n' + out.join('\\n'));\n"
      ."  }\n"
      ."  return (out.length==0);\n"
      ."}\n"
      ."</script>\n"
      ."</head>\n";

    switch ($submode){
      case "sent":
        print
           $head
          ."<body>\n"
          ."<div style='text-align: center; margin: auto; width: 80%;'>\n"
          ."<div>\n"
          ."<h1>You sent this message using our 'E-mail a Friend' service:</h1>\n"
          ."<table summary='Summary of message sent' class='box'>"
          ."  <tr>\n"
          ."    <th colspan='2'>Your message</th>\n"
          ."  </tr>\n"
          ."  <tr>\n"
          ."    <th class='lbl'>To:</th>\n"
          ."    <td>".$_POST['email']."</td>\n"
          ."  </tr>\n"
          ."  <tr>\n"
          ."    <th class='lbl'>Subject</th>\n"
          ."    <td>".$data['subject']."</td>\n"
          ."  </tr>\n"
          ."  <tr>\n"
          ."    <th class='lbl'>From:</th>\n"
          ."    <td>".$_POST['your_name']." - ".$_POST['your_email']."</td>\n"
          ."  </tr>\n"
          ."  <tr>\n"
          ."    <th class='lbl'>Content</th>\n"
          ."    <td>".$data['html']."</td>\n"
          ."  </tr>\n"
          ."</table>"
          ."<input type='button' value='Done' onclick='window.close()' />\n"
          ."</div></div></form></body></html>";
      break;
      default:
        print
           $head
          ."<body>\n"
          ."<form id='form' enctype='multipart/form-data' method='post' action=\""
          .trim($page_vars['absolute_URL'],'/')
          .(isset($page_vars['path_extension']) ? "/".$page_vars['path_extension'] : "")
          ."\" onsubmit=\"return check(this)\">\r\n"
          ."<div style='text-align: center; margin: auto; width: 80%;'>\n"
          ."<div>\n"
          ."<p class='margin_none padding_none'>\r\n"
          .draw_form_field('command',$command,'hidden')."\r\n"
          .draw_form_field('submode','send','hidden')."\r\n"
          ."</p>\n"
          ."<table summary='send to friend form'>\n"
          ."  <tr>\n"
          ."    <td>\n"
          ."<h1 style='text-align:center'>Send <i>".$page_vars['title']."</i> to a friend</h1>\n"
          ."      <table summary='Destination address' class='box'>"
          ."      <tr>\n"
          ."        <th colspan='3'>To:</th>\n"
          ."      </tr>\n"
          ."      <tr>\n"
          ."        <td class='lbl'><label for='email'>E-Mail Address</label></td>\n"
          ."        <td class='req'>*</td>\n"
          ."        <td>".draw_form_field('email',$email,'text','300')."</td>\n"
          ."      </tr>\n"
          ."    </table>"
          ."    <table summary='Sender Details' class='box'>"
          ."      <tr>\n"
          ."        <th colspan='3'>Your Details:</th>\n"
          ."      </tr>\n"
          ."      <tr>\n"
          ."        <td class='lbl'><label for='your_name'>Your Name:</label></td>\n"
          ."        <td class='req'>*</td>\n"
          ."        <td>".draw_form_field('your_name',$your_name,'text','300')."</td>\n"
          ."      </tr>\n"
          ."      <tr>\n"
          ."        <td class='lbl'><label for='your_email'>Your Email:</label></td>\n"
          ."        <td class='req'>*</td>\n"
          ."        <td>".draw_form_field('your_email',$your_email,'text','300')."</td>\n"
          ."      </tr>\n"
          ."      <tr>\n"
          ."        <td class='lbl'><label for='your_message'>Message:</label><br />\n"
          ."(<span id='your_message_chars'>".($max_chars - strlen($your_message))." characters left</span>)</td>\n"
          ."        <td class='req'></td>\n"
          ."        <td>".draw_form_field('your_message',$your_message,'textarea','300',"","","onkeypress=\"return this.value.length<".$max_chars.";\" onkeyup=\"char_counter(this,".$max_chars.",'your_message_chars');\"" )."</td>\n"
          ."      </tr>\n"
          ."      <tr class='table_header'>\n"
          ."        <td class='va_t'>&nbsp;Verification Image&nbsp;</td>\n"
          ."        <td class='req'></td>\n"
          ."        <td><img class='formField std_control' style='border:1px solid #7F9DB9;padding:2px;' src='".BASE_PATH."?command=captcha_img' alt='Verification Image' /></td>\n"
          ."      </tr>\n"
          .($msg ?
               "      <tr class='table_header'>\n"
              ."        <td colspan='3'>".$msg."</td>\n"
              ."      </tr>\n"
            :  ""
           )
          ."      <tr class='table_header'>\n"
          ."        <td>&nbsp;Verification Code&nbsp;</td>\n"
          ."        <td class='req'>*</td>\n"
          ."        <td><input type='text' name='captcha_key' size='20' style='width: 180px;' value=\"\"/></td>\n"
          ."      </tr>\n"
          ."    </table>\n"
          ."    <div style='float: left;'><span class='req'>*</span> Required &nbsp;</div>\n"
          ."    <div style='float: right'><input type='submit' value='Send the ".$page_vars['object_name']."' /></div>\n"
          ."  </td>\n"
          ."</table>\n"
          ."</div></div></form></body></html>";
        break;
      }
  }

  public function get_version(){
    return VERSION_COMPONENT_EMAIL_TO_FRIEND;
  }
}
?>