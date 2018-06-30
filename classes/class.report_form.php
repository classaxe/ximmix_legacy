<?php
define("VERSION_REPORT_FORM", "1.0.60");

/*
Version History:
  1.0.60 (2015-01-06)
    1) Report_Form::_do_update() now uses correct object to perform update and validates fields where possible
    2) Now uses OPTION_SEPARATOR constant not option_separator in Report_Form::_prepare_field() for 'option_list'
    3) Now PSR-2 Compliant

  (Older version history in class.report_form.txt)
*/

class Report_Form extends Report
{
    protected $_bulk_update;
    protected $_can_add;
    protected $_columnList;
    protected $_current_user_rights;
    protected $_default_settings;
    protected $_fields_shown;
    protected $_form_settings;
    protected $_hidden_fields;
    protected $_html = '';
    protected $_mode;
    protected $_msg;
    protected $_Obj_Report_Column;
    protected $_operation;
    protected $_record;
    protected $_recordID;
    protected $_report_record;
    protected $_submode;
    protected $_selectID;
    protected $_selected_section;
    protected $_status_msg;
    protected $_table;
    protected $_tab_last_shown;
    protected $_tabs_array;
    protected $_tabs_shown;
    protected $_targetID;

    public function __construct($ID = '')
    {
        parent::__construct($ID);
    }

    public function draw($report_name, $controls = 1, $alt_controls = '', $show_header = true, $forced_width = false)
    {
        global $msg;
        try {
            $this->_setup($report_name, $controls, $alt_controls, $show_header, $forced_width);
        } catch (Exception $e) {
            return "<strong>Error</strong><br />".$e->getMessage();
        }
  //    print 'access = '.Report::COLUMN_DEFAULT_VALUE;die;
  //    foreach($this->_columnList as $c){if ($c['required_feature']){y($c);}};die;
        $this->_get_operation_by_submode();
        $this->_execute_actions_pre_save();
        $this->_do_initial_operations();
        $this->_get_operation_by_submode();
        $this->_do_save_or_update();
        $this->_expand_status_msg();
        $this->_execute_assignments_post_save();
        $this->_execute_actions_post_save();
        $this->_draw_js();
        if ($this->_submode=='save_and_close' && $msg=='') {
            return "";
        }
        $this->_load_default_settings();
        $this->_set_default_settings();
        $this->_load_form_dimensions();
        $this->_load_tabs();
        $this->_load_record();
        $this->_load_current_user_rights();
        $this->_draw_status_msg();
        $this->_draw_form_outer_container_open();
        if ($this->_show_header) {
            $this->_draw_form_title();
        }
        $this->_html.=
             "  <tr class='table_header'>\n"
            ."    <td class='va_t' style='height:"
            .($this->_form_settings['height']-($this->_controls ? 58 : 30))
            ."px;background-image: none;'>";
        $this->_draw_form_fields();
        $this->_html.=
             "    </table>\n"
            .$this->_hidden_fields
            .($this->_tabs_shown ? "</div>" : "")
            ."</td>\n"
            ."  </tr>\n"
            .($this->_controls ?
                 "  <tr>\n"
                ."    <td colspan='2' class='table_admin_h txt_c'>\n"
                ."<input type='button' value='Close' class='formbutton' style='width: 60px;'"
                ." onclick=\"window.close()\"/>\n"
                ."<input type='button' value='Save' class='formbutton' style='width: 60px;'"
                ." onclick=\"this.value='Please Wait...';this.disabled=1;geid('submode').value='save';"
                ."geid('form').submit();\"/>\n"
                ."<input type='button' value='Save and Close' class='formbutton' style='width: 120px;'"
                ." onclick=\"this.value='Please Wait...';this.disabled=1;geid('submode').value='save_and_close';"
                ."geid('form').submit();\"/>\n"
                .(!$this->_bulk_update && $this->_can_add!==false ?
                     "<input type='button' value='Save and New...' class='formbutton' style='width: 120px;'"
                    ." onclick=\"this.value='Please Wait...';this.disabled=1;geid('submode').value='save_and_new';"
                    ."geid('form').submit();\"/>\n"
                 :
                    ""
                )
                ."</td>\n"
                ."  </tr>\n"
             :
                ""
            )
            .($this->_alt_controls ?
                 "  <tr>\n"
                ."    <td colspan='2' class='table_admin_h txt_c'>\n"
                .$this->_alt_controls
                ."</td>\n"
                ."  </tr>\n"
             :
                ""
            )
            ."</table>\n";
        return $this->_html;
    }

    protected function _draw_form_fields()
    {
        foreach ($this->_columnList as $c) {
            if ($c['access']==Report::COLUMN_FULL_ACCESS && $c['formField']!='' || $c['fieldType']=='iframe') {
                $this->_draw_form_field($c);
            }
        }
    }

