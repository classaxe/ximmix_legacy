<?php
define ("VERSION_QUICKBOOKS","1.0.31");
/*
Vital info:
You need to turn up the logging for the Web Connector to see the complete command.
* Exit the Web Connector
* Running regedit (Start > Run > regedit.exe)
* Navigating to: \HKEY_CURRENT_USER\Software\Intuit\QBWebConnector
* Change the 'Level' key to VERBOSE
* Start the Web Connector, and run your sync
*/


/*
Version History:
  1.0.31 (2012-12-10)
    1) Changes to handle embedded ampersand in customer data:
       QuickBooks::_qbwc_wrap_XML() now replaces any standalone ampersands with
       html entity variant

  (Older version history in class.quickbooks.txt)
*/


class QuickBooks {
  const _do_quickbooks_log = 1;
  const _do_system_log = 1;
  private $_dsn;
  private $_callback_map;
  private $_driver_options;
  private $_error_map;
  private $_handler_options;
  private $_hooks;
  private $_log_level;
  private $_qbwc_user;
  private $_qbwc_pass;
  private $_qbwc_AssetAccountRef;
  private $_qbwc_COGSAccountRef;
  private $_qbwc_IncomeAccountRef;
  private $_qbwc_export_orders;
  private $_qbwc_export_people;
  private $_qbwc_export_products;
  private $_soap_server;
  private $_soap_options;

  public function __construct(){
    define ("QUICKBOOKS_IMPORT_ALL_CUSTOMERS","CustomerQBImportAll");
  }

  public function get_qwc_xml(){
    global $system_vars;
    header('Content-Type: application/xml');
    $ownerID = get_guid_from_string($system_vars['adminName'].$system_vars['adminEmail']);
    $fileID = get_guid_from_string($system_vars['ID'].$system_vars['URL'].$system_vars['textEnglish']);
    print
       "<?xml version=\"1.0\"?".">\n"
      ."<QBWCXML>\n"
      ."  <AppName>QuickBooks Integrator - ".$system_vars['textEnglish']."</AppName>\n"
      ."  <AppID>".$system_vars['textEnglish']."</AppID>\n"
      ."  <AppURL>".trim($system_vars['URL'],'/')."/qbwc/</AppURL>\n"
      ."  <AppDescription></AppDescription>\n"
      ."  <AppSupport>".trim($system_vars['URL'],'/')."/qbwc/?support=1</AppSupport>\n"
      ."  <UserName>".$system_vars['qbwc_user']."</UserName>\n"
      ."  <OwnerID>".$ownerID."</OwnerID>\n"
      ."  <FileID>".$fileID."</FileID>\n"
      ."  <QBType>QBFS</QBType>\n"
      ."  <Scheduler>\n"
      ."    <RunEveryNMinutes>60</RunEveryNMinutes>\n"
      ."  </Scheduler>\n"
      ."  <IsReadOnly>false</IsReadOnly>\n"
      ."</QBWCXML>\n";
    die;
  }

