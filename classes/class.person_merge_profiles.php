<?php
define('VERSION_PERSON_MERGE_PROFILES', '1.0.4');
/*
Version History:
  1.0.4 (2015-01-11)
    1) Changed references from System::tables to System::TABLES
    2) Now PSR-2 Compliant

  (Older version history in class.person_merge_profiles.txt)
*/
class Person_merge_profiles extends Person
{
    protected $_affected_records = 0;
    protected $_delete_source_profiles;
    protected $_sourceID_csv;
    protected $_targetID;
    protected $_targetValue;

    public function draw()
    {
        try {
            $this->_draw_setup();
        } catch (Exception $e) {
            $this->_draw_css();
            $this->_common_draw_status();
            $this->_draw_render();
        }
        $this->_draw_css();
        $this->_common_draw_status();
        $this->_draw_form();
        $this->_draw_render();
    }

    protected function _draw_css()
    {
        $this->_css.=
         "[\n"
        ."  ['#merge_profiles h1',{color:'#000', margin:0}],\n"
        ."  ['#merge_profiles label',{display:'block', fontWeight:'bold'}],\n"
        ."  ['#merge_profiles table',{width:'100%'}],\n"
        ."  ['#merge_profiles table thead th',{backgroundColor:'#707070',color:'#fff'}],\n"
        ."  ['#merge_profiles table thead th.v',{width:'20px'}]\n"
        ."]\n";
    }

