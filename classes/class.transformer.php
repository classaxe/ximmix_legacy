<?php
define ("VERSION_TRANSFORMER","1.0.5");
/*
Version History:
  1.0.5 (2012-09-03)
    1) Changes to extend from Record class and use machinery there where required
    2) Removed Transformer::admin() -
       never accessed now since there is no longer a CKEditor plugin that is
       compatible with it
  1.0.4 (2009-07-11)
    1) Moved into classes folder and added get_version() method
  1.0.3 (2009-03-17)
    1) MF - Martined mainly for formatting of interface
  1.0.2 (2009-03-09)
    1) JF - many changes
  1.0.1
    1) JF - some versions of php already have json support - changed to load the json stub instead.
  1.0.0 (Initial release)

*/

// we need json to convert the default settings from the db table into an array, sorry martin
// 

if (!class_exists('Services_JSON')) { // added cuz sometimes we include this from elsewhere like /fck/plugins.....etc
	require_once(SYS_CLASSES . "class.services_json.php");
}

class Transformer extends Record{
  private $typeName;
  private $context = '';
  private $data = array();
  private $settings = array();
  private $requiredParams = array();
  private $htmlTemplate;
  private $scriptTemplate;
  public  $error = '';
  public static $dbTable = 'field_templates';

  public function __construct($context='', $typeName='', $data=''){
    if(!$context) {
      return;
    }
    parent::__construct("field_templates");
    $this->_set_object_name("Field Template");
    // should load a record with templates indicating how to structure this field type
    $this->context = strtolower($context); // whether we are in a page, a form, a report, a table, etc
    $this->typeName = strtolower($typeName); // this defines the type of formfield we are using
    $sql =
       "SELECT\n"
      ." *\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `name`='".$this->typeName."' AND\n"
      ."  `context`='".$this->context."'";
    $record = $this->get_record_for_sql($sql);
    if (!$record){
      $htmlTemplate = "%%value%%";
      $scriptTemplate = "%%value%%";
    }
    $json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE); // so we get an assoc array as output instead of some weird object
    $this->settings = $json->decode("{" . $record['settings'] . "}");
    // now settings has the default settings
    if ($record['requiredParams'] != null) {
      $this->requiredParams = explode(",", $record['requiredParams']);
    }
    foreach ($data as $key => $value) {
      $bits = explode(".", $key);
      if (count($bits) > 1 && $bits[0] == 'SETTING') {
        // this allows settings to be overridden from the calling page
        $this->settings[$bits[1]] = $value;
      }
      else {
        $this->data[$key] = $value;
      }
    }
    foreach ($this->requiredParams as $rp) {
      if (!array_key_exists($rp, $this->data)) {
        $this->error = "Required parameter [$rp] not provided";
        return;
      }
    }
    $this->htmlTemplate = $record['HTML'];
    $this->scriptTemplate = $record['JS'];
    // we need at LEAST to have $this->htmlTemplate populated
    if (strlen($this->htmlTemplate) == 0) {
      $this->error = "Error - there is no template definition for context " . $this->context;
      return;
    }
  }

  public function __get($key) {
    switch ($key) {
      case 'HTML':
        if (strlen($this->htmlTemplate) == 0) {
          return;
        }
        $out = $this->htmlTemplate;
        foreach ($this->data as $key => $value) {
          if (array_key_exists('UseEncryption', $this->settings) && $this->settings['UseEncryption'] == 'yes' && $key == 'value') {
            $out = str_replace('%%'.$key.'%%', XOREncrypt($value), $out);
          }
          else {
            $out = str_replace('%%'.$key.'%%', $value, $out);
          }
        }
        return $out;
      break;
      case 'JS':
        if (strlen($this->scriptTemplate) == 0) {
          return;
        }
        $out = $this->scriptTemplate;
        foreach ($this->data as $key => $value) {
          if (array_key_exists('UseEncryption', $this->settings) && $this->settings['UseEncryption'] == 'yes' && $key == 'value') {
            $out = str_replace('%%'.$key.'%%', XOREncrypt($value), $out);
          }
          else {
            $out = str_replace('%%'.$key.'%%', $value, $out);
          }
        }
        return strlen($out) > 0 ? '<script type=\'text/javascript\'>' . $out . '</script>' : '';
      break;
    }
  }

  public function get_version(){
    return VERSION_TRANSFORMER;
  }
}
?>
