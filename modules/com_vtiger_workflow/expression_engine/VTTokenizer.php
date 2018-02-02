<?php
/* +*******************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * **************************************************************************** */

class VTExpressionTokenizer
{

	public function __construct($expr)
	{
		$expr = App\Purifier::decodeHtml($expr);
		$tokenTypes = [
			'SPACE' => ['\s+', '_vt_processtoken_id'],
			'SYMBOL' => ['[a-zA-Z][\w]*', '_vt_processtoken_symbol'],
			'ESCAPED_SYMBOL' => ['?:`([^`]+)`', '_vt_processtoken_symbol'],
			//"STRING" => array('?:(?:"((?:\\\\"|[^"])+)"|'."'((?:\\\\'|[^'])+)')", 'stripcslashes'),
			//"STRING" => array('?:"((?:\\\\"|[^"])+)"', 'stripcslashes'),
			'STRING' => ["?:'((?:\\\\'|[^'])+)'", 'stripcslashes'],
			'FLOAT' => ['\d+[.]\d+', 'floatval'],
			'INTEGER' => ['\d+', 'intval'],
			'OPERATOR' => ['[+]|[-]|[*]|>=|<=|[<]|[>]|==|\/', '_vt_processtoken_symbol'],
			// NOTE: Any new Operator added should be updated in VTParser.inc::$precedence and operation at VTExpressionEvaluater
			'OPEN_BRACKET' => ['[(]', '_vt_processtoken_symbol'],
			'CLOSE_BRACKET' => ['[)]', '_vt_processtoken_symbol'],
			'COMMA' => ['[,]', '_vt_processtoken_symbol']
		];
		$tokenReArr = [];
		$tokenNames = [];
		$this->tokenTypes = $tokenTypes;

		foreach ($tokenTypes as $tokenName => $code) {
			list($re) = $code;
			$tokenReArr[] = '(' . $re . ')';
			$tokenNames[] = $tokenName;
		}
		$this->tokenNames = $tokenNames;
		$tokenRe = '/' . implode('|', $tokenReArr) . '/';
		$this->EOF = new VTExpressionToken('EOF');

		$matches = [];
		preg_match_all($tokenRe, $expr, $matches, PREG_SET_ORDER);
		$this->matches = $matches;
		$this->idx = 0;
	}

	public function nextToken()
	{
		$matches = $this->matches;
		$idx = $this->idx;
		if ($idx == sizeof($matches)) {
			return $this->EOF;
		} else {
			$match = $matches[$idx];
			$this->idx = $idx + 1;
			$i = 1;
			while (empty($match[$i])) {
				$i += 1;
			}
			$tokenName = $this->tokenNames[$i - 1];
			$token = new VTExpressionToken($tokenName);
			if (method_exists($this, $this->tokenTypes[$tokenName][1])) {
				$token->value = call_user_func([$this, $this->tokenTypes[$tokenName][1]], $match[$i]);
			} else {
				$token->value = $this->tokenTypes[$tokenName][1]($match[$i]);
			}
			return $token;
		}
	}

	private function _vt_processtoken_id($token)
	{
		return $token;
	}

	private function _vt_processtoken_symbol($token)
	{
		return new VTEXpressionSymbol($token);
	}
}
