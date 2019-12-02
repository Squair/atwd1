<?php
	require_once("XMLFunctions.php");
	
    //Validates the format parameters, checking its both set and either equal to 'xml' or 'json'
	function checkFormatValueValid($format){
		return isset($format) && ($format == "xml" || $format == "json");
	}

    //Takes in the response to be sent back and the format and prints according to the format
	function sendResponse($response, $format){
		if ($format == "json"){
			header('Content-Type: application/json');
			return json_encode($response, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
		} else if ($format == "xml") {
            //Converted the built up json response to xml if the format is requested
			$convertedResponse = XMLOperation::invoke(function($f) use ($response){
				return $f
					->createXmlFromJson(json_encode($response))
					->dom;
			});
			
			//if request type is put post or delete, append the top level action tag in the response
			$action = isset($_GET['action']) ? $_GET['action'] : "get";
			if ($action != "get"){
				//Import convertedJson skeleton into action tag
				$actionElement = $convertedResponse->createElement("action");
				$actionElement->setAttribute("type", $_GET['action']);
				$actionElement->appendChild($convertedResponse->documentElement);	
				$convertedResponse->appendChild($actionElement);
			}
			//Return finalised response
			return XMLOperation::invoke(function($f) use ($convertedResponse){
				return $f->printElements($convertedResponse);
			});
		}
	}

?>
