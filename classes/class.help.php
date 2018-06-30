<?php
define('VERSION_HELP','1.0.7');
/*
Version History:
  1.0.7 (2012-11-28)
    1) Help::menu() now uses System::get_item_version() not
       System::get_version() as before

  (Older version history in class.help.txt)
*/
class Help {

  static function do_help() {
    switch(get_var('submode')) {
      case "":
        Help::frameset();
      break;
      case "blank":
        Help::blank();
      break;
      case "functions_js":
        Help::functions_js();
      break;
      case "menu":
        Help::menu();
      break;
      case "menu_js":
        Help::menu_js();
      break;
      case "navbar":
        Help::navbar();
      break;
    }
  }
  function blank() {
    header("Content-type: text/html");
    print "<html><head></head><body></body></html>";
  }
  function frameset() {
    global $system_vars;
    header("Content-type: text/html");
    print
       "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">\n"
      ."<html>\n"
      ."<head><title>".$system_vars['textEnglish']." Help</title>\n"
      ."<script type='text/javascript'>\n"
      ."//<![CDATA[\n"
      ."var startpage =\n"
      ."  ((document.URL.match(/\/?mode=help&page=/i)==null) ?\n"
      ."   '".BASE_PATH."?page=_help_user'\n"
      ."  : '".BASE_PATH."?page='+document.URL.match(/\/?mode=help&page=(.+)/i)[1]);\n"
      ."var nav = {'next':'','back':'','sync':'','up':''};\n"
      ."function setPage() {\n"
      ."  top.basefrm.location=startpage;\n"
      ."  top.navigation.loadSynchPage(top.startpage);\n"
      ."}\n"
      ."function op() {\n"
      ."}\n"
      ."//]]>\n"
      ."</script></head>\n"
      ."<frameset cols=\"240,*\" onload=\"setTimeout('setPage()',200)\">\n"
      ."  <frame src=\"".BASE_PATH."?mode=help&amp;submode=menu\" name=\"treeframe\" frameborder=\"0\">\n"
      ."  <frameset rows=\"27,*\">\n"
      ."    <frame src=\"".BASE_PATH."?mode=help&amp;submode=navbar\" name=\"navigation\" scrolling=\"no\" marginheight=\"0\" marginwidth=\"0\" frameborder=\"0\">\n"
      ."    <frame src=\"".BASE_PATH."?mode=help&amp;submode=blank\" name=\"basefrm\" frameborder=\"0\">\n"
      ."  </frameset>\n"
      ."</frameset>\n"
      ."</html>";
  }
  function functions_js() {
    header("Content-type: text/javascript");
    readfile(SYS_JS."help.js");
  }
  // ************************************
  // * METHOD: menu()                   *
  // ************************************
  function menu() {
    header("Content-type: text/html");
    print
       "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd\">\n"
      ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n"
      ."<head>\n"
      ."<title>Help Menu</title>\n"
      ."<style type=\"text/css\">\n"
      ."body   { margin: 10px; }\n"
      ."td     { font-size: 8pt;font-family: verdana,helvetica;text-decoration: none;white-space:nowrap;}\n"
      ."a      { text-decoration: none; color: black}\n"
      .".icons { border: 0; display: block; float: left; background-image:url(".BASE_PATH."img/sysimg/icons.gif/".System::get_item_version('icons').");}\r\n"
      ."</style>\n"
      ."<script type='text/javascript' src=\"".BASE_PATH."sysjs/treeview/".System::get_item_version('js_treeview')."\"></script>\n"
      ."<script type='text/javascript' src=\"".BASE_PATH."?mode=help&amp;submode=menu_js\"></script>\n"
      ."</head>\n"
      ."<body>\n"
      ."<script type='text/javascript'>\n"
      ."initializeDocument();\n"
      ."</script>\n"
      ."</body>\n"
      ."</html>";
  }

  function menu_js() {
    $Obj = new Link;
    $Obj->_set_ID($Obj->get_ID_by_path('//help'));
    header("Content-type: text/javascript");
    print
       Link::draw_treeview_js()
      .$Obj->draw_js(0,"foldersTree");
    return;
  }

  function navbar() {
    header("Content-type: text/html");
    print
       "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/2002/REC-xhtml1-20020801/DTD/xhtml1-transitional.dtd\">\n"
      ."<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">\n"
      ."<head>\n"
      ."<title>Help Navbar</title>\n"
      ."<script type=\"text/javascript\" src=\"".BASE_PATH."?mode=help&amp;submode=functions_js\"></script>\n"
      ."<style type=\"text/css\">\n"
      ."body { margin:0; padding:0; }\n"
      ."div.toolbar {\n"
      ."  background-image: url(".BASE_PATH."img/sysimg/icon_help_toolbar_extender.gif);\n"
      ."  background-color:#89b3dc;\n"
      ."  width: 120px;\n"
      ."}\n"
      ."div.toolbar img { border: 0; height:26px;}\n"
      ."</style>\n"
      ."</head>\n"
      ."<body onload='nav_setup()'>\n"
      ."<div class='toolbar'>\n"
      ."<img src='".BASE_PATH."img/sysimg/icon_help_left_edge.gif' width='10' alt=\"|\" />"
      ."<a href=\"#\" onclick=\"nav_go('up');return false;\"   onmouseout=\"return nav_out('up')\"   onmouseover=\"return nav_over('up')\"><img src='".BASE_PATH."img/sysimg/icon_help_up_n.gif' id='up' alt='Previous Level' width='26' /></a>"
      ."<a href=\"#\" onclick=\"nav_go('back');return false;\" onmouseout=\"return nav_out('back')\" onmouseover=\"return nav_over('back')\"><img src='".BASE_PATH."img/sysimg/icon_help_previous_n.gif' id='back' title='Previous Topic' alt='Previous Topic' width='20' /></a>"
      ."<a href=\"#\" onclick=\"nav_go('next');return false;\" onmouseout=\"return nav_out('next')\" onmouseover=\"return nav_over('next')\"><img src='".BASE_PATH."img/sysimg/icon_help_next_n.gif' id='next' alt='Next Topic' width='20' /></a>"
      ."<img src='".BASE_PATH."img/sysimg/icon_help_separator.gif' alt='|' width='12' />"
      ."<a href=\"#\" onclick=\"print_topic();return false;\" onmouseout=\"return nav_out('print')\" onmouseover=\"return nav_over('print')\"><img src='".BASE_PATH."img/sysimg/icon_help_print_n.gif' id='print' alt='Print this Topic' width='27' /></a>"
      ."</div>\n"
      ."</body>\n"
      ."</html>";
  }
  public function get_version(){
    return VERSION_HELP;
  }
}
?>