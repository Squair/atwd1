<?php

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

	function getBaseRateMultiplier($newBaseRate){
		return 1 / $newBaseRate;
	}

	function convertBaseRate($jsonData, $newBaseType = "GBP"){
		$newJsonData = json_decode($jsonData);
		
		$newBaseRate = $newJsonData->rates->{$newBaseType};
		$multiplier = getBaseRateMultiplier($newBaseRate);
		
		$newJsonData->base = $newBaseType;
		
		foreach ($newJsonData->rates as $rate => $value){
			$newJsonData->rates->{$rate} = $value * $multiplier;
		}
		
		return json_encode($newJsonData);
	}

?>