    protected function _draw_form_field($c)
    {
        if (
            $c['formField']=="ID" &&
            $c['fieldType']!="groups_assign_person" &&
            $c['fieldType']!="read_only_person_info" &&
            $c['fieldType']!="sample_navsuite" &&
            $c['fieldType']!="sample_buttonstyle" &&
            $c['fieldType']!="view_credit_memo" &&
            $c['fieldType']!="view_order_details"
        ) {
            $this->_hidden_fields.= draw_form_field('ID', $this->_recordID, 'hidden')."\n";
            return;
        }
        $reportTab =        $c['tab'];
        $field_form_safe = (substr($c['formField'], 0, 4)=='xml:' ?
            str_replace('/', ':', $c['formField'])
         :
            $c['formField']
        );
        $c['formFieldWidth'] = ($c['formFieldWidth'] ?
            $c['formFieldWidth']
         :
            $this->_form_settings['default_field_width']
        );
        $value = ($this->_recordID ?
            (isset($this->_record) && is_array($this->_record) && array_key_exists($field_form_safe, $this->_record) ?
                $this->_record[$field_form_safe]
             :
                ""
            )
            :
            (isset($_REQUEST[$field_form_safe]) ?
                $_REQUEST[$field_form_safe]
             :
                ""
            )
        );
        $selectorSQL = $this->_Obj_Report_Column->get_selector_sql(
            $c['fieldType'],
            $this->_current_user_rights['is_MASTERADMIN'],
            $c['formSelectorSQLMaster'],
            $c['formSelectorSQLMember']
        );
        if (!$this->_recordID && $value=='') {
            $value = get_sql_constants($c['defaultValue']);
        }
        switch ($c['fieldType']) {
            case "iframe":
                $c['formFieldSpecial'].= (isset($this->_record) ? $this->_record['ID'] : "");
                break;
            case "seq":
                if ($this->_recordID=='') {
                    switch ($this->_report_name) {
                        case 'navbuttons_for_navsuite':
                            $Obj = new Navsuite($this->_selectID);
                            $value=$Obj->get_next_seq();
                            break;
                        case 'listdata_for_listtype':
                            $Obj = new Listtype($this->_selectID);
                            $value=$Obj->get_next_seq();
                            break;
                    }
                }
                break;
        }
        switch($c['fieldType']){
    //      case "iframe":
            case "combo_value":
            case "quantity":
                return;
            break;
            case "fixed":
            case "hidden":
            case "listTypeID":
            case "selectID":
                $this->_hidden_fields.=     draw_form_field($c['formField'], $value, 'hidden')."\n";
                return;
            break;
        }
        if ($c['formField']=='ID') {
            switch ($c['fieldType']) {
                case 'edit_value_readonly':
                    $this->_hidden_fields.=    draw_form_field($c['formField'], $this->_recordID, 'hidden');
                    $c['fieldType'] =   'read_only';
                    $c['formField'] =  'ID_copy';  // rename so it will get shown
                    break;
                case "link_edit_customform":
                case "sample_fontface":
                case 'link_validate_this_content':
                    $c['formField']='ID_copy';
                    break;
            }
        }
        $inline_label = $this->_field_has_inline_label($c);
        if (count($reportTab)) {
            if ($reportTab != $this->_tab_last_shown) {
                if ($reportTab !="0.") {
                    if ($this->_tabs_shown) {
                        $this->_html.= "</table></div>";
                    } else {
                        $this->_html.= (count($this->_tabs_array) ?
                        ($this->_fields_shown ? "</table>" : "")
                        .HTML::draw_section_tabs($this->_tabs_array, $this->_report_name, $this->_selected_section)
                        : "");
                        $this->_tabs_shown = true;
                    }
                    $reportTab_ID = str_replace(" ", "_", $reportTab);
                    $this->_html.=
                         draw_section_tab_div($reportTab_ID, $this->_selected_section)
                        ."<table cellpadding='2' cellspacing='0'"
                        ." style='width:100%;border:0;border-collapse:collapse;'>\n";
                } else {
                    $this->_html.=
                         "<table cellpadding='2' cellspacing='0'"
                        ."style='width:100%;border:0;border-collapse:collapse;'>\n";
                }
                $this->_tab_last_shown = $reportTab;
            }
        } else {
            $this->_html.=
                "<table cellpadding='2' cellspacing='0' style='width:100%;border:0;border-collapse:collapse;'>\n";
        }
        $standalone = $c['readOnly'] || $c['fieldType']=='iframe'  || $c['fieldType']=='event_recurrence_settings';
        $label = Report_Column::draw_label($c['formLabel'], $c['formFieldTooltip'], $field_form_safe, $standalone);
        $this->_html.=
             "  <tr>\n"
            .($inline_label ?
                  "    <td class='nowrap va_t txt_l' style='width:".$this->_form_settings['label_width']."px'>"
                 .$label
                 ."</td>\n"
                 ."    <td>"
             :
                  "    <td colspan='2' class='nowrap va_t txt_l' style='width:100%'>"
            );
        $fieldWidth = ($inline_label ? $c['formFieldWidth'] : $this->_form_settings['width']-26);
        switch ($c['fieldType']) {
            case "combo_action_operation":
                $destinationOperation =   (isset($this->_record) ?
                    $this->_record['destinationOperation']
                 :
                    ''
                );
                $destinationID =          (isset($this->_record) ?
                    $this->_record['destinationID']
                 :
                    get_var('destinationID')
                );
                $destinationValue =       (isset($this->_record) ?
                    $this->_record['destinationValue']
                 :
                    ''
                );
                $Obj_Action =             new Action;
                $this->_html.=            $Obj_Action->draw_combo_action_operation(
                    $destinationOperation,
                    $destinationID,
                    $destinationValue,
                    $c['formFieldSpecial'],
                    $this->_form_settings['default_field_width'],
                    $this->_report_name
                );
                break;
            case "combo_product_relationship":
                $related_object =         (isset($this->_record) ?
                    $this->_record['related_object']
                 :
                    get_var('related_objectID')
                );
                $related_objectID =       (isset($this->_record) ?
                    $this->_record['related_objectID']
                 :
                    get_var('related_objectID')
                );
                $Obj_PR =                 new Product_Relationship;
                $this->_html.=            $Obj_PR->draw_combo_product_relationship(
                    $related_object,
                    $related_objectID,
                    $c['formFieldSpecial'],
                    $this->_form_settings['default_field_width'],
                    $this->_report_name
                );
                break;
            default:
                $this->_html.= $this->_Obj_Report_Column->draw_form_field(
                    (isset($this->_record) ? $this->_record : array()),
                    $field_form_safe,
                    $value,
                    $c['fieldType'],
                    $fieldWidth,
                    $selectorSQL,
                    $this->_reportID,
                    "",
                    $c['readOnly'],
                    $this->_bulk_update,
                    $label,
                    $c['formFieldSpecial'],
                    $c['formFieldHeight']
                );
                break;
        }
        $this->_fields_shown = true;
        $this->_html.=
        "    </td>\n"
        ."  </tr>";
    }

