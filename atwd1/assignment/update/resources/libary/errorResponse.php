<?php
	require_once("response.php");
	require_once("fileHandler.php");

	//Creates skeleton for error response message ready to be passed back to the user
	function getErrorResponse($error){
		//Create skeleton for all responses as json.
		$response = array(
			'conv' => array(
				'error' => $error['code'],
				'msg' => $error['msg']
			)
		);
		
		//If format is set, validate it otherwise default to xml or if not set, set to xml
		if (isset($_GET['format'])){
			$format = checkFormatValueValid($_GET['format']) ? $_GET['format'] : "xml";
		} else {
			$format = "xml";
		}
		return sendResponse($response, $format);
	}

	//Deconstructs the url parameters to check the same parameters hasnt been entered twice, which would overwrite the first instance
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

	//Runs multiple types of checks on parameters dependant on the type of request to evaluate if an error needs to be sent back to the user
	function checkParametersValid($validParameters, $requestType) {
		$parameters = array_keys($_GET);
		//Checks each $_GET parameter has an associating value, and returns the corresponding error code depending on which doesn't have a value. Or if parameter is action, ensures its a valid one.
		$validActionParameters = array("put", "post", "delete");
			
		foreach($_GET as $parameter => $value){
			if (empty($value)){
				if ($requestType == "get"){
					echo getErrorResponse(MISSING_PARAM);
				} else { //If not get request, return error depending on which parameter is missing
					echo $parameter == "action" ? getErrorResponse(UNKOWN_ACTION) : getErrorResponse(MISSING_CURRENCY);
				}
				return false;			
			}
			//If update action and not put, post or delete, return error 2000
			if ($requestType != "get" && $parameter == "action" && !in_array($value, $validActionParameters)){
				echo getErrorResponse(UNKOWN_ACTION);
				return false;
			}
		}
		
		//Checks if the supplied $_GET parameters match the number of validParameters passed in
		if (count(array_diff($validParameters, $parameters)) != 0){
			//RETURN error codes 1000 or 2000
			echo $requestType == "get" ? getErrorResponse(MISSING_PARAM) : getErrorResponse(UNKOWN_ACTION);
			return false;
		} 
		
		//Checks the reverse of the above to catch garbage parameters and also checks there are no duplicate parameters
		if (count(array_diff($parameters, $validParameters)) != 0 || urlHasDuplicateParameters($_SERVER['QUERY_STRING'])){
			echo $requestType == "get" ? getErrorResponse(UNKOWN_PARAM) : getErrorResponse(UNKOWN_ACTION);
			return false;
			//Return invalid parameter code 1100 or 2000
		}
		
		//At this point we can be sure all parameters exist and have a value, so we can set $codes
		$codes = array();
		$requestType == "get" ? array_push($codes, $_GET['to'], $_GET['from']) : array_push($codes, $_GET['to']); 
		
		//Check currency codes exist and are availible when request type is not set to put
		if ($requestType != "put" && (!checkCurrencyCodesExists($codes) || !checkCurrencyCodesLive($codes))){
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
			
			//Check format is valid, if not return error as xml
			if (!checkFormatValueValid($_GET['format'])){
				echo getErrorResponse(INCORRECT_FORMAT);
				return false;
				//Return format must be xml/json 1400
			}
		} else if ($requestType != "get"){ //Action specific errors
			if ($_GET['to'] == "GBP"){
				//Return error 2400
				echo getErrorResponse(IMMUTABLE_BASE_CURRENCY);
				return;
			}
		}
		
		return true;
	}
?>