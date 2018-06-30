<?php
/*
Version History:
  1.0.3 (2012-05-02)
    1)
  1.0.2 (2012-02-22)
  1.0.1 (2012-02-17)
    1) Changes to operate with Ecclesiact environment:
       Globalising $dsn
       Setting include path to shared folder
       Updating person records when quickbooks inserts customer and issues a
       ListID as the primary key
    2) TODO:
       Major tidy up
       Complete real-word code for syncronisation functions
  1.0.0 (?)
    1) Initial release by Keith Palmer
*/

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . SYS_SHARED.'qb');
require_once 'QuickBooks.php';

global $dsn;
$user = 'qbwc';
$pass = 'password';

// Logging level
//$log_level = QUICKBOOKS_LOG_NORMAL;
//$log_level = QUICKBOOKS_LOG_VERBOSE;
//$log_level = QUICKBOOKS_LOG_DEBUG;
$log_level = QUICKBOOKS_LOG_DEVELOP;		// Use this level until you're sure everything works!!!
if (function_exists('date_default_timezone_set')){
  date_default_timezone_set('America/New_York');
}

error_reporting(E_ALL);
ini_set('display_errors',1);

$map = array(
  QUICKBOOKS_ADD_CUSTOMER => array(
    '_quickbooks_customer_add_request',
    '_quickbooks_customer_add_response'
  ),
  QUICKBOOKS_ADD_SALESRECEIPT => array(
    '_quickbooks_salesreceipt_add_request',
    '_quickbooks_salesreceipt_add_response'
  ),
);

$errmap = array(
  3070 => '_quickbooks_error_stringtoolong',				// Whenever a string is too long to fit in a field, call this function: _quickbooks_error_stringtolong()
);

$hooks = array(
);
$soapserver = QUICKBOOKS_SOAPSERVER_BUILTIN;		// A pure-PHP SOAP server (no PHP ext/soap extension required, also makes debugging easier)
$soap_options = array();
$handler_options = array();
$driver_options = array();
$callback_options = array();

if (!QuickBooks_Utilities::initialized($dsn)){
	QuickBooks_Utilities::initialize($dsn);
	QuickBooks_Utilities::createUser($dsn, $user, $pass);
}

if (QuickBooks_Utilities::initialized($dsn)){
	$primary_key_of_new_order = 205000501;
    $primary_key_of_new_receipt = 123455;
    $Obj_Person = new Person;
    if ($personID = $Obj_Person->get_next_ID_for_QB_conversion()){
      $Queue = new QuickBooks_Queue($dsn);
      $Queue->enqueue(QUICKBOOKS_ADD_CUSTOMER, $personID, 10);
    }
	//	Queue up the customer with a priority of 10
//	$Queue->enqueue(QUICKBOOKS_ADD_INVOICE, $primary_key_of_new_order, 1);
	// Queue up the invoice with a priority of 0, to make sure it doesn't run until after the customer is created
    $Queue = new QuickBooks_Queue($dsn);
	$Queue->enqueue(QUICKBOOKS_ADD_SALESRECEIPT, $primary_key_of_new_receipt, 1);
    $Server = new QuickBooks_Server($dsn, $map, $errmap, $hooks, $log_level, $soapserver, QUICKBOOKS_WSDL, $soap_options, $handler_options, $driver_options, $callback_options);
    $response = $Server->handle(true, true);
}


	// Queueing up a test request
	//
	// You can instantiate and use the QuickBooks_Queue class to queue up
	//	actions whenever you want to queue something up to be sent to
	//	QuickBooks. So, for instance, a new customer is created in your
	//	database, and you want to add them to QuickBooks:
	//
	//	Queue up a request to add a new customer to QuickBooks
	//	$Queue = new QuickBooks_Queue($dsn);
	//	$Queue->enqueue(QUICKBOOKS_ADD_CUSTOMER, $primary_key_of_new_customer);
	//
	// Oh, and that new customer placed an order, so we want to create an
	//	invoice for them in QuickBooks too:
	//
	//	Queue up a request to add a new invoice to QuickBooks
	//
	// Remember that for each action type you queue up, you should have a
	//	request and a response function registered by using the $map parameter
	//	to the QuickBooks_Server class. The request function will accept a list
	//	of parameters (one of them is $ID, which will be passed the value of
	//	$primary_key_of_new_customer/order that you passed to the ->enqueue()
	//	method and return a qbXML request. So, your request handler for adding
	//	customers might do something like this:
	//
	//	$arr = mysql_fetch_array(mysql_query("SELECT * FROM my_customer_table WHERE ID = " . (int) $ID));
	//	// build the qbXML CustomerAddRq here
	//	return $qbxml;
	//
	// We're going to queue up a request to add a customer, just as a demo...
	// 	NOTE: You would *never* want to do this in this file *unless* it's for testing. See example_integration.php for more details!

	// Also note the that ->enqueue() method supports some other parameters:
	// 	string $action				The type of action to queue up
	//	mixed $ident = null			Pass in the unique primary key of your record here, so you can pull the data from your application to build a qbXML request in your request handler
	//	$priority = 0				You can assign priorities to requests, higher priorities get run first
	//	$extra = null				Any extra data you want to pass to the request/response handler
	//	$user = null				If you're using multiple usernames, you can pass the username of the user to queue this up for here
	//	$qbxml = null
	//	$replace = true
	//
	// Of particular importance and use is the $priority parameter. Say a new
	//	customer is created and places an order on your website. You'll want to
	//	send both the customer *and* the sales receipt to QuickBooks, but you
	//	need to ensure that the customer is created *before* the sales receipt,
	//	right? So, you'll queue up both requests, but you'll assign the
	//	customer a higher priority to ensure that the customer is added before
	//	the sales receipt.
	//

