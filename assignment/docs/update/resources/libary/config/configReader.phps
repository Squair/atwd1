<?php
	require_once(__DIR__ . "/../global.php");
    
    //Will try to locate the config file and return the file contents
	function getConfig(){
		$filePath = ROOT_PATH . 'libary/config/config.json';
		if (file_exists($filePath)){
			return file_get_contents($filePath);
		} else {
            //If the file can't be found, return error in service depending on which action was trying to be invoked
			if (isset($_GET['action'])){
				return $_GET['action'] == "get" ? exit(getErrorResponse(ERROR_IN_SERVICE)) : exit(getErrorResponse(ACTION_ERROR));
			}
			return exit(getErrorResponse(ERROR_IN_SERVICE));
		}
	}

    //Will return a specific set of items from the config 
	function getItemFromConfig($item){
		return json_decode(getConfig())->{$item};
	}
?>
