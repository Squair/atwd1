<?php
require_once("../libary/XMLFunctions.php");
require_once ("../libary/currencyFunctions.php");

echo getConversionResponse($_GET['from'], $_GET['to'], $_GET['amnt'], $_GET['format']);
?>