// Create a new server and tell it to handle the requests
// __construct($dsn_or_conn, $map, $errmap = array(), $hooks = array(), $log_level = QUICKBOOKS_LOG_NORMAL, $soap = QUICKBOOKS_SOAPSERVER_PHP, $wsdl = QUICKBOOKS_WSDL, $soap_options = array(), $handler_options = array(), $driver_options = array(), $callback_options = array()
//die('init okay');

/*
$fp = fopen('/www/logs/qb.txt', 'a+');
fwrite($fp, $response);
fclose($fp);
*/

/**
 * Generate a qbXML response to add a particular customer to QuickBooks
 *
 * So, you've queued up a QUICKBOOKS_ADD_CUSTOMER request with the
 * QuickBooks_Queue class like this:
 * 	$Queue = new QuickBooks_Queue('mysql://user:pass@host/database');
 * 	$Queue->enqueue(QUICKBOOKS_ADD_CUSTOMER, $primary_key_of_your_customer);
 *
 * And you're registered a request and a response function with your $map
 * parameter like this:
 * 	$map = array(
 * 		QUICKBOOKS_ADD_CUSTOMER => array( '_quickbooks_customer_add_request', '_quickbooks_customer_add_response' ),
 * 	 );
 *
 * This means that every time QuickBooks tries to process a
 * QUICKBOOKS_ADD_CUSTOMER action, it will call the
 * '_quickbooks_customer_add_request' function, expecting that function to
 * generate a valid qbXML request which can be processed. So, this function
 * will generate a qbXML CustomerAddRq which tells QuickBooks to add a
 * customer.
 *
 * Our response function will in turn receive a qbXML response from QuickBooks
 * which contains all of the data stored for that customer within QuickBooks.
 *
 * @param string $requestID					You should include this in your qbXML request (it helps with debugging later)
 * @param string $action					The QuickBooks action being performed (CustomerAdd in this case)
 * @param mixed $ID							The unique identifier for the record (maybe a customer ID number in your database or something)
 * @param array $extra						Any extra data you included with the queued item when you queued it up
 * @param string $err						An error message, assign a value to $err if you want to report an error
 * @param integer $last_action_time			A unix timestamp (seconds) indicating when the last action of this type was dequeued (i.e.: for CustomerAdd, the last time a customer was added, for CustomerQuery, the last time a CustomerQuery ran, etc.)
 * @param integer $last_actionident_time	A unix timestamp (seconds) indicating when the combination of this action and ident was dequeued (i.e.: when the last time a CustomerQuery with ident of get-new-customers was dequeued)
 * @param float $version					The max qbXML version your QuickBooks version supports
 * @param string $locale
 * @return string							A valid qbXML request
 */
