
	$(document).ready(function () {
		$('#paramsForm').submit(function (event) {
			
			event.preventDefault();
			var radio;
			if ($('#radioSelect:checked').val() != "get"){
				radio = "update";
			} else {
				radio = "get";
			}
			
			$.ajax({
				type: "GET",
				data: $(this).serialize(),
				contextType: "text/plain",
				dataType: "text",
				url: "../resources/api/" + radio + ".php",
				success: function (result) {
					//alert(result);
					$("#response").html(result);
				}
			});
		});
	}); 

