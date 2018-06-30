<?php
define ("VERSION_PRODUCT_CATALOGUE","1.0.31");
/*
Version History:
  1.0.31 (2012-10-17)
    1) Product_Catalogue::_draw_setup_load_product_groupings() now sets item field
       'has_second_row' for each item loaded
    2) Product_Catalogue::_draw_item_description() now checks each item's
       'has_second_row' value to decide whether or not to skip.
    3) Product_Catalogue::_has_catalogue_row_description() now only returns true
       where an entry HAS an image value, the column list includes Image and
       the $this->_image flag is set indicating that images are to be shown
    4) Product_Catalogue::_draw_item_credit_memo() now spans one additional column
       since quantity was removed from columns count now it spans two rows.
  1.0.30 (2012-09-19)
    1) Product_Catalogue::_draw_calc_colspan() no longer includes quantity column
       when calculating colspan since this field now spans two rows
  1.0.29 (2012-09-03)
    1) Changes to Product_Catalogue::_draw_item_credit_memo() to use
       Person object to discover username for tooltip message, not codebase
       function get_userPUsername_by_ID() as before
  1.0.28 (2012-01-11)
    1) Removed debug code in Product_Catalogue::_draw_refund_items()

  (Older version history in class.product_catalogue.txt)
*/

class Product_Catalogue {
  protected $_BCountryID;
  protected $_BSpID;
  protected $_current_user_rights =   array();
  protected $_currency_suffix;
  protected $_currency_symbol;
  protected $_html =                  '';
  protected $_items =                 array();
  protected $_js =                    '';
  protected $_orderID;
  protected $_totals =                array();
  protected $_ObjTaxRegime;
  protected $_related_type =          false;
  protected $_related_ID =            false;
  protected $_payment_status;
  protected $_popup =                 array();
  protected $_product_groupings =     array();
  protected $_shipping;
  protected $_tabindex =              1;

  public function draw($args) {
    return "This class is intended for use as a base class";
  }

  protected function _draw_calc_colspan($columns){
    $colspan=0;
    foreach ($columns as $c) {
      switch ($c['field']){
        case 'quantity':
        break;
        default:
          $colspan+=($c['row']==1 ? 1 : 0);
        break;
      }
    }
    return $colspan;
  }

  protected function _draw_category_heading($groupingID,$category_name){
    if (!$this->_current_user_rights['canEditCategory']) {
      return "<b>".$category_name."</b>\n";
    }
    return
       "<a href=\"#\" "
      ."onclick=\"details('product_grouping',".$groupingID.",".$this->_popup['ProductGrouping']['h'].",".$this->_popup['ProductGrouping']['w'].");return false;\" "
      ."onmouseover=\"window.status='Edit this Product Category';return true;\" "
      ."onmouseout=\"window.status='';return true;\" "
      ."title=\"Edit this Product Category\">"
      ."<b>".$category_name."</b></a>\n";
  }

  protected function _draw_catalogue_row_headings($columns){
    $this->_html.= "  <tr>\n";
    foreach ($columns as $column){
      if ($column['row']==1){
        $this->_html.=
          "    <th class='nowrap'".($column['width'] ? " style='width:".$column['width']."'" : "").">"
          .$column['heading']
          ."</th>\n";
      }
    }
    $this->_html.= "  </tr>\n";
  }