    protected function _draw_form_outer_container_open()
    {
        $this->_html.=  "<table class='minimal'>\n";
    }

    protected function _draw_form_title()
    {
        $this->_html.=
         "  <tr>\n"
        ."    <td style='width:".$this->_form_settings['width']."px;'>"
        .draw_form_header(
            $this->_report_record['formTitle'].($this->_bulk_update ? " (Bulk update)" : ""),
            $this->_report_record['help'],
            0
        )
        ."</td>\n"
        ."  </tr>\n";
    }

    protected function _draw_js()
    {
        global $msg;
        Page::push_content(
            'javascript',
            $this->_get_js_form_code(
                $this->_mode,
                $this->_submode,
                $this->_recordID,
                $msg,
                $this->_report_name,
                $this->_selectID,
                (isset($this->_operation) ? $this->_operation : false)
            )
        );
    }

    protected function _draw_status_msg()
    {
        $this->_html.=  HTML::draw_status('form_edit_inpage', $this->_status_msg);
    }

    protected function _do_post_operation_assignments()
    {
        foreach ($this->_columnList as $c) {
            $update =
            ($this->_bulk_update==0 ||
            ($this->_bulk_update==1 &&
             isset($_POST[$c['formField']."_apply"]) &&
             $_POST[$c['formField']."_apply"]==1
            )
            );
            if (!$c['readOnly'] && $update) {
                $value =    (isset($_POST[$c['formField']]) ? $_POST[$c['formField']] : "");
                switch ($c['fieldType']) {
                    case "categories_assign":
                        $this->_Obj_Primary->category_assign($value);
                        break;
                    case "groups_assign":
                        $this->_Obj_Primary->group_assign($value);
                        break;
                    case "keywords_assign":
                        $this->_Obj_Primary->keyword_assign($value);
                        break;
                    case "languages_assign":
                        $this->_Obj_Primary->language_assign($value);
                        break;
                    case "push_products_assign":
                        $this->_Obj_Primary->push_product_assign($value);
                        break;
                }
            }
        }
    }

    protected function _do_initial_operations()
    {
        global $_POST;
        $targetField = get_var('targetField');
        $targetValue = get_var('targetValue');
        switch ($this->_submode) {
            case "add_note":
                switch ($targetValue) {
                    case "note":
                        $_POST[$targetField] =
                         get_timestamp()." (".get_userPUsername().") \n"
                        ."NOTE:   ".$_POST["_notes_".$targetField]."\n"
                        ."----------------------------------------------------\n"
                        .$_POST[$targetField];
                        $this->_submode="save_open";
                        break;
                }
                break;
            case "seq_up":
                $this->seq($this->_table, $targetField, $targetValue, -1, DEBUG_FORM);
                $_POST[$targetField] = $_POST[$targetField]+1;
                $this->_submode="save_open";
                break;
            case "seq_down":
                $this->seq($this->_table, $targetField, $targetValue, +1, DEBUG_FORM);
                $_POST[$targetField] = $_POST[$targetField]-1;
                $this->_submode="save_open";
                break;
        }
    }

    protected function _do_insert()
    {
        $field_set =    $this->_prepare_fields();
        $validation =   $this->_Obj_Primary->get_validation_fields();
        $this->_recordID = $this->_Obj_Primary->insert($field_set, $validation);
    }

    protected function _do_save_or_update()
    {
        switch ($this->_operation){
            case "insert":
                $this->_do_insert();
                break;
            case "update":
                $this->_do_update();
                break;
        }
    }

    protected function _do_update()
    {
        if ($this->_report_record['archiveChanges']=='1') {
            $this->_Obj_Primary->archive();
        }
        $field_set =    $this->_prepare_fields();
        $validation =   $this->_Obj_Primary->get_validation_fields();
        $this->_Obj_Primary->update($field_set, $validation);
    }

    protected function _execute_actions_post_save()
    {
        global $_POST;
        switch ($this->_operation){
            case "insert":
                $this->actions_execute(
                    'report_insert_post',
                    $this->_table,
                    $this->_report_record['primaryObject'],
                    $this->_recordID,
                    $_POST
                );
                break;
            case "update":
                $this->actions_execute(
                    'report_update_post',
                    $this->_table,
                    $this->_report_record['primaryObject'],
                    $this->_recordID,
                    $_POST
                );
                break;
        }
    }

    protected function _execute_actions_pre_save()
    {
        switch ($this->_operation) {
            case "insert":
                $this->actions_execute(
                    'report_insert_pre',
                    $this->_table,
                    $this->_report_record['primaryObject'],
                    false,
                    $_POST
                );
                break;
            case "update":
                $this->actions_execute(
                    'report_update_pre',
                    $this->_table,
                    $this->_report_record['primaryObject'],
                    $this->_recordID,
                    $_POST
                );
                break;
        }
    }

