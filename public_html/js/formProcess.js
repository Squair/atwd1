$(document).ready(function () {
	$(".radioSelect").click(function () {
		$('#but').val($('.radioSelect:checked').val());

		if ($('.radioSelect:checked').val() != "get") {
			//disables unneeded fields for non get requests
			$('input[name="amnt"]').attr("disabled", "disabled");
			$('select[name="format"]').val("xml");
			$('select[name="format"]').attr("disabled", "disabled");
		} else {
			//reenables if going back to get option
			$('input[name="amnt"]').removeAttr("disabled");
			$('select[name="format"]').removeAttr("disabled");
			$('select[name="format"]').val("xml");

		}
	});


	$('#paramsForm').submit(function (event) {

		event.preventDefault();
		var radio;
		if ($('#radioSelect:checked').val() != "get") {
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