  public function qbwc(){
    ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SYS_SHARED.'qb');
    require_once 'QuickBooks.php';
    $this->_qbwc_setup();
    $this->_qbwc_initialize_database();
    $this->_qbwc_queue();
    $this->_qbwc_serve();
    die;
  }

  protected function _qbwc_initialize_database(){
    if (!QuickBooks_Utilities::initialized($this->_dsn)){
      QuickBooks_Utilities::initialize($this->_dsn);
      QuickBooks_Utilities::createUser($this->_dsn, $this->_qbwc_user, $this->_qbwc_pass);
    }
  }

  protected function _qbwc_queue(){
    if (!QuickBooks_Utilities::initialized($this->_dsn)){
      return;
    }
    $this->_qbwc_queue_customers_download_from_QB(1);
    $this->_qbwc_queue_tax_codes_lookup_from_qb_name(2);
    $this->_qbwc_queue_customers_lookup_from_qb_name(3);
    $this->_qbwc_queue_customers_upload_to_QB_name_check(4);
    $this->_qbwc_queue_customers_upload_to_QB(5);
    $this->_qbwc_queue_inventory_items_lookup_from_qb_name(6);
    $this->_qbwc_queue_inventory_items_upload_to_QB(7);
    $this->_qbwc_queue_orders(8);
  }

  protected function _qbwc_queue_customers_upload_to_QB_name_check($step){
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    if (!$this->_qbwc_export_people){
      return;
    }
    $Obj = new QB_Customer;
    $only_customers = $this->_qbwc_export_people=='1';
    while ($ID = $Obj->get_next_ID_for_QB_conversion('',$only_customers)){
      $Obj->_set_ID($ID);
      $Obj->set_field('qb_name',$Obj->get_field('PUserName'));
      $Obj->set_field('qb_ident','query');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_QUERY_CUSTOMER for '.$ID);
    }
    $this->_qbwc_current_step_inc();
  }


  protected function _qbwc_queue_customers_upload_to_QB($step){
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    if (!$this->_qbwc_export_people){
      return;
    }
    $Obj = new QB_Customer;
    $only_customers = $this->_qbwc_export_people=='1';
    while ($ID = $Obj->get_next_ID_for_QB_conversion('not_found',$only_customers)){
      $Obj->_set_ID($ID);
      $Obj->set_field('qb_ident','queued');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_ADD_CUSTOMER, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_ADD_CUSTOMER for '.$ID);
    }
    $this->_qbwc_current_step_inc();
  }

  protected function _qbwc_queue_customers_download_from_QB($step){
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    $Obj_QB_Import = new QB_Import;
    if (!$Obj_QB_Import->get_records()){
      $data = array(
        'systemID' =>   SYS_ID,
        'iterator' =>   'Start',
        'status' =>     'queued',
        'type' =>       'customer_import'
      );
      $ID = $Obj_QB_Import->insert($data);
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_IMPORT_ALL_CUSTOMERS, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_IMPORT_ALL_CUSTOMERS for '.$ID);
      return;
    }
    if ($record = $Obj_QB_Import->get_active_download('customer_import')){
      $Obj_QB_Import->_set_ID($record['ID']);
      $Obj_QB_Import->set_field('status','processed',false);
      $data = array(
        'systemID' =>   SYS_ID,
        'iterator' =>   'Continue',
        'iteratorID' => $record['iteratorID'],
        'status' =>     'active',
        'type' =>       'customer_import'
      );
      $ID = $Obj_QB_Import->insert($data);
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_IMPORT_ALL_CUSTOMERS, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_IMPORT_ALL_CUSTOMERS for '.$ID);
      return;
    }
    if ($Obj_QB_Import->download_has_completed('customer_import')){
      $this->_qbwc_current_step_inc();
      return;
    }
  }

  protected function _qbwc_queue_customers_lookup_from_qb_name($step){
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    $only_customers = $this->_qbwc_export_people=='1';
    $Obj_Customer = new QB_Customer;
    if ($ID = $Obj_Customer->get_next_ID_for_QB_Name_Lookup('',$only_customers)){
      $Obj_Customer->_set_ID($ID);
      $Obj_Customer->set_field('qb_ident','query');
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_QUERY_CUSTOMER, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_QUERY_CUSTOMER for '.$ID);
      return;
    }
    $this->_qbwc_current_step_inc();
  }

  protected function _qbwc_queue_orders($step){
    global $system_vars;
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    if (!$this->_qbwc_export_orders){
      return;
    }
    switch(strToLower($system_vars['qbwc_invoice_type'])){
      case 's':
        $this->_qbwc_queue_order_salesorders();
      break;
      default:
        $this->_qbwc_queue_order_invoices();
      break;
    }
  }

  protected function _qbwc_queue_order_invoices(){
    $Obj = new QB_Order;
    while ($ID = $Obj->get_next_ID_for_QB_conversion('')){
      $Obj->_set_ID($ID);
      $Obj->set_field('qb_ident','query');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_QUERY_INVOICE, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queried','QUICKBOOKS_QUERY_INVOICE for '.$ID);
    }
    while ($ID = $Obj->get_next_ID_for_QB_conversion('not_found')){
      $Obj->_set_ID($ID);
      $Obj->set_field('qb_ident','queued');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_ADD_INVOICE, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_ADD_INVOICE for '.$ID);
    }
  }

  protected function _qbwc_queue_order_salesorders(){
    $Obj = new QB_Order;
    while ($ID = $Obj->get_next_ID_for_QB_conversion('')){
      $Obj->_set_ID($ID);
      $Obj->set_field('qb_ident','query');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_QUERY_SALESORDER, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queried','QUICKBOOKS_QUERY_SALESORDER for '.$ID);
    }
    while ($ID = $Obj->get_next_ID_for_QB_conversion('not_found')){
      $Obj->_set_ID($ID);
      $Obj->set_field('qb_ident','queued');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_ADD_SALESORDER, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_ADD_SALESORDER for '.$ID);
    }
  }

  protected function _qbwc_queue_inventory_items_lookup_from_qb_name($step){
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    if (!$this->_qbwc_export_products){
      return;
    }
    $only_ordered = $this->_qbwc_export_products=='1';
    $Obj = new QB_Inventory_Item;
    while ($ID = $Obj->get_next_ID_for_QB_conversion('',$only_ordered)){
      $Obj->_set_ID($ID);
      $Obj->load();
      if ($Obj->record['qb_name']==''){
        $Obj->set_field('qb_name',$Obj->record['itemCode']);
      }
      $Obj->set_field('qb_ident','query');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_QUERY_INVENTORYITEM, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_QUERY_INVENTORYITEM for '.$ID);
    }
    $this->_qbwc_current_step_inc();
  }

  protected function _qbwc_queue_inventory_items_upload_to_QB($step){
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    if (!$this->_qbwc_export_products){
      return;
    }
    $only_ordered = $this->_qbwc_export_products=='1';
    $Obj = new QB_Inventory_Item;
    while ($ID = $Obj->get_next_ID_for_QB_conversion('not_found',$only_ordered)){
      $Obj->_set_ID($ID);
      $Obj->set_field('qb_ident','queued');  // prevent loops!
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_ADD_INVENTORYITEM, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_ADD_INVENTORYITEM for '.$ID);
    }
    $this->_qbwc_current_step_inc();
  }

  protected function _qbwc_queue_tax_codes_lookup_from_qb_name($step){
    if ($this->_qbwc_current_step_get()!=$step){
      return;
    }
    $Obj_Tax_Code = new QB_Tax_Code;
    if ($ID = $Obj_Tax_Code->get_next_ID_for_QB_Name_Lookup('')){
      $Obj_Tax_Code->_set_ID($ID);
      $Obj_Tax_Code->set_field('qb_ident','query');
      $Queue = new QuickBooks_Queue($this->_dsn);
      $Queue->enqueue(QUICKBOOKS_QUERY_SALESTAXCODE, $ID, $this->_qbwc_current_priority_get());
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Queued','QUICKBOOKS_QUERY_SALESTAXCODE for '.$ID);
      return;
    }
    $this->_qbwc_current_step_inc();
  }

  protected function _qbwc_serve(){
    if (!QuickBooks_Utilities::initialized($this->_dsn)){
      return;
    }
    $Server = new QuickBooks_Server(
      $this->_dsn,
      $this->_callback_map,
      $this->_error_map,
      $this->_hooks,
      $this->_log_level,
      $this->_soap_server,
      QUICKBOOKS_WSDL,
      $this->_soap_options,
      $this->_handler_options,
      $this->_driver_options,
      $this->_callback_options
    );
    $response = $Server->handle(true, true);
  }

  protected function _qbwc_current_priority_get(){
    return 100-$_SESSION['qbwc_current_step'];
  }

  protected function _qbwc_current_step_get(){
    return $_SESSION['qbwc_current_step'];
  }

  protected function _qbwc_current_step_inc(){
    $_SESSION['qbwc_current_step']++;
  }

  protected function _qbwc_current_step_set($step=1){
    $_SESSION['qbwc_current_step'] = $step;
  }

  protected function _qbwc_setup(){
    $this->_qbwc_current_step_set();
    $this->_qbwc_setup_set_log_level();
    $this->_qbwc_setup_set_timezone();
    $this->_qbwc_setup_set_dsn();
    $this->_qbwc_setup_set_site_settings();
    $this->_qbwc_setup_set_settings();
    $this->_qbwc_setup_callback_map();
    $this->_qbwc_setup_error_map();
  }

  protected function _qbwc_setup_callback_map(){
    $this->_callback_map = array(
      QUICKBOOKS_ADD_CUSTOMER => array(
        array($this, '_customer_add_request'),
        array($this, '_customer_add_response')
      ),
      QUICKBOOKS_IMPORT_ALL_CUSTOMERS => array(
        array($this, '_customer_download_all_request'),
        array($this, '_customer_download_all_response')
      ),
      QUICKBOOKS_QUERY_CUSTOMER => array(
        array($this, '_customer_query_request'),
        array($this, '_customer_query_response')
      ),
      QUICKBOOKS_ADD_INVENTORYITEM => array(
        array($this, '_inventory_item_add_request'),
        array($this, '_inventory_item_add_response')
      ),
      QUICKBOOKS_QUERY_INVENTORYITEM => array(
        array($this, '_inventory_item_query_request'),
        array($this, '_inventory_item_query_response')
      ),
      QUICKBOOKS_ADD_INVOICE => array(
        array($this, '_invoice_add_request'),
        array($this, '_invoice_add_response')
      ),
      QUICKBOOKS_QUERY_INVOICE => array(
        array($this, '_invoice_query_request'),
        array($this, '_invoice_query_response')
      ),
      QUICKBOOKS_ADD_SALESORDER => array(
        array($this, '_salesorder_add_request'),
        array($this, '_salesorder_add_response')
      ),
      QUICKBOOKS_QUERY_SALESORDER => array(
        array($this, '_salesorder_query_request'),
        array($this, '_salesorder_query_response')
      ),
      QUICKBOOKS_QUERY_SALESTAXCODE => array(
        array($this, '_salestaxcode_query_request'),
        array($this, '_salestaxcode_query_response')
      )
    );
  }

  protected function _qbwc_setup_error_map(){
    $this->_error_map = array(
      1 =>    'QB_Error::_error_no_match',	    // Whenever a query finds no result, call this function: _error_nomatch()
      500 =>  'QB_Error::_error_not_found',	    // Whenever a query finds no result, call this function: _error_notfound()
      3070 => 'QB_Error::_error_stringtoolong'	// Whenever a string is too long to fit in a field, call this function: _error_stringtolong()
    );
  }

  protected function _qbwc_setup_set_dsn(){
    global $dsn;
    $this->_dsn =                   $dsn;
  }

  protected function _qbwc_setup_set_log_level(){
    // QUICKBOOKS_LOG_NORMAL | QUICKBOOKS_LOG_VERBOSE | QUICKBOOKS_LOG_DEBUG | QUICKBOOKS_LOG_DEVELOP
    $this->_log_level =             QUICKBOOKS_LOG_DEVELOP;
  }

  protected function _qbwc_setup_set_site_settings(){
    global $system_vars;
    $this->_qbwc_user =                         $system_vars['qbwc_user'];
    $this->_qbwc_pass =                         $system_vars['qbwc_pass'];
    $this->_qbwc_AssetAccountRef =              $system_vars['qbwc_AssetAccountRef'];
    $this->_qbwc_COGSAccountRef =               $system_vars['qbwc_COGSAccountRef'];
    $this->_qbwc_IncomeAccountRef =             $system_vars['qbwc_IncomeAccountRef'];
    $this->_qbwc_export_orders =                $system_vars['qbwc_export_orders'];
    $this->_qbwc_export_orders_billing_addr =   $system_vars['qbwc_export_orders_billing_addr'];
    $this->_qbwc_export_orders_product_desc =   $system_vars['qbwc_export_orders_product_desc'];
    $this->_qbwc_export_orders_taxcodes =       $system_vars['qbwc_export_orders_taxcodes'];
    $this->_qbwc_export_people =                $system_vars['qbwc_export_people'];
    $this->_qbwc_export_products =              $system_vars['qbwc_export_products'];
  }

  protected function _qbwc_setup_set_settings(){
    $this->_hooks =                 array();
    $this->_soap_server =           QUICKBOOKS_SOAPSERVER_BUILTIN;		// A pure-PHP SOAP server (no PHP ext/soap extension required, also makes debugging easier)
    $this->_soap_options =          array();
    $this->_handler_options =       array();
    $this->_driver_options =        array(
      'max_log_history' => 1000,
      'max_queue_history' => 1000,
      'max_ticket_history' => 1000,
      'log_level' => QUICKBOOKS_LOG_NORMAL
    );
    $this->_callback_options =      array();
  }

  protected function _qbwc_setup_set_timezone(){
    if (function_exists('date_default_timezone_set')){
      date_default_timezone_set('America/New_York');
    }
  }

  protected function _qbwc_wrap_XML($doc,$version,$onerror){
    return
       "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n"
      ."<?qbxml version=\"".$version."\"?".">\n"
      ."<QBXML>\n"
      ."  <QBXMLMsgsRq onError=\"".$onerror."\">\n"
      .fix_ampersands($doc)
      ."  </QBXMLMsgsRq>\n"
      ."</QBXML>\n";
  }

  function _customer_add_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    $Obj =      new QB_Customer($ID);
    $record =   $Obj->load();
  	$xml =
         "<CustomerAddRq requestID=\"".$requestID."\">\n"
        ."  <CustomerAdd>\n"
        ."    <Name>".$record['PUsername']."</Name>\n"
        ."    <CompanyName>".$record['WCompany']."</CompanyName>\n"
        ."    <FirstName>".$record['NFirst']."</FirstName>\n"
        ."    <LastName>".$record['NLast']."</LastName>\n"
        ."    <BillAddress>\n"
        ."      <Addr1>".$record['AAddress1']."</Addr1>\n"
        ."      <Addr2>".$record['AAddress2']."</Addr2>\n"
        ."      <City>".$record['ACity']."</City>\n"
        ."      <State>".$record['ASpID']."</State>\n"
        ."      <PostalCode>".$record['APostal']."</PostalCode>\n"
        ."      <Country>".$record['ACountryID']."</Country>\n"
        ."    </BillAddress>\n"
        ."    <Phone>".$record['ATelephone']."</Phone>\n"
        ."    <AltPhone>".$record['ACellphone']."</AltPhone>\n"
        ."    <Fax>".$record['AFax']."</Fax>\n"
        ."    <Email>".$record['PEmail']."</Email>\n"
        ."    <Contact>".$record['NFirst'].' '.$record['NLast']."</Contact>\n"
        ."  </CustomerAdd>\n"
        ."</CustomerAddRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'7.0','stopOnError');
  }

  function _customer_add_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    $Obj = new QB_Customer($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _customer_download_all_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    $Obj = new QB_Import($ID);
    $iterator =     $Obj->get_field('iterator');
    $iteratorID =   $Obj->get_field('iteratorID');
    $limit =        '100';      // $extra['limit'];
    $xml =
       "<CustomerQueryRq requestID=\"".$requestID."\" iterator=\"".$iterator."\"".($iteratorID ? " iteratorID=\"".$iteratorID."\"" : "")." >\n"
      ."  <MaxReturned>".$limit."</MaxReturned>\n"
      ."  <OwnerID>0</OwnerID>\n"
      ."</CustomerQueryRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'7.0','stopOnError');
  }

  function _customer_download_all_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    $this->quickbooks_log($xml);
    $Obj_XML = new SimpleXMLElement($xml);
    $CustomerQueryRs =          $Obj_XML->QBXMLMsgsRs->CustomerQueryRs;
    $iterator =                 (string)$CustomerQueryRs['iterator'];
    $iteratorID =               (string)$CustomerQueryRs['iteratorID'];
    $iteratorRemainingCount =   (string)$CustomerQueryRs['iteratorRemainingCount'];
    $Obj_QB_Import =  new QB_Import($ID);
    $data = array(
      'data' =>                     addslashes($xml),
      'status' =>                   ($iteratorRemainingCount ? 'active' : 'downloaded'),
      'iteratorID' =>               $iteratorID,
      'iteratorRemainingCount' =>   $iteratorRemainingCount
    );
    $Obj_QB_Import->update($data);
    $this->_customer_download_all_response_parse($ID);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." updated");
    if ($iteratorRemainingCount){
      $this->_qbwc_queue_customers_download_from_QB(1);
      Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Beginning next cycle");
    }
  }

  protected function _customer_download_all_response_parse($ID){
    $Obj_QB_Import =      new QB_Import($ID);
    $customers =    $Obj_QB_Import->get_customer_data();
    $matched =      0;
    $Obj_Customer =     new QB_Customer;
    foreach ($customers as $data){
      $ID = $Obj_Customer->lookup_from_qb_data($data);
      if ($ID){
        $matched++;
        $Obj_Customer->_set_ID($ID);
        $data = array(
          'qb_ident' => $data['qb_ident'],
          'qb_name' =>  $data['qb_name']
        );
        $Obj_Customer->update($data,true,false);
      }
    }
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Parsed','Successfully downloaded and parsed '.count($customers).' customers from QB');
  }



  function _customer_query_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    $Obj = new QB_Customer($ID);
    $record = $Obj->load();
    $xml =
       "<CustomerQueryRq requestID=\"".$requestID."\">\n"
      ."  <FullName>".$record['qb_name']."</FullName>\n"
      ."</CustomerQueryRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'7.0','stopOnError');
  }

  function _customer_query_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){

    // If not found, will call error instead - we catch that and process from there.
    $Obj = new QB_Customer($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _inventory_item_add_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Record ".$ID." to be added");

    $Obj = new QB_Inventory_Item($ID);
    $record = $Obj->load();
  	$xml =
       "<ItemInventoryAddRq requestID=\"".$requestID."\">\n"
      ."  <ItemInventoryAdd>\n"
      ."    <Name>".$record['qb_name']."</Name>\n"
      ."    <IsActive>".$record['enable']."</IsActive>\n"
      ."    <SalesDesc>".$record['title']."\r\n".$record['content']."</SalesDesc>\n"
      ."    <SalesPrice>".$record['price']."</SalesPrice>\n"
      ."     <IncomeAccountRef>\n"
      ."      <FullName>".$this->_qbwc_IncomeAccountRef."</FullName>\n"
      ."    </IncomeAccountRef>\n"
      ."    <COGSAccountRef>\n"
      ."      <FullName>".$this->_qbwc_COGSAccountRef."</FullName>\n"
      ."     </COGSAccountRef>\n"
      ."    <AssetAccountRef>\n"
      ."      <FullName>".$this->_qbwc_AssetAccountRef."</FullName>\n"
      ."    </AssetAccountRef>\n"
      ."  </ItemInventoryAdd>\n"
      ."</ItemInventoryAddRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'7.0','stopOnError');
  }

  function _inventory_item_add_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    $Obj = new QB_Inventory_Item($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _inventory_item_query_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    $Obj = new QB_Inventory_Item($ID);
    $record = $Obj->load();
    $xml =
       "<ItemInventoryQueryRq requestID=\"".$requestID."\">\n"
      ."  <FullName>".$record['qb_name']."</FullName>\n"
      ."</ItemInventoryQueryRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'7.0','stopOnError');
  }

  function _inventory_item_query_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    // If not found, will call error instead - we catch that and process from there.
    $data = array(
      'qb_name' =>  $idents['FullName'],
      'qb_ident' => $idents['ListID']
    );
    $Obj = new QB_Inventory_Item($ID);
    $Obj->update($data);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _invoice_add_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
      $Obj =    new QB_Order($ID);
      $data =   $Obj->get_quickbooks_data();
      $Obj_Country =    new Country;
      $country =        $Obj_Country->get_text_for_value($data['BCountryID']);
      // http://wiki.consolibyte.com/wiki/doku.php/quickbooks_qbxml_invoiceadd
      $xml =
         "<InvoiceAddRq requestID=\"".$requestID."\">\n"
        ."  <InvoiceAdd>\n"
        ."    <CustomerRef>\n"
        ."      <ListID>".$data['person_qb_ident']."</ListID>\n"
        ."    </CustomerRef>\n"
        ."    <TxnDate>".substr($data['history_created_date'],0,10)."</TxnDate>\n"
        ."    <RefNumber>".$data['ID']."</RefNumber>\n";
      if ($this->_qbwc_export_orders_billing_addr){
        $xml.=
           "    <BillAddress>\n"
          ."      <Addr1>".$data['WCompany']."</Addr1>\n"
          ."      <Addr2>".$data['NFull']."</Addr2>\n"
          ."      <Addr3>".$data['BAddress1']."</Addr3>\n"
          ."      <Addr4>".$data['BAddress2']."</Addr4>\n"
          ."      <City>".$data['BCity']."</City>\n"
          ."      <State>".$data['BSpID']."</State>\n"
          ."      <PostalCode>".$data['BPostal']."</PostalCode>\n"
          ."      <Country>".$country."</Country>\n"
          ."    </BillAddress>\n";
      }
      $xml.=
         "    <PONumber></PONumber>\n"
        ."    <Memo>".trim(preg_replace('/\s+/', ' ', $data['instructions']))."</Memo>\n";
      foreach($data['items'] as $item){
        if ($item['ID']){
          $xml.=
             "    <InvoiceLineAdd>\n"
            ."      <ItemRef>\n"
            ."        <ListID>".$item['qb_ident']."</ListID>\n"
            ."      </ItemRef>\n";
          if ($this->_qbwc_export_orders_product_desc){
            $xml.=
               "      <Desc>"
              .preg_replace("/[^0-9a-zA-Z\-\/\(\) ]/i", '', $item['title'])
              ."</Desc>\n";
          }
          $xml.=
             "      <Quantity>".$item['quantity']."</Quantity>\n"
            ."      <Rate>".$item['price']."</Rate>\n"
            .$this->_salestaxcodes_get_xml($item)
            ."    </InvoiceLineAdd>\n";
        }
      }
      $xml.=
         "  </InvoiceAdd>\n"
        ."</InvoiceAddRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'2.0','stopOnError');
  }

  function _invoice_add_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    $Obj = new QB_Order($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _invoice_query_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    $Obj = new QB_Order($ID);
    $record = $Obj->load();
    $xml =
       "<InvoiceQueryRq requestID=\"".$requestID."\">\n"
      ."  <RefNumber>".$record['ID']."</RefNumber>\n"
      ."</InvoiceQueryRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'7.0','stopOnError');
  }

  function _invoice_query_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    // If not found, will call error instead - we catch that and process from there.
    $Obj = new QB_Order($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _salesorder_add_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
      $Obj =    new QB_Order($ID);
      $data =   $Obj->get_quickbooks_data();
      $Obj_Country =    new Country;
      $country =        $Obj_Country->get_text_for_value($data['BCountryID']);
      $xml =
         "<SalesOrderAddRq requestID=\"".$requestID."\">\n"
        ."  <SalesOrderAdd>\n"
        ."    <CustomerRef>\n"
        ."      <ListID>".$data['person_qb_ident']."</ListID>\n"
        ."    </CustomerRef>\n"
        ."    <TxnDate>".substr($data['history_created_date'],0,10)."</TxnDate>\n"
        ."    <RefNumber>".$data['ID']."</RefNumber>\n";
      if ($this->_qbwc_export_orders_billing_addr){
        $xml.=
           "    <BillAddress>\n"
          ."      <Addr1>".$data['WCompany']."</Addr1>\n"
          ."      <Addr2>".$data['NFull']."</Addr2>\n"
          ."      <Addr3>".$data['BAddress1']."</Addr3>\n"
          ."      <Addr4>".$data['BAddress2']."</Addr4>\n"
          ."      <City>".$data['BCity']."</City>\n"
          ."      <State>".$data['BSpID']."</State>\n"
          ."      <PostalCode>".$data['BPostal']."</PostalCode>\n"
          ."      <Country>".$country."</Country>\n"
          ."    </BillAddress>\n";
      }
      $xml.=
         "    <PONumber></PONumber>\n"
        ."    <Memo>".trim(preg_replace('/\s+/', ' ', $data['instructions']))."</Memo>\n";
      foreach($data['items'] as $item){
        if ($item['ID']){
          $xml.=
             "    <SalesOrderLineAdd>\n"
            ."      <ItemRef>\n"
            ."        <ListID>".$item['qb_ident']."</ListID>\n"
            ."      </ItemRef>\n";
          if ($this->_qbwc_export_orders_product_desc){
            $xml.=
               "      <Desc>"
              .preg_replace("/[^0-9a-zA-Z\-\/\(\) ]/i", '', $item['title'])
              ."</Desc>\n";
          }
          $xml.=
             "      <Quantity>".$item['quantity']."</Quantity>\n"
            ."      <Rate>".$item['price']."</Rate>\n"
            .$this->_salestaxcodes_get_xml($item)
            ."    </SalesOrderLineAdd>\n";
        }
      }
      $xml.=
         "  </SalesOrderAdd>\n"
        ."</SalesOrderAddRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'10.0','stopOnError');
  }

  function _salesorder_add_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    $Obj = new QB_Order($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _salesorder_query_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    $Obj = new QB_Order($ID);
    $record = $Obj->load();
    $xml =
       "<SalesOrderQueryRq requestID=\"".$requestID."\">\n"
      ."  <RefNumber>".$record['ID']."</RefNumber>\n"
      ."</SalesOrderQueryRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'7.0','stopOnError');
  }

  function _salesorder_query_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    // If not found, will call error instead - we catch that and process from there.
    $Obj = new QB_Tax_Code($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  function _salestaxcode_query_request(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
  ){
    $Obj = new QB_Tax_Code($ID);
    $record = $Obj->load();
    $xml =
       "<SalesTaxCodeQueryRq requestID=\"".$requestID."\">\n"
      ."  <FullName>".$record['qb_name']."</FullName>\n"
      ."</SalesTaxCodeQueryRq>\n";
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Requested',"Query for ".$ID."\r\n".str_replace("\n","\r\n",$xml));
    return $this->_qbwc_wrap_XML($xml,'8.0','stopOnError');
  }

  function _salestaxcode_query_response(
    $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
  ){
    // If not found, will call error instead - we catch that and process from there.
    $Obj = new QB_Tax_Code($ID);
    $Obj->set_field('qb_ident',$idents['ListID']);
    Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Responded',"Record ".$ID." qb_ident set to ".$idents['ListID']);
  }

  protected function _salestaxcodes_get_xml($item){
    if (!$this->_qbwc_export_orders_taxcodes){
      return '';
    }
    $xml = "";
    $tax_arr = array();
    for($i=1; $i<=20; $i++){
      if ((float)$item['tax'.$i.'_cost']!=0){
        $tax_arr[] = $item['tax'.$i.'_name'];
      }
    }
    foreach($tax_arr as $tax){
      $Obj = new Tax_Code;
      $Obj->_set_ID($Obj->get_ID_by_name($tax));
      $listID = $Obj->get_field('qb_ident');
      $xml.=
         "      <SalesTaxCodeRef>"
        ."        <ListID>".$listID."</ListID>\n"
        ."      </SalesTaxCodeRef>";
    }
    return $xml;
  }

  static function quickbooks_log($data=''){
    if (Quickbooks::_do_quickbooks_log){
      $handle = fopen(SYS_LOGS."qb.txt",'a+');
      fwrite($handle, $data."\r\n");
      fclose($handle);
    }
  }

  static function system_log($level,$source,$operation,$message){
    if (Quickbooks::_do_system_log){
      do_log($level,$source,$operation,$message);
    }
  }

  public function get_version(){
    return VERSION_QUICKBOOKS;
  }
}

