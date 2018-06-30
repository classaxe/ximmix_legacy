<?php
define('VERSION_COMMUNITY_COMPONENT_COLLECTION_VIEWER','1.0.0');
/*
Version History:
  1.0.0 (2012-11-15)
    1) New release - needed now that community is included as a class and not
       permanently loaded as a module
*/
class Community_Component_Collection_Viewer extends Component_Collection_Viewer{
  public function __construct(){
    parent::__construct();
    $this->_ident =             'community_collection_viewer';
    $this->_cm_podcast =        'module_cm_podcast';
    $this->_cm_podcastalbum =   'podcastalbum';
  }
  public function get_version(){
    return VERSION_COMMUNITY_COMPONENT_COLLECTION_VIEWER;
  }
}
?>