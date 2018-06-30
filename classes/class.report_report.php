<?php
define("VERSION_REPORT_REPORT", "1.0.29");

/*
Version History:
  1.0.29 (2015-01-10)
    1) Now PSR-2 Compliant

  (Older version history in class.report_report.txt)
*/

class Report_Report extends Report
{
    public function do_commands()
    {
        if (!isset($_REQUEST['command'])) {
            print "?";
            die;
        }
        switch ($_REQUEST['command']){
            case 'report':
                $ID = $this->get_ID_by_name($_REQUEST['report_name']);
                $this->_set_ID($ID);
                $componentID = $this->get_field('reportComponentID');
                if ($componentID!= "1") {
                    print convert_safe_to_php(draw_component($componentID));
                    die;
                }
                $json_mode = (isset($_REQUEST['ajax_popup_url']) && $_REQUEST['ajax_popup_url'] ?
                    $_REQUEST['ajax_popup_url']
                 :
                    false
                );
                $result = $this->draw($_REQUEST['report_name'], $_REQUEST['toolbar'], $json_mode);
                if ($json_mode) {
                    print convert_safe_to_php($result['html']);
                } else {
                    print convert_safe_to_php($result);
                }
                die;
            break;
        }
    }

    public function draw($report_name, $toolbar, $ajax_mode = false)
    {
      // Toolbar settings and operation:
      // -----------------------------------------------
      // Function                    | 0  1  2  3  4  5
      // -----------------------------------------------
      // Report Edit  (Master Admin) |    Y  Y
      // Component TB (Master Admin) |    Y  Y
      // Selected TB                 |    Y  Y        Y
      // Paging TB                   |    Y           Y
      // Filter TB                   |    Y
      // Apply filter (if set)       |    Y     Y
      // Apply limits (if set)       |    Y     Y
      // Process actions and quit    |              Y
      // -----------------------------------------------
      // Mode 5 is used in community dashboard
      //
        global $db, $anchor, $submode, $sortBy, $limit, $offset, $print;
        global $filterField,$filterFieldID,$filterValue,$filterExact;
        global $targetID, $targetField, $targetFieldID, $targetReportID, $targetValue, $selectID;
        global $db, $system_vars, $MM, $YYYY;
        @set_time_limit(600);    // Extend maximum execution time to 10 mins (if permitted)
        $isMASTERADMIN =        get_person_permission("MASTERADMIN");
        $isUSERADMIN =            get_person_permission("USERADMIN");
        $isCOMMUNITYADMIN =        get_person_permission("COMMUNITYADMIN");
        $isSYSADMIN =            get_person_permission("SYSADMIN");
        $isSYSAPPROVER =        get_person_permission("SYSAPPROVER");
        $isSYSEDITOR =            get_person_permission("SYSEDITOR");
        $isSYSMEMBER =            get_person_permission("SYSMEMBER");
        $isSYSLOGON =            get_person_permission("SYSLOGON");
        $isGROUPEDITOR =        get_person_permission("GROUPEDITOR");
        $isGROUPVIEWER =        get_person_permission("GROUPVIEWER");
        $isPUBLIC =                get_person_permission("PUBLIC");
  //    y($_POST);DIE;
/*
        print
             "<pre>\$targetReportID=$targetReportID, \$targetField=$targetField, \$targetFieldID=$targetFieldID, "
            ."\$targetValue=$targetValue, \$filterField=$filterField, \$filterExact=$filterExact, "
            ."\$filterValue=$filterValue, \$toolbar=$toolbar</pre>\n";
*/
        mem(__FUNCTION__."()-1");
        $this->_debug =     DEBUG_REPORT;
        $this->_toolbar =   $toolbar;
        if ($this->_toolbar==1 || $this->_toolbar==2 || $this->_toolbar==5) {
            $this->test_feature(Report::REPORT_FEATURES.',button_add_new'); // Precache features available for toolbars
        }
  //    $this->_debug = 1;
        if ($this->_toolbar==0 || $this->_toolbar==2) {
  // preserve vars if no toolbar - used for pages with multiple reports
            $old_limit =  $limit;
            $limit = -1;
            $old_offset = $offset;
            $offset = 0;
            $old_filterField =  $filterField;
            $filterField = "";
            $old_filterValue =  $filterValue;
            $filterValue = "";
            $old_filterExact =  $filterExact;
            $filterExact = "";
        }
        $newID =          "";
        $msg =            "";
        $msg_tooltip =    "";
        $now =    get_timestamp();
        $ObjReportColumn =  new Report_Column;
/*
        print
             "\$targetReportID=$targetReportID, \$targetValue=$targetValue, \$filterField=$filterField, "
            ."\$filterExact=$filterExact, \$filterValue=$filterValue<br />\n";
*/
        $filterField_sql = "";
        if ($filterField!='') {
            $ObjReportColumn->_set_ID($filterField);
            $filter_column_record = $ObjReportColumn->get_record();
            if ($filter_column_record['reportID'] == $this->_get_ID()) {
                $filterField_sql = $filter_column_record['reportFilter'];
            }
        }
        $filterValue = addslashes(trim($filterValue));
        $filterValueDefault = "(Search for ...)";
        if ($filterValue===$filterValueDefault) {
            $filterValue="";
        }
        if (empty($limit)) {
            $limit = 10;

        } else {
            $limit = (int) $limit;
        }
        if (empty($offset)) {
            $offset = 0;

        } else {
            $offset = (int) $offset;
        }
        if ($offset<0) {
            $offset=0;
        }
         // This code to preselect correct report name when adding a new report column:
        $selectedReportID="0";
        if ($filterField_sql=="`report`.`name`" && $filterExact=='1') {
            $selectedReportID = $this->get_ID_by_name($filterValue);
        }
/*
        print
             "\$targetReportID=$targetReportID, \$targetValue=$targetValue, \$filterFieldID=$filterFieldID, "
            ."\$filterExact=$filterExact, \$filterValue=$filterValue<br />\n";
*/
        mem(__FUNCTION__."()-2");
        if ($this->_debug==1) {
            print
             "<pre>\n"
            ."\$report_name =     $report_name\n"
            ."\$reportID =        ".$this->_get_ID()."\n"
            ."\$targetField =     $targetField\n"
            ."\$targetFieldID =   $targetFieldID\n"
            ."\$targetID =        $targetID\n"
            ."\$targetReportID =  $targetReportID\n"
            ."\$targetValue =     $targetValue\n"
            ."\$sortBy =          $sortBy\n"
            ."\$limit =           $limit\n"
            ."\$offset =          $offset\n"
            ."\$filterField_sql = $filterField_sql\n"
            ."\$filterField =     $filterField\n"
            ."\$filterExact =     $filterExact\n"
            ."\$filterValue =     $filterValue\n"
            ."\$selectID =        $selectID\n"
            ."\$submode =         $submode\n"
            ."\$toolbar =         ".$this->_toolbar."\n"
            ."\$ajax_mode =       $ajax_mode\n"
            ."</pre>";
    //      die;
        }
        if (!$report_row = $this->get_record()) {
            if ($ajax_mode) {
                return array('html'=>'','js'=>'');
            }
            return "";
        }
        if (
            !$isMASTERADMIN &&
            $report_row['required_feature'] &&
            !SYSTEM::has_feature($report_row['required_feature'])
        ) {
            if ($ajax_mode) {
                return array('html'=>'','js'=>'');
            }
            return
                 "<p><b>Error:</b> Required feature <b>".$report_row['required_feature']."</b> must be enabled"
                ." in order to use report <b>".$report_row['name']."</b></p>";
        }
        mem(__FUNCTION__."()-3");
  //    y($report_row);
        $popupFormHeight =                $report_row['popupFormHeight'];
        $popupFormWidth =                $report_row['popupFormWidth'];
        $thisReportID =                    $report_row['ID'];
        $reportComponentID =            $report_row['reportComponentID'];
        $reportGroupBy =                $report_row['reportGroupBy'];
        $reportSortReverse =            false;
        $reportPrimaryTable =           $report_row['primaryTable'];
        $reportPrimaryObjectName =      $report_row['primaryObject'];
        $reportTitle =                  $report_row['reportTitle'];
        $help =                         $report_row['help'];
        $reportMembersGlobalEditors =   $report_row['reportMembersGlobalEditors'];
        $componentID =                  $report_row['reportComponentID'];
        $ObjPrimary =                   $this->get_ObjPrimary($report_name, $reportPrimaryObjectName);
        mem(__FUNCTION__."()-4");
        $record_type =                  $ObjPrimary->_get_object_name();
        mem(__FUNCTION__."()-5");
        if ($isMASTERADMIN) {
            switch ($submode){
                case "column_delete":
                    $Obj = new Report_Column($targetID);
                    if ($thisReportID==$Obj->get_field('reportID')) {
                        $Obj->delete();
                        $msg = status_message(0, true, 'report_column', '', 'been deleted.', $targetID);
                    }
                    break;
            }
        }
        $columnList =    $this->get_columns();
        mem(__FUNCTION__."()-6");
      // These operations do NOT have field level permissions:
        if ($thisReportID==$targetReportID) {
            switch ($submode) {
                case "copy_report":
                    if (!$isMASTERADMIN) {
                        $msg = "<b>Error:</b> You don't have permission to do that.";
                        break;
                    }
                    if ($targetValue=="") {
                        $msg = "<b>Error:</b> Report must have a name.";
                        break;
                    }
                    $Obj = new Report($targetReportID);
                    $targetSystemID = $Obj->get_field('systemID');
                    if ($Obj->exists_named($targetValue, $targetSystemID)) {
                        $msg = "<b>Error:</b> a report named ".$targetValue." already exists.";
                        break;
                    }
                    $newID = $Obj->copy($targetValue);
                    if ($newID) {
                        $ID = $newID;
                        $msg = status_message(
                            0,
                            true,
                            'Report',
                            '',
                            "been copied to "
                            ."<a href=\"".BASE_PATH."report/report?"
                            ."filterField=82&amp;filterExact=1&amp;filterValue="
                            .$targetValue."\">"
                            .$targetValue."</a>.",
                            $newID
                        );
                    }
                    break;
                case "filter_add":
                    $Obj_Filter = new Report_Filter;
                    $Obj_Filter->filter_add($targetReportID, $targetValue, $filterField, $filterExact, $filterValue);
                    break;
                case "filter_assign_global":
                    if ($isMASTERADMIN) {
                        $Obj_Filter = new Report_Filter($targetID);
                        $Obj_Filter->assign($targetReportID, 'global', 0);
                    } else {
                        $msg = "<b>Error:</b> You don't have permission to do that.";
                    }
                    break;
                case "filter_assign_local":
                    if ($isMASTERADMIN || $isSYSADMIN) {
                        $Obj_Filter = new Report_Filter($targetID);
                        $Obj_Filter->assign($targetReportID, 'system', SYS_ID);
                    } else {
                        $msg = "<b>Error:</b> You don't have permission to do that.";
                    }
                    break;
                case "filter_assign_me":
                    if ($isMASTERADMIN || $isSYSADMIN) {
                        $Obj_Filter = new Report_Filter($targetID);
                        $Obj_Filter->assign($targetReportID, 'person', get_userID());
                    } else {
                        $msg = "<b>Error:</b> You don't have permission to do that.";
                    }
                    break;
                case "filter_delete":
                    $Obj_Filter = new Report_Filter($targetID);
                    $filter = $Obj_Filter->get_record();
                    $canDo = false;
                    if ($isMASTERADMIN && $filter['destinationType']=='global') {
                        $canDo = true;
                    } elseif (
                        ($isMASTERADMIN || $isSYSADMIN) &&
                        $filter['destinationType']=='system' &&
                        $filter['destinationID']==SYS_ID
                    ) {
                        $canDo = true;
                    } elseif ($filter['destinationType']=='person' && $filter['destinationID']==get_userID()) {
                        $canDo = true;
                    }
                    if ($canDo) {
                        $Obj_Filter->delete();
                    } else {
                        $msg = "<b>Error:</b> You don't have permission to do that.";
                    }
                    break;
                case "filter_rename":
                    $Obj_Filter = new Report_Filter($targetID);
                    $filter = $Obj_Filter->get_record();
                    $canDo = false;
                    if ($isMASTERADMIN && $filter['destinationType']=='global') {
                        $canDo = true;
                    } elseif (
                        ($isMASTERADMIN || $isSYSADMIN) &&
                        $filter['destinationType']=='system' &&
                        $filter['destinationID']==SYS_ID
                    ) {
                        $canDo = true;
                    } elseif ($filter['destinationType']=='person' && $filter['destinationID']==get_userID()) {
                        $canDo = true;
                    }
                    if ($canDo) {
                        $Obj_Filter->set_field('label', $targetValue);
                    } else {
                        $msg = "<b>Error:</b> You don't have permission to do that.";
                    }
                    break;
                case "filter_seq":
                    $Obj_Filter = new Report_Filter($targetID);
                    $filter = $Obj_Filter->get_record();
                    $canDo = false;
                    if ($isMASTERADMIN && $filter['destinationType']=='global') {
                        $canDo = true;
                    } elseif (
                        ($isMASTERADMIN || $isSYSADMIN) &&
                        $filter['destinationType']=='system' &&
                        $filter['destinationID']==SYS_ID
                    ) {
                        $canDo = true;
                    } elseif ($filter['destinationType']=='person' && $filter['destinationID']==get_userID()) {
                        $canDo = true;
                    }
                    if ($canDo) {
                        $Obj_Filter->set_seq($targetValue);
                    } else {
                        $msg = "<b>Error:</b> You don't have permission to do that.";
                    }
                    break;
            }
        }
        mem(__FUNCTION__."()-7");
      // Test for field-level permissions before doing anything with records
        $isEDITOR = false;
        if ($thisReportID==$targetReportID && $submode!='' && $targetFieldID!='') {
            foreach ($columnList as $column) {
                if ($column['ID']==$targetFieldID) {
          //        y($column);die;
                    $targetField = $column['formField'];
                    if (
                    ($column['access']==1) &&
                    (
                    ($isPUBLIC &&         $column['permPUBLIC'] =='2') ||
                    ($isGROUPVIEWER &&    $column['permGROUPVIEWER'] =='2') ||
                    ($isGROUPEDITOR &&    $column['permGROUPEDITOR'] =='2') ||
                    ($isSYSLOGON &&       $column['permSYSLOGON'] =='2') ||
                    ($isSYSMEMBER &&      $column['permSYSMEMBER'] =='2') ||
                    ($isSYSEDITOR &&      $column['permSYSEDITOR'] =='2') ||
                    ($isSYSAPPROVER &&    $column['permSYSAPPROVER'] =='2') ||
                    ($isSYSADMIN &&       $column['permSYSADMIN'] =='2') ||
                    ($isUSERADMIN &&      $column['permUSERADMIN'] =='2') ||
                    ($isCOMMUNITYADMIN && $column['permCOMMUNITYADMIN'] =='2') ||
                    ($isMASTERADMIN &&    $column['permMASTERADMIN'] =='2')
                    )
                    ) {
                        $isEDITOR = true;
                    }
                }
            }
        }
        mem(__FUNCTION__."()-8");
    //  print "$thisReportID==$targetReportID ";
        if ($thisReportID==$targetReportID && $isEDITOR) {
            $args =
            array(
            'submode' =>        $submode,
            'report_name' =>    $report_name,
            'reportPrimaryTable' => $reportPrimaryTable,
            'targetID' =>       $targetID,
            'targetValue' =>    $targetValue
            );
        // ***************************
      // * Pre Operation Actions   *
        // ***************************
            switch ($submode) {
                case 'delete':
                    $this->actions_execute(
                        'report_delete_pre',
                        $reportPrimaryTable,
                        $reportPrimaryObjectName,
                        $targetID
                    );
                    break;
                case "set":
                    $data = array($targetField => $targetValue);
                    $this->actions_execute(
                        'report_update_pre',
                        $reportPrimaryTable,
                        $reportPrimaryObjectName,
                        $targetID,
                        $data
                    );
                    break;
            }
            $done = false;
        // (Handler extender code begins)
            $ObjHandler = new Handler('draw_auto_report');
            $args =
            array(
            'submode' =>        $submode,
            'report_name' =>    $report_name,
            'reportPrimaryTable' => $reportPrimaryTable,
            'targetID' =>       $targetID,
            'targetValue' =>    $targetValue
            );
            $handler = $ObjHandler->handle($args);
            if ($handler['handled']) {
                $done =           true;
                $msg =            $handler['msg'];
                $msg_tooltip =    $handler['msg_tooltip'];
                $newID =          (isset($handler['newID']) ? $handler['newID'] : false);
            }
        // (Handler extender code ends)
            if (!$done) {
                switch ($submode) {
                    case "copy":
                        if ($this->handle_copy($report_name, $args, $newID, $msg, $msg_tooltip)) {
                            $this->actions_execute(
                                'report_copy_post',
                                $reportPrimaryTable,
                                $reportPrimaryObjectName,
                                $newID
                            );
                        } else {
                            $targetID = false;
                        }
                        break;
                    case "delete":
                        $this->handle_delete($report_name, $args, $msg);
                        $targetID=false;
                        break;
                    case "empty":
                        switch ($report_name) {
                            case "groups":
                                $Obj = new Group($targetID);
                                $members = $Obj->member_count();
                                $Obj->delete_members();
                                $msg = status_message(
                                    0,
                                    true,
                                    $record_type,
                                    'had all '.$members.' members',
                                    'deleted.',
                                    $targetID
                                );
                                break;
                        }
                        break;
                    case "process_order":
                        $Obj = new Order($targetID);
                      // attempt process and show how many actually WERE procecessed:
                        $targetID = $Obj->actions_process_product_pay();
                        if (strlen($targetID)>0) {
                            $msg = status_message(
                                0,
                                true,
                                $record_type,
                                '',
                                "been processed.",
                                $targetID
                            );
                        } else {
                            $msg = status_message(
                                1,
                                true,
                                $record_type,
                                '',
                                "been processed as none were previously unprocessed.",
                                $targetID
                            );
                        }
                        break;
                    case "set":
                        if ($targetField!="") {
                            $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                            $data =       array($targetField=>$targetValue);
                            $ObjRecord->update($data);
                            $msg =         status_message(0, true, $record_type, '', 'been updated.', $targetID);
                            $msg_tooltip = status_message(0, false, $record_type, '', 'been updated.', $targetID);
                            $this->actions_execute(
                                'report_update_post',
                                $reportPrimaryTable,
                                $reportPrimaryObjectName,
                                $targetID,
                                $data
                            );
                        }
                        break;
                    case "set_as_approved":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('approved'=>'approved');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked as approved.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked as approved.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_as_hidden":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('approved'=>'hidden');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been hidden.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been hidden.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_as_attended":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('attender_attended'=>'1');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been shown as attended.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been shown as attended.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_as_member":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('permSYSMEMBER'=>'1');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been set to full member.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been set to full member.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_as_spam":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('approved'=>'spam');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked and reported as spam.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked and reported as spam.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_as_unapproved":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('approved'=>'pending');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked as pending approval.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked as pending approval.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_email_opt_in":
                        $targetID_arr = explode(',', $targetID);
                        foreach ($targetID_arr as $ID) {
                            $ObjRecord =  new Record($reportPrimaryTable, $ID);
                            $old_log =    $ObjRecord->get_field('email_subscription_log');
                            $new_log =
                                  get_timestamp()
                                 .' | '
                                 .pad((isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "No remote IP"), 16)
                                 .' | '
                                 .pad(get_userPUsername(), 25)
                                 .' | Reason: '
                                 .$targetValue
                                 .($old_log ? "\r\n".$old_log : "");
                            $data = array(
                                'permEMAILOPTIN' =>         '1',
                                'permEMAILOPTOUT' =>        '0',
                                'email_subscription_log' => $new_log
                            );
                            $ObjRecord->update($data);
                        }
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked as opting in for emails to this group.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked as opting in for emails to this group.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_email_opt_out":
                        $targetID_arr = explode(',', $targetID);
                        foreach ($targetID_arr as $ID) {
                            $ObjRecord =  new Record($reportPrimaryTable, $ID);
                            $old_log =    $ObjRecord->get_field('email_subscription_log');
                            $new_log =
                            get_timestamp().' | '
                             .pad((isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "No remote IP"), 16)
                             .' | '
                             .pad(get_userPUsername(), 25).' | Reason: '
                             .$targetValue
                             .($old_log ? "\r\n".$old_log : "");
                            $data = array(
                                'permEMAILOPTIN'=>'0',
                                'permEMAILOPTOUT'=>'1',
                                'email_subscription_log'=>$new_log
                            );
                            $ObjRecord->update($data);
                        }
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked as opting out of emails to this group.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked as opting out of emails to this group.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_important_off":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('important'=>'0');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked as of normal importance.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked as of normal importance.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_important_on":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('important'=>'1');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked as of high importance.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked as of high importance.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                    case "set_password":
                        if ($targetValue!="") {
                            $PPassword = encrypt(strToLower($targetValue));
                            $Obj = new Person($targetID);
                            $Obj->set_field('PPassword', $PPassword);
                            $PUsername = $Obj->get_field('PUsername');
                            $msg = status_message(
                                0,
                                true,
                                'Password',
                                '',
                                'been updated for '.$PUsername.' (Passwords are <b>not</b> case sensitive.)',
                                $targetID
                            );
                            $msg_tooltip = status_message(
                                0,
                                false,
                                'Password',
                                '',
                                'been updated for '.$PUsername,
                                $targetID
                            );
                        }
                        break;
                    case "set_process_maps":
                        $ObjRecord =  new Record($reportPrimaryTable, $targetID);
                        $data =       array('process_maps'=>'1');
                        $ObjRecord->update($data);
                        $msg = status_message(
                            0,
                            true,
                            $record_type,
                            '',
                            'been marked for map processing.',
                            $targetID
                        );
                        $msg_tooltip = status_message(
                            0,
                            false,
                            $record_type,
                            '',
                            'been marked for map processing.',
                            $targetID
                        );
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                }
              // ***************************
              // * Post Operation Actions  *
              // ***************************
                switch ($submode) {
                    case 'delete':
                        $this->actions_execute(
                            'report_delete_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID
                        );
                        break;
                    case "set":
                        $data = array($targetField => $targetValue);
                        $this->actions_execute(
                            'report_update_post',
                            $reportPrimaryTable,
                            $reportPrimaryObjectName,
                            $targetID,
                            $data
                        );
                        break;
                }
            }
        }
        mem(__FUNCTION__."()-9");
      // Quit now if toolbar mode=4
        if ($this->_toolbar==4) {
            if ($ajax_mode) {
                return array('html'=>'','js'=>'');
            }
            return "";
        }
        $sortBy = Report::get_and_set_sortOrder($report_row, $columnList, $sortBy);
        mem(__FUNCTION__."()-10");
        Report::convert_xml_field_for_filter($filterField_sql, $reportPrimaryTable);
        $record_count =
        $this->get_records_count(
            $report_row,
            $filterField_sql,
            $filterExact,
            $filterValue,
            0,
            $this->_debug
        );
        mem(__FUNCTION__."()-11");
        if ($record_count>1000 && $limit==-1) {
            $limit = 1000;
            $msg =
                "<b>Information:</b> In order to preserve memory, the number of records shown has been limited to "
                .$limit;
        }
        if ($offset>$record_count) {
            $offset = 0;
        }
        $records =
        $this->get_records(
            $report_row,
            $columnList,
            $filterField_sql,
            $filterExact,
            $filterValue,
            0,
            $limit,
            $offset,
            $this->_debug
        );
        mem(__FUNCTION__."()-12");
        if ($this->_toolbar==1 || $this->_toolbar==2) {
            $this->test_feature(Report::REPORT_FEATURES.',button_add_new');
            $Obj_HTML = new HTML;
            $args = array();
            $args['report'] =
            array(
            'report_name' =>      $report_name,
            'reportID' =>         $this->_get_ID(),
            'selectedReportID' => $selectedReportID,
            'record_count' =>     $record_count,
            'toolbar' =>          $this->_toolbar,
            'help' =>             $help,
            'ajax_mode' =>        $ajax_mode
            );
            $args['with_selected'] =
            array(
            'report_name' =>      $report_name,
            'reportID' =>         $this->_get_ID(),
            'record_count' =>     $record_count,
            'toolbar' =>          $this->_toolbar,
            'ajax_mode' =>        $ajax_mode
            );
            $args['component'] =
            array(
            'componentID' =>      $componentID
            );

            $_content = $Obj_HTML->draw_toolbar('with_selected', $args['with_selected']);
            $this->_html.=
            $Obj_HTML->draw_toolbar('report', $args['report'])
            .$Obj_HTML->draw_toolbar('component', $args['component'])
            .($ajax_mode && isset($_content['html']) ? $_content['html'] : $_content);
            if ($this->_html) {
                $this->_html.=  "<br class='clear' />";
            }
            if ($ajax_mode) {
                $this->_js.=$_content['js'];
            }
        }
        if ($this->_toolbar==5) {
          // Mode 5 is used in community dashboard
            $Obj_HTML = new HTML;
            $args = array();
            $args['with_selected'] =
            array(
              'report_name' =>  $report_name,
              'reportID' =>     $this->_get_ID(),
              'record_count' => $record_count,
              'toolbar' =>      $this->_toolbar,
              'ajax_mode' =>    $ajax_mode
            );
            $_content =
                $Obj_HTML->draw_toolbar('with_selected', $args['with_selected']);
            $this->_html.= $this->draw_toolbar_paging(
                $record_count,
                $limit,
                $offset,
                1,
                1,
                $this->_get_ID(),
                $report_name,
                $this->_toolbar,
                '',
                $ajax_mode
            );
            $this->_html.=
                 ($ajax_mode ? $_content['html'] : $_content)
                ."<br class='clr_b' />";
            if ($ajax_mode) {
                $this->_js.=$_content['js'];
            }
        }
        mem(__FUNCTION__."()-13");
        if ($this->_toolbar==1) {
            $Obj_filter =   new Report_Filter;
            $presets =      $Obj_filter->get_filter_buttons_for_report($this->_get_ID(), $report_name);
            $this->_html.= $this->draw_toolbar_paging(
                $record_count,
                $limit,
                $offset,
                1,
                1,
                $this->_get_ID(),
                $report_name,
                $this->_toolbar,
                '',
                $ajax_mode
            );
            $this->_html.=
                 "<br class='clr_b' />"
                .$this->draw_toolbar_filter(
                    $columnList,
                    $filterExact,
                    $filterField,
                    $filterValue,
                    $filterValueDefault,
                    $this->_get_ID(),
                    $report_name,
                    $this->_toolbar,
                    $ajax_mode
                )
                ."<br class='clr_b' />"
                .($presets ? $presets."<br class='clr_b' />" : "");
        }
        mem(__FUNCTION__."()-14");
        if ($submode=='show_addresses' && $thisReportID==$targetReportID) {
            $email_addresses = $this->get_email_addresses();
            if (count($email_addresses)==0) {
                $email_title = "No email addresses to show";
            } elseif (count($email_addresses)==1 && substr($email_addresses[0], 0, 6)=='Error:') {
                $email_title = "Error:";
                $email_addresses[0] = substr($email_addresses[0], 6);
            } elseif (count($email_addresses)==1) {
                $email_title =
                    "Viewing the only email address:";
            } else {
                $email_title =
                    "Viewing ".($targetID ? "selected" : "all")." email addresses (".count($email_addresses).")";
            }
            $this->_html.=
                 "<h1 style='margin:0px;'>".$email_title."</h1>\n"
                ."<text"."area name='show_addresses' rows='5'  cols='80' style='width: 640px'>\n"
                .implode("", $email_addresses)
                ."</text"."area>\n";
        }
        mem(__FUNCTION__."()-15");
        if ($msg!="") {
            if ($msg_tooltip=="") {
                $this->_html.= "<div style='padding:2px;clear:both'><a id='msg'></a>".$msg."</div>";
                $anchor = "msg";
            } else {
                $this->_html.= "<div style='padding:2px;clear:both'>".$msg."</div>";
            }
        }
        $this->_html.= draw_form_field(
            'sortBy_'.$this->_get_ID(),
            ($thisReportID==$targetReportID ? $sortBy : ""),
            'hidden'
        );
        $this->_html.=
             "\n"
            ."<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" class=\"report\" summary=\"".$reportTitle."\">\n"
            ."  <thead>\n"
            ."    <tr>\n"
            .$this->get_report_heads(
                $columnList,
                false,
                $report_name,
                $this->_toolbar,
                $ajax_mode
            )
            ."    </tr>\n"
            ."  </thead>\n";
    //  print "reportMembersGlobalEditors - $reportMembersGlobalEditors";die();
        mem(__FUNCTION__."()-16");
        $mayCancel  = false;
        if (count($records)) {
            $_targetID = $targetID;   // Keeps $targetID value safe where multiple reports are shown at once
            foreach ($records as $row) {
                $systemID = (array_key_exists('systemID', $row) ? $row['systemID'] : SYS_ID);
                $isSys = $systemID==SYS_ID;
                $writeable = ($isMASTERADMIN || $isSys || $reportMembersGlobalEditors);
                $readOnly = !$writeable;
                $this->_html.=
                 "      <tr "
                .($readOnly ?
                  " style='background-color: #e8e8e8;' title='Global Data - you cannot edit this record'" : "")
                .(isset($row['ID']) && $row['ID']==$selectID ?
                  " style='background-color: #ffffa0;' title='(Selected)'" : "")
                .(isset($row['ID']) && $row['ID']==$newID ?
                  " style='background-color: #FFE2CD;' title='This is the copy'" : "")
                .(isset($row['ID']) && in_array($row['ID'], explode(",", $targetID)) ?
                 " style='background-color: #e0ffe0;' title='"
                 .($msg_tooltip!="" ? $msg_tooltip : "SUCCESS: This record has been updated")
                 ."'\n" : "")
                .(isset($row['ID']) && !in_array($row['ID'], explode(",", $targetID)) && $anchor =="row_".$row['ID'] ?
                 " style='background-color: #e0ffe0;' title='"
                 .($msg_tooltip!="" ? $msg_tooltip : "SUCCESS: This record has been updated")
                 ."'\n" : "")
                .">\n"
                .(isset($row['ID']) ? "<td style='display: none;'><a id='row_".$row['ID']."'></a></td>\n" : "");
                $this->xmlfields_decode($row);
      //        y($row);
                foreach ($columnList as $column) {
                    if ($column['access']==1 && $column['visible']==1) {
                        $this->_html.=
                        $ObjReportColumn->draw_report_field(
                            $column,
                            $row,
                            $popupFormHeight,
                            $popupFormWidth,
                            $isEDITOR,
                            $report_name,
                            $_targetID,
                            $mayCancel,
                            $reportMembersGlobalEditors,
                            $ajax_mode,
                            $ObjPrimary
                        );
                    }
                }
                $this->_html.= "      </tr>\n";
                mem(__FUNCTION__."()-17");
            }
        } else {
            $cols = 0;
            foreach ($columnList as $column) {
                if (
                    ($column['reportLabel']!="" || $column['fieldType']=='checkbox') &&
                    $ObjReportColumn->is_visible($column)
                ) {
                    $cols++;
                }
            }
            $this->_html.=
            "      <tr>\n"
            ."        <td colspan='".$cols."'>&nbsp;<i>--- No records ---</i></td>\n"
            ."      </tr>\n";
        }
        $this->_html.= "    </table>";
        if ($this->_toolbar==1 && $limit!=-1 && $record_count>$limit) {
            $this->_html.= $this->draw_toolbar_paging(
                $record_count,
                $limit,
                $offset,
                true,
                true,
                $this->_get_ID(),
                $report_name,
                $this->_toolbar,
                true,
                $ajax_mode
            );
            $this->_html.= "<br class='clr_b' />";
        }
        if ($this->_toolbar==0 || $this->_toolbar==2) {
            $filterField =  $old_filterField;
            $filterValue =  $old_filterValue;
            $filterExact =  $old_filterExact;
            $offset = $old_offset;
            $limit =  $old_limit;
        }
        if ($ajax_mode) {
            return array('html'=>$this->_html,'js'=>$this->_js);
        }
        Page::push_content('javascript', $this->_js);
        return $this->_html;
    }