    protected function _execute_assignments_post_save()
    {
        if ($this->_operation!='insert' && $this->_operation!='update') {
            return;
        }
        if (!$this->_Obj_Primary) {
            return;
        }
        $ID_arr = explode(',', $this->_recordID);
        foreach ($ID_arr as $_ID) {
            $this->_Obj_Primary->_set_ID($_ID);
            $this->_do_post_operation_assignments($this->_Obj_Primary);
        }
    }

    protected function _expand_status_msg()
    {
        switch ($this->_status_msg){
            case "profile-saved":
                $this->_status_msg = "<b>Success</b>: Your profile has been saved.";
                break;
            default:
                $this->_status_msg = "";
                break;
        }
    }

    protected function _get_operation_by_submode()
    {
        switch ($this->_submode) {
            case "seq_up":
            case "seq_down":
            case "save":
            case "save_and_close":
            case "save_and_new":
            case "save_open":
                $this->_operation = ($this->_recordID ? 'update' : ($this->_can_add ? 'insert' : ''));
                break;
            default:
                $this->_operation = "";
                break;
        }
    }

    protected function _field_has_inline_label($c)
    {
        switch ($c['fieldType']) {
            case "button_generic":
            case "combo_action_operation":
            case "combo_product_relationship":
            case "groups_assign":
            case "groups_assign_person":
            case "html":
            case "html_multi_block":
            case "html_multi_language":
            case "html_with_text":
            case "iframe":
            case "list (sequenced)":
            case "notes":
            case "option_list":
            case "read_only_person_info":
            case "textarea_big":
            case "textarea_readonly":
                return false;
            break;
            case "sample_buttonstyle":
                if ($this->_bulk_update) {
                    return false;
                }
                break;
            default:
                return true;
            break;
        }
    }

    protected function _field_type_skip_check($type)
    {
        switch ($type) {
          // These are always skipped for form operations:
            case "add":
            case "categories_assign":
            case "create_mail_list":
            case "delete":
            case "groups_assign":
            case "groups_assign_person":
            case "iframe":
            case "keywords_assign":
            case "languages_assign":
            case "label_button_states":
            case "link_edit_customform":
            case "link_edit_formsectionwizard":
            case "link_programmable_form":
            case "link_programmable_report":
            case "link_validate_this_content":
            case "password_set":
            case "read_only":
            case "sample_buttonstyle":
            case "sample_fontface":
            case "sample_navsuite":
            case "view_credit_memo":
            case "view_order_details":
                return true;
            break;
          // These are set automatically:
            case 'ID':
            case 'history_created_by':
            case 'history_created_date':
            case 'history_created_IP':
            case 'history_modified_by':
            case 'history_modified_date':
            case 'history_modified_IP':
                if ($this->_operation=='insert') {
                    return true;
                }
                break;
          // These are not adjusted once set:
            case "fixed":
            case "hidden":
            case "listTypeID":
            case "selectID":
                if ($this->_operation=='update') {
                    return true;
                }
                break;
        }
        return false;
    }

    public static function _get_js_form_code($mode, $submode, $ID, $error_msg, $report_name, $selectID, $operation)
    {
      // Also used by Page_Edit::draw()
        $out = "";
        if ($error_msg!='') {
            $out.=
             "// *******************\n"
            ."// * Error reporting *\n"
            ."// *******************\n"
            ."popup_msg += \"".$error_msg."\";\n"
            ."addEvent(\n"
            ."  window,\n"
            ."  \"load\",\n"
            ."  function(e){\n"
            ."    popup_dialog(\n"
            ."      'Form Submission Issues',\n"
            ."      \"<div style='padding:4px;max-height:290px;overflow:auto;'>"
            ."Please note:<ul style='margin:0;padding:0 0 0 2em'>\"+popup_msg+\"</ul></div>\",\n"
            ."      600,300,'OK','',\"hidePopWin(null)\"\n"
            ."    )\n"
            ."  }\n"
            .");\n"
            ;
        }
        if ($submode=='save' && isset($operation) && $operation=='update') {
            $out.=
             "if (window.opener && window.opener.geid('form')) {\n"
            ."window.opener.geid('anchor').value='row_".$ID."';\n"
            ."window.opener.geid('form').action='#row_".$ID."';\n"
            ."window.opener.geid('form').submit();\n"
            ."}\n"
            ;
        }
        switch ($submode) {
            case 'save_and_close':
                if ($error_msg=='') {
                    $out.=
                     "if (window.opener && window.opener.geid('form')) {\n"
                    ."  window.opener.geid('anchor').value='row_".$ID."';\n"
                    ."  window.opener.geid('form').action='#row_".$ID."'\n;"
                    ."  window.opener.geid('form').submit();\n"
                    ."  window.close();\n"
                    ."}\n"
                    ;
                }
                break;
            case 'save_and_new':
                $out.=
                 "if (window.opener && window.opener.geid('form')) {\n"
                ."  window.opener.geid('anchor').value='row_".$ID."';\n"
                ."  window.opener.geid('form').action='#row_".$ID."';\n"
                ."  window.opener.geid('form').submit();\n"
                ."  window.location = \"./?mode=".$mode."&report_name=".$report_name."&selectID=".$selectID."\";\n"
                ."}\n"
                ;
                break;
            case "save_open":
                $out.=
                 "if (window.opener && window.opener.geid('form')) {\n"
                ."  window.opener.geid('anchor').value='row_".$ID."';\n"
                ."  window.opener.geid('form').action='#row_".$ID."';\n"
                ."  window.opener.geid('form').submit();\n"
                ."}\n"
                ;
                break;
            case 'save':
                if (isset($operation) && $operation=='insert') {
                    $out.=
                     "if (window.opener && window.opener.geid('form')) {\n"
                    ."  window.opener.geid('anchor').value='row_".$ID."';\n"
                    ."  window.opener.geid('form').action='#row_".$ID."';\n"
                    ."  window.opener.geid('form').submit();\n"
                    ."}\n"
                    ;
                }
                break;
        }
        return $out;
    }

