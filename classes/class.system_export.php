<?php
define('VERSION_SYSTEM_EXPORT', '1.0.18');

/*
Version History:
  1.0.18 (2015-01-10)
    1) Changed references from System::tables to System::TABLES

  (Older version history in class.system_export.txt)
*/

class System_Export extends System
{
    private $_custom_tables_csv;
    private $_custom_tables_delete_sql;
    private $_custom_tables_select_sql;

    public function draw($show_fields)
    {
        global $page_vars;
        $targetID = $this->_get_ID();
        $this->get_db_custom_tables();
        if (get_var('targetValue')=='') {
            $this->_draw_form($show_fields);
            die;
        }
        $this->get_db_custom_tables();
        header("Content-type: text/plain; charset=UTF-8");
        $sql_arr = array(
            'system' =>                   "`textEnglish`",
            'action' =>                   "`systemID`,`sourceType`,`sourceID`,`seq`",
            'activity' =>                 "`systemID`,`sourceType`,`history_created_date`",
            'block_layout' =>             "`systemID`,`name`",
            'cases' =>                    "`systemID`,`history_created_date`",
            'case_tasks' =>               "`systemID`,`history_created_date`",
            'category_assign' =>          "`systemID`,`history_created_date`",
            'colour_scheme' =>            "`systemID`,`name`",
            'comment' =>                  "`systemID`,`history_created_date`",
            'community' =>                "`systemID`,`history_created_date`",
            'community_member' =>         "`systemID`,`history_created_date`",
            'community_membership' =>     "`systemID`,`history_created_date`",
            'component' =>                "`systemID`,`name`",
            'content_block' =>            "`systemID`,`name`",
            'custom_form' =>              "`systemID`,`name`",
            'ecl_tags' =>                 "`systemID`,`tag`",
            'field_templates' =>          "`systemID`,`name`",
            'gateway_settings' =>         "`systemID`,`name`",
            'gateway_type' =>             "`systemID`,`name`",
            'geocode_cache' =>            "`systemID`,`query_date`,`input_address",
            'groups' =>                   "`systemID`,`name`",
            'group_assign' =>             "`systemID`,`groupID`,`assign_type`",
            'group_members' =>            "`systemID`,`groupID`",
            'keywords' =>                 "`systemID`,`keyword`",
            'keyword_assign' =>           "`systemID`,`keywordID`",
            'language_assign' =>          "`systemID`,`languageID`",
            'layout' =>                   "`systemID`,`name`",
            'listdata' =>                 "`systemID`,`listTypeID`,`textEnglish`",
            'listtype' =>                 "`systemID`,`ID`",
            'mailidentity' =>             "`systemID`,`name`",
            'mailqueue' =>                "`systemID`,`date_started`",
            'mailqueue_item' =>           "`systemID`,`mailQueueID`,`PEmail`",
            'mailtemplate' =>             "`systemID`,`name`",
            'membership_rule' =>          "`systemID`,`seq`",
            'module_credits' =>           "`systemID`,`history_created_date`",
            'navbuttons' =>               "`systemID`,`suiteID`,`position`,`text1`",
            'navsuite' =>                 "`systemID`,`name`",
            'navstyle' =>                 "`systemID`,`name`",
            'orders' =>                   "`systemID`,`history_created_date`",
            'order_items' =>              "`systemID`,`history_created_date`",
            'pages' =>                    "`systemID`,`page`",
            'payment_method' =>           "`systemID`,`history_created_date`",
            'person' =>                   "`systemID`,`PUsername`",
            'poll' =>                     "`systemID`,`date`,`question`",
            'poll_choice' =>              "`systemID`,`parentID`,`title`",
            'postings' =>                 "`systemID`,`date`,`time_start`",
            'product' =>                  "`systemID`,`groupingID`,`itemCode`",
            'product_grouping' =>         "`systemID`,`name`",
            'product_relationship' =>     "`systemID`,`productID`,`related_object`,`related_objectID`",
            'push_product_assign' =>      "`systemID`,`productID`",
            'qb_config' =>                "`systemID`,`history_created_date`",
            'qb_connection' =>            "`systemID`,`history_created_date`",
            'qb_ident' =>                 "`systemID`,`history_created_date`",
            'qb_import' =>                "`systemID`,`history_created_date`",
            'qb_log' =>                   "`systemID`,`history_created_date`",
            'qb_notify' =>                "`systemID`,`history_created_date`",
            'qb_queue' =>                 "`systemID`,`history_created_date`",
            'qb_recur' =>                 "`systemID`,`history_created_date`",
            'qb_ticket' =>                "`systemID`,`history_created_date`",
            'qb_user' =>                  "`systemID`,`history_created_date`",
            'registerevent' =>            "`systemID`,`eventID`,`inviter_personID`,`attender_NLast`",
            'report' =>                   "`systemID`,`name`",
            'report_columns' =>           "`systemID`,`reportID`,`tab`,`seq`",
            'report_defaults' =>          "`systemID`,`reportID`,`personID`",
            'report_filter' =>            "`systemID`,`reportID`,`label`",
            'report_filter_criteria' =>   "`systemID`,`filterID`,`filter_seq`",
            'report_settings' =>          "`systemID`,`reportID`,`destinationType`,`destinationID`",
            'scheduled_task' =>           "`systemID`,`description`",
            'tax_code' =>                 "`systemID`,`name`",
            'tax_regime' =>               "`systemID`,`name`",
            'tax_rule' =>                 "`systemID`,`seq`",
            'tax_zone' =>                 "`systemID`,`name`",
            'theme' =>                    "`systemID`,`name`",
            'widget' =>                   "`systemID`,`name`"
        );
        $this->get_custom_tables_sql($show_fields);
        $chosen = explode(',', get_var('targetValue'));
        $ObjBackup = new Backup;
        $delete_sql = "";
        if (in_array('system', $chosen)) {
            $delete_sql.=     "DELETE FROM `system`                  WHERE `ID`       IN (".$this->_get_ID().");\n";
        }
        $system_tables = explode(',', str_replace(' ', '', System::TABLES));
        foreach ($system_tables as $table) {
            if ($table!=='system' && in_array($table, $chosen)) {
                $delete_sql.=   "DELETE FROM ".pad("`".$table."`", 25)." WHERE `systemID` IN (".$this->_get_ID().");\n";
            }
        }
        print
        $this->sql_header("Selected ".$this->_get_object_name().$this->plural($this->_get_ID()))
        .$delete_sql
        .(in_array('custom_tables', $chosen) ? $this->_custom_tables_delete_sql : "")
        .$this->sql_footer();
        foreach ($chosen as $table) {
            switch ($table){
                case 'custom_tables':
                    break;
                default:
                    print $ObjBackup->db_export_sql_query(
                        "`".$table."`",
                        "SELECT * FROM `".$table."` WHERE "
                        .($table=='system' ? "`ID`      " : "`systemID`")
                        ." IN (".$targetID.")"
                        ." ORDER BY ".$sql_arr[$table],
                        $show_fields
                    );
                    break;
            }
        }
        if (in_array('custom_tables', $chosen)) {
            print $this->_custom_tables_select_sql;
        }
        die;
    }