  protected function _draw_item($item,$columns){
    $colspan =          $this->_draw_calc_colspan($columns);
    $ID =               $item['ID'];
    $price =            $item['price'];
    $hasCreditMemo =    (isset($item['creditMemo']) ? $item['creditMemo'] : 0);
    $creditMemoID =     (isset($item['creditMemoID']) ? $item['creditMemoID'] : 0);
    $_calc =            $this->_draw_calc_cost_info($item);
    $quantity =         $_calc['quantity'];
    $cost =             $_calc['cost'];
    $net =              $_calc['net'];
    if ($hasCreditMemo) {
      $this->_html.= $this->_draw_item_credit_memo($item,$creditMemoID,$colspan);
    }
    $this->_html.=
      "  <tr"
      .($hasCreditMemo ?
         " class='"
        .($creditMemoID ? 'credit_memo_issued' : 'credit_memo_pending')
        ."'"
       :
         ""
       )
       .">\n";
    foreach ($columns as $column) {
      $field =          $column['field'];
      $width =          $column['width'];
      $isNum =          $column['isNum'];
      $ID =             $item['ID'];
      // Get values:
      switch (strtolower($field)){
        case "sub-total":
          $value = $this->_currency_symbol.two_dp($net);
        break;
        case "tax":
          $value_arr = array();
          for($i=1; $i<=20; $i++){
            if((float)$_calc['tax'.$i.'_cost']!=0){
              $value_arr[] = "<span class='nowrap'>".$_calc['tax'.$i.'_name']."</span>";
            }
          }
          $value = implode(' ',$value_arr);
        break;
        case "quantity":
          $value = $quantity;
        break;
        case "price":
          $value = $this->_currency_symbol.$price;
        break;
        case "image":
          // don't bother
        break;
        case "tax":
          $value_lines = array();
          for($i=1; $i<=20; $i++){
            if (isset($item['tax'.$i.'_apply']) && $item['tax'.$i.'_apply'] && $item['tax'.$i.'_rate']){
              $value_lines[] = $item['tax'.$i.'_name'].' '.(float)$item['tax'.$i.'_rate'].'%';
            }
          }
          $value = implode('<br />',$value_lines);
        break;
        default:
          $value =  $item[$field];
        break;
      }
      // Gotten values, now start outputting row:
      switch ($field) {
        case "content":
        case "image":
          // do nothing yet...
        break;
        case "itemCode":
          if ($this->_current_user_rights['canEditProduct']) {
            $this->_html.=
               "    <td>"
              ."<a href=\"#\" "
              ."onclick=\"details('product',".$ID.",".$this->_popup['Product']['h'].",".$this->_popup['Product']['w'].");return false;\" "
              ."onmouseover=\"window.status='Edit this Product';return true;\" "
              ."onmouseout=\"window.status='';return true;\" "
              ."title=\"Edit this Product\">"
              .$value
              ."</a>"
              ."</td>\n";
          }
          else {
            $this->_html.= "    <td>".$value."</td>\n";
          }
        break;
        case "quantity":
          $this->_html.= $this->_draw_item_quantity($item,$hasCreditMemo);
        break;
        default:
          $this->_html.=
             "    <td style='text-align:".($isNum ? 'right' : 'left')."'"
            .">"
            .$value
            ."</td>\n";
        break;
      }
    }
    $this->_html.=  "  </tr>\n";
    $this->_draw_item_description($columns,$item,$colspan,$hasCreditMemo,$creditMemoID);
  }

  protected function _draw_item_credit_memo($item,$creditMemoID,$colspan){
    $Obj = new Person($item['history_created_by']);
    $created_by = $Obj->get_field('PUsername');
    $modified_by = false;
    if ($item['history_modified_date']!="0000-00-00 00:00:00"){
      $Obj->_set_ID($item['history_modified_by']);
      $modified_by = $Obj->get_field('PUsername');
    }
    return
       "  <tr class='"
      .($creditMemoID ? 'credit_memo_issued' : 'credit_memo_pending')
      ."'>\n"
      ."    <td colspan='".($colspan+1)."' valign='top'>"
      ."<i>"
      ."<b>--- "
      .($creditMemoID ? "Credit Memo Issued" : "Credit Memo Pending")
      ." ---</b>"
      ." (".$item['history_created_date']
      ." by ".$created_by
      .($modified_by ?
          ", modified ".$item['history_modified_date']
         ." by ".$modified_by
       : ""
       )
      .")</i>"
      .($creditMemoID ?
         ""
       :
          " &nbsp; [ "
         ."<a title=\"Issue Credit Memo\" href=\"#credit_memo\">"
         ."Issue Credit Memo"
         ."</a> ]"
       )
      ."<br />\n"
      ."<p style='margin:0;padding-left:25px;'>"
      .($item['creditMemoID']!=0 ?
          "<b>Items Refunded:</b> ".(int)$item['quantity']."<br />"
        : ""
       )
      ."<br /></p>"
      ."</td>\n"
      ."</tr>\n";
  }

