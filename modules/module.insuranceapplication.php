<?php
define('MODULE_INSURANCEAPPLICATION_VERSION','1.0.5');

/*
Version History:
  1.0.5 (2012-09-09)
    1) Changes to Forum::processApplication() to avoid native DB access
  1.0.4 (2011-07-19)
    1) References to HSFinancial anonymised:
       a) table name changed from cus_hsfinancial_completedApplications to
          cus_insurance_applications and references to ->table changes for getter access
       b) Internal references to emailToHSF changed to emailToBroker
       c) InsuranceApplication::downloadFlattenedPDF() now accesses system_vars to
          get correct URL for site to include embedded link
  1.0.3 (2011-01-28)
    1) Replaced call to add() with insert() in InsuranceApplication::processApplication()
  1.0.2 (2011-01-24)
    1) Changes to eliminate deprecated function calls
  1.0.1 (2010-11-08)
    1) Widened fields to fit real data
    2) Added 'x_documentAltName' to protected field list
    3) Added 'Alt' link in InsuranceApplication::listApplications() if
       x_documentAltName is set and has a value
    4) InsuranceApplication::downloadFlattenedPDF() now takes optional 'use_alt'
       which uses the alt document for display if given
  1.0.0
    1) Initial (unversioned) release
*/

class InsuranceApplication extends Record {
	
	protected $fields = array(
		'ID',
		'systemID',
		'x_company',
		'x_companyOperatingName',
		'x_businessType',
		'x_copartnersNames',
		'x_contactFirst',
		'x_contactLast',
		'x_contactIsOwner',
		'x_contactIsSubcontractor',
		'x_contactPhone',
		'x_contactPhoneOther',
		'x_contactFax',
		'x_contactEmail',
		'x_contactWebsite',
		'x_contactPhone',
		'x_contactAddress',
		'x_contactAddressOther',
		'x_documentAltName',
		'x_documentName',
		'xmlDoc',
		'status',
		'userAgent',
		'history_created_by',
		'history_created_date',
		'history_created_IP',
		'history_modified_by',
		'history_modified_date',
		'history_modified_IP'
	);

	public function __construct() {
		parent::__construct("cus_insurance_applications");
		$this->set_module_version(MODULE_INSURANCEAPPLICATION_VERSION);
	}

	public function install() {
		// create the table
		$this->uninstall();
		
		$query = "CREATE TABLE " . $this->_get_table_name() . "(\r\n"
		  . "`ID` bigint(20) unsigned NOT NULL,\r\n"
  		. "`systemID` bigint(20) NOT NULL,\r\n"
  		. "`archive` tinyint(1) NOT NULL,\r\n"
  		. "`archiveID` bigint(20) NOT NULL,\r\n"  		
			. "`x_company` varchar(255) default '',\r\n"
			. "`x_companyOperatingName` varchar(255) default '',\r\n"
			. "`x_businessType` varchar(255) default '',\r\n"
			. "`x_copartnersNames` varchar(255) default '',\r\n"
			. "`x_contactName` varchar(255) default '',\r\n"
			. "`x_contactFirst` varchar(255) default '',\r\n"
			. "`x_contactLast` varchar(255) default '',\r\n"			
			. "`x_contactIsOwner` varchar(255) default '',\r\n"
			. "`x_contactIsSubcontractor` varchar(255) default '',\r\n"
			. "`x_contactPhone` varchar(255) default '',\r\n"				
			. "`x_contactPhoneOther` varchar(255) default '',\r\n"				
			. "`x_contactFax` varchar(255) default '',\r\n"				
			. "`x_contactEmail` varchar(255) default '',\r\n"				
			. "`x_contactWebsite` varchar(255) default '',\r\n"				
			. "`x_contactAddress` text default '',\r\n"				
			. "`x_contactAddressOther` text default '',\r\n"				
			. "`x_documentName` varchar(255) default '',\r\n"			
			. "`x_documentDate` varchar(255) default '',\r\n"			
			. "`xmlDoc` text default '',\r\n"
			. "`status` varchar(255) default '',\r\n"			
			. "`userAgent` varchar(255) default '',\r\n"			
  		. "`history_created_by` bigint(20) default 0,\r\n"
  		. "`history_created_date` datetime default '0000-00-00 00:00:00',\r\n"
  		. "`history_created_IP` varchar(23) default '',\r\n"
  		. "`history_modified_by` bigint(20) default 0,\r\n"
  		. "`history_modified_date` datetime default '0000-00-00 00:00:00',\r\n"
  		. "`history_modified_IP` varchar(23) default '',\r\n"
  		. "PRIMARY KEY  (`ID`)\r\n"
			. ") ENGINE=MyISAM DEFAULT CHARSET=utf8\r\n";
		$this->do_sql_query($query);

	}

