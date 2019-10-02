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

?>
