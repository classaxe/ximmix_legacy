<?php
define("SUBMISSION_VERSION","SUBMISSION_THEAURORAN 1.0.10");
/*
Version History:
  1.0.10 (2011-11-09)
    1) Changed types for video and prerolls to 'custom-preroll' and 'custom-video'
  1.0.9 (2011-01-31)
    1) Changed add() method to insert() in Submission::handleForm()
  1.0.8 (2011-01-24)
    1) Changes to remove deprecated function calls
  1.0.7 (2010-05-19)
    1) Change to Submission::_get_type_spec_coupon() to make coupons unmoderated
  1.0.6 (2009-12-19)
    1) Submission::handleForm() now includes uploaded file path when writing XML
       for video
  1.0.5 (2009-12-17)
    1) Fixed bug in Submission::handleForm() so that credits are deducted correctly
  1.0.4 (2009-11-16)
    1) Removed implementation of FCKEditor - too open to abuse
    2) Now sets category assignments and themeID
  1.0.3 (2009-11-06)
    1) Tidy up and changed remaining product codes to match those now provided
       with url-safe paths
  1.0.2 (2009-11-02)
    1) Initialised default value for codeReceived to prevent unassigned var warning
    2) Fix to Submission::drawForm() to deal with change to products table
  1.0.1 (2009-10-28)
    1) Modified to use built-in support for XML now standard in Record::add()
  1.0.0 (?)
    1) Initial release by James
*/


class Submission {
  var $currentType;
  var $currentType_spec;
  var $action;

  public function __construct($type='article', $action='submit') {
    $this->action = $action;
    $this->currentType = $type;
    $this->currentType_spec = $this->get_type_spec();
  }

  public function collectFormData() {
    $hasCaptcha = false;
    $okCaptcha = true;
    $theType = $this->currentType_spec;
    $codeReceived = "";
    foreach ($theType['fields'] as $key => $value) {
      $fieldType = getOrDont('type', $value, '');
      if ($fieldType == 'captcha') {
        $hasCaptcha = true;
        $c = new Captcha;
        $codeReceived = isset($_REQUEST[$key]) ? $_REQUEST[$key] : 'NOTCORRECT';
        if (!$c->isKeyRight($codeReceived)) {
        $okCaptcha = false;
        }
        break;
      }
      else {
        if (isset($_REQUEST[$key])) {
          $this->currentType_spec['fields'][$key]['value'] = $_REQUEST[$key];
        }
        else if (isset($this->currentType_spec['fields'][$key]['source'])) {
          $userID = get_userID();
          if ($userID != -1) {
            $sql = str_replace('%%userid%%', $userID, $this->currentType_spec['fields'][$key]['source']);
            $r = new Record;
            $value = $r->get_field_for_sql($sql);
            if ($value !== false) {
              $this->currentType_spec['fields'][$key]['value'] = $value;
            }
          }
        }
      }
    }
    return array($hasCaptcha, $okCaptcha,$codeReceived);
  }

  public function draw() {
    if ($this->currentType_spec===false){
      return "<p><b>Error:</b> The Submission class doesn't yet recognise ".$this->currentType."</p>";
    }
    switch ($this->action) {
      case 'submit':
        if (isset($_REQUEST['submission_' . $this->currentType])) {
          // a form was posted, handle it
          $out = $this->handleForm();
        }
        else if(isset($_REQUEST['submission_preview_' . $this->currentType])) {
          // there is an intermediate page to preview and confirm
          $out = $this->previewForm();
        }
        else {
          // a form must be shown
          $out = $this->drawForm();
        }
      break;
      case 'moderate':
        //
      break;
    }
    return $out;
  }