    protected function _draw_form()
    {
        if (get_var('submode')!='') {
            return;
        }
        $this->_html.=
             "<p>\n"
            ."This tool moves all items relating to one or more source profiles together with their group membership"
            ." records to a single chosen destination profile."
            ."</p>"
            ."<label>Selected Profiles</label>"
            ."<div>Click a profile below to set it as the destination for the merge."
            ." Right-click any record to edit it.</div>\n"
            ."<table summary='Profiles' border='1' cellpadding='2' class='report' cellspacing='0'>\n"
            ."  <thead>\n"
            ."    <tr>\n"
            ."      <th>User<br />Name</th>\n"
            ."      <th>Full<br />Name</th>\n"
            ."      <th>Primary<br />Email</th>\n"
            .(System::has_feature('show-home-address') ? "      <th>Home<br />Address</th>\n" : "")
            .(System::has_feature('show-home-address') ? "      <th>Home<br />Phone</th>\n" : "")
            .(System::has_feature('show-work-address') ? "      <th>Work<br />Address</th>\n" : "")
            .(System::has_feature('show-work-address') ? "      <th>Work<br />Phone</th>\n" : "")
            ."      <th class='v'>"
            ."[LBL]PEACH-num-cases|100|Number of Cases assigned to person[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-case-tasks|100|Number of Case Tasks assigned to person[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-emails-sent|100|Number of Emails sent to person[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-event-registrations|100|Number of Events person has registered[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-groups|100|Number of Groups Member is a part of[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-orders|100|Number of Orders placed by (or for) the person[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-credit-memos|100|Number of Credit Memos issued to person[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-notes|100|Number of Notes assigned to person[/LBL]"
            ."</th>\n"
            ."      <th class='v'>"
            ."[LBL]PEACH-num-logins|100|Number of times this person has logged in[/LBL]"
            ."</th>\n"
            ."      <th class='v'>Last Login</th>\n"
            ."      <th class='v'>Method</th>\n"
            ."      <th>Created</th>\n"
            ."      <th>Modified</th>\n"
            ."    </tr>\n"
            ."  </thead>\n"
            ."  <tbody>\n";
        foreach ($this->_records as $r) {
            $this->load($r);
            $this->_context_menu_ID = $r['type'];
            $CM = substr((substr($this->BL_context_selection_start(), 4)), 0, -1);
            $this->_html.=
                 "    <tr id='merge_profiles_row_".$r['ID']."'"
                ." onclick=\"merge_profiles_select_destination(".$r['ID'].")\""
                .$CM
                .">\n"
                ."      <td>".$r['PUsername']."</td>\n"
                ."      <td>".$r['name_full']."</td>"
                ."      <td>".$r['PEmail']."</td>\n"
                .(System::has_feature('show-home-address') ?
                    "      <td>".$r['home_address']."</td>\n"
                 :
                    ""
                )
                .(System::has_feature('show-home-address') ?
                    "      <td>".$r['ATelephone']."</td>\n"
                 :
                    ""
                )
                .(System::has_feature('show-work-address') ?
                    "      <td>".$r['work_address']."</td>\n"
                 :
                    ""
                )
                .(System::has_feature('show-work-address') ?
                    "      <td>".$r['WTelephone']."</td>\n"
                 :
                    ""
                )
                ."      <td>".$r['num_cases']."</td>\n"
                ."      <td>".$r['num_case_tasks']."</td>\n"
                ."      <td>".$r['num_emails']."</td>\n"
                ."      <td>".$r['num_event_registrations']."</td>\n"
                ."      <td>".$r['num_groups']."</td>\n"
                ."      <td>".$r['num_orders']."</td>\n"
                ."      <td>".$r['num_credit_memos']."</td>\n"
                ."      <td>".$r['num_notes']."</td>\n"
                ."      <td>".$r['PLogonCount']."</td>\n"
                ."      <td"
                .($r['PLogonLastDate']!='0000-00-00 00:00:00' ?
                      " title=\"From Host ".$r['PLogonLastHost']." (".$r['PLogonLastIP'].")\">"
                     ."<span class='nowrap'>"
                     .substr($r['PLogonLastDate'], 0, 10)
                     ."</span> "
                     .substr($r['PLogonLastDate'], 11)
                  :
                     ">&nbsp;"
                )
                ."</td>\n"
                ."      <td>".$r['PLogonLastMethod']."</td>\n"
                ."      <td"
                .($r['history_created_date']!='0000-00-00 00:00:00' ?
                     " title=\"By ".$r['created_by']."\">"
                    ."<span class='nowrap'>"
                    .substr($r['history_created_date'], 0, 10)
                    ."</span> "
                    .substr($r['history_created_date'], 11)
                 :
                    ">&nbsp;"
                )
                ."</td>\n"
                ."      <td"
                .($r['history_modified_date']!='0000-00-00 00:00:00' ?
                     " title=\"By ".$r['modified_by']."\">"
                    ."<span class='nowrap'>"
                    .substr($r['history_modified_date'], 0, 10)
                    ."</span> "
                    .substr($r['history_modified_date'], 11)
                 :
                    ">&nbsp;"
                )
                ."</td>\n"
                ."    </tr>\n";
        }
        $this->_html.=
         "  </tbody>\n"
        ."</table>\n"
        ."<p class='txt_c'>\n"
        ."<input id='merge_profiles_cancel' type='button' value='Cancel'"
        ." style='width:100px' class='formButton' onclick=\"hidePopWin()\" />\n"
        ."<input id='merge_profiles_submit' type='button' value='Merge Profiles' disabled='disabled'"
        ." style='width:100px' class='formButton' onclick=\"merge_profiles_process('".$this->_targetID."')\" />\n"
        ."</p>"
        ."</div>"
        ;
    }

    protected function _draw_render()
    {
        $this->_html =
         "<div id='merge_profiles'>\n"
        ."<h1>Profile Merge</h1>\n"
        .$this->_html
        ."</div>";
        $this->_draw_render_JSON();
    }

    protected function _draw_setup()
    {
        $this->_safe_ID = 'merge_profiles';
        $this->_draw_setup_get_targetID();
        $this->_draw_setup_check_user_is_admin();
        $this->_draw_seup_do_merge();
        $this->_draw_setup_load_records();
    }

