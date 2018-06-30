<?php
define('VERSION_SYSTEM_HEALTH', '1.0.43');
define('HTACCESS_STACK', '(ajax|cron|css|facebook|img|java|lib|osd|qbwc|resource|sysjs)');
/*
Version History:
  1.0.43 (2015-02-01)
    1) System_Health::_get_config_libraries_array() bug fix for file name of /js/spectrum.min.js
       (was /js/spectrum.min.css)

  (Older version history in class.system_health.txt)
*/
class System_Health extends System
{

    public function draw($config_arr, $ID)
    {
        if (!is_array($config_arr)) {
            return "System details not known.";
        }
        return
             $this->_draw_system_table($config_arr, $ID)
            .$this->_draw_classes_table($config_arr)
            .$this->_draw_db_table($config_arr)
            .$this->_draw_libraries_table($config_arr)
            .$this->_draw_reports_table($config_arr);
    }

    private function _draw_button_code_documentation($config_arr, $ID)
    {
        $value = "";
        $cs_arr = $this->_get_config_classes_expected(System::get_item_version('classes_detail'));
        $classes_changed = 0;
        $libraries_checksum =       System::get_item_version('libraries_cs_actual');
        $reports_checksum =         System::get_item_version('reports_cs_actual');
        foreach ($config_arr as $config) {
            if ($config['category']=='classes' && $config['title']!='classes_cs_actual') {
                $content_arr = explode('|', $config['content']);
                $actual =       $content_arr[2];
                $title =        $config['title'];
                $expected_arr = (isset($cs_arr[$title]) ? $cs_arr[$title] : '0');
                if ($actual != $expected_arr['expected_cs']) {
                    $classes_changed++;
                }
            }
            if ($config['category']=='classes' && $config['title']=='classes_cs_actual') {
                $cs_actual = $config['content'];
            }
            if ($config['category']=='tables' && $config['title']=='db_cs_actual') {
                $db_checksum = $config['content'];
            }
            if ($config['category']=='config' && $config['title'] == 'libraries_detail') {
                $libraries_detail = $config['content'];
            }
            if ($config['category']=='config' && $config['title']=='db_version') {
                $db_version = $config['content'];
            }
        }
        $changed_files = array("codebase.php");
        $value =
         "Promote:\n"
        ."  ".pad('codebase.php', 52).System::get_item_version('codebase')."\n";
        if ($classes_changed) {
            foreach ($config_arr as $config) {
                if ($config['category']=='classes' && $config['title']=='classes_cs_actual') {
                    $value.=
                     "  classes/  (".$classes_changed." file".($classes_changed==1 ? '' : 's')." changed)\n";
                }
            }
            foreach ($config_arr as $config) {
                $changed = false;
                if ($config['category']=='classes' && $config['title']!='classes_cs_actual') {
                    $content_arr = explode('|', $config['content']);
                    $title =        $config['title'];
                    $title_arr =    explode('.', $title);
                    $class =        $title_arr[1];
                    $version =      $content_arr[0];
                    $size =         $content_arr[1];
                    $actual =       $content_arr[2];
                    $expected_arr = (isset($cs_arr[$title]) ? $cs_arr[$title] : "0|0|0");
                    $changed =      $actual != $expected_arr['expected_cs'];
                    if ($changed) {
                        $value.=
                             "    ".pad($title, 48)
                            .($version==$expected_arr['expected_version'] ? "* " : "  ")
                            .pad($version, 10)
                            ."CS:".pad($actual, 10)
                            .($version==$expected_arr['expected_version'] ?
                             " * PROBLEM - VERSION NUMBER DID NOT CHANGE"
                             : ""
                            )
                            ."\n";
                            $changed_files[] = "classes/".$title;
                    }
                }
            }
        }
        $libraries_array = $this->_get_config_libraries_array();
        foreach ($libraries_array as $entry) {
            $changed = false;
            foreach ($config_arr as $config) {
                $version =          "";
                $checksum =         "";
                foreach ($config_arr as $config) {
                    if ($config['category']=='config' && $config['title'] == $entry[1]."_version") {
                        $version = $config['content'];
                    }
                    if ($config['category']=='config' && $config['title'] == $entry[1]."_cs") {
                        $checksum = $config['content'];
                    }
                }
                $libraries_detail_arr = explode(',', $libraries_detail);
                $expected_v =  0;
                $expected_cs = -1;
                foreach ($libraries_detail_arr as $config) {
                    $content_arr =  explode('|', trim($config));
                    if ($content_arr[0] == $entry[0]) {
                        $expected_v =  $content_arr[1];
                        $expected_cs = $content_arr[2];
                        break;
                    }
                }
            }
            if ($checksum != $expected_cs && trim($entry[0], '/')!='codebase.php') {
                $value.=
                     pad("  ".trim($entry[0], '/'), 54)
                    .pad($version, 10)
                    ."CS:".pad($checksum, 10)
                    ."\n";
                    $changed_files[] = trim($entry[0], '/');
            }
        }
        $changed_files_list = "";
        $ObjFS = new FileSystem;
        foreach ($changed_files as $changed_file) {
            switch ($changed_file){
                case 'codebase.php':
                    $history_version_arr = explode(
                        ' ',
                        System::get_item_version('codebase').' ('.date('Y-m-d', time()).')'
                    );
                    $changed_files_list.=
                         "  "
                        .pad($changed_file, 95)
                        .pad($history_version_arr[0], 10)
                        .$history_version_arr[1]
                        ."\n"
                        ."    1) Updated version information\n";
                    break;
                case 'images/icons.gif':
                case 'images/icons-big.gif':
                case 'images/labels.gif':
                  // Do Nothing
                    break;
                default:
                    if (substr($changed_file, 0, 4)==='www/') {
                        $path = "./".substr($changed_file, 4);
                        if (!file_exists($path)) {
                            $path = "../".substr($changed_file, 4);
                        }
                    } else {
                        $path = SYS_SHARED.$changed_file;
                    }
                    $history = $ObjFS->get_file_changes($path);
                    $history_arr =          explode("\n", trim($history));
                    $history_version_arr =  explode(' ', trim(array_shift($history_arr)));
                    $history =              implode("\n", $history_arr);
                    $changed_files_list.=
                        "  "
                        .pad(trim($changed_file), 95)
                        .pad($history_version_arr[0], 10)
                        .$history_version_arr[1]
                        ."\n"
                        .$history
                        ."\n";
                    break;
            }
        }
        $value =
             System::get_item_version('build')." (".date('Y-m-d', time()).")\n"
            ."Summary:\n"
            ."  (Provide top-level summary here)\n"
            ."\n"
            ."Final Checksums:\n"
            ."  Classes     CS:".$cs_actual."\n"
            ."  Database    CS:".$db_checksum."\n"
            ."  Libraries   CS:".$libraries_checksum."\n"
            ."  Reports     CS:".$reports_checksum."\n"
            ."\n"
            ."Code Changes:\n"
            .$changed_files_list
            ."\n"
            .$db_version.".sql\n"
            ."  1) Set version information\n\n"
            .$value;
        return
             draw_form_field($ID.'_code_build_info', $value, 'hidden')
            ."<input class='formButton fr txt_c' type=\"button\" value=\"Code Header\""
            ." onclick=\"copy_clip(geid_val('".$ID."_code_build_info'));"
            ."alert('Code Header info copied to clipboard.\\n"
            ."Steps remaining:\\n"
            ."  1) Provide top-level summary\\n"
            ."  2) Insert SQL changes where indicated')\" />";
    }

