<?php
	require_once("response.php");

	function getErrorResponse($error, $format){
		$response = array(
			'conv' => array(
				'error' => $error['code'],
				'msg' => $error['msg']
			)
		);
		
		$format = checkFormatValueValid($format) ? $format : "xml";
		return sendResponse($response, $format);
	}

	function urlHasDuplicateParameters($queryString){
		$parts = explode('&', $queryString);
		$parameters = array();
		
		foreach($parts as $part){
			$key = substr($part, 0, strpos($part, '='));
			if (in_array($key, $parameters)){
				return true;
			} else {
				array_push($parameters, $key);
			}
		}
		return false;
	}


?>