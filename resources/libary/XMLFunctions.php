<?php
	require_once("global.php");
	require_once("config/configReader.php");

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
		
		//Based on https://www.php.net/manual/en/book.dom.php#93717
		public function xml_to_array($root) {
			$result = array();

			if ($root->hasAttributes()) {
				$attrs = $root->attributes;
				foreach ($attrs as $attr) {
					$result['@attributes'][$attr->name] = $attr->value;
				}
			}

			if ($root->hasChildNodes()) {
				$children = $root->childNodes;
				if ($children->length == 1) {
					$child = $children->item(0);
					if ($child->nodeType == XML_TEXT_NODE) {
						$result['_value'] = $child->nodeValue;
						return count($result) == 1
							? $result['_value']
							: $result;
					}
				}
				$groups = array();
				foreach ($children as $child) {
					if (!isset($result[$child->nodeName])) {
						$result[$child->nodeName] = xml_to_array($child);
					} else {
						if (!isset($groups[$child->nodeName])) {
							$result[$child->nodeName] = array($result[$child->nodeName]);
							$groups[$child->nodeName] = 1;
						}
						$result[$child->nodeName][] = xml_to_array($child);
					}
				}
			}

			return $result;
		}


		public function findElements($xpathquery){
			$xpath = new domxpath($this->dom);
			return $xpath->query($xpathquery);
		}
		
		public function checkElementExists($elementName){
			if ($this->dom->getElementsByTagName($elementName)->length == 0){
				return false;
			} else {
				return true;
			}
		}
		
		//Will return false if any one element in array doesnt exist
		public function checkElementsExist($elementNames){
			foreach ($elementNames as $elementName){
				if (!$this->checkElementExists($elementName)){
					return false;
				}
			}
			return true;
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
