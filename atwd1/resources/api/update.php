<?php
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);

	require_once("../libary/XMLFunctions.php");
	require_once("../libary/global.php");
	require_once ("../libary/currencyFunctions.php");
	require_once ("../libary/actionResponse.php");
	require_once("../libary/config/configReader.php");
	
	if (isset($_GET['action'])){
		$requestType = $_GET['action'];
        $validParameters = array("action", "to");
		
		if (!checkParametersValid($validParameters, $requestType)){
			return;
		}
		$toCode = $_GET['to'];
		$currencyJson;
        

        		
		//put action
		if ($requestType == "put"){
			$currencyJson = updateSingleCurrency($toCode, $requestType);
		}
		
		//post action
		if ($requestType == "post"){
			updateSingleCurrency($toCode, $requestType);
			return;
		}

		//Delete action
		if ($requestType == "delete"){
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
			if (!checkCurrencyCodesLive($toCode) && $requestType != "put"){
				echo getErrorResponse(CURRENCY_NOT_FOUND);
				return; 
			}
		
			$apiConfig = getItemFromConfig("api");
			$unconverted = file_get_contents($apiConfig->fixer->endpoint . "&symbols=GBP," . $toCode);
			$currencyJson = json_decode(convertBaseRate($unconverted));
			
			//Send response before performing update to get old data on post
			if ($requestType == "post"){
				echo getActionResponse($requestType, $toCode, $currencyJson);				
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