    protected function _draw_setup_check_user_is_admin()
    {
        $this->_common_load_user_rights();
        if (!$this->_current_user_rights['isUSERADMIN']) {
            $this->_msg = "<b>Error:</b> You do not have permission to perform this operation.";
            throw new Exception;
        }
    }

    protected function _draw_seup_do_merge()
    {
        if (get_var('submode')!='merge') {
            return;
        }
        if (!$this->_targetValue = get_var('targetValue')) {
            $this->_msg = "<b>Error:</b> No Target Value was given.";
            throw new Exception;
        }
        $source_arr =   explode(',', $this->_targetID);
        if (!in_array($this->_targetValue, $source_arr)) {
            $this->_msg = "<b>Error:</b> Target was not one of the items selected for inclusion.";
            throw new Exception;
        }
        $this->_sourceID_csv =  implode(',', array_diff($source_arr, array($this->_targetValue)));
        $deleteSourceAcct =     false;
        $result =               $this->merge($this->_sourceID_csv, $this->_targetValue, $deleteSourceAcct);
        $this->_html.=          "<p><b>Result:</b> Merged ".$result." records.</p>";
    }

    protected function _draw_setup_get_targetID()
    {
        if (!$this->_targetID = get_var('targetID')) {
            $this->_msg = "<b>Error:</b> There are no items selected.";
            throw new Exception;
        }
    }

    protected function _draw_setup_load_records()
    {
        $sql =
         "SELECT\n"
        ."  `ID`,\n"
        ."  `type`,\n"
        ."  `systemID`,\n"
        ."  `PEmail`,\n"
        ."  `PUsername`,\n"
        ."  `PLogonCount`,\n"
        ."  `PLogonLastDate`,\n"
        ."  `PLogonLastHost`,\n"
        ."  `PLogonLastIP`,\n"
        ."  `PLogonLastMethod`,\n"
        ."  `history_created_date`,\n"
        ."  (SELECT\n"
        ."    `PUsername`\n"
        ."  FROM\n"
        ."    `person` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`ID` = `person`.`history_created_by`\n"
        ."  )  AS `created_by`,\n"
        ."  `history_modified_date`,\n"
        ."  (SELECT\n"
        ."    `PUsername`\n"
        ."  FROM\n"
        ."    `person` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`ID` = `person`.`history_modified_by`\n"
        ."  )  AS `modified_by`,\n"
        ."  `ATelephone`,\n"
        ."  `WTelephone`,\n"
        ."  TRIM(\n"
        ."    CONCAT(\n"
        ."      `NTitle`,\n"
        ."      IF(`NTitle`!='',' ',''),\n"
        ."      `NFirst`,\n"
        ."      IF(`NFirst`!='',' ',''),\n"
        ."      `NMiddle`,\n"
        ."      IF(`NMiddle`!='',' ',''),\n"
        ."      `NLast`\n"
        ."      )\n"
        ."    ) AS `name_full`,\n"
        .(System::has_feature('show-home-address') ?
             "  TRIM(\n"
            ."    CONCAT(\n"
            ."      `AAddress1`,\n"
            ."      IF(`AAddress1`!='',' ',''),\n"
            ."      `AAddress2`,IF(`AAddress2`!='',' ',''),\n"
            ."      `ACity`,\n"
            ."      IF(`ACity`!='',' ',''),\n"
            ."      `ASpID`,\n"
            ."      IF(`ASpID`!='',' ',''),\n"
            ."      `APostal`,\n"
            ."      IF(`APostal`!='',' ',''),\n"
            ."      `ACountryID`\n"
            ."    )\n"
            ."  ) AS `home_address`,\n"
         :
            ""
        )
        .(System::has_feature('show-work-address') ?
             "  TRIM(\n"
            ."    CONCAT(\n"
            ."      `WCompany`,\n"
            ."      IF(`WCompany`!='',' ',''),\n"
            ."      `WAddress1`,\n"
            ."      IF(`WAddress1`!='',' ',''),\n"
            ."      `WAddress2`,\n"
            ."      IF(`WAddress2`!='',' ',''),\n"
            ."      `WCity`,\n"
            ."      IF(`WCity`!='',' ',''),\n"
            ."      `WSpID`,\n"
            ."      IF(`WSpID`!='',' ',''),\n"
            ."      `WPostal`,\n"
            ."      IF(`WPostal`!='',' ',''),\n"
            ."      `WCountryID`\n"
            ."    )\n"
            ."  ) AS `work_address`,\n"
         :
            ""
        )
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `cases` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`assigned_personID` = `person`.`ID`\n"
        ."  )  AS `num_cases`,\n"
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `case_tasks` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`assigned_personID` = `person`.`ID`\n"
        ."  )  AS `num_case_tasks`,\n"
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `registerevent` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`attender_personID` = `person`.`ID` OR\n"
        ."    `x`.`inviter_personID` = `person`.`ID`\n"
        ."  )  AS `num_event_registrations`,\n"
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `group_members` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`personID` = `person`.`ID`\n"
        ."  )  AS `num_groups`,\n"
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `mailqueue_item` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`personID` = `person`.`ID`\n"
        ."  )  AS `num_emails`,\n"
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `orders` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`personID` = `person`.`ID` AND\n"
        ."    `x`.`archive` = 0 AND\n"
        ."    `x`.`credit_memo_for_orderID` = 0\n"
        ."  )  AS `num_orders`,\n"
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `orders` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`personID` = `person`.`ID` AND\n"
        ."    `x`.`archive` = 0 AND\n"
        ."    `x`.`credit_memo_for_orderID` !=0\n"
        ."  )  AS `num_credit_memos`,\n"
        ."  (SELECT\n"
        ."    COUNT(*)\n"
        ."  FROM\n"
        ."    `postings` AS `x`\n"
        ."  WHERE\n"
        ."    `x`.`personID` = `person`.`ID` AND\n"
        ."    `x`.`type`='note'\n"
        ."  )  AS `num_notes`\n"
        ."FROM\n"
        ."  `person`\n"
        ."WHERE\n"
        ."  `ID` IN(".$this->_targetID.")\n"
        ."ORDER BY\n"
        ."  `history_modified_date` DESC,\n"
        ."  `history_created_date` DESC";
//        $this->_html.="<textarea style='height:100px;width:800px'>".$sql."</textarea>";
        $this->_records = $this->get_records_for_sql($sql);
    }

