<?php
	require_once ("../libary/currencyFunctions.php");

	if (currencyNeedsUpdate()){
		$apiConfig = getItemFromConfig("api");
		$currencyJson = file_get_contents($apiConfig->fixer->endpoint);
		XMLOperation::invoke(function($f) use ($currencyJson){
			return $f
				->setFilePath("rates")
				->createXmlFromJson(convertBaseRate($currencyJson));
		});
	}

	echo getConversionResponse($_GET['from'], $_GET['to'], $_GET['amnt'], $_GET['format']);

?>
