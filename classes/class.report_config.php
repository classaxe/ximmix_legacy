<?php
define ("VERSION_REPORT_CONFIG","1.0.7");

/*
Version History:
  1.0.7 (2012-12-03)
    1) Removed ini_set() that forces display_errors on
  1.0.6 (2012-02-15)
    1) Changes to Report_Config::_draw_config_for_type() now that `adminLinkPosition`
       is renamed to `seq` and `tab` has been added
    2) Changes to Report_Config::get_config_all() now that `adminLinkPosition`
       is renamed to `seq` and `tab` has been added
    3) Changes to Report_Config::get_overview_global now that `adminLinkPosition`
       is renamed to `seq` and `tab` has been added

  (Older version history in class.report_config.txt)
*/

class Report_Config extends Report {

  function draw(){
    global $db;
    $out = "";
    $this->get_config_all();
    $configs = array(
      'global' => array(),
      'site' =>   array(),
      'person' => array()
    );
    foreach($this->_reports as $report){
      if ($report['systemID']==1){
        $configs['global'][] = $report;
      }
      else {
        $configs['site'][] = $report;
      }
    }
    $out.=
       "<h1>Global Reports on ".date('Y-m-d j:m:s')." for database `".$db."`<br />\non ".$_SERVER["SERVER_NAME"]."</h1>"
      .$this->_draw_config_for_type($configs['global'])
      ."<h1>Custom Reports on ".date('Y-m-d j:m:s')." for database `".$db."`<br />\non ".$_SERVER["SERVER_NAME"]."</h1>"
      .$this->_draw_config_for_type($configs['site']);
    return $out;

  }

