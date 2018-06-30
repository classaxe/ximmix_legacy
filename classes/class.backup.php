<?php
define("VERSION_BACKUP","1.1.9");
/*
Version History:
  1.1.9 (2012-12-03)
    1) Backup::db_backup() now uses time() not mktime() as per strict standards
       compliance
  1.1.8 (2012-09-14)
    1) Backup::db_export_sql_data() now uses Table::do_sql_query() to use UTF8

  (Older version history in class.backup.txt)
*/

class Backup extends Record{
  function db_backup($local=true,$orderBy=false,$structure=true,$tableNames=false,$filename="export.sql") {
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=".$filename);
    global $dsn;
    $b = parse_url($dsn);
    $db = trim($b['path'],'/');
    set_time_limit(600);	// Extend maximum execution time to 10 mins
    $spaces =	    str_repeat(" ",56);
    $Obj =          new System(SYS_ID);
    $db_version =   $Obj->get_field('db_version');
    $date =	        time();
    $server =	    getenv("SERVER_NAME");
    $filename =
      ($filename=="" ?
        $db."_".strftime('%Y%m%d_%H%M',$date)."_v".$db_version.".sql"
      : $filename);
    $records = $Obj->get_records();
    foreach ($records as $record) {
      $rows[] = $record;
    }
    $max_len_ID =             0;
    $max_len_txt =            0;
    for ($i=0; $i<count($rows); $i++) {
      $row = $rows[$i];
      if (strlen($row['ID'])>$max_len_ID) { $max_len_ID = strlen($row['ID']); }
      if (strlen($row['textEnglish'])>$max_len_txt) { $max_len_txt = strlen($row['textEnglish']); }
    }
    $out = "";
    for ($i=0; $i<count($rows); $i++) {
      $row = $rows[$i];
      $out.=
        "#  "
        .substr($row['textEnglish'].$spaces,0,$max_len_txt+2)
        .substr($row['ID'].$spaces,0,$max_len_ID+2)
        .$row['URL']
        ."\n";
    }
    print
       "# ***********************************************************************\n"
      ."# * Database Export File                                                *\n"
      ."# ***********************************************************************\n"
      ."# * Filename:   ".substr($filename.$spaces,0,55)." *\n"
      ."# * Server:     ".substr($server.$spaces,0,55)." *\n"
      ."# * DB Version: ".substr($db_version.$spaces,0,55)." *\n"
      ."# * Date:       ".substr(strftime('%a %d/%m/%Y %H:%M:%S',$date).$spaces,0,55)." *\n"
      ."# ***********************************************************************\n"
      ."# Systems included:\n"
      ."# ----------------------------------------------------------------------\n"
      ."#  ".substr('Title'.$spaces,0,$max_len_txt+2).substr('ID'.$spaces,0,$max_len_ID+2)."URL\n"
      ."# ----------------------------------------------------------------------\n"
      .$out
      ."# ----------------------------------------------------------------------\n"
      ."#\n"
      ."# Remember to triple escape any single quotes in data if manually editing:\n"
      ."#    e.g. \"The user\\\\\'s account\"\n"
      ."\n";
    if ($structure) {
      $this->db_export_sql_structure($tableNames);
    }
    flush();
    $this->db_export_sql_data($tableNames,$orderBy);
    return $filename;
  }

  function db_export_sql_query($table,$sql,$showFields=false) {
    set_time_limit(600);	// Extend maximum execution time to 10 mins
    $Obj = new Record;
    $Obj->do_sql_query('SET NAMES utf8');
    $records = $Obj->get_rows_for_sql($sql);
    if (!count($records)){		// If there was no match, skip adding data
      return "# (No data for ".trim($table).")\n";
    }
    $columns = $Obj->get_table_fields($table);
    $out = "";
    foreach ($records as $record) {
      $fields = array();
      for ($i=0; $i<count($columns); $i++) {
        $fields[] = "`".$columns[$i]['Field']."`";
      }
      $line =		array();
      for ($i=0; $i<count($record); $i++) {
        if (isset($columns[$i])) {
          if ($record[$i]===null){
            $line[] =	"NULL";
          }
          else if($columns[$i]['number']){
            $line[] =	$record[$i];
          }
          else {
            $line[] =	"'".Record::escape_string($record[$i])."'";
          }
        }
      }
      $out.=
         "INSERT INTO $table "
        .($showFields ? "(".implode($fields,",").") " : "")
        ."VALUES (".implode($line,",").");\n";
    }
    return $out;
  }

  function db_export_sql_data($tableNames=false,$orderBy=false) {
    set_time_limit(600);	// Extend maximum execution time to 10 mins
    $out =	        array();
    $tables =	    array();
    $Obj_Table =    new Table;
    if (!$tableNames) {
      $tables = $Obj_Table->get_tables_names();
    }
    else {
      $tableNames_arr =	explode(',',$tableNames);
      foreach ($tableNames_arr as $tableName) {
        $tables[] =   array('Name'=>$tableName);
      }
    }
    for ($i=0; $i<count($tables); $i++) {
      $tables[$i]['columns'] = $Obj_Table->get_table_fields($tables[$i]['Name']);
    }
    print
       "# ************************************\n"
      ."# * Table Data:                      *\n"
      ."# ************************************\n";
    foreach ($tables as $table) {
      print
         "\n"
        ."# ".$table['Name'].":\n";
      $Obj_Table->do_sql_query('SET NAMES utf8');
      $sql =
         "SELECT * FROM `".$table['Name']."`\n"
        .($orderBy? "ORDER BY $orderBy" : '');
      $arrays = $Obj_Table->get_rows_for_sql($sql);
      if ($arrays===false) {
        return;
      }
      if (!count($arrays))	{		// If there was no match, skip adding data
        print "# (No data)\n";
      }
      else {
        $data =		array();
        foreach ($arrays as $array){
          $line =		array();
          for ($j=0; $j<count($array); $j++) {
            if ($array[$j]===null){
              $line[] =	"NULL";
            }
            else if($table['columns'][$j]['number']){
              $line[] =	$array[$j];
            }
            else {
              $line[] =	"'".addslashes($array[$j])."'";
            }
          }
          print	"INSERT IGNORE INTO `".$table['Name']."` VALUES ";
          print	"(".implode($line,",").");\n";
        }
      }
      flush();
    }
    print
       "# ************************************\n"
      ."# * (End of Table Data)              *\n"
      ."# ************************************\n";
    return true;
  }

