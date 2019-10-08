<?php ?>
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
					type: "GET",
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
			<input id="radioSelect" type="radio" name="requestType" checked value="get.php"> GET <br>
			<input id="radioSelect" type="radio" name="requestType" value="delete.php"> DELETE <br>
			<input id="radioSelect" type="radio" name="requestType" value="post.php"> POST <br>
			<input id="radioSelect" type="radio" name="requestType" value="update.php"> UPDATE <br>

			<input type="text" name="xpath" placeholder="xpath query">

			<input type="text" name="to" placeholder="to">
			<input type="text" name="from" placeholder="from">
			<input type="text" name="amnt" placeholder="amnt">
			<input type="text" name="format" placeholder="format">

			<input id="but" type="submit" name="submitRequest" value="Submit">

			<textarea id="response" rows="20" cols="50" placeholder="Report things here..."></textarea>
		</form>
	</div>
</body>


</html>
