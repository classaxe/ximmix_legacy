<?php
define("VERSION_REPORT", "1.0.85");

/*
Version History:
  1.0.85 (2015-01-04)
    1) Now uses OPTION_SEPARATOR constant not option_separator in Report::draw_form_view()
    2) Now PSR-2 Compliant

  (Older version history in class.report.txt)
*/

class Report extends Displayable_Item
{
    const FIELDS =                'ID, archive, archiveID, deleted, name, systemID, icon, seq, tab, label, archiveChanges, description, formComponentID, formTitle, help, listTypeID, permCOMMUNITYADMIN, permGROUPVIEWER, permGROUPEDITOR, permMASTERADMIN, permPUBLIC, permSYSADMIN, permSYSAPPROVER, permSYSEDITOR, permSYSLOGON, permSYSMEMBER, permUSERADMIN, popupFormHeight, popupFormWidth, primaryObject, primaryTable, reportComponentID, reportGroupBy, reportMembersGlobalEditors, reportSortBy, reportSQL_COMMUNITYADMIN, reportSQL_GROUPADMIN, reportSQL_MASTERADMIN, reportSQL_SYSADMIN, reportTitle, required_feature, history_created_by, history_created_date, history_created_IP, history_modified_by, history_modified_date, history_modified_IP';
    const REPORT_FEATURES =       'selected_add_to_group, selected_delete, selected_empty, selected_export_excel, selected_export_sql, selected_merge_profiles, selected_process_maps, selected_process_order, selected_send_email, selected_set_as_approved, selected_set_as_attended, selected_set_as_hidden, selected_set_as_member, selected_set_as_spam, selected_set_as_unapproved, selected_set_email_opt_in, selected_set_email_opt_out, selected_set_importance, selected_show_on_map, selected_update, selected_view_email_addresses';
    const COLUMN_FULL_ACCESS =    1;
    const COLUMN_DEFAULT_VALUE =  -1;
    const COLUMN_NO_ACCESS =      0;

    public static $cache_feature_array =     array();
    public static $cache_titles =            false;
    public static $cache_popup_size =        array();

    protected $_report_columns =      array();
    protected $_report_record =       false;
    protected $_report_fields =       array();
    public $match_mode;

    public function __construct($ID = '')
    {
        parent::__construct("report", $ID);
        $this->_set_assign_type("report");
        $this->_set_object_name("Report");
        $this->_set_has_actions(true);
        $this->_set_has_groups(false);
            // Needs to stay false - Reports are used on multiple sites, having group_assign_csv
            // would mess things up badly when copying from one to another.
        $this->_set_message_associated('and associated Columns and Actions have');
    }

    public function actions_execute(
        $trigger,
        $primaryTable,
        $primaryObject,
        $targetID,
        $data = array(),
        &$msg = ''
    ) {
        switch ($trigger) {
            case "report_insert_pre":
            case "report_delete_pre":
            case "report_update_pre":
            case "report_copy_post":
            case "report_delete_post":
            case "report_insert_post":
            case "report_update_post":
                $sourceID =         $this->_get_ID();
                $sourceType =       $this->_get_object_name();
                $sourceTrigger =    $trigger;
                $triggerID =        $targetID;
                $triggerType =      $primaryTable;
                $triggerObject =    $primaryObject;
                $personID =         '';
                $ObjAction =        new Action;

                return
            $ObjAction->execute(
                $sourceType,
                $sourceID,
                $sourceTrigger,
                $personID,
                $triggerType,
                $triggerObject,
                $triggerID,
                $data
            );
            break;
        }
        return false;
    }

    public function convert_xml_field_for_filter(&$field, $table)
    {
      // This code converts xml virtual fields into nested sql matches to isolate the required node
        if (substr($field, 0, 4)!='xml:') {
            return;
        }
        preg_match_all('(xml:[^ =\b]+)', $field, $xml_fields, PREG_SET_ORDER);
        $replacement_sql = array();
        foreach ($xml_fields as $xml_field) {
            $xml_field_path = explode(':', str_replace('/', ':', $xml_field[0]));
            $xml_field_path = array_reverse($xml_field_path);
            $_tmp_sql =
             "\n"
            ."  TRIM(\n"
            ."    LEADING '<![CDATA[' FROM\n"
            ."    SUBSTRING(\n"
            ."      `".$table."`.`XML_data`,\n"
            ."      LOCATE('<![CDATA[',`".$table."`.`XML_data`,\n";
            for ($i=0; $i<count($xml_field_path); $i++) {
                $_tmp_sql.=
                 str_repeat('  ', $i+4)
                ."LOCATE('<".$xml_field_path[$i].">',`".$table."`.`XML_data`"
                .($i+1==count($xml_field_path) ? ')' : ',')
                ."\n";
            }
            for ($i=count($xml_field_path)-1; $i>=0; $i--) {
                $_tmp_sql.=
                 "    "
                .str_repeat('  ', $i+1)
                .")"
                .($i>0 ? "\n" : ",\n");
            }
            $_tmp_sql.=
            "      LOCATE(']]>',`".$table."`.`XML_data`,\n";
            for ($i=0; $i<count($xml_field_path); $i++) {
                $_tmp_sql.=
                 str_repeat('  ', $i+4)
                ."LOCATE('<".$xml_field_path[$i].">',`".$table."`.`XML_data`"
                .($i+1==count($xml_field_path) ? ')' : ',')
                ."\n";
            }
            for ($i=count($xml_field_path)-1; $i>=0; $i--) {
                $_tmp_sql.=
                 "    "
                .str_repeat('  ', $i+1)
                .")"
                .($i>0 ? "\n" : "-\n");
            }
            $_tmp_sql.=
            "      LOCATE('<![CDATA[',`".$table."`.`XML_data`,\n";
            for ($i=0; $i<count($xml_field_path); $i++) {
                $_tmp_sql.=
                 str_repeat('  ', $i+4)
                ."LOCATE('<".$xml_field_path[$i].">',`".$table."`.`XML_data`"
                .($i+1==count($xml_field_path) ? ')' : ',')
                ."\n";
            }
            for ($i=count($xml_field_path)-1; $i>=0; $i--) {
                $_tmp_sql.=
                 "    "
                .str_repeat('  ', $i+1)
                .")\n";
            }
            $_tmp_sql.=
            "    )\n"
            ."  )";


            $replacement_sql[] = $_tmp_sql;
        }
        $match_num = 0;
        $field =
        preg_replace(
            "/(xml:[^ =\b]+)/e",
            "\$replacement_sql[\$match_num++]",
            $field
        );
    }

