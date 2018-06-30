<?php
  define ("VERSION_COMPONENT_CATEGORY_STACKER","1.0.3");
/*
Version History:
  1.0.3 (2013-06-07)
    1) Changed the following CPs:
         Old: 'filter_limit_per_category',  'filter_order_by'
         New: 'results_limit_per_category', 'results_order'

  (Older version history in class.component_category_stacker.txt)
*/
class Component_Category_Stacker extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false) {
    $ident =            "category_stacker";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'author_show' =>                          array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'category_show' =>                        array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'content_char_limit' =>                   array('match' => 'range|0,n',							'default' =>'0',          'hint'=>'0..n'),
      'content_plaintext' =>                    array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'content_show' =>                         array('match' => 'enum|0,1',  							'default' =>'1',          'hint'=>'0|1'),
      'content_use_summary' =>                  array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'date_show' =>                            array('match' => 'enum|0,1',  							'default' =>'1',          'hint'=>'0|1'),
      'extra_fields_list' =>                    array('match' => '',									'default' =>'',           'hint'=>'CSV list format: field|label|group,field|label|group...'),
      'filter_category_list' =>                 array('match' => '',									'default' =>'',           'hint'=>'CSV list of categories'),
      'filter_sites_list' =>                    array('match' => '',									'default' =>'',           'hint'=>'CSV list of site URLs'),
      'filter_type' =>                          array('match' => 'enum|Article,Product',    			'default' =>'Article',    'hint'=>'Article|Product'),
      'links_point_to_URL' =>                   array('match' => 'enum|0,1',                            'default' =>'0',          'hint'=>'0|1 - If there is a URL, both title and thumbnails links go to it'),
      'more_link_text' =>                       array('match' => '',									'default' =>'(More)',     'hint'=>'text for more link'),
      'related_show' =>                         array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'results_limit_per_category' =>           array('match' => 'range|1,n',							'default' =>'1',          'hint'=>'Max articles per tab'),
      'results_order' =>                        array('match' => 'enum|latest,title',					'default' =>'latest',     'hint'=>'|latest|title'),
      'subtitle_show' =>                        array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'thumbnail_at_top' =>                     array('match' => 'enum|0,1',							'default' =>'1',          'hint'=>''),
      'thumbnail_image' =>                      array('match' => 'enum|s,m,l',							'default' =>'s',          'hint'=>'s|m|l - Choose only \'s\' unless Multiple-Thumbnails option is enabled'),
      'thumbnail_link' =>                       array('match' => 'enum|0,1',  							'default' =>'0',          'hint'=>'0|1'),
      'thumbnail_show' =>                       array('match' => 'enum|0,1',                            'default' =>'1',          'hint'=>'0|1'),
      'thumbnail_width' =>                      array('match' => 'range|1,n',							'default' =>'',           'hint'=>'|1..n'),
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $isMASTERADMIN =    get_person_permission("MASTERADMIN");
    $isSYSADMIN =       get_person_permission("SYSADMIN");
    $isSYSEDITOR =      get_person_permission("SYSEDITOR");
    $canEdit =        ($isMASTERADMIN || $isSYSADMIN || $isSYSEDITOR);
    switch ($cp['filter_type']){
      case 'Article':
        $Obj =          new Article;
        $add_form =     'articles';
      break;
      case 'Product':
        $Obj =          new Product;
        $add_form =     'product';
      break;
    }
    if ($canEdit){
      $popup =    get_popup_size($add_form);
    }
    $systemIDs_csv =    System::get_IDs_for_URLs($cp['filter_sites_list']);
    switch ($cp['results_order']) {
      case "latest":
        $order = "`date` DESC";
      break;
      case "title":
        $order = "`title` ASC";
      break;
      default:
        $order = false;
      break;
    }
    $args =
      array(
        'category_list' =>      $cp['filter_category_list'],
        'systemIDs_csv' =>      $systemIDs_csv,
        'limit_per_category' => $cp['results_limit_per_category'],
        'order' =>              $order
      );
    $records = $Obj->get_n_per_category($args);
    if ($records) {
      $Obj_Category = new Category;
      if (!isset($selected_section)) {
        $selected_section = $records[0]['cat'];
      }
      $args =  array(
        'author_show' =>          $cp['author_show'],
        'category_show' =>        $cp['category_show'],
        'content_plaintext' =>    $cp['content_plaintext'],
        'content_char_limit' =>   $cp['content_char_limit'],
        'content_show' =>         $cp['content_show'],
        'content_use_summary' =>  $cp['content_use_summary'],
        'extra_fields_list' =>    $cp['extra_fields_list'],
        'date_show' =>            $cp['date_show'],
        'links_point_to_URL' =>   $cp['links_point_to_URL'],
        'more_link_text' =>       $cp['more_link_text'],
        'related_show' =>         $cp['related_show'],
        'subtitle_show' =>        $cp['subtitle_show'],
        'thumbnail_at_top' =>     $cp['thumbnail_at_top'],
        'thumbnail_image' =>      $cp['thumbnail_image'],
        'thumbnail_link' =>       $cp['thumbnail_link'],
        'thumbnail_show' =>       $cp['thumbnail_show'],
        'thumbnail_width' =>      $cp['thumbnail_width']
      );
      $categories = array();
      foreach($records as $record) {
        $categories[$record['cat']] = $record['cat'];
      }
      $categories = $Obj_Category->get_labels_for_values("'".implode("','",array_keys($categories))."'","'".$cp['filter_type']." category'");
//      y($categories);
      $out.= "<div id=\"".$ident."_".strToLower($instance)."\">\n";
      foreach ($categories as $value=>$text){
        $these = array();
        foreach ($records as $record) {
          if (strToLower($record['cat'])==strToLower($value)){
            $these[] = $record;
          }
        }
        $out.=
          ($canEdit ?
              "<a class='fl' href=\"#\" onclick=\"details("
             ."'".$add_form."','','".$popup['h']."','".$popup['w']."','','','','&amp;category=".$value."'"
             .");return false;\"  title='Add ".$cp['filter_type']." for ".$value." category&hellip;'>"
             ."[ICON]11 11 1188 [/ICON]</a>\n"
            : ""
           )
          .$Obj->draw_from_recordset($these,$args);
      }

      $out.= "</div>";
    }
    else {
      $out .= __CLASS__."::".__FUNCTION__."()<br />\nNo records available to display.";
    }
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_CATEGORY_STACKER;
  }
}
?>