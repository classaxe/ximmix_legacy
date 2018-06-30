<?php
  define ("VERSION_COMPONENT_SEARCH_DATE_PICKER","1.0.0");
/*
Version History:
  1.0.0 (2011-12-31)
    1) Initial release - moved from Component class
*/
class Component_Search_Date_Picker extends Component_Base {

  function draw() {
    global $system_vars;
    $ident =            "search_results"; // This is the ident for the CPs used - we can do that if we want :-)
    $safe_ID =          Component_Base::get_safe_ID($ident,'');
    $parameter_spec =   array(
      'sites_list' =>   array('default'=>$system_vars['URL'], 'hint'=>"CSV list of local site URLs\nThis control uses the setting provided for search results")
    );
    $cp_settings =      Component_Base::get_parameter_defaults_and_values($ident, '', false, $parameter_spec);
    $cp_defaults =      $cp_settings['defaults'];
    $cp =               $cp_settings['parameters'];
    $out =              Component_Base::get_help($ident, '', false, $parameter_spec, $cp_defaults);
    $systemIDs_csv =    System::get_IDs_for_URLs($cp['sites_list']);
    $isMASTERADMIN =	get_person_permission("MASTERADMIN");
    $isSYSADMIN =		get_person_permission("SYSADMIN");
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER");
    $isSYSEDITOR =	    get_person_permission("SYSEDITOR");
    $isAdmin =          ($isSYSEDITOR||$isSYSAPPROVER||$isSYSADMIN||$isMASTERADMIN);
    $range =            System::get_global_date_range($systemIDs_csv);
    $dates =            System::get_global_date_YYYY_MM($range['min'],$range['max'],$systemIDs_csv,$isAdmin);
    $out .=
       "<table summary='Date picker for search results'>"
      ."  <tr>\n"
      ."    <td>&nbsp;</td>\n"
      ."    <th>Jan</th>\n"
      ."    <th>Feb</th>\n"
      ."    <th>Mar</th>\n"
      ."    <th>Apr</th>\n"
      ."    <th>May</th>\n"
      ."    <th>Jun</th>\n"
      ."    <th>Jul</th>\n"
      ."    <th>Aug</th>\n"
      ."    <th>Sep</th>\n"
      ."    <th>Oct</th>\n"
      ."    <th>Nov</th>\n"
      ."    <th>Dec</th>\n"
      ."  </tr>\n";
    for ($YYYY=substr($range['max'],0,4); $YYYY>=substr($range['min'],0,4); $YYYY--) {
      $out.=
         "  <tr>\n"
        ."    <th><a href=\"".BASE_PATH.$YYYY."\">".$YYYY."</a></th>\n";
      for ($_MM=1; $_MM<=12; $_MM++) {
        $MM = lead_zero($_MM,2);
        $count = $dates[$YYYY."-".$MM];
        $out.= "<td class='txt_r'>".($count ? "<a href=\"".BASE_PATH.$YYYY."/".$MM."\">".$count."</a>" : "&nbsp;")."</td>\n";
      }
      $out.= "  </tr>\n";
    }
    $out.= "</table>\n";
    return $out;
  }

  public function get_version(){
    return VERSION_COMPONENT_SEARCH_DATE_PICKER;
  }
}
?>