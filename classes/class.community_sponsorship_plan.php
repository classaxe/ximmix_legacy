<?php
define('VERSION_COMMUNITY_SPONSORSHIP_PLAN','1.0.2');
/*
Version History:
  1.0.2 (2012-11-23)
    1) Changes to Community_Sponsorship_Plan::set_container_path() to allow
       container album to be changed to something other than the one given in
       the community record and to remap the path based on such a change
  1.0.1 (2012-11-19)
    1) More work to implement automatic assignment of file folders for each
       community sponsorship plan
  1.0.0 (2012-11-19)
    1) Initial release - incomplete as yet

*/
class Community_Sponsorship_Plan extends Sponsorship_Plan {
  public function __construct($ID='',$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_object_name('Community Sponsorship Plan');
  }

  public function set_container_path(){
    $this->load();
    $parentID = $this->record['parentID'];
    if ($parentID==0){
      if (!$this->record['communityID']){
        return;
      }
      $Obj_Community = new Community($this->record['communityID']);
      $parentID = $Obj_Community->get_field('sponsorship_gallery_albumID');
    }
    $this->set_field('parentID',$parentID,true,false);
    parent::set_container_path();
  }

  public function get_version(){
    return VERSION_COMMUNITY_SPONSORSHIP_PLAN;
  }
}
?>