    private function _draw_button_sql_build_info($config_arr, $ID)
    {
        global $db;
        $db_version =           "";
        $db_checksum =          "";
        $db_detail_arr =        array();
        $class_checksum =       "";
        $class_detail_arr =     array();
        foreach ($config_arr as $config) {
            if ($config['category']=='classes' && $config['title']=='classes_cs_actual') {
                $class_checksum = $config['content'];
                break;
            }
        }
        foreach ($config_arr as $config) {
            if ($config['category']=='classes' && $config['title']!='classes_cs_actual') {
                $title_arr =  explode(' ', $config['title']);
                $title =      $title_arr[0];
                $detail =     str_replace(",", "", $config['content']);
                $class_detail_arr[] = $title."|".$detail;
            }
        }
        $reports_checksum =       11;
        $reports_detail_arr =     array();
        foreach ($config_arr as $config) {
            if ($config['category']=='reports' && $config['title']=='reports_cs_actual') {
                $reports_checksum = $config['content'];
                break;
            }
        }
        foreach ($config_arr as $config) {
            if (
                $config['category']=='reports' &&
                $config['title']!='reports_cs_actual'
            ) {
                $title =      $config['title'];
                if (
                    substr($title, 0, 4)!='cus_' &&
                    substr($title, 0, 7)!='module.' &&
                    substr($title, 0, 12)!='custom_treb_'
                ) {
                    $detail =     str_replace(",", "", $config['content']);
                    $reports_detail_arr[] = $title."|".$detail;
                }
            }
        }
        foreach ($config_arr as $config) {
            if ($config['category']=='config' && $config['title']=='db_version') {
                $db_version = $config['content'];
            }
            if ($config['category']=='tables' && $config['title']=='db_cs_actual') {
                $db_checksum = $config['content'];
            }
        }
        $libraries_checksum =       System::get_item_version('libraries_cs_actual');
        $libraries_detail_arr =     $this->_get_config_libraries_detail_array();
        foreach ($config_arr as $config) {
            if ($config['category']=='tables') {
                if (
                $config['title']=='db_cs_actual' ||
                substr($config['title'], 0, 16)=='checksum_module_' ||
                substr($config['title'], 0, 4)=='cus_' ||
                substr($config['title'], 0, 7)=='module_') {
                    // Skip
                } else {
                    $checksum =   $config['content'];
                    $title =      $config['title'];
                    $title_arr = explode(" ", $title);
                    $title =    $title_arr[0];

                    if (substr($title, 0, strlen($db."_media."))==$db."_media.") {
                        $title = "DB_media.".substr($title, strlen($db."_media."));
                    }
                    $title = htmlentities($title);
                    $db_detail_arr[] = $title."|".$checksum;
                }
            }
        }

        $value =
             "UPDATE `system` SET `system`.`db_version` =  '".$db_version."';\n"
            ."UPDATE `system` SET `system`.`db_cs_target` = '".$db_checksum."';\n"

            .(count($class_detail_arr) ?
             "UPDATE `system` SET `system`.`db_detail` =\n  CONCAT(\n"
            ."    '".wordwrap(implode(', ', $db_detail_arr), 500, " ',\n    '")."');\n"
            ."UPDATE `system` SET `system`.`classes_cs_target` = '".$class_checksum."';\n"
            ."UPDATE `system` SET `system`.`classes_detail` =\n  CONCAT(\n"
            ."    '".wordwrap(implode(', ', $class_detail_arr), 500, " ',\n    '")."');\n"
            ."UPDATE `system` SET `system`.`libraries_cs_target` = '".$libraries_checksum."';\n"
            ."UPDATE `system` SET `system`.`libraries_detail` =\n  CONCAT(\n"
            ."    '".wordwrap(implode(', ', $libraries_detail_arr), 500, " ',\n    '")."');\n"
            ."UPDATE `system` SET `system`.`reports_cs_target` = '".$reports_checksum."';\n"
            ."UPDATE `system` SET `system`.`reports_detail` =\n  CONCAT(\n"
            ."    '".wordwrap(implode(', ', $reports_detail_arr), 500, " ',\n    '")."');\n"
             : ""
            );

        return
            draw_form_field($ID.'_sql_build_info', $value, 'hidden')
            ."<input class='formButton fr txt_c' type=\"button\" value=\"SQL Build\""
            ." onclick=\"copy_clip(geid_val('".$ID."_sql_build_info'));alert('SQL Build info copied to clipboard')\" />"
        ;
    }

    private function _draw_db_table($config_arr)
    {
        $count = 0;
        foreach ($config_arr as $config) {
            if ($config['category']=='tables' && $config['title']!='db_cs_actual') {
                $count++;
            }
        }
        $out =
             "<a id='DB'></a><b>Database Tables (".$count.")</b>\n"
            ."<table class='report' cellpadding='0' cellspacing='0'>\n"
            ."  <tr class='head'>\n"
            ."    <th style='width:290px'>Table</th>\n"
            ."    <th class='txt_r' style='width:75px;'>Checksum</th>\n"
            ."  </tr>\n";
        $checksum_final =   "";
        $db_name =          "";

        $url = "";
        foreach ($config_arr as $config) {
            switch ($config['category']) {
                case 'config':
                    if ($config['title'] =='db_cs_target') {
                        $db_cs_target = trim($config['content']);
                    }
                    if ($config['title'] =='URL') {
                        $url = trim($config['content']);
                    }
                    if ($config['title']=='db_name') {
                        $db_name = trim($config['content']);
                    }
                    break;
                case 'tables':
                    if ($config['title']=='db_cs_actual') {
                        $checksum_final = trim($config['content']);
                    }
                    break;
            }
        }
        $final_cs_fail = $db_cs_target!=$checksum_final;
        foreach ($config_arr as $config) {
            if ($config['category']=='tables' && $config['title']!='db_cs_actual') {
                if (substr($config['title'], 0, 16)=='checksum_module_') {
                    $out.= $this->_draw_db_row_checksum_module($config);
                } else {
                    $out.= $this->_draw_db_row_checksum_table($config, $url, $db_name, $final_cs_fail);
                }
            }
        }
        foreach ($config_arr as $config) {
            if ($config['category']=='tables' && $config['title']=='db_cs_actual') {
                $out.= $this->_draw_db_row_checksum_final($config, $db_cs_target);
            }
        }
        $out.= "</table><br />";
        return $out;
    }