class QB_Error{
  static function _error_no_match($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg){
    QuickBooks::quickbooks_log("error_no_match: Error code ".$errnum." - ".$errmsg."Handling ".$action." for ID ".$ID);
    return true;
  }

  static function _error_not_found($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg){
    switch($action){
      case 'CustomerQuery':
        Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Not Found',"Record ".$ID." for action ".$action." was not found.");
        $Obj = new QB_Customer($ID);
        $Obj->set_field('qb_ident','not_found');
        return true;
      break;
      case 'InvoiceQuery':
        Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Not Found',"Record ".$ID." for action ".$action." was not found.");
        $Obj = new QB_Order($ID);
        $Obj->set_field('qb_ident','not_found');
        return true;
      break;
      case 'ItemInventoryQuery':
        Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Not Found',"Record ".$ID." for action ".$action." was not found.");
        $Obj = new QB_Inventory_Item($ID);
        $Obj->set_field('qb_ident','not_found');
        return true;
      break;
      case 'ItemSalesTaxQuery':
        Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Not Found',"Record ".$ID." for action ".$action." was not found.");
        $Obj = new QB_Tax_Code($ID);
        $Obj->set_field('qb_ident','not_found');
        return true;
      break;
      case 'SalesOrderQuery':
        Quickbooks::system_log(1,__CLASS__."::".__FUNCTION__.'()','Not Found',"Record ".$ID." for action ".$action." was not found.");
        $Obj = new QB_Order($ID);
        $Obj->set_field('qb_ident','not_found');
        return true;
      break;
    }
    Quickbooks::system_log(3,__CLASS__."::".__FUNCTION__.'()','Not Found',"Record ".$ID." for action ".$action." was not found.");
    return false;
  }

