<?php
define('VERSION_REPORT_COLUMN_REPORT_FIELD','1.0.27');
/*
Version History:
  1.0.27 (2014-06-22)
    1) Added specific support in Report_Column_Report_Field::draw() for columns of type
       'textarea', 'textarea_big' and 'textarea_readonly' to have new lines shown with breaks

  (Older version history in class.report_column_report_field.txt)
*/

class Report_Column_Report_Field extends Record {
  function draw(
    $column,$row,$popupFormHeight,$popupFormWidth,
    $isEDITOR,$this_report_name,&$targetID,&$mayCancel,
    $reportMembersGlobalEditors,$ajax_popup_url,$primaryObject
  ){
    global $report_name, $page, $system_vars, $selectID, $YYYY, $MM;
    $isVisible =    $column['reportLabel']!="" || ($column['fieldType']=='checkbox' && $column['visible']);
    if (!$isVisible){
      return "";
    }
    $out = "";
    $group_assign_csv = $column['group_assign_csv'];
    $isMASTERADMIN =	get_person_permission("MASTERADMIN",$group_assign_csv);
    $isUSERADMIN =	    get_person_permission("USERADMIN",$group_assign_csv);
    $isCOMMUNITYADMIN =	get_person_permission("COMMUNITYADMIN",$group_assign_csv);
    $isSYSADMIN =		get_person_permission("SYSADMIN",$group_assign_csv);
    $isSYSAPPROVER =	get_person_permission("SYSAPPROVER",$group_assign_csv);
    $isSYSEDITOR =	    get_person_permission("SYSEDITOR",$group_assign_csv);
    $isSYSMEMBER =	    get_person_permission("SYSMEMBER",$group_assign_csv);
    $isSYSLOGON =	    get_person_permission("SYSLOGON",$group_assign_csv);
    $isGROUPEDITOR =	get_person_permission("GROUPEDITOR");
    $isGROUPVIEWER =	get_person_permission("GROUPVIEWER");
    $isPUBLIC =         get_person_permission("PUBLIC");
    $js_submit =        ($ajax_popup_url ? "popup_layer_submit('".$ajax_popup_url."')" : "geid('form').submit()");
    $field =	        $column['reportField'];
    $field =            (substr($field,0,4)=='xml:' ? str_replace('/',':',$field) : $field);
    if (array_key_exists($field,$row)) {
      $value = $row[$field];
    }
    else if(substr($field,0,4)=='xml:'){
      $value= "";
    }
    else {
      $value = "<span title='".$field." is undefined'>?</span>";
    }
    $systemID =         (array_key_exists('systemID',$row) ? $row['systemID'] : SYS_ID);
    $isSys =            $systemID==SYS_ID;
    $readOnly = !(
      ($isSys && $isPUBLIC ?        $column['permPUBLIC']=='2' : 0) ||
      ($isSys && $isSYSLOGON ?      $column['permSYSLOGON']=='2' : 0) ||
      ($isSys && $isSYSMEMBER ?     $column['permSYSMEMBER']=='2' : 0) ||
      ($isSys && $isSYSEDITOR ?     $column['permSYSEDITOR']=='2' : 0) ||
      ($isSys && $isSYSAPPROVER ?   $column['permSYSAPPROVER']=='2' : 0) ||
      ($isSys && $isSYSADMIN ?      $column['permSYSADMIN']=='2' : 0) ||
      ($isSys && $isCOMMUNITYADMIN ?$column['permCOMMUNITYADMIN']=='2' : 0) ||
      ($isSys && $isUSERADMIN ?     $column['permUSERADMIN']=='2' : 0) ||
      ($isSys && $isGROUPVIEWER ?   $column['permGROUPVIEWER']=='2' : 0) ||
      ($isSys && $isGROUPEDITOR ?   $column['permGROUPEDITOR']=='2' : 0) ||
      ($isMASTERADMIN)
    );
    $checksum =         System::get_item_version('icons');
    $type =	$column['fieldType'];
    $targetReportID = $column['reportID'];
    if ($field=="ID") {
      $targetID = $value;
    }
    switch ($type) {
      case "button_add_new":
      case "button_export_excel":
      break;
      case "bool":
        if ($readOnly) {
          $out.=
             "    <td>"
            .($value=="1" ?
                "<img src=\"".BASE_PATH."img/spacer\" alt='Yes' title='Yes' class='icons' style='height:13px;width:13px;background-position: -2222px 0px;' />"
              :
                "<img src=\"".BASE_PATH."img/spacer\" alt='No' title='No' class='icons' style='height:13px;width:13px;background-position: -2235px 0px;' />"
             )
            ."</td>\n";
        }
        else {
          $out.=
             "    <td>"
            .($value=="1" ?
                "<a onmouseover=\"window.status='Set to NO'; return true;\" "
               ."onmouseout=\"window.status=''; return true;\" "
               ."href=\"#\" onclick=\""
               ."geid('submode').value='set';"
               ."geid('targetReportID').value='".$targetReportID."';"
               ."geid('targetFieldID').value='".$column['ID']."';"
               ."geid('targetID').value='$targetID';"
               ."geid('targetValue').value='0';"
               ."geid('anchor').value='row_".$row['ID']."';"
               .$js_submit.";return false;\">"
               ."<img class='icons' src='".BASE_PATH."img/spacer' alt=\"Yes - Click for No\" title=\"Yes - Click for No\" style='height:13px;width:13px;background-position: -2196px 0px;' /></a>"
            :
                "<a onmouseover=\"window.status='Set to YES'; return true;\" "
               ."onmouseout=\"window.status=''; return true;\" "
               ."href=\"#\" onclick=\""
               ."geid('submode').value='set';"
               ."geid('targetReportID').value='".$targetReportID."';"
               ."geid('targetFieldID').value='".$column['ID']."';"
               ."geid('targetID').value='$targetID';"
               ."geid('targetValue').value='1';"
               ."geid('anchor').value='row_".$row['ID']."';"
               .$js_submit.";return false;\">"
               ."<img class='icons' src='".BASE_PATH."img/spacer' alt=\"No - Click for Yes\" title=\"No - Click for Yes\" style='height:13px;width:13px;background-position: -2209px 0px;' /></a>"
            )
           ."</td>\n";
        }
      break;
      case "bool_read":
        $out.=
           "    <td>"
          .($value=="1" ?
              "<img class='icons' src='".BASE_PATH."img/spacer' alt='Yes' title='Yes' style='height:13px;width:13px;background-position: -2222px 0px;' />"
            :
              "<img class='icons' src='".BASE_PATH."img/spacer' alt='No' title='No' style='height:13px;width:13px;background-position: -2235px 0px;' />"
           )
          ."</td>\n";
      break;
      case "cancel":
        if ($report_name=='my_registered_events' || $page=='your_registered_events' || $page=='register') {
          sscanf($row['effective_date_start'], "%04d-%02d-%02d", $_YYYY, $_MM, $_DD);
          if (time() > mktime(23, 59, 59, $_MM, $_DD, $_YYYY)) {
            $out.=
               "    <td>"
              ."<a onmouseover=\"window.status='Cannot cancel - event has already passed.';return true;\" onmouseout=\"window.status='';return true;\" "
              ."href=\"#\" onclick=\"alert('Sorry!\\nYou cannot cancel this registration as the event has already taken place.\\n\\nPlease contact us if you need to discuss this.');return false;\"><span style='color:#808080;'><i>Past</i></span>"
              ."</a></td>\n";
          }
          else {
            if (time() > mktime(23, 59, 59, $_MM, $_DD-(int)$system_vars['system_cancellation_days'], $_YYYY)){
              $out.=
                 "    <td>"
                ."<a onmouseover=\"window.status='Cannot cancel - ".$system_vars['system_cancellation_days']." days notice required.'; return true;\" onmouseout=\"window.status=''; return true;\" href=\"#\" onclick=\"alert('Sorry!\\nYou cannot cancel this registration as the required\\n".$system_vars['system_cancellation_days']." days notice period has not been given.\\n\\nPlease contact us if you need to discuss this.');return false;\"><font color='#808080'><i>Cancel</i></font></a></td>\n";
            }
            else {
              $out.=
                 "    <td><a onmouseover=\"window.status='Cancel registration'; return true;\" onmouseout=\"window.status=''; return true;\" "
                ."href=\"#\" onclick=\"if(confirm('Cancel this registation?')) { geid('targetFieldID').value='".$column['ID']."';geid('targetID').value='$value';geid('targetReportID').value='".$targetReportID."';geid('submode').value='delete';".$js_submit.";} else { alert('Operation cancelled');};return false\">Cancel</a></td>\n";
            }
          }
        }
        else {
          $out.=
             "    <td><a onmouseover=\"window.status='Cancel registration'; return true;\" onmouseout=\"window.status=''; return true;\" "
            ."href=\"#\" onclick=\"if (confirm('Cancel this registation?')) { geid('targetFieldID').value='".$column['ID']."';geid('targetID').value='$value';geid('targetReportID').value='".$targetReportID."';geid('submode').value='delete';".$js_submit.";} else { alert('Operation cancelled');};return false\">Cancel</a></td>\n";
        }
      break;
      case "char":
        $out.=	"    <td>".$value."</td>\n";
      break;
      case 'checkbox':
        if ($readOnly) {
          $out.= "    <td>&nbsp</td>";
        }
        else {
          $out.="<td><input type=\"checkbox\" id=\"row_select_".$column['reportID']."_$value\" name=\"row_select_".$column['reportID']."_$value\" value='1' onclick=\"selected_operation_enable(".$column['reportID'].")\"/></td>";
        }
      break;
      case "copy":
        switch ($this_report_name) {
          case "report":
            $type =  "Report with its columns";
            $label = "new name";
            $rename = true;
          break;
          case "system":
            $type =  "System and all associated data";
            $label = "English Title";
            $rename = true;
          break;
          default:
            $type =   $primaryObject->_get_object_name().$primaryObject->plural(1);
            $params = $primaryObject->get_edit_params();
            $rename = $params['report_rename'];
            $label =  $params['report_rename_label'];
          break;
        }
        $msg =
           ($rename ? "Copy" : "Duplicate")
          ." this "
          .($isSys ? "" : "Global ")
          .$type
          .($isSys || $isMASTERADMIN ? "" : " to your site");
        $out.=
           "    <td><a onmouseover=\"window.status='".$msg."'; return true;\" "
          ."onmouseout=\"window.status=''; return true;\" "
          ."href=\"#\" onclick=\""
          .($rename ?
              "var name=prompt('Please enter the ".$label." for the copied ".$type."','');"
             ."if (name==null || name=='') {"
             ."alert('Copy cancelled');"
             ."} else {"
             ."geid('targetValue').value=name;"
           :
              "var name=confirm('Make an identical copy of this ".$type."?');"
             ."if (name==false) {"
             ."alert('Copy cancelled');"
             ."} else {"
           )
          ."geid('targetFieldID').value='".$column['ID']."';"
          ."geid('anchor').value='row_".$row['ID']."';"
          ."geid('submode').value='copy';"
          ."geid('targetID').value='".$targetID."';"
          ."geid('targetReportID').value='".$targetReportID."';"
          .$js_submit.";return false;"
          ."}\">"
          ."<img src='".BASE_PATH."img/spacer' class='icons' style='height:16px;width:14px;background-position:-893px 0px;' title='".$msg."' alt='".$msg."' />"
          ."</a></td>\n";
      break;
      case "currency":
        $out.=	"    <td class='num'>".$value."</td>\n";
      break;
      case "date":
        $out.=
           "    <td title='Format: YYYY-MM-DD' class='admin_fixed nowrap'>"
          .(substr((substr($value,0,10)=="0000-00-00" ? '&nbsp;' : $value),0,10))
          ."</td>\n";
      break;
      case "datetime":
        $out.="    <td title='Format: YYYY-MM-DD hh:mm:ss' class='admin_fixed nowrap'>".($value=="0000-00-00 00:00:00" ? "&nbsp;" : $value)."</td>\n";
      break;
      case "delete":
        if ($readOnly) {
          $out.="    <td>&nbsp</td>";
        }
        else {
          switch ($this_report_name){
            case "groups":
              $out.=
                 "     <td><a onmouseover=\"window.status='Delete group and all members?';return true;\" onmouseout=\"window.status='';return true;\" "
                ."href=\"#\" onclick=\"if (confirm('Delete group - are you sure?')) { geid('targetFieldID').value='".$column['ID']."';geid('targetID').value='$value';geid('targetFieldID').value='".$column['ID']."';geid('targetReportID').value='".$targetReportID."';geid('submode').value='delete';".$js_submit.";} else { alert('Operation cancelled');};return false;\">"
                ."<img src='".BASE_PATH."img/spacer' class='icons' style='height:10px;width:10px;background-position:-907px 0px;' alt='Delete' />"
                ."</a></td>\n";
            break;
            default:
              $out.=
                 "    <td><a onmouseover=\"window.status='Delete record?'; return true;\" onmouseout=\"window.status=''; return true;\" "
                ."href=\"#\" onclick=\"if (confirm('Delete record - are you sure?')) { geid('targetFieldID').value='".$column['ID']."';geid('targetID').value='$value';geid('targetFieldID').value='".$column['ID']."';geid('targetReportID').value='".$targetReportID."';geid('submode').value='delete';".$js_submit.";} else { alert('Operation cancelled');};return false;\">"
                ."<img src='".BASE_PATH."img/spacer' class='icons' style='height:10px;width:10px;background-position:-907px 0px;' alt='Delete' />"
                ."</a></td>\n";
            break;
          }
        }
      break;
      case "download_media":
        $out.=
           "    <td><a onmouseover=\"window.status='Download this file'; return true;\" onmouseout=\"window.status=''; return true;\" href=\"./?command=download_media&amp;targetID=".$row['ID']."\" title='Download this file...'>$value</a></td>\n";
      break;
      case "edit":
      case "edit_value_readonly":
       if ($readOnly) {
          $out.=
             "    <td>".($value!="" ? str_replace('& ','&amp; ',$value) : "")."</td>\n";
        }
        else {
          $out.=
             "    <td><a href=\"#\" onclick=\"details('".$column['name']."',".$row['ID'].",".$popupFormHeight.",".$popupFormWidth.",'',".($selectID!="" ? $selectID : "''").");return false;\" "
            ."onmouseover=\"window.status='Edit this record';return true;\" "
            ."onmouseout=\"window.status='';return true;\" "
            ."title=\"Edit this record\">".($value!="" ? str_replace('& ','&amp; ',$value) : "<i>[Edit]</i>")."</a></td>\n";
        }
      break;
      case "edit_list_data":
        if ($readOnly) {
          $out.=
             "    <td>".($value!="" ? str_replace('& ','&amp; ',$value) : "<i>[Cannot Edit]</i>")."</td>\n";
        }
        else {
          $out.=
             "    <td><a title='Edit items for this List Type' "
            ."onmouseover=\"window.status='Edit items for this List Type';return true;\" "
            ."onmouseout=\"window.status='';return true;\" "
            ."href=\"".BASE_PATH."report/listdata_for_listtype?print=2&amp;selectID=".$row['ID']."\" rel='external'>"
            .str_replace('& ','&amp; ',$value)
            ."</a></td>\n";
        }
      break;
      case "edit_user":
        if (!isset($row['type']) || $row['type']=='') {
          $row['type'] = 'user';
        }
        if ($readOnly) {
          $out.=
             "    <td>".($value!="" ? $value : "<i>[Cannot Edit]</i>")."</td>\n";
        }
        else if ($row[$field]=='') {
          $out.=
             "    <td><span style='color: #808080' title='This record was deleted'>[Deleted]</span></td>\n";
        }
        else {
          $popup = get_popup_size($row['type']);
          $field = ($column['reportFieldSpecial'] ? $column['reportFieldSpecial'] : 'personID');
          $out.=
             "    <td>"
            .(isset($row[$field]) && $row[$field] ?
                 "<a href='#' onclick='details(\"".$row['type']."\",\"".$row[$field]."\",".$popup['h'].",".$popup['w'].");return false;' title='Click to edit this person'>"
                .($value!=='' ? str_replace('& ','&amp; ',$value) : '(Blank)')
                ."</a>"
              :
                "<span style='color: #808080' title='This record was deleted'>".str_replace('& ','&amp; ',$value)."</span>"
             )
            ."</td>\n";
        }
      break;
      case "edit_report_columns":
        $out.=
           "    <td><a title='Edit columns for this report' "
          ."onmouseover=\"window.status='Edit columns for this report';return true;\" "
          ."onmouseout=\"window.status='';return true;\" "
          ."href=\"".BASE_PATH."report/report_columns?"
          ."filterField=35&amp;filterExact=1"
          ."&amp;filterValue=".$row['name']."\" rel='external'>"
          .str_replace('& ','&amp; ',$value)
          ."</a></td>\n";
      break;
      case "email":
        $out.=	"    <td valign='top'>".($value!='' ? "<a href=\"mailto:".$value."\" title='Click to send an email'>".str_replace('& ','&amp; ',$value)."</a>" : "&nbsp;")."</td>\n";
      break;
      case "fieldset_map_loc_lat_lon":
        $params = explode('|',$column['reportFieldSpecial']);
        $_type =    $params[0];
        $_info =    $params[1];
        $_lat =     $params[2];
        $_lon =     $params[3];
        $_area =    $params[4];
        if (!$row[$_lat] && !$row[$_lon]) {
          $out.= "    <td>".$value."</td>\n";
        }
        else {
          $out.=
             "    <td>"
            ."<a href=\"#\" onclick=\"return popup_map_general("
            ."'".$_type."'"
            .",".$row['ID']
            .",'".$_info."'"
            .",'".$_lat."'"
            .",'".$_lon."'"
            .",'".$_area."'"
            .")\" title=\"Map link\">"
            .str_replace('& ','&amp; ',$value)
            ."</a>"
            ."</td>\n";
        }
      break;
      case "file_upload":
        if ($value!='') {
          $file_params = $this->get_embedded_file_properties($value);
          $out =
             "    <td><a href=\"".BASE_PATH."?command=download_data"
            ."&amp;reportID=".$column['reportID']
            ."&amp;targetID=".$row['ID']
            ."&amp;targetValue=".$field
            ."\" rel=\"external\" title=\"Download ".$file_params['name']." (".$file_params['type'].", ".$file_params['size']." bytes)\">Download</a></td>\n";
        }
        else {
          $out = "    <td>&nbsp</td>\n";
        }
      break;
      case "file_upload_to_userfile_folder":
        if ($value!='') {
          $file_params = $this->get_embedded_file_properties($value);
          $out =
             "    <td><a href=\"".BASE_PATH."?command=download_userfile_data"
            ."&amp;reportID=".$column['reportID']
            ."&amp;targetID=".$row['ID']
            ."&amp;targetValue=".$field
            ."\" rel=\"external\" title=\"Download ".$file_params['name']." (".$file_params['type'].", ".$file_params['size']." bytes)\">Download</a></td>\n";
        }
        else {
          $out = "    <td>&nbsp</td>\n";
        }
      break;
      case "groups_assign_person":
        $out.=
           "    <td><a href=\"./?mode=report&amp;report_name=person_groups&amp;print=2&amp;selectID=".$row['ID']."\" "
          ."rel='external' "
          ."onmouseover=\"window.status='Edit groups for this person';return true;\" "
          ."onmouseout=\"window.status='';return true;\" "
          ."title='Click to edit groups for this person'>"
          .str_replace('& ','&amp; ',$value)
          ."</a></td>\n";
      break;
      case "hh:mm":
        $ampm = $system_vars['defaultTimeFormat'];
        $out.=
           "    <td title='"
          .($ampm ? 'Time in am/pm format' : 'Time in 24hr format')
          ."' class='admin_fixed nowrap'>"
          .hhmm_format($value,$ampm)
          ."</td>\n";
      break;
      case "html":
      case "html_multi_block":
      case "html_multi_language":
      case "html_with_text":
        $out.=	"    <td>".convert_html_to_safe_view($value)."</td>\n";
      break;
      case "image":
        $out.=
           "    <td class='nowrap'>"
          .($value=="" ? "&nbsp;" : "<a href=\"$value\" title=\"Click to view image\" rel='external'>Image</a>")
          ."</td>\n";
      break;
      case "importance":
        if ($readOnly) {
          $out.=
             "    <td>"
            .($value=="1" ?
                "<img src=\"".BASE_PATH."img/spacer\" alt='Yes' title='Yes' class='icons' style='height:13px;width:13px;background-position: -5220px 0px;' />"
              :
                "<img src=\"".BASE_PATH."img/spacer\" alt='No' title='No' class='icons' style='height:13px;width:13px;background-position: -5233px 0px;' />"
             )
            ."</td>\n";
        }
        else {
          $out.=
             "    <td>"
            .($value=="1" ?
                "<a onmouseover=\"window.status='Set to NO'; return true;\" "
               ."onmouseout=\"window.status=''; return true;\" "
               ."href=\"#\" onclick=\""
               ."geid('submode').value='set';"
               ."geid('targetReportID').value='".$targetReportID."';"
               ."geid('targetFieldID').value='".$column['ID']."';"
               ."geid('targetID').value='".$targetID."';"
               ."geid('targetValue').value='0';"
               ."geid('anchor').value='row_".$row['ID']."';"
               .$js_submit.";return false;\">"
               ."<img class='icons' src='".BASE_PATH."img/spacer' alt=\"Yes - Click for No\" title=\"Yes - Click for No\" style='height:13px;width:13px;background-position: -5220px 0px;' /></a>"
            :
                "<a onmouseover=\"window.status='Set to YES'; return true;\" "
               ."onmouseout=\"window.status=''; return true;\" "
               ."href=\"#\" onclick=\""
               ."geid('submode').value='set';"
               ."geid('targetReportID').value='".$targetReportID."';"
               ."geid('targetFieldID').value='".$column['ID']."';"
               ."geid('targetID').value='".$targetID."';"
               ."geid('targetValue').value='1';"
               ."geid('anchor').value='row_".$row['ID']."';"
               .$js_submit.";return false;\">"
               ."<img class='icons' src='".BASE_PATH."img/spacer' alt=\"No - Click for Yes\" title=\"No - Click for Yes\" style='height:13px;width:13px;background-position: -5233px 0px;' /></a>"
            )
           ."</td>\n";
        }
      break;
      case "int":
        $out.=	"    <td class='num'>".$value."</td>\n";
      break;
      case "label":
        $out.=	"    <td>".str_replace('& ','&amp; ',$value)."</td>\n";
      break;
      case "link_programmable_form":
        $params = explode('|',$column['reportFieldSpecial']);
        $_report_name = $params[0];
        $_h =           (isset($params[1]) && $params[1] ? $params[1] :       600);
        $_w =           (isset($params[2]) && $params[2] ? $params[2] :       800);
        $_tooltip =     (isset($params[3]) && $params[3] ? $params[3] :       'Click to view details');
        $_link_text =   (isset($params[4]) && $params[4] ? $params[4] :       $value);
        $_value =       (isset($params[5]) && $params[5] ? $row[$params[5]] : $value);
        $out.=
           "    <td>"
          .($_link_text=='[Deleted]' ?
             "<span style='color: #808080' title='This record was deleted'>".$_link_text."</span>\n"
           :
              "<a href=\"".BASE_PATH."details/".$_report_name."/".$_value."\" "
             ." onclick=\"details('".$_report_name."','".$_value."','".$_h."','".$_w."');return false;\""
             ." title=\"".$_tooltip."\">"
             .str_replace('& ','&amp; ',$_link_text)
             ."</a>"
           )
          ."</td>\n";
      break;
      case "link_programmable_report":
//            print_r($column); die;
        $params = explode('|',$column['reportFieldSpecial']);
        $_report_name = $params[0];
        $_h =           (isset($params[1]) ? $params[1] : 800);
        $_w =           (isset($params[2]) ? $params[2] : 600);
        $_tooltip =     (isset($params[3]) ? $params[3] : 'Click to view report');
        $_link_text =   (isset($params[4]) ? $params[4] : $value);
        $_print =       1;

        $out.=
           "    <td>"
          ."<a href='#' onclick='"
          ."report(\"".$_report_name."\",\"".$row['ID']."\",".$_h.",".$_w.",".$_print.");return false;'"
          ." title=\"".$_tooltip."\">"
          .str_replace('& ','&amp; ',$_link_text)
          ."</a>"
          ."</td>\n";
      break;
      case "option_list":
        $out.=	"    <td>".convert_html_to_safe_view($value,250)."</td>\n";
      break;
      case "password_set":
        if ($readOnly) {
          $out.= "    <td>&nbsp</td>";
        }
        else {
          $out.=
             "    <td><a onmouseover='window.status=\"Set password for user\"; return true;' "
            ."onmouseout='window.status=\"\"; return true;' "
            ."href=\"#\" onclick=\"popup_password_change('".$column['ID']."','".$targetReportID."','$targetID');return false;\">"
            ."<img src='".BASE_PATH."img/spacer' class='icons' style='height:15px;width:30px;background-position:-2873px 0px;' alt='Set password for this user' />"
            ."</a></td>\n";
        }
      break;
      case "percent":
        $out.=	"    <td class='num'>".($value ? $value."%" : "")."</td>\n";
      break;
      case "quantity":
      break;
      case "sample_buttonstyle":
      case "sample_navsuite":
        $submode =  "btn_style";
        $height =   $row['img_height'];
        $width =    $row['img_width'];
        $url =      "url(".BASE_PATH."img/sample/".$submode."/".$value."/".$row['img_checksum'].")";
        $out.=
           "<td class='nowrap' style='width:".(4*$width+10)."px;'>"
          ."<img class='fl' src='".BASE_PATH."img/spacer' style='margin:1px;background: ".$url." no-repeat 100% 0px'   width='".$width."' height='".$height."' alt='Active'/>"
          ."<img class='fl' src='".BASE_PATH."img/spacer' style='margin:1px;background: ".$url." no-repeat 100% -".$height."px' width='".$width."' height='".$height."' alt='Down'/>"
          ."<img class='fl' src='".BASE_PATH."img/spacer' style='margin:1px;background: ".$url." no-repeat 100% -".(2*$height)."px' width='".$width."' height='".$height."' alt='Normal'/>"
          ."<img class='fl' src='".BASE_PATH."img/spacer' style='margin:1px;background: ".$url." no-repeat 100% -".(3*$height)."px' width='".$width."' height='".$height."' alt='Over'/>"
          ."</td>";
      break;
      case "sample_fontface":
        $Obj = new Font_Face($value);
        $out.= "<td>".$Obj->sample()."</td>\n";
      break;
      case "select_item":
        $out.=
           "    <td>"
          ."<a style=\"color:#808000;font-weight:bold;\""
          ." href=\"".BASE_PATH."report/".$report_name."/"
          ."?selectID=".$row['ID'].$column['reportFieldSpecial']
          ."\" title=\"Click to select this item\">"
          .str_replace('& ','&amp; ',$value)
          ."</a></td>\n";
      break;
      case "select_yyyy_mm":
        $Obj=new Report($targetReportID);
        $targetReportName = $Obj->get_field('name');
        $out.=
           "    <td class='admin_fixed'><a style='text-decoration:none;' "
           ."href='#' onclick='select_YYYY_MM(\"".$row['YYYY']."\",\"".$row['MM']."\",\"$targetReportName\");return false;' title='Click to select this month' onmouseover='window.status=\"Click to select this month\";return true;' onmouseout='window.status=\"\";return true;'>"
          .(($YYYY==$row['YYYY']&&$MM==$row['MM']) ?
              "<span style='color:#0000ff'><b>".str_replace('& ','&amp; ',$value)."</b></span>"
           :
              "<span style='color:#000000'>".str_replace('& ','&amp; ',$value)."</span>"
           )
          ."</a></td>\n";
      break;
      case "seq":
        $out.=	"    <td class='num'>".str_replace('& ','&amp; ',$value)."</td>\n";
      break;
      case "swatch":
        if (substr($value,0,1)=='<'){
          $value=''; // Means we are reporting a missing value with <span> wrapper
        }
        $out.=
           "    <td>"
          .($value!="" ?
            "<img src=\"".BASE_PATH."img/color/".$value."\" style='margin-top:1px;border: 2px solid #c0c0c0;height:12px;width:16px;display:block;' alt=\"#".$value."\" title=\"#".$value."\" />"
           :
            "<img src=\"".BASE_PATH."img/spacer\" class='icons' style='margin-top:1px;border:2px solid #c0c0c0;height:12px;width:16px;background-position:-2903px 0px;display:block;' alt='Value not given'  title='Value not given' />"
           )
          ."</td>\n";
      break;
      case "sysurl":
        $out.=	"    <td><a href='".$row['systemURL']."' title=\"Click to visit this system\" rel='external'>".str_replace('& ','&amp; ',$value)."</a></td>\n";
      break;
      case "textarea":
      case "textarea_big":
      case "textarea_readonly":
        $out.=	"    <td>".nl2br($value)."</td>\n";
      break;
      case "toggle_shared":
        if ($readOnly) {
          $out.=
             "    <td>"
            .($value=="1" ?
                "<img src=\"".BASE_PATH."img/spacer\" alt='Yes' title='Yes' class='icons' style='height:13px;width:12px;background-position: -7822px 0px;' />"
              :
                "<img src=\"".BASE_PATH."img/spacer\" alt='No' title='No' class='icons' style='height:13px;width:12px;background-position: -7834px 0px;' />"
             )
            ."</td>\n";
        }
        else {
          $out.=
             "    <td>"
            .($value=="1" ?
                "<a onmouseover=\"window.status='Set to NO'; return true;\" "
               ."onmouseout=\"window.status=''; return true;\" "
               ."href=\"#\" onclick=\""
               ."geid('submode').value='set';"
               ."geid('targetReportID').value='".$targetReportID."';"
               ."geid('targetFieldID').value='".$column['ID']."';"
               ."geid('targetID').value='".$targetID."';"
               ."geid('targetValue').value='0';"
               ."geid('anchor').value='row_".$row['ID']."';"
               .$js_submit.";return false;\">"
               ."<img class='icons' src='".BASE_PATH."img/spacer' alt=\"Yes - Click for No\" title=\"Yes - Click for No\" style='height:13px;width:12px;background-position: -7822px 0px;' /></a>"
            :
                "<a onmouseover=\"window.status='Set to YES'; return true;\" "
               ."onmouseout=\"window.status=''; return true;\" "
               ."href=\"#\" onclick=\""
               ."geid('submode').value='set';"
               ."geid('targetReportID').value='".$targetReportID."';"
               ."geid('targetFieldID').value='".$column['ID']."';"
               ."geid('targetID').value='".$targetID."';"
               ."geid('targetValue').value='1';"
               ."geid('anchor').value='row_".$row['ID']."';"
               .$js_submit.";return false;\">"
               ."<img class='icons' src='".BASE_PATH."img/spacer' alt=\"No - Click for Yes\" title=\"No - Click for Yes\" style='height:13px;width:12px;background-position: -7834px 0px;' /></a>"
            )
           ."</td>\n";
        }
      break;
      case "tristate":
        switch ($value){
          case "1":
            $msg_next = 'Read and Write';
            $msg_val =  'Read';
            $newVal =   '2';
            $offset =   '2849';
          break;
          case "2":
            $msg_next = 'None';
            $msg_val =  'Read and Write';
            $newVal =   '0';
            $offset =   '2861';
          break;
          default:
            $msg_next = 'Read';
            $msg_val =  'None';
            $newVal =   '1';
            $offset =   '2837';
          break;
        }
        if ($readOnly) {
          $out.=
             "    <td>"
            ."<img src='".BASE_PATH."img/spacer' class='icons' style='height:12px;width:12px;background-position:-".$offset."px 0px;' alt='".$msg_val."' />"
            ."</td>\n";
        }
        else {
          $out.=
             "    <td>"
            ."<a onmouseover='window.status=\"$msg_val - click to set to $msg_next\"; return true;' "
            ."onmouseout='window.status=\"\"; return true;' "
            ."href=\"#\" onclick=\""
            ."geid('targetFieldID').value='".$column['ID']."';"
            ."geid('submode').value='set';"
            ."geid('targetReportID').value='".$targetReportID."';"
            ."geid('targetID').value='$targetID';"
            ."geid('targetValue').value='$newVal';"
            ."geid('anchor').value='row_".$row['ID']."';"
            .$js_submit.";return false;\">"
            ."<img src='".BASE_PATH."img/spacer' class='icons' style='height:12px;width:12px;background-position:-".$offset."px 0px;' alt='".$msg_val." - click to set to ".$msg_next."' />"
            ."</a></td>";
        }
      break;
      case "url":
      case "url_external":
      case "selector_url":
        $value_show =
          ($value=='./' ? '[home]' : (substr($value,0,8)=='./?page=' ? "[".substr($value,8)."]" : $value));
        if (substr($value,0,4)=='www.'){
          $value = "http://".$value;
        }
        $out.=
           "    <td class='nowrap'>"
          .($value!="" ? "<a href=\"".$value."\" title=\"Click to visit this page\" rel='external'>".str_replace('& ','&amp; ',$value_show)."</a>" : "&nbsp;")
          ."</td>\n";
      break;
      case "url_path":
        switch ($row['systemID']) {
          case SYS_ID:
            $value_link = trim($value,"/");
            $out.=
               "    <td class='nowrap'>"
              .($value!="" ?
                  "<a href=\"".BASE_PATH.$value_link."/\""
                 ." title=\"Click to view\" rel='external'>"
                 .str_replace('& ','&amp; ',$value)
                 ."</a>"
               : "&nbsp;"
              )
              ."</td>\n";
          break;
          default:
            $out.=
               "    <td class='nowrap'>".($value!="" ? str_replace('& ','&amp; ',$value) : "&nbsp;")."</td>\n";
          break;
        }
      break;
      case "url_short":
        $out.=	"    <td class='nowrap'>".($value!="" ? "<a href=\"$value\" title=\"Click to visit this page\" rel='external'>Link</a>" : "&nbsp;")."</td>\n";
      break;
      case "view_credit_memo":
        $out.=
           "    <td>"
          .($value ?
             "<a href='#' onclick='view_credit_memo(\"".$value."\",800,550);return false;' title='Click to see details for this credit memo'>".str_replace('& ','&amp; ',$value)."</a>"
           : "&nbsp;"
          )
          ."</td>\n";
      break;
      case "view_event_registrants":
        $out.=	"    <td><a href=\"".BASE_PATH."report/event_registrants?selectID=".$row['eventID']."&amp;print=2\" onclick=\"view_event_registrants('".$row['eventID']."',800,400);return false\" title='Click to see registrants for this event'>".str_replace('& ','&amp; ',$value)."</a></td>\n";
      break;
      case "view_order_details":
        $out.=
           "    <td>"
          .($value ?
             "<a href='#' onclick='view_order_details(\"".$value."\",800,550);return false;' title='Click to see details for this order'>".str_replace('& ','&amp; ',$value)."</a>"
           : "&nbsp;"
          )
          ."</td>\n";
      break;
      case "view_order_pdf":
        $out.=	"    <td><a href='#' onclick='download_order_pdf(\"".$value."\",\"".$column['ID']."\",800,550);return false;'>[ICON]16 16 1602 Click to see PDF for this order[/ICON]</a></td>\n";
      break;
      case "view_record_pdf":
        $reportFieldSpecial_arr =       explode('|',$column['reportFieldSpecial']);
        $label = (isset($reportFieldSpecial_arr[2]) ? $reportFieldSpecial_arr[2] : '');
        $out.=	"    <td><a href='#' onclick='download_record_pdf(\"".$value."\",\"".$column['ID']."\",800,550);return false;'>[ICON]16 16 1602 Click to see PDF for this record[/ICON]".$label."</a></td>\n";
      break;
      default:
        $out.=	"    <td>".str_replace('& ','&amp; ',$value)."</td>\n";
      break;
    }
    return $out;
  }

  public function get_version(){
    return VERSION_REPORT_COLUMN_REPORT_FIELD;
  }

}
?>