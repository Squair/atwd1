<?php
	require_once("XMLFunctions.php");
	require_once("response.php");


	function calcConversionAmount($fromRate, $toRate, $amount){
		return ($toRate / $fromRate) * $amount;
	}

	function getRateData($code){
		$curr = XMLOperation::invoke(function($f) use ($code){
			return $f
				->setFilePath("rates")
				->findElements("(/root/rates/" . $code . ")[1]");
		});
		return $curr->item(0)->nodeValue;
	}

	function currencyNeedsUpdate(){
		//7200 = 2hours
		return time() - getTimeLastUpdated() >= 7200 ? true : false;
	}

	function getTimeLastUpdated(){
		$timestamp = XMLOperation::invoke(function($f){
			return $f
				->setFilePath("rates")
				->findElements("/root/timestamp");
		});		
		return $timestamp->item(0)->nodeValue;
	}

	function getConversionResponse($fromCode, $toCode, $amount, $format = "xml"){
		$at = gmdate("d F Y H:i",  getTimeLastUpdated());
		        
        $fromCurrencyData = getCurrencyData($fromCode);
        $toCurrencyData = getCurrencyData($toCode);

		$fromRate = getRateData($fromCode);
		$toRate = getRateData($toCode);
		
		$convAmount = calcConversionAmount(getRateData($fromCode), getRateData($toCode), $amount);
        $rate = ($convAmount / $amount);

        
		$response = array(
			'conv' => array(
				'at' => $at,
				'rate' => $rate,
				'from' => array(
					'code' => $fromCode,
					'curr' => $fromCurrencyData['curr'],
					'loc' => $fromCurrencyData['loc'],
					'amnt' => number_format($amount, 2, '.', '')
				),
				'to' => array(
					'code' => $toCode,
					'curr' => $toCurrencyData['curr'],
					'loc' => $toCurrencyData['loc'],
					'amnt' => number_format($convAmount, 2, '.', '')
				)
			)
		);
		return sendResponse($response, $format);
	}

	function getCurrencyData($currCode){
		$matches = XMLOperation::invoke(function($f) use ($currCode){
				return $f
					->setFilePath("currencies")
					->findElements("//CcyNtry[Ccy='{$currCode}']");
		});

        $locArr = array();
        
        foreach($matches as $match){
			$ctryNm = $match->getElementsByTagName("CtryNm");
            array_push($locArr, $ctryNm->item(0)->nodeValue);
        }
    	
		$ccyNm = $matches->item(0)->getElementsByTagName("CcyNm")->item(0);

        return array(
            'curr' => $ccyNm->nodeValue,
            'loc' => implode(", ", $locArr)
        );
	}

	function convertBaseRate($jsonData, $newBaseType = "GBP"){
		$incomingJsonData = json_decode($jsonData);
		
		$newBaseRate = $incomingJsonData->rates->{$newBaseType};
		$multiplier = 1 / $newBaseRate;

		$incomingJsonData->base = $newBaseType;
		
		foreach ($incomingJsonData->rates as $rate => $value){
			$incomingJsonData->rates->{$rate} = $value * $multiplier;
		}
		
		return json_encode($incomingJsonData);
	}

	function getAllRateCodes(){
		return XMLOperation::invoke(function($f){
			return $f
				->setFilePath("rates")
				->findElements("rates");
		});		
	}

	function getDataForDropdown($dataList){
		foreach ($dataList->item(0)->childNodes as $item){
			echo "<option value='{$item->nodeName}'>{$item->nodeName}</option>";
		}
	}
?>