  static function _error_stringtoolong($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg){
    QuickBooks::quickbooks_log("error_stringtoolong: Error code ".$errnum." - ".$errmsg."Handling ".$action." for ID ".$ID);
    return false;
  }


}

class QB_Customer extends User {
  public function get_next_ID_for_QB_conversion($acceptable_codes='',$only_customers=false){
    if ($only_customers){
      $sql =
         "SELECT\n"
        ."  `ID`\n"
        ."FROM\n"
        ."  `person`\n"
        ."WHERE\n"
        ."  `systemID`=".SYS_ID." AND\n"
        ."  `qb_ident` IN('".implode("','",explode(',',$acceptable_codes))."') AND\n"
        ."  (SELECT COUNT(*) FROM `orders` WHERE `orders`.`personID`=`person`.`ID`)>0\n"
        ."LIMIT 0,1";
      return $this->get_field_for_SQL($sql);
    }
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `person`\n"
      ."WHERE\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `qb_ident` IN('".implode("','",explode(',',$acceptable_codes))."')\n"
      ."LIMIT 0,1";
    return $this->get_field_for_SQL($sql);
  }

  public function get_next_ID_for_QB_Name_Lookup($acceptable_codes='',$only_customers=false){
    if ($only_customers){
      $sql =
         "SELECT\n"
        ."  `ID`\n"
        ."FROM\n"
        ."  `person`\n"
        ."WHERE\n"
        ."  `systemID`=".SYS_ID." AND\n"
        ."  `qb_ident` IN('".implode("','",explode(',',$acceptable_codes))."') AND\n"
        ."  `qb_name`!='' AND\n"
        ."  (SELECT COUNT(*) FROM `orders` WHERE `orders`.`personID`=`person`.`ID`)>0\n"
        ."LIMIT 0,1";
      return $this->get_field_for_SQL($sql);
    }
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `person`\n"
      ."WHERE\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `qb_ident` IN('".implode("','",explode(',',$acceptable_codes))."') AND\n"
      ."  `qb_name`!='' AND\n"
      ."LIMIT 0,1";
    return $this->get_field_for_SQL($sql);
  }

