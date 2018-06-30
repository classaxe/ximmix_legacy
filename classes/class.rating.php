<?php
define('VERSION_RATING','1.0.5');
/*
Version History:
  1.0.5 (2014-01-28)
    1) Includes newlines after JS blocks in Rating::draw()

  (Older version history in class.rating.txt)
*/
class Rating extends Record {
  static $img =         "icon_ratings_13x13.gif";
  static $size =        13;
  static $max =         5;
  static $shown_css =   false;

  function __construct() {
  }

  function submit($id,$type,$value){
    $personID =     get_userID();
    $ratings_allow = $this->get_field('ratings_allow');
    if ($ratings_allow==false || $ratings_allow=='none' || ($ratings_allow=='registered' && !$personID)){
      die('You cannot rate this item');
    }
    $id = $this->_get_assign_type()."_".$this->_get_ID();
    $cookie_name =  "ECC_V_".SYS_ID;
    $cookie_value = (isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : '');
    $ip =           $_SERVER["REMOTE_ADDR"];
    $Obj =      new Activity;
    $rating =   $Obj->get_rating($this->_get_assign_type(),$this->_get_ID());
    $rating['votes'][] =
      array(
        'i' =>  $ip,
        's' =>  $value,
        'c' =>  $cookie_value,
        'd' =>  get_timestamp(),
        'u' =>  $personID
      );
    $score = 0;
    foreach($rating['votes'] as $vote){
      $score+=(int)$vote['s'];
    }
    $score = two_dp($score*100)/(count($rating['votes'])*Rating::$max);
    $Obj->_set_ID($rating['ID']);
    $data =
      array(
        'count_total_ratings' =>    (int)$rating['count_total_ratings']+1,
        'count_weighted_ratings' => (int)$rating['count_weighted_ratings']+1,
        'rating_percent' =>         $score,
        'rating_submissions' =>     addslashes(serialize($rating['votes'])),
        'sourceType' =>             $this->_get_assign_type(),
        'sourceID' =>               $this->_get_ID()
      );
    $Obj->update($data);
    $_SESSION['rated_'.$id] = true;
    return Rating::draw();
    die;
  }

  function draw_block($submode=false,$value=false) {
    $id = $this->_get_assign_type()."_".$this->_get_ID();
    switch($submode) {
      case 'rate':
        return Rating::submit($this->_get_assign_type(),$this->_get_ID(),$value);
      break;
      default:
        return "\n<div class='rating' id='rating_block_".$id."'>\n".Rating::draw()."</div>\n";
      break;
    }
  }

  function draw() {
    $check_IP = false;
    $id = $this->_get_assign_type()."_".$this->_get_ID();
    $Obj = new Activity;
    $rating = $Obj->get_rating($this->_get_assign_type(),$this->_get_ID());
//    y($rating);die;
    $value = ($rating['percent']*Rating::$max)/100;
    $votes = count($rating['votes']);
    $voted =    false;
    $v_score =  false;
    $v_date =   false;
    if ($votes) {
      $cookie_name =   "ECC_V_".SYS_ID;
      $cookie_value =  (isset($_COOKIE[$cookie_name]) ? $_COOKIE[$cookie_name] : '');
      $ip = $_SERVER["REMOTE_ADDR"];
      $personID = get_userID();
      foreach($rating['votes'] as $vote) {
        if (
          ($personID && $personID == $vote['u']) ||
          ($cookie_value == $vote['c']) ||
          isset($_SESSION['rated_'.$id]) ||
          ($ip == $vote['i'] && $check_IP)
        ){
          $_SESSION['rated_'.$id] = true;
          $voted =    true;
          $v_score =  $vote['s'];
          $v_date =   $vote['d'];
          break;
        }
      }
    }
    if (!Rating::$shown_css) {
      $width = Rating::$size*Rating::$max;
      Page::push_content(
        "style",
         "div.rating div.bg { position:relative;width:".$width."px; }\n"
        ."div.rating ul { left: -".($width)."px;}\n"
        ."div.rating div.current  { left: -".(2*$width)."px;}\n"
      );
      Page::push_content("javascript_onload","  rating_blocks_init();\n");
      Rating::$shown_css = true;
    }
    if (!$voted) {
      Page::push_content('javascript',"rating_blocks.push('".$id."');\n");
    }
    $out =
       ($voted ?
          "<div title='A rating of ".$v_score
         ." was awarded on ".$v_date."\nfrom your computer address'>\n"
       : ""
       )
      ."  <h2>"
      .($voted ? $this->_get_object_name()." rating" : "Rate this ".$this->_get_object_name())
      ."</h2>\n"
      ."  <div class='bg img'></div>"
      ."  <ul id='rating_".$id."'>\n";
    for ($i=1; $i<=Rating::$max; $i++) {
      $out.=
        "    <li class='img'><a href='#' onclick='return false' rel='nofollow'></a></li>\n";
    }
    $out.=
       "  </ul>\n"
      .($value ? "  <div class='current img' style='width:".(int)(Rating::$size*$value)."px;'></div>\n" : "")
      ."<br />\n"
      ."  <div class='score'>".round($value,2)." (".$votes." vote".($votes==1 ? "" : "s").")</div>\n"
      .($voted ? "</div>\n" : "");
    return $out;
  }

  public function get_version(){
    return VERSION_RATING;
  }
}
?>