<?php
require_once("../libary/XMLFunctions.php");
require_once ("../libary/currencyFunctions.php");

XMLOperation::invoke(function($f){
	$filePath = "../xml/test.xml";
	$xpathQuery = $_POST['xpath'];
	$newCurrency = createNewCurrency("GBP", "England", "£", 1.0);
		return $f
			->setFilePath($filePath)
			->getElements($xpathQuery);
			
});
?>
