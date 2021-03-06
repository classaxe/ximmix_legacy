<?php
define('COMMUNITY_MEMBER_ARTICLE_VERSION','1.0.2');
/*
Custom Fields used:
custom_1 = denomination (must be as used in other SQL-based controls)
*/
/*
Version History:
  1.0.2 (2013-12-14)
    1) Added override for report form to use for 'Add New...' icon

  (Older version history in class.community_member_article.txt)
*/

class Community_Member_Article extends Article{
  public function __construct(){
    parent::__construct();
    $this->_set_object_name('Community Member Article');
    $this->_set_context_menu_ID('module_cm_article');
    $this->_set_edit_param('report','community_member.articles');
  }

  protected function _get_records_get_sql(){
    return Community_Member_Posting::_get_records_get_sql($this);
  }

  protected function BL_category(){
    return Community_Posting::BL_category();
  }

  protected function BL_shared_source_link(){
    return Community_Member_Posting::BL_shared_source_link($this);
  }

  public function get_version(){
    return COMMUNITY_MEMBER_ARTICLE_VERSION;
  }
}
?>