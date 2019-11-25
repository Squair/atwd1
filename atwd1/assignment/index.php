<?php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);

   require_once ("update/resources/libary/currencyFunctions.php");
    require_once("update/resources/libary/global.php");
	require_once("update/resources/libary/errorResponse.php");

	////Set default from curreny to GBP if not passed as parameter
   //$_GET['from'] = !isset($_GET['from']) ? "GBP" : $_GET['from'];

	$validParameters = array("from", "to", "amnt", "format");
	$parameters = array_keys($_GET);
	
	//Update should be called first if application never run before
	$timeLastUpdated = getTimeLastUpdated();

	if (currencyNeedsUpdate($timeLastUpdated)){
		if (!updateRatesFile($timeLastUpdated)){
		  return;
		}
    }

	$requestType = "get";
	////Run validation
	if (!checkParametersValid($validParameters, $requestType)){
		return;
	}
	
	echo getConversionResponse($_GET['from'], $_GET['to'], $_GET['amnt'], $_GET['format']);



?>