  function _draw_config_for_type($reports){
    $out = "";
    $strings = array(
      'ID' =>               '',
      'actions' =>          '',
      'columns' =>          '',
      'description' =>      '',
      'filter_presets' =>   '',
      'form' =>             '',
      'help' =>             '',
      'icon' =>             '',
      'name' =>             '',
      'perms' =>            '',
      'reportSortBy' =>     '',
      'report_vars_1' =>    '',
      'report_vars_2' =>    '',
      'report_vars_3' =>    '',
      'report_vars_4' =>    '',
      'report_vars_5' =>    '',
      'report_final' =>     ''
    );
    $odd = true;
    foreach ($reports as $report){
      $custom = false;
      switch($report['name']){
        case "custom_treb_view_listings":
        case "custom_treb_view_room_listings":
          $custom = true;
        break;
        default:
          if (substr($report['name'],0,7)=='module.' || substr($report['name'],0,8)=='CUSTOM: '){
            $custom = true;
          }
        break;
      }
      $actions =        $report['actions'];
      $columns =        $report['columns'];
      $perms =
         $report['permPUBLIC'].$report['permSYSLOGON'].$report['permSYSMEMBER']
        .$report['permSYSEDITOR'].$report['permSYSAPPROVER'].$report['permSYSADMIN']
        .$report['permGROUPVIEWER'].$report['permGROUPEDITOR']
        .$report['permCOMMUNITYADMIN'].$report['permUSERADMIN'].$report['permMASTERADMIN'];
      $icon =           $report['tab']."|".$report['seq']."|".$report['icon'];
      $form =           $report['popupFormWidth']."|".$report['popupFormHeight']."|".$report['formComponentID']."|".$report['archiveChanges']."|".$report['formTitle'];
      $report_vars_1 =  $report['primaryTable']."|".$report['primaryObject']."|".$report['required_feature']."|".$report['reportTitle'];
      $report_vars_2 =  $report['listTypeID']."|".$report['reportComponentID']."|".$report['reportMembersGlobalEditors'];
      $report_vars_3 =  $report['reportSQL_MASTERADMIN']."|".$report['reportSQL_SYSADMIN'];
      $report_vars_4 =  $report['reportSQL_GROUPADMIN']."|".$report['reportSQL_COMMUNITYADMIN'];
      $report_vars_5 =  $report['reportGroupBy'];
      $report_final =   $report['report_crc32'].$report['actions'].$report['columns'].$report['filter_presets'];
      if (!$custom){
        $strings['ID'].=            crc32($report['ID']);
        $strings['actions'].=       crc32($report['actions']);
        $strings['columns'].=       crc32($report['columns']);
        $strings['description'].=   crc32($report['description']);
        $strings['filter_presets'].=crc32($report['filter_presets']);
        $strings['form'].=          crc32($form);
        $strings['help'].=          crc32($report['help']);
        $strings['icon'].=          crc32($icon);
        $strings['name'].=          crc32($report['name']);
        $strings['perms'].=         crc32($perms);
        $strings['reportSortBy'].=  crc32($report['reportSortBy']);
        $strings['report_vars_1'].= crc32($report_vars_1);
        $strings['report_vars_2'].= crc32($report_vars_2);
        $strings['report_vars_3'].= crc32($report_vars_3);
        $strings['report_vars_4'].= crc32($report_vars_4);
        $strings['report_vars_5'].= crc32($report_vars_5);
        $strings['report_final'].=  crc32($report_final);
      }
      $out.=
         "  <tr".($custom ? " bgcolor='#ffff00'" :($odd ? " bgcolor='#f0f0f0'" : "")).">"
        ."    <td style='background-color:#c0c0ff;color:blue'>".dechex(crc32($report_final))."</td>\n"
        ."    <td title=\"".$actions."\">".($actions=='' ? '&nbsp;' : dechex(crc32($actions)))."</td>\n"
        ."    <td title=\"".htmlentities($columns)."\">".($columns=='' ? '&nbsp;' : dechex(crc32($columns)))."</td>\n"
        ."    <td title=\"".$report['filter_presets']."\">".($report['filter_presets']=='' ? '&nbsp;' : dechex(crc32($report['filter_presets'])))."</td>\n"
        ."    <td>".$report['ID']."</td>\n"
        ."    <td>".$report['system_title']."</td>\n"
        ."    <td><b>".$report['name']."</b></td>\n"
        ."    <td title=\"".$report['description']."\">".($report['description']=='' ? '&nbsp;' : dechex(crc32($report['description'])))."</td>\n"
        ."    <td title=\"".$report['help']."\">".($report['help']=='' ? '&nbsp;' : dechex(crc32($report['help'])))."</td>\n"
        ."    <td title=\"".$icon."\">".($icon=='0|' ? '&nbsp;' : dechex(crc32($icon)))."</td>\n"
        ."    <td>".$perms."</td>\n"
        ."    <td title=\"".$form."\">".($form=='0|0|1|0|' ? '&nbsp;' : dechex(crc32($form)))."</td>\n"
        ."    <td>".($report['reportSortBy']=='0' ? '&nbsp;' : $report['reportSortBy'])."</td>\n"
        ."    <td title=\"primaryTable|primaryObject|required_feature|\n".htmlentities($report_vars_1)."\">".dechex(crc32($report_vars_1))."</td>\n"
        ."    <td title=\"listTypeID|reportComponentID|reportMembersGlobalEditors\n".htmlentities($report_vars_2)."\">".($report_vars_2=='1|1|0' ? '&nbsp;' : dechex(crc32($report_vars_2)))."</td>\n"
        ."    <td title=\"reportSQL_MASTERADMIN|reportSQL_SYSADMIN\n".htmlentities($report_vars_3)."\">".dechex(crc32($report_vars_3))."</td>\n"
        ."    <td title=\"reportSQL_GROUPADMIN|reportSQL_COMMUNITYADMIN\n".htmlentities($report_vars_4)."\">".($report_vars_4=='|' ? '&nbsp;' : dechex(crc32($report_vars_4)))."</td>\n"
        ."    <td title=\"reportGroupBy\n".htmlentities($report_vars_5)."\">".($report_vars_5=='||' ? '&nbsp;' : dechex(crc32($report_vars_5)))."</td>\n"
        ."  </tr>";
      $odd = !$odd;
    }
    return
       "<style type=\"text/css\" media=\"print,screen\" >\n"
      ."th {\n"
      ."}\n"
      ."thead {\n"
      ."  display:table-header-group;\n"
      ."}\n"
      ."tbody {\n"
      ."  font-family:monospace;font-size:90%;\n"
      ."  display:table-row-group;\n"
      ."}\n"
      ."</style>\n"
      ."<table border='1' cellspacing='0'>\n"
      ."  <thead>\n"
      ."  <tr>\n"
      ."    <th>Final</th>\n"
      ."    <th>Actions</th>\n"
      ."    <th>Columns</th>\n"
      ."    <th>Filters</th>\n"
      ."    <th>ID</th>\n"
      ."    <th>Site</th>\n"
      ."    <th>Name</th>\n"
      ."    <th>Description</th>\n"
      ."    <th>Help</th>\n"
      ."    <th>Icon</th>\n"
      ."    <th>Permissions</th>\n"
      ."    <th>Form</th>\n"
      ."    <th>Sort</th>\n"
      ."    <th>Report<br />v1</th>\n"
      ."    <th>Report<br />v2</th>\n"
      ."    <th>Report<br />v3</th>\n"
      ."    <th>Report<br />v4</th>\n"
      ."    <th>Report<br />v5</th>\n"
      ."  </tr>\n"
      ."  </thead>\n"
      ."  <tbody>\n"
      ."  <tr style='background-color:#c0c0ff;color:blue'>\n"
      ."    <td style='background-color:#ffc0c0;color:red'><b>".dechex(crc32($strings['report_final']))."</b></td>\n"
      ."    <td>".dechex(crc32($strings['actions']))."</td>\n"
      ."    <td>".dechex(crc32($strings['columns']))."</td>\n"
      ."    <td>".dechex(crc32($strings['filter_presets']))."</td>\n"
      ."    <td>".dechex(crc32($strings['ID']))."</td>\n"
      ."    <td>&nbsp;</td>\n"
      ."    <td>".dechex(crc32($strings['name']))."</td>\n"
      ."    <td>".dechex(crc32($strings['description']))."</td>\n"
      ."    <td>".dechex(crc32($strings['help']))."</td>\n"
      ."    <td>".dechex(crc32($strings['icon']))."</td>\n"
      ."    <td>".dechex(crc32($strings['perms']))."</td>\n"
      ."    <td>".dechex(crc32($strings['form']))."</td>\n"
      ."    <td>".dechex(crc32($strings['reportSortBy']))."</td>\n"
      ."    <td>".dechex(crc32($strings['report_vars_1']))."</td>\n"
      ."    <td>".dechex(crc32($strings['report_vars_2']))."</td>\n"
      ."    <td>".dechex(crc32($strings['report_vars_3']))."</td>\n"
      ."    <td>".dechex(crc32($strings['report_vars_4']))."</td>\n"
      ."    <td>".dechex(crc32($strings['report_vars_5']))."</td>\n"
      ."  </tr>\n"
      .$out
      ."  </tbody>\n"
      ."</table>";
  }

