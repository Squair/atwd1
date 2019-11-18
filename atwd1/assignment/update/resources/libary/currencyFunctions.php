<?php
	require_once("XMLFunctions.php");
	require_once("config/configReader.php");
	require_once("response.php");
	require_once("global.php");
	require_once("fileHandler.php");


	function calcConversionAmount($fromRate, $toRate, $amount){
		return ($toRate / $fromRate) * $amount;
	}

	function getRateCurrency($code){
		$curr = XMLOperation::invoke(function($f) use ($code){
			return $f
				->setFilePath("rateCurrencies")
				->getParentNodeOfValue("code", $code);
		});
		
		return simplexml_import_dom($curr);
	}

	function getOldRate($code){
		$curr = XMLOperation::invoke(function($f) use ($code){
			return $f
				->setFilePath("ratesOld")
				->findElements("//{$code}");
		});
		//If false, no older rates file exists.
		return $curr->length > 0 ? $curr->item(0)->nodeValue : NULL ;
	}

    function getAllCurrencyCodes(){
        $currCodes = XMLOperation::invoke(function($f){
            return $f
                ->setFilePath("currencies")
                ->findElementsArray("//Ccy");
        });
		return array_unique($currCodes);
    }

	function currencyNeedsUpdate($lastUpdated){
		//If it was never updated, we need to update.
		if ($lastUpdated == false){
			return true;
		}
		
		$updateRate = getItemFromConfig("api")->fixer->updateRate;
		return time() - getTimeLastUpdated() >= $updateRate ? true : false;
	}

	function updateRatesFile($timeLastUpdated){
		//Check if rates needs updating, if so update it
			$apiConfig = getItemFromConfig("api");
			//@ symbol to supress warnings generated from file_get_contents in case server is having issues talking to other host names, this would breach API key
			$currencyJson = @file_get_contents($apiConfig->fixer->endpoint);
		
			//If API call fails, return
			if ($currencyJson === FALSE){
				echo $_GET['action'] == "get" ? getErrorResponse(ERROR_IN_SERVICE) : getErrorResponse(ACTION_ERROR);
				return false;
			}
		
			$currencyDecode = json_decode($currencyJson);
		
			//Get latest file path, and copy to new path with timestamp if it exists
			$filePathLocs = getItemFromConfig("filepaths");
			$filePath = ROOT_PATH . $filePathLocs->xml->rates;
		
			//Replace placeholder with timestamps ready to be copied
			$ratePath = replaceTimestamp($filePath, $timeLastUpdated);
			$newRatePath = replaceTimestamp($filePath, $currencyDecode->timestamp);
		
			//Copy old rates file with new timestamp in name
			if (file_exists(realpath($ratePath))){
				copy($ratePath, $newRatePath);
	            clearstatcache();
			} else { //If cant find rates at config location, create empty rates files, and overwrite
				file_put_contents($newRatePath, "<root></root>"); 
			}	
		
			//Update the latest timestamped rates file with the API response
			XMLOperation::invoke(function($f) use ($currencyJson){
				return $f
					->setFilePath("rates")
					->createXmlFromJson(convertBaseRate($currencyJson));
			});
			//Will stitch rates and currencies together which means less processing later down the line.
			combineFiles();
			return true;
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

	//Source: https://stackoverflow.com/questions/5696412/how-to-get-a-substring-between-two-strings-in-php
	function get_string_between($string, $start, $end){
		$string = ' ' . $string;
		$ini = strpos($string, $start);
		if ($ini == 0) return '';
		$ini += strlen($start);
		$len = strpos($string, $end, $ini) - $ini;
		return substr($string, $ini, $len);
	}

	function replaceTimestamp($filePath, $timestamp){
		return str_replace("{timestamp}", $timestamp, $filePath);
	}

	function getConversionResponse($fromCode = "GBP", $toCode, $amount, $format){
		$at = gmdate("d F Y H:i",  getTimeLastUpdated());
		        
        $fromCurrencyData = getRateCurrency($fromCode);
        $toCurrencyData = getRateCurrency($toCode);
		$convAmount = calcConversionAmount($fromCurrencyData['rate'], $toCurrencyData['rate'], $amount);
        $rate = ($convAmount / $amount);

		$response = array(
			'conv' => array(
				'at' => $at,
				'rate' => $rate,
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

	function checkCurrencyCodesExists($currCodes){
		return XMLOperation::invoke(function($f) use ($currCodes){
				return $f
					->setFilePath("rateCurrencies")
					->checkElementValue("code", $currCodes);
		});
	}

	function checkCurrencyCodesLive($currCodes){
		return XMLOperation::invoke(function($f) use ($currCodes){
				return $f
					->setFilePath("rateCurrencies")
					->checkAttributeValues($f->getParentNodesOfValues("code", $currCodes), "live", "1");
		});
	}

	function getDataForDropdown($dataList){
		//Sort the list and print out option tags
		$sortedList = array_map('strval', $dataList);
		sort($sortedList);
		foreach ($sortedList as $listItem){
			 echo "<option value='{$listItem}'>{$listItem}</option>";
		}
	}

	//Will reformat the country names where (THE) proceeds the country name or other typically prefixed statements and strip whitespace from end of string
	function sanitiseLocationName($locName){
		$pattern = "~([\w\s’']*)\(((THE)?([\w\s’']*))(OF)?\)~";
		$replacement = "$2 $1";
		return ucwords(strtolower(rtrim(preg_replace($pattern, $replacement, $locName))));
	}
?>