    protected function _load_current_user_rights()
    {
        $this->_current_user_rights['is_MASTERADMIN'] =    get_person_permission("MASTERADMIN");
    }

    protected function _load_default_settings()
    {
        $this->_default_settings['active_now'] =        false;
        $this->_default_settings['all_user_perms'] =    false;
        $this->_default_settings['comments_public'] =   false;
        $this->_default_settings['enabled'] =           false;
        $this->_default_settings['publish_now'] =       false;
        $this->_default_settings['ratings_public'] =    false;
        if ($this->_Obj_Primary) {
            if (is_a($this->_Obj_Primary, 'Navbutton')) {
                $this->_default_settings['all_user_perms'] =    System::has_feature('Button-default-all-user-perms');
            }
            if (is_a($this->_Obj_Primary, 'Poll')) {
                $this->_default_settings['publish_now'] =       System::has_feature('Posting-default-publish-now');
            }
            if (is_subclass_of($this->_Obj_Primary, 'Posting')) {
                $this->_default_settings['all_user_perms'] =    System::has_feature('Posting-default-all-user-perms');
                $this->_default_settings['comments_public'] =   System::has_feature('Posting-default-public-comments');
                $this->_default_settings['publish_now'] =       System::has_feature('Posting-default-publish-now');
                $this->_default_settings['ratings_public'] =    System::has_feature('Posting-default-public-ratings');
            }
            if (is_a($this->_Obj_Primary, 'Product')) {
                $this->_default_settings['active_now'] =        System::has_feature('Product-default-publish-now');
                $this->_default_settings['all_user_perms'] =    System::has_feature('Product-default-all-user-perms');
                $this->_default_settings['enabled'] =           System::has_feature('Product-default-enabled');
            }
        }
    }

    protected function _load_form_dimensions()
    {
        $this->_form_settings['height'] =               (int)$this->_report_record['popupFormHeight']-20;
        $this->_form_settings['width'] =                ($this->_forced_width ?
            $this->_forced_width
         :
            (int)$this->_report_record['popupFormWidth']
        )-20;
        $this->_form_settings['label_width'] =            165;
        $this->_form_settings['default_field_width'] =
            $this->_form_settings['width'] - $this->_form_settings['label_width'] - 15;
    }

    protected function _load_record()
    {
        if (!$this->_recordID || $this->_bulk_update) {
            return;
        }
        $sql =
         "SELECT\n"
        ."  *\n"
        ."FROM\n"
        ."  `".$this->_table."`\n"
        ."WHERE\n"
        ."  `ID` IN(".$this->_recordID.")";
        $this->_record = $this->get_record_for_sql($sql);
        $this->xmlfields_decode($this->_record);
    }

    protected function _load_tabs()
    {
        $this->_tabs_array =        $this->get_tabs_array($this->_columnList, $this->_selected_section);
        $this->_tab_last_shown =    false;
        $this->_tabs_shown =        false;
    }