	public function uninstall() {
		$query = "IF EXISTS " . $this->_get_table_name() . " DROP TABLE " . $this->_get_table_name();
		$this->do_sql_query($query);
	}

	public function processApplication($applicationData = '') {
		if ($applicationData == '') {
			$applicationData = file_get_contents('php://input');
		}
		// try to jam the application data into a DOMDocument
		try {
			$dd = new DOMDocument;
			$dd->loadXML($applicationData);
			$xp = new DOMXPath($dd);
			$xp->registerNamespace("xfdf", "http://ns.adobe.com/xfdf/");
			// we will create a new record with the data in this array
			$dataToAdd = array();
			$msg = "";
			foreach ($this->fields as $field) {
				if (substr($field, 0, 2) == "x_") {
					$nodeList = $xp->query("//xfdf:fields/xfdf:field[@name='$field']/xfdf:value");
					if ($nodeList->length > 0) {
						// we found some data, add it to our array
						$dataToAdd[$field] = Record::escape_string($nodeList->item(0)->nodeValue);
					}
				}
			}
			// and add the entire document as well
			$dataToAdd['xmlDoc'] = Record::escape_string($dd->saveXML($dd->documentElement));
			// add the status
			$dataToAdd['status'] = 'submitted_pending';
			// and the user agent in case we have weirdness
			$dataToAdd['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
			// system id
			$dataToAdd['systemID'] = SYS_ID;
			// die($dd->saveXML($dd->documentElement));
			$theNewID = $this->insert($dataToAdd);
			// now let's add the id and the date to the xfdf data
			$this->_set_ID($theNewID);
			$application = $this->get_record(false);
			$fieldsNode = $xp->query("//xfdf:fields")->item(0);
			$testNodes = $xp->query("xfdf:field[@name='ReferenceNum']", $fieldsNode);
			if ($testNodes->length == 0) {
				$appIdNode = $fieldsNode->appendChild($dd->createElement("field"));
				$appIdNode->setAttribute("name", "ReferenceNum");
			} else {
				$appIdNode = $testNodes->item(0);
			}
			$testNodes = $xp->query("xfdf:value", $appIdNode);
			if ($testNodes->length == 0) {
				$valueNode = $appIdNode->appendChild($dd->createElement("value"));
			} else {
				$valueNode = $testNodes->item(0);
			}
			$valueNode->nodeValue = $theNewID;

			$testNodes = $xp->query("xfdf:field[@name='ApplicationDate']", $fieldsNode);
			if ($testNodes->length == 0) {
				$appDateNode = $fieldsNode->appendChild($dd->createElement("field"));
				$appDateNode->setAttribute("name", "ApplicationDate");
			} else {
				$appDateNode = $testNodes->item(0);
			}
			$testNodes = $xp->query("xfdf:value", $appDateNode);
			if ($testNodes->length == 0) {
				$valueNode = $appDateNode->appendChild($dd->createElement("value"));
			} else {
				$valueNode = $testNodes->item(0);
			}
			$valueNode->nodeValue = date("F j, Y H:i:s", datestamp2date($application['history_created_date']));
			//$this->appendAnnotation($dd);
			$dd->formatOutput = true;
			$this->update(array('xmlDoc' => Record::escape_string($dd->saveXML($dd->documentElement))));
			
			// now send the user the flattened form with the date and application id fields filled out
			// this also emails the form to the person specified in email_form_address
			$this->downloadFlattenedPDF($theNewID, true);
		}
        catch (Exception $e) {
			die("An error occurred processing your form.  Please contact us via telephone.");
		}
	}
	
	public function getApplications($filter = '', $order = '') {
		$clauses = array();
		
		if ($filter == '') {
			
		} else {
			$clauses = array($filter);
		}
		
		$sql = "SELECT *, CONCAT(x_contactFirst, ' ', x_contactLast) AS x_contactFullName FROM " . $this->_get_table_name();
		$sql .= count($clauses) > 0 ? " WHERE " . implode(" AND ", $clauses) : "";
		
		if ($order != '') {
			$sql .= " ORDER BY $order";
		}
		
		$list = $this->get_records_for_sql($sql);
		return $list;
	}
	
	public function getApplication($id) {
		$sql = "SELECT * FROM " . $this->_get_table_name() . " WHERE ID=$id";
		$fields = $this->get_record_for_sql($sql);
		return $fields;
	}
	
	protected function deleteApplication($id) {
		$sql = "DELETE FROM " . $this->_get_table_name() . " WHERE ID=$id";
		$this->do_sql_query($sql);
	}
	
	protected function exportCSV($appIds) {
		$csvString = "";
		$hasHeader = false;

		$applicationType = "";

		foreach ($appIds as $dummy => $appId) {
			$app = $this->getApplication($appId);
			
			if ($applicationType == "") {
				$applicationType = $app['x_documentName'];
			} else {
				if ($applicationType != $app['x_documentName']) {
					return "Sorry, you can only export applications of the same type together";
				}
			}
			
			$dd = new DOMDocument;
			$dd->loadXML($app['xmlDoc']);
			$xp = new DOMXPath($dd);
			$xp->registerNamespace("xfdf", "http://ns.adobe.com/xfdf/");
			$nodeList = $xp->query("//xfdf:fields/xfdf:field");
			//die($nodeList->length);
			
			if (!$hasHeader) {
				$csvArray = array();
				foreach ($nodeList as $node) {
					$csvArray[] = "\"" . $node->getAttribute("name") . "\"";
				}
				$csvString .= implode(",", $csvArray) . "\r\n";
				$hasHeader = true;
			}
			$csvArray = array();
			foreach ($nodeList as $node) {
				$nodeValueList = $xp->query("xfdf:value", $node);
				if ($nodeValueList->length == 1) {
					$csvArray[] = "\"" . $nodeValueList->item(0)->nodeValue . "\"";
				} else {
					$csvArray[] = "\"\"";
				}
			}
			$csvString .= implode(",", $csvArray) . "\r\n";
		}
		
		header("Content-Type: text/csv");
		header("Content-Disposition: attachment; filename=export_" . date("Y-m-d_H-i-s") . ".csv");
		die($csvString);
	}
	
	public function listApplications() {
		$appFilter =        get_var('applications_filter');
		$appOrder =         get_var('applications_order','status ASC, history_created_date DESC');
		$appWithSelected =  get_var('applications_withselected');
		switch ($appWithSelected) {
			case 'export_to_excel':
				$appList = isset($_REQUEST['application_selector_checkbox']) ? $_REQUEST['application_selector_checkbox'] : array();
				if (count($appList) > 0) {
					$errorMsg = $this->exportCSV($appList);
					// if errorMsg contains a value and we haven't died, there was an error
				}
			break;
			case 'delete':
				if (isset($_REQUEST['application_selector_checkbox']) && count($_REQUEST['application_selector_checkbox']) > 0) {
					foreach ($_REQUEST['application_selector_checkbox'] as $appId) {
						$this->deleteApplication($appId);
					}
				}				
			break;
			case 'set_status_processed':
				if (isset($_REQUEST['application_selector_checkbox']) && count($_REQUEST['application_selector_checkbox']) > 0) {
					foreach ($_REQUEST['application_selector_checkbox'] as $appId) {
						$app = $this->getApplication($appId);
						$this->_set_ID($appId);
						$this->update(array('status' => 'processed'));
					}
				}				
			break;
			case 'set_status_pending':
				if (isset($_REQUEST['application_selector_checkbox']) && count($_REQUEST['application_selector_checkbox']) > 0) {
					foreach ($_REQUEST['application_selector_checkbox'] as $appId) {
						$app = $this->getApplication($appId);
						$this->_set_ID($appId);
						$this->update(array('status' => 'submitted_pending'));
					}
				}				
			break;
		}
		$appStatus = array(
			'' => '',
			'submitted_pending' => '#eeeedd',
			'processed' => '#ddeedd',
			'approved' => '#eeeeee',
			'declined' => '#eedddd'
		);
		$tableColumns = array(
			'id' => array(
				'title' => 'ID',
				'field' => 'ID',
				'width' => '90',
				'content' => '%%field%%',
				'sortByAsc' => 'ID ASC',
				'sortByDesc' => 'ID DESC'
			),
			'contactName' => array(
				'title' => 'Applicant Name',
				'field' => 'x_contactFullName',
				'width' => '150',
				'content' => '%%field%%',
				'sortByAsc' => 'x_contactFullName ASC',
				'sortByDesc' => 'x_contactFullName DESC'
			),
			'type' => array(
				'title' => 'Type',
				'field' => 'x_documentName',
				'width' => '170',
				'content' => '%%field%%',
				'sortByAsc' => 'x_documentName ASC',
				'sortByDesc' => 'x_documentName DESC'				
			),
			'status' => array(
				'title' => 'Status',
				'field' => 'status',
				'width' => '140',
				'content' => '%%field%%',
				'sortByAsc' => 'status ASC',
				'sortByDesc' => 'status DESC'
			),
			'date' => array(
				'title' => 'Date',
				'field' => 'history_created_date',
				'width' => '160',
				'content' => '%%field%%',
				'sortByAsc' => 'history_created_date ASC',
				'sortByDesc' => 'history_created_date DESC'
			),
			'view' => array(
				'title' => 'Tools',
				'field' => 'ID',
				'width' => '150',
				'content' =>
					 "<input type='checkbox' name='application_selector_checkbox[]' value='%%field%%' %%isselected%% />&nbsp;\n"
					."<a rel='external' href='/viewApplication?id=%%field%%'>PDF</a>&nbsp;\n"
					."%%altview%%"
					."<a rel='external' href='/viewXML?id=%%field%%'>XML</a>"
			)
		);
		
		$html = "";
		$apps = $this->getApplications($appFilter, $appOrder);
//		y($apps);
		$isIE =	 strpos(@$_SERVER['HTTP_USER_AGENT'],"MSIE");
		
		$cellBorderWidth = 1;
		$cellPaddingWidth = 5;
		$widthFix = $isIE ? $cellBorderWidth + $cellPaddingWidth * 2 : 0;
		
		$containerWidth = 0;
		foreach ($tableColumns as $tc) {
			$containerWidth += intval($tc['width']) + ($cellBorderWidth * 2);
		}
		$containerWidth += 10; //scrollbar
		$containerWidth-=70;
		$html .= <<<CSSTEXT
<style type='text/css'>
table.adminList {
	padding: 0;
	margin: 0;
	table-layout: fixed;
	border-collapse: collapse;
	font-size: 12px;
}
table.adminList td, table.adminList th {
	border: {$cellBorderWidth}px solid #999999;
	padding: {$cellPaddingWidth}px;
}
table.adminList_top tr {
	background-color: #013775;
	color: #cdb97d;
}
table.adminList_top th {
	text-align: center;
	position: relative;
}
table.adminList_bottom td {
	overflow: none;
}
.adminList_container {
	width: {$containerWidth}px;
	height: 400px;
	overflow: auto;
	clear: both;
}
.adminList_topbar {

	width:{$containerWidth}px;
	background-color: #013775;
	color: #cdb97d;
	xtext-align: right;
	font-weight: bold;
	position: relative;
	height: 22px;
}
.adminList_topbar span.status {
  xfloat: right;
}
iframe.iefix_select {
}
img.sortArrowUp {
  position: absolute;
  top: 4px;
  right: 1px;
  width: 11px;
  height: 6px;
	cursor: pointer;    
}
img.sortArrowDown {
  position: absolute;
  top: 13px;
  right: 1px;
  width: 11px;
  height: 6px;
	cursor: pointer;  
}
</style>
CSSTEXT;

		$html .= <<<JAVASCRIPTY
<script type='text/javascript'>
function handleWithSelected(ws) {
	switch (ws.value) {
		case 'delete':
			var cnf = confirm("Actually delete the selected applications?");
			if (cnf) {
				ws.form.action = "";
				ws.form.submit();
			}
		break;
		case 'export_to_excel':
		case 'set_status_processed':
		case 'set_status_pending':
			ws.form.action = "";
			ws.form.submit();
		break;
		case '':
			return false;
		break;
	}
	ws.options.selectedIndex = 0;
	//var chx = ws.form.elements['application_selector_checkbox[]'];
	//for (var i=0; i < chx.length; i++) {
	//	chx[i].checked = false;
	//}
			
}
function applySortOrder(theOrder) {
	document.getElementById('applications_order').value = theOrder;
	document.forms[0].submit();
}
</script>
JAVASCRIPTY;

	
		$filter = isset($_REQUEST['applications_filter']) ? $_REQUEST['applications_filter'] : '';
		
		$html .= "<input type='hidden' name='applications_order' id='applications_order' value='' />\r\n";
		$html .= "<table class='adminList adminList_top'>\r\n";
		foreach ($tableColumns as $tc) {
			$html .= "<col style='width:" . ($tc['width'] - $widthFix) . "px;' />";
			//$html .= "<col width='" . ($tc['width'] - $widthFix) . "' />";			
		}
		$html .= "<col style='width: " . ($isIE ? 18 - $cellPaddingWidth * 2 : 18) . "px;' />";
		//$html .= "<col width='" . ($isIE ? 18 - $cellPaddingWidth * 2 : 18) . "' />";		
		$html .= "\r\n<tr>";
		
		foreach ($tableColumns as $tc) {
			$sortImages = "";
			if (isset($tc['sortByAsc'])) {
				if ($appOrder == $tc['sortByAsc']) {
					//current sort is by this field, ascending.  Display hi-lited arrow
					$sortImages .= "<img class='sortArrowUp' title='Sorted by " . $tc['title'] . " A-Z' src='/img/spacer' style='border:none;background:url(/UserFiles/Image/layout/sortbyarrows_up.gif);background-position:0px 6px;width:11px;height:6px;' />";
				} else {
					//display gray arrow with white arrow mouseover and onclick event
					$sortImages .= "<img class='sortArrowUp' title='Sort by " . $tc['title'] . " A-Z' src='/img/spacer' style='border:none;background:url(/UserFiles/Image/layout/sortbyarrows_up.gif);background-position:0px 0px;width:11px;height:6px;' onmouseover='this.style.backgroundPosition=\"0px 12px\";' onmouseout='this.style.backgroundPosition=\"0px 0px\";' onclick='applySortOrder(\"" . $tc['sortByAsc'] . "\");' />";
				}
				// display sort by ascending arrow
			}
			if (isset($tc['sortByDesc'])) {
				if ($appOrder == $tc['sortByDesc']) {
					//current sort is by this field, ascending.  Display hi-lited arrow
					$sortImages .= "<img class='sortArrowDown' title='Sorted by " . $tc['title'] . " Z-A' src='/img/spacer' style='border:none;background:url(/UserFiles/Image/layout/sortbyarrows_down.gif);background-position:0px 6px;width:11px;height:6px;' />";
				} else {
					//display white arrow
					$sortImages .= "<img class='sortArrowDown' title='Sort by " . $tc['title'] . " Z-A' src='/img/spacer' style='border:none;background:url(/UserFiles/Image/layout/sortbyarrows_down.gif);background-position:0px 0px;width:11px;height:6px;' onmouseover='this.style.backgroundPosition=\"0px 12px\";' onmouseout='this.style.backgroundPosition=\"0px 0px\";' onclick='applySortOrder(\"" . $tc['sortByDesc'] . "\");' />";
				}
				// display sort by descending arrow
			}
			//$html .= "<th>" . $tc['title'] . "<div class='sortArrows'>" . $sortImages . "</div></th>";
			$html .= "<th><div style='position:relative;'>"  . $tc['title'] . $sortImages . "</div></th>";
		}
		$html .= "<th>&nbsp;</th>";
		
		$html .= "</tr>\r\n</table>\r\n";

		$html .= "<div class='adminList_container'>";
		$html .= "<table class='adminList adminList_bottom'>\r\n";
		foreach ($tableColumns as $tc) {
			$html .= "<col style='width:" . ($tc['width'] - $widthFix) . "px;' />";
			//$html .= "<col width='" . ($tc['width'] - $widthFix) . "' />";
		}
		$html .= "\r\n";

		if (count($apps) == 0) {
			$html .= "<tr><td style='font-style: italic;text-align: center;' colspan='" . count($tableColumns) . "'>No Applications Found</td></tr>\r\n";
		}

		foreach ($apps as $app) {
			$bgColor = isset($appStatus[$app['status']]) ? $appStatus[$app['status']] : '';
			$html .= "<tr>";
			foreach ($tableColumns as $tc) {
				$html .=
                    "<td style='background-color: $bgColor;'>"
                    .str_replace(
					array(
						'%%field%%',
						'%%isselected%%',
                        '%%altview%%'
					),
                    array(
						$app[$tc['field']],
						(isset($_REQUEST['application_selector_checkbox']) ? (in_array($app['ID'], $_REQUEST['application_selector_checkbox']) ? 'checked="checked"' : '') : ''),
                        ($app['x_documentAltName'] ? "<a rel='external' href='/viewApplication?id=".$app[$tc['field']]."&alt=1'>ALT</a>&nbsp;\n" : "")
					),
                    $tc['content']) . "</td>";
			}
			$html .= "</tr>\r\n";
		}
		$html .= "</table>\r\n";
		$html .= "</div>";		
		$html .= "<div class='adminList_topbar'>";
// EX delete button (was last option in below list)
// 		<option value='delete'>Delete</option>
		$html .= "
<span class='withselected'>
	With Selected Applications: <select name='applications_withselected' onchange='handleWithSelected(this);'>
		<option value=''>Choose an Action</option>
		<option value='export_to_excel'>Export to Excel</option>
		<option value='set_status_processed'>Change status to Processed</option>
		<option value='set_status_pending'>Change status to Pending</option>
	</select>
</span>&nbsp;
<span class='status'>Filter: <select name='applications_filter' onchange='this.form.action=\"\";this.form.submit();'>
	<option style='background-color: " . $appStatus[''] . ";' value=''" . ($filter == "" ? ' SELECTED' : '') . ">All Applications</option>
	<option style='background-color: " . $appStatus['submitted_pending'] . ";' value=\"status='submitted_pending'\"" . ($filter == "status='submitted_pending'" ? ' SELECTED' : '') . ">Pending</option>
	<option style='background-color: " . $appStatus['processed'] . ";' value=\"status='processed'\"" . ($filter == "status='processed'" ? ' SELECTED' : '') . ">Processed</option>
</select></span>
";
		$html .= "</div>\r\n";
		
		$html .= "<br style='clear:both;' />";

		if (isset($errorMsg) && strlen($errorMsg) > 0) {
			$html .= "<script type='text/javascript'>alert('" . $errorMsg . "');</script>";
		}

		return $html;		
	}
	/*
	public function downloadApplicationXFDF($id = '', $debug = false) {
		if ($id == '') {
			return "there was an error";
		}
		$app = $this->getApplication($id);
		
		$dd = new DOMDocument;
		$dd->loadXML($app['xmlDoc']);
		$xp = new DOMXPath($dd);
		$xp->registerNamespace("xfdf", "http://ns.adobe.com/xfdf/");

		$firstNode = $dd->documentElement;

		$f = $dd->createElement("f");
		$f->setAttribute("href", "http://hsfinancial.ca/forms/" . $app['x_documentName']);
		$firstNode->insertBefore($f, $firstNode->firstChild);
		
		if ($debug) {
			header("content-type: application/xml");
		} else {
			header("content-type: application/vnd.adobe.xfdf");
		}
		
		echo $dd->saveXML();
		die;
	
	}
	*/
	protected function appendAnnotation($xfdf, $msg = 'Hello, World') {
		$rootNode = $xfdf->documentElement;
		$annots = $rootNode->appendChild($xfdf->createElement('annots'));
		$text = $annots->appendChild($xfdf->createElement('text'));
		$text->setAttribute("subject", "sausages");
		$text->setAttribute("icon", "Comment");
		$text->setAttribute("title", "SausaGES");
	
		$contents = $text->appendChild($xfdf->createElement('contents-richtext'));
		
		$body = $contents->appendChild($xfdf->createElement('body'));
		$body->setAttribute("xmlns", "http://www.w3.org/1999/xhtml");
		$body->setAttribute("xmlns:xfa", "http://www.xfa.org/schema/xfa-data/1.0/");
		$body->setAttribute("xfa:APIVersion", "Acrobat:6.0.0");
		$body->setAttribute("xfa:spec", "2.0.2");
		
		$p = $body->appendChild($xfdf->createElement("p"));
		
		$span = $p->appendChild($xfdf->createElement("span"));
		$span->setAttribute("style", "font-size:10.0pt");
		$span->nodeValue = $msg;

		$popup = $text->appendChild($xfdf->createElement('popup'));
		$popup->setAttribute("flags", "noprint,nozoom,norotate");
		$popup->setAttribute("page", "1");
		$popup->setAttribute("rect", "224.334137,520.427856,352.834137,575.427856");
		$popup->setAttribute("open", "no");
	}

	public function downloadFlattenedPDF($id = '', $emailToBroker = false, $use_alt=false) {
	  global $system_vars;
		if ($id == '') {
			return "Could not find the application";
		}
		$app = $this->getApplication($id);
		$templateFile = ($use_alt ? $app['x_documentAltName'] : $app['x_documentName']);
		$xfdfData = new DOMDocument;
		$xfdfData->loadXML($app['xmlDoc']);
		$applicationID = $id;
		// make a copy of the templateFile in the pdfmishmash folder using the applicationID
		copy("./forms/".$templateFile, "../pdfmishmash/".$applicationID."_template.pdf");
		$f = fopen("../pdfmishmash/".$applicationID.".xfdf", "w");
		fwrite($f, $xfdfData->saveXML());
		fclose($f);
		$output = array();
		// flatten the xfdf data into the pdf
		exec("pdftk ../pdfmishmash/".$applicationID."_template.pdf fill_form ../pdfmishmash/".$applicationID.".xfdf output ../pdfmishmash/".$applicationID.".pdf flatten", $output);
		exec("pdftk ../pdftemplates/ThankYouForSubmitting.pdf fill_form ../pdfmishmash/".$applicationID.".xfdf output ../pdfmishmash/".$applicationID.".pdf2 flatten", $output);
		// prepend the pdf cover page
		exec("pdftk ../pdfmishmash/".$applicationID.".pdf2 ../pdfmishmash/".$applicationID.".pdf cat output ../pdfmishmash/".$applicationID."_wcover.pdf", $output);
		if ($emailToBroker) {
			$xp = new DOMXPath($xfdfData);
			$xp->registerNamespace("xfdf", "http://ns.adobe.com/xfdf/");
			$emailToCheck = $xp->query("//xfdf:fields/xfdf:field[@name='email_form_address']/xfdf:value");
			if ($emailToCheck->length == 0) {
			  $emailTo = "service@hsfinancial.ca";
			}
            else {
			  $emailTo = $emailToCheck->item(0)->nodeValue;
			}
//			$emailTo = "martin@classaxe.com";
			$theEmailAddress = $emailTo;
			get_mailsender_to_component_results();
            $data = array(
              'PEmail' => $theEmailAddress,
              'NName' => 'Broker',
              'subject' => $app['x_documentName']." submitted to ".$theEmailAddress,
              'html' => "<a href='".trim($system_vars['URL'],'/')."/viewApplication?id=$id'>View completed application</a>",
			  'text' => "The completed application is available at ".trim($system_vars['URL'],'/')."/viewApplication?id=$id"
			  //'attachmentPath' => "../pdfmishmash/".$applicationID."_wcover.pdf",
			  //'attachmentName' => str_replace(".", "_$applicationID.", $app['x_documentName']),
			  //'attachmentMimeType' => 'application/pdf'
			);
			$wtf = mailto($data);
			//die(print_r($wtf,true));
		}
		$dataStream = file_get_contents("../pdfmishmash/".$applicationID."_wcover.pdf");
		//delete the mishmash files
		unlink("../pdfmishmash/".$applicationID."_template.pdf");
		unlink("../pdfmishmash/".$applicationID.".xfdf");
		unlink("../pdfmishmash/".$applicationID.".pdf");
		unlink("../pdfmishmash/".$applicationID.".pdf2");
		unlink("../pdfmishmash/".$applicationID."_wcover.pdf");
		header("Content-Type: application/pdf");
		//header("Content-Disposition: inline; filename=".$templateFile."_".$applicationID.""); // this doesn't work
		die($dataStream);
	}

	public function downloadXML($id = '') {
		if ($id == '') {
			return "Could not find the application";
		}
		$app = $this->getApplication($id);
		$templateFile = $app['x_documentName'];
		$xfdfData = new DOMDocument;
		$xfdfData->loadXML($app['xmlDoc']);
		$xfdfData->formatOutput = false;
		header("Content-Type: application/xml");
		die($xfdfData->saveXML());
	}
	
	public function prepareCoverPage($id = '') {
		if ($id == '') {
			return "Could not find the application";
		}
		$app = $this->getApplication($id);
		$templateFile = $app['x_documentName'];
		$xfdfData = new DOMDocument;
		$xfdfData->loadXML($app['xmlDoc']);
	}
}

function datestamp2date($datestamp) {
	return mktime(
    (strlen($datestamp)==19 ? substr($datestamp,11,2) : 0),
    (strlen($datestamp)==19 ? substr($datestamp,14,2) : 0),
    (strlen($datestamp)==19 ? substr($datestamp,17,2) : 0),
    substr($datestamp,5,2),
    substr($datestamp,8,2),
    substr($datestamp,0,4)
  );	
}
?>
