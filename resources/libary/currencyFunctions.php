<?php
	require_once("XMLFunctions.php");

	function createNewCurrency($type, $country, $symbol, $rate){
		
		$tempDom = new domdocument();
		
		$newCurrency = $tempDom->createElement("Currency");
		
		//Adds attribute for the currency
		$currencyType = $tempDom->createAttribute("type");
		$currencyType->value = $type;
		$newCurrency->appendChild($currencyType);
		
		$newCurrency->appendChild($tempDom->createElement("Country", $country));
		$newCurrency->appendChild($tempDom->createElement("Symbol", $symbol));
		$newCurrency->appendChild($tempDom->createElement("Rate", $rate));
		
		return $newCurrency;
	}

	function createNewRate($countryCode, $rate){
		//TODO
	}

	function getConversionAmount($fromRate, $toRate, $amount){
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
		$timestamp = XMLOperation::invoke(function($f){
			return $f
				->setFilePath("rates")
				->findElements("/root/timestamp");
		});
		//7200 = 2hours
		return time() - $timestamp->item(0)->nodeValue >= 7200 ? true : false;
	}


	function getConversionResponse($fromCode, $toCode, $amount, $format){
		$fromRate = getRateData($fromCode);
		$toRate = getRateData($toCode);
                
        $fromCountryData = getCurrencyInfo($fromCode);
        $toCountryData = getCurrencyInfo($toCode);

        $convAmount = getConversionAmount($fromRate, $toRate, $amount);
        $rate = ($convAmount / $amount);
        
		$response = array(
			'conv' => array(
				'at' => "test",
				'rate' => $rate,
				'from' => array(
					'code' => $fromCode,
					'curr' => $fromCountryData['curr'],
					'loc' => $fromCountryData['loc'],
					'amnt' => number_format($amount, 2, '.', '')
				),
				'to' => array(
					'code' => $toCode,
					'curr' => $toCountryData['curr'],
					'loc' => $toCountryData['loc'],
					'amnt' => number_format($convAmount, 2, '.', '')
				)
			)
		);
		
		if ($format == "json"){
			return json_encode($response, JSON_PRETTY_PRINT);
		} else if ($format == "xml") {
			return XMLOperation::invoke(function($f) use ($response){
				return $f
					->createXmlFromJson(json_encode($response))
					->printElements($f->dom);
			});
		}
	}

	function getCurrencyInfo($currCode){
		$xml = simplexml_load_file("../xml/currencies.xml");
        $matches = $xml->xpath("//CcyNtry[Ccy='{$currCode}']");
        
        $locArr = array();
        
        foreach($matches as $match){
            array_push($locArr, (string) $match->CtryNm);
        }
                
        $infoArray = array(
            'curr' => (string) $matches[0]->CcyNm,
            'loc' => implode(", ", $locArr)
        );
        return $infoArray;
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
		$codes = XMLOperation::invoke(function($f){
			return $f
				->setFilePath("rates")
				->dom->getElementsByTagName("rates");
		});		
		return $codes;
	}

	function getDataForDropdown($dataList){
		foreach ($dataList->item(0)->childNodes as $item){
			echo "<option value='{$item->nodeName}'>{$item->nodeName}</option>";
		}
	}
?>
