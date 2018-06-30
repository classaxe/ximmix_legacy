<?php
define ("VERSION_COMPONENT_LANGUAGE_BUTTON","1.0.1");
/*
Version History:
  1.0.1 (2012-12-08)
    1) French c-cedilla charcacter now defined as HTML entity
  1.0.0 (2012-12-08)
    1) Initial released - moved here from HTML::draw_icon()

*/
class Component_Language_Button extends Component_Base {
  protected $_html =    '';

  public function __construct(){
    $this->_ident =             "draw_language_button";
    $this->_parameter_spec =    array(
      'cancel' =>       array('default'=>'Cancel',                              'hint'=>'Title for Cancel button in dialog'),
      'ok' =>           array('default'=>'OK',                                  'hint'=>'Title for OK button in dialog'),
      'disable' =>      array('default'=>'0',                                   'hint'=>'0|1'),
      'languages' =>    array('default'=>'en|gb|English,fr|fr|Fran&ccedil;ais', 'hint'=>'CSV list - language|flag|label'),
      'title' =>        array('default'=>'Language Chooser',                    'hint'=>'Title for icon and for dialog'),
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false){
    $this->_setup($instance,$args,$disable_params);
    $this->_draw();
    return $this->_html;
  }

  protected function _draw(){
    $this->_draw_control_panel(true);
    if (!$this->_cp['disable']) {
      $this->_html.=
         "<span class='noprint' title='Click to change language'"
        ." onmouseover=\"geid('icon_language').style.backgroundPosition='-4982px 0px';window.status='Click to change language';return true;\""
        ." onmouseout=\"geid('icon_language').style.backgroundPosition='-4966px 0px';window.status='';return true;\""
        .">"
        ."<a href=\"#\" onclick=\"language_chooser("
        ."'".htmlentities($this->_cp['languages'])."','".htmlentities($this->_cp['title'])."',"
        ."'".htmlentities($this->_cp['ok'])."','".htmlentities($this->_cp['cancel'])."');return false;\">"
        ."<img id='icon_language' src='".BASE_PATH."img/spacer' class='toolbar_icon'"
        ." style='width:16px;background-position:-4966px 0px;' alt='Language Chooser'/></a>"
        ."</span>";
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_LANGUAGE_BUTTON;
  }

}
?>