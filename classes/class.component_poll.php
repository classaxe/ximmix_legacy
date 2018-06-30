<?php
  define ("VERSION_COMPONENT_POLL","1.0.2");
/*
Version History:
  1.0.2 (2012-11-03)
    1) Now inserts extra break between multiple items where required
  1.0.1 (2012-11-03)
    1) Big changes to allow for use of standard methods for setup and control panel
       and refactoring to reduce stament nesting
  1.0.0 (2011-12-29)
    1) Initial release - moved from Component class
*/
class Component_Poll extends Component_Base {
  protected $_Obj_Poll;
  protected $_polls;

  public function __construct(){
    $this->_ident =             'poll';
    $this->_parameter_spec = array(
      'category' =>     array('match' => '',				'default'=>'*',           'hint'=>'*|CSV Category List'),
      'limit' =>        array('match' => 'range|1,n',		'default'=>'1',           'hint'=>'1..n')
    );
  }

  public function draw($instance='',$args=array(),$disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    if (!$this->_polls) {
      $this->_html.= "<p>There are no polls available to view at this time.</p>";
      return $this->_html;
    }
    for($i=0; $i<count($this->_polls); $i++){
      $poll = $this->_polls[$i];
      $this->_draw_poll($poll);
      if ($i!=count($this->_polls)-1){
        $this->_html.="<br />";
      }
    }
    return $this->_html;
  }

  protected function _draw_poll($poll){
    $ID =       $poll['ID'];
    $this->_Obj_Poll->_set_ID($ID);
    $voted =    $this->_has_voted($ID);
    $this->_html.=
       "<div id='poll_".$ID."' class='poll'>\n"
      .($poll['active']=='0' || $voted ?
         $this->_Obj_Poll->draw_result($voted)
       :
         $this->_Obj_Poll->draw_question()
       )
      ."</div>";

  }

  protected function _has_voted($ID){
    return isset($_COOKIE['poll_'.$ID]) || isset($_SESSION["poll_".$ID]);
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_Obj_Poll = new Poll;
    $this->_polls = $this->_Obj_Poll->get_current_polls(
      $this->_cp['category'],
      $this->_cp['limit'],
      0
    );
  }

  public function get_version(){
    return VERSION_COMPONENT_POLL;
  }
}
?>