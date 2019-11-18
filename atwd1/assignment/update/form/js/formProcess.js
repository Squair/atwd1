$(document).ready(function () {
	$(".radioSelect").click(function () {
		//Set the 'submit' button text to be whatever type of request is selected
		$('#but').val($('.radioSelect:checked').val());

		if ($('.radioSelect:checked').val() != "get") {
			//disables unneeded fields for non get requests
			$('#format, #amnt').attr("disabled", "disabled");
			$('#format').val("xml");
		} else {
			//reenables if going back to get option
			$('#format, #amnt').removeAttr("disabled");
			$('#format').val("xml");
		}
	});

	$('#paramsForm').submit(function (event) {
		event.preventDefault();

		//Get the checked radio buttton and send request to update.php if not a get request, or get.php if it is.
        let url = $('#radioSelect:checked').val() != "get" ? "../../update/resources/api/update.php" : "../../update/resources/api/get.php";
        
		$.ajax({
			type: "GET",
			data: $(this).serialize(),
			contextType: "text/plain",
			dataType: "text",
			url: url,
			success: function (result) {
				//Set the textarea to the response of the request
				$("#response").html(result);
			}
		});
	});
});
