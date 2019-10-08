<?php 
	function getConfig(){
		return file_get_contents(__DIR__ . "\config.json");
	}

	function getItemFromConfig($item){
		return json_decode(getConfig())->{$item};
	}
?>
