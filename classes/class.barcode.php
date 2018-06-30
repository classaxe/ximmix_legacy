<?php
define ("VERSION_BARCODE","1.0.3");
/*
Version History:
  1.0.3 (2011-08-04)
    1) Tweak to BarCode::_generateImage() to use Image_Factory::allocateColor()
    2) Removed BarCode::allocateColor()
  1.0.2 (2009-11-16)
    1) Replace ereg() and eregi() with preg_match() for php 5.3+
  1.0.1 (2009-07-02)
    Changes to allow for dynamic include of barcode class and to add get_version()
  1.0.0 (2009-06-12)
    Initial release
*/

class BarCode {
  /*
   * Based on PHP-Barcode 0.3pl1
   * (C) 2001,2002,2003,2004 by Folke Ashberg <folke@ashberg.de>
   * The newest version can be found at http://www.ashberg.de/bar
   * This program is free software; you can redistribute it and/or modify
   * it under the terms of the GNU General Public License as published by
   * the Free Software Foundation; either version 2 of the License, or
   * (at your option) any later version.
  */
  private $color_bars, $color_bg, $color_text, $font, $height, $scale;

  function __construct( $color_bg='ffffff', $color_text='000000', $color_bars='000000', $font='arial.ttf', $scale=2, $height=0){
    $this->color_bg =   $color_bg;
    $this->color_text = $color_text;
    $this->color_bars = $color_bars;
    $this->font =       SYS_FONTS.$font;
    $this->height =     ($height ? $height : (int)($scale * 50));
    $this->scale =      $scale;
  }

  private function _encode_ean($ean){
    $digits = array(3211,2221,2122,1411,1132,1231,1114,1312,1213,3112);
    $mirror = array("000000","001011","001101","001110","010011","011001","011100","010101","010110","011010");
    $guards = array("9a1a","1a1a1","a1a");
    $ean = trim($ean);
    if (preg_match("/[^0-9]/i",$ean)){
      return array("error"=>"Invalid EAN-Code - must contain only numbers");
    }
    $ean = substr("000000000000",0,12-strlen($ean)).$ean;
    $ean = substr($ean,0,12);
    $eansum=$this->_gen_ean_sum($ean);
    $ean.=$eansum;
    $line=$guards[0];
    for ($i=1;$i<13;$i++){
      $str=$digits[$ean[$i]];
      if ($i<7 && $mirror[$ean[0]][$i-1]==1) {
        $line.=strrev($str);
      }
      else {
        $line.=$str;
      }
      if ($i==6) {
        $line.=$guards[1];
      }
    }
    $line.=$guards[2];
    $pos=0;
    $text="";
    for ($a=0;$a<13;$a++){
      if ($a>0) {
        $text .= " ";
      }
      $text .= "$pos:12:{$ean[$a]}";
      if ($a==0) {
        $pos+=12;
      }
      else if ($a==6) {
        $pos+=12;
      }
      else {
        $pos+=7;
      }
    }
    return array(
      "bars" => $line,
      "text" => $text
    );
  }

  private function _gen_ean_sum($ean){
    $even=true; $esum=0; $osum=0;
    for ($i=strlen($ean)-1;$i>=0;$i--){
      if ($even) {
        $esum+=$ean[$i];
      }
      else {
        $osum+=$ean[$i];
      }
      $even=!$even;
    }
    return (10-((3*$esum+$osum)%10))%10;
  }

  private function _generateImage($text, $bars, $space = false){
    $scale = $this->scale;
    $total_y = $this->height;
    if (!$space) {
      $space = array('top'=>2*$scale,'bottom'=>2*$scale,'left'=>2*$scale,'right'=>2*$scale);
    }
    /* count total width */
    $xpos=0;
    $width=true;
    for ($i=0;$i<strlen($bars);$i++){
      $val=strtolower($bars[$i]);
      if ($width){
        $xpos+=$val*$scale;
        $width=false;
        continue;
      }
      if (preg_match("/[a-z]/", $val)){
    /* tall bar */
        $val=ord($val)-ord('a')+1;
      }
      $xpos += $val * $scale;
      $width = true;
    }
    /* allocate the image */
    $total_x =  $xpos + (2*$space['right']);
    $xpos =     $space['left'];
    $im =       imagecreate($total_x, $total_y);
    /* create two images */
    if ($this->color_bg=='123456') {
      $RGB_transp =   Image_Factory::allocateColor($im, 'ffffff');
      imageColorTransparent($im, $RGB_transp);
    }
    $col_bg =   Image_Factory::allocateColor($im,$this->color_bg);
    $col_bar =  Image_Factory::allocateColor($im,$this->color_bars);
    $col_text = Image_Factory::allocateColor($im,$this->color_text);
    $height =   round($total_y-($scale*10));
    $height2 =  round($total_y-$space['bottom']);
    /* paint the bars */
    $width=true;
    for ($i=0 ; $i<strlen($bars) ; $i++){
      $val=strtolower($bars[$i]);
      if ($width){
        $xpos+=$val*$scale;
        $width=false;
        continue;
      }
      if (preg_match("/[a-z]/", $val)){
        /* tall bar */
        $val=ord($val)-ord('a')+1;
        $h=$height2;
      }
      else {
        $h=$height;
      }
      imagefilledrectangle($im, $xpos, $space['top'], $xpos+($val*$scale)-1, $h, $col_bar);
      $xpos+=$val*$scale;
      $width=true;
    }
    /* write out the text */
    $chars=explode(" ", $text);
    reset($chars);
    while (list($n, $v)=each($chars)){
      if (trim($v)){
        $inf=explode(":", $v);
        $fontsize=$scale*($inf[1]/1.8);
        $fontheight=$total_y-($fontsize/2.7)+2;
        @imagettftext($im, $fontsize, 0, $space['left']+($scale*$inf[0])+2,
        $fontheight, $col_text, $this->font, $inf[2]);
      }
    }
    return $im;
  }

  public function image($code){
    $bars = $this->_encode_ean($code);
    if (isset($bars['error'])){
      die($bars['error']);
    }
    return $this->_generateImage($bars['text'],$bars['bars']);
  }

  public function get_version(){
    return VERSION_BARCODE;
  }
}
?>