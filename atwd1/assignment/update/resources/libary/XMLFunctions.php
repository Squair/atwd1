<?php
	require_once("global.php");
	require_once("config/configReader.php");
	require_once("errorResponse.php");
	require_once("currencyFunctions.php");
	require_once("fileHandler.php");

	class XMLOperation {
		public $dom;
		public $filePath;
		
		public static function invoke(callable $fn){
			$f = $fn(new static());
			
			if (isset($f->dom) && isset($f->filePath)){
				$f->formatDom(); //Can this be removed?
				$f->saveDom();
			}
			return $f;
		}
		
		public function setFilePath($filePathType){
			$filePathLocs = getItemFromConfig("filepaths");
			$filePath = ROOT_PATH . $filePathLocs->xml->{$filePathType};
            
			$this->setDom();
            $this->formatDom();
            
			switch ($filePathType){
				case "rates":
					$lastUpdated = getTimeLastUpdated();				
					if ($lastUpdated != false) $this->filePath = replaceTimestamp($filePath, $lastUpdated);
					break;
				case "ratesOld":
					$previouslyUpdated = getTimeLastUpdated(1);	
					if ($previouslyUpdated == false) { return $this; } //If no previous rates file exists, return here and it will fall out pipeline later
					//Otherwise set it to the previous
					$this->filePath = replaceTimestamp($filePath, $previouslyUpdated);	
					break;
				default:
					$this->filePath = $filePath;
					
			}
						
			//Short circuit logic, if no file found then try to create it
            if (file_exists(realpath($this->filePath)) || $this->tryCreateFile($filePathType, $this->filePath)){
                $this->loadDom();
            } else { //Couldnt find required file and couldnt create the file - so throw error in service
				$action = isset($_GET['action']) ? $_GET['action'] : "get"; //Default to get if error thrown on page entry
                return $action == "get" ? exit(getErrorResponse(ERROR_IN_SERVICE)) : exit(getErrorResponse(ACTION_ERROR));
            }
            
			//Prevents file_exists from caching results
            clearstatcache();

			return $this;
		}
        
        private function tryCreateFile($fileType, $filePath){			               
			$api = getItemFromConfig("api");

            if ($fileType == "currencies"){
                $isoCurrencies = @file_get_contents($api->ISOCurrencies->endpoint);
                if ($isoCurrencies !== FALSE){
                    file_put_contents($filePath, $isoCurrencies);
                    return true;
                } else {
                    return false;
                }
            }
            if ($fileType == "rates"){
				return updateRatesFile(getTimeLastUpdated());
            }
			if ($fileType == "rateCurrencies"){
				return combineFiles();
			}
			return false;
        }
		
        public function addAttributeToElement($element, $attributeName, $attributeValue){
            $element->setAttribute($attributeName, $attributeValue);
            return $this;
        }
        
		public function updateXmlElement($xpathQuery, $newElement) {
			
			$elements = $this->findElements($xpathQuery);
			
			if (!is_null($elements->item(0)))
			{
				$oldnode = $elements->item(0);
				$newnode = $this->dom->importNode($newElement, true);
				$oldnode->parentNode->replaceChild($newnode, $oldnode);
			}
			return $this;
		}
		
		public function printElements($dom){
			header('Content-type: text/xml');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			echo $dom->saveXML();
		}
		
		public function createXmlFromJson($jsonData){
			$jsonArray = json_decode($jsonData, true);
			$convertedXml = $this->array_to_xml($jsonArray);
			$this->dom = dom_import_simplexml($convertedXml)->ownerDocument;
			return $this;
		}
		
		//Not my code, although slightly edited from the original found on stackoverflow at: https://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml (second answer)
		public function array_to_xml($data, $xml = false){
			foreach( $data as $key => $value ) {
				if( is_numeric($key) ){
					$key = 'item'.$key; //dealing with <0/>..<n/> issues
				}
				
				if( is_array($value) ) {
					//if first item is array use it as root node otherwise, create a root node
					if ($xml === false){
						$xml = new SimpleXMLElement("<" . $key . "/>");
						$this->array_to_xml($value, $xml);					
					} else {
						$subnode = $xml->addChild($key);
						$this->array_to_xml($value, $subnode);
					}
				} else if ($xml === false){
					$xml = new SimpleXMLElement("<root/>");
					$xml->addChild("$key",htmlspecialchars("$value"));
				} else {
				   $xml->addChild("$key",htmlspecialchars("$value"));
				}
			 }
			return $xml;
		}
		
		//Uses domXpath and returns domnodelist
		public function findElements($xpathquery){
			if (!isset($this->dom)) return false;
			$xpath = new domxpath($this->dom);
			return $xpath->query($xpathquery);
		}
		
		//Convert to simplexml and returns results as array to make use of array functions
		public function findElementsArray($xpathquery){
			if (!isset($this->dom)) return false;
			$dom = simplexml_import_dom($this->dom);
			return $dom->xpath($xpathquery);
		}
		
		public function checkElementExists($elementName){
			if ($this->dom->getElementsByTagName($elementName)->length == 0){
				return false;
			} else {
				return true;
			}
		}		
		
		public function checkElementValue($elementName, $values){
			$elements = $this->dom->getElementsByTagName($elementName);
			$elementArr = array();
			foreach($elements as $element){
				array_push($elementArr, $element->nodeValue);
			}
			if (count(array_diff($values, $elementArr)) > 0){
				return false;
			} else {
				return true;
			}
		}			
		
		public function checkAttributeValues($elements, $attrName, $value){
			foreach ($elements as $element){
				if ($element->getAttribute($attrName) != $value){
					return false;
				} 
			}
			return true;
		}
		
		public function getParentNodesOfValues($elementName, $values){
			$parentNodes = array();

			foreach ((array)$values as $value){
				$parentNode = $this->getParentNodeOfValue($elementName, $value);
				array_push($parentNodes, $parentNode);
			}
			return $parentNodes;
		}
		
		public function getParentNodeOfValue($elementName, $value){
			return $this->findElements("//{$elementName}[.='{$value}']/parent::*")[0];
		}		

		private function setDom(){
			$this->dom = new domdocument('1.0');
		}
		
		private function formatDom(){
			$this->dom->preserveWhiteSpace = false;
			$this->dom->formatOutput = true;
		}
		
		private function loadDom(){
			$this->formatDom();
			$this->dom->load($this->filePath);
			return $this;
		}
		
		private function saveDom()
		{
			$this->dom->save($this->filePath);
		}
	}
?>