  function db_export_sql_structure($noprint=false,$drop=true) {
    // Does not wait for table locks.
    set_time_limit(600);	// Extend maximum execution time to 10 mins
    $Obj = new Table();
    $tables = $Obj->get_tables_names();
    for ($i=0; $i<count($tables); $i++) {
      $tables[$i]['Data'] = $Obj->get_table_create_sql($tables[$i]['Name']);
    }
    $out =
       "# ************************************\n"
      ."# * Table Structures:                *\n"
      ."# ************************************\n";

    for($i=0; $i<count($tables); $i++) {
      $table =	$tables[$i];
      $out.=
         ($drop ? "DROP TABLE IF EXISTS `".$tables[$i]['Name']."`;\n" : "")
  	     .$tables[$i]['Data'].";\n\n";
    }
    $out.=
       "# ************************************\n"
      ."# * (End of Table Structures)        *\n"
      ."# ************************************\n"
      ."\n";
    if ($noprint) {
      return $out;
    }
    print $out;
  }

  // ************************************
  // * db_split_sql()                   *
  // ************************************
  // Adapted from code used in phpMyAdmin -
  // a GPL project hosted at http://sourceforge.net/projects/phpmyadmin
  function db_split_sql($sql) {
    set_time_limit(600);	// Extend maximum execution time to 10 mins
    $out =		array();
    $sql =		trim($sql);
    $sql_len =		strlen($sql);
    $char =		'';
    $string_start =	'';
    $in_string =		false;
    $time0 =		time();

    for ($i = 0; $i < $sql_len; ++$i) {
      $char =		$sql[$i];
      // We are in a string, check for not escaped end of strings except for
      // backquotes that can't be escaped
      if ($in_string) {
        for (;;) {
          $i =		strpos($sql, $string_start, $i);
          // No end of string found -> add the current substring to the
          // returned array
          if (!$i) {
             $out[] =	$sql;
             return	$out;
          }
          // Backquotes or no backslashes before quotes: it's indeed the
          // end of the string -> exit the loop
          else if ($string_start == '`' || $sql[$i-1] != '\\') {
            $string_start =	'';
            $in_string =	FALSE;
            break;
          }
          // one or more Backslashes before the presumed end of string...
          else {
            // ... first checks for escaped backslashes
            $j = 2;
            $escaped_backslash = false;
            while ($i-$j > 0 && $sql[$i-$j] == '\\') {
              $escaped_backslash = !$escaped_backslash;
              $j++;
            }
            // ... if escaped backslashes: it's really the end of the
            // string -> exit the loop
            if ($escaped_backslash) {
               $string_start  = '';
               $in_string     = FALSE;
               break;
            }
            // ... else loop
            else {
              $i++;
            }
          } // end if...elseif...else
        } // end for
      } // end if (in string)

      // We are not in a string, first check for delimiter...
      else if ($char == ';') {
        // if delimiter found, add the parsed part to the returned array
        $out[]      = substr($sql, 0, $i);
        $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
        $sql_len    = strlen($sql);
        if ($sql_len) {
          $i      = -1;
        } else {
          // The submited statement(s) end(s) here
          return $out;
        }
      } // end else if (is delimiter)

      // ... then check for start of a string,...
      else if (($char == '"') || ($char == '\'') || ($char == '`')) {
        $in_string    = TRUE;
        $string_start = $char;
      } // end else if (is start of string)

      // ... for start of a comment (and remove this comment if found)...
      else if ($char == '#' || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')) {
        // starting position of the comment depends on the comment type
        $start_of_comment = (($sql[$i] == '#') ? $i : $i-2);
        // if no "\n" exits in the remaining string, checks for "\r"
        // (Mac eol style)
        $end_of_comment   = (strpos(' ' . $sql, "\012", $i+2)) ?
                             strpos(' ' . $sql, "\012", $i+2) :
                             strpos(' ' . $sql, "\015", $i+2);
        if (!$end_of_comment) {
          // no eol found after '#', add the parsed part to the returned
          // array if required and exit
          if ($start_of_comment > 0) {
            $out[]    = trim(substr($sql, 0, $start_of_comment));
          }
          return $out;
        }
        else {
          $sql =	substr($sql, 0, $start_of_comment).ltrim(substr($sql, $end_of_comment));
          $sql_len      = strlen($sql);
          $i--;
        } // end if...else
      } // end else if (is comment)

      // loic1: send a fake header each 30 sec. to bypass browser timeout
      $time1     = time();
      if ($time1 >= $time0 + 30) {
        $time0 = $time1;
        header('X-pmaPing: Pong');
      } // end if
    } // end for

    // add any rest to the returned array
    if (!empty($sql) && preg_match('/[^[:space:]]+/', $sql)) {
      $out[] = $sql;
    }
    return $out;
  }

  public function get_version(){
    return VERSION_BACKUP;
  }
}
?>
