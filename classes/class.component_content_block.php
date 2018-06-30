<?php
define ("VERSION_COMPONENT_CONTENT_BLOCK","1.0.0");
/*
Version History:
  1.0.0 (2013-06-27)
    1) Initial release

*/
class Component_Content_Block extends Component_Base {
  protected $_record;
  static $style="";

  public function __construct(){
    $this->_ident = "draw_content_block";
    $this->_parameter_spec =    array(
      'name' =>                     array('match' => '',                'default' =>'',             'hint'=>'Name of content block to insert')
    );
  }

  function draw($instance='', $args=array(), $disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_css();
    $this->_draw_control_panel();
    $this->_draw_content_block();
    return $this->_render();
  }

  protected function _draw_css(){
    if ($this->_record['style'] && preg_replace('/\s+/','',$this->_record['style'])!='.content_block_'.$this->_record['name'].'{}') {
      Page::push_content('style',
         "/* [Content Block: ".$this->_cp['name']."] */\r\n"
        .$this->_record['style']
        ."\r\n"
      );
    }
  }

  protected function _draw_content_block(){
//    y($this->_current_user_rights);die;
    $canEdit =
      $this->_ID &&
      $this->_current_user_rights['canEdit'] &&
      ($this->_record['systemID']==SYS_ID || $this->_current_user_rights['isMASTERADMIN']);
    $this->_html.=
      "<div"
      .($canEdit ?
           " title='".$this->_tooltip."' "
          ."onmouseover=\""
          ."if(!CM_visible('CM_content_block')) {"
          ."window.status='".$this->_tooltip."';"
          ."this.style.backgroundColor='"
          .($this->_record['systemID']==SYS_ID ? '#80ff80' : '#ffe0e0')
          ."';"
          ."_CM.type='content_block';"
          ."_CM.ID='".$this->_ID."';"
          ."_CM_text[0]='&quot;".$this->_cp['name']."&quot;';"
          ."};return false;\" "
          ."onmouseout=\""
          ."if(!CM_visible('CM_event')){"
          ."this.style.backgroundColor=''"
          ."};"
          ."window.status='';"
          ."_CM.type='';return false;\">"
        : ">"
       )
       .($this->_ID ?
          ($this->_current_user_rights['canEdit'] && $this->_record['content']=='' ?
             "<span style='background-color:#ffffd0;'>Content Block ".$this->_instance." -<br /><b>\"".$this->_cp['name']."\"</b> is empty...<br /><i>Right-click to edit</i></span>"
           : $this->_record['content']
          )
        :
          ($this->_current_user_rights['canEdit'] ?
              "<a href=\"".BASE_PATH."details/".$this->_form."/\" style='text-decoration:none;color:#0000ff;font-weight:bold;'"
             ." onclick=\"details('".$this->_form."','','".$this->_popup['h']."','".$this->_popup['w']."','','','','name=".$this->_instance."&amp;style=.content_block_home-contact {   }');return false;\""
             .">"
             ."[ICON]11 11 1188 Create new ".$this->_get_object_name()."[/ICON]\n"
             ."&nbsp;Create new ".$this->_get_object_name()." \"".$this->_instance."\"</a>"
           :
             ""
          )
        )
       ."</div>\n";
  }

  protected function _render(){
    return
       "<div style='zoom:100%' id=\"content_block_".$this->_instance."\" class=\"content_block_".$this->_record['name']."\">\n"
      .$this->_html
      ."</div>\n";
  }

  protected function _setup($instance, $args, $disable_params){
    $this->_parameter_spec['name']['default'] = $instance;
    parent::_setup($instance, $args, $disable_params);
    $this->_setup_load_user_rights();
    $this->_setup_load_record();
    $this->_setup_load_edit_parameters();
  }

  protected function _setup_load_edit_parameters(){
    if ($this->_current_user_rights['canEdit']) {
      $edit_params =        $this->_Obj->get_edit_params();
      $this->_form =        $edit_params['report'];
      $this->_popup =       get_popup_size($this->_form);
      $this->_tooltip =     "Right-click to edit &quot;".$this->_instance."&quot;";
    }
  }

  protected function _setup_load_record(){
    $this->_Obj =       new Content_Block;
    $this->_ID =        $this->_Obj->get_ID_by_name($this->_cp['name'],'1,'.SYS_ID);
    $this->_Obj->_set_ID($this->_ID);
    $this->_record =    $this->_Obj->load();
  }

  public function get_version(){
    return VERSION_COMPONENT_CONTENT_BLOCK;
  }
}
?>