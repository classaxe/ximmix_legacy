<?php
define('VERSION_SURVEY_BLOCK','1.0.5');
/*
Version History:
  1.0.5 (2013-09-09)
    1) Changes to apply classnames to table cells for better css control

  (Older version history in class.survey_block.txt)
*/
class Survey_Block extends Posting {
  public function __construct($ID="",$systemID=SYS_ID) {
    parent::__construct($ID,$systemID);
    $this->_set_has_actions(false);
    $this->_set_has_groups(false);
    $this->_set_type('survey block');
    $this->_set_assign_type('survey block');
    $this->_set_object_name('Survey Block');
    $this->_set_message_associated('');
  }

  public function draw(&$question_num, $width, $show_numbers=1){
    switch($this->record['subtype']){
      case "":
        return $this->_draw_content();
      break;
      case "Question: Checkbox":
        return $this->_draw_question_checkbox($question_num, $width, $show_numbers);
      break;
      case "Question: Choose 1":
        return $this->_draw_question_radio($question_num, $width, $show_numbers);
      break;
      case "Question: Text":
        return $this->_draw_question_text($question_num, $width, $show_numbers);
      break;
      case "Question: Textarea":
        return $this->_draw_question_textarea($question_num, $width, $show_numbers);
      break;
    }
    return "Unknown Survey Block type ".$this->record['subtype'];
  }

  protected function _draw_content(){
    $content = nl2br($this->record['content']);
    return
       "  <tr class='survey_block_info'>\n"
      ."    <td colspan='4'><p>".$content."</p></td>\n"
      ."  </tr>\n";
  }

  protected function _draw_question_checkbox(&$question_num, $width, $show_numbers){
    $content = nl2br($this->record['content']);
    return
       "  <tr class='survey_block_question' onclick=\"geid('survey_block_".$this->record['ID']."').focus();\">\n"
      .($show_numbers ?
           "    <td class='s_number'><label for=\"survey_block_".$this->record['ID']."\">".$question_num++.")</label></td>\n"
         : ""
       )
      ."    <td class='s_question'>\n"
      ."      <label for=\"survey_block_".$this->record['ID']."\">".$this->record['title']."</label>\n"
      ."    </td>\n"
      ."    <td class='s_status".($this->record['status'] ? " req" : "")."'>\n"
      ."      ".($this->record['status'] ? "*" : "&nbsp;")."\n"
      ."    </td>\n"
      ."    <td class='s_options'>\n"
      ."      ".draw_form_field("survey_block_".$this->record['ID'],'','bool','','',0,'onclick="survey_test_'.$this->record['parentID'].'_required()" ')
      .$content."\n"
      ."    </td>\n"
      ."  </tr>\n";
  }

  protected function _draw_question_radio(&$question_num, $width, $show_numbers){
    $options =        $this->record['content'];
    $options_arr =    explode(',',$options);
    $first =          get_web_safe_ID($options_arr[0]);
    return
       "  <tr class='survey_block_question'>\n"
      .($show_numbers ?
           "    <td class='s_number'><label for=\"survey_block_".$this->record['ID']."\">".$question_num++.")</label></td>\n"
         : ""
       )
      ."    <td class='s_question'>\n"
      ."      <label>".$this->record['title']."</label>\n"
      ."    </td>\n"
      ."    <td class='s_status".($this->record['status'] ? " req" : "")."'>\n"
      ."      ".($this->record['status'] ? "*" : "&nbsp;")."\n"
      ."    </td>\n"
      ."    <td class='s_options'>\n"
      ."      "
      .str_replace(
         "</span>",
         "</span><br />",
         draw_form_field("survey_block_".$this->record['ID'],'','radio_csvlist',$width,'',0,'onclick="survey_test_'.$this->record['parentID'].'_required()" ',0,0,'',$options)
       )
      ."    </td>\n"
      ."  </tr>\n";
  }

  protected function _draw_question_text(&$question_num, $width, $show_numbers){
    return
       "  <tr class='survey_block_question' onclick=\"geid('survey_block_".$this->record['ID']."').focus();\">\n"
      .($show_numbers ?
           "    <td class='s_number'><label for=\"survey_block_".$this->record['ID']."\">".$question_num++.")</label></td>\n"
         : ""
       )
      ."    <td class='s_question'>\n"
      ."      <label for=\"survey_block_".$this->record['ID']."\">".$this->record['title']."</label>\n"
      ."    </td>\n"
      ."    <td class='s_status".($this->record['status'] ? " req" : "")."'>\n"
      ."      ".($this->record['status'] ? "*" : "&nbsp;")."\n"
      ."    </td>\n"
      ."    <td class='s_options'>\n"
      ."      ".draw_form_field("survey_block_".$this->record['ID'],$this->record['content'],'text',$width,'','','onchange="survey_test_'.$this->record['parentID'].'_required()" ')."\n"
      ."    </td>\n"
      ."  </tr>\n";
  }

  protected function _draw_question_textarea(&$question_num, $width, $show_numbers){
    return
       "  <tr class='survey_block_question' onclick=\"geid('survey_block_".$this->record['ID']."').focus();\">\n"
      .($show_numbers ?
           "    <td class='s_number'><label for=\"survey_block_".$this->record['ID']."\">".$question_num++.")</label></td>\n"
         : ""
       )
      ."    <td class='s_question'>\n"
      ."      <label for=\"survey_block_".$this->record['ID']."\">".$this->record['title']."</label>\n"
      ."    </td>\n"
      ."    <td class='s_status".($this->record['status'] ? " req" : "")."'>\n"
      ."      ".($this->record['status'] ? "*" : "&nbsp;")."\n"
      ."    </td>\n"
      ."    <td class='s_options'>\n"
      ."      ".draw_form_field("survey_block_".$this->record['ID'],$this->record['content'],'textarea',$width,'','','onchange="survey_test_'.$this->record['parentID'].'_required()" ')."\n"
      ."    </td>\n"
      ."  </tr>\n";
  }

  public function get_version(){
    return VERSION_SURVEY_BLOCK;
  }
}
?>