    public function draw_by_name($report_name)
    {
        $this->_set_ID($this->get_ID_by_name($report_name));
        if (!$this->load()) {
            $this->do_tracking("404");
            return draw_html_error_404();
        }
        if (!$this->is_visible($this->record)) {
            $this->do_tracking("403");
            if (get_userID()) {
                return draw_html_error_403();
            }
            header("Location: ".BASE_PATH."signin");
            return;
        }
        $this->do_tracking("200");
        $componentID = $this->record['reportComponentID'];
        if ($componentID!= "1") {
            return draw_component($componentID);
        }
        $toolbar = get_var('toolbar', 1);
        return draw_auto_report($report_name, $toolbar);
    }

    public function draw_toolbar_filter(
        $columnList,
        $filterExact,
        $filterField,
        $filterValue,
        $filterValueDefault,
        $reportID = false,
        $report_name = false,
        $toolbar = false,
        $ajax_mode = false
    ) {
        global $page_vars;
        $page_url =         BASE_PATH.trim($page_vars['path'], '/');
        $filters = false;
        foreach ($columnList as $column) {
            if ($column['access'] && $column['reportFilterLabel']!="" && $column['reportFilter']) {
                $filters = true;
                break;
            }
        }
        $report_match_modes = Report::get_match_modes();
        $out =
         "<table class='admin_toolbartable' summary='Report Filter Toolbar'>\n"
        ."  <tr>\n"
        ."    <td>"
        ."<img class='b' src='".BASE_PATH."img/sysimg/icon_toolbar_end_left.gif'"
        ." style='height:16px;width:6px;padding-top:2px;padding-bottom:2px;' alt='|' />"
        ."</td>\n"
        ."    <td><span class='va_b' style='font-size:10pt;font-weight:bold;'>&nbsp;Filter&nbsp;</span></td>\n"
      // FilterField:
        ."    <td><select id='filterField_".$reportID."' class='formField' "
        .(!$filters ? ' disabled' : '')
        ." onchange=\"filterbar_filterField_onchange('".$reportID."')\">\n"
        ."    <option value=\"\" style='background:#f0f0f0;'>(No Filter Field)</option>\n";
        foreach ($columnList as $column) {
            if ($column['access']==1 && $column['reportFilterLabel']!="" && $column['reportFilter']) {
                $out.=
                 "    <option"
                ." value=\"".$column['ID']."\""
                .($filterField==$column['ID'] ? " selected='selected'" : "")
                .">".$column['reportFilterLabel']."</option>\n";
            }
        }
        $out.=
         "</select>\n"
        ."</td>\n"
     // FilterExact
        ."    <td><select id='filterExact_".$reportID."' class='formField'".(!$filters ? " disabled='disabled'" : '')
        ." onchange=\"filterbar_filterExact_onchange('".$reportID."')\">\n";
        foreach ($report_match_modes as $rmm) {
            $out.=
             "      <option value=\"".$rmm['value']."\""
            .($filterExact==$rmm['value'] ?   " selected='selected'" : "")
            ." style='color: #".$rmm['color_text']."; background-color: #".$rmm['color_background'].";'"
            .">".$rmm['textEnglish']."</option>\n";
        }
        $out.=
         "</select></td>\n"
        ."    <td>"
        ."<input type='text' id='filterValue_".$reportID."' class='formField' "
        ."style=\"".(stripslashes($filterValue)=='' ? "color: #0000ff;" : "")
        ."height: 15px;width:100px;\""
        ." value=\""
        .(stripslashes($filterValue)!="" ? stripslashes($filterValue) : $filterValueDefault)
        ."\"\n"
        ." onclick=\"filterbar_value_onclick(event,'".$reportID."','".$filterValueDefault."')\"\n"
        ." onblur=\"filterbar_value_onblur(event,'".$reportID."','".$filterValueDefault."')\"\n"
        ." onkeypress=\"filterbar_value_onkeypress(event,'".$reportID."','".$report_name."','".$toolbar."')\"\n"
        ." size='20' title='Enter text or value here'".(!$filters ? ' disabled' : '')."/></td>"
        ."    <td><a class='info' href='#' onclick='return false' title=\""
        ."For [b]Yellow[/b] filter modes you can specify:\\n"
        ."  YESTERDAY      TODAY             TOMORROW\\n"
        ."  LAST_WEEK      THIS_WEEK     NEXT_WEEK\\n"
        ."  LAST_MONTH   THIS_MONTH   NEXT_MONTH\\n"
        ."  LAST_YEAR      THIS_YEAR       NEXT_YEAR\\n"
        ."\\n"
        ."[b]Red[/b] filter modes use comma-delimited lists\\n"
        ."of items that may be matched.\">"
        ."<img class='b' src=\"".BASE_PATH."img/sysimg/icon_info.gif\""
        ." style='border:none' alt='' /></a></td>\n"
        ."    <td class='va_t'><input type='button' id='btn_go_".$reportID."' value='Go'"
        ." class='formButton' style='width: 30px; height: 20px;'"
        ." onclick=\"filterbar_go_onclick("
        ."'".$reportID."','".$report_name."','".$toolbar."','".$ajax_mode."','".$page_url."'"
        .")\" /></td>"
        ."    <td class='va_t'><input type='button' id='btn_clear_".$reportID."' value='Clear'"
        ." class='formButton' style='width: 40px; height: 20px;'"
        ." onclick=\"filterbar_clear_onclick("
        ."'".$reportID."','".$report_name."','".$toolbar."','".$ajax_mode."','".$page_url."'"
        .")\" />"
        ."</td>\n"
        .($ajax_mode ?
             ""
         :
             "    <td class='va_t'><input type='button' id='btn_save_".$reportID."' value='Save...'"
            ." class='formButton' style='width: 50px; height: 20px'"
            ." onclick=\"filterbar_save_onclick('".$reportID."','".$report_name."','".$toolbar."');\" "
            ."/>"
            ."</td>\n"
        )
        ."  </tr>\n"
        ."</table>";
        return $out;
    }

