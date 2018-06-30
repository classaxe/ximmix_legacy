<?php
define('VERSION_COMMENT','1.0.18');
/*
Version History:
  1.0.18 (2014-02-17)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.comment.txt)
*/
class Comment extends Record{
  const fields = 'ID, archive, archiveID, deleted, systemID, approved, author_browser, author_email, author_hostname, author_ip, author_name, author_url, content, parentID, personID, sourceID, sourceType, type, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("comment",$ID);
    $this->_set_object_name("Comment");
  }

  public function do_commands() {
    switch ($_REQUEST['submode']) {
      case "delete":
        $this->_set_ID($_REQUEST['commentID']);
        $this->set_field('approved','pending'); // So we can update approved comments count before we kill it
        $this->update_comment_count();
        $this->delete();
        print "(Deleted)";
        die;
      break;
      case "edit":
        $this->_set_ID($_REQUEST['commentID']);
        $this->update_comment_count();
        print $this->get_edit_comment_form();
        die;
      break;
      case "get_count":
        $records =  $this->get_comments_for_item($_REQUEST['mode'],$_REQUEST['ID']);
        print ($records ? $this->get_count_text($records) : "");
        die;
      break;
      case "mark":
        $this->_set_ID($_REQUEST['commentID']);
        $old = $this->get_field('approved');
        $new = htmlentities(strip_tags($_POST['comment_approved']));
        $this->set_field('approved',$new);
        $this->update_comment_count();
        $msg = "";
        // Update akismet if needed:
        switch($old){
          case 'spam':
            if ($new=='approved') {
              $msg = $this->submitHam();
            }
          break;
          case 'approved':
          case 'hidden':
          case 'pending':
            if ($new=='spam') {
              $msg = $this->submitSpam();
            }
          break;
        }
        $this->_set_ID($_REQUEST['commentID']);
        $record = $this->get_record();
        print $this->get_comment_html($_REQUEST['mode'],$_REQUEST['ID'],$record,true,$msg);
        die;
      break;
      case "new":
        print
           "<div class='edit'>"
          .$this->get_new_comment_form($_REQUEST['mode'],$_REQUEST['ID'],false)
          ."</div>";
        die;
      break;
      case "post":
        $Obj = new Captcha();
        if (isset($_POST['captcha_key']) && $Obj->isKeyRight($_POST['captcha_key'])){
          $browser =                get_browser_safe();
          $data =
            array(
              'approved' =>         'pending',
              'author_browser' =>   $browser['browser']." ".$browser['version']." (".$browser['platform'].")",
              'author_email' =>     htmlentities(strip_tags($_POST['comment_email'])),
              'author_hostname' =>  gethostbyaddr($_SERVER['REMOTE_ADDR']),
              'author_ip' =>        $_SERVER['REMOTE_ADDR'],
              'author_name' =>      htmlentities(strip_tags($_POST['comment_name'])),
              'author_url' =>       htmlentities(strip_tags($_POST['comment_url'])),
              'content' =>          htmlentities(strip_tags($_POST['comment_text'])),
              'personID' =>         get_userID(),
              'sourceID' =>         $_REQUEST['ID'],
              'sourceType' =>       strToLower($_REQUEST['mode']),
              'systemID' =>         SYS_ID,
              'type' =>             'contributed'
            );
          $ID = $this->insert($data);
          $this->_set_ID($ID);
          switch ($this->isCommentSpam()){
            case false:
              $this->set_field('approved','pending');
            break;
            default:
              $this->set_field('approved','spam');
            break;
          }
          $this->update_comment_count();
          print $this->get_new_comment_link($_REQUEST['mode'],$_REQUEST['ID']);
          die;
        }
        print
           "<div class='edit'>"
          .$this->get_new_comment_form($_REQUEST['mode'],$_REQUEST['ID'],true)
          ."</div>";
        die;
      break;
      case "save":
        $this->_set_ID($_REQUEST['commentID']);
        $data =
           array(
              'approved' =>         htmlentities(strip_tags($_POST['comment_approved'])),
              'author_name' =>      htmlentities(strip_tags($_POST['comment_name'])),
              'author_email' =>     htmlentities(strip_tags($_POST['comment_email'])),
              'author_url' =>       htmlentities(strip_tags($_POST['comment_url'])),
              'content' =>          htmlentities(strip_tags($_POST['comment_text']))
          );
        $this->update($data);
        // Now get updated record:
        $this->update_comment_count();
        $record = $this->get_record();
        print $this->get_new_comment_link($_REQUEST['mode'],$_REQUEST['ID']);
        die;
      break;
      case "show":
        $this->_set_ID($_REQUEST['commentID']);
        $record = $this->get_record();
        print $this->get_comment_html($_REQUEST['mode'],$_REQUEST['ID'],$record,true);
        die;
      break;
      case "show_all":
        print $this->get_comments_all_html($_REQUEST['mode'],$_REQUEST['ID'],true);
        die;
      break;
    }
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  public function get_comments_all_html($sourceType,$sourceID,$allow_add='none') {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isApprover =      ($isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);

    $out = "";
    $commentTypeCSV =   "contributed";
    $Obj =              new Comment();
    $records =          $Obj->get_comments_for_item($sourceType,$sourceID);
    if ($records) {
      $out.=
         "<h1 id='comment_count'>"
        .$Obj->get_count_text($records)
        ."</h1>"
        ."<ol>\n";
//      y($records);die;
      foreach ($records as $record) {
        if ($isApprover || $record['approved']=='approved') {
          $out.=
             "<li id='comment_".$record['ID']."'>"
            .$Obj->get_comment_html($sourceType,$sourceID,$record)
            ."</li>";
        }
      }
      $out.= "</ol>";
    }
    return $out;
  }
  public function get_comments_for_item($sourceType,$sourceID,$commentApprovedCSV=false,$commentTypeCSV=false) {
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      .($commentApprovedCSV ? "  `approved` IN ('".implode("','",explode(',',$commentApprovedCSV))."') AND\n" : "")
      .($commentTypeCSV ? "  `type` IN ('".implode("','",explode(',',$commentTypeCSV))."') AND\n" : "")
      ."  `sourceType` = \"".$sourceType."\" AND\n"
      ."  `sourceID` = ".$sourceID."\n"
      ."ORDER BY\n"
      ."  `history_created_date` ASC"
      ;
    return $this->get_records_for_sql($sql);
  }
  public function get_count_text($records){
    $approved_count =   0;
    $unapproved_count = 0;
    foreach ($records as $record){
      $approved_count+=($record['approved']=='approved' ? 1 : 0);
      $unapproved_count+=($record['approved']=='pending' ? 1 : 0);
    }
    return
       ($approved_count==0 ? "No" : ($approved_count==1 ? "One" : ($approved_count==2 ? "Two" : $approved_count)))
      ." Response".($approved_count==1 ? "" : "s")
      .($unapproved_count>0 ? " (and ".$unapproved_count." awaiting moderation)" : "");
  }

  public function get_comment_html($sourceType,$sourceID,$record,$from_ajax=false,$msg=""){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isApprover =      ($isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
    $out = "";
    if ($isApprover || $record['approved']=='approved') {
      $out.=
         "<div"
        .($record['approved']=='pending' ? " title='(Awaiting moderation)'" : "")
        .($record['approved']=='spam' ? " title='(Marked as spam)'" : "")
        ." class='"
        .trim(
           ($record['approved']=='hidden' ? "hidden " : "")
          .($record['approved']=='pending' ? "pending " : "")
          .($record['approved']=='spam' ? "spam " : "")
          .($record['type']=='contributed' ? "contributed " : "")
          .($record['type']=='trackback' ? "trackback " : "")
          .($record['type']=='pingback' ? "pingback " : "")
       )
      ."'>"
      .($msg!='' ? "<p class='msg'>".$msg."</p>" : "");
      switch ($record['type']) {
        case "contributed":
          $out.=
             "<p class='date'><b>Comment from"
            ." ".$record['author_name']."</b>, "
            .format_datetime($record['history_created_date'])
            .($record['author_url'] ?
                "<br />\n"
               .($record['approved']=='approved' ?
                   " <a href=\"".$record['author_url']."\" "
                  .($from_ajax ? "target=\"_blank\"" : "rel=\"external\"")
                  ." title=\"Open link in a new window\"><b>Link</b></a>"
                :
                   " (Link -> ".$record['author_url'].")"
                )
              : ""
             )
            ."</p>"
            ."<p>".nl2br(strip_tags($record['content']))."</p>\n"
            ;
        break;
        case "pingback":
          $out.=
             "<p class='date'><b>Pingback</b> on "
            .format_datetime($record['history_created_date'])
            ."</p>"
            ."<p>"
            .($record['approved']=='approved' ?
                 "<a href=\"".$record['author_url']."\" "
                .($from_ajax ? "target=\"_blank\"" : "rel=\"external\"")
                ." title=\"Open link in a new window\"><b>"
                .$record['author_name']
                ."</b></a>"
              :
                 " (".$record['author_name']." -> ".$record['author_url'].")"
             )
            ."</p>"
            ;
        break;
        case "trackback":
          $out.=
             "<p class='date'><b>Trackback</b> on "
            .format_datetime($record['history_created_date'])
            ."</p>"
            ."<p>"
            .($record['approved']=='approved' ?
                 "<a href=\"".$record['author_url']."\" "
                .($from_ajax ? "target=\"_blank\"" : "rel=\"external\"")
                ." title=\"Open link in a new window\"><b>"
                .$record['author_name']
                ."</b></a>"
              :
                 " (".$record['author_name']." -> ".$record['author_url'].")"
             )
            ."</p>"
            ;
        break;
      }
      $out.=
        ($isApprover ?
            "<div class='actions'>"
           ."  <div class='fl' style='width:30%'>"
           ."    <a title='Click to Edit' href=\"#\" onclick=\"comment('".$sourceType."','".$sourceID."','edit','".$record['ID']."');return false;\">Edit</a> |"
           ."    <a title='Click to Delete' href=\"#\" onclick=\"if(confirm('Delete this comment?')){comment('".$sourceType."','".$sourceID."','delete','".$record['ID']."');};return false;\">Delete</a>"
           ."  </div>\n"
           ."  <div class='fl' style='width:70%'>"
           ."    <div class='fr'>\n"
           ."      Mark as: ["
           ."        ".($record['approved']=='pending' ? "<span class='pending'>Pending</span>" : "<a title=\"Mark as 'Awaiting Moderation'\" href=\"#\" onclick=\"comment('".$sourceType."','".$sourceID."','mark_pending','".$record['ID']."');return false;\">Pending</a>")." |\n"
           ."        ".($record['approved']=='spam' ? "<span class='spam'>Spam</span>" : "<a title=\"Mark and report as 'Spam'\" href=\"#\" onclick=\"comment('".$sourceType."','".$sourceID."','mark_spam','".$record['ID']."');return false;\">Spam</a>")." |\n"
           ."        ".($record['approved']=='approved' ? "<span class='approved'>Approved</span>" : "<a title=\"Mark as 'Approved'\" href=\"#\" onclick=\"comment('".$sourceType."','".$sourceID."','mark_approved','".$record['ID']."');return false;\">Approved</a> |\n")
           ."        ".($record['approved']=='hidden' ? "<span class='hidden'>Hidden</span>" : "<a title=\"Mark as 'Hidden'\" href=\"#\" onclick=\"comment('".$sourceType."','".$sourceID."','mark_hidden','".$record['ID']."');return false;\">Hidden</a>")
           ."      ]\n"
           ."    </div>\n"
           ."  </div>\n"
           ."  <div class='clr_b'></div>\n"
           ."</div>"
           ."<div class='clr_b'></div>\n"
         :  ""
        )
       ."</div>\n";
    }
    return $out;
  }
  public function get_edit_comment_form() {
    $record = $this->get_record();
    return
       "<div"
      .($record['approved']=='pending' ? " title='(Awaiting moderation)'" : "")
      .($record['approved']=='spam' ? " title='(Marked as spam)'" : "")
      ." class='"
      .trim(
         ($record['approved']=='pending' ? "pending " : "")
        .($record['approved']=='spam' ? "spam " : "")
        .($record['type']=='contributed' ? "contributed " : "")
        .($record['type']=='trackback' ? "trackback " : "")
        .($record['type']=='pingback' ? "pingback " : "")
       )
      ."'>"
      ."<div class='actions'>"
      ."<a title=\"Abandon changes (if any)\" href=\"#\" onclick=\"comment('".$_REQUEST['mode']."','".$_REQUEST['ID']."','show','".$record['ID']."');return false;\">Cancel</a> | "
      ."<a title=\"Save Changes\" href=\"#\" onclick=\"comment('".$_REQUEST['mode']."','".$_REQUEST['ID']."','save','".$record['ID']."');return false;\">Save</a>"
      ."</div>"
      ."<table>\n"
      ."  <tr>\n"
      ."    <th>Date Posted</th>\n"
      ."    <td>".$record['history_created_date']."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th>Comment Type</th>\n"
      ."    <td>".$record['type']."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th>IP Address</th>\n"
      ."    <td>".$record['author_ip']."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th>Host</th>\n"
      ."    <td>".$record['author_hostname']."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th>Browser</th>\n"
      ."    <td>".$record['author_browser']."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th id='th_comment_name'>Name</th>\n"
      ."    <td>".draw_form_field('comment_name',$record['author_name'],'text',150)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th id='th_comment_email'>Email</th>\n"
      ."    <td>".draw_form_field('comment_email',$record['author_email'],'text',150)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th id='th_comment_url'>Website</th>\n"
      ."    <td>".draw_form_field('comment_url',$record['author_url'],'text',150)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th colspan='2' id='th_comment_text' style='padding-top:1em;'>Comment</th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2'>&nbsp;<textarea name='comment_text' id='comment_text' style='width:95%;height:200px;' row='4' cols='80'>".$record['content']."</textarea></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th id='th_comment_url'>Comment Status</th>\n"
      ."    <td>".draw_form_field('comment_approved',$record['approved'],'radio_listdata','','','','','','','','lst_comment_approved','')."</td>\n"
      ."  </tr>\n"
      ."</table>\n"
      ."<div class='actions'>"
      ."<a title=\"Abandon changes (if any)\" href=\"#\" onclick=\"comment('".$_REQUEST['mode']."','".$_REQUEST['ID']."','show','".$record['ID']."');return false;\">Cancel</a> | "
      ."<a title=\"Save Changes\" href=\"#\" onclick=\"comment('".$_REQUEST['mode']."','".$_REQUEST['ID']."','save','".$record['ID']."');return false;\">Save</a>"
      ."</div>";
  }

  public function get_new_comment_form($mode,$ID,$bad_captcha=false) {
    if ($personID = get_userID()) {
      $Obj = new User($personID);
      $record = $Obj->get_record();
      if ($record['profile_locked']) {
        $personID = false;
      }
    }
    $comment_name =
      (isset($_POST['comment_name']) ?
         $_POST['comment_name']
      :  ($personID ? trim($record['NFirst']." ".$record['NLast']) : "")
     );
    $comment_email =
      (isset($_POST['comment_email']) ?
         $_POST['comment_email']
      :  ($personID ? $record['PEmail'] : "")
     );
    $comment_url =
      (isset($_POST['comment_url']) ?
         $_POST['comment_url']
      :  ($personID ? $record['AWeb'] : "")
     );
    $comment_text =
      (isset($_POST['comment_text']) ?
         $_POST['comment_text']
      :  ""
     );
    return
       "<ol style='list-style-type: none;'>\n"
      ."<li>\n"
      ."<div class='new'>\n"
      ."<h1 style='margin:0;'>Add new comment...</h1>\n"
      ."<div style='padding-bottom:1em;'><b>Please note:</b> all comments are held for moderation.</div>"
      ."<table summary='New Comment Form'>\n"
      ."  <tr>\n"
      ."    <th id='th_comment_name'>Name</th>\n"
      ."    <td>".draw_form_field('comment_name',$comment_name,'text',150)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th id='th_comment_email'>Email (won't be published)</th>\n"
      ."    <td>".draw_form_field('comment_email',$comment_email,'text',150)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th id='th_comment_url'>Website (optional)</th>\n"
      ."    <td>".draw_form_field('comment_url',$comment_url,'text',150)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th>Verification image -<br />\nLetters are lower-case<br />\n"
      ."<i><a href=\"#\" onclick=\"geid('captcha_image').src='".BASE_PATH."?command=captcha_img&rnd='+Math.random();return false;\">(<b>Unclear? Try another image)</b></a></i></th>\n"
      ."    <td><img id='captcha_image' class='formField' style='border:1px solid #7F9DB9;padding:2px;' src='".BASE_PATH."?command=captcha_img&rnd=".(rand(100000,999999))."' alt='' /></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th id='th_captcha_key'".($bad_captcha ? " style='color:#ff0000;'" : "").">Verification text<br /><span style='font-weight:normal'>(Type the characters you see)</span>&nbsp;</th>\n"
      ."    <td><input type='text' name='captcha_key' size='20' style='width: 180px;' value=\"\"/></td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <th colspan='2' id='th_comment_text' style='padding-top:1em;'>Your comment (no HTML allowed)</th>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2'>&nbsp;".draw_form_field('comment_text',$comment_text,'textarea',400,'','','','','','','',300)."</td>\n"
      ."  </tr>\n"
      ."  <tr>\n"
      ."    <td colspan='2' class='txt_c'>"
      ."<input type='button' id='comment_button_cancel' onclick=\"comment('".$mode."','".$ID."','cancel')\" value=\"Cancel\" />"
      ."<input type='button' id='comment_button_submit' onclick=\"comment('".$mode."','".$ID."','post')\" value=\"Submit\" />"
      ."</td>\n"
      ."  </tr>\n"
      ."</table>\n"
      ."</div></li></ol>"
      ;
  }

  function get_new_comment_link($mode,$ID) {
    return "<p><a href=\"#\" onclick=\"comment('".$mode."','".$ID."','new');return false;\">Add Comment</a></p>";
  }

  public function get_notification_summary($datetime,$systemID,$base_url){
    global $system_vars;
    $records = $this->get_records_since($datetime,$systemID);
    if (!count($records)){
      return;
    }
    $out =
       "<h2>New ".$this->_get_object_name().$this->plural('1,2')."</h2>"
      ."<table cellpadding='2' cellspacing='0' border='1'>\n"
      ."  <thead>\n"
      ."    <th>Relates to</th>\n"
      ."    <th>Name</th>\n"
      ."    <th>Email</th>\n"
      ."    <th>Content</th>\n"
      ."    <th class='datetime'>Created</th>\n"
      ."  </thead>\n"
      ."  <tbody>\n";
    foreach ($records as $record){
      $type =   $record['sourceType'];
      $Obj =    new $type($record['sourceID']);
      $Obj->load();
      if ($record['personID']){
        $Obj_User =   new User($record['personID']);
        $Obj_User->load();
        $PUsername =    $Obj_User->record['PUsername'];
        $User_URL =     $base_url.'details/user/'.$Obj_User->record['ID'];
      }
      else {
        $PUsername =    '';
        $User_URL =     '';
      }
      $NName =  $record['author_name'].($PUsername ? " (".$PUsername.")" : "");
      $URL =    $base_url.trim($Obj->record['path'],'/');
      $title =  $Obj->record['title'];
      $out.=
         "  <tr>\n"
        ."    <td><a target=\"_blank\" href=\"".$URL."\">".$title."</a></td>\n"
        ."    <td>".($User_URL ? "<a target=\"_blank\" href=\"".$User_URL."\">".$NName."</a>" : $NName)."</td>\n"
        ."    <td>".($record['author_email'] ? "<a href=\"mailto:".$record['author_email']."\">".$record['author_email']."</a>" : '')."</td>\n"
        ."    <td>".$record['content']."</td>\n"
        ."    <td class='datetime'>".$record['history_created_date']."</td>\n"
        ."  </tr>\n";
    }
    $out.=
       "  </tbody>\n"
      ."</table>\n";
    return $out;
  }

  function isCommentSpam() {
    global $system_vars;
    $record = $this->get_record();

    $Obj = new Akismet($system_vars['URL'],$system_vars['akismet_api_key']);
    $Obj->setUserIP($record['author_ip']);
    $Obj->setCommentType($record['type']);
    $Obj->setCommentAuthor($record['author_name']);
    $Obj->setCommentAuthorEmail($record['author_email']);
    $Obj->setCommentAuthorURL($record['author_url']);
    $Obj->setCommentContent($record['content']);

    $result = $Obj->isCommentSpam();
  }

  static function on_action_update_counts_and_akismet(){
    global $action_parameters;
    $ID_csv =           $action_parameters['triggerID'];
    $sourceTrigger =    $action_parameters['sourceTrigger'];
    $ID_arr =           explode(",",$ID_csv);
    $Obj =              new Comment;
    foreach ($ID_arr as $ID) {
      $Obj->_set_ID($ID);
      if ($sourceTrigger == 'report_delete_pre') {
        // set to 'pending' to allow removal from comments count
        $Obj->set_field('approved','pending');
      }
      else {
        if ($Obj->get_field('approved')=='spam') {
          $Obj->submitSpam();
        }
      }
      $Obj->update_comment_count();
    }
  }

  function submitHam() {
    global $system_vars;
    $record = $this->get_record();
    $Obj = new Akismet($system_vars['URL'],$system_vars['akismet_api_key']);
    $Obj->setUserIP($record['author_ip']);
    $Obj->setCommentType($record['type']);
    $Obj->setCommentAuthor($record['author_name']);
    $Obj->setCommentAuthorEmail($record['author_email']);
    $Obj->setCommentAuthorURL($record['author_url']);
    $Obj->setCommentContent($record['content']);

   $result = $Obj->submitHam();
   return ($result ? "<b>Message from Akismet:</b> ".$result : "");
  }

  function submitSpam() {
    global $system_vars;
    $record = $this->get_record();
    $Obj = new Akismet($system_vars['URL'],$system_vars['akismet_api_key']);
    $Obj->setUserIP($record['author_ip']);
    $Obj->setCommentType($record['type']);
    $Obj->setCommentAuthor($record['author_name']);
    $Obj->setCommentAuthorEmail($record['author_email']);
    $Obj->setCommentAuthorURL($record['author_url']);
    $Obj->setCommentContent($record['content']);

    $result = $Obj->submitSpam();
    return ($result ? "<b>Message from Akismet:</b> ".$result : "");
  }

  function update_comment_count(){
    $record =       $this->get_record();
    $sourceType =   $record['sourceType'];
    $sourceID =     $record['sourceID'];
    $approved =     $this->get_comments_for_item($sourceType,$sourceID,'approved',false);
    $hidden =       $this->get_comments_for_item($sourceType,$sourceID,'hidden',false);
    switch (strtolower($sourceType)) {
        case 'article':
          $Obj = new Article;
        break;
        case 'event':
          $Obj = new Event;
        break;
        case 'news item':
          $Obj = new News_Item;
        break;
        case 'job posting':
          $Obj = new Job_Posting;
        break;
        case 'page':
          $Obj = new Page;
        break;
        case 'podcast':
          $Obj = new Podcast;
        break;
        default:
          return false;
        break;
    }
    $Obj->_set_ID($sourceID);
    $old = $Obj->get_field('comments_count');
    $new = count($approved);
    $Obj->set_field('comments_count',$new,false);
    if (System::has_feature('Activity-Tracking')){
      $sourceType = get_class($Obj);
      $ObjActivity = new Activity;
      $ObjActivity->do_tracking('comments',$sourceType,$sourceID,($new - $old - count($hidden)));
    }
    return true;
  }

  public function get_version(){
    return VERSION_COMMENT;
  }
}
?>