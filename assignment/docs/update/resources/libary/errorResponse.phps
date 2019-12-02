<?php
	require_once("response.php");
	require_once("fileHandler.php");
    require_once("currencyFunctions.php");

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

	//Deconstructs the url parameters to check the same parameters hasnt been entered twice, has to use the SERVER['QUERY_STRING'] as GET will overwrite duplicate parameters
	function urlHasDuplicateParameters($queryString){
        //Segment the query string by breaking up each parameter into key value pairs delimited by '&'
		$parts = explode('&', $queryString);
		$parameters = array();
		
        //Loop through each parameter and push into an array, checking if its already been added
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
		//If no format supplied on get request, default to xml
		if ($requestType == "get" && !isset($_GET['format'])){
			$_GET['format'] = "xml";
		}
        
		$parameters = array_keys($_GET);
        
        //Valid values for the action parameter used when making put, post or delete requests
		$validActionParameters = array("put", "post", "del");
		
		//Checks each $_GET parameter has an associating value, and returns the corresponding error code depending on which doesn't have a value. Or if parameter is action, ensures its a valid one.
		foreach($_GET as $parameter => $value){
			if (empty($value)){
				if ($requestType == "get"){
                    //Print error code 1000
					echo getErrorResponse(MISSING_PARAM);
				} else { //If its not a get request, return error if action is empty, otherwise it'll be the missing currency error
					echo $parameter == "action" ? getErrorResponse(UNKOWN_ACTION) : getErrorResponse(MISSING_CURRENCY);
				}
                //Return false if any parameter is empty
				return false;			
			}
            
			//If not a get request and action parameter value is not put, post or delete, return error 2000
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
		
		//Checks the reverse of the above to catch extra garbage parameters and also checks there are no duplicate valid parameters
		if (count(array_diff($parameters, $validParameters)) != 0 || urlHasDuplicateParameters($_SERVER['QUERY_STRING'])){
			echo $requestType == "get" ? getErrorResponse(UNKOWN_PARAM) : getErrorResponse(UNKOWN_ACTION);
			return false;
			//Return invalid parameter code 1100 or 2000
		}
		
		//At this point we can be sure all parameters exist and have a value, so we can set $codes array
		$codes = array();
        //Push codes in depending on the requestType
		$requestType == "get" ? array_push($codes, $_GET['to'], $_GET['from']) : array_push($codes, $_GET['cur']); 
		
        //Check all codes exist within the rateCurrencies file
        if (!checkCurrencyCodesExists($codes)){
            echo $requestType == "get" ? getErrorResponse(UNKOWN_CURRENCY) : getErrorResponse(CURRENCY_NOT_FOUND);
            return false;
        }
        
		//Check currency codes are live if not making a put request
		if ($requestType != "put" && !checkCurrencyCodesLive($codes)){
			echo $requestType == "get" ? getErrorResponse(UNKOWN_CURRENCY) : getErrorResponse(CURRENCY_NOT_FOUND);
			return false;	
		}
		
		//Get request specific errors
		if ($requestType == "get"){
			//Check if amount parameter submitted is decimal
            $amount = $_GET['amnt'];
			if (!is_numeric($amount)){
                    echo getErrorResponse(CURRENCY_NOT_DECIMAL);
				    return false;
			}
            //Cast parameter to float, after checking is_numeric and check its a float
            $convAmount = (float) $amount;
            if (!is_float($convAmount)){        
                    echo getErrorResponse(CURRENCY_NOT_DECIMAL);
				    return false;
            }
			
			//Check format parameter is valid, if not return error as xml
			if (!checkFormatValueValid($_GET['format'])){
				echo getErrorResponse(INCORRECT_FORMAT);
				return false;
				//Return format must be xml/json 1400
			}
        
        //If making a put, post or delete request and targetting the base currency, return error 2400
		} else if ($requestType != "get"){ //Action specific errors
			if ($_GET['cur'] == getBaseRate()){
				echo getErrorResponse(IMMUTABLE_BASE_CURRENCY);
				return;
			}
		}
		
		return true;
	}
?>
