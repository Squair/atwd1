<?php 
	//Root path for pulling filepaths out of config file
	define('ROOT_PATH', dirname(__DIR__). '/');

	//Status codes for error handling
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

	define('UNKOWN_ACTION', array(
		'code' => 2000,
		'msg' => "Action not recognized or is missing"
	));

	define('MISSING_CURRENCY', array(
		'code' => 2100,
		'msg' => "Currency code in wrong format or is missing"
	));

	define('CURRENCY_NOT_FOUND', array(
		'code' => 2200,
		'msg' => "Currency code not found for update"
	));

	define('UNKNOWN_RATE', array(
		'code' => 2300,
		'msg' => "No rate listed for this currency"
	));

	define('IMMUTABLE_BASE_CURRENCY', array(
		'code' => 2400,
		'msg' => "Cannot update base currency"
	));

	define('ACTION_ERROR', array(
		'code' => 2500,
		'msg' => "Error in service"
	));

?>