  function get_config_all(){
    $sql =
       "SELECT\n"
      ."  *,\n"
      ."  (SELECT `textEnglish` FROM `system` WHERE `system`.`ID`=`systemID`) `system_title`,\n"
      ."  CRC32(\n"
      ."    CONCAT_WS(\n"
      ."      '|',\n"
      ."      `ID`, `archive`, `archiveID`, `name`, `systemID`, `icon`, `seq`, `tab`\n"
      ."      `archiveChanges`, `description`, `formComponentID`, `formTitle`, `help`, `listTypeID`,\n"
      ."      `permCOMMUNITYADMIN`, `permGROUPVIEWER`, `permGROUPEDITOR`,\n"
      ."      `permMASTERADMIN`, `permPUBLIC`, `permSYSADMIN`, `permSYSAPPROVER`,\n"
      ."      `permSYSEDITOR`, `permSYSLOGON`, `permSYSMEMBER`, `permUSERADMIN`,\n"
      ."      `popupFormHeight`, `popupFormWidth`,\n"
      ."      `primaryObject`, `primaryTable`, `reportComponentID`, `reportGroupBy`,\n"
      ."      `reportMembersGlobalEditors`, `reportSortBy`, `reportSQL_COMMUNITYADMIN`,\n"
      ."      `reportSQL_GROUPADMIN`, `reportSQL_MASTERADMIN`,\n"
      ."      `reportSQL_SYSADMIN`, `reportTitle`, `required_feature`\n"
      ."    )\n"
      ."  ) `report_crc32`\n"
      ."FROM\n"
      ."  `report`\n"
      ."ORDER BY\n"
      ."  `system_title`,`name`";
    $this->_reports = $this->get_records_for_sql($sql);
    $this->_get_config_report_actions();
    $this->_get_config_report_columns();
    $this->_get_config_report_filter_presets();
  }

  private function _get_config_report_actions(){
    foreach ($this->_reports as &$report){
      $sql =
         "SELECT\n"
        ."  CONCAT_WS(\n"
        ."    '|',\n"
        ."    `ID`, `destinationOperation`, `destinationID`, `destinationValue`, `seq`, `sourceTrigger`\n"
        ."  ) `item`\n"
        ."  FROM\n"
        ."    `action`\n"
        ."  WHERE\n"
        ."    `action`.`systemID` = 1 AND\n"
        ."    `action`.`sourcetype` = 'report' AND\n"
        ."    `action`.`sourceID`=".$report['ID']."\n"
        ."  ORDER BY\n"
        ."    `sourceTrigger`,\n"
        ."    `seq`,\n"
        ."    `ID`";
      $records =    $this->get_records_for_sql($sql);
      $items = array();
      foreach ($records as $record){
        $items[] = $record['item'];
      }
      $report['actions'] = implode("\n",$items);
    }
  }