    private function _draw_db_row_checksum_final($config, $checksum_final)
    {
        return
             "  <tr>\n"
            ."    <th>System Target Checksum:<br />System Actual Checksum:</th>\n"
            ."    <td class='cs'><b>".$config['content']."<br />\n"
            ."<span style='color:"
            .($checksum_final==$config['content'] ? '#008000' : '#ff0000')
            ."'>".$checksum_final."</span></b></td>\n"
            ."  </tr>\n";
    }
    private function _draw_db_row_checksum_module($config)
    {
        $name = substr($config['title'], 16);
        return
             "  <tr class='module'>\n"
            ."    <th>Module \"".$name."\" Checksum:</th>\n"
            ."    <td class='cs'>".$config['content']."</td>\n"
            ."  </tr>\n";

    }
    private function _draw_db_row_checksum_table($config, $url, $db_name, $final_cs_fail)
    {
        $custom =   substr($config['title'], 0, 4)=='cus_';
        $module =   substr($config['title'], 0, 7)=='module_';
        $config['title'] =    str_replace('- Real CS', '-<br />Real CS', $config['title']);
        $table =    preg_replace('/ ([^\n])*/', '', $config['title']);
        $table_arr = explode(" ", $table);
        $cs_table =  $table_arr[0];
        if ($db_name && substr($cs_table, 0, strlen($db_name))==$db_name) {
            $cs_table =    "DB".substr($cs_table, strlen($db_name));
        }
        $suffix =   ($config['title']==$table ? "" : substr($config['title'], strlen($table)));
        $cs_arr =   $this->_get_config_tables_expected(System::get_item_version('db_detail'));

        $expected_cs =  (isset($cs_arr[$cs_table]) ? $cs_arr[$cs_table] : '2342424');
        $customised =   (strpos($config['title'], "Real CS"));
        $correct =      ($config['content'] == $expected_cs && !$custom && !$module);
        $incorrect =    ($config['content'] != $expected_cs && !$custom && !$module && $final_cs_fail);

        return
             "<tr"
            .($customised && $correct ?
                " class='warning' title='This table has been customised,\nbut still meets the checksum criteria'"
             :
                ""
            )
            .($customised && $incorrect ?
                 " class='bad' title='This table has been customised\nand in addition it fails the checksum criteria -"
                ." expected CS was ".$expected_cs."'"
             :
                ""
            )
            .(!$customised && $correct ?
                " class='unchanged' title='This table has not changed'"
             :
                ""
            )
            .(!$customised && $incorrect && $expected_cs===false ?
                " class='bad' title='This table was added resulting in changes to final checksum'"
             :
                ""
            )
            .(!$customised && $incorrect && $expected_cs!==false ?
                 " class='bad' title='This table was altered resulting in changes to final checksum -"
                ." expected CS was ".$expected_cs."'"
             :
                ""
            )
            .(!$customised && $custom ?
                " class='ignored' title='Custom tables do not affect the final checksum'"
             :
                ""
            )
            .(!$customised && $module ?
                 " class='module' title='Module tables do not affect the final checksum"
                ." but do generate their own combined checksums'"
             :
                ""
            )
            .">\n"
            ."  <th>"
            ."<span class='link' title=\"Click to see table create SQL\""
            ." onclick=\"popup_table_structure('".$url."','".$table."');\""
            .">".$table."</span>"
            .$suffix
            ."</th>\n"
            ."  <td class='cs'>"
            .$config['content']
            ."</td>\n"
            ."</tr>\n";
    }

    private function _draw_classes_table($config_arr)
    {
        global $system_vars;
        $classes_cs_actual =   false;
        foreach ($config_arr as $config) {
            if ($config['category']=='classes' && $config['title'] == 'classes_cs_actual') {
                $classes_cs_actual = $config['content'];
                break;
            }
        }
        if (!$classes_cs_actual) {
            return "";
        }
        $count = 0;
        foreach ($config_arr as $config) {
            if ($config['category']=='classes' && $config['title']!='classes_cs_actual') {
                $count++;
            }
        }
        $cs_arr = $this->_get_config_classes_expected(System::get_item_version('classes_detail'));
        $out =
             "<a id='Classes'></a><b>Classes (".$count.")</b>\n"
            ."<table class='report' cellpadding='0' cellspacing='0'>\n"
            ."  <tr class='head'>\n"
            ."    <th style='width:150px'>Class File</th>\n"
            ."    <th style='width:35px'>Ver</th>\n"
            ."    <th style='width:50px'>Size</th>\n"
            ."    <th style='width:80px'>Checksum</th>\n"
            ."  </tr>\n";
        foreach ($config_arr as $config) {
            if ($config['category']=='classes' && $config['title']!='classes_cs_actual') {
            // Regular entries
                $title =        $config['title'];
                $class_arr =    explode('.', $title);
                $content_arr =  explode('|', $config['content']);
                $title =        $config['title'];
                $title_arr =    explode('.', $title);
                $class =        $title_arr[1];
                $version =      $content_arr[0];
                $size =         $content_arr[1];
                $actual =       $content_arr[2];
                $expected_arr = (isset($cs_arr[$title]) ?
                   $cs_arr[$title]
                 :
                    array(
                        'expected_cs'=>'0',
                        'expected_size'=>'0',
                        'expected_version'=>'0'
                    )
                );
                $correct =      $actual == $expected_arr['expected_cs'];
                if ($actual == $expected_arr['expected_cs']) {
                    $out.= "  <tr class='unchanged' title='No changes'>\n";
                } elseif ($expected_arr['expected_version']=='0') {
                    $out.= "  <tr class='good' title='New addition for this build'>\n";
                } elseif ($version!=$expected_arr['expected_version']) {
                    $out.=
                         "  <tr class='good' title='Changed, new version number supplied -\n"
                        ."Previous version:".$expected_arr['expected_version'].","
                        ." size:".number_format($expected_arr['expected_size']).","
                        ." checksum:".$expected_arr['expected_cs']
                        ."'"
                        .">\n";
                } else {
                    $out.=
                         "  <tr class='bad' title='Changed but NO new version number supplied -\n"
                        ."Previous version:".$expected_arr['expected_version'].","
                        ." size:".number_format($expected_arr['expected_size']).","
                        ." checksum:".$expected_arr['expected_cs']
                        ."'"
                        .">\n";
                }
                $out.=
                    "    <th>".$class."</th>\n"
                    ."    <td>".$version."</td>\n"
                    ."    <td class='num'>".$size."</td>\n"
                    ."    <td class='cs'>".$actual."</td>\n"
                    ."  </tr>\n";
            }
            if ($config['category']=='classes' && $config['title']=='classes_cs_actual') {
            // Combined entry
                $title =    $config['title'];
                $class_arr = explode('.', $title);
                $expected = trim($system_vars['classes_cs_target']);
                $actual =   trim($config['content']);
                $correct =  ($actual == $expected);
                $result =   $expected."<br />".$actual;
                $out.=
                     "  <tr class='".($correct ? 'good' : 'bad')."'>\n"
                    ."    <th colspan='3'>Classes Target Checksum:<br />\nClasses Actual Checksum:</th>\n"
                    ."    <td class='cs'>".$result."</td>\n"
                    ."  </tr>\n";
            }
        }
        $out.=
             "</table>\n"
            ."<br />\n";
        return $out;
    }

    private function _draw_libraries_table($config_arr)
    {
        $libraries = $this->_get_config_libraries_array();
        $out =
             "<div><a id='Libraries'></a><b>Libraries</b></div>\n"
            ."<table class='report' cellpadding='0' cellspacing='0'>\n"
            ."  <tr class='head'>\n"
            ."    <th style='width:232px'>File</th>\n"
            ."    <th style='width:45px'>Ver</th>\n"
            ."    <th style='width:80px'>Checksum</th>\n"
            ."  </tr>\n";
        foreach ($libraries as $library) {
            $out.= $this->_draw_libraries_table_row($config_arr, $library);
        }
        $out.=
             "</table>\n"
            ."<br />\n";
        return $out;
    }