    public function convert_xml_field_for_sort(&$field, $table)
    {
      // This code converts xml virtual fields into nested sql matches to isolate the required node
        preg_match_all('(xml:[^ =\b]+)', $field, $xml_fields, PREG_SET_ORDER);
        $replacement_sql = array();
        foreach ($xml_fields as $xml_field) {
            $xml_field_path = explode(':', str_replace('/', ':', $xml_field[0]));
            $xml_field_path = array_reverse($xml_field_path);
            $_tmp_sql =
             "\n"
            ."  SUBSTRING(\n"
            ."    `".$table."`.`XML_data`,\n"
            ."    9+LOCATE('<![CDATA[',`".$table."`.`XML_data`,\n";
            for ($i=0; $i<count($xml_field_path); $i++) {
                $_tmp_sql.=
                 str_repeat('  ', $i+4)
                ."LOCATE('<".$xml_field_path[$i].">',`".$table."`.`XML_data`"
                .($i+1==count($xml_field_path) ? ')' : ',')
                ."\n";
            }
            for ($i=count($xml_field_path)-1; $i>=0; $i--) {
                $_tmp_sql.=
                 "  "
                .str_repeat('  ', $i+1)
                .")\n";
            }
            $_tmp_sql.= "  )";
            $replacement_sql[] = $_tmp_sql;
        }
        $match_num = 0;
        $field =
        preg_replace(
            "/(xml:[^ =\b]+)/e",
            "\$replacement_sql[\$match_num++]",
            $field
        );
    }

    public function copy($new_name = false, $new_systemID = false, $new_date = true)
    {
        $newID =    parent::copy($new_name, $new_systemID, $new_date);
        $old_reportSortBy = $this->get_field('reportSortBy');
        $new_reportSortBy = false;
        $columns =          $this->get_report_columns();
        $Obj_RC =           new Report_Column;
        foreach ($columns as $data) {
            $oldColumnID = $data['ID'];
            unset($data['ID']);
            unset($data['archive']);
            unset($data['archiveID']);
            if ($new_date) {
                unset($data['history_created_by']);
                unset($data['history_created_date']);
                unset($data['history_created_IP']);
                unset($data['history_modified_by']);
                unset($data['history_modified_date']);
                unset($data['history_modified_IP']);
            }
            if ($new_systemID) {
                $data['systemID'] = $new_systemID;
            }
            $data['reportID'] = $newID;
            foreach ($data as $column => $content) {
                $data[$column] = str_replace('"', '\"', $content);
            }
            $newColumnID =    $Obj_RC->insert($data);
            if ($oldColumnID==$old_reportSortBy) {
                $new_reportSortBy=$newColumnID;
            }
            $Obj_RC->_set_ID($oldColumnID);
            $Obj_RC->copy_group_assign($newColumnID);
        }
      // Update default sorting column to reflect the ID for the equivalent copied column
        if ($new_reportSortBy) {
            $this->_set_ID($newID);
            $this->set_field('reportSortBy', $new_reportSortBy);
        }
        return $newID;
    }

    public function delete()
    {
        $Obj_RC = new Report_Column;
        $columns = $this->get_report_columns();
        foreach ($columns as $column) {
            $Obj_RC->_set_ID($column['ID']);
            $Obj_RC->delete();  // Takes care of group unassignments
        }
        parent::delete();
    }

    public function download_data($ID, $field)
    {
        $table = $this->get_field('primaryTable');
        $Obj = new Record($table, $ID);
        $value = $Obj->get_field($field);
        if ($value!='') {
            $parts = $this->get_embedded_file_properties($value);
            header("Content-Disposition: attachment;filename=\"".$parts['name']."\"");
            header('Content-Type: '.$parts['type']);
            header('Content-Length: '.$parts['size']);
            print $parts['data'];
            die;
        }
    }

    public function download_userfile_data($ID, $field)
    {
        $table = $this->get_field('primaryTable');
        $Obj = new Record($table, $ID);
        $value = $Obj->get_field($field);
        if ($value!='') {
            $parts = $this->get_embedded_file_properties($value);
            header("Content-Disposition: attachment;filename=\"".$parts['name']."\"");
            header('Content-Type: '.$parts['type']);
            header('Content-Length: '.$parts['size']);
            readfile(".".substr($parts['data'], strlen('url('), -1));
            die;
        }
    }

    public function draw_form($report_name, $controls = 1, $alt_controls = '')
    {
        $Obj_Report_Form = new Report_Form;
        return $Obj_Report_Form->draw($report_name, $controls, $alt_controls);
    }

