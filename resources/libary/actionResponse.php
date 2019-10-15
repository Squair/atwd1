<?php
	require_once("response.php");
	require_once("currencyFunctions.php");
	require_once("XMLFunctions.php");
	

	function getActionResponse($type){
		$xmlResponse = new SimpleXMLElement("<action></action>");
		$xmlResponse->addAttribute('type', $type);
		$xmlResponse->addChild('at', time());
		//rate for post and put
		//old rate for post
		
		
		if ($type="delete"){
			$xmlResponse->addChild('code', $_GET['to']);
		} else {
			$xmlResponse->addChild(createCurrencyInfo($_GET['to']));
		}
		
		$dom = dom_import_simplexml($xmlResponse)->ownerDocument;
		return XMLOperation::invoke(function($f) use ($dom){
			return $f->printElements($dom);
		});		 
	}

	function createCurrencyInfo($currCode){
		$currencyInfo = getCurrencyData($currCode);
		
		$xmlCurrencyInfo = new SimpleXMLElement("<curr></curr>");
		$xmlCurrencyInfo->addChild('code', $currCode);
		$xmlCurrencyInfo->addChild('name', $currencyInfo['curr']);
		$xmlCurrencyInfo->addChild('loc', $currencyInfo['loc']);
		
		return $xmlCurrencyInfo;
	}

?>