    public function draw_toolbar_paging(
        $record_count,
        $limit,
        $offset,
        $show_prev_next,
        $show_page_select,
        $reportID = false,
        $report_name = false,
        $toolbar = false,
        $secondary = false,
        $ajax_mode = ''
    ) {
        global $filterField, $page_vars;
        $page_url =         BASE_PATH.trim($page_vars['path'], '/');
        if ($limit>$record_count) {
            if ($record_count>20) {
                $limit = 10;
            }
            if ($record_count>50) {
                $limit = 25;
            }
            if ($record_count>100) {
                $limit = 50;
            }
            if ($record_count>200) {
                $limit = 100;
            }
            if ($record_count>500) {
                $limit = 200;
            }
            if ($record_count>1000) {
                $limit = 500;
            }
        }
        $out =
         "<table class='admin_toolbartable' summary='Report Paging Toolbar'>\n"
        ."  <tr>\n"
        ."    <td>"
        ."<img class='b' src='".BASE_PATH."img/sysimg/icon_toolbar_end_left.gif'"
        ." style='height:16px;width:6px;padding-top:2px;padding-bottom:2px;' alt='|' />"
        ."</td>\n";
        if ($record_count>10) {
            $out.=
             "<td><select id=\"limit_".$reportID.($secondary ? "_s" : "")."\" "
            ."onchange=\"geid_set('limit',geid_val('limit_".$reportID.($secondary ? "_s" : "")."'));"
            .($report_name!==false ?
             "ajax_report('".$reportID."','".$report_name."','".$toolbar."','".$ajax_mode."','".$page_url."')"
             :
             "geid('form').submit();"
            )
            ."\" class=\"formField\">\n"
            ."  <option value=\"10\"".($limit==10 ? " selected='selected'":"").">10 Results</option>\n"
            .($record_count>20 ?
                "  <option value=\"20\"".($limit==20 ? " selected='selected'":"").">20 Results</option>\n"
             :
                ""
            )
            .($record_count>50 ?
                "  <option value=\"50\"".($limit==50 ? " selected='selected'":"").">50 Results</option>\n"
             :
                ""
            )
            .($record_count>100 ?
                "  <option value=\"100\"".($limit==100 ? " selected='selected'":"").">100 Results</option>\n"
             :
                ""
            )
            .($record_count>200 ?
                "  <option value=\"200\"".($limit==200 ? " selected='selected'":"").">200 Results</option>\n"
             :
                ""
            )
            .($record_count>500 ?
                "  <option value=\"500\"".($limit==500 ? " selected='selected'":"").">500 Results</option>\n"
             :
                ""
            )
            .($record_count==1000 ?
                "  <option value=\"1000\"".($limit==1000 ? " selected='selected'":"").">1000 Results</option>\n"
             :
                ""
            )
            ."  <option value=\"-1\"". ($limit==-1 ?" selected='selected'":"").">All Results</option>\n"
            ."</select>&nbsp;</td>";
            if ($show_prev_next && $limit!=-1) {
                if ($show_page_select) {
                    $out.=
                         "<td><input type='button' class='formButton'"
                        .($offset==0 ? " disabled='disabled'": "")
                        ." id='previous_".$reportID.($secondary ? "_s" : "")."' value='&lt;'"
                        ." onclick=\""
                        ."geid('previous_".$reportID.($secondary ? "_s" : "")."').disabled=true;"
                        ."geid('next_".$reportID.($secondary ? "_s" : "")."').disabled=true;"
                        ."geid('offset_".$reportID.($secondary ? "_s" : "")."').selectedIndex-=1;"
                        ."geid('offset').value=".($offset-$limit).";"
                        .($report_name!==false ?
                             "ajax_report("
                            ."'".$reportID."','".$report_name."','".$toolbar."','".$ajax_mode."','".$page_url."'"
                            .")"
                          :
                            "geid('form').submit();"
                        )
                        ."\" style='width: 20px; height: 18px;'/>&nbsp;</td>\n"
                        ."<td><input type='button' class='formButton'"
                        .($offset+$limit>=$record_count ? " disabled='disabled'": "")
                        ." id='next_".$reportID.($secondary ? "_s" : "")."' value='&gt;'"
                        ." onclick=\""
                        ."geid('previous_".$reportID.($secondary ? "_s" : "")."').disabled=true;"
                        ."geid('next_".$reportID.($secondary ? "_s" : "")."').disabled=true;"
                        ."geid('offset_".$reportID.($secondary ? "_s" : "")."').selectedIndex+=1;"
                        ."geid('offset').value=".($offset+$limit).";"
                        .($report_name!==false ?
                             "ajax_report("
                            ."'".$reportID."','".$report_name."','".$toolbar."','".$ajax_mode."','".$page_url."'"
                            .")"
                         :
                            "geid('form').submit();"
                        )
                        ."\" style='width: 20px; height: 18px;'/>&nbsp;</td>\n";
                }
            }
            if ($limit!=-1 && $show_page_select) {
                $out.=
                 "<td><select id=\"offset_".$reportID.($secondary ? "_s" : "")."\""
                ." name=\"offset_".$reportID.($secondary ? "_s" : "")."\""
                ." onchange=\"geid_set('offset',geid_val('offset_".$reportID.($secondary ? "_s" : "")."'));"
                .($report_name!==false ?
                 "ajax_report('".$reportID."','".$report_name."','".$toolbar."','".$ajax_mode."','".$page_url."')"
                 :
                 "geid('form').submit()"
                )
                ."\" class=\"formField\">\n";
                for ($i=0; $i<$record_count; $i = $i+$limit) {
                    $out.=
                         "  <option value=\"".$i."\"".($offset==$i ? " selected='selected'":"").">"
                        ."Show ".($i+1)."-".($i+$limit>$record_count ? $record_count : $i+$limit)
                        ."</option>\n";
                }
                $out.=
                "</select>&nbsp;</td>\n"
                ."<td class='nowrap'> of ".$record_count." records&nbsp;</td>\n";
            } else {
                $out.=
                "<td class='nowrap'>"
                ."Showing "
                .($limit==-1 ?
                " all of "
                :
                " ".(1+$offset)." to "
                .($offset+$limit > $record_count ? $record_count : $offset+$limit)
                ." of "
                )
                .$record_count." records&nbsp;</td>\n";
            }
        } else {
            $out.=
             "<td class='nowrap'>"
             .draw_form_field("limit_".$reportID, '', 'hidden')
             .draw_form_field("offset_".$reportID, '', 'hidden')
            ."</td>\n";
            if ($record_count>0) {
                $out.=
                 "<td class='nowrap'>Showing "
                .($record_count==1 ?
                "the only matching"
                : ($record_count==2 ? "both matching " : "all $record_count matching")
                )
                ." record".($record_count<>1 ? "s" : "")."&nbsp;</td>\n";
            } else {
                if ($filterField=="") {
                    $out.=  "<td class='nowrap'>(No records)&nbsp;</td>";
                } else {
                    $out.=  "<td class='nowrap'>(No records matched criteria.)&nbsp;</td>";
                }
            }
        }
        $out.=
         "  </tr>\n"
        ."</table>";
        return $out;
    }

