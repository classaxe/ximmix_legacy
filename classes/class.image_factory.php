<?php
define ("VERSION_IMAGE_FACTORY","1.0.10");
/*
Version History:
  1.0.10 (2012-01-20)
    1) Changes to Image_Factory::xml_to_image() for text handling using datasource
       to make it show field name if datasource field is not actually set

  (Older version history in class.image_factory.txt)
*/

class Image_Factory {

  static function allocateColor(&$image,$string) {
    sscanf($string, "%2x%2x%2x", $r, $g, $b);
    return ImageColorAllocate($image,$r,$g,$b);
  }

  static function get_dimensions($file){
    $ext = Image_Factory::get_extension($file);
    switch ($ext){
      case "gif":
        $img =    imageCreateFromGif($file);
      break;
      case "jpg":
        $img =    imageCreateFromJpeg($file);
      break;
      case "png":
        $img =    imageCreateFromPNG($file);
      break;
    }
    $result = array(imagesx($img), imagesy($img));
    return $result;
  }

  static function get_extension($file){
    $file_arr =       explode(",",$file);
    $file =           array_shift($file_arr);
    $filename =       "./".$file;
    $file_ext_arr =   explode(".",$filename);
    $ext =            array_pop($file_ext_arr);
    switch ($ext){
      // Check to see if image was misnamed
      case "gif":
      case "jpg":
      case "jpeg":
      case "png":
        $data = getimagesize($filename);
        $mime = $data['mime'];
        switch($mime){
          case 'image/gif':
            return 'gif';
          break;
          case 'image/jpeg':
            return 'jpg';
          break;
          case 'image/png':
            return 'png';
          break;
        }
      break;
    }
  }

  static function resize_to_max($file, $max=1024){
    $size =   Image_Factory::get_dimensions($file);
    $ext =    Image_Factory::get_extension($file);
    $width =  $size[0];
    $height = $size[1];
    $aspect = $width / $height;
    if ($width>$max){
      $width = $max;
      $height = (int)($width/$aspect);
    }
    if ($height>$max){
      $height = $max;
      $width = (int)($width*$aspect);
    }
    switch (strToLower($ext)){
      case "gif":
        $img =    imageCreateFromGif($file);
      break;
      case "jpg":
        $img =    imageCreateFromJpeg($file);
      break;
      case "png":
        $img =    imageCreateFromPNG($file);
      break;
    }
    unlink($file);
    switch (strToLower($ext)){
      case "gif":
        $img2 = imageCreate($width,$height);
        ImageCopyResized($img2,$img,0,0,0,0,$width,$height,imagesx($img),imagesy($img));
        $img = imageCreate($width,$height);
        ImageCopyMerge($img,$img2,0,0,0,0,imagesx($img),imagesy($img),100);
        ImageGIF($img,$file);
        return;
      break;
      case "png":
        $img2 = ImageCreateTruecolor($width,$height);
        ImageCopyResized($img2,$img,0,0,0,0,$width,$height,imagesx($img),imagesy($img));
      ImagePNG($img2,$file);
        return;
      break;
      case "jpg":
      case "jpeg":
        $img2 = ImageCreateTruecolor($width,$height);
        imagecopyresampled($img2,$img,0,0,0,0,$width,$height,imagesx($img),imagesy($img));
        Imagejpeg($img2,$file,100);
        return;
      break;
    }
  }

  static function rotate($filename,$degrees){
    $ext =        Image_Factory::get_extension($filename);
    $filename =    "./".trim(preg_replace('/\.\./','',$filename),'/');
    switch (strToLower($ext)){
      case "gif":
        $img =    imageCreateFromGIF($filename);
        $img2 =   imagerotate($img, $degrees, 0);
        $result = imageGIF($img2,$filename);
        do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Rotate '.$degrees,'Result for '.$filename.': '.($result ? 'OK' : 'Fail'));
        return $filename;
      break;
      case "jpg":
        $img =    imageCreateFromJPEG($filename);
        $img2 =   imagerotate($img, $degrees, 0);
        $result = imageJPEG($img2,$filename);
        do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Rotate '.$degrees,'Result for '.$filename.': '.($result ? 'OK' : 'Fail'));
        return $filename;
      break;
      case "png":
        $img =    imageCreateFromPNG($filename);
        $img2 =   imagerotate($img, $degrees, 0);
        $result = imagePNG($img2,$filename);
        do_log(1,__CLASS__.'::'.__FUNCTION__.'()','Rotate '.$degrees,'Result for '.$filename.': '.($result ? 'OK' : 'Fail'));
        return $filename;
      break;
      default:
        return false;
      break;
    }
  }