  protected function _draw_item_description($columns,$item,$colspan,$hasCreditMemo,$creditMemoID){
    if (!$item['has_second_row']){
      return;
    }
    $this->_html.=
      "  <tr".($hasCreditMemo ? " class='".($creditMemoID ? 'credit_memo_issued' : 'credit_memo_pending')."'" : "").">\n"
     ."    <td colspan='$colspan'>\n";
    foreach ($columns as $column) {
      $heading =    $column['heading'];
      $field =      $column['field'];
      $width =      $column['width'];
      $isNum =      $column['isNum'];
      $content =    $item['content'];
      if (isset($item['related_object']) && $type = $item['related_object']){
        $Obj_Related = new $type($item['related_objectID']);
        $content.= "<br />".$Obj_Related->draw_associated();
      }
      switch ($field) {
        case "content":
           $this->_html.=
              "<div>"
             .($item['content']!='' ? "<b>".$heading."</b><br />\n".$content : "&nbsp;"  )
             ."</div>\n";
        break;
        case "image":
          switch($this->_image){
            case 's':
              $image = trim($item['thumbnail_small'],'./');
            break;
            case 'm':
              $image = trim($item['thumbnail_medium'],'./');
            break;
            case 'l':
              $image = trim($item['thumbnail_large'],'./');
            break;
            default:
              $image = '';
            break;
          }
          if ($image!='' && file_exists('.'.BASE_PATH.$image)){
            if ($this->_image_width){
              if ($this->_image_height){
                $image = BASE_PATH."img/resize/".$image."?width=".$this->_image_width."&amp;height=".$this->_image_height;
              }
              else{
                $image = BASE_PATH."img/width/".$this->_image_width."/".$image;
              }
            }
            else {
              if ($this->_image_height){
                $image = BASE_PATH."img/height/".$this->_image_height."/".$image;
              }
              else{
                $image = BASE_PATH."img/resize/".$image;
              }
            }
            $this->_html.=
               "<div class='fl' style='border:solid 1px #888;margin:0 5px 0 0;'>"
              ."<img src=\"".$image."\" class='border_none' alt='' />"
              ."</div>\n";
          }
        break;
        default:
          // Do nothing
        break;
      }
    }
    $this->_html.=
       "</td>\n"
      ."  </tr>\n";
  }

  protected function _draw_product_groupings_with_items(){
    foreach ($this->_product_groupings as $groupingID=>$product_grouping){
      $columns =      $this->_get_columns_for_grouping($groupingID);
      $this->_html.=
         $this->_draw_category_heading($groupingID, $product_grouping['name'])
        ."<table class='product_catalogue'>\n";
      $this->_draw_catalogue_row_headings($columns);
      foreach($product_grouping['items'] as $item){
        $item['has_second_row'] = $this->_has_catalogue_row_description($columns,$item);
        $this->_draw_item($item,$columns);
      }
      $this->_html.= "</table><br />\n";
    }
  }

