<?php
  define ("VERSION_COMPONENT_SEARCH_WORD_CLOUD","1.0.0");
/*
Version History:
  1.0.0 (2011-12-31)
    1) Initial release - moved from Component class
*/
class Component_Search_Word_Cloud extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false) {
    $ident =            "search_word_cloud";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'colour_min' =>     array('match' => 'hex3|#808080',  'default'=>'#808080',   'hint'=>'Hex colour code for minimum significance'),
      'colour_max' =>     array('match' => 'hex3|#404040',  'default'=>'#800000',   'hint'=>'Hex colour code for maximum significance'),
      'min_characters' => array('match' => 'range|1,n',		'default'=>'4',         'hint'=>'Minimum number of characters for words shown'),
      'max_matches' =>    array('match' => 'range|1,n',		'default'=>'1000',      'hint'=>'Maximum number of matches for words shown'),
      'min_matches' =>    array('match' => 'range|1,n',		'default'=>'5',         'hint'=>'Minimum number of matches for words shown')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $now =          get_timestamp();
    sscanf(
      $now,
      "%4s-%2s-%2s %2s:%2s:%2s",
      $now_YYYY, $now_MM, $now_DD, $now_hh, $now_mm, $now_ss
    );
    $sql =
       "SELECT\n"
      ."  `content_text`\n"
      ."FROM\n"
      ."  `postings`\n"
      ."WHERE\n"
      ."  (\n"
      ."    (`postings`.`date` < \"".$now_YYYY."-".$now_MM."-".$now_DD."\") OR\n"
      ."    (`postings`.`date` = \"".$now_YYYY."-".$now_MM."-".$now_DD."\" AND `postings`.`time_start`<=\"".$now_hh.":".$now_mm."\") OR\n"
      ."    (`postings`.`date`='0000-00-00')\n"
      ."  ) AND\n"
      ."  (\n"
      ."    `postings`.`date_end` > \"".$now_YYYY."-".$now_MM."-".$now_DD."\" OR\n"
      ."    `postings`.`date_end`='0000-00-00'\n"
      ."  ) AND\n"
      ."  `systemID` = ".SYS_ID;
    $records = $this->get_records_for_sql($sql);
    $text_arr = array();
    foreach($records as $record){
      $text_arr[] = $record['content_text'];
    }
    $text = implode(" ",$text_arr);
    $wc = html_entity_decode($text);
    $wc = trim(str_replace("><", "> <", $wc));
    $wc = strip_tags($wc);
    $wc = strToLower($wc);
    # remove 'words' that don't consist of alphanumerical characters or punctuation
    $pattern = "#[^(\w|\d|\'|\"|\.|\!|\?|;|,|\\|\/|\-|:|\&|@)]+#";
    $wc = trim(preg_replace($pattern, " ", $wc));
    # remove one-letter 'words' that consist only of punctuation
    $wc = trim(preg_replace("#\s*[(\'|\"|\.|\!|\?|;|,|\\|\/|\-|_|:|\&|@)]\s*#", " ", $wc));
    # remove superfluous whitespace
    $wc = preg_replace("/\s\s+/", " ", $wc);
    # split string into an array of words
    $wc = explode(" ", $wc);
    # remove empty elements
    $wc = array_filter($wc);
    $words = array_count_values($wc);
    ksort($words);
    $total = 0;
    $filtered = array();
    $max = 0;
    foreach ($words as $word=>$count){
      if (!is_numeric(substr($word,0,1)) &&
        strlen($word)>$cp['min_characters'] &&
        $count>=$cp['min_matches'] &&
        $count<=$cp['max_matches']
      ){
        $filtered[$word] = $count;
        $total++;
        if ($count>$max){
          $max=$count;
        }
      }
    }
    $color_min=$cp['colour_min'];
    $color_max=$cp['colour_max'];
    foreach ($filtered as $word=>$count){
      $out.=
         "<a href=\"".(BASE_PATH."search_results/?search_text=".$word)."\""
        ." style=\"font-size:".(int)(500*$count/$max)."%;"
        ."color:".get_color_for_weight(100*$count/$max, $color_min, $color_max)
        ."\""
        ." title=\"".$word." (".$count.")\">"
        .$word
        ."</a> ";
    }
    return
       $out
      ."<p>Total: ".$total."</p>\n"
      ."<p>Criteria used:<br />\n"
      ."Minimum letters per word: ".$cp['min_characters']." &nbsp; "
      ."Minimum number of matches: ".$cp['min_matches']." &nbsp; "
      ."Maximum number of matches: ".$cp['max_matches']."</p>";
      ;
  }

  public function get_version(){
    return VERSION_COMPONENT_SEARCH_WORD_CLOUD;
  }
}
?>