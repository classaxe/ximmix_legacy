<?php
define('MODULE_CREDITSSYSTEM_VERSION','1.0.4');
/*
Version History:
  1.0.4 (2012-09-04)
    1) Changes to installer (which will probably never be run again anyway) to
       use Record based methods to get query result data
  1.0.3 (2011-03-21)
    1) Replaced add() method throughout with insert() - Record::add() is deprecated
  1.0.2 (2011-02-03)
    1) Constructor now calls  parent::__construct() NOT  parent::Record()
  1.0.1 (2009-10-27)
    1) Change to CreditSystem::doPurchase() because products now have title field,
       not titleEnglish
  1.0.0 (2009-07-20)
    1) Initial unversioned release by JF
*/

class CreditSystem extends Record {
  protected $fields = array(
    'ID',
    'systemID',
    'personID',
    'amount',
    'transaction_type',
    'details',
    'data',
    'ip_address',
    'history_created_by',
    'history_created_date',
    'history_modified_by',
    'history_modified_date'
  );

  public function __construct() {
    parent::__construct("module_credits");
    //$this->table = "cus_trucksafetymanager_credits";
  }

  public function install() {
    // create the table
    $query =
       "CREATE TABLE " . $this->table . "(\r\n"
      ."`ID` bigint(20) unsigned NOT NULL,\r\n"
    		. "`systemID` bigint(20) NOT NULL,\r\n"
    		. "`personID` bigint(20) NOT NULL,\r\n"
    		. "`amount` decimal(10,2) default 0 NOT NULL,\r\n"
    		. "`transaction_type` varchar(64) default '' NOT NULL,\r\n"
    		. "`details` varchar(255) default '',\r\n"
    		. "`data` varchar(255) default '',\r\n"
    		. "`ip_address` varchar(32) default '',\r\n"
    		. "`history_created_by` bigint(20) default 0,\r\n"
    		. "`history_created_date` datetime default '0000-00-00 00:00:00',\r\n"
    		. "`history_created_ip` varchar(32) default '000.000.000.000',\r\n"
    		. "PRIMARY KEY  (`ID`)\r\n"
    	. ") ENGINE=MyISAM DEFAULT CHARSET=utf8\r\n";
    $this->do_sql_query($query);

    // add fields to products table to support credits

    $query = "ALTER TABLE product\r\n"
    	. "\tADD COLUMN module_creditsystem_creditPrice DOUBLE\r\n";
    $this->do_sql_query($query);

    $query = "ALTER TABLE product\r\n"
    	. "\tADD COLUMN module_creditsystem_creditValue DOUBLE\r\n";
    $this->do_sql_query($query);

    $query = "ALTER TABLE product\r\n"
    	. "\tADD COLUMN module_creditsystem_useCredits TINYINT DEFAULT 0\r\n";
    $this->do_sql_query($query);

    $query = "SELECT ID from report WHERE name='person'";
    if (!$reportID = $this->get_field_for_sql($query)){
  	  die("error, the person report was not found");
    }
    $query = "SELECT COUNT(*) FROM report_columns WHERE reportID=" . $reportID . " AND formField='module_creditsystem_balance'";
    if ($this->_get_field_for_sql($query) == 0) {
    	$query = "INSERT INTO report_columns(ID, systemID, reportID, seq, tab, fieldType, formField, formLabel, permMASTERADMIN, permSYSADMIN, reportField, reportLabel, reportSortBy_a, reportSortBy_d, formFieldWidth) VALUES("
    		 . Record::getUniqueID('report_columns') . ","
    		 . SYS_ID . ","
    		 . $reportID . ","
    		 . "501,"
    		 . "'5.Credits',"
    		 . "'currency',"
    		 . "'module_creditsystem_balance',"
    		 . "'Credit Balance',"
    		 . "2,"
    		 . "2,"
    		 . "'module_creditsystem_balance',"
    		 . "'Credit Balance',"
    		 . "'module_creditsystem_balance ASC',"
    		 . "'module_creditsystem_balance DESC',"
    		 . "40)";
    	$this->do_sql_query($query);
    }
    $query = "SELECT ID FROM report WHERE name='product'";
    if (!$reportID = $this->get_field_for_sql($query)){
      die("error, the product report was not found");
    }
    $query = "SELECT count(*) FROM report_columns WHERE reportID=" . $reportID . " AND formField='module_creditsystem_creditValue'";
    if ($this->_get_field_for_sql($query) == 0) {
    	$query = "INSERT INTO report_columns(ID, systemID, reportID, seq, tab, fieldType, formField, formLabel, permMASTERADMIN, permSYSADMIN, reportField, reportLabel, reportSortBy_a, reportSortBy_d, formFieldWidth) VALUES("
    		 . Record::getUniqueID('report_columns') . ","
    		 . SYS_ID . ","
    		 . $reportID . ","
    		 . "501,"
    		 . "'5.Credits',"
    		 . "'currency',"
    		 . "'module_creditsystem_creditValue',"
    		 . "'Credits Awarded',"
    		 . "2,"
    		 . "2,"
    		 . "'module_creditsystem_creditValue',"
    		 . "'Credits Awarded',"
    		 . "'module_creditsystem_creditValue ASC',"
    		 . "'module_creditsystem_creditValue DESC',"
    		 . "40)";
    	$this->do_sql_query($query);
    }

    $query = "SELECT count(*) FROM report_columns WHERE reportID=" . $reportID . " AND formField='module_creditsystem_creditPrice'";
    if ($this->_get_field_for_sql($query) == 0) {
    	$query = "INSERT INTO report_columns(ID, systemID, reportID, seq, tab, fieldType, formField, formLabel, permMASTERADMIN, permSYSADMIN, reportField, reportLabel, reportSortBy_a, reportSortBy_d, formFieldWidth) VALUES("
    		 . Record::getUniqueID('report_columns') . ","
    		 . SYS_ID . ","
    		 . $reportID . ","
    		 . "502,"
    		 . "'5.Credits',"
    		 . "'currency',"
    		 . "'module_creditsystem_creditPrice',"
    		 . "'Price in credits',"
    		 . "2,"
    		 . "2,"
    		 . "'module_creditsystem_creditPrice',"
    		 . "'Price in credits',"
    		 . "'module_creditsystem_creditPrice ASC',"
    		 . "'module_creditsystem_creditPrice DESC',"
    		 . "40)";
    	$this->do_sql_query($query);
    }

    $query = "SELECT count(*) FROM report_columns WHERE reportID=" . $reportID . " AND formField='module_creditsystem_useCredits'";
    if ($this->_get_field_for_sql($query) == 0) {
    	$query = "INSERT INTO report_columns(ID, systemID, reportID, seq, tab, fieldType, formField, formLabel, permMASTERADMIN, permSYSADMIN, reportField, reportLabel, reportSortBy_a, reportSortBy_d) VALUES("
    		 . Record::getUniqueID('report_columns') . ","
    		 . SYS_ID . ","
    		 . $reportID . ","
    		 . "503,"
    		 . "'5.Credits',"
    		 . "'bool',"
    		 . "'module_creditsystem_useCredits',"
    		 . "'Can purchase with credits?',"
    		 . "2,"
    		 . "2,"
    		 . "'module_creditsystem_useCredits',"
    		 . "'Can purchase with credits?',"
    		 . "'module_creditsystem_useCredits ASC',"
    		 . "'module_creditsystem_useCredits DESC')";
    	$this->do_sql_query($query);
    }
  }

