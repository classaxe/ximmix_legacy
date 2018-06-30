<?php
define('VERSION_TAX_REGIME','1.0.15');
/*
Version History:
  1.0.15 (2014-01-28)
    1) Refreshed fields list - now declared as a class constant

  (Older version history in class.tax_regime.txt)
*/

class Tax_Regime extends Record {
  const fields = 'ID, archive, archiveID, deleted, systemID, name, color_background, color_text, seq, tax_zoneID, description, qb_ident, qb_name, tax1_rate, tax2_rate, tax3_rate, tax4_rate, tax5_rate, tax6_rate, tax7_rate, tax8_rate, tax9_rate, tax10_rate, tax11_rate, tax12_rate, tax13_rate, tax14_rate, tax15_rate, tax16_rate, tax17_rate, tax18_rate, tax19_rate, tax20_rate, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';

  public function __construct($ID="") {
    parent::__construct("tax_regime",$ID);
    $this->_set_object_name("Tax Regime");
    $this->_set_message_associated(' and associated Tax Rules have');
  }

  function copy($new_name=false,$new_systemID=false,$new_date=true) {
    $isMASTERADMIN = get_person_permission("MASTERADMIN");
    $newID =    parent::copy($new_name,$new_systemID,$new_date);
    $rules =    $this->get_tax_rules();
    $Obj =      new Tax_Rule;
    foreach ($rules as $data) {
      $data['tax_regimeID'] = $newID;
      if (!$isMASTERADMIN){
        $data['systemID'] = SYS_ID;
      }
      unset($data['archive']);
      unset($data['archiveID']);
      unset($data['history_created_by']);
      unset($data['history_created_date']);
      unset($data['history_created_IP']);
      unset($data['history_modified_by']);
      unset($data['history_modified_date']);
      unset($data['history_modified_IP']);
      $new_tax_ruleID =    $Obj->insert($data);
    }
    return $newID;
  }

  function delete() {
    $Obj = new Tax_Rule;
    $rules = $this->get_tax_rules();
    foreach ($rules as $rule) {
      $Obj->_set_ID($rule['ID']);
      $Obj->delete();
    }
    parent::delete();
  }

  function export_sql($targetID,$show_fields) {
    $header =
      "Selected ".$this->_get_object_name().$this->plural($targetID)."\n"
     ."(with associated Tax Rules)";
    $extra_delete =
       "DELETE FROM `tax_rule`               WHERE `tax_regimeID` IN (".$targetID.") AND `systemID` IN(1,".SYS_ID.");\n"
      ;
    $Obj = new Backup;
    $extra_select =
       $Obj->db_export_sql_query("`tax_rule`              ","SELECT * FROM `tax_rule` WHERE `tax_regimeID` IN (".$targetID.") AND `systemID` IN(1,".SYS_ID.")",$show_fields)
      ;
    return parent::sql_export($targetID,$show_fields,$header,'',$extra_delete,$extra_select);
  }

  function get_costs($data,$BCountryID,$BSpID){
    $this->load();
    $Obj_Tax_Zone = new Tax_Zone($this->record['tax_zoneID']);
    $Obj_Tax_Zone->load();
    $rules = $this->get_tax_rules();
    $out =  array();
    $out['quantity'] =  $data['quantity'];
    $out['net'] =       (float)$data['quantity']*$data['price'];
    $out['cost'] =      $out['net'];
    for($i=1; $i<=20; $i++){
      $out['tax'.$i.'_name'] = $Obj_Tax_Zone->record['tax'.$i.'_name'];
      $out['tax'.$i.'_rate'] = $this->record['tax'.$i.'_rate'];
      $out['tax'.$i.'_cost'] = 0;
    }
    foreach ($rules as $rule){
      $rule_total = 0;
      for($i=1; $i<=20; $i++){
        $tax_places =   explode(',',str_replace(' ','',$rule['tax'.$i.'_apply']));
        foreach ($tax_places as $place){
          $invert = (substr($place,0,1)=='!' ? true : false);
          if ($invert){
            $place = substr($place,1);
            if (
              $place != $BCountryID.'.'.$BSpID &&
              $place != $BCountryID.'.*' &&
              $place != '*.'.$BSpID &&
              $place != '*.*'
            ){
              $cost = $out['cost'] * ($out['tax'.$i.'_rate']/100);
              $out['tax'.$i.'_cost'] = $cost;
              $rule_total += $cost;
            }
          }
          else {
            if (
              $place == $BCountryID.'.'.$BSpID ||
              $place == $BCountryID.'.*' ||
              $place == '*.'.$BSpID ||
              $place == '*.*'
            ){
              $cost = $out['cost'] * ($out['tax'.$i.'_rate']/100);
              $out['tax'.$i.'_cost'] = $cost;
              $rule_total += $cost;
            }
          }
        }
      }
      $out['cost']+=$rule_total;
    }
    return $out;
  }