    protected function _prepare_field(&$field_set, $data)
    {
        $access =             $data['access'];
        $default_value =      $data['default_value'];
        $field =              $data['field'];
        $parameters =         $data['parameters'];
        $read_only =          $data['read_only'];
        $type =                  $data['type'];
        if ($field=='') {
            return;
        }
        if ($this->_field_type_skip_check($type)) {
            return;
        }
        $field_form_safe =  (substr($field, 0, 4)=='xml:' ? str_replace('/', ':', $field) : $field);
        $raw_value =        (isset($_POST[$field_form_safe]) ? $_POST[$field_form_safe] : "");
        switch($this->_operation){
            case "insert":
                if ($access==Report::COLUMN_DEFAULT_VALUE || $read_only) {
                    $raw_value =    $default_value;
                }
                $value =      get_sql_constants(addslashes(fix_currency_symbols($raw_value)));
                break;
            case "update":
                if ($read_only) {
                    ;
                    return;
                }
                if ($access==Report::COLUMN_DEFAULT_VALUE) {
                    ;
                    return;
                }
                if ($access==Report::COLUMN_NO_ACCESS) {
                    ;
                    return;
                }
                if ($this->_bulk_update==1) {
                    if (!isset($_POST[$field_form_safe."_apply"]) || $_POST[$field_form_safe."_apply"]==0) {
                        return;
                    }
                }
                $value =      addslashes(fix_currency_symbols($raw_value));
                break;
        }
        switch ($type) {
            case "bool":
                $field_set[$field] = ($value=='1' ? "1" : "0");
                break;
            case "csv":
                $field_set[$field] =     implode(", ", explode("\r\n", $value));
                break;
            case "file_upload":
                if (is_uploaded_file($_FILES[$field_form_safe]['tmp_name'])) {
                    $field_set[$field] =
                     addslashes(
                         "name:".$_FILES[$field_form_safe]['name'].","
                         ."size:".$_FILES[$field_form_safe]['size'].","
                         ."type:".$_FILES[$field_form_safe]['type'].","
                         ."data:".file_get_contents($_FILES[$field_form_safe]['tmp_name'])
                     );
                }
                break;
            case "file_upload_to_userfile_folder":
                if (
                    isset($_REQUEST[$field_form_safe."_mark_delete"]) &&
                    $_REQUEST[$field_form_safe."_mark_delete"]=='1'
                ) {
                    $old_data =
                    $this->_Obj_Primary->get_embedded_file_properties(
                        $this->_Obj_Primary->get_field($field_form_safe)
                    );
                    $old_file = '.'.substr($old_data['data'], 4, -1);
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                    $field_set[$field] = '';
                } elseif (
                    isset($_FILES[$field_form_safe]) &&
                    isset($_FILES[$field_form_safe]['tmp_name']) &&
                    is_uploaded_file($_FILES[$field_form_safe]['tmp_name'])
                ) {
                    $path =       'UserFiles/'.trim($parameters, '/').'/';
                    mkdirs($path, 0777);
                    $new_name =   $path.$this->_recordID.'_'.get_path_safe_filename($_FILES[$field_form_safe]['name']);
                    if (file_exists($new_name)) {
                        unlink($new_name);
                    }
                    rename($_FILES[$field_form_safe]['tmp_name'], $new_name);
                    $field_set[$field] =
                    addslashes(
                        "name:".$_FILES[$field_form_safe]['name'].","
                        ."size:".$_FILES[$field_form_safe]['size'].","
                        ."type:".$_FILES[$field_form_safe]['type'].","
                        ."data:url(".BASE_PATH.$new_name.")"
                    );
                }
                break;
            case "fixed":
                $field_set[$field] =   get_sql_constants($default_value);
                break;
            case "hidden":
                $field_set[$field] =   get_sql_constants($value);
                break;
            case "html":
                $field_set[$field] =     addslashes($raw_value);
                break;
            case "html_multi_block":
                $params_arr = explode("|", $parameters);
                for ($blk=1; $blk<=$params_arr[0]; $blk++) {
                    $field_set[$field.'_'.$blk] =      addslashes($_POST[$field."_".$blk]);
                }
                break;
            case "html_multi_language":
                $Obj =      new Language;
                $value =    $Obj->prepare_field($field);
                $field_set[$field] =     addslashes($value);
                break;
            case "html_with_text":
                $Obj =      new Language;
                $value =    $Obj->prepare_field($field);
                $field_set[$field] =            addslashes($value);
                $field_set[$field.'_text'] =    addslashes(
                    preg_replace(
                        "/\[ECL\][^\[]*\[\/ECL\]/",
                        "",
                        strip_tags($raw_value)
                    )
                );
                break;
            case "listTypeID":
                $field_set[$field] =   $this->_report_record['listTypeID'];
                break;
            case "media_file_upload":
                if (is_uploaded_file($_FILES[$field_form_safe]['tmp_name'])) {
                    y($_FILES);
                    die;
                    $field_set['data'] =       addslashes(file_get_contents($_FILES[$field_form_safe]['tmp_name']));
                    $field_set['fileName'] =   addslashes($_FILES[$field_form_safe]['name']);
                    $field_set['mime_type'] =  addslashes($_FILES[$field_form_safe]['type']);
                    $field_set['size'] =       addslashes($_FILES[$field_form_safe]['size']);
                }
                break;
            case "option_list":
                $field_set[$field] =   implode(OPTION_SEPARATOR, explode("\r\n", $value));
                break;
            case "selectID":
                $field_set[$field] = $this->_selectID;
                break;
            case "swatch":
                $field_set[$field] =    strToUpper($value);
                break;
            case "tax_name_and_rate":
                $field_set[$field.'_rate'] =         addslashes($_POST[$field."_rate"]);
                break;
            case "url":
            case "url_path":
            case "url_short":
                if ($value=="./?page=home" || $value=="./?page=") {
                    $value="./";
                }
                $field_set[$field] =    $value;
                break;
            default:
                $field_set[$field] =    $value;
                break;
        }
    }

