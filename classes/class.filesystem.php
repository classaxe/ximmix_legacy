<?php
define('VERSION_FILESYSTEM', '1.0.17');
/*
Version History:
  1.0.17 (2015-01-11)
    1) Changes to FileSystem::get_file_changes() to deal with unix-style line endings in classes
    2) Now PSR-2 Compliant

  (Older version history in class.filesystem.txt)
*/
class FileSystem
{
    public function delete_dir_entry($key, $value)
    {
        if (substr($key, 0, 10)=='dir_entry_' && $value=='1') {
            $crc32 = substr($key, 10);
            $dirTree = $this->get_dir_tree(SYS_LOGS);
            $result = $this->in_dir_tree($dirTree, $crc32);
            if ($result!=false) {
                if ($result['type']=='d') {
                    rmdir($result['path'].$result['name']);
                } else {
                    unlink($result['path'].$result['name']);
                }
                return true;
            }
        }
        return false;
    }

    public function dir_getMatchingFiles($files, $search)
    {
    // Thanks to: tmm at aon dot at (see http://ca2.php.net/manual/en/function.glob.php)
      // Split to name and filetype
        if (strpos($search, ".")) {
            $baseexp=substr($search, 0, strpos($search, "."));
            $typeexp=substr($search, strpos($search, ".")+1, strlen($search));
        } else {
            $baseexp=$search;
            $typeexp="";
        }
      // Escape all regexp Characters
        $baseexp=preg_quote($baseexp);
        $typeexp=preg_quote($typeexp);
      // Allow ? and *
        $baseexp=str_replace(array("\*","\?"), array(".*","."), $baseexp);
        $typeexp=str_replace(array("\*","\?"), array(".*","."), $typeexp);
        $matches = array();
        if ($files) {
          // Search for Matches
            $i=0;
            foreach ($files as $file) {
                $filename=basename($file);
                if (strpos($filename, ".")) {
                    $base=substr($filename, 0, strpos($filename, "."));
                    $type=substr($filename, strpos($filename, ".")+1, strlen($filename));
                } else {
                    $base=$filename;
                    $type="";
                }
                if (preg_match("/^".$baseexp."$/i", $base) && preg_match("/^".$typeexp."$/i", $type)) {
                    $matches[$i]=$file;
                    $i++;
                }
            }
        }
        return $matches;
    }

    public function dir_getContents($dir, $files = array())
    {
        if (!($res=opendir($dir))) {
            exit("$dir doesn't exist!");
        }
        while (($file=readdir($res))==true) {
            if ($file!="." && $file!="..") {
                if (is_dir("$dir/$file")) {
                    $files=FileSystem::dir_getContents("$dir/$file", $files);
                } else {
                    array_push($files, "$dir/$file");
                }
            }
        }
        closedir($res);
        return $files;
    }

    public function dir_wildcard_search($path, $pattern)
    {
        return self::dir_getMatchingFiles(FileSystem::dir_getContents($path), $pattern);
    }