    public static function get_filter($filterField, $filterExact, $filterValue)
    {
        if ($filterField=='') {
            return "";
        }
        switch ($filterExact) {
            case "":
            case 0: // Contains
                return "\nAND $filterField LIKE \"%$filterValue%\"";
            break;
            case 1: // Exacly equal to
                if ($filterValue=='') {
                    return "\nAND ($filterField = '' OR $filterField IS NULL)";
                }
                return "\nAND $filterField = \"$filterValue\"";
            break;
            case 2: // Not exactly equal to
                return "\nAND $filterField != \"$filterValue\"";
            break;
            case 3: // Starts with
                return "\nAND $filterField LIKE \"$filterValue%\"";
            break;
            case 4: // Ends with
                return "\nAND $filterField LIKE \"%$filterValue\"";
            break;
            case 5: // Contains word
                return "\nAND CONCAT(' ',$filterField,' ') LIKE \"% $filterValue %\"";
            break;
            case 6: // Contains word starting with
                return "\nAND CONCAT(' ',$filterField) LIKE \"% $filterValue%\"";
            break;
            case 7: // Contains word ending with
                return "\nAND CONCAT($filterField,' ') LIKE \"%$filterValue %\"";
            break;
            case 8: // Greater than
            case 9: // Less than
                switch ($filterExact) {
                    case 8:
                        $operator = ">";
                        break;
                    case 9:
                        $operator = "<";
                        break;
                }
                $now =          time();
                $YYYY =         adodb_date('Y', $now);
                $MM =           adodb_date('m', $now);
                switch ($filterValue) {
                    case "LAST_WEEK":
                        return
                    "\nAND YEARWEEK($filterField) $operator YEARWEEK(CURRENT_DATE - INTERVAL 7 DAY)";
                    break;
                    case "THIS_WEEK":
                        return
                    "\nAND YEARWEEK($filterField) $operator YEARWEEK(CURRENT_DATE)";
                    break;
                    case "NEXT_WEEK":
                        return
                    "\nAND YEARWEEK($filterField) $operator YEARWEEK(CURRENT_DATE + INTERVAL 7 DAY)";
                    break;
                    case "LAST_MONTH":
                        return
                    "\nAND $filterField $operator DATE_ADD('$YYYY-$MM-01',INTERVAL -1 MONTH)";
                    break;
                    case "THIS_MONTH":
                        return
                    "\nAND $filterField $operator '$YYYY-$MM-01'";
                    break;
                    case "NEXT_MONTH":
                        return
                    "\nAND $filterField $operator DATE_ADD('$YYYY-$MM-01',INTERVAL 1 MONTH)";
                    break;
                    case "LAST_YEAR":
                        return
                    "\nAND $filterField $operator DATE_ADD('$YYYY-01-01',INTERVAL -1 YEAR)";
                    break;
                    case "THIS_YEAR":
                        return
                    "\nAND $filterField $operator '$YYYY-01-01'";
                    break;
                    case "NEXT_YEAR":
                        return
                    "\nAND $filterField $operator DATE_ADD('$YYYY-01-01',INTERVAL 1 YEAR)";
                    break;
                    case "YESTERDAY":
                        return
                    "\nAND $filterField $operator DATE_ADD(CURDATE(),INTERVAL -1 DAY)";
                    break;
                    case "TODAY":
                        return "\nAND $filterField $operator CURDATE()";
                    break;
                    case "TOMORROW":
                        return
                    "\nAND $filterField $operator DATE_ADD(CURDATE(),INTERVAL 1 DAY)";
                    break;
                }
                return "\nAND $filterField $operator \"$filterValue\"";
            break;
            case 10: // Does not contain
                return "\nAND $filterField NOT LIKE \"%$filterValue%\"";
            break;
            case 11: // Within date range
                $now =          time();
                $YYYY =         adodb_date('Y', $now);
                $MM =           adodb_date('m', $now);
                switch ($filterValue) {
                    case "LAST_MONTH":
                        return
                    "\nAND $filterField >=DATE_ADD('$YYYY-$MM-01',INTERVAL -1 MONTH)"
                    ."\nAND $filterField<'$YYYY-$MM-01'";
                    break;
                    case "THIS_MONTH":
                        return
                    "\nAND $filterField >='$YYYY-$MM-01'"
                    ."\nAND $filterField < DATE_ADD('$YYYY-$MM-01',INTERVAL 1 MONTH)";
                    break;
                    case "NEXT_MONTH":
                        return
                    "\nAND $filterField >=DATE_ADD('$YYYY-$MM-01',INTERVAL 1 MONTH)"
                    ."\nAND $filterField<DATE_ADD('$YYYY-$MM-01',INTERVAL 2 MONTH)";
                    break;
                    case "LAST_WEEK":
                        return
                    "\nAND YEARWEEK($filterField) = YEARWEEK(CURRENT_DATE - INTERVAL 7 DAY)";
                    break;
                    case "THIS_WEEK":
                        return
                    "\nAND YEARWEEK($filterField) = YEARWEEK(CURRENT_DATE)";
                    break;
                    case "NEXT_WEEK":
                        return
                    "\nAND YEARWEEK($filterField) = YEARWEEK(CURRENT_DATE + INTERVAL 7 DAY)";
                    break;
                    case "LAST_YEAR":
                        return
                    "\nAND $filterField >=DATE_ADD('$YYYY-01-01',INTERVAL -1 YEAR)"
                    ."\nAND $filterField<'$YYYY-01-01'";
                    break;
                    case "THIS_YEAR":
                        return
                    "\nAND $filterField >='$YYYY-01-01'"
                    ."\nAND $filterField<DATE_ADD('$YYYY-01-01',INTERVAL 1 YEAR)";
                    break;
                    case "NEXT_YEAR":
                        return
                    "\nAND $filterField >=DATE_ADD('$YYYY-01-01',INTERVAL 1 YEAR)"
                    ."\nAND $filterField<DATE_ADD('$YYYY-01-01',INTERVAL 2 YEAR)";
                    break;
                    case "YESTERDAY":
                        return
                    "\nAND $filterField >=DATE_ADD(CURDATE(),INTERVAL -1 DAY)"
                    ."\nAND $filterField <CURDATE()";
                    break;
                    case "TODAY":
                        return
                    "\nAND $filterField >=CURDATE()"
                    ."\nAND $filterField <DATE_ADD(CURDATE(),INTERVAL 1 DAY)";
                    break;
                    case "TOMORROW":
                        return
                    "\nAND $filterField >=DATE_ADD(CURDATE(),INTERVAL 1 DAY)"
                    ."\nAND $filterField < DATE_ADD(CURDATE(),INTERVAL 2 DAY)";
                    break;
                    default:
                        return "";
                    break;
                }
                break;
            case 12: // Value in this CSV list
                $filterValue_arr = explode(',', str_replace('"', '', $filterValue));
                for ($i=0; $i<count($filterValue_arr); $i++) {
                    $filterValue_arr[$i] = trim($filterValue_arr[$i]);
                }
                $filterValue = "\"".implode("\",\"", $filterValue_arr)."\"";
                return "\nAND $filterField IN($filterValue)";
            break;
            case 13: // Value like one in this CSV list
                $filterValue_arr = explode(',', str_replace('"', '', $filterValue));
                for ($i=0; $i<count($filterValue_arr); $i++) {
                    $filterValue_arr[$i] =
                    $filterField." LIKE \"%".trim($filterValue_arr[$i])."%\"";
                }
                return "\nAND (".implode(" OR ", $filterValue_arr).")";
            break;
        }
    }