    protected function _draw_form($show_fields)
    {
        $targetID = $this->_get_ID();
        $this->get_db_custom_tables();
        $counts = $this->get_counts_for_tables($targetID);
        $custom_tables = $this->get_counts_for_custom_tables($targetID);
        $system_tables = explode(',', str_replace(' ', '', System::TABLES));
        $tables = array();
        foreach ($system_tables as $t) {
            if ($counts[$t]!=-1) {
                $tables[] = $t;
            }
        }
        header("Content-type: text/html; charset=UTF-8");
        $html =
             "<!DOCTYPE html PUBLIC "
            ."\"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n"
            ."<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\n"
            ."<head>\n"
            ."<title>System Export</title>\n"
            ."<style type=\"text/css\">\n"
            ."/*<![CDATA[*/\n"
            ."table.options .a{\n"
            ." width: 25px;\n"
            ."}\n"
            ."table.options .b{\n"
            ." font-weight: normal; width: 150px; text-align: left;\n"
            ."}\n"
            ."table.options .c{\n"
            ." width: 40px; text-align: right;\n"
            ."}\n"
            ."table.options .d{\n"
            ." padding-right:50px;\n"
            ."}\n"
            ."/*]]>*/\n"
            ."</style>\n"
            ."<script type='text/javascript'>\n"
            ."//<![CDATA[\n"
            ."// These $() functions are using their own function NOT JQuery\n"
            ."function \$(id){\n"
            ."  return document.getElementById(id);\n"
            ."}\n"
            ."window.onpageshow = function(evt){form_reset()}\n"
            ."// See https://developer.mozilla.org/en-US/docs/Using_Firefox_1.5_caching\n"
            ."var tables = ('".(implode(',', $tables)).",custom_tables').split(',');\n"
            ."function btn_all_none(state){\n"
            ."  for(var i=0; i<tables.length; i++){\n"
            ."    if(\$('chk_'+tables[i])){\n"
            ."      \$('chk_'+tables[i]).checked=state;\n"
            ."    }\n"
            ."  }\n"
            ."}\n"
            ."function form_reset(){\n"
            ."  \$('btn_all').disabled=false;\n"
            ."  \$('btn_none').disabled=false;\n"
            ."  \$('btn_go').disabled=false;\n"
            ."  for(var i=0; i<tables.length; i++){\n"
            ."    if(\$('chk_'+tables[i])){\n"
            ."      \$('chk_'+tables[i]).checked=true;\n"
            ."    }\n"
            ."  }\n"
            ."}\n"
            ."function form_submit(){\n"
            ."  \$('btn_all').disabled=true;\n"
            ."  \$('btn_none').disabled=true;\n"
            ."  \$('btn_go').disabled=true;\n"
            ."  chosen = [];\n"
            ."  for(var i=0; i<tables.length; i++){\n"
            ."    if(\$('chk_'+tables[i]) && \$('chk_'+tables[i]).checked){\n"
            ."      chosen.push(tables[i]);\n"
            ."    }\n"
            ."  }\n"
            ."  \$('targetValue').value = chosen.join(',');\n"
            ."}\n"
            ."//]]>\n"
            ."</script>\n"
            ."</head>\n"
            ."<body onload='form_reset()'>\n"
            ."<h1>Configure System Export</h1>\n"
            ."<form id='form' action='".BASE_PATH."export/sql/system' method='post' onsubmit=\"form_submit()\">\n"
            ."<fieldset>\n"
            ."<input type='hidden' name='targetValue' id='targetValue' value='1' />\n"
            ."<input type='hidden' name='show_fields' id='show_fields' value='".$show_fields."' />\n"
            ."<input type='hidden' name='targetID' id='targetID' value='".$targetID."' />\n"
            ."<table class='options' cellpadding='0' cellspacing='0' border='0'>\n";
        $columns = 3;
        $len = ceil(count($tables)/$columns);
        for ($i=0; $i<$len; $i++) {
            $html.= "  <tr>\n";
            for ($j=0; $j<$columns; $j++) {
                if (isset($tables[$i+($j*$len)])) {
                    $table = $tables[$i+($j*$len)];
                    $html.=
                         "    <td class='a'>"
                        ."<input type='checkbox' id='chk_".$table."' name='chk_".$table."'"
                        ." checked='checked' tabindex='".($i+($j*$len))."' />"
                        ."</td>\n"
                        ."    <th class='b'>"
                        ."<label for='chk_".$table."'>".$table."</label>"
                        ."</th>\n"
                        ."    <td class='c".($j==$columns-1 ? '' : ' d')."'>"
                        .$counts[$table]
                        ."</td>\n";
                }
            }
            $html.= "  </tr>\n";
        }
        $html.= "</table>\n";
        if (count($custom_tables)) {
            $html.=
             "<hr />\n"
            ."<table class='options' cellpadding='0' cellspacing='0' border='0'>\n"
            ."  <tr>\n"
            ."    <td class='a'>"
            ."<input type='checkbox' id='chk_custom_tables' name='chk_custom_tables' checked='checked' />"
            ."</td>\n"
            ."    <th class='b'>"
            ."<label for='chk_custom_tables'>Custom Tables (".count($custom_tables).")</label>"
            ."</th>\n"
            ."    <td class='c'>"
            ."&nbsp;"
            ."</td>\n"
            ."  </tr>\n"
            ."</table>\n";
        }
        $html.=
             "</fieldset>\n"
            ."<p style='text-align:center'>\n"
            ."<input type='button' id='btn_all' value='All' style='width:100px;'"
            ." onclick='return btn_all_none(1)' tabindex='100'/>\n"
            ."<input type='button' id='btn_none' value='None' style='width:100px;'"
            ." onclick='return btn_all_none(0)' tabindex='101' />\n"
            ."<input type='submit' id='btn_go' value='Go' style='width:100px;' tabindex='102' />\n"
            ."</p>\n"
            ."</form>\n"
            ."</body>\n"
            ."</html>";
        print $html;
        die;
    }

