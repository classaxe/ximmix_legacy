<?php
define('VERSION_RSS_PROXY','1.0.1');
/*
Version History:
  1.0.1 (2011-12-31)
    1) Replaced deprecated ereg_replace functions which would fail in newer PHP
  1.0.0 (2010-03-13)
    1) Initial release - based on http://momche.net/publish/article.php?page=rssload
      This version WAS to have limited type to xml to prevent abuse of the proxy
      for other purposes (see _get_remote_xml()) but wouldn't you know it,
      unlike our feeds, all the WP feeds gave back a mime-type of text/html
      and so failed - doh!
  .      
*/

class RSS_Proxy {
  static private $_cache_dir;

  private static function _safe_url( $sFolder ){
    $sFolder = preg_replace( "/^[\.\/]*/", "", $sFolder );
    $sFolder = preg_replace( "/[\.\/]*$/", "", $sFolder  );
    $sFolder_arr = explode('#',$sFolder);
    return $sFolder_arr[0];
  }

  private static function _translate_url_to_filename( $sFileName ){
    $sFileName = str_replace( array('http://','https://'),'', $sFileName );
    return
       RSS_Proxy::$_cache_dir
      .str_replace( array('/','?','.','=','&amp;','&'),'_', $sFileName )
      .'.cache';
  }

  private static function _cache_save( $sFileName, &$sFileContent ){
    file_put_contents($sFileName, $sFileContent);
  }

  private static function _get_remote_xml($url){
    $sFile = @file_get_contents($url);
/*
    foreach($http_response_header as $header){
      if (substr(strToLower($header),0,13)=='content-type:'){
        $type = trim(substr(strToLower($header),13));
        if (stripos($type,'xml',0)){
          return $sFile;
        }
        else {
          die('<error>The requested document is of type '.$type.'</error>');
        }
      }
    }
    die('<error>The requested document type is not known.</error>');
    */
    return $sFile;
  }


  private static function _cache_read($sLocalName){
    $hFile = fopen( $sLocalName, "r" );
    if( $hFile ) {
      $sFile = fread( $hFile, filesize( $sLocalName ) );
      fclose( $hFile );
      return $sFile;
    }
    else {
      return "File is missing: ".$sLocalName;
    }
  }

  public static function get( $sFileName, $max_age_seconds=600 ){
    RSS_Proxy::$_cache_dir = SYS_SHARED.'rss_proxy_cache/';
    $sFileName = RSS_Proxy::_safe_url($sFileName);
    header( "Content-type: text/xml" );
    if( empty( $sFileName ) ) {
      return "<empty>nothing</empty>";
    }
    $sLocalName = RSS_Proxy::_translate_url_to_filename( $sFileName );
    $sFile = "";
    if( file_exists( $sLocalName ) ) {
    $nMTime = filemtime( $sLocalName );
      if( ( time() - $nMTime ) > $max_age_seconds ) {
        $sFile = RSS_Proxy::_get_remote_xml($sFileName);
        RSS_Proxy::_cache_save($sLocalName, $sFile);
      }
      else {
        $sFile = RSS_Proxy::_cache_read($sLocalName);
      }
    }
    else {
      $sFile = RSS_Proxy::_get_remote_xml($sFileName);
      RSS_Proxy::_cache_save($sLocalName, $sFile);
    }
    return $sFile;
  }
  public function get_version(){
    return VERSION_RSS_PROXY;
  }
}

?>