  public function lookup_from_qb_data($data){
    if ($data['qb_name']){
      $sql =
         "SELECT\n"
        ."  `ID`\n"
        ."FROM\n"
        ."  `".$this->_get_table_name()."`\n"
        ."WHERE\n"
        ."  `systemID` = ".SYS_ID." AND\n"
        ."  `qb_ident`='' AND\n"
        .($this->_get_type() ? "  `type`=\"".$this->_get_type()."\" AND\n" : "")
        ."  `qb_name`=\"".$data['qb_name']."\"";
      $records = $this->get_records_for_sql($sql);
      if (count($records)==1){
        return $records[0]['ID'];
      }
    }
    if ($data['WCompany']){
      $sql =
         "SELECT\n"
        ."  `ID`\n"
        ."FROM\n"
        ."  `".$this->_get_table_name()."`\n"
        ."WHERE\n"
        ."  `systemID` = ".SYS_ID." AND\n"
        ."  `qb_ident`='' AND\n"
        .($this->_get_type() ? "  `type`=\"".$this->_get_type()."\" AND\n" : "")
        ."  `WCompany`=\"".$data['WCompany']."\"";
      $records = $this->get_records_for_sql($sql);
      if (count($records)==1){
        return $records[0]['ID'];
      }
    }
    if ($data['WEmail']){
      $sql =
         "SELECT\n"
        ."  `ID`\n"
        ."FROM\n"
        ."  `".$this->_get_table_name()."`\n"
        ."WHERE\n"
        ."  `systemID` = ".SYS_ID." AND\n"
        ."  `qb_ident`='' AND\n"
        .($this->_get_type() ? "  `type`=\"".$this->_get_type()."\" AND\n" : "")
        ."  (`WEmail`=\"".$data['WEmail']."\" OR `AEmail`=\"".$data['WEmail']."\")";
      $records = $this->get_records_for_sql($sql);
      if (count($records)==1){
        return $records[0]['ID'];
      }
    }
    if ($data['WTelephone']){
      $number =     preg_replace('/[^0-9]/', '', $data['WTelephone']);
      $pattern =    "REGEXP '";
      for($i=0; $i<strlen($number); $i++){
        $pattern.= '[^0-9]*'.$number[$i];
      }
      $pattern.="[^0-9]*'";
      $sql =
         "SELECT\n"
        ."  `ID`\n"
        ."FROM\n"
        ."  `".$this->_get_table_name()."`\n"
        ."WHERE\n"
        ."  `systemID` = ".SYS_ID." AND\n"
        ."  `qb_ident`='' AND\n"
        .($this->_get_type() ? "  `type`=\"".$this->_get_type()."\" AND\n" : "")
        ."  (`WTelephone` ".$pattern." OR `ATelephone` ".$pattern.")";
      $records = $this->get_records_for_sql($sql);
      if (count($records)==1){
        return $records[0]['ID'];
      }
    }
  }


}