    public function draw_form_view(
        $report_name,
        $ID = false,
        $doQuery = false,
        $headers = true,
        $with_fields = true,
        $language = 'en',
        $forEmail = false
    ) {
        $reportID =   $this->get_ID_by_name($report_name);
        if (!$reportID) {
            return "Invalid Report - ".$report_name;
        }
        $this->_set_ID($reportID);
        $rowForm =      $this->get_record();
        $columnList =    $this->get_columns();
        if ($doQuery) {
            $sql =    "SELECT * FROM `".$rowForm['primaryTable']."` WHERE `ID` IN($ID)";
            $records = $this->get_records_for_sql($sql);
        } else {
            foreach ($_POST as $key => $val) {
                $row[$key] = $val;
            }
            $records[] = $row;
        }
        $out =    "";
        foreach ($records as $row) {
            $this->xmlfields_decode($row);
            switch ($language){
                case 'en':
                    $lbl_att = 'Attribute';
                    $lbl_val = 'Value';
                    break;
                case 'fr':
                    $lbl_att = 'Atribut';
                    $lbl_val = 'Valeur';
                    break;
            }
            $out.=
                "<table"
                .($forEmail ? " border='1' cellspacing='0' cellpadding='2'" : " class='form_view'")
                .">\n"
                .($headers ?
                     "  <tr class='head'>\n"
                    ."    <th>".$lbl_att."</th>\n"
                    ."    <th>".$lbl_val."</th>\n"
                    ."  </tr>\n"
                :
                    ""
                );
    //      y($columnList);die;
            $old_section_tab = "";
            foreach ($columnList as $column) {
      //        print_r($column); die;
                if ($column['visible']) {
                    $tab =            $column['tab'];
                    $field =            $column['formField'];
                    $label =            $column['formLabel'];
                    if ($field!="") {
                        $formSelectorSQLMaster =    $column['formSelectorSQLMaster'];
                        $formSelectorSQLMember =    $column['formSelectorSQLMember'];
                        $formFieldSpecial =         $column['formFieldSpecial'];
                        $value =    stripslashes(is_array($row) && array_key_exists($field, $row) ? $row[$field] : "");
                        $type =        $column['fieldType'];
                        if ($tab!=$old_section_tab) {
                            $tab_arr = explode(".", $tab);
                            if (isset($tab_arr[1]) && $tab_arr[1]!='') {
                                $out.=
                                 "  <tr class='subhead'>\n"
                                ."    <th colspan='2'>".$tab_arr[1]."</th>\n"
                                ."  </tr>\n";
                            }
                            $old_section_tab = $tab;
                        }
                        switch ($field){
                            case "icon":
                                $value = convert_html_to_safe_view($value, false);
                                break;
                        }
                        switch($type) {
                            case "categories_assign":
                            case "checkbox_listdata_csv":
                            case "combo_listdata":
                            case "radio_listdata":
                            case "selector_listdata":
                            case "selector_listdata_csv":
                                $params_arr =   explode("|", $formFieldSpecial);
                                $listtype = $params_arr[0];
                                $valueField = (isset($params_arr[2]) ? $params_arr[2] : 'value');
                                break;
                            case "selector_billing_address":
                                $listtype = "lst_billing_address";
                                $valueField = 'value';
                                break;
                        }
                        switch ($type) {
                            case "categories_assign":
                            case "checkbox_listdata_csv":
                            case "combo_listdata":
                            case "radio_listdata":
                            case "selector_billing_address":
                            case "selector_listdata":
                            case "selector_listdata_csv":
                                $selectorSQL =
                                    "SELECT\n"
                                     ."  `color_text`,\n"
                                     ."  `color_background`,\n"
                                     ."  `isHeader`,\n"
                                     ."  `seq`,\n"
                                     ."  `textEnglish` AS `text`,\n"
                                     ."  `".$valueField."` AS `value`\n"
                                     ."FROM\n"
                                     ."  `listdata`\n"
                                     ."INNER JOIN `listtype` ON\n"
                                     ."  `listdata`.`listTypeID` = `listtype`.`ID`\n"
                                     ."WHERE\n"
                                     ."  `listtype`.`name` = \"".$listtype."\" AND\n"
                                     ."  `listdata`.`systemID` IN(1,"
                                     .(isset($row['systemID']) ? $row['systemID'] : SYS_ID)
                                     .")\n"
                                     ."ORDER BY\n"
                                     ."  `seq`,`text`";
              //              z($selectorSQL);
                                break;
                            case "keywords_assign":
                                $selectorSQL = Keyword::get_selector_sql();
                                break;
                            case "languages_assign":
                                $selectorSQL =
                                    "SELECT\n"
                                     ."  `color_text`,\n"
                                     ."  `color_background`,\n"
                                     ."  `isHeader`,\n"
                                     ."  `seq`,\n"
                                     ."  `textEnglish` AS `text`,\n"
                                     ."  `value` AS `value`\n"
                                     ."FROM\n"
                                     ."  `listdata`\n"
                                     ."INNER JOIN `listtype` ON\n"
                                     ."  `listdata`.`listTypeID` = `listtype`.`ID`\n"
                                     ."WHERE\n"
                                     ."  `listtype`.`name` = \"".$listtype."\" AND\n"
                                     ."  `listdata`.`systemID` IN(1,"
                                     .(isset($row['systemID']) ? $row['systemID'] : SYS_ID).")\n"
                                     ."ORDER BY\n"
                                     ."  `seq`,`text`";
              //              z($selectorSQL);
                                break;
                            default:
                                $selectorSQL = (get_person_permission("MASTERADMIN") ?
                                    $formSelectorSQLMaster
                                 :
                                    $formSelectorSQLMember
                                );
                                break;
                        }
                        switch ($type) {
                            case "html":
                                $value = convert_html_to_safe_view($value, false);
                                break;
                            case "option_list":
                                $value = str_replace(OPTION_SEPARATOR, "<br />", $value);
                                break;
                        }
                        switch ($type) {
                            case "hidden":
                            case "listTypeID":
                            case "selectID":
                            case "fixed":
                              // do nothing
                                break;
                            case "bool":
                                $out.=
                                     "  <tr>\n"
                                    ."    <th title='Field Type: ".$type."'"
                                    .($forEmail ? " align='left'" : "")
                                    .">".$label."</th>\n"
                                    ."    <td>"
                                    .($with_fields ? draw_form_field($field, $value, 'hidden') : "")
                                    .($value==0 ? 'No' : 'Yes')
                                    ."</td>\n"
                                    ."  </tr>\n";
                                break;
                            case "checkbox_listdata_csv":
                            case "checkbox_sql_csv":
                            case "radio_listdata":
                            case "selector":
                            case "selector_url":
                            case "selector_listdata":
                                $out_arr = array();
                                $sql =   get_sql_constants($selectorSQL);
                                $records = $this->get_records_for_sql($sql);
                                if ($records===false) {
                                    $out_arr[]=z($sql);
                                } else {
                                    foreach ($records as $record) {
                                        if (count($records)==1 || $record['value']==$value) {
                                            $out_arr[] = $record['text'];
                                        }
                                    }
                                }
                                $out.=
                                     "  <tr>\n"
                                    ."    <th title='Field Type: ".$type."'"
                                    .($forEmail ? " align='left'" : "")
                                    .">".$label."</th>\n"
                                    ."    <td>"
                                    .($with_fields ? draw_form_field($field, $value, 'hidden') : "")
                                    .implode("", $out_arr)
                                    ."</td>\n"
                                    ."  </tr>\n";
                                break;
                            case "file_upload":
                                $out.=
                                     "  <tr>\n"
                                    ."    <th title='Field Type: ".$type."'"
                                    .($forEmail ? " align='left'" : "")
                                    .">".$label."</th>\n"
                                    ."    <td>(File Upload)</td>\n"
                                    ."  </tr>\n";
                                break;
                            case "file_upload_to_userfile_folder":
                                $out.=
                                     "  <tr>\n"
                                    ."    <th title='Field Type: ".$type."'"
                                    .($forEmail ? " align='left'" : "")
                                    .">".$label."</th>\n"
                                    ."    <td>(File Upload to UserFiles folder)</td>\n"
                                    ."  </tr>\n";
                                break;
                            case "textarea":
                            case "textarea_big":
                                $out.=
                                     "  <tr>\n"
                                    ."    <th title='Field Type: ".$type."'"
                                    .($forEmail ? " align='left'" : "")
                                    .">".$label."</th>\n"
                                    ."    <td>"
                                    .($with_fields ? draw_form_field($field, $value, 'hidden') : "")
                                    .nl2br($value)."</td>\n"
                                    ."  </tr>\n";
                                break;
                            default:
                                $out.=
                                     "  <tr>\n"
                                    ."    <th title='Field Type: ".$type."'"
                                    .($forEmail ? " align='left'" : "")
                                    .">".$label."</th>\n"
                                    ."    <td>"
                                    .($with_fields ? draw_form_field($field, $value, 'hidden') : "")
                                    .$value."</td>\n"
                                    ."  </tr>\n";
                                break;
                        }
                    }
                }
            }
            $out.= "</table>\n";
        }
  //    die($out);
        return $out;
    }

    public function draw_print_form($report_name, $ID)
    {
        return $this->draw_form_view($report_name, $ID, true, true, false);
    }

    public function draw_report($report_name, $toolbar, $ajax_mode = false)
    {
        deprecated();
        $Obj = new Report_Report($this->_get_ID());
        return $Obj->draw($report_name, $toolbar, $ajax_mode);
    }

    public function email_form($targetID, $subject, $to, $cc = '', $bcc = '')
    {
        global $system_vars;
        $report_name =  $this->get_name();
        $body_html =
            $this->draw_form_view(
                $report_name,
                $targetID,
                true,
                true,
                false,
                $system_vars['defaultLanguage'],
                true
            );
        get_mailsender_to_component_results(); // Use system default mail details
        component_result_set('NName', $to);
        component_result_set('PEmail', $to);
        component_result_set('system_title', $system_vars['textEnglish']);
        component_result_set('system_URL', $system_vars['URL']);
        $data =               array();
        $data['PEmail'] =     $to;
        $data['NName'] =      $to;
        $data['subject'] =    $subject;
        $data['html'] =       $body_html;
        $data['text'] =       "";
  //    print_r($data);die;
        return mailto($data);
    }