  public function uninstall() {
    $this->purgeData();
  }

  public function purgeData() {
    // remove fields from products table
    $query = "ALTER TABLE product\r\n"
    	. " DROP COLUMN module_creditsystem_creditPrice,\r\n"
    	. " DROP COLUMN module_creditsystem_creditValue,\r\n"
    	. " DROP COLUMN module_creditsystem_useCredits\r\n";
    $this->do_sql_query($query);

    // remove fields from person table
    $query = "ALTER TABLE person\r\n"
    	. " DROP COLUMN module_creditsystem_balance\r\n";
    $this->do_sql_query($query);

    // find report
    $query = "SELECT ID FROM report WHERE name='product'";
    $rows = $this->get_records_for_sql($query);
    $reportID = $rows[0]['ID'];

    // remove records from report_columns
    $query = "DELETE FROM report_columns WHERE reportID=" . $reportID . " AND formField='module_creditsystem_creditValue'";
    $this->do_sql_query($query);

    $query = "DELETE FROM report_columns WHERE reportID=" . $reportID . " AND formField='module_creditsystem_creditPrice'";
    $this->do_sql_query($query);

    // remove the balance from the person report
    $query = "SELECT ID FROM report WHERE name='person'";
    $rows = $this->get_records_for_sql($query);
    $reportID = $rows[0]['ID'];

    // remove records from report_columns
    $query = "DELETE FROM report_columns WHERE reportID=" . $reportID . " AND formField='module_creditsystem_balance'";
    $this->do_sql_query($query);

    // drop the main table
    $query = "DROP TABLE " . $this->table;
    $this->do_sql_query($query);
  }

