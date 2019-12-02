<?php
	require_once("XMLFunctions.php");
	require_once("config/configReader.php");
	require_once("global.php");

	//Returns the converted rate between two currencies, multiplied by the amount requested
	function calcConversionAmount($fromRate, $toRate, $amount){
		return ($toRate / $fromRate) * $amount;
	}

	//Will retrive all information amount a given currency from the amalgamated rateCurrencies file
	function getRateCurrency($code){
		$curr = XMLOperation::invoke(function($f) use ($code){
			return $f
				->setFilePath("rateCurrencies")
				->getParentNodeOfValue("code", $code);
		});
		return simplexml_import_dom($curr);
	}
	
	//Find old rate, used in post requests by looking at an older rates file if it exists
	function getOldRate($code){
		$curr = XMLOperation::invoke(function($f) use ($code){
			return $f
				->setFilePath("ratesOld")
				->findElements("//{$code}");
		});
		//If false, no older rates file exists.
		return $curr->length > 0 ? $curr->item(0)->nodeValue : NULL ;
	}

	//Returns an array of all currency codes from the ISO currencies file
    function getAllCurrencyCodes(){
        $currCodes = XMLOperation::invoke(function($f){
            return $f
                ->setFilePath("currencies")
                ->findElementsArray("//Ccy");
        });
		return array_unique($currCodes);
    }

    //Return the base rate from the rateCurrencies file
    function getBaseRate(){
        $baseRate = XMLOperation::invoke(function($f){
            return $f
                ->setFilePath("rateCurrencies")
                ->findElements("//currencies/@base");
        });
		return $baseRate->item(0)->nodeValue;
    }

	//Checks the timestamp on the most recent rates file to see if its higher than the update rate pulled from config file
	function currencyNeedsUpdate($lastUpdated){
		//If it was never updated, we need to update.
		if ($lastUpdated == false){
			return true;
		}
		
		$updateRate = getItemFromConfig("api")->fixer->updateRate;
		return time() - getTimeLastUpdated() >= $updateRate ? true : false;
	}

	//Picks the timestamps from all the historic rates files and returns the most recent time
	function getTimeLastUpdated($offset = 0){
		$filePathLocs = getItemFromConfig("filepaths");
		
		//Find all rates files with timestamps proceeding them
		$ratePath = ROOT_PATH . $filePathLocs->xml->ratesGlob . "*";

		$timestamps = array();
		
		//Loop thorugh file name, get timestamp between 'rates' and extension
		foreach(glob($ratePath) as $foundFile){
			$timestamp = get_string_between($foundFile, "rates", ".xml");
			if ($timestamp != '') array_push($timestamps, (int)$timestamp);
		}
		//If no timestamps found for rates for or if trying to access historic rates file that doesn't exist, return false
		if (empty($timestamps) || $offset >= count($timestamps)) return false;
		//Sort decending
		rsort($timestamps);
		//Get most recent timestamp at base index, or return next descending timestamp at offset 
		return $timestamps[$offset];
	}

    //Taking the response from Fixer, convert all currencies to a chosen base rate, defaults to GBP if nothing explicitley passed
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

    //Takes a single or multiple codes and checks that they exist within the rateCurrencies file, if they all exist this will return true, if one returns false then the function will return false
	function checkCurrencyCodesExists($currCodes){
		return XMLOperation::invoke(function($f) use ($currCodes){
				return $f
					->setFilePath("rateCurrencies")
					->checkElementValue("code", $currCodes);
		});
	}

    //Takes a single or multiple codes and checks that the attribute 'live' is set to 1 within the rateCurrencies file, if they're all set to 1 this function will return true, if one returns false then the function will return false
	function checkCurrencyCodesLive($currCodes){
		return XMLOperation::invoke(function($f) use ($currCodes){
				return $f
					->setFilePath("rateCurrencies")
					->checkAttributeValues($f->getParentNodesOfValues("code", $currCodes), "live", "1");
		});
	}

    //Will take in an array of items, sort them in acending order and print them out within <option> tags, to populate a <select> dropdown
	function getDataForDropdown($dataList){
		//Sort the list and print out option tags
		$sortedList = array_map('strval', $dataList);
		sort($sortedList);
		foreach ($sortedList as $listItem){
			 echo "<option value='{$listItem}'>{$listItem}</option>";
		}
	}
?>
