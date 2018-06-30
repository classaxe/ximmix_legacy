<?php
define('VERSION_EXPORT', '1.0.24');
/*
Version History:
  1.0.24 (2015-01-04)
    1) Now always sets show_fields to true whe exporting sql results
    2) Now conforms to PSR-2

  (Older version history in class.export.txt)
*/
class Export extends Record
{
    public static function draw()
    {
        global $submode, $show_fields, $report_name, $system_vars, $targetID;
        switch ($submode) {
            case "excel":
                $Obj = new Export;
                print $Obj->excel();
                break;
            case "icalendar":
                $Obj = new Event($targetID);
                $Obj->export_icalendar();
                die;
            break;
            default:
                switch($report_name){
                    case 'report_filters':
                      // There IS no such report so just fake it:
                        $Obj = new Report_Filter;
                        $Obj->_set_ID($targetID);
                        $result = $Obj->sql_export($targetID, 1);
                        header("Content-type: text/plain; charset=UTF-8");
                        print $result;
                        die;
                    break;
                    case 'system':
                        break;
                    default:
                        header("Content-type: text/plain; charset=UTF-8");
                        break;
                }
                $Obj_Report = new Report;
                $Obj_Report->_set_ID($Obj_Report->get_ID_by_name($report_name));
                $reportPrimaryObjectName = $Obj_Report->get_field('primaryObject');
                $Obj = $Obj_Report->get_ObjPrimary($report_name, $reportPrimaryObjectName);
                $result = $Obj->export_sql($targetID, 1);
                print $result;
                die;
            break;
        }
    }