    public function email_sql($targetID, $subject, $to, $cc = '', $bcc = '')
    {
        global $system_vars;
        $report_name = $this->get_name();
        $Obj = new Export();
        $body_text = $Obj->sql(true, $report_name, $targetID);
        get_mailsender_to_component_results(); // Use system default mail details
        component_result_set('NName', $to);
        component_result_set('PEmail', $to);
        component_result_set('system_title', $system_vars['textEnglish']);
        component_result_set('system_URL', $system_vars['URL']);
        $data =               array();
        $data['PEmail'] =     $to;
        $data['NName'] =      $to;
        $data['subject'] =    $subject;
        $data['html'] =       "";
        $data['text'] =       $body_text;
  //    print_r($data);die;
        return mailto($data);
    }

    public function export_sql($targetID, $show_fields = 1)
    {
        $include_site_filter_presets = 0;
        $clause =
        ($include_site_filter_presets ?
            " AND (`destinationType`='global' OR (`destinationType`='system' AND `destinationID`=".SYS_ID."))"
         :
            " AND (`destinationType`='global')"
        );
        $header =
             "Selected ".$this->_get_object_name().$this->plural($targetID)."\n"
            ."(with Actions, Columns and Column Group Assignments\n"
            ." available to THIS system)";
        $extra_delete =
             "DELETE FROM `group_assign`           "
            ."WHERE `assign_type` = 'Report Column' AND `assignID` IN("
            ."SELECT `ID` FROM `report_columns` WHERE `reportID` IN(".$targetID.") AND `systemID` IN(1,".SYS_ID.")"
            .");\n"
            ."DELETE FROM `report_columns`         "
            ."WHERE `reportID` IN ($targetID) AND `systemID` IN(1,".SYS_ID.");\n"
            ."DELETE FROM `report_filter_criteria` "
            ."WHERE `filterID` IN (SELECT `ID` FROM `report_filter`  WHERE `reportID` IN (".$targetID.")".$clause.");\n"
            ."DELETE FROM `report_filter`          "
            ."WHERE `reportID` IN (".$targetID.")".$clause.";\n"
            ."DELETE FROM `report_settings`        "
            ."WHERE `reportID` IN (".$targetID.")".$clause.";\n";
        $Obj = new Backup;
        $extra_select =
            $Obj->db_export_sql_query(
                "`group_assign`          ",
                "SELECT * FROM `group_assign` WHERE `assign_type` = 'Report Column' AND"
                ." `assignID` IN ("
                ."SELECT `ID` FROM `report_columns` WHERE `reportID` IN(".$targetID.") AND `systemID` IN (1,".SYS_ID.")"
                .") ORDER BY `ID`",
                $show_fields
            )
            .$Obj->db_export_sql_query(
                "`report_columns`        ",
                "SELECT * FROM `report_columns` WHERE `reportID` IN (".$targetID.") AND `systemID` IN(1,".SYS_ID.") "
                ."ORDER BY `reportID`,`tab`,`seq`,`ID`",
                $show_fields
            )
            .$Obj->db_export_sql_query(
                "`report_filter`         ",
                "SELECT * FROM `report_filter` WHERE `reportID` IN (".$targetID.")".$clause." ORDER BY `ID`",
                $show_fields
            )
            .$Obj->db_export_sql_query(
                "`report_filter_criteria`",
                "SELECT * FROM `report_filter_criteria` WHERE `filterID` IN ("
                ."SELECT `ID` FROM `report_filter` WHERE `reportID` IN (".$targetID.")".$clause.") ORDER BY `ID`",
                $show_fields
            )
            .$Obj->db_export_sql_query(
                "`report_settings`       ",
                "SELECT * FROM `report_settings` WHERE `reportID` IN (".$targetID.")".$clause." ORDER BY `ID`",
                $show_fields
            )
        ;
        return parent::sql_export($targetID, $show_fields, $header, '', $extra_delete, $extra_select);
    }

    public static function get_and_set_sortOrder($report_row, $columnList, $sortBy)
    {
      // See if this user has previously changed sort order:
        $_Obj_Report_Defaults = new Report_Defaults;
        $_defaults =            $_Obj_Report_Defaults->get_defaults($report_row['ID'], $report_row['reportSortBy']);

      // Get column and direction to sort by:
        if ($sortBy == "") {
            foreach ($columnList as $_column) {
                if ($_column['ID']==$_defaults['sortColumnID']) {
                    $sortBy =
                    ($_column['reportSortBy_AZ'] ?
                       ($_defaults['sortColumnReverse'] ? $_column['reportField']."_d" : $_column['reportField'])
                     :
                       ($_defaults['sortColumnReverse'] ? $_column['reportField'] : $_column['reportField']."_d")
                    );
                }
            }
        }
      // Save the direction actually chosen:
        if ($sortBy!="") {
            $_save_default_columnID = false;
            $_save_default_column_reverse = 0;
            foreach ($columnList as $_column) {
                if ($_column['reportField']==$sortBy) {
                    $_save_default_columnID = $_column['ID'];
                    $_save_default_column_reverse = ($_column['reportSortBy_AZ'] ? 0 : 1);
                }
                if ($_column['reportField']."_d"==$sortBy) {
                    $_save_default_columnID = $_column['ID'];
                    $_save_default_column_reverse = ($_column['reportSortBy_AZ'] ? 1 : 0);
                }
            }
            if ($_save_default_columnID) {
                $_Obj_Report_Defaults->_set_ID($_defaults['ID']);
                $_Obj_Report_Defaults->set_defaults(
                    $report_row['ID'],
                    $_save_default_columnID,
                    $_save_default_column_reverse
                );
            }
        }
        return $sortBy;
    }

    public function get_columns()
    {
        if (!$this->_get_ID()) {
            return false;
        }
        $this->_get_columns_fetch_all();
        $this->_get_columns_fix_tabs();
        $this->_get_columns_mark_visiblity();
        $this->_get_columns_check_features();
        $this->_get_columns_mark_readonly();
        $this->_get_columns_remove_overridden();
        $this->_get_columns_custom_code_report_column_rules();
        $this->_get_columns_sort();
        return $this->_report_columns;
    }


