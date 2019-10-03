<?php
require_once("../resources/libary/XMLFunctions.php");
require_once ("../resources/libary/currencyFunctions.php");

XMLOperation::invoke(function($f){
	$filePath = "../resources/xml/test.xml";
	$xpathQuery = "(/Currencies/Currency[@type='GBP'])";
	$newCurrency = createNewCurrency("GBP", "England", "£", 1.0);
		return $f
			->setFilePath($filePath)
			->writeNewElement($newCurrency)
			->getElements($xpathQuery);
});
?>
