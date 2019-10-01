<?php
	class CurrencyWriter {
		public $dom;
		public $filePath;
		
		public static function write(callable $fn){
			$f = $fn(new static());
			
			$f->formatOutput = true;
			$f->saveDom($f->filePath);
		}
		
		public function replaceXmlElement($xpathQuery, $newDom) {

			$this->setDom();
			$xpath = new domxpath($this->dom);
			$elements = $xpath->query($xpathQuery);
			
			if (!is_null($elements->item(0)))
			{
				$oldnode = $elements->item(0);
				$newnode = $this->dom->importNode($newDom->documentElement, true);
				$oldnode->parentNode->replaceChild($newnode, $oldnode);
			}
			return $this;
		}
		
		public function setFilePath($filePath){
			$this->filePath = $filePath;
			return $this;
		}
		
		private function setDom()
		{
			$this->dom = new domdocument();
			$this->dom->load($this->filePath);
			return $this;
		}
		
		private function saveDom()
		{
			$this->dom->save($this->filePath);
		}
	}

	function createNewCurrency($type, $country, $symbol, $rate)
	{
		$curr = new domdocument();
		$curr_node = $curr ->createElement("Currency");
		$curr_attribute = $curr->createAttribute("type");
		$curr_attribute->value = $type;
		$curr_node->appendChild($curr_attribute);
		$curr_node->appendChild($curr->createElement("Country", $country));
		$curr_node->appendChild($curr->createElement("Symbol", $symbol));
		$curr_node->appendChild($curr->createElement("Rate", $rate));
		$curr->appendChild($curr_node);
		$curr->formatOutput = true;
		return $curr;
	}
?>
