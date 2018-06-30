<?php
define('VERSION_KEYWORD_ASSIGN','1.0.2');
/*
Version History:
  1.0.2 (2010-10-19)
    1) Keyword_Assign::set_for_assignment() now calls insert() method 
  1.0.1 (2010-10-04)
    1) Changes to setter and getter names for parent-based object properties
  1.0.0 (2009-07-02)
    Initial release
*/
class Keyword_Assign extends Record {
  function __construct($ID=""){
    parent::__construct("keyword_assign",$ID);
    $this->_set_object_name('Keyword Assign');
  }

  function delete_for_assignment($assign_type,$assignID) {
    $sql =
       "DELETE FROM\n"
      ."  `keyword_assign`\n"
      ."WHERE\n"
      ."  `assign_type` = \"".$assign_type."\" AND\n"
      ."  `assignID` IN(".$assignID.")";
//    z($sql);
    $this->do_sql_query($sql);
  }

  function set_for_assignment($assign_type,$assignID,$keywords_csv,$systemID) {
    $this->delete_for_assignment($assign_type,$assignID);
    if (!$keywords_csv) {
      return;
    }
    $Obj = new Keyword;
    $keywords_arr = explode(",",str_replace(", ",",",$keywords_csv));
    foreach ($keywords_arr as $keyword) {
      $keyword_arr = explode('[',trim($keyword));
      $keyword = $keyword_arr[0];
      $weight =  (isset($keyword_arr[1]) ? (int)$keyword_arr[1] : 0);
      $keywordID = $Obj->get_ID_by_name($keyword);
      if ($keywordID) {
        $data =
          array(
            'assign_type' => $assign_type,
            'assignID' =>    $assignID,
            'keywordID' =>   $keywordID,
            'weight' =>      $weight,
            'systemID' =>    $systemID
          );
        $this->insert($data);
      }
    }
  }
  public function get_version(){
    return VERSION_KEYWORD_ASSIGN;
  }
}
?>