    protected function _prepare_fields()
    {
        $field_set = array();
        $this->_fields_shown = false;
        foreach ($this->_columnList as $c) {
            $data =
            array(
            'access' =>           $c['access'],
            'default_value' =>    $c['defaultValue'],
            'field' =>            $c['formField'],
            'parameters' =>       $c['formFieldSpecial'],
            'read_only' =>        $c['readOnly'],
            'type' =>             $c['fieldType']
            );
            $data_entries = array();
            switch ($c['fieldType']){
                case 'button_state_swatches':
                    $data['type'] =       'swatch';
                    $data['field'] =      $c['formField'].'_active';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_down';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_normal';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_over';
                    $data_entries[] =     $data;
                    break;
                case 'button_state_effect_types':
                case 'button_state_effect_levels':
                    $data['type'] =       'selector_listdata';
                    $data['field'] =      $c['formField'].'_active';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_down';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_normal';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_over';
                    $data_entries[] =     $data;
                    break;
                case 'event_end_date_and_time':
                    $data['type'] =       'date';
                    $data['field'] =      'effective_date_end';
                    $data_entries[] =     $data;
                    $data['type'] =       'hh:mm';
                    $data['field'] =      'effective_time_end';
                    $data_entries[] =     $data;
                    break;
                case 'fieldset_map_loc_lat_lon':
                    $field_names = explode(',', $c['formField']);
                    $data['type'] =       'text';
                    $data['field'] =      $field_names[0];
                    $data_entries[] =     $data;
                    break;
                case 'fieldset_name_email':
                    $field_names = explode(',', $c['formField']);
                    $data['type'] =       'text';
                    $data['field'] =      $field_names[0];
                    $data_entries[] =     $data;
                    $data['type'] =       'text';
                    $data['field'] =      $field_names[1];
                    $data_entries[] =     $data;
                    break;
                case 'fieldset_name_phone':
                    $field_names = explode(',', $c['formField']);
                    $data['type'] =       'text';
                    $data['field'] =      $field_names[0];
                    $data_entries[] =     $data;
                    $data['type'] =       'text';
                    $data['field'] =      $field_names[1];
                    $data_entries[] =     $data;
                    break;
                case 'fieldset_text_text_date':
                    $field_names = explode(',', $c['formField']);
                    $data['type'] =       'text';
                    $data['field'] =      $field_names[0];
                    $data_entries[] =     $data;
                    $data['type'] =       'text';
                    $data['field'] =      $field_names[1];
                    $data_entries[] =     $data;
                    $data['type'] =       'date';
                    $data['field'] =      $field_names[2];
                    $data_entries[] =     $data;
                    break;
                case 'event_start_date_and_time':
                    $data['type'] =       'date';
                    $data['field'] =      'effective_date_start';
                    $data_entries[] =     $data;
                    $data['type'] =       'hh:mm';
                    $data['field'] =      'effective_time_start';
                    $data_entries[] =     $data;
                    break;
                case 'event_start_date_and_time':
                    $data['type'] =       'date';
                    $data['field'] =      'effective_date_start';
                    $data_entries[] =     $data;
                    $data['type'] =       'hh:mm';
                    $data['field'] =      'effective_time_start';
                    $data_entries[] =     $data;
                    break;
                case 'media_information':
                    $data['type'] =       'int';
                    $data['field'] =      $c['formField'].'_size';
                    $data_entries[] =     $data;
                    $data['type'] =       'selector_listdata';
                    $data['field'] =      $c['formField'].'_type';
                    $data_entries[] =     $data;
                    $data['type'] =       'int';
                    $data['field'] =      $c['formField'].'_secs';
                    $data_entries[] =     $data;
                    break;
                case 'text_alignment_and_offsets':
                    $data['type'] =       'selector_listdata';
                    $data['field'] =      $c['formField'].'_h_align';
                    $data_entries[] =     $data;
                    $data['type'] =       'int';
                    $data['field'] =      $c['formField'].'_h_offset';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_v_offset';
                    $data_entries[] =     $data;
                    break;
                case 'text_font_and_size':
                    $data['type'] =       'selector_listdata';
                    $data['field'] =      $c['formField'].'_font_face';
                    $data_entries[] =     $data;
                    $data['field'] =      $c['formField'].'_font_size';
                    $data_entries[] =     $data;
                    break;
                default:
                    $data_entries[] = $data;
                    break;
            }
            foreach ($data_entries as $data) {
                $this->_prepare_field(
                    $field_set,
                    $data
                );
            }
        }
  //    y($field_set);die;
        return $field_set;
    }

    protected function _set_default_settings()
    {
        if ($this->_recordID!='') {
            return;
        }
        $_REQUEST['navsuite1ID'] = 0;
        $_REQUEST['navsuite2ID'] = 0;
        $_REQUEST['navsuite3ID'] = 0;
        if ($this->_default_settings['active_now'] && !isset($_REQUEST['active_date_from'])) {
            $_REQUEST['active_date_from']=date('Y-m-d', time());
        }
        if ($this->_default_settings['all_user_perms']) {
            if (!isset($_REQUEST['permCOMMUNITYADMIN'])) {
                $_REQUEST['permCOMMUNITYADMIN']=1;
            }
            if (!isset($_REQUEST['permGROUPEDITOR'])) {
                $_REQUEST['permGROUPEDITOR']=1;
            }
            if (!isset($_REQUEST['permGROUPVIEWER'])) {
                $_REQUEST['permGROUPVIEWER']=1;
            }
            if (!isset($_REQUEST['permMASTERADMIN'])) {
                $_REQUEST['permMASTERADMIN']=1;
            }
            if (!isset($_REQUEST['permPUBLIC'])) {
                $_REQUEST['permPUBLIC']=1;
            }
            if (!isset($_REQUEST['permSYSLOGON'])) {
                $_REQUEST['permSYSLOGON']=1;
            }
            if (!isset($_REQUEST['permSYSMEMBER'])) {
                $_REQUEST['permSYSMEMBER']=1;
            }
            if (!isset($_REQUEST['permSYSEDITOR'])) {
                $_REQUEST['permSYSEDITOR']=1;
            }
            if (!isset($_REQUEST['permSYSAPPROVER'])) {
                $_REQUEST['permSYSAPPROVER']=1;
            }
            if (!isset($_REQUEST['permSYSADMIN'])) {
                $_REQUEST['permSYSADMIN']=1;
            }
            if (!isset($_REQUEST['permUSERADMIN'])) {
                $_REQUEST['permUSERADMIN']=1;
            }
        }
        if (!isset($_REQUEST['comments_allow'])) {
            $_REQUEST['comments_allow'] =   ($this->_default_settings['comments_public'] ? 'all' : 'none');
        }
        if (!isset($_REQUEST['ratings_allow'])) {
            $_REQUEST['ratings_allow']=     ($this->_default_settings['ratings_public'] ?  'all' : 'none');
        }
        if (!isset($_REQUEST['enable'])) {
            $_REQUEST['enable']=            ($this->_default_settings['enabled'] ?         '1' : '0');
        }
        if ($this->_default_settings['publish_now'] && !isset($_REQUEST['date'])) {
            $_REQUEST['date']=date('Y-m-d', time());
        }
        if (is_a($this->_Obj_Primary, 'Event')) {
            if ($this->_default_settings['publish_now'] && !isset($_REQUEST['effective_date_start'])) {
                $_REQUEST['effective_date_start']=date('Y-m-d', time());
            }
            if ($this->_default_settings['publish_now'] && !isset($_REQUEST['effective_date_end'])) {
                $_REQUEST['effective_date_end']=date('Y-m-d', time());
            }
        }
        if (is_a($this->_Obj_Primary, 'Note')) {
            if ($this->_default_settings['publish_now'] && !isset($_REQUEST['effective_date_start'])) {
                $_REQUEST['effective_date_start']=date('Y-m-d', time());
            }
        }
    }