    public function get_report_heads(
        $columns,
        $ajax,
        $report_name,
        $toolbar,
        $ajax_mode
    ) {
        $isMASTERADMIN = get_person_permission("MASTERADMIN");
        $out = '';
        $Obj_Report = new Report();
        foreach ($columns as $column) {
            if (
                $column['access']==1 &&
                $column['visible']==1 &&
                ($column['reportLabel']!="" || $column['fieldType']=='checkbox' || $column['fieldType']=='delete')
            ) {
                switch ($column['fieldType']) {
                    case "quantity":
                        break;
                    default:
                        $labelIsImage = (
                            substr($column['reportLabel'], 0, 4)=="<img" ||
                            substr($column['reportLabel'], 0, 7)=="[LABEL]"
                        );
                        $cm_code = ($isMASTERADMIN && !$ajax ?
                             " onmouseover=\"if(!_contextActive) {"
                            ."_CM.type='report_column';"
                            ."_CM.ID='".$column['report_columnID']."'"
                            ."};\""
                            ." onmouseout=\"_CM.type='';\""
                         :
                             ""
                        );
                        switch (strToLower($column['fieldType'])){
                            case 'checkbox':
                                $out.=
                                 ($ajax? "\"" : "")
                                ."<th class='grid_head_nosort' $cm_code>"
                                .($ajax ? "\\n" : "\n")
                                ."  <input type='checkbox' value='1'"
                                ." onclick=\"column_select(".$column['reportID'].",this.checked)\"/>"
                                .($ajax ? "\\n" : "\n")
                                ."</th>".($ajax ? "\\n" : "\n")
                                .($ajax? "\"" : "");
                                break;
                            default:
                                if ($column['reportSortBy_a']=="") {
                                    $out.=
                                     ($ajax? "\"" : "")
                                    ."<th class='grid_head_nosort' $cm_code>"
                                    .($ajax ? "\\n" : "\n")
                                    ."  ".$column['reportLabel']
                                    .($ajax ? "\\n" : "\n")
                                    ."</th>"
                                    .($ajax ? "\\n\"+\n" : "\n");
                                } else {
                                    $out.=
                                    ($ajax? "\"" : "")
                                    .$this->get_report_head_sortable(
                                        "Sort by ".$column['reportFilterLabel'],
                                        $column['reportLabel'],
                                        $column['reportField'],
                                        $column['reportSortBy_AZ'],
                                        $labelIsImage,
                                        $column['report_columnID'],
                                        $report_name,
                                        $toolbar,
                                        $ajax_mode
                                    )
                                    .($ajax ? "\\n\"+\n" : "\n");
                                }
                                break;
                        }
                        break;
                }
            }
        }
        return $out;
    }

