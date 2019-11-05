<?php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);

	require_once ("../libary/currencyFunctions.php");
	require_once("../libary/global.php");
	require_once("../libary/errorResponse.php");

	//Set default from curreny to GBP if not passed as parameter
    $_GET['from'] = !isset($_GET['from']) ? "GBP" : $_GET['from'];

	$validParameters = array("from", "to", "amnt", "format");
	$parameters = array_keys($_GET);
	//Check all parameters are present
	if (count(array_diff($validParameters, $parameters)) != 0){
		echo getErrorResponse(MISSING_PARAM);
		return;
		//RETURN missing parameters 1000
	}

	//Check all parameters have a value
	foreach($_GET as $parameter => $value){
		if (empty($value)){
			echo getErrorResponse(MISSING_PARAM);
			return;			
		}
	}

	//Check all parameters match $validParameters and dosent have duplicate parameters
	if (count(array_diff($parameters, $validParameters)) != 0 || urlHasDuplicateParameters($_SERVER['QUERY_STRING'])){				
		echo getErrorResponse(UNKOWN_PARAM);
		return;
		//Return invalid parameter code 1100
	}

	//Check currency codes exist and are availible
	if (!checkCurrencyCodesExists($_GET['to'], $_GET['from']) || checkCurrencyCodesUnavailable($_GET['to'], $_GET['from'])){
			echo getErrorResponse(UNKOWN_CURRENCY);
			return;		
	}



	//Check if amount submitted is decimal (short circuit for non numerical values)
	if (!is_numeric($_GET['amnt']) || !is_float((float)$_GET['amnt'])){
		echo getErrorResponse(CURRENCY_NOT_DECIMAL);
		return;
	}

	//Check format is valid
	if (!checkFormatValueValid($_GET['format'])){
		echo getErrorResponse(INCORRECT_FORMAT);
		return;
		//Return format must be xml/json 1400

	}

	if (currencyNeedsUpdate()){
		updateRatesFile();
	}
	
	echo getConversionResponse($_GET['from'], $_GET['to'], $_GET['amnt'], $_GET['format']);



?>
