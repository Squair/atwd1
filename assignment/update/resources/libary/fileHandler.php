<?php
	require_once("global.php");
	require_once("config/configReader.php");
	require_once("errorResponse.php");
	require_once("currencyFunctions.php");
	require_once("XMLFunctions.php");

    //This function will combine the most recent rates file with the currencies file, sanitise and structure all the relevant currency information
    function combineFiles(){
        //Get all filePaths stored in config file
        $filePathLocs = getItemFromConfig("filepaths");
        
        $ratesPath = ROOT_PATH . $filePathLocs->xml->rates;
        $currenciesPath = ROOT_PATH . $filePathLocs->xml->currencies;
		$rateCurrenciesPath = ROOT_PATH . $filePathLocs->xml->rateCurrencies;
		        
        $ratesTimestamp = getTimeLastUpdated();
		//Get list of currency codes from ISO and check rates doesn't need update.
		//If by some very unkown reason program flow managed to get to here without ever invoking XMLOperation on rates.xml and currencies.xml, these functions will ensure this 
        $currencies = getAllCurrencyCodes();
		$timeLastUpdated = getTimeLastUpdated();
		if (currencyNeedsUpdate($timeLastUpdated)){
			if (!updateRatesFile($timeLastUpdated)){
				return;
			}
		}
        
		//If a rateCurrencies file already exists, get wheter the codes were live or not so it persists across files
		if (file_exists($rateCurrenciesPath)){
            $rateCurrenciesXml = simplexml_load_file($rateCurrenciesPath);
        }
		
        $ratesXml = simplexml_load_file(replaceTimestamp($ratesPath, $ratesTimestamp));
        $currenciesXml = simplexml_load_file($currenciesPath);
        
        $baseRate = $ratesXml->xpath("(//base)");
        $combinedDoc = new SimpleXMLElement("<currencies ts='{$ratesTimestamp}' base='{$baseRate[0]}'></currencies>");

        foreach($currencies as $currency){			
			//Get all entries associated to currency code
            $matches = $currenciesXml->xpath("//CcyNtry[Ccy='{$currency}']");
            
			//Get location information where currency is used and ensure name is sanitised
			$locArr = array();

            foreach($matches as $match){
                array_push($locArr, sanitiseLocationName($match->CtryNm));
				$ccyNm = $match->CcyNm;
            }
			
            if (isset($rateCurrenciesXml)){
                $live = $rateCurrenciesXml->xpath("//currency[code={$currency}]@live")->item(0)->nodeValue;
            } else {
                $live = 1;
            }
            
            $currInfo = array(
                'rate' => $ratesXml->rates->{$currency},
                'code' => $currency,
                'curr' => $ccyNm,
                'loc' => implode(", ", $locArr),
				'live' => $live
            );
			
			//Build up combined file using both information from rates and currencies file.
			sxml_append($combinedDoc, formatCurrency($currInfo));
        }

		$dom = dom_import_simplexml($combinedDoc)->ownerDocument;
		$dom->formatOutput = true;
		$dom->save($rateCurrenciesPath);
		return true;
    }

    function formatCurrency($currInfo){
        $currency = new SimpleXMLElement("<currency rate='{$currInfo['rate']}' live='{$currInfo['live']}'></currency>");
		$currency->addChild('code', $currInfo['code']);
		$currency->addChild('curr', $currInfo['curr']);
		$currency->addChild('loc', $currInfo['loc']);
        
        return $currency;
    }

    //https://stackoverflow.com/questions/4778865/php-simplexml-addchild-with-another-simplexmlelement
    function sxml_append(SimpleXMLElement $to, SimpleXMLElement $from) {
        $toDom = dom_import_simplexml($to);
        $fromDom = dom_import_simplexml($from);
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
    }

	//Will reformat the country names where (THE) proceeds the country name or other typically prefixed statements and strip whitespace from end of string
	function sanitiseLocationName($locName){
		$pattern = "~([\w\s’']*)\(((THE)?([\w\s’']*))(OF)?\)~";
		$replacement = "$2 $1";
		//Capitalise the first letter of each word, remove additonal spacing from end of string and reorder location from match of regular expression
		return ucwords(strtolower(rtrim(preg_replace($pattern, $replacement, $locName))));
	}

//Will call the fixer api to retrive an up to date rates file and calls combineFiles() to combine the rates and currencies files together
	function updateRatesFile($timeLastUpdated){
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
		
			//Copy old rates file with new timestamp in name so it take precedent in xmlOperation's setFilePath()
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

	function replaceTimestamp($filePath, $timestamp){
		return str_replace("{timestamp}", $timestamp, $filePath);
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

?>