    private function _draw_libraries_table_row($config_arr, $entry)
    {
        $libraries_detail = '';
        foreach ($config_arr as $config) {
            if ($config['category']=='config' && $config['title'] == 'libraries_detail') {
                $libraries_detail = $config['content'];
                break;
            }
        }
        $version =          "";
        $checksum =         "";
        foreach ($config_arr as $config) {
            if ($config['category']=='config' && $config['title'] == $entry[1]."_version") {
                $version = $config['content'];
            }
            if ($config['category']=='config' && $config['title'] == $entry[1]."_cs") {
                $checksum = $config['content'];
            }
        }
        $tr = "  <tr class='good' title='New addition for this build'>\n";
        if ($libraries_detail) {
            $libraries_detail_arr = explode(',', $libraries_detail);
            $expected_v =  0;
            $expected_cs = -1;
            foreach ($libraries_detail_arr as $config) {
                $content_arr =  explode('|', trim($config));
                if ($content_arr[0] == $entry[0]) {
                    $expected_v =  $content_arr[1];
                    $expected_cs = $content_arr[2];
                    break;
                }
            }
            if (trim($entry[0], '/')=='codebase.php' && $version!=$expected_v) {
                $tr =
                     "  <tr class='good' title=\"Version has changed but the checksum cannot measured"
                    ." without affecting the result - it's quantum.\">\n";
                $checksum = "Quantum";
            } elseif (trim($entry[0], '/')!='codebase.php' && $checksum=='') {
                $tr = "  <tr class='warning' title='The file checksum is unmonitored in this build'>\n";
            } elseif ($checksum == $expected_cs) {
                $tr = "  <tr class='unchanged' title='No changes'>\n";
            } elseif ($expected_cs=='-1') {
                $tr = "  <tr class='good' title='New addition for this build'>\n";
            } elseif ($version!=$expected_v) {
                $tr =
                     "  <tr class='good' title='Changed, new version number supplied -\n"
                    ."Previous version:".$expected_v.","
                    ." checksum:".$expected_cs
                    ."'"
                    .">\n";
            } elseif ($version=='') {
                $tr =
                     "  <tr class='good' title='Changed, unversioned file with new checksum -\n"
                    ." checksum:".$expected_cs
                    ."'"
                    .">\n";
            } else {
                $tr =
                     "  <tr class='bad' title='Changed but NO new version number supplied -\n"
                    ."Previous version:".$expected_v.","
                    ." checksum:".$expected_cs
                    ."'"
                    .">\n";
            }
        }
        return
         $tr
            ."  <th>".trim($entry[0], '/')."</th>\n"
            ."  <td>".$version."</td>\n"
            ."  <td class='cs'>".$checksum."</td>\n"
            ."</tr>\n";
    }

    private function _draw_reports_table($config_arr)
    {
        global $system_vars;
        $reports_cs_actual =   false;
        foreach ($config_arr as $config) {
            if ($config['category']=='reports' && $config['title'] == 'reports_cs_actual') {
                $reports_cs_actual = $config['content'];
                break;
            }
        }
        if (!$reports_cs_actual) {
            return "";
        }
        $count = 0;
        foreach ($config_arr as $config) {
            if ($config['category']=='reports' && $config['title']!='reports_cs_actual') {
                $count++;
            }
        }
        $cs_arr = $this->_get_config_reports_expected(System::get_item_version('reports_detail'));
        $out =
             "<a id='Reports'></a><b>Reports (".$count.")</b>\n"
            ."<table class='report' cellpadding='0' cellspacing='0'>\n"
            ."  <tr class='head'>\n"
            ."    <th style='width:285px'>Report</th>\n"
            ."    <th style='width:80px'>Checksum</th>\n"
            ."  </tr>\n";
        foreach ($config_arr as $config) {
            if ($config['category']=='reports' && $config['title']!='reports_cs_actual') {
          // Regular entries
                $title =        $config['title'];
                $content_arr =  explode('|', $config['content']);
                $report =       $config['title'];
                $ID =           $content_arr[0];
                $final =        $content_arr[5];
                $expected_arr = (isset($cs_arr[$ID]) ?
                    $cs_arr[$ID]
                 :
                    array('expected_final'=>'0')
                );
                $correct =      $final == $expected_arr['expected_final'];
                if (substr($title, 0, 4)=='cus_' || substr($title, 0, 12)=='custom_treb_') {
                    $out.= "  <tr class='ignored' title='Custom reports do not affect the final checksum'>\n";
                } elseif (substr($title, 0, 7)=='module.') {
                    $out.= "  <tr class='module' title='Module reports do not affect the final checksum'>\n";
                } elseif ($expected_arr['expected_final']=='0') {
                    $out.= "  <tr class='good' title='New addition for this build'>\n";
                } elseif ($final == $expected_arr['expected_final']) {
                    $out.= "  <tr class='unchanged' title='No changes'>\n";
                } else {
                    $out.=
                     "  <tr class='good' title='Changed\n"
                    ."Previous checksum:".$expected_arr['expected_final']
                    ."'"
                    .">\n";
                }
                $out.=
                     "    <th><span class='link' title=\"Click to export this report\""
                    ." onclick=\"return export_sql('report',".$ID.")\">".$report."</span></th>\n"
                    ."    <td class='cs'>".$final."</td>\n"
                    ."  </tr>\n";
            }
            if ($config['category']=='reports' && $config['title']=='reports_cs_actual') {
            // Combined entry
                $title =    $config['title'];
                $expected = trim($system_vars['reports_cs_target']);
                $final =   trim($config['content']);
                $correct =  ($final == $expected);
                $result =   $expected."<br />".$final;

                $out.=
                 "  <tr class='".($correct ? 'good' : 'bad')."'>\n"
                ."    <th>Reports Target Checksum:<br />\nReports Actual Checksum:</th>\n"
                ."    <td class='cs'>".$result."</td>\n"
                ."  </tr>\n";
            }
        }
        $out.=
         "</table>\n";
        return $out;
    }

    private function _draw_system_table($config_arr, $ID)
    {
        return
             "<div class='fl'><a id='Summary'></a><b>Summary</b></div>\n"
            .$this->_draw_button_sql_build_info($config_arr, $ID)
            .$this->_draw_button_code_documentation($config_arr, $ID)
            ."<div class='clr_b'></div>"
            ."<table class='report' cellpadding='0' cellspacing='0'>\n"
            ."  <tr class='head'>\n"
            ."    <th style='width:120px'>Parameter</th>\n"
            ."    <th style='width:245px'>Value</th>\n"
            ."  </tr>\n"
            .$this->_draw_system_table_row($config_arr, "URL", 'URL')
            .$this->_draw_system_table_row($config_arr, "Title", 'title')
            .$this->_draw_system_table_row($config_arr, "Build Version", 'build_version')
            .$this->_draw_system_table_row($config_arr, "Classes Status", 'classes_cs_status')
            .$this->_draw_system_table_row($config_arr, "Classes Checksum", 'classes_cs_target')
            .$this->_draw_system_table_row($config_arr, "DB Status", 'db_cs_status')
            .$this->_draw_system_table_row($config_arr, "DB Checksum", 'db_cs_target')
            .$this->_draw_system_table_row($config_arr, "Libraries Status", 'libraries_cs_status')
            .$this->_draw_system_table_row($config_arr, "Libraries Checksum", 'libraries_cs_target')
            .$this->_draw_system_table_row($config_arr, "Reports Status", 'reports_cs_status')
            .$this->_draw_system_table_row($config_arr, "Reports Checksum", 'reports_cs_target')
            .$this->_draw_system_table_row($config_arr, "Akismet Key", 'akismet_key_status')
            .$this->_draw_system_table_row($config_arr, "Bugtracker", 'bugtracker_status')
            .$this->_draw_system_table_row($config_arr, "Google Maps Key", 'google_key_status')
            .$this->_draw_system_table_row($config_arr, "Heartbeat Status", 'heartbeat_status')
            .$this->_draw_system_table_row($config_arr, "config.php Status", 'config_status')
            .$this->_draw_system_table_row($config_arr, "htaccess Status", 'htaccess_status')
            .$this->_draw_system_table_row($config_arr, "Last User Access", 'last_loggedin_access')
            ."</table>\n"
            ."<br />\n"
            ."<div><a id='ServerConfig'></a><b>Server Config</b></div> \n"
            ."<table class='report' cellpadding='0' cellspacing='0'>\n"
            ."  <tr class='head'>\n"
            ."    <th style='width:120px'>Parameter</th>\n"
            ."    <th style='width:245px'>Value</th>\n"
            ."  </tr>\n"
            .$this->_draw_system_table_row($config_arr, 'Web Server', 'http_software')
            .$this->_draw_system_table_row($config_arr, 'Server Name', 'server_name')
            .$this->_draw_system_table_row($config_arr, 'PHP Version', 'php_version')
            .$this->_draw_system_table_row($config_arr, 'MySQL Version', 'mysql_version')
            ."</table>\n"
            ."<br />\n"
            ."<div><a id='SiteConfig'></a><b>Site Config</b></div>\n"
            ."<table class='report' cellpadding='0' cellspacing='0'>\n"
            ."  <tr class='head'>\n"
            ."    <th style='width:120px'>Parameter</th>\n"
            ."    <th style='width:245px'>Value</th>\n"
            ."  </tr>\n"
            .$this->_draw_system_table_row($config_arr, 'System ID', 'systemID')
            .$this->_draw_system_table_row($config_arr, 'System Family', 'system_family')
            .$this->_draw_system_table_row($config_arr, 'Codebase Version', 'codebase_version')
            .$this->_draw_system_table_row($config_arr, 'DB Version', 'db_version')
            .$this->_draw_system_table_row($config_arr, 'System Version', 'system_version')
            .$this->_draw_system_table_row($config_arr, 'Custom code', 'custom_version')
            .$this->_draw_system_table_row($config_arr, 'DB Name', 'db_name')
            .$this->_draw_system_table_row($config_arr, 'Document Root', 'document_root')
            ."</table>\n"
            ."<br />\n";
    }

