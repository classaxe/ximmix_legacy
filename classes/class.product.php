<?php
define('VERSION_PRODUCT', '1.0.77');
/*
Version History:
  1.0.77 (2015-02-01)
    1) Changes to Product::get_records() to rename some expected arguments to conform to other classes:
         Old: order_by,      limit
         New: results_order, results_limit
    2) Class constant Product::fields renamed to Product::FIELDS
    2) Now PSR-2 Compliant

  (Older version history in class.product.txt)
*/
class Product extends Displayable_Item
{
    const FIELDS = 'ID, archive, archiveID, deleted, systemID, parentID, groupingID, seq, active_date_from, active_date_to, canBackorder, canPrintTaxReceipt, category, comments_allow, component_parameters, content, content_text, custom_1, custom_2, custom_3, custom_4, custom_5, custom_6, custom_7, custom_8, custom_9, custom_10, deliveryMethod, effective_date_from, effective_date_to, effective_period, effective_period_unit, enable, group_assign_csv, important, itemCode, keywords, media, meta_description, meta_keywords, module_creditsystem_creditPrice, module_creditsystem_creditValue, module_creditsystem_useCredits, price, price_non_refundable, quantity_available, quantity_maximum_order, quantity_unlimited, permPUBLIC, permSYSAPPROVER, permSYSLOGON, permSYSMEMBER, push_products, qb_ident, qb_name, ratings_allow, subtitle, tax_benefit_1_apply, tax_benefit_2_apply, tax_benefit_3_apply, tax_benefit_4_apply, tax_regimeID, themeID, thumbnail_small, thumbnail_medium, thumbnail_large, specialShippingInstructions, title, type, XML_data, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
    public $type;
    public $systemID;

