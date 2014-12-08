<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
	class VTConditionalExpression{
		public function __construct($expression){
			$parser = new VTConditionalParser($expression);
			$this->expTree = $parser->parse();
		}

		public function evaluate($data){
			$this->env=$data;
			return $this->evalGate($this->expTree);
		}
		
		private function evalGate($tree){
			if(in_array($tree[0], array("and", "or"))){
				switch($tree[0]){
					case "and":
						return $this->evalGate($tree[1]) and $this->evalGate($tree[2]);
					case "or":
						return $this->evalGate($tree[1]) or $this->evalGate($tree[2]);
				}
			}else{
				return $this->evalCondition($tree);
			}
		}
		
		private function evalCondition($tree){
			switch($tree[0]){
				case "=":
					return (int)$this->getVal($tree[1]) == (int)$this->getVal($tree[2]);
			}
		}
		
		private function getVal($node){
			list($valueType, $value) = $node;
			switch($valueType){
				case "sym":
					return $this->env[$value];
				case "num":
					return $value;
			}
		}
	}
	
class VTParseFailed extends Exception { }
	
/**
 * This is a simple parser for conditional expressions used to trigger workflow actions.
 * 
 */
class VTConditionalParser{
	
		
	public function __construct($expr){
		$this->tokens = $this->getTokens($expr);
		$this->pos = 0;
	}
	
	private function getTokens($expression){
		preg_match_all('/and|or|\\d+|=|\\w+|\\(|\\)/',$expression, $matches, PREG_SET_ORDER);
		$tokens=array();
		foreach($matches as $arr){
			$tokenVal = $arr[0];
			if(in_array($tokenVal, array("and", "or", "=", "(", ")"))){
				$tokenType = "op";
			}else if(is_numeric($tokenVal)){
				$tokenType = "num";
			}else{
				$tokenType = "sym";
			}
			$tokens[]=array($tokenType, $tokenVal);
		}
		return $tokens;
	}
	
	
	public function parse(){
		$op =  array(
			"and"=>array("op", "and"),
			"or"=>array("op", "or"),
			"="=>array("op", "="),
			"("=>array("op", "("),
			")"=>array("op", ")"));

		if($this->peek()==$op['(']){
			$this->nextToken();
			$left = $this->parse();
			if($this->nextToken()!= $op[')']){
				throw new VTParseFailed();
			}
		}else{
			$left = $this->cond();
		}
		if(sizeof($this->tokens)>$this->pos and in_array($this->peek(), array($op["and"], $op["or"]))){
			$nt = $this->nextToken();
			return array($nt[1], $left, $this->parse());
		}else{
			return $left;
		}
	}
	
	private function cond(){
		$left = $this->nextToken();
		$operator = $this->nextToken();
		$right = $this->nextToken();
		return array($operator[1], $left, $right);
	}
	
	private function peek(){
		return $this->tokens[$this->pos];
	}
	
	private function nextToken(){
		$this->pos+=1;
		return $this->tokens[$this->pos - 1];
	}
}
	
?>