    public function get_report_head_sortable(
        $hint,
        $label,
        $test,
        $AZ,
        $lblImage,
        $ID,
        $report_name,
        $toolbar,
        $ajax_popup_url
    ) {
        global $sortBy,$selectID,$page_vars;
        $page_url =         BASE_PATH.trim($page_vars['path'], '/');
        $isMASTERADMIN =    get_person_permission("MASTERADMIN");
        $sorted =           ($sortBy==$test || $sortBy==$test."_d");
        return
         "<th class='".($sorted ? "grid_head_a" : "grid_head_n")."'"
        ." onmouseover=\""
        .($isMASTERADMIN ? "if(!_contextActive) {_CM.type='report_column';_CM.ID='".$ID."'};" : "")
        .($sorted ? "" : "column_over(this,'o')")."\""
        ." onmouseout=\"".($isMASTERADMIN ? "_CM.type='';" : "")
        .($sorted ? "" : "column_over(this,'n')")
        ."\""
        ." onmousedown=\"column_over(this,'d');\""
        ." onclick=\""
        ."geid_set('selectID','".$selectID."');"
        ."geid_set('sortBy','"
        .($AZ=='1' ? ($sortBy==$test ? $test."_d" : $test) : ($sortBy==$test."_d" ? $test : $test."_d"))
        ."');"
        ."ajax_report("
        ."'".$this->_get_ID()."','".$report_name."','".$toolbar."','".$ajax_popup_url."','".$page_url."'"
        .");\""
        ." title=\"".$hint."\">".$label
        .($sortBy==$test ?
             "<img src='".BASE_PATH."img/spacer' class='icons'"
            ." style='height:8px;width:9px;background-position:-2919px 0px;' alt='A-Z' />"
         :
            ""
        )
        .($sortBy==$test."_d" ?
             "<img src='".BASE_PATH."img/spacer' class='icons'"
            ." style='height:8px;width:9px;background-position:-2928px 0px;' alt='Z-A' />"
         :
            ""
        )
        ."</th>";
    }

    public function get_version()
    {
        return VERSION_REPORT_REPORT;
    }
}
