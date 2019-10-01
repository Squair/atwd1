<?php
	abstract class CurrencyOperation {
		public $dom;
		public $filePath;
		
		public static function invoke(callable $fn){
			$f = $fn(new static());
			
			$f->formatOutput = true;
			$f->saveDom($f->filePath);
		}
		
		public function setFilePath($filePath){
			$this->filePath = $filePath;
			return $this;
		}
		
		protected function setDom()
		{
			$this->dom = new domdocument();
			$this->dom->load($this->filePath);
			return $this;
		}
		
		protected function saveDom()
		{
			$this->dom->save($this->filePath);
		}
	}
	
	class CurrencyUpdate extends CurrencyOperation {
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
	}

	class CurrencyWriter extends CurrencyOperation {
		public function writeNewCurrency($type, $country, $symbol, $rate){
			if (is_null($this->dom)){
				$this->setDom();
			}
			
			$newCurrency = $this->dom->createElement("Currency");
			
			//Adds attribute for the currency
			$currencyType = $this->dom->createAttribute("type");
			$currencyType->value = $type;
			$newCurrency->appendChild($currencyType);
			
			$newCurrency->appendChild($this->dom->createElement("Country", $country));
			$newCurrency->appendChild($this->dom->createElement("Symbol", $symbol));
			$newCurrency->appendChild($this->dom->createElement("Rate", $rate));
			
			$this->dom->documentElement->appendChild($newCurrency);
			return $this;
		}
	}
?>
