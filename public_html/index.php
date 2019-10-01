<?php
require_once("../resources/libary/XMLFunctions.php");

CurrencyWriter::write(function($f){
	return $f
		->setFilePath("../resources/xml/test.xml")
		->replaceXmlElement("(//Currency[@type='GBP'])[1]", createNewCurrency("GBP", "England", "£", 1.1));
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
