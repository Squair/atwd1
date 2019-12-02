<?php
	require_once("currencyFunctions.php");
	require_once("XMLFunctions.php");
	require_once("config/configReader.php");
    
    //Builds up the response returned from a post, put or delete request
	function getActionResponse($type, $toCode, $currencyJson){

		$xmlResponse = new SimpleXMLElement("<action></action>");
		$xmlResponse->addAttribute('type', $type);
		$xmlResponse->addChild('at', gmdate("d F Y H:i", time()));
		
        //Get the data for the currency code from the rateCurrencies file
		$currencyData = getRateCurrency($toCode);
		
        //rate is shared tag within put and post requests
		if ($type == "post" || $type == "put"){
			$xmlResponse->addChild('rate', $currencyJson->rates->{$toCode});
		}
		
        //Retrive previous rate for code to be updated and add to response, if no previous rate listed it will just be an empty tag
        if ($type == "post"){
			$xmlResponse->addChild('old_rate', getOldRate($toCode));
		}

        //Del action doesn't have nested curr info, but create it for post and put
		if ($type == "del"){ 
			$xmlResponse->addChild('code', $toCode);
		} else {
			$dom = dom_import_simplexml($xmlResponse);
			$dom->appendChild($dom->ownerDocument->importNode(createCurrencyInfo($currencyData), true));
		}
		
        //Import simpleXml into DOM to be passed to XMLoperation to be printed
		if (!isset($dom)){ 
            $dom = dom_import_simplexml($xmlResponse); 
        }
        return XMLOperation::invoke(function($f) use ($dom){
            return $f->printElements($dom->ownerDocument);
		});		 
	}

    //Create structure for <curr> within post and put responses, return as DOM to be appended to the response being built in getActionResponse() 
	function createCurrencyInfo($currencyData){		
		$xmlCurrencyInfo = new SimpleXMLElement("<curr></curr>");
		$xmlCurrencyInfo->addChild('code', (string) $currencyData->code);
		$xmlCurrencyInfo->addChild('name', (string) $currencyData->curr);
		$xmlCurrencyInfo->addChild('loc', (string) $currencyData->loc);
		
		return dom_import_simplexml($xmlCurrencyInfo);
	}

?>
