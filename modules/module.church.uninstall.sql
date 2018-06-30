# module.church.uninstall.sql
# 1.0.0 (2012-03-14)
#   1) Initial Release

# System Feature
  UPDATE `system` SET `features` = REPLACE(`features`,'module-church','') WHERE `ID` = $systemID;

# Administer Prayer Requests report and form
  DELETE FROM `report`                 WHERE `ID` IN (467924682);
  DELETE FROM `report_columns`         WHERE `reportID` IN (467924682) AND `systemID` IN(1,1470373540);
  DELETE FROM `report_filter_criteria` WHERE `filterID` IN (SELECT `ID` FROM `report_filter`  WHERE `reportID` IN (467924682) AND `destinationType`='global');
  DELETE FROM `report_filter`          WHERE `reportID` IN (467924682) AND `destinationType`='global';
  DELETE FROM `report_settings`        WHERE `reportID` IN (467924682) AND `destinationType`='global';

# Toolbar icon stub (cannot combine as this breaks ajax update)
  DELETE FROM `report`                 WHERE `ID` IN (609724247);

# End-user Prayer Request form
  DELETE FROM `report`                 WHERE `ID` IN (412509576);
  DELETE FROM `report_columns`         WHERE `reportID` IN (412509576) AND `systemID`=1;

# Listtype module.church.prayer-request-status
  DELETE FROM `listtype` WHERE `ID` IN (1839625737);
  DELETE FROM `listdata` WHERE `listtypeID` IN (1839625737) AND `systemID`=1;

# ECL Tags component_daily_bible_verse(), component_report_prayer_requests
# and component_form_prayer_request
  DELETE FROM `ecl_tags`               WHERE `ID` IN (203080703,429904883,916478965);

# Component 'REPORT: MODULE: CHURCH: Prayer Requests Report Redirector'
  DELETE FROM `component`              WHERE `ID` IN (771307961);

# Delete feature module_church for Church Features
  DELETE FROM `listdata`               WHERE `ID` IN (1153178549);
