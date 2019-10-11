<?php

	function getErrorResponse($error, $format){
		$response = array(
			'conv' => array(
				'error' => $error['code'],
				'msg' => $error['msg']
			)
		);
		
		$format = checkFormatValueValid($format) ? $format : "xml";
		
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

	function checkFormatValueValid($format){
		return !empty($format) && $format == "xml" && $format == "json";
	}
?>