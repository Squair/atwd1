<?php
	require_once ("../libary/currencyFunctions.php");
	require_once("../libary/global.php");
	require_once("../libary/errorResponse.php");

	if (currencyNeedsUpdate()){
		$apiConfig = getItemFromConfig("api");
		$currencyJson = file_get_contents($apiConfig->fixer->endpoint);
		XMLOperation::invoke(function($f) use ($currencyJson){
			return $f
				->setFilePath("rates")
				->createXmlFromJson(convertBaseRate($currencyJson));
		});
	}

	$validParameters = array("from", "to", "amnt", "format", "requestType");
	$parameters = array_keys($_GET);
	
	//Check all parameters are present
	if (count(array_diff($validParameters, $parameters)) > 0){
		echo getErrorResponse(MISSING_PARAM, $_GET['format']);
		return;
		//RETURN missing parameters 1000
	}

	//Check all parameters have a value
	foreach($_GET as $parameter => $value){
		if (empty($value)){
			echo getErrorResponse(MISSING_PARAM, $_GET['format']);
			return;			
		}
	}

	//Check all parameters match $validParameters
	if (count(array_diff($parameters, $validParameters)) > 0){				
		echo getErrorResponse(UNKOWN_PARAM, $_GET['format']);
		return;
		//Return invalid parameter code 1100
	}

	//Check format is valid
	if (!checkFormatValueValid($_GET['format'])){
		echo getErrorResponse(INCORRECT_FORMAT, $_GET['format']);
		return;
		//Return format must be xml/json 1400

	}

	echo getConversionResponse($_GET['from'], $_GET['to'], $_GET['amnt'], $_GET['format']);



?>
