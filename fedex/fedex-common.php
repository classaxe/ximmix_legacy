<?php

// Copyright 2007, FedEx Corporation. All rights reserved.

define('TRANSACTIONS_LOG_FILE', SYS_SHARED.'fedex/fedextransactions.log');  // Transactions log file

/**
 *  Print SOAP request and response
 */
function printRequestResponse($client) {
  echo '<h2>Transaction processed successfully.</h2>'. "\n"; 
  echo '<h2>Request</h2>' . "\n";
  echo '<pre>' . htmlspecialchars($client->__getLastRequest()). '</pre>';  
  echo "\n";
   
  echo '<h2>Response</h2>'. "\n";
  echo '<pre>' . htmlspecialchars($client->__getLastResponse()). '</pre>';
  echo "\n";
}

/**
 *  Print SOAP Fault
 */
function printFault($exception, $client) {
  // Intermediate VersionId in the v1 responses for Rate, RateAvailableServices, Ship does not match the WSDL. The response is successful though and
  // to avoid failing the transaction this check is needed.
  if("SOAP-ERROR: Encoding: Element 'Intermediate' has fixed value '1' (value '0' is not allowed)" !=  $exception->faultstring)
  {
    echo '<h2>Fault</h2>' . "\n";                        
    echo "<b>Code:</b>{$exception->faultcode}<br>\n";
    echo "<b>String:</b>{$exception->faultstring}<br>\n";
  }
  else
  {
      writeToLog($client);
      printRequestResponse($client); 
  }
}

/**
 * SOAP request/response logging to a file
 */                                  
function writeToLog($client){  
if (!$logfile = fopen(TRANSACTIONS_LOG_FILE, "a"))
{
   error_func("Cannot open " . TRANSACTIONS_LOG_FILE . " file.\n", 0);
   exit(1);
}

//fwrite($logfile, sprintf("\r%s:- %s",date("D M j G:i:s T Y"), $client->__getLastRequest(). "\n\n" . $client->__getLastResponse()));
}

?>