    private function _draw_system_table_row($config_arr, $label, $entry)
    {
        $out = '';
        $classes_cs_actual =        "";
        $libraries_cs_actual =       "";
        $system_checksum_final =    "";
        foreach ($config_arr as $config) {
            if ($config['category']=='classes' && $config['title'] == 'classes_cs_actual') {
                $classes_cs_actual = $config['content'];
            }
            if ($config['category']=='tables' && $config['title'] == 'db_cs_actual') {
                $system_checksum_final = $config['content'];
            }
            if ($config['category']=='config' && $config['title'] == 'libraries_cs_actual') {
                $libraries_cs_actual = $config['content'];
            }
            if ($config['category']=='reports' && $config['title'] == 'reports_cs_actual') {
                $reports_cs_actual = $config['content'];
            }
        }
        foreach ($config_arr as $config) {
            $category =   trim($config['category']);
            $title =      trim($config['title']);
            $content =    trim($config['content']);
            if ($category == "config") {
                switch ($title) {
                    case $entry:
                        switch ($entry) {
                            case "config_status":
                                $out.= ($content=='Pass' ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                    "<tr class='bad'><th>".$label."</th><td>".$content."</td></tr>\n"
                                );
                                break;
                            case "classes_cs_target":
                                $out.= ($content==$classes_cs_actual ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                     "<tr class='bad'><th>".$label."</th>"
                                    ."<td><span title='Actual value'>".$classes_cs_actual."</span>"
                                    ." <span style='color:#ff8000' title='Expected value'>(Exp ".$content.")</span>"
                                    ."</td></tr>\n"
                                );
                                break;
                            case "classes_cs_status":
                                $out.= ($content=='Pass' ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                     "<tr class='bad'><th><a href='#Classes'>".$label."</a></th>"
                                    ."<td>".$content."</td></tr>\n"
                                );
                                break;
                            case "db_cs_target":
                                $out.= ($content==$system_checksum_final ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                     "<tr class='bad'><th>".$label."</th>"
                                    ."<td><span title='Actual value'>".$system_checksum_final."</span>"
                                    ." <span style='color:#ff8000' title='Expected value'>(Exp ".$content.")</span>"
                                    ."</td></tr>\n"
                                );
                                break;
                            case "db_cs_status":
                                $out.= ($content=='Pass' ?
                                     "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                      "<tr class='bad'><th><a href='#DB'>".$label."</a></th>"
                                     ."<td>".$content."</td></tr>\n"
                                );
                                break;
                            case "heartbeat_status":
                                $out.= ($content=='Pass' ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                    "<tr class='bad'><th>".$label."</th><td>".$content."</td></tr>\n"
                                );
                                break;
                            case "htaccess_status":
                                $out.= ($content=='Pass' ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                    "<tr class='bad'><th>".$label."</th><td>".$content."</td></tr>\n"
                                );
                                break;
                            case "libraries_cs_status":
                                $out.= ($content=='Pass' ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                     "<tr class='bad'><th><a href='#Libraries'>".$label."</a></th>"
                                    ."<td>".$content."</td></tr>\n"
                                );
                                break;
                            case "libraries_cs_target":
                                $out.= ($content==$libraries_cs_actual ?
                                   "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                    "<tr class='bad'><th>".$label."</th>"
                                   ."<td><span title='Actual value'>".$libraries_cs_actual."</span>"
                                   ." <span style='color:#ff8000' title='Expected value'>(Exp ".$content.")</span></td>"
                                   ."</tr>\n"
                                );
                                break;
                            case "reports_cs_status":
                                $out.= ($content=='Pass' ?
                                    "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                     "<tr class='bad'><th><a href='#Reports'>".$label."</a></th>"
                                    ."<td>".$content."</td></tr>\n"
                                );
                                break;
                            case "reports_cs_target":
                                $out.= ($content==$reports_cs_actual ?
                                   "<tr class='good'><th>".$label."</th><td>".$content."</td></tr>\n"
                                 :
                                    "<tr class='bad'><th>".$label."</th>"
                                   ."<td><span title='Actual value'>".$reports_cs_actual."</span>"
                                   ." <span style='color:#ff8000' title='Expected value'>(Exp ".$content.")</span></td>"
                                   ."</tr>\n"
                                );
                                break;
                            case "akismet_key_status":
                                $out.=
                                     "<tr class='".($content=='Pass' ? 'good' : 'bad')."'>"
                                    ."<th>".$label."</th><td>".$content."</td></tr>\n";
                                break;
                            case "bugtracker_status":
                                $out.=
                                     "<tr class='".($content=='Pass' ? 'good' : 'bad')."'>"
                                    ."<th>".$label."</th><td>".$content."</td></tr>\n";
                                break;
                            case "google_key_status":
                                $out.=
                                     "<tr class='".($content=='Pass' ? 'good' : 'bad')."'>"
                                    ."<th>".$label."</th><td>".$content."</td></tr>\n";
                                break;
                            case "build_version":
                                if ($content) {
                                    $out.=
                                         "<tr><th>".$label."</th>"
                                        ."<td><a href=\"#\" onclick=\"version('".$content."');return false;\">"
                                        .$content."</a></td></tr>\n";
                                } else {
                                    $out.=
                                        "<tr class='bad'><th>".$label."</th><td>ID not in database</td></tr>\n";
                                }
                                break;
                            case "URL":
                                $out.=
                                     "<tr><th>".$label."</th>"
                                    ."<td><a href=\"".$content."\" rel=\"external\">".$content."</a></td></tr>\n";
                                break;
                            default:
                                $out.=
                                    "<tr><th>".$label."</th><td>".$content."</td></tr>\n";
                                break;
                        }
                        break;
                }
            }
        }
        return $out;
    }

    public function get_config()
    {
        $out = array();
        $this->_get_config_libraries($out);
        $this->_get_config_classes($out);
        $this->_get_config_reports($out);
        $this->_get_config_tables($out);
        $array_to_insert = array();
        $this->_get_config_system($array_to_insert, $out);
        return array_merge($array_to_insert, $out);
    }


