<?php 
	require_once("../resources/libary/global.php");
	require_once("../resources/libary/currencyFunctions.php");

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

	$rateCodes = getAllRateCodes();
?>
<html>

<head>
	<link rel="stylesheet" href="css/stylesheet.css">
	<link href="https://fonts.googleapis.com/css?family=Roboto+Mono&display=swap" rel="stylesheet">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
	<script src="js/formProcess.js"></script>

</head>

<body>
	<h1>Currency Conversion API</h1>
	<div id="form-container">
		<form id="paramsForm" action="#">
			<input id="radioSelect" type="radio" name="requestType" checked value="get.php"> GET <br>
			<input id="radioSelect" type="radio" name="requestType" value="delete.php"> DELETE <br>
			<input id="radioSelect" type="radio" name="requestType" value="post.php"> POST <br>
			<input id="radioSelect" type="radio" name="requestType" value="update.php"> UPDATE <br>

			<p>From:</p><select name="from"><?php getDataForDropdown($rateCodes); ?></select>
			<p>To:</p><select name="to"><?php getDataForDropdown($rateCodes); ?></select>

			<input type="text" name="amnt" placeholder="amnt">
			<input type="text" name="format" placeholder="format">

			<input id="but" type="submit" name="submitRequest" value="Submit">

			<textarea id="response" rows="20" cols="50" placeholder="Report things here..."></textarea>
		</form>
	</div>
</body>


</html>
