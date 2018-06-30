<?php
define ("VERSION_COMPONENT_JUMPLOADER","1.0.4");
/*
Version History:
  1.0.4 (2011-03-30)
    1) Added Component_Jumploader::_draw_uploaded_files_table()
  1.0.3 (2011-03-25)
    1) Split into more mini functions to allow easier override
  1.0.2 (2011-03-23)
    1) Added new cp 'mode' to allow either embedded or framed (popup) mode
  1.0.1 (2010-12-09)
    1) Initial release
*/
class Component_Jumploader extends Component_Base {
  protected $_cp;
  protected $_args;
  protected $_cp_are_fixed;
  protected $_html;
  protected $_ident;
  protected $_instance;
  protected $_safe_ID;
  protected $_parameter_spec;

  function draw($instance='', $args=array(), $cp_are_fixed=false) {
    $this->_ident = "jumploader_uploader";
    $this->_draw_setup($instance, $args, $cp_are_fixed);
    $this->_draw_jumploader();
    return $this->_html;
  }

  protected function _draw_jumploader(){
    $this->_html.=
       HTML::draw_status('form_edit_inpage',$this->_msg)
      .$this->_Obj_JL->draw();
  }

  protected function _draw_uploaded_files_table(){
    $this->_html.=
       "<table class='form_view' style='width:100%'>\n"
      ."  <tr class='head'>\n"
      ."    <th>Filename</th>\n"
      ."    <th>Type</th>\n"
      ."    <th>Size (bytes)</th>\n"
      ."    <th>Status</th>\n"
      ."  </tr>\n";
    foreach($this->_upload_status as $file){
      $this->_html.=
         "  <tr>\n"
        ."    <td>"
        ."<a rel='external' href=\"".$file['path']."\">"
        ."<img src=\"".get_icon_for_extension($file['extension'])."\""
        ." class=\"file_icon\" alt=\".".$file['extension']." file\" /></a>"
        ."<a rel='external' href=\"".$file['path']."\">"
        .$file['filename']."</a></td>\n"
        ."    <td class='txt_r'>".$file['extension']."</td>\n"
        ."    <td class='txt_r'>".number_format ($file['size'])."</td>\n"
        ."    <td>".$file['message']."</td>\n"
        ."  </tr>\n";
    }
    $this->_html.=
       "</table>\n";
  }

  protected function _draw_setup($instance, $args, $cp_are_fixed){
    $this->_draw_setup_args($instance, $args, $cp_are_fixed);
    $this->_draw_setup_load_parameters();
    $this->_draw_setup_jumploader_init();
    $this->_draw_setup_jumploader_upload();
  }

  protected function _draw_setup_jumploader_init(){
    $this->_Obj_JL = new Jumploader;
    $this->_Obj_JL->init(
      $this->_safe_ID,
      $this->_cp['width'],
      $this->_cp['height'],
      $this->_cp['mode'],
      $this->_cp['type'],
      $this->_cp['ext'],
      $this->_cp['show_summary']
    );
  }

  protected function _draw_setup_jumploader_upload(){
    $this->_Obj_JL->files_uploader(BASE_PATH.trim($this->_cp['folder'],'/'));
    $this->_msg =   "";
    $this->_upload_status =    $this->_Obj_JL->get_status();
    $this->_upload_count =     $this->_Obj_JL->get_uploaded_count();
    $this->_Obj_JL->clear_status();
    if ($this->_upload_count){
      $this->_msg = "<b>Success:</b> Uploaded ".$this->_upload_count." file".($this->_upload_count==1? '' : 's');
    }
  }

  protected function _draw_setup_args($instance='', $args=array(), $cp_are_fixed=false){
    $this->_args =              $args;
    $this->_instance =          $instance;
    $this->_cp_are_fixed =      $cp_are_fixed;
    $this->_safe_ID =           Component_Base::get_safe_ID($this->_ident, $this->_instance);
  }

  protected function _draw_setup_load_parameters(){
    $this->_parameter_spec = array(
      'ext' =>              array('match' => '',                                    'default'=>'csv,xls,xlsx',      'hint'=>'CSV list of acceptable file extensions'),
      'folder' =>           array('match' => '',                                    'default'=>'/UserFiles/File',   'hint'=>'Folder in which to place uploaded items'),
      'height' =>           array('match' => '',                                    'default'=>320,                 'hint'=>'Height in px'),
      'mode' =>             array('match' => 'enum|embedded,framed',                'default'=>'embedded',          'hint'=>'embedded|framed'),
      'show_summary' =>     array('match' => 'enum|0,1',                            'default'=>'0',                 'hint'=>'0|1'),
      'type' =>             array('match' => 'enum|file,flash,image,media,video',   'default'=>'file',              'hint'=>'file|flash|image|media|video'),
      'width' =>            array('match' => '',                                    'default'=>480,                 'hint'=>'Width in px')
    );
    $settings =
      Component_Base::get_parameter_defaults_and_values(
        $this->_ident, $this->_instance, $this->_cp_are_fixed, $this->_parameter_spec, $this->_args
      );
    $this->_cp_defaults =   $settings['defaults'];
    $this->_cp =            $settings['parameters'];
    $this->_html.=          Component_Base::get_help($this->_ident, $this->_instance, false, $this->_parameter_spec, $this->_cp_defaults);
    if ($this->_html){
      $this->_html.= "<br />\n";
    }
  }

  public function get_version(){
    return VERSION_COMPONENT_JUMPLOADER;
  }

}
?>