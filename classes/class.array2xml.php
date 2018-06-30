<?php
define('VERSION_ARRAY2XML','1.0.0');
/*
Version History:
  1.0.0 (2010-04-19)
    Initial release
    Based on code at phpclasses by Roger Veciana
    http://www.phpclasses.org/package/2286-PHP-Convert-XML-documents-into-arrays-and-vice-versa.html
*/
class array2xml {
  var $text;
  var $arrays, $keys, $node_flag, $depth, $xml_parser;

  function convert($array,$root_element='root') {
    $this->text =
       "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>"
      ."<".$root_element.">";
    $this->text .= $this->array_transform($array);
    $this->text .=  "</".$root_element.">";
    $Obj_SXE = new SimpleXMLElement($this->text);
    $Obj_DOM = new DOMDocument('1.0');
    $Obj_DOM->preserveWhiteSpace = false;
    $Obj_DOM->formatOutput = true;
    $Obj_DOM->loadXML($Obj_SXE->asXML());
    return $Obj_DOM->saveXML();
  }

  function array_transform($array){
    foreach($array as $key => $value){
      if(!is_array($value)){
        $this->text .= "<$key>$value</$key>";
      }
      else {
        $this->text.="<$key>";
        $this->array_transform($value);
        $this->text.="</$key>";
      }
    }
  }

  function xml2array($xml){
    $this->depth=-1;
    $this->xml_parser = xml_parser_create();
    xml_set_object($this->xml_parser, $this);
    xml_parser_set_option ($this->xml_parser,XML_OPTION_CASE_FOLDING,0);//Don't put tags uppercase
    xml_set_element_handler($this->xml_parser, "startElement", "endElement");
    xml_set_character_data_handler($this->xml_parser,"characterData");
    xml_parse($this->xml_parser,$xml,true);
    xml_parser_free($this->xml_parser);
    return $this->arrays[0];
  }

  function startElement($parser, $name, $attrs){
    $this->keys[]=$name; //We add a key
    $this->node_flag=1;
    $this->depth++;
  }

  function characterData($parser,$data){
    $key=end($this->keys);
    $this->arrays[$this->depth][$key]=$data;
    $this->node_flag=0; //So that we don't add as an array, but as an element
  }

  function endElement($parser, $name){
     $key=array_pop($this->keys);
     //If $node_flag==1 we add as an array, if not, as an element
     if($this->node_flag==1){
       $this->arrays[$this->depth][$key]=$this->arrays[$this->depth+1];
       unset($this->arrays[$this->depth+1]);
     }
     $this->node_flag=1;
     $this->depth--;
  }

  public function get_version(){
    return VERSION_ARRAY2XML;
  }
}
?>