  public function drawForm() {
    list($hasCaptcha, $okCaptcha) = $this->collectFormData();
    $theType = $this->currentType_spec;
    $html = '';
    $js = $this->getFormJS();
    $css = '';
    //$html .= "<textarea id='theDebugTextarea' style='background:pink;'></textarea>";
    $html .= "<div class='submission_container'>";
    $html .= "<div id='coverup' style='display:none;'>Your data is being sent to the server</div>";
    if (isset($theType['product']) && $theType['product'] !== false) {
      $p = new Product;
      $productID = $p->get_ID_by_name($theType['product']);
      $p->_set_ID($productID);
      $productData = $p->get_record();
      $html .= "<div class='product_charge'>The <b>".$productData['itemCode']."</b> product carries a charge of " . $productData['module_creditsystem_creditPrice'] . " credits.</div>";
    }

    $html .= "<fieldset class='submission' id='submission_fieldset'>\r\n";
    $html .= "<legend>" . getOrDont('title', $theType, '') . "</legend>\r\n";
    if (isset($theType['preview']) && $theType['preview'] === true) {
      $html .= "<input type='hidden' name='submission_preview_" . $this->currentType . "' value='previewed' />\r\n";
    }
    else {
      $html .= "<input type='hidden' name='submission_" . $this->currentType . "' value='confirmed' />\r\n";
    }
    $onSubmitJS = array();
    $needsFileChooserJS = false;
    $includedCalendarJS = false;

    foreach ($theType['fields'] as $key => $value) {
      if (!isset($value['visible']) || $value['visible'] === true && (!isset($value['moderationonly']) || $value['moderationonly'] === false )) {
        $html .= "<div class='formfield hloff' onmouseover='this.className=\"formfield hlon\";' onmouseout='this.className=\"formfield hloff\";'>\r\n";
        $html .= "<label>" . $value['title'];
        $required = getOrDont('required', $value, false);
        if ($required) {
          $html .= " <span title='This field is required' style='color:red;'>*</span>";
        }
        $html .= "</label>";
      }

      $fieldType =  getOrDont('type', $value, 'text');
      $required =   getOrDont('required', $value, false);
      $validation = getOrDont('validation', $value, '');

      if ($required || strlen($validation) > 0) {
        $onSubmitJS[] .= "validateField('$key','" . $value['title'] . "'," . ($required ? 'true' : 'false') . ",'$validation');";
      }
      switch ($fieldType) {
        case 'text':
          $html .= "<br /><input type=\"text\" name=\"$key\" value=\"" . getOrDont('value', $value, '') . "\" />\r\n";
        break;
        case 'date':
          if (!$includedCalendarJS) {
            $html .=
               "<script type='text/javascript' src='/js/tigra_calendar/calendar_db.js'></script>\r\n"
              ."<link rel='stylesheet' type='text/css' href='/js/tigra_calendar/calendar.css' />\r\n";
            $includedCalendarJS = true;
          }
          $html .=
             "<br /><input type='text' class='calendarInput' name=\"".$key."\" value=\""
             .getOrDont('value', $value, '')."\" />\r\n"
            ."<script type='text/javascript'>\r\n"
            ."new tcal({'formname':'form','controlname':\"".$key."\"});\r\n"
            ."</script>\r\n";
        break;
        case 'textarea':
          $html .= "<textarea name=\"".$key."\">" . getOrDont('value', $value, '') . "</textarea>";
        break;
        case 'file':
          $html .=
             "<input type='hidden' name='MAX_FILE_SIZE' value='100000000' />"
            ."<br /><input type=\"file\" name=\"".$key."\" />\r\n"; // value='" . getOrDont('value', $value, '') . "'
        break;
        case 'all_category_dropdown':
          $html .=
             "<br /><select name=\"".$key."\">\r\n<option value=\"\">Choose a category</option>\r\n"
            .getCategories(getOrDont('value', $value, ''), ',unavailable')
            ."</select>\r\n";
        break;
        case 'business_category_dropdown':
          $html .=
             "<br /><select name=\"".$key."\">\r\n<option value=\"\">Choose a category</option>\r\n"
            .getCategories(getOrDont('value', $value, ''), '')
            ."</select>\r\n";
        break;
        case 'FCKEditor':
          $html .=
             "<br />"
    //      ."<textarea name='$key'>" . getOrDont('value', $value, '') . "</textarea>"
            .draw_form_field($key, getOrDont('value', $value, ''), "html", 555, "", 0, "", 0, 0, "", "Public_Submission", 300);
        break;
        case 'captcha':
          $html .=
             "&nbsp;&nbsp;<a title='Click to load a new code' style='font-size: 85%;' href='' onclick='document.getElementById(\"submission_captcha_image\").src=\"/?command=captcha_img&rnd=\"+Math.random();return false;'>Unclear?</a><br />"
            ."<img id='submission_captcha_image' class='formField std_control' style='border:1px solid #7F9DB9;padding:2px;margin:5px 0' src='".BASE_PATH."img/spacer' alt='Verification Image' width='180' height='50' />"
            ."<input type='text' name='$key' style='width:183px;' value='' />\r\n"
            ."<script type='text/javascript'>\r\n"
            ."if (window.onload) {\r\n"
            ."\tvar oldLoad = window.onload;\r\n"
            ."\twindow.onload = function () {\r\n"
            ."\t\toldLoad();\r\n"
            ."\t\tdocument.getElementById('submission_captcha_image').src='"
            .BASE_PATH."?command=captcha_img';\r\n"
            ."\t};"
            ."}\r\n"
            ."else {\r\n"
            ."\twindow.onload = function(){\r\n"
            ."\t\tdocument.getElementById('submission_captcha_image').src='"
            .BASE_PATH."?command=captcha_img';\r\n"
            ."\t};"
            ."}"
            ."</script>\r\n";
        break;
        case 'fixed':
          $html .=
             "<input type='hidden' name=\"".$key."\" value=\""
            .(isset($value['value']) ? $value['value'] : "")
            ."\" />";
        break;
        case 'tac':
          $html .= "<input class='checkbox' type='checkbox' name=\"".$key."\" value='yes' id='tac_$key' />&nbsp;&nbsp;<a href='/terms' rel='external' style='font-size:85%;'>Read the Terms and Conditions</a>";
        break;
      }
      if (!isset($value['visible']) || $value['visible'] === true) {
        $html .= "</div>";
      }
    }
    $html .=
       "<input type='submit' class='button' value='submit' onclick='this.form.action = \"\";' />\r\n"
      ."</fieldset>"
      ."</div>"
      ."<script type='text/javascript'>\r\n"
      .$js
      ."document.forms[0].onsubmit = function(){\r\n"
      ."\tvar errorMsgs = '';\r\n";
    foreach ($onSubmitJS as $thingy) {
      $html .=
         "\tvar errorMsg = $thingy\r\n"
        ."\tif (errorMsg.length > 0) {\r\n"
        ."\t\terrorMsgs += errorMsg + '\\r\\n';\r\n"
        ."\t}\r\n";
    }
    $html .=
       "\tif(errorMsgs.length > 0) {\r\n"
      ."\t\talert(errorMsgs);\r\n"
      ."\t\treturn false;\r\n"
      ."\t}\r\n"
      ."\tdocument.getElementById('coverup').style.display='';\r\n"
      ."\treturn true;\r\n"
      ."}\r\n"
      ."</script>\r\n";
    return $this->getFormCSS() . $html;
  }