  private function _get_config_report_columns(){
    foreach ($this->_reports as &$report){
      $sql =
         "SELECT\n"
        ."  CONCAT_WS(\n"
        ."    '|',\n"
        ."    `ID`, `group_assign_csv`, `seq`, `tab`, `defaultValue`, `fieldType`, `formField`,\n"
        ."    `formFieldHeight`, `formFieldSpecial`, `formFieldTooltip`, `formFieldUnique`,\n"
        ."    `formFieldWidth`, `formLabel`, `formSelectorSQLMaster`, `formSelectorSQLMember`,\n"
        ."    `permCOMMUNITYADMIN`, `permGROUPVIEWER`, `permGROUPEDITOR`,\n"
        ."    `permMASTERADMIN`, `permPUBLIC`, `permSYSADMIN`, `permSYSAPPROVER`,\n"
        ."    `permSYSEDITOR`, `permSYSLOGON`, `permSYSMEMBER`, `permUSERADMIN`,\n"
        ."    `reportField`, `reportFieldSpecial`,\n"
        ."    `reportFilter`, `reportFilterLabel`, `reportLabel`, `reportSortBy_AZ`,\n"
        ."    `reportSortBy_a`, `reportSortBy_d`, `required_feature`, `required_feature_invert`\n"
        ."  ) `item`\n"
        ."  FROM\n"
        ."    `report_columns`\n"
        ."  WHERE\n"
        ."    `report_columns`.`systemID` = 1 AND\n"
        ."    `report_columns`.`reportID`=".$report['ID']."\n"
        ."  ORDER BY\n"
        ."    `ID`\n";
      $records =    $this->get_records_for_sql($sql);
      $items = array();
      foreach ($records as $record){
        $items[] = $record['item'];
      }
      $report['columns'] = implode("\n",$items);
    }
  }

  private function _get_config_report_filter_presets(){
    foreach ($this->_reports as &$report){
      $sql =
         "SELECT\n"
        ."  CONCAT_WS(\n"
        ."    '|',\n"
        ."    `ID`, `destinationID`, `destinationType`, `label`, `seq`\n"
        ."  ) `item`\n"
        ."  FROM\n"
        ."    `report_filter`\n"
        ."  WHERE\n"
        ."    `report_filter`.`systemID` = 1 AND\n"
        ."    `report_filter`.`destinationType` = 'global' AND\n"
        ."    `report_filter`.`reportID`=".$report['ID']."\n"
        ."  ORDER BY\n"
        ."    `ID`";
      $records =    $this->get_records_for_sql($sql);
      $items = array();
      foreach ($records as $record){
        $items[] = $record['item'];
      }
      $report['filter_presets'] = implode("\n",$items);
    }
  }

