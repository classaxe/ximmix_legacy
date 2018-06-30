<?php
define('VERSION_ORDERS_OVERVIEW','1.0.1');
/*
Version History:
  1.0.1 (2012-04-25)
    1) Fix to Orders_Overview::_setup_get_unique_payment_status() to default
       payment status to 'IGNORED' not 'Paid' if not given.
       Was failing to show orders if they all had the same status and that status
       was other than 'Paid'
    2) Was using wrong constant for returned version number
  1.0.0 (2012-04-04)
    1) Initial release - Moved Order::orders_overview() to Orders_Overview::draw()
*/
class Orders_Overview extends Order {
  protected $_filter_category;
  protected $_filter_date_end;
  protected $_filter_date_start;
  protected $_html;
  protected $_records;
  protected $_sum_pretax_sales;
  protected $_sum_shipping;
  protected $_sum_total_tax;
  protected $_tax_columns;
  protected $_tax_names;
  protected $_tax_sums;
  protected $_unique_categories;
  protected $_unique_payment_status;


  public function draw(){
    $this->_setup();
    $this->_get_results();
    $this->_draw_toolbar();
    $this->_draw_heading();
    $this->_draw_results_table_open();
    $this->_draw_results_thead();
    $this->_draw_results_tbody();
    $this->_draw_results_table_close();
    $this->_draw_footer();
    return $this->_html;
  }

  protected function _draw_footer(){
    $this->_html.=
       "<p style='margin: 0.5em 0;text-align:center;font-style:italic'>"
      ."Prepared for <b>".get_userPUsername()."</b> on <b>".format_date(get_timestamp())."</b>"
      ."</p>";
  }

  protected function _draw_heading(){
    global $system_vars;
    $this->_html.=
       "<h3 style='margin: 0.5em 0;text-align:center'>"
      .$system_vars['textEnglish']."<br />\n"
      ."Overview of Orders placed<br />\n"
      ."between ".format_date($this->_filter_date_start)." and ".format_date($this->_filter_date_end)
      .(count($this->_unique_payment_status)>1 && $this->_filter_payment_status!= 'IGNORED' ? "<br />\nhaving Payment Status of \"".$this->_filter_payment_status."\"" : "")
      .($this->_filter_category!= 'IGNORED' ? "<br />\nin Category \"".$this->_filter_category."\"" : "")
      ."</h3>";
  }

  protected function _draw_results_table_open(){
    $this->_html.= "<div class='orders_overview'><table summary='Orders Overview by Month' style='margin: auto' class='report'>\n";
  }

  protected function _draw_results_thead(){
    $this->_html.=
       "  <thead>\n"
      ."    <tr class='head'>\n"
      ."      <th>Month</th>\n"
      ."      <th>Pre Tax Sales</th>\n";
    foreach($this->_tax_columns as $tax_column){
      $this->_html.=    "      <th>".$this->_tax_names[$tax_column]."</th>\n";
    }
    $this->_html.=
       "      <th>Tax Total</th>\n"
      .($this->_sum_shipping ? "      <th>Shipping</th>\n" : "")
      ."    </tr>\n"
      ."  </thead>\n";
  }

  protected function _draw_results_tbody(){
    $this->_html.=
       "  <tbody>\n";
    foreach ($this->_records as $record){
      $this->_html.=
         "    <tr>\n"
        ."      <td>".$record['month']."</td>\n"
        ."      <td class='num'>".number_format($record['pre_tax_sales'],2)."</td>\n";
      foreach($this->_tax_columns as $tax_column){
        $this->_html.=
          "      <td class='num'>".number_format($record['tax'.$tax_column.'_sum'],2)."</td>\n";
      }
      $this->_html.=
         "      <td class='num'>".number_format($record['total_tax'],2)."</td>\n"
        .($this->_sum_shipping ? "      <td class='num'>".number_format($record['shipping'],2)."</td>\n" : "")
        ."    </tr>\n";
    }
    $this->_html.=
       "    <tr class='rollup'>\n"
      ."      <th>Total</th>\n"
      ."      <th class='num'>".number_format($this->_sum_pretax_sales,2)."</th>\n";
    foreach($this->_tax_columns as $tax_column){
      $this->_html.=
         "      <th class='num'>"
        .number_format($this->_tax_sums[$tax_column],2)
        ."</th>\n";
    }
    $this->_html.=
       "      <th class='num'>".number_format($this->_sum_total_tax,2)."</th>\n"
      .($this->_sum_shipping ?       "      <th class='num'>".number_format($this->_sum_shipping,2)."</th>\n" : "")
      ."    </tr>\n"
      ."  </tbody>\n";
  }

