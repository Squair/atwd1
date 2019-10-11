<?php 
	//Root path for pulling filepaths out of config file
	define('ROOT_PATH', dirname(__DIR__). '/');

	//Status codes
	define('MISSING_PARAM', array(
		'code' => 1000,
		'msg' => "Required parameter is missing"
	));
		   
	define('UNKOWN_PARAM', array(
		'code' => 1100,
		'msg' => "Parameter not recognized"
	));
		   
	define('UNKOWN_CURRENCY', array(
		'code' => 1200,
		'msg' => "Currency type not recognized"
	));
		   
	define('CURRENCY_NOT_DECIMAL', array(
		'code' => 1300,
		'msg' => "Currency amount must be a decimal number"
	));
		   
	define('INCORRECT_FORMAT', array(
		'code' => 1400,
		'msg' => "Format must be xml or json"
	));
		   
	define('ERROR_IN_SERVICE', array(
		'code' => 1500,
		'msg' => "Error in service"
	));
		   
?>
