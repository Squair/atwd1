<?php
	require_once("global.php");
	require_once("config/configReader.php");
	require_once("errorResponse.php");
	require_once("currencyFunctions.php");
	require_once("XMLFunctions.php");

    function combineFiles(){
        $filePathLocs = getItemFromConfig("filepaths");
        
        $ratesPath = ROOT_PATH . $filePathLocs->xml->rates;
        $currenciesPath = ROOT_PATH . $filePathLocs->xml->currencies;
		$rateCurrenciesPath = ROOT_PATH . $filePathLocs->xml->rateCurrencies;
		
		//If a rateCurrencies file already exists, get wheter the codes were live or not so it persists across files
		
        
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
			
            $currInfo = array(
                'rate' => $ratesXml->rates->{$currency},
                'code' => $currency,
                'curr' => $ccyNm,
                'loc' => implode(", ", $locArr),
				'live' => "1"
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

?>
