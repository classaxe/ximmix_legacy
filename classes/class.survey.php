<?php
define('VERSION_SURVEY','1.0.17');
/*
Version History:
  1.0.17 (2013-03-08)
    1) Changes to standard CPs to include options such as showing title or date

  (Older version history in class.survey.txt)
*/
class Survey extends Posting {
  protected $_blocks = false;

  public function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_has_actions(false);
    $this->_set_has_groups(true);
    $this->_set_type('survey');
    $this->_set_assign_type('survey');
    $this->_set_object_name('Survey');
    $this->_set_message_associated('and defined Blocks and Responses have');
    $this->set_edit_params(
      array(
        'report' =>                 'survey',
        'report_rename' =>          true,
        'report_rename_label' =>    'new title',
        'icon_edit' =>              '[ICON]14 14 3795 Edit this Survey[/ICON]',
        'icon_edit_disabled' =>     '[ICON]14 14 3804 (Edit this Survey)[/ICON]',
        'icon_edit_popup' =>        '[ICON]18 18 3823 Edit this Survey in a popup window[/ICON]'
      )
    );
    $this->_cp_vars_detail = array(
      'block_layout' =>             array('match' => '',            'default'=>'Survey',    'hint'=>'Name of Block Layout to use'),
      'category_show' =>            array('match' => 'enum|0,1',    'default' =>'1',            'hint'=>'0|1'),
      'comments_show' =>            array('match' => 'enum|0,1',    'default' =>'1',            'hint'=>'0|1'),
      'comments_link_show' =>       array('match' => 'enum|0,1',    'default' =>'1',            'hint'=>'0|1'),
      'date_show' =>                array('match' => 'enum|0,1',    'default' =>'1',            'hint'=>'0|1'),
      'extra_fields_list' =>        array('match' => '',            'default'=>'',          'hint'=>'CSV list format: field|label|group,field|label|group...'),
      'field_width' =>              array('match' => 'range|1,n',   'default' =>'150',      'hint'=>'|1..n - width in px for fields'),
      'item_footer_component' =>    array('match' => '',            'default'=>'',          'hint'=>'Name of component rendered below displayed New Item'),
      'numbers_show' =>             array('match' => 'enum|0,1',    'default' =>'1',        'hint'=>'0|1'),
      'title_linked' =>             array('match' => 'enum|0,1',    'default' =>'1',            'hint'=>'0|1'),
      'title_show' =>               array('match' => 'enum|0,1',    'default' =>'1',            'hint'=>'0|1')
    );
    $this->_cp_vars_listings = array(
      // Not implemented yet
    );
  }

  public function _do_submission(){
    $answers = array();
    $blocks = $this->get_blocks();
    foreach ($blocks as $block){
      switch($block['subtype']){
        case 'Question: Checkbox':
        case 'Question: Choose 1':
        case 'Question: Text':
          $value =  (isset($_REQUEST['survey_block_'.$block['ID']]) ? $_REQUEST['survey_block_'.$block['ID']] : "");
          $field =  htmlentities($block['name']);
          $answers[$field] = htmlentities($value);
        break;
      }
    }
    $max_len =      0;
    $content =      "";
    foreach($answers as $field=>$value){
      if (strlen($field)>$max_len){
        $max_len = strlen($field);
      }
    }
    foreach($answers as $field=>$value){
      $content.=pad($field." = ",$max_len+3).$value."\n";
    }
    $ObjSR = new Survey_Response;
    $data = array(
      'type' =>     'survey response',
      'enabled' =>  1,
      'parentID' => $this->_get_ID(),
      'content' =>  $content
    );
    $ObjSR->insert($data);
    $expires = time()+60*60*24*90; // 90 days
    setcookie("survey_".$this->_get_ID()."_submitted",1,$expires,'/','',0);
    return "Thank you for your submission.";
  }

  private function _draw_submit(){
    $required = array();
//    y($this->blocks);die;
    foreach($this->blocks as $block){
      switch ($block['subtype']){
        case 'Question: Checkbox':
        case 'Question: Choose 1':
        case 'Question: Text':
        case 'Question: Textarea':
          if ($block['status']){
            $required[] = "'survey_block_".$block['ID']."'";
          }
        break;
      }
    }
    if (count($required)){
      Page::Push_Content(
         'javascript',
          "function survey_test_".$this->_get_ID()."_required(){\n"
         ."  var disabled = false;\n"
         ."  var req = [\n"
         ."    ".implode(",\n    ",$required)."\n"
         ."  ];\n"
         ."  for (var i=0; i<req.length; i++){\n"
         ."    if (!geid_val(req[i])){\n"
         ."      disabled=true;\n"
         ."      break;\n"
         ."    }\n"
         ."  }\n"
         ."  geid('survey_submit_".$this->_get_ID()."').disabled = disabled;\n"
         ."}\n"
      );
    }
    return
       "  <tr>\n"
      ."    <td colspan='4' class='txt_c'>"
      ."<input id='survey_submit_".$this->_get_ID()."' type='submit'"
      ." onclick=\"geid_set('submode','survey_submit_".$this->_get_ID()."');\""
      ." value=\"Submit\""
      .(count($required)? " disabled=\"disabled\"" : "")
      ." />\n"
      ."    </td>\n"
      ."  </tr>\n";
  }
  protected function BL_survey_blocks(){
    $submitted = (isset($_COOKIE['survey_'.$this->_get_ID().'_submitted']) ? 1 : 0);
    if ($submitted){
      return "<h1>You have already completed this survey</h1>";
    }
    $this->blocks = $this->get_blocks();
//    y($this->blocks);
    $question_num = 1;
    $width =        $this->_cp['field_width'];
    $show_numbers = $this->_cp['numbers_show'];
    $out = "<table class='survey_blocks' cellpadding='0' cellspacing='0'>\n";
    $ObjSB = new Survey_Block;
    foreach ($this->blocks as $record){
      $ObjSB->_set_ID($record['ID']);
      $ObjSB->load($record);
      $out.= $ObjSB->draw($question_num,$width,$show_numbers);
    }
    $out.= $this->_draw_submit();
    $out.= "</table>";
    return $out;
  }

  function copy($new_name=false,$new_systemID=false,$new_date=true) {
    $newID =    parent::copy($new_name,$new_systemID,$new_date);
    $Obj =      new Survey_Block;
    $blocks =   $this->get_blocks();
    foreach ($blocks as $data) {
      unset($data['ID']);
      unset($data['archive']);
      unset($data['archiveID']);
      if ($new_date){
        unset($data['history_created_by']);
        unset($data['history_created_date']);
        unset($data['history_created_IP']);
        unset($data['history_modified_by']);
        unset($data['history_modified_date']);
        unset($data['history_modified_IP']);
      }
      if ($new_systemID) {
        $data['systemID'] = $new_systemID;
      }
      $data['parentID'] =               $newID;
      $data['content'] =                addslashes($data['content']);
      $Obj->insert($data);
    }
    return $newID;
  }

  function delete() {
    $sql =
       "DELETE FROM\n"
      ."  `postings`\n"
      ."WHERE\n"
      ." `type` = 'survey block' AND `parentID` IN(".$this->_get_ID().")";
    $this->do_sql_query($sql);
    parent::delete();
  }

  public function draw_detail(){
    $submode=(isset($_REQUEST['submode']) ? $_REQUEST['submode'] : '');
    switch ($submode){
      case "survey_submit_".$this->_get_ID():
        return $this->_do_submission();
      break;
    }
    return parent::draw_detail();
  }

  function export_sql($targetID,$show_fields){
    $header =       "Selected ".$this->_get_object_name().$this->plural($targetID)." with Blocks and Responses";
    $extra_delete =
       "DELETE FROM `postings`               WHERE `type` = 'survey block' AND `parentID` IN (".$targetID.");\n"
      ."DELETE FROM `postings`               WHERE `type` = 'survey response' AND `parentID` IN (".$targetID.");\n";
    $Obj = new Backup;
    $extra_select =
       $Obj->db_export_sql_query("`postings`              ","SELECT * FROM `postings` WHERE `type` = 'survey block' AND `parentID` IN (".$targetID.") ORDER BY `parentID`,`seq`",$show_fields)
      .$Obj->db_export_sql_query("`postings`              ","SELECT * FROM `postings` WHERE `type` = 'survey response' AND `parentID` IN (".$targetID.") ORDER BY `parentID`,`seq`",$show_fields);
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_blocks(){
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `postings`\n"
      ."WHERE\n"
      ."  `type` = 'survey block' AND\n"
      ."  `parentID` IN(".$this->_get_ID().")\n"
      ."ORDER BY\n"
      ."  `seq`,`history_created_date`";
    return $this->get_records_for_sql($sql);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip,$name);
  }

  public function manage_blocks(){
    if (get_var('command')=='report'){
      return draw_auto_report('survey_blocks_for_survey',1);
    }
    $out = "<h3 style='margin:0.25em'>Blocks belonging to this ".$this->_get_object_name()."</h3>";
    if (!get_var('selectID')) {
      $out.="<p style='margin:0.25em'>No associated Survey Blocks - this ".$this->_get_object_name()." has not been saved yet.</p>";
    }
    else {
      $out.= draw_auto_report('survey_blocks_for_survey',1);
    }
    return $out;
  }

  public function manage_responses(){
    if (get_var('command')=='report'){
      return draw_auto_report('survey_responses_for_survey',1);
    }
    $out = "<h3 style='margin:0.25em'>Responses to this ".$this->_get_object_name()."</h3>";
    if (!get_var('selectID')) {
      $out.="<p style='margin:0.25em'>No associated responses - this ".$this->_get_object_name()." has not been saved yet.</p>";
    }
    else {
      $out.= draw_auto_report('survey_responses_for_survey',1);
    }
    return $out;
  }

  public function get_version(){
    return VERSION_SURVEY;
  }
}
?>