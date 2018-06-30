<?php
define('VERSION_RECORD', '1.0.88');
/*
Version History:
  1.0.88 (2015-02-17)
    1) Typo for error message in Record::update() for warning about validating without fields

  (Older version history in class.record.txt)
*/
class Record extends Portal
{
    public static $cache_ID_by_name_array =      array();
    public static $cache_record_array =          array();
    public static $cache_debug_query_checksum =  array();
    private $_edit_params_array =         array(); // Name of form, icons etc for editing this object
    private $_fixed_fields_array =        array();
    private $_has_actions;
    private $_has_activity;
    private $_has_archive;
    private $_has_categories;
    private $_has_groups;
    private $_has_keywords;
    private $_has_languages;
    private $_has_push_products;
    private $_message_associated =        '';
    private $_path_prefix;                // For postings this is same as `type`
    private $_Obj_MySQLi;
    private $_plural_append_array =       array();
    private $_search_type;                // For postings this is same as `type`

    public $ID;
    public $assign_type;         // Used for `group_assign`.`assign_type` and `action`.`sourceType` - e.g. 'job'
    protected $db_name;
    public $fieldsort_arr;
    public $listType = false;    // Only set or referenced for lst_named_type objects
    public $listTypeID = false;  // Only set or referenced for lst_named_type objects
    public $name_field;          // e.g. 'title'
    public $record = false;
    private $systemID;
    public $table;               // e.g. 'postings'

    public function __construct($_table = '', $_ID = '', $_db_name = '')
    {
        global $db, $Obj_MySQLi;
      // Does table name include a dot for DB also?
        $_table_arr = preg_split("/[\.]/", $_table);
        if (count($_table_arr)==1) {
            $this->_set_table_name($_table);
            $this->_set_db_name($_db_name=='' ? $db : $_db_name);
        } else {
            $this->_set_table_name(str_replace('`', '', $_table_arr[1]));
            $this->_set_db_name(str_replace('`', '', $_table_arr[0]));
        }
        $this->_Obj_MySQLi = $Obj_MySQLi;
        $this->_set_ID($_ID);
        $this->_set_fixed_fields(array());
        $this->_set_has_actions(false);
        $this->_set_has_archive(false);
        $this->_set_has_categories(false);
        $this->_set_has_groups(false);
        $this->_set_has_keywords(false);
        $this->_set_has_push_products(false);
        $this->_set_object_name('Record');
        $this->_set_name_field('name'); // Default for exists_named() etc - may be overridden
        $this->set_plural_append('', 's');
        $this->set_edit_params(
            array(
            'report' =>                 '',
            'report_rename' =>          false,
            'report_rename_label' =>    '',
            'icon_edit' =>              '',
            'icon_edit_disabled' =>     '',
            'icon_edit_popup' =>        ''
            )
        );
    }

    public function _get($property)
    {
        return $this->$property;
    }
    public function _get_assign_type()
    {
        return $this->assign_type;
    }
    public function _get_db_name()
    {
        return $this->db_name;
    }
    public function _get_fixed_fields()
    {
        return $this->_fixed_fields_array;
    }
    public function _get_has_actions()
    {
        return $this->_has_actions;
    }
    public function _get_has_activity()
    {
        return $this->_has_activity;
    }
    public function _get_has_archive()
    {
        return $this->_has_archive;
    }
    public function _get_has_categories()
    {
        return $this->_has_categories;
    }
    public function _get_has_groups()
    {
        return $this->_has_groups;
    }
    public function _get_has_keywords()
    {
        return $this->_has_keywords;
    }
    public function _get_has_languages()
    {
        return $this->_has_languages;
    }
    public function _get_has_push_products()
    {
        return $this->_has_push_products;
    }
    public function _get_ID()
    {
        return $this->ID;
    }
    public function _get_listtype()
    {
        return $this->listtype;
    }
    public function _get_listTypeID()
    {
        return $this->listTypeID;
    }
    public function _get_message_associated()
    {
        return $this->_message_associated;
    }
    public function _get_name_field()
    {
        return $this->name_field;
    }
    public function _get_path_prefix()
    {
        return $this->_path_prefix;
    }
    public function _get_search_type()
    {
        return $this->_search_type;
    }
    public function _get_systemID()
    {
        return $this->systemID;
    }
    public function _get_table_name()
    {
        return $this->table;
    }
    public function _get_type()
    {
        return '';
    }  // overridden by child classes

    public function _set($property, $value)
    {
        $this->$property = $value;
    }
    public function _set_multiple($args)
    {
        foreach ($args as $key => $value) {
            $this->$key = $value;
        }
    }
    public function _set_assign_type($value)
    {
        $this->assign_type = $value;
    }
    public function _set_db_name($value)
    {
        $this->db_name = $value;
    }
    public function _set_fixed_fields($value)
    {
        $this->_fixed_fields_array = $value;
    }
    public function _set_has_actions($value)
    {
        $this->_has_actions = $value;
    }
    public function _set_has_activity($value)
    {
        $this->_has_activity = $value;
    }
    public function _set_has_archive($value)
    {
        $this->_has_archive = $value;
    }
    public function _set_has_categories($value)
    {
        $this->_has_categories = $value;
    }
    public function _set_has_groups($value)
    {
        $this->_has_groups = $value;
    }
    public function _set_has_keywords($value)
    {
        $this->_has_keywords = $value;
    }
    public function _set_has_languages($value)
    {
        $this->_has_languages = $value;
    }
    public function _set_has_push_products($value)
    {
        $this->_has_push_products = $value;
    }
    public function _set_ID($value)
    {
        $this->ID = $value;
    }
    public function _set_listtype($value)
    {
        $this->listtype = $value;
    }
    public function _set_listTypeID($value)
    {
        $this->listTypeID = $value;
    }
    public function _set_message_associated($value)
    {
        $this->_message_associated = $value;
    }
    public function _set_name_field($value)
    {
        $this->name_field = $value;
    }
    public function _set_path_prefix($value)
    {
        $this->_path_prefix = $value;
    }
    public function _set_search_type($value)
    {
        $this->_search_type = $value;
    }
    public function _set_systemID($value)
    {
        $this->systemID = $value;
    }
    public function _set_table_name($value)
    {
        $this->table = $value;
    }

    public function _push_fixed_field($key, $value)
    {
        $this->_fixed_fields_array[$key] = $value;
    }

    public function add($data, $validate = false)
    {
        deprecated();
        return $this->insert($data, $validate);
    }