  protected function _draw_results_table_close(){
    $this->_html.= "</table></div>\n";
  }

  protected function _draw_toolbar(){
    $this->_html =
       "<table class='admin_toolbartable noprint' summary='Orders Overview Toolbar'>\n"
      ."  <tr>\n"
      .$this->_draw_toolbar_control_date_start()
      .$this->_draw_toolbar_control_date_end()
      .$this->_draw_toolbar_control_categories()
      .$this->_draw_toolbar_control_payment_status()
      .$this->_draw_toolbar_control_buttons()
      ."  </tr>\n"
      ."</table>\n"
      ."<div class='clear'>&nbsp;</div>";
  }

  protected function _draw_toolbar_control_buttons(){
    return
       $this->_draw_toolbar_separator()
      ."    <td><input type='button' class='formButton' value='Go' onclick=\"geid('form').submit()\" /></td>\n"
      ."    <td><input type='button' class='formButton' value='Reset' onclick=\""
      ."geid_set('orders_overview_start','');"
      ."geid_set('orders_overview_end','');"
      ."geid_set('orders_overview_category','IGNORED');"
      ."geid_set('orders_overview_payment_status','IGNORED');"
      ."geid('form').submit()"
      ."\" /></td>\n";
  }

  protected function _draw_toolbar_control_categories(){
    if (count($this->_unique_categories)==1){
      return;
    }
    $item_arr = array(
      'IGNORED|(All)|e0e0e0'
    );
    foreach ($this->_unique_categories as $item){
      $item_arr[] = $item.'|'.title_case_string(str_replace(array('-','_')," ",$item)).'|FFFFFF';
    }
    $csv_list = implode(',',$item_arr);
    return
       $this->_draw_toolbar_separator()
      ."    <td><label for='orders_overview_category'>Category</label></td>\n"
      ."    <td>".draw_form_field('orders_overview_category',$this->_filter_category,'selector_csvlist',140,'',0,'',0,0,'',$csv_list)."</td>\n";
  }

  protected function _draw_toolbar_control_date_end(){
    return
       $this->_draw_toolbar_separator()
      ."    <td><label for='orders_overview_end'>End</label></td>\n"
      ."    <td>".draw_form_field('orders_overview_end',$this->_filter_date_end,'date')."</td>\n";
  }

  protected function _draw_toolbar_control_date_start(){
    return
       $this->_draw_toolbar_separator()
      ."    <td><label for='orders_overview_start'>Start</label></td>\n"
      ."    <td>".draw_form_field('orders_overview_start',$this->_filter_date_start,'date')."</td>\n";
  }

  protected function _draw_toolbar_control_payment_status(){
    if (count($this->_unique_payment_status)==1){
      return;
    }
    $item_arr = array(
      'IGNORED|(All)|e0e0e0'
    );
    foreach ($this->_unique_payment_status as $item){
      $item_arr[] = $item.'|'.title_case_string(str_replace(array('-','_')," ",$item)).'|FFFFFF';
    }
    $csv_list = implode(',',$item_arr);
    return
       $this->_draw_toolbar_separator()
      ."    <td><label for='orders_overview_payment_status'>Payment Status</label></td>\n"
      ."    <td>".draw_form_field('orders_overview_payment_status',$this->_filter_payment_status,'selector_csvlist',100,'',0,'',0,0,'',$csv_list)."</td>\n";
  }

  protected function _draw_toolbar_separator(){
     return
       "    <td><img class='b' src='".BASE_PATH."img/sysimg/icon_toolbar_end_left.gif' style='height:16px;width:6px;padding: 2px 0px;' alt='|' /></td>\n";
  }



  protected function _setup(){
    $this->_setup_initialize();
    $this->_setup_get_date_range();
    $this->_setup_get_unique_categories();
    $this->_setup_get_unique_payment_status();
  }

  protected function _setup_initialize(){
    $this->_sum_pretax_sales =      0;
    $this->_sum_shipping =          0;
    $this->_tax_sums =              array_fill(1,20,0);
    $this->_tax_names =             array_fill(1,20,'');
    $this->_tax_columns =           array();
    $this->_unique_categories =     array();
    $this->_unique_payment_status = array();
  }