    public function merge($sourceID_csv, $targetID, $delete_source_profiles = false)
    {
        $this->_merge_setup($sourceID_csv, $targetID, $delete_source_profiles);
        $this->_merge_delete_source_profiles();
        $this->_merge_group_membership();
        $this->_merge_set_history_created_by();
        $this->_merge_set_history_modified_by();
        $this->_merge_update_cases();
        $this->_merge_update_case_tasks();
        $this->_merge_update_community_members();
        $this->_merge_update_event_registrations();
        $this->_merge_update_mailqueue_items();
        $this->_merge_update_orders_and_credit_memos();
        $this->_merge_update_postings();
        return $this->_affected_records;
    }

    protected function _merge_delete_source_profiles()
    {
        if (!$this->_delete_source_profiles) {
            return;
        }
        $this->_Obj_s->delete();
    }

    protected function _merge_group_membership()
    {
        $Obj_GM = new Group_Member;
        $sql =
         "SELECT\n"
        ."  `group_members`.*\n"
        ."FROM\n"
        ."  `group_members`\n"
        ."WHERE\n"
        ."  `personID` IN (".$this->_sourceID_csv.",".$this->_targetID.")\n"
        ."ORDER BY `groupID`, `history_modified_date` DESC, `history_created_date` DESC";
        $records = $Obj_GM->get_records_for_sql($sql);
        $group_arr = array();
        foreach ($records as $r) {
            $groupID = $r['groupID'];
            $r['personID'] = $this->_targetID;
            if (!isset($group_arr[$groupID])) {
                unset($r['ID']);
                $group_arr[$groupID] = $r;
            } else {
                if ($r['permEMAILRECIPIENT']=='1') {
                    $group_arr[$groupID]['permEMAILRECIPIENT']=1;
                }
                if ($r['permEMAILOPTOUT']=='1') {
                    $group_arr[$groupID]['permEMAILOPTOUT']=1;
                }
                if ($r['permVIEWER']=='1') {
                    $group_arr[$groupID]['permVIEWER']=1;
                }
                if ($r['permEDITOR']=='1') {
                    $group_arr[$groupID]['permEDITOR']=1;
                }
            }
        }
        $sql =
         "DELETE FROM\n"
        ."  `group_members`\n"
        ."WHERE\n"
        ."  `personID` IN (".$this->_sourceID_csv.",".$this->_targetID.")";
        $Obj_GM->do_sql_query($sql);
        $this->_affected_records+= Record::get_affected_rows()+count($group_arr);
        foreach ($group_arr as $data) {
            $Obj_GM->insert($data);
        }
        $Obj_Person = new Person;
        foreach ($records as $r) {
            $Obj_Person->_set_ID($r['personID']);
            $Obj_Person->set_groups_list_description(false);
        }
    }