  static function setColorIndex(&$image,$i,$string) {
    sscanf($string, "%2x%2x%2x", $r, $g, $b);
    return Imagecolorset($image,$i,$r,$g,$b);
  }

  static function xml_to_image($xml_doc,$data){
    if ($xml_doc=='') {
      return array('error' => 'No XML Document to parse');
    }
    try {
      $xml = new SimpleXMLElement($xml_doc);
    }
    catch (Exception $e) {
      return array('error' => 'Errors in XML Document');
    }
    $canvas = $xml;
    foreach ($xml->children() as $type => $node){
      if ($type=='canvas'){
        $canvas = $node;
        break;
      }
    }
    // Canvas
    $bgcolor =      (isset($canvas['bgcolor']) ?   trim((string)$canvas['bgcolor'],'#')  : '123456');
    $height =       (isset($canvas['height']) ?    (string)$canvas['height'] : 480);
    $width =        (isset($canvas['width']) ?     (string)$canvas['width'] : 640);
    $output =       (isset($canvas['mode']) ?    (string)$canvas['mode'] : 'png');
    $img =	        imageCreateTrueColor($width,$height);
    $RGB_transp =   Image_Factory::allocateColor($img, '123456');
    $RGB_bgcolor =  Image_Factory::allocateColor($img, $bgcolor);
    imageFill($img,0,0,$RGB_bgcolor);
    imageColorTransparent($img, $RGB_transp);
    foreach ($canvas->children() as $type => $node){
      switch ($type) {
        case "barcode":
          $color =      (isset($node['color']) ?    trim((string)$node['color'],'#') : '000000');
          $bgcolor =    (isset($node['bgcolor']) ?  trim((string)$node['bgcolor'],'#') : 'ffffff');
          $scale =      (isset($node['scale']) ?    (string)$node['scale'] : 2);
          $height =     (isset($node['height']) ?   (string)$node['height'] : 0);
          $xpos =       (isset($node['xpos']) ?     (string)$node['xpos'] : 0);
          $ypos =       (isset($node['ypos']) ?     (string)$node['ypos'] : 0);
          $datasrc =    (isset($node['datasrc']) ?  (string)$node['datasrc'] : 'ID');
          $value =      (isset($node['value']) ?    (string)$node['value'] : '');
          $value =      ($value==='' && isset($data[$datasrc]) ? $data[$datasrc] : $value);

          $Obj =        new BarCode($bgcolor,$color,$color,'arial.ttf',$scale,$height);
          $_img =       $Obj->image($value);
          $w =	        imagesx($_img);
          $h =	        imagesy($_img);
          ImageCopy($img,$_img,$xpos,$ypos,0,0,$w,$h);
        break;
        case "img":
          $alpha =      (isset($node['alpha']) ?    (string)$node['alpha'] : 100);
          $xpos =       (isset($node['xpos']) ?     (string)$node['xpos'] : 0);
          $ypos =       (isset($node['ypos']) ?     (string)$node['ypos'] : 0);
          $height =     (isset($node['height']) ?   (string)$node['height'] : '');
          $width =      (isset($node['width']) ?    (string)$node['width'] : '');
          $ypos =       (isset($node['ypos']) ?     (string)$node['ypos'] : 0);
          $src =        (isset($node['src']) ?      '.'.trim((string)$node['src'],'.') : SYS_IMAGES.'icon_image_unavailable.gif');
          if (!file_exists($src)) {
            $src = SYS_IMAGES.'icon_image_unavailable.gif';
          }
          $ext_arr =    explode('.',$src);
          switch(array_pop($ext_arr)){
            case 'gif':
              $_img = imageCreateFromGif($src);
            break;
            case 'jpg':
            case 'jpeg':
              $_img = imageCreateFromJpeg($src);
            break;
            case 'png':
              $_img = imageCreateFromPng($src);
            break;
          }
          $w =	imagesx($_img);
          $h =	imagesy($_img);
          if ($height || $width){
            $height =       ($height ? $height : $h);
            $width =        ($width ? $width : $w);
            $_old_img =     $_img;
            $_img =	        imageCreateTrueColor($width,$height);
            ImageCopyResized($_img, $_old_img, 0, 0, 0, 0, $width, $height, $w, $h);
            $w =	imagesx($_img);
            $h =	imagesy($_img);
          }
          ImageCopyMerge($img,$_img,$xpos,$ypos,0,0,$w,$h,$alpha);
        break;
        case "text":
          $align =      (isset($node['align']) ?    (string)$node['align'] : 'left');
          $color =      (isset($node['color']) ?    trim((string)$node['color'],'#') : '000000');
          $datasrc =    (isset($node['datasrc']) ?  (string)$node['datasrc'] : '');
          $font =       (isset($node['font']) ?     (string)$node['font'] : 'arialbd');
          $font =       SYS_FONTS.$font.'.ttf';
          $height =     (isset($node['width']) ?    (string)$node['height'] : 0);
          $size =       (isset($node['size']) ?     (string)$node['size'] : 12);
          $valign =     (isset($node['valign']) ?   (string)$node['valign'] : 'top');
          $width =      (isset($node['width']) ?    (string)$node['width'] : 0);
          $xpos =       (isset($node['xpos']) ?     (string)$node['xpos'] : 0);
          $ypos =       (isset($node['ypos']) ?     (string)$node['ypos'] : 0);

          $value =      (isset($node['value']) ?    (string)$node['value'] : '');
          $value =
            str_replace(
              array(
                '\r\n',
                '\n'
              ),
              "\r\n",
              ($value==='' && $datasrc ? (isset($data[$datasrc]) ? $data[$datasrc]  : '['.$datasrc.']') : $value)
            );
          $value = strip_tags($value);
          if (trim($value)==''){
            break;
          }
          $lines =      count(explode("\r\n",$value));

          $arr_bbox =	    imagettfbbox($size,0,$font,$value);
          $_w = 	        $arr_bbox[2] - $arr_bbox[6];
          $arr_bbox =	    imagettfbbox($size,0,$font," \r\n ");
          $line_height = 	$arr_bbox[3] - $arr_bbox[7];

          $arr_bbox =	    imagettfbbox($size,0,$font,"b");
          $above_height =   $arr_bbox[3] - $arr_bbox[7];
          $v_offset =      ($above_height-$line_height)*0.5;

          $x = $xpos;
          $y = $ypos+$line_height;
          if ($width || $height){
            switch ($align){
              case "left":
                $x = $xpos;
              break;
              case "center":
                $x = $xpos + ($width/2) - ($_w/2);
              break;
              case "right":
                $x = $xpos + $width - $_w;
              break;
            }
            switch ($valign){
              case "top":
                $y = $ypos+$line_height+$v_offset;
              break;
              case "middle":
                $y = $ypos + ($height/2)+($line_height - ($lines*$line_height/2))+$v_offset;
              break;
              case "bottom":
                $y = ($ypos + $height +$line_height - $line_height*$lines)+($v_offset);
              break;
            }
          }
          $_RGB_color = Image_Factory::allocateColor($img, $color);
          $line_arr =   explode("\r\n",$value);
          for ($i=0; $i<count($line_arr); $i++){
            $line =         $line_arr[$i];
            $arr_bbox =	    imagettfbbox($size,0,$font,$line);
            $line_w = 	    $arr_bbox[2] - $arr_bbox[6];
            switch($align){
              case "left":
                $line_x = $x;
              break;
              case "center":
                $line_x = $x + (($_w - $line_w)/2);
              break;
              case "right":
                $line_x = $x + $_w - $line_w;
              break;
            }
            $line_y =       $y+($i*$line_height);
            ImageTTFText($img, $size, 0, $line_x, $line_y, $_RGB_color, $font, $line);
          }
        break;
      }
    }
    switch ($output){
      case "gif":
        header("Content-Type: image/gif; name=\"image_factory.gif\"");
        ImageGIF($img);
        die;
      break;
      case "jpg":
      case "jpeg":
        header("Content-Type: image/jpg; name=\"image_factory.jpg\"");
        ImageJPEG($img);
        die;
      break;
      case "png":
        header("Content-Type: image/png; name=\"image_factory.png\"");
        ImagePNG($img);
        die;
      break;
      case "none":
        return $img;
      break;
      default:
        return array('error' => 'Unhandled output type '.$output);
      break;
    }
  }
  public function get_version(){
    return VERSION_IMAGE_FACTORY;
  }
}

?>