  private function getFormCSS() {
    return
       "<style type='text/css'>\n"
      ."fieldset.submission div.formfield {\n"
      ."  padding: 5px;\n"
      ."  margin: 2px;\n"
      ."}\n"
      ."fieldset.submission div.hloff {\n"
      ."  background: transparent;\n"
      ."}\n"
      ."fieldset.submission div.hlon {\n"
      ."  background: #eeeeee;\n"
      ."}\n"
      ."fieldset.submission input, fieldset.submission select, fieldset.submission textarea {\n"
      ."  width: 98%;\n"
      ."}\n"
      ."fieldset.submission input.button {\n"
      ."  width: auto;\n"
      ."  margin: 0 7px;\n"
      ."}\n"
      ."fieldset.submission label {\n"
      ."  font-size: 85%;\n"
      ."}\n"
      ."fieldset.submission textarea {\n"
      ."  height: 100px;\n"
      ."}\n"
      ."fieldset.submission input.checkbox {\n"
      ."  width: auto;\n"
      ."  margin-left: 5px;\n"
      ."  vertical-align: middle;\n"
      ."}\n"
      ."fieldset.submission input.calendarInput {\n"
      ."  width:  200px;\n"
      ."}\n"
      ."</style>\n";
  }

  function getFormJS() {
    return
       "function validateField(fieldName, fieldTitle, isrequired, validationType) {\n"
      ."// code\n"
      ."  var theValue = document.forms[0].elements[fieldName].value;\n"
      ."  var inputType = document.forms[0].elements[fieldName].nodeName;\n"
      ."  inputType = (inputType == 'INPUT' ? document.forms[0].elements[fieldName].type : inputType);\n"
      ."  if (isrequired && theValue.length == 0 && inputType != 'checkbox') {\n"
      ."    return \"Error - the field '\" + fieldTitle + \"' must be completed\";\n"
      ."  }\n"
      ."  if (theValue.length == 0 && inputType != 'checkbox') {\n"
      ."    return '';\n"
      ."  }\n"
      ."  switch (validationType) {\n"
      ."    case 'email':\n"
      ."      var pat = /^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)\$/;\n"
      ."      if (!pat.test(theValue)) {\n"
      ."        return \"Error - the email address you entered in field '\" + fieldTitle + \"' is invalid\";\n"
      ."      }\n"
      ."    break;\n"
      ."    case 'phone':\n"
      ."      if (theValue.length < 10) {\n"
      ."        return \"Error - the phone number '\" + fieldTitle + \"' looks too short\";\n"
      ."      }\n"
      ."    break;\n"
      ."    case 'tac':\n"
      ."      if (!document.forms[0].elements[fieldName].checked) {\n"
      ."        return \"Error - You must accept the terms and conditions\";\n"
      ."      }\n"
      ."    break;\n"
      ."    default:\n"
      ."      if (validationType.indexOf('+') == 0) {\n"
      ."        var vtBits = validationType.substr(1).split(':');\n"
      ."        var num_of_days = vtBits[0] - 0;\n"
      ."        var compare_field = vtBits[1];\n"
      ."        var startDateBits = document.forms[0].elements[compare_field].value.split('-');\n"
      ."        var endDateBits = theValue.split('-');\n"
      ."        var startDate = new Date(startDateBits[0], startDateBits[1], startDateBits[2], 0, 0, 0);\n"
      ."        var endDate = new Date(endDateBits[0], endDateBits[1], endDateBits[2], 0, 0, 0);\n"
      ."        if (endDate.getTime() - startDate.getTime() > (num_of_days * 24 * 60 * 60 * 1000)) {\n"
      ."          return \"Error - the field '\" + fieldTitle + \"' cannot be more than \" + num_of_days + \" days later than the start date\";\n"
      ."        }\n"
      ."      }\n"
      ."    break;\n"
      ."  }\n"
      ."  return '';\n"
      ."}\n";
  }

