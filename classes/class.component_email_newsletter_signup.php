<?php
  define ("VERSION_COMPONENT_EMAIL_NEWSLETTER_SIGNUP","1.0.0");
/*
Version History:
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Email_Newsletter_Signup extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false) {
    global $system_vars, $page_vars;
    $ident =            "email_newsletter_signup";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'adminEmail' =>       array('match' => '',					'default'=>'',                    'hint'=>'Send form to this address'),
      'adminName' =>        array('match' => '',					'default'=>'',                    'hint'=>'Address form to this person'),
      'allowHTML' =>        array('match' => 'enum|0,1',			'default'=>0,                     'hint'=>'0|1'),
      'askComments' =>      array('match' => 'enum|0,1',			'default'=>1,                     'hint'=>'0|1'),
      'askCountry' =>       array('match' => '',					'default'=>1,                     'hint'=>'0|1|2 - 2 means required'),
      'askName' =>          array('match' => '',					'default'=>2,                     'hint'=>'0|1|2 - 2 means required'),
      'askStateProv' =>     array('match' => '',					'default'=>1,                     'hint'=>'0|1|2 - 2 means required'),
      'askSurname' =>       array('match' => '',					'default'=>0,                     'hint'=>'0|1|2 - 2 means required'),
      'field_width' =>      array('match' => '',					'default'=>350,                   'hint'=>'width in px'),
      'signupMethod' =>     array('match' => 'enum|admin,online',	'default'=>'admin',               'hint'=>'admin|online'),
      'successURL' =>       array('match' => '',					'default'=>'newsletter-success',  'hint'=>'Page to display if succeeded')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $bad_captcha =  false;
    if (!empty($_REQUEST['submode'])) {
      switch ($_REQUEST['submode']){
        case "send":
          $Obj = new Captcha();
          if (!isset($_POST['captcha_key']) || !$Obj->isKeyRight($_POST['captcha_key'])){
            $bad_captcha = true;
            break;
          }
          get_mailsender_to_component_results(); // Use system default mail sender details
          $title = $system_vars['textEnglish']." newsletter signup via ".$page_vars['absolute_URL'];
          component_result_set('NName',$cp['adminName']);
          component_result_set('PEmail',$cp['adminEmail']);
          component_result_set('from_name',$system_vars['adminName']);
          component_result_set('from_email',$system_vars['adminEmail']);
          $text_arr =     array();
          $html_arr =     array();
          $ignore_arr =   explode(",",SYS_STANDARD_FIELDS.",captcha_key");
          foreach ($_POST as $field=>$value) {
            if ($value!=="" && !in_array($field,$ignore_arr)){
              $value = ($cp['allowHTML'] ? $value : sanitize('html',$value));
              $text_arr[] = pad($field,25).$value."\n";
              $html_arr[] =
                 "  <tr>\n"
                ."    <th style='text-align:left'>".$field."</th>\n"
                ."    <td>".($value=="" ? "&nbsp;" : $value)."</td>\n"
                ."  </tr>\n";
            }
          }
          component_result_set('content_html',
            "<h1>".$title."</h1>\n"
           ."<table cellpadding='2' cellspacing='0' border='1' bordercolor='#808080' bgcolor='#ffffff'>\n"
           ."  <tr>\n"
           ."    <th style='text-align:left;background-color:#e0e0e0;'>Field</th>\n"
           ."    <th style='text-align:left;background-color:#e0e0e0;'>Value</th>\n"
           ."  </tr>\n"
           .implode("",$html_arr)
           ."</table>"
          );
          component_result_set('content_text',
             "$title\n"
            .pad("FIELD",25)."VALUE\n"
            ."---------------------------------------------------------\n"
           .implode("",$text_arr)
          );
          $data =               array();
          $data['PEmail'] =     component_result('PEmail');
          $data['NName'] =      component_result('NName');
          $data['subject'] =    $system_vars['textEnglish']." newsletter signup via ".$page_vars['absolute_URL'];
          $data['html'] =       component_result('content_html');
          $data['text'] =       component_result('content_text');
          $mail_result = mailto($data);
          if (substr($mail_result,0,12)=="Message-ID: ") {
             header(
                "Location: ".BASE_PATH.$cp['successURL']
             );
          }
          return $mail_result;
        break;
      }
    }
    Page::push_content(
      'javascript',
       "function email_newsletter_signup_validate() {\n"
      ."  var err = [];\n"
      .($cp['askName']=='2' ?       "  if (geid_val('name').length==0)         { err.push((err.length+1)+') Your Name'); }\n" : "")
      .($cp['askSurname']=='2' ?    "  if (geid_val('surname').length==0)      { err.push((err.length+1)+') Your Surname'); }\n" : "")
      .                             "  if (geid_val('email').indexOf('@')==-1) { err.push((err.length+1)+') A valid Email Address'); }\n"
      .($cp['askStateProv']=='2' ?  "  if (geid_val('sp').length==0)           { err.push((err.length+1)+') Your State or Province'); }\n" : "")
      .($cp['askCountry']=='2' ?    "  if (geid_val('country').length==0)      { err.push((err.length+1)+') Your Country'); }\n" : "")
      .                             "  if (geid_val('captcha_key').length!=6)  { err.push((err.length+1)+') Verification text (6 characters)'); }\n"
      ."  err = err.join('\\n');\n"
      ."  if (err.length==0){\n"
      ."    geid_set('submode','send');geid('form').submit();\n"
      ."  }\n"
      ."  else {\n"
      ."    alert('The following fields are required:\\n\\n'+err);\n"
      ."  }\n"
      ."}"
    );

    $out.=
       "<table class='minimal' summary='Newsletter Signup Form'>\n"
      ."  <tbody>\n"
      .($cp['askName']=='1' || $cp['askName']=='2' ?
           "    <tr>\n"
          ."      <th class='va_t' style='width:200px;'><label for='name'>"
          .($cp['askName']=='2' ? "<span class='req'>*</span> " : "")
          ."Your Name</label></th>\n"
          ."      <td class='va_t'>"
          .draw_form_field('name',(!empty($_REQUEST['name']) ? $_REQUEST['name'] : ''),'text',$cp['field_width'])
          ."</td>\n"
          ."    </tr>\n"
        :  ""
       )
      .($cp['askSurname']=='1' || $cp['askSurname']=='2' ?
           "    <tr>\n"
          ."      <th class='va_t' style='width:200px;'><label for='surname'>"
          .($cp['askSurname']=='2' ? "<span class='req'>*</span> " : "")
          ."Your Surname</label></th>\n"
          ."      <td class='va_t'>"
          .draw_form_field('surname',(!empty($_REQUEST['surname']) ? $_REQUEST['surname'] : ''),'text',$cp['field_width'])
          ."</td>\n"
          ."    </tr>\n"
        :  ""
       )
      ."    <tr>\n"
      ."      <th class='va_t'><label for='email'><span class='req'>*</span> Your Email Address</label></th>\n"
      ."      <td class='va_t'>"
      .draw_form_field('email',(!empty($_REQUEST['email']) && strpos($_REQUEST['email'],'@')>0 ? $_REQUEST['email'] : ''),'text',$cp['field_width'])
      ."</td>\n"
      ."    </tr>\n"
      .($cp['askStateProv']=='1' || $cp['askStateProv']=='2' ?
           "    <tr>\n"
          ."      <th class='va_t'><label for='sp'>"
          .($cp['askStateProv']=='2' ? "<span class='req'>*</span> " : "")
          ."State / Province</label></th>\n"
          ."      <td class='va_t'>"
          .draw_form_field('sp',(!empty($_REQUEST['sp']) ? $_REQUEST['sp'] : ''),'combo_listdata',$cp['field_width'],'',0,'',0,0,'','lst_sp')
          ."</td>\n"
          ."    </tr>\n"
        : ""
       )
      .($cp['askCountry']=='1' || $cp['askCountry']=='2' ?
           "    <tr>\n"
          ."      <th class='va_t'><label for='country'>"
          .($cp['askCountry']=='2' ? "<span class='req'>*</span> " : "")
          ."Country</label></th>\n"
          ."      <td class='va_t'>"
          .draw_form_field('country',(!empty($_REQUEST['country']) ? $_REQUEST['country'] : 'CAN'),'selector_listdata',$cp['field_width'],'','','','','','','lst_country|0')
          ."</td>\n"
          ."    </tr>\n"
        : ""
       )
      .($cp['askComments']=='1' ?
           "    <tr>\n"
          ."      <th class='va_t'><label for='comments'>"
          ."Comments</label></th>\n"
          ."      <td class='va_t'>"
          .draw_form_field('comments',(!empty($_REQUEST['comments']) ? $_REQUEST['comments'] : ''),'textarea',$cp['field_width'],'','','','','','','',100)
          ."</td>\n"
          ."    </tr>\n"
        : ""
       )
      ."    <tr>\n"
      ."      <th class='va_t'>Verification image -<br />\nLetters are lower-case<br />\n"
      ."<a href=\"javascript:void(geid('captcha_image').src='".BASE_PATH."?command=captcha_img&rnd='+Math.random())\">(<b><i>Unclear? Try another image)</i></b></a></th>\n"
      ."      <td><img id='captcha_image' class='formField' style='border:1px solid #7F9DB9;padding:2px;' src='".BASE_PATH."?command=captcha_img&rnd=".(rand(100000,999999))."' alt='' /></td>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <th id='th_captcha_key'".($bad_captcha ? " style='color:#ff0000;'" : "")."><label for='captcha_key'>Verification text<br /><span style='font-weight:normal'>(Type the characters you see)</span></label> </th>\n"
      ."      <td class='va_t'><input type='text' name='captcha_key' id='captcha_key' size='20' style='width: 180px;' value=\"\"/></td>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td colspan='2' class='txt_c'><input type='button' onclick=\"email_newsletter_signup_validate()\" value='Send' /></td>\n"
      ."    </tr>\n"
      ."  </tbody>\n"
      ."</table>";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_EMAIL_NEWSLETTER_SIGNUP;
  }
}
?>