  protected function _draw_refund_items(){
    $ref_arr = array();
    foreach ($this->_items as $item) {
      if ($item['creditMemo']==1 && $item['creditMemoID']==0){
        $ref_arr[] = $item['orderItemID'];
      }
    }
    $taxes_arr = array();
    foreach ($this->_items as $item) {
      if ($item['creditMemo']==1 && $item['creditMemoID']==0){
        for ($i=1; $i<=20; $i++){
          if ((float)$item['tax'.$i.'_cost']>0){
            $taxes_arr[$i] =
              array(
                'idx' =>  $i,
                'name' => $item['tax'.$i.'_name'],
                'rate' => $item['tax'.$i.'_rate']
              );
          }
        }
      }
    }
    Page::push_content(
      'javascript',
      "var ref_items_arr = [".implode(",",$ref_arr)."];\n"
    );
    $this->_html.= "<a name=\"credit_memo\"></a>\n";
    if (!count($ref_arr)){
      return;
    }
    $this->_html.=
       "<h1 style='font-size:125%;margin:0'>".count($ref_arr)." Item"
      .(count($ref_arr)==1 ? '' : 's')." Pending Refund</h1>\n"
      ."<table class='order_cost_summary'>\n"
      ."  <tr>\n"
      ."    <th class='va_b'>Qty</th>\n"
      ."    <th class='va_b'>Item Code</th>\n"
      ."    <th class='va_b'>Price<br />(Each)</th>\n"
      ."    <th class='va_b'>Non-Refundable<br />Amount (NRA)</th>\n"
      ."    <th class='va_b'>Diff</th>\n"
      ."    <th class='va_b'>Sub Tot</th>\n";
    foreach($taxes_arr as $tax){
      $this->_html.=
         "    <th class='va_b'>"
        .$tax['name']."<br />(".$tax['rate']."%)"
        ."</th>\n";
    }
    $this->_html.=
       "    <th class='va_b' colspan='2'>Total</th>\n"
      ."  </tr>\n";
      foreach ($this->_items as $item) {
        if ($item['creditMemo']==1 && $item['creditMemoID']==0){
          $diff = $item['price']-$item['price_non_refundable'];
          $sub =  $item['quantity'] * $diff;
          $taxes = 0;
          $tax_js = array();
          foreach($taxes_arr as $tax){
            $taxes += $item['tax'.$tax['idx'].'_cost']*(($item['price']-$item['price_non_refundable'])/$item['price']);
            $tax_js[] =
               '{'
              .'idx:'.$tax['idx'].',amount:'.$item['tax'.$tax['idx'].'_cost']
              .'}';
          }
          $line = $sub + $taxes;
          $this->_html.=
             "  <tr>\n"
            ."    <td class='txt_r'>".$item['quantity']."</td>\n"
            ."    <td>".$item['itemCode']."</td>\n"
            ."    <td class='txt_r'>".$item['price']."</td>\n"
            ."    <td>\n"
            ."<div class='fl' style='width:40px'><a href=\"#\" onclick=\"order_item_refund_nra_reset('".$item['orderItemID']."','".$item['price_non_refundable']."');return false;\">Reset</a>&nbsp;</div>"
            ."<input id=\"ref_".$item['orderItemID']."_nra\" tabindex=\"".$this->_tabIndex++."\" type='text' class='formField txt_r fl' style='width:40px;' value=\"".two_dp($item['price_non_refundable'])."\" "
            ."onchange=\""
            ."order_item_refund_calculate("
            ."'".$item['orderItemID']."',"
            ."'".$item['quantity']."',"
            ."'".$item['price']."',"
            ."[".implode(',',$tax_js)."],"
            ."'".$line."'"
            .");order_items_refund_total(ref_items_arr);\"/>\n"
            ."</td>\n"
            ."    <td><div class='formField_ro txt_r' id=\"ref_".$item['orderItemID']."_diff\" style='width:40px'>"
            .two_dp($diff)
            ."</div></td>\n"
            ."    <td><div class='formField_ro txt_r' id=\"ref_".$item['orderItemID']."_sub\" style='width:40px'>"
            .two_dp($sub)
            ."</div></td>\n";
          foreach($taxes_arr as $tax){
            $amount =
              $item['tax'.$tax['idx'].'_cost'] *
              ($item['price']-$item['price_non_refundable']) / $item['price'];
            $this->_html.=
               "    <td>\n"
              ."<div class='formField_ro txt_r'"
              ." id=\"ref_".$item['orderItemID']."_tax_".$tax['idx']."\" style='width:40px'>"
               .two_dp(
                 $amount
               )
               ."</div></td>\n";
          }
          $this->_html.=
             "    <td><div class='formField_ro txt_r' id=\"ref_".$item['orderItemID']."_line\" style='width:40px'>"
            .two_dp($line)
            ."</div></td>\n"
            ."    <td><a title=\"Cancel refund on this item\" href=\"#\" onclick=\"order_item_refund_flag_clear('".$item['orderItemID']."');return false;\">"
            ."<img src='".BASE_PATH."img/spacer' class='icons' style='height:10px;width:10px;background-position:-907px 0px;' alt='' />"
            ."</a></td>\n"
            ."  </tr>\n";
        }
      }
      $this->_html.=
         "</table><br />\n";
  }

