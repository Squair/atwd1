<?php
	require_once("XMLFunctions.php");
	
	function checkFormatValueValid($format){
		return !empty($format) && ($format == "xml" || $format == "json");
	}

	function sendResponse($response, $format){
		if ($format == "json"){
			header('Content-Type: application/json');
			return json_encode($response, JSON_PRETTY_PRINT);
		} else if ($format == "xml") {
			return XMLOperation::invoke(function($f) use ($response){
				return $f
					->createXmlFromJson(json_encode($response))
					->printElements($f->dom);
			});
		}
	}

?>