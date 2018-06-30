# module.treb.uninstall.sql

  # TREB Listings
  DELETE FROM `action`                 WHERE `sourceType`  = 'report' AND `sourceID` IN (1098298172);
  DELETE FROM `report`                 WHERE `ID` IN (1098298172);
  DELETE FROM `group_assign`           WHERE `assign_type` = 'Report Column' AND `assignID` IN (SELECT `ID` FROM `report_columns` WHERE `reportID` IN(1098298172) AND `systemID` IN (1,1472921219));
  DELETE FROM `report_columns`         WHERE `reportID` IN (1098298172) AND `systemID` IN(1,1472921219);
  DELETE FROM `report_filter_criteria` WHERE `filterID` IN (SELECT `ID` FROM `report_filter`  WHERE `reportID` IN (1098298172) AND `destinationType`='global');
  DELETE FROM `report_filter`          WHERE `reportID` IN (1098298172) AND `destinationType`='global';
  DELETE FROM `report_settings`        WHERE `reportID` IN (1098298172) AND `destinationType`='global';

  # TREB Listings Category List type
  DELETE FROM `listtype`               WHERE `ID` IN (696209783);
  DELETE FROM `listdata`               WHERE `ID` IN (447588369,1987799430,201810990,933924119,1679680660,1907097654);


  # TREB Rooms subreport
  DELETE FROM `report`                 WHERE `ID` IN (1372840809);
  DELETE FROM `report_columns`         WHERE `reportID` IN (1372840809) AND `systemID`=1;
  DELETE FROM `report_filter_criteria` WHERE `filterID` IN (SELECT `ID` FROM `report_filter`  WHERE `reportID` IN (1372840809) AND `destinationType`='global');
  DELETE FROM `report_filter`          WHERE `reportID` IN (1372840809) AND `destinationType`='global';
  DELETE FROM `report_settings`        WHERE `reportID` IN (1372840809) AND `destinationType`='global';

  # TREB Module-Specific ECL tags:
  DELETE FROM `ecl_tags`               WHERE `ID` IN (823256807,1717264048);

  # TREB Module-Specific Block Layout:
  DELETE FROM `block_layout`           WHERE `ID` IN (489730715);