  private function get_type_spec(){
    $function_name = '_get_type_spec_'.$this->currentType;
    if (method_exists($this,$function_name)){
      return $this->$function_name();
    }
    return false;
  }

  private function _get_type_spec_article(){
    return array(
      'type' => 'a',
      'title' => 'Submit Your Article',
      'oncomplete' => '/submit-article/thanks',
      'fields' => array(
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'validation' =>     'phone'
        ),
        'article_category' => array(
          'title' =>          'Category that best suits your article',
          'destination' =>    'category',
          'type' =>           'all_category_dropdown',
          'required' =>       true
        ),
        'article_title' => array(
          'title' =>          'Your Article Title',
          'destination' =>    'title',
          'required' =>       true
        ),
        'article_content' => array(
          'title' =>          'Type or paste your article',
          'destination' =>    'content',
          'type' =>           'textarea',
          'required' =>       true
        ),
        'article_captcha' => array(
          'title' =>          'Enter this code in the space provided',
          'type' =>           'captcha',
          'required' =>       true
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }

  private function _get_type_spec_article_member(){
    return array(
      'type' => 'a',
      'product' => 'member-article',
      'preview' => true,
      'moderated' => false,
      'title' => 'Submit Your Article',
      'oncomplete' => '/submit-article-member/thanks',
      'fields' => array(
        'personID' =>       array(
          'title' =>        '',
          'destination' => 'personID',
          'type' =>         'fixed',
          'value' =>        get_userID(),
          'visible' =>      false
        ),
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'source' =>         "SELECT CONCAT(NFirst, ' ', NLast) FROM person WHERE ID=%%userid%%",
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'source' =>         'SELECT PEmail FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'source' =>         'SELECT WTelephone FROM person WHERE ID=%%userid%%',
          'validation' =>     'phone'
        ),
        'article_title' => array(
          'title' =>          'Your Article Title',
          'destination' =>    'title',
          'required' =>       true
        ),
        'article_category' => array(
          'title' =>          'Category that best suits your article',
          'destination' =>    'category',
          'type' =>           'business_category_dropdown',
          'required' =>       true
        ),
        'article_content' => array(
          'title' =>          'Type or paste your article',
          'destination' =>    'content',
          'type' =>           'textarea',
          'required' =>       true
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }

  private function _get_type_spec_coupon(){
    return array(
      'type' => 'custom coupon',
      'product' => 'coupon-30-days',
      'max_day_range' => 30,
      'moderated' => false,
      'preview' => true,
      'title' => 'Create Your Coupon',
      'oncomplete' => '/create-a-coupon/thanks',
      'fields' => array(
        'personID' =>       array(
          'title' =>        '',
          'destination' => 'personID',
          'type' =>         'fixed',
          'value' =>        get_userID(),
          'visible' =>      false
        ),
        'coupon_location' => array(
          'title' => 'Location',
          'destination' => 'location',
          'required' => true
        ),
        'coupon_company' => array(
          'title' => 'Your Company Name',
          'destination' => 'author',
          'required' => true
        ),
        'coupon_content' => array(
          'title' => 'Content to appear on your coupon',
          'destination' => 'content',
          'type' => 'textarea',
          'required' => true
        ),
        'coupon_category' => array(
          'title' => 'Category for your coupon',
          'destination' => 'category',
          'type' => 'business_category_dropdown',
          'required' => true
        ),
        'coupon_rules' => array(
          'title' => 'Any terms and conditions',
          'destination' => 'custom_1',
          'type' => 'textarea'
        ),
        'coupon_startdate' => array(
          'title' => 'Start date',
          'destination' => 'date',
          'type' => 'date',
          'required' => true
        ),
        'coupon_enddate' => array(
          'title' => 'End date',
          'destination' => 'date_end',
          'type' => 'date',
          'required' => true,
          'validation' => '+30:coupon_startdate'
        ),
        'agree_tac' => array(
          'title' => 'I agree to the terms and conditions',
          'type' => 'tac',
          'required' => true,
          'validation' => 'tac'
        )
      )
    );
  }

  private function _get_type_spec_draw_member(){
    return array(
      'type' => 'survey',
      'product' => 'member-draw',
      'preview' => true,
      'moderated' => true,
      'title' => 'Submit Your Draw',
      'oncomplete' => '/submit-draw-member/thanks',
      'fields' => array(
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'source' =>         "SELECT CONCAT(NFirst, ' ', NLast) FROM person WHERE ID=%%userid%%",
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'source' =>         'SELECT PEmail FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'source' =>         'SELECT WTelephone FROM person WHERE ID=%%userid%%',
          'validation' =>     'phone'
        ),
        'draw_title' => array(
          'title' =>          'Your Draw Title',
          'destination' =>    'title',
          'required' =>       true
        ),
        'draw_category' => array(
          'title' =>          'Category that best suits your draw',
          'destination' =>    'category',
          'type' =>           'business_category_dropdown',
          'required' =>       true
        ),
        'draw_content' => array(
          'title' =>          'Type or paste the article that describes the draw and your prize - include a skill-testing question and multi-choice answers',
          'destination' =>    'content',
          'type' =>           'textarea',
          'required' =>       true
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }
  private function _get_type_spec_event(){
    return array(
      'type' => 'e',
      'title' => 'Submit Your Event',
      'oncomplete' => '/submit-event/thanks',
      'fields' => array(
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'validation' =>     'phone'
        ),
        'event_title' => array(
          'title' =>          'Your Event Title',
          'destination' =>    'title',
          'required' =>       true
        ),
        'event_category' => array(
          'title' =>          'Category that best suits your event',
          'destination' =>    'category',
          'type' =>           'all_category_dropdown',
          'required' =>       true
        ),
        'event_date' => array(
          'title' =>          'Date of your event',
          'type' =>           'date',
          'destination' =>    'date',
          'required' =>       true
        ),
        'event_time' => array(
          'title' =>          'Time of your event',
          'destination' =>    'time_start',
          'type' =>           'text'
        ),
        'event_link' => array(
          'title' =>          'Link to a web page that describes the event',
          'type' =>           'text',
          'destination' =>    'URL'
        ),
        'event_location' => array(
          'title' =>          'Location of your event',
          'type' =>           'text',
          'required' =>       true,
          'destination' =>    'location'
        ),
        'event_content' => array(
          'title' =>          'Type or paste your event description',
          'destination' =>    'content',
          'type' =>           'textarea',
          'required' =>       true
        ),
        'event_captcha' => array(
          'title' =>          'Enter this code in the space provided',
          'type' =>           'captcha',
          'required' =>       true
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }
  private function _get_type_spec_event_member(){
    return array(
      'type' => 'e',
      'title' => 'Submit Your Event',
      'product' => 'member-event',
      'moderated' => false,
      'preview' => true,
      'oncomplete' => '/submit-event-member/thanks',
      'fields' => array(
        'personID' =>       array(
          'title' =>        '',
          'destination' => 'personID',
          'type' =>         'fixed',
          'value' =>        get_userID(),
          'visible' =>      false
        ),
        'full_name' => array(
          'title' =>          'Your Full Name',
          'source' =>         "SELECT CONCAT(NFirst, ' ', NLast) FROM person WHERE ID=%%userid%%",
          'destination' =>    'author',
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'source' =>         'SELECT PEmail FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'source' =>         'SELECT WTelephone FROM person WHERE ID=%%userid%%',
          'validation' =>     'phone'
        ),
        'event_title' => array(
          'title' =>          'Your Event Title',
          'destination' =>    'title',
          'required' =>       true
        ),
        'event_category' => array(
          'title' =>          'Category that best suits your event',
          'destination' =>    'category',
          'type' =>           'business_category_dropdown',
          'required' =>       true
        ),
        'event_date' => array(
          'title' =>          'Date of your event',
          'type' =>           'date',
          'destination' =>    'date',
          'required' =>       true
        ),
        'event_time' => array(
          'title' =>          'Time of your event',
          'destination' =>    'time_start',
          'type' =>           'text'
        ),
        'event_link' => array(
          'title' =>          'Link to a web page that describes the event',
          'type' =>           'text',
          'destination' =>    'URL'
        ),
        'event_location' => array(
          'title' =>          'Location of your event',
          'type' =>           'text',
          'required' =>       true,
          'destination' =>    'location'
        ),
        'event_content' => array(
          'title' =>          'Type or paste your event description',
          'destination' =>    'content',
          'type' =>           'textarea',
          'required' =>       true
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }
  private function _get_type_spec_preroll(){
    return array(
      'type' => 'custom-preroll',
      'title' => 'Submit Your Preroll',
      'oncomplete' => '/submit-preroll/thanks',
      'fields' => array(
        'personID' =>       array(
          'title' =>        '',
          'destination' => 'personID',
          'type' =>         'fixed',
          'value' =>        get_userID(),
          'visible' =>      false
        ),
        'preroll_title' => array(
          'title' =>          'Your Preroll Title',
          'destination' =>    'title',
          'required' =>       true
        ),
        'preroll_description' => array(
          'title' =>          'Your Preroll Description',
          'destination' =>    'xml:video_description'
        ),
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'source' =>         "SELECT CONCAT(NFirst, ' ', NLast) FROM person WHERE ID=%%userid%%",
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'source' =>         'SELECT PEmail FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'source' =>         'SELECT WTelephone FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'phone'
        ),
        'video_category' => array(
          'title' =>          'Category for your preroll (this needs to be expanded to support multiple categories)',
          'destination' =>    'category',
          'type' =>           'business_category_dropdown',
          'required' =>       true
        ),
        'video_content' => array(
          'title' =>          'Upload your preroll video',
          'type' =>           'file',
          'required' =>       true
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }
  private function _get_type_spec_video(){
    return array(
      'type' => 'custom-video',
      'title' => 'Submit Your Video',
      'oncomplete' => '/submit-video/thanks',
      'fields' => array(
        'personID' =>       array(
          'title' =>        '',
          'destination' => 'personID',
          'type' =>         'fixed',
          'value' =>        get_userID(),
          'visible' =>      false
        ),
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'validation' =>     'phone'
        ),
        'video_title' => array(
          'title' =>          'Your Video Title',
          'destination' =>    'title',
          'required' =>       true
        ),
        'video_description' => array(
          'title' =>          'Your Video Description',
          'destination' =>    'xml:video_description'
        ),
        'video_category' => array(
          'title' =>          'Category that best suits your video',
          'destination' =>    'category',
          'type' =>           'all_category_dropdown',
          'required' =>       true
        ),
        'video_content' => array(
          'title' =>          'Upload your video',
          'type' =>           'file'//,
          //'required' => true
        ),
        'video_captcha' => array(
          'title' =>          'Enter this code in the space provided',
          'type' =>           'captcha',
          'required' =>       true
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }

  private function _get_type_spec_video_commercial(){
    return array(
      'type' =>         'customer-request',
      'email' =>        'cynthia.proctor@hotmail.com',
      'product' =>      'member-video-commercial',
      'preview' =>      true,
      'title' =>        'Commission a Video Commercial',
      'oncomplete' =>   '/video-commercial/thanks',
      'fields' => array(
        'personID' =>       array(
          'title' =>        '',
          'destination' => 'personID',
          'type' =>         'fixed',
          'value' =>        get_userID(),
          'visible' =>      false
        ),
        'subtype' => array(
          'title' =>        'Subtype',
          'destination' =>  'subtype',
          'type' =>         'fixed',
          'required' =>     true,
          'value' =>        'video-commercial',
          'visible' =>      false
        ),
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'source' =>         "SELECT CONCAT(NFirst, ' ', NLast) FROM person WHERE ID=%%userid%%",
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'source' =>         'SELECT PEmail FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'source' =>         'SELECT WTelephone FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'phone'
        ),
        'visit_date' => array(
          'title' =>          'Preferred date to film',
          'type' =>           'date',
          'destination' =>    'xml:preferred_date_to_film',
          'required' =>       false
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }

  private function _get_type_spec_profile_video(){
    return array(
      'type' =>         'customer-request',
      'email' =>        'cynthia.proctor@hotmail.com',
      'product' =>      'member-profile-video',
      'preview' =>      true,
      'title' =>        'Commission a Profile Video',
      'oncomplete' =>   '/profile-video/thanks',
      'fields' => array(
        'personID' =>       array(
          'title' =>        '',
          'destination' => 'personID',
          'type' =>         'fixed',
          'value' =>        get_userID(),
          'visible' =>      false
        ),
        'subtype' => array(
          'title' =>        'Subtype',
          'destination' =>  'subtype',
          'type' =>         'fixed',
          'required' =>     true,
          'value' =>        'profile-video',
          'visible' =>      false
        ),
        'username' => array(
          'title' =>          'Your Username',
          'destination' =>    'xml:username',
          'source' =>         "SELECT PUsername FROM person WHERE ID=%%userid%%",
          'required' =>       true,
          'visible' =>        false
        ),
        'full_name' => array(
          'title' =>          'Your Full Name',
          'destination' =>    'author',
          'source' =>         "SELECT CONCAT(NFirst, ' ', NLast) FROM person WHERE ID=%%userid%%",
          'required' =>       true
        ),
        'email_address' => array(
          'title' =>          'Your Email Address',
          'destination' =>    'xml:email_address',
          'source' =>         'SELECT PEmail FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'email'
        ),
        'phone_number' => array(
          'title' =>          'Your Phone Number',
          'destination' =>    'xml:phone_number',
          'source' =>         'SELECT WTelephone FROM person WHERE ID=%%userid%%',
          'required' =>       true,
          'validation' =>     'phone'
        ),
        'visit_date' => array(
          'title' =>          'Preferred date to visit',
          'type' =>           'date',
          'destination' =>    'xml:preferred_date_to_visit',
          'required' =>       false
        ),
        'agree_tac' => array(
          'title' =>          'I agree to the terms and conditions',
          'type' =>           'tac',
          'required' =>       true,
          'validation' =>     'tac'
        )
      )
    );
  }

  public function handleForm() {
    $theType = $this->currentType_spec;
    // figure out if the purchasing of a product in involved in the post
    $product =  (isset($theType['product']) ? $theType['product'] : "");
    $email =    (isset($theType['email']) ? $theType['email'] : "");

    $creditSystem = Base::use_module('CreditSystem');
    if($product){
      $Obj_Product = new Product;
      $productID = $Obj_Product->get_ID_by_name($product);
      $Obj_Product->_set_ID($productID);
      $productData = $Obj_Product->get_record();
      $priceInCredits = $productData['module_creditsystem_creditPrice'];
      if (!$creditSystem->calculateBalance(get_userID()) >= $priceInCredits) {
        return "Sorry, you have insufficient credits to perform this purchase";
      }
    }
    // check if we're using a captcha and go back to last page if its present and incorrect
    list($hasCaptcha, $okCaptcha, $codeReceived) = $this->collectFormData();
    if ($hasCaptcha && !$okCaptcha) {
      return
         "<p class='error'>Incorrect Code Entered "
        .$codeReceived
        ."</p>".$this->drawForm();
    }
    $fields = array();
    foreach ($theType['fields'] as $key => $value) {
      if (isset($value['destination'])) {
        if (array_key_exists($key, $_REQUEST)) {
          $fields[$value['destination']] = addslashes($_REQUEST[$key]);
          if ($value['destination'] == 'content') {
            // also add text content
            $fields['content_text'] = strip_tags(addslashes($_REQUEST[$key]));
          }
        }
      }
      else if ($value['type'] == 'file') {
        //print_r($_FILES);
        // we received a file, handle it
        $uploadData = $_FILES[$key];
        //print_r('handling file ' . $uploadData['name']);
        //print_r($uploadData);die();
        if (isset($uploadData['error']) && $uploadData['error'] != 0) {
          die('There was an error : ' . print_r($uploadData, true));
        }
        // get the filename and temporary file
        $fb = getFileBits($uploadData['name']);
        $fileName = VideoUtilities::uuidSecure();
        $fileName .= "." . $fb['ext'];
        // move the file into the unprocessed folder
        move_uploaded_file($uploadData['tmp_name'], WEB_ROOT . UNPROCESSED_DIR . $fileName);
        $fields['active'] = false;
        $fields['status'] = 'Video Uploaded';
        $fields['xml:video:original_filename']= addslashes($uploadData['name']);
        $fields['xml:video:unprocessed_filename']= addslashes(UNPROCESSED_DIR.$fileName);
      }
    }
    $setPermissions = (isset($theType['moderated']) && $theType['moderated'] == false);
    if ($setPermissions) {
      $fields['permPUBLIC'] = 1;
      $fields['permSYSLOGON'] = 1;
    }
    if (!isset($fields['date'])) {
      $fields['date'] = date('Y-m-d');
    }
    if ($this->currentType == 'event' || $this->currentType == 'event_member') {
      if (!isset($fields['time_end'])) {
        $fields['time_end'] = "";
      }
    }
    $Obj_Posting = new Posting;
    $fields['type'] = $theType['type'];
    $fields['themeID'] = 1;
    $fields['systemID'] = SYS_ID;
    $postingID = $Obj_Posting->insert($fields);
    if (isset($fields['category'])){
      $Obj_Posting->_set_ID($postingID);
      $Obj_Posting->category_assign($fields['category']);
    }
    if ($product!=""){
      $result = $creditSystem->doPurchase(get_userID(), $productID, 1, "'postingID':'" . $postingID . "'");
    }
    if ($email){
      $result = $this->email_form($email);
    }
    $redirect = getOrDont('oncomplete', $theType, '');
    //die($redirect);
    if (strlen($redirect) > 0) {
      header('Location: ' . $redirect);
    }
  }

  public function email_form($address){
    global $system_vars;
    get_mailsender_to_component_results(); // Use system default mail sender details
    $title = "Form submission from ".$system_vars['textEnglish'];
    component_result_set('PEmail',$address);
    component_result_set('from_name',$system_vars['adminName']);
    component_result_set('from_email',$system_vars['adminEmail']);
    $text_arr =     array();
    $html_arr =     array();
    $ignore_arr =   explode(",",SYS_STANDARD_FIELDS);
    $html =
      "<h1>".$title."</h1>\n"
     ."<table cellpadding='2' cellspacing='0' border='1' bordercolor='#808080' bgcolor='#ffffff'>\n"
     ."  <tr>\n"
     ."    <th align='left' style='text-align:left;background-color:#e0e0e0;'>Field</th>\n"
     ."    <th align='left' style='text-align:left;background-color:#e0e0e0;'>Value</th>\n"
     ."  </tr>\n";
    $text =
       $title."\n"
      .pad("FIELD",25)."VALUE\n"
      ."---------------------------------------------------------\n";
    foreach ($_POST as $field=>$value) {
      if ($value!=="" && !in_array($field,$ignore_arr)){
        $value = sanitize('html',$value);
        $text.= pad($field,25).$value."\n";
        $html.=
           "  <tr>\n"
          ."    <th align='left' style='text-align:left'>".$field."</th>\n"
          ."    <td>".($value=="" ? "&nbsp;" : $value)."</td>\n"
          ."  </tr>\n";
      }
    }
    $html.= "</table>";
    component_result_set('content_html',$html);
    component_result_set('content_text',$text);
    $data =             array();
    $data['subject'] =  "Form Submission from ".$system_vars['textEnglish'];
    $data['html'] =     component_result('content_html');
    $data['text'] =     component_result('content_text');
    $PEmail_csv =       explode(',',component_result('PEmail'));
    $errors =      "";
    foreach($PEmail_csv as $PEmail){
      $data['PEmail'] = trim($PEmail);
      $data['NName'] =  trim($PEmail);
      $mail_result = mailto($data);
      if (!substr($mail_result,0,12)=="Message-ID: ") {
        $errors.= $mail_result."\n";
      }
    }
    if ($errors){
      return $errors;
    }

  }

  public function previewForm() {
    list($hasCaptcha, $okCaptcha) = $this->collectFormData();

    $html =
       "<fieldset>\n"
      ."  <legend>Please check your posting and confirm your purchase</legend>\n";

    switch ($this->currentType) {
      case 'coupon':
        $previewData = array(
          'ID' =>       '000000000000',
          'content' =>  $this->currentType_spec['fields']['coupon_content']['value'],
          'author' =>   $this->currentType_spec['fields']['coupon_company']['value'],
          'location' => $this->currentType_spec['fields']['coupon_location']['value'],
          'custom_1' => $this->currentType_spec['fields']['coupon_rules']['value'],
          'date' =>     $this->currentType_spec['fields']['coupon_startdate']['value'],
          'date_end' => $this->currentType_spec['fields']['coupon_enddate']['value']
        );
        $html .= PostingPreview::draw($this->currentType, $previewData);
      break;
    }

    $theType = $this->currentType_spec;

    $productInfo = "";

    if (isset($theType['product']) && $theType['product'] !== false){
      $Obj_Product = new Product;
      $productData = $Obj_Product->get_record_by_name($theType['product']);
      $productInfo .=
         "<p><b>".$productData['title']."</b>: "
        .$productData['module_creditsystem_creditPrice']." credits.<br />"
        .$productData['content']
        ."</p>";
    }

    if (isset($this->currentType_spec['product'])){
      // a product purchase is required
      // make sure the user confirms the purchase
      //$html .= "<fieldset><legend>Please confirm your purchase</legend>";
      // add all of the data in as hidden fields
      $html .= "\t<input type='hidden' id='submittype' name='submission_".$this->currentType . "' value='purchase-confirmed' />\r\n";
      foreach ($this->currentType_spec['fields'] as $fieldName => $field) {
        if (isset($field['value']) && $field['value']) {
          $html .= "\t<input type='hidden' name=\"".$fieldName."\" value=\"".htmlentities($field['value'])."\" />\r\n";
        }
      }
      $html .= $productInfo;
      $html .= "\t<input type='submit' value='Go Back' onclick='var n=document.getElementById(\"submittype\");n.parentNode.removeChild(n);' />";
      $html .= "\t<input type='submit' value='Confirm Purchase' />";
      $html .= "</fieldset>";
    }
    return $html;
    // also notify the user if a PURCHASE is involved, and how much it will cost
  }


}

function getOrDont($key, $array, $default) {
	if (array_key_exists($key, $array)) {
		return $array[$key];
	}
	return $default;
}

function getCategories($default, $custom_1 = '') {
  $custom_1_arr = explode(',',$custom_1);
  $html = "";
  $categories = Listtype::getListData('Article Category', '`custom_1` IN("'.implode('","',$custom_1_arr).'")', '`text`');
  foreach ($categories as $key => $value) {
    if ($key == $default) {
      $html .= "\t<option value='".$key."' selected='selected'>".$value."</option>\r\n";
    }
    else {
      $html .= "\t<option value='".$key."'>".$value."</option>\r\n";
    }
  }
  return $html;
}

class PostingPreview {
  static function draw($type, $datastructure = '') {
    switch ($type) {
      case 'coupon':
        $Obj_CustomCoupon =     new Custom_Coupon;
        $theImage =             $Obj_CustomCoupon->draw_image($datastructure, 'none', true);
        $theResizedImage =      imagecreatetruecolor(550, 215);
        imagecopyresampled($theResizedImage, $theImage, 0, 0, 0, 0, 550, 215, 639, 250);
        $imageFileName =        VideoUtilities::uuidSecure().".gif";
        ImageGif($theResizedImage,"./UserFiles/Image/coupon_previews/".$imageFileName);
        return "<img src='/UserFiles/Image/coupon_previews/".$imageFileName."' />";
      break;
    }
  }
}
?>