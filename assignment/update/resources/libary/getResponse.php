<?php	
	//Handles the response made when a GET request is made. builds it up in JSON which can be simply converted to XML if format is required
    function getConversionResponse($fromCode = "GBP", $toCode, $amount, $format){
		$at = gmdate("d F Y H:i",  getTimeLastUpdated());
		        
        $fromCurrencyData = getRateCurrency($fromCode);
        $toCurrencyData = getRateCurrency($toCode);
		
        $fromRate = $fromCurrencyData->attributes()['rate'];
        $toRate = $toCurrencyData->attributes()['rate'];

		$convAmount = calcConversionAmount((float) $fromRate, (float) $toRate, $amount);
        
		$response = array(
			'conv' => array(
				'at' => $at,
				'rate' => (string) $fromRate,
				'from' => array(
					'code' => $fromCode,
					'curr' => (string) $fromCurrencyData->curr,
					'loc' => (string) $fromCurrencyData->loc,
					'amnt' => number_format($amount, 2, '.', '')
				),
				'to' => array(
					'code' => $toCode,
					'curr' => (string) $toCurrencyData->curr,
					'loc' => (string) $toCurrencyData->loc,
					'amnt' => number_format($convAmount, 2, '.', '')
				)
			)
		);
		return sendResponse($response, $format);
	}
?>
