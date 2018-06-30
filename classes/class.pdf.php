<?php
define('VERSION_PDF','1.0.1');
/*
Version History:
  1.0.1 (2009-07-11)
    Changes to paths for cpdf and cezpdf
  1.0.0 (2009-07-02)
    Initial release
*/
class PDF {
  function PDF() {
    include_once(SYS_CLASSES.'class.cpdf.php');
    include_once(SYS_CLASSES.'class.cezpdf.php');
  }
  public function get_version(){
    return VERSION_PDF;
  }
}
?>