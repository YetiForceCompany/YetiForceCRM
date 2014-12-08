<?php
	//Todo: find a decent set implementation for php
	class Set{
		public function __construct($arr){
			$this->store = array();
			foreach($arr as $el){
				$this->store[$el] = $el;
			}
		}
		
		public function add($value){
			$this->store[$value] = $value;
		}
		
		public function member($value){
			return array_key_exists($value, $this->store);
		}
		
		public function union($otherSet){
			return new Set(array_merge($this->store, $otherSet->store));
		}
		
		public function unionInPlace($otherSet){
			$this->store = $this->union($otherSet)->store;
		}
		
		public function remove($value){
			unset($this->store[$value]);
		}
	}
	
	

?>