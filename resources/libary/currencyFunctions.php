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
		return number_format(($toRate / $fromRate) * $amount, 2, '.', '');
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
		
		$response = array(
			'conv' => array(
				'at' => "test",
				'rate' => $toRate,
				'from' => array(
					'code' => $fromCode,
					'amnt' => $amount
				),
				'to' => array(
					'code' => $toCode,
					'amnt' => getConversionAmount($fromRate, $toRate, $amount)
				)
			)
		);
		
		if ($format == "json"){
			return json_encode($response);
		} else if ($format == "xml") {
			return XMLOperation::invoke(function($f) use ($response){
				return $f
					->createXmlFromJson(json_encode($response))
					->printElements($f->dom);
			});
		}
	}

	function convertBaseRate($jsonData, $newBaseType = "GBP"){
		$newJsonData = json_decode($jsonData);
		
		$newBaseRate = $newJsonData->rates->{$newBaseType};
		$multiplier = 1 / $newBaseRate;
		
		$newJsonData->base = $newBaseType;
		
		foreach ($newJsonData->rates as $rate => $value){
			$newJsonData->rates->{$rate} = $value * $multiplier;
		}
		
		return json_encode($newJsonData);
	}
?>
