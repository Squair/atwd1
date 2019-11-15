<?php
	require_once("XMLFunctions.php");
	
	function checkFormatValueValid($format){
		return isset($format) && ($format == "xml" || $format == "json");
	}

	function sendResponse($response, $format){
		if ($format == "json"){
			header('Content-Type: application/json');
			return json_encode($response, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
		} else if ($format == "xml") {
			$convertedResponse = XMLOperation::invoke(function($f) use ($response){
				return $f
					->createXmlFromJson(json_encode($response))
					->dom;
			});
			
			//if request type is put post or delete, append the top level action tag
			if ($_GET['action'] != "get"){
				//Import convertedJson skeleton into action tag
				$actionElement = $convertedResponse->createElement("action");
				$actionElement->setAttribute("type", $_GET['action']);
				$actionElement->appendChild($convertedResponse->documentElement);	
				$convertedResponse->appendChild($actionElement);
			}
			
			return XMLOperation::invoke(function($f) use ($convertedResponse){
				return $f->printElements($convertedResponse);
			});
		}
	}

?>
