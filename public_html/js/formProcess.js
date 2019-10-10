
	$(document).ready(function () {
		$('#paramsForm').submit(function (event) {
			event.preventDefault();
			$.ajax({
				type: "GET",
				data: $(this).serialize(),
				contextType: "text/plain",
				dataType: "text",
				url: "../resources/api/" + $('#radioSelect:checked').val(),
				success: function (result) {
					//alert(result);
					$("#response").html(result);
				}
			});
		});
	}); 