    protected function get_counts_for_tables($targetID)
    {
        $count = array();
        $sql = "SHOW TABLES";
        $rows = $this->get_rows_for_sql($sql);
        $existing = array();
        foreach ($rows as $row) {
            $existing[] = $row[0];
        }
  //    y($existing);die;
        $system_tables = explode(',', str_replace(' ', '', System::TABLES));
        foreach ($system_tables as $t) {
            if (in_array($t, $existing)) {
                $sql =
                 "SELECT\n"
                ."  COUNT(*)\n"
                ."FROM\n"
                ."  `".$t."`\n"
                ."WHERE\n"
                ."  `".($t=='system' ? 'ID' : 'systemID')."` IN(".$targetID.")";
                $count[$t] = $this->get_field_for_sql($sql);
            } else {
                $count[$t] = -1;
            }
        }
        return $count;
    }

    protected function get_counts_for_custom_tables($targetID)
    {
        $sql =
         "SELECT\n"
        ."  GROUP_CONCAT(`db_custom_tables`)\n"
        ."FROM\n"
        ."  `system`\n"
        ."WHERE\n"
        ."  `ID` IN(".$targetID.")";
        $result_arr = explode(',', $this->get_field_for_sql($sql));
        $out = array();
        foreach ($result_arr as $result) {
            $table = trim($result);
            if ($table) {
                $out[$table] = $table;
            }
        }
        return array_keys($out);
    }

