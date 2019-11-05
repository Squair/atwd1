<?php
	require_once("XMLFunctions.php");
	require_once("config/configReader.php");
	require_once("response.php");
	require_once("global.php");


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

    function getAllCurrencyCodes(){
        return XMLOperation::invoke(function($f){
            return $f
                ->setFilePath("currencies")
                ->findElements("/ISO_4217/CcyTbl/CcyNtry/Ccy");
        });
    }

	function currencyNeedsUpdate(){
		$updateRate = getItemFromConfig("api")->fixer->updateRate;
		return time() - getTimeLastUpdated() >= $updateRate ? true : false;
	}

	function updateRatesFile(){
		//Check if rates needs updating, if so update it
			$apiConfig = getItemFromConfig("api");
			$currencyJson = file_get_contents($apiConfig->fixer->endpoint); //TODO return 1500 if not availible
			XMLOperation::invoke(function($f) use ($currencyJson){
				return $f
					->setFilePath("rates")
					->createXmlFromJson(convertBaseRate($currencyJson));
			});
				
	}

	function getTimeLastUpdated(){
		$timestamp = XMLOperation::invoke(function($f){
			return $f
				->setFilePath("rates")
				->findElements("/root/timestamp");
		});		
		return $timestamp->item(0)->nodeValue;
	}

	function getConversionResponse($fromCode = "GBP", $toCode, $amount, $format){
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
        //TODO: BTC doresnt have a location, handle this!!
		$matches = XMLOperation::invoke(function($f) use ($currCode){
				return $f
					->setFilePath("currencies")
					->findElements("//CcyNtry[Ccy='{$currCode}']");
		});
        $locArr = array();
        
        foreach($matches as $match){
			$ctryNm = $match->getElementsByTagName("CtryNm");
			$location = $ctryNm->item(0)->nodeValue;
            array_push($locArr, sanitiseLocationName($location));
			
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

	function checkCurrencyCodesExists($currCodes){
		return XMLOperation::invoke(function($f) use ($currCodes){
				return $f
					->setFilePath("rates")
					->checkElementsExist($currCodes);
		});
	}

	function checkCurrencyCodesUnavailable($currCodes){
		return XMLOperation::invoke(function($f) use ($currCodes){
				return $f
					->setFilePath("rates")
					->checkAttributeValues($currCodes, "unavailable", "true");
		});
	}

	function getDataForDropdown($dataList){
		foreach ($dataList as $item){
			echo "<option value='{$item->nodeValue}'>{$item->nodeValue}</option>";
		}
	}

	//Will reformat the country names where (THE) proceeds the country name or other typically prefixed statements
	function sanitiseLocationName($locName){
		$pattern = "~([\w\s’']*)\(((THE)?([\w\s’']*))(OF)?\)~";
		$replacement = "$2 $1";
		return preg_replace($pattern, $replacement, $locName);
	}
?>
