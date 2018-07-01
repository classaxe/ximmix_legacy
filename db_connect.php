<?php
define("DB_CONNECT", "1.0.2b");
/*
Version History:
  1.0.3 (2018-06-30)
    1) Remobed references to mysql functions not supported in PHP 7
  1.0.2 (2014-12-30)
    1) Now conforms to PSR-2
  1.0.1 (2013-05-27)
    1) Bug fix for error checking on conection failure
  1.0.0 (2012-09-10)
    1) Initial release
*/

function db_connect()
{
    global $db, $dsn, $li, $Obj_MySQLi;
    $b = parse_url($dsn);
    $db = trim($b['path'], '/');
    @$Obj_MySQLi = new MySQLi($b['host'], $b['user'], $b['pass'], $db);
    if ($Obj_MySQLi -> connect_errno > 0) {
        die("<b>Fatal error:</b><br />\n".$Obj_MySQLi->connect_error);
    }
}
db_connect();