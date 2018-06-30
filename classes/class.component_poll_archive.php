<?php
  define ("VERSION_COMPONENT_POLL_ARCHIVE","1.0.1");
/*
Version History:
  1.0.1 (2012-11-03)
    1) Big changes to allow for use of standard methods for setup and control panel
       and refactoring to reduce stament nesting
    2) Changes to deal with fields being renamed in poll_choice table in preparation
       for moving to postings table
  1.0.0 (2011-12-31)
    1) Initial release - moved from Component class
*/
class Component_Poll_Archive extends Component_Base {
  protected $_Obj_Poll;
  protected $_polls;

  public function __construct(){
    $this->_ident =             'poll_archive';
    $this->_parameter_spec = array(
      'choice_indent' =>            array('match' => 'range|0,n',               'default'=>'100',         'hint'=>'Width in pixels to indent choices'),
      'choice_width' =>             array('match' => 'range|0,n',               'default'=>'250',         'hint'=>'Width in pixels to use for choices'),
      'colour_choice_loser' =>      array('match' => 'hex3|#808080',            'default'=>'#808080',     'hint'=>'Hex colour code for winning choice'),
      'colour_choice_winner' =>     array('match' => 'hex3|#404040',            'default'=>'#404040',     'hint'=>'Hex colour code for non-winning choice'),
      'colour_question_active' =>   array('match' => 'hex3|#000000',            'default'=>'#000000',     'hint'=>'Hex colour code for active question and date'),
      'colour_question_inactive' => array('match' => 'hex3|#404040',            'default'=>'#404040',     'hint'=>'Hex colour code for inactive question and date'),
      'date_width' =>               array('match' => 'range|0,n',               'default'=>'100',         'hint'=>'Width in pixels to use for date'),
      'include_active' =>           array('match' => 'enum|0,1',                'default'=>'0',           'hint'=>'0|1'),
      'include_unanswered' =>       array('match' => 'enum|0,1',	            'default'=>'0',           'hint'=>'0|1'),
      'question_width' =>           array('match' => 'range|0,n',               'default'=>'400',         'hint'=>'Width in pixels to use for question'),
      'separator_colour' =>         array('match' => 'hex3|#404040',            'default'=>'#404040',     'hint'=>'Hex colour code for question separators'),
      'separator_spacing' =>        array('match' => 'range|0,n',               'default'=>'20',           'hint'=>'Distance in pixels to separate each question'),
      'separator_thickness' =>      array('match' => 'range|0,n',               'default'=>'1',           'hint'=>'Height of separator line in pixels'),
      'show_date' =>                array('match' => 'enum|0,1',                'default'=>'1',           'hint'=>'0|1'),
      'show_percent' =>             array('match' => 'enum|0,1',                'default'=>'1',           'hint'=>'0|1'),
      'show_votes' =>               array('match' => 'enum|0,1',                'default'=>'1',           'hint'=>'0|1')
    );
  }

  function draw($instance='',$args=array(),$disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_draw_css();
    if (!$this->_polls){
      $this->_html.= "  <p>There are no polls available to view right now.</p>";
      return $this->_render();
    }
    foreach ($this->_polls as $poll){
      $this->_draw_poll($poll);
    }
    return $this->_render();
  }

  protected function _draw_css(){
    $css =
       ".poll_archives {}\n"
      ."  .poll_archive_active       { color:".$this->_cp['colour_question_active']."; padding: 10px 0; }\n"
      ."  .poll_archive_inactive     { color:".$this->_cp['colour_question_inactive']."; padding: 10px 0; }\n"
      ."    .poll_archive_date       { float:left; width:".$this->_cp['date_width']."px; font-weight: bold; }\n"
      ."    .poll_archive_question   { float:left; width:".$this->_cp['question_width']."px; font-weight: bold; }\n"
      ."    .poll_archive_c           { list-style-type:none; margin:3px 0px 0px ".$this->_cp['choice_indent']."px ; padding:0; color:".$this->_cp['colour_choice_loser'].";}\n"
      ."    .poll_archive_c_winner    { font-weight: bold; color: ".$this->_cp['colour_choice_winner']."; }\n"
      ."      .poll_archive_c_text    { float:left; width:".$this->_cp['choice_width']."px; }\n"
      ."      .poll_archive_c_percent { float:left; width:30px; text-align:right }\n"
      ."      .poll_archive_c_votes   { float:left; width:40px; text-align:right }\n"
      ."  .poll_archive_separator     { height: ".($this->_cp['separator_spacing']/2)."px; border-bottom: ".$this->_cp['separator_thickness']."px solid ".$this->_cp['separator_colour']."; margin-bottom: ".($this->_cp['separator_spacing']/2)."px;  }\n";
    Page::push_content('style',$css);
  }

  protected function _draw_poll($poll){
    if (!count($poll['choices'])){
      return;
    }
    $this->_html.=
       "  <div class='"
       .($poll['active']=='1' ? "poll_archive_active" : "poll_archive_inactive")
       ."'>\n"
      .($this->_cp['show_date'] ? "    <div class='poll_archive_date'>".format_date($poll['history_created_date'])."</div>\n" : "")
      ."    <div class='poll_archive_question'>\n"
      ."      ".stripslashes($poll['question'])."\n"
      .($poll['active']=='1' ? "      (Active)\n" : "")
      ."    </div>\n"
      ."    <div class='clr_b' style='overflow:hidden;width:0;height:0'>&nbsp;</div>\n"
      ."    <ul class='poll_archive_c' style='overflow:hidden'>\n";
    $total = 0;
    $max_votes = 0;
    foreach ($poll['choices'] as $choice){
      $total += $choice['votes'];
      if ($choice['votes']>$max_votes){
        $max_votes = $choice['votes'];
      }
    }
    foreach ($poll['choices'] as $choice){
      $percent =    ($total==0 ? 0 : round((($choice['votes']/$total)*100),2));
      $winner =     $choice['votes']>0 && $choice['votes']==$max_votes;
      $this->_html.=
         "      <li".($winner ? " class='poll_archive_c_winner'" : "").">\n"
        ."        <div class='poll_archive_c_text'>".stripslashes($choice['title'])."</div>\n"
        .($this->_cp['show_percent'] ? "        <div class='poll_archive_c_percent'>".$percent."%</div>\n" : "")
        .($this->_cp['show_votes'] ? "        <div class='poll_archive_c_votes'>(".$choice['votes'].")</div>\n" : "")
        ."    <div class='clear'>&nbsp;</div>\n"
        ."      </li>\n";
    }
    $this->_html .=
       "    </ul>\n"
      ."  </div>\n"
      ."  <div class='poll_archive_separator' style='overflow:hidden'>&nbsp;</div>\n";

  }

  protected function _render(){
    return
      "<div class='poll_archives' id=\"".$this->_ident."_".strToLower($this->_instance)."\">\n"
      .$this->_html."\n"
      ."</div>\n";
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_Obj_Poll = new Poll;
    $this->_polls = $this->_Obj_Poll->get_all_polls(
      $this->_cp['include_active'],
      $this->_cp['include_unanswered']
    );
  }

  public function get_version(){
    return VERSION_COMPONENT_POLL_ARCHIVE;
  }
}
?>