    protected function _get_config_classes(&$out)
    {
        $all_items = safe_glob(SYS_CLASSES.'*.php');
        $entries = array();
        $final = "";
        foreach ($all_items as $item) {
            $entries[] = $item;
        }
        foreach ($entries as $entry) {
            $ext_arr =    explode(".", $entry);
            if (count($ext_arr)==3) {
                $ext =              array_pop($ext_arr);
                $class =            array_pop($ext_arr);
                $Obj =              new $class;
                $version =          $Obj->get_version();
                $file =             file_get_contents(SYS_CLASSES.$entry);
                $size =             number_format(strlen($file));

                $file_normalised =  preg_replace("/\r\n/", "\n", $file);
                $crc32 =            dechex(crc32($file_normalised));

                $out[] =
                array(
                'category'=>'classes',
                'title'=>$entry,
                'content'=>$version."|".$size."|".$crc32
                );
                $final.=$entry."|".$crc32;
            }
        }
        $out[] =
        array(
        'category'=>'classes',
        'title'=>'classes_cs_actual',
        'content'=>dechex(crc32($final))
        );
    }
    private function _get_config_classes_expected($csv)
    {
        $_cs_all =  explode(', ', $csv);
        $cs_arr =   array();
        foreach ($_cs_all as $_cs_entry) {
            $_cs_entry_arr =      explode('|', $_cs_entry);
            $_title =               $_cs_entry_arr[0];
            $_expected_version =    $_cs_entry_arr[1];
            $_expected_size =       $_cs_entry_arr[2];
            $_expected_cs =         $_cs_entry_arr[3];
            $cs_arr[$_title] =
            array(
            'expected_version' =>   $_expected_version,
            'expected_size' =>      $_expected_size,
            'expected_cs' =>        $_expected_cs
            );
        }
        return $cs_arr;
    }

    private function _get_config_libraries(&$out)
    {
        $entries =
        array(
            array('codebase_cs',                    ''),
            array('codebase_version',               System::get_item_version('codebase')),
            array('css_cs',                         System::get_item_version('css_cs')),
            array('css_version',                    System::get_item_version('css')),
            array('css_breadcrumbs_cs',             System::get_item_version('css_breadcrumbs_cs')),
            array('css_breadcrumbs_version',        System::get_item_version('css_breadcrumbs')),
            array('css_community_cs',               System::get_item_version('css_community_cs')),
            array('css_community_version',          System::get_item_version('css_community')),
            array('css_labels_cs',                  System::get_item_version('css_labels_cs')),
            array('css_labels_version',             System::get_item_version('css_labels')),
            array('css_pie_cs',                     System::get_item_version('css_pie_cs')),
            array('css_pie_version',                System::get_item_version('css_pie')),
            array('css_spectrum_cs',                System::get_item_version('css_spectrum_cs')),
            array('css_spectrum_version',           System::get_item_version('css_spectrum')),
            array('db_connect_cs',                  System::get_item_version('db_connect_cs')),
            array('db_connect_version',             System::get_item_version('db_connect')),
            array('fedex_rate_cs',                  System::get_item_version('fedex_rate_cs')),
            array('fedex_rate_version',             System::get_item_version('fedex_rate')),
            array('functions_cs',                   System::get_item_version('functions_cs')),
            array('functions_version',              System::get_item_version('functions')),
            array('getid3_cs',                      System::get_item_version('getid3_cs')),
            array('getid3_version',                 System::get_item_version('getid3')),
            array('icons_cs',                       System::get_item_version('icons')),
            array('icons_version',                  ''),
            array('icons_big_cs',                   System::get_item_version('icons_big')),
            array('icons_big_version',              ''),
            array('img_cs',                         System::get_item_version('img_cs')),
            array('img_version',                    System::get_item_version('img')),
            array('js_cke_cs',                      System::get_item_version('js_cke_cs')),
            array('js_cke_version',                 System::get_item_version('js_cke')),
            array('js_cke_config_cs',               System::get_item_version('js_cke_config_cs')),
            array('js_cke_config_version',          System::get_item_version('js_cke_config')),
            array('js_cke_plugin_audio_cs',         System::get_item_version('js_cke_plugin_audio_cs')),
            array('js_cke_plugin_audio_version',    System::get_item_version('js_cke_plugin_audio')),
            array('js_cke_plugin_ecl_cs',           System::get_item_version('js_cke_plugin_ecl_cs')),
            array('js_cke_plugin_ecl_version',      System::get_item_version('js_cke_plugin_ecl')),
            array('js_cke_plugin_more_cs',          System::get_item_version('js_cke_plugin_more_cs')),
            array('js_cke_plugin_more_version',     System::get_item_version('js_cke_plugin_more')),
            array('js_cke_plugin_video_cs',         System::get_item_version('js_cke_plugin_video_cs')),
            array('js_cke_plugin_video_version',    System::get_item_version('js_cke_plugin_video')),
            array('js_cke_plugin_youtube_cs',       System::get_item_version('js_cke_plugin_youtube_cs')),
            array('js_cke_plugin_youtube_version',  System::get_item_version('js_cke_plugin_youtube')),
            array('js_cke_plugin_zonebreak_cs',     System::get_item_version('js_cke_plugin_zonebreak_cs')),
            array('js_cke_plugin_zonebreak_version',System::get_item_version('js_cke_plugin_zonebreak')),
            array('js_ecc_cs',                      System::get_item_version('js_ecc_cs')),
            array('js_ecc_version',                 System::get_item_version('js_ecc')),
            array('js_functions_cs',                System::get_item_version('js_functions_cs')),
            array('js_functions_version',           System::get_item_version('js_functions')),
            array('js_member_cs',                   System::get_item_version('js_member_cs')),
            array('js_member_version',              System::get_item_version('js_member')),
            array('js_jdplayer_cs',                 System::get_item_version('js_jdplayer_cs')),
            array('js_jdplayer_version',            System::get_item_version('js_jdplayer')),
            array('js_jquery_cs',                   System::get_item_version('js_jquery_cs')),
            array('js_jquery_version',              System::get_item_version('js_jquery')),
            array('js_jquery_ui_cs',                System::get_item_version('js_jquery_ui_cs')),
            array('js_jquery_ui_version',           System::get_item_version('js_jquery_ui')),
            array('js_rssreader_cs',                System::get_item_version('js_rssreader_cs')),
            array('js_rssreader_version',           System::get_item_version('js_rssreader_version')),
            array('js_spectrum_cs',                 System::get_item_version('js_spectrum_cs')),
            array('js_spectrum_version',            System::get_item_version('js_spectrum_version')),
            array('js_treeview_cs',                 System::get_item_version('js_treeview_cs')),
            array('js_treeview_version',            System::get_item_version('js_treeview_version')),
            array('labels_cs',                      System::get_item_version('labels')),
            array('labels_version',                 ''),
            array('icons_checksum',                 System::get_item_version('icons')),
            array('labels_checksum',                System::get_item_version('labels')),
            array('ckfinder_cs',                    System::get_item_version('ckfinder_cs')),
            array('ckfinder_version',               System::get_item_version('ckfinder')),
            array('ckfinder_config_cs',             System::get_item_version('ckfinder_config_cs')),
            array('ckfinder_config_version',        System::get_item_version('ckfinder_config')),
        );
        foreach ($entries as $entry) {
            $out[] = array(
                'category' => 'config',
                'title' =>    $entry[0],
                'content' =>  $entry[1]
            );
        }
    }

