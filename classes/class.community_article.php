<?php
define('COMMUNITY_ARTICLE','1.0.2');
/*
Version History:
  1.0.2 (2013-12-14)
    1) Added override for report form to use for 'Add New...' icon

  (Older version history in class.community_article.txt)
*/


class Community_Article extends Article{
  public function __construct(){
    parent::__construct();
    $this->_set_context_menu_ID('module_cm_article');
    $this->_show_latest_for_each_member = false;
    $this->_set_edit_param('report','community_member.articles');
  }

  protected function _get_records_get_sql(){
    return Community_Posting::_get_records_get_sql($this);
  }

  protected function BL_shared_source_link(){
    return Community_Posting::BL_shared_source_link($this,'#articles');
  }

  protected function BL_category(){
    return Community_Posting::BL_category();
  }

  public function get_version(){
    return COMMUNITY_ARTICLE;
  }
}

?>