    public function excel()
    {
        global $system_vars, $sortBy;
      // sortBy HAS to be global or this doesn't work
        $targetID =         get_var('targetID');
        $targetReportID =   get_var('targetReportID');
        $filterField =      get_var('filterField');
        $filterExact =      get_var('filterExact');
        $filterValue =      get_var('filterValue');
        $Obj_Report =       new Report($targetReportID);
        $table =            $Obj_Report->get_field('primaryTable');
        $filterField_sql = "";
        if ($filterField!='') {
            $ObjReportColumn =  new Report_Column;
            $ObjReportColumn->_set_ID($filterField);
            $filter_column_record = $ObjReportColumn->get_record();
            if ($filter_column_record['reportID'] == $targetReportID) {
                $filterField_sql = $filter_column_record['reportFilter'];
            }
            Report::convert_xml_field_for_filter($filterField_sql, $table);
        }
        $report_record =    $Obj_Report->get_record();
        $columnList =       $Obj_Report->get_columns();
        Report::get_and_set_sortOrder($report_record, $columnList, $sortBy);
        $all_records =
        $Obj_Report->get_records(
            $report_record,
            $columnList,
            $filterField_sql,
            $filterExact,
            $filterValue,
            false,
            -1,
            0
        );
        if ($targetID) {
            $records = array();
            $targetID_arr = explode(',', $targetID);
            foreach ($all_records as $record) {
                if (in_array($record['ID'], $targetID_arr)) {
                    $records[] = $record;
                }
            }
        } else {
            $records = $all_records;
        }
  //    y($records);
        $columns =          array();
        foreach ($columnList as $_c) {
            switch (strToLower($_c['fieldType'])) {
                case "add":
                case "cancel":
                case "checkbox":
                case "copy":
                case "delete":
                case "password_set":
                    break;
                default:
                    $_c['textLabel'] = get_image_alt($_c['reportLabel']);
                    if ($_c['textLabel'] && $_c['access']=='1') {
                        $columns[] = $_c;
                    }
                    break;
            }
        }
        new PHP_Excel; // forces include
        $cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp;
        $cacheSettings = array( ' memoryCacheSize ' => '32MB');
        PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
        $author =       get_userFullName();
        $generator =
             System::get_item_version('system_family')." "
            .System::get_item_version('codebase')
            .".".$system_vars['db_version'];
        $subtitle =     "Created ".date('M j Y \a\t H:i', time())." for ".$author;
        $title =        $system_vars['textEnglish'].' > '.$report_record['reportTitle'];
        switch($report_record['name']){
            case "email_job":
                $title.= " > Email Job #".get_var('selectID');
                break;
            case "group_members":
                $Obj_Group = new Group(get_var('selectID'));
                $title.= " > ".$Obj_Group->get_name();
                break;
        }
        $ObjPHPExcel = new PHP_Excel;
        $ObjPHPExcel->getProperties()
            ->setCreator($author)
            ->setLastModifiedBy($author)
            ->setSubject($report_record['reportTitle'])
            ->setTitle($title)
            ->setDescription("Excel export from ".$system_vars['textEnglish']." using ".$generator);
        $ObjWorksheet = $ObjPHPExcel->setActiveSheetIndex(0);
        $ObjTitle =     new PHPExcel_RichText;
        $ObjTitle->createTextRun($title)->getFont()->setBold(true);
        $ObjSubtitle =  new PHPExcel_RichText;
        $ObjSubtitle->createTextRun($subtitle)->getFont()->setItalic(true);
        $ObjWorksheet
            ->setCellValue('A1', $ObjTitle)
            ->setCellValue('A2', $ObjSubtitle);
        $headerStyle = array(
        'font' =>       array(
            'bold' => true,
            'color' => array('rgb' => 'FFFFFF')
        ),
        'alignment' =>  array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
            'rotation' => -90,
            'wrapText' => true
        ),
        'borders' =>    array(
            'allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => array('rgb' => '404040')),
        ),
        'fill' =>       array(
        'type' =>       PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
        'rotation' =>   45,
        'startcolor' => array('argb' => 'FF808080'),
        'endcolor' =>   array('argb' => 'FFA0A0A0')
        )
        );
        $cellStyle = array(
            'borders' =>    array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '808080')
                ),
            ),
            'width'
        );
        $ObjWorksheet->getStyle(
            'A4:'
            .(PHPExcel_Cell::stringFromColumnIndex(count($columns)-1)).'4'
        )->applyFromArray($headerStyle);
        $ObjWorksheet->getColumnDimensionByColumn(
            'A:'
            .(PHPExcel_Cell::stringFromColumnIndex(count($columns)-1))
        )->setWidth(20);
        $ObjWorksheet->getStyle(
            'A5:'
            .(PHPExcel_Cell::stringFromColumnIndex(count($columns)-1)).(count($records)+4)
        )->applyFromArray($cellStyle);
        for ($col=0; $col<count($columns); $col++) {
            $ObjWorksheet->setCellValue(PHPExcel_Cell::stringFromColumnIndex($col).'4', $columns[$col]['textLabel']);
            $width = 10;
            for ($row=0; $row<count($records); $row++) {
                $cell = PHPExcel_Cell::stringFromColumnIndex($col).($row+5);
                $this->xmlfields_decode($records[$row]);
                $value = (isset($records[$row][$columns[$col]['reportField']]) ?
                    $records[$row][$columns[$col]['reportField']]
                 :
                    ""
                );
                $value = str_replace("<br />", " ", $value);
                $value = str_replace("&#8211;", "-", $value);
                $isHyperlink =  false;
                switch($columns[$col]['fieldType']){
                    case 'bool':
                        $value=($value != '' ? $value : 0);
                        $width = 3;
                        break;
                    case 'currency':
                        $ObjWorksheet->getStyle($cell)->getNumberFormat()->setFormatCode('#,##0.00');
                        $width = 12;
                        break;
                    case 'date':
                    case 'datetime':
                        $width = 18;
                        break;
                    case 'file_upload':
                        if ($value) {
                            $isHyperlink =  true;
                            $file_params = $this->get_embedded_file_properties($value);
                            $value = "Download";
                            $url =
                             trim($system_vars['URL'], '/')
                            .BASE_PATH
                            ."?command=download_data"
                            ."&reportID=".$targetReportID
                            ."&targetID=".$records[$row]['ID']
                            ."&targetValue=".$columns[$col]['reportField'];
                            $ObjWorksheet->getCell($cell)
                                ->getHyperlink()
                                ->setURL($url);
                            $ObjWorksheet->getCell($cell)
                                ->getHyperlink()
                                ->setTooltip(
                                    "Download ".$file_params['name']
                                    ." (".$file_params['type'].", "
                                    .$file_params['size']." bytes)"
                                );
                        }
                        break;
                    case 'int':
                        $width = 5;
                        break;
                    case 'view_record_pdf':
                        $isHyperlink =  true;
                        $value = 'PDF';
                        $url =
                         trim($system_vars['URL'], '/')
                        .BASE_PATH
                        ."?command=download_record_pdf"
                        ."&targetID=".$records[$row]['ID']
                        ."&columnID=".$columns[$col]['ID'];
                        $ObjWorksheet->getCell($cell)->getHyperlink()->setURL($url);
                        $ObjWorksheet->getCell($cell)->getHyperlink()->setTooltip('Open PDF');
                        break;
                    case 'view_order_details':
                        $isHyperlink =  true;
                        $url =
                         trim($system_vars['URL'], '/')
                        .BASE_PATH
                        ."view_order/?print=2&ID=".$value;
                        $ObjWorksheet->getCell($cell)->getHyperlink()->setURL($url);
                        $ObjWorksheet->getCell($cell)->getHyperlink()->setTooltip('View Order Details');
                        break;
                }
                if ($isHyperlink) {
                    $ObjWorksheet->getStyle($cell)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLUE);
                    $ObjWorksheet->getStyle($cell)->getFont()->setBold(true);
                    $ObjWorksheet->getStyle($cell)->getFont()->setUnderline(true);
                }
                $ObjWorksheet->setCellValue($cell, $value);
            }
            $ObjWorksheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($col))->setWidth($width);
        }
        $ObjPHPExcel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment;filename=\"".$report_record['reportTitle'].".xlsx\"");
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($ObjPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        die();
    }

    public function get_version()
    {
        return VERSION_EXPORT;
    }
}
