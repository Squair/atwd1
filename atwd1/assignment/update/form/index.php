<?php 
   ini_set('display_errors', 1);
   ini_set('display_startup_errors', 1);
   error_reporting(E_ALL);

	require_once("../resources/libary/global.php");
	require_once("../resources/libary/currencyFunctions.php");

	//Get all currency codes from currencies.xml to populate the dropdown
	$rateCodes = getAllCurrencyCodes();
?>
<html>

<head>
	<link rel="stylesheet" href="css/stylesheet.css">
	<script src="js/JQuery.js"></script>
	<script src="js/formProcess.js"></script>
</head>

<body>
	<h1>Currency Conversion API</h1>
	<div id="form-container">
		<label class="container">Get
			<input class="radioSelect" id="radioSelect" type="radio" name="action" value="get">
			<span class="checkmark"></span>
		</label>
		<label class="container">Delete
			<input class="radioSelect" id="radioSelect" type="radio" name="action" value="del">
			<span class="checkmark"></span>
		</label>
		<label class="container">Post
			<input class="radioSelect" id="radioSelect" type="radio" name="action" value="post">
			<span class="checkmark"></span>
		</label>
		<label class="container">Put
			<input class="radioSelect" id="radioSelect" type="radio" name="action" value="put">
			<span class="checkmark"></span>
		</label>
		<form id="paramsForm" action="#">
			<p>From:</p><select id='from' name="from"><?php getDataForDropdown($rateCodes); ?></select>
			<p>To:</p><select id='to' name="to"><?php getDataForDropdown($rateCodes); ?></select>

			<input id="amnt" type="number" step="0.01" name="amnt" placeholder="amnt">

			<p>Format:</p><select id="format" name="format">
				<option value="xml">XML</option>
				<option value="json">JSON</option>
			</select>

			<input id="but" type="submit" name="submitRequest" value="Submit">

			<textarea id="response" rows="20" cols="50" placeholder="Report things here..."></textarea>
		</form>
	</div>
</body>

</html>
