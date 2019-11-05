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
        $action = $_GET['action'];
		$toCode = $_GET['to'];
		$currencyJson;
        
        $validActions = array("put", "post", "delete");
        
        if (!in_array($action, $validActions)){
            echo getErrorResponse(UNKOWN_ACTION, $_GET['format']);
            return;
        }
        		
		if ($toCode == "GBP"){
			//Return error 2400
			echo getErrorResponse(IMMUTABLE_BASE_CURRENCY, $_GET['format']);
			return;
		}
		
		//put action
		if ($action == "put"){
			$currencyJson = updateSingleCurrency($toCode, $action);
		}
		
		//post action
		if ($action == "post"){
			updateSingleCurrency($toCode, $action);
			return;
		}

		//Delete action
		if ($action == "delete"){
            $currencyJson = "";
			XMLOperation::invoke(function($f) use ($toCode){
					return $f
						->setFilePath("rates")
						->addAttributeToElement($toCode, "unavailable", "true");
			});
		}

		echo getActionResponse($action, $toCode, $currencyJson);
	}

	function updateSingleCurrency($toCode, $action){
			if (checkCurrencyCodesUnavailable($toCode) && $action != "put"){
				echo getErrorResponse(CURRENCY_NOT_FOUND);
				return; 
			}
		
			$apiConfig = getItemFromConfig("api");
			$unconverted = file_get_contents($apiConfig->fixer->endpoint . "&symbols=GBP," . $toCode);
			$currencyJson = json_decode(convertBaseRate($unconverted));
			
			//Send response before performing update to get old data on post
			if ($action == "post"){
				echo getActionResponse($action, $toCode, $currencyJson);				
			}
			
			XMLOperation::invoke(function($f) use ($currencyJson, $toCode){
					return $f
						->setFilePath("rates")
						->updateXmlElement("(/root/rates/" . $toCode . ")[1]", $f->createNewElement($toCode, $currencyJson->rates->{$toCode}));
			});		
		return $currencyJson;
	}
?>