    private function _get_columns_check_features()
    {
      //  const COLUMN_FULL_ACCESS=1;
      //  const COLUMN_DEFAULT_VALUE=-1;
      //  const COLUMN_NO_ACCESS=0;
        global $system_vars;
        $features_arr = explode(",", str_replace(", ", ",", $system_vars['features']));
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        foreach ($this->_report_columns as &$record) {
          // Assume no access to start
            $record['access'] = Report::COLUMN_NO_ACCESS;
        }
        foreach ($this->_report_columns as &$record) {
          // If visible and no feature, access=1
            if ($record['visible'] && !$record['required_feature']) {
                $record['access'] = Report::COLUMN_FULL_ACCESS;
            }
        }
        foreach ($this->_report_columns as &$record) {
          // Assume that if visible and has feature, access=-1
            if ($record['visible'] && $record['required_feature']) {
                $record['access'] = Report::COLUMN_DEFAULT_VALUE;
            }
        }
        if ($isMASTERADMIN) {
            foreach ($this->_report_columns as &$record) {
              // Check feature-enabled features again if IS MASTERADMIN
                if ($record['required_feature']) {
                    if (!$record['required_feature_invert']) {
                        $record['access'] = Report::COLUMN_FULL_ACCESS;
                    }
                    if ($record['required_feature_invert']) {
                        $record['access'] = Report::COLUMN_NO_ACCESS; // was set to COLUMN_DEFAULT_VALUE
                    }
                }
            }
            return;
        }
      // Check feature-enabled features again if IS NOT MASTERADMIN
        foreach ($this->_report_columns as &$record) {
            if ($record['required_feature']) {
                $req_arr = explode(',', $record['required_feature']);
                $req_met = true;
                if (!$record['required_feature_invert']) {
                    foreach ($req_arr as $r) {
                        if (!in_array(trim($r), $features_arr)) {
                            $req_met = false;
                        }
                    }
                }
                if ($record['required_feature_invert']) {
                    foreach ($req_arr as $r) {
                        if (in_array(trim($r), $features_arr)) {
                            $req_met = false;
                        }
                    }
                }
                if ($req_met) {
                    $record['access'] = Report::COLUMN_FULL_ACCESS;
                }
            }
        }
    }

    private function _get_columns_fetch_all()
    {
      // Places global columns last to allow them to be ignored if overridden
        $sql =
             "SELECT\n"
            ."  `report`.`name`,\n"
            ."  `report_columns`.`ID` AS `report_columnID`,\n"
            ."  `report_columns`.*\n"
            ."FROM\n"
            ."  `report`\n"
            ."LEFT JOIN `report_columns` ON\n"
            ."  `report`.`ID` = `report_columns`.`reportID`\n"
            ."WHERE\n"
            ."  `report`.`ID`=".$this->_get_ID()." AND\n"
            ."  `report_columns`.`systemID` IN(1,".SYS_ID.")\n"
            ."ORDER BY\n"
            ."  `tab`,\n"
            ."  `seq`,\n"
            ."  `report_columns`.`systemID`=1";
        $this->_report_columns = $this->get_records_for_sql($sql);
    }


    private function _get_columns_fix_tabs()
    {
        foreach ($this->_report_columns as &$record) {
            if ($record['tab']=='') {
                $record['tab'] = '0.';
            }
        }
    }


    private function _get_columns_custom_code_report_column_rules()
    {
      // Used by GRPA
        if (!function_exists('custom_code_report_column_rules')) {
            return;
        }
        foreach ($this->_report_columns as &$record) {
            $record = custom_code_report_column_rules($this->_get_ID(), $record);
        }
    }


    private function _get_columns_mark_readonly()
    {
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $isUSERADMIN =        get_person_permission("USERADMIN");
        $isCOMMUNITYADMIN =    get_person_permission("COMMUNITYADMIN");
        $isSYSADMIN =        get_person_permission("SYSADMIN");
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =        get_person_permission("SYSEDITOR");
        $isSYSMEMBER =        get_person_permission("SYSMEMBER");
        $isSYSLOGON =        get_person_permission("SYSLOGON");
        $isGROUPEDITOR =    get_person_permission("GROUPEDITOR");
        $isGROUPVIEWER =    get_person_permission("GROUPVIEWER");
        $isPUBLIC =            get_person_permission("PUBLIC") || get_person_permission("SYSLOGON");
        foreach ($this->_report_columns as &$record) {
            $record['readOnly'] = 1;
            $isVIEWER = get_person_permission("VIEWER", $record['group_assign_csv']);
            if (
            ($record['permMASTERADMIN'] ==      '2' && $isMASTERADMIN) ||
            ($record['permUSERADMIN'] ==        '2' && $isUSERADMIN) ||
            ($record['permCOMMUNITYADMIN'] ==   '2' && $isCOMMUNITYADMIN) ||
            ($record['permSYSADMIN'] ==         '2' && $isSYSADMIN) ||
            ($record['permSYSAPPROVER'] ==      '2' && $isSYSAPPROVER) ||
            ($record['permSYSEDITOR'] ==        '2' && $isSYSEDITOR) ||
            ($record['permSYSMEMBER'] ==        '2' && $isSYSMEMBER) ||
            ($record['permSYSLOGON'] ==         '2' && $isSYSLOGON) ||
            ($record['permGROUPEDITOR'] ==      '2' && $isGROUPEDITOR) ||
            ($record['permGROUPVIEWER'] ==      '2' && $isGROUPVIEWER) ||
            ($record['permPUBLIC'] ==           '2' && $isPUBLIC) ||
            ($isVIEWER)               // Assume read / write if specific group member
            ) {
                $record['readOnly'] = 0;
            }
        }
    }


    private function _get_columns_mark_visiblity()
    {
        foreach ($this->_report_columns as &$record) {
            $record['visible'] = $this->is_visible($record);
        }
    }


    private function _get_columns_remove_overridden()
    {
        $reduced =        array();
        $overridden =   array();
        foreach ($this->_report_columns as $record) {
            $key =
                 $record['fieldType']."-"
                .$record['formField']."-"
                .$record['tab']."-"
                .$record['required_feature']."-"
                .$record['required_feature_invert'];
            if (!isset($overridden[$key])) {
                $reduced[] = $record;
                if ($record['formField']!="" && $record['fieldType']!='delete') {
                    $overridden[$key] = true;
                }
            }
        }
        $this->_report_columns = $reduced;
    }

    private function _get_columns_sort()
    {
        usort($this->_report_columns, array($this,'_get_columns_sort_function'));
    }

    public function _get_columns_sort_function($a, $b)
    {
        $sort_tabs = strcmp(strtolower($a['tab']), strtolower($b['tab']));
        if ($sort_tabs != 0) {
            return $sort_tabs;
        }
        $sort_seq = ($a['seq'] > $b['seq']);
        if ($sort_seq != 0) {
            return $sort_seq;
        }
        $sort_sys = ($a['systemID']==1 ? 1 : 0) - ($b['systemID']==1 ? 1 : 0);
        if ($sort_sys != 0) {
            return $sort_sys;
        }
        return 0;
    }

