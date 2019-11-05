<?php
	require_once("response.php");

	function getErrorResponse($error){
		$response = array(
			'conv' => array(
				'error' => $error['code'],
				'msg' => $error['msg']
			)
		);
		
		$format = checkFormatValueValid($_GET['format']) ? $_GET['format'] : "xml";
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

		//Check all parameters are present
	function checkParametersValid($validParameters, $requestType) {
		$parameters = array_keys($_GET);
		$codes = array();
		$requestType == "get" ? array_push($codes, $_GET['to'], $_GET['from']) : array_push($codes, $_GET['to']); 
		if (count(array_diff($validParameters, $parameters)) != 0){
			echo $requestType == "get" ? getErrorResponse(MISSING_PARAM) : getErrorResponse(UNKOWN_ACTION);
			return false;
			//RETURN missing parameters 1000 & 2000
		} 
		
		foreach($_GET as $parameter => $value){
			if (empty($value)){
				echo $requestType == "get" ? getErrorResponse(MISSING_PARAM) : getErrorResponse(UNKOWN_ACTION);
				return false;			
			}
		}
		
		//Check all parameters match $validParameters and dosent have duplicate parameters
		if (count(array_diff($parameters, $validParameters)) != 0 || urlHasDuplicateParameters($_SERVER['QUERY_STRING'])){				
			echo $requestType == "get" ? getErrorResponse(UNKOWN_PARAM) : getErrorResponse(UNKOWN_ACTION);
			return false;
			//Return invalid parameter code 1100 or 2000
		}
		
		//Check currency codes exist and are availible when request type not put
		if ($requestType != "put" && (!checkCurrencyCodesExists($codes) || checkCurrencyCodesUnavailable($codes))){
			echo $requestType == "get" ? getErrorResponse(UNKOWN_CURRENCY) : getErrorResponse(CURRENCY_NOT_FOUND);
			return false;	
		}
		
		//Get request specific errors
		if ($requestType == "get"){
			//Check if amount submitted is decimal (short circuit for non numerical values)
			if (!is_numeric($_GET['amnt']) || !is_float((float)$_GET['amnt'])){
				echo getErrorResponse(CURRENCY_NOT_DECIMAL);
				return false;
			}
			
			//Check format is valid
			if (!checkFormatValueValid($_GET['format'])){
				echo getErrorResponse(INCORRECT_FORMAT);
				return false;
				//Return format must be xml/json 1400
			}
		} else if ($requestType != "get"){ //Action specific errors
			if ($_GET['to'] == "GBP"){
				//Return error 2400
				echo getErrorResponse(IMMUTABLE_BASE_CURRENCY, $_GET['format']);
				return;
			}
		}
		
		return true;
	}
?>
