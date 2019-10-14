<?php
	require_once("response.php");
	require_once("XMLFunctions.php");
	

	function getActionResponse($type){
		$xmlResponse = new SimpleXMLElement("<action></action>");
		$xmlResponse->addAttribute('type', $type);
		$xmlResponse->addChild('at', time());
		$xmlResponse->addChild('code', $_GET['to']);
		
		$dom = dom_import_simplexml($xmlResponse)->ownerDocument;
		return XMLOperation::invoke(function($f) use ($dom){
			return $f->printElements($dom);
		});		 
	}


?>