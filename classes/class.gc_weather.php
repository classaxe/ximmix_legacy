<?php
define("VERSION_GC_WEATHER","1.0.0");
/*
Version History:
  1.0.0 (2010-12-10)
    Initial release
  0.
*/

class GC_Weather{
  private function _weather_css(){
    static $done = false;
    if (!$done){
      Page::push_content(
        'head_top',
         "<link href=\"http://www.weatheroffice.gc.ca/city/styles/newcity.css\" "
        ."media=\"screen, print\" rel=\"stylesheet\" type=\"text/css\" />"
      );
      $done = true;
    }
  }

  function draw_current_conditions($station, $ident=''){
    $this->_weather_css();
    $baseURL = 'http://www.weatheroffice.gc.ca';
    $contents = file_get_contents($baseURL.'/city/pages/'.$station.'_metric_e.html');
    $dd = new DOMDocument;
    @$dd->loadHTML($contents);
    $xp = new DOMXPath($dd);
    // replace any HREF or SRC attributes that begin with / with the fully q URL
    $nodeList =
      $xp->query(
        "//div[@id='currentcond']//*[starts-with(@href, '/') or starts-with(@src, '/')]"
      );
    foreach ($nodeList as $node) {
      if ($node->hasAttribute('href')) {
        $node->setAttribute('href', $baseURL . $node->getAttribute('href'));
        $node->setAttribute('rel', 'external');
      }
      if ($node->hasAttribute('src')) {
        $node->setAttribute('src', $baseURL . $node->getAttribute('src'));
      }
    }
    $ccText =
      $xp->query(
        "//div[@id='conditionscontainer']/div[@class='toprow']/div[@class='lefttab']"
      )->item(0);
    $ccText->nodeValue = "";
    $ccAnchor = $ccText->appendChild($dd->createElement("a"));
    $ccAnchor->setAttribute('href', 'http://www.weatheroffice.gc.ca/city/pages/'.$station.'_metric_e.html');
    $ccAnchor->setAttribute('rel', 'external');
    $ccAnchor->nodeValue = $ident;
    $currentConditionsDIV = $xp->query("//div[@id='conditionscontainer']")->item(0);
    $content = $dd->saveXML($currentConditionsDIV);
    $c_bits =  explode('<noscript>',$content);
    $c_bits2 =  explode('</script>',$c_bits[1]);
    $content = $c_bits[0].$c_bits2[1];
    return $content."<br style='clear:both;' />";
  }

  function draw_long_range_forecast($station, $ident=''){
    $this->_weather_css();
    $baseURL = 'http://www.weatheroffice.gc.ca';
    $contents = file_get_contents($baseURL.'/city/pages/'.$station.'_metric_e.html');
    $dd = new DOMDocument;
    @$dd->loadHTML($contents);
    $xp = new DOMXPath($dd);
    // replace any HREF or SRC attributes that begin with / with the fully q URL
    $nodeList = $xp->query("//div[@id='forecastData']//*[starts-with(@href, '/') or starts-with(@src, '/')]");
    foreach ($nodeList as $node) {
      if ($node->hasAttribute('href')) {
        $node->setAttribute('href', $baseURL . $node->getAttribute('href'));
        $node->setAttribute('rel', 'external');
      }
      if ($node->hasAttribute('src')) {
        $node->setAttribute('src', $baseURL . $node->getAttribute('src'));
      }
    }
    $ccText =
      $xp->query(
        "//div[@id='forecastData']/div[@class='toprow']/div[@class='lefttab ']"
      )->item(0);
    $ccText->nodeValue = "";
    $ccAnchor = $ccText->appendChild($dd->createElement("a"));
    $ccAnchor->setAttribute('href', 'http://www.weatheroffice.gc.ca/city/pages/'.$station.'_metric_e.html');
    $ccAnchor->setAttribute('rel', 'external');
    $ccAnchor->nodeValue = $ident;
    $currentConditionsDIV = $xp->query("//div[@id='forecastData']")->item(0);
    $content = $dd->saveXML($currentConditionsDIV);
    $c_bits =  explode('<noscript>',$content);
    $c_bits2 =  explode('</script>',$c_bits[1]);
    $content = $c_bits[0].$c_bits2[1];
    return $content."<br style='clear:both;' />";
  }

  public function draw_radar($station='WKR',$ident=''){
    $baseURL = 'http://www.weatheroffice.gc.ca';
    $contents = file_get_contents($baseURL."/radar/index_e.html?id=".$station);
    $dd = new DOMDocument;
    @$dd->loadHTML($contents);
    $xp = new DOMXPath($dd);
    // locate the img id="merged-image"
    $mergedImage = $xp->query("//div[@id='animation']//img[@id='merged-image']")->item(0);
    $mergedImage->setAttribute('src', $baseURL . "/radar/" . $mergedImage->getAttribute('src'));
    return
       "<div class='lefttab'><a class='extratag' href='http://www.weatheroffice.gc.ca/radar/index_e.html?id=".$station."' rel='external'>".$ident."</a></div>"
      ."<br style='clear:both;' />"
      .$dd->saveXML($mergedImage)
      ."<br style='clear:both;' />";
  }

  public function get_version(){
    return VERSION_GC_WEATHER;
  }
}
?>
