<?php 
   //ini_set('display_errors', 1);
   //ini_set('display_startup_errors', 1);
   //error_reporting(E_ALL);

	require_once("../resources/libary/global.php");
	require_once("../resources/libary/currencyFunctions.php");

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
            <label class="container">Get
			     <input class="radioSelect" id="radioSelect" type="radio" value="get">
                <span class="checkmark"></span>
            </label>
            <label class="container">Delete
			    <input class="radioSelect" id="radioSelect" type="radio" name="action" value="delete">
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
             
			<p>To:</p><select name="to"><?php getDataForDropdown($rateCodes); ?></select>
			<input type="number" step="0.01" name="amnt" placeholder="amnt">

            <label class="container">XML
                <input type="radio" name="format" checked value="xml">
                <span class="checkmark"></span>
            </label>
            
            <label class="container">JSON
                <input type="radio" name="format" value="json">
                <span class="checkmark"></span>
            </label>

            
			<input id="but" type="submit" name="submitRequest" value="Submit">

			<textarea id="response" rows="20" cols="50" placeholder="Report things here..."></textarea>
		</form>
	</div>
</body>


</html>
