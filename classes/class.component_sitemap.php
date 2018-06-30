<?php
  define ("VERSION_COMPONENT_SITEMAP","1.0.2");
/*
Version History:
  1.0.2 (2012-12-19)
    1) Changes to Component_Sitemap::get_sitemap() to handle possibility that the
       current pages doesn't appear on the nav structure at all
  1.0.1 (2012-12-16)
    1) Changes to get_sitemap() in localised mode to ensure that navsuite branch
       matches the currently viewed nav structure -
       this ensures that correct branch is shown when multiple languages are in use.
       Side-effect of this is that the current level is also shown (previously only
       children were seen). This gives context to the structure shown that was not
       previously available.
  1.0.0 (2011-12-28)
    1) Initial release - moved from Component class
*/
class Component_Sitemap extends Component_Base {

  function get_sitemap($full=false,$navsuiteID='',$flatList=false,$depth=0) {
    global $page,$page_vars;
    $depth++;
    if ($navsuiteID=='') {
      if ($full) {
        $navsuiteID = $page_vars['navsuite1ID'];
      }
      else {
        $Obj_Navbutton = new Navbutton;
        $sql =
           "SELECT\n"
          ."  `ID`,\n"
          ."  `suiteID`\n"
          ."FROM\n"
          ."  `navbuttons`\n"
          ."WHERE\n"
          ."  `navbuttons`.`systemID` = ".$page_vars['systemID']." AND\n"
          ."  `navbuttons`.`URL` = \"./?page=".$page."\"";
        $records = $Obj_Navbutton->get_records_for_sql($sql);
        foreach ($records as $record){
          $possible_root_suiteID = $Obj_Navbutton->get_root_navsuiteID($record['ID']);
          if ($possible_root_suiteID==$page_vars['navsuite1ID']){
            $navsuiteID = $record['suiteID'];
            break;
          }
        }
        if ($navsuiteID=='') {
          return "No Navbuttons to map associated with this page.";
        }
      }
    }
    $out = "";
    $Obj_Navsuite =     new Navsuite($navsuiteID);
    $buttons =          $Obj_Navsuite->get_buttons();
    if (!$buttons===false){
      foreach ($buttons as $button) {
        $Obj = new Navbutton($button['ID']);
        if ($button['visible']) {
          $bID =        $button['ID'];
          $bText =      $button['text1'].($button['text2'] ? "<br />\n".$button['text1'] : "");
          $bPopup =     $button['popup'];
          $childID =    $button['childID'];
          $bURL =       $button['URL'];
          if (substr($bURL,0,8)=='./?page='){
            $bURL = BASE_PATH.substr($bURL,8);
          }
          $bURL = htmlentities(html_entity_decode($bURL));
          if ($flatList) {
            if (!$bPopup) {
              $out.=
                "[!]".htmlentities($bText)."[?]".$bURL
               .($childID ? Component_Sitemap::get_sitemap(false,$childID,$flatList) : "");
            }
          }
          else {
            $out.=
               str_repeat("  ",$depth)
              ."<li>"
              .($bURL ?
                   "<a"
                  .($bPopup ? " rel='external'" : "")
                  ." href=\"".$bURL."\">"
                 : "<span>"
               )
              .$bText
              .($bPopup ? HTML::draw_icon('external') : "")
              .($bURL ? "</a>" : "</span>")
              .($childID ?
                  "\n".Component_Sitemap::get_sitemap(false,$childID,$flatList,$depth).str_repeat("  ",$depth)."</li>\n"
                :
                  "</li>\n"
              );
          }
        }
      }
    }
    if ($flatList) {
      return $out;
    }
    return
       str_repeat("  ",$depth-1)."<ul>\n"
      .$out
      .str_repeat("  ",$depth-1)."</ul>\n";
  }

  public function get_version(){
    return VERSION_COMPONENT_SITEMAP;
  }
}
?>