  public static function get_selector_sql(){
    return
       "SELECT\n"
      ."  `ID` `value`,\n"
      ."  CONCAT((SELECT `TextEnglish` FROM `system` WHERE `system`.`ID` = `tax_zone`.`systemID`),' | ',`name`) `text`,\n"
      ."  IF(`tax_zone`.`systemID`=1,'e0e0e0',IF(`tax_zone`.`systemID`=".SYS_ID.",'c0ffc0','ffe0e0')) AS `color_background`\n"
      ."FROM\n"
      ."  `tax_zone`\n"
      ."ORDER BY\n"
      ."  `text`";
  }

  function get_tax_regimes_and_rules_for_CSV($taxRegimeID_csv){
    $sql =
       "SELECT\n"
      ."  `tr`.`ID`,\n"
      ."  `tr`.`tax1_rate`,\n"
      ."  `tr`.`tax2_rate`,\n"
      ."  `tr`.`tax3_rate`,\n"
      ."  `tr`.`tax4_rate`,\n"
      ."  `tr`.`tax5_rate`,\n"
      ."  `tr`.`tax6_rate`,\n"
      ."  `tr`.`tax7_rate`,\n"
      ."  `tr`.`tax8_rate`,\n"
      ."  `tr`.`tax9_rate`,\n"
      ."  `tr`.`tax10_rate`,\n"
      ."  `tr`.`tax11_rate`,\n"
      ."  `tr`.`tax12_rate`,\n"
      ."  `tr`.`tax13_rate`,\n"
      ."  `tr`.`tax14_rate`,\n"
      ."  `tr`.`tax15_rate`,\n"
      ."  `tr`.`tax16_rate`,\n"
      ."  `tr`.`tax17_rate`,\n"
      ."  `tr`.`tax18_rate`,\n"
      ."  `tr`.`tax19_rate`,\n"
      ."  `tr`.`tax20_rate`,\n"
      ."  `tz`.`tax1_name`,\n"
      ."  `tz`.`tax2_name`,\n"
      ."  `tz`.`tax3_name`,\n"
      ."  `tz`.`tax4_name`,\n"
      ."  `tz`.`tax5_name`,\n"
      ."  `tz`.`tax6_name`,\n"
      ."  `tz`.`tax7_name`,\n"
      ."  `tz`.`tax8_name`,\n"
      ."  `tz`.`tax9_name`,\n"
      ."  `tz`.`tax10_name`,\n"
      ."  `tz`.`tax11_name`,\n"
      ."  `tz`.`tax12_name`,\n"
      ."  `tz`.`tax13_name`,\n"
      ."  `tz`.`tax14_name`,\n"
      ."  `tz`.`tax15_name`,\n"
      ."  `tz`.`tax16_name`,\n"
      ."  `tz`.`tax17_name`,\n"
      ."  `tz`.`tax18_name`,\n"
      ."  `tz`.`tax19_name`,\n"
      ."  `tz`.`tax20_name`\n"
      ."FROM\n"
      ."  `tax_regime` `tr`\n"
      ."INNER JOIN `tax_zone` `tz` ON\n"
      ."  `tr`.`tax_zoneID` = `tz`.`ID`\n"
      ."WHERE\n"
      ."  `tr`.`ID` IN(".$taxRegimeID_csv.")";
    $records = $this->get_records_for_sql($sql);
    $out = array();
    foreach($records as $record){
      $this->_set_ID($record['ID']);
      $record['rules'] = $this->get_tax_rules();
      $out[] = $record;
    }
    return $out;
  }

  function get_tax_columns_in_use($regimes){
    $out = array();
    foreach($regimes as $regime){
      foreach($regime['rules'] as $rule){
        for($i=1; $i<=20; $i++){
          if (trim($rule['tax'.$i.'_apply'])!=''){
            $out[$i] = $i-1;
          }
        }
      }
    }
    sort($out);
    return implode(',',$out);
  }

  function get_tax_rules(){
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `tax_rule`\n"
      ."WHERE\n"
      ."  `tax_regimeID` IN(".$this->_get_ID().")"
      ."ORDER BY\n"
      ."  `seq`";
    return $this->get_records_for_sql($sql);
  }

  function handle_report_copy(&$newID,&$msg,&$msg_tooltip,$name){
    return parent::try_copy($newID,$msg,$msg_tooltip);
  }

  public function get_version(){
    return VERSION_TAX_REGIME;
  }
}
?>