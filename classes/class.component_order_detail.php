<?php
  define ("VERSION_COMPONENT_ORDER_DETAIL","1.0.6");
/*
Version History:
  1.0.6 (2013-10-31)
    1) Component_Order_Detail::draw() now uses ECL tag 'draw_signin()' rather than
       the hated 'component_signin_context' which is now gone forever.

  (Older version history in class.component_order_detail.txt)
*/
class Component_Order_Detail extends Component_Base {
  protected $_ID;
  protected $_old_selectID;
  protected $_html = '';

  function __construct(){
    $this->_ident =            "order_detail";
    $this->_parameter_spec =   array(
      'billing' =>                      array('default'=>'1',    'hint'=>'0|1'),
      'billing_expanded' =>             array('default'=>'1',    'hint'=>'0|1'),
      'changes' =>                      array('default'=>'1',    'hint'=>'0|1'),
      'changes_expanded' =>             array('default'=>'1',    'hint'=>'0|1'),
      'credit_memos' =>                 array('default'=>'1',    'hint'=>'0|1'),
      'credit_memos_expanded' =>        array('default'=>'1',    'hint'=>'0|1'),
      'customer_notes' =>               array('default'=>'1',    'hint'=>'0|1'),
      'customer_notes_expanded' =>      array('default'=>'1',    'hint'=>'0|1'),
      'gateway' =>                      array('default'=>'1',    'hint'=>'0|1'),
      'gateway_expanded' =>             array('default'=>'1',    'hint'=>'0|1'),
      'headers' =>                      array('default'=>'1',    'hint'=>'0|1'),
      'items' =>                        array('default'=>'1',    'hint'=>'0|1'),
      'items_expanded' =>               array('default'=>'1',    'hint'=>'0|1'),
      'person' =>                       array('default'=>'1',    'hint'=>'0|1'),
      'person_expanded' =>              array('default'=>'1',    'hint'=>'0|1'),
      'registered_events' =>            array('default'=>'1',    'hint'=>'0|1'),
      'registered_events_expanded' =>   array('default'=>'1',    'hint'=>'0|1'),
      'status' =>                       array('default'=>'1',    'hint'=>'0|1'),
      'status_expanded' =>              array('default'=>'1',    'hint'=>'0|1'),
      'title' =>                        array('default'=>'Details for Order Number',    'hint'=>'Custom title')
    );
  }

