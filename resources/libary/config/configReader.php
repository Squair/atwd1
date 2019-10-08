<?php 
	function getConfig(){
		return file_get_contents(__DIR__ . "\config.json");
	}

	function getFilePathsFromConfig(){
		$config = json_decode(getConfig());
		return $config->filepaths;
	}

	function getApiEndpointsFromConfig(){
		$config = json_decode(getConfig());
		return $config->api;
	}

?>