    public function archive()
    {
        $_ID =       $this->_get_ID();
        $ID_arr =   explode(',', $_ID);
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $newID =          $this->copy(false, false, false);
            // do NOT set created or reset modified dates
            $this->_set_ID($newID);
            $data =
            array(
            'archive' =>      1,
            'archiveID' =>    $ID
            );
            $this->update($data, false, false);
        // do NOT set modified date
        }
        $this->_set_ID($_ID);
    }

    public function cache_clear_ID_by_name($name, $systemID = "")
    {
        $key = $this->_get_object_name()."_".$systemID."_".$name;
        unset(Record::$cache_ID_by_name_array[$key]);
    }

    public function category_assign($csv)
    {
        if (!$this->_get_has_categories()) {
            return;
        }
        $systemID = $this->get_field('systemID');
        $assign_arr = explode(",", str_replace(' ', '', $csv));
        sort($assign_arr);
        $csv = implode(', ', $assign_arr);
        $Obj = new Category_Assign;
        $Obj->set_for_assignment($this->_get_assign_type(), $this->_get_ID(), $csv, $systemID);
        $data = array('category'=>$csv);
        $this->update($data, false, false); // don't set modified date - will be set elsewhere if needed
    }

    public function category_unassign()
    {
        if (!$this->_get_has_categories()) {
            return;
        }
        $Obj = new Category_Assign;
        $Obj->delete_for_assignment($this->_get_assign_type(), $this->_get_ID());
    }

    public function copy($new_name = false, $new_systemID = false, $new_date = true)
    {
        $now =      get_timestamp();
        $newID =    $this->uniqID();
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `ID` IN(".$this->_get_ID().")";
  //    z($sql);
        $fields = $this->get_record_for_sql($sql);
        $sql_arr = array();
        foreach ($fields as $key => $value) {
            switch($key) {
                case "ID":
                    $value = $newID;
                    break;
                case "history_created_by":
                    if ($new_date) {
                        $value = get_userID();
                    }
                    break;
                case "history_created_date":
                    if ($new_date) {
                        $value = $now;
                    }
                    break;
                case "history_created_IP":
                    if ($new_date) {
                        $value = $_SERVER['REMOTE_ADDR'];
                    }
                    break;
                case "history_modified_by":
                case "history_modified_date":
                case "history_modified_IP":
                    if ($new_date) {
                        $value = "";
                    }
                    break;
                case "comments_count":
                case "max_sequence":
                    $value= 0;
                    break;
                case "qb_ident":
                    $value= "";
                    break;
                case $this->_get_name_field():
                    if ($new_name) {
                        $value = $new_name;
                    }
                    break;
                case "page":
                    if ($this->_get_table_name()=="pages") {
                        if (!$new_systemID) {
                            $value = $new_name;
                        }
                    }
                    break;
                case "systemID":
                    if ($new_systemID) {
                        $value = $new_systemID;
                    }
                    break;
            }
            $sql_arr[] = "  \"".addslashes($value)."\"";
        }
        $sql =
             "INSERT INTO\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."VALUES (\n"
            .implode(",\n", $sql_arr)
            .")";
  //    z($sql);
        $this->do_sql_query($sql);
        $this->copy_actions($newID);
        $this->copy_category_assign($newID);
        $this->copy_group_assign($newID);
        $this->copy_keyword_assign($newID);
        $this->copy_push_product_assign($newID);
        return $newID;
    }

    public function copy_actions($newID)
    {
        if (!$this->_get_has_actions()) {
            return;
        }
        $records = $this->get_actions();
        foreach ($records as $data) {
            $Obj = new Action($data['ID']);
            $Obj->copy($newID, $this->_get_table_name());
        }
        return;
    }

    public function copy_category_assign($newID, $new_systemID = "")
    {
        if (!$this->_get_has_categories()) {
            return;
        }
        $records = $this->get_category_assign();
        $Obj_Category_Assign = new Category_Assign;
        foreach ($records as $data) {
            unset($data['ID']);
            $data['assignID'] = $newID;
            if ($new_systemID!="") {
                $data['systemID'] = $new_systemID;
            }
            $Obj_Category_Assign->insert($data);
        }
    }

    public function copy_group_assign($newID, $new_systemID = "")
    {
        if (!$this->_get_has_groups()) {
            return;
        }
        $records =  $this->get_group_assign();
        $Obj =      new Group_Assign;
        foreach ($records as $data) {
            unset($data['ID']);
            $data['assignID'] = $newID;
            if ($new_systemID!="") {
                $data['systemID'] = $new_systemID;
            }
            $Obj->insert($data);
        }
    }

    public function copy_keyword_assign($newID, $new_systemID = "")
    {
        if (!$this->_get_has_keywords()) {
            return;
        }
        $records =  $this->get_keyword_assign();
        $Obj =      new Keyword_Assign;
        foreach ($records as $data) {
            unset($data['ID']);
            $data['assignID'] = $newID;
            if ($new_systemID!="") {
                $data['systemID'] = $new_systemID;
            }
            $Obj->insert($data);
        }
    }

    public function copy_push_product_assign($newID, $new_systemID = "")
    {
        if (!$this->_get_has_push_products()) {
            return;
        }
        $records =  $this->get_push_product_assign();
        $Obj =      new Push_Product_Assign;
        foreach ($records as $data) {
            unset($data['ID']);
            $data['assignID'] = $newID;
            if ($new_systemID!="") {
                $data['systemID'] = $new_systemID;
            }
            $Obj->insert($data);
        }
    }

    public function count()
    {
        if ($this->_get_ID()=="") {
            return false;
        }
        $sql =
         "SELECT\n"
        ."  count(*) AS `count`\n"
        ."FROM\n"
        ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
        ."WHERE\n"
        ."  `ID` = \"".$this->_get_ID()."\"";
        return $this->get_field_for_sql($sql);
    }

    public function count_all_for_system($systemID = SYS_ID)
    {
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `systemID` IN(".$systemID.")";
        return (int)$this->get_field_for_sql($sql);
    }

    public function count_changes()
    {
        if ($this->_get_ID()=="") {
            return false;
        }
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `archiveID` IN(".$this->_get_ID().")";
        return $this->get_field_for_sql($sql);
    }

    public function count_children()
    {
        if ($this->_get_ID()=="") {
            return false;
        }
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `parentID` IN(".$this->_get_ID().")";
        return $this->get_field_for_sql($sql);
    }

    public function count_named($name, $systemID = "")
    {
        $sql =
             "SELECT\n"
            ."  count(*) AS `count`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($systemID!="" ? "  `systemID` = ".$systemID." AND\n" : "" )
            .($this->_get_type() ? "  `type` = \"".$this->_get_type()."\" AND\n" : "")
            .($this->_get_listTypeID() ? "  `listTypeID` = ".$this->_get_listTypeID()." AND\n" : "")
            ."  `".$this->_get_name_field()."`"
            .($name === html_entity_decode($name) ?
             "=\"".$name."\""
             :
             " IN (\"".$name."\",\"".html_entity_decode($name)."\")"
            );
  //    z($sql);
        return $this->get_field_for_sql($sql);
    }

    public function count_parents()
    {
        if ($this->_get_ID()=="") {
            return false;
        }
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `ID` IN(SELECT `parentID` FROM `postings` WHERE `parentID` IN(".$this->_get_ID()."))";
        return $this->get_field_for_sql($sql);
    }

    public function decrement($field, $validate = false)
    {
        $value = $this->get_field($field);
        if (!is_numeric($value)) {
            return false;
        }
        return $this->set_field($field, (int)$value-1, $validate);
    }

    public function delete()
    {
        $this->delete_actions();
        $this->category_unassign();
        $this->group_unassign();
        $this->keyword_unassign();
        $this->language_unassign();
        $this->push_product_unassign();
        $sql =
             "DELETE FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `ID` IN(".$this->_get_ID().")"
            .($this->_get_has_archive() ? " OR `archiveID` IN(".$this->_get_ID().")" : "");
  //    z($sql);
        return $this->do_sql_query($sql);
    }

    public function delete_actions()
    {
        if (!$this->_get_has_actions()) {
            return;
        }
        $actions_arr = $this->get_actions();
        foreach ($actions_arr as $action) {
            $Obj = new Action($action['ID']);
            $Obj->delete();
        }
        return;
    }

    public static function do_sql_query($sql, $connection = false)
    {
        global $system_vars, $Obj_MySQLi;
        if (!$connection) {
            $connection = $Obj_MySQLi;
        }
        if (isset($system_vars) && isset($system_vars['debug']) && $system_vars['debug']=='1') {
            return Record::_do_sql_query_log($sql, $connection);
        }
        return $Obj_MySQLi->query($sql);
    }

    private static function _do_sql_query_log($sql, $connection)
    {
      // Please note - this will ALWAYS miss the first query to read system
      // This is because until we read system we don't know whether to log queries or not.
      // Furthermore, we don't have page vars or even page or mode name at this point
      // so cannot write the debug query header anyway.
        global $sql_debug_filename, $sql_debug_filepath, $sql_debug_hits, $sql_debug_total_period;
        Record::_do_sql_query_log_setup();
        $sql_debug_hits++;
        $start =        microtime_float();
        $result =       $connection->query($sql);
        $period =       microtime_float()-$start;
        $sql_debug_total_period+=$period;
        $query_date =   date('Y-m-d H:i:s');
        $checksum =     trim(dechex(crc32($sql)));
        if (isset(Record::$cache_debug_query_checksum[$checksum])) {
            Record::$cache_debug_query_checksum[$checksum]++;
            $duplicate =  "!!! DUPLICATE #".Record::$cache_debug_query_checksum[$checksum];
        } else {
            $duplicate =  "";
            Record::$cache_debug_query_checksum[$checksum] = 0;
        }
        $slow = (($period*1000)>SYS_LOG_SLOW ? "!!! SLOW" : "");
        $message =
             "**************\r\n"
            ."* Query #".pad($sql_debug_hits, 3)." *\r\n"
            ."**************\r\n"
            .($duplicate!='' ? $duplicate."\r\n" : "")
            .($slow!='' ? $slow."\r\n" : "")
            ."Date:       ".$query_date."\r\n"
            ."Query time: ".three_dp($period*1000)." mS"
            ."\r\n"
            ."Cumulative: ".three_dp($sql_debug_total_period*1000)." mS\r\n"
            ."Query:\r\n"
            .str_replace("\n", "\r\n", $sql)."\r\n"
            ."------------------------\r\n\r\n\r\n";
        if (is_writable($sql_debug_filepath.$sql_debug_filename)) {
            if ($handle = fopen($sql_debug_filepath.$sql_debug_filename, 'a')) {
                if (fwrite($handle, $message) === false) {
                    echo "Cannot write to file ".$sql_debug_filename;
                }
                fclose($handle);
            }
        } else {
            echo "The file ".$sql_debug_filename." is not writable";
        }
        return $result;
    }

    private static function _do_sql_query_log_setup()
    {
        global $mode, $submode, $page, $report_name;
        global $sql_debug_filename, $sql_debug_filepath, $sql_debug_hits, $sql_debug_total_period;
        $sql_debug_filepath =   SYS_LOGS;
        $sql_debug_filename =   SYS_LOG_FILE;
        if (!isset($sql_debug_total_period)) {
            $sql_debug_total_period = (float)0;
            $sql_debug_hits = 0;
            mkdirs($sql_debug_filepath, 0777);
            if (!file_exists($sql_debug_filepath.".htaccess")) {
                $handle = fopen($sql_debug_filepath.".htaccess", 'w');
                fwrite($handle, "order deny,allow\ndeny from all");
                fclose($handle);
            }
            if (!file_exists($sql_debug_filepath.$sql_debug_filename)) {
                $header =
                 str_repeat("*", strlen($sql_debug_filename)+4)."\r\n"
                ."* ".$sql_debug_filename." *\r\n"
                .str_repeat("*", strlen($sql_debug_filename)+4)."\r\n"
                ."\r\n"
                ."#########\r\n"
                ."# START #\r\n"
                ."#########\r\n"
                ."SLOW Threshold = ".SYS_LOG_SLOW."mS\r\n"
                ."Referer =        ".(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "")."\r\n"
                ."URI =            ".(isset($_SERVER["REQUEST_URI"])  ? $_SERVER["REQUEST_URI"]  : "")."\r\n"
                ."\$mode =          ".$mode."\r\n"
                ."\$submode =       ".$submode."\r\n"
                ."\$page =          ".$page."\r\n"
                ."\$report_name =   ".$report_name."\r\n"
                ."\r\n";
                $handle = fopen($sql_debug_filepath.$sql_debug_filename, 'wa');
                fwrite($handle, $header);
                fclose($handle);
            }
        }
    }


    public static function escape_string($string)
    {
        global $Obj_MySQLi;
        return $Obj_MySQLi->escape_string($string);
    }

    public function exists()
    {
        $count = $this->count();
        if ($count===false) {
            return false;
        }
        return $count>0;
    }

    public function exists_named($name, $systemID = "")
    {
        return ($this->count_named($name, $systemID) > 0);
    }

    public function exists_table_column($test)
    {
        $columns =  $this->get_table_fields($this->_get_table_name());
        foreach ($columns as $column) {
            if ($column['Field']==$test) {
                return true;
            }
        }
        return false;
    }

    public function export_sql($targetID, $show_fields)
    {
      // This is designed to be overridden for each class
        return "Forbidden unless enabled for specific class";
    }

    public function get_actions()
    {
        if ($this->_get_ID()=='') {
            return array();
        }
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `action`\n"
            ."WHERE\n"
            ."  `sourceID` IN(".$this->_get_ID().") AND\n"
            ."  `sourceType` = \"".$this->_get_table_name()."\"\n"
            ."ORDER BY\n"
            ."  `sourceTrigger`,\n"
            ."  `seq`,\n"
            ."  `destinationOperation`,\n"
            ."  `destinationValue`";
        return $this->get_records_for_sql($sql);
    }

    public static function get_affected_rows()
    {
        global $Obj_MySQLi;
        return ($Obj_MySQLi->affected_rows ? $Obj_MySQLi->affected_rows : 0);
    }

    public function get_arrays_for_sql($sql)
    {
        deprecated();
        return $this->get_rows_for_sql($sql);
    }

    public function get_category_assign()
    {
        if ($this->_get_ID()=='') {
            return array();
        }
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `category_assign`\n"
            ."WHERE\n"
            ."  `assign_type` = \"".$this->_get_assign_type()."\" AND\n"
            ."  `assignID` = \"".$this->_get_ID()."\"";
        return $this->get_records_for_sql($sql);
    }

    public function get_coords($address)
    {
        $Obj_Map = new Google_Map(0, SYS_ID);
        return $Obj_Map->get_geocode($address);
    }

    public function get_children()
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `systemID` = ".SYS_ID." AND\n"
            ."  `parentID` IN(".$this->_get_ID().")";
        return $this->get_records_for_sql($sql);
    }

    public function get_children_by_ID($ID)
    {
      // Added back in for John's reconciliation code
        $sql =
             "SELECT\n"
            ."  `".$this->_get_table_name()."`.*\n"
            ."FROM\n"
            ."  `".$this->_get_table_name()."`\n"
            ."INNER JOIN\n"
            ."  `".$this->_get_table_name()."` AS `parent`\n"
            ."WHERE\n"
            ."  `".$this->_get_table_name()."`.`systemID` = ".SYS_ID." AND\n"
            ."  `parent`.`systemID` = ".SYS_ID." AND\n"
            ."  `parent`.`ID` = `".$this->_get_table_name()."`.`parentID` AND\n"
            ."  `parent`.`ID` = ".$ID;
        return $this->get_records_for_sql($sql);
    }

    public function get_children_by_parentID($ID, $sortBy = '', $apply_visibility = false)
    {
        if (!$ID = sanitize('ID', $ID)) {
            return false;
        }
        $sql =
             "SELECT\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`.*\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."INNER JOIN\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."` AS `parent`\n"
            ."WHERE\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`.`systemID` = ".SYS_ID." AND\n"
            ."  `parent`.`systemID` = ".SYS_ID." AND\n"
            ."  `parent`.`ID` = `".$this->_get_table_name()."`.`parentID` AND\n"
            ."  `parent`.`ID` = $ID\n"
            .($sortBy!='' ? "ORDER BY $sortBy" : "");
  //    z($sql);
        $records = $this->get_records_for_sql($sql);
        if (!$apply_visibility) {
            return $records;
        }
        $out = array();
        foreach ($records as $record) {
            if ($this->is_visible($record)) {
                $out[] = $record;
            }
        }
        return $out;
    }

    public function get_children_by_name($name, $sortBy = false, $apply_visibility = false)
    {
        $out = array();
        $ID = $this->get_ID_by_name($name);
        return $this->get_children_by_parentID($ID, $sortBy, $apply_visibility);
    }

    public function get_csv_for_sql($sql)
    {
        $out = array();
        $records = $this->get_rows_for_sql($sql);
        foreach ($records as $record) {
            $out[] = $record[0];
        }
        return implode(",", $out);
    }

    public function get_edit_params()
    {
        return $this->_edit_params_array;
    }

    public function get_field($field)
    {
        if (!$ID = sanitize('ID', $this->_get_ID())) {
            return false;
        }
        if (substr($field, 0, 4)!='xml:') {
            $sql =
                 "SELECT\n"
                ."  `".$field."`\n"
                ."FROM\n"
                ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
                ."WHERE\n"
                ."  `ID` = ".$ID;
            return $this->get_field_for_sql($sql);
        }
        $this->load();
        $this->xmlfields_decode($this->record);
        if (isset($this->record[$field])) {
            return $this->record[$field];
        }
    }

    public function get_field_for_sql($sql)
    {
        if (!$records = Record::get_rows_for_sql($sql)) {
            return false;
        }
        return $records[0][0];
    }

    public static function get_embedded_file_properties($value)
    {
        $out = array(
            'name' => '',
            'size' => '',
            'type' => '',
            'data' => ''
        );
        if ($value!='') {
            $offsets_arr = array();
            $offsets_arr['name'] = strpos($value, "name:");
            $offsets_arr['size'] = strpos($value, "size:");
            $offsets_arr['type'] = strpos($value, "type:");
            $offsets_arr['data'] = strpos($value, "data:");
            $out['name'] = substr($value, $offsets_arr['name']+5, $offsets_arr['size']-$offsets_arr['name']-6);
            $out['size'] = substr($value, $offsets_arr['size']+5, $offsets_arr['type']-$offsets_arr['size']-6);
            $out['type'] = substr($value, $offsets_arr['type']+5, $offsets_arr['data']-$offsets_arr['type']-6);
            $out['data'] = substr($value, $offsets_arr['data']+5);
        }
        return $out;
    }

    public function get_group_assign()
    {
        if ($this->_get_ID()=='') {
            return array();
        }
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `group_assign`\n"
            ."WHERE\n"
            ."  `assign_type` = \"".$this->_get_assign_type()."\" AND\n"
            ."  `assignID` = \"".$this->_get_ID()."\"";
        return $this->get_records_for_sql($sql);
    }

    public function get_group_assign_csv()
    {
        $sql =
            "SELECT\n"
            ."  `groupID`\n"
            ."FROM\n"
            ."  `group_assign`\n"
            ."WHERE\n"
            ."  `assign_type` = \"".$this->_get_assign_type()."\" AND\n"
            ."  `assignID` = \"".$this->_get_ID()."\"";
  //    z($sql);die;
        return $this->get_csv_for_sql($sql);
    }

    public function get_ID_by_name($name, $systemID = false, $no_cache = false)
    {
        $key = $this->_get_object_name()."_".$systemID."_".$name;
        if (isset(Record::$cache_ID_by_name_array[$key]) && !$no_cache) {
            return Record::$cache_ID_by_name_array[$key];
        }
        $sql =
             "SELECT\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($this->_get_listTypeID() ? "  `listTypeID` = ".$this->_get_listTypeID()." AND\n" : "")
            .($systemID ?
              "  `systemID` IN(".$systemID.") AND\n"
            : "  `systemID` IN(1," . SYS_ID . ") AND\n"
            )
            ."  `".$this->_get_name_field()."` = \"".$name."\"\n"
            ."ORDER BY\n"
            ."  `systemID` = ".SYS_ID." DESC\n"
            ."LIMIT 0,1";
  //    z($sql);
        $value = $this->get_field_for_sql($sql);
        Record::$cache_ID_by_name_array[$key] = $value;
        return $value;
    }

    public function get_IDs_by_system($systemID)
    {
        $sql =
             "SELECT\n"
            ."  `ID`\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($this->_get_listTypeID() ? "  `listTypeID` = ".$this->_get_listTypeID()." AND\n" : "")
            ."  `systemID` IN(".$systemID.")";
        $records = $this->get_rows_for_sql($sql);
        $out = array();
        foreach ($records as $record) {
            $out[] = $record[0];
        }
        return $out;
    }

    public function get_keyword_assign()
    {
        if ($this->_get_ID()=='') {
            return array();
        }
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `keyword_assign`\n"
            ."WHERE\n"
            ."  `assign_type` = \"".$this->_get_assign_type()."\" AND\n"
            ."  `assignID` = \"".$this->_get_ID()."\"";
        return $this->get_records_for_sql($sql);
    }

    public static function get_last_db_error_msg()
    {
        global $Obj_MySQLi;
        return $Obj_MySQLi->error;
    }

    public static function get_last_db_error_msg_generic()
    {
        $error_number = Record::get_last_db_error_num();
        switch ($error_number){
            case 1062:
                return 'DUPLICATE_ENTRY';
            break;
            default:
                return 'CODE_'.$error_number;
            break;
        }
    }

    public static function get_last_db_error_num()
    {
        global $Obj_MySQLi;
        return $Obj_MySQLi->errno;
    }

    public function get_name()
    {
        $sql =
             "SELECT\n"
            ."  GROUP_CONCAT(`".$this->_get_name_field()."` SEPARATOR ', ')\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `ID` IN(".$this->_get_ID().")";
        return $this->get_field_for_sql($sql);
    }

    public function get_push_product_assign()
    {
        if ($this->_get_ID()=='') {
            return array();
        }
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `push_product_assign`\n"
            ."WHERE\n"
            ."  `assign_type` = \"".$this->_get_assign_type()."\" AND\n"
            ."  `assignID` = \"".$this->_get_ID()."\"";
        return $this->get_records_for_sql($sql);
    }

    public function get_record($Caching = true)
    {
        if (!$ID = sanitize('ID', $this->_get_ID())) {
            return false;
        }
        $key = $this->_get_db_name()."_".$this->_get_table_name()."_".$this->_get_ID();
        if ($Caching && isset(Record::$cache_record_array[$key])) {
            return Record::$cache_record_array[$key];
        }
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `ID` = ".$ID;
        $value = $this->get_record_for_sql($sql);
  //    z($sql);
        Record::$cache_record_array[$key] = $value;
        return $value;
    }

    public function get_records($systemID = '', $sortBy = '')
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($this->_get_listTypeID() ? "  `listTypeID` = ".$this->_get_listTypeID()." AND\n" : "")
            .($systemID !="" ? "  `systemID` = $systemID AND\n" : "")
            ."  1\n"
            .($sortBy !="" ? "ORDER BY ".$sortBy : "");
        return Record::get_records_for_sql($sql);
    }

    public function get_records_by_ID($systemID = '', $sortBy = '')
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($systemID !="" ? "  `systemID` = $systemID AND\n" : "")
            ."  `ID` IN(".$this->_get_ID().")\n"
            .($sortBy !="" ? "ORDER BY = $sortBy" : "");
        return Record::get_records_for_sql($sql);
    }

    public function get_record_by_name($name, $systemID = SYS_ID)
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .($this->_get_listTypeID() ? "  `listTypeID` = ".$this->_get_listTypeID()." AND\n" : "")
            ."  `".$this->_get_name_field()."` = \"$name\" AND\n"
            ."  `systemID` IN(1,".$systemID.")\n"
            ."ORDER BY\n"
            ."  `systemID` = ".$systemID." DESC\n"
            ."LIMIT 0,1";
        return Record::get_record_for_sql($sql);
    }

    public function get_records_by_parentID($parentID, $systemID = '', $sortBy = '')
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `parentID` = ".$parentID." AND\n"
            .($this->_get_listTypeID() ? "  `listTypeID` = ".$this->_get_listTypeID()." AND\n" : "")
            .($systemID !="" ? "  `systemID` = $systemID AND\n" : "")
            ."  1\n"
            .($sortBy !="" ? "ORDER BY ".$sortBy : "");
        return Record::get_records_for_sql($sql);
    }

    public function get_record_for_sql($sql)
    {
        if (!$records = Record::get_records_for_sql($sql)) {
            return false;
        }
        return $records[0];
    }

    public function get_records_for_sql($sql)
    {
        if (!$result = Record::do_sql_query($sql)) {
            do_log(
                3,
                __CLASS__.'::'.__FUNCTION__.'()',
                'execute',
                "Object: ".$this->_get_object_name()."\nQuery: ".$sql."\nError: ".Record::get_last_db_error_msg()
            );
            print draw_sql_debug(__CLASS__.'::'.__FUNCTION__.'()', $sql, Record::get_last_db_error_msg());
            return false;
        }
        $out = array();
        while ($row = $result->fetch_assoc()) {
            $out[] = $row;
        }
        return $out;
    }

    public function get_records_for_system($systemID, $sortBy = false)
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `systemID` = $systemID"
            .($sortBy ? "\nORDER BY\n  $sortBy" : "");
  //    z($sql);
        return Record::get_records_for_sql($sql);
    }

    public function get_records_since($datetime, $systemID = false, $filter = '')
    {
        $sql =
             "SELECT\n"
            ."  *\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `systemID`=".($systemID!==false ? $systemID : SYS_ID)." AND\n"
            .($this->_get_type() ? "  `type` = '".$this->_get_type()."' AND\n" : "")
            .($this->_get_listTypeID() ? "  `listTypeID` = ".$this->_get_listTypeID()." AND\n" : "")
            .($filter ? $filter." AND\n" : "")
            ."  `history_created_date`>='".$datetime."'";
  //    z($sql);
        return $this->get_records_for_sql($sql);
    }

    public function get_remote_xml_file($url)
    {
        if ($url=="") {
            return "";
        }
  //    die($url);
        $s = new gwSocket;
        if ($s->getUrl($url)) {
            if (is_array($s->headers)) {
                $h = array_change_key_case($s->headers, CASE_LOWER);
                if ($s->error) {
    // failed to connect with host
                    $buffer = $this->get_remote_xml_file_error($s->error);
                } elseif (preg_match("/404/", (isset($h['status']) ? $h['status'] : "404"))) // page not found
                $buffer = $this->get_remote_xml_file_error("Page Not Found");
                elseif (preg_match("/xml/i", $h['content-type'])) // got XML back
                $buffer = $s->page;
                else {
    // got a page, but wrong content type
                    $buffer = $this->get_remote_xml_file_error(
                        "The server did not return XML. The content type returned was ".$h['content-type']
                    );
                }
            } else {
                $buffer=$this->get_remote_xml_file_error("An unknown error occurred.");
            }
        } else {
            $buffer=$this->get_remote_xml_file_error("An unknown error occurred.");
        }
        return $buffer;
    }

    public function get_remote_xml_file_error($error)
    {
        $retVal=
             "<?xml version=\"1.0\" ?>\n"
            ."<rss version=\"2.0\">\n"
            ."\t<channel>\n"
            ."\t\t<title>Failed to Get RSS Data</title>\n"
            ."\t\t<description>An error was ecnountered attempting to get the RSS data: $error</description>\n"
            ."\t\t<pubdate>".date("D, d F Y H:i:s T")."</pubdate>\n"
            ."\t\t<lastbuilddate>".date("D, d F Y H:i:s T")."</lastbuilddate>\n"
            ."\t</channel>\n"
            ."</rss>\n";
        return $retVal;
    }

    public function get_rows_for_sql($sql)
    {
        if (!$result = Record::do_sql_query($sql)) {
            do_log(
                3,
                __CLASS__.'::'.__FUNCTION__.'()',
                'execute',
                "Object: ".$this->_get_object_name()."\nQuery: ".$sql."\nError: ".Record::get_last_db_error_msg()
            );
            print draw_sql_debug(__CLASS__.'::'.__FUNCTION__.'()', $sql, Record::get_last_db_error_msg());
            return false;
        }
        $out = array();
        while ($row = $result->fetch_row()) {
            $out[] = $row;
        }
        return $out;
    }


    public function get_status_text($message, $args, &$msg, &$msg_tooltip)
    {
        switch ($message) {
            case "name_exists_global":
            case "name_exists_local":
            case "name_required":
            case "subject_required":
                switch ($message) {
                    case "name_exists_global":
                        $attribute =
                            'a unique '.$this->_get_name_field().($this->_get_name_field()!='value' ? " value" : "")
                            ." - <b>".$args['targetValue']."</b> is already in use";
                        break;
                    case "name_exists_local":
                        $attribute =
                            'a unique '.$this->_get_name_field().($this->_get_name_field()!='value' ? " value" : "")
                            ." for this site - <b>".$args['targetValue']."</b> is already in use in this system";
                        break;
                    case "name_required":
                        $attribute = 'a name';
                        break;
                    case "subject_required":
                        $attribute = 'a subject';
                        break;
                }
                $targetID =     (isset($args['targetID']) ? $args['targetID'] : "");
                $targetValue =  (isset($args['targetValue']) ? $args['targetValue'] : "");
                $msg =
                status_message(2, true, "new ".$this->_get_object_name(), '', 'must have '.$attribute.'.', $targetID);
                break;
            case "copied":
                $targetID =     (isset($args['targetID']) ? $args['targetID'] : "");
                $targetValue =  (isset($args['targetValue']) ? $args['targetValue'] : "");
                $msg = status_message(
                    0,
                    true,
                    $this->_get_object_name().$this->plural(1),
                    '',
                    'been copied'.($targetValue ? " to $targetValue" : "").'.',
                    $targetID
                );
                $msg_tooltip = status_message(
                    0,
                    false,
                    $this->_get_object_name().$this->plural(1),
                    '',
                    'been copied'.($targetValue ? " to $targetValue" : "").'.',
                    $args['targetID']
                );
                break;
        }
    }

    public function get_table_fields($name)
    {
        $sql =    "SHOW COLUMNS FROM ".$name;
        $records = $this->get_records_for_sql($sql);
        if ($records==false) {
            return false;
        }
        $out = array();
        foreach ($records as $record) {
            preg_match('/([^\/(]+)/', $record['Type'], $type);
            switch ($type[0]) {
                case 'tinyint':
                case 'smallint':
                case 'mediumint':
                case 'int':
                case 'bigint':
                case 'float':
                case 'double':
                case 'decimal':
                    $number = true;
                    break;
                default:
                    $number = false;
                    break;
            }
            $out[] = array(
                'Field' =>    $record['Field'],
                'Type' =>     $record['Type'],
                'number' =>   $number
            );
        }
        return $out;
    }

    public function get_validation_fields()
    {
        $classname = get_class($this);
        if (defined($classname.'::FIELDS')) {
            return explode(',', str_replace(' ', '', $classname::FIELDS));
        }
        if (defined($classname.'::fields')) {
            return explode(',', str_replace(' ', '', $classname::fields));
        }
        if (property_exists($classname, 'fields')) {
            return $classname::$fields;
        }
        if (isset($this->fields)) {
            return $this->fields;
        }
        return false;
    }

    public function get_xml_for_sql($sql, $enc = 'UTF-8')
    {
        $records = $this->get_records_for_sql($sql);
        $fields_arr = array();
        if ($records===false) {
            return $sql;
        }
        $fields = false;
        if (count($records)) {
            $row = $records[0];
            $field_arr = array();
            foreach ($row as $key => $value) {
                $field_arr[] = $key;
            }
            $fields = implode(',', $field_arr);
        }
        header("Content-type: application/xml");
        $out =
             "<"."?xml version=\"1.0\" encoding=\"".$enc."\"?>\n"
            ."<!DOCTYPE records [\n"
            ."<!ELEMENT records (record*)>\n"
            ."<!ATTLIST records count CDATA #REQUIRED>\n"
            .($fields ? "<!ELEMENT record (".$fields."*)>\n" : "");
        if ($fields) {
            foreach ($field_arr as $f) {
                $out.="<!ELEMENT ".$f." (#PCDATA)>\n";
            }
        }
        $out.=
             "]>\n"
            ."<records count=\"".count($records)."\">\n";
        foreach ($records as $record) {
            $out.= "  <record>\n";
            foreach ($record as $field => $val) {
                $val =
                str_replace(
                    array('&'),
                    array('&amp;'),
                    $val
                );
                $out.=
                "   <".$field.">"
      //          ."<![CDATA["
                .$val
      //          ."]]>"
                ."</".$field.">\n";
            }
            $out.= '  </record>'."\n";
        }
        $out.= "</records>\n";
        return $out;
    }

    public function get_YYYYMMDD_to_format($YYYYMMDD, $format = "MMM DD, YYYY")
    {
        $date =
        adodb_mktime(
            (strlen($YYYYMMDD)==19 ? substr($YYYYMMDD, 11, 2) : 0),
            (strlen($YYYYMMDD)==19 ? substr($YYYYMMDD, 14, 2) : 0),
            (strlen($YYYYMMDD)==19 ? substr($YYYYMMDD, 17, 2) : 0),
            substr($YYYYMMDD, 5, 2),
            substr($YYYYMMDD, 8, 2),
            substr($YYYYMMDD, 0, 4)
        );
        switch($format) {
            case "MM DD YYYY":
                return date("M j Y", $date);
            break;
            case "MM DD, YYYY":
                return date("M j, Y", $date);
            break;
            case "MM DD YYYY h:mmXM":
                return date("M j Y g:ia", $date);
            break;
            case "MM DDD YYYY":
                return date("M j\<\s\u\p\>S\<\/\s\u\p\> Y", $date);
            break;
            case "MM DDD YYYY hh:mm":
                return date("M j\<\s\u\p\>S\<\/\s\u\p\> Y H:i", $date);
            break;
            case "MM DDD YYYY h:mmXM":
                return date("M j\<\s\u\p\>S\<\/\s\u\p\> Y g:ia", $date);
            break;
            case "MMM DD, YYYY":
                return date("F j, Y", $date);
            break;
            case "MMM DDD YYYY":
                return date("F j\<\s\u\p\>S\<\/\s\u\p\> Y", $date);
            break;
            default:
                return "Unexpected date format:".$format;
            break;
        }
        return "";
    }

    public function group_assign($csv_list = '')
    {
        if (!$this->_get_has_groups()) {
            return;
        }
        $systemID = $this->get_field('systemID');
        $Obj = new Group_Assign();
        $Obj->set_for_assignment($this->_get_assign_type(), $this->_get_ID(), $csv_list, $systemID);
        $data = array('group_assign_csv'=>$csv_list);
        $this->update($data, false, false); // don't set modified date - will be set elsewhere if needed
    }

    public function group_unassign()
    {
        if (!$this->_get_has_groups()) {
            return;
        }
        $Obj = new Group_Assign();
        $Obj->delete_for_assignment($this->_get_assign_type(), $this->_get_ID());
    }

    public function handle_report_delete(&$msg)
    {
        return $this->try_delete($msg);
    }

    public function increment($field, $validate = false)
    {
        $value = $this->get_field($field);
        if (!is_numeric($value)) {
            return false;
        }
        return $this->set_field($field, (int)$value+1, $validate);
    }

    public function insert($data, $validate = false)
    {
        if (!is_array($data)) {
            do_log(
                3,
                __CLASS__.'::'.__FUNCTION__.'()',
                'validate',
                "Object: ".$this->_get_object_name()."\nData not an Array"
            );
            print __CLASS__.'::'.__FUNCTION__.'() Data is not an array';
            return false;
        }
        if ($validate) {
            $classname = get_class($this);
            if (!is_array($validate_fields = $this->get_validation_fields())) {
                do_log(
                    3,
                    __CLASS__.'::'.__FUNCTION__.'()',
                    'validate',
                    "Object: ".$this->_get_object_name()."\n"
                    ."When validating you must set a static \$fields property (table: ".$this->_get_table_name().")"
                );
                print __CLASS__.'::'.__FUNCTION__.'() When validating you must set static $fields property';
                return false;
            }
        }
        $this->_set_ID($this->uniqID());
        $data['ID']=$this->_get_ID();
        $fixed_fields = $this->_get_fixed_fields();
        foreach ($fixed_fields as $key => $value) {
            if (!array_key_exists($key, $data)) {
                $data[$key]=$value;
            }
        }
        $now =      get_timestamp();
        if (!array_key_exists('systemID', $data)) {
            $data['systemID']=SYS_ID;
        }
        if (!array_key_exists('history_created_by', $data)) {
            $data['history_created_by']=get_userID();
        }
        if (!array_key_exists('history_created_date', $data)) {
            $data['history_created_date']=$now;
        }
        if (substr($this->_get_table_name(), 0, 4)=='cus_') {
            if ($this->exists_table_column('history_created_IP')) {
                $data['history_created_IP'] = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (!array_key_exists('history_created_IP', $data)) {
                $data['history_created_IP'] = $_SERVER['REMOTE_ADDR'];
            }
        }
        if ($this->_get_listTypeID() && !array_key_exists('listTypeID', $data)) {
            $data['listTypeID'] = $this->_get_listTypeID();
        }
        $this->xmlfields_encode($data);
        if (isset($data['XML_data'])) {
            $data['XML_data'] = Record::escape_string($data['XML_data']);
        }
        $sql_fields = array();
        foreach ($data as $key => $value) {
            if ($validate) {
                if (in_array($key, $validate_fields)) {
                    $sql_fields[] = "  `".$key."` = \"".$value."\"";
                }
            } else {
                $sql_fields[] = "  `".$key."` = \"".$value."\"";
            }
        }
        natcasesort($sql_fields);
        $sql =
         "INSERT INTO\n"
        ."  "
        .($this->_get_db_name() ? "`".$this->_get_db_name()."`." : "")
        ."`".$this->_get_table_name()."`\n"
        ."SET\n"
        .implode(",\n", $sql_fields);
        if (!$this->do_sql_query($sql)) {
            print draw_sql_debug(__CLASS__.'::'.__FUNCTION__.'()', $sql, Record::get_last_db_error_msg());
            do_log(
                3,
                __CLASS__.'::'.__FUNCTION__.'()',
                'execute',
                "Object: ".$this->_get_object_name()."\nQuery: ".$sql."\nError: ".Record::get_last_db_error_msg()
            );
            $this->_set_ID(false);
        }
        return $this->_get_ID();
    }

    public function is_visible($record)
    {
        $group_assign_csv = (isset($record['group_assign_csv']) ? $record['group_assign_csv'] : "");
        $isMASTERADMIN =    get_person_permission("MASTERADMIN", $group_assign_csv);
        $isUSERADMIN =      get_person_permission("USERADMIN", $group_assign_csv);
        $isCOMMUNITYADMIN = get_person_permission("COMMUNITYADMIN", $group_assign_csv);
        $isSYSADMIN =       get_person_permission("SYSADMIN", $group_assign_csv);
        $isSYSAPPROVER =    get_person_permission("SYSAPPROVER", $group_assign_csv);
        $isSYSEDITOR =      get_person_permission("SYSEDITOR", $group_assign_csv);
        $isSYSMEMBER =      get_person_permission("SYSMEMBER", $group_assign_csv);
        $isSYSLOGON =       get_person_permission("SYSLOGON", $group_assign_csv);
        $isPUBLIC =         get_person_permission("PUBLIC", $group_assign_csv);
        $isMember =(
            $isMASTERADMIN ||
            $isUSERADMIN ||
            $isCOMMUNITYADMIN ||
            $isSYSADMIN ||
            $isSYSAPPROVER ||
            $isSYSEDITOR ||
            $isSYSMEMBER
        );
    // These ones are JUST group based permission:
        $isGROUPEDITOR =    get_person_permission("GROUPEDITOR", $group_assign_csv);
        $isGROUPVIEWER =    get_person_permission("GROUPVIEWER", $group_assign_csv);
        $isVIEWER =         get_person_permission("VIEWER", $group_assign_csv);
        $is_visible =
            (isset($record['permPUBLIC'])        && $record['permPUBLIC']        && $isPUBLIC) ||
            (isset($record['permSYSLOGON'])      && $record['permSYSLOGON']      && $isSYSLOGON) ||
            (isset($record['permSYSEDITOR'])     && $record['permSYSEDITOR']     && $isSYSEDITOR) ||
            (isset($record['permSYSAPPROVER'])   && $record['permSYSAPPROVER']   && $isSYSAPPROVER) ||
            (isset($record['permSYSADMIN'])      && $record['permSYSADMIN']      && $isSYSADMIN) ||
            (isset($record['permCOMMUNITYADMIN'])&& $record['permCOMMUNITYADMIN']&& $isCOMMUNITYADMIN) ||
            (isset($record['permMASTERADMIN'])   && $record['permMASTERADMIN']   && $isMASTERADMIN) ||
            (isset($record['permGROUPVIEWER'])   && $record['permGROUPVIEWER']   && $isGROUPVIEWER) ||
            (isset($record['permGROUPEDITOR'])   && $record['permGROUPEDITOR']   && $isGROUPEDITOR) ||
            (isset($record['permSYSMEMBER'])     && $record['permSYSMEMBER']     && $isMember) ||
            (isset($record['permUSERADMIN'])     && $record['permUSERADMIN']     && $isUSERADMIN) ||
            ($isVIEWER);
        return ($is_visible ? 1 : 0);
    }

    public function is_visible_to_groups($record_csv, $group_csv)
    {
        $groups = explode(",", $group_csv);
        $record_groups = explode(",", $record_csv);
        foreach ($groups as $group) {
            if (in_array($group, $record_groups)) {
                return true;
            }
        }
        return false;
    }

    public function keyword_assign($csv = "")
    {
        if (!$this->_get_has_keywords()) {
            return;
        }
        $systemID = $this->get_field('systemID');
        $assign_arr = explode(",", str_replace(' ', '', $csv));
        sort($assign_arr);
        $csv = implode(', ', $assign_arr);
        $Obj = new Keyword_Assign;
        $Obj->set_for_assignment($this->_get_assign_type(), $this->_get_ID(), $csv, $systemID);
        $data = array('keywords'=>$csv);
        $this->update($data, false, false); // don't set modified date - will be set elsewhere if needed
    }

    public function keyword_unassign()
    {
        if (!$this->_get_has_keywords()) {
            return;
        }
        $Obj = new Keyword_Assign;
        $Obj->delete_for_assignment($this->_get_assign_type(), $this->_get_ID());
    }

    public function language_assign($csv = "")
    {
        if (!$this->_get_has_languages()) {
            return;
        }
        $systemID = $this->get_field('systemID');
        $assign_arr = explode(",", str_replace(' ', '', $csv));
        sort($assign_arr);
        $csv = implode(', ', $assign_arr);
        $Obj = new Language_Assign;
        $Obj->set_for_assignment($this->_get_assign_type(), $this->_get_ID(), $csv, $systemID);
        $data = array('languages'=>$csv);
        $this->update($data, false, false); // don't set modified date - will be set elsewhere if needed
    }

    public function language_unassign()
    {
        if (!$this->_get_has_languages()) {
            return;
        }
        $Obj = new Language_Assign;
        $Obj->delete_for_assignment($this->_get_assign_type(), $this->_get_ID());
    }

    public function load($data = false, $caching = false)
    {
        if ($data!==false) {
            $this->record = $data;
        } else {
            $this->record = $this->get_record($caching);
        }
        $this->_set_ID(isset($this->record['ID']) ? $this->record['ID'] : false);
        return $this->record;
    }

    public function manage_actions($report_name)
    {
        if (get_var('command')=='report') {
            return draw_auto_report($report_name, 1);
        }
        if (!$selectID = get_var('selectID')) {
            return
                 "<h3 style='margin:0.25em'>Actions for ".$this->_get_object_name()."</h3>"
                ."<p style='margin:0.25em'>No Actions - this ".$this->_get_object_name()
                ." has not been saved yet.</p>";
        }
        $this->_set_ID($selectID);
        if (!$this->exists()) {
            return
                 "<h3 style='margin:0.25em'>Actions for ".$this->_get_object_name()."</h3>"
                ."<p style='margin:0.25em'>No Actions - this ".$this->_get_object_name()
                ." appears to have been deleted.</p>";
        }
        return
             "<h3 style='margin:0.25em'>Actions for ".$this->_get_object_name()." \"".$this->get_name()."\"</h3>"
            .draw_auto_report($report_name, 1);
    }

    public function matched($data, $validate = false)
    {
        $sql_checks = array();
        if ($validate) {
            $classname = get_class($this);
            $validate_fields = $this->get_validation_fields();
        }
        foreach ($data as $key => $value) {
            if ($validate) {
                if (in_array($key, $validate_fields)) {
                    $sql_checks[] = "  `$key` = \"".$value."\"";
                }
            } else {
                $sql_checks[] = "  `$key` = \"".$value."\"";
            }
        }
        $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            .implode(" AND\n", $sql_checks);
        return $this->get_field_for_sql($sql);
    }

    public function on_action_set_map_location()
    {
        $ID_arr = explode(',', str_replace(' ', '', $this->_get_ID()));
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $coords = $this->get_coords();
            $this->update($coords);
        }
    }

    public function on_action_warn_if_invisible()
    {
        global $action_parameters;
        $ID_arr =   explode(",", $action_parameters['triggerID']);
        $errors =   array();
        $msg = array();
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            $r = $this->get_record();
            if (
            !(isset($r['permPUBLIC']) && $r['permPUBLIC']) &&
            !(isset($r['permSYSLOGON']) && $r['permSYSLOGON']) &&
            !(isset($r['permSYSMEMBER']) && $r['permSYSMEMBER']) &&
            !(isset($r['group_assign_csv']) && $r['group_assign_csv']!='')
            ) {
                $msg[] = $r['name'];
            }
        }
        if (!count($msg)) {
            return;
        }
        return
         "No-one has permission to see the following "
        .$this->_get_object_name()
        .(count($msg)==1 ? '' : $this->_plural_append_array[1])
        .":<ul><li>"
        .implode('</li><li>', $msg)
        ."</li></ul>";
    }

    public function plural($targetID)
    {
        return (count(explode(",", $targetID))>1 ? $this->_plural_append_array[1] : $this->_plural_append_array[0]);
    }

    public function push_product_assign($csv = "")
    {
        if (!$this->_get_has_push_products()) {
            return;
        }
        $systemID = $this->get_field('systemID');
        $Obj = new Push_Product_Assign;
        $assign_arr = explode(",", str_replace(' ', '', $csv));
        sort($assign_arr);
        $csv = implode(', ', $assign_arr);
        $Obj->set_for_assignment($this->_get_assign_type(), $this->_get_ID(), $csv, $systemID);
        $data = array('push_products'=>$csv);
        $this->update($data, false, false); // don't set modified date - will be set elsewhere if needed
    }

    public function push_product_unassign()
    {
        if (!$this->_get_has_push_products()) {
            return;
        }
        $Obj = new Push_Product_Assign;
        $Obj->delete_for_assignment($this->_get_assign_type(), $this->_get_ID());
    }

    public function _set_edit_param($param, $value)
    {
        $this->_edit_params_array[$param]=$value;
    }

    public function set_edit_params($value)
    {
        $this->_edit_params_array = array(
            'command_for_delete' =>     (isset($value['command_for_delete']) ?
                $value['command_for_delete']
             :
                ''
            ),
            'report' =>                 (isset($value['report']) ?
                $value['report']
              :
                ''
             ),
            'report_related_products' =>(isset($value['report_related_products']) ?
                $value['report_related_products']
             :
                false
            ),
            'report_rename' =>          (isset($value['report_rename']) ?
                $value['report_rename']
             :
                false
            ),
            'report_rename_label' =>    (isset($value['report_rename_label']) ?
                $value['report_rename_label']
             :
                ''
            ),
            'icon_delete' =>            (isset($value['icon_delete']) ?
                $value['icon_delete']
             :
                ''
            ),
            'icon_edit' =>              (isset($value['icon_edit']) ?
                $value['icon_edit']
             :
                ''
            ),
            'icon_edit_disabled' =>     (isset($value['icon_edit_disabled']) ?
                $value['icon_edit_disabled']
             :
                ''
            ),
            'icon_edit_popup' =>        (isset($value['icon_edit_popup']) ?
                $value['icon_edit_popup']
             :
                ''
            )
        );
    }

    public function set_field_date_add($field, $YYYY, $MM, $DD)
    {
        $YYYYMMDD = $this->get_field($field);
        $YYYYMMDD_arr = explode('-', $YYYYMMDD);
        $YYYYMMDD_arr[0]+=$YYYY;
        $YYYYMMDD_arr[1]+=$MM;
        $YYYYMMDD_arr[2]+=$DD;
        $YYYYMMDD = implode('-', $YYYYMMDD_arr);
        $this->set_field($field, $YYYYMMDD);
    }

    public function set_field($field, $value, $validate = true, $reveal_modification = true)
    {
  //    print "\$field=$field,\$value=$value,\$validate=$validate<br />";
        $data = array($field=>$value);
        return $this->update($data, $validate, $reveal_modification);
    }

    public function set_field_for_all($field, $value)
    {
        $sql =
             "UPDATE\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."SET\n"
            ."  `".$field."` = \"".$value."\"";
            $this->do_sql_query($sql);
        return Record::get_affected_rows();
    }

    public function set_field_on_value($field, $old_value_csv, $new_value)
    {
        $sql =
             "UPDATE\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."SET\n"
            ."  `".$field."` = ".$new_value."\n"
            ."WHERE\n"
            ."  `".$field."` IN(".$old_value_csv.")";
        $this->do_sql_query($sql);
        return Record::get_affected_rows();
    }

    public function set_group_concat_max_len($len = 1000000)
    {
        $len =  sanitize('range', $len, 4, 'n', 4294967295);
        $sql =
             "SET SESSION\n"
            ."  group_concat_max_len = ".$len;
        $this->do_sql_query($sql);
    }

    public function set_ID_by_name($name, $systemID = false, $no_cache = false)
    {
        $this->_set_ID($this->get_ID_by_name($name, $systemID, $no_cache));
    }

    public function set_plural_append($one, $many)
    {
        $this->_plural_append_array = array($one,$many);
    }

    public function sort_records($array, $fieldsort_arr)
    {
        $this->field_arr = $fieldsort_arr;
        usort($array, array($this,'sort_records_function'));
        return $array;
    }

    public function sort_records_function($a, $b)
    {
        foreach ($this->field_arr as $field) {
            $this->xmlfields_decode($a);
            $this->xmlfields_decode($b);
            switch ($field[1]) { // switch on ascending or descending value
                case "d":
                    if (is_numeric($a[$field[0]]) && is_numeric($b[$field[0]])) {
                        return (float)$b[$field[0]] - (float)$a[$field[0]];
                    } else {
                        $strc = strcmp(strtolower($b[$field[0]]), strtolower($a[$field[0]]));
                        if ($strc != 0) {
                            return $strc;
                        }
                    }
                    break;
                default:
                    if (is_numeric($a[$field[0]]) && is_numeric($b[$field[0]])) {
                        return (float)$a[$field[0]] - (float)$b[$field[0]];
                    } else {
                        $strc = strcmp(strtolower($a[$field[0]]), strtolower($b[$field[0]]));
                        if ($strc != 0) {
                            return $strc;
                        }
                    }
                    break;
            }
        }
        return 0;
    }

    public function sql_export(
        $targetID,
        $show_fields,
        $header = "",
        $orderBy = "",
        $extra_delete = "",
        $extra_select = ""
    ) {
        if (!(get_person_permission("MASTERADMIN") || get_person_permission("SYSADMIN"))) {
            return "Forbidden";
        }
        return
             $this->sql_export_delete($targetID, $show_fields, $header, $orderBy, $extra_delete, $extra_select)
            .$this->sql_export_select($targetID, $show_fields, $header, $orderBy, $extra_delete, $extra_select);
    }

    public function sql_export_delete(
        $targetID,
        $show_fields,
        $header = "",
        $orderBy = "",
        $extra_delete = "",
        $extra_select = ""
    ) {
        global $db;
        if ($header=="") {
            $header = "Selected ".$this->_get_object_name().$this->plural($targetID);
        }
        $table = ($this->_get_db_name()==$db ?
            $this->_get_table_name()
         :
            $this->_get_db_name()."`.`".$this->_get_table_name()
        );
        $table = pad("`".$table."`", 24);
        return
             $this->sql_header($header)
            .($extra_delete!="" ? trim($extra_delete, "\n")."\n" : "")
            .($this->_get_has_actions() ?
                 "DELETE FROM `action`                 "
                ."WHERE `sourceType`  = '".$this->_get_assign_type()."' AND `sourceID` IN (".$targetID.");\n"
             :
                ""
            )
            .($this->_get_has_activity() ?
                 "DELETE FROM `activity`               "
                ."WHERE `sourceType`  = '".$this->_get_assign_type()."' AND `sourceID` IN (".$targetID.");\n"
             :
                ""
            )
            .($this->_get_has_categories() ?
                 "DELETE FROM `category_assign`        "
                ."WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN (".$targetID.");\n"
             :
                ""
            )
            .($this->_get_has_groups() ?
                 "DELETE FROM `group_assign`           "
                ."WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN (".$targetID.");\n"
             :
                ""
            )
            .($this->_get_has_keywords() ?
                 "DELETE FROM `keyword_assign`         "
                ."WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN (".$targetID.");\n"
             :
                ""
            )
            .($this->_get_has_languages() ?
                 "DELETE FROM `language_assign`        "
                ."WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN (".$targetID.");\n"
             :
                ""
            )
            ."DELETE FROM ".pad($table, 25)
            ."WHERE ".($this->_get_listTypeID() ?
                "`listTypeID` = ".$this->_get_listTypeID()." AND "
            :
                ""
            )
            ."`ID` IN ($targetID);\n";
    }

    public function sql_export_select(
        $targetID,
        $show_fields,
        $header = "",
        $orderBy = "",
        $extra_delete = "",
        $extra_select = ""
    ) {
        global $db;
        $table = ($this->_get_db_name()==$db ?
            $this->_get_table_name()
        :
            $this->_get_db_name()."`.`".$this->_get_table_name()
        );
        $table = pad("`".$table."`", 24);

        $Obj = new Backup;
        return
             $this->sql_footer()
            .$Obj->db_export_sql_query(
                $table,
                "SELECT * FROM ".$table." WHERE "
                .($this->_get_listTypeID() ?
                    "`listTypeID` = ".$this->_get_listTypeID()." AND "
                :
                    ""
                )
                ."`ID` IN (".$targetID.")".$orderBy,
                $show_fields
            )
            .($this->_get_has_actions() ?
                $Obj->db_export_sql_query(
                    "`action`                ",
                    "SELECT * FROM `action`"
                    ." WHERE `sourceType` = '".$this->_get_assign_type()."' AND `sourceID` IN (".$targetID.")"
                    ." ORDER BY `seq`",
                    $show_fields
                )
             :
                ""
            )
            .($this->_get_has_activity() ?
                $Obj->db_export_sql_query(
                    "`activity`              ",
                    "SELECT * FROM `activity`"
                    ." WHERE `sourceType` = '".$this->_get_assign_type()."' AND `sourceID` IN (".$targetID.")"
                    ." ORDER BY `history_created_date`",
                    $show_fields
                )
             :
                ""
            )
            .($this->_get_has_categories()!='' ?
                $Obj->db_export_sql_query(
                    "`category_assign`       ",
                    "SELECT * FROM `category_assign`"
                    ." WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN(".$targetID.");",
                    $show_fields
                )
             :
                ""
            )
            .($this->_get_has_groups()!='' ?
                $Obj->db_export_sql_query(
                    "`group_assign`          ",
                    "SELECT * FROM `group_assign`"
                    ." WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN(".$targetID.");",
                    $show_fields
                )
             :
                ""
            )
            .($this->_get_has_keywords() ?
                $Obj->db_export_sql_query(
                    "`keyword_assign`        ",
                    "SELECT * FROM `keyword_assign`"
                    ." WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN (".$targetID.")",
                    $show_fields
                )
             :
                ""
            )
            .($this->_get_has_languages() ?
                $Obj->db_export_sql_query(
                    "`language_assign`       ",
                    "SELECT * FROM `language_assign`"
                    ." WHERE `assign_type` = '".$this->_get_assign_type()."' AND `assignID` IN (".$targetID.")",
                    $show_fields
                )
             :
                ""
            )
            .($extra_select!="" ? "$extra_select" : "")
            ."\n";
    }

    public function sql_header($text)
    {
        global $db,$system_vars;
        $lines_arr = explode("\n", $text);
        for ($i=0; $i<count($lines_arr); $i++) {
            if ($i==0) {
                $lines_arr[$i]= pad($lines_arr[$i], 55);
            } else {
                $lines_arr[$i]= "# *              ".pad($lines_arr[$i], 55);
            }
        }
        $text = implode("*\n", $lines_arr);
        return
             "# ***********************************************************************\n"
            ."# * Database Export File                                                *\n"
            ."# ***********************************************************************\n"
            ."# * Collection:  ".$text."*\n"
            ."# * Web Server:  ".pad(getenv("SERVER_NAME"), 54)." *\n"
            ."# * Database:    ".pad($db.' (Version: '.$system_vars['db_version'].')', 54)." *\n"
            ."# * Date:        ".pad(strftime('%a %d/%m/%Y %H:%M:%S', time()), 54)." *\n"
            ."# ***********************************************************************\n"
            ."\n"
            ."# *************************\n"
            ."# * Delete existing data: *\n"
            ."# *************************\n";
    }

    public function sql_footer()
    {
        return
             "\n\n"
            ."# *************************\n"
            ."# * Import new data:      *\n"
            ."# *************************\n";
    }

    public function touch()
    {
        $data = array();
        $this->update($data);
    }

    public function try_copy(&$newID, &$msg, &$msg_tooltip, $name = false, $global = false)
    {
        $targetSystemID = (get_person_permission("MASTERADMIN") ? false : SYS_ID);
        $args =
        array(
            'targetID' => $this->_get_ID(),
            'targetValue' => $name
        );
        $singular = $this->plural(1);
        if ($name==="") {
            switch($this->_get_name_field()){
                case "subject":
                    $this->get_status_text("subject_required", $args, $msg, $msg_tooltip);
                    break;
                default:
                    $this->get_status_text("name_required", $args, $msg, $msg_tooltip);
                    break;
            }
            return false;
        }
        if ($name!==false && !$global) {
            if ($this->exists_named($name, $targetSystemID)) {
                $this->get_status_text("name_exists_local", $args, $msg, $msg_tooltip);
                return false;
            }
        }
        $sourceSystemID = $this->get_field((is_a($this, 'System') ? 'ID' : 'systemID'));
        if (!$targetSystemID) {
            $targetSystemID = $sourceSystemID;
            if ($name!==false && $global && $this->exists_named($name)) {
                $this->get_status_text("name_exists_global", $args, $msg, $msg_tooltip);
                return false;
            }
        }
        $newID = $this->copy($name);
        if ($newID) {
            if ($targetSystemID && $sourceSystemID!=$targetSystemID) {
                $this->_set_ID($newID);
                $this->set_field('systemID', $targetSystemID, false, false);
            }
            $msg =         status_message(
                0,
                true,
                $this->_get_object_name().$singular,
                $this->_get_message_associated(),
                ($name ? "been copied to ".$name : "been duplicated."),
                $this->_get_ID()
            );
            $msg_tooltip = status_message(
                0,
                false,
                $this->_get_object_name().$singular,
                $this->_get_message_associated(),
                ($name ? "been copied to ".$name : "been duplicated."),
                $this->_get_ID()
            );
            return true;
        }
        return false;
    }

    public function try_delete(&$msg)
    {
        $this->delete();
        $msg = status_message(
            0,
            true,
            $this->_get_object_name(),
            $this->_get_message_associated(),
            'been deleted.',
            $this->_get_ID()
        );
        return true;
    }

    public function uniqID()
    {
        while (true) {
            $newID =  mt_rand(1, mt_getrandmax());
            $sql =
             "SELECT\n"
            ."  COUNT(*)\n"
            ."FROM\n"
            ."  `".$this->_get_db_name()."`.`".$this->_get_table_name()."`\n"
            ."WHERE\n"
            ."  `ID` = ".$newID;
            if ($this->get_field_for_sql($sql)==0) {
                return $newID;
            }
        }
    }

    public function update($data, $validate = false, $reveal_modification = true)
    {
    // Note: reverts to insert() if ID not set
        if ($this->_get_ID()=="") {
            return $this->insert($data);
        }
        if (!is_array($data)) {
            do_log(
                3,
                __CLASS__.'::'.__FUNCTION__.'()',
                'validate',
                "Object: ".$this->_get_object_name()."\nData is not an array"
            );
            print __CLASS__.'::'.__FUNCTION__.'() Data is not an array';
            return false;
        }
        if ($validate) {
            if (!is_array($validate_fields = $this->get_validation_fields())) {
                do_log(
                    3,
                    __CLASS__.'::'.__FUNCTION__.'()',
                    'validate',
                    "Object: ".$this->_get_object_name()."\n"
                    ."When validating you must set a FIELDS constant for the associated object "
                    ."(table: ".$this->_get_table_name().")"
                );
                print __CLASS__.'::'.__FUNCTION__.'() When validating you must set static $fields property';
                return false;
            }
        }
        $key = $this->_get_db_name()."_".$this->_get_table_name()."_".$this->_get_ID();
        if (isset(Record::$cache_record_array[$key])) {
            unset(Record::$cache_record_array[$key]);
        }
        $now =      get_timestamp();
        if (!array_key_exists('history_modified_by', $data) && $reveal_modification) {
            $data['history_modified_by']=get_userID();
        }
        if (!array_key_exists('history_modified_date', $data) && $reveal_modification) {
            $data['history_modified_date']=$now;
        }
        if (substr($this->_get_table_name(), 0, 4)=='cus_'  && $reveal_modification) {
            if ($this->exists_table_column('history_modified_IP')) {
                $data['history_modified_IP'] = $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (
                !array_key_exists('history_modified_IP', $data) &&
                isset($_SERVER['REMOTE_ADDR']) && $reveal_modification
            ) {
                $data['history_modified_IP'] = $_SERVER['REMOTE_ADDR'];
            }
        }
        $hasXML = false;
        foreach ($data as $key => $value) {
            if (substr($key, 0, 4)=='xml:') {
                $hasXML = true;
                break;
            }
        }
        if ($hasXML) {
            $this->xmlfields_decode($data);
            $this->xmlfields_slash_to_colon_delimiter($data);
        }
        $ID_arr = explode(',', $this->_get_ID());
        foreach ($ID_arr as $ID) {
            $this->_set_ID($ID);
            if ($hasXML) {
                $xml_data = (is_a($this, 'Page') ? '' : $this->get_field('XML_data'));
                $old_record = array('XML_data' => $xml_data);
                $this->xmlfields_decode($old_record);
                $_data = array_merge($old_record, $data);
                $this->xmlfields_encode($_data);
            } else {
                $_data = $data;
            }
            $sql_fields = array();
            foreach ($_data as $key => $value) {
                if ($validate) {
                    if (in_array($key, $validate_fields)) {
                        $sql_fields[] = "  `$key` = \"".$value."\"";
                    }
                } else {
                    $sql_fields[] = "  `$key` = \"".$value."\"";
                }
            }
            natcasesort($sql_fields);
            $sql =
            "UPDATE\n"
            ."  "
            .($this->_get_db_name() ? "`".$this->_get_db_name()."`." : "")
            ."`".$this->_get_table_name()."`\n"
            ."SET\n"
            .implode(",\n", $sql_fields)."\n"
            ."WHERE\n"
            ."  `ID`=".$ID."";
    //      z($sql); die;
            if (!$this->do_sql_query($sql)) {
                do_log(
                    3,
                    __CLASS__.'::'.__FUNCTION__.'()',
                    'execute',
                    "Object: ".$this->_get_object_name()."\nQuery: ".$sql."\nError: ".Record::get_last_db_error_msg()
                );
                print draw_sql_debug("Record::update()", $sql, Record::get_last_db_error_msg());
                $this->_set_ID(false);
            }
        }
        return implode(',', $ID_arr);
    }

    public function xmlfields_encode(&$row)
    {
      // By James Fraser
      // takes an array of fieldname => fieldvalue pairs,
   // locates the xml: fields
   // converts those fields to a dom
   // remove those fields
   // add a new field "XML_data" that contains the dom
        $delimiter = ':';
        $keys = array_keys($row);
        $xml_keys = preg_grep("/^xml:/", $keys);
        if (!count($xml_keys)) {
            return;
        }
        $dd = new DOMDocument('1.0', 'iso-8859-1');
        $xp = new DOMXPath($dd);
        $dd->appendChild($dd->createElement('xml'));
        foreach ($xml_keys as $key) {
            $path = str_replace(array('xml:','/'), array('',':'), $key);
            $pathBits = explode($delimiter, $path);
            $currentNode = $dd->documentElement;
            foreach ($pathBits as $bit) {
                if (strlen($bit) > 0) {
      // in case of double slashes?
                    $nodeList = $xp->query($bit, $currentNode);
                    if ($nodeList->length == 0) {
                        $currentNode->appendChild($dd->createElement($bit));
                    }
                    $currentNode = $xp->query($bit, $currentNode)->item(0);
                }
            }
            $currentNode->appendChild($dd->createCDATASection($row[$key]));
            unset($row[$key]);
        }
        $row['XML_data'] = $dd->saveXML();
        $row['XML_data'] =
            str_replace(
                "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>",
                "<?xml version='1.0' encoding='iso-8859-1'?>",
                $row['XML_data']
            );
    }

    public function xmlfields_decode(&$row)
    {
      // By James Fraser
      // takes an array of fieldname => fieldvalue pairs,
      // locates the XML_data field
      // creates xml: fields for each value found
        if (!isset($row['XML_data']) || !$row['XML_data']) {
            unset($row['XML_data']);
            return false;
        }
        $dd = new DOMDocument;
        $dd->loadXML($row['XML_data']);
        $xmlPairs = $this->xmlfields_decodeRecursive($dd->documentElement);
        $row = array_merge($row, $xmlPairs);
        unset($row['XML_data']);
        return true;
    }

    public function xmlfields_decodeRecursive($el, $key = 'xml:')
    {
      // By James Fraser
        $delimiter = ':';
        $pairs = array();
        if ($el->firstChild && $el->firstChild->nodeType == XML_CDATA_SECTION_NODE) {
          // we have a value in CDATA section
            $pairs[$key] = $el->firstChild->nodeValue;
        } else {
          // we have a value without CDATA section (not ideal)
    //      $pairs[$key] = $el->nodeValue;
        }
        foreach ($el->childNodes as $child) {
            if ($child->nodeType == XML_ELEMENT_NODE) {
                $childList =
                $this->xmlfields_decodeRecursive(
                    $child,
                    ($key == 'xml:' ? $key : $key.$delimiter)
                    .$child->nodeName
                );
                $pairs = array_merge($pairs, $childList);
            }
        }
        return $pairs;
    }

    public function xmlfields_slash_to_colon_delimiter(&$row)
    {
        $out = array();
        foreach ($row as $key => $value) {
            if (substr($key, 0, 4)=='xml:') {
                $key = 'xml:'.str_replace('/', ':', substr($key, 4));
            }
            $out[$key] = $value;
        }
        $row = $out;
    }

    public function get_version()
    {
        return VERSION_RECORD;
    }
}
