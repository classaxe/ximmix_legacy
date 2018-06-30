<?php
define('VERSION_GWSOCKET','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class gwSocket{
  var $Name="gwSocket";
  var $Version="0.1";
  var $userAgent="Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.0)";
  var $headers;
  var $page="";
  var $result="";
  var $redirects=0;
  var $maxRedirects=3;
  var $error="";
  var $cookies="";

  function getUrl( $url ) {
    global $system_vars,$page,$mode,$submode,$report_name;
    $retVal="";
    switch ($url) {
      case "":
      case "?mode=rss&amp;submode=config":
        return "";
      break;
    }
    $url_parsed = parse_url($url);
//    y($url_parsed);die;
    $scheme =   (isset($url_parsed["scheme"]) ?   $url_parsed["scheme"]   : "");
    $host =     (isset($url_parsed["host"]) ?     $url_parsed["host"]     : "");
    $port =     (isset($url_parsed["port"]) ?     $url_parsed["port"]     : "80");
    $user =     (isset($url_parsed["user"]) ?     $url_parsed["user"]     : "");
    $pass =     (isset($url_parsed["pass"]) ?     $url_parsed["pass"]     : "");
    $path =     (isset($url_parsed["path"]) ?     $url_parsed["path"]     : "/");
    $query =    (isset($url_parsed["query"]) ?    $url_parsed["query"]    : "");
    $anchor =   (isset($url_parsed["fragment"]) ? $url_parsed["fragment"] : "");

    $uri_arr = array();
    if ($page!="" )         { $uri_arr[] = "page=$page"; }
    if ($mode!="" )         { $uri_arr[] = "mode=$mode"; }
    if ($submode!="" )      { $uri_arr[] = "submode=$submode"; }
    if ($report_name!="" )  { $uri_arr[] = "report_name=$report_name"; }

    $uri = (count($uri_arr) ? implode("&",$uri_arr) : "");
    $referer = $system_vars['URL']."/".($uri!="" ? "?$uri" : "");
    if (!empty($host)){
      if(@$fp = fsockopen($host, $port, $errno, $errstr, 2)){
        $path .= $query?"?$query":"";
        $path .= $anchor?"$anchor":"";
//        die($path);
        $request =
           "GET $path "
          ."HTTP/1.0\r\n"
          .($this->cookies!="" ? "Cookie: ".$this->cookies."\r\n" : "")
          ."Host: $host\r\n"
          ."Referer: $referer\r\n"
          ."Connection: Close\r\n"
          ."User-Agent: $this->userAgent\r\n";
//          print$request;die;
        if(!empty($user)) {
          $request.=
             "Authorization: Basic "
            .base64_encode("$user:$pass")
            ."\r\n";
        }
        $request .= "\r\n";
//        print ($request);
        fputs($fp, $request);
        while (!feof($fp)) {
          $retVal.=fgets($fp, 128);
        }
        fclose($fp);
      }
      else {
        $this->error="Failed to make connection to host.";//$errstr;
      }
      $this->result=$retVal;
//      print ($this->result);die;
      $this->headers=$this->parseHeaders(trim(substr($retVal,0,strpos($retVal,"\r\n\r\n"))));
      $this->page=trim(stristr($retVal,"\r\n\r\n"))."\n";
      if(isset($this->headers['Location'])){
        $this->redirects++;
        if($this->redirects<$this->maxRedirects){
          if (isset($this->headers['Set-Cookie'])){
            $this->cookies = $this->headers['Set-Cookie'];
          }
          $location="http://".$host."/".$this->headers['Location'];
          $this->headers=array();
          $this->result="";
          $this->page="";
          $this->getUrl($location);
//          print $this->cookies."okay?";
        }
      }
      if (isset($this->headers['Status'])) {
        $status = $this->headers['Status'];
        if(preg_match("/403/",$status)){
          $this->page = "Denied: <a href=\"$url\" rel='external'>[url]</a>";
        }
        if(preg_match("/404/",$status)){
          $this->page = "Not found: <a href=\"$url\" rel='external'>[url]</a>";
        }
      }
    }
    return (!$retVal="");
  }

  function parseHeaders($s){
    $hdr = array();
    $h=preg_split("/[\r\n]/",$s);
    foreach($h as $i){
      $i=trim($i);
      if(strstr($i,":")){
        list($k,$v)=explode(":",$i);
        $hdr[$k]=substr(stristr($i,":"),2);
      }
      else{
        if(strlen($i)>3) {
          $hdr[]=$i;
        }
      }
    }
    if(isset($hdr[0])){
      $hdr['Status']=$hdr[0];
      unset($hdr[0]);
    }
    return $hdr;
  }
  public function get_version(){
    return VERSION_GWSOCKET;
  }
}
?>