    protected function _setup(
        $report_name,
        $controls = 1,
        $alt_controls = '',
        $show_header = true,
        $forced_width = false
    ) {
        global $ID, $msg;
        $this->_report_name =   $report_name;
        $this->_controls =      $controls;
        $this->_alt_controls =  $alt_controls;
        $this->_show_header =   $show_header;
        $this->_forced_width =  $forced_width;
        if (!$this->_reportID = $this->get_ID_by_name($this->_report_name)) {
            throw new Exception("Invalid Report - ".$this->_report_name);
        }
        $msg =                      "";
        $this->_recordID =          $ID;
        $this->_mode =              get_var('mode');
        $this->_submode =           get_var('submode');
        $this->_bulk_update =       get_var('bulk_update', 0);
        $this->_targetID =          get_var('targetID');
        $this->_selectID =          get_var('selectID');
        $this->_selected_section =  get_var('selected_section');
        $this->_status_msg =        get_var('status_msg');
        if ($this->_bulk_update  && $this->_targetID!="" && $this->_recordID=="") {
            $this->_recordID = $this->_targetID;
        }
        $this->_set_ID($this->_reportID);
        $this->_columnList =        $this->get_columns();
        $this->_report_record =     $this->get_record();
        $this->_table =             get_sql_constants($this->_report_record['primaryTable']);
        $this->_can_add =           $this->test_feature('button_add_new');
        $this->_Obj_Primary =       $this->get_ObjPrimary($this->_report_name, $this->_report_record['primaryObject']);
        $this->_Obj_Primary->_set_ID($this->_recordID);
        $this->_Obj_Report_Column = new Report_Column;
    }

    public function seq($primaryTable, $targetField, $targetValue, $offset, $debug = false)
    {
        if ($targetField=="" || $targetValue=="") {
            return;
        }
        $clause = "";
        switch($primaryTable){
            case 'action':
                $clause =
                     " AND\n  `sourceID` = ".$_POST['sourceID']." AND\n"
                    ."  `sourceType`='".$_POST['sourceType']."'";
                break;
            case 'case_tasks':
                $clause =
                     " AND\n  `destinationType` = '".$_POST['destinationType']."' AND\n"
                    ."  `destinationID`=".$_POST['selectID'];
                break;
            case 'listdata':
                $clause =
                    " AND\n  `listTypeID` = ".$_POST['selectID'];
                break;
            case 'navbuttons':
                $clause = " AND\n  `suiteID` = ".$_POST['suiteID'];
                break;
            case 'poll_choice':
                $clause = " AND\n  `pollID` = ".$_POST['pollID'];
                break;
            case 'postings':
                $clause = " AND\n  `parentID` = ".$_POST['parentID'];
                break;
            case 'product':
                $clause = " AND\n  `parentID` = ".$_POST['parentID'];
                break;
            case 'report':
                $clause = " AND\n  `tab` = \"".$_POST['tab']."\"";
                break;
            case 'report_columns':
                $clause = " AND\n  `reportID` = ".$_POST['reportID'];
                break;
            case 'tax_regime':
                $clause = " AND\n  `tax_zoneID` = ".$_POST['tax_zoneID'];
                break;
            case 'tax_rule':
                $clause = " AND\n  `tax_regimeID` = ".$_POST['tax_regimeID'];
                break;
            case 'widget':
                $clause = "";
                break;
            default:
                do_log(
                    3,
                    __CLASS__.'::'.__FUNCTION__.'()',
                    'seq',
                    'The SEQ operation is not supported for entries in the '.$primaryTable.' table.'
                );
                die(
                    'The SEQ operation is not supported for entries in the '.$primaryTable.' table.'
                    .' Please report this error.'
                );
            break;
        }
        $sql =
             "UPDATE\n"
            ."  `".$primaryTable."`\n"
            ."SET\n"
            ."  `history_modified_by` = ".get_userID().",\n"
            ."  `history_modified_date` = \"".get_timestamp()."\",\n"
            ."  `".$targetField."` = `".$targetField."` + ".$offset."\n"
            ."WHERE\n"
            ."  `".$targetField."` = ".$targetValue." AND\n"
            ."  `systemID` = ".(isset($_POST['systemID']) ? $_POST['systemID'] : SYS_ID)
            .$clause;
  //    z($sql);die;
        if ($debug || !$result = $this->do_sql_query($sql)) {
            z($sql);
        }
    }

    public function get_version()
    {
        return VERSION_REPORT_FORM;
    }
}