    private function get_db_custom_tables()
    {
        global $system_vars;
        if ($this->_get_ID()==SYS_ID) {
            $this->_custom_tables_csv = trim($system_vars['db_custom_tables']);
            return;
        }
        $sql =
         "SELECT\n"
        ."  DISTINCT `db_custom_tables`\n"
        ."FROM\n"
        ."  `system`\n"
        ."WHERE\n"
        ."  `ID` IN(".$this->_get_ID().") AND\n"
        ."  `db_custom_tables`!=''";
        if (!$records = $this->get_records_for_sql($sql)) {
            return "";
        };
        $tables_arr = array();
        $tables_unique_arr = array();
        foreach ($records as $record) {
            $tables_arr = explode(',', trim($record['db_custom_tables']));
            foreach ($tables_arr as $table) {
                $tables_unique_arr[trim($table)] = true;
            }
        }
        $custom_tables_arr = array();
        foreach ($tables_unique_arr as $key => $val) {
            $custom_tables_arr[] = $key;
        }
        asort($custom_tables_arr);
        $this->_custom_tables_csv = implode(',', $custom_tables_arr);
    }

    private function get_custom_tables_sql($show_fields)
    {
        if ($this->_custom_tables_csv=='') {
            return;
        }
        $extra_delete_arr = array();
        $extra_select_arr = array();
        $custom_tables_arr = explode(",", $this->_custom_tables_csv);
        $ObjBackup = new Backup;
        $Obj_Table = new Table;
        foreach ($custom_tables_arr as $custom_table) {
            $custom_table = trim($custom_table);
            $hasSystemID = $Obj_Table->hasSystemID($custom_table);
            $extra_delete_arr[] =
            "DELETE FROM `".$custom_table."`"
             .($hasSystemID ?
             " WHERE `systemID` IN (".$this->_get_ID().");\n"
             : ";   # (This table doesn't have systemID column)\n"
             );
            $extra_select_arr[] =
            $ObjBackup->db_export_sql_query(
                "`".$custom_table."`",
                "SELECT * FROM `".$custom_table."`"
                .($hasSystemID ?
                str_repeat(' ', (30-strlen($custom_table)>0 ? 30-strlen($custom_table) : 1))
                ." WHERE `systemID` IN (".$this->_get_ID().")"
                : ""
                ),
                $show_fields
            );
        }
        $this->_custom_tables_delete_sql =
        "\n"
         ."# Custom Table".(count($extra_delete_arr)>1 ? 's' : '').":\n"
         .implode("", $extra_delete_arr)
         ."\n";
        $this->_custom_tables_select_sql =
        "\n"
         ."# Custom Table".(count($extra_delete_arr)>1 ? 's' : '').":\n"
         .implode("", $extra_select_arr)
         ."\n";
    }

    public function get_version()
    {
        return VERSION_SYSTEM_EXPORT;
    }
}
