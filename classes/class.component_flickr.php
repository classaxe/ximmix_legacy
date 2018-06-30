<?php
  define ("VERSION_COMPONENT_FLICKR","1.0.0");
/*
Version History:
  1.0.0 (2011-12-30)
    1) Initial release - moved from Component class
*/
class Component_Flickr extends Component_Base {

  function draw($instance='',$args=array(),$disable_params=false){
    $ident =        "flickr";
    $safe_ID =          Component_Base::get_safe_ID($ident,$instance);
    $parameter_spec =   array(
      'id' =>           array('match' => '',				'default'=>'',    'hint'=>'your flickr id'),
      'limit' =>        array('match' => 'range|1,20',		'default'=>'4',   'hint'=>'1..20'),
      'size' =>         array('match' => 'enum|s,m',		'default'=>'s',   'hint'=>'s|m')
    );
    $cp_settings =
      Component_Base::get_parameter_defaults_and_values(
        $ident, $instance, $disable_params, $parameter_spec, $args
      );
    $cp_defaults =  $cp_settings['defaults'];
    $cp =           $cp_settings['parameters'];
    $out =          Component_Base::get_help($ident, $instance, $disable_params, $parameter_spec, $cp_defaults);
    $url =
       "http://api.flickr.com/services/feeds/photos_public.gne"
      ."?id=".$cp['id']
      ."&tags=&format=rss_200";
  //  http://api.flickr.com/services/feeds/photos_public.gne?id=71123691@N00&tags=&format=rss_200
    $xml_doc = $this->get_remote_xml_file($url);
    $xml = new SimpleXMLElement($xml_doc);
    $n = 1;
    $Obj = new FileSystem();
    mkdirs("./UserFiles/Image/flickr",0777);
    foreach ($xml->channel->item as $item) {
      preg_match("/<img src=\"([^\"]+)/",$item->description,$src);
      switch($cp['size']){
        case 'm':
          $src =    $src[1];
        break;
        default:
          $src =    str_replace("m.jpg", "s.jpg",$src[1]);
        break;
      }
      preg_match("/http:\/\/farm[0-9]\.staticflickr\.com\/[0-9a-f]+\/([0-9a-f\_]+".$cp['size']."\.jpg)/",$src,$local);
      $local =  "UserFiles/Image/flickr/".$local[1];
      if (!file_exists("./".$local)){
        $data = file_get_contents($src);
        if ($data) {
          $Obj->write_file("./".$local,$data);
        }
      }
      $out.=
         "<a href=\"".$item->link."\" "
        ."title=\"".$item->title."\" "
        ."rel='external'>"
        ."<img style='border:none;padding-right:2px;padding-bottom:2px;' "
        ."src=\"".BASE_PATH."img/sysimg/?img=./".$local."\" alt=\"Flickr Photo\" />"
        ."</a>\n";
      if ($n++ == $cp['limit']) {
        break;
      }
    }
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_FLICKR;
  }
}
?>