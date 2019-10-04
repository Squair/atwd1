<?php
require_once("../libary/XMLFunctions.php");
require_once ("../libary/currencyFunctions.php");

XMLOperation::invoke(function($f){
	$filePath = "../xml/test.xml";
	$newCurrency = createNewCurrency($_POST['type'], $_POST['country'], $_POST['symbol'], $_POST['rate']);
		return $f
			->setFilePath($filePath)
			->writeNewElement($newCurrency);
			
});
?>
