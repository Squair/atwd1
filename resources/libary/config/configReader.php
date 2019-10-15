<?php
	require_once(__DIR__ . "/../global.php");

	function getConfig(){
		$filePath = ROOT_PATH . 'libary/config/config.json';
		if (file_exists($filePath)){
			return file_get_contents($filePath);
		} else {
			//SOMETHING
		}
	}

	function getItemFromConfig($item){
		return json_decode(getConfig())->{$item};
	}
?>
