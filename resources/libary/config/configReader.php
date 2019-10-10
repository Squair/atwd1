<?php
	require_once(__DIR__ . "/../global.php");

	function getConfig(){
		$filePath = ROOT_PATH . 'libary/config/config.json';
		return file_get_contents($filePath);
	}

	function getItemFromConfig($item){
		return json_decode(getConfig())->{$item};
	}
?>
