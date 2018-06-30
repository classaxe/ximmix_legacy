<?php
define('VERSION_WIDGET','1.0.9');
/*
Version History:
  1.0.9 (2014-02-18)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.widget.txt)
*/
class Widget extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, name, category, seq, color_background, color_text, php_setup, php_display, required_feature, permGROUPVIEWER, permGROUPEDITOR, permMASTERADMIN, permPUBLIC, permSYSADMIN, permSYSAPPROVER, permSYSEDITOR, permSYSLOGON, permSYSMEMBER, permUSERADMIN, title, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
  static $container_width =     750;
  static $container_height =    400;
  private $title;
  private $width = 750;
  private $url;

  public function __construct($ID="") {
    parent::__construct("widget",$ID);
    $this->_set_has_groups(false);
    $this->_set_object_name('Widget');
    $this->url = BASE_PATH.'_popup_layer/dashboard/'.Widget::$container_width."/".Widget::$container_height;
    $this->set_edit_params(
      array(
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  function draw_expander_heading($open,$content,$info='',$msg=''){
    $js = "";
    if ($msg) {
      $status = HTML::draw_status('msg_'.$this->_get_ID(),$msg,true);
      $js = $status['js'];
    }
    $html =
       "<h2 style='cursor:hand;font-style:italic' title='Click to view / hide customisation panel'"
      ." onclick=\"widget_toggle('id_".$this->_get_ID()."');\">"
      ."<img id='id_".$this->_get_ID()."_show' class='icon' src='/img/spacer' style='width:9px;height:9px;margin:2px;background-position:-3190px 0px;".($open ? "display:none" : "")."' alt='+' />"
      ."<img id='id_".$this->_get_ID()."_hide' class='icon' src='/img/spacer' style='width:9px;height:9px;margin:2px;background-position:-3199px 0px;".($open ? "" : "display:none")."' alt='-' />"
      .$this->title
      ."</h2>"
      ."<div id='id_".$this->_get_ID()."' style='".($open ? "" : "display:none")."'>"
      .($info ?
           "<div class='info'>"
          ."<img class='icon' src='/img/spacer' style='width:11px;height:11px;background-position:-2600px 0px;margin-right:2px;' alt='i' />"
          .$info
          ."</div>\n"
       : ""
       )
      .($msg ?
          "<div id='msg_".$this->_get_ID()."'>"
         .$status['html']
         ."</div>\n"
       : ""
       )
      .($content ? "<div class='content'>\n".$content."</div>" : "")
      ."</div>"
      ;
    return
      array(
        'html' =>   $html,
        'js' =>     $js
      );
  }
  function draw_param_row(&$ObjRC,$val_arr,$param,$type,$width='',$height='',$listtype='') {
    return
       "<div class='param'>\n"
      ."<div class='lbl'>".$param."</div>\n"
      ."<div class='val'>".$ObjRC->draw_form_field('',$val_arr[$param][0],$val_arr[$param][1],$type,$width,'','','',0,0,'',$listtype,$height)."</div>"
      ."</div>\n";
  }
  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }
  function get_available_widgets() {
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `widget`\n"
      ."WHERE\n"
      .($isMASTERADMIN ? "" : "  `systemID` IN(1,".SYS_ID.") AND\n")
      ."  `name` NOT IN('Customise Dashboard')\n"
      ."ORDER BY\n"
      ."  `category`,`seq`"
      ;
    $widgets = Widget::get_records_for_sql($sql);
    $out = array();
    foreach ($widgets as $widget){
      if (Widget::is_visible($widget)) {
        if ($widget['required_feature']=='' || System::has_feature($widget['required_feature'])) {
          $out[$widget['ID']] = $widget;
        }
        else {
//          print $widget['required_feature']."<br />";
        }
      }
    }
//    die;
//    y($out);die;
    return $out;
  }

  function get_persons_widgets($available,$PWidgets_csv) {
    $out = array();
    $out[] = $this->get_record_by_name('Customise Dashboard');
    if ($PWidgets_csv){
      $PWidgets_arr = explode(",",str_replace(" ","",$PWidgets_csv));
      foreach($PWidgets_arr as $PWidget){
        if (array_key_exists($PWidget,$available)){
          $out[] = $available[$PWidget];
        }
      }
    }
    return $out;
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  function view_dashboard(){
    // Exposes these variables to widget code:
    //   $this                  Instance of Widget object, with ID set to ID of active widget
    //   $ObjPerson             Person Object with ID set to current person
    //   $isMASTERADMIN         True if person is a MASTERADMIN
    //   $widgets_available     List of available widgets for person
    //   $PWidgets_csv          List of chosen widgets for person
    //   $submode               Only available for widget startup blocks - then set to ""
    //   $_submode              Available for startup and display blocks - reflects last $submode value
    global $submode,$PWidgets_csv,$msg;
    $_submode = $submode;
    $ObjPerson =            new Person(get_userID());
    switch ($submode) {
      case "save_dashboard_options":
        $ObjPerson->set_field('PWidgets_csv',$_POST['PWidgets_csv']);
        $msg = "<b>Success</b>: Your Dashboard Preferences have been saved.";
      break;
      default:
      break;
    }
    $PWidgets_csv =         $ObjPerson->get_field('PWidgets_csv');
    $widgets_available =    $this->get_available_widgets();
    $widgets =              $this->get_persons_widgets($widgets_available,$PWidgets_csv);
    // Run widget pre-display operations
    foreach($widgets as $_record) {
      $ID = $_record['ID'];
      $this->_set_ID($ID);
      eval($_record['php_setup']);
    }
    $submode='';
    $_html =    "<div id='dashboard'>";
    $_js =      "";
    foreach($widgets as $_record) {
      $ID = $_record['ID'];
      $this->_set_ID($ID);
      $this->title = $_record['title'];
      $result = eval($_record['php_display']);
      $_html.=
         "<div class='widget'>"
        .$result['html']
        ."</div>";
      $_js.=    (isset($result['js']) ? $result['js'] : "");
    }
    $_html.= "</div>";
    return
      array(
        'html'=>$_html,
        'js'=>$_js,
        'css'=>"[['#dashboard',{backgroundColor:'#ff0000'},['#dashboard .widget h2',{color:'#ff0000'}]"
      );
  }

  function widget_comments_hidden() {
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `comment`\n"
      ."WHERE\n"
      ."  `archive`=0 AND\n"
      ."  `approved`='hidden'"
      .(get_person_permission("MASTERADMIN") ? "" : " AND `systemID`=".SYS_ID);
    $count =  $this->get_field_for_sql($sql);
    $this->title.= ": ".$count;
    $info =
       "This widget allows you to see all hidden comments."
      .($count ?
           "<br />To approve, delete or mark a comment as 'spam' (which also sends a notification to Akismet), "
          ."click the check box to the left and choose an action in the 'with selected' dropdown selector."
       :   " There aren't any right now."
      );
    $html =     "";
    $js =       "";
    if ($count) {
      $tmp =    draw_auto_report('comments_hidden',2,$this->url);
      $html =   $tmp['html'];
      $js =     $tmp['js'];
    }
    $content = $this->draw_expander_heading($count,$html,$info);
    return
      array(
        'html' =>   $content['html'],
        'js' =>     $content['js']."\n".$js.";\n"
      );
  }

  function widget_comments_pending() {
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `comment`\n"
      ."WHERE\n"
      ."  `archive`=0 AND\n"
      ."  `approved`='pending'"
      .(get_person_permission("MASTERADMIN") ? "" : " AND `systemID`=".SYS_ID);
    $count =  $this->get_field_for_sql($sql);
    $this->title.= ": ".$count;
    $info =
       "This widget allows you to see all pending comments."
      .($count ?
           "<br />To approve, delete or mark a comment as 'spam' (which also sends a notification to Akismet), "
          ."click the check box to the left and choose an action in the 'with selected' dropdown selector."
       :   " There aren't any right now."
      );
    $html =     "";
    $js =       "";
    if ($count) {
      $tmp =    draw_auto_report('comments_pending',2,$this->url);
      $html =   $tmp['html'];
      $js =     $tmp['js'];
    }
    $content = $this->draw_expander_heading($count,$html,$info);
    return
      array(
        'html' =>   $content['html'],
        'js' =>     $content['js']."\n".$js.";\n"
      );
  }

  function widget_comments_spam() {
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `comment`\n"
      ."WHERE\n"
      ."  `archive`=0 AND\n"
      ."  `approved`='spam'"
      .(get_person_permission("MASTERADMIN") ? "" : " AND `systemID`=".SYS_ID);
    $count =  $this->get_field_for_sql($sql);
    $this->title.= ": ".$count;
    $info =
       "This widget allows you to see all comments currently marked as spam."
      .($count ?
           "<br />To delete, mark a comment as unapproved or approve it (which also sends a notification to Akismet), "
          ."click the check box to the left and choose an action in the 'with selected' dropdown selector."
       :   " There aren't any right now."
      );
    $html =     "";
    $js =       "";
    if ($count) {
      $tmp =    draw_auto_report('comments_spam',2,$this->url);
      $html =   $tmp['html'];
      $js =     $tmp['js'];
    }
    $content = $this->draw_expander_heading($count,$html,$info);
    return
      array(
        'html' =>   $content['html'],
        'js' =>     $content['js']."\n".$js.";\n"
      );
  }

  function widget_customise_widgets($widgets_available,$PWidgets_csv,$_submode){
    global $msg;
    $_msg = ($_submode=='save_dashboard_options' ? $msg : "");
    $value_arr =    explode(",",str_replace(" ","",$PWidgets_csv));
    $list_arr =     array();

    $idx = Page::get_css_idx('404040','d0d0d0');
    $info =
       "This widget sets your dashboard preferences.<br />"
      ."Choose from the available list of widgets in the dropdown list then press 'Save'.<br />\n"
      ."To remove a selected option simply click on it.";
    $content=
       "<select id=\"selector_csv_PWidgets_csv\""
      ." style=\"width:".(((int)$this->width-85)*0.45)."px;font-size:8pt;\""
      ." class=\"formField fl\""
      ." onchange=\"selector_csv_add("
      ."'PWidgets_csv',this.options[this.selectedIndex].value);\">\n"
      ."<option class='color_".$idx."' value=''>(None)</option>";
//    y($widgets_available);die;
    foreach($widgets_available as $wa) {
      $idx = Page::get_css_idx($wa['color_text'],$wa['color_background']);
      $content.="<option class='color_".$idx."' value=\"".$wa['ID']."\">".$wa['name']."</option>";
    }
    $content.=
       "</select>"
      ."<div id=\"selector_csv_div_PWidgets_csv\""
      ." class='formField fl txt_l'"
      ." style='width:".(((int)$this->width-85)*0.55)."px;"
      ."height:60px;overflow:auto;background-color:#ffffff;"
      ."font-size:8pt;'>"
      ."</div>"
      ."<input class='formButton fr' type='button' style='width:50px;;margin-right:1px'"
      ." onclick=\"status_message_hide('form_status_msg_".$this->_get_ID()."');this.value='Saving';this.disabled=1;"
      ."geid_set('submode','save_dashboard_options');"
      ."popup_layer_submit('".$this->url."','PWidgets_csv='+geid_val('PWidgets_csv'));\""
      ." value='Save' />"
      ."<div class='clr_b'></div>"
      ."<input type='hidden' id=\"PWidgets_csv\""
      ." name=\"PWidgets_csv\" value=\"".$PWidgets_csv."\" />";
    $content = $this->draw_expander_heading($_msg!='',$content,$info,$_msg);
    return
      array(
        'html' =>   $content['html'],
        'js' =>     $content['js']."selector_csv_show(\"PWidgets_csv\");"
      );
  }

  function widget_manage_subscriptions($phase,$_submode='') {
    global $msg;
    $out = array('html'=>'','js'=>'');
    if (!$_personID = get_userID()) {
      $out['html'] = "You must be logged in to manage subscriptions";
      return $out;
    }
    $_sql =
       "SELECT\n"
      ."  `g`.`ID`,\n"
      ."  `g`.`description`,\n"
      ."  `g`.`name`,\n"
      ."  COALESCE(`gm`.`permEMAILRECIPIENT`,0) `subscribed`\n"
      ."FROM\n"
      ."  `groups` `g`\n"
      ."LEFT JOIN `group_members` `gm` ON\n"
      ."  `g`.`ID` = `gm`.`groupID` AND\n"
      ."  `gm`.`personID` = ".$_personID."\n"
      ."WHERE\n"
      ."  `g`.`systemID` = ".SYS_ID." AND\n"
      ."  `g`.`isSubscribable` = 1";
    $_groups = $this->get_records_for_sql($_sql);
    $_groups_arr = array();
    foreach ($_groups as $_group){
      $_groups_arr[] = $_group['ID'];
    }
    $val_width = 500;
    switch ($phase) {
      case 'setup':
        global $submode;
        switch ($submode){
          case 'cancel_'.$this->_get_ID():
            $msg = "Changes cancelled.";
          break;
          case 'save_'.$this->_get_ID():
            $_chosen = array();
            foreach ($_REQUEST as $_key=>$_value) {
              $_key_bits = explode("_",$_key);
              if (
                count($_key_bits)==3 &&
                $_key_bits[0]."_".$_key_bits[1] == 'subscribe_'.$this->_get_ID() &&
                in_array($_key_bits[2],$_groups_arr)
              ){
                $_chosen[] = $_key_bits[2];
              }
            }
            $_obj_group = new Group;
            foreach($_groups_arr as $_group){
              $_obj_group->_set_ID($_group);
              $_perms = array("permEMAILRECIPIENT"=>(in_array($_group,$_chosen) ? 1 : 0));
              $_obj_group->member_assign($_personID,$_perms);
            }
            $msg = "Your subscription choices have been updated.";
          break;
        }
      break;
      case 'display':
        $_msg = ($_submode=='save_'.$this->_get_ID() || $_submode=='cancel_'.$this->_get_ID() ? $msg : "");
        $info =
           "This widget lets you manage your mailing list subscriptions.<br />\n"
          ."To subscribe to a public mail list place a checkmark in the appropriate box, or clear it to unsubscribe.<br />\n"
          ."To apply your choices, click the <b>Save</b> button.";
        $content =
           "<table class='minimal'>"
          ."  <tr>\n"
          ."    <th>Name</th>\n"
          ."    <th style='padding:0 0.5em 0 0.5em;'>Description</th>\n"
          ."    <th>Subscribed</th>\n"
          ."  </tr>\n";
        foreach($_groups as $_group) {
          $_id =        "subscribe_".$this->_get_ID()."_".$_group['ID'];
          $_checked =   ($_group['subscribed'] ? " checked='checked'" : "");
          $content.=
             "<tr>\n"
            ."  <td><label for='".$_id."'>".$_group['name']."</label></td>"
            ."  <td style='padding:0 0.5em 0 0.5em;'><label for='".$_id."'>".$_group['description']."</label></td>"
            ."  <td class='txt_c'><input type='checkbox' name='".$_id."' id='".$_id."'".$_checked." /></td>"
            ."</tr>\n";
        }
        $content.=
           "</table>\n"
          ."<br />\n"
          ."<div style='text-align:center'>"
          ."<input id='save_".$this->_get_ID()."' class='formButton' style='width:50px;' type='button'"
          ." onclick=\"this.disabled=true;geid('cancel_".$this->_get_ID()."').disabled=true;"
          ."geid_set('submode','save_".$this->_get_ID()."');"
          ."popup_layer_submit('".$this->url."');\""
          ." value='Save' />\n"
          ."<input id='cancel_".$this->_get_ID()."' class='formButton' style='width:50px;' type='button'"
          ." onclick=\"this.disabled=true;geid('save_".$this->_get_ID()."').disabled=true;"
          ."geid_set('submode','cancel_".$this->_get_ID()."');"
          ."popup_layer_submit('".$this->url."');\""
          ." value='Cancel' />\n"
          ."</div>";
        $content = $this->draw_expander_heading($_msg!='',$content,$info,$_msg);
        return
          array(
            'html' =>   $content['html'],
            'js' =>     $content['js']."selector_csv_show(\"PWidgets_csv\");"
          );
      break;
    }
    return $out;
  }

  function widget_pending_members() {
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `person`\n"
      ."WHERE\n"
      ."  `person`.`type` = 'user' AND\n"
      ."  `permSYSMEMBER`='0'"
      .(get_person_permission("MASTERADMIN") ? "" : " AND `systemID`=".SYS_ID);
    $count =  $this->get_field_for_sql($sql);
    $this->title.= ": ".$count;
    $info =
       "This widget allows you to see all persons not assigned member rights."
      .($count ?
           "<br />To make someone a full member or delete their account, click the check box to the left "
          ."and choose an action in the 'with selected' dropdown selector."
       :   " There aren't any right now."
      );
    $html =     "";
    $js =       "";
    if ($count) {
      $tmp =    draw_auto_report('dashboard_persons_pending',1,$this->url);
      $html =   $tmp['html'];
      $js =     $tmp['js'];
    }
    $content =  $this->draw_expander_heading($count,$html,$info);
    return
      array(
        'html' =>   $content['html'],
        'js' =>     $content['js']."\n".$js.";\n"
      );
  }

  function widget_set_category_stacker($phase,$args,$_submode=''){
    global $msg;
    $params_csv =
       "category_list,category_show,content_char_limit,content_plaintext,content_show,date_show,extra_fields_list,"
      ."keywords_show,limit_per_category,related_show,sites_list,sort_by,"
      ."thumbnail_at_top,thumbnail_image,thumbnail_width";
    $params_arr = explode(",",$params_csv);
    $val_width = 500;
    $ident = 'category_stacker.';
    switch ($phase) {
      case 'setup':
        global $submode;
        switch ($submode){
          case 'save_'.$this->_get_ID():
            $data = array();
            foreach ($params_arr as $param) {
              $field = str_replace(".","_",$ident).$param."_".$this->_get_ID();
              $data[$ident.$param] = (isset($_REQUEST[$field]) ?  $_REQUEST[$field] : "");
            }
            $Obj = new System(SYS_ID);
            $Obj->set_parameters_for_instance($args['instance'],$data);
            $msg =
               "<b>Success:</b> Changes to the System for the Category Stacker Component named "
              ."<b>".$args['instance']."</b> have been saved.";
          break;
          default:
            return "";
          break;
        }
      break;
      case 'display':
        $_msg = ($_submode=='save_'.$this->_get_ID() ? $msg : "");
        $Obj = new System(SYS_ID);
        $parameters = $Obj->get_field('component_parameters');
        $val_arr = array();
        foreach ($params_arr as $param) {
          $val_arr[$param] =
            array(
              $ident.$param."_".$this->_get_ID(),
              Component_Base::get_parameter_for_instance($args['instance'],$parameters,$ident.$param,'')
            );
        }
        $ObjRC = new Report_Column;
        $info =
           "This widget sets the System values for the Category Stacker Component named <b>".$args['instance']."</b>.<br />"
          ."That won't have any effect if the parameter has been overridden in the page or layout using that setting.";
        $content =
           $this->draw_param_row($ObjRC,$val_arr,'category_list','selector_listdata_csv',$val_width,40,$args['listtype'])
          .$this->draw_param_row($ObjRC,$val_arr,'category_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'content_char_limit','int',25)
          .$this->draw_param_row($ObjRC,$val_arr,'content_plaintext','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'content_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'date_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'extra_fields_list','textarea',$val_width,40)
          .$this->draw_param_row($ObjRC,$val_arr,'keywords_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'limit_per_category','int',20)
          .$this->draw_param_row($ObjRC,$val_arr,'related_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'sites_list','textarea',$val_width,40)
          .$this->draw_param_row($ObjRC,$val_arr,'sort_by','radio_csvlist','','','latest|latest')
          .$this->draw_param_row($ObjRC,$val_arr,'thumbnail_at_top','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'thumbnail_image','radio_csvlist','','','s|s,m|m,l|l')
          .$this->draw_param_row($ObjRC,$val_arr,'thumbnail_width','int',25)
          ."<div class='clr_b'></div>"
          ."<input class='formButton' style='width:50px;' type='button'"
          ." onclick=\""
          ."this.disabled=true;geid_set('submode','save_".$this->_get_ID()."');"
          ."popup_layer_submit('".$this->url."');\""
          ." value='Save' />\n";
        $content = $this->draw_expander_heading($_msg!='',$content,$info,$_msg);
        return
          array(
            'html' =>   $content['html'],
            'js' =>     $content['js']."selector_csv_show(\"PWidgets_csv\");"
          );
      break;
    }
  }

  function widget_set_category_tabber($phase,$args,$_submode=''){
    global $msg;
    $params_csv =
       "category_list,category_show,content_char_limit,content_plaintext,content_show,date_show,extra_fields_list,"
      ."keywords_show,limit_per_category,related_show,sites_list,sort_by,thumbnail_at_top,thumbnail_image,thumbnail_width";
    $params_arr = explode(",",$params_csv);
    $val_width = 500;
    $ident = 'category_tabber.';
    switch ($phase) {
      case 'setup':
        global $submode;
        switch ($submode){
          case 'save_'.$this->_get_ID():
            $data = array();
            foreach ($params_arr as $param) {
              $field = str_replace(".","_",$ident).$param."_".$this->_get_ID();
              $data[$ident.$param] = (isset($_REQUEST[$field]) ?  $_REQUEST[$field] : "");
            }
            $Obj = new System(SYS_ID);
            $Obj->set_parameters_for_instance($args['instance'],$data);
            $msg =
               "<b>Success:</b> Changes to the System for the Tabber Component named "
              ."<b>".$args['instance']."</b> have been saved.";
          break;
          default:
            return "";
          break;
        }
      break;
      case 'display':
        $_msg = ($_submode=='save_'.$this->_get_ID() ? $msg : "");
        $Obj = new System(SYS_ID);
        $parameters = $Obj->get_field('component_parameters');
        $val_arr = array();
        foreach ($params_arr as $param) {
          $val_arr[$param] =
            array(
              $ident.$param."_".$this->_get_ID(),
              Component_Base::get_parameter_for_instance($args['instance'],$parameters,$ident.$param,'')
            );
        }
        $ObjRC = new Report_Column;
        $info =
           "This widget sets the System values for the Tabber Component named <b>".$args['instance']."</b>.<br />"
          ."That won't have any effect if the parameter has been overridden in the page or layout using that setting.";
        $content =
           $this->draw_param_row($ObjRC,$val_arr,'category_list','selector_listdata_csv',$val_width,40,$args['listtype'])
          .$this->draw_param_row($ObjRC,$val_arr,'category_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'content_char_limit','int',25)
          .$this->draw_param_row($ObjRC,$val_arr,'content_plaintext','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'content_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'date_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'extra_fields_list','textarea',$val_width,40)
          .$this->draw_param_row($ObjRC,$val_arr,'keywords_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'limit_per_category','int',20)
          .$this->draw_param_row($ObjRC,$val_arr,'related_show','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'sites_list','textarea',$val_width,40)
          .$this->draw_param_row($ObjRC,$val_arr,'sort_by','radio_csvlist','','','latest|latest')
          .$this->draw_param_row($ObjRC,$val_arr,'thumbnail_at_top','bool')
          .$this->draw_param_row($ObjRC,$val_arr,'thumbnail_image','radio_csvlist','','','s|s,m|m,l|l')
          .$this->draw_param_row($ObjRC,$val_arr,'thumbnail_width','int',25)
          ."<div class='clr_b'></div>"
          ."<input class='formButton' style='width:50px;' type='button'"
          ." onclick=\""
          ."this.disabled=true;geid_set('submode','save_".$this->_get_ID()."');"
          ."popup_layer_submit('".$this->url."');\""
          ." value='Save' />\n";
        $content = $this->draw_expander_heading($_msg!='',$content,$info,$_msg);
        return
          array(
            'html' =>   $content['html'],
            'js' =>     $content['js']."selector_csv_show(\"PWidgets_csv\");"
          );
      break;
    }
  }

  function widget_set_component_parameter($phase,$args,$_submode=''){
    global $msg;
    switch ($phase) {
      case 'setup':
        global $submode;
        switch ($submode){
          case 'save_'.$this->_get_ID():
            $value =    $_REQUEST[str_replace(".","_",$args['parameter'])."_".$this->_get_ID()];
            $Obj = new System(SYS_ID);
            $data =
              array(
                $args['parameter'] => $value
              );
            $Obj->set_parameters_for_instance($args['instance'],$data);
            $msg =
              "<b>Success:</b> Changes to the System for <b>".$args['instance'].":".$args['parameter']."</b> have been saved.";
          break;
          default:
            return "";
          break;
        }
      break;
      case 'display':
        $_msg = ($_submode=='save_'.$this->_get_ID() ? $msg : "");
        $Obj = new System(SYS_ID);
        $parameters = $Obj->get_field('component_parameters');
        $value = Component_Base::get_parameter_for_instance($args['instance'],$parameters,$args['parameter'],'');

        $Obj = new Report_Column();
        $info =
           "This widget sets the System value for the component parameter <b>".$args['instance'].":".$args['parameter']."</b>.<br />"
          ."That won't have any effect if the parameter has been overridden in the page or layout using that setting.";
        $content =
           "<div class='fl'>\n"
          .$Obj->draw_form_field('',$args['parameter']."_".$this->_get_ID(),$value,'selector_listdata_csv',(int)$this->width-85,'','','',0,0,'',$args['listtype'],60)
          ."</div>\n"
          ."<input class='formButton fr' style='width:50px;margin-right:1px' type='button'"
          ." onclick=\""
          ."geid_set('submode','save_".$this->_get_ID()."');"
          ."popup_layer_submit(base_url+'_popup_layer/dashboard','".$args['parameter']."_".$this->_get_ID()."='+geid_val('".$args['parameter']."_".$this->_get_ID()."'));\""
          ." value='Save' />\n";
        $content = $this->draw_expander_heading($_msg!='',$content,$info,$_msg);
        return
          array(
            'html' =>   $content['html'],
            'js' =>     $content['js']."selector_csv_show(\"".$args['parameter']."_".$this->_get_ID()."\");"
          );
      break;
    }
  }

  public function get_version(){
    return VERSION_WIDGET;
  }
}
?>