class QB_Import extends Record {
  function __construct($ID='', $systemID=SYS_ID) {
    parent::__construct('qb_import', $ID);
    $this->_set_systemID($systemID);
  }

  public function download_has_completed($type){
    $sql =
       "SELECT\n"
      ."  COUNT(*)\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `type` = '".$type."' AND\n"
      ."  `status`='downloaded'\n";
    return $this->get_field_for_sql($sql);
  }

  public function get_active_download($type){
    $sql =
       "SELECT\n"
      ."  *\n"
      ."FROM\n"
      ."  `".$this->_get_table_name()."`\n"
      ."WHERE\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `type` = '".$type."' AND\n"
      ."  `status`='active' AND\n"
      ."  `iteratorRemainingCount`>0\n"
      ."ORDER BY\n"
      ."  `iteratorRemainingCount` DESC\n"
      ."LIMIT 0,1";
    return $this->get_record_for_sql($sql);
  }

  public function get_customer_data(){
    $record =  $this->get_record();
    $data = array();
    $Obj_XML = new SimpleXMLElement($record['data']);
    $CustomerQueryRs =          $Obj_XML->QBXMLMsgsRs->CustomerQueryRs;
    foreach ($CustomerQueryRs->children() as $child){
      $data[] = array(
        'qb_ident' =>     (string)$child->ListID,
        'qb_name' =>      (string)$child->FullName,
        'NFirst' =>       (string)$child->FirstName,
        'NLast' =>        (string)$child->LastName,
        'WCompany' =>     (string)$child->CompanyName,
        'WEmail' =>       (string)$child->Email,
        'WTelephone' =>   (string)$child->Phone,
        'WPostal' =>      (string)$child->BillAddress->PostalCode
      );
    }
    return $data;
  }
}

