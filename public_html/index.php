<?php
require_once("../resources/libary/XMLFunctions.php");
require_once ("../resources/libary/currencyFunctions.php");

XMLOperation::invoke(function($f){
	$filePath = "../resources/xml/test.xml";
	$xpathQuery = "(/Currencies/Currency[@type='USD'])";
	$newCurrency = createNewCurrency("AFN", "Afghanistan", "???", 0.75);
		return $f
			->setFilePath($filePath)
			->deleteElement($xpathQuery);
});

?>

<html>

<head>
	<link rel="stylesheet" href="css/stylesheet.css">
	<link href="https://fonts.googleapis.com/css?family=Big+Shoulders+Text&display=swap" rel="stylesheet">
</head>

<body>
	<h1>Currency Conversion API</h1>
	<div id="form-container">
		<form>
			<input type="text" name="field1" placeholder="field1">
			<input type="text" name="field2" placeholder="field2">
			<input type="text" name="field3" placeholder="field3">
			<input type="submit" name="submitRequest" value="Submit">

			<textarea rows="4" cols="50" placeholder="Report things here..."></textarea>
		</form>
	</div>
</body>

</html>
