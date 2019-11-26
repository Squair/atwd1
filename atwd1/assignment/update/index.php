<?php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);

	require_once("resources/libary/XMLFunctions.php");
	require_once("resources/libary/global.php");
	require_once("resources/libary/currencyFunctions.php");
	require_once("resources/libary/actionResponse.php");
	require_once("resources/libary/config/configReader.php");
	
	if (isset($_GET['action'])){
		$requestType = $_GET['action'];
        $validParameters = array("action", "cur");
		
		//Update should be called first if application never run before
		$timeLastUpdated = getTimeLastUpdated();

		if (currencyNeedsUpdate($timeLastUpdated)){
			if (!updateRatesFile($timeLastUpdated)){
				return;
			}
		}
		
		if (!checkParametersValid($validParameters, $requestType)) return;

		$toCode = $_GET['cur'];
		$currencyJson;
        
		//put an post action
		if ($requestType == "put" || $requestType == "post"){
			$currencyJson = updateSingleCurrency($toCode, $requestType);
			//If reponse wasn't successful, return
			if (!isset($currencyJson)) return;
		}

		//Delete action
		if ($requestType == "del"){
            $currencyJson = "";
			XMLOperation::invoke(function($f) use ($toCode){
					return $f
						->setFilePath("rateCurrencies")
						->addAttributeToElement($f->getParentNodeOfValue("code", $toCode), "live", "0");
			});
		}

		echo getActionResponse($requestType, $toCode, $currencyJson);
	}

	function updateSingleCurrency($toCode, $requestType){
			//If trying to post when code isnt live, return error for not availble
			if (!checkCurrencyCodesLive($toCode) && $requestType != "put"){
				echo getErrorResponse(CURRENCY_NOT_FOUND);
				return; 
			}
		
			$apiConfig = getItemFromConfig("api");
			$unconverted = @file_get_contents($apiConfig->fixer->endpoint . "&symbols=GBP," . $toCode);
		
			if ($unconverted === FALSE){
				echo getErrorResponse(ACTION_ERROR);
				return;
			}
		
			$currencyJson = json_decode(convertBaseRate($unconverted));
		
			//If no rate exists for currency throw rate missing error
			if (!isset($currencyJson->rates->{$toCode})){
				echo getErrorResponse(UNKNOWN_RATE);
				return;
			}
			
			XMLOperation::invoke(function($f) use ($currencyJson, $toCode){
					return $f
						->setFilePath("rateCurrencies")
						->addAttributeToElement($f->getParentNodeOfValue("code", $toCode), "rate", $currencyJson->rates->{$toCode})
						->addAttributeToElement($f->getParentNodeOfValue("code", $toCode), "live", "1");

			});		
		return $currencyJson;
	}
?>