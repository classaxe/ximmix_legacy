<?php
define('VERSION_CKFINDER','1.0.5');
/*
Version History:
  1.0.5 (2011-11-25)
    1) Added .mp4, .m4v, .f4v, .mov  to list of acceptable video files
  1.0.4 (2011-07-28)
    1) Now chmods new folders as 777 - was 755 earlier
  1.0.3 (2011-05-09)
    1) Added doc, pdf and image types to Media files list
  1.0.2 (2010-12-03)
    1) Now allows .gif and .png images to be uploaded to video folder
       (for use as preview images)
  1.0.1 (2010-11-19)
    1) Now creates Video folder
  1.0.0 (2009-09-01)
    Initial release - this is so I never have to set these in doc root again!
*/
class CKFinder{
  function __construct(){

  }

  function check_authentication(){
    return
      isset($_SESSION['person']) &&(
        $_SESSION['person']['permMASTERADMIN'] ||
        $_SESSION['person']['permSYSADMIN'] ||
        $_SESSION['person']['permSYSAPPROVER'] ||
        $_SESSION['person']['permSYSEDITOR']
      );
  }

  function config(&$config){
    $config['LicenseName'] =    'Ecclesiact Web System';
    $config['LicenseKey'] =     'EWA3-Q5LG-TYX6-TR8W-MMA6-JRWH-JHM2';
    $baseUrl = BASE_PATH.'UserFiles/';
    $baseDir = resolveUrl($baseUrl);
    $config['Thumbnails'] = array(
      'url' =>          $baseUrl.'_thumbs',
      'directory' =>    $baseDir.'_thumbs',
      'enabled' =>      true,
      'directAccess' => false,
      'maxWidth' =>     100,
      'maxHeight' =>    100,
      'bmpSupported' => false,
      'quality' =>      80
    );
    $config['Images'] = array(
      'maxWidth' => 0,
      'maxHeight' => 0,
      'quality' => 0
    );
    $config['RoleSessionVar'] = 'CKFinder_UserRole';
    $config['AccessControl'][] = array(
      'role' =>         '*',
      'resourceType' => '*',
      'folder' =>       '/',
      'folderView' =>   true,
      'folderCreate' => true,
      'folderRename' => true,
      'folderDelete' => true,
      'fileView' =>     true,
      'fileUpload' =>   true,
      'fileRename' =>   true,
      'fileDelete' =>   true
    );
    $config['DefaultResourceTypes'] = '';
    $config['ResourceType'][] = array(
      'name' =>                 'File',
      'url' =>                  $baseUrl.'File',
      'directory' =>            $baseDir.'File',
      'maxSize' =>              0,
      'allowedExtensions' =>    '',
      'deniedExtensions' =>     'php,php3,php5,phtml,asp,aspx,ascx,jsp,cfm,cfc,pl,bat,exe,dll,reg,cgi,htaccess,asis,sh,shtml,shtm,phtm'
    );
    $config['ResourceType'][] = array(
      'name' =>                 'Flash',
      'url' =>                  $baseUrl.'Flash',
      'directory' =>            $baseDir.'Flash',
      'maxSize' =>              0,
      'allowedExtensions' =>    'swf,fla,flv',
      'deniedExtensions' =>     ''
    );
    $config['ResourceType'][] = array(
      'name' =>                 'Image',
      'url' =>                  $baseUrl.'Image',
      'directory' =>            $baseDir.'Image',
      'maxSize' =>              0,
      'allowedExtensions' =>    'gif,ico,jpeg,jpg,png',
      'deniedExtensions' =>     ''
    );
    $config['ResourceType'][] = array(
      'name' =>                 'Media',
      'url' =>                  $baseUrl.'Media',
      'directory' =>            $baseDir.'Media',
      'maxSize' =>              0,
      'allowedExtensions' =>    'doc,flv,gif,jpg,jpeg,mp3,pdf,png',
      'deniedExtensions' =>     ''
    );
    $config['ResourceType'][] = array(
      'name' =>                 'Video',
      'url' =>                  $baseUrl.'Video',
      'directory' =>            $baseDir.'Video',
      'maxSize' =>              0,
      'allowedExtensions' =>    'f4v,flv,gif,jpg,jpeg,mov,m4v,mp4,png',
      'deniedExtensions' =>     ''
    );
    $config['CheckDoubleExtension'] =   true;
    $config['FilesystemEncoding'] =     'UTF-8';
    $config['SecureImageUploads'] =     true;
    $config['CheckSizeAfterScaling'] =  true;
    $config['HtmlExtensions'] =         array('html', 'htm', 'xml', 'js');
    $config['HideFolders'] =            array(".svn", "CVS");
    $config['HideFiles'] =              array(".*");
    $config['ChmodFiles'] =             0777;
    $config['ChmodFolders'] =           0777;
  }

  public function get_version(){
    return VERSION_CKFINDER;
  }
}

?>