    public function draw_dir_tree($dir_array, $level = 0, $checkboxes = false, $title = 'Files', $expanded = 0)
    {
        global $page_vars;
        $get = BASE_PATH.trim($page_vars['path'], '/')."/?command=get_file";
        $out = '';
        if ($level==0) {
            $out.=
             "<script type=\"text/javascript\" src=\"".BASE_PATH."sysjs/treeview\"></script>\n"
            ."<script type=\"text/javascript\">\n"
            ."//<![CDATA[\n"
            .Link::draw_treeview_js($expanded)
            ."foldersTree = gFld(\"$title\", \"javascript:void(0)\");\n"
            ."foldersTree.xID = \"javascript:void(0)\";\n";
        }
        foreach ($dir_array as $entry) {
            if (isset($entry['type'])) {
                $_get = $get."&ID=".$entry['crc32'];
                switch ($entry['type']) {
                    case 'd':
                        $out.=
                             str_repeat("  ", $level)."  L".($level+1)." = insFldX("
                            .($level==0 ? "foldersTree" : "L$level")
                            .", gFld(\"".$entry['name']."\",\"\")"
                            .");\n"
                            .str_repeat("  ", $level)."  L".($level+1).".xID = \"".$_get."\";\n";
                        break;
                    case 'f':
                        $out.=
                             str_repeat("  ", $level)."  L".($level+1)." = insDocX("
                            .($level==0 ? "foldersTree" : "L$level")
                            .", gLnk(\"R\",\"".$entry['name']."\", \"".$_get."\")"
                            .");\n"
                            .str_repeat("  ", $level)."  L".($level+1).".xID = \"".$_get."\";\n";
                        $ext_arr = explode(".", $entry['name']);
                        $out.=
                             str_repeat("  ", $level)
                            ."  L".($level+1).".iconSrc = \"".get_icon_for_extension(array_pop($ext_arr))
                            .($checkboxes ? "" : "?width=20")
                            ."\";\n";
                        break;
                }
                if ($checkboxes==true) {
                    $out.=
                         str_repeat("  ", $level)."  L".($level+1)
                        .".prependHTML = \""
                        ."<td class='va_m'>"
                        ."<input type='checkbox' name='dir_entry_".($entry['crc32'])."' value='1'"
                        .(isset($_REQUEST['dir_entry_'.$entry['crc32']]) && $_REQUEST['dir_entry_'.$entry['crc32']]==1 ?
                            " checked=\"checked\""
                         :
                            ""
                         )
                        ." \/><\/td>\";\n";
                }
            } else {
                $out.= $this->draw_dir_tree($entry, $level+1, $checkboxes, '', $expanded);
            }
        }
        if ($level==0) {
            $out.=
             "initializeDocument();\n"
            ."//]]>\n"
            ."</script>\n";
        }
        return $out;
    }

    public function file_append($name, $data)
    {
        if ($fp=fopen($name, 'a')) {
            fwrite($fp, $data, strlen($data));
            fclose($fp);
            return true;
        }
        return false;
    }

    public function get_dir_tree($dir)
    {
        $d = dir($dir);
        $out = array();
        $entries = array();
        while (($entry = $d->read())!== false) {
            switch ($entry) {
                case '.':
                case '..':
                case '.htaccess':
                  // do nothing
                    break;
                default:
                    $ext_arr = explode(".", $entry);
                    switch ($ext_arr[count($ext_arr)-1]){
                        case 'filepart':
                          // do nothing
                            break;
                        default:
                            $entries[] =
                            array(
                            'name'=>$entry,
                            'dir'=>is_dir($dir.$entry)
                            );
                            break;
                    }
                    break;
            }
        }
        $order_arr =
        array(
        array('dir','d'),
        array('name','a')
        );
        $Obj = new Record();
        $entries = $Obj->sort_records($entries, $order_arr);

        foreach ($entries as $entry) {
            $name = $entry['name'];
            $path = $dir.$name;
            if ($entry['dir']) {
                $out[] = array(
                    'name' => $name,
                    'type' => 'd',
                    'size' => 0,
                    'path' => $dir,
                    'crc32' => dechex(crc32($path))
                );
                $out[] = $this->get_dir_tree($path.'/');
            } else {
                $out[] = array(
                    'name' => $name,
                    'type' => 'f',
                    'size' => filesize($path),
                    'path' => $dir,
                    'crc32' => dechex(crc32($path))
                );
            }
        }
        $d->close();
        return $out;
    }

