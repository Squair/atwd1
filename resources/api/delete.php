<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

require_once("../libary/XMLFunctions.php");
require_once("../libary/global.php");
require_once ("../libary/currencyFunctions.php");

XMLOperation::invoke(function($f){
		return $f
			->setFilePath("rates")
			->addAttributeToElement($_GET['to'], "unavailible", "true");
});
?>
