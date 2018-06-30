<?php
define('VERSION_REPORT_COLUMN_DOWNLOAD_PDF','1.0.2');
/*
Version History:
  1.0.2 (2013-10-30)
    1) Report_Column_Download_PDF::_setup() modified to handle XML fields
    2) Report_Column_Download_PDF::_merge_mapfile_with_data() now handles extra
       tabs as separator
  1.0.1 (2011-10-12)
    1) Split functionality into sub-methods and now uses exceptions for error
       handline to simplify code flow
  1.0.0 (2011-01-31)
    Initial release
*/
class Report_Column_Download_PDF extends Report_Column {
  private   $_columnID;
  private   $_data;
  private   $_flatten;
  private   $_reportID;
  private   $_report_name;
  private   $_table_name;
  private   $_targetID;
  private   $_pdf_filename;
  private   $_pdf_filepath;
  private   $_pdf_map_path;
  private   $_pdf_template_path;
  private   $_xfdf_filename;

  function draw($targetID,$flatten=true){
    try{
      $this->_setup($targetID,$flatten);
      $this->_merge_mapfile_with_data();
      $this->_generate_pdf($targetID,$flatten);
    }
    catch (Exception $e){
      do_log(3,__CLASS__.'::'.__FUNCTION__.'()','',$e->getMessage());
      die($e->getMessage());
    }
    header_mimetype_for_extension('pdf');
    header("Content-Disposition: attachment;filename=\"".$this->_pdf_filename."\"");
    header('Content-Length: '.strlen($this->_pdf_content));
    print $this->_pdf_content;
    flush();
    die;
  }

  private function _generate_pdf(){
    if (file_exists($this->_xfdf_filepath)) {
      if (!@unlink($this->_xfdf_filepath)){
        throw new Exception('Failed to delete old XFDF file '.$this->_xfdf_filepath.' prior to recreation');
      }
    }
    if (file_exists($this->_pdf_filepath)) {
      if (!@unlink($this->_pdf_filepath)){
        throw new Exception('Failed to delete old PDF file '.$this->_pdf_filepath.' prior to recreation');
      }
    }
    $Obj_FDF =  new FDF;
    $fdf_data = $Obj_FDF->get_XFDF($this->_pdf_template_path,$this->_data);
    $Obj_FS =   new FileSystem;
    $Obj_FS->write_file($this->_xfdf_filepath,$fdf_data);
    $cmd =      "pdftk ".$this->_pdf_template_path." fill_form ".$this->_xfdf_filepath." output ".$this->_pdf_filepath.($this->_flatten ? " flatten" : "");
//    die($cmd);
    exec($cmd);
    $this->_pdf_content = file_get_contents($this->_pdf_filepath);
    if (!@unlink($this->_xfdf_filepath)){
      throw new Exception('Failed to delete XFDF file '.$this->_xfdf_filepath.' after creation');
    }
    if (!@unlink($this->_pdf_filepath)){
      throw new Exception('Failed to delete PDF file '.$this->_pdf_filepath.' after creation');
    }
  }

  private function _merge_mapfile_with_data(){
    $data = $this->_data;
    if ($this->_pdf_map_path){
      $_data =  array();
      $pdf_map =    str_replace("\r\n","\n",file_get_contents($this->_pdf_map_path));
      $lines =      explode("\n",$pdf_map);
      foreach($lines as $line){
        $pair = explode("\t",preg_replace("/\t+/","\t",$line));
        if (count($pair)>1){
          $dest =    $pair[0];
          $source =  $pair[1];
          eval('$_data[$dest]='.$source.';');
        }
      }
      $this->_data = $_data;
    }
  }

  private function _setup($targetID,$flatten){
    $this->_columnID =  $this->_get_ID();
    $this->_flatten =   $flatten;
    $this->_targetID =  $targetID;
    if (!$this->_columnID){
      throw new Exception('No columnID given');
    }
    if (!$this->_targetID){
      throw new Exception('No targetID given');
    }
    if (!$this->load()){
      throw new Exception('Invalid columnID given - '.$this->_columnID);
    }
    if ($this->record['fieldType']!='view_record_pdf'){
      throw new Exception('Invalid column type of "'.$this->record['fieldType'].'" for columnID '.$this->_columnID);
    }
    $this->_reportID = $this->record['reportID'];
    $Obj_Report = new Report($this->_reportID);
    if (!$Obj_Report->load()){
      throw new Exception('Invalid report '.$this->_reportID.' for columnID '.$this->_columnID);
    }
    $this->_report_name =   $Obj_Report->record['name'];
    $this->_table_name =    $Obj_Report->record['primaryTable'];
    if (!$this->_table_name){
      throw new Exception('No table specified in report '.$this->_reportID.' for columnID '.$this->_columnID);
    }
    $Obj_Record = new Record($this->_table_name,$this->_targetID);
    if (!$this->_data = $Obj_Record->load()){
      throw new Exception('Invalid record '.$this->_targetID.' for table '.$this->_table_name.' in report '.$this->_reportID.' for columnID '.$this->_columnID);
    }
    $Obj_Record->xmlfields_decode($this->_data);
    $reportFieldSpecial = $this->record['reportFieldSpecial'];
    if (!$reportFieldSpecial){
      throw new Exception('Report Field Params for columnID '.$this->_columnID.' for report '.$this->_report_name.' should indicate PDF template with optional field mapping file - this is missing');
    }
    $reportFieldSpecial_arr =       explode('|',$reportFieldSpecial);
    $this->_pdf_template_path =     '.'.BASE_PATH.trim($reportFieldSpecial_arr[0],'./');
    $this->_pdf_map_path =          (isset($reportFieldSpecial_arr[1]) ? '.'.BASE_PATH.trim($reportFieldSpecial_arr[1],'./') : "");
    if (!file_exists($this->_pdf_template_path)){
      throw new Exception('Report Field Params for columnID '.$this->_columnID.' for report '.$this->_report_name.' - PDF template '.$this->_pdf_template_path.' not found');
    }
    if ($this->_pdf_map_path && !file_exists($this->_pdf_map_path)){
      throw new Exception('Report Field Params for columnID '.$this->_columnID.' for report '.$this->_report_name.' - PDF Mapping file '.$this->_pdf_map_path.' not found');
    }
    $this->_xfdf_filepath =    ".".BASE_PATH."UserFiles/".$this->_table_name."_".$this->_targetID.".xfdf";
    $this->_pdf_filename =     $this->_table_name."_".$this->_targetID.".pdf";
    $this->_pdf_filepath =     ".".BASE_PATH."UserFiles/".$this->_pdf_filename;
  }

  public function get_version(){
    return VERSION_REPORT_COLUMN_DOWNLOAD_PDF;
  }
}
?>