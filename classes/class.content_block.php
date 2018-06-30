<?php
define('VERSION_CONTENT_BLOCK','1.0.9');
/*
Version History:
  1.0.9 (2013-06-27)
    1) Moved displaying of content_blocks as ECL tags into own dedicated class
  (Older version history in class.content_block.txt)
*/
class Content_Block extends Record{
  protected $_record;
  static $style="";

  function __construct($ID="") {
    parent::__construct("content_block",$ID);
    $this->_set_object_name("Content Block");
    $this->set_edit_params(
      array(
        'report' =>                 'content_block',
        'report_rename' =>          true,
        'report_rename_label' =>    'new name'
      )
    );
  }

  function export_sql($targetID,$show_fields) {
    return  $this->sql_export($targetID,$show_fields);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function get_version(){
    return VERSION_CONTENT_BLOCK;
  }
}
?>