    public function get_email_addresses()
    {
        global $sortBy;
        $targetID =     get_var('targetID');
        $filterField =  get_var('filterField');
        $filterExact =  get_var('filterExact');
        $filterValue =  get_var('filterValue');
        $targetReportID =   get_var('targetReportID');
        $out = array();
        $report_record = $this->get_record();
        $_columns =      $this->get_columns();
        $table =            $report_record['primaryTable'];
        $filterField_sql = "";
        if ($filterField!='') {
            $ObjReportColumn =  new Report_Column;
            $ObjReportColumn->_set_ID($filterField);
            $filter_column_record = $ObjReportColumn->get_record();
            if ($filter_column_record['reportID'] == $targetReportID) {
                $filterField_sql = $filter_column_record['reportFilter'];
            }
            Report::convert_xml_field_for_filter($filterField_sql, $table);
        }
        $sql =  (get_person_permission("MASTERADMIN") ?
            $report_record['reportSQL_MASTERADMIN']
         :
            $report_record['reportSQL_SYSADMIN']
        );
        $sql =
         get_sql_constants($sql)
        .Report_Report::get_filter($filterField_sql, $filterExact, $filterValue)
        .($sortBy ? Report::get_sortBy($_columns, $table) : "")
        ;
        $records = $this->get_records_for_sql($sql);
        if ($records && (!isset($records[0]['mail_PEmail']) || !isset($records[0]['mail_NName']))) {
            $out[] =
                  "Error:"
                 ."Unsupported feature for the '".$report_record['name']."' report:\n"
                 ."  All reports require `mail_PEmail` and `mail_NName` columns to correctly\n"
                 ."  display email addresses. This report doesn't have those columns yet.\n\n"
                 ."Please tell us about this error so that we can fix it.";
            return $out;
        }
        $targetID_arr = ($targetID ? explode(',', $targetID) : array());
        foreach ($records as $record) {
            if (strpos($record['mail_PEmail'], '@')!==false && (!$targetID || in_array($record['ID'], $targetID_arr))) {
                $out[] =
                     (trim($record['mail_NName']) ? "\"".str_replace("  ", " ", trim($record['mail_NName']))."\" " : "")
                    ."<".trim($record['mail_PEmail']).">;\n";
            }
        }
        return $out;
    }

    public static function get_match_mode($name)
    {
        $Obj_Listtype = new Listtype;
        $Obj_Listtype->_set_ID($Obj_Listtype->get_ID_by_name('lst_report_match_mode'));
        $result =   $Obj_Listtype->get_record_by_name($name);
        return $result['value'];
    }

    public static function get_match_modes()
    {
        $Obj_Listtype = new Listtype;
        $Obj_Listtype->_set_ID($Obj_Listtype->get_ID_by_name('lst_report_match_mode'));
        return $Obj_Listtype->get_listdata('seq');
    }

    public static function get_ObjPrimary($report_name, $primaryObjectName)
    {
        $ObjPrimary = false;
        if ($primaryObjectName) {
            if (substr($report_name, 0, 7)=='module.') {
                $report_name_arr = explode('.', $report_name);
                $Obj_Module = Base::use_module($report_name_arr[1]);
                if ($Obj_Module instanceof Base_Error) {
                    return "<p><b>Error:</b> ".$Obj_Module->errorMessage."</p>";
                }
            }
            $ObjPrimary = new $primaryObjectName;
        }
        if (!$ObjPrimary) {
            $ObjPrimary = new Record;
        }
        return $ObjPrimary;
    }

    public function get_popup_params_for_report_form($report_name)
    {
        if (isset(Report::$cache_popup_size[$report_name])) {
            $record = Report::$cache_popup_size[$report_name];
        } else {
            $sql =
                 "SELECT\n"
                ."  `popupFormHeight`,\n"
                ."  `popupFormWidth`\n"
                ."FROM\n"
                ."  `report`\n"
                ."WHERE\n"
                ."  `name` = \"".$report_name."\"";
    //      y($sql);
            $record = $this->get_record_for_sql($sql);
            if ($record===false) {
                print
                __CLASS__.'::'.__FUNCTION__."()<br />No such report as ".$report_name."<br />"
                .x();
                return false;
            }
            Report::$cache_popup_size[$report_name] = $record;
        }
        return
        array(
            'h' =>              $record['popupFormHeight'],
            'w' =>              $record['popupFormWidth']
        );
    }

    public function get_popup_sizes_for_names(&$result, $names_csv)
    {
        $names_arr = explode(",", $names_csv);
        $miss_arr = array();
        foreach ($names_arr as $name) {
            if (isset(Report::$cache_popup_size[$name])) {
                $_cache = Report::$cache_popup_size[$report_name];
                $result[$name] =
                array(
                    'linkText' =>       $_cache['linkText'],
                    'report_name' =>    $_cache['report_name'],
                    'objectName' =>     $_cache['objectName'],
                    'primaryObject' =>  $_cache['primaryObject'],
                    'h' =>              $_cache['popupFormHeight'],
                    'w' =>              $_cache['popupFormWidth']
                );
            } else {
                $miss_arr[] = $name;
            }
        }
        if (count($miss_arr)) {
            $sql =
                 "SELECT\n"
                ."  `name`,\n"
                ."  `popupFormHeight`,\n"
                ."  `popupFormWidth`,\n"
                ."  `primaryObject`\n"
                ."FROM\n"
                ."  `report`\n"
                ."WHERE\n"
                ."  `name` IN ('".implode("','", $miss_arr)."')";
  //      z($sql);
            $records = $this->get_records_for_sql($sql);
            foreach ($records as $record) {
                $ObjPrimary = Report::get_ObjPrimary($record['name'], $record['primaryObject']);
                $objectName = $ObjPrimary->_get_object_name().$ObjPrimary->plural(1);
                $record['objectName'] = $objectName;
                $record['linkText'] =   'Edit this '.$objectName;
                Report::$cache_popup_size[$record['name']] = $record;
                $result[$record['name']] =
                array(
                    'linkText' =>       $record['linkText'],
                    'report_name' =>    $record['name'],
                    'objectName' =>     $record['objectName'],
                    'primaryObject' =>  $record['primaryObject'],
                    'h' =>              $record['popupFormHeight'],
                    'w' =>              $record['popupFormWidth']
                );
            }
        }
        return $result;
    }