    public function get_file($ID)
    {
      // ID here is the CRC32 of path + file
        global $page_vars;
        $this->ID = $ID;
        $this->page_vars = $page_vars;
        if ($this->_get_file_test_page_404()) {
            die;
        }
        if ($this->_get_file_test_page_403()) {
            die;
        }
        if ($this->_get_file_test_has_no_folderviewer()) {
            die;
        }
        if (get_person_permission("MASTERADMIN") && $this->page_vars['path']=='//details/system/'.SYS_ID) {
            $dir = SYS_LOGS;
        } else {
            $ident =            "folder_viewer";
            $parameter_spec =   array(
                'dir' => array('default'=>'./UserFiles/File/',    'hint'=>'Directory')
            );
            $cp_settings =
            Component_Base::get_parameter_defaults_and_values(
                $ident,
                '',
                false,
                $parameter_spec,
                array()
            );
            $cp_defaults =      $cp_settings['defaults'];
            $cp =               $cp_settings['parameters'];
            $dir =              '.'.BASE_PATH.trim($cp['dir'], './').'/';
        }
        $filePath =   "";
        $fileName =   "";
        if (is_dir($dir)) {
            $dirTree = $this->get_dir_tree($dir);
            $result = $this->in_dir_tree($dirTree, $this->ID);
            if ($result!=false && $result['type']=='f') {
                $filePath = $result['path'];
                $fileName = $result['name'];
            }
        }
        if ($filePath=="" || $fileName=="") {
            Page::do_tracking("404");
            do_log(
                2,
                __CLASS__."::".__FUNCTION__."()",
                '',
                "404 file not found for file ID ".$this->ID." for ".$_SERVER["REQUEST_URI"]
            );
            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
            print "<h1>404</h1><p>The resource ".$_SERVER['REQUEST_URI']." wasn't found here.</p>";
            die;
        }
        do_log(
            0,
            __CLASS__."::".__FUNCTION__."()",
            '',
            "200 streamed file ".$this->ID." \"".$filePath.$fileName."\" for ".$_SERVER["REQUEST_URI"]
        );
        $ext_arr = explode(".", $fileName);
        $ext = array_pop($ext_arr);
        header_mimetype_for_extension($ext);
        header("Content-Disposition: attachment;filename=\"".$fileName."\"");
        header("Content-Length: ".@filesize($filePath.$fileName));
        FileSystem::readfile_chunked($filePath.$fileName);
        die;
    }

    private function _get_file_test_page_403()
    {
        if ($this->page_vars['object_type']=='Page') {
            $Obj = new Page($this->page_vars['ID']);
            if (!$Obj->is_visible($this->page_vars)) {
                Page::do_tracking("403");
                do_log(
                    2,
                    __CLASS__."::".__FUNCTION__."()",
                    '',
                    "403 Page not found when trying to download file ID ".$this->ID." from ".$_SERVER["REQUEST_URI"]
                );
                header($_SERVER["SERVER_PROTOCOL"]." 403 Not Found", true, 403);
                print "<h1>403</h1><p>The resource ".$_SERVER['REQUEST_URI']." isn't available to you.</p>";
                return true;
            }
        }
        return false;
    }

    private function _get_file_test_page_404()
    {
        if (!isset($this->page_vars['status']) || $this->page_vars['status']!='404') {
            return false;
        }
        Page::do_tracking("404");
        do_log(
            2,
            __CLASS__."::".__FUNCTION__."()",
            '',
            "404 Page not found when trying to download file ID ".$this->ID." from ".$_SERVER["REQUEST_URI"]
        );
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        print "<h1>404</h1><p>The resource ".$_SERVER['REQUEST_URI']." wasn't found here.</p>";
        return true;
    }

    private function _get_file_test_has_no_folderviewer()
    {
        if (!$this->page_vars['object_type']=='Page') {
            return true;
        }
        $content =
         $this->page_vars['content']
        .$this->page_vars['layout']['content']
        .$this->page_vars['theme']['accent_1']
        .$this->page_vars['theme']['accent_2']
        .$this->page_vars['theme']['accent_3']
        .$this->page_vars['theme']['accent_4']
        .$this->page_vars['theme']['accent_5'];
      // IF page contained a group_member_mirror AND explicit parametyers for folder_viewer,
      // assume that this is OK.
        if (
            strpos($content, '[ECL]content_group_member_mirror[/ECL]')!==false &&
            strpos($this->page_vars['component_parameters'], 'folder_viewer.dir=')!==false
        ) {
            return false;
        }
        if (strpos($content, '[ECL]component_folderviewer[/ECL]')!==false) {
            return false;
        }
        Page::do_tracking("404");
        do_log(
            3,
            __CLASS__."::".__FUNCTION__."()",
            '',
            "Tried to access file on a page without a folder viewer -"
            ." file ID=".$this->ID." for ".$_SERVER["REQUEST_URI"]
        );
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found", true, 404);
        print "<h1>404</h1><p>The resource ".$_SERVER['REQUEST_URI']." wasn't found here.</p>";
        return true;
    }

