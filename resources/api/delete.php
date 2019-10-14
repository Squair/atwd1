<?php
	require_once("../libary/XMLFunctions.php");
	require_once("../libary/global.php");
	require_once ("../libary/currencyFunctions.php");
	require_once ("../libary/actionResponse.php");


	if ($_GET['to'] == "GBP"){
		//Return error 2400
	}

	XMLOperation::invoke(function($f){
			return $f
				->setFilePath("rates")
				->addAttributeToElement($_GET['to'], "unavailable", "true");
	});
	echo getActionResponse($_GET['requestType']);
?>