    public function __construct($ID = "", $systemID = SYS_ID)
    {
        parent::__construct("product", $ID, $systemID);
        $this->_set_name_field('itemCode');
        $this->_set_assign_type('product');
        $this->_set_has_actions(true);
        $this->_set_has_categories(true);
        $this->_set_has_groups(true);
        $this->_set_has_keywords(true);
        $this->_set_has_enable_flag(true);       // Do now allow item to be seen if disabled
        $this->_set_has_publish_date(true);      // Do now allow item to be seen prior to publish date
        $this->_set_has_push_products(true);
        $this->_set_message_associated(' and all associated actions, group assignments and relationship records have');
        $this->_set_object_name('Product');
        $this->_set_path_prefix('product');      // Used to prefix items with IDs in path or to activate search
        $this->_set_type('product');
        $this->set_edit_params(
            array(
                'report' =>                 'product',
                'report_rename' =>          true,
                'report_rename_label' =>    'new Item Code',
                'icon_edit' =>              '[ICON]17 17 4148 Edit this Product[/ICON]',
                'icon_edit_disabled' =>     '[ICON]17 17 4165 (Edit this Product)[/ICON]',
                'icon_edit_popup' =>        '[ICON]18 18 4182 Edit this Product in a popup window[/ICON]'
            )
        );
        $this->_cp_vars_detail = array(
            'block_layout' =>               array(
                'match' =>      '',
                'default' =>    'Product',
                'hint' =>       'Name of Block Layout to use'
            ),
            'block_layout_push_products' => array(
                'match' =>      '',
                'default' =>    'Product',
                'hint' =>       'Name of Block Layout to use for Push Products'
            ),
            'cart_add_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'cart_checkout_link_show' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'cart_checkout_link_text' =>    array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Leave blank to use icon'
            ),
            'cart_emptycart_link_show' =>   array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'cart_emptycart_link_text' =>   array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Leave blank to use icon'
            ),
            'cart_skin' =>                  array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'cart_skin_image' =>            array(
                'match' =>      '',
                'default' =>
                    "/img/sysimg/cart_skin.gif,C6D6FF,4273E7,C6D6FF,3D4C8B,E7EFEF,A5A594,E7F7FF,4A63D6,0000FF,7BA5F7",
                'hint' =>       'Graphic to use for cart operations'
            ),
            'cart_skin_classname' =>        array(
                'match' =>      '',
                'default' =>    'cart_skin',
                'hint' =>       'Graphic to use for cart operations'
            ),
            'category_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'extra_fields_list' =>          array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'item_footer_component' =>      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below displayed Event'
            ),
            'links_open_image' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'product_grouping_show' =>      array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'product_price_show' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'subtitle_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1') ,
            'thumbnail_at_top' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height' =>           array(
                'match' =>      'range|1,n',
                'default' =>    '',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_image' =>            array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_link' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>            array(
                'match' =>      'range|1,n',
                'default' =>    '',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'title_linked' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            )
        );
        $this->_cp_vars_listings = array(
            'background' =>                 array(
                'match' =>      'hex3|',
                'default' =>    '',
                'hint' =>       'Hex code for background colour to use'
            ),
            'block_layout' =>               array(
                'match' =>      '',
                'default' =>    'Product',
                'hint' =>       'Name of Block Layout to use'
            ),
            'box' =>                        array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1|2'
            ),
            'box_footer' =>                 array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text below displayed Products'
            ),
            'box_header' =>                 array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Text above displayed Products'
            ),
            'box_rss_link' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title' =>                  array(
                'match' =>      '',
                'default' =>    'Products',
                'hint' =>       'text'
            ),
            'box_title_link' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'box_title_link_page' =>        array(
                'match' =>      '',
                'default' =>    'all-products',
                'hint' =>       'page'
            ),
            'box_width' =>                  array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..x'
            ),
            'cart_add_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'cart_checkout_link_show' =>    array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'cart_checkout_link_text' =>    array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Leave blank to use icon'
            ),
            'cart_emptycart_link_show' =>   array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'cart_emptycart_link_text' =>   array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Leave blank to use icon'
            ),
            'cart_skin' =>                  array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'cart_skin_image' =>            array(
                'match' =>      '',
                'default' =>
                    "/img/sysimg/cart_skin.gif,C6D6FF,4273E7,C6D6FF,3D4C8B,E7EFEF,A5A594,E7F7FF,4A63D6,0000FF,7BA5F7",
                'hint' =>       'Graphic to use for cart operations'
            ),
            'cart_skin_classname' =>        array(
                'match' =>      '',
                'default' =>    'cart_skin',
                'hint' =>       'Graphic to use for cart operations'
            ),
            'category_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_char_limit' =>         array(
                'match' =>      'range|0,n',
                'default' =>    '0',
                'hint' =>       '0..n'
            ),
            'content_plaintext' =>          array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'content_show' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'extra_fields_list' =>          array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'CSV list format: field|label|group,field|label|group...'
            ),
            'filter_category_list' =>       array(
                'match' =>      '',
                'default' =>    '*',
                'hint' =>       '*|CSV value list'
            ),
            'filter_category_master' =>     array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Optionally INSIST on this category'
            ),
            'filter_important' =>           array(
                'match' =>      'enum|,0,1',
                'default' =>    '',                'hint' => 'Blank to ignore, 0 for not important, 1 for important'
            ),
            'item_footer_component' =>      array(
                'match' =>      '',
                'default' =>    '',
                'hint' =>       'Name of component rendered below each displayed Product'
            ),
            'more_link_text' =>             array(
                'match' =>      '',
                'default' =>    '(More)',
                'hint' =>       'text for \'Read More\' link'
            ),
            'product_grouping_show' =>      array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'product_price_show' =>         array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'results_limit' =>              array(
                'match' =>      'range|0,n',
                'default' =>    '3',
                'hint' =>       '0..n'
            ),
            'results_order' =>              array(
                'match' =>      'enum|date,itemCode,title',
                'default' =>    'date',
                'hint' =>       'date|itemCode|title'
            ),
            'results_paging' =>             array(
                'match' =>      'enum|0,1,2',
                'default' =>    '0',
                'hint' =>       '0|1|2 - 1 for buttons, 2 for links'
            ),
            'subtitle_show' =>              array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_at_top' =>           array(
                'match' =>      'enum|0,1',
                'default' =>    '0',
                'hint' =>       '0|1'
            ),
            'thumbnail_height' =>           array(
                'match' =>      'range|1,n',
                'default' =>    '',
                'hint' =>       '|1..n or blank - height in px to resize'
            ),
            'thumbnail_image' =>            array(
                'match' =>      'enum|s,m,l',
                'default' =>    's',
                'hint' =>       's|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'
            ),
            'thumbnail_link' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_show' =>             array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'thumbnail_width' =>            array(
                'match' =>      'range|1,n',
                'default' =>    '',
                'hint' =>       '|1..n or blank - width in px to resize'
            ),
            'title_linked' =>               array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            ),
            'title_show' =>                 array(
                'match' =>      'enum|0,1',
                'default' =>    '1',
                'hint' =>       '0|1'
            )
        );
    }

    public function _draw_detail_include_js()
    {
        global $page_vars;
        if (
        isset($_REQUEST['command']) &&
        $_REQUEST['command']==$this->_ident."_detail_load"
        ) {
            return;
        }
        $safeID = $this->_ident."_".$this->_instance;
        Page::push_content(
            "javascript",
            "function ".$safeID."_cart(productID,qty,offset,related_object,related_objectID) {\n"
            ."  if(parseInt(this.value,10)<0){"
            ."    return false;\n"
            ."  }\n"
            ."  show_popup_please_wait('<b>Please wait...</b><br />Updating cart contents',200,60);"
            ."  geid_set('command','cart');\n"
            ."  geid_set('source',\n"
            ."     (typeof related_object!=='undefined' ? related_object : '')+\n"
            ."     '|'+\n"
            ."     (typeof related_objectID!=='undefined' ? related_objectID : '')\n"
            ."  );"
            ."  geid_set('targetID',productID);\n"
            ."  geid_set('targetValue',qty);\n"
            ."  geid('form').submit();\n"
            ."  return false;\n"
            ."}"
            ."function ".$safeID."_empty(offset) {\n"
            ."  geid('command').value='empty_cart';\n"
            ."  geid('form').submit();\n"
            ."}"
        );
    }

    protected function _draw_listings_include_js()
    {
        global $page_vars;
        if (
        isset($_REQUEST['command']) &&
        $_REQUEST['command']==$this->_ident."_".$this->_instance."_load"
        ) {
            return;
        }
        Page::push_content(
            "javascript",
            "function ".$this->_ident."_".$this->_instance."_cart(productID,qty,offset) {\n"
            ."  if(parseInt(this.value,10)<0){"
            ."    return false;\n"
            ."  }\n"
            ."  var post_vars='command=".$this->_ident."_".$this->_instance."_cart&targetID='+productID+"
            ."'&targetValue='+qty+'&offset='+offset;\n"
            ."  window.focus();\n"
            ."  if (btn=geid('".$this->_ident."_".$this->_instance."_previous')) {btn.disabled=true;}\n"
            ."  if (btn=geid('".$this->_ident."_".$this->_instance."_next')) {btn.disabled=true;}\n"
            ."  var fn = function(){hidePopWin(null)};\n"
            ."  show_popup_please_wait();\n"
            ."  ajax_post_streamed(base_url+'".trim($page_vars['path'], '/')."','"
            .$this->_ident."_".$this->_instance."_content',post_vars,fn);\n"
            ."  return false;\n"
            ."}\n"
            ."function ".$this->_ident."_".$this->_instance."_empty(offset) {\n"
            ."  var post_vars='command=".$this->_ident."_".$this->_instance."_empty&offset='+offset;\n"
            ."  window.focus();\n"
            ."  if (btn=geid('".$this->_ident."_".$this->_instance."_previous')) {btn.disabled=true;}\n"
            ."  if (btn=geid('".$this->_ident."_".$this->_instance."_next')) {btn.disabled=true;}\n"
            ."  var fn = function(){hidePopWin(null)};\n"
            ."  show_popup_please_wait();\n"
            ."  ajax_post_streamed(base_url+'".trim($page_vars['path'], '/')."','"
            .$this->_ident."_".$this->_instance."_content',post_vars,fn);\n"
            ."  return false;\n"
            ."}"
        );
    }

    protected function BL_cart_operations()
    {
        global $system_vars;
        if (!isset($this->_cp['cart_add_show']) || $this->_cp['cart_add_show']!='1') {
            return;
        }
        $quantity =     Cart::item_get_quantity($this->record['ID']);
        $productID =    $this->record['ID'];
        $offset =       ($this->_filter_offset ? $this->_filter_offset : 0);
        $safeID =       $this->_ident."_".$this->_instance;
        $show_price =   (isset($this->_cp['product_price_show']) && $this->_cp['product_price_show']=='1' ? 1 : 0);
        $js =           "onchange=\"".$safeID."_cart(".$this->record['ID'].",this.value,".$offset.")\"";
        $out = "<div class='fl'>";
        if ($show_price) {
            $out.=
                 "<div class='txt_r'>".$system_vars['defaultCurrencySymbol'].$this->record['price']."</div>"
                ."<div class='clear'>&nbsp;</div>";
        }
        $has_skin =             (isset($this->_cp['cart_skin']) && $this->_cp['cart_skin']=='1' ? 1 : 0);
        $cart_skin_image =      ($has_skin ? $this->_cp['cart_skin_image'] : '');
        $cart_skin_classname = (isset($this->_cp['cart_skin_classname']) && $this->_cp['cart_skin_classname']!='' ?
            $this->_cp['cart_skin_classname']
            :
            ''
        );
        $out.=
            Cart::draw_cart_controls(
                $safeID,
                $this->record['ID'],
                $quantity,
                $offset,
                $cart_skin_image,
                $cart_skin_classname
            )
            ."</div>\n";
        $this->_js .= "afb('cart_".$this->record['ID']."','".($has_skin ? 'cart' : 'qty')."');\n";
        if (Cart::has_items()) {
            if (isset($this->_cp['cart_checkout_link_show']) && $this->_cp['cart_checkout_link_show']=='1') {
                $out.=
                     "<a href='".BASE_PATH."checkout'>"
                    .(isset($this->_cp['cart_checkout_link_text']) && $this->_cp['cart_checkout_link_text'] ?
                         $this->_cp['cart_checkout_link_text']
                      :
                        "<div class='clear'>&nbsp;</div>"
                        ."<img src='".BASE_PATH."img/spacer'"
                        ." class='"
                        .($has_skin ? "cart_skin_bg cart_skin_checkout": "icons cart_noskin_checkout")
                        ."'"
                        ." alt='Proceed to Checkout'"
                        ." title='Proceed to Checkout' />"
                     )
                    ."</a>\n";
            }
            if (isset($this->_cp['cart_emptycart_link_show']) && $this->_cp['cart_emptycart_link_show']=='1') {
                $out.=
                     "<a href='#'"
                    ." onclick=\"if(confirm('Remove ALL items from your shopping cart?')){"
                    .$this->_ident."_".$this->_instance."_empty(".$offset.");"
                    ."}return false;\">"
                    .(isset($this->_cp['cart_emptycart_link_text']) && $this->_cp['cart_emptycart_link_text'] ?
                        $this->_cp['cart_emptycart_link_text']
                      :
                         "<div class='clear'>&nbsp;</div>"
                        ."<img src='".BASE_PATH."img/spacer'"
                        ." class='".($has_skin ? "cart_skin_bg cart_skin_emptycart": "icons cart_noskin_emptycart")."'"
                        ." alt='Empty ALL items from your cart'"
                        ." title='Empty ALL items from your cart' />"
                     )
                    ."</a>";
            }
        }
        return $out;
    }

    protected function BL_product_grouping()
    {
        if (!isset($this->_cp['product_grouping_show']) || $this->_cp['product_grouping_show']!='1') {
            return;
        }
        return $this->record['product_grouping_name'];
    }

    protected function BL_push_products()
    {
        if (isset($this->record['push_products'])) {
            return $this->draw_push_products();
        }
    }

    protected function BL_title()
    {
        return
             trim($this->record['title'])
            .($this->_is_expired_publication ? " <em>(Expired Product)</em>" : "")
            .($this->_is_pending_publication ? " <em>(Future Product)</em>" : "");
    }

    public function delete()
    {
        $sql =
             "DELETE FROM\n"
            ."  `product_relationship`\n"
            ."WHERE\n"
            ." `productID` IN(".$this->_get_ID().")";
        $this->do_sql_query($sql);
        parent::delete();
    }

    protected function _draw_listings_load_records()
    {
        $results = $this->get_records(
            array(
                'category' =>         $this->_cp['filter_category_list'],
                'category_master' =>  $this->_cp['filter_category_master'],
                'important' =>        $this->_cp['filter_important'],
                'offset' =>           $this->_filter_offset,
                'results_limit' =>    $this->_cp['results_limit'],
                'results_order' =>    $this->_cp['results_order']
            )
        );
        $this->_records =           $results['data'];
        $this->_records_total =     $results['total'];
    }

    protected function _draw_listings_set_shop_page_if_relevant()
    {
        global $page_vars;
        History::set('shop', BASE_PATH.trim($page_vars['path'], '/'));
    }

    public function draw_push_products()
    {
        if (!$this->record['push_products']) {
            return;
        }
        $Obj = new Product;
        $Obj->_safe_ID = 'push_products_for_'.$this->_get_ID();
        $Obj->_set_ID($this->record['push_products']);
        $Obj->_cp['box'] = 0;
        $Obj->_cp['box_rss_link'] = '';
        $Obj->_cp['box_title'] = '';
        $Obj->_cp['box_title_link'] = '';
        $Obj->_cp['box_title_link_page'] = '';
        $Obj->_cp['box_width'] = '';
        $Obj->_cp['title_linked'] = 1;
        $Obj->_cp['title_show'] = 1;
        $Obj->_cp['thumbnail_at_top'] = 1;
        $Obj->_cp['thumbnail_height'] = 0;
        $Obj->_cp['thumbnail_link'] = 1;
        $Obj->_cp['thumbnail_width'] = 60;
        $Obj->_cp['thumbnail_image'] = 's';
        $Obj->_current_user_rights = array('canEdit'=>0);
        $Obj->_html = "";
        $Obj->_records = $Obj->get_records_by_ID();
        $Obj_BlockLayout = new Block_Layout;
        $layoutName = $this->_cp['block_layout_push_products'];
        $blockLayoutID = $Obj_BlockLayout->get_ID_by_name($layoutName);
        $Obj_BlockLayout->_set_ID($blockLayoutID);
        if ($blockLayoutID) {
            $Obj->_block_layout = $Obj_BlockLayout->load();
            $Obj_BlockLayout->draw_css_include('listings');
        }
        for ($i=0; $i<count($Obj->_records); $i++) {
            $Obj->record = $Obj->_records[$i];
            $Obj->xmlfields_decode($Obj->record);
            if ($this->is_visible($Obj->record)) {
                $Obj->_html.=    $Obj->convert_Block_Layout($Obj->_block_layout['listings_item_detail']);
            }
        }
        return $Obj->_draw_listings_render();
    }

    public function draw_product_child_selector($name, $itemCode, $width = 150)
    {
        global $system_vars;
        $ID =      $this->get_ID_by_name($itemCode);
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isAPPROVER =       $isSYSAPPROVER || $isSYSADMIN || $isMASTERADMIN;
        if ($isAPPROVER) {
            $options = $this->get_children_by_parentID($ID, false, true);
        } else {
            $options = $this->get_children_by_parentID($ID, true, true);
        }
        $html =
             "<div style='width:".((int)$width-4)."px'>\n"
            ."<select class='formField fl' id=\"product_".$name."\" name=\"".$name."\" "
            ."style=\"width:".((int)$width-69)."px\" "
            ."onchange=\"loadTotalCost();\">\n"
            ."  <option value='' style='background-color: #f0f0f0;'>(Not selected)</option>\n";
        $js = "";
        if ($options) {
            $js.= "  cost_arr[\"".$name."\"] = {};\n";
            foreach ($options as $option) {
                $restricted = $option['restricted'];
                $out_of_date = $option['in_active_date_range']==0;
                $html.=
                     "  <option"
                    ." value=\"".$option['ID']."\""
                    ." title='"
                    .($out_of_date || $restricted ?
                         ($restricted ? " [R - Admins only ]" : "")
                        .($out_of_date ? " [D - Not in active Date range]" : "")
                      :
                        ""
                     )
                    ." ".strip_tags($option['content'])
                    ."'"
                    .($out_of_date || $restricted ?
                        " style='background-color: #ffe0e0;'"
                      :
                        ""
                     )
                    .">"
                    .($out_of_date ? "[D]" : "")
                    .($restricted ? "[R]" : "")
                    ." ".strip_tags($option['content'])
                    ."</option>\n";
                $js.=
                    pad("  cost_arr[\"".$name."\"][".$option['ID']."] = ", 50)
                    ."{'c':".$option['price'].",'tr':".$option['tax_regimeID']."};\n";
            }
        }
        $html.=
             "</select>\n"
            ."<div class='fl txt_r' style='width: 15px'>"
            .$system_vars['defaultCurrencySymbol']
            ."</div>"
            ."<div style='width:50px;float:right;'>\n"
            ."<input id=\"cost_".$name."\" class='formField txt_r' "
            ."style='width:50px;background-color: #f0f0f0;color: #404040;' type='text' onfocus='blur()' />\n"
            ."</div>"
            ."</div>";
        $js.=  "\n";
        return array(
            'html' => $html,
            'js' =>   $js
        );
    }

    public function draw_search_results($result)
    {
        global $page;
        $out = "";
        $offset =       $result['offset'];
        $found =        $result['count'];
        $limit =        $result['limit'];
        $retrieved =    count($result['results']);
        $search_name =  $result['search_name'];
        $search_text =  $result['search_text'];
        if ($found) {
            $out.=
                 $this->draw_search_results_paging_nav($result, $search_name)
                ."<table cellpadding='2' cellspacing='0' border='1' style='width:100%' class='table_border'>\n"
                ."  <tr class='table_header'>\n"
                .(isset($result['results'][0]['textEnglish']) ?
                 "    <th class='table_border txt_l'>Site</th>\n"
                 : "")
                ."    <th class='table_border txt_l'>".$result['search_name_label']."</th>\n"
                ."    <th class='table_border txt_l'>Title</th>\n"
                ."    <th class='table_border txt_l'>Summary</th>\n"
                ."    <th class='table_border'>Date</th>\n"
                ."  </tr>\n";
            foreach ($result['results'] as $row) {
                $itemCode = context($row['itemCode'], $search_name, 30);
                $title =    context($row['title'], $search_text, 30);
                $text =     context($row['content_text'], $search_text, 60);
                $date =     $row['active_date_from'];
                $local =    $row['systemID']==SYS_ID;
                $active =       $this->test_publish_date($row);
                $out.=
                     "  <tr class='table_data'"
                    .($active=='expired' ? " style='color:#808080' title='(Expired product)'" : "")
                    .($active=='pending' ? " style='color:#808080' title='(Future product)'" : "")
                    .">\n"
                    .(isset($row['textEnglish']) ?
                        "    <td class='table_border va_t'>".$row['textEnglish']."</th>\n"
                      :
                        ""
                     )
                    ."    <td class='table_border va_t'"
                    .($row['itemCode']!=strip_tags($itemCode) ?
                         " title=\""
                        .$row['itemCode']
                        .($active=='expired' ? " (Expired product)" : "")
                        .($active=='pending' ? " (Future product)" : "")
                        ."\""
                      :
                        ""
                     )
                    .">\n"
                    ."<a href=\""
                    .($local ? BASE_PATH : $row['URL'])
                    .$row['itemCode']."\">"
                    ."<b>"
                    .($itemCode!="" ? $itemCode : "(No Item Code)")
                    ."</b></a></td>\n"
                    ."    <td class='table_border va_t'"
                    .($row['title']!=strip_tags($title) ?
                         " title=\""
                        .$row['title']
                        .($active=='expired' ? " (Expired product)" : "")
                        .($active=='pending' ? " (Future product)" : "")
                        ."\""
                      :
                        ""
                     )
                    ."><b>"
                    .($title!="" ? $title : "(Untitled)")
                    ."</b></td>\n"
                    ."    <td class='table_border va_t'>".$text."</td>\n"
                    ."    <td class='table_border va_t txt_r nowrap'>".format_date($date)."</td>\n"
                    ."  </tr>\n";
            }
            $out.=
                "</table>\n<br />";
        }
        return $out;
    }

    public function export_sql($targetID, $show_fields)
    {
        $header =
             "Selected ".$this->_get_object_name().$this->plural($targetID)."\n"
            ."(with actions, group assignments and relationships)";
        $extra_delete =
             "DELETE FROM `product_relationship`   WHERE `productID` IN(".$targetID.");\n"
            ."DELETE FROM `push_product_assign`    WHERE `productID` IN(".$targetID.");\n";
        $Obj = new Backup;
        $extra_select =
            $Obj->db_export_sql_query(
                "`product_relationship`  ",
                "SELECT * FROM `product_relationship` WHERE `productID` IN(".$targetID.")",
                $show_fields
            )
            .$Obj->db_export_sql_query(
                "`push_product_assign`   ",
                "SELECT * FROM `push_product_assign`  WHERE `assignID` IN(".$targetID.")",
                $show_fields
            )
            ."\n";
        return parent::sql_export($targetID, $show_fields, $header, '', $extra_delete, $extra_select);
    }

    public function get_array_for_parent_itemcode_list($csv_list)
    {
        $items =    explode(",", $csv_list);
        $out = array();
        foreach ($items as $item) {
            $parent_record = $this->get_record_by_name($item);
            if ($this->is_visible($parent_record)) {
                $out[$item] = array();
                $children =             $this->get_children_by_name($item, "`product`.`seq`");
                foreach ($children as $child) {
                    if ($this->is_visible($child)) {
                        $out[$item][] = $child;
                    }
                }
            }
        }
        return $out;
    }

    public function get_children_by_parentID(
        $parentID = false,
        $apply_availability = false,
        $apply_visibility = false
    ) {
        if ($parentID===false) {
            $parentID = $this->_get_ID();
        }
        $records = parent::get_children_by_parentID($parentID, "`seq` ASC,`itemCode` ASC");
        if (!$records) {
            return false;
        }
        $records_enabled = array();
        foreach ($records as $record) {
            if ($record['enable']=='1') {
                $record['visible'] =              $this->is_visible($record);
                $record['in_active_date_range'] = $this->is_in_active_date_range($record);
                $record['restricted'] = (
                    $record['permPUBLIC']==0 &&
                    $record['permSYSLOGON']==0 &&
                    $record['permSYSMEMBER']==0 &&
                    $record['group_assign_csv']=='' &&
                    $record['permSYSAPPROVER']==1
                ) ? 1 : 0;
                $records_enabled[] = $record;
            }
        }
        $records = $records_enabled;
        if ($apply_availability) {
            $records_available = array();
            foreach ($records as $record) {
                if ($record['in_active_date_range']) {
                    $records_available[] = $record;
                }
            }
            $records = $records_available;
        }
        if ($apply_visibility) {
            $records_visible = array();
            foreach ($records as $record) {
                if ($record['visible']) {
                    $records_visible[] = $record;
                }
            }
            $records = $records_visible;
        }

        return $records;
    }

    public function get_ID_by_itemCode($itemCode)
    {
        $sql =
             "SELECT\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `product`\n"
            ."WHERE\n"
            ."  `itemCode` = \"".$itemCode."\" AND\n"
            ."  `systemID` = ".SYS_ID;
        return $this->get_field_for_sql($sql);
    }

    public function get_match_for_name($name, &$mode, &$ID)
    {
        $sql =
             "SELECT\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `product`\n"
            ."WHERE\n"
            ."  `itemCode` = \"".addslashes($name)."\" AND\n"
            ."  `systemID` = ".SYS_ID;
        $result = Product::get_field_for_sql($sql);
        if (!$result) {
            return false;
        }
        $mode = 'product';
        $ID =   $result;
        return true;
    }

    public function get_n_per_category($args)
    {
        $category_list =        $args['category_list'];
        $category_master =      (isset($args['category_master']) ? $args['category_master'] : "");
        $systemIDs_csv =        ($args['systemIDs_csv'] ? $args['systemIDs_csv'] : SYS_ID);
        $limit_per_category =   $args['limit_per_category'];
        $order =                $args['order'];
        $sql_arr =              array();
        $category_arr =         explode(",", str_replace(" ", "", $category_list));
        foreach ($category_arr as $category) {
            $sql_arr[] =
                 "(SELECT\n"
                ."  '".$category."' `cat`,\n"
                ."  `product`.`active_date_from` `date`,\n"
                ."  '' `URL`,\n"
                ."  '' `popup`,\n"
                ."  0 `comments_count`,\n"
                ."  `product`.*\n"
                ."FROM\n"
                ."  `product`\n"
                ."WHERE\n"
                .$this->get_permission_sql_for_viewer()
                ."  `systemID` IN(".$systemIDs_csv.") AND\n"
                ."  `archive` = 0 AND\n"
                ."  (`active_date_to` = '0000-00-00' OR `active_date_to` > NOW()) AND\n"
                ."  (`active_date_from` = '0000-00-00' OR `active_date_from` < NOW()) "
                .($category ? "AND\n  `category` REGEXP('".$category."')\n" : "")
                .($category_master ? "AND\n  `category` REGEXP('".$category_master."')\n" : "")
                .($order ? "ORDER BY\n  ".$order."\n" : "")
                .($limit_per_category ? "LIMIT 0,".$limit_per_category.")\n" : "");
        }
        $sql =  implode("UNION\n", $sql_arr);
  //    z($sql);
        $records = $this->get_records_for_sql($sql);
        $Obj_Category = new Category;
        $categories = array();
        foreach ($records as $record) {
            $categories[$record['cat']] = $record['cat'];
        }
        $categories = $Obj_Category->get_labels_for_values(
            "'".implode("','", array_keys($categories))."'",
            "'".get_class($this)." category'"
        );
  //    y($categories);
        foreach ($records as &$record) {
            $record['cat_label'] =    $categories[$record['cat']];
        }
        return $records;
    }

    public function get_number_of_related_orders()
    {
        $sql =
             "SELECT\n"
            ."  COUNT(DISTINCT `orderID`) `orders`\n"
            ."FROM\n"
            ."  `order_items`\n"
            ."WHERE\n"
            ."  `order_items`.`productID` IN(".$this->_get_ID().")";
        return $this->get_field_for_sql($sql);
    }

    public function get_parent_itemcodes()
    {
        $sql =
             "SELECT\n"
            ."  'product_parent_itemcodes' AS `category`,\n"
            ."  CONCAT(\n"
            ."    `pp`.`title`,\n"
            ."    ' (',COUNT(DISTINCT(`cp`.`ID`)),\n"
            ."    ' option',\n"
            ."    IF(COUNT(DISTINCT(`cp`.`ID`))=1,'','s'),\n"
            ."    ')') AS `title`,\n"
            ."  `pp`.`itemCode` AS `content`\n"
            ."FROM\n"
            ."  `product` AS `pp`\n"
            ."INNER JOIN `product` as `cp` ON\n"
            ."  `pp`.`ID` = `cp`.`parentID`\n"
            ."WHERE\n"
            ."  `pp`.`ID` != 1 AND\n"
            ."  `pp`.`systemID` = ".SYS_ID." AND\n"
            ."  `cp`.`systemID` = ".SYS_ID."\n"
            ."GROUP BY\n"
            ."  `pp`.`ID`\n"
            ."ORDER BY\n"
            ."  `content`";
  //    z($sql);die;
        return $this->get_records_for_sql($sql);

    }

    public function get_permission_sql_for_viewer()
    {
        if (!get_userID()) {
            return "  `permPUBLIC` = 1 AND\n";
        }
        $sql =
             "  (\n"
            ."    `permSYSLOGON` = 1"
            .($_SESSION['person']['permSYSMEMBER']==1 ?   " OR\n    `permSYSMEMBER` = 1" : "")
            .($_SESSION['person']['permSYSAPPROVER']==1 ? " OR\n    `permSYSAPPROVER` = 1" : "");
        $group_IDs = array();
        foreach ($_SESSION['person']['permissions'] as $level => $groups) {
            switch($level){
                case "VIEWER":
                case "EDITOR":
                case "APPROVER":
                case "ADMIN":
                    foreach ($groups as $group) {
                        $group_IDs[$group] = true;
                    }
                    break;
            }
        }
        $group_IDs = array_keys($group_IDs);
        foreach ($group_IDs as $groupID) {
            $sql.=
                " OR\n    ".$groupID." IN(`group_assign_csv`)";
        }
        $sql.=
            "\n  ) AND\n";
        return $sql;
    }

    public function get_price_by_name($name)
    {
        $this->_set_ID($this->get_ID_by_name($name));
        return $this->get_field('price');
    }

    public function get_products_filtered(
        $product_category_list = false,
        $product_grouping_list = false,
        $get_children = false
    ) {
        $out = array();
        $sql =
             "SELECT\n"
            ."  `product`.*,\n"
            ."  `product_grouping`.`name` AS `product_grouping_name`,\n"
            ."  `system`.`textEnglish` AS `systemTitle`\n"
            ."FROM\n"
            ."  `product`\n"
            ."INNER JOIN `system` ON\n"
            ."  `product`.`systemID` = `system`.`ID`\n"
            ."INNER JOIN `product_grouping` ON\n"
            ."  `product`.`groupingID` = `product_grouping`.`ID`\n"
            ."WHERE\n"
            .($product_category_list ?
                "  `product`.`category` REGEXP \"".implode("|", explode(',', $product_category_list))."\" AND\n"
              :
                ""
             )
            .($product_grouping_list ?
                "  `product_grouping`.`name` IN(\"".implode("\",\"", explode(",", $product_grouping_list))."\") AND\n"
              :
                ""
             )
            ."  `product_grouping`.`systemID` = ".SYS_ID." AND\n"
            ."  `product`.`systemID` = ".SYS_ID."\n"
            ."ORDER BY\n"
            ."  `product_grouping_name`,\n"
            ."  `product`.`seq`,"
            ."  `product`.`title`";
  //    z($sql);
        $records = $this->get_records_for_sql($sql);
        foreach ($records as $record) {
            if ($this->is_available($record)) {
                $out[] = $record;
                if ($get_children) {
                    $children =             $this->get_children_by_name($records['itemCode'], "`product`.`seq`");
                    foreach ($children as $child) {
                        if ($this->is_available($child)) {
                            $out[] = $child;
                        }
                    }
                }
            }
        }
        return $out;
    }

    public function get_products_for_productID_list($productID_csv = false)
    {
        if ($productID_csv===false) {
            $productID_csv = $this->_get_ID();
        }
        $sql =
             "SELECT\n"
            ."  `product`.`ID`,\n"
            ."  `product`.`systemID`,\n"
            ."  `product`.`parentID`,\n"
            ."  `product`.`groupingID`,\n"
            ."  `product`.`active_date_from`,\n"
            ."  `product`.`active_date_to`,\n"
            ."  `product`.`category`,\n"
            ."  `product`.`content`,\n"
            ."  `product`.`effective_date_from`,\n"
            ."  `product`.`effective_date_to`,\n"
            ."  `product`.`effective_period`,\n"
            ."  `product`.`effective_period_unit`,\n"
            ."  `product`.`enable`,\n"
            ."  `product`.`group_assign_csv`,\n"
            ."  `product`.`itemCode`,\n"
            ."  `product`.`price`,\n"
            ."  `product`.`permPUBLIC`,\n"
            ."  `product`.`permSYSAPPROVER`,\n"
            ."  `product`.`permSYSLOGON`,\n"
            ."  `product`.`permSYSMEMBER`,\n"
            ."  `product`.`seq`,\n"
            ."  `product`.`subtitle`,\n"
            ."  `product`.`tax_regimeID`,\n"
            ."  `product`.`thumbnail_small`,\n"
            ."  `product`.`thumbnail_medium`,\n"
            ."  `product`.`thumbnail_large`,\n"
            ."  `product`.`title`,\n"
            ."  `product`.`type`,\n"
            ."  `product_grouping`.`name` AS `product_grouping_name`,\n"
            ."  `system`.`textEnglish` AS `systemTitle`\n"
            ."FROM\n"
            ."  `product`\n"
            ."INNER JOIN `product_grouping` ON\n"
            ."  `product`.`groupingID` = `product_grouping`.`ID`\n"
            ."LEFT JOIN `system` ON\n"
            ."  `product`.`systemID` = `system`.`ID`\n"
            ."WHERE\n"
            ."  `product`.`ID` IN (".$productID_csv.") AND\n"
            ."  `product`.`archive`=0\n"
            ."ORDER BY\n"
            ."  `product_grouping`.`name`,\n"
            ."  `product`.`title`";
        $records = $this->get_records_for_sql($sql);
        $out = array();
        foreach ($records as $record) {
            $out[$record['ID']] = $record;
        }
        return $out;
    }

    public function get_records()
    {
        global $system_vars, $page;
        $args = func_get_args();
        $vars = array(
            'byRemote' =>         0,
            'category' =>         '*',
            'category_master' =>  '',
            'container_path' =>   '',
            'container_subs' =>   0,
            'DD' =>               '',
            'important' =>        '',
            'MM' =>               '',
            'memberID' =>         0,
            'offset' =>           0,
            'personID' =>         0,
            'results_limit' =>    0,
            'results_order' =>   'date',
            'what' =>             '',
            'YYYY' =>             ''
        );
        if (!$this->_get_args($args, $vars)) {
            die('Error - no parameters passed');
        }
        $limit =            $vars['results_limit'];
        $category =         $vars['category'];
        $offset =           $vars['offset'];
        $category_master =  $vars['category_master'];
        $results_order =    $vars['results_order'];
        $important =        $vars['important'];

        $now =              get_timestamp();
        $now_DD =           substr($now, 8, 2);
        $now_MM =           substr($now, 5, 2);
        $now_YYYY =         substr($now, 0, 4);
        $now_hh =           substr($now, 11, 2);
        $now_mm =           substr($now, 14, 2);
        $sql =
             "SELECT\n"
            ."  `product`.*,\n"
            ."  `product_grouping`.`name` `product_grouping_name`,\n"
            ."  `system`.`textEnglish` `systemTitle`,\n"
            ."  `system`.`URL` `systemURL`\n"
            ."FROM\n"
            ."  `product`\n"
            ."INNER JOIN `product_grouping` ON\n"
            ."  `product`.`groupingID` = `product_grouping`.`ID`\n"
            ."INNER JOIN `system` ON\n"
            ."  `product`.`systemID` = `system`.`ID`\n"
            ."WHERE\n"
            ."  `product`.`systemID` = ".$this->_get_systemID()." AND\n"
            .($category!="*" ?
                "  `product`.`category` REGEXP \"".implode("|", explode(',', $category))."\" AND\n"
              :
                ""
             )
            .($category_master ?
                "  `product`.`category` REGEXP \"".implode("|", explode(',', $category_master))."\" AND\n"
              :
                ""
             )
            .($important!=='' ?
                "  `product`.`important`=".$important." AND\n"
              :
                ""
             )
            ."  (\n"
            ."    `product`.`active_date_from` <= \"".$now_YYYY."-".$now_MM."-".$now_DD."\" OR\n"
            ."    `product`.`active_date_from` = '0000-00-00'\n"
            ."  ) AND\n"
            ."  (\n"
            ."    `product`.`active_date_to` > \"".$now_YYYY."-".$now_MM."-".$now_DD."\" OR\n"
            ."    `product`.`active_date_to` = '0000-00-00'\n"
            ."  ) AND\n"
            ."  1\n";
        $records = $this->get_records_for_sql($sql);
        if ($records === false) {
            return;
        }
        $out = array();
    // get into array
        foreach ($records as $row) {
            if ($this->is_available($row)) {
                $out[] = $row;
            }
        }
        switch($vars['results_order']){
            case "date":
                $order_arr =
                array(
                    array('active_date_from', 'a'),
                    array('systemTitle', 'a'),
                    array('title', 'a')
                );
                break;
            case "itemCode":
                $order_arr =
                array(
                    array('itemCode','a')
                );
                break;
            case "title":
                $order_arr =
                array(
                    array('title','a')
                );
                break;
        }
        $out = $this->sort_records($out, $order_arr);
        $total = count($out);
  //    return $out;
  //y($out); die;
        if ($limit!=0 || $offset) {
            $out = array_slice($out, ($offset ? $offset : 0), $limit);
        }
        $out = array('total'=>$total,'data'=>$out);
  //    y($out);
        return $out;
    }


    public function get_search_results($args)
    {
        $search_categories =
            (isset($args['search_categories']) ? $args['search_categories'] : "");
        $search_date_end =
            (isset($args['search_date_end']) ? $args['search_date_end'] : "");
        $search_date_start =
            (isset($args['search_date_start']) ? $args['search_date_start'] : "");
        $search_keywordIDs =
            (isset($args['search_keywordIDs']) ? $args['search_keywordIDs'] : "");
        $search_memberID =
            (isset($args['search_memberID']) ? $args['search_memberID'] : 0);
        $search_name =
            (isset($args['search_name']) ? $args['search_name'] : "");
        $search_name_label =
            (isset($args['search_name_label']) ? $args['search_name_label'] : "");
        $search_offset =
            (isset($args['search_offset']) ? $args['search_offset'] : 0);
        $search_sites =
            (isset($args['search_sites']) ? $args['search_sites'] : "");
        $search_text =
            (isset($args['search_text']) ? $args['search_text'] : "");
        $search_type =
            (isset($args['search_type']) ? $args['search_type'] : "*");
        $systems_csv =
            (isset($args['systems_csv']) ? $args['systems_csv'] : "");
        $systemIDs_csv =
            (isset($args['systemIDs_csv']) ? $args['systemIDs_csv'] : "");
        $limit =
            (isset($args['search_results_page_limit']) ? $args['search_results_page_limit'] : false);
        $sortBy =
            (isset($args['search_results_sortBy']) ? $args['search_results_sortBy'] : 'relevance');
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isSYSADMIN =       get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =      get_person_permission("SYSEDITOR");
        $userIsAdmin =      ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
        if (strlen($search_date_end)==4) {
            $search_date_end = $search_date_end."-12-31";
        }
        if (strlen($search_date_end)==7) {
            $search_date_end = $search_date_end."-31";
        }
        $out =
        array(
            'count' =>              0,
            'limit' =>              $limit,
            'offset' =>             $search_offset,
            'results' =>            array(),
            'search_name' =>        $search_name,
            'search_name_label' =>  $search_name_label,
            'search_text' =>        $search_text
        );
        if ($search_memberID) {
            return $out;
        }
        switch ($sortBy) {
            case 'date':
                $order = "  `p`.`active_date_from` DESC\n";
                break;
            case 'relevance':
                $order = ($search_text ?
                   "  `p`.`itemCode` LIKE \"".$search_text."%\" DESC,\n"
                  ."  `p`.`title` LIKE \" %".$search_text." %\" DESC,\n"
                  ."  `p`.`content` LIKE \" %".$search_text." %\" DESC,\n"
                  ."  `active_date_from` DESC,\n"
                  ."  `p`.`content` LIKE \"%".$search_text." %\" DESC,\n"
                  ."  `p`.`title` LIKE \"%".$search_text." %\" DESC\n"
                :
                  "  `p`.`active_date_from` DESC, `p`.`title`\n"
                );
                break;
            case 'title':
                $order = "  `p`.`title`\n";
                break;
        }
        $search_offset = (int)$search_offset;
        $sql =
            "SELECT\n"
      //      ."#$search_text\n"
            ."  `p`.`ID`,\n"
            ."  `p`.`systemID`,\n"
            ."  CONVERT(`p`.`content_text` USING utf8) AS `content_text`,\n"
            ."  `p`.`active_date_from`,\n"
            ."  `p`.`active_date_to`,\n"
            ."  `p`.`enable`,\n"
            ."  `p`.`group_assign_csv`,\n"
            ."  `p`.`itemCode`,\n"
            ."  `p`.`permPUBLIC`,\n"
            ."  `p`.`permSYSLOGON`,\n"
            ."  `p`.`permSYSMEMBER`,\n"
            ."  `p`.`title`,\n"
            ."  IF(`p`.`active_date_from` <= NOW() AND"
            ." (`p`.`active_date_to` ='0000-00-00' OR `p`.`active_date_to`>= NOW()),1,0) `active`,\n"
            .((string)$systemIDs_csv!=(string)SYS_ID ?
                 "  `s`.`textEnglish`,\n"
                ."  `s`.`URL` AS `systemURL`,\n"
              :
                ""
             )
            ."  `p`.`history_created_date`\n"
            ."FROM\n"
            ."  `product` `p`\n"
            .((string)$systemIDs_csv!=(string)SYS_ID ?
                 "INNER JOIN `system` `s` ON\n"
                ."  `p`.`systemID` = `s`.`ID`\n"
              :
                ""
             )
            .($search_keywordIDs!="" ?
                  "INNER JOIN `keyword_assign` `k` ON\n"
                 ."  `k`.`assignID` = `p`.`ID`\n"
              :
                ""
             )
            ."WHERE\n"
            ."  `p`.`systemID` IN (".$systemIDs_csv.") AND\n"
            .($userIsAdmin ?
                ""
              :
                "  (`p`.`active_date_from`='0000-00-00' OR `p`.`active_date_from`<= NOW()) AND\n"
             )
            .($userIsAdmin ?
                ""
              :
                "  (`p`.`active_date_to`='0000-00-00'   OR `p`.`active_date_to` >= NOW()) AND\n"
             )
            .($search_date_start!="" ?
                "  (`active_date_from` >= '".$search_date_start."') AND\n"
              :
                ""
             )
            .($search_date_end!="" ?
                "  (`active_date_from` < DATE_ADD('".$search_date_end."',INTERVAL 1 DAY)) AND\n"
              :
                ""
             )
            .($search_keywordIDs!="" ?
                "  `k`.`keywordID` IN(".$search_keywordIDs.") AND\n"
              :
                ""
             )
            .($search_text ?
                 "(\n"
                .($search_name=='' ? "" : "  `p`.`itemCode` LIKE \"%".$search_text."%\" OR\n")
                ."  `p`.`content_text` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`content_text` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`content_text` LIKE \"".$search_text."%\" OR\n"
                ."  `p`.`meta_description` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`meta_description` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`meta_description` LIKE \"".$search_text."%\" OR\n"
                ."  `p`.`meta_keywords` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`meta_keywords` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`meta_keywords` LIKE \"".$search_text."%\" OR\n"
                ."  `p`.`title` LIKE \"%".$search_text."\" OR\n"
                ."  `p`.`title` LIKE \"%".$search_text."%\" OR\n"
                ."  `p`.`title` LIKE \"".$search_text."%\"\n"
                .") AND\n"
            :
                ""
            )
            .($search_categories!="" ?
                "  `p`.`category` REGEXP \"".implode("|", explode(', ', $search_categories))."\" AND\n"
              :
                ""
             )
            ."  (`p`.`systemID`=".SYS_ID." OR `p`.`permPUBLIC` = 1)\n"
            .($search_keywordIDs!="" ?
                "GROUP BY `p`.`ID`\n"
              :
                ""
             )
            ."ORDER BY ".$order;
  //    z($sql);
        $records = $this->get_records_for_sql($sql);
        if ($records) {
            foreach ($records as $row) {
                if ($row['systemID']==SYS_ID) {
                    $visible = $userIsAdmin || $this->is_available($row);
                } else {
                    $visible = $row['permPUBLIC'];
                }
                if ($visible) {
                    if ($out['count']>=$search_offset && count($out['results'])<$limit) {
                        $out['results'][] = $row;
                    }
                    $out['count']++;
                }
            }
        }
        return $out;
    }

    public function handle_report_copy(&$newID, &$msg, &$msg_tooltip, $name)
    {
        return parent::try_copy($newID, $msg, $msg_tooltip, $name);
    }

    public function handle_report_delete(&$msg)
    {
        $targetID = $this->_get_ID();
        if (!$orders =   $this->get_number_of_related_orders()) {
            return parent::try_delete($msg);
        }
        $is_are =   (count(explode(",", $targetID))==1 ? 'is' : 'are');
        $msg =
        status_message(
            2,
            true,
            $this->_get_object_name(),
            '',
            $is_are." referenced in ".$orders." order".($orders==1 ? '' : 's')." - your deletion has been cancelled.",
            $targetID
        );
        return false;
    }

    public function is_visible($record)
    {
        return (
            Product::is_enabled($record) &&
            Product::is_in_active_date_range($record) &&
            parent::is_visible($record)
        );
    }

    protected static function is_available($record)
    {
        return (
            Product::is_enabled($record) &&
            Product::is_in_active_date_range($record) &&
            Product::is_visible($record)
        );
    }

    public static function is_enabled($record)
    {
        return $record['enable']==1;
    }

    public static function is_in_active_date_range($record)
    {
        $now = date('Y-m-d', time());
        return (
        ( $record['active_date_from']=='0000-00-00' || $now >= $record['active_date_from']) &&
        ( $record['active_date_to']=='0000-00-00' ||   $now < $record['active_date_to'])
        ? 1 : 0
        );
    }

    public function manage_actions()
    {
        return parent::manage_actions('actions_for_product');
    }

    public function manage_relationships()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('relationships_for_product', 1);
        }
        return
             "<h3 style='margin:0.25em'>Relationships for this ".$this->_get_object_name()."</h3>"
            .(get_var('selectID') ?
                draw_auto_report('relationships_for_product', 1)
             :
                 "<p style='margin:0.25em'>No Relationships -"
                ." this ".$this->_get_object_name()." has not been saved yet.</p>"
             );
    }

    protected function test_publish_date($record = false)
    {
        if (!$record) {
            $record = $this->record;
        }
        if ($record['active_date_from']!='0000-00-00') {
            sscanf($record['active_date_from'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
            if (mktime(0, 0, 0, $_MM, $_DD, $_YYYY)>time()) {
                return "pending";
            }
        }
        if ($record['active_date_to']!='0000-00-00') {
            sscanf($record['active_date_to'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
            if (mktime(23, 59, 59, $_MM, $_DD, $_YYYY)<time()) {
                return "expired";
            }
        }
        return "good";
    }

    public function try_delete_item()
    {
        if (!$this->_try_delete_item_check_user_rights()) {
            $this->_try_delete_item_msg_insufficient_rights();
            return false;
        }
        if ($orders =   $this->get_number_of_related_orders()) {
            $msg =
                "<b>Error</b><br /><br />This Product is referenced in ".$orders." order".($orders==1 ? '' : 's')
                ." - your deletion has been cancelled.";
            Page::push_content(
                'javascript_onload',
                "  popup_msg=\"".$msg."\";popup_dialog("
                ."'Item Delete',\"<div style='padding:4px'>\"+popup_msg+\"</div>\",'320',120,'OK','',''"
                .");"
            );
            return false;
        }
        $this->delete();
        return true;
    }

    public function get_version()
    {
        return VERSION_PRODUCT;
    }
}
