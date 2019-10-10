<?php 
	function getConfig(){

		return file_get_contents("/nas/students/g/g2-squair/unix/public_html/year 3/CurrencyConversionAPI/resources/libary/config/config.json");
	}

	function getItemFromConfig($item){
		return json_decode(getConfig())->{$item};
	}
?>
