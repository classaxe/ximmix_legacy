<?php
define ("VERSION_POSTING_CONTAINER","1.0.4");
/*
Version History:
  1.0.4 (2012-11-21)
    1) Now Posting_Container::set_default_enclosure_folder() checks the parent
       folder if there is one, and then builds default folder based on parent
       path combined with the name of the object represented.
       Also checks that the path is properly prefixed ad suffixed with a slash

  (Older version history in class.posting_container.txt)
*/

class Posting_Container extends Posting_Contained {
  public function __construct($ID='',$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
  }

  function get_path($ID,$path=''){
    if ($ID==0) {
      // 'No Parent' specified - get out now
      return $path;
    }
    $sql =
       "SELECT\n"
      ."  `name`,\n"
      ."  `parentID`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `ID` = ".$ID;
    if (!$record =   $this->get_record_for_sql($sql)) {
      // Parent doesn't exist - move up tree
      return $path;
    }
    // Okay, continue...
    $name =     $record['name'];
    $parentID = $record['parentID'];
    $path =     $name."/".$path;  // Add new piece to front of path
    return      $this->get_path($parentID,$path);
  }

  public function get_selector_sql(){
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    if ($isMASTERADMIN){
      return
         "SELECT\n"
        ."  '' `value`,\n"
        ."  '(None)' `text`,\n"
        ."  'd0d0d0' `color_background`\n"
        ."UNION SELECT\n"
        ."  `ID` `value`,\n"
        ."  CONCAT(\n"
        ."    IF(\n"
        ."       `postings`.`systemID` = 1,\n"
        ."      '* ',\n"
        ."      CONCAT(\n"
        ."        (SELECT\n"
        ."           UPPER(`system`.`textEnglish`)\n"
        ."         FROM\n"
        ."           `system`\n"
        ."         WHERE\n"
        ."           `system`.`ID` = `postings`.`systemID`\n"
        ."         ),\n"
        ."        ' | '\n"
        ."      )\n"
        ."    ),\n"
        ."    REPLACE(\n"
        ."      REPLACE(\n"
        ."        `path`,\n"
        ."        '//',\n"
        ."        ''\n"
        ."      ),\n"
        ."      '/',\n"
        ."      ' / '\n"
        ."    )\n"
        ."  ) `text`,\n"
        ."  IF(`systemID`!=".SYS_ID.",'ffe8e8','e0ffe0') `color_background`\n"
        ."FROM\n"
        ."  `postings`\n"
        ."WHERE\n"
        ."  `ID` NOT IN(_ID_) AND\n"
        ."  `type` = '".$this->_get_type()."'\n"
        ."ORDER BY\n"
        ."  `text`";
    }
    return
       "SELECT\n"
      ."  '' `value`,\n"
      ."  '(None)' `text`,\n"
      ."  'd0d0d0' `color_background`\n"
      ."UNION SELECT\n"
      ."  `ID`,\n"
      ."  REPLACE(\n"
      ."    REPLACE(\n"
      ."      `path`,\n"
      ."      '//',\n"
      ."      ''\n"
      ."    ),\n"
      ."    '/',\n"
      ."    ' / '\n"
      ."  ),\n"
      ."  IF(`systemID`!=".SYS_ID.",'ffe8e8','e0ffe0')\n"
      ."FROM\n"
      ."  `postings`\n"
      ."WHERE\n"
      ."  `ID` NOT IN(_ID_) AND\n"
      ."  `systemID` IN(1,".SYS_ID.") AND\n"
      ."  `type` = '".$this->_get_type()."'\n"
      ."ORDER BY\n"
      ."  `text`;";
  }

  public function on_action_set_default_enclosure_folder(){
    global $action_parameters;
    $type =     $action_parameters['triggerObject'];
    $ID =       $action_parameters['triggerID'];
    $ID_arr =   explode(',',$ID);
    foreach($ID_arr as $ID){
      $Obj =      new $type($ID);
      $Obj->set_default_enclosure_folder();
    }
  }
  public function set_default_enclosure_folder(){
    $this->load();
    if ($this->record['enclosure_url']){
      $path = '/'.trim($this->record['enclosure_url'],'/').'/';
      if ($this->record['enclosure_url']==$path){
        return;
      }
      $this->set_field('enclosure_url',$path,true,false);
      return;
    }
    $container_type =   $this->_get_container_object_type();
    $parentID =         $this->record['parentID'];
    $Obj =              new $container_type($parentID);
    $parent_base =      $Obj->get_field('enclosure_url');
    $default_base =     $Obj->_get_default_enclosure_base_folder();
    $folder =
      ($parent_base ?
          $parent_base.$this->record['name']
       :
          $default_base.$this->_get_ID()
       ).'/';
    $this->set_field('enclosure_url',$folder,true);
  }

  public function set_path(){
    $path = "//".trim($this->get_path($this->_get_ID()),'/');
    $this->set_field('path',$path);
  }


  public function get_version(){
    return VERSION_POSTING_CONTAINER;
  }
}

?>