  protected function _draw_setup($args){
    global $system_vars;
    $this->_ObjTaxRegime =      new Tax_Regime;
    $this->_items =             $args['items'];
    $this->_BCountryID =        $args['BCountryID'];
    $this->_BSpID =             $args['BSpID'];
    $this->_paymentStatus =     $args['paymentStatus'];
    $this->_orderID =           $args['_orderID'];
    $this->_related_type =      (isset($args['related_type']) ? $args['related_type'] : false);
    $this->_related_ID =        (isset($args['related_ID']) ? $args['related_ID'] : false);
    $this->_image =             (isset($args['image']) ? $args['image'] : '');
    $this->_image_height =      (isset($args['image_height']) ? $args['image_height'] : '');
    $this->_image_width =       (isset($args['image_width']) ?  $args['image_width'] : '');
    $this->_currency_suffix =   $system_vars['defaultCurrencySuffix'];
    $this->_currency_symbol =   $system_vars['defaultCurrencySymbol'];
    $this->_draw_setup_load_permissions();
    $this->_draw_setup_load_popup_sizes();
    $this->_draw_setup_load_product_groupings();
  }

  protected function _draw_setup_load_permissions(){
    $isMASTERADMIN =	    get_person_permission("MASTERADMIN");
    $isSYSADMIN =		    get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	    get_person_permission("SYSAPPROVER");
    $isSYSEDITOR =	        get_person_permission("SYSEDITOR");
    $this->_current_user_rights['canEditCategory'] =    $isMASTERADMIN || $isSYSADMIN;
    $this->_current_user_rights['canEditProduct'] =     $isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR;
    $this->_current_user_rights['canIssueRefund'] =     $isMASTERADMIN || $isSYSADMIN || $isSYSAPPROVER;
  }

  protected function _draw_setup_load_popup_sizes(){
    $this->_popup['Product'] =          get_popup_params_for_report_form('product');
    $this->_popup['ProductGrouping'] =  get_popup_params_for_report_form('product_grouping');
  }

  protected function _draw_setup_load_product_groupings(){
    $old_ID = false;
    foreach ($this->_items as $item) {
      if ($old_ID != $item['groupingID']){
        $this->_product_groupings[$item['groupingID']] =
          array(
            'name' =>   $item['product_grouping_name'],
            'items' =>  array()
          );
        $old_ID = $item['groupingID'];
      }
    }
    foreach ($this->_items as $item){
      $this->_product_groupings[$item['groupingID']]['items'][] = $item;
    }
  }

  protected function _draw_totals(){
    $this->_html.= "<div style='margin:0 0 5px 0' class='fr'>\n";
    $this->_draw_totals_for_order();
    $this->_html.= "</div>";
  }

