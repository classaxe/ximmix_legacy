<?php
define('VERSION_SHIPPING','1.0.6');
/*
Version History:
  1.0.6 (2013-10-31)
    1) Achived changelog

  (Older version history in class.shipping.txt)
*/
class Shipping extends Record {

  function get_shipping($method,$dest_xml,$cp) {
    $ship =             new SimpleXMLElement(urldecode($dest_xml));
    $productID_csv =    trim((string)$ship->items);
    $page =             (string)$ship->page;
    if ($productID_csv=='') {
      return array(
        'error' =>  0,
        'method'=>  '(Nothing to ship)',
        'cost'=>    0,
        'taxes'=>   ''
      );
    }
    $productID_arr =    explode(",",$productID_csv);
    $doShip =     false;
    $Obj =      new Product();
    foreach ($productID_arr as $productID) {
      $Obj->_set_ID($productID);
      if (strtolower($Obj->get_field('deliveryMethod'))=='ship'){
        $doShip = true;
        break;
      }
    }
    if (!$doShip) {
      return array(
        'error' =>  0,
        'method'=>  '(Shipping not required)',
        'cost'=>    0,
        'taxes'=>   ''
      );
    }
    switch ($method){
      case 'FEDEX':
        include_once(SYS_SHARED."fedex/rate.php");
        if (!System::has_feature('Fedex')) {
          return array(
            'error' =>  1,
            'method'=>  '<b>Error:</b> Fedex Shipping is not enabled for this site',
            'cost'=>    0,
            'taxes'=>   ''
          );
        }
        $config =
          array(
            'AccountNumber' =>              $cp['ship_FEDEX_AccountNumber'],
            'DropoffType' =>                $cp['ship_FEDEX_DropoffType'],
            'LiveGateway' =>                $cp['ship_FEDEX_LiveGateway'],
            'MeterNumber' =>                $cp['ship_FEDEX_MeterNumber'],
            'PackagingType' =>              $cp['ship_FEDEX_PackagingType'],
            'UserCredential_key' =>         $cp['ship_FEDEX_username'],
            'UserCredential_password' =>    $cp['ship_FEDEX_password'],
            'AAddress1' =>                  $cp['ship_from_AAddress1'],
            'AAddress2' =>                  $cp['ship_from_AAddress2'],
            'ACity' =>                      $cp['ship_from_ACity'],
            'ASpID' =>                      $cp['ship_from_ASpID'],
            'APostal' =>                    $cp['ship_from_APostal'],
            'ACountryID' =>                 $cp['ship_from_ACountryID']
          );
        switch ($config['ACountryID']) {
          case 'CAN':
            switch ((string)$ship->SCountryID) {
              case 'CAN':
                $config['ServiceType'] =    'PRIORITY_OVERNIGHT';
                $serviceCode =              'Priority Overnight';
              break;
              default:
                $config['ServiceType'] =    'INTERNATIONAL_PRIORITY';
                $serviceCode =              'International Priority';
              break;
            }
          break;
        }
        global $page_vars;
        $result = fedex_get_rate($config,$dest_xml);
        // JF modified the next bit to work whether or not there was a RatedShipmentDetails array
        $fedexError = 0;
        if ($result->HighestSeverity == 'FAILURE' || $result->HighestSeverity == 'ERROR') {
        	$fedexError = 1;
        } else {
        	if (!isset($result->RatedShipmentDetails)) {
        		$fedexError = 2;
        	} else {
        		$shipDetails = $result->RatedShipmentDetails;
        		if (is_array($shipDetails)) {
        			$shipDetails = $shipDetails[0];
        		}
        		if (isset($shipDetails->ShipmentRateDetail) &&
        			isset($shipDetails->ShipmentRateDetail->TotalBaseCharge) &&
         			isset($shipDetails->ShipmentRateDetail->TotalBaseCharge->Amount)
         		) {

		          $time = (isset($result->TransitTime) ? $result->TransitTime : 'UNKNOWN');
		          // Only available for Ground shipping
		          switch ($time) {
		            case "ONE_DAY":     $time = 'Next Day';     break;
		            case "TWO_DAYS":    $time = 'Two Days';     break;
		            case "THREE_DAYS":  $time = 'Three Days';   break;
		            case "FOUR_DAYS":   $time = 'Four Days';    break;
		            case "FIVE_DAYS":   $time = 'Five Days';    break;
		            case "SIX_DAYS":    $time = 'Six Days';     break;
		            case "SEVEN_DAYS":  $time = 'Seven Days';   break;
		            case "EIGHT_DAYS":  $time = 'Eight Days';   break;
		            case "NINE_DAYS":   $time = 'Nine Days';    break;
		            default:            $time = '';             break;
		          }
		          $tax_arr =    array();
		          return array(
		            'error' =>  0,
		            'method'=>  $method.' '.$serviceCode.' '.$time,
		            'cost'=>
		              $shipDetails->ShipmentRateDetail->TotalNetCharge->Amount
		              ,
		            'taxes'=>   $tax_arr
		          );
				} else {
					$fedexError = 3;
				}
			}
		}
		if ($fedexError > 0) {
          return array(
            'error' =>  1 ,
            //'method'=> print_r($result, false),
            'method'=>  $method.' '.$serviceCode." (".$fedexError.")<br />".(isset($result->Notifications->Message) ? $result->Notifications->Message : "Rate information unavailable - <br />\nPlease try again later"),
            'cost'=>    '0.00',
            'taxes'=>   array()
          );
        }
      break;
    }
  }
  public function get_version(){
    return VERSION_SHIPPING;
  }
}
?>