  public function doPurchase($personID, $productID, $quantity, $data) {
    $Obj = new Product($productID);
    if (!$row = $Obj->get_record()){
      die('Error - the product could not be found');
    }
    if (strlen($data) > 0) {
      $data .= ",'productID':'" . $productID . "'";
    }
    else {
      $data = "'productID':'" . $productID . "'";
    }
    $result =
      $this->doTransaction(
        $personID,
        'debit',
        $row['module_creditsystem_creditPrice']*$quantity,
        'Purchase of '.$row['title'],
        $data
      );
    return $result;
  }

  public function purchaseCredits($personID, $productID, $quantity, $data) {
    $query = "SELECT * FROM product WHERE ID=" . $productID;
    if (!$row = $this->get_record_for_sql($query)){
      die('Error - the product could not be found');
    }
    if ($row['module_creditsystem_creditValue'] > 0) {
      $thingy =
      $this->doTransaction(
        $personID,
        'credit',
        $row['module_creditsystem_creditValue']*$quantity,
        'Purchased '.$row['module_creditsystem_creditValue']*$quantity.' credits',
        $data
      );
    }
  }

  public function doRefund($personID, $productID, $quantity) {
  $query = "SELECT * FROM person WHERE ID=" . $personID;
  $query = "SELECT * FROM product WHERE ID=" . $productID;


  }

  public function doTransaction($personID, $type, $amount, $details='', $data='') {
    switch (strtolower($type)) {
      case 'debit':
        $fieldData =
          array(
            'personID' => $personID,
            'systemID' => SYS_ID,
            'transaction_type' => $type,
            'amount' => $amount > 0 ? -1 * $amount : $amount,
            'details' => $details,
            'data' => $data,
            'ip_address' => $_SERVER['REMOTE_ADDR']
          );
        $theAddResult = $this->insert($fieldData);
        $this->consolidateBalance($personID);
        return $theAddResult;
      break;
      case 'credit':
        $fieldData =
          array(
            'personID' => $personID,
            'systemID' => SYS_ID,
            'transaction_type' => $type,
            'amount' => $amount < 0 ? -1 * $amount : $amount,
            'details' => $details,
            'data' => $data,
            'ip_address' => $_SERVER['REMOTE_ADDR']
          );
        $theAddResult = $this->insert($fieldData);
        $this->consolidateBalance($personID);
        return $theAddResult;
      break;
      default:

      break;
    }
    return false;
  }

  public function getTransactions($personID = '', $type = '', $matchingData = array()) {
  // return an array of transaction data
  $query = "SELECT * FROM " . $this->table;
  $clauses = array();

  if (strlen($personID) > 0) {
  	$clauses[] = "personID=" . $personID;
  }

  if (strlen($type) > 0) {
  	$clauses[] = "transaction_type='" . $type . "'";
  }

  if (count($matchingData) > 0) {
  	foreach ($matchingData as $item) {
  		$item = str_replace("'", "''", $item);
  		$clauses[] = "data LIKE '%" . $item . "%'";
  	}
  }

  $query .= count($clauses) > 0 ? " WHERE " . implode(" AND ", $clauses) : "";
  return $this->get_records_for_sql($query);
  }

  public function getTransactionByID($transactionID) {
    // return a transaction record (assoc array)
    $query = "SELECT * FROM " . $this->table . " WHERE ID=" . $transactionID;
    return $this->get_record_for_sql($query);
  }

  public function consolidateBalance($personID = '') {
    if (strlen($personID == 0)) {
      return false;
    }
    $person = new Person($personID);
    if (!$person->exists()) {
      return false;
    }
    $balance = $this->calculateBalance($personID);
    $person->set_field('module_creditsystem_balance', $balance, false);
    return true;
  }

  public function getBalance($personID = '') {
    // not currently implemented
    // we need to decide where this will live (a field in person table, for example)
    $query = "SELECT module_creditsystem_balance FROM person WHERE ID=" . $personID;
    $row = $this->get_record_for_sql($query);
    return $row['balance'];
  }

  public function calculateBalance($personID = '') {
  // calculate the person's balance based on previous transactions
  $query = "SELECT SUM(amount) AS thetotal FROM " . $this->table . " WHERE personID=" . $personID;
  $row = $this->get_record_for_sql($query);
  return ($row['thetotal'] == null ? 0 : $row['thetotal']);
  // update the balance in the person record from here maybe
  }

  public function getCreditBasedProducts() {


  }

  }
?>