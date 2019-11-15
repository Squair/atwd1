<?php
	require_once("response.php");
	require_once("currencyFunctions.php");
	require_once("XMLFunctions.php");
	require_once("config/configReader.php");

	function getActionResponse($type, $toCode, $currencyJson){

		$xmlResponse = new SimpleXMLElement("<action></action>");
		$xmlResponse->addAttribute('type', $type);
		$xmlResponse->addChild('at', gmdate("d F Y H:i", time()));
		
		$currencyData = getRateCurrency($toCode);
		
		if ($type == "post" || $type == "put"){
			$xmlResponse->addChild('rate', $currencyJson->rates->{$toCode});
		}
		
		//THe order of sending request will be resolved when rates files are timestamped
		if ($type == "post"){
			$xmlResponse->addChild('old_rate', getOldRate($toCode));
		}

		if ($type == "delete"){
			$xmlResponse->addChild('code', $toCode);
		} else {
			$dom = dom_import_simplexml($xmlResponse);
			$dom->appendChild($dom->ownerDocument->importNode(createCurrencyInfo($currencyData), true));
		}
		
		if (!isset($dom)){ 
            $dom = dom_import_simplexml($xmlResponse); 
        }
        return XMLOperation::invoke(function($f) use ($dom){
            return $f->printElements($dom->ownerDocument);
		});		 
	}

	function createCurrencyInfo($currencyData){		
		$xmlCurrencyInfo = new SimpleXMLElement("<curr></curr>");
		$xmlCurrencyInfo->addChild('code', (string) $currencyData->code);
		$xmlCurrencyInfo->addChild('name', (string) $currencyData->curr);
		$xmlCurrencyInfo->addChild('loc', (string) $currencyData->loc);
		
		return dom_import_simplexml($xmlCurrencyInfo);
	}

?>