    // Used for copy and delete for a report
    public function get_report_columns()
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `report_columns`\n"
            ."WHERE\n"
            ."  `reportID` IN(".$this->_get_ID().")";
  //    z($sql);
        return $this->get_records_for_sql($sql);
    }

    public function get_report_icons()
    {
        global $system_vars;
        $sql =
             "SELECT\n"
            ."  `ID`,\n"
            ."  `tab`,\n"
            ."  `seq`,\n"
            ."  `icon`,\n"
            ."  `label`,\n"
            ."  `name`,\n"
            ."  `help`,\n"
            ."  `required_feature`\n"
            ."FROM\n"
            ."  `report`\n"
            ."WHERE\n"
            ."  `icon`!=\"\" AND\n"
            ."  `systemID` IN(1,".SYS_ID.") AND\n"
            ."  (\n"
            .($_SESSION['person']['permMASTERADMIN']   ? "    `permMASTERADMIN`='1' OR\n" : "")
            .($_SESSION['person']['permUSERADMIN']      ? "   `permUSERADMIN`='1' OR\n"    : "")
            .($_SESSION['person']['permSYSADMIN']      ? "    `permSYSADMIN`='1' OR\n"    : "")
            .($_SESSION['person']['permSYSAPPROVER']   ? "    `permSYSAPPROVER`='1' OR\n" : "")
            .($_SESSION['person']['permSYSEDITOR']     ? "    `permSYSEDITOR`='1' OR\n"   : "")
            .($_SESSION['person']['permSYSMEMBER']     ? "    `permSYSMEMBER`='1' OR\n"   : "")
            .($_SESSION['person']['permGROUPVIEWER']   ? "    `permGROUPVIEWER`='1' OR\n"  : "")
            .($_SESSION['person']['permGROUPEDITOR']   ? "    `permGROUPEDITOR`='1' OR\n"  : "")
            ."    0\n"
            ."  )\n"
            ."ORDER BY\n"
            ."  `tab` ASC,"
            ."  `seq` ASC";
  //    z($sql);die;
        $result = $this->get_records_for_sql($sql);
  //    y($result);die;
        if ($_SESSION['person']['permMASTERADMIN']) {
            return $result;
        }
        $features_arr = explode(",", str_replace(", ", ",", $system_vars['features']));
        $filtered = array();
        foreach ($result as $r) {
            $req_met = true;
            if ($r['required_feature']!='') {
                $req_arr = explode(',', $r['required_feature']);
                foreach ($req_arr as $req_arr_item) {
                    if (!in_array(trim($req_arr_item), $features_arr)) {
                        $req_met = false;
                    }
                }
            }
            if ($req_met) {
                $filtered[] = $r;
            }
        }
        return $filtered;

  //    y($result);
        return $result;
    }

    public function get_records(
        $report_record,
        $columnList = false,
        $filterField = false,
        $filterExact = false,
        $filterValue = false,
        $unused_field = 0,
        $limit = -1,
        $offset = 0,
        $debug = false
    ) {
        $where =
        ($filterField!==false  ?
         Report_Report::get_filter($filterField, $filterExact, $filterValue)
         : "");
        $query = $report_record['reportSQL_SYSADMIN'];
        if (isset($_SESSION['person'])) {
            if ($_SESSION['person']['permMASTERADMIN'] && $report_record['permMASTERADMIN']) {
                $query = $report_record['reportSQL_MASTERADMIN'];
            } elseif (
            $_SESSION['person']['permSYSADMIN']    && $report_record['permSYSADMIN'] ||
            $_SESSION['person']['permSYSAPPROVER'] && $report_record['permSYSAPPROVER'] ||
            $_SESSION['person']['permSYSEDITOR']   && $report_record['permSYSEDITOR']
            ) {
                $query = $report_record['reportSQL_SYSADMIN'];
            } elseif (
            $_SESSION['person']['permGROUPEDITOR'] && $report_record['permGROUPEDITOR']
            ) {
                $query = $report_record['reportSQL_GROUPADMIN'];
            } elseif (
            $_SESSION['person']['permCOMMUNITYADMIN'] && $report_record['permCOMMUNITYADMIN']
            ) {
                $query = $report_record['reportSQL_COMMUNITYADMIN'];
            }
        }

        $sql =
         get_sql_constants(
             $query
             .$where
             .($report_record['reportGroupBy']!="" ? "\n GROUP BY\n  ".$report_record['reportGroupBy']."\n" : "")
             .($columnList!==false ? $this->get_sortby($columnList, $report_record['primaryTable']) : "")
             .($limit!=-1 ? "\nLIMIT\n  $offset, $limit" : "")
         );
  //    z($sql);
        if ($debug==1) {
            z($sql);
        }
        $records =   $this->get_records_for_sql($sql);
        if ($records===false) {
            $report_name = $report_record['name'];
            return array();
        }
        return $records;
    }

    public function get_records_count(
        $report_record,
        $filterField,
        $filterExact,
        $filterValue,
        $unused_field = 0,
        $debug = false
    ) {
        $where = ($filterField!==false  ?
             Report_Report::get_filter($filterField, $filterExact, $filterValue)
         :
            ""
        );
        $query = $report_record['reportSQL_SYSADMIN'];
        if (isset($_SESSION['person'])) {
            if ($_SESSION['person']['permMASTERADMIN'] && $report_record['permMASTERADMIN']) {
                $query = $report_record['reportSQL_MASTERADMIN'];
            } elseif (
                $_SESSION['person']['permSYSADMIN']    && $report_record['permSYSADMIN'] ||
                $_SESSION['person']['permSYSAPPROVER'] && $report_record['permSYSAPPROVER'] ||
                $_SESSION['person']['permSYSEDITOR']   && $report_record['permSYSEDITOR']
            ) {
                $query = $report_record['reportSQL_SYSADMIN'];
            } elseif (
                $_SESSION['person']['permGROUPEDITOR']   && $report_record['permGROUPEDITOR']
            ) {
                $query = $report_record['reportSQL_GROUPADMIN'];
            } elseif (
                $_SESSION['person']['permCOMMUNITYADMIN'] && $report_record['permCOMMUNITYADMIN']
            ) {
                $query = $report_record['reportSQL_COMMUNITYADMIN'];
            }
        }
        $pos = (strpos($query, 'FROM #primary')!==false ?
            strpos($query, 'FROM #primary')
         :
            strrpos($query, 'FROM')
        );
        $sql =
            get_sql_constants(
                "SELECT COUNT(*) AS `count` FROM (\n"
                ."SELECT `".$report_record['primaryTable']."`.`ID`\n"
                .substr($query, $pos)
                .$where."\n"
                .($report_record['reportGroupBy']!="" ? "\n GROUP BY\n  ".$report_record['reportGroupBy']."\n" : "")
                .") AS `countSQL`"
            );
  //    z($sql);
        if ($debug) {
            z($sql);
        }
        $record =   $this->get_record_for_sql($sql);
        if ($debug) {
            y($record);
        }
        if (!$record) {
            $report_name = $report_record['name'];
            return 0;
        }
        return $record['count'];
    }

    public static function get_sortby($columnList, $reportPrimaryTable)
    {
        global $sortBy;
        if ($sortBy=="") {
            return "";
        }
        $sortBy_columns = "";
        for ($i=0; $i<count($columnList); $i++) {
            $label = $columnList[$i]['reportLabel'];
            if (
                $label!="" &&
                strpos($label, "Export")==null &&
                strpos($label, "Del")==null &&
                $sortBy == $columnList[$i]['reportField']
            ) {
                $sortBy_columns = $columnList[$i]['reportSortBy_a'];
                break;
            }
            if (
                $label!="" &&
                strpos($label, "Export")==null &&
                strpos($label, "Del")==null &&
                $sortBy == $columnList[$i]['reportField']."_d"
            ) {
                $sortBy_columns = $columnList[$i]['reportSortBy_d'];
                break;
            }
        }
        if ($sortBy_columns=="") {
            return "";
        }
        Report::convert_xml_field_for_sort($sortBy_columns, $reportPrimaryTable);
    //  Y($sortBy_columns);DIE;
        return
         "\nORDER BY\n"
        ."  ".$sortBy_columns;
    }

    public function get_tabs_array($columnList, &$selected_section)
    {
        $tab_arr = array();
        foreach ($columnList as $column) {
            $reportTab =    $column['tab'];
            if (!isset($tab_arr[$reportTab])) {
                if ($column['access']==Report::COLUMN_FULL_ACCESS) {
                    switch (strToLower($column['fieldType'])) {
                        case "button_add_new":
                        case "selected_add_to_group":
                        case "selected_export_excel":
                        case "selected_view_email_addresses":
                        case "checkbox":
                        case "copy":
                        case "delete":
                        case "hidden":
                        case "listtypeid":
                        case "password_set":
                        case "quantity":
                        case "selectid":
                        case "selected_delete":
                        case "selected_export_sql":
                        case "selected_update":
                        // These are not used in forms so ignore
                            break;
                        default:
                            if ($selected_section=="") {
                                if ($reportTab!="0.") {
                                    $selected_section = str_replace(" ", "_", $reportTab);
                                }
                            }
                            $reportTab_bits = explode(".", $reportTab);
                            $reportTab_label = $reportTab_bits[count($reportTab_bits)-1];
                            $reportTab_ID = str_replace(" ", "_", $reportTab);
                            if ($reportTab) {
                                $tab_arr[$reportTab] = array('ID'=>$reportTab_ID,'label'=>$reportTab_label,'width'=>0);
                            }
                            break;
                    }
                }
            }
        }
        if (count($tab_arr)==1) {
  // If there's only one tab, don't bother
            $tab_arr=array();
        }
        return $tab_arr;
    }

    public function get_titles_for_name($report_name)
    {
        if (Report::$cache_titles) {
            $row = Report::$cache_titles;
        } else {
            $sql =
                 "SELECT\n"
                ."  `formTitle`,\n"
                ."  `reportTitle`\n"
                ."FROM\n"
                ."  `report`\n"
                ."WHERE\n"
                ."  `name` = \"$report_name\" AND\n"
                ."  `systemID` IN (1,".SYS_ID.")\n"
                ."ORDER BY\n"
                ."  `systemID` = ".SYS_ID." DESC\n";
    //       z($sql);
            $row = $this->get_record_for_sql($sql);
            Report::$cache_titles = $row;
        }
        return $row;
    }

    public function handle_copy($report_name, $args, &$newID, &$msg, &$msg_tooltip)
    {
        global $system_vars;
        $targetID =     $args['targetID'];
        $targetValue =  $args['targetValue'];
        if (substr($report_name, 0, 7)=='module.') {
            $bits  = explode(".", $report_name);
            if (count($bits)==3) {
                if (!Base::module_test($bits[1])) {
                    $msg='<b>Error</b> The '.$bits[1].' module is not currently installed.';
                    return false;
                }
                $Obj = Base::use_module($bits[1]);
                $Obj->_set_ID($targetID);
                return $Obj->handle_report_copy($newID, $msg, $msg_tooltip, $targetValue);
            }
        }

        $this->_set_ID($this->get_ID_by_name($report_name));
        if ($primaryObject_name = $this->get_field('primaryObject')) {
            $Obj = new $primaryObject_name;
            $Obj->_set_ID($targetID);
            return $Obj->handle_report_copy($newID, $msg, $msg_tooltip, $targetValue);
        }
        $msg = "<b>Error</b>This report doesn't have an associated class to allow copy functionality: ".$report_name;
        return false;
    }

    public function handle_delete($report_name, $args, &$msg)
    {
        global $system_vars;
        $targetID =             $args['targetID'];

        if (substr($report_name, 0, 7)=='module.') {
            $bits  = explode(".", $report_name);
            if (count($bits)==3) {
                if (!Base::module_test($bits[1])) {
                    $msg='<b>Error</b> The '.$bits[1].' module is not currently installed.';
                    return false;
                }
                Base::use_module($bits[1]);
            }
        }

        $this->_set_ID($this->get_ID_by_name($report_name));
        if ($primaryObject_name = $this->get_field('primaryObject')) {
            $Obj = new $primaryObject_name;
            $Obj->_set_ID($targetID);
            return $Obj->handle_report_delete($msg);
        }
        $Obj = new Record($this->get_field('primaryTable'), $targetID);
        $Obj->delete();
        $msg =    status_message(0, true, 'Record', '', 'been deleted.', $targetID);

      /*
      $msg =
        status_message(
        2,true,"<b>\"".$report_name."\"</b> ".$this->_get_object_name(),'',
        "doesn't have an associated class to allow delete functionality.",$this->_get_ID()
        );
      return false;
      */
    }
    public function handle_report_copy(&$newID, &$msg, &$msg_tooltip, $name)
    {
        return parent::try_copy($newID, $msg, $msg_tooltip, $name);
    }

    public function manage_actions()
    {
        return parent::manage_actions('actions_for_report');
    }

    public function manage_columns()
    {
        if (get_var('command')=='report') {
            return draw_auto_report('report_columns_for_report', 1);
        }
        if (!$selectID = get_var('selectID')) {
            return
                 "<h3 style='margin:0.25em'>Columns for ".$this->_get_object_name()."</h3>"
                ."The ".$this->_get_object_name()." must be saved before any entries can be added";
        }
        $this->_set_ID($selectID);
        if ($this->exists()) {
            return
                 "<h3 style='margin:0.25em'>Columns for ".$this->_get_object_name()." \"".$this->get_name()."\"</h3>"
                .draw_auto_report('report_columns_for_report', 1);
        }
        return
             "<h3 style='margin:0.25em'>Columns for ".$this->_get_object_name()." #".$selectID."</h3>"
            ."Sorry - the ".$this->_get_object_name()." appears to have been deleted";
    }

    public function test_feature($test_csv)
    {
        $test_arr = explode(",", str_replace(' ', '', $test_csv));
        $uncached = false;
        foreach ($test_arr as $test) {
            $key = $this->_get_ID()."_".$test;
            if (isset(Report::$cache_feature_array[$key])) {
                if (Report::$cache_feature_array[$key]!==false) {
                    return Report::$cache_feature_array[$key];
                }
            } else {
                $uncached = true;
            }
        }
        if ($uncached === false) {
            return false;  // we HAD all the values we needed in cache, they were just all false
        }
        $test =     "\"".implode("\",\"", $test_arr)."\"";
        $sql =
            "SELECT\n"
            ."  `ID`,\n"
            ."  `fieldType`,\n"
            ."  `group_assign_csv`,\n"
            ."  `permMASTERADMIN`,\n"
            ."  `permUSERADMIN`,\n"
            ."  `permCOMMUNITYADMIN`,\n"
            ."  `permGROUPEDITOR`,\n"
            ."  `permGROUPVIEWER`,\n"
            ."  `permSYSADMIN`,\n"
            ."  `permSYSAPPROVER`,\n"
            ."  `permSYSEDITOR`,\n"
            ."  `permSYSMEMBER`,\n"
            ."  `permSYSLOGON`,\n"
            ."  `permPUBLIC`\n"
            ."FROM\n"
            ."  `report_columns`\n"
            ."WHERE\n"
            ."  `fieldType` IN (".$test.") AND\n"
            ."  `reportID` = ".$this->_get_ID();
  //    z($sql);die;
        $records =     $this->get_records_for_sql($sql);
        $result = false;
        foreach ($records as $record) {
            $key = $this->_get_ID()."_".$record['fieldType'];
            if ($this->is_visible($record)) {
                Report::$cache_feature_array[$key] = $record['ID'];
                $result = $record['ID'];
            } else {
                Report::$cache_feature_array[$key] = false;
            }
        }
      // Now set the ones we didn't get from the SQL lookup to false:
        foreach ($test_arr as $test) {
            $key = $this->_get_ID()."_".$test;
            if (!isset(Report::$cache_feature_array[$key])) {
                Report::$cache_feature_array[$key]=false;
            }
        }
        return $result;
    }

    public function get_version()
    {
        return VERSION_REPORT;
    }
}