  protected function _setup_get_date_range(){
    $this->_filter_date_start =     sanitize('date-stamp',get_var('orders_overview_start'));
    $this->_filter_date_end =       sanitize('date-stamp',get_var('orders_overview_end'));
    if (!$this->_filter_date_start){
      $this->_filter_date_start =   date('Y-m-d',mktime(0, 0, 0, (int)date('m')-11, 1, date('Y')));
    }
    if (!$this->_filter_date_end){
      $this->_filter_date_end =     date('Y-m-d',mktime(0, 0, 0, (int)date('m')+1, 0, date('Y')));
    }
  }

  protected function _setup_get_unique_categories(){
    $this->_filter_category =     sanitize('html',get_var('orders_overview_category','IGNORED'));
    $sql =
       "SELECT\n"
      ."  DISTINCT `category`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".SYS_ID." AND\n"
      ."  `archive` = 0 AND\n"
      ."  `credit_memo_for_orderID` = 0\n"
      ."ORDER BY\n"
      ."  `category`";
    $records = $this->get_records_for_sql($sql);
    foreach ($records as $record){
      $this->_unique_categories[] = $record['category'];
    }
  }

  protected function _setup_get_unique_payment_status(){
    $this->_filter_payment_status =     sanitize('html',get_var('orders_overview_payment_status','IGNORED'));
    $sql =
       "SELECT\n"
      ."  DISTINCT `paymentStatus`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".SYS_ID." AND\n"
      ."  `archive` = 0 AND\n"
      ."  `credit_memo_for_orderID` = 0\n"
      ."ORDER BY\n"
      ."  `paymentStatus`";
    $records = $this->get_records_for_sql($sql);
    foreach ($records as $record){
      $this->_unique_payment_status[] = $record['paymentStatus'];
    }
  }



  protected function _get_results(){
    $this->_get_results_data();
    $this->_get_results_pretax_sales();
    $this->_get_results_tax_names();
    $this->_get_results_tax_sums();
    $this->_get_results_shipping();
    $this->_get_results_tax_columns_used();
  }

  protected function _get_results_data(){
    $sql =
       "SELECT\n"
      ."  LEFT(`history_created_date`,7) `month`,\n";
    for ($i=1; $i<=20; $i++){
      $sql.=
         "  `tax".$i."_name`,\n"
        ."  SUM(`tax".$i."_cost`) `tax".$i."_sum`,\n";
    }
    $sql.=
       "  SUM(`cost_items_pre_tax`) `pre_tax_sales`,\n"
      ."  SUM(`cost_shipping`) `shipping`,\n"
      ."  0 `total_tax`\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".SYS_ID." AND\n"
      ."  `archive` = 0 AND\n"
      ."  `credit_memo_for_orderID` = 0 AND\n"
      .($this->_filter_category!=='IGNORED'       ? "  `category`='".$this->_filter_category."' AND\n" : "")
      .($this->_filter_payment_status!=='IGNORED' ? "  `paymentStatus`='".$this->_filter_payment_status."' AND\n" : "")
      ."  `history_created_date`>='".$this->_filter_date_start."' AND\n"
      ."  `history_created_date`<='".$this->_filter_date_end."'\n"
      ."GROUP BY\n"
      ."  LEFT(`history_created_date`,7)\n"
      ."ORDER BY\n"
      ."  LEFT(`history_created_date`,7) DESC\n";
//    z($sql);
    $this->_records =          $this->get_records_for_sql($sql);
  }

  protected function _get_results_pretax_sales(){
    foreach($this->_records as $record){
      $this->_sum_pretax_sales+= $record['pre_tax_sales'];
    }
  }

  protected function _get_results_shipping(){
    foreach($this->_records as $record){
      $this->_sum_shipping+= $record['shipping'];
    }
  }
  protected function _get_results_tax_columns_used(){
    for ($i=1; $i<=20; $i++){
      if ($this->_tax_sums[$i]>0){
        $this->_tax_columns[] = $i;
      }
    }
  }

  protected function _get_results_tax_names(){
    foreach($this->_records as $record){
      for($i=1; $i<=20; $i++){
        if ($this->_tax_names[$i]=='' && $record['tax'.$i.'_name']){
           $this->_tax_names[$i] = $record['tax'.$i.'_name'];
        }
      }
    }
  }

  protected function _get_results_tax_sums(){
    foreach($this->_records as &$record){
      for($i=1; $i<=20; $i++){
        $this->_tax_sums[$i]+=$record['tax'.$i.'_sum'];
        $record['total_tax']+=$record['tax'.$i.'_sum'];
        $this->_sum_total_tax+=$record['tax'.$i.'_sum'];
      }
    }
  }

  public function get_version(){
    return VERSION_ORDERS_OVERVIEW;
  }
}
?>