function _quickbooks_customer_add_request(
  $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
){
    $Obj_Person = new Person($ID);
    $record = $Obj_Person->load();
	return
       "<?xml version=\"1.0\" encoding=\"utf-8\"?".">\n"
      ."<?qbxml version=\"2.0\"?".">\n"
      ."<QBXML>\n"
      ."  <QBXMLMsgsRq onError=\"stopOnError\">\n"
      ."    <CustomerAddRq requestID=\"".$requestID."\">\n"
      ."      <CustomerAdd>\n"
      ."        <Name>".$record['NFirst'].' '.$record['NMiddle'].' '.$record['NLast']."</Name>\n"
      ."        <CompanyName>".$record['WCompany']."</CompanyName>\n"
      ."        <FirstName>".$record['NFirst']."</FirstName>\n"
      ."        <LastName>".$record['NLast']."</LastName>\n"
      ."        <BillAddress>\n"
      ."          <Addr1>".$record['AAddress1']."</Addr1>\n"
      ."          <Addr2>".$record['AAddress2']."</Addr2>\n"
      ."          <City>".$record['ACity']."</City>\n"
      ."          <State>".$record['ASpID']."</State>\n"
      ."          <PostalCode>".$record['APostal']."</PostalCode>\n"
      ."          <Country>".$record['ACountryID']."</Country>\n"
      ."        </BillAddress>\n"
      ."        <Phone>".$record['ATelephone']."</Phone>\n"
      ."        <AltPhone>".$record['ACellphone']."</AltPhone>\n"
      ."        <Fax>".$record['AFax']."</Fax>\n"
      ."        <Email>".$record['PEmail']."</Email>\n"
      ."        <Contact>".$record['NFirst'].' '.$record['NLast']."</Contact>\n"
      ."      </CustomerAdd>\n"
      ."    </CustomerAddRq>\n"
      ."  </QBXMLMsgsRq>\n"
      ."</QBXML>\n";
}

function _quickbooks_salesreceipt_add_request(
  $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $version, $locale
){
	/*
	*/
	$xml = '<?xml version="1.0" encoding="utf-8"?>
		<?qbxml version="2.0"?>
		<QBXML>
			<QBXMLMsgsRq onError="stopOnError">
				<SalesReceiptAddRq requestID="' . $requestID . '">
					<SalesReceiptAdd>
		<CustomerRef>
			<ListID>8000003B-1335990802</ListID>
		</CustomerRef>
						<TxnDate>2012-01-09</TxnDate>
						<RefNumber>16467</RefNumber>
						<BillAddress>
							<Addr1>Keith Palmer Jr.</Addr1>
							<Addr3>134 Stonemill Road</Addr3>
							<City>Storrs-Mansfield</City>
							<State>CT</State>
							<PostalCode>06268</PostalCode>
							<Country>United States</Country>
						</BillAddress>
						<SalesReceiptLineAdd>
							<ItemRef>
								<FullName>Gift Certificate</FullName>
							</ItemRef>
							<Desc>$25.00 gift certificate</Desc>
							<Quantity>1</Quantity>
							<Rate>25.00</Rate>
							<SalesTaxCodeRef>
								<FullName>NON</FullName>
							</SalesTaxCodeRef>
						</SalesReceiptLineAdd>
					</SalesReceiptAdd>
				</SalesReceiptAddRq>
			</QBXMLMsgsRq>
		</QBXML>';
 	return $xml;
}

function _quickbooks_customer_add_response(
  $requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents
){
  $Obj_Person = new Person($ID);
  $Obj_Person->set_field('qb_ident',$idents['ListID']);
}

function _quickbooks_salesreceipt_add_response($requestID, $user, $action, $ID, $extra, &$err, $last_action_time, $last_actionident_time, $xml, $idents)
{
	// Great, sales receipt $ID has been added to QuickBooks with a QuickBooks
	//	TxnID value of: $idents['TxnID']
	//
	// The QuickBooks EditSequence is: $idents['EditSequence']
	//
	// We probably want to store that TxnID in our database, so we can use it
	//	later. You might also want to store the EditSequence. If you wanted to
	//	issue a SalesReceiptMod to modify the sales receipt somewhere down the
	//	road, you'd need to refer to the sales receipt using the TxnID and
	//	EditSequence
}

/**
 * Catch and handle a "that string is too long for that field" error (err no. 3070) from QuickBooks
 *
 * @param string $requestID
 * @param string $action
 * @param mixed $ID
 * @param mixed $extra
 * @param string $err
 * @param string $xml
 * @param mixed $errnum
 * @param string $errmsg
 * @return void
 */
function _quickbooks_error_stringtoolong($requestID, $user, $action, $ID, $extra, &$err, $xml, $errnum, $errmsg)
{
	mail('your-email@your-domain.com',
		'QuickBooks error occured!',
		'QuickBooks thinks that ' . $action . ': ' . $ID . ' has a value which will not fit in a QuickBooks field...');
}
?>