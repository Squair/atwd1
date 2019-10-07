<?php
    $key = "97341f9a29a6e2c9e44153ef98fb52bb";
    $array = json_decode(file_get_contents("http://data.fixer.io/api/latest?access_key=" . $key . "&format=1"));

    foreach ($array as $key => $jsons){
        foreach ($jsons as $key => $value){
            echo $key . " -> " . $value . "<br>";
        }
    }
    print_r($json);
?>

<html>

<head>
	<link rel="stylesheet" href="css/stylesheet.css">
	<link href="https://fonts.googleapis.com/css?family=Roboto+Mono&display=swap" rel="stylesheet">
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>

	<script>
		$(document).ready(function() {
			$('#paramsForm').submit(function(event) {
				event.preventDefault();
				$.ajax({
					type: "POST",
					data: $(this).serialize(),
					contextType: "text/plain",
					dataType: "text",
					url: "../resources/api/" + $('#radioSelect:checked').val(),
					success: function(result) {
						//alert(result);
						$("#response").html(result);
					}
				});
			});
		});

	</script>

</head>

<body>
	<h1>Currency Conversion API</h1>
	<div id="form-container">
		<form id="paramsForm" action="#">
			<input id="radioSelect" type="radio" name="requestType" value="get.php"> GET <br>
			<input id="radioSelect" type="radio" name="requestType" value="delete.php"> DELETE <br>
			<input id="radioSelect" type="radio" name="requestType" value="post.php"> POST <br>
			<input id="radioSelect" type="radio" name="requestType" value="update.php"> UPDATE <br>

			<input type="text" name="xpath" placeholder="xpath query">

			<input type="text" name="country" placeholder="country">
			<input type="text" name="symbol" placeholder="symbol">
			<input type="text" name="type" placeholder="type">
			<input type="text" name="rate" placeholder="rate">

			<input id="but" type="submit" name="submitRequest" value="Submit">

			<textarea id="response" rows="20" cols="50" placeholder="Report things here..."></textarea>
		</form>
	</div>
</body>


</html>
