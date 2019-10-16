<?php
	require_once("../libary/XMLFunctions.php");
	require_once("../libary/global.php");
	require_once ("../libary/currencyFunctions.php");
	require_once ("../libary/actionResponse.php");
	require_once("../libary/config/configReader.php");
	
	if (isset($_GET['action'])){
		$action = $_GET['action'];
		$toCode = $_GET['to'];
		$currencyJson;
		
		if ($toCode == "GBP"){
			//Return error 2400
			echo getErrorResponse(IMMUTABLE_BASE_CURRENCY, $_GET['format']);
			return;
		}
		
		//put action
		
		//post action
		if ($action == "post"){
			$apiConfig = getItemFromConfig("api");
			$unconverted = file_get_contents($apiConfig->fixer->endpoint . "&symbols=GBP," . $toCode);
			$currencyJson = json_decode(convertBaseRate($unconverted));
			
			//Send response before performing update if valid request
			echo getActionResponse($action, $toCode, $currencyJson);
			
			XMLOperation::invoke(function($f) use ($currencyJson, $toCode){
					return $f
						->setFilePath("rates")
						->updateXmlElement("(/root/rates/" . $toCode . ")[1]", $f->createNewElement($toCode, $currencyJson->rates->{$toCode}));
			});
			return;
		}

		//Delete action
		if ($action == "delete"){
			XMLOperation::invoke(function($f){
					return $f
						->setFilePath("rates")
						->addAttributeToElement($toCode, "unavailable", "true");
			});
		}

		echo getActionResponse($action, $toCode, $currencyJson);
	}
?>
