<?php
require_once("../libary/XMLFunctions.php");
require_once ("../libary/currencyFunctions.php");

XMLOperation::invoke(function($f){
	$filePath = "../xml/test.xml";
	$xpathQuery = $_POST['xpath'];
		return $f
			->setFilePath($filePath)
			->getElements($xpathQuery);
			
});
?>