class QB_Inventory_Item extends Product {
  public function get_next_ID_for_QB_conversion($acceptable_codes='',$only_ordered=false){
    if ($only_ordered){
      $sql =
         "SELECT\n"
        ."  `ID`\n"
        ."FROM\n"
        ."  `product`\n"
        ."WHERE\n"
        ."  `systemID`=".SYS_ID." AND\n"
        ."  `qb_ident` IN('".(!$acceptable_codes ? "" : implode("','",explode(',',$acceptable_codes)))."') AND\n"
        ."  (SELECT COUNT(*) FROM `order_items` WHERE `order_items`.`productID`=`product`.`ID`)>0\n"
        ."LIMIT 0,1";
      return $this->get_field_for_SQL($sql);
    }
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `product`\n"
      ."WHERE\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `qb_ident` IN('".(!$acceptable_codes ? "" : implode("','",explode(',',$acceptable_codes)))."')\n"
      ."LIMIT 0,1";
    return $this->get_field_for_SQL($sql);
  }
}

class QB_Order extends Order{
  public function get_next_ID_for_QB_conversion($acceptable_codes=''){
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `orders`\n"
      ."WHERE\n"
      ."  `qb_ident` IN('".(!$acceptable_codes ? "" : implode("','",explode(',',$acceptable_codes)))."') AND\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `archive`=0 AND\n"
      ."  `credit_memo_for_orderID`=0 AND\n"
      ."  (SELECT COUNT(*) FROM `order_items` WHERE `order_items`.`orderID` = `orders`.`ID`)>0\n"
      ."LIMIT 0,1";
    $ID = $this->get_field_for_SQL($sql);
//    Quickbooks::system_log(3,__CLASS__."::".__FUNCTION__.'()','Query',"Query found ".$ID." -\n".$sql);
    return $ID;
  }