    private function _get_config_libraries_array()
    {
        return array(
            array('/codebase.php','codebase'),
            array('/db_connect.php','db_connect'),
            array('/functions.php','functions'),
            array('/img.php','img'),
            array('/fedex/rate.php','fedex_rate'),
            array('/getid3/getid3.php','getid3'),
            array('/images/icons.gif','icons'),
            array('/images/icons-big.gif','icons_big'),
            array('/images/labels.gif','labels'),
            array('/js/ckeditor/ckeditor.js','js_cke'),
            array('/js/ckeditor/config.js','js_cke_config'),
            array('/js/ckeditor/plugins/audio/plugin.js','js_cke_plugin_audio'),
            array('/js/ckeditor/plugins/ecl/plugin.js','js_cke_plugin_ecl'),
            array('/js/ckeditor/plugins/more/plugin.js','js_cke_plugin_more'),
            array('/js/ckeditor/plugins/video/plugin.js','js_cke_plugin_video'),
            array('/js/ckeditor/plugins/youtube/plugin.js','js_cke_plugin_youtube'),
            array('/js/ckeditor/plugins/zonebreak/plugin.js','js_cke_plugin_zonebreak'),
            array('/js/ecc.js','js_ecc'),
            array('/js/functions.js','js_functions'),
            array('/js/member.js','js_member'),
            array('/js/jdplayer/mediaelement-and-player.min.js','js_jdplayer'),
            array('/js/jquery.min.js','js_jquery'),
            array('/js/jquery-ui.min.js','js_jquery_ui'),
            array('/js/rss_reader.js','js_rssreader'),
            array('/js/spectrum.min.js','js_spectrum'),
            array('/js/treeview.js','js_treeview'),
            array('/style/breadcrumbs.css','css_breadcrumbs'),
            array('/style/default.css','css'),
            array('/style/community.css','css_community'),
            array('/style/labels.css','css_labels'),
            array('/style/spectrum.min.css','css_spectrum'),
            array('/style/pie.htc','css_pie'),
            array('/www/js/ckfinder/ckfinder.html','ckfinder'),
            array('/www/js/ckfinder/config.php','ckfinder_config'),
        );
    }

    protected function _get_config_libraries_detail_array()
    {
        $keys = $this->_get_config_libraries_array();
        $this->_get_config_libraries($libraries);
        foreach ($keys as $key) {
            $path =       $key[0];
            $entry =      $key[1];
            $version =    '';
            $checksum =   '';
            foreach ($libraries as $library) {
                if ($library['title'] == $entry."_cs") {
                    $checksum = $library['content'];
                }
                if ($library['title'] == $entry."_version") {
                    $version = $library['content'];
                }
            }
            $out[]= $path."|".$version."|".$checksum;
        }
        return $out;
    }

    private function _get_config_reports(&$out)
    {
        $Obj_Report_Config = new Report_Config;
        $reports =              $Obj_Report_Config->get_overview_global();
        $final =    "";
        foreach ($reports as $report) {
            $out[] = array(
                'category' => 'reports',
                'title' =>    $report['name'],
                'content' =>
                 $report['ID'].'|'.$report['report'].'|'.$report['actions'].'|'
                .$report['columns'].'|'.$report['filters'].'|'.$report['crc32']
            );
            if (
                substr($report['name'], 0, 4)!='cus_' &&
                substr($report['name'], 0, 7)!='module.' &&
                substr($report['name'], 0, 12)!='custom_treb_'
            ) {
                $final.= $report['name']."|".$report['crc32'];
            }
        }
        $out[] = array(
            'category'=>'reports',
            'title'=>'reports_cs_actual',
            'content'=>dechex(crc32($final))
        );
    }

    private function _get_config_reports_expected($csv)
    {
        $_cs_all =  explode(', ', $csv);
        $cs_arr =   array();
        foreach ($_cs_all as $_cs_entry) {
            $_cs_entry_arr =      explode('|', $_cs_entry);
            $cs_arr[$_cs_entry_arr[1]] =
            array(
                'expected_name' =>      $_cs_entry_arr[0],
                'expected_report' =>    $_cs_entry_arr[2],
                'expected_actions' =>   $_cs_entry_arr[3],
                'expected_columns' =>   $_cs_entry_arr[4],
                'expected_filters' =>   $_cs_entry_arr[5],
                'expected_final' =>     $_cs_entry_arr[6]
            );
        }
        return $cs_arr;
    }

    private function _get_config_system(&$out, $initial)
    {
        $record = $this->get_record();
        $access = $this->get_last_loggedin_access();
        foreach ($initial as $_cs) {
            if ($_cs['title'] == 'classes_cs_actual') {
                System::$cache_version['classes_cs_actual'] = ($_cs['content']);
            }
            if ($_cs['title'] == 'db_cs_actual') {
                System::$cache_version['db_cs_actual'] = ($_cs['content']);
            }
            if ($_cs['title'] == 'reports_cs_actual') {
                System::$cache_version['reports_cs_actual'] = ($_cs['content']);
            }
        }
        System::$cache_version['classes_cs_status'] =
        (System::$cache_version['classes_cs_actual']==System::get_item_version('classes_cs_target') ?
            "Pass"
         :
            "Fail"
        );
        System::$cache_version['db_cs_status'] =
        (System::$cache_version['db_cs_actual']==System::get_item_version('db_cs_target') ?
            'Pass'
         :
            'Fail'
        );
        System::$cache_version['config_status'] =       $this->_get_config_system_config_check();
        System::$cache_version['heartbeat_status'] =    $this->_get_config_system_heartbeat_check();
        System::$cache_version['htaccess_status'] =     $this->_get_config_system_htaccess_check();
        System::$cache_version['config_status'] =       $this->_get_config_system_config_check();
        $lib_arr = $this->_get_config_libraries_detail_array();
        System::$cache_version['libraries_cs_actual'] =  dechex(crc32(implode(',', $lib_arr)));
        System::$cache_version['libraries_cs_status'] =
        (System::$cache_version['libraries_cs_actual']==System::get_item_version('libraries_cs_target') ?
            'Pass'
         :
            'Fail'
        );
        System::$cache_version['reports_cs_status'] =
        (System::$cache_version['reports_cs_actual']==System::get_item_version('reports_cs_target') ?
            'Pass'
         :
            'Fail'
        );
        $entries = array(
            array('URL',                    $this->get_path()),
            array('title',                  $record['textEnglish']),
            array('build_version',          System::get_item_version('build')),
            array('config_status',          System::$cache_version['config_status']),
            array('classes_cs_status',      System::get_item_version('classes_cs_status')),
            array('classes_cs_actual',      System::get_item_version('classes_cs_actual')),
            array('db_cs_status',           System::$cache_version['db_cs_status']),
            array('db_cs_actual',           System::$cache_version['db_cs_actual']),
            array('libraries_cs_status',    System::$cache_version['libraries_cs_status']),
            array('libraries_cs_actual',    System::$cache_version['libraries_cs_actual']),
            array('reports_cs_status',      System::$cache_version['reports_cs_status']),
            array('reports_cs_actual',      System::$cache_version['reports_cs_actual']),
            array('akismet_key_status',     System::get_item_version('akismet_key_status')),
            array('bugtracker_status',      System::get_item_version('bugtracker_status')),
            array('heartbeat_status',       System::$cache_version['heartbeat_status']),
            array('htaccess_status',        System::$cache_version['htaccess_status']),
            array('http_software',          System::get_item_version('http_software')),
            array('server_name',            System::get_item_version('server_name')),
            array('php_version',            System::get_item_version('php')),
            array('mysql_version',          System::get_item_version('mysql')),
            array('systemID',               $record['ID']),
            array('system_family',          System::get_item_version('system_family')),
            array('db_version',             System::get_item_version('db_version')),
            array('db_name',                System::get_item_version('db_name')),
            array('document_root',          System::get_item_version('document_root')),
            array(
                'last_loggedin_access',
                ($access['history_datetime'] ?
                    $access['PUsername']." (".$access['history_datetime'].")"
                :
                    ""
                )
            ),
            array('codebase_version',       System::get_item_version('codebase')),
            array('classes_cs_target',      System::get_item_version('classes_cs_target')),
            array('classes_detail',         System::get_item_version('classes_detail')),
            array('db_detail',              System::get_item_version('db_detail')),
            array('db_cs_target',           System::$cache_version['db_cs_target']),
            array('libraries_detail',       System::get_item_version('libraries_detail')),
            array('libraries_cs_target',    System::get_item_version('libraries_cs_target')),
            array('reports_detail',         System::get_item_version('reports_detail')),
            array('reports_cs_target',      System::get_item_version('reports_cs_target')),
            array('custom_version',         System::get_item_version('custom')),
            array('cache_version_hits',     System::$cache_version_hit),
            array('cache_version_misses',   System::$cache_version_miss),
            array('system_version',         System::get_item_version('system')),
        );
        foreach ($entries as $entry) {
            $out[] = array(
                'category' => 'config',
                'title' =>    $entry[0],
                'content' =>  $entry[1]
            );
        }
    }

