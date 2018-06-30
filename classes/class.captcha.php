<?php
define('VERSION_CAPTCHA','1.0.0');
/*
Version History:
  1.0.0 (2009-07-02)
    Initial release
*/
class Captcha {
  protected $sessionName = 'vihash';
  protected $fontPath = SYS_FONTS;
  protected $fontFile = MONO_FONT;
  protected $imageWidth = 180;
  protected $imageHeight = 50;
  protected $allowedChars = '2345678abcdefhijkmnpqrstuvwxyz';
  protected $stringLength = 6;
  protected $charWidth = 28;
  protected $blurRadius = 3;
  protected $secretKey = "oerjf6w4ymry99234";

  public function __construct($options = array()) {
    if (is_array($options)) {
      $allowedOptions =
        array (
          'sessionName',
          'fontPath',
          'fontFile',
          'imageWidth',
          'imageHeight',
          'allowedChars',
          'stringLength',
          'charWidth',
          'blurRadius',
          'secretKey'
        );
      $allowedOptionsCount = count($allowedOptions);
      for ($i = 0; $i < $allowedOptionsCount; $i++) {
        if (isset($options[$allowedOptions[$i]])) {
          $this->$allowedOptions[$i] = $options[$allowedOptions[$i]];
        }
      }
    }
  }

  /**
   * This function creates a captcha image, and outputs it, in addition to
   * add the value to the session. for the validation.
   *
   */
  public function getCaptcha() {
    $rand = $this->randomString();
//    $rand='abcdef';
    $_SESSION[$this->sessionName] = md5($rand.$this->secretKey);
    $this->generateValidationImage($rand);
  }

  /**
   * this function checks if an entered key is right or wrong
   *
   * @param string $key
   * @return bool
   */
  public function isKeyRight($key) {
    $isKeyRight = isset($_SESSION[$this->sessionName]) && $_SESSION[$this->sessionName] == md5(strToLower($key).$this->secretKey);
    if ($isKeyRight) {
      return true;
    }
    return false;
  }

  protected function randomString() {
    $chars = $this->allowedChars;
    $s = "";
    for ($i = 0; $i < $this->stringLength; $i++) {
      $int         = rand(0, strlen($chars)-1);
      $rand_letter = $chars[$int];
      $s           = $s . $rand_letter;
    }
    return $s;
  }

  protected function generateValidationImage($rand) {
    $width = $this->imageWidth;
    $height = $this->imageHeight;
    $image = imagecreate($width, $height);
    $bgColor = imagecolorallocate ($image, 230,230,230);
    $color_arr = array();
    for($i=0; $i<$this->stringLength; $i++) {
      $color_arr[] = imagecolorallocate ($image, rand(0,140), rand(0,140), rand(0,140));
    }

    // add random noise
    for ($i = 0; $i < 30; $i++) {
      $rx1 = rand(0, $width);
      $rx2 = rand(0, $width);
      $ry1 = rand(0, $height);
      $ry2 = rand(0, $height);
      $rcVal = rand(0, 255);
      $rc1 = imagecolorallocate($image, rand(100, 255), rand(100, 255), rand(120, 255));
      imageline($image, $rx1, $ry1, $rx2, $ry2, $rc1);
    }

    // write the random number
    for ($i = 0; $i < $this->stringLength; $i++) {
      ImageTTFText( $image, rand(22,26), rand(-30,30), rand(6,8)+ ($i * $this->charWidth),($height*0.7),$color_arr[$i],$this->fontPath."/".$this->fontFile,$rand[$i]);
    }

    $this->blur($image, $this->blurRadius);
    $this->blur($image, $this->blurRadius*1.5);

    // send several headers to make sure the image is not cached
    // date in the past
    header("Expires: Mon, 23 Jul 1993 05:00:00 GMT");

    // always modified
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    // HTTP/1.1
    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);

    // HTTP/1.0
    header("Pragma: no-cache");

    // send the content type header so the image is displayed properly
    header('Content-type: image/jpeg');

    imagejpeg($image);
    imagedestroy($image);
  }

  protected function blur(&$gdimg, $radius = 5.0) {
    // Taken from Torstein Honsi's phpUnsharpMask (see phpthumb.unsharp.php)

    $radius = round(max(0, min($radius, 50)) * 2);
    if (!$radius) {
      return false;
    }

    $w = ImageSX($gdimg);
    $h = ImageSY($gdimg);
    if ($imgBlur = ImageCreateTrueColor($w, $h)) {
      // Gaussian blur matrix:
      //    1    2    1
      //    2    4    2
      //    1    2    1

      // Move copies of the image around one pixel at the time and merge them with weight
      // according to the matrix. The same matrix is simply repeated for higher radii.
      for ($i = 0; $i < $radius; $i++)    {
        ImageCopy     ($imgBlur, $gdimg, 0, 0, 1, 1, $w - 1, $h - 1);            // up left
        ImageCopyMerge($imgBlur, $gdimg, 1, 1, 0, 0, $w,     $h,     50.00000);  // down right
        ImageCopyMerge($imgBlur, $gdimg, 0, 1, 1, 0, $w - 1, $h,     33.33333);  // down left
        ImageCopyMerge($imgBlur, $gdimg, 1, 0, 0, 1, $w,     $h - 1, 25.00000);  // up right
        ImageCopyMerge($imgBlur, $gdimg, 0, 0, 1, 0, $w - 1, $h,     33.33333);  // left
        ImageCopyMerge($imgBlur, $gdimg, 1, 0, 0, 0, $w,     $h,     25.00000);  // right
        ImageCopyMerge($imgBlur, $gdimg, 0, 0, 0, 1, $w,     $h - 1, 20.00000);  // up
        ImageCopyMerge($imgBlur, $gdimg, 0, 1, 0, 0, $w,     $h,     16.666667); // down
        ImageCopyMerge($imgBlur, $gdimg, 0, 0, 0, 0, $w,     $h,     50.000000); // center
        ImageCopy     ($gdimg, $imgBlur, 0, 0, 0, 0, $w,     $h);
      }
      return true;
    }
    return false;
  }
  public function get_version(){
    return VERSION_CAPTCHA;
  }
}
?>