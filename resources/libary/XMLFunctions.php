<?php
	require_once("config/configReader.php");

	class XMLOperation {
		public $dom;
		public $filePath;
		
		public static function invoke(callable $fn){
			$f = $fn(new static());
			
			if (isset($f->dom) && isset($f->filePath)){
				//TODO: $f->formatDom(); Can this be removed?
				$f->saveDom();
			}
			return $f;
		}
		
		public function setFilePath($filePathType){
			$filePathLocs = getItemFromConfig("filepaths");
			$filePath = $filePathLocs->xml->{$filePathType};
			$this->setDom();
			$this->filePath = $filePath;

			if(file_exists($filePath)){
				$this->loadDom();
			} else {
				$this->dom->loadXML("<" . $filePathType . "/>");
			}
			
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
		
		public function writeNewElement($newNode){
			$this->dom->documentElement->appendChild($this->dom->importNode($newNode, true));
			
			return $this;
		}
		
		public function deleteElements($xpathQuery){
			$elements = iterator_to_array($this->findElements($xpathQuery));
			
			$removeFunc = function($element){
				$element->parentNode->removeChild($element);
			};
			
			array_map($removeFunc, $elements);
			return $this;
		}
		
		public function printElements($dom){
			header('Content-type: text/xml');
			$dom->preserveWhiteSpace = false;
			$dom->formatOutput = true;
			echo $dom->saveXML();
		}
		
		public function getElements($xpathQuery = "//*")
		{
			$elements = $this->findElements($xpathQuery);
			$newDom = new DomDocument('1.0');
			$newDom->preserveWhiteSpace = false;
			$newDom->formatOutput = true;
			
			foreach($elements as $element){
				$newDom->appendChild($newDom->importNode($element, true));
			}

			return $newDom;
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

		public function findElements($xpathquery){
			$xpath = new domxpath($this->dom);
			return $xpath->query($xpathquery);
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