    private function _get_config_system_config_check()
    {
        $content = @file_get_contents('./config.php');
        $match =    preg_match("/\\\$dsn/i", $content, $matches);
        if (!$match) {
            return "Fail - No \$dsn connection variable";
        }
        $match =    preg_match("/\\\$li/i", $content, $matches);
        if ($match) {
            return "Fail - Shouldn't have \$li";
        }
        $match =    preg_match("/\\\$db/i", $content, $matches);
        if ($match) {
            return "Fail - Shouldn't have \$db";
        }
        return 'Pass';
    }

    private function _get_config_system_heartbeat_check()
    {
        $last_heartbeat = $this->get_field('cron_job_heartbeat_last_run');
        if ($last_heartbeat=='0000-00-00 00:00:00') {
            return 'Fail - heartbeat never started';
        }
        sscanf(
            $last_heartbeat,
            "%4s-%2s-%2s %2s:%2s:%2s",
            $now_YYYY,
            $now_MM,
            $now_DD,
            $now_hh,
            $now_mm,
            $now_ss
        );
        $heartbeat = mktime($now_hh, $now_mm, $now_ss, $now_MM, $now_DD, $now_YYYY);
        $diff = (time()-$heartbeat);
        if ($diff>120) {
            return "Fail - time since heartbeat: ".seconds_to_hhmmss($diff);
        }
        return "Pass";
    }

    private function _get_config_system_htaccess_check()
    {
        if (!file_exists('./.htaccess')) {
            return "Fail: .htaccess is missing";
        }
        $content = @file_get_contents('./.htaccess');
        if (!$content) {
            return "Fail: .htaccess empty or unreadable";
        }
        $match =    preg_match("/RewriteBase ([^\xA\xD]*)/i", $content, $matches);
        if (!$match) {
            return "Fail: RewriteBase missing";
        }
        $base =     trim($matches[1]);
        if ($base!=BASE_PATH) {
            return "Fail: RewriteBase=".$base;
        }
        $match =    preg_match("/RewriteCond %{REQUEST_FILENAME} !-f/i", $content);
        if (!$match) {
            return "Fail: RewriteCond for Files missing";
        }
        $match =    preg_match("/RewriteCond %{REQUEST_FILENAME} !-d/i", $content);
        if (!$match) {
            return "Fail: RewriteCond for Directories missing";
        }
        $match =    preg_match("/ReWriteRule facebook\\\$ ([^ ]*) \[L\]/i", $content, $matches);
        if (!$match) {
            return "Fail: Facebook Rule missing";
        }
        if ($matches[1]!='streamer.php') {
            return "Fail: Facebook Rule action=".$matches[1];
        }
        $match =    preg_match("/ReWriteRule \^(\([^\)]*\))\(\\\\\?\|\/\) ([^ ]*) \[L\]/i", $content, $matches);
        if (!$match) {
            return "Fail: RewriteRule missing";
        }
        if ($matches[1]!=HTACCESS_STACK) {
            return "Fail: RewriteRule match=".$matches[1];
        }
        if ($matches[2]!='streamer.php') {
            return "Fail: RewriteRule action=".$matches[2];
        }
        $match =    preg_match("/ReWriteRule \. ([^ ]*) \[L\]/i", $content, $matches);
        if (!$match) {
            return "Fail: Main RewriteRule missing";
        }
        if ($matches[1]!='index.php') {
            return "Fail: Main RewriteRule action=".$matches[1];
        }
        return 'Pass';
    }

    protected function _get_config_tables(&$out)
    {
        global $db;
        $sql =    "SHOW TABLES FROM `$db`";
        $records = $this->get_records_for_sql($sql);
        foreach ($records as $record) {
            foreach ($record as $key => $value) {
                $table_name =   $value;
                $out[] = array(
                    'category'=>'tables',
                    'title'=>$table_name,
                    'content'=>0
                );
            }
        }
        $sql =    "SHOW TABLES FROM `".$db."_media`";
        $records = $this->get_records_for_sql($sql);
        foreach ($records as $record) {
            foreach ($record as $key => $value) {
                $table_name =   $db."_media.".$value;
                $out[] = array(
                'category'=>'tables',
                'title'=>$table_name,
                'content'=>0);
            }
        }
        $final = "";
        $module_arr = array();
        foreach ($out as &$entry) {
            if ($entry['category']=='tables') {
                $Obj = new Table($entry['title']);
                $cs =         $Obj->get_checksum(true);
                $cs_real =    $Obj->get_checksum(false);
                $entry['content'] = $cs;
                if ($cs != $cs_real) {
                    $entry['title'].=
                    ' &lt;= Customised - Real CS: '.dechex($cs_real);
                }
                if (substr($entry['title'], 0, 7)=="module_") {
                    $name_arr = explode("_", $entry['title']);
                    $name = $name_arr[1];
                    if (!array_key_exists($name, $module_arr)) {
                        $module_arr[$name] = "";
                    }
                    $module_arr[$name] .= $entry['content'];
                  //
                } elseif (substr($entry['title'], 0, 4)=="cus_") {
                  //
                } else {
                    $final.=  ($entry['content']<0 ? $entry['content']+= 0x100000000 : $entry['content']);
                }
            }
        }
        $out[] = array(
            'category'=>'tables',
            'title'=>'db_cs_actual',
            'content'=>crc32($final)
        );
        foreach ($module_arr as $key => $value) {
            $out[] = array(
                'category'=>'tables',
                'title'=>'checksum_module_'.$key,
                'content'=>crc32($value)
            );
        }
        foreach ($out as &$entry) {
            if ($entry['category']=='tables') {
                $entry['content'] = dechex($entry['content']);
            }
        }
    }

    private function _get_config_tables_expected($csv)
    {
        $_cs_all =  explode(', ', $csv);
        $cs_arr =   array();
        foreach ($_cs_all as $_cs_entry) {
            $_cs_entry_arr =      explode('|', $_cs_entry);
            if (isset($_cs_entry_arr[1])) {
                $_title =               $_cs_entry_arr[0];
                $_expected_cs =         $_cs_entry_arr[1];
                $cs_arr[$_title] =      $_expected_cs;
            }
        }
        return $cs_arr;
    }

    public function get_version()
    {
        return VERSION_SYSTEM_HEALTH;
    }
}
