<?php
define('VERSION_UPLOADER','1.0.6');
/*
Version History:
  1.0.6 (2011-05-05)
    1) Change to Uploader::do_upload() to unlink old file if it exists before
       trying to rename new file to old file name
  1.0.5 (2011-03-30)
    1) Uploader::do_upload() now also lists size in result
  1.0.4 (2011-03-24)
    1) Uploader::do_upload() now also lists extension and filename in result
  1.0.3 (2010-12-07)
    1) Changes to Uploader::do_upload() to allow type video and to allow images to
       be placed in video directory for preview
  1.0.2 (2010-09-03)
    1) Now limits files to expected type server-side
  1.0.1 (2010-09-02)
    1) Now prints error strings to allow JumpLoader to giv details on errors
  1.0.0 (2010-09-01)
    1) Initial release
*/
class Uploader extends Record {
  function __construct($type='image', $upload_base=''){
    switch(strToLower($type)){
      case "file":
      case "flash":
      case "image":
      case "media":
      case "video":
        $this->_type =          $type;
      break;
      default:
        throw new Exception('Invalid type '.$type);
      break;
    }
    $this->_upload_base =   rtrim($upload_base,'/').'/';
  }

  function do_upload(){
    $stage_dir =        $_SERVER[ 'DOCUMENT_ROOT' ] . "/UserFiles/";
    $file_name =        $_FILES[ 'file' ][ 'name' ];
    $source_file_path = $_FILES[ 'file' ][ 'tmp_name' ];
    $file_id =          $_POST[ 'fileId' ];
    $partition_index =  $_POST[ 'partitionIndex' ];
    $partition_count =  $_POST[ 'partitionCount' ];
    $file_length =      $_POST[ 'fileLength' ];
    $sessionID =        session_id();
    $chunk_file =       $stage_dir.$sessionID.".".$file_id.".".$partition_index;
    $file_ext_arr =   explode(".",$file_name);
    $ext =            strToLower(array_pop($file_ext_arr));
    $arr_denied =     explode(',', 'php,php3,php5,phtml,asp,aspx,ascx,jsp,cfm,cfc,pl,bat,exe,dll,reg,cgi,htaccess,asis,sh,shtml,shtm,phtm');
    $arr_flash =      explode(',', 'fla,flv,swf');
    $arr_image =      explode(',', 'gif,ico,jpg,jpeg,png');
    $arr_media =      explode(',', 'mp3');
    $arr_video =      explode(',', 'flv,gif,jpg,jpeg,png');
    $file_name =      implode(".", $file_ext_arr);
    if (in_array($ext,$arr_denied)){
      return array('status'=>'403', 'message'=>'Denied file extension .'.$ext);
    }
    switch(strToLower($this->_type)){
      case "file":
        // Anything goes (unless explicitly denied)
      break;
      case "flash":
        if (!in_array($ext,$arr_flash)){
          return array('status'=>'403', 'message'=>'Invalid type .'.$ext.' for uploaded '.$this->_type);
        }
      break;
      case "image":
        if (!in_array($ext,$arr_image)){
          return array('status'=>'403', 'message'=>'Invalid type .'.$ext.' for uploaded '.$this->_type);
        }
      break;
      case "media":
        if (!in_array($ext,$arr_media)){
          return array('status'=>'403', 'message'=>'Invalid type .'.$ext.' for uploaded '.$this->_type);
        }
      break;
      case "video":
        if (!in_array($ext,$arr_video)){
          return array('status'=>'403', 'message'=>'Invalid type .'.$ext.' for uploaded '.$this->_type);
        }
      break;
    }
    if(!move_uploaded_file($source_file_path, $chunk_file)) {
      return array('status'=>'500', 'message'=>'Cannot move uploaded file');
    }
    //    check if we have collected all partitions properly
    $all_in_place = true;
    $partitions_length = 0;
    for($i = 0; $all_in_place && $i<$partition_count; $i++) {
      $partition_file = $stage_dir.$sessionID.".".$file_id.".".$i;
      if(file_exists($partition_file)) {
        $partitions_length += filesize($partition_file);
      }
      else {
        $all_in_place = false;
      }
    }
    if($partition_index==$partition_count -1 && (!$all_in_place||$partitions_length!=intval($file_length))) {
      return array('status'=>'500', 'message'=>'Reassembly error');
    }
    if( $all_in_place ) {
      $file = $_SERVER[ 'DOCUMENT_ROOT' ].$this->_upload_base . $sessionID . "." . $file_id;
      $file_handle = fopen( $file, 'w' );
      for( $i = 0; $all_in_place && $i < $partition_count; $i++ ) {
        $partition_file = $stage_dir . $sessionID . "." . $file_id . "." . $i;
        $partition_file_handle = fopen( $partition_file, "rb" );
        $contents = fread( $partition_file_handle, filesize( $partition_file ) );
        fclose( $partition_file_handle );
        fwrite( $file_handle, $contents );
        unlink( $partition_file );
      }
      fclose( $file_handle );
      $file_path =      $this->_upload_base . get_web_safe_ID($file_name).".".$ext;
      if (file_exists($_SERVER[ 'DOCUMENT_ROOT' ].$file_path)){
        unlink($_SERVER[ 'DOCUMENT_ROOT' ].$file_path);
      }
      rename($file,$_SERVER[ 'DOCUMENT_ROOT' ].$file_path);
      return array(
        'status'=>'200',
        'size'=>(int)$file_length,
        'extension'=>$ext,
        'filename'=>get_web_safe_ID($file_name).".".$ext,
        'message'=>'Uploaded',
        'path'=>$file_path
      );
    }
    return array('status'=>'100', 'message'=>'Continue');
  }

  public function get_version(){
    return VERSION_UPLOADER;
  }
}

?>