<?php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);
    
    require_once("update/resources/libary/getResponse.php");
    require_once("update/resources/libary/currencyFunctions.php");
    require_once("update/resources/libary/global.php");
	require_once("update/resources/libary/errorResponse.php");

    //valid parameters for get response
	$validParameters = array("from", "to", "amnt", "format");

    //Get all paramters sent
	$parameters = array_keys($_GET);
	
	//Update should be called first if application never run before
	$timeLastUpdated = getTimeLastUpdated();

    //Check if rates file needs updating, if updateRatesFile didn't succeed an error will be responded from within updateRateFile()
	if (currencyNeedsUpdate($timeLastUpdated)){
		if (!updateRatesFile($timeLastUpdated)){
		  return;
		}
    }
    
    //Set the request type to get to identify the type within checkParametersValid
	$requestType = "get";
    //Validate parameters, if it returns false an error will be responded from within checkParameters valid
	if (!checkParametersValid($validParameters, $requestType)){
		return;
	}

    //Using the verified parameters, build up the get response and print it out
	echo getConversionResponse($_GET['from'], $_GET['to'], $_GET['amnt'], $_GET['format']);



?>
