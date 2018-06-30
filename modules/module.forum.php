<?php
define('MODULE_FORUM_VERSION','1.0.24');

/*
Version History:
  1.0.24 (2012-10-02)
    1) Tweak to call to Forum::do_submode() for case 'save_your_settings' -
       Call to Person::get_field() implied that this function takes two parameters,
       it only takes one.
  1.0.23 (2012-09-09)
    1) Changes to Forum::install() and Forum::uninstall() to avoid native DB access
  1.0.22 (2011-07-17)
    1) Changed five references from Component::function_name() to
       Forum::render()

  (Older version history in module.forum.txt)
*/

class Forum extends Posting {

  function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_type('forum');
    $this->_set_assign_type('forum posting');
    $this->_set_object_name('Forum Posting');
    $this->set_module_version(MODULE_FORUM_VERSION);
  }

  function do_submode($args,&$ID,&$category,&$msg){
    switch ($args['submode']) {
      case 'ajax_cancel':
        die;
      break;
      case 'ajax_edit_forum':
        print
          ($args['isAdmin'] ?
            "<div class='edit'>\n"
           ."<h2>Edit Forum '".$args['current_forum_title']."'</h2>\n"
           ."<table>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_title'>Title</label></td>\n"
           ."    <td>".draw_form_field('new_title',$args['current_forum_title'],'text',400)."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_name'>Category</label></td>\n"
           ."    <td>".draw_form_field('new_category',$args['current_forum_category'],'selector_listdata',400,'','','','','','','module.forum.category|1')."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_name'>Description (Optional)</label></td>\n"
           ."    <td>".draw_form_field('new_content',$args['current_forum_content'],'textarea',400,'','','','','','','',100)."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td colspan='2' class='txt_c'>"
           ."<input class='btn' type='button' value=\"Cancel\""
           ." onclick=\"forum_action('cancel','edit_".$args['current_forumID']."','".$args['instance']."');\" />"
           ."<input class='btn' type='button' value='Save'"
           ." onclick=\"geid_set('targetID','".$args['current_forumID']."');geid_set('submode','save_forum');geid('form').submit();\" />"
           ."</td>\n"
           ."  </tr>\n"
           ."</table>\n"
           ."</div>"
          : ""
         );
         die;
      break;
      case 'ajax_edit_topic':
        print
          ($args['isAdmin'] ?
            "<div class='edit'>\n"
           ."<h2>Edit Topic '".$args['current_topic_title']."'</h2>"
           ."<table>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_title'>Title</label></td>\n"
           ."    <td>".draw_form_field('new_title',$args['current_topic_title'],'text',400)."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td colspan='2' class='txt_c'>"
           ."<input class='btn' type='button' value=\"Cancel\""
           ." onclick=\"forum_action('cancel','edit_".$args['current_topicID']."','".$args['instance']."');\" />"
           ."<input class='btn' type='button' value='Save'"
           ." onclick=\"geid_set('targetID','".$args['current_topicID']."');geid_set('submode','save_topic');geid('form').submit();\" />"
           ."</td>\n"
           ."  </tr>\n"
           ."</table>"
           ."</div>"
          : ""
         );
         die;
      break;
      case 'ajax_new_forum':
        print
         ($args['isAdmin'] ?
            "<div class='add'>\n"
           ."<h2>Adding new Forum</h2>\n"
           ."<table>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_title'>Title</label></td>\n"
           ."    <td>".draw_form_field('new_title','','text',400)."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_name'>Category</label></td>\n"
           ."    <td>".draw_form_field('new_category',$args['category'],'selector_listdata',400,'','','','','','','module.forum.category|1')."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td class='va_t'><label for='forum_name'>Description<br />(Optional)</label></td>\n"
           ."    <td>".draw_form_field('new_content','','textarea',400,'','','','','','','',100)."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td colspan='2' class='txt_c'>"
           ."<input type='button' value=\"Cancel\""
           ." onclick=\"forum_action('cancel','forum_form_".$args['instance']."','".$args['instance']."');\" />"
           ."<input type='button' value='Create'"
           ." onclick=\"geid_set('submode','add_forum');geid('form').submit();\" />"
           ."</td>\n"
           ."  </tr>\n"
           ."</table>\n"
           ."</div>"
          : ""
        );
        die;
      break;
      case 'ajax_new_post':
        print
        ($args['isContributor'] && $args['current_topicID'] ?
          "<div id='div_add_post'  class='add'>\n"
         ."<h2>Adding new Post for Topic '".$args['current_topic_title']."'</h2>"
         ."<table>\n"
         ."  <tr>\n"
         ."    <td><label for='forum_title'>Title</label></td>\n"
         ."    <td>".draw_form_field('new_title','Re: '.$args['current_topic_title'],'text',400)."</td>\n"
         ."  </tr>\n"
         ."  <tr>\n"
         ."    <td><label for='forum_name'>Message</label></td>\n"
         ."    <td>"
         .draw_form_field('new_content',($args['signature'] ? $args['signature_block'] : ""),'textarea',400,'','','','','','','',200)
         ."</td>\n"
         ."  </tr>\n"
         ."  <tr>\n"
         ."    <td colspan='2' class='txt_c'>"
         ."<input type='button' value=\"Cancel\""
         ." onclick=\"forum_action('cancel','add_post_for_topic','".$args['instance']."');\" />"
         ."<input type='button' value='Post'"
         ." onclick=\"geid_set('submode','add_post');geid('form').submit();\" />"
         ."</td>\n"
         ."  </tr>\n"
         ."</table>"
         ."</div>"
        : ""
        );
        die;
      break;
      case 'ajax_new_topic':
        print
          ($args['isContributor'] ?
            "<div class='add'>\n"
           ."<h2>Adding new topic for Forum '".$args['current_forum_title']."'</h2>"
           ."<table>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_title'>Title</label></td>\n"
           ."    <td>".draw_form_field('new_title','','text',400)."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_name'>Initial Post</label></td>\n"
           ."    <td>".draw_form_field('new_content',($args['signature'] ? $args['signature_block'] : ""),'textarea',400,'','','','','','','',200)."</td>\n"
           ."  </tr>\n"
           ."  <tr>\n"
           ."    <td colspan='2' class='txt_c'>"
           ."<input type='button' value=\"Cancel\""
           ." onclick=\"forum_action('cancel','add_topic','".$args['instance']."');\" />"
           ."<input type='button' value='Post'"
           ." onclick=\"geid_set('submode','add_topic');geid('form').submit();\" />"
           ."</td>\n"
           ."  </tr>\n"
           ."</table>"
           ."</div>"
          : ""
         );
         die;
      break;
      case 'ajax_user_admin':
        if (!$args['isAdmin']) {
          break;
        }
        print convert_safe_to_php($this->manage_users());
        die;
      break;
      case 'ajax_your_settings':
        global $page;
        $row =
          array(
            'ID' => get_userID()
          );
        $Obj = new Report;
        $reportID = $Obj->get_ID_by_name('module.forum.users');
        if ($args['avatar']) {
          $avatar =
             BASE_PATH.$page."?submode=avatar"
            ."&amp;targetID=".get_userID();
        }
        print
         ($args['isContributor'] ?
            "<div class='edit'>\n"
           ."<h2>Manage Your Forum Settings</h2>\n"
           ."<table>\n"
           ."  <tr>\n"
           ."    <td style='width: 150px'><label for='forum_title'>Your Signature</label></td>\n"
           ."    <td>".draw_form_field('new_signature',$args['signature'],'textarea',400)."</td>\n"
           ."  </tr>\n"
           ."  <tr><td colspan='2'>&nbsp;</td></tr>\n"
           ."  <tr>\n"
           ."    <td><label for='forum_title'>Your Avatar<br />(Shown in posts)</label></td>\n"
           ."    <td>"
           .($args['avatar'] ? "<img style='float:left; margin:0 10px 0 0;' src=\"".$avatar."\" alt=\"Your current Avatar\" />" : "")
           .Report_Column::draw_form_field($row,'xml:forum:avatar',$args['avatar'],'file_upload_to_userfile_folder',300,'',$reportID)
           ."<br /><br />Uploaded image will be resized and should be of type<br />.jpg, .gif or .png<br />\n"
           ."</td>\n"
           ."  </tr>\n"
           ."  <tr><td colspan='2'>&nbsp;</td></tr>\n"
           ."  <tr>\n"
           ."    <td colspan='2' class='txt_c'>"
           ."<input type='button' value=\"Cancel\""
           ." onclick=\"forum_action('cancel','forum_form_".$args['instance']."','".$args['instance']."');\" />"
           ."<input type='button' value='Save'"
           ." onclick=\"geid_set('submode','save_your_settings');geid('form').submit();\" />"
           ."</td>\n"
           ."  </tr>\n"
           ."</table>\n"
           ."</div>"
          : ""
        );
        die;
      break;
      case 'add_forum':
        if (!$args['isAdmin']) {
          break;
        }
        if (!$args['new_category'] || !$args['new_title']) {
          $msg[] = "<b>Error: Cannot create new forum</b><ul>";
          if (!$args['new_category']) {
            $msg[] = "<li>You must choose a category</li>";
          }
          if (!$args['new_title']) {
            $msg[] = "<li>You must provide a title</li>";
          }
          $msg[] = "</ul>";
          break;
        }
        $data =
          array(
            'category' =>       $args['new_category'],
            'subtype' =>        'forum',
            'content' =>        addslashes(strip_tags($args['new_content'])),
            'permPUBLIC' =>     1,
            'permSYSLOGON' =>   1,
            'permSYSMEMBER' =>  1,
            'systemID' =>       SYS_ID,
            'title' =>          addslashes(strip_tags($args['new_title'])),
            'type' =>           $this->_get_type()
          );
        $this->add($data);
        if ($category != '' && $category != $args['new_category']) {
          $category = $args['new_category'];
        }
        $msg[] = "<b>Success:</b> Forum '".$args['new_category']."' was created";
      break;
      case 'add_post':
        if (!$args['isContributor']) {
          break;
        }
        if (!$args['new_content'] || strip_whitespace($args['new_content'])==strip_whitespace($args['signature_block'])) {
          $msg[] =
             "<b>Error: Cannot create new post</b>"
            ."<ul>"
            ."<li>You must provide some content</li>"
            ."</ul>";
          break;
        }
        if (!$args['new_title']) {
          $args['new_title'] = '(Untitled)';
        }
        $data =
          array(
            'content' =>        addslashes(strip_tags($args['new_content'])),
            'subtype' =>        'post',
            'parentID' =>       $args['current_topicID'],
            'permPUBLIC' =>     1,
            'permSYSLOGON' =>   1,
            'permSYSMEMBER' =>  1,
            'systemID' =>       SYS_ID,
            'title' =>          addslashes(strip_tags($args['new_title'])),
            'type' =>           $this->_get_type()
          );
        $this->add($data);
        $msg[] = "<b>Success:</b> Message was posted to Topic.";
      break;
      case 'add_topic':
        if (!$args['isContributor']) {
          break;
        }
        if (!$args['new_title'] || !$args['new_content'] || strip_whitespace($args['new_content'])==strip_whitespace($args['signature_block'])) {
          $msg[] = "<b>Error: Cannot create new topic</b><ul>";
          if (!$args['new_title']) {
            $msg[] = "<li>You must provide a title</li>";
          }
          if (!$args['new_content'] || strip_whitespace($args['new_content'])==strip_whitespace($args['signature_block'])) {
            $msg[] = "<li>You must provide some content for the initial post</li>";
          }
          $msg[] = "</ul>";
          break;
        }
        $data =
          array(
            'subtype' =>        'topic',
            'parentID' =>       $args['current_forumID'],
            'permPUBLIC' =>     1,
            'permSYSLOGON' =>   1,
            'permSYSMEMBER' =>  1,
            'systemID' =>       SYS_ID,
            'title' =>          addslashes(strip_tags($args['new_title'])),
            'type' =>           $this->_get_type()
          );
        $tmpID = $this->add($data);
        if ($args['new_content']){
          $data =
            array(
              'content' =>        addslashes(strip_tags($args['new_content'])),
              'subtype' =>        'post',
              'parentID' =>       $tmpID,
              'permPUBLIC' =>     1,
              'permSYSLOGON' =>   1,
              'permSYSMEMBER' =>  1,
              'systemID' =>       SYS_ID,
              'title' =>          addslashes(strip_tags($args['new_title'])),
              'type' =>           $this->_get_type()
            );
          $this->add($data);
        }
        // Now load up in the newly added topic:
        $ID = $tmpID;
      break;
      case "avatar":
        $this->get_avatar($args['targetID']);
      break;
      case "css":
        set_cache(3600*24*365); // expire in one year
        header("Content-type: text/css");
        readfile(SYS_MODULES."module.forum.css");
        die;
      break;
      case "delete_forum":
        if (!$args['isAdmin']) {
          break;
        }
        $this->_set_ID($args['targetID']);
        $this->delete();
        $ID = "";
        $category = "";
        $msg[] = "<b>Success:</b> Deleted Forum";
      break;
      case "delete_post":
        if (!$args['isAdmin']) {
          break;
        }
        $this->_set_ID($args['targetID']);
        $this->delete();
        $msg[] = "<b>Success:</b> Deleted Posting";
      break;
      case "delete_topic":
        if (!$args['isAdmin']) {
          break;
        }
        $this->_set_ID($args['targetID']);
        $this->delete();
        // Now go up one level
        $ID = $args['current_forumID'];
        $msg[] = "<b>Success:</b> Deleted Topic";
      break;
      case 'save_forum':
        if (!$args['isAdmin']) {
          break;
        }
        if (!$args['new_category'] || !$args['new_title']) {
          $msg[] =
             "<b>Error: Cannot save forum</b>"
            ."<ul>"
            .(!$args['new_category'] ? "<li>You must choose a category</li>" : "")
            .(!$args['new_title'] ?    "<li>You must provide a title</li>" : "")
            ."</ul>";
          break;
        }
        $this->_set_ID($args['targetID']);
        $data =
          array(
            'category' => addslashes(strip_tags($args['new_category'])),
            'content' => addslashes(strip_tags($args['new_content'])),
            'title' => addslashes(strip_tags($args['new_title']))
          );
        $this->update($data);
        $msg[] = "<b>Success:</b> Saved Changes to Forum '".strip_tags($args['new_title'])."'";
      break;
      case 'save_topic':
        if (!$args['isAdmin'] && !$args['isContributor']) {
          break;
        }
        if (!$args['new_title']) {
          $msg[] =
             "<b>Error: Cannot save topic</b>"
            ."<ul>"
            .(!$args['new_title'] ? "<li>You must provide a title</li>" : "")
            ."</ul>";
          break;
        }
        $this->_set_ID($args['targetID']);
        $data =
          array(
            'title' =>           addslashes(strip_tags($args['new_title']))
          );
        $this->update($data);
        $msg[] = "<b>Success:</b> Saved Changes to Topic '".strip_tags($args['new_title'])."'";
      break;
      case 'save_your_settings':
        if (!$args['isAdmin'] && !$args['isContributor']) {
          break;
        }
        $Obj = new Person(get_UserID());
        $data =
          array(
            'xml:forum/signature' => get_var('new_signature'),
          );
        if (isset($_REQUEST['xml:forum:avatar_mark_delete']) && $_REQUEST['xml:forum:avatar_mark_delete']=='1'){
          $old_data =
            $Obj->get_embedded_file_properties(
              $Obj->get_field('xml:forum:avatar')
            );
          $old_file = '.'.substr($old_data['data'],4,-1);
          if(file_exists($old_file)){ unlink($old_file); }
          $data['xml:forum/avatar'] = '';

        }
        else if (is_uploaded_file($_FILES['xml:forum:avatar']['tmp_name'])){
          $path =       'UserFiles/Image/forum_avatars/';
          mkdirs($path,0777);
          $new_name =   $path.$Obj->_get_ID().'_'.$_FILES['xml:forum:avatar']['name'];
          if(file_exists($new_name)){ unlink($new_name); }
          rename($_FILES['xml:forum:avatar']['tmp_name'],$new_name);
          $data['xml:forum/avatar'] =
             addslashes(
               "name:".$_FILES['xml:forum:avatar']['name'].","
              ."size:".$_FILES['xml:forum:avatar']['size'].","
              ."type:".$_FILES['xml:forum:avatar']['type'].","
              ."data:url(".BASE_PATH.$new_name.")"
            );
        }
        $Obj->update($data);
        $msg[] = "<b>Success:</b> Saved Changes to Your Profile'";
      break;
    }
  }

  function draw_path($_category,$forums,$ID,$topID,$topics) {
    global $page;
    $out =
       "      <ul class='breadcrumbs'>\n"
      ."        <li class='root'>\n"
      ."<img src='/img/spacer' class='category icon' alt='[Category icon]' />"
      ."<a href=\"".BASE_PATH.$page."\" title=\"View Forums in all Categories\">"
      ."All Categories</a></li>\n"
      ."        <li class='sub'>\n"
      ."<a href=\"".BASE_PATH.$page."?category=".$_category."\" title=\"View Forums in '".htmlentities($_category)."' Category\">"
      .htmlentities($_category)."</a></li>";
    foreach($forums as $forum){
      if ($topID==$forum['ID']){
        $out.=
           "        <li class='sub'><a href=\"".BASE_PATH.$page."?ID=".$forum['ID']."\""
          ." title=\"View Forum '".htmlentities($forum['title'])."' \">"
          .htmlentities($forum['title'])
          ."</a></li>";
        break;
      }
    }
    if ($topID && $forum['topicID_csv'] && $topics){
      foreach ($topics as $topic) {
        if ($ID == $topic['ID']) {
          $out.=
             "        <li class='sub'>"
            ."<a href=\"".BASE_PATH.$page."?ID=".$topic['ID']."\""
            ." title=\"View Topic '".$topic['title']."'\">"
            .htmlentities($topic['title'])
            ."</a></li>";
          break;
        }
      }
    }
    $out.= "  </ul>";
    return $out;
  }

  function draw_posts($forum,$instance,$isAdmin,$isContributor,$posts,$topic,$topID,$view_posts,$view_replies){
    global $page;
    if (!$view_posts){
      return
         "<table class='post'>\n"
        ."  <tr class='forbidden'>\n"
        ."    <td class='icon' style='width:7%'>"
        ."<img src='/img/spacer' class='forbidden icon' alt='[Forbidden]' />"
        ."</td>\n"
        ."    <td class='info'><b>(".$topic['posts']." post".($topic['posts']==1 ? '' : 's').")</b></td>\n"
        ."    <td class='body'><b>You do not have access to read ".($topic['posts']==1 ? 'this post' : 'these posts').".</b></td>\n"
        ."  </tr>"
        ."</table>";
    }
    $post_num = 0;
    $out = "<table class='post'>\n";
    foreach($posts as $post){
      $this->xmlfields_decode($post);
      $post_num++;
      if ($post['xml:forum:avatar']) {
        $avatar =
           BASE_PATH.$page."?submode=avatar"
          ."&amp;targetID=".$post['history_created_by'];
      }
      $out.=
         "  <tr class='header'>\n"
        ."    <td class='icon' style='width:7%'>"
        ."<img src='/img/spacer' "
        .($post_num==1 ? "class='post icon' alt='[Posting icon]'" : "class='reply icon' alt='[Reply icon]'")
        ." />"
        ."</td>\n"
        ."    <td class='info'>"
        .($post['created_by'] ? $post['created_by']."<br />" : "")
        .($post['xml:forum:avatar'] ? "<img src=\"".$avatar."\" alt=\"Avatar for ".$post['created_by']."\" /><br />" : "")
        .($post['xml:forum:role'] ? "<span style='color:red;font-weight:bold'>".$post['xml:forum:role']."</span><br />" : "")
        ."</td>"
        ."    <td class='body'>"
        ."<div class='fl'>"
        .($post['title'] ? "<b>".$post['title']."</b>" : "")
        ."</div>"
        .($isAdmin ?
           "<div class='fl'>&nbsp; <a href='#' title='Delete Post'"
          ." onclick=\"if(confirm('Delete Post?')){ geid_set('targetID',".$post['ID'].");geid_set('submode','delete_post');geid('form').submit();};return false;\">"
          ." <img src='/img/spacer' class='delete icon' alt='[Delete icon]' /></a></div>"
         : ""
         )
        ."<div class='posting_date'>".$this->get_YYYYMMDD_to_format($post['history_created_date'],'MM DD YYYY h:mmXM')."</div><br class='clr_b' />"
        .nl2br($post['content'])
        ."    </td>\n"
        ."  </tr>\n";
      if (!$view_replies && $topic['posts']>1){
        $out.=
           "  <tr class='forbidden'>\n"
          ."    <td class='icon' style='width:7%'>"
          ."<img src='/img/spacer' class='reply icon' alt='[Reply]' />"
          .""
          ."    </td>\n"
          ."    <td class='info'>"
          ."<img src='/img/spacer' class='forbidden icon' style='float:left' alt='[Forbidden]' />"
          ."<b>(".($topic['posts']-1)." repl".($topic['posts']==2 ? 'y' : 'ies').")</b></td>\n"
          ."    <td class='body'><b>You do not have access to read ".($topic['posts']==2 ? 'this reply' : 'these replies').".</b></td>\n"
          ."  </tr>";
        break;
      }
    }
    $out.=
      ($isContributor && $forum['ID']==$topID ?
         "  <tr class='reply'>\n"
        ."    <td colspan='3'>\n"
        ."<div class='txt_c'><input type='button'"
        ." value=\"".($post_num==0 ? "Post to Topic..." : "Reply to Topic...")."\""
        ." onclick=\"forum_action('ajax_new_post','add_post_for_topic','".$instance."')\" /></div>"
        ."<div id='add_post_for_topic'></div></td>"
        ."  </tr>\n"
     : ""
     );

    $out.= "</table>";
    return $out;
  }

  function get_avatar($personID) {
    $Obj = new Person($personID);
    $value = $Obj->get_field('xml:forum:avatar');
    if ($value!='') {
      $parts =      $this->get_embedded_file_properties($value);
      $filename =   ".".substr($parts['data'],strlen('url('),-1);
      $name =       $parts['name'];
      $type =       $parts['type'];
      $file_ext_arr =   explode(".",$filename);
      $ext =            array_pop($file_ext_arr);
      switch($ext){
        case "gif":
        case "jpg":
        case "jpeg":
        case "png":
          // carry on
        break;
        default:
          header("Status: 500 Invalid file");
          header("HTTP/1.0 500 Invalid File");
          die('Cannot open '.$file);
        break;
      }
      if (!file_exists($filename)) {
        $filename = SYS_IMAGES.$file;
        if (!file_exists($filename) && $alt!='') {
          $filename = "./$alt";
          if (!file_exists($filename)) {
            $filename = SYS_IMAGES.$alt;
            if (!file_exists($filename)) {
              header("Status: 404 Not Found");
              header("HTTP/1.0 404 Not Found");
              die('Cannot open '.$file);
            }
          }
        }
      }
      switch ($ext){
        // Check to see if image was misnamed
        case "gif":
        case "jpg":
        case "jpeg":
        case "png":
          $data = getimagesize($filename);
          $mime = $data['mime'];
          switch($mime){
            case 'image/gif':
              $ext = 'gif';
            break;
            case 'image/jpeg':
              $ext = 'jpg';
            break;
            case 'image/png':
              $ext = 'png';
            break;
          }
        break;
      }
      switch (strToLower($ext)){
        case "gif":
          $img =    imageCreateFromGif($filename);
        break;
        case "jpg":
          $img =    imageCreateFromJpeg($filename);
        break;
        case "png":
          $img =    imageCreateFromPNG($filename);
        break;
      }
      $box =        100;
      $width =      imagesx($img);
      $height =     imagesy($img);
      if ($height > $box || $width > $box) {
        $newHeight = ($width/$box > $height/$box ? $height/($width/$box) : $box);
        $newWidth = ($height/$box > $width/$box ? $width/($height/$box) : $box);
        $img2 = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($img2, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        $img = $img2; // Overwrite original img
      }
      header("Content-Disposition: attachment;filename=\"".$name."\"");
      header("Content-Type: \"".$type."\"");
      switch ($type){
        case "image/gif":
          ImageGIF($img);
        break;
        case "image/jpeg":
        case "image/pjpeg":
          ImageJPEG($img);
        break;
        case "image/png":
          ImagePNG($img);
        break;
        default:
          print "Unsupported image type ".$type;
        break;
      }
      die;
    }
  }

  function get_structure($category_csv) {
    $category_arr =     explode(",",str_replace(" ","",sanitize('html',$category_csv)));
    $categories =       implode("\",\"",$category_arr);
    $sql =
       "SELECT\n"
      ."  COALESCE(GROUP_CONCAT(DISTINCT `topic`.`ID`),'') `topicID_csv`,\n"
      ."  `forum`.`ID`,\n"
      ."  `forum`.`title`,\n"
      ."  `forum`.`category`,\n"
      ."  `forum`.`content`,\n"
      ."  MAX(`post`.`history_created_date`) `last_post_date`,\n"
      ."  COUNT(distinct `topic`.`ID`) `topics`,\n"
      ."  COUNT(distinct `post`.`ID`) `posts`\n"
      ."FROM\n"
      ."  `postings` `forum`\n"
      ."LEFT JOIN `person` `forum_creator` ON\n"
      ."  `forum`.`history_created_by` = `forum_creator`.`ID`\n"
      ."LEFT JOIN `postings` `topic` ON\n"
      ."  `forum`.`ID` = `topic`.`parentID`\n"
      ."LEFT JOIN `postings` `post` ON\n"
      ."  `topic`.`ID` = `post`.`parentID`\n"
      ."WHERE\n"
      ."  `forum`.`systemID`=".SYS_ID." AND\n"
      ."  `forum`.`type` = '".$this->_get_type()."' AND\n"
      .($category_csv ? "  `forum`.`category` IN (\"".$categories."\") AND\n" : "")
      ."  `forum`.`parentID` = 0\n"
      ."GROUP BY\n"
      ."  `forum`.`ID`\n"
      ."ORDER BY\n"
      ."  `forum`.`history_created_date`";
//      z($sql);
//    y($sql);
    return $this->get_records_for_sql($sql);
  }

  function get_topics_for_forum($ID) {
    $sql =
       "SELECT\n"
      ."  COUNT(`post`.`ID`) `posts`,\n"
      ."  COALESCE(GROUP_CONCAT(`post`.`ID`),'') `postID_csv`,\n"
      ."  `topic`.`ID`,\n"
      ."  `topic`.`title`,\n"
      ."  `topic`.`content`,\n"
      ."  MAX(`post`.`history_created_date`) `last_post_date`\n"
      ."FROM\n"
      ."  `postings` `topic`\n"
      ."LEFT JOIN `postings` `post` ON\n"
      ."  `post`.`parentID` = `topic`.`ID`\n"
      ."WHERE\n"
      ."  `topic`.`systemID`=".SYS_ID." AND\n"
      ."  `topic`.`type` = '".$this->_get_type()."' AND\n"
      ."  `topic`.`parentID` = ".$ID."\n"
      ."GROUP BY\n"
      ."  `topic`.`ID`\n"
      ."ORDER BY\n"
      ."  `topic`.`history_created_date`";
//    z($sql);
    return $this->get_records_for_sql($sql);
  }

  function get_posts_for_topic($ID) {
    $sql =
       "SELECT\n"
      ."  `postings`.`ID`,\n"
      ."  `postings`.`content`,\n"
      ."  `postings`.`title`,\n"
      ."  `postings`.`history_created_date`,\n"
      ."  `postings`.`history_created_by`,\n"
      ."  COALESCE(`person`.`PUsername`,'') `created_by`,\n"
      ."  `person`.`XML_data`\n"
      ."FROM\n"
      ."  `postings`\n"
      ."LEFT JOIN `person` ON\n"
      ."  `postings`.`history_created_by` = `person`.`ID`\n"
      ."WHERE\n"
      ."  `postings`.`systemID`=".SYS_ID." AND\n"
      ."  `postings`.`type` = '".$this->_get_type()."' AND\n"
      ."  `postings`.`parentID` = ".$ID."\n"
      ."GROUP BY\n"
      ."  `postings`.`ID`\n"
      ."ORDER BY\n"
      ."  `postings`.`history_created_date`";
    return $this->get_records_for_sql($sql);
  }
  function install() {
$sql = <<<INSTALL_SQL
  # Forum Postings
    INSERT INTO `report`                 (`ID`,`archive`,`archiveID`,`name`,`systemID`,`adminLink`,`adminLinkPosition`,`archiveChanges`,`description`,`formComponentID`,`formTitle`,`help`,`listTypeID`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`popupFormHeight`,`popupFormWidth`,`primaryObject`,`primaryTable`,`reportComponentID`,`reportGroupBy`,`reportMembersGlobalEditors`,`reportSortBy`,`reportSQL_COMMUNITYADMIN`,`reportSQL_GROUPADMIN`,`reportSQL_MASTERADMIN`,`reportSQL_SYSADMIN`,`reportTitle`,`required_feature`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (2032663965,0,0,'module.forum.postings',1,'[ICON]13 13 3354 Manage Forum Postings[/ICON]',43,0,'',1,'Edit Forum Posting','',1,0,0,1,1,1,1,0,1,1,1,0,0,560,800,'Forum','postings',1,'',0,1289284378,'','SELECT\r\n  (SELECT COUNT(`ga`.`ID`) FROM `postings` `p1` LEFT JOIN `group_assign` AS `ga` ON `ga`.`assign_type` = \'forum posting\' AND `ga`.`assignID` = `p1`.`ID` WHERE `p1`.`ID` = `postings`.`ID`) AS `groups`,\r\n  (SELECT `system`.`textEnglish` FROM `system` WHERE `system`.`ID` = `postings`.`systemID`) AS `systemTitle`,\r\n  COALESCE((SELECT  `parent_posting`.`title` FROM `postings` `parent_posting` WHERE `parent_posting`.`ID` = `postings`.`parentID`),\'\') AS `parent_title`,\r\n  `postings`.*\r\nFROM\r\n  `postings`\r\nLEFT JOIN `group_assign` AS `ga` ON\r\n  `ga`.`assign_type` = \'forum posting\' AND\r\n  `ga`.`assignID` = `postings`.`ID`\r\nLEFT JOIN `group_members` AS `gm` ON `gm`.`groupID` = `ga`.`groupID`\r\nWHERE\r\n  `postings`.`type` = \'forum\' AND\r\n  `postings`.`systemID` = SYS_ID AND\r\n  `gm`.`personID` = PERSON_ID','SELECT\r\n  (SELECT COUNT(`ga`.`ID`) FROM `postings` `p1` LEFT JOIN `group_assign` AS `ga` ON `ga`.`assign_type` = \'forum posting\' AND `ga`.`assignID` = `p1`.`ID` WHERE `p1`.`ID` = `postings`.`ID`) AS `groups`,\r\n  (SELECT `system`.`textEnglish` FROM `system` WHERE `system`.`ID` = `postings`.`systemID`) AS `systemTitle`,\r\n  COALESCE((SELECT  `parent_posting`.`title` FROM `postings` `parent_posting` WHERE `parent_posting`.`ID` = `postings`.`parentID`),\'\') AS `parent_title`,\r\n  `postings`.*\r\nFROM\r\n  `postings`\r\n#LEFT JOIN `group_members` AS `gm` ON `gm`.`groupID` = `ga`.`groupID`\r\nWHERE\r\n  `postings`.`type` = \'forum\'','SELECT\r\n  (SELECT COUNT(`ga`.`ID`) FROM `postings` `p1` LEFT JOIN `group_assign` AS `ga` ON `ga`.`assign_type` = \'forum posting\' AND `ga`.`assignID` = `p1`.`ID` WHERE `p1`.`ID` = `postings`.`ID`) AS `groups`,\r\n  (SELECT `system`.`textEnglish` FROM `system` WHERE `system`.`ID` = `postings`.`systemID`) AS `systemTitle`,\r\n  COALESCE((SELECT  `parent_posting`.`title` FROM `postings` `parent_posting` WHERE `parent_posting`.`ID` = `postings`.`parentID`),\'\') AS `parent_title`,\r\n  `postings`.*\r\nFROM\r\n  `postings`\r\n#LEFT JOIN `group_members` AS `gm` ON `gm`.`groupID` = `ga`.`groupID`\r\nWHERE\r\n  `postings`.`type` = \'forum\' AND\r\n  `postings`.`systemID` = SYS_ID','Manage Forum Postings','Forums',1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-12-01 12:35:43','209.104.160.30');
    INSERT INTO `action`                 (`ID`,`archive`,`archiveID`,`systemID`,`destinationOperation`,`destinationID`,`destinationValue`,`seq`,`sourceID`,`sourceTrigger`,`sourceType`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1547300075,0,0,1,'component_execute',1834578402,'',1,2032663965,'report_insert_post','report',1,'2008-10-28 09:26:48','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `action`                 (`ID`,`archive`,`archiveID`,`systemID`,`destinationOperation`,`destinationID`,`destinationValue`,`seq`,`sourceID`,`sourceTrigger`,`sourceType`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (196541711,0,0,1,'component_execute',1834578402,'',1,2032663965,'report_update_post','report',1,'2008-10-28 09:26:48','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (430796789,0,0,1,2032663965,'',0,'0.','','selected_export_sql','',0,'','',0,0,'','','',0,0,0,0,0,2,0,2,0,0,0,0,'','','','','',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (677021235,0,0,1,2032663965,'',0,'0.','','selected_delete','',0,'','',0,0,'','','',0,0,0,0,0,2,0,2,2,2,0,0,'','','','','',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (988346680,0,0,1,2032663965,'',0,'0.','','button_add_new','',0,'','',0,0,'','','',0,0,0,0,0,2,0,2,2,2,0,0,'','','','','',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1404087502,0,0,1,2032663965,'',0,'0.','forum','fixed','type',0,'','',0,0,'','','',0,2,2,2,2,2,2,2,2,2,2,2,'','','','','',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1562871203,0,0,1,2032663965,'',0,'0.','','hidden','ID',0,'','',0,0,'','','',0,2,2,2,2,2,2,2,2,2,2,2,'','','','','',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (2086316636,0,0,1,2032663965,'',0,'0.','','selected_update','',0,'','',0,0,'','','',0,0,2,2,2,2,0,2,2,2,0,0,'','','','','',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (454125991,0,0,1,2032663965,'',1,'0.','','checkbox','',0,'','',0,0,'','','',0,0,2,2,2,2,0,2,2,2,0,0,'ID','','','','',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (372349554,0,0,1,2032663965,'',2,'0.','','copy','',0,'','',0,0,'','','',0,0,0,0,0,2,0,2,2,0,0,0,'ID','','','','[LBL]copy||Copy Job Posting[/LBL]',0,'','','',0,1,'2008-10-28 09:26:48','127.0.0.1',0,'2011-01-03 14:05:02','74.122.130.252');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (2146817427,0,0,1,2032663965,'',3,'0.','SYS_ID','selector','systemID',0,'','Site to which Job Posting belongs',0,0,'Site','SELECT\r\n  `ID` AS `value`,\r\n  `textEnglish` AS `text`,\r\n  IF(`system`.`ID`=1,\'e0e0e0\',IF(`system`.`ID`=SYS_ID,\'c0ffc0\',\'ffe0e0\')) AS `color_background`\r\nFROM\r\n  `system`\r\nORDER BY\r\n  `system`.`ID`!=1,`text`','SELECT\r\n  `ID` AS `value`,\r\n  `textEnglish` AS `text`\r\nFROM\r\n  `system`\r\nWHERE\r\n  `system`.`ID` = SYS_ID',0,0,0,0,0,2,0,0,0,0,0,0,'systemTitle','','(SELECT `system`.`textEnglish` FROM `system` WHERE `system`.`ID` = `postings`.`systemID`)','Site','Site',1,'systemTitle ASC','systemTitle DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-10-30 14:39:36','209.104.160.30');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1608957550,0,0,1,2032663965,'',6,'0.','','edit','title',0,'','Short descriptive title',0,0,'Title','','',0,0,2,2,2,2,0,2,2,2,0,0,'title','','title','Title','Title',1,'`postings`.`title` ASC','`postings`.`title` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1783821678,0,0,1,2032663965,'',100,'1.General','','radio_csvlist','subtype',0,'forum|Forum|FFE0E0,post|Post|E0E0FF,topic|Topic|FFFFC0','Type of Posting',0,0,'Posting Type','','',0,0,2,2,2,2,0,2,2,2,0,0,'subtype','','`postings`.`subtype`','Posting Type','Posting Type',1,'`postings`.`subtype` ASC','`postings`.`subtype` DESC','',0,1,'2008-10-29 10:48:50','127.0.0.1',1,'2011-02-10 21:14:32','174.94.48.185');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (2056717849,0,0,1,2032663965,'',100,'1.General','','selector','parentID',0,'','Posting to which Forum Posting relates (unless this is a root forum)',0,0,'Parent','SELECT\r\n  \'\' `value`,\r\n  \'(None)\' `text`,\r\n  \'d0d0d0\' `color_background`\r\nUNION SELECT\r\n  `ID`,\r\n  `title`,\r\n  IF(`systemID`!=SYS_ID,\'ffe8e8\',\'e0ffe0\')\r\nFROM\r\n  `postings`\r\nWHERE\r\n  `ID` NOT IN(_ID_) AND\r\n  `type` = \'f\'','SELECT\r\n  \'\' `value`,\r\n  \'(None)\' `text`,\r\n  \'d0d0d0\' `color_background`\r\nUNION SELECT\r\n  `ID`,\r\n  `title`,\r\n  \'ffffff\'\r\nFROM\r\n  `postings`\r\nWHERE\r\n  `ID` NOT IN(_ID_) AND\r\n  `systemID` = SYS_ID AND\r\n  `type` = \'f\'',0,0,2,2,2,2,0,2,2,2,0,0,'parent_title','','COALESCE(`p`.`title`,\'\')','Parent','Parent',1,'`parent_title` = \'\',`parent_title` ASC','`parent_title` = \'\',`parent_title` DESC','',0,1,'2008-10-28 14:28:51','127.0.0.1',1,'2009-10-15 12:49:19','209.104.160.30');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1300653746,0,0,1,2032663965,'',103,'1.General','','categories_assign','category',0,'module.forum.category|1','Category to which Forum belongs',0,0,'Category','','',0,0,2,2,2,2,0,2,2,2,0,0,'category','','`postings`.`category`','Category','Category',1,'`postings`.`category` ASC, `postings`.`date`','`postings`.`category` DESC, `postings`.`date`','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (644051950,0,0,1,2032663965,'',105,'1.General','','html_with_text','content',300,'Job','Text for Jobs panel - for longer text use a \'more\' break',0,0,'Content','','',0,0,2,2,2,2,0,2,2,2,0,0,'content','','`postings`.`content`','Content','Content',1,'`postings`.`content` ASC','`postings`.`content` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1133495250,0,0,1,2032663965,'',200,'2.Permissions','0','bool','permPUBLIC',0,'','Job Posting shown for people who haven\'t signed in',0,0,'Public','','',0,0,1,1,1,2,0,2,2,1,0,0,'permPUBLIC','Job Posting shown for people who haven\'t signed in','`postings`.`permPUBLIC`','Public','[LBL]GREEN-public|87|Job Posting Viewable to Public[/LBL]',0,'`postings`.`permPUBLIC` ASC','`postings`.`permPUBLIC` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',0,'2011-01-03 14:05:02','74.122.130.252');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1746791074,0,0,1,2032663965,'',201,'2.Permissions','','bool','permSYSLOGON',0,'','Visible to unapproved members<br />i.e. those who can log on but who are NOT members.',0,0,'Unapproved Member','','',0,0,1,1,1,2,0,2,2,1,0,0,'permSYSLOGON','','`postings`.`permSYSLOGON`','Unapproved Member','[LBL]GREEN-sys-logon|87| Unapproved Member Access(Persons who can sign inbut who are NOT system members)[/LBL]',0,'`postings`.`permSYSLOGON` DESC','`postings`.`permSYSLOGON` ASC','',0,1,'2008-10-28 09:26:48','127.0.0.1',0,'2011-01-03 14:05:02','74.122.130.252');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1537062886,0,0,1,2032663965,'',202,'2.Permissions','0','bool','permSYSMEMBER',0,'','Job Posting shows for people who have signed in and are members',0,0,'Members','','',0,0,1,1,1,2,0,2,2,1,0,0,'permSYSMEMBER','','`postings`.`permSYSMEMBER`','Members','[LBL]GREEN-sys-member|87|Job Posting viewable by System Members[/LBL]',0,'`postings`.`permSYSMEMBER` ASC','`postings`.`permSYSMEMBER` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',0,'2011-01-03 14:05:02','74.122.130.252');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (326852204,0,0,1,2032663965,'',203,'2.Permissions','0','bool','permSHARED',0,'','News Item available to be displayed on other sites that subscribe to this one as a news-source',0,0,'Shared (Inter-site)','','',0,0,1,1,1,2,0,2,2,1,0,0,'permSHARED','','`postings`.`permSHARED`','Shared (Inter-site)','[LBL]GREEN-shared|87|Job Posting viewable via other subscribing sites[/LBL]',0,'`postings`.`permSHARED` ASC','`postings`.`permSHARED` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',0,'2011-01-03 14:05:02','74.122.130.252');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1452843700,0,0,1,2032663965,'',204,'2.Permissions','','groups_assign','group_assign_csv',0,'','Job Posting visible to persons in these groups having \'Viewer\' rights within these groups',0,0,'Available to members of these Groups:','','',0,0,1,1,1,2,0,2,2,1,0,0,'groups','','(SELECT COUNT(`ga`.`ID`) FROM `postings` `p1` LEFT JOIN `group_assign` AS `ga` ON `ga`.`assign_type` = \'forum posting\' AND `ga`.`assignID` = `p1`.`ID` WHERE `p1`.`ID` = `postings`.`ID`)','Groups','[LBL]GREEN-num-groups|87|Available to this many groups[/LBL]',0,'`groups` ASC','`groups` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',0,'2011-01-03 14:05:02','74.122.130.252');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1289284378,0,0,1,2032663965,'',901,'9.','','date','',0,'','',0,0,'','','',0,0,1,1,1,1,0,1,1,1,0,0,'history_created_date','','`postings`.`history_created_date`','Created Date','Created',0,'`postings`.`history_created_date`=\'0000-00-00\',\r\n`postings`.`history_created_date` ASC','`postings`.`history_created_date`=\'0000-00-00\',\r\n`postings`.`history_created_date` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1000450074,0,0,1,2032663965,'',902,'9.','','date','',0,'','',0,0,'','','',0,0,1,1,1,1,0,1,1,1,0,0,'history_modified_date','','`postings`.`history_modified_date`','Modified Date','Modified',0,'`postings`.`history_modified_date`=\'0000-00-00\',\r\n`postings`.`history_modified_date` ASC','`postings`.`history_modified_date`=\'0000-00-00\',\r\n`postings`.`history_modified_date` DESC','',0,1,'2008-10-28 09:26:48','127.0.0.1',1,'2009-04-23 21:38:10','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (615738324,0,0,1,0,'global','(All)',2032663965,1,1,'2008-10-28 11:32:02','127.0.0.1',1,'2008-10-28 11:32:08','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (686213740,0,0,1,0,'global','+Today',2032663965,2,1,'2008-10-28 11:32:31','127.0.0.1',1,'2008-10-28 11:32:38','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1590026223,0,0,1,0,'global','~Today',2032663965,3,1,'2008-10-28 11:32:48','127.0.0.1',1,'2008-10-28 11:33:02','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1621013561,0,0,1,0,'global','Topics',2032663965,5,1,'2008-10-30 16:10:54','127.0.0.1',1,'2008-10-30 16:11:18','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1628595560,0,0,1,0,'global','Forums',2032663965,4,1,'2008-10-30 16:10:42','127.0.0.1',1,'2008-10-30 16:11:12','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (2060879905,0,0,1,0,'global','Posts',2032663965,6,1,'2008-10-30 16:11:06','127.0.0.1',1,'2008-10-30 16:11:23','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (706951432,0,0,1,686213740,'1289284378','11',1,'TODAY',1,'2008-10-28 11:32:31','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1209672248,0,0,1,615738324,'','',1,'',1,'2008-10-28 11:32:02','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1487404920,0,0,1,1628595560,'1783821678','1',1,'forum',1,'2008-10-30 16:10:42','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1768363195,0,0,1,1590026223,'1000450074','11',1,'TODAY',1,'2008-10-28 11:32:48','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1794852771,0,0,1,1621013561,'1783821678','1',1,'topic',1,'2008-10-30 16:10:54','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1852981721,0,0,1,2060879905,'1783821678','1',1,'post',1,'2008-10-30 16:11:06','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_settings`        (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`reportID`,`report_columns_csv`,`report_filters_csv`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1380185576,0,0,1,0,'global',2032663965,'','615738324,686213740,1590026223,1628595560,1621013561,2060879905',1,'2010-09-24 16:30:00','127.0.0.1',0,'0000-00-00 00:00:00','');

  # Listdata for use in Forum Report - module.forum.category
    INSERT INTO `listtype`               (`ID`,`archive`,`archiveID`,`systemID`,`name`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1185122145,0,0,1,"module.forum.category",1,"2008-10-28 11:26:53","127.0.0.1",1,"2008-11-06 12:13:03","127.0.0.1");
    INSERT INTO `listdata`               (`ID`,`archive`,`archiveID`,`systemID`,`color_background`,`color_text`,`custom_1`,`custom_2`,`custom_3`,`custom_4`,`custom_5`,`isHeader`,`listTypeID`,`parentID`,`seq`,`textEnglish`,`textFrench`,`value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1737683813,0,0,1,"FFFFFF","000000","","","","","",0,1185122145,0,1,"(None)","","",1,"2008-10-28 11:27:13","127.0.0.1",0,"0000-00-00 00:00:00","127.0.0.1");

  # ECL Tag for forum
    INSERT INTO `ecl_tags`               (`ID`,`archive`,`archiveID`,`systemID`,`description`,`for_email`,`for_layout`,`for_page`,`nameable`,`php`,`tag`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (634411157,0,0,1,'COMPONENT: Forum',0,0,0,1,'return Base::use_module(\'forum\')->render(\$instance_name);','component_forum',0,1,'2008-10-24 10:55:20','127.0.0.1',1,'2009-01-08 07:46:09','74.14.104.170');

  # Person Report Forum Columns / fields
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (193315330,0,0,1,6,'',912,'9.Forums','','bool','xml:forum/permADMIN',0,'','Person can administer Forums and Forum Users',0,0,'Forum Administrator','','',0,0,0,0,0,2,1,2,2,1,1,1,'','','xml:forum/permADMIN','Forum Administrator','',1,'','','Forums',0,1,'2008-12-08 16:01:00','127.0.0.1',1,'2011-02-10 23:15:03','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (292832858,0,0,1,6,'',914,'9.Forums','','textarea','xml:forum/signature',100,'','Signature displayed with postings by this user',0,0,'User Signature','','',0,0,0,0,0,2,1,2,2,2,2,2,'','','xml:forum/signature','Forum User Signature','',1,'','','Forums',0,1,'2008-12-08 15:43:12','127.0.0.1',1,'2011-02-10 23:16:30','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (685163725,0,0,1,6,'',911,'9.Forums','','bool','xml:forum/permCONTRIBUTOR',0,'','Person can contribute postings to forums',0,0,'Forum Contributor','','',0,0,0,0,0,2,1,2,2,1,1,1,'','','xml:forum/permCONTRIBUTOR','Forum Contributor','',1,'','','Forums',0,1,'2008-12-08 16:17:28','127.0.0.1',1,'2011-02-10 23:14:35','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (773472291,0,0,1,6,'',913,'9.Forums','','text','xml:forum/role',0,'','Role displayed with person\'s username in postings',0,0,'User Role','','',0,0,0,0,0,2,1,2,2,1,1,1,'','','xml:forum/role','Forum User Role','',1,'','','Forums',0,1,'2008-12-08 15:27:26','127.0.0.1',1,'2011-02-10 23:15:39','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1108694310,0,0,1,6,'',915,'9.Forums','','file_upload_to_userfile_folder','xml:forum/avatar',0,'Image/forum_avatars/','Upload a .gif, .jpg or.png image - will be resized to 100x100 pixels',0,0,'User Avatar','','',0,0,0,0,0,2,1,2,2,2,2,2,'','','xml:forum/avatar','Forum User Avatar','',1,'','','Forums',0,1,'2008-12-08 15:15:20','127.0.0.1',1,'2011-02-10 23:17:17','127.0.0.1');

  # Administer Forum Users report - used in Forum
    INSERT INTO `report`                 (`ID`,`archive`,`archiveID`,`name`,`systemID`,`adminLink`,`adminLinkPosition`,`archiveChanges`,`description`,`formComponentID`,`formTitle`,`help`,`listTypeID`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`popupFormHeight`,`popupFormWidth`,`primaryObject`,`primaryTable`,`reportComponentID`,`reportGroupBy`,`reportMembersGlobalEditors`,`reportSortBy`,`reportSQL_COMMUNITYADMIN`,`reportSQL_GROUPADMIN`,`reportSQL_MASTERADMIN`,`reportSQL_SYSADMIN`,`reportTitle`,`required_feature`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (462298250,0,0,'module.forum.users',1,'',14,0,'Used to administer Forum Users',1,'Forum User','',1,0,0,0,0,0,1,0,1,1,1,1,1,400,600,'Person','person',1018245224,'',0,117288788,'','','SELECT\r\n  (SELECT `system`.`textEnglish` FROM `system` WHERE `person`.`systemID`=`system`.`ID`) AS `systemTitle`,\r\n  `person`.`ID`,\r\n  `person`.`PUsername`,\r\n  `person`.`NFirst`,\r\n  `person`.`NMiddle`,\r\n  `person`.`NLast`,\r\n  `person`.`XML_data`\r\nFROM\r\n  `person`\r\nWHERE\r\n  1','SELECT\r\n  (SELECT `system`.`textEnglish` FROM `system` WHERE `person`.`systemID`=`system`.`ID`) AS `systemTitle`,\r\n  `person`.`ID`,\r\n  `person`.`PUsername`,\r\n  `person`.`NFirst`,\r\n  `person`.`NMiddle`,\r\n  `person`.`NLast`,\r\n  `person`.`XML_data`\r\nFROM\r\n  `person`\r\nWHERE\r\n  `person`.`systemID` = SYS_ID\r\n','Forum Users','Forums',1,'2008-12-11 09:14:04','127.0.0.1',1,'2011-02-11 10:26:39','208.124.251.194');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1179393106,0,0,1,462298250,'',0,'','','selected_update','',0,'','',0,0,'','','',0,2,2,2,2,2,0,2,2,2,2,2,'','','','','',0,'','','',0,1,'2008-12-11 09:14:04','127.0.0.1',1,'2008-12-11 16:24:49','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1503273418,0,0,1,462298250,'',0,'','','hidden','ID',0,'','',0,0,'','','',0,2,2,2,2,2,0,2,2,2,2,2,'','','','','',0,'','','',0,1,'2008-12-11 09:14:04','127.0.0.1',1,'2008-12-11 16:24:49','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (77486067,0,0,1,462298250,'',1,'','','checkbox','',0,'','',0,0,'','','',0,2,2,2,2,2,0,2,2,2,2,2,'ID','','','','',0,'','','',0,1,'2008-12-11 09:14:04','127.0.0.1',1,'2008-12-11 16:24:49','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1219365451,0,0,1,462298250,'',2,'','','link_programmable_form','',0,'','',0,0,'','','',0,1,1,1,1,2,1,2,2,2,2,2,'ID','module.forum.users|400|600|Edit Forum User|Edit...','','','Edit',0,'','','',0,1,'2008-12-11 16:31:34','127.0.0.1',1,'2008-12-11 16:35:05','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (171398836,0,0,1,462298250,'',4,'','SYS_ID','selector','systemID',0,'','Site to which this user belongs',0,0,'Site','SELECT\r\n  `ID` AS `value`,\r\n  `textEnglish` AS `text`\r\nFROM\r\n  `system`\r\nORDER BY\r\n  `text`','SELECT\r\n  `ID` AS `value`,\r\n  `textEnglish` AS `text`\r\nFROM\r\n  `system`\r\nORDER BY\r\n  `text`',0,0,0,0,0,1,0,0,0,0,0,0,'systemTitle','','(SELECT `system`.`textEnglish` FROM `system` WHERE `person`.`systemID`=`system`.`ID`)','Site','Site',1,'`systemTitle` ASC','`systemTitle` DESC','',0,1,'2008-12-11 09:14:04','127.0.0.1',1,'2011-02-10 23:31:11','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (117288788,0,0,1,462298250,'',5,'','','text','PUsername',0,'','Name used to identify Person',0,0,'User Name','','',0,1,1,1,1,1,0,1,1,1,1,1,'PUsername','','`person`.`PUsername`','UserName','User<br />Name',1,'`person`.`PUsername` ASC','`person`.`PUsername` DESC','',0,1,'2008-12-11 09:14:04','127.0.0.1',1,'2008-12-11 16:34:58','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (303543075,0,0,1,462298250,'',6,'','','text','NFirst',0,'','First Name of user',0,0,'First Name','','',0,1,1,1,1,1,1,1,1,1,1,1,'NFirst','','`person`.`NFirst`','First Name','First<br />Name',1,'`person`.`NFirst` ASC','`person`.`NFirst` DESC','',0,1,'2008-12-11 09:14:04','127.0.0.1',1,'2008-12-11 16:34:55','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (513179806,0,0,1,462298250,'',7,'','','text','NMiddle',0,'','Middle Name of user',0,0,'Middle Name','','',0,1,1,1,1,1,1,1,1,1,1,1,'NMiddle','','`person`.`NMiddle`','Middle Name','Middle<br />Name',1,'`person`.`NMiddle` ASC','`person`.`NMiddle` DESC','',0,1,'2008-12-11 10:02:49','127.0.0.1',1,'2008-12-11 16:34:48','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (474678096,0,0,1,462298250,'',8,'','','text','NLast',0,'','Last Name of user',0,0,'Last Name','','',0,1,1,1,1,1,1,1,1,1,1,1,'NLast','','`person`.`NLast`','Last Name','Last<br />Name',1,'`person`.`NLast` ASC','`person`.`NLast` DESC','',0,1,'2008-12-11 10:18:45','127.0.0.1',1,'2008-12-11 16:34:44','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (861569023,0,0,1,462298250,'',11,'','','bool','xml:forum/permCONTRIBUTOR',0,'','Person can contribute postings to forums',0,0,'Forum Contributor','','',0,2,2,2,2,2,1,2,2,2,2,2,'xml:forum/permCONTRIBUTOR','','xml:forum/permCONTRIBUTOR','Forum Contributor','Forum<br />Contrib',0,'xml:forum/permCONTRIBUTOR ASC','xml:forum/permCONTRIBUTOR DESC','',0,1,'2008-12-11 10:45:40','127.0.0.1',1,'2011-02-10 22:09:15','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1115644185,0,0,1,462298250,'',12,'','','bool','xml:forum/permADMIN',0,'','Person can administer Forums and Forum Users',0,0,'Forum Admin','','',0,2,2,2,2,2,1,2,2,2,2,2,'xml:forum/permADMIN','','xml:forum/forum_permADMIN','Forum Admin','Forum<br />Admin',0,'xml:forum/forum_permADMIN ASC','xml:forum/forum_permADMIN DESC','',0,1,'2008-12-11 10:39:45','127.0.0.1',1,'2011-02-10 22:11:47','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (478469678,0,0,1,462298250,'',13,'','','text','xml:forum/role',0,'','Role is displayed with person\'s details in Forum Postings',0,0,'User Role','','',0,1,1,1,1,2,1,2,2,2,2,2,'xml:forum/role','','xml:forum/role','User Role','User Role',1,'xml:forum/role=\'\',\r\nxml:forum/role ASC','xml:forum/role=\'\',\r\nxml:forum/role DESC','',0,1,'2008-12-11 10:19:41','127.0.0.1',1,'2011-02-10 22:13:54','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1447018324,0,0,1,462298250,'',14,'','','textarea','xml:forum/signature',0,'','Default signature appended to new postings by user',0,0,'User Signature','','',0,1,1,1,1,2,1,2,2,2,2,2,'xml:forum/signature','','xml:forum/signature','User Signature','User Signature',1,'xml:forum/signature=\'\',\r\nxml:forum/signature ASC','xml:forum/signature=\'\',\r\nxml:forum/signature DESC','',0,1,'2008-12-11 10:48:10','127.0.0.1',1,'2011-02-10 22:15:58','127.0.0.1');
    INSERT INTO `report_columns`         (`ID`,`archive`,`archiveID`,`systemID`,`reportID`,`group_assign_csv`,`seq`,`tab`,`defaultValue`,`fieldType`,`formField`,`formFieldHeight`,`formFieldSpecial`,`formFieldTooltip`,`formFieldUnique`,`formFieldWidth`,`formLabel`,`formSelectorSQLMaster`,`formSelectorSQLMember`,`permCOMMUNITYADMIN`,`permGROUPVIEWER`,`permGROUPEDITOR`,`permGROUPAPPROVER`,`permGROUPADMIN`,`permMASTERADMIN`,`permPUBLIC`,`permSYSADMIN`,`permSYSAPPROVER`,`permSYSEDITOR`,`permSYSLOGON`,`permSYSMEMBER`,`reportField`,`reportFieldSpecial`,`reportFilter`,`reportFilterLabel`,`reportLabel`,`reportSortBy_AZ`,`reportSortBy_a`,`reportSortBy_d`,`required_feature`,`required_feature_invert`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1387299962,0,0,1,462298250,'',15,'','','file_upload_to_userfile_folder','xml:forum/avatar',0,'Image/forum_avatars/','Upload a .gif, .jpg or.png image - will be resized to 100x100 pixels',0,0,'User Avatar','','',0,1,1,1,1,2,1,2,2,2,2,2,'xml:forum/avatar','','xml:forum/avatar','Avatar','Avatar',1,'xml:forum/avatar=\'\',\r\nxml:forum/avatar ASC','xml:forum/avatar=\'\',\r\nxml:forum/avatar DESC','',0,1,'2008-12-11 10:03:51','127.0.0.1',1,'2011-02-10 22:23:55','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (808838829,0,0,1,0,'global','Admins',462298250,2,1,'2008-12-11 14:49:47','127.0.0.1',1,'2008-12-11 14:49:53','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (938222761,0,0,1,0,'global','(All)',462298250,1,1,'2008-12-11 14:49:06','127.0.0.1',1,'2008-12-11 14:49:15','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1142666749,0,0,1,0,'global','Avatars',462298250,4,1,'2008-12-11 14:51:15','127.0.0.1',1,'2008-12-11 14:51:25','127.0.0.1');
    INSERT INTO `report_filter`          (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`label`,`reportID`,`seq`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1645299720,0,0,1,0,'global','Contributors',462298250,3,1,'2008-12-11 14:49:35','127.0.0.1',1,'2008-12-11 14:49:59','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (431387239,0,0,1,808838829,'1115644185','1',1,'1',1,'2008-12-11 14:49:47','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1188005685,0,0,1,938222761,'','',1,'',1,'2008-12-11 14:49:06','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1211269948,0,0,1,1142666749,'1387299962','2',1,'',1,'2008-12-11 14:51:15','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_filter_criteria` (`ID`,`archive`,`archiveID`,`systemID`,`filterID`,`filter_criterion`,`filter_matchmodeID`,`filter_seq`,`filter_value`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1618131858,0,0,1,1645299720,'861569023','1',1,'1',1,'2008-12-11 14:49:35','127.0.0.1',0,'0000-00-00 00:00:00','127.0.0.1');
    INSERT INTO `report_settings`        (`ID`,`archive`,`archiveID`,`systemID`,`destinationID`,`destinationType`,`reportID`,`report_columns_csv`,`report_filters_csv`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1159129869,0,0,1,0,'global',462298250,'','938222761,808838829,1645299720,1142666749',1,'2010-09-24 16:30:00','127.0.0.1',0,'0000-00-00 00:00:00','');
    INSERT INTO `component`              (`ID`,`archive`,`archiveID`,`systemID`,`name`,`for_action`,`for_page`,`for_schedule`,`php`,`history_created_by`,`history_created_date`,`history_created_IP`,`history_modified_by`,`history_modified_date`,`history_modified_IP`) VALUES (1018245224,0,0,1,'module.forum: Report: Manage Users',0,0,0,'return Base::use_module(\'forum\')->manage_users();',1,'2008-12-11 10:56:03','',1,'2011-02-10 21:51:34','127.0.0.1');

INSTALL_SQL;
    $this->uninstall();

    $commands = Backup::db_split_sql($sql);
    foreach ($commands as $command) {
      $this->do_sql_query($command);
    }
    return 'Loaded data';
  }

  function manage_users() {
    $isAdmin = false;
    $personID = get_userID();
    if ($personID) {
      $isMASTERADMIN =	get_person_permission("MASTERADMIN");
      $isSYSADMIN =	    get_person_permission("SYSADMIN");
      $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
      $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
      $Obj = new Person($personID);
      $sql =
         "SELECT\n"
        ."  `XML_data`\n"
        ."FROM\n"
        ."  `person`\n"
        ."WHERE\n"
        ."  `ID` = ".$personID;
      $row = $Obj->get_record_for_sql($sql);
      $Obj->xmlfields_decode($row);
      $isFORUMADMIN =   (isset($row['xml:forum:permADMIN']) ? $row['xml:forum:permADMIN'] : false);
      $isAdmin =        ($isFORUMADMIN||$isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
    }
    if ($isAdmin) {
      return draw_auto_report('module.forum.users');
    }
    return 'You do not have permission to access this report.';
  }

  function render($instance) {
    global $page, $component_help;
    $ident =            'module_forum';
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =	    get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
    $avatar =	            "";
    $isFORUMADMIN =	        false;
    $isFORUMCONTRIBUTOR =	false;
    $signature =	        "";
    $personID = get_userID();
    if ($personID) {
      $Obj = new Person($personID);
      $sql =
         "SELECT\n"
        ."  `XML_data`\n"
        ."FROM\n"
        ."  `person`\n"
        ."WHERE\n"
        ."  `ID` = ".$personID;
      $row = $Obj->get_record_for_sql($sql);
      $Obj->xmlfields_decode($row);
      $avatar =	            (isset($row['xml:forum:avatar']) ?          $row['xml:forum:avatar'] : false);
      $isFORUMADMIN =	    (isset($row['xml:forum:permADMIN']) ?       $row['xml:forum:permADMIN'] : false);
      $isFORUMCONTRIBUTOR =	(isset($row['xml:forum:permCONTRIBUTOR']) ? $row['xml:forum:permCONTRIBUTOR'] : false);
      $signature =	        (isset($row['xml:forum:signature']) ?       $row['xml:forum:signature'] : "");
    }
    $signature_block =      "\r\n\r\n\r\n--------------------------------\r\n".$signature;
    $isAdmin =              ($isFORUMADMIN||$isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
    $isContributor =        ($isAdmin||$isFORUMCONTRIBUTOR);
    $category =             sanitize('html',get_var('category'));
    $ID =                   sanitize('ID',get_var('ID'));
    $targetID =             sanitize('ID',get_var('targetID'));
    $submode =              sanitize('html',get_var('submode'));
    $new_category =         sanitize('html',get_var('new_category'));
    $new_title =            sanitize('html',get_var('new_title'));
    $new_content =          sanitize('html',get_var('new_content'));
    // Get names and IDs of levels
    $topID = $ID;
    if ($ID) {
      $topID = $this->get_root_ID($ID);
      $this->_set_ID($topID);
      if (!$category = $this->get_field('category')){
        $category = "";
        $ID = "";
      }
    }
    $records = $this->get_structure($category);
    $categories_arr = array();
    $current_forumID = false;
    $current_forum_title = "";
    $current_forum_category = "";
    $current_forum_content = "";
    $current_topicID = false;
    $current_topic_title = "";
    $topics = false;
    foreach($records as $record){
      $categories_arr[$record['category']][] = $record;
    }
    foreach($categories_arr as $_category=>$forums) {
      foreach($forums as $forum){
        if (!$topID || $forum['ID']==$topID){
          $current_forumID = $topID;
          $current_forum_category = $forum['category'];
          $current_forum_content =  $forum['content'];
          $current_forum_title =    $forum['title'];
        }
        if ($topID && $forum['topicID_csv']){
          $topics = $this->get_topics_for_forum($topID);
          foreach ($topics as $topic) {
            if ($ID == $topic['ID']) {
              $current_topicID =        $topic['ID'];
              $current_topic_content =  $topic['content'];
              $current_topic_title =    $topic['title'];
            }
          }
        }
      }
    }
    $args =
      array(
        'isAdmin'   =>                  $isAdmin,
        'isContributor'   =>            $isContributor,
        'avatar' =>                     $avatar,
        'category' =>                   $category,
        'current_forumID' =>            $current_forumID,
        'current_forum_category' =>     $current_forum_category,
        'current_forum_content' =>      $current_forum_content,
        'current_forum_title' =>        $current_forum_title,
        'current_topicID' =>            $current_topicID,
        'current_topic_title' =>        $current_topic_title,
        'instance' =>                   $instance,
        'new_category' =>               $new_category,
        'new_content' =>                $new_content,
        'new_title' =>                  $new_title,
        'signature' =>                  $signature,
        'signature_block' =>            $signature_block,
        'submode' =>                    $submode,
        'targetID' =>                   $targetID
      );
    // Handle submodes:
    $msg = array();
    $this->do_submode($args,$ID,$category,$msg);
    $msg = implode("",$msg);
    $ObjFS = new FileSystem;
    $css_version = trim(substr($ObjFS->get_line(SYS_MODULES."module.forum.css"),3));
    Page::push_content(
      'head_top',
      '<link rel="stylesheet" type="text/css" href="'
      .$page.'?'
      .'submode=css&amp;v='.$css_version.'" />'
    );
    Page::push_content(
      'javascript',
       "function forum_action(request,div,instance) {\n"
      ."  if (geid('forum_status_'+instance)) {\n"
      ."    geid('forum_status_'+instance).style.display='none';\n"
      ."  }\n"
      ."  window.focus();\n"
      ."  geid(div).innerHTML=\n"
      ."    \"<div class='fl'>&nbsp; <img src='\"+base_url+\"img/sysimg/progress_indicator.gif' width='16' height='16' alt='Please wait...'>&nbsp;Loading...</div>\";\n"
      ."  switch(request) {\n"
      ."    case 'cancel':\n"
      ."      geid(div).innerHTML='';\n"
      ."      return;\n"
      ."    break;\n"
      ."  }\n"
      ."  post_vars = 'submode='+request+'&ID='+geid_val('ID');\n"
      ."  xFn = function() {externalLinks(); }\n"
      ."  ajax_post(base_url+geid_val('goto'),div,post_vars,xFn);\n"
      ."}\n"
    );
    $out = "";

    if ($component_help==1) {
      $out =
        Component_Base::help(
          __CLASS__."::".__FUNCTION__."()",
          $ident.".public_view_forums=[0|1]{1};"
         .$ident.".public_view_posts=[0|1]{1};"
         .$ident.".public_view_replies=[0|1]{1};"
         .$ident.".public_view_topics=[0|1]{1};"
         , $instance
        );
    }
    $parameters =   Component_Base::get_parameters();
    $public_view_forums =   Component_Base::get_parameter_for_instance($instance,$parameters,$ident.'.public_view_forums',1);
    $public_view_posts =    Component_Base::get_parameter_for_instance($instance,$parameters,$ident.'.public_view_posts',1);
    $public_view_replies =  Component_Base::get_parameter_for_instance($instance,$parameters,$ident.'.public_view_replies',1);
    $public_view_topics =   Component_Base::get_parameter_for_instance($instance,$parameters,$ident.'.public_view_topics',1);
    $view_posts =   $isContributor || $public_view_posts;
    $view_replies = $isContributor || $public_view_replies;
    if ($submode) {
      // The position may have changed - get position again
      $topID = $ID;
      if ($ID) {
        $topID = $this->get_root_ID($ID);
        $this->_set_ID($topID);
        if (!$category = $this->get_field('category')){
          $category = "";
          $ID = "";
        }
      }
      $records = $this->get_structure($category);
      $categories_arr = array();
      $current_topicID = false;
      foreach($records as $record){
        $categories_arr[$record['category']][] = $record;
      }
      foreach($categories_arr as $_category=>$forums) {
        foreach($forums as $forum){
          if (!$topID || $forum['ID']==$topID){
            $current_forumID = $topID;
            $current_forum_title= $forum['title'];
          }
        }
        if ($submode && $topID && $forum['topicID_csv']){
          $topics = $this->get_topics_for_forum($topID);
          foreach ($topics as $topic) {
            if ($ID == $topic['ID']) {
              $current_topicID =        $topic['ID'];
              $current_topic_content =  $topic['content'];
              $current_topic_title =    $topic['title'];
            }
          }
        }
      }
    }
    if ($msg) {
      $status_border = (substr($msg,0,8)=='<b>Error' ? '#ff0000' : '#008000');
      $status_bg = (substr($msg,0,8)=='<b>Error' ? '#ffd0d0' : '#d0ffd0');
      $status_highlight = (substr($msg,0,8)=='<b>Error' ? '#ff8080' : '#80ff80');
    }
    $out.=
       draw_form_field('category',$category,'hidden')
      .draw_form_field('ID',$ID,'hidden')
      ."<div class='forums' id='forum_".$instance."'>\n"
      .HTML::draw_status($instance,$msg);

    foreach($categories_arr as $_category=>$forums) {
      $out.=
         "<div class='forum'>\n"
        ."<table summary='Forum listing for ".htmlentities($_category)."' class='category'>\n"
        ."  <tr>\n"
        ."    <th class='title' colspan='2'>\n"
        .$this->draw_path($_category,$forums,$ID,$topID,$topics)
        ."</th>\n"
        ."    <th class='created'>Last Post</th>\n"
        ."    <th class='qty'>Topics</th>\n"
        ."    <th class='qty'>Posts</th>\n"
        ."  </tr>\n"
        ."  <tr>\n"
        ."    <td colspan='5'><table class='forum'>\n";
      foreach($forums as $forum){
        if (!$topID || $forum['ID']==$topID){
          $current_forumID = $topID;
          $current_forum_title= $forum['title'];
          $out.=
             "    <tr class='header".($forum['ID']==$topID ? " current" : "")."'>\n"
            ."      <td class='icon' style='width: 4%'>\n"
            ."<a href=\"".BASE_PATH.$page."?ID=".$forum['ID']."\" title=\"View Forum '".htmlentities($forum['title'])."'\">"
            ."<img src='/img/spacer' class='forum icon' alt='[Forum icon]' />"
            ."</a>"
            ."      </td>\n"
            ."      <td class='title'>\n"
            ."<a href=\"".BASE_PATH.$page."?ID=".$forum['ID']."\" title=\"View Forum '".htmlentities($forum['title'])."'\">"
            .htmlentities($forum['title'])
            ."</a>&nbsp; "
            .($isAdmin && $forum['ID']==$topID ?
               "<a href='#forum_form' title='Edit Forum' onclick=\"forum_action('ajax_edit_forum','edit_".$forum['ID']."','".$instance."')\">"
              ."<img src='/img/spacer' class='edit icon' alt='[Edit icon]' /></a>"
             : ""
             )
             .($isAdmin && $forum['topicID_csv']=='' ?
               "<a href='#' title='Delete Forum'"
              ." onclick=\"if(confirm('Delete Forum?')){ geid_set('targetID',".$forum['ID'].");geid_set('submode','delete_forum');geid('form').submit();};return false;\">"
              ."<img src='/img/spacer' class='delete icon' alt='[Delete icon]' /></a>"
             : ""
             )
            ."</td>\n"
            ."      <td class='created'>\n"
            .($forum['last_post_date'] ? $this->get_YYYYMMDD_to_format($forum['last_post_date'],'MM DD YYYY h:mmXM') : "")
            ."</td>"
            ."      <td class='qty'>".$forum['topics']."</td>\n"
            ."      <td class='qty'>".$forum['posts']."</td>\n"
            ."    </tr>\n"
            .($forum['content'] ?
               "        <tr>\n"
              ."          <td class='icon'>&nbsp;</td>\n"
              ."          <td colspan='4' class='body'>".nl2br($forum['content'])."</td>\n"
              ."        </tr>\n"
            : ""
            )
            .($isAdmin && $forum['ID']==$topID ?
                "        <tr>\n"
               ."          <td colspan='5' id=\"edit_".$forum['ID']."\"></td>\n"
               ."        </tr>"
             : ""
             );


// Start of Topics Listing
          if ($topID && (($isContributor && $forum['ID']==$topID) || $forum['topicID_csv'])) {
            $out .=
               "    <tr>\n"
              ."      <td colspan='5'><table class='topic' summary='Topics for Forum'>\n"
              .($isContributor && $forum['ID']==$topID ?
                 "        <tr>\n"
                ."          <td class='icon' style='width:4%'>\n"
                ."<a href='#add_topic' title='New Topic for Forum' onclick=\"forum_action('ajax_new_topic','add_topic','".$instance."')\">"
                ."<img src='/img/spacer' class='add_topic icon' alt='[Add Topic icon]' />"
                ."</a></td>\n"
                ."          <td colspan='4' class='body'>"
                ."<a href='#add_topic' title='New Topic for Forum' onclick=\"forum_action('ajax_new_topic','add_topic','".$instance."')\">Add New Topic...</a></td>\n"
                ."        </tr>"
                 ."        <tr>\n"
                 ."          <td colspan='5' id=\"add_topic\"></td>\n"
                 ."        </tr>"
               : ""
               )
              ;

            if ($topID && $forum['topicID_csv']){
              $count = count($topics);
              $current_topicID = false;
              foreach($topics as $topic){
                if ($ID == $topic['ID']) {
                  $current_topicID = $topID;
                  $current_topic_title= $topic['title'];
                }
                if (1 || $forum['ID']==$ID || $ID==$topic['ID']) {
                  if ($ID && $ID==$topic['ID'] && $view_posts) {
                    $posts = $this->get_posts_for_topic($ID);
                  }
                  $out.=
                     "        <tr class='header".($ID && $ID==$topic['ID'] ? " current" : "")."'>\n"
                    ."          <td class='icon' style='width:5%'>\n"
                    ."<a href=\"".BASE_PATH.$page."?ID=".$topic['ID']."\" title=\"View Topic '".$topic['title']."'\">"
                    ."<img src='/img/spacer' class='topic icon' alt='[Topic icon]' /></a>"
                    ."</td>"
                    ."          <td class='title'>"
                    ."<a href=\"".BASE_PATH.$page."?ID=".$topic['ID']."\" title=\"View Topic '".$topic['title']."'\">"
                    .$topic['title']
                    ."</a>&nbsp; "
                    .($isAdmin && $ID == $topic['ID'] ?
                       "<a href='#forum_form' title='Edit Topic' onclick=\"forum_action('ajax_edit_topic','edit_".$topic['ID']."','".$instance."')\">"
                      ."<img src='/img/spacer' class='edit icon' alt='[Edit icon]' /></a>"
                     : ""
                     )
                    .($isAdmin && $topic['postID_csv']=='' ?
                       "&nbsp; <a href='#' title='Delete Topic'"
                      ." onclick=\"if(confirm('Delete Topic?')){ geid_set('targetID',".$topic['ID'].");geid_set('submode','delete_topic');geid('form').submit();};return false;\">"
                      ."<img src='/img/spacer' class='delete icon' alt='[Delete icon]' /></a>"
                     : ""
                     )
                    ."</td>\n"
                    ."          <td class='created'>\n"
                    .$this->get_YYYYMMDD_to_format($topic['last_post_date'],'MM DD YYYY h:mmXM')
                    ."</td>"
                    ."          <td class='qty'>&nbsp;</td>\n"
                    ."          <td class='qty'>".$topic['posts']."</td>\n"
                    ."        </tr>\n"
                    .($topic['content'] ?
                       "        <tr>\n"
                      ."          <td class='icon'>&nbsp;</td>\n"
                      ."          <td colspan='4' class='body'>".nl2br($topic['content'])."</td>\n"
                      ."        </tr>\n"
                    : ""
                    )
                    .($isAdmin && $ID == $topic['ID'] ?
                       "        <tr>\n"
                      ."          <td colspan='5' id='edit_".$topic['ID']."'></td>\n"
                      ."        </tr>\n"
                     : ""
                     )
                    ;
                  if ($ID && $ID==$topic['ID']) {
                    $out.=
                       "        <tr>\n"
                      ."          <td colspan='5'>\n"
                      .$this->draw_posts($forum,$instance,$isAdmin,$isContributor,$posts,$topic,$topID,$view_posts,$view_replies)
                      ."          </td>\n"
                      ."        </tr>\n";
                  }
                }
              }
            }
            $out.=
               "</table>\n"
              ."      </td>\n"
              ."    </tr>\n";
          }
        }
      }
// End of Topics Listing

      $out.=
         "    </table></td>\n"
        ."  </tr>\n"
        ."</table>\n"
        ."</div>";
    }

    $out.=
       ($isContributor ?
          "<input type='button' value='Your Settings...'"
         ." onclick=\"forum_action('ajax_your_settings','forum_form_".$instance."','".$instance."')\" />"
        : ""
       )
      .($isAdmin && !$ID ?
          "<input type='button' value='New Forum...'"
         ." onclick=\"forum_action('ajax_new_forum','forum_form_".$instance."','".$instance."')\" />"
        : ""
       )
      ."<div style='clear:both;margin-top:5px'><a name='forum_form_a_".$instance."'></a><div id='forum_form_".$instance."'></div></div>"
      .($isAdmin ?
          "<hr />"
         ."<div style='padding:5px;'>"
         ."<h1>Forum User Admin</h1>\n"
         .draw_auto_report('module.forum.users')
         ."</div>"
        : ""
       )
      ."</div>";
    return $out;
  }
  function uninstall() {
$sql =
<<<UNINSTALL_SQL
  # Forum Postings
  DELETE FROM `action`                 WHERE `sourceType`  = 'report' AND `sourceID` IN (2032663965);
  DELETE FROM `report`                 WHERE `ID` IN (2032663965);
  DELETE FROM `report_columns`         WHERE `reportID` IN (2032663965) AND `systemID` IN(1,1470373540);
  DELETE FROM `report_filter_criteria` WHERE `filterID` IN (SELECT `ID` FROM `report_filter`  WHERE `reportID` IN (2032663965) AND `destinationType`='global');
  DELETE FROM `report_filter`          WHERE `reportID` IN (2032663965) AND `destinationType`='global';
  DELETE FROM `report_settings`        WHERE `reportID` IN (2032663965) AND `destinationType`='global';

  DELETE FROM `listtype`               WHERE `ID` IN (1185122145);
  DELETE FROM `listdata`               WHERE `listtypeID` IN (1185122145) AND `systemID`IN (1);
  DELETE FROM `ecl_tags`               WHERE `ID` IN (634411157);

  # Extra Person fields
  DELETE FROM `report_columns`         WHERE `ID` IN (1108694310,773472291,292832858,193315330,685163725);

  # Forum Users
  DELETE FROM `report`                 WHERE `ID` IN (462298250);
  DELETE FROM `report_columns`         WHERE `reportID` IN (462298250) AND `systemID` IN(1);
  DELETE FROM `report_filter_criteria` WHERE `filterID` IN (SELECT `ID` FROM `report_filter`  WHERE `destinationType` = 'system' AND `reportID` IN (462298250) AND `systemID` IN(1,640269186));
  DELETE FROM `report_filter`          WHERE `reportID` IN (462298250) AND `destinationType` = 'system' AND `systemID` IN(1,640269186);
  DELETE FROM `report_settings`        WHERE `reportID` IN (462298250) AND `destinationType`='global';
  DELETE FROM `component`              WHERE `ID` IN (1018245224);

UNINSTALL_SQL;
    $commands = Backup::db_split_sql($sql);

    foreach ($commands as $command) {
      $this->do_sql_query($command);
    }

    return 'Removed Module';
  }
}
function strip_whitespace($string) {
  return str_replace(array("\n","\r"," "),'',$string);
}
?>