    protected function _merge_set_history_created_by()
    {
        $system_tables = explode(',', str_replace(' ', '', System::TABLES));
        foreach ($system_tables as $table) {
            $this->_Obj->_set_table_name($table);
            $this->_affected_records+= $this->_Obj->set_field_on_value(
                'history_created_by',
                $this->_sourceID_csv,
                $this->_targetID
            );
        }
    }

    protected function _merge_set_history_modified_by()
    {
        $system_tables = explode(',', str_replace(' ', '', System::TABLES));
        foreach ($system_tables as $table) {
            $this->_Obj->_set_table_name($table);
            $this->_affected_records+= $this->_Obj->set_field_on_value(
                'history_modified_by',
                $this->_sourceID_csv,
                $this->_targetID
            );
        }
    }

    protected function _merge_update_cases()
    {
        $this->_Obj->_set_table_name('cases');
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'assigned_personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'related_personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
    }

    protected function _merge_update_case_tasks()
    {
        $this->_Obj->_set_table_name('case_tasks');
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'assigned_personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
    }

    protected function _merge_update_community_members()
    {
        $this->_Obj->_set_table_name('community_member');
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'contactID',
            $this->_sourceID_csv,
            $this->_targetID
        );
    }

    protected function _merge_update_event_registrations()
    {
        $this->_Obj->_set_table_name('registerevent');
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'attender_personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'inviter_personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
    }

    protected function _merge_update_mailqueue_items()
    {
        $this->_Obj->_set_table_name('mailqueue_item');
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
    }

    protected function _merge_update_orders_and_credit_memos()
    {
        $this->_Obj->_set_table_name('orders');
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
    }

    protected function _merge_update_postings()
    {
        $this->_Obj->_set_table_name('postings');
        $this->_affected_records+= $this->_Obj->set_field_on_value(
            'personID',
            $this->_sourceID_csv,
            $this->_targetID
        );
    }

    protected function _merge_setup($sourceID_csv, $targetID, $delete_source_profiles = false)
    {
        $this->_sourceID_csv =              $sourceID_csv;
        $this->_targetID =                  $targetID;
        $this->_delete_source_profiles =    $delete_source_profiles;
        $this->_Obj =                       new Record;
        $this->_Obj_s =                     new Person($this->_sourceID_csv);
        $this->_Obj_t =                     new Person($this->_targetID);
    }

    public function get_version()
    {
        return VERSION_PERSON_MERGE_PROFILES;
    }
}
