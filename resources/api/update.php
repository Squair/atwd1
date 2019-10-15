<?php
	require_once("../libary/XMLFunctions.php");
	require_once("../libary/global.php");
	require_once ("../libary/currencyFunctions.php");
	require_once ("../libary/actionResponse.php");
	
	if (isset($_GET['action'])){
		$action = $_GET['action'];
		
		if ($_GET['to'] == "GBP"){
			//Return error 2400
			echo getErrorResponse(IMMUTABLE_BASE_CURRENCY, $_GET['format']);
			return;
		}

		//Delete action
		if ($action == "delete"){
			XMLOperation::invoke(function($f){
					return $f
						->setFilePath("rates")
						->addAttributeToElement($_GET['to'], "unavailable", "true");
			});
		}

		echo getActionResponse($action);
	}
?>
