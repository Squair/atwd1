<?php
	require_once("response.php");

	function getErrorResponse($error, $format){
		$response = array(
			'conv' => array(
				'error' => $error['code'],
				'msg' => $error['msg']
			)
		);
		
		$format = checkFormatValueValid($format) ? $format : "xml";
		return sendResponse($response, $format);
	}


?>