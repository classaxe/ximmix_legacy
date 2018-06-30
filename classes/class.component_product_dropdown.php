<?php
  define ("VERSION_COMPONENT_PRODUCT_DROPDOWN","1.0.2");
/*
Version History:
  1.0.2 (2014-04-16)
    1) Now has separate setup method and applies correct visibility to displayed items

  (Older version history in class.component_product_dropdown.txt)
*/
class Component_Product_Dropdown extends Component_Base {
  protected $_html;
  protected $_products;

  public function __construct(){
    $this->_ident =             "product_dropdown";
    $this->_parameter_spec =    array(
      'name_show' =>            array('match' => 'enum|0,1',  		'default' => '1',           'hint' => '0|1'),
      'name_char_limit' =>      array('match' => 'range|0,n',	    'default' => '10',          'hint' => '0..n'),
      'sort_order' =>           array('match' => 'enum|name,title', 'default' => 'name',        'hint' => 'name|title'),
      'title_show' =>           array('match' => 'enum|0,1',  		'default' => '1',           'hint' => '0|1'),
      'title_char_limit' =>     array('match' => 'range|0,n',       'default' => '30',          'hint' => '0|1')
    );
  }

  public function draw($instance='', $args=array(), $disable_params=false) {
    $this->_setup($instance,$args,$disable_params);
    $this->_draw_control_panel(true);
    $this->_html.=
       "<select name=\"".$this->_safe_ID."\" id=\"".$this->_safe_ID."\" style='font-family:courier' onchange=\"document.location='".BASE_PATH."'+geid_val('".$this->_safe_ID."');\">\n"
      ."  <option value=''></option>\n"
      .$this->_draw_options()
      ."</select>\n";
    return $this->_html;
  }

  protected function _draw_options(){
    global $page_vars;
    $html = '';
    $this->_max_len_name =  0;
    $this->_max_len_title = 0;
    foreach ($this->_products as $product){
      if (strlen($product['itemCode'])>$this->_max_len_name){
        $this->_max_len_name = strlen($product['itemCode']);
      }
      if (strlen($product['title'])>$this->_max_len_title){
        $this->_max_len_title = strlen($product['title']);
      }
    }
    $path_bits =    explode('/',trim((isset($page_vars['relative_URL']) ? $page_vars['relative_URL'] : ''),'/'));
    foreach ($this->_products as $product){
      $name =       $this->_get_truncated_name($product);
      $title =      $this->_get_truncated_title($product);
      $selected =   (count($path_bits)==1 && $path_bits[0] == $product['itemCode'] ? true : false);
      $html.=
         "<option value=\"".$product['itemCode']."\""
        .($this->_cp['name_char_limit']>0 || $this->_cp['title_char_limit']>0 ? " title=\"".$product['itemCode']." | ".$product['title']."\"" : "")
        .($selected ? " selected='selected'" : "")
        .">"
        .$name
        .($this->_cp['name_show']==1 && $this->_cp['title_show']==1 ? " | " : "")
        .$title
        ."</option>\n";

    }
    return $html;
  }

  protected function _get_truncated_name($product){
    $name = "";
    if ($this->_cp['name_show']==1){
      if ($this->_cp['name_char_limit']==0){
        $name = pad($product['itemCode'],$this->_max_len_name);
      }
      else {
        $name = substr($product['itemCode'],0,$this->_cp['name_char_limit']);
        if ($name!=$product['itemCode']){
          $name.="...";
        }
        else {
          $name.="   ";
        }
        $name = pad($name,$this->_cp['name_char_limit']+3);
      }
      $name = str_replace(" ","&nbsp;",$name);
    }
    return $name;
  }

  protected function _get_truncated_title($product){
    $title = "";
    if ($this->_cp['title_show']==1){
      if ($this->_cp['title_char_limit']==0){
        $title = $product['title'];
      }
      else {
        $title = substr($product['title'],0,$this->_cp['title_char_limit']);
        if ($title!=$product['title']){
          $title.="...";
        }
      }
    }
    return $title;
  }

  protected function _load_products(){
    $Obj_Product = new Product;
    $sql =
       "SELECT\n"
      ."  `active_date_from`,\n"
      ."  `active_date_to`,\n"
      ."  `enable`,\n"
      ."  `itemCode`,\n"
      ."  `permPUBLIC`,\n"
      ."  `permSYSAPPROVER`,\n"
      ."  `permSYSLOGON`,\n"
      ."  `permSYSMEMBER`,\n"
      ."  `title`\n"
      ."FROM\n"
      ."  `".$Obj_Product->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID` = ".SYS_ID;
    $records = $Obj_Product->get_records_for_sql($sql);
    foreach ($records as $r){
      $Obj_Product->load($r);
      if ($Obj_Product->is_visible($r)){
        $this->_products[] = $r;
      }
    }
  }


  protected function _sort_products(){
    switch ($this->_cp['sort_order']){
      case 'name':
        $order_arr =
          array(
            array('itemCode','a')
          );
      break;
      case 'title':
        $order_arr =
          array(
            array('title','a')
          );
      break;
    }
    $this->_products = $this->sort_records($this->_products, $order_arr);
  }

  protected function _setup($instance, $args, $disable_params){
    parent::_setup($instance, $args, $disable_params);
    $this->_load_products();
    $this->_sort_products();
  }

  public function get_version(){
    return VERSION_COMPONENT_PRODUCT_DROPDOWN;
  }
}
?>