  function draw($instance='',$args=array(), $disable_params=false) {
    $this->_setup($instance, $args, $disable_params);
    if (!isset($_SESSION['person'])){
      $this->_html.=
        "[ECL]draw_signin()[/ECL]"
       .draw_form_field('ID',get_var('ID'),'hidden');
      return $this->_html;
    }
    $this->_ID =    get_var('ID');
    $this->_draw_control_panel();
    $this->_old_selectID =  $GLOBALS['selectID'];
    $GLOBALS['selectID'] =  $this->_ID;
    $Obj_Order = new Order($this->_ID);
    if (!$Obj_Order->exists()) {
      $this->_html.= "<h1>No such order as ".$this->_ID."</h1>";
      return $this->_html;
    }
    $Obj_Report =   new Report;
    $this->_record = $Obj_Order->get_record();
    $this->_html.=
       "<h3 class='fl margin_none padding_none'>".$this->_cp['title']." ".$this->_record['ID']." (".$this->_record['paymentStatus'].")</h3>"
      .(get_var('print')==1 || get_var('print')==2 ? "<div class='fr'>".HTML::draw_icon('print',true)."</div>\n" : "");
// Order Status:
    if ($this->_cp['status']) {
      $div = "order_detail_status_".$this->_record['ID'];
      $this->_html.=
         draw_hide_show($div,"Order Status",$this->_cp['status_expanded'])
        .draw_auto_report('order_status',0)
        ."</div>";
    }
// View Event Registrations for Order:
    $this->_html.= $this->draw_event_registrations($this->_record['ID']);
// View Credit Memos for Order:
    if ($this->_cp['credit_memos']) {
      $credit_memos = $Obj_Order->count_credit_memos();
      if ($credit_memos>0){
        $div = "order_detail_credit_memos_".$this->_record['ID'];
        $this->_html.=
           draw_hide_show($div,"Credit Memos (".$credit_memos.")",$this->_cp['credit_memos_expanded'])
          .draw_auto_report('view_credit_memos_for_order',0)
          ."</div>";
      }
    }
// Gateway Result:
    $gateway_result = $Obj_Order->get_field('gateway_result');
    if ($this->_cp['gateway'] && $gateway_result!="") {
      $div = "order_gateway_result_".$this->_record['ID'];
      $this->_html.=
         draw_hide_show($div,"Transaction Result",$this->_cp['gateway_expanded'])
        .draw_auto_report('order_gateway_result',0)
        ."</div>";
    }
// Change History:
    if ($this->_cp['changes']) {
      $changes = $Obj_Order->count_changes();
      if ($changes>0){
        $div =          "order_change_history_".$this->_record['ID'];
        $this->_html.=
           draw_hide_show($div,"Change History (".$changes." previous - latest is highlighted)",$this->_cp['changes_expanded'])
          .draw_auto_report('order_change_history',0)
          ."</div>";
      }
    }
// Person Details:
    if ($this->_cp['person']) {
      $div =        "order_person_details_".$this->_record['ID'];
      $personID =   $this->_record['personID'];
      $this->_html.=
         draw_hide_show($div,"Person's Details",$this->_cp['person_expanded'])
        ."<table style='width:100%'>\n"
        ."  <tr>\n"
        ."    <td class='va_t' style='width:50%'>"
        ."<b>Profile / Home Address</b>"
        .$Obj_Report->draw_form_view('person_view_for_order_1',$personID,true,$this->_cp['headers'])
        ."</td>"
        ."<td class='va_t' style='width: 50%'>"
        ."<b>Work Address</b>"
        .$Obj_Report->draw_form_view('person_view_for_order_2',$personID,true,$this->_cp['headers'])
        ."</td></tr></table><br /></div>";
    }
// Billing / Shipping Addresses:
    if ($this->_cp['billing']) {
      $div =        "order_billing_".$this->_record['ID'];
      $this->_html.=
         draw_hide_show($div,"Billing and Shipping Information",$this->_cp['billing_expanded'])
        ."<table style='width:100%'><tr><td class='va_t' style='width:50%'>"
        ."<b>Billing Information</b>"
        .$Obj_Report->draw_form_view('order_billing',$this->_ID,true,$this->_cp['headers'])
        ."</td>"
        ."<td class='va_t' style='width: 50%'>"
        ."<b>Shipping Information</b>"
        .$Obj_Report->draw_form_view('order_shipping',$this->_ID,true,$this->_cp['headers'])
        ."</td></tr></table><br /></div>";
    }
// Items
    if ($this->_cp['items']){
      $div =        "order_items_".$this->_ID;
      $this->_html.=
         draw_hide_show($div,"Items included in Order Number ".$this->_ID,$this->_cp['items_expanded'])
        .$Obj_Order->draw_order_summary($this->_record['paymentStatus'])
        ."</div>";
    }
    $GLOBALS['selectID'] = $this->_old_selectID;
    return $this->_html;
  }

  public function draw_event_registrations(){
    if (!$this->_cp['registered_events']) {
      return;
    }
    $Obj_Order = new Order($this->_record['ID']);
    if (!$registered_events = $Obj_Order->count_registered_events()){
      return;
    }
    $div = "order_detail_registered_events_".$this->_ID;
    $out =
       draw_hide_show($div,"Booked Event".($registered_events==1 ? '' : 's')
      ." for Order (".$registered_events.")",$this->_cp['registered_events_expanded'])
      .$this->draw_print_tickets_link()
      .draw_auto_report('view_registered_events_for_order',0)
      ."</div>";
    return $out;
  }

  public function draw_print_tickets_link(){
    $Obj_Order = new Order($this->_record['ID']);
    if (!$ID_csv = $Obj_Order->get_registered_event_tickets()){
      return;
    }
    $ID_arr = explode(',',$ID_csv);
    $out= "<div>";
    if (count($ID_arr)<=100){
      if ($this->_record['systemID']==SYS_ID){
        $URL =    BASE_PATH."_ticket?ID=".$ID_csv;
      }
      else{
        $Obj_System = new System($this->_record['systemID']);
        $URL =    trim($Obj_System->get_field('URL'),'/')."/_ticket?ID=".$ID_csv;
      }
      $out.=
         "Please <a rel=\"external\" href=\"".$URL."\">click here now</a> to print"
        .(count($ID_arr)==1 ? ' your ticket.' : ' all '.count($ID_arr).' tickets.');
    }
    else {
      $out.= "Print tickets ";
      for($i=0; $i<count($ID_arr); $i+=100){
        $slice =  array_slice($ID_arr,$i,100);
        $ID_csv = implode(',',$slice);
        if ($this->_record['systemID']==SYS_ID){
          $URL =    BASE_PATH."_ticket?ID=".$ID_csv;
        }
        else{
          $Obj_System = new System($this->_record['systemID']);
          $URL =    trim($Obj_System->get_field('URL'),'/')."/_ticket?ID=".$ID_csv;
        }
        $out.=
           "<a rel=\"external\" href=\"".$URL."\">"
          .($i+1)."-".(($i)+count($slice))
          ."</a> ";
      }
    }
    $out.= "</div>";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_ORDER_DETAIL;
  }
}
?>