  public function get_quickbooks_data(){
    $sql =
        "SELECT\n"
       ."  `orders`.*,\n"
       ."  CONCAT(`person`.`NFirst`, ' ',`person`.`NMiddle`, ' ', `person`.`NLast`) `NFull`,\n"
       ."  `person`.`WCompany` `WCompany`,\n"
       ."  `person`.`qb_ident` `person_qb_ident`\n"
       ."FROM\n"
       ."  `orders`\n"
       ."LEFT JOIN `person` ON\n"
       ."  `orders`.`personID`=`person`.`ID`\n"
       ."WHERE\n"
       ."  `orders`.`ID` = ".$this->_get_ID();
    $order = $this->get_record_for_sql($sql);
    $sql =
        "SELECT\n"
       ."  `product`.`ID`,\n"
       ."  `product`.`title`,\n"
       ."  `product`.`qb_ident`,\n"
       ."  `order_items`.*\n"
       ."FROM\n"
       ."  `order_items`\n"
       ."LEFT JOIN `product` ON\n"
       ."  `order_items`.`productID` = `product`.`ID`\n"
       ."WHERE\n"
       ."  `order_items`.`orderID` = ".$this->_get_ID();
    Quickbooks::system_log(3,__CLASS__."::".__FUNCTION__.'()','Query',$sql);
    $order['items'] = $this->get_records_for_sql($sql);
    return $order;
  }
}

class QB_Tax_Code extends Tax_Code {
  public function get_next_ID_for_QB_conversion($acceptable_codes=''){
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `tax_code`\n"
      ."WHERE\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `qb_ident` IN('".(!$acceptable_codes ? "" : implode("','",explode(',',$acceptable_codes)))."')\n"
      ."LIMIT 0,1";
    return $this->get_field_for_SQL($sql);
  }

  public function get_next_ID_for_QB_Name_Lookup($acceptable_codes=''){
    $sql =
       "SELECT\n"
      ."  `ID`\n"
      ."FROM\n"
      ."  `tax_code`\n"
      ."WHERE\n"
      ."  `systemID`=".SYS_ID." AND\n"
      ."  `qb_name`!='' AND\n"
      ."  `qb_ident` IN('".(!$acceptable_codes ? "" : implode("','",explode(',',$acceptable_codes)))."')\n"
      ."LIMIT 0,1";
    return $this->get_field_for_SQL($sql);
  }

}



?>