  function get_overview_global(){
    $sql =
       "SELECT\n"
      ."  `ID`,\n"
      ."  `name`,\n"
      ."  HEX(\n"
      ."    CRC32(\n"
      ."      CONCAT_WS(\n"
      ."        '|',\n"
      ."        `ID`, `archive`, `archiveID`, `name`, `systemID`, `icon`, `seq`, `tab`,\n"
      ."        `archiveChanges`, `description`, `formComponentID`, `formTitle`, `help`, `listTypeID`,\n"
      ."        `permCOMMUNITYADMIN`, `permGROUPVIEWER`, `permGROUPEDITOR`,\n"
      ."        `permMASTERADMIN`, `permPUBLIC`, `permSYSADMIN`, `permSYSAPPROVER`,\n"
      ."        `permSYSEDITOR`, `permSYSLOGON`, `permSYSMEMBER`, `permUSERADMIN`,\n"
      ."        `popupFormHeight`, `popupFormWidth`,\n"
      ."        `primaryObject`, `primaryTable`, `reportComponentID`, `reportGroupBy`,\n"
      ."        `reportMembersGlobalEditors`, `reportSortBy`, `reportSQL_COMMUNITYADMIN`,\n"
      ."        `reportSQL_GROUPADMIN`, `reportSQL_MASTERADMIN`,\n"
      ."        `reportSQL_SYSADMIN`, `reportTitle`, `required_feature`\n"
      ."      )\n"
      ."    )\n"
      ."  ) `report`,\n"
      ."  (SELECT\n"
      ."    COALESCE(\n"
      ."      HEX(\n"
      ."        CRC32(\n"
      ."          GROUP_CONCAT(\n"
      ."            CRC32(\n"
      ."              CONCAT_WS(\n"
      ."                '|',\n"
      ."                `ID`,`destinationOperation`,`destinationID`,`destinationValue`,`seq`,`sourceTrigger`\n"
      ."               )\n"
      ."            )\n"
      ."            ORDER BY \n"
      ."              `ID`\n"
      ."            )\n"
      ."        )\n"
      ."      ),\n"
      ."      ''\n"
      ."    )\n"
      ."    FROM\n"
      ."      `action`\n"
      ."    WHERE\n"
      ."      `action`.`systemID` = 1 AND\n"
      ."      `action`.`sourcetype` = 'report' AND\n"
      ."      `action`.`sourceID`=`report`.`ID`\n"
      ."  ) `actions`,\n"
      ."  (SELECT\n"
      ."    COALESCE( \n"
      ."      HEX(\n"
      ."        CRC32(\n"
      ."          GROUP_CONCAT(\n"
      ."            CRC32(\n"
      ."              CONCAT_WS(\n"
      ."                '|',\n"
      ."                `ID`, `group_assign_csv`, `seq`, `tab`, `defaultValue`, `fieldType`, `formField`,\n"
      ."                `formFieldHeight`, `formFieldSpecial`, `formFieldTooltip`, `formFieldUnique`,\n"
      ."                `formFieldWidth`, `formLabel`, `formSelectorSQLMaster`, `formSelectorSQLMember`,\n"
      ."                `permCOMMUNITYADMIN`, `permGROUPVIEWER`, `permGROUPEDITOR`,\n"
      ."                `permMASTERADMIN`, `permPUBLIC`, `permSYSADMIN`, `permSYSAPPROVER`,\n"
      ."                `permSYSEDITOR`, `permSYSLOGON`, `permSYSMEMBER`, `permUSERADMIN`,\n"
      ."                `reportField`, `reportFieldSpecial`,\n"
      ."                `reportFilter`, `reportFilterLabel`, `reportLabel`, `reportSortBy_AZ`,\n"
      ."                `reportSortBy_a`, `reportSortBy_d`, `required_feature`, `required_feature_invert`\n"
      ."              )\n"
      ."            )\n"
      ."            ORDER BY \n"
      ."              `ID`\n"
      ."          )\n"
      ."        )\n"
      ."      ),\n"
      ."      ''\n"
      ."    )\n"
      ."    FROM\n"
      ."      `report_columns`\n"
      ."    WHERE\n"
      ."      `report_columns`.`systemID` = 1 AND\n"
      ."      `report_columns`.`reportID`=`report`.`ID`\n"
      ."  ) `columns`,\n"
      ."  (SELECT\n"
      ."    COALESCE(\n"
      ."      HEX(\n"
      ."        CRC32(\n"
      ."          GROUP_CONCAT(\n"
      ."            CRC32(\n"
      ."              CONCAT_WS(\n"
      ."                '|',\n"
      ."                `ID`, `destinationID`, `destinationType`, `label`, `seq`\n"
      ."              )\n"
      ."            )\n"
      ."            ORDER BY \n"
      ."              `ID`\n"
      ."          )\n"
      ."        )\n"
      ."      ),\n"
      ."      ''\n"
      ."    )\n"
      ."    FROM\n"
      ."      `report_filter`\n"
      ."    WHERE\n"
      ."      `report_filter`.`systemID` = 1 AND\n"
      ."      `report_filter`.`destinationType` = 'global' AND\n"
      ."      `report_filter`.`reportID`=`report`.`ID`\n"
      ."  ) `filters`\n"
      ."FROM\n"
      ."  `report`\n"
      ."WHERE\n"
      ."  `systemID` = 1\n"
      ."ORDER BY\n"
      ."  `name`,\n"
      ."  `ID`";
    $records = $this->get_records_for_sql($sql);
    $final = '';
    foreach ($records as &$record){
      $record['report'] =   strToLower($record['report']);
      $record['actions'] =  strToLower($record['actions']);
      $record['columns'] =  strToLower($record['columns']);
      $record['filters'] =  strToLower($record['filters']);
      $crc32 =  dechex(crc32($record['report'].'|'.$record['actions'].'|'.$record['columns'].'|'.$record['filters']));
      $record['crc32'] =    $crc32;
    }
    return $records;
  }

  public function get_version(){
    return VERSION_REPORT_CONFIG;
  }
}
?>
