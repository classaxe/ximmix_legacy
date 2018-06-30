<?php
define('VERSION_PIWIK','1.0.2');
/*
Version History:
  1.0.2 (2014-02-05)
    1) Piwik::get_outlinks() is incapable of accepting a filter so removed 'find'
    2) Added Piwik::get_outlink() - takes a pipe delimited find parameter
    3) Added Piwik::get_visit() - takes a pipe delimited find parameter
  1.0.1 (2013-10-17)
    1) Piwik::get_visits() is now recursive
    2) Piwik::_get_visits_for_subtable() now gets cumulative count for visits
       including submodes rather tha letting last matching entry blow the rest away.
  1.0.0 (2011-09-10)
    1) Initial release
*/
class Piwik extends System{
  private $_base_URL;
  private $_idSite;
  private $_token_auth;
  private $_URL;
  protected $_date;
  protected $_period;

  private function do_xml_request($params){
    $params[] =         "module=API";
    $params[] =         "format=xml";
    $params[] =         "idSite=".$this->_idSite;
    $params[] =         "token_auth=".$this->_token_auth;
    $this->_request =   implode('&',$params);
    $Obj_CURL =         new Curl($this->_base_URL,$this->_request);
    $xml_doc =$Obj_CURL->get();
    $out =              new SimpleXMLElement($xml_doc);
//    print $this->_base_URL.'?'.$this->_request."<pre>".print_r($out,true)."</false>";
    return $out;
  }

  public function setup($date_start='', $date_end=''){
    global $system_vars;
    $this->_date_start =    $date_start;
    $this->_date_end =      $date_end;
    $this->_get_period();
    $this->_get_date();
    $this->_base_URL =      trim($system_vars['URL'],'/').BASE_PATH.'piwik/';
    $this->_idSite =        $system_vars['piwik_id'];
    $this->_token_auth =    $system_vars['piwik_token'];
  }

  protected function _get_date(){
    if (strlen($this->_date_start)==4){  $this->_date_start.='-01-01'; }
    if (strlen($this->_date_start)==7){  $this->_date_start.='-01'; }
    if (strlen($this->_date_end)==4){    $this->_date_end.='-01-01'; }
    if (strlen($this->_date_end)==7){    $this->_date_end.='-01'; }
    $this->_date =
      ($this->_date_start && $this->_date_end ?
        ($this->_date_start==$this->_date_end ?
           $this->_date_start
         :
           $this->_date_start.",".$this->_date_end
        )
       :
         $this->_date_start
      );
  }

  protected function _get_period(){
    if (($this->_date_start && $this->_date_end) && $this->_date_start!==$this->_date_end){
      $this->_period='range';
      return;
    }
    switch (strlen($this->_date_start)){
      case 4:  $this->_period='year';  break;
      case 7:  $this->_period='month'; break;
      default: $this->_period='day';   break;
    }
  }

  public function get_outlink($date_start='',$date_end='',$urls=''){
    $this->setup($date_start, $date_end);
    $url_arr =  explode('|',$urls);
    $out =      array();
    foreach($url_arr as $url){
      if ($url===''){
        $out[$url] = array(
          'hits' =>   0,
          'visits' => 0
        );
        continue;
      }
      $params =       array();
      $params[] =     "method=Actions.getOutlink";
      $params[] =     "outlinkUrl=".$url;
      $params[] =     "period=".$this->_period;
      $params[] =     "date=".$this->_date;
      $xml =          $this->do_xml_request($params);
      $out[$url] =  array(
        'hits' =>   (int)(string)$xml->row->nb_hits,
        'visits' => (int)(string)$xml->row->nb_visits
      );
    }
    return $out;
  }

  public function get_outlinks($date_start='',$date_end=''){
    $this->setup($date_start, $date_end);
    $params =       array();
    $params[] =     "method=Actions.getOutlinks";
    $params[] =     "period=".$this->_period;
    $params[] =     "date=".$this->_date;
    $params[] =     "expanded=1";
    $xml =          $this->do_xml_request($params);
    $out = array();
    foreach ($xml->row as $type => $node){
      $url =    (string)$node->url;
      $out[$url] =  array(
        'hits' =>   (string)$node->nb_hits,
        'visits' => (string)$node->nb_visits
      );
      $this->_get_visits_for_subtable($node, $out);
    }
    ksort($out);
    return $out;
  }

  public function get_visit($date_start='', $date_end='', $urls=''){
    $out =      array();
    $this->setup($date_start, $date_end);
    $find_arr = explode('|',$urls);
    foreach($find_arr as $url){
      if ($url===''){
        $out[$url] = array(
          'hits' =>   0,
          'visits' => 0,
          'time_a' => 0,
          'time_t' => 0,
          'bounce' => 0
        );
        continue;
      }
      $params =       array();
      $params[] =     "method=Actions.getPageUrl";
      $params[] =     "pageUrl=".$url;
      $params[] =     "period=".$this->_period;
      $params[] =     "date=".$this->_date;
      $xml =          $this->do_xml_request($params);
      $out[$url] =  array(
        'hits' =>   (string)$xml->row->nb_hits,
        'visits' => (string)$xml->row->nb_visits,
        'time_a' => (string)$xml->row->avg_time_on_page,
        'time_t' => (string)$xml->row->sum_time_spent,
        'bounce' => (string)$xml->row->bounce_rate
      );
    }
//    y($xml);y($params);die;
    return $out;
  }

  public function get_visits($date_start='',$date_end='',$find=''){
    $this->setup($date_start, $date_end);
    $params =       array();
    $params[] =     "method=Actions.getPageUrls";
    $params[] =     "period=".$this->_period;
    $params[] =     "date=".$this->_date;
    $params[] =     "expanded=1";
    $params[] =     "filter_limit=-1";
    if ($find){
      $params[] =   "filter_column_recursive=label";
      $params[] =   "filter_pattern_recursive=".$find;
    }
    $xml =          $this->do_xml_request($params);
    $out = array();
//    y($xml);die;
    foreach ($xml->row as $type=>$node){
      $url_arr  =   explode('?',(string)$node->url);
      $url =        $url_arr[0];
      if (!isset($out[$url])){
        $out[$url] =  array(
          'hits' =>   0,
          'visits' => 0
        );
      }
      $out[$url]['hits']+=(int)(string)$node->nb_hits;
        $out[$url]['visits']+=(int)(string)$node->nb_visits;
      $this->_get_visits_for_subtable($node, $out);
    }
    ksort($out);
//    y($out);
    return $out;
  }

  public function _get_visits_for_subtable($node, &$out){
    foreach($node->subtable as $subtable){
      foreach ($subtable->row as $type=>$node){
        $url_arr  =   explode('?',(string)$node->url);
        $url =        $url_arr[0];
        if (!isset($out[$url])){
          $out[$url] =  array(
            'hits' =>   0,
            'visits' => 0
          );
        }
        $out[$url]['hits']+=(int)(string)$node->nb_hits;
        $out[$url]['visits']+=(int)(string)$node->nb_visits;
        $this->_get_visits_for_subtable($node, $out);
      }
    }
  }


  public function get_version(){
    return VERSION_PIWIK;
  }
}

?>