    public function readfile_chunked($filename, $retbytes = true)
    {
      // From http://www.php.net/manual/en/function.readfile.php#54295
        $chunksize = 1*(1024*1024); // how many bytes per chunk
        $buffer = '';
        $cnt =0;
        $handle = fopen($filename, 'rb');
        if ($handle === false) {
            return false;
        }
        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            echo $buffer;
            ob_flush();
            flush();
            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }
        $status = fclose($handle);
        if ($retbytes && $status) {
            return $cnt; // return num. bytes delivered like readfile() does.
        }
        return $status;
    }

    public function get_file_checksum($filepath)
    {
        if (!file_exists($filepath)) {
            return "(Missing)";
        }
        $file = file_get_contents($filepath);
        return dechex(crc32($file));
    }

    public static function get_file_changes($filepath)
    {
        if (!file_exists($filepath)) {
            return "(Missing)";
        }
        $out = "";
        $file = file_get_contents($filepath);
        $file_arr = preg_split("/Version History:/", $file);
        if (count($file_arr)==1) {
            return "      (No Version History)";
        }
        $lines = preg_split("/\n/", $file_arr[1]);
        $started = false;
        foreach ($lines as $line) {
            if (substr($line, 0, 2)=="*/") {
                return $out;
            }
            if (substr($line, 0, 2)=="  " && substr($line, 0, 4)!="    ") {
                if ($started) {
                    return preg_replace("/(^[\n]*|[\n]+)[\s\t]*[\n]+/", "\n", $out);
                }
                $started = true;
            }
            $out.=$line."\n";
        }
    }

    public function get_file_version_phpdoc($filepath)
    {
        $file = file_get_contents($filepath);
        $file_arr = preg_split("/@version/", $file);
        if (count($file_arr)==1) {
            return "";
        }
        $file_arr = preg_split("/[\n\r]/", $file_arr[1]);
        return trim($file_arr[0]);
    }

    public static function get_file_version($file, $line_num)
    {
        $line = FileSystem::get_line($file, $line_num);
        $line_arr = explode("\"", $line);
        return trim(isset($line_arr[3]) ? $line_arr[3] : "?");
    }

    public static function get_line($filepath, $line = 0)
    {
        $handle = @fopen($filepath, "r");
        $out = "";
        if ($handle) {
            for ($i=0; $i<$line; $i++) {
                $out = fgets($handle, 4096);
            }
            $out = fgets($handle, 4096);
            fclose($handle);
        }
        return $out;
    }

    public function in_dir_tree($dir_array, $crc32)
    {
        for ($i=0; $i<count($dir_array); $i++) {
            $this_entry = $dir_array[$i];
            if (isset($this_entry['type'])) {
                if ($crc32 == $this_entry['crc32']) {
                    return array(
                    'type'=>$this_entry['type'],
                    'path'=>$this_entry['path'],
                    'name'=>$this_entry['name']
                    );
                }
            } else {
                $result = ($this->in_dir_tree($this_entry, $crc32));
                if ($result!=false) {
                    return $result;
                }
            }
        }
        return false;
    }

    /**
     * rm() -- Vigorously erase files and directories.
     *
     * @param $fileglob mixed If string, must be a file name (foo.txt), glob pattern (*.txt), or directory name.
     *                        If array, must be an array of file names, glob patterns, or directories.
     */
    public function rm($fileglob)
    {
        if (is_string($fileglob)) {
            if (is_file($fileglob)) {
                return unlink($fileglob);
            } elseif (is_dir($fileglob)) {
                $ok = $this->rm("$fileglob/*");
                if (! $ok) {
                    return false;
                }
                return @rmdir($fileglob);
            } else {
                $matching = glob($fileglob);
                if ($matching === false) {
                    return false;
                }
                $rcs = array_map(array($this,'rm'), $matching);
                if (in_array(false, $rcs)) {
                    return false;
                }
            }
        } elseif (is_array($fileglob)) {
            $rcs = array_map(array($this,'rm'), $fileglob);
            if (in_array(false, $rcs)) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function write_file($name, $data)
    {
        if ($fp=fopen($name, 'w')) {
            $result = fwrite($fp, $data, strlen($data));
            fclose($fp);
            return $result;
        }
        return false;
    }

    public function get_version()
    {
        return VERSION_FILESYSTEM;
    }
}