  protected function _draw_totals_for_order(){
    $this->_html.=
       "  <table class='order_cost_summary' width='200'>\n"
      ."    <tr>\n"
      ."      <th colspan='2' class='txt_l'>Totals</th>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td style='width:120px'><b>Items:</b></td>\n"
      ."      <td class='txt_r'>".$this->_totals['total_quantity']."</td>\n"
      ."    </tr>\n"
      ."    <tr>\n"
      ."      <td><b>Item Total:</b></td>\n"
      ."      <td class='txt_r'>".$this->_currency_symbol.two_dp($this->_totals['item_total'])."</td>\n"
      ."    </tr>\n"
      ."  </table>\n";
    if ($this->_totals['shipping']!=0) {
      $this->_html.=
         "  <img src='".BASE_PATH."img/spacer' height='5' width='1' class='b' alt=''/>\n"
        ."  <table class='order_cost_summary' width='200'>\n"
        ."    <tr>\n"
        ."      <td style='width:120px'><b>Shipping:</b></td>\n"
        ."      <td class='txt_r'>".$this->_currency_symbol.two_dp($this->_totals['shipping'])."</td>\n"
        ."    </tr>\n"
        ."  </table>\n";
    }
    if ($this->_totals['total_tax_all']!=0){
      $this->_html.=
         "  <img src='".BASE_PATH."img/spacer' height='5' width='1' class='b' alt=''/>\n"
        ."  <table class='order_cost_summary' width='200'>\n";
      foreach ($this->_totals['total_tax'] as $total_tax_item) {
        if ($total_tax_item['cost']!=0) {
          $this->_html.=
            "    <tr>\n"
           ."      <td style='width:120px'><b>"
           .$total_tax_item['name']." at ".$total_tax_item['rate']."%</b></td>\n"
           ."        <td class='txt_r'>"
           .$this->_currency_symbol
           .two_dp($total_tax_item['cost'])
           ."</td>\n"
           ."    </tr>\n";
        }
      }
      $this->_html.= "  </table>";
    }
    if ((float)$this->_totals['paymentMethodSurcharge']!=0) {
      $this->_html.=
         "  <img src='".BASE_PATH."img/spacer' height='5' width='1' class='b' alt=''/>\n"
        ."  <table class='order_cost_summary' width='200'>\n"
        ."    <tr>\n"
        ."      <td style='width:120px'><b>Sub Total</b></td>\n"
        ."        <td class='txt_r'>"
        .$this->_currency_symbol.two_dp($this->_totals['sub_total'])
        ."</td>\n"
        ."    </tr>\n"
        ."    <tr>\n"
        ."      <td style='width:120px'><b>"
        .get_var('TMethod')." "
        .((float)$this->_totals['paymentMethodSurcharge']>0 ? "Surcharge" : "Discount")
        ." at ".abs($this->_totals['paymentMethodSurcharge'])."%</b></td>\n"
        ."        <td class='txt_r'>"
        .($this->_totals['method_surcharge_cost']<0 ? "-" : "")
        .$this->_currency_symbol.two_dp(abs($this->_totals['method_surcharge_cost']))
        ."</td>\n"
        ."    </tr>\n"
        ."  </table>";
    }
    $this->_html.=
       "  <img src='".BASE_PATH."img/spacer' height='5' width='1' class='b' alt=''/>\n"
      ."  <table class='order_cost_summary' width='200'>\n"
      ."    <tr>\n"
      ."      <td style='width:120px'><b>Grand Total:</b></td>\n"
      ."      <td class='txt_r'>"
      .$this->_currency_symbol.two_dp($this->_totals['grand_total'])." "
      .$this->_currency_suffix
      ."<input id=\"total_cost\" type=\"hidden\" value=\"".$this->_totals['grand_total']."\" />"
      ."</td>\n"
      ."    </tr>\n"
      .($this->_paymentStatus ?
           "    <tr>\n"
          ."      <td style='width:120px'><b>Payment Status:</b></td>\n"
          ."      <td class='txt_r'>".$this->_paymentStatus."</td>\n"
          ."    </tr>\n"
       : "")
      ."  </table>\n";
  }

  protected function _get_columns_for_grouping($groupingID=""){
    $Obj_PGC =  new Product_Grouping_Column;
    $filtered = false;
    return $Obj_PGC->get_all_for_grouping($groupingID, $filtered);
  }


  protected function _has_catalogue_row_description($columns,$item){
    foreach ($columns as $column) {
      switch ($column['field']) {
        case "content":
          if ($item['content']!=''){
            return true;
          }
        break;
        case "image":
          if ($item['thumbnail_small']!='' && $this->_image) {
            return true;
          }
        break;
      }
    }
    return false;
  }

  public function get_version(){
    return VERSION_PRODUCT_CATALOGUE;
  }
}
?>