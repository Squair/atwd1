<?php
	class XMLOperation {
		public $dom;
		public $filePath;
		
		public static function invoke(callable $fn){
			$f = $fn(new static());
			
			if (isset($f->dom) && isset($f->filePath)){
				$f->formatOutput = true;
				$f->saveDom($f->filePath);
			}
		}
		
		public function setFilePath($filePath){
			$this->filePath = $filePath;
			$this->setDom();
			return $this;
		}
		
		public function replaceXmlElement($xpathQuery, $newDom) {
			$xpath = new domxpath($this->dom);
			$elements = $xpath->query($xpathQuery);
			
			if (!is_null($elements->item(0)))
			{
				$oldnode = $elements->item(0);
				$newnode = $this->dom->importNode($newDom, true);
				$oldnode->parentNode->replaceChild($newnode, $oldnode);
			}
			return $this;
		}
		
		public function writeNewElement($newNode){
			$this->dom->documentElement->appendChild($this->dom->importNode($newNode, true));
			
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
	





?>
