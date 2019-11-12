<?php
	require_once("global.php");
	require_once("config/configReader.php");
	require_once("errorResponse.php");
	require_once("currencyFunctions.php");

    function combineFiles(){
        $filePathLocs = getItemFromConfig("filepaths");
        
        $ratesPath = ROOT_PATH . $filePathLocs->xml->rates;
        $currenciesPath = ROOT_PATH . $filePathLocs->xml->currencies;
        
        $ratesTimestamp = getTimeLastUpdated();

        
        $ratesXml = simplexml_load_file(replaceTimestamp($ratesPath, $ratesTimestamp));
        $currenciesXml = simplexml_load_file($currenciesPath);
        
        $baseRate = $ratesXml->xpath("(//base)");
        $combinedDoc = new SimpleXMLElement("<currencies ts='{$ratesTimestamp}' base='{$baseRate[0]}'></currencies>");

        
        $rates = $ratesXml->xpath("//rates");
        $currencies = getAllCurrencyCodes();
        
        $locArr = array();
        
        
        foreach($rates[0] as $rate => $value){
            $matches = $currenciesXml->xpath("//CcyNtry[Ccy='{$rate}']");
            
            foreach($matches as $match){
                $ctryNm = $match->CtryNm;
                array_push($locArr, sanitiseLocationName($ctryNm));
				$ccyNm = $match->CcyNm;

            }
            $currInfo = array(
                'rate' => $value,
                'code' => $rate,
                'curr' => $ccyNm,
                'loc' => implode(", ", $locArr)
            );
			sxml_append($combinedDoc, formatCurrency($currInfo));

            
        }

		$dom = dom_import_simplexml($combinedDoc)->ownerDocument;
		$dom->formatOutput = true;
		$dom->save("test.xml");
        
    }

    function formatCurrency($currInfo){
        $currency = new SimpleXMLElement("<currency rate='{$currInfo['rate']}' live='1'></currency>");
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
