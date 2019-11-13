<?php
	require_once(__DIR__ . "/../global.php");

	function getConfig(){
		$filePath = ROOT_PATH . 'libary/config/config.json';
		if (file_exists($filePath)){
			return file_get_contents($filePath);
		} else {
			if (isset($_GET['action'])){
				return $_GET['action'] == "get" ? exit(getErrorResponse(ERROR_IN_SERVICE)) : exit(getErrorResponse(ACTION_ERROR));
			}
			return exit(getErrorResponse(ERROR_IN_SERVICE));
		}
	}

	function getItemFromConfig($item